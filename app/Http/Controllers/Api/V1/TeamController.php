<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EditionResource;
use App\Http\Resources\MatchResource;
use App\Http\Resources\PlayerResource;
use App\Http\Resources\PlayerStatResource;
use App\Http\Resources\TeamResource;
use App\Models\CricketMatch;
use App\Models\Edition;
use App\Models\PlayerEditionStat;
use App\Models\PlayerEditionTeam;
use App\Models\Team;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index(): JsonResponse
    {
        $teams = Team::active()
            ->withCount('players')
            ->orderBy('name')
            ->get();

        return ApiResponse::success(TeamResource::collection($teams));
    }

    /** Squad, fixtures and record for one club. */
    public function show(Team $team, Request $request): JsonResponse
    {
        $editionId = $request->integer('edition_id')
            ?: Edition::where('is_current', true)->value('id');

        $roster = PlayerEditionTeam::where('team_id', $team->id)
            ->when($editionId, fn ($q) => $q->where('edition_id', $editionId))
            ->with(['player.currentTeam'])
            ->get()
            ->pluck('player')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();

        // If nobody is on this edition's roster yet, fall back to all-time.
        if ($roster->isEmpty()) {
            $roster = PlayerEditionTeam::where('team_id', $team->id)
                ->with('player')->get()->pluck('player')->filter()
                ->unique('id')->sortBy('name')->values();
        }

        $matches = CricketMatch::with(['homeTeam', 'awayTeam', 'winner', 'edition', 'innings'])
            ->where(fn ($q) => $q->where('home_team_id', $team->id)->orWhere('away_team_id', $team->id))
            ->when($editionId, fn ($q) => $q->where('edition_id', $editionId))
            ->orderBy('scheduled_at')
            ->get();

        $completed = $matches->where('status', 'completed');

        return ApiResponse::success([
            'team'     => new TeamResource($team->loadCount('players')),
            'editions' => EditionResource::collection($team->editions()->orderByDesc('edition_number')->get()),
            'players'  => PlayerResource::collection($roster),
            'matches'  => MatchResource::collection($matches),
            'record'   => [
                'played'    => $completed->count(),
                'won'       => $completed->where('winner_id', $team->id)->count(),
                'lost'      => $completed->whereNotNull('winner_id')->where('winner_id', '!=', $team->id)->count(),
                'tied'      => $completed->where('result_type', 'tie')->count(),
                'no_result' => $completed->where('result_type', 'no_result')->count(),
            ],
            'top_performers' => $editionId
                ? PlayerStatResource::collection(
                    PlayerEditionStat::where('edition_id', $editionId)
                        ->where('team_id', $team->id)
                        ->with(['player', 'team'])
                        ->orderByDesc('mvp_points')
                        ->limit(5)->get()
                )
                : [],
        ]);
    }
}
