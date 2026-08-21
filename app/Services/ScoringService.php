<?php

namespace App\Services;

use App\Models\BallByBallLog;
use App\Models\CricketMatch;
use App\Models\Innings;
use App\Models\MatchSquad;
use App\Repositories\Interfaces\ScoringRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Orchestrates a live match: toss, squads, innings, deliveries, finalisation.
 *
 * The React Native console owns the *state machine* (strike rotation, over
 * completion, undo stack). This service owns *persistence and truth*: it
 * accepts what the phone reports, stores it idempotently, and recomputes every
 * derived number so the web site and the API agree with the phone.
 */
class ScoringService
{
    public function __construct(
        private readonly ScoringRepositoryInterface $scoring,
        private readonly CalculationEngineService   $calculator,
        private readonly MatchStatisticsService     $stats,
    ) {}

    /* ── Setup ─────────────────────────────────────────────────────── */

    public function recordToss(CricketMatch $match, int $tossWinnerId, string $decision): CricketMatch
    {
        $match->update([
            'toss_winner_id' => $tossWinnerId,
            'toss_decision'  => $decision,
            'status'         => $match->status === 'upcoming' ? 'live' : $match->status,
        ]);

        Cache::forget("live_match_{$match->id}");

        return $match->fresh(['homeTeam', 'awayTeam', 'tossWinner']);
    }

    /**
     * Replace the named XI for one side.
     *
     * @param  array<int, array{player_id:int, batting_order?:int, is_captain?:bool, is_wicket_keeper?:bool}>  $players
     */
    public function saveSquad(CricketMatch $match, int $teamId, array $players): int
    {
        return DB::transaction(function () use ($match, $teamId, $players) {
            MatchSquad::where('match_id', $match->id)->where('team_id', $teamId)->delete();

            foreach (array_values($players) as $index => $p) {
                MatchSquad::create([
                    'match_id'         => $match->id,
                    'team_id'          => $teamId,
                    'player_id'        => (int) $p['player_id'],
                    'batting_order'    => (int) ($p['batting_order'] ?? $index + 1),
                    'is_captain'       => (bool) ($p['is_captain'] ?? false),
                    'is_wicket_keeper' => (bool) ($p['is_wicket_keeper'] ?? false),
                ]);
            }

            return count($players);
        });
    }

    /* ── Innings ───────────────────────────────────────────────────── */

    public function startInnings(
        int $matchId,
        int $battingTeamId,
        int $bowlingTeamId,
        int $inningsNumber,
        ?int $target = null
    ): Innings {
        $existing = Innings::where('match_id', $matchId)
            ->where('innings_number', $inningsNumber)
            ->first();

        if ($existing) {
            // Re-entering a console that was already open must not create a duplicate.
            $existing->update([
                'batting_team_id' => $battingTeamId,
                'bowling_team_id' => $bowlingTeamId,
                'target'          => $target ?? $existing->target,
            ]);

            return $existing->fresh();
        }

        CricketMatch::where('id', $matchId)
            ->where('status', 'upcoming')
            ->update(['status' => 'live']);

        return $this->scoring->createInnings([
            'match_id'        => $matchId,
            'batting_team_id' => $battingTeamId,
            'bowling_team_id' => $bowlingTeamId,
            'innings_number'  => $inningsNumber,
            'target'          => $target,
        ]);
    }

    /* ── Deliveries ────────────────────────────────────────────────── */

    /**
     * Persist one delivery and return the refreshed innings state.
     *
     * @param  array<string, mixed>  $delivery
     * @return array{ball: BallByBallLog, innings: Innings, is_innings_over: bool, required_run_rate: float, current_run_rate: float}
     */
    public function recordDelivery(int $inningsId, array $delivery): array
    {
        $innings = Innings::with('match')->findOrFail($inningsId);
        $match   = $innings->match;

        $payload = $this->normaliseDelivery($innings, $delivery);
        $ball    = $this->scoring->recordBall($payload);

        $innings = $innings->fresh();
        $over    = $this->isInningsOver($match, $innings);

        if ($over && ! $innings->is_completed) {
            $innings->update(['is_completed' => true]);
            $innings = $innings->fresh();
        }

        Cache::forget("live_match_{$match->id}");

        return [
            'ball'              => $ball,
            'innings'           => $innings,
            'is_innings_over'   => $over,
            'required_run_rate' => $this->requiredRunRate($innings),
            'current_run_rate'  => (float) $innings->run_rate,
        ];
    }

