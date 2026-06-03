<?php

namespace App\Services;

use App\Models\CricketMatch;
use App\Models\Innings;
use App\Repositories\Interfaces\ScoringRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class ScoringService
{
    public function __construct(
        private readonly ScoringRepositoryInterface $scoring,
        private readonly CalculationEngineService   $calculator,
    ) {}

    public function startMatch(int $matchId, array $tossData): CricketMatch
    {
        $match = CricketMatch::findOrFail($matchId);
        $match->update(array_merge($tossData, ['status' => 'live']));
        return $match->fresh();
    }

    public function startInnings(int $matchId, int $battingTeamId, int $bowlingTeamId, int $inningsNumber, ?int $target = null): Innings
    {
        return $this->scoring->createInnings([
            'match_id'        => $matchId,
            'batting_team_id' => $battingTeamId,
            'bowling_team_id' => $bowlingTeamId,
            'innings_number'  => $inningsNumber,
            'target'          => $target,
        ]);
    }

    public function recordDelivery(int $inningsId, array $deliveryData): array
    {
        $innings = Innings::findOrFail($inningsId);
        $match   = $innings->match;

        $lastBall     = $this->scoring->getLastBall($inningsId);
        $overNumber   = $lastBall ? $lastBall->over_number   : 1;
        $ballInOver   = $lastBall ? $lastBall->ball_number   : 0;
        $isLegal      = !($deliveryData['is_wide'] ?? false) && !($deliveryData['is_no_ball'] ?? false);

        if ($isLegal) {
            $ballInOver++;
            if ($ballInOver > 6) {
                $overNumber++;
                $ballInOver = 1;
            }
        }

        $deliveryData = array_merge($deliveryData, [
            'innings_id'               => $inningsId,
            'match_id'                 => $match->id,
            'over_number'              => $overNumber,
            'ball_number'              => $ballInOver,
            'batting_team_score_after' => $innings->total_runs + ($deliveryData['runs_scored'] ?? 0) + ($deliveryData['extra_runs'] ?? 0),
            'batting_team_wickets_after' => $innings->total_wickets + (($deliveryData['is_wicket'] ?? false) ? 1 : 0),
        ]);

        $ball = $this->scoring->recordBall($deliveryData);

        // Invalidate live cache
        Cache::forget("live_match_{$match->id}");

        // Check innings end conditions
        $innings->refresh();
        $isOver = ($innings->total_wickets >= 10)
               || ($innings->total_balls >= ($match->overs_per_side * 6))
               || ($innings->innings_number > 1 && $innings->total_runs >= ($innings->target ?? PHP_INT_MAX));

        if ($isOver) {
            $this->scoring->updateInnings($inningsId, ['is_completed' => true]);
        }

        return [
            'ball'     => $ball,
            'innings'  => $innings->fresh(),
            'is_over'  => $isOver,
            'rrr'      => $this->requiredRunRate($innings->fresh()),
        ];
    }

    public function completeMatch(int $matchId): CricketMatch
    {
        $match   = CricketMatch::with('innings')->findOrFail($matchId);
        $innings = $match->innings->sortBy('innings_number');

        $inn1 = $innings->first();
        $inn2 = $innings->last();

        $winner   = null;
        $resultType   = 'no_result';
        $resultMargin = 0;

        if ($inn1 && $inn2) {
            if ($inn2->total_runs > $inn1->total_runs) {
                $winner       = $inn2->batting_team_id;
                $resultType   = 'wickets';
                $resultMargin = 10 - $inn2->total_wickets;
            } elseif ($inn1->total_runs > $inn2->total_runs) {
                $winner       = $inn1->batting_team_id;
                $resultType   = 'runs';
                $resultMargin = $inn1->total_runs - $inn2->total_runs;
            } else {
                $resultType = 'tie';
            }
        }

        $match->update([
            'status'       => 'completed',
            'winner_id'    => $winner,
            'result_type'  => $resultType,
            'result_margin'=> $resultMargin,
        ]);

        return $match->fresh();
    }

    public function getLiveState(int $matchId): array
    {
        return Cache::remember("live_match_{$matchId}", 30, fn() =>
            $this->scoring->getLiveMatchState($matchId)
        );
    }

    private function requiredRunRate(?Innings $innings): float
    {
        if (!$innings || $innings->innings_number < 2 || !$innings->target) return 0.00;

        $match          = $innings->match;
        $totalBalls     = $match->overs_per_side * 6;
        $ballsRemaining = $totalBalls - $innings->total_balls;

        return $this->calculator->requiredRunRate(
            $innings->target,
            $innings->total_runs,
            $ballsRemaining
        );
    }
}
