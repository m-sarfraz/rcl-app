<?php

namespace Database\Seeders;

use App\Models\CricketMatch;
use App\Models\Innings;
use App\Models\MatchSquad;
use App\Models\Player;
use App\Models\PlayerEditionTeam;
use App\Services\MatchStatisticsService;
use App\Services\ScoringService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Plays out the group stage ball by ball so the league has real statistics.
 *
 * Nothing here shortcuts the production path: every delivery goes through the
 * same `syncDeliveries` endpoint the phone uses, and every match is sealed with
 * the same `finalizeMatch` call. That means seeding is also an end-to-end
 * exercise of the scoring pipeline — if the maiden detection, the strike
 * rotation or the MVP maths were wrong, this seeder would show it.
 *
 * The simulation is seeded from the match id, so re-running produces identical
 * scorecards rather than a different league every time.
 */
class MatchStatisticsSeeder extends Seeder
{
    /** How many of the scheduled fixtures to play out. */
    private const MATCHES_TO_PLAY = 18;

    private const XI_SIZE = 11;

    /**
     * Outcome weights per legal delivery. Tuned for a village 10-over game:
     * high boundary count, wickets a little cheaper than the first-class norm.
     *
     * @var array<string, int>
     */
    private const OUTCOMES = [
        'dot'     => 26,
        'one'     => 27,
        'two'     => 11,
        'three'   => 3,
        'four'    => 12,
        'six'     => 7,
        'wicket'  => 6,
        'wide'    => 4,
        'no_ball' => 2,
        'bye'     => 1,
        'leg_bye' => 1,
    ];

    private const WICKET_TYPES = [
        'bowled'  => 30,
        'caught'  => 38,
        'lbw'     => 12,
        'run_out' => 12,
        'stumped' => 8,
    ];

    public function __construct(
        private readonly ScoringService $scoring,
        private readonly MatchStatisticsService $stats,
    ) {}

    public function run(): void
    {
        $matches = CricketMatch::with(['homeTeam', 'awayTeam'])
            ->where('status', 'upcoming')
            ->whereColumn('home_team_id', '!=', 'away_team_id')
            ->orderBy('scheduled_at')
            ->orderByRaw('CAST(match_number AS UNSIGNED)')
            ->limit(self::MATCHES_TO_PLAY)
            ->get();

        if ($matches->isEmpty()) {
            $this->command?->warn('No upcoming fixtures with two distinct teams — nothing to simulate.');

            return;
        }

        $played = 0;

        foreach ($matches as $match) {
            if ($this->simulate($match)) {
                $played++;
                $this->command?->line(sprintf(
                    '   %-3s %s vs %s — %s',
                    '#'.$match->match_number,
                    $match->homeTeam?->short_code,
                    $match->awayTeam?->short_code,
                    $match->fresh()->result_description ?? 'no result'
                ));
            }
        }

        $editionIds = $matches->pluck('edition_id')->unique();
        foreach ($editionIds as $editionId) {
            $this->stats->rebuildEdition((int) $editionId);
        }

        $this->command?->info("✅ {$played} match(es) simulated ball-by-ball and finalised.");
    }

    private function simulate(CricketMatch $match): bool
    {
        $homeXI = $this->pickXI($match, (int) $match->home_team_id);
        $awayXI = $this->pickXI($match, (int) $match->away_team_id);

        if (count($homeXI) < self::XI_SIZE || count($awayXI) < self::XI_SIZE) {
            $this->command?->warn(
                "   Skipping match #{$match->match_number}: not enough eligible players."
            );

            return false;
        }

        // Deterministic per fixture.
        mt_srand(700_000 + $match->id);

        $this->scoring->saveSquad($match, (int) $match->home_team_id, $this->squadPayload($homeXI));
        $this->scoring->saveSquad($match, (int) $match->away_team_id, $this->squadPayload($awayXI));

        $tossWinnerId = mt_rand(0, 1) === 0 ? (int) $match->home_team_id : (int) $match->away_team_id;
        $decision     = mt_rand(0, 2) === 0 ? 'field' : 'bat';
        $this->scoring->recordToss($match, $tossWinnerId, $decision);

        $tossLoserId = $tossWinnerId === (int) $match->home_team_id
            ? (int) $match->away_team_id
            : (int) $match->home_team_id;

        $battingFirstId = $decision === 'bat' ? $tossWinnerId : $tossLoserId;
        $bowlingFirstId = $battingFirstId === (int) $match->home_team_id
            ? (int) $match->away_team_id
            : (int) $match->home_team_id;

        $xiFor = [
            (int) $match->home_team_id => $homeXI,
            (int) $match->away_team_id => $awayXI,
        ];

        // ── First innings ────────────────────────────────────────────
        $inn1 = $this->scoring->startInnings(
            $match->id, $battingFirstId, $bowlingFirstId, 1
        );
        $this->playInnings($match, $inn1, $xiFor[$battingFirstId], $xiFor[$bowlingFirstId], null);
        $inn1->refresh();

        // ── Second innings, chasing ──────────────────────────────────
        $inn2 = $this->scoring->startInnings(
            $match->id, $bowlingFirstId, $battingFirstId, 2, (int) $inn1->total_runs + 1
        );
        $this->playInnings($match, $inn2, $xiFor[$bowlingFirstId], $xiFor[$battingFirstId], (int) $inn1->total_runs + 1);

        $this->scoring->finalizeMatch($match, ['finalized_by' => 'seeder']);

        return true;
    }

