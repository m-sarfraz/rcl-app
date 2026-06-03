<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Edition;
use App\Models\Fine;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FineController extends Controller
{
    public function index(Request $request)
    {
        $fines = Fine::with(['team', 'player', 'edition', 'issuedBy'])
            ->when($request->status, fn($q) => $q->where('status', '=', $request->status))
            ->when($request->edition_id, fn($q) => $q->where('edition_id', '=', $request->edition_id))
            ->latest()
            ->paginate(20);

        $editions = Edition::orderByDesc('edition_number')->get();
        return view('admin.fines.index', compact('fines', 'editions'));
    }

    public function create()
    {
        $teams    = Team::where('is_active', '=', 1)->orderBy('name')->get();
        $editions = Edition::orderByDesc('edition_number')->get();
        $currentEdition = Edition::where('is_current', '=', 1)->first();
        return view('admin.fines.create', compact('teams', 'editions', 'currentEdition'));
    }

    public function playersByTeam(Request $request, Team $team)
    {
        $editionId = $request->integer('edition_id');

        $players = DB::table('player_team_editions')
            ->join('players', 'players.id', '=', 'player_team_editions.player_id')
            ->where('player_team_editions.team_id', '=', $team->id)
            ->where('player_team_editions.edition_id', '=', $editionId)
            ->select('players.id', 'players.name', 'players.father_name', 'players.jersey_number',
                     'player_team_editions.is_captain', 'player_team_editions.is_vice_captain')
            ->orderBy('players.name')
            ->get();

        return response()->json($players);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'team_id'        => 'required|exists:teams,id',
            'player_id'      => 'nullable|exists:players,id',
            'edition_id'     => 'required|exists:editions,id',
            'amount'         => 'required|numeric|min:1',
            'description'    => 'required|string|max:500',
            'violation_type' => 'required|in:code_of_conduct,chucking,disciplinary_card,misconduct,other',
            'match_id'       => 'nullable|exists:cricket_matches,id',
            'due_date'       => 'nullable|date',
            'admin_notes'    => 'nullable|string',
        ]);

        $data['issued_by'] = Auth::id();
        $data['status']    = 'unpaid';
        $data['card_type'] = 'none';

        Fine::create($data);
        return redirect()->route('admin.fines.index')->with('success', 'Fine issued.');
    }

    public function updateStatus(Request $request, Fine $fine)
    {
        $data = $request->validate([
            'status'      => 'required|in:paid,unpaid,waived',
            'paid_date'   => 'nullable|date',
            'admin_notes' => 'nullable|string',
        ]);

        $fine->update($data);
        return back()->with('success', 'Fine status updated.');
    }

    public function destroy(Fine $fine)
    {
        $fine->delete();
        return back()->with('success', 'Fine deleted.');
    }
}
