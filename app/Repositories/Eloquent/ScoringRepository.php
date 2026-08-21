<?php

namespace App\Repositories\Eloquent;

use App\Models\BallByBallLog;
use App\Models\Innings;
use App\Repositories\Interfaces\ScoringRepositoryInterface;
use App\Services\MatchStatisticsService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Persistence for live scoring.
 *
 * Writes are deliberately thin: a ball goes in, and every aggregate is then
 * rebuilt from the log by MatchStatisticsService. Incrementally patching
 * scorecards was the old approach and it could not survive an undo or a
 * duplicate delivery from a retrying phone.
 */
class ScoringRepository implements ScoringRepositoryInterface
{
    public function __construct(private readonly MatchStatisticsService $stats) {}

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
            // A phone that retries after a dropped connection sends the same
            // client_uuid; return the original row instead of a second ball.
            if (! empty($data['client_uuid'])) {
                $existing = BallByBallLog::where('client_uuid', $data['client_uuid'])->first();
                if ($existing) {
                    return $existing;
                }
            }

            $ball = BallByBallLog::create($data);

            $this->stats->rebuildInnings(Innings::findOrFail($data['innings_id']));

            return $ball->fresh();
        });
    }

    public function getLastBall(int $inningsId): ?BallByBallLog
    {
        return BallByBallLog::where('innings_id', $inningsId)
            ->orderByDesc('id')
            ->first();
    }

    public function getBallsByOver(int $inningsId, int $over): Collection
    {
        return BallByBallLog::where('innings_id', $inningsId)
            ->where('over_number', $over)
            ->orderBy('id')
            ->get();
    }

    public function getLiveMatchState(int $matchId): array
    {
        $innings = Innings::where('match_id', $matchId)
            ->with(['battingTeam', 'bowlingTeam', 'battingScorecards.player', 'bowlingScorecards.player'])
            ->orderByDesc('innings_number')
            ->first();

        if (! $innings) {
            return [];
        }

        $lastBall = $this->getLastBall($innings->id);

        return [
            'innings'            => $innings,
            'last_ball'          => $lastBall,
            'current_over_balls' => $lastBall
                ? $this->getBallsByOver($innings->id, (int) $lastBall->over_number)
                : collect(),
            'batsmen'            => $innings->battingScorecards
                ->whereIn('dismissal_type', ['not_out', 'retired_hurt'])
                ->where('balls_faced', '>', 0)
                ->values(),
            'current_bowler'     => $lastBall
                ? $innings->bowlingScorecards->firstWhere('player_id', $lastBall->bowler_id)
                : null,
        ];
    }

    public function undoLastBall(int $inningsId): bool
    {
        return DB::transaction(function () use ($inningsId) {
            $last = $this->getLastBall($inningsId);
            if (! $last) {
                return false;
            }

            $last->delete();
            $this->stats->rebuildInnings(Innings::findOrFail($inningsId));

            return true;
        });
    }
}