    /**
     * Bowl out one innings.
     *
     * Mirrors the client engine: strike rotates on odd runs and at the end of
     * each over, a bowler may not bowl consecutive overs, and the innings ends
     * on the quota, the last wicket, or the target being passed.
     *
     * @param  array<int, Player>  $battingXI
     * @param  array<int, Player>  $bowlingXI
     */
    private function playInnings(
        CricketMatch $match,
        Innings $innings,
        array $battingXI,
        array $bowlingXI,
        ?int $target
    ): void {
        $maxOvers  = (int) $match->overs_per_side;
        $bowlers   = $this->pickBowlers($bowlingXI, $maxOvers);
        $fielders  = $bowlingXI;

        $strikerIdx    = 0;
        $nonStrikerIdx = 1;
        $nextBatIdx    = 2;
        $wickets       = 0;
        $runs          = 0;
        $queued        = [];
        $lastBowlerId  = null;

        for ($over = 1; $over <= $maxOvers; $over++) {
            $bowler = $this->nextBowler($bowlers, $lastBowlerId, $over);
            $lastBowlerId = $bowler->id;

            $legalBalls = 0;

            while ($legalBalls < 6) {
                if ($wickets >= self::XI_SIZE - 1) {
                    break 2;
                }
                if ($target !== null && $runs >= $target) {
                    break 2;
                }

                $outcome = $this->weighted(self::OUTCOMES);

                $ball = [
                    'client_uuid'    => (string) Str::uuid(),
                    'innings_id'     => $innings->id,
                    'match_id'       => $match->id,
                    'batsman_id'     => $battingXI[$strikerIdx]->id,
                    'non_striker_id' => $battingXI[$nonStrikerIdx]->id ?? null,
                    'bowler_id'      => $bowler->id,
                    'over_number'    => $over,
                    'ball_number'    => min(6, $legalBalls + 1),
                    'runs_scored'    => 0,
                    'extra_runs'     => 0,
                    'is_wide'        => false,
                    'is_no_ball'     => false,
                    'is_bye'         => false,
                    'is_leg_bye'     => false,
                    'is_penalty'     => false,
                    'is_four'        => false,
                    'is_six'         => false,
                    'is_wicket'      => false,
                    'wicket_type'    => null,
                    'out_player_id'  => null,
                    'fielder_id'     => null,
                ];

                $rotate = false;

                switch ($outcome) {
                    case 'dot':
                        $legalBalls++;
                        break;

                    case 'one':
                        $ball['runs_scored'] = 1; $runs += 1; $legalBalls++; $rotate = true;
                        break;

                    case 'two':
                        $ball['runs_scored'] = 2; $runs += 2; $legalBalls++;
                        break;

                    case 'three':
                        $ball['runs_scored'] = 3; $runs += 3; $legalBalls++; $rotate = true;
                        break;

                    case 'four':
                        $ball['runs_scored'] = 4; $ball['is_four'] = true; $runs += 4; $legalBalls++;
                        break;

                    case 'six':
                        $ball['runs_scored'] = 6; $ball['is_six'] = true; $runs += 6; $legalBalls++;
                        break;

                    case 'wide':
                        $ball['is_wide'] = true; $ball['extra_runs'] = 1; $runs += 1;
                        break;

                    case 'no_ball':
                        $offBat = $this->weighted(['0' => 45, '1' => 25, '4' => 20, '6' => 10]);
                        $ball['is_no_ball'] = true;
                        $ball['extra_runs'] = 1;
                        $ball['runs_scored'] = (int) $offBat;
                        $ball['is_four'] = $offBat === '4';
                        $ball['is_six']  = $offBat === '6';
                        $runs += 1 + (int) $offBat;
                        $rotate = (int) $offBat === 1;
                        break;

                    case 'bye':
                    case 'leg_bye':
                        $extra = mt_rand(1, 2);
                        $ball[$outcome === 'bye' ? 'is_bye' : 'is_leg_bye'] = true;
                        $ball['extra_runs'] = $extra;
                        $runs += $extra;
                        $legalBalls++;
                        $rotate = $extra % 2 === 1;
                        break;

                    case 'wicket':
                        $type = $this->weighted(self::WICKET_TYPES);
                        $legalBalls++;
                        $wickets++;

                        $ball['is_wicket']   = true;
                        $ball['wicket_type'] = $type;

                        // On a run-out it is often the non-striker who goes.
                        $outIdx = ($type === 'run_out' && mt_rand(0, 1) === 1)
                            ? $nonStrikerIdx
                            : $strikerIdx;

                        $ball['out_player_id'] = $battingXI[$outIdx]->id;

                        if (in_array($type, ['caught', 'stumped', 'run_out'], true)) {
                            $ball['fielder_id'] = $fielders[array_rand($fielders)]->id;
                        }

                        $queued[] = $ball;

                        // The incoming batter replaces whoever was dismissed.
                        if ($nextBatIdx < count($battingXI)) {
                            if ($outIdx === $strikerIdx) {
                                $strikerIdx = $nextBatIdx;
                            } else {
                                $nonStrikerIdx = $nextBatIdx;
                            }
                            $nextBatIdx++;
                        }

                        continue 2;
                }

                $queued[] = $ball;

                if ($rotate) {
                    [$strikerIdx, $nonStrikerIdx] = [$nonStrikerIdx, $strikerIdx];
                }
            }

            // Ends swap at the end of every over.
            [$strikerIdx, $nonStrikerIdx] = [$nonStrikerIdx, $strikerIdx];
        }

        if ($queued) {
            // Chunked so a long innings never builds an oversized insert.
            foreach (array_chunk($queued, 120) as $chunk) {
                $this->scoring->syncDeliveries($innings->id, $chunk);
            }
        }

        $innings->refresh()->update(['is_completed' => true]);
    }

