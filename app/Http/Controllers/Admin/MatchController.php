<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CricketMatch;
use App\Models\Edition;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    public function index(Request $request)
    {
        $matches = CricketMatch::with(['edition','homeTeam','awayTeam','winner'])
            ->when($request->edition_id, fn($q) => $q->where('edition_id', $request->edition_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderByDesc('scheduled_at')
            ->paginate(20);

        $editions = Edition::orderByDesc('edition_number')->get();
        return view('admin.matches.index', compact('matches','editions'));
    }

    public function create()
    {
        $editions = Edition::orderByDesc('edition_number')->get();
        $teams    = Team::where('is_active', true)->orderBy('name')->get();
        $officials = User::whereHas('role', fn($q) => $q->whereIn('name', ['umpire','scorer']))->get();
        return view('admin.matches.create', compact('editions','teams','officials'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'edition_id'    => 'required|exists:editions,id',
            'home_team_id'  => 'required|exists:teams,id|different:away_team_id',
            'away_team_id'  => 'required|exists:teams,id',
            'match_number'  => 'required|string|max:20',
            'match_type'    => 'required|in:group,quarter_final,semi_final,final',
            'venue'         => 'required|string|max:255',
            'scheduled_at'  => 'required|date',
            'overs_per_side'=> 'required|integer|min:1|max:50',
            'umpire1_id'    => 'nullable|exists:users,id',
            'umpire2_id'    => 'nullable|exists:users,id',
            'scorer_id'     => 'nullable|exists:users,id',
            'notes'         => 'nullable|string',
        ]);

        CricketMatch::create($data);
        return redirect()->route('admin.matches.index')->with('success', 'Match scheduled.');
    }

    public function edit(CricketMatch $match)
    {
        $editions  = Edition::orderByDesc('edition_number')->get();
        $teams     = Team::where('is_active', true)->orderBy('name')->get();
        $officials = User::whereHas('role', fn($q) => $q->whereIn('name', ['umpire','scorer']))->get();
        return view('admin.matches.edit', compact('match','editions','teams','officials'));
    }

    public function update(Request $request, CricketMatch $match)
    {
        $data = $request->validate([
            'home_team_id'  => 'required|exists:teams,id',
            'away_team_id'  => 'required|exists:teams,id',
            'match_number'  => 'required|string|max:20',
            'match_type'    => 'required|in:group,quarter_final,semi_final,final',
            'venue'         => 'required|string|max:255',
            'scheduled_at'  => 'required|date',
            'overs_per_side'=> 'required|integer|min:1',
            'status'        => 'required|in:upcoming,live,completed,abandoned,postponed',
            'umpire1_id'    => 'nullable|exists:users,id',
            'umpire2_id'    => 'nullable|exists:users,id',
            'scorer_id'     => 'nullable|exists:users,id',
        ]);

        $match->update($data);
        return redirect()->route('admin.matches.index')->with('success', 'Match updated.');
    }

    public function result(Request $request, CricketMatch $match)
    {
        $data = $request->validate([
            'winner_id'          => 'nullable|exists:teams,id',
            'result_type'        => 'required|in:runs,wickets,tie,no_result,super_over',
            'result_margin'      => 'nullable|integer',
            'result_description' => 'nullable|string',
            'man_of_match_player_id' => 'nullable|exists:players,id',
        ]);

        $match->update(array_merge($data, ['status' => 'completed']));
        return back()->with('success', 'Result recorded.');
    }

    public function destroy(CricketMatch $match)
    {
        $match->delete();
        return back()->with('success', 'Match deleted.');
    }
}
