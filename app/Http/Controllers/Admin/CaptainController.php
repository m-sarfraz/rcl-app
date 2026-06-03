<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Edition;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CaptainController extends Controller
{
    private function currentEdition(): ?Edition
    {
        return Edition::where('is_current', true)->first();
    }

    public function index()
    {
        $edition = $this->currentEdition();
        $teams = Team::where('is_active', true)->orderBy('name')->get();

        $rosters = [];
        if ($edition) {
            foreach ($teams as $team) {
                $players = DB::table('player_team_editions')
                    ->join('players', 'players.id', '=', 'player_team_editions.player_id')
                    ->where('player_team_editions.team_id', $team->id)
                    ->where('player_team_editions.edition_id', $edition->id)
                    ->select('players.id', 'players.name', 'players.role',
                             'player_team_editions.is_captain',
                             'player_team_editions.is_vice_captain')
                    ->orderByDesc('player_team_editions.is_captain')
                    ->orderByDesc('player_team_editions.is_vice_captain')
                    ->orderBy('players.name')
                    ->get();

                $rosters[$team->id] = [
                    'count'   => $players->count(),
                    'captain' => $players->firstWhere('is_captain', 1),
                    'vc'      => $players->firstWhere('is_vice_captain', 1),
                ];
            }
        }

        return view('admin.captains.index', compact('teams', 'edition', 'rosters'));
    }

    public function edit(Team $team)
    {
        $edition = $this->currentEdition();
        $players = $edition
            ? DB::table('player_team_editions')
                ->join('players', 'players.id', '=', 'player_team_editions.player_id')
                ->where('player_team_editions.team_id', $team->id)
                ->where('player_team_editions.edition_id', $edition->id)
                ->select('players.id', 'players.name', 'players.role',
                         'player_team_editions.is_captain',
                         'player_team_editions.is_vice_captain')
                ->orderBy('players.name')
                ->get()
            : collect();

        return view('admin.captains.edit', compact('team', 'edition', 'players'));
    }

    public function update(Request $request, Team $team)
    {
        $request->validate([
            'captain_id'    => 'required|exists:players,id',
            'vc_id'         => 'nullable|exists:players,id',
        ]);

        $edition = $this->currentEdition();
        if (!$edition) return back()->with('error', 'No active edition found.');

        $captainId = (int) $request->captain_id;
        $vcId      = $request->vc_id ? (int) $request->vc_id : null;

        if ($vcId && $vcId === $captainId) {
            return back()->withErrors(['vc_id' => 'Captain and Vice-Captain must be different players.']);
        }

        // Reset all captain/vc flags for this team in this edition
        DB::table('player_team_editions')
            ->where('team_id', $team->id)
            ->where('edition_id', $edition->id)
            ->update(['is_captain' => false, 'is_vice_captain' => false]);

        // Set new captain
        DB::table('player_team_editions')
            ->where('team_id', $team->id)
            ->where('edition_id', $edition->id)
            ->where('player_id', $captainId)
            ->update(['is_captain' => true]);

        // Set new VC
        if ($vcId) {
            DB::table('player_team_editions')
                ->where('team_id', $team->id)
                ->where('edition_id', $edition->id)
                ->where('player_id', $vcId)
                ->update(['is_vice_captain' => true]);
        }

        return redirect()->route('admin.captains.index')
            ->with('success', "Captain updated for {$team->name}.");
    }
}
