<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\CricketMatch;
use App\Models\Edition;
use App\Models\Notification;
use App\Models\Poll;
use App\Models\Team;
use App\Models\SiteSetting;
use App\Models\VccCabinet;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    public function index()
    {
        $currentEdition = Edition::where('is_current', '=', 1)->first();
        $currentEdition?->loadCount(['matches','teams']);

        $liveMatches = CricketMatch::with(['homeTeam','awayTeam','innings'])
            ->where('status', 'live')
            ->latest()->limit(3)->get();

        $upcomingMatches = CricketMatch::with(['homeTeam','awayTeam'])
            ->where('status', 'upcoming')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')->limit(5)->get();

        $recentMatches = CricketMatch::with(['homeTeam','awayTeam','winner'])
            ->where('status', 'completed')
            ->latest()->limit(5)->get();

        $activePoll = Poll::where('is_active', '=', 1)
            ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))
            ->with(['options' => fn($q) => $q->orderBy('display_order')])
            ->latest()->first();
        $activePoll?->options->each->loadCount('votes');

        $banners    = Banner::active()->get();
        $teams      = Team::where('is_active', '=', 1)->orderBy('name')->get();
        $editions   = Edition::orderByDesc('edition_number')->limit(6)->get();
        $editions->loadCount(['matches','teams']);
        $vccMembers    = VccCabinet::active()->limit(4)->get();
        $meetingContent = SiteSetting::get('meeting_content', '');

        return view('frontend.home', compact(
            'currentEdition','liveMatches','upcomingMatches','recentMatches','activePoll',
            'banners','teams','editions','vccMembers','meetingContent'
        ));
    }

    public function ticker(): JsonResponse
    {
        $items = Notification::where('is_active', true)
            ->where('is_ticker', true)
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->latest()->limit(10)->pluck('message');

        return response()->json($items);
    }
}
