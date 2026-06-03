<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\BannedBowler;
use App\Models\Edition;
use App\Models\Fine;
use App\Models\Sponsor;
use Illuminate\Support\Facades\DB;

class MoreController extends Controller
{
    public function bannedBowlers()
    {
        $bans = BannedBowler::with('player', 'issuedBy')
            ->orderByDesc('is_active')
            ->orderByDesc('banned_from')
            ->get();

        return view('frontend.banned_bowlers', compact('bans'));
    }

    public function index()
    {
        $currentEdition = Edition::where('is_current', '=', 1)->first();
        return view('frontend.more', compact('currentEdition'));
    }

    public function captains()
    {
        $currentEdition = Edition::where('is_current', '=', 1)->first();

        $teams = DB::table('teams')
            ->where('teams.is_active', '=', true)
            ->orderBy('teams.name')
            ->get();

        $captains = collect();
        if ($currentEdition) {
            $captains = DB::table('player_team_editions')
                ->join('players', 'players.id', '=', 'player_team_editions.player_id')
                ->join('teams', 'teams.id', '=', 'player_team_editions.team_id')
                ->where('player_team_editions.edition_id', '=', $currentEdition->id)
                ->where(function ($q) {
                    $q->where('player_team_editions.is_captain', '=', true)
                      ->orWhere('player_team_editions.is_vice_captain', '=', true);
                })
                ->select(
                    'teams.id as team_id', 'teams.name as team_name',
                    'teams.village_name', 'teams.short_code',
                    'teams.primary_color', 'teams.secondary_color',
                    'players.id as player_id', 'players.name as player_name',
                    'players.photo', 'players.role',
                    'player_team_editions.is_captain',
                    'player_team_editions.is_vice_captain'
                )
                ->orderBy('teams.name')
                ->orderByDesc('player_team_editions.is_captain')
                ->get()
                ->groupBy('team_id');
        }

        return view('frontend.captains', compact('teams', 'captains', 'currentEdition'));
    }

    public function sponsors()
    {
        $sponsors = Sponsor::active()->get()->groupBy('tier');
        return view('frontend.sponsors', compact('sponsors'));
    }

    public function teamFines()
    {
        $currentEdition = Edition::where('is_current', '=', 1)->first();

        $fines = Fine::with(['player', 'player.teams'])
            ->when($currentEdition, fn($q) => $q->where('edition_id', '=', $currentEdition->id))
            ->latest()
            ->get();

        $teamFines = collect();

        if ($currentEdition) {
            $roster = DB::table('player_team_editions')
                ->where('edition_id', '=', $currentEdition->id)
                ->join('teams', 'teams.id', '=', 'player_team_editions.team_id')
                ->select('player_team_editions.player_id', 'teams.name as team_name')
                ->get()
                ->keyBy('player_id');

            $grouped = $fines->groupBy(function ($fine) use ($roster) {
                $entry = $roster->get($fine->player_id);
                return $entry ? $entry->team_name : 'Unassigned';
            });

            $teamFines = $grouped->sortKeys();
        } else {
            $teamFines = collect();
        }

        return view('frontend.team_fines', compact('teamFines', 'currentEdition'));
    }
}
