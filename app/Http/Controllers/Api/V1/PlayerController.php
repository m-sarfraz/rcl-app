<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BanResource;
use App\Http\Resources\FineResource;
use App\Http\Resources\PlayerResource;
use App\Http\Resources\PlayerStatResource;
use App\Models\BattingScorecard;
use App\Models\BowlingScorecard;
use App\Models\Player;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    /** Searchable player directory — used by the app's player search. */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search'     => 'nullable|string|max:100',
            'team_id'    => 'nullable|integer|exists:teams,id',
            'edition_id' => 'nullable|integer|exists:editions,id',
            'role'       => 'nullable|in:batsman,bowler,all_rounder,wicket_keeper',
            'per_page'   => 'nullable|integer|min:1|max:100',
        ]);

        $players = Player::query()
            ->where('is_active', true)
            ->when($validated['search'] ?? null, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($validated['role'] ?? null, fn ($q, $r) => $q->where('role', $r))
            ->when($validated['team_id'] ?? null, fn ($q, $id) => $q->whereHas(
                'rosterEntries',
                fn ($r) => $r->where('team_id', $id)
                    ->when($validated['edition_id'] ?? null, fn ($e, $ed) => $e->where('edition_id', $ed))
            ))
            ->with('currentTeam')
            ->orderBy('name')
            ->paginate($validated['per_page'] ?? 30);

        return ApiResponse::paginated(PlayerResource::collection($players));
    }

    /** Career profile: per-edition aggregates plus recent form. */
    public function show(Player $player): JsonResponse
    {
        $player->load([
            'currentTeam',
            'editionStats.edition', 'editionStats.team',
            'fines' => fn ($q) => $q->where('status', 'unpaid')->with('team'),
            'activeBans',
            'suspensions' => fn ($q) => $q->where('is_active', true),
        ]);

        $recentBatting = BattingScorecard::where('player_id', $player->id)
            ->whereHas('innings.match', fn ($q) => $q->where('status', 'completed'))
            ->with(['innings.match.homeTeam', 'innings.match.awayTeam', 'bowledBy'])
            ->orderByDesc('id')->limit(10)->get();

        $recentBowling = BowlingScorecard::where('player_id', $player->id)
            ->whereHas('innings.match', fn ($q) => $q->where('status', 'completed'))
            ->with(['innings.match.homeTeam', 'innings.match.awayTeam'])
            ->orderByDesc('id')->limit(10)->get();

        $stats = $player->editionStats;

        return ApiResponse::success([
            'player'         => new PlayerResource($player),
            'edition_stats'  => PlayerStatResource::collection($stats->sortByDesc('edition_id')->values()),
            'career'         => [
                'matches'        => (int) $stats->sum('matches_played'),
                'runs'           => (int) $stats->sum('total_runs'),
                'wickets'        => (int) $stats->sum('total_wickets'),
                'fours'          => (int) $stats->sum('total_fours'),
                'sixes'          => (int) $stats->sum('total_sixes'),
                'fifties'        => (int) $stats->sum('fifties'),
                'centuries'      => (int) $stats->sum('centuries'),
                'catches'        => (int) $stats->sum('total_catches'),
                'stumpings'      => (int) $stats->sum('total_stumpings'),
                'run_outs'       => (int) $stats->sum('total_run_outs'),
                'maidens'        => (int) $stats->sum('total_maidens'),
                'highest_score'  => (int) ($stats->max('highest_score') ?? 0),
                'mvp_awards'     => (int) $stats->sum('mvp_count'),
                'mvp_points'     => round((float) $stats->sum('mvp_points'), 2),
            ],
            'recent_batting' => $recentBatting->map(fn ($sc) => [
                'match_id'    => $sc->match_id,
                'opponent'    => $this->opponentName($sc->innings?->match, $sc->team_id),
                'runs'        => (int) $sc->runs_scored,
                'balls'       => (int) $sc->balls_faced,
                'strike_rate' => round((float) $sc->strike_rate, 2),
                'is_out'      => $sc->is_out,
            ])->values(),
            'recent_bowling' => $recentBowling->map(fn ($sc) => [
                'match_id' => $sc->match_id,
                'opponent' => $this->opponentName($sc->innings?->match, $sc->team_id),
                'overs'    => \App\Support\Overs::display((int) $sc->overs_bowled_balls),
                'runs'     => (int) $sc->runs_conceded,
                'wickets'  => (int) $sc->wickets,
                'economy'  => round((float) $sc->economy, 2),
            ])->values(),
            'eligibility'    => [
                'is_eligible' => $player->ineligibilityReason() === null,
                'reason'      => $player->ineligibilityReason(),
            ],
            'unpaid_fines'   => FineResource::collection($player->fines),
            'active_bans'    => BanResource::collection($player->activeBans),
        ]);
    }

    private function opponentName(?\App\Models\CricketMatch $match, ?int $teamId): ?string
    {
        if (! $match || ! $teamId) {
            return null;
        }

        return $match->home_team_id === $teamId
            ? $match->awayTeam?->name
            : $match->homeTeam?->name;
    }
}
