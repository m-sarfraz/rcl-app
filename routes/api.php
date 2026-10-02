<?php

use App\Http\Controllers\Api\V1\DisciplineController;
use App\Http\Controllers\Api\V1\EditionController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\MatchController;
use App\Http\Controllers\Api\V1\PlayerController;
use App\Http\Controllers\Api\V1\PollController;
use App\Http\Controllers\Api\V1\ScoringController;
use App\Http\Controllers\Api\V1\StatsController;
use App\Http\Controllers\Api\V1\TeamController;
use App\Http\Controllers\Api\V1\VccController;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| RCL Mobile API (v1)
|--------------------------------------------------------------------------
| Consumed by the React Native app. Every response comes back in the
| ApiResponse envelope: { success, message?, data, meta?, errors? }.
|
| Two tiers:
|   • public  — read-only league data, rate limited at 120 req/min
|   • scoring — writes, gated by the 10-character passkey token
*/

Route::prefix('v1')->middleware('throttle:api')->group(function () {

    /* ── Discovery ─────────────────────────────────────────────── */
    Route::get('/', fn () => ApiResponse::success([
        'api'     => 'Royal Champions League',
        'version' => 'v1',
        'docs'    => url('/api/v1/settings'),
    ]));

    Route::get('health', fn () => ApiResponse::success([
        'status'    => 'ok',
        'time'      => now()->toIso8601String(),
        'database'  => 'connected',
    ]));

    /* ── Home & config ─────────────────────────────────────────── */
    Route::get('home',     [HomeController::class, 'index']);
    Route::get('ticker',   [HomeController::class, 'ticker']);
    Route::get('settings', [HomeController::class, 'settings']);

    /* ── Fixtures & scorecards ─────────────────────────────────── */
    Route::get('schedule',                   [MatchController::class, 'index']);
    Route::get('matches',                    [MatchController::class, 'index']);
    Route::get('matches/{match}',            [MatchController::class, 'show']);
    Route::get('matches/{match}/live',       [MatchController::class, 'live']);
    Route::get('matches/{match}/commentary', [MatchController::class, 'commentary']);

    /* ── Editions ──────────────────────────────────────────────── */
    Route::get('editions',                        [EditionController::class, 'index']);
    Route::get('editions/{edition}',              [EditionController::class, 'show']);
    Route::get('editions/{edition}/points-table', [EditionController::class, 'pointsTable']);
    Route::get('editions/{edition}/leaderboards', [EditionController::class, 'leaderboards']);

    /* ── Teams & players ───────────────────────────────────────── */
    Route::get('teams',           [TeamController::class, 'index']);
    Route::get('teams/{team}',    [TeamController::class, 'show']);
    Route::get('players',         [PlayerController::class, 'index']);
    Route::get('players/{player}',[PlayerController::class, 'show']);

    /* ── Statistics ────────────────────────────────────────────── */
    Route::get('stats',        [StatsController::class, 'index']);
    Route::get('leaderboards', [StatsController::class, 'index']);

    /* ── Player governance ─────────────────────────────────────── */
    Route::get('bans',           [DisciplineController::class, 'bans']);
    Route::get('fines',          [DisciplineController::class, 'fines']);
    Route::get('suspensions',    [DisciplineController::class, 'suspensions']);
    Route::get('captains',       [DisciplineController::class, 'captains']);
    Route::get('demerit-points', [DisciplineController::class, 'demeritPoints']);

    /* ── VCC & sponsors ────────────────────────────────────────── */
    Route::get('vcc',      [VccController::class, 'index']);
    Route::get('sponsors', [VccController::class, 'sponsors']);

    /* ── Fan polls ─────────────────────────────────────────────── */
    Route::get('polls/{poll}',       [PollController::class, 'show']);
    Route::post('poll/{poll}/vote',  [PollController::class, 'vote'])->middleware('throttle:20,1');
    Route::post('polls/{poll}/vote', [PollController::class, 'vote'])->middleware('throttle:20,1');

    /*
    |----------------------------------------------------------------
    | Scoring — the mobile console is the only writer in the system
    |----------------------------------------------------------------
    */
    Route::prefix('scoring')->group(function () {

        // The one open door: passkey → token. Tightly throttled.
        Route::post('verify', [ScoringController::class, 'verify'])
            ->middleware('throttle:scoring-verify');

        Route::middleware(['scoring.key', 'throttle:scoring-write'])->group(function () {
            Route::get('session', [ScoringController::class, 'session']);
            Route::get('matches', [ScoringController::class, 'matches']);

            Route::get('matches/{match}/setup',            [ScoringController::class, 'setup']);
            Route::get('matches/{match}/state',            [ScoringController::class, 'state']);
            Route::post('matches/{match}/toss',            [ScoringController::class, 'toss']);
            Route::post('matches/{match}/squad',           [ScoringController::class, 'saveSquad']);
            Route::post('matches/{match}/quick-player',    [ScoringController::class, 'addQuickPlayer']);
            Route::post('matches/{match}/innings',         [ScoringController::class, 'startInnings']);
            Route::get('matches/{match}/finalize-preview', [ScoringController::class, 'finalizePreview']);
            Route::post('matches/{match}/finalize',        [ScoringController::class, 'finalize']);
            Route::post('matches/{match}/abandon',         [ScoringController::class, 'abandon']);

            Route::post('innings/{innings}/balls',        [ScoringController::class, 'recordBall']);
            Route::post('innings/{innings}/sync',         [ScoringController::class, 'syncBalls']);
            Route::delete('innings/{innings}/balls/last', [ScoringController::class, 'undoBall']);
        });
    });
});

/* Anything else under /api resolves to a JSON 404, never an HTML error page. */
Route::fallback(fn () => ApiResponse::error('Endpoint not found.', 404, [], 'not_found'));
