<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\CricketMatch;
use App\Observers\MatchObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        CricketMatch::observe(MatchObserver::class);
    }
}
