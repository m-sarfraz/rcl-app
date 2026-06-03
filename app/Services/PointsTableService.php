<?php

namespace App\Services;

use App\Models\CricketMatch;
use App\Models\Edition;
use App\Models\Innings;
use Illuminate\Support\Collection;

class PointsTableService
{
    public function __construct(private readonly CalculationEngineService $calculator) {}

    public function generate(int $editionId): array
    {
        $edition = Edition::with('teams')->findOrFail($editionId);
        $table = [];

        foreach ($edition->teams as $team) {
            $matches = CricketMatch::where('edition_id', $editionId)
                ->where('status', 'completed')
                ->where(fn($q) => $q->where('home_team_id', $team->id)->orWhere('away_team_id', $team->id))
                ->get();

            $played  = $matches->count();
            $won     = $matches->where('winner_id', $team->id)->count();
            $lost    = $matches->whereNotNull('winner_id')->where('winner_id', '!=', $team->id)->count();
            $tied    = $matches->where('result_type', 'tie')->count();
            $nr      = $matches->where('result_type', 'no_result')->count();
            $points  = ($won * 2) + $tied;
            $nrr     = $this->teamNRR($team->id, $editionId);

            $table[] = compact('team','played','won','lost','tied','nr','points','nrr');
        }

        usort($table, fn($a,$b) => $b['points'] <=> $a['points'] ?: $b['nrr'] <=> $a['nrr']);

        return $table;
    }

    private function teamNRR(int $teamId, int $editionId): float
    {
        $innings = Innings::whereHas('match', fn($q) => $q->where('edition_id', $editionId)->where('status','completed'))
            ->get();

        $runsFor = $ballsFor = $runsAgainst = $ballsAgainst = 0;

        foreach ($innings as $inn) {
            if ($inn->batting_team_id === $teamId) {
                $runsFor    += $inn->total_runs;
                $ballsFor   += $inn->total_balls;
            } elseif ($inn->bowling_team_id === $teamId) {
                $runsAgainst  += $inn->total_runs;
                $ballsAgainst += $inn->total_balls;
            }
        }

        if (!$ballsFor || !$ballsAgainst) return 0.00;

        $rrFor      = $runsFor  / $this->calculator->ballsToDecimalOvers($ballsFor);
        $rrAgainst  = $runsAgainst / $this->calculator->ballsToDecimalOvers($ballsAgainst);

        return round($rrFor - $rrAgainst, 2);
    }
}
