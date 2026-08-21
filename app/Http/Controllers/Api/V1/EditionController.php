<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EditionResource;
use App\Http\Resources\MatchResource;
use App\Http\Resources\PlayerStatResource;
use App\Http\Resources\TeamResource;
use App\Models\Edition;
use App\Services\MatchStatisticsService;
use App\Services\PointsTableService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class EditionController extends Controller
{
    public function __construct(
        private readonly PointsTableService $pointsTable,
        private readonly MatchStatisticsService $stats,
    ) {}

    public function index(): JsonResponse
    {
        $editions = Edition::withCount(['matches', 'teams'])
            ->orderByDesc('edition_number')
            ->paginate(20);

        return ApiResponse::paginated(EditionResource::collection($editions));
    }

    /** Everything the edition hub screen renders in one call. */
    public function show(Edition $edition): JsonResponse
    {
        $edition->load('teams')->loadCount(['matches', 'teams']);

        $matches = $edition->matches()
            ->with(['homeTeam', 'awayTeam', 'winner', 'innings'])
            ->orderBy('scheduled_at')
            ->orderByRaw('CAST(match_number AS UNSIGNED)')
            ->get();

        return ApiResponse::success([
            'edition'           => new EditionResource($edition),
            'teams'             => TeamResource::collection($edition->teams),
            'live_matches'      => MatchResource::collection($matches->where('status', 'live')->values()),
            'upcoming_matches'  => MatchResource::collection($matches->where('status', 'upcoming')->values()),
            'completed_matches' => MatchResource::collection($matches->where('status', 'completed')->sortByDesc('scheduled_at')->values()),
            'points_table'      => $this->formatPointsTable($edition->id),
            'leaderboards'      => $this->formatLeaderboards($edition->id),
        ]);
    }

    public function pointsTable(Edition $edition): JsonResponse
    {
        return ApiResponse::success($this->formatPointsTable($edition->id));
    }

    public function leaderboards(Edition $edition): JsonResponse
    {
        return ApiResponse::success($this->formatLeaderboards($edition->id));
    }

    private function formatPointsTable(int $editionId): array
    {
        return collect($this->pointsTable->generate($editionId))
            ->values()
            ->map(fn ($row, $i) => [
                'position'      => $i + 1,
                'team'          => new TeamResource($row['team']),
                'played'        => $row['played'],
                'won'           => $row['won'],
                'lost'          => $row['lost'],
                'tied'          => $row['tied'],
                'no_result'     => $row['no_result'],
                'points'        => $row['points'],
                'nrr'           => $row['nrr'],
                'runs_for'      => $row['runs_for'],
                'overs_for'     => $row['overs_for'],
                'runs_against'  => $row['runs_against'],
                'overs_against' => $row['overs_against'],
            ])->all();
    }

    private function formatLeaderboards(int $editionId): array
    {
        return collect($this->stats->leaderboards($editionId))
            ->map(fn ($rows) => PlayerStatResource::collection($rows))
            ->all();
    }
}
