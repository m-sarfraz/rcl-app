<?php

namespace App\Services;

use App\Models\BallByBallLog;
use App\Models\BattingScorecard;
use App\Models\BowlingScorecard;
use App\Models\CricketMatch;
use App\Models\FieldingScorecard;
use App\Models\Innings;
use App\Models\MatchSquad;
use App\Models\PlayerEditionStat;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Everything derived from the ball log, rebuilt rather than incremented.
 *
 * The old path mutated scorecards with `increment()` on each delivery, which
 * meant an undo left stale totals behind and a re-sent ball double-counted.
 * Here the ball log is the single source of truth and every aggregate — innings
 * totals, scorecards, fielding, result, edition stats — is recomputed from it.
 * That makes every operation idempotent, which is exactly what an offline-first
 * mobile scorer needs.
 */
class MatchStatisticsService
{
    /* ── MVP scoring weights (VCC house formula) ───────────────────────── */
    private const MVP_PER_RUN            = 1.0;
    private const MVP_PER_FOUR           = 1.0;
    private const MVP_PER_SIX            = 2.0;
    private const MVP_FIFTY_BONUS        = 20.0;
    private const MVP_CENTURY_BONUS      = 50.0;
    private const MVP_PER_WICKET         = 20.0;
    private const MVP_PER_MAIDEN         = 10.0;
    private const MVP_FIVE_WICKET_BONUS  = 30.0;
    private const MVP_HAT_TRICK_BONUS    = 25.0;
    private const MVP_PER_CATCH          = 10.0;
    private const MVP_PER_STUMPING       = 10.0;
    private const MVP_PER_RUN_OUT        = 8.0;
    private const MVP_MOTM_BONUS         = 25.0;

    public function __construct(private readonly CalculationEngineService $calculator) {}

    /* ══════════════════════════════════════════════════════════════════
     |  Innings
     ══════════════════════════════════════════════════════════════════ */

    /**
     * Recompute one innings — totals, extras, both scorecards — from its balls.
     */
    public function rebuildInnings(Innings $innings): Innings
    {
        return DB::transaction(function () use ($innings) {
            $balls = BallByBallLog::where('innings_id', $innings->id)
                ->orderBy('id')
                ->get();

            $this->writeRunningTotals($balls);
            $this->writeInningsTotals($innings, $balls);
            $this->writeBattingScorecards($innings, $balls);
            $this->writeBowlingScorecards($innings, $balls);

            Cache::forget("live_match_{$innings->match_id}");

            return $innings->fresh();
        });
    }

    /**
     * Stamp the running score onto each delivery.
     *
     * These two columns are a cache of "what the scoreboard read after this
     * ball", used by the commentary feed and the last-wicket line. Treating
     * them as derived rather than trusting whatever the client sent means a
     * bulk sync, an undo and an edit all leave them correct — a client posting
     * a queue of thirty balls has no reliable running total of its own.
     */
    private function writeRunningTotals(Collection $balls): void
    {
        $runs = 0;
        $wickets = 0;

        foreach ($balls as $ball) {
            $runs += (int) $ball->runs_scored + (int) $ball->extra_runs;

            if ($ball->is_wicket && $ball->wicket_type !== 'retired_hurt') {
                $wickets++;
            }

            if ((int) $ball->batting_team_score_after !== $runs
                || (int) $ball->batting_team_wickets_after !== $wickets) {
                $ball->forceFill([
                    'batting_team_score_after'   => $runs,
                    'batting_team_wickets_after' => $wickets,
                ])->saveQuietly();
            }
        }
    }

