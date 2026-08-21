<?php

namespace App\Services;

use App\Models\CricketMatch;
use App\Models\Edition;
use App\Models\MatchSquad;
use App\Models\Team;
use Illuminate\Support\Facades\Cache;

/**
 * Points table + net run rate for one edition.
 *
 * Two rules that the previous implementation quietly skipped:
 *
 *  • **Bowled out ⇒ full quota.** A side dismissed inside its allotted overs is
 *    charged the whole quota for run-rate purposes. Without this, collapsing
 *    cheaply in 4 overs *improved* a team's NRR.
 *  • **No result ⇒ one point each.** Previously a washout scored nothing.
 *
 * All rates run through CalculationEngineService, so the VCC 0.17-overs-per-ball
 * rule applies here exactly as it does on the scoreboard.
 */
class PointsTableService
{
    private const POINTS_WIN       = 2;
    private const POINTS_TIE       = 1;
    private const POINTS_NO_RESULT = 1;

    public function __construct(private readonly CalculationEngineService $calculator) {}

    /**
     * The table for one edition.
     *
     * Only the *numbers* are cached. Eloquent models are re-hydrated on every
     * read, because serialising models into the shared cache store is how you
     * end up with `__PHP_Incomplete_Class` on the way back out.
     */
    public function generate(int $editionId): array
    {
        $rows = Cache::remember(
            "points_table_{$editionId}",
            60,
            fn () => $this->computeRows($editionId)
        );

        $teams = Team::whereIn('id', array_column($rows, 'team_id'))->get()->keyBy('id');

        return collect($rows)
            ->map(fn (array $row) => array_merge($row, ['team' => $teams->get($row['team_id'])]))
            ->filter(fn (array $row) => $row['team'] !== null)
            ->values()
            ->all();
    }

    /** @return array<int, array<string, mixed>> plain scalars, safe to cache */
    private function computeRows(int $editionId): array
    {
        $edition = Edition::with('teams')->findOrFail($editionId);

        $matches = CricketMatch::where('edition_id', $editionId)
            ->where('status', 'completed')
            ->with(['innings'])
            ->get();

        $table = [];

        foreach ($edition->teams as $team) {
            $teamMatches = $matches->filter(
                fn ($m) => $m->home_team_id === $team->id || $m->away_team_id === $team->id
            );

            $played = $teamMatches->count();
            $won    = $teamMatches->where('winner_id', $team->id)->count();
            $tied   = $teamMatches->where('result_type', 'tie')->count();
            $nr     = $teamMatches->whereIn('result_type', ['no_result', null])->count();
            $lost   = max(0, $played - $won - $tied - $nr);

            $nrrParts = $this->netRunRateParts($teamMatches, $team->id);

            $table[] = [
                'team_id'   => $team->id,
                'team_name' => $team->name,
                'played'    => $played,
                'won'       => $won,
                'lost'      => $lost,
                'tied'      => $tied,
                'no_result' => $nr,
                'nr'        => $nr, // legacy key kept for existing Blade views
                'points'    => ($won * self::POINTS_WIN)
                             + ($tied * self::POINTS_TIE)
                             + ($nr * self::POINTS_NO_RESULT),
                'nrr'           => $nrrParts['nrr'],
                'runs_for'      => $nrrParts['runs_for'],
                'overs_for'     => $nrrParts['overs_for'],
                'runs_against'  => $nrrParts['runs_against'],
                'overs_against' => $nrrParts['overs_against'],
            ];
        }

        usort($table, fn ($a, $b) =>
            $b['points'] <=> $a['points']
                ?: $b['nrr'] <=> $a['nrr']
                ?: $b['won'] <=> $a['won']
                ?: strcmp($a['team_name'], $b['team_name'])
        );

        return $table;
    }

    /**
     * @return array{nrr:float, runs_for:int, overs_for:float, runs_against:int, overs_against:float}
     */
    private function netRunRateParts($teamMatches, int $teamId): array
    {
        $runsFor = $runsAgainst = 0;
        $oversFor = $oversAgainst = 0.0;

        foreach ($teamMatches as $match) {
            if (in_array($match->result_type, ['no_result', null], true)) {
                continue; // abandoned games never count towards run rate
            }

            foreach ($match->innings as $innings) {
                if ((int) $innings->innings_number > 2) {
                    continue; // super overs are excluded from NRR
                }

                $overs = $this->chargeableOvers($match, $innings);

                if ($innings->batting_team_id === $teamId) {
                    $runsFor  += (int) $innings->total_runs;
                    $oversFor += $overs;
                } elseif ($innings->bowling_team_id === $teamId) {
                    $runsAgainst  += (int) $innings->total_runs;
                    $oversAgainst += $overs;
                }
            }
        }

        $rrFor     = $oversFor > 0 ? $runsFor / $oversFor : 0;
        $rrAgainst = $oversAgainst > 0 ? $runsAgainst / $oversAgainst : 0;

        return [
            'nrr'           => ($oversFor > 0 && $oversAgainst > 0) ? round($rrFor - $rrAgainst, 2) : 0.00,
            'runs_for'      => $runsFor,
            'overs_for'     => round($oversFor, 2),
            'runs_against'  => $runsAgainst,
            'overs_against' => round($oversAgainst, 2),
        ];
    }

    /**
     * Overs to charge an innings: the overs actually faced, unless the side was
     * bowled out — then the full allotted quota, per standard NRR practice.
     */
    private function chargeableOvers(CricketMatch $match, $innings): float
    {
        $faced = $this->calculator->ballsToDecimalOvers((int) $innings->total_balls);

        $squad = MatchSquad::where('match_id', $match->id)
            ->where('team_id', $innings->batting_team_id)
            ->count();
        $wicketsAvailable = $squad > 1 ? $squad - 1 : 10;

        if ((int) $innings->total_wickets >= $wicketsAvailable) {
            $quota = $this->calculator->ballsToDecimalOvers((int) $match->overs_per_side * 6);

            return max($faced, $quota);
        }

        return $faced;
    }
}
