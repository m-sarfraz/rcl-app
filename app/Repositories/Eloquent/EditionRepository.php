<?php

namespace App\Repositories\Eloquent;

use App\Models\CricketMatch;
use App\Models\Edition;
use App\Models\PlayerEditionStat;
use App\Repositories\Interfaces\EditionRepositoryInterface;
use App\Services\CalculationEngineService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EditionRepository implements EditionRepositoryInterface
{
    public function __construct(private readonly CalculationEngineService $calculator) {}

    public function all(): Collection
    {
        return Edition::orderByDesc('edition_number')->get();
    }

    public function find(int $id): ?Edition
    {
        return Edition::with(['teams'])->find($id);
    }

    public function findCurrent(): ?Edition
    {
        return Edition::where('is_current', true)->first()
            ?? Edition::where('status', 'active')->latest()->first();
    }

    public function create(array $data): Edition
    {
        return Edition::create($data);
    }

    public function update(int $id, array $data): bool
    {
        return (bool) Edition::where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        return (bool) Edition::destroy($id);
    }

    public function setAsCurrent(int $id): void
    {
        DB::transaction(function () use ($id) {
            Edition::where('is_current', true)->update(['is_current' => false]);
            Edition::where('id', $id)->update(['is_current' => true]);
        });
    }

    public function getPointsTable(int $editionId): array
    {
        $edition = Edition::with('teams')->findOrFail($editionId);
        $teams = $edition->teams;
        $table = [];

        foreach ($teams as $team) {
            $matches = CricketMatch::where('edition_id', $editionId)
                ->where('status', 'completed')
                ->where(fn($q) => $q->where('home_team_id', $team->id)->orWhere('away_team_id', $team->id))
                ->get();

            $played = $matches->count();
            $won = $matches->where('winner_id', $team->id)->count();
            $lost = $matches->whereNotNull('winner_id')->where('winner_id', '!=', $team->id)->count();
            $tied = $matches->where('result_type', 'tie')->count();
            $noResult = $matches->where('result_type', 'no_result')->count();
            $points = ($won * 2) + $tied;

            // NRR calculation using VCC custom formula
            $nrr = $this->calculateNRR($team->id, $editionId);

            $table[] = [
                'team' => $team,
                'played' => $played,
                'won' => $won,
                'lost' => $lost,
                'tied' => $tied,
                'no_result' => $noResult,
                'points' => $points,
                'nrr' => $nrr,
            ];
        }

        // Sort: points desc, then NRR desc
        usort($table, fn($a, $b) =>
            $b['points'] <=> $a['points'] ?: $b['nrr'] <=> $a['nrr']
        );

        return $table;
    }

    private function calculateNRR(int $teamId, int $editionId): float
    {
        $matches = CricketMatch::where('edition_id', $editionId)
            ->where('status', 'completed')
            ->where(fn($q) => $q->where('home_team_id', $teamId)->orWhere('away_team_id', $teamId))
            ->with('innings')
            ->get();

        $totalRunsFor = 0;
        $totalOversFor = 0;
        $totalRunsAgainst = 0;
        $totalOversAgainst = 0;

        foreach ($matches as $match) {
            foreach ($match->innings as $innings) {
                $overs = $this->calculator->ballsToDecimalOvers($innings->total_balls);
                if ($innings->batting_team_id === $teamId) {
                    $totalRunsFor += $innings->total_runs;
                    $totalOversFor += $overs;
                } else {
                    $totalRunsAgainst += $innings->total_runs;
                    $totalOversAgainst += $overs;
                }
            }
        }

        if ($totalOversFor == 0 || $totalOversAgainst == 0) return 0.00;

        $rrFor = $totalRunsFor / $totalOversFor;
        $rrAgainst = $totalRunsAgainst / $totalOversAgainst;

        return round($rrFor - $rrAgainst, 2);
    }

    public function getLeaderboards(int $editionId): array
    {
        return [
            'top_batsmen' => PlayerEditionStat::where('edition_id', $editionId)
                ->with(['player', 'team'])
                ->orderByDesc('total_runs')
                ->limit(10)
                ->get(),
            'top_bowlers' => PlayerEditionStat::where('edition_id', $editionId)
                ->with(['player', 'team'])
                ->orderByDesc('total_wickets')
                ->orderBy('bowling_economy')
                ->limit(10)
                ->get(),
            'top_boundaries' => PlayerEditionStat::where('edition_id', $editionId)
                ->with(['player', 'team'])
                ->orderByDesc('total_sixes')
                ->orderByDesc('total_fours')
                ->limit(10)
                ->get(),
            'most_fifties' => PlayerEditionStat::where('edition_id', $editionId)
                ->with(['player', 'team'])
                ->orderByDesc('fifties')
                ->orderByDesc('centuries')
                ->limit(10)
                ->get(),
        ];
    }
}