    /**
     * Bulk upload from the phone's offline queue. Already-seen `client_uuid`s
     * are skipped, so replaying the whole queue is always safe.
     *
     * @param  array<int, array<string, mixed>>  $deliveries
     * @return array{accepted:int, duplicates:int, innings: Innings, is_innings_over: bool}
     */
    public function syncDeliveries(int $inningsId, array $deliveries): array
    {
        $innings = Innings::with('match')->findOrFail($inningsId);
        $match   = $innings->match;

        $accepted = $duplicates = 0;

        DB::transaction(function () use ($innings, $deliveries, &$accepted, &$duplicates) {
            foreach ($deliveries as $delivery) {
                $uuid = $delivery['client_uuid'] ?? null;

                if ($uuid && BallByBallLog::where('client_uuid', $uuid)->exists()) {
                    $duplicates++;
                    continue;
                }

                BallByBallLog::create($this->normaliseDelivery($innings, $delivery));
                $accepted++;
            }
        });

        $this->stats->rebuildInnings($innings);

        $innings = $innings->fresh();
        $over    = $this->isInningsOver($match, $innings);

        if ($over && ! $innings->is_completed) {
            $innings->update(['is_completed' => true]);
            $innings = $innings->fresh();
        }

        Cache::forget("live_match_{$match->id}");

        return [
            'accepted'        => $accepted,
            'duplicates'      => $duplicates,
            'innings'         => $innings,
            'is_innings_over' => $over,
        ];
    }

    public function undoLastBall(int $inningsId): bool
    {
        $innings = Innings::findOrFail($inningsId);
        $undone  = $this->scoring->undoLastBall($inningsId);

        if ($undone) {
            // Undoing can reopen an innings that had just been closed out.
            $innings->fresh()->update(['is_completed' => false]);
            Cache::forget("live_match_{$innings->match_id}");
        }

        return $undone;
    }

    /**
     * Map a client delivery onto the ball_by_ball_logs columns, filling in
     * over/ball numbering when the client did not supply it.
     *
     * @return array<string, mixed>
     */
    private function normaliseDelivery(Innings $innings, array $d): array
    {
        $isWide    = (bool) ($d['is_wide'] ?? false);
        $isNoBall  = (bool) ($d['is_no_ball'] ?? false);
        $isPenalty = (bool) ($d['is_penalty'] ?? false);

        // None of these advance the over: wides and no-balls must be re-bowled,
        // and a penalty award is not a delivery at all.
        [$overNumber, $ballNumber] = $this->resolveBallPosition(
            $innings, $d, $isWide || $isNoBall || $isPenalty
        );

        $runs   = (int) ($d['runs_scored'] ?? 0);
        $extras = (int) ($d['extra_runs'] ?? 0);

        $isWicket = (bool) ($d['is_wicket'] ?? false);

        return [
            'client_uuid'    => $d['client_uuid'] ?? null,
            'innings_id'     => $innings->id,
            'match_id'       => $innings->match_id,
            'bowler_id'      => (int) $d['bowler_id'],
            'batsman_id'     => (int) $d['batsman_id'],
            'non_striker_id' => isset($d['non_striker_id']) ? (int) $d['non_striker_id'] : null,
            'over_number'    => $overNumber,
            'ball_number'    => $ballNumber,
            'runs_scored'    => $runs,
            'extra_runs'     => $extras,
            'is_wide'        => $isWide,
            'is_no_ball'     => $isNoBall,
            'is_bye'         => (bool) ($d['is_bye'] ?? false),
            'is_leg_bye'     => (bool) ($d['is_leg_bye'] ?? false),
            'is_penalty'     => $isPenalty,
            'is_four'        => (bool) ($d['is_four'] ?? ($runs === 4 && ! ($d['is_bye'] ?? false) && ! ($d['is_leg_bye'] ?? false))),
            'is_six'         => (bool) ($d['is_six'] ?? ($runs === 6 && ! ($d['is_bye'] ?? false) && ! ($d['is_leg_bye'] ?? false))),
            'is_wicket'      => $isWicket,
            'wicket_type'    => $isWicket ? ($d['wicket_type'] ?? null) : null,
            'out_player_id'  => $isWicket ? (int) ($d['out_player_id'] ?? $d['batsman_id']) : null,
            'fielder_id'     => isset($d['fielder_id']) && $d['fielder_id'] ? (int) $d['fielder_id'] : null,
            'batting_team_score_after'   => (int) ($d['score_after'] ?? ((int) $innings->total_runs + $runs + $extras)),
            'batting_team_wickets_after' => (int) ($d['wickets_after'] ?? ((int) $innings->total_wickets + ($isWicket ? 1 : 0))),
            'commentary'     => $d['commentary'] ?? ($d['comment'] ?? null),
        ];
    }