    private function writeInningsTotals(Innings $innings, Collection $balls): void
    {
        // Wides and no-balls must be re-bowled; a penalty is not a delivery at
        // all. None of the three advance the over.
        $legalBalls = $balls->reject(fn ($b) => $b->is_wide || $b->is_no_ball || $b->is_penalty)->count();
        $totalRuns  = (int) $balls->sum(fn ($b) => (int) $b->runs_scored + (int) $b->extra_runs);
        $wickets    = $balls->filter(fn ($b) => $b->is_wicket && $b->wicket_type !== 'retired_hurt')->count();

        $innings->forceFill([
            'total_runs'      => $totalRuns,
            'total_wickets'   => $wickets,
            'total_balls'     => $legalBalls,
            'overs_faced'     => $this->calculator->ballsToDecimalOvers($legalBalls),
            'run_rate'        => $this->calculator->calculateRunRate($totalRuns, $legalBalls),
            'extras_wides'    => (int) $balls->where('is_wide', true)->sum('extra_runs'),
            'extras_no_balls' => (int) $balls->where('is_no_ball', true)->sum('extra_runs'),
            'extras_byes'     => (int) $balls->where('is_bye', true)->sum('extra_runs'),
            'extras_leg_byes' => (int) $balls->where('is_leg_bye', true)->sum('extra_runs'),
            'extras_penalty'  => (int) $balls->where('is_penalty', true)->sum('extra_runs'),
        ])->save();
    }

    private function writeBattingScorecards(Innings $innings, Collection $balls): void
    {
        /** @var array<int, array<string, mixed>> $cards keyed by player id */
        $cards    = [];
        $position = 0;

        $ensure = function (?int $playerId) use (&$cards, &$position) {
            if (! $playerId || isset($cards[$playerId])) {
                return;
            }
            $cards[$playerId] = [
                'batting_position' => ++$position,
                'runs_scored'      => 0,
                'balls_faced'      => 0,
                'fours'            => 0,
                'sixes'            => 0,
                'dismissal_type'   => 'not_out',
                'bowled_by_id'     => null,
                'caught_by_id'     => null,
                'six_streak'       => 0,
                'hat_trick_sixes'  => false,
                'over_sixes'       => [],
            ];
        };

        foreach ($balls as $ball) {
            $ensure($ball->batsman_id);
            $ensure($ball->non_striker_id);

            $striker = $ball->batsman_id;

            if ($striker && isset($cards[$striker])) {
                // A wide is not a ball faced; a no-ball is. A penalty is not a
                // delivery, so it is faced by nobody.
                if (! $ball->is_wide && ! $ball->is_penalty) {
                    $cards[$striker]['balls_faced']++;
                }

                // Byes and leg-byes are extras, never credited to the bat.
                $offBat = ($ball->is_bye || $ball->is_leg_bye) ? 0 : (int) $ball->runs_scored;
                $cards[$striker]['runs_scored'] += $offBat;

                if ($ball->is_four) {
                    $cards[$striker]['fours']++;
                }
                if ($ball->is_six) {
                    $cards[$striker]['sixes']++;
                    $cards[$striker]['six_streak']++;
                    if ($cards[$striker]['six_streak'] >= 3) {
                        $cards[$striker]['hat_trick_sixes'] = true;
                    }
                    $key = $ball->over_number;
                    $cards[$striker]['over_sixes'][$key] = ($cards[$striker]['over_sixes'][$key] ?? 0) + 1;
                } elseif (! $ball->is_wide) {
                    // Wides do not break a batter's streak — they never faced one.
                    $cards[$striker]['six_streak'] = 0;
                }
            }

            if ($ball->is_wicket) {
                $out = $ball->out_player_id ?: $ball->batsman_id;
                $ensure($out);
                if (isset($cards[$out])) {
                    $cards[$out]['dismissal_type'] = $ball->wicket_type ?: 'bowled';
                    $cards[$out]['bowled_by_id']   = $this->creditsBowler($ball->wicket_type) ? $ball->bowler_id : null;
                    $cards[$out]['caught_by_id']   = $ball->fielder_id;
                }
            }
        }

        // Anyone named in the XI who never came to the crease.
        foreach ($this->squadPlayerIds($innings->match_id, $innings->batting_team_id) as $playerId) {
            if (! isset($cards[$playerId])) {
                $cards[$playerId] = [
                    'batting_position' => ++$position,
                    'runs_scored' => 0, 'balls_faced' => 0, 'fours' => 0, 'sixes' => 0,
                    'dismissal_type' => 'did_not_bat', 'bowled_by_id' => null, 'caught_by_id' => null,
                    'six_streak' => 0, 'hat_trick_sixes' => false, 'over_sixes' => [],
                ];
            }
        }

        BattingScorecard::where('innings_id', $innings->id)
            ->whereNotIn('player_id', array_keys($cards) ?: [0])
            ->delete();

        foreach ($cards as $playerId => $c) {
            $sr = $c['balls_faced'] > 0
                ? round(($c['runs_scored'] / $c['balls_faced']) * 100, 2)
                : 0;

            BattingScorecard::updateOrCreate(
                ['innings_id' => $innings->id, 'player_id' => $playerId],
                [
                    'match_id'           => $innings->match_id,
                    'team_id'            => $innings->batting_team_id,
                    'batting_position'   => $c['batting_position'],
                    'runs_scored'        => $c['runs_scored'],
                    'balls_faced'        => $c['balls_faced'],
                    'fours'              => $c['fours'],
                    'sixes'              => $c['sixes'],
                    'strike_rate'        => $sr,
                    'dismissal_type'     => $c['dismissal_type'],
                    'bowled_by_id'       => $c['bowled_by_id'],
                    'caught_by_id'       => $c['caught_by_id'],
                    'is_fifty'           => $c['runs_scored'] >= 50 && $c['runs_scored'] < 100,
                    'is_century'         => $c['runs_scored'] >= 100,
                    'hat_trick_sixes'    => $c['hat_trick_sixes'],
                    'five_sixes_in_over' => collect($c['over_sixes'])->max() >= 5,
                ]
            );
        }
    }