    /* ── Selection helpers ─────────────────────────────────────────── */

    /** @return array<int, Player> */
    private function pickXI(CricketMatch $match, int $teamId): array
    {
        $roster = PlayerEditionTeam::where('team_id', $teamId)
            ->where('edition_id', $match->edition_id)
            ->orderBy('id')
            ->with(['player.fines', 'player.suspensions'])
            ->get()
            ->pluck('player')
            ->filter();

        $eligible = $roster->filter(fn (Player $p) => $p->ineligibilityReason() === null)->values();

        // Keepers and bowlers first so every XI can actually take the field.
        $keeper  = $eligible->firstWhere('role', 'wicket_keeper');
        $bowlers = $eligible->whereIn('role', ['bowler', 'all_rounder'])->take(5);
        $rest    = $eligible->reject(
            fn ($p) => $p->is($keeper) || $bowlers->contains(fn ($b) => $b->is($p))
        );

        return collect([$keeper])
            ->filter()
            ->merge($rest)
            ->merge($bowlers)
            ->unique('id')
            ->take(self::XI_SIZE)
            ->values()
            ->all();
    }

    /** @param array<int, Player> $xi */
    private function squadPayload(array $xi): array
    {
        return collect($xi)->map(fn (Player $p, int $i) => [
            'player_id'        => $p->id,
            'batting_order'    => $i + 1,
            'is_captain'       => $i === 0,
            'is_wicket_keeper' => $p->role === 'wicket_keeper',
        ])->all();
    }

    /**
     * Five bowlers sharing the quota, preferring specialists.
     *
     * @param  array<int, Player>  $bowlingXI
     * @return array<int, Player>
     */
    private function pickBowlers(array $bowlingXI, int $overs): array
    {
        $specialists = collect($bowlingXI)
            ->filter(fn (Player $p) => in_array($p->role, ['bowler', 'all_rounder'], true))
            ->filter(fn (Player $p) => $p->bowling_action_status !== 'banned')
            ->values();

        if ($specialists->count() < 3) {
            $specialists = collect($bowlingXI)
                ->filter(fn (Player $p) => $p->bowling_action_status !== 'banned')
                ->values();
        }

        return $specialists->take(max(3, (int) ceil($overs / 2)))->all();
    }

    /** @param array<int, Player> $bowlers */
    private function nextBowler(array $bowlers, ?int $lastBowlerId, int $over): Player
    {
        $count = count($bowlers);
        $index = ($over - 1) % $count;

        // No bowler bowls consecutive overs.
        if ($count > 1 && $bowlers[$index]->id === $lastBowlerId) {
            $index = ($index + 1) % $count;
        }

        return $bowlers[$index];
    }

    /** @param array<string, int> $weights */
    private function weighted(array $weights): string
    {
        $total = array_sum($weights);
        $roll  = mt_rand(1, $total);

        foreach ($weights as $key => $weight) {
            $roll -= $weight;
            if ($roll <= 0) {
                return (string) $key;
            }
        }

        return (string) array_key_first($weights);
    }
}
