<?php

namespace App\Repositories\Eloquent;

use App\Models\BallByBallLog;
use App\Models\BattingScorecard;
use App\Models\BowlingScorecard;
use App\Models\Innings;
use App\Repositories\Interfaces\ScoringRepositoryInterface;
use App\Services\CalculationEngineService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ScoringRepository implements ScoringRepositoryInterface
{
    public function __construct(private readonly CalculationEngineService $calculator) {}

    public function getInnings(int $matchId): Collection
    {
        return Innings::where('match_id', $matchId)
            ->with(['battingTeam', 'bowlingTeam'])
            ->orderBy('innings_number')
            ->get();
    }

    public function createInnings(array $data): Innings
    {
        return Innings::create($data);
    }

    public function updateInnings(int $inningsId, array $data): bool
    {
        return (bool) Innings::where('id', $inningsId)->update($data);
    }

    public function recordBall(array $data): BallByBallLog
    {
        return DB::transaction(function () use ($data) {
            $ball = BallByBallLog::create($data);
            $this->recalculateInnings($data['innings_id']);
            $this->updateScorecards($ball);
            $this->checkMilestones($ball);
            return $ball;
        });
    }

    public function getLastBall(int $inningsId): ?BallByBallLog
    {
        return BallByBallLog::where('innings_id', $inningsId)
            ->orderByDesc('over_number')
            ->orderByDesc('ball_number')
            ->first();
    }

    public function getBallsByOver(int $inningsId, int $over): Collection
    {
        return BallByBallLog::where('innings_id', $inningsId)
            ->where('over_number', $over)
            ->orderBy('ball_number')
            ->get();
    }

    public function getLiveMatchState(int $matchId): array
    {
        $innings = Innings::where('match_id', $matchId)
            ->with(['battingTeam', 'bowlingTeam', 'battingScorecards.player', 'bowlingScorecards.player'])
            ->orderByDesc('innings_number')
            ->first();

        if (!$innings) return [];

        $lastBall = $this->getLastBall($innings->id);
        $currentOverBalls = $lastBall
            ? $this->getBallsByOver($innings->id, $lastBall->over_number)
            : collect();

        return [
            'innings' => $innings,
            'last_ball' => $lastBall,
            'current_over_balls' => $currentOverBalls,
            'batsmen' => $innings->battingScorecards->whereIn('dismissal_type', ['not_out', 'retired_hurt']),
            'current_bowler' => $innings->bowlingScorecards->sortByDesc('id')->first(),
        ];
    }

    public function undoLastBall(int $inningsId): bool
    {
        return DB::transaction(function () use ($inningsId) {
            $last = $this->getLastBall($inningsId);
            if (!$last) return false;
            $last->delete();
            $this->recalculateInnings($inningsId);
            return true;
        });
    }

    private function recalculateInnings(int $inningsId): void
    {
        $balls = BallByBallLog::where('innings_id', $inningsId)->get();

        $totalRuns = $balls->sum(fn($b) => $b->runs_scored + $b->extra_runs);
        $totalWickets = $balls->where('is_wicket', true)->count();
        $legalBalls = $balls->filter(fn($b) => !$b->is_wide && !$b->is_no_ball)->count();

        $overs = $this->calculator->ballsToDecimalOvers($legalBalls);
        $runRate = $overs > 0 ? $this->calculator->calculateRunRate($totalRuns, $legalBalls) : 0;

        Innings::where('id', $inningsId)->update([
            'total_runs' => $totalRuns,
            'total_wickets' => $totalWickets,
            'total_balls' => $legalBalls,
            'overs_faced' => $overs,
            'run_rate' => $runRate,
            'extras_wides' => $balls->where('is_wide', true)->sum('extra_runs'),
            'extras_no_balls' => $balls->where('is_no_ball', true)->sum('extra_runs'),
            'extras_byes' => $balls->where('is_bye', true)->sum('extra_runs'),
            'extras_leg_byes' => $balls->where('is_leg_bye', true)->sum('extra_runs'),
            'extras_penalty' => $balls->where('is_penalty', true)->sum('extra_runs'),
        ]);
    }

    private function updateScorecards(BallByBallLog $ball): void
    {
        // Update batting scorecard
        $batting = BattingScorecard::firstOrCreate(
            ['innings_id' => $ball->innings_id, 'player_id' => $ball->batsman_id],
            ['match_id' => $ball->match_id, 'team_id' => $ball->innings->batting_team_id]
        );

        if (!$ball->is_wide) {
            $batting->increment('balls_faced');
        }
        if (!$ball->is_wide && !$ball->is_no_ball && !$ball->is_bye && !$ball->is_leg_bye) {
            $batting->increment('runs_scored', $ball->runs_scored);
            if ($ball->is_four) $batting->increment('fours');
            if ($ball->is_six) $batting->increment('sixes');
        }

        $sr = $batting->balls_faced > 0
            ? round(($batting->runs_scored / $batting->balls_faced) * 100, 2)
            : 0;

        $batting->update([
            'strike_rate' => $sr,
            'is_fifty' => $batting->runs_scored >= 50 && $batting->runs_scored < 100,
            'is_century' => $batting->runs_scored >= 100,
        ]);

        if ($ball->is_wicket) {
            $batting->update(['dismissal_type' => $ball->wicket_type, 'bowled_by_id' => $ball->bowler_id]);
        }

        // Update bowling scorecard
        $bowling = BowlingScorecard::firstOrCreate(
            ['innings_id' => $ball->innings_id, 'player_id' => $ball->bowler_id],
            ['match_id' => $ball->match_id, 'team_id' => $ball->innings->bowling_team_id]
        );

        $bowling->increment('runs_conceded', $ball->runs_scored + $ball->extra_runs);
        if ($ball->is_wicket && !in_array($ball->wicket_type, ['run_out'])) {
            $bowling->increment('wickets');
        }
        if ($ball->is_wide) $bowling->increment('wides');
        if ($ball->is_no_ball) $bowling->increment('no_balls');

        if (!$ball->is_wide && !$ball->is_no_ball) {
            $bowling->increment('overs_bowled_balls');
        }

        $economy = $bowling->overs_bowled_balls > 0
            ? $this->calculator->calculateRunRate($bowling->runs_conceded, $bowling->overs_bowled_balls)
            : 0;

        $bowling->update([
            'overs_bowled' => $this->calculator->ballsToDecimalOvers($bowling->overs_bowled_balls),
            'economy' => $economy,
            'five_wicket_haul' => $bowling->wickets >= 5,
        ]);
    }

    private function checkMilestones(BallByBallLog $ball): void
    {
        // Hat-trick of sixes: 3 consecutive sixes by same batter
        if ($ball->is_six) {
            $lastThree = BallByBallLog::where('innings_id', $ball->innings_id)
                ->where('batsman_id', $ball->batsman_id)
                ->orderByDesc('id')
                ->limit(3)
                ->pluck('is_six');
            if ($lastThree->count() === 3 && $lastThree->every(fn($v) => $v)) {
                BattingScorecard::where('innings_id', $ball->innings_id)
                    ->where('player_id', $ball->batsman_id)
                    ->update(['hat_trick_sixes' => true]);
            }
        }

        // Hat-trick of wickets by bowler
        if ($ball->is_wicket) {
            $lastThree = BallByBallLog::where('innings_id', $ball->innings_id)
                ->where('bowler_id', $ball->bowler_id)
                ->where('is_wicket', true)
                ->orderByDesc('id')
                ->limit(3)
                ->pluck('id');
            if ($lastThree->count() === 3) {
                BowlingScorecard::where('innings_id', $ball->innings_id)
                    ->where('player_id', $ball->bowler_id)
                    ->update(['hat_trick_wickets' => true]);
            }
        }
    }
}