    private function writeBowlingScorecards(Innings $innings, Collection $balls): void
    {
        $cards = [];

        foreach ($balls->groupBy('bowler_id') as $bowlerId => $bowlerBalls) {
            if (! $bowlerId) {
                continue;
            }

            $legal    = $bowlerBalls->reject(fn ($b) => $b->is_wide || $b->is_no_ball || $b->is_penalty)->count();
            $conceded = (int) $bowlerBalls->sum(function ($b) {
                // Byes, leg-byes and umpire penalties are not the bowler's runs.
                $extras = ($b->is_bye || $b->is_leg_bye || $b->is_penalty) ? 0 : (int) $b->extra_runs;

                return (int) $b->runs_scored + $extras;
            });
            $wickets = $bowlerBalls->filter(fn ($b) => $b->is_wicket && $this->creditsBowler($b->wicket_type))->count();

            $cards[(int) $bowlerId] = [
                'balls'    => $legal,
                'conceded' => $conceded,
                'wickets'  => $wickets,
                'wides'    => $bowlerBalls->where('is_wide', true)->count(),
                'no_balls' => $bowlerBalls->where('is_no_ball', true)->count(),
                'maidens'  => $this->countMaidens($bowlerBalls),
                'hat_trick'=> $this->hasWicketHatTrick($bowlerBalls),
            ];
        }

        BowlingScorecard::where('innings_id', $innings->id)
            ->whereNotIn('player_id', array_keys($cards) ?: [0])
            ->delete();

        foreach ($cards as $playerId => $c) {
            BowlingScorecard::updateOrCreate(
                ['innings_id' => $innings->id, 'player_id' => $playerId],
                [
                    'match_id'           => $innings->match_id,
                    'team_id'            => $innings->bowling_team_id,
                    'overs_bowled_balls' => $c['balls'],
                    'overs_bowled'       => $this->calculator->ballsToDecimalOvers($c['balls']),
                    'maidens'            => $c['maidens'],
                    'runs_conceded'      => $c['conceded'],
                    'wickets'            => $c['wickets'],
                    'wides'              => $c['wides'],
                    'no_balls'           => $c['no_balls'],
                    'economy'            => $this->calculator->calculateRunRate($c['conceded'], $c['balls']),
                    'hat_trick_wickets'  => $c['hat_trick'],
                    'five_wicket_haul'   => $c['wickets'] >= 5,
                ]
            );
        }
    }

