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

        return view('frontend.scorecard', compact('match','inn1','inn2'));
    }

    public function poll(Request $request, int $pollId): JsonResponse
    {
        $option = \App\Models\PollOption::findOrFail($request->option_id);
        if ($option->poll_id !== $pollId) abort(422);

        $option->increment('votes');
        return response()->json(['total' => $option->poll->options->sum('votes'), 'option_votes' => $option->votes]);
    }
}
