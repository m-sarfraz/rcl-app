<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BanResource;
use App\Http\Resources\FineResource;
use App\Http\Resources\TeamResource;
use App\Models\BannedBowler;
use App\Models\Fine;
use App\Models\PlayerSuspension;
use App\Models\Team;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Player governance — bans, fines and suspensions — readable from both the app
 * and the web site so eligibility is never a surprise on match day.
 */
class DisciplineController extends Controller
{
    /** Bowling-action bans. */
    public function bans(Request $request): JsonResponse
    {
        $onlyActive = $request->boolean('active_only', false);

        $bans = BannedBowler::with('player.currentTeam')
            ->when($onlyActive, fn ($q) => $q->where('is_active', true))
            ->orderByDesc('is_active')
            ->orderByDesc('banned_from')
            ->get();

        return ApiResponse::success([
            'active'   => BanResource::collection($bans->where('is_active', true)->values()),
            'lifted'   => BanResource::collection($bans->where('is_active', false)->values()),
            'total'    => $bans->count(),
        ]);
    }

    /** Fines grouped by team, which is how the web site presents them. */
    public function fines(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status'     => 'nullable|in:all,unpaid,paid,waived',
            'edition_id' => 'nullable|integer|exists:editions,id',
            'team_id'    => 'nullable|integer|exists:teams,id',
        ]);

        $fines = Fine::with(['player', 'team'])
            ->when(($validated['status'] ?? 'all') !== 'all', fn ($q) => $q->where('status', $validated['status']))
            ->when($validated['edition_id'] ?? null, fn ($q, $id) => $q->where('edition_id', $id))
            ->when($validated['team_id'] ?? null, fn ($q, $id) => $q->where('team_id', $id))
            ->orderByDesc('created_at')
            ->get();

        $byTeam = $fines->groupBy('team_id')->map(function ($group, $teamId) {
            $team = $group->first()->team;

            return [
                'team'         => $team ? new TeamResource($team) : null,
                'total_amount' => round((float) $group->sum('amount'), 2),
                'unpaid_amount'=> round((float) $group->where('status', 'unpaid')->sum('amount'), 2),
                'count'        => $group->count(),
                'fines'        => FineResource::collection($group->values()),
            ];
        })->values();

        return ApiResponse::success([
            'summary' => [
                'total_amount'  => round((float) $fines->sum('amount'), 2),
                'unpaid_amount' => round((float) $fines->where('status', 'unpaid')->sum('amount'), 2),
                'unpaid_count'  => $fines->where('status', 'unpaid')->count(),
                'total_count'   => $fines->count(),
            ],
            'by_team' => $byTeam,
            'all'     => FineResource::collection($fines),
        ]);
    }

    /** Suspensions — separate from bowling-action bans. */
    public function suspensions(): JsonResponse
    {
        $suspensions = PlayerSuspension::with('player.currentTeam')
            ->where('is_active', true)
            ->orderByDesc('start_date')
            ->get();

        return ApiResponse::success($suspensions->map(fn ($s) => [
            'id'                => $s->id,
            'player'            => $s->player ? new \App\Http\Resources\PlayerResource($s->player) : null,
            'type'              => $s->type,
            'reason'            => $s->reason,
            'start_date'        => $s->start_date?->toDateString(),
            'end_date'          => $s->end_date?->toDateString(),
            'matches_suspended' => (int) $s->matches_suspended,
        ])->values());
    }

    /** Captains and vice-captains for the current (or given) edition. */
    public function captains(Request $request): JsonResponse
    {
        $editionId = $request->integer('edition_id')
            ?: \App\Models\Edition::where('is_current', true)->value('id');

        /*
         * Pivot columns are referenced by their qualified names rather than
         * wherePivot(): inside `when()` the callback receives the underlying
         * query builder, where wherePivot() would be swallowed by Laravel's
         * dynamic-where magic and become `where('pivot', …)`.
         */
        $teams = Team::active()
            ->with(['players' => function ($q) use ($editionId) {
                $q->when($editionId, fn ($p, $id) => $p->where('player_team_editions.edition_id', $id))
                  ->where(fn ($p) => $p
                      ->where('player_team_editions.is_captain', true)
                      ->orWhere('player_team_editions.is_vice_captain', true));
            }])
            ->orderBy('name')
            ->get();

        return ApiResponse::success($teams->map(function ($team) {
            $captain = $team->players->first(fn ($p) => (bool) $p->pivot->is_captain);
            $vice    = $team->players->first(fn ($p) => (bool) $p->pivot->is_vice_captain);

            return [
                'team'         => new TeamResource($team),
                'captain'      => $captain ? new \App\Http\Resources\PlayerResource($captain) : null,
                'vice_captain' => $vice ? new \App\Http\Resources\PlayerResource($vice) : null,
            ];
        })->values());
    }
}
