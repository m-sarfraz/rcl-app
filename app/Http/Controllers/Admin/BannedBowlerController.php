<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BannedBowler;
use App\Models\Player;
use Illuminate\Http\Request;

class BannedBowlerController extends Controller
{
    public function index()
    {
        $bans = BannedBowler::with(['player', 'issuedBy'])
            ->latest()
            ->paginate(20);

        return view('admin.banned_bowlers.index', compact('bans'));
    }

    public function create()
    {
        $players = Player::where('is_active', true)->orderBy('name')->get();
        return view('admin.banned_bowlers.create', compact('players'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'player_id'    => 'required|exists:players,id',
            'reason'       => 'required|string|max:1000',
            'banned_from'  => 'required|date',
            'banned_until' => 'nullable|date|after:banned_from',
            'notes'        => 'nullable|string|max:2000',
        ]);

        $data['issued_by'] = auth()->id();
        $data['is_active'] = true;

        BannedBowler::create($data);

        Player::find($data['player_id'])->update(['bowling_action_status' => 'banned']);

        return redirect()->route('admin.banned-bowlers.index')->with('success', 'Bowler banned successfully.');
    }

    public function toggle(BannedBowler $bannedBowler)
    {
        $bannedBowler->update(['is_active' => !$bannedBowler->is_active]);

        $activeBansExist = BannedBowler::where('player_id', $bannedBowler->player_id)
            ->where('is_active', true)
            ->exists();

        $bannedBowler->player->update([
            'bowling_action_status' => $activeBansExist ? 'banned' : 'legal',
        ]);

        return back()->with('success', 'Ban status updated.');
    }

    public function destroy(BannedBowler $bannedBowler)
    {
        $playerId = $bannedBowler->player_id;
        $bannedBowler->delete();

        $activeBansExist = BannedBowler::where('player_id', $playerId)
            ->where('is_active', true)
            ->exists();

        Player::find($playerId)->update([
            'bowling_action_status' => $activeBansExist ? 'banned' : 'legal',
        ]);

        return back()->with('success', 'Ban record deleted.');
    }
}
