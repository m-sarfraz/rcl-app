<?php

use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\EmbedController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DemeritPointController;
use App\Http\Controllers\Admin\EditionController;
use App\Http\Controllers\Admin\FineController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\MatchController;
use App\Http\Controllers\Admin\MatchManageController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PlayerController;
use App\Http\Controllers\Admin\PollController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ScorecardController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Admin\VccCabinetController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\StatsController;
use App\Http\Controllers\Frontend\TeamFrontendController;
use App\Http\Controllers\Frontend\TournamentController;
use App\Http\Controllers\Frontend\VccController;
use App\Http\Controllers\Frontend\MoreController;
use App\Http\Controllers\Admin\BannedBowlerController;
use App\Http\Controllers\Admin\CaptainController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\SponsorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Frontend Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/schedule', function (\Illuminate\Http\Request $req) {
    $matches = \App\Models\CricketMatch::with(['homeTeam','awayTeam','winner','edition'])
        ->when($req->status && $req->status !== 'all', fn($q) => $q->where('status', $req->status))
        ->orderBy('scheduled_at')
        ->orderByRaw('CAST(match_number AS UNSIGNED)')
        ->paginate(15);
    return view('frontend.schedule', compact('matches'));
})->name('schedule');

Route::get('/tournaments', [TournamentController::class, 'index'])->name('tournaments.index');
Route::get('/tournaments/{edition}', [TournamentController::class, 'show'])->name('tournaments.show');

Route::get('/stats', [StatsController::class, 'index'])->name('stats');
Route::get('/player/{player}', [StatsController::class, 'player'])->name('player');

Route::get('/scorecard/{match}', [TournamentController::class, 'scorecard'])->name('scorecard');
Route::get('/scorecard/{match}/print', [ScorecardController::class, 'print'])->name('scorecard.print');

Route::get('/vcc', [VccController::class, 'index'])->name('vcc');
Route::get('/teams', [TeamFrontendController::class, 'index'])->name('teams');
Route::get('/teams/{team}', [TeamFrontendController::class, 'show'])->name('team.show');
Route::get('/more', [MoreController::class, 'index'])->name('more');
Route::get('/sponsors', [MoreController::class, 'sponsors'])->name('sponsors');
Route::get('/banned-bowlers', [MoreController::class, 'bannedBowlers'])->name('banned-bowlers');
Route::get('/team-fines', [MoreController::class, 'teamFines'])->name('team-fines');
Route::get('/captains', [MoreController::class, 'captains'])->name('captains');

Route::post('/poll/{pollId}/vote', [TournamentController::class, 'poll'])->name('poll.vote');

// Ticker API
Route::get('/api/ticker', [HomeController::class, 'ticker'])->name('api.ticker');
Route::get('/api/match/{match}/live', [TournamentController::class, 'liveMatch'])->name('api.match.live');

/*
|--------------------------------------------------------------------------
| Embeddable scoreboards
|--------------------------------------------------------------------------
| Public, read-only, and designed to be framed: a card for a web page, a
| transparent lower-third for a PRISM Live Studio / OBS browser source so a
| Facebook Live stream carries the score, and a PNG for link previews.
*/
Route::prefix('embed')->name('embed.')->group(function () {
    Route::get('oembed',                   [EmbedController::class, 'oembed'])->name('oembed');
    Route::get('match/{match}',            [EmbedController::class, 'widget'])->name('widget');
    Route::get('match/{match}/state.json', [EmbedController::class, 'state'])->name('state');
    Route::get('match/{match}/image.png',  [EmbedController::class, 'image'])->name('image');
});
Route::get('/share/{match}', [EmbedController::class, 'builder'])->name('share');

/*
|--------------------------------------------------------------------------
| Live scoring lives in the React Native app only
|--------------------------------------------------------------------------
| The web and admin scoring consoles were retired: scoring authority now sits
| exclusively with the mobile app, which talks to /api/v1/scoring/*. Anyone
| landing on the old URL gets pointed at the read-only live view.
*/
Route::get('/score', fn () => redirect()->route('schedule')
    ->with('error', 'Live scoring has moved to the RCL mobile app.'))->name('score.moved');

/*
|--------------------------------------------------------------------------
| Google OAuth
|--------------------------------------------------------------------------
*/
Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

