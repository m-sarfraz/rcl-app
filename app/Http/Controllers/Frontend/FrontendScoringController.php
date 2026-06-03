<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CricketMatch;
use App\Models\Innings;
use App\Models\PlayerEditionTeam;
use App\Models\SiteSetting;
use App\Services\ScoringService;
use App\Repositories\Interfaces\ScoringRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FrontendScoringController extends Controller
{
    public function __construct(
        private readonly ScoringService $scoring,
        private readonly ScoringRepositoryInterface $scoringRepo,
    ) {}

    /* ── Key check helper ─────────────────────────────── */
    private function hasAccess(): bool
    {
        return session('scoring_key_valid') === true;
    }

    /* ── GET /score  — key gate or match selector ──────── */
    public function index()
    {
        if (!$this->hasAccess()) {
            return view('frontend.scoring-key');
        }

        $matches = CricketMatch::whereIn('status', ['upcoming', 'live'])
            ->with(['homeTeam', 'awayTeam', 'edition'])
            ->orderByRaw("FIELD(status,'live','upcoming')")
            ->orderBy('scheduled_at')
            ->get();

        return view('frontend.scoring-select', compact('matches'));
    }

    /* ── POST /score/verify  — validate secret key ──────── */
    public function verifyKey(Request $request)
    {
        $request->validate(['key' => 'required|string']);

        $stored = SiteSetting::get('scoring_secret_key');

        if (!$stored) {
            return back()->with('error', 'Scoring console not configured. Ask admin to set a key.');
        }

        if ($request->input('key') !== $stored) {
            return back()->withInput()->with('error', 'Wrong secret key. Access denied.');
        }

        session(['scoring_key_valid' => true]);
        return redirect()->route('frontend.scoring')->with('success', 'Access granted!');
    }

    /* ── GET /score/lock  — clear session key ───────────── */
    public function lock()
    {
        session()->forget('scoring_key_valid');
        return redirect()->route('frontend.scoring');
    }

    /* ── GET /score/{match}  — mobile scoring console ───── */
    public function console(CricketMatch $match)
    {
        if (!$this->hasAccess()) {
            return redirect()->route('frontend.scoring');
        }

        if (!in_array($match->status, ['upcoming', 'live'])) {
            return redirect()->route('frontend.scoring')->with('error', 'Match is not scoreable.');
        }

        $match->load(['homeTeam', 'awayTeam', 'edition', 'innings.ballByBall']);

        $currentInnings = $match->innings()
            ->where('is_completed', false)
            ->latest()
            ->first();

        $homePlayers = PlayerEditionTeam::where('team_id', $match->home_team_id)
            ->where('edition_id', $match->edition_id)
            ->with('player')
            ->get()
            ->pluck('player')
            ->filter();

        $awayPlayers = PlayerEditionTeam::where('team_id', $match->away_team_id)
            ->where('edition_id', $match->edition_id)
            ->with('player')
            ->get()
            ->pluck('player')
            ->filter();

        return view('frontend.scoring-console', compact(
            'match', 'currentInnings', 'homePlayers', 'awayPlayers'
        ));
    }

    /* ── POST /score/{match}/start-innings ──────────────── */
    public function startInnings(Request $request, CricketMatch $match): JsonResponse
    {
        if (!$this->hasAccess()) return response()->json(['error' => 'Unauthorized'], 403);

        $request->validate([
            'batting_team_id' => 'required|exists:teams,id',
            'bowling_team_id' => 'required|exists:teams,id',
            'innings_number'  => 'required|integer|in:1,2',
        ]);

        if ($match->status === 'upcoming') {
            $match->update(['status' => 'live']);
        }

        $target = null;
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

    /* ── POST /score/{match}/innings/{innings}/ball ──────── */
    public function recordBall(Request $request, CricketMatch $match, Innings $innings): JsonResponse
    {
        if (!$this->hasAccess()) return response()->json(['error' => 'Unauthorized'], 403);

        $request->validate([
            'batsman_id'     => 'required|exists:players,id',
            'bowler_id'      => 'required|exists:players,id',
            'runs_scored'    => 'required|integer|min:0|max:6',
            'extra_runs'     => 'required|integer|min:0',
            'is_wide'        => 'boolean',
            'is_no_ball'     => 'boolean',
            'is_bye'         => 'boolean',
            'is_leg_bye'     => 'boolean',
            'is_wicket'      => 'boolean',
            'wicket_type'    => 'nullable|in:bowled,caught,run_out,lbw,stumped,hit_wicket,obstructing_field',
            'fielder_id'     => 'nullable|exists:players,id',
            'is_four'        => 'boolean',
            'is_six'         => 'boolean',
            'non_striker_id' => 'nullable|exists:players,id',
            'comment'        => 'nullable|string|max:255',
        ]);

        $result = $this->scoring->recordDelivery($innings->id, $request->all());
        Cache::forget("live_match_{$match->id}");

        return response()->json($result);
    }

    /* ── DELETE /score/{match}/innings/{innings}/undo ────── */
    public function undoBall(CricketMatch $match, Innings $innings): JsonResponse
    {
        if (!$this->hasAccess()) return response()->json(['error' => 'Unauthorized'], 403);

        $undone = $this->scoringRepo->undoLastBall($innings->id);
        Cache::forget("live_match_{$match->id}");

        return response()->json([
            'success' => $undone,
            'innings' => $innings->fresh(),
        ]);
    }

    /* ── POST /score/{match}/complete ───────────────────── */
    public function completeMatch(CricketMatch $match): JsonResponse
    {
        if (!$this->hasAccess()) return response()->json(['error' => 'Unauthorized'], 403);

        $result = $this->scoring->completeMatch($match->id);
        return response()->json(['success' => true, 'match' => $result]);
    }

    /* ── GET /score/{match}/live-state ──────────────────── */
    public function getLiveState(CricketMatch $match): JsonResponse
    {
        if (!$this->hasAccess()) return response()->json(['error' => 'Unauthorized'], 403);

        return response()->json($this->scoring->getLiveState($match->id));
    }
}