    /**
     * A maiden is a completed over with no runs off the bat and no wides or
     * no-balls. Byes and leg-byes are not charged to the bowler, so they do not
     * spoil it — which is the standard reading of Law 17.
     */
    private function countMaidens(Collection $bowlerBalls): int
    {
        return $bowlerBalls->reject(fn ($b) => $b->is_penalty)
            ->groupBy('over_number')
            ->filter(function (Collection $over) {
                $legal = $over->reject(fn ($b) => $b->is_wide || $b->is_no_ball)->count();
                if ($legal < 6) {
                    return false;
                }
                if ($over->contains(fn ($b) => $b->is_wide || $b->is_no_ball)) {
                    return false;
                }

                return (int) $over->sum('runs_scored') === 0;
            })
            ->count();
    }

    /** Three wickets off three consecutive deliveries by the same bowler. */
    private function hasWicketHatTrick(Collection $bowlerBalls): bool
    {
        $streak = 0;

        foreach ($bowlerBalls->sortBy('id') as $ball) {
            if ($ball->is_wicket && $this->creditsBowler($ball->wicket_type)) {
                if (++$streak >= 3) {
                    return true;
                }
            } elseif (! $ball->is_wide) {
                $streak = 0;
            }
        }

        return false;
    }

    /** Run-outs, obstruction and retirements are not the bowler's wicket. */
    private function creditsBowler(?string $wicketType): bool
    {
        return $wicketType !== null
            && ! in_array($wicketType, ['run_out', 'obstructing_field', 'retired_hurt', 'handled_ball'], true);
    }

    /** @return array<int, int> */
    private function squadPlayerIds(int $matchId, int $teamId): array
    {
        return MatchSquad::where('match_id', $matchId)
            ->where('team_id', $teamId)
            ->orderBy('batting_order')
            ->pluck('player_id')
            ->all();
    }

    /* ══════════════════════════════════════════════════════════════════
     |  Match
     ══════════════════════════════════════════════════════════════════ */

    public function rebuildMatch(CricketMatch $match): CricketMatch
    {
        $match->load('innings');

        foreach ($match->innings as $innings) {
            $this->rebuildInnings($innings);
        }

        $this->writeFieldingScorecards($match);

        Cache::forget("live_match_{$match->id}");

        return $match->fresh(['innings']);
    }

    private function writeFieldingScorecards(CricketMatch $match): void
    {
        $balls = BallByBallLog::where('match_id', $match->id)
            ->where('is_wicket', true)
            ->whereNotNull('fielder_id')
            ->get();

        $tally = [];

        foreach ($balls as $ball) {
            $id = (int) $ball->fielder_id;
            $tally[$id] ??= ['catches' => 0, 'run_outs' => 0, 'stumpings' => 0, 'team_id' => null];

            match ($ball->wicket_type) {
                'caught'  => $tally[$id]['catches']++,
                'stumped' => $tally[$id]['stumpings']++,
                'run_out' => $tally[$id]['run_outs']++,
                default   => null,
            };

            $innings = $ball->relationLoaded('innings') ? $ball->innings : $ball->innings()->first();
            $tally[$id]['team_id'] ??= $innings?->bowling_team_id;
        }

        FieldingScorecard::where('match_id', $match->id)
            ->whereNotIn('player_id', array_keys($tally) ?: [0])
            ->delete();

        foreach ($tally as $playerId => $t) {
            if (! $t['team_id']) {
                continue;
            }
            FieldingScorecard::updateOrCreate(
                ['match_id' => $match->id, 'player_id' => $playerId],
                [
                    'team_id'   => $t['team_id'],
                    'catches'   => $t['catches'],
                    'run_outs'  => $t['run_outs'],
                    'stumpings' => $t['stumpings'],
                ]
            );
        }
    }

