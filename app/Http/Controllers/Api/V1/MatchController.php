<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BallResource;
use App\Http\Resources\MatchDetailResource;
use App\Http\Resources\MatchResource;
use App\Models\BallByBallLog;
use App\Models\CricketMatch;
use App\Services\ScoringService;
use App\Support\ApiResponse;
use App\Support\Overs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    public function __construct(private readonly ScoringService $scoring) {}

    /** Paginated fixture list, filterable by status and edition. */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status'     => 'nullable|in:all,upcoming,live,completed,abandoned,postponed',
            'edition_id' => 'nullable|integer|exists:editions,id',
            'team_id'    => 'nullable|integer|exists:teams,id',
            'per_page'   => 'nullable|integer|min:1|max:100',
        ]);

        $matches = CricketMatch::with(['homeTeam', 'awayTeam', 'winner', 'edition', 'innings'])
            ->when(
                ($validated['status'] ?? 'all') !== 'all',
                fn ($q) => $q->where('status', $validated['status'])
            )
            ->when($validated['edition_id'] ?? null, fn ($q, $id) => $q->where('edition_id', $id))
            ->when($validated['team_id'] ?? null, fn ($q, $id) => $q->where(
                fn ($w) => $w->where('home_team_id', $id)->orWhere('away_team_id', $id)
            ))
            ->orderBy('scheduled_at')
            ->orderByRaw('CAST(match_number AS UNSIGNED)')
            ->paginate($validated['per_page'] ?? 20);

        return ApiResponse::paginated(MatchResource::collection($matches));
    }

    /** Full scorecard. */
    public function show(CricketMatch $match): JsonResponse
    {
        $match->load([
            'edition', 'homeTeam', 'awayTeam', 'winner', 'tossWinner', 'manOfMatch',
            'innings.battingTeam', 'innings.bowlingTeam',
            'innings.battingScorecards' => fn ($q) => $q->orderBy('batting_position'),
            'innings.battingScorecards.player',
            'innings.battingScorecards.bowledBy',
            'innings.battingScorecards.caughtBy',
            'innings.bowlingScorecards.player',
        ]);

        return ApiResponse::success(new MatchDetailResource($match));
    }

    /**
     * Live view for fans — the current innings, the last two overs of
     * commentary and the chase maths. Deliberately separate from the scoring
     * console, which is the only thing allowed to write.
     */
    public function live(CricketMatch $match): JsonResponse
    {
        $match->load([
            'homeTeam', 'awayTeam', 'edition',
            'innings.battingTeam', 'innings.bowlingTeam',
            'innings.battingScorecards.player',
            'innings.bowlingScorecards.player',
        ]);

        $current = $match->innings->sortByDesc('innings_number')->first();

        $recentBalls = $current
            ? BallByBallLog::where('innings_id', $current->id)
                ->with(['batsman', 'bowler'])
                ->orderByDesc('id')->limit(24)->get()->reverse()->values()
            : collect();

        $striker = $nonStriker = $bowler = null;

        if ($current) {
            $lastBall = $recentBalls->last();

            $atCrease = $current->battingScorecards
                ->whereIn('dismissal_type', ['not_out', 'retired_hurt'])
                ->where('balls_faced', '>', 0)
                ->sortByDesc('batting_position')
                ->values();

            $striker    = $lastBall
                ? $current->battingScorecards->firstWhere('player_id', $lastBall->batsman_id)
                : $atCrease->first();
            $nonStriker = $lastBall && $lastBall->non_striker_id
                ? $current->battingScorecards->firstWhere('player_id', $lastBall->non_striker_id)
                : $atCrease->skip(1)->first();
            $bowler     = $lastBall
                ? $current->bowlingScorecards->firstWhere('player_id', $lastBall->bowler_id)
                : null;
        }

        $ballsRemaining = $current
            ? max(0, ((int) $match->overs_per_side * 6) - (int) $current->total_balls)
            : 0;

        return ApiResponse::success([
            'match'            => new MatchDetailResource($match),
            'current_innings_id' => $current?->id,
            'recent_balls'     => BallResource::collection($recentBalls),
            'striker'          => $striker ? new \App\Http\Resources\BattingScorecardResource($striker->loadMissing('player')) : null,
            'non_striker'      => $nonStriker ? new \App\Http\Resources\BattingScorecardResource($nonStriker->loadMissing('player')) : null,
            'current_bowler'   => $bowler ? new \App\Http\Resources\BowlingScorecardResource($bowler->loadMissing('player')) : null,
            'chase'            => $current && $current->target ? [
                'target'          => (int) $current->target,
                'runs_needed'     => max(0, (int) $current->target - (int) $current->total_runs),
                'balls_remaining' => $ballsRemaining,
                'overs_remaining' => Overs::display($ballsRemaining),
                'required_rate'   => $this->scoring->requiredRunRate($current),
            ] : null,
        ]);
    }

    /** Full ball-by-ball commentary for one innings. */
    public function commentary(CricketMatch $match, Request $request): JsonResponse
    {
        $inningsId = $request->integer('innings_id')
            ?: $match->innings()->orderByDesc('innings_number')->value('id');

        if (! $inningsId) {
            return ApiResponse::success([]);
        }

        $balls = BallByBallLog::where('innings_id', $inningsId)
            ->where('match_id', $match->id)
            ->with(['batsman', 'bowler'])
            ->orderByDesc('id')
            ->get();

        return ApiResponse::success(BallResource::collection($balls));
    }
}
