<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CricketMatch;
use App\Models\Edition;
use App\Models\Innings;
use App\Models\PlayerEditionStat;
use App\Services\PointsTableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TournamentController extends Controller
{
    public function __construct(private readonly PointsTableService $pointsTable) {}

    public function index()
    {
        $editions = Edition::withCount('matches','teams')
            ->orderByDesc('edition_number')->paginate(10);
        return view('frontend.tournaments.index', compact('editions'));
    }

    public function show(Edition $edition)
    {
        $edition->load('teams');

        $liveMatches     = $edition->matches()->with(['homeTeam','awayTeam','innings'])->where('status','live')->get();
        $upcomingMatches = $edition->matches()->with(['homeTeam','awayTeam'])->where('status','upcoming')->orderBy('scheduled_at')->get();
        $completedMatches= $edition->matches()->with(['homeTeam','awayTeam','winner'])->where('status','completed')->latest()->get();

        $pointsTable = $this->pointsTable->generate($edition->id);

        // Leaderboards
        $topBatsmen  = PlayerEditionStat::where('edition_id', $edition->id)
            ->with('player','team')->orderByDesc('total_runs')->limit(10)->get();
        $topBowlers  = PlayerEditionStat::where('edition_id', $edition->id)
            ->with('player','team')->orderByDesc('total_wickets')->limit(10)->get();

        return view('frontend.tournaments.show', compact(
            'edition','liveMatches','upcomingMatches','completedMatches','pointsTable','topBatsmen','topBowlers'
        ));
    }

    public function liveMatch(CricketMatch $match): JsonResponse
    {
        $match->load(['homeTeam','awayTeam','innings.ballByBall','innings.battingScorecards.player','innings.bowlingScorecards.player']);
        return response()->json($match);
    }

    public function scorecard(CricketMatch $match)
    {
        $match->load([
            'edition','homeTeam','awayTeam','winner',
            'innings.battingTeam','innings.bowlingTeam',
            'innings.battingScorecards.player',
            'innings.battingScorecards.bowledBy',
            'innings.bowlingScorecards.player',
        ]);

        $inn1 = $match->innings->firstWhere('innings_number', 1);
        $inn2 = $match->innings->firstWhere('innings_number', 2);

        // Drives the Open Graph tags, so a shared link previews the live score.
        $scoreboard = app(\App\Services\ScoreboardService::class)->snapshot($match);

        return view('frontend.scorecard', compact('match','inn1','inn2','scoreboard'));
    }

    /**
     * Cast a poll vote.
     *
     * `poll_options` has no counter column — votes are rows in `poll_votes`,
     * which also carries the `ip_address` / `session_token` columns the ballot
     * needs to stop the same visitor voting in a loop.
     */
    public function poll(Request $request, int $pollId): JsonResponse
    {
        $request->validate(['option_id' => 'required|integer|exists:poll_options,id']);

        $option = \App\Models\PollOption::findOrFail($request->integer('option_id'));

        if ((int) $option->poll_id !== $pollId) {
            return response()->json(['error' => 'That option does not belong to this poll.'], 422);
        }

        $poll = $option->poll;

        if (! $poll || ! $poll->is_active || ($poll->ends_at && $poll->ends_at->isPast())) {
            return response()->json(['error' => 'This poll is closed.'], 422);
        }

        $sessionToken = $request->session()->getId();

        $already = \App\Models\PollVote::where('poll_id', $pollId)
            ->where(fn ($q) => $q->where('session_token', $sessionToken)->orWhere('ip_address', $request->ip()))
            ->first();

        if ($already) {
            return response()->json([
                'error'           => 'You have already voted in this poll.',
                'voted_option_id' => $already->poll_option_id,
                'counts'          => $this->pollCounts($pollId),
            ], 409);
        }

        \App\Models\PollVote::create([
            'poll_id'        => $pollId,
            'poll_option_id' => $option->id,
            'user_id'        => auth()->id(),
            'ip_address'     => $request->ip(),
            'session_token'  => $sessionToken,
        ]);

        $counts = $this->pollCounts($pollId);

        return response()->json([
            'total'        => array_sum($counts),
            'option_votes' => $counts[$option->id] ?? 0,
            'counts'       => $counts,
        ]);
    }

    /** @return array<int, int> option id ⇒ vote count */
    private function pollCounts(int $pollId): array
    {
        return \App\Models\PollVote::where('poll_id', $pollId)
            ->selectRaw('poll_option_id, COUNT(*) as total')
            ->groupBy('poll_option_id')
            ->pluck('total', 'poll_option_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }
}