    /**
     * Decide the result from the innings totals.
     *
     * @return array{winner_id:?int, result_type:string, result_margin:int, result_description:string}
     */
    public function resolveResult(CricketMatch $match): array
    {
        $innings = $match->relationLoaded('innings')
            ? $match->innings->sortBy('innings_number')->values()
            : $match->innings()->orderBy('innings_number')->get();

        $regular = $innings->whereIn('innings_number', [1, 2])->values();
        $inn1    = $regular->firstWhere('innings_number', 1);
        $inn2    = $regular->firstWhere('innings_number', 2);

        if (! $inn1 || ! $inn2) {
            return [
                'winner_id'          => null,
                'result_type'        => 'no_result',
                'result_margin'      => 0,
                'result_description' => 'No result — the match did not reach a second innings.',
            ];
        }

        if ($inn2->total_runs > $inn1->total_runs) {
            $available = $this->wicketsAvailable($match, (int) $inn2->batting_team_id);
            $margin    = max(0, $available - (int) $inn2->total_wickets);
            $team      = $this->teamName($match, (int) $inn2->batting_team_id);

            return [
                'winner_id'          => (int) $inn2->batting_team_id,
                'result_type'        => 'wickets',
                'result_margin'      => $margin,
                'result_description' => "{$team} won by {$margin} wicket".($margin === 1 ? '' : 's').'.',
            ];
        }

        if ($inn1->total_runs > $inn2->total_runs) {
            $margin = (int) $inn1->total_runs - (int) $inn2->total_runs;
            $team   = $this->teamName($match, (int) $inn1->batting_team_id);

            return [
                'winner_id'          => (int) $inn1->batting_team_id,
                'result_type'        => 'runs',
                'result_margin'      => $margin,
                'result_description' => "{$team} won by {$margin} run".($margin === 1 ? '' : 's').'.',
            ];
        }

        // Scores level — a super over decides it if one was played.
        $so1 = $innings->firstWhere('innings_number', 3);
        $so2 = $innings->firstWhere('innings_number', 4);

        if ($so1 && $so2 && $so1->total_runs !== $so2->total_runs) {
            $winner = $so1->total_runs > $so2->total_runs ? $so1 : $so2;
            $team   = $this->teamName($match, (int) $winner->batting_team_id);

            return [
                'winner_id'          => (int) $winner->batting_team_id,
                'result_type'        => 'super_over',
                'result_margin'      => abs((int) $so1->total_runs - (int) $so2->total_runs),
                'result_description' => "Match tied — {$team} won the Super Over.",
            ];
        }

        return [
            'winner_id'          => null,
            'result_type'        => 'tie',
            'result_margin'      => 0,
            'result_description' => 'Match tied.',
        ];
    }

    private function wicketsAvailable(CricketMatch $match, int $teamId): int
    {
        $squad = MatchSquad::where('match_id', $match->id)->where('team_id', $teamId)->count();

        return $squad > 1 ? $squad - 1 : 10;
    }

    private function teamName(CricketMatch $match, int $teamId): string
    {
        if ($match->home_team_id === $teamId) {
            return $match->homeTeam?->name ?? 'Home';
        }
        if ($match->away_team_id === $teamId) {
            return $match->awayTeam?->name ?? 'Away';
        }

        return 'Team';
    }

    /* ══════════════════════════════════════════════════════════════════
     |  Player of the match & MVP points
     ══════════════════════════════════════════════════════════════════ */

    /** @return array<int, float> playerId ⇒ impact points for this match */
    public function matchImpactPoints(CricketMatch $match): array
    {
        $points = [];
        $add    = function (int $id, float $n) use (&$points) {
            $points[$id] = ($points[$id] ?? 0) + $n;
        };

        foreach (BattingScorecard::where('match_id', $match->id)->get() as $bat) {
            $add((int) $bat->player_id,
                $bat->runs_scored * self::MVP_PER_RUN
                + $bat->fours * self::MVP_PER_FOUR
                + $bat->sixes * self::MVP_PER_SIX
                + ($bat->is_century ? self::MVP_CENTURY_BONUS : ($bat->is_fifty ? self::MVP_FIFTY_BONUS : 0))
            );
        }

        foreach (BowlingScorecard::where('match_id', $match->id)->get() as $bowl) {
            $add((int) $bowl->player_id,
                $bowl->wickets * self::MVP_PER_WICKET
                + $bowl->maidens * self::MVP_PER_MAIDEN
                + ($bowl->five_wicket_haul ? self::MVP_FIVE_WICKET_BONUS : 0)
                + ($bowl->hat_trick_wickets ? self::MVP_HAT_TRICK_BONUS : 0)
            );
        }

        foreach (FieldingScorecard::where('match_id', $match->id)->get() as $field) {
            $add((int) $field->player_id,
                $field->catches * self::MVP_PER_CATCH
                + $field->stumpings * self::MVP_PER_STUMPING
                + $field->run_outs * self::MVP_PER_RUN_OUT
            );
        }

        arsort($points);

        return $points;
    }

