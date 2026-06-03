<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Edition;
use App\Models\Player;
use App\Models\PlayerEditionStat;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function index(Request $request)
    {
        $editionId = $request->edition_id ?? optional(Edition::where('is_current',true)->first())->id;

        $topBatsmen   = PlayerEditionStat::where('edition_id', $editionId)->with('player','team')->orderByDesc('total_runs')->limit(20)->get();
        $topBowlers   = PlayerEditionStat::where('edition_id', $editionId)->with('player','team')->orderByDesc('total_wickets')->limit(20)->get();
        $topBoundaries= PlayerEditionStat::where('edition_id', $editionId)->with('player','team')->orderByDesc('total_fours')->limit(10)->get();
        $topSixes     = PlayerEditionStat::where('edition_id', $editionId)->with('player','team')->orderByDesc('total_sixes')->limit(10)->get();
        $mvps         = PlayerEditionStat::where('edition_id', $editionId)->with('player','team')->orderByDesc('mvp_count')->limit(10)->get();

        $editions = Edition::orderByDesc('edition_number')->get();
        return view('frontend.stats', compact('topBatsmen','topBowlers','topBoundaries','topSixes','mvps','editions','editionId'));
    }

    public function player(Player $player)
    {
        $player->load(['editionStats.edition','editionStats.team','teams','fines' => fn($q) => $q->where('status','unpaid')]);
        return view('frontend.player', compact('player'));
    }
}