    /** @return array{0:int, 1:int} */
    private function resolveBallPosition(Innings $innings, array $d, bool $isExtraDelivery): array
    {
        if (isset($d['over_number'], $d['ball_number'])) {
            return [max(1, (int) $d['over_number']), max(1, (int) $d['ball_number'])];
        }

        $last = $this->scoring->getLastBall($innings->id);

        $over = $last ? (int) $last->over_number : 1;
        $ball = $last ? (int) $last->ball_number : 0;

        if (! $isExtraDelivery) {
            $ball++;
            if ($ball > 6) {
                $over++;
                $ball = 1;
            }
        } elseif (! $last) {
            $ball = 1;
        }

        return [$over, max(1, $ball)];
    }

    /**
     * A super over is its own format: one over, and it ends on the second
     * wicket rather than the tenth.
     */
    private function isInningsOver(CricketMatch $match, Innings $innings): bool
    {
        $isSuperOver = (int) $innings->innings_number >= 3;

        $available = $isSuperOver
            ? 2
            : $this->wicketsAvailable($match, (int) $innings->batting_team_id);

        if ((int) $innings->total_wickets >= $available) {
            return true;
        }

        $quota = $isSuperOver ? 6 : (int) $match->overs_per_side * 6;

        if ((int) $innings->total_balls >= $quota) {
            return true;
        }

        return $innings->target !== null && (int) $innings->total_runs >= (int) $innings->target;
    }

    private function wicketsAvailable(CricketMatch $match, int $teamId): int
    {
        $squad = MatchSquad::where('match_id', $match->id)->where('team_id', $teamId)->count();

        return $squad > 1 ? $squad - 1 : 10;
    }

    /* ── Live read ─────────────────────────────────────────────────── */

    /**
     * Live snapshot for one match.
     *
     * Deliberately *not* cached: the value is a graph of Eloquent models, and
     * serialising models into a shared cache store is a well-known way to get
     * `__PHP_Incomplete_Class` back on read. The underlying queries are all
     * indexed and cheap, so there is nothing to gain by risking it.
     */
    public function getLiveState(int $matchId): array
    {
        return $this->scoring->getLiveMatchState($matchId);
    }

    public function requiredRunRate(?Innings $innings): float
    {
        if (! $innings || ! $innings->target) {
            return 0.00;
        }

        $match          = $innings->match ?? CricketMatch::find($innings->match_id);
        $ballsRemaining = ((int) $match->overs_per_side * 6) - (int) $innings->total_balls;

        return $this->calculator->requiredRunRate(
            (int) $innings->target,
            (int) $innings->total_runs,
            max(0, $ballsRemaining)
        );
    }

    /* ── Finalisation ──────────────────────────────────────────────── */

    /**
     * Seal a match: rebuild every scorecard, decide the result, award the
     * player of the match, then push fresh aggregates into the edition tables
     * so Orange Cap / Purple Cap / MVP / NRR all move immediately.
     *
     * @param  array<string, mixed>  $options
     */
    public function finalizeMatch(CricketMatch $match, array $options = []): array
    {
        return DB::transaction(function () use ($match, $options) {
            $this->stats->rebuildMatch($match);
            $match->refresh()->load(['innings', 'homeTeam', 'awayTeam']);

            $match->innings()->update(['is_completed' => true]);

            $result = $this->stats->resolveResult($match);

            $match->update([
                'status'             => 'completed',
                'winner_id'          => $result['winner_id'],
                'result_type'        => $result['result_type'],
                'result_margin'      => $result['result_margin'],
                'result_description' => $options['result_description'] ?? $result['result_description'],
                'notes'              => $options['notes'] ?? $match->notes,
                'finalized_at'       => now(),
                'finalized_by'       => $options['finalized_by'] ?? 'mobile-scorer',
            ]);

            $match->refresh();

            $motm = $options['man_of_match_player_id'] ?? $this->stats->pickManOfTheMatch($match);
            if ($motm) {
                $match->update(['man_of_match_player_id' => (string) $motm]);
                $match->refresh();
            }

            $playersTouched = $this->stats->syncEditionStatsForMatch($match);

            Cache::forget("live_match_{$match->id}");

            return [
                'match'            => $match->fresh(['homeTeam', 'awayTeam', 'winner', 'innings']),
                'result'           => $result,
                'man_of_match_id'  => $motm ? (int) $motm : null,
                'players_updated'  => $playersTouched,
            ];
        });
    }
}