    /** Highest impact score in the match, preferring a player from the winning side. */
    public function pickManOfTheMatch(CricketMatch $match): ?int
    {
        $points = $this->matchImpactPoints($match);
        if (! $points) {
            return null;
        }

        if ($match->winner_id) {
            $winnersXI = MatchSquad::where('match_id', $match->id)
                ->where('team_id', $match->winner_id)
                ->pluck('player_id')
                ->all();

            if ($winnersXI) {
                foreach ($points as $playerId => $_) {
                    if (in_array($playerId, $winnersXI, false)) {
                        return (int) $playerId;
                    }
                }
            }
        }

        return (int) array_key_first($points);
    }

    /* ══════════════════════════════════════════════════════════════════
     |  Edition aggregates
     ══════════════════════════════════════════════════════════════════ */

    /** Refresh the edition table for every player who appeared in this match. */
    public function syncEditionStatsForMatch(CricketMatch $match): int
    {
        $playerIds = collect()
            ->merge(BattingScorecard::where('match_id', $match->id)->pluck('player_id'))
            ->merge(BowlingScorecard::where('match_id', $match->id)->pluck('player_id'))
            ->merge(FieldingScorecard::where('match_id', $match->id)->pluck('player_id'))
            ->merge(MatchSquad::where('match_id', $match->id)->pluck('player_id'))
            ->unique()
            ->values();

        foreach ($playerIds as $playerId) {
            $this->syncPlayerEditionStat((int) $playerId, (int) $match->edition_id);
        }

        $this->flushEditionCaches((int) $match->edition_id);

        return $playerIds->count();
    }

    public function rebuildEdition(int $editionId): int
    {
        $playerIds = collect()
            ->merge(
                BattingScorecard::whereHas('innings.match', fn ($q) => $q->where('edition_id', $editionId))
                    ->pluck('player_id')
            )
            ->merge(
                BowlingScorecard::whereHas('innings.match', fn ($q) => $q->where('edition_id', $editionId))
                    ->pluck('player_id')
            )
            ->merge(
                MatchSquad::whereHas('match', fn ($q) => $q->where('edition_id', $editionId))
                    ->pluck('player_id')
            )
            ->unique()
            ->values();

        foreach ($playerIds as $playerId) {
            $this->syncPlayerEditionStat((int) $playerId, $editionId);
        }

        $this->flushEditionCaches($editionId);

        return $playerIds->count();
    }

