<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BallByBallLog;
use App\Models\CricketMatch;
use App\Models\Innings;
use App\Models\MatchSquad;
use App\Models\Player;
use App\Models\Team;
use App\Services\CalculationEngineService;
use App\Services\MatchStatisticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MatchManageController extends Controller
{
    public function __construct(
        private readonly MatchStatisticsService   $stats,
        private readonly CalculationEngineService $calculator,
    ) {}

    /**
     * Advanced Match & Innings Console
     */
    public function index(CricketMatch $match, Request $request)
    {
        $match->load([
            'edition',
            'homeTeam',
            'awayTeam',
            'winner',
            'tossWinner',
            'manOfMatch',
            'innings' => fn ($q) => $q->orderBy('innings_number'),
            'innings.battingTeam',
            'innings.bowlingTeam',
            'innings.battingScorecards.player',
            'innings.bowlingScorecards.player',
        ]);

        $inningsList = $match->innings;

        // Determine active innings to view/edit
        $activeInningsId = $request->query('innings_id');
        $activeInnings = $activeInningsId
            ? $inningsList->firstWhere('id', (int) $activeInningsId)
            : ($inningsList->where('is_completed', false)->sortByDesc('innings_number')->first() ?? $inningsList->last());

        $ballsByOver = collect();
        $selectedOver = $request->query('over');
        $allDeliveries = collect();
        $battingPlayers = collect();
        $bowlingPlayers = collect();
        $fieldingPlayers = collect();

        if ($activeInnings) {
            $allDeliveries = BallByBallLog::where('innings_id', $activeInnings->id)
                ->with(['bowler', 'batsman', 'nonStriker', 'fielder', 'outPlayer'])
                ->orderBy('over_number')
                ->orderBy('ball_number')
                ->orderBy('id')
                ->get();

            $ballsByOver = $allDeliveries->groupBy('over_number');

            // Load Squad or Team players for Batting and Bowling
            $battingPlayers = $this->getTeamPlayers($match->id, (int) $activeInnings->batting_team_id);
            $bowlingPlayers = $this->getTeamPlayers($match->id, (int) $activeInnings->bowling_team_id);
            $fieldingPlayers = $bowlingPlayers;
        }

        $allTeams = Team::where('is_active', true)->orderBy('name')->get();

        return view('admin.matches.manage', compact(
            'match',
            'inningsList',
            'activeInnings',
            'allDeliveries',
            'ballsByOver',
            'selectedOver',
            'battingPlayers',
            'bowlingPlayers',
            'fieldingPlayers',
            'allTeams',
        ));
    }

    /**
     * Update match level status, toss, result, etc.
     */
    public function updateMatch(Request $request, CricketMatch $match)
    {
        $data = $request->validate([
            'status'                 => 'required|in:upcoming,live,completed,abandoned,postponed',
            'overs_per_side'         => 'required|integer|min:1|max:50',
            'venue'                  => 'nullable|string|max:255',
            'toss_winner_id'         => 'nullable|exists:teams,id',
            'toss_decision'          => 'nullable|in:bat,field',
            'winner_id'              => 'nullable|exists:teams,id',
            'result_type'            => 'nullable|in:runs,wickets,tie,no_result,super_over',
            'result_margin'          => 'nullable|integer',
            'result_description'     => 'nullable|string',
            'man_of_match_player_id' => 'nullable|exists:players,id',
            'notes'                  => 'nullable|string',
        ]);

        $match->update($data);
        Cache::forget("live_match_{$match->id}");

        return back()->with('success', 'Match settings and status updated successfully.');
    }

    /**
     * Create a new innings for this match (e.g. Inning 2 or Super Over)
     */
    public function storeInnings(Request $request, CricketMatch $match)
    {
        $data = $request->validate([
            'innings_number'  => 'required|integer|min:1|max:4',
            'batting_team_id' => 'required|exists:teams,id',
            'bowling_team_id' => 'required|exists:teams,id|different:batting_team_id',
            'target'          => 'nullable|integer|min:1',
        ]);

        // Auto-mark previous innings as completed
        Innings::where('match_id', $match->id)
            ->where('innings_number', '<', $data['innings_number'])
            ->where('is_completed', false)
            ->update(['is_completed' => true]);

        $innings = Innings::create([
            'match_id'        => $match->id,
            'innings_number'  => $data['innings_number'],
            'batting_team_id' => $data['batting_team_id'],
            'bowling_team_id' => $data['bowling_team_id'],
            'target'          => $data['target'],
            'is_completed'    => false,
        ]);

        Cache::forget("live_match_{$match->id}");

        return redirect()->route('admin.matches.manage', [
            'match'      => $match->id,
            'innings_id' => $innings->id,
        ])->with('success', "Innings {$innings->innings_number} created.");
    }

    /**
     * Update innings metadata (completion status, target, teams)
     */
    public function updateInnings(Request $request, CricketMatch $match, Innings $innings)
    {
        $data = $request->validate([
            'target'          => 'nullable|integer|min:1',
            'is_completed'    => 'required|boolean',
            'batting_team_id' => 'required|exists:teams,id',
            'bowling_team_id' => 'required|exists:teams,id|different:batting_team_id',
        ]);

        $innings->update($data);
        $this->stats->rebuildInnings($innings);
        Cache::forget("live_match_{$match->id}");

        return back()->with('success', "Innings {$innings->innings_number} settings updated and stats rebuilt.");
    }

    /**
     * Add a ball / delivery to an innings
     */
    public function storeBall(Request $request, CricketMatch $match, Innings $innings)
    {
        $data = $request->validate([
            'over_number'    => 'required|integer|min:1',
            'ball_number'    => 'required|integer|min:1',
            'bowler_id'      => 'required|exists:players,id',
            'batsman_id'     => 'required|exists:players,id',
            'non_striker_id' => 'nullable|exists:players,id|different:batsman_id',
            'runs_scored'    => 'required|integer|min:0|max:6',
            'extra_type'     => 'required|in:none,wide,no_ball,bye,leg_bye,penalty',
            'extra_runs'     => 'nullable|integer|min:0|max:10',
            'is_wicket'      => 'nullable|boolean',
            'wicket_type'    => 'nullable|required_if:is_wicket,1|in:bowled,caught,run_out,lbw,stumped,hit_wicket,obstructing_field,handled_ball,timed_out',
            'out_player_id'  => 'nullable|required_if:is_wicket,1|exists:players,id',
            'fielder_id'     => 'nullable|exists:players,id',
            'commentary'     => 'nullable|string',
        ]);

        $isWide    = ($data['extra_type'] === 'wide');
        $isNoBall  = ($data['extra_type'] === 'no_ball');
        $isBye     = ($data['extra_type'] === 'bye');
        $isLegBye  = ($data['extra_type'] === 'leg_bye');
        $isPenalty = ($data['extra_type'] === 'penalty');

        $extraRuns = (int) ($data['extra_runs'] ?? ($isWide || $isNoBall ? 1 : 0));
        if ($data['extra_type'] === 'none') {
            $extraRuns = 0;
        }

        $runsScored = (int) $data['runs_scored'];
        $isWicket   = ! empty($data['is_wicket']);

        DB::transaction(function () use ($match, $innings, $data, $isWide, $isNoBall, $isBye, $isLegBye, $isPenalty, $extraRuns, $runsScored, $isWicket) {
            BallByBallLog::create([
                'client_uuid'                => (string) Str::uuid(),
                'match_id'                   => $match->id,
                'innings_id'                 => $innings->id,
                'over_number'                => (int) $data['over_number'],
                'ball_number'                => (int) $data['ball_number'],
                'bowler_id'                  => (int) $data['bowler_id'],
                'batsman_id'                 => (int) $data['batsman_id'],
                'non_striker_id'             => ! empty($data['non_striker_id']) ? (int) $data['non_striker_id'] : null,
                'runs_scored'                => $runsScored,
                'extra_runs'                 => $extraRuns,
                'is_wide'                    => $isWide,
                'is_no_ball'                 => $isNoBall,
                'is_bye'                     => $isBye,
                'is_leg_bye'                 => $isLegBye,
                'is_penalty'                 => $isPenalty,
                'is_four'                    => ($runsScored === 4 && ! $isBye && ! $isLegBye),
                'is_six'                     => ($runsScored === 6 && ! $isBye && ! $isLegBye),
                'is_wicket'                  => $isWicket,
                'wicket_type'                => $isWicket ? $data['wicket_type'] : null,
                'out_player_id'              => $isWicket ? (int) $data['out_player_id'] : null,
                'fielder_id'                 => (! empty($data['fielder_id'])) ? (int) $data['fielder_id'] : null,
                'commentary'                 => $data['commentary'] ?? null,
                'batting_team_score_after'   => 0, // rebuildInnings updates running totals
                'batting_team_wickets_after' => 0,
            ]);

            $this->stats->rebuildInnings($innings);

            // If Inning 1 changed, auto-update Inning 2 target if needed
            if ($innings->innings_number === 1) {
                $inn2 = Innings::where('match_id', $match->id)->where('innings_number', 2)->first();
                if ($inn2) {
                    $inn2->update(['target' => $innings->fresh()->total_runs + 1]);
                    $this->stats->rebuildInnings($inn2);
                }
            }
        });

        Cache::forget("live_match_{$match->id}");

        return redirect()->route('admin.matches.manage', [
            'match'      => $match->id,
            'innings_id' => $innings->id,
            'over'       => $data['over_number'],
        ])->with('success', 'Delivery recorded and scorecards synchronized.');
    }

    /**
     * Update an existing delivery
     */
    public function updateBall(Request $request, CricketMatch $match, Innings $innings, BallByBallLog $ball)
    {
        $data = $request->validate([
            'over_number'    => 'required|integer|min:1',
            'ball_number'    => 'required|integer|min:1',
            'bowler_id'      => 'required|exists:players,id',
            'batsman_id'     => 'required|exists:players,id',
            'non_striker_id' => 'nullable|exists:players,id|different:batsman_id',
            'runs_scored'    => 'required|integer|min:0|max:6',
            'extra_type'     => 'required|in:none,wide,no_ball,bye,leg_bye,penalty',
            'extra_runs'     => 'nullable|integer|min:0|max:10',
            'is_wicket'      => 'nullable|boolean',
            'wicket_type'    => 'nullable|required_if:is_wicket,1|in:bowled,caught,run_out,lbw,stumped,hit_wicket,obstructing_field,handled_ball,timed_out',
            'out_player_id'  => 'nullable|required_if:is_wicket,1|exists:players,id',
            'fielder_id'     => 'nullable|exists:players,id',
            'commentary'     => 'nullable|string',
        ]);

        $isWide    = ($data['extra_type'] === 'wide');
        $isNoBall  = ($data['extra_type'] === 'no_ball');
        $isBye     = ($data['extra_type'] === 'bye');
        $isLegBye  = ($data['extra_type'] === 'leg_bye');
        $isPenalty = ($data['extra_type'] === 'penalty');

        $extraRuns = (int) ($data['extra_runs'] ?? ($isWide || $isNoBall ? 1 : 0));
        if ($data['extra_type'] === 'none') {
            $extraRuns = 0;
        }

        $runsScored = (int) $data['runs_scored'];
        $isWicket   = ! empty($data['is_wicket']);

        DB::transaction(function () use ($match, $innings, $ball, $data, $isWide, $isNoBall, $isBye, $isLegBye, $isPenalty, $extraRuns, $runsScored, $isWicket) {
            $ball->update([
                'over_number'    => (int) $data['over_number'],
                'ball_number'    => (int) $data['ball_number'],
                'bowler_id'      => (int) $data['bowler_id'],
                'batsman_id'     => (int) $data['batsman_id'],
                'non_striker_id' => ! empty($data['non_striker_id']) ? (int) $data['non_striker_id'] : null,
                'runs_scored'    => $runsScored,
                'extra_runs'     => $extraRuns,
                'is_wide'        => $isWide,
                'is_no_ball'     => $isNoBall,
                'is_bye'         => $isBye,
                'is_leg_bye'     => $isLegBye,
                'is_penalty'     => $isPenalty,
                'is_four'        => ($runsScored === 4 && ! $isBye && ! $isLegBye),
                'is_six'         => ($runsScored === 6 && ! $isBye && ! $isLegBye),
                'is_wicket'      => $isWicket,
                'wicket_type'    => $isWicket ? $data['wicket_type'] : null,
                'out_player_id'  => $isWicket ? (int) $data['out_player_id'] : null,
                'fielder_id'     => (! empty($data['fielder_id'])) ? (int) $data['fielder_id'] : null,
                'commentary'     => $data['commentary'] ?? null,
            ]);

            $this->stats->rebuildInnings($innings);

            if ($innings->innings_number === 1) {
                $inn2 = Innings::where('match_id', $match->id)->where('innings_number', 2)->first();
                if ($inn2) {
                    $inn2->update(['target' => $innings->fresh()->total_runs + 1]);
                    $this->stats->rebuildInnings($inn2);
                }
            }
        });

        Cache::forget("live_match_{$match->id}");

        return redirect()->route('admin.matches.manage', [
            'match'      => $match->id,
            'innings_id' => $innings->id,
            'over'       => $data['over_number'],
        ])->with('success', 'Delivery updated and scorecards synchronized.');
    }

    /**
     * Delete a delivery
     */
    public function destroyBall(CricketMatch $match, Innings $innings, BallByBallLog $ball)
    {
        $overNumber = $ball->over_number;

        DB::transaction(function () use ($match, $innings, $ball) {
            $ball->delete();
            $this->stats->rebuildInnings($innings);

            if ($innings->innings_number === 1) {
                $inn2 = Innings::where('match_id', $match->id)->where('innings_number', 2)->first();
                if ($inn2) {
                    $inn2->update(['target' => $innings->fresh()->total_runs + 1]);
                    $this->stats->rebuildInnings($inn2);
                }
            }
        });

        Cache::forget("live_match_{$match->id}");

        return redirect()->route('admin.matches.manage', [
            'match'      => $match->id,
            'innings_id' => $innings->id,
            'over'       => $overNumber,
        ])->with('success', 'Delivery deleted and scorecards rebuilt.');
    }

    /**
     * Rebuild entire match (both innings, running totals, scorecards, targets)
     */
    public function rebuildInnings(CricketMatch $match, Innings $innings)
    {
        $this->stats->rebuildInnings($innings);

        if ($innings->innings_number === 1) {
            $inn2 = Innings::where('match_id', $match->id)->where('innings_number', 2)->first();
            if ($inn2) {
                $inn2->update(['target' => $innings->fresh()->total_runs + 1]);
                $this->stats->rebuildInnings($inn2);
            }
        }

        Cache::forget("live_match_{$match->id}");

        return back()->with('success', "Innings {$innings->innings_number} statistics rebuilt and synchronized.");
    }

    public function rebuildMatch(CricketMatch $match)
    {
        foreach ($match->innings as $inn) {
            $this->stats->rebuildInnings($inn);
        }

        $inn1 = $match->innings()->where('innings_number', 1)->first();
        $inn2 = $match->innings()->where('innings_number', 2)->first();

        if ($inn1 && $inn2) {
            $inn2->update(['target' => $inn1->total_runs + 1]);
            $this->stats->rebuildInnings($inn2);
        }

        Cache::forget("live_match_{$match->id}");

        return back()->with('success', 'Entire match statistics, scorecards, and targets synchronized.');
    }

    /**
     * Helper to retrieve team players: priority squad, then full roster
     */
    private function getTeamPlayers(int $matchId, int $teamId)
    {
        $squadPlayerIds = MatchSquad::where('match_id', $matchId)
            ->where('team_id', $teamId)
            ->orderBy('batting_order')
            ->pluck('player_id');

        if ($squadPlayerIds->isNotEmpty()) {
            return Player::whereIn('id', $squadPlayerIds)
                ->orderBy('name')
                ->get();
        }

        return Team::find($teamId)?->players()->orderBy('name')->get() ?? collect();
    }
}
