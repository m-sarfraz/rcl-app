<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Http\Resources\EditionResource;
use App\Http\Resources\MatchResource;
use App\Http\Resources\PollResource;
use App\Http\Resources\TeamResource;
use App\Http\Resources\VccMemberResource;
use App\Models\Banner;
use App\Models\CricketMatch;
use App\Models\Edition;
use App\Models\Notification;
use App\Models\Poll;
use App\Models\SiteSetting;
use App\Models\Team;
use App\Models\VccCabinet;
use App\Services\ScoringAccessService;
use Illuminate\Http\JsonResponse;
use App\Support\ApiResponse;

class HomeController extends Controller
{
    /** One round-trip that fills the entire home screen. */
    public function index(): JsonResponse
    {
        $currentEdition = Edition::where('is_current', true)
            ->withCount(['matches', 'teams'])
            ->first()
            ?? Edition::orderByDesc('edition_number')->withCount(['matches', 'teams'])->first();

        $withTeams = ['homeTeam', 'awayTeam', 'edition'];

        $live = CricketMatch::with([...$withTeams, 'innings'])
            ->where('status', 'live')
            ->orderBy('scheduled_at')
            ->limit(5)->get();

        $upcoming = CricketMatch::with($withTeams)
            ->where('status', 'upcoming')
            ->orderBy('scheduled_at')
            ->orderByRaw('CAST(match_number AS UNSIGNED)')
            ->limit(5)->get();

        $recent = CricketMatch::with([...$withTeams, 'winner', 'innings'])
            ->where('status', 'completed')
            ->orderByDesc('scheduled_at')
            ->limit(5)->get();

        $poll = Poll::where('is_active', true)
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))
            ->with(['options' => fn ($q) => $q->withCount('votes')->orderBy('display_order')])
            ->latest()->first();

        return ApiResponse::success([
            'current_edition'  => $currentEdition ? new EditionResource($currentEdition) : null,
            'live_matches'     => MatchResource::collection($live),
            'upcoming_matches' => MatchResource::collection($upcoming),
            'recent_matches'   => MatchResource::collection($recent),
            'active_poll'      => $poll ? new PollResource($poll) : null,
            'teams'            => TeamResource::collection(Team::active()->orderBy('name')->get()),
            'editions'         => EditionResource::collection(
                Edition::withCount(['matches', 'teams'])->orderByDesc('edition_number')->limit(6)->get()
            ),
            'vcc_members'      => VccMemberResource::collection(VccCabinet::active()->limit(8)->get()),
            'banners'          => BannerResource::collection(Banner::active()->get()),
            'ticker'           => $this->tickerItems(),
        ]);
    }

    public function ticker(): JsonResponse
    {
        return ApiResponse::success($this->tickerItems());
    }

    /** Public app configuration — nothing secret ever goes in here. */
    public function settings(ScoringAccessService $access): JsonResponse
    {
        return ApiResponse::success([
            'league_name'         => 'Royal Champions League',
            'governing_body'      => 'Village Cricket Council',
            'ball_fraction'       => 0.17,
            'ball_fraction_note'  => 'VCC house rule: one ball counts as 0.17 of an over.',
            'scoring_configured'  => $access->isConfigured(),
            'passkey_length'      => 10,
            'meeting_notice'      => SiteSetting::get('meeting_content', ''),
            'current_edition_id'  => Edition::where('is_current', true)->value('id'),
        ]);
    }

    /** @return array<int, array{id:int, message:string, type:string}> */
    private function tickerItems(): array
    {
        return Notification::where('is_active', true)
            ->where('is_ticker', true)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->latest()->limit(10)
            ->get(['id', 'message', 'type'])
            ->map(fn ($n) => [
                'id'      => $n->id,
                'message' => $n->message,
                'type'    => $n->type,
            ])->all();
    }
}
