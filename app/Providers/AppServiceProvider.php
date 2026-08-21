<?php

namespace App\Providers;

use App\Repositories\Eloquent\EditionRepository;
use App\Repositories\Eloquent\PlayerRepository;
use App\Repositories\Eloquent\ScoringRepository;
use App\Repositories\Interfaces\EditionRepositoryInterface;
use App\Repositories\Interfaces\PlayerRepositoryInterface;
use App\Repositories\Interfaces\ScoringRepositoryInterface;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(EditionRepositoryInterface::class, EditionRepository::class);
        $this->app->bind(ScoringRepositoryInterface::class, ScoringRepository::class);
        $this->app->bind(PlayerRepositoryInterface::class,  PlayerRepository::class);
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Paginator::defaultView('vendor.pagination.rcl');
        Paginator::defaultSimpleView('vendor.pagination.rcl-simple');

        $this->registerRateLimiters();
    }

    private function registerRateLimiters(): void
    {
        // General read traffic from the app.
        RateLimiter::for('api', fn (Request $request) =>
            Limit::perMinute(120)->by($request->ip())
        );

        // Ball-by-ball writes burst hard during an over — keep the ceiling high
        // enough that a real scorer never hits it, low enough to stop a runaway.
        RateLimiter::for('scoring-write', fn (Request $request) =>
            Limit::perMinute(300)->by($request->ip())
        );

        // The passkey gate is the whole perimeter for scoring writes, so the
        // one door that must not be brute-forceable.
        RateLimiter::for('scoring-verify', fn (Request $request) =>
            Limit::perMinute(5)->by($request->ip())
        );

        // Same reasoning for the web-side admin login form.
        RateLimiter::for('login', fn (Request $request) =>
            Limit::perMinute(10)->by($request->ip())
        );
    }
}
