<?php

namespace App\Providers;

use App\Repositories\Eloquent\EditionRepository;
use App\Repositories\Eloquent\PlayerRepository;
use App\Repositories\Eloquent\ScoringRepository;
use App\Repositories\Interfaces\EditionRepositoryInterface;
use App\Repositories\Interfaces\PlayerRepositoryInterface;
use App\Repositories\Interfaces\ScoringRepositoryInterface;
use Illuminate\Pagination\Paginator;
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
    }
}
