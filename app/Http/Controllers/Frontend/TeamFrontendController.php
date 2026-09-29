<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Edition;
use App\Models\PlayerEditionStat;
use App\Models\Team;
use Illuminate\Support\Facades\DB;

class TeamFrontendController extends Controller
{
    public function index()
    {
        $teams = Team::active()->orderBy('name')->get();
        $currentEdition = Edition::current()->first();
        return view('frontend.teams', compact('teams', 'currentEdition'));
    }

    public function show(Team $team)
    {
        $currentEdition = Edition::current()->first();

        $players = collect();
        if ($currentEdition) {
            $players = DB::table('player_team_editions')
                ->join('players', 'players.id', '=', 'player_team_editions.player_id')
                ->where('player_team_editions.team_id', '=', $team->id)
                ->where('player_team_editions.edition_id', '=', $currentEdition->id)
                ->select(
                    'players.id', 'players.name', 'players.father_name', 'players.jersey_number',
                    'players.role', 'players.batting_style', 'players.bowling_style',
                    'players.photo', 'players.phone', 'players.bio',
                    'player_team_editions.is_captain',
                    'player_team_editions.is_vice_captain'
                )
                ->orderByDesc('player_team_editions.is_captain')
                ->orderByDesc('player_team_editions.is_vice_captain')
                ->orderBy('players.name')
                ->get();
        }

        $stats = collect();
        if ($currentEdition) {
            $stats = PlayerEditionStat::query()
                ->where('team_id', '=', $team->id)
                ->where('edition_id', '=', $currentEdition->id)
                ->get()
                ->keyBy('player_id');
        }

        return view('frontend.team_detail', compact('team', 'players', 'currentEdition', 'stats'));
    }
}
