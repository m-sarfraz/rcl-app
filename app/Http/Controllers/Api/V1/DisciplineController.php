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

    /** Demerit points ledger and standings by team, player, and other categories. */
    public function demeritPoints(Request $request): JsonResponse
    {
        $editionId = $request->integer('edition_id') ?: null;

        // Fetch all active incidents
        $incidents = \App\Models\DemeritPoint::with(['player.currentTeam', 'team', 'edition', 'match.homeTeam', 'match.awayTeam'])
            ->active()
            ->when($editionId, fn ($q) => $q->where('edition_id', $editionId))
            ->orderByDesc('incident_date')
            ->get();

        // Distinct category tabs
        $dbCategories = \App\Models\DemeritPoint::distinct()->pluck('target_type')->toArray();
        $allCategories = array_values(array_unique(array_merge(['player', 'team', 'umpire'], $dbCategories)));

        $categories = collect($allCategories)->map(fn ($cat) => [
            'slug'  => $cat,
            'label' => $cat === 'player' ? 'Players' : ($cat === 'team' ? 'Teams' : ($cat === 'umpire' ? 'Umpires' : ucfirst($cat))),
        ])->values();

        // 1. Teams: All active teams with demerit points (0 if none)
        $teamIncidents = $incidents->where('target_type', 'team')->groupBy('team_id');
        $teams = Team::active()->orderBy('name')->get()->map(function ($team) use ($teamIncidents) {
            $group = $teamIncidents->get($team->id, collect());
            $pts = (int) $group->sum('points');

            return [
                'team'           => new TeamResource($team),
                'demerit_points' => $pts,
                'status'         => $pts >= \App\Models\DemeritPoint::THRESHOLD_BAN ? 'banned' : ($pts === 2 ? 'critical' : ($pts === 1 ? 'warning' : 'safe')),
                'color'          => $pts >= \App\Models\DemeritPoint::THRESHOLD_BAN ? 'red' : ($pts === 2 ? 'red' : ($pts === 1 ? 'yellow' : 'green')),
                'color_code'     => $pts >= \App\Models\DemeritPoint::THRESHOLD_BAN ? '#ef4444' : ($pts === 2 ? '#ef4444' : ($pts === 1 ? '#eab308' : '#22c55e')),
                'is_banned'      => $pts >= \App\Models\DemeritPoint::THRESHOLD_BAN,
                'incident_count' => $group->count(),
                'incidents'      => \App\Http\Resources\DemeritPointResource::collection($group->values()),
            ];
        })->sortByDesc('demerit_points')->values();

        // 2. Players: Players who have demerit points
        $playerIncidents = $incidents->where('target_type', 'player')->groupBy('player_id');
        $playerIds = $playerIncidents->keys();
        $players = \App\Models\Player::with('currentTeam')
            ->whereIn('id', $playerIds)
            ->get()
            ->map(function ($player) use ($playerIncidents) {
                $group = $playerIncidents->get($player->id, collect());
                $pts = (int) $group->sum('points');

                return [
                    'player'         => new \App\Http\Resources\PlayerResource($player),
                    'demerit_points' => $pts,
                    'status'         => $pts >= \App\Models\DemeritPoint::THRESHOLD_BAN ? 'banned' : ($pts === 2 ? 'critical' : ($pts === 1 ? 'warning' : 'safe')),
                    'color'          => $pts >= \App\Models\DemeritPoint::THRESHOLD_BAN ? 'red' : ($pts === 2 ? 'red' : ($pts === 1 ? 'yellow' : 'green')),
                    'color_code'     => $pts >= \App\Models\DemeritPoint::THRESHOLD_BAN ? '#ef4444' : ($pts === 2 ? '#ef4444' : ($pts === 1 ? '#eab308' : '#22c55e')),
                    'is_banned'      => $pts >= \App\Models\DemeritPoint::THRESHOLD_BAN,
                    'incident_count' => $group->count(),
                    'incidents'      => \App\Http\Resources\DemeritPointResource::collection($group->values()),
                ];
            })->sortByDesc('demerit_points')->values();

        // 3. Other categories (Umpires, Custom)
        $otherIncidents = $incidents->whereNotIn('target_type', ['player', 'team'])->groupBy('target_type');
        $others = $otherIncidents->map(function ($group, $category) {
            return $group->groupBy('target_name')->map(function ($entityGroup, $name) use ($category) {
                $pts = (int) $entityGroup->sum('points');
                return [
                    'name'           => $name ?: 'Unnamed',
                    'category'       => $category,
                    'demerit_points' => $pts,
                    'status'         => $pts >= \App\Models\DemeritPoint::THRESHOLD_BAN ? 'banned' : ($pts === 2 ? 'critical' : ($pts === 1 ? 'warning' : 'safe')),
                    'color'          => $pts >= \App\Models\DemeritPoint::THRESHOLD_BAN ? 'red' : ($pts === 2 ? 'red' : ($pts === 1 ? 'yellow' : 'green')),
                    'color_code'     => $pts >= \App\Models\DemeritPoint::THRESHOLD_BAN ? '#ef4444' : ($pts === 2 ? '#ef4444' : ($pts === 1 ? '#eab308' : '#22c55e')),
                    'is_banned'      => $pts >= \App\Models\DemeritPoint::THRESHOLD_BAN,
                    'incident_count' => $entityGroup->count(),
                    'incidents'      => \App\Http\Resources\DemeritPointResource::collection($entityGroup->values()),
                ];
            })->values();
        });

        return ApiResponse::success([
            'rule_note'       => \App\Models\DemeritPoint::RULE_NOTE,
            'threshold'       => \App\Models\DemeritPoint::THRESHOLD_BAN,
            'color_scale'     => [
                0  => ['label' => '0 points', 'color' => 'green',  'hex' => '#22c55e', 'status' => 'safe'],
                1  => ['label' => '1 point',  'color' => 'yellow', 'hex' => '#eab308', 'status' => 'warning'],
                2  => ['label' => '2 points', 'color' => 'red',    'hex' => '#ef4444', 'status' => 'critical'],
                '3+' => ['label' => '3+ points', 'color' => 'banned', 'hex' => '#ef4444', 'status' => 'banned'],
            ],
            'categories'      => $categories,
            'summary'         => [
                'total_points'         => (int) $incidents->sum('points'),
                'banned_players_count' => $players->where('is_banned', true)->count(),
                'banned_teams_count'   => $teams->where('is_banned', true)->count(),
                'total_incidents'      => $incidents->count(),
            ],
            'teams'           => $teams,
            'players'         => $players,
            'others'          => $others,
            'all_incidents'   => \App\Http\Resources\DemeritPointResource::collection($incidents),
        ]);
    }
}