    public function syncPlayerEditionStat(int $playerId, int $editionId): ?PlayerEditionStat
    {
        $completedMatchIds = CricketMatch::where('edition_id', $editionId)
            ->where('status', 'completed')
            ->pluck('id');

        if ($completedMatchIds->isEmpty()) {
            PlayerEditionStat::where('player_id', $playerId)->where('edition_id', $editionId)->delete();

            return null;
        }

        $batting = BattingScorecard::where('player_id', $playerId)
            ->whereIn('match_id', $completedMatchIds)->get();
        $bowling = BowlingScorecard::where('player_id', $playerId)
            ->whereIn('match_id', $completedMatchIds)->get();
        $fielding = FieldingScorecard::where('player_id', $playerId)
            ->whereIn('match_id', $completedMatchIds)->get();
        $appearances = MatchSquad::where('player_id', $playerId)
            ->whereIn('match_id', $completedMatchIds)->get();

        $teamId = $appearances->last()?->team_id
            ?? $batting->last()?->team_id
            ?? $bowling->last()?->team_id
            ?? \App\Models\PlayerEditionTeam::where('player_id', $playerId)
                ->where('edition_id', $editionId)->value('team_id');

        if (! $teamId) {
            return null;
        }

        $matchesPlayed = $appearances->pluck('match_id')
            ->merge($batting->pluck('match_id'))
            ->merge($bowling->pluck('match_id'))
            ->unique()->count();

        // Batting
        $innsBatted = $batting->where('dismissal_type', '!=', 'did_not_bat')->count();
        $runs       = (int) $batting->sum('runs_scored');
        $ballsFaced = (int) $batting->sum('balls_faced');
        $notOuts    = $batting->whereIn('dismissal_type', ['not_out', 'retired_hurt'])
            ->where('balls_faced', '>', 0)->count();
        $dismissals = max(0, $innsBatted - $notOuts);

        // Bowling
        $ballsBowled = (int) $bowling->sum('overs_bowled_balls');
        $conceded    = (int) $bowling->sum('runs_conceded');
        $wickets     = (int) $bowling->sum('wickets');
        $maidens     = (int) $bowling->sum('maidens');
        $oversDec    = $this->calculator->ballsToDecimalOvers($ballsBowled);

        $best = $bowling->sortBy([['wickets', 'desc'], ['runs_conceded', 'asc']])->first();

        // Fielding
        $catches   = (int) $fielding->sum('catches');
        $runOuts   = (int) $fielding->sum('run_outs');
        $stumpings = (int) $fielding->sum('stumpings');

        $motmCount = CricketMatch::whereIn('id', $completedMatchIds)
            ->where('man_of_match_player_id', (string) $playerId)
            ->count();

        $mvpPoints =
            $runs * self::MVP_PER_RUN
            + (int) $batting->sum('fours') * self::MVP_PER_FOUR
            + (int) $batting->sum('sixes') * self::MVP_PER_SIX
            + $batting->where('is_fifty', true)->count() * self::MVP_FIFTY_BONUS
            + $batting->where('is_century', true)->count() * self::MVP_CENTURY_BONUS
            + $wickets * self::MVP_PER_WICKET
            + $maidens * self::MVP_PER_MAIDEN
            + $bowling->where('five_wicket_haul', true)->count() * self::MVP_FIVE_WICKET_BONUS
            + $bowling->where('hat_trick_wickets', true)->count() * self::MVP_HAT_TRICK_BONUS
            + $catches * self::MVP_PER_CATCH
            + $stumpings * self::MVP_PER_STUMPING
            + $runOuts * self::MVP_PER_RUN_OUT
            + $motmCount * self::MVP_MOTM_BONUS;

        return PlayerEditionStat::updateOrCreate(
            ['player_id' => $playerId, 'edition_id' => $editionId],
            [
                'team_id'                  => $teamId,
                'matches_played'           => $matchesPlayed,
                'innings_batted'           => $innsBatted,
                'total_runs'               => $runs,
                'highest_score'            => (int) ($batting->max('runs_scored') ?? 0),
                'balls_faced'              => $ballsFaced,
                'not_outs'                 => $notOuts,
                'batting_average'          => $dismissals > 0 ? round($runs / $dismissals, 2) : $runs,
                'batting_strike_rate'      => $ballsFaced > 0 ? round(($runs / $ballsFaced) * 100, 2) : 0,
                'fifties'                  => $batting->where('is_fifty', true)->count(),
                'centuries'                => $batting->where('is_century', true)->count(),
                'total_fours'              => (int) $batting->sum('fours'),
                'total_sixes'              => (int) $batting->sum('sixes'),
                'hat_trick_sixes_count'    => $batting->where('hat_trick_sixes', true)->count(),
                'five_sixes_in_over_count' => $batting->where('five_sixes_in_over', true)->count(),

                'innings_bowled'           => $bowling->where('overs_bowled_balls', '>', 0)->count(),
                'overs_bowled'             => $oversDec,
                'balls_bowled'             => $ballsBowled,
                'runs_conceded'            => $conceded,
                'total_wickets'            => $wickets,
                'total_maidens'            => $maidens,
                'bowling_average'          => $wickets > 0 ? round($conceded / $wickets, 2) : 0,
                'bowling_economy'          => $oversDec > 0 ? round($conceded / $oversDec, 2) : 0,
                'hat_trick_wickets_count'  => $bowling->where('hat_trick_wickets', true)->count(),
                'five_wicket_hauls'        => $bowling->where('five_wicket_haul', true)->count(),
                'best_bowling_wickets'     => (int) ($best?->wickets ?? 0),
                'best_bowling_runs'        => (int) ($best?->runs_conceded ?? 0),

                'total_catches'            => $catches,
                'total_run_outs'           => $runOuts,
                'total_stumpings'          => $stumpings,

                'mvp_count'                => $motmCount,
                'mvp_points'               => round($mvpPoints, 2),
            ]
        );
    }

