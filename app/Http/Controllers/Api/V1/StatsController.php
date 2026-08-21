<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EditionResource;
use App\Http\Resources\PlayerStatResource;
use App\Models\Edition;
use App\Services\MatchStatisticsService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function __construct(private readonly MatchStatisticsService $stats) {}

    /**
     * Tournament leaderboards. `orange_cap` and `purple_cap` are the headline
     * lists; the legacy `top_batsmen` / `top_bowlers` keys are kept as aliases
     * so nothing that already reads them breaks.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'edition_id' => 'nullable|integer|exists:editions,id',
            'limit'      => 'nullable|integer|min:1|max:50',
        ]);

        $editionId = $validated['edition_id']
            ?? Edition::where('is_current', true)->value('id')
            ?? Edition::orderByDesc('edition_number')->value('id');

        if (! $editionId) {
            return ApiResponse::success([
                'edition_id' => null,
                'editions'   => [],
                'boards'     => [],
            ]);
        }

        $boards = collect($this->stats->leaderboards($editionId, $validated['limit'] ?? 20))
            ->map(fn ($rows) => PlayerStatResource::collection($rows));

        return ApiResponse::success([
            'edition_id'     => (int) $editionId,
            'editions'       => EditionResource::collection(
                Edition::orderByDesc('edition_number')->get()
            ),
            'orange_cap'     => $boards['orange_cap'],
            'purple_cap'     => $boards['purple_cap'],
            'mvp'            => $boards['mvp'],
            'most_sixes'     => $boards['most_sixes'],
            'most_fours'     => $boards['most_fours'],
            'best_strike_rate' => $boards['best_strike_rate'],
            'best_economy'   => $boards['best_economy'],
            'most_catches'   => $boards['most_catches'],

            /* legacy aliases */
            'top_batsmen'    => $boards['orange_cap'],
            'top_bowlers'    => $boards['purple_cap'],
            'top_sixes'      => $boards['most_sixes'],
            'top_boundaries' => $boards['most_fours'],
            'mvps'           => $boards['mvp'],
        ]);
    }
}
