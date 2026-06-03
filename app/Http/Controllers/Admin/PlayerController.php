<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Edition;
use App\Models\Player;
use App\Models\Team;
use App\Repositories\Interfaces\PlayerRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlayerController extends Controller
{
    public function __construct(private readonly PlayerRepositoryInterface $players) {}

    public function index(Request $request)
    {
        $currentEdition = Edition::where('is_current', '=', 1)->first();
        $filters = $request->only('search', 'role', 'team_id');
        if ($currentEdition) {
            $filters['edition_id'] = $currentEdition->id;
        }
        $players = $this->players->paginate(20, $filters);
        $roles   = ['batsman','bowler','all_rounder','wicket_keeper'];
        $teams   = Team::where('is_active', '=', 1)->orderBy('name')->get();
        return view('admin.players.index', compact('players', 'roles', 'teams'));
    }

    public function create()
    {
        $teams    = Team::where('is_active', '=', 1)->orderBy('name')->get();
        $editions = Edition::orderByDesc('edition_number')->get();
        return view('admin.players.create', compact('teams','editions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                  => 'required|string|max:255',
            'father_name'           => 'nullable|string|max:255',
            'jersey_number'         => 'nullable|string|max:10',
            'photo'                 => 'nullable|image|max:1024',
            'date_of_birth'         => 'nullable|date',
            'role'                  => 'required|in:batsman,bowler,all_rounder,wicket_keeper',
            'batting_style'         => 'required|in:right_hand,left_hand',
            'bowling_style'         => 'required|in:right_arm_fast,right_arm_medium,right_arm_spin,left_arm_fast,left_arm_medium,left_arm_spin,none',
            'bowling_action_status' => 'required|in:legal,flagged,banned',
            'phone'                 => 'nullable|string|max:20',
            'bio'                   => 'nullable|string',
            'team_id'               => 'nullable|exists:teams,id',
            'edition_id'            => 'nullable|exists:editions,id',
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('players/photos', 'public');
        }

        $player = $this->players->create($data);

        if ($request->filled('team_id') && $request->filled('edition_id')) {
            $this->players->assignToTeam($player->id, $request->team_id, $request->edition_id);
        }

        return redirect()->route('admin.players.index')->with('success', 'Player created.');
    }

    public function show(Player $player)
    {
        $player->load(['teams','fines','suspensions','editionStats.edition','editionStats.team']);
        return view('admin.players.show', compact('player'));
    }

    public function edit(Player $player)
    {
        $teams    = Team::where('is_active', '=', 1)->orderBy('name')->get();
        $editions = Edition::orderByDesc('edition_number')->get();
        return view('admin.players.edit', compact('player','teams','editions'));
    }

    public function update(Request $request, Player $player)
    {
        $data = $request->validate([
            'name'                  => 'required|string|max:255',
            'father_name'           => 'nullable|string|max:255',
            'jersey_number'         => 'nullable|string|max:10',
            'photo'                 => 'nullable|image|max:1024',
            'date_of_birth'         => 'nullable|date',
            'role'                  => 'required|in:batsman,bowler,all_rounder,wicket_keeper',
            'batting_style'         => 'required|in:right_hand,left_hand',
            'bowling_style'         => 'required|in:right_arm_fast,right_arm_medium,right_arm_spin,left_arm_fast,left_arm_medium,left_arm_spin,none',
            'bowling_action_status' => 'required|in:legal,flagged,banned',
            'phone'                 => 'nullable|string|max:20',
            'bio'                   => 'nullable|string',
            'is_active'             => 'boolean',
        ]);

        if ($request->hasFile('photo')) {
            if ($player->photo) Storage::disk('public')->delete($player->photo);
            $data['photo'] = $request->file('photo')->store('players/photos', 'public');
        }

        $this->players->update($player->id, $data);
        return redirect()->route('admin.players.index')->with('success', 'Player updated.');
    }

    public function destroy(Player $player)
    {
        $this->players->delete($player->id);
        return back()->with('success', 'Player deleted.');
    }
}
