<?php

namespace App\Repositories\Eloquent;

use App\Models\BattingScorecard;
use App\Models\BowlingScorecard;
use App\Models\FieldingScorecard;
use App\Models\Player;
use App\Models\PlayerEditionStat;
use App\Repositories\Interfaces\PlayerRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PlayerRepository implements PlayerRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return Player::when(isset($filters['team_id']), fn($q) =>
                $q->whereHas('teams', fn($q2) => $q2->where('teams.id', $filters['team_id']))
            )
            ->when(isset($filters['edition_id']), fn($q) =>
                $q->whereHas('teams', fn($q2) =>
                    $q2->where('player_team_editions.edition_id', $filters['edition_id'])
                )
            )
            ->orderBy('name')
            ->get();
    }

    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        $editionId = $filters['edition_id'] ?? null;

        return Player::when(isset($filters['search']), fn($q) =>
                $q->where('name', 'like', "%{$filters['search']}%")
            )
            ->when(isset($filters['role']) && $filters['role'] !== '', fn($q) => $q->where('role', '=', $filters['role']))
            ->when(isset($filters['team_id']) && $filters['team_id'] !== '', fn($q) =>
                $q->whereHas('teams', fn($q2) => $q2->where('teams.id', '=', $filters['team_id']))
            )
            ->with(['teams' => function ($q) use ($editionId) {
                if ($editionId) {
                    $q->wherePivot('edition_id', '=', $editionId);
                }
            }])
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function find(int $id): ?Player
    {
        return Player::with(['teams', 'fines', 'suspensions', 'editionStats'])->find($id);
    }

    public function create(array $data): Player
    {
        return Player::create($data);
    }

    public function update(int $id, array $data): bool
    {
        return (bool) Player::where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        return (bool) Player::destroy($id);
    }

    public function getEligible(int $editionId, int $teamId): Collection
    {
        return Player::eligible()
            ->whereHas('teams', fn($q) =>
                $q->where('teams.id', $teamId)
                  ->where('player_team_editions.edition_id', $editionId)
            )
            ->orderBy('name')
            ->get();
    }

    public function assignToTeam(int $playerId, int $teamId, int $editionId, array $extra = []): void
    {
        $player = Player::findOrFail($playerId);
        $player->teams()->syncWithoutDetaching([
            $teamId => array_merge(['edition_id' => $editionId], $extra),
        ]);
    }

    public function getByTeamAndEdition(int $teamId, int $editionId): Collection
    {
        return Player::whereHas('teams', fn($q) =>
            $q->where('teams.id', $teamId)
              ->where('player_team_editions.edition_id', $editionId)
        )->with(['fines', 'suspensions'])->orderBy('name')->get();
    }

    public function updateEditionStats(int $playerId, int $editionId): void
    {
        $batting = BattingScorecard::where('player_id', $playerId)
            ->whereHas('innings', fn($q) => $q->whereHas('match', fn($q2) => $q2->where('edition_id', $editionId)))
            ->get();

        $bowling = BowlingScorecard::where('player_id', $playerId)
            ->whereHas('innings', fn($q) => $q->whereHas('match', fn($q2) => $q2->where('edition_id', $editionId)))
            ->get();

        $fielding = FieldingScorecard::where('player_id', $playerId)
            ->whereHas('match', fn($q) => $q->where('edition_id', $editionId))
            ->get();

        $teamId = $batting->first()?->team_id ?? $bowling->first()?->team_id;

        $stats = [
            'matches_played' => $batting->pluck('match_id')->merge($bowling->pluck('match_id'))->unique()->count(),
            'innings_batted' => $batting->count(),
            'total_runs' => $batting->sum('runs_scored'),
            'highest_score' => $batting->max('runs_scored') ?? 0,
            'total_fours' => $batting->sum('fours'),
            'total_sixes' => $batting->sum('sixes'),
            'fifties' => $batting->where('is_fifty', true)->count(),
            'centuries' => $batting->where('is_century', true)->count(),
            'hat_trick_sixes_count' => $batting->where('hat_trick_sixes', true)->count(),
            'five_sixes_in_over_count' => $batting->where('five_sixes_in_over', true)->count(),
            'innings_bowled' => $bowling->count(),
            'overs_bowled' => $bowling->sum('overs_bowled'),
            'total_wickets' => $bowling->sum('wickets'),
            'total_maidens' => $bowling->sum('maidens'),
            'hat_trick_wickets_count' => $bowling->where('hat_trick_wickets', true)->count(),
            'five_wicket_hauls' => $bowling->where('five_wicket_haul', true)->count(),
            'total_catches' => $fielding->sum('catches'),
            'total_run_outs' => $fielding->sum('run_outs'),
            'total_stumpings' => $fielding->sum('stumpings'),
        ];

        $totalRuns = $stats['total_runs'];
        $notOuts = $batting->whereIn('dismissal_type', ['not_out', 'retired_hurt'])->count();
        $dismissals = $batting->count() - $notOuts;
        $stats['batting_average'] = $dismissals > 0 ? round($totalRuns / $dismissals, 2) : $totalRuns;

        $ballsFaced = $batting->sum('balls_faced');
        $stats['batting_strike_rate'] = $ballsFaced > 0 ? round(($totalRuns / $ballsFaced) * 100, 2) : 0;

        $runsConc = $bowling->sum('runs_conceded');
        $oversBowled = (float) $stats['overs_bowled'];
        $stats['bowling_average'] = $stats['total_wickets'] > 0 ? round($runsConc / $stats['total_wickets'], 2) : 0;
        $stats['bowling_economy'] = $oversBowled > 0 ? round($runsConc / $oversBowled, 2) : 0;

        if ($teamId) {
            $stats['team_id'] = $teamId;
            PlayerEditionStat::updateOrCreate(
                ['player_id' => $playerId, 'edition_id' => $editionId],
                $stats
            );
        }
    }
}