/*
|--------------------------------------------------------------------------
| Admin Auth (Hash-protected)
|--------------------------------------------------------------------------
*/
Route::prefix('admin/auth')->name('admin.')->group(function () {
    Route::get('{hash}',   [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('{hash}',  [AdminAuthController::class, 'login'])->middleware('throttle:login')->name('login.submit');
    Route::post('logout',  [AdminAuthController::class, 'logout'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| Admin Panel
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['admin'])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Editions
    Route::resource('editions', EditionController::class);

    // Teams
    Route::resource('teams', TeamController::class);

    // Players
    Route::resource('players', PlayerController::class);

    // Matches
    Route::resource('matches', MatchController::class);
    Route::patch('matches/{match}/result', [MatchController::class, 'result'])->name('matches.result');
    Route::get('matches/{match}/manage', [MatchManageController::class, 'index'])->name('matches.manage');
    Route::put('matches/{match}/manage/match', [MatchManageController::class, 'updateMatch'])->name('matches.manage.match');
    Route::post('matches/{match}/manage/rebuild', [MatchManageController::class, 'rebuildMatch'])->name('matches.manage.rebuild');
    Route::post('matches/{match}/manage/innings', [MatchManageController::class, 'storeInnings'])->name('matches.manage.innings.store');
    Route::put('matches/{match}/manage/innings/{innings}', [MatchManageController::class, 'updateInnings'])->name('matches.manage.innings.update');
    Route::post('matches/{match}/manage/innings/{innings}/rebuild', [MatchManageController::class, 'rebuildInnings'])->name('matches.manage.innings.rebuild');
    Route::post('matches/{match}/manage/innings/{innings}/balls', [MatchManageController::class, 'storeBall'])->name('matches.manage.balls.store');
    Route::put('matches/{match}/manage/innings/{innings}/balls/{ball}', [MatchManageController::class, 'updateBall'])->name('matches.manage.balls.update');
    Route::delete('matches/{match}/manage/innings/{innings}/balls/{ball}', [MatchManageController::class, 'destroyBall'])->name('matches.manage.balls.destroy');

    // Scorecard
    Route::get('scorecard/{match}', [ScorecardController::class, 'show'])->name('scorecard.show');

    // Fines
    Route::resource('fines', FineController::class)->except(['show','edit','update']);
    Route::patch('fines/{fine}/status', [FineController::class, 'updateStatus'])->name('fines.status');
    Route::get('fines/players-by-team/{team}', [FineController::class, 'playersByTeam'])->name('fines.players-by-team');

    // Finance
    Route::get('finance',              [FinanceController::class, 'index'])->name('finance.index');
    Route::post('finance',             [FinanceController::class, 'store'])->name('finance.store');
    Route::delete('finance/{transaction}', [FinanceController::class, 'destroy'])->name('finance.destroy');

    // VCC Cabinet
    Route::resource('vcc', VccCabinetController::class)->parameters(['vcc' => 'vcc']);

    // Notifications
    Route::get('notifications',                 [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications',                [NotificationController::class, 'store'])->name('notifications.store');
    Route::patch('notifications/{notification}/toggle', [NotificationController::class, 'toggle'])->name('notifications.toggle');
    Route::delete('notifications/{notification}',       [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Banners
    Route::resource('banners', BannerController::class)->except(['show']);

    // Roles & Users
    Route::resource('roles', RoleController::class)->except(['show']);
    Route::get('users',                   [RoleController::class, 'users'])->name('roles.users');
    Route::patch('users/{user}/role',     [RoleController::class, 'assignRole'])->name('roles.assign');
    Route::patch('users/{user}/toggle',   [RoleController::class, 'toggleUserStatus'])->name('roles.toggle-user');

    // Polls
    Route::resource('polls', PollController::class)->except(['show','edit','update']);
    Route::patch('polls/{poll}/toggle',   [PollController::class, 'toggle'])->name('polls.toggle');

    // Demerit Points
    Route::get('demerit-points/players-by-team/{team}',  [DemeritPointController::class, 'playersByTeam'])->name('demerit-points.players-by-team');
    Route::patch('demerit-points/{demeritPoint}/toggle', [DemeritPointController::class, 'toggle'])->name('demerit-points.toggle');
    Route::resource('demerit-points', DemeritPointController::class)->except(['show']);

    // Banned Bowlers
    Route::get('banned-bowlers',                          [BannedBowlerController::class, 'index'])->name('banned-bowlers.index');
    Route::get('banned-bowlers/create',                   [BannedBowlerController::class, 'create'])->name('banned-bowlers.create');
    Route::post('banned-bowlers',                         [BannedBowlerController::class, 'store'])->name('banned-bowlers.store');
    Route::patch('banned-bowlers/{bannedBowler}/toggle',  [BannedBowlerController::class, 'toggle'])->name('banned-bowlers.toggle');
    Route::delete('banned-bowlers/{bannedBowler}',        [BannedBowlerController::class, 'destroy'])->name('banned-bowlers.destroy');

    // Captains
    Route::get('captains',              [CaptainController::class, 'index'])->name('captains.index');
    Route::get('captains/{team}/edit',  [CaptainController::class, 'edit'])->name('captains.edit');
    Route::put('captains/{team}',       [CaptainController::class, 'update'])->name('captains.update');

    // Sponsors
    Route::resource('sponsors', SponsorController::class)->except(['show']);

    // Site Settings
    Route::get('settings/meeting',     [SiteSettingController::class, 'meeting'])->name('settings.meeting');
    Route::post('settings/meeting',    [SiteSettingController::class, 'saveMeeting'])->name('settings.meeting.save');
    Route::get('settings/scoring-key', [SiteSettingController::class, 'scoringKey'])->name('settings.scoring-key');
    Route::post('settings/scoring-key',[SiteSettingController::class, 'saveScoringKey'])->name('settings.scoring-key.save');
});