    /* ══════════════════════════════════════════════════════════════════
     |  Leaderboards
     ══════════════════════════════════════════════════════════════════ */

    /**
     * Orange Cap (runs), Purple Cap (wickets), MVP and the rest.
     *
     * The cache holds only the ordered *id lists* — models are re-hydrated on
     * every read. Caching Eloquent objects in a shared store risks getting an
     * `__PHP_Incomplete_Class` back, which is exactly the kind of failure that
     * only shows up in production.
     *
     * @return array<string, \Illuminate\Support\Collection<int, PlayerEditionStat>>
     */
    public function leaderboards(int $editionId, int $limit = 10): array
    {
        $version = Cache::get("leaderboards_version_{$editionId}", 1);

        $ids = Cache::remember(
            "leaderboards_{$editionId}_{$limit}_v{$version}",
            60,
            fn () => $this->leaderboardIds($editionId, $limit)
        );

        $all = PlayerEditionStat::whereIn('id', collect($ids)->flatten()->unique()->values())
            ->with(['player', 'team'])
            ->get()
            ->keyBy('id');

        return collect($ids)
            ->map(fn (array $board) => collect($board)
                ->map(fn ($id) => $all->get($id))
                ->filter()
                ->values())
            ->all();
    }

    /**
     * Invalidate every cached view of an edition.
     *
     * Leaderboards are cached per `limit`, so a single `forget` would leave
     * other limits stale — bumping a version stamp retires them all at once.
     */
    public function flushEditionCaches(int $editionId): void
    {
        Cache::forget("points_table_{$editionId}");
        Cache::increment("leaderboards_version_{$editionId}")
            ?: Cache::forever("leaderboards_version_{$editionId}", 2);
    }

    /** @return array<string, array<int, int>> */
    private function leaderboardIds(int $editionId, int $limit): array
    {
        $base = fn () => PlayerEditionStat::where('edition_id', $editionId);

        return [
            'orange_cap' => $base()->where('total_runs', '>', 0)
                ->orderByDesc('total_runs')->orderByDesc('batting_strike_rate')
                ->limit($limit)->pluck('id')->all(),
            'purple_cap' => $base()->where('total_wickets', '>', 0)
                ->orderByDesc('total_wickets')->orderBy('bowling_economy')
                ->limit($limit)->pluck('id')->all(),
            'mvp' => $base()->where('mvp_points', '>', 0)
                ->orderByDesc('mvp_points')
                ->limit($limit)->pluck('id')->all(),
            'most_sixes' => $base()->where('total_sixes', '>', 0)
                ->orderByDesc('total_sixes')
                ->limit($limit)->pluck('id')->all(),
            'most_fours' => $base()->where('total_fours', '>', 0)
                ->orderByDesc('total_fours')
                ->limit($limit)->pluck('id')->all(),
            'best_strike_rate' => $base()->where('balls_faced', '>=', 20)
                ->orderByDesc('batting_strike_rate')
                ->limit($limit)->pluck('id')->all(),
            'best_economy' => $base()->where('balls_bowled', '>=', 12)
                ->orderBy('bowling_economy')
                ->limit($limit)->pluck('id')->all(),
            'most_catches' => $base()->where('total_catches', '>', 0)
                ->orderByDesc('total_catches')
                ->limit($limit)->pluck('id')->all(),
        ];
    }
}
