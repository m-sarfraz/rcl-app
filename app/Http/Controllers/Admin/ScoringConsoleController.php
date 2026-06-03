<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CricketMatch;
use App\Models\Innings;
use App\Models\Player;
use App\Models\PlayerEditionTeam;
use App\Services\ScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScoringConsoleController extends Controller
{
    public function __construct(private readonly ScoringService $scoring) {}

    public function console(CricketMatch $match)
    {
        if (!in_array($match->status, ['upcoming','live'])) {
            return redirect()->route('admin.matches.index')->with('error', 'Match is not scoreable.');
        }

        $match->load(['homeTeam','awayTeam','edition','innings.ballByBall']);

        $currentInnings = $match->innings()->where('is_completed', false)->latest()->first();

        $homePlayers = PlayerEditionTeam::where('team_id', $match->home_team_id)
            ->where('edition_id', $match->edition_id)->with('player')->get()->pluck('player');
        $awayPlayers = PlayerEditionTeam::where('team_id', $match->away_team_id)
            ->where('edition_id', $match->edition_id)->with('player')->get()->pluck('player');

        return view('admin.scoring.console', compact('match','currentInnings','homePlayers','awayPlayers'));
    }

    public function startInnings(Request $request, CricketMatch $match): JsonResponse
    {
        $request->validate([
            'batting_team_id' => 'required|exists:teams,id',
            'bowling_team_id' => 'required|exists:teams,id',
            'innings_number'  => 'required|integer|in:1,2',
        ]);

        if ($match->status === 'upcoming') {
            $match->update(['status' => 'live']);
        }

        $target  = null;
        if ($request->innings_number == 2) {
            $firstInnings = $match->innings()->where('innings_number', 1)->first();
            $target = $firstInnings ? $firstInnings->total_runs + 1 : null;
        }

        $innings = $this->scoring->startInnings(
            $match->id,
            $request->batting_team_id,
            $request->bowling_team_id,
            $request->innings_number,
            $target
        );

        return response()->json(['success' => true, 'innings' => $innings]);
    }

    public function recordBall(Request $request, CricketMatch $match, Innings $innings): JsonResponse
    {
        $request->validate([
            'batsman_id'      => 'required|exists:players,id',
            'bowler_id'       => 'required|exists:players,id',
            'runs_scored'     => 'required|integer|min:0|max:6',
            'extra_runs'      => 'required|integer|min:0',
            'is_wide'         => 'boolean',
            'is_no_ball'      => 'boolean',
            'is_bye'          => 'boolean',
            'is_leg_bye'      => 'boolean',
            'is_penalty'      => 'boolean',
            'is_wicket'       => 'boolean',
            'wicket_type'     => 'nullable|in:bowled,caught,run_out,lbw,stumped,hit_wicket,obstructing_field',
            'fielder_id'      => 'nullable|exists:players,id',
            'is_four'         => 'boolean',
            'is_six'          => 'boolean',
            'non_striker_id'  => 'nullable|exists:players,id',
            'comment'         => 'nullable|string|max:255',
        ]);

        $result = $this->scoring->recordDelivery($innings->id, $request->all());
        return response()->json($result);
    }

    public function completeMatch(CricketMatch $match): JsonResponse
    {
        $match = $this->scoring->completeMatch($match->id);
        return response()->json(['success' => true, 'match' => $match]);
    }

    public function getLiveState(CricketMatch $match): JsonResponse
    {
        return response()->json($this->scoring->getLiveState($match->id));
    }
}
