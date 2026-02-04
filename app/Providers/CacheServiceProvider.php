<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Services\CacheService;

class CacheServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register cache middleware
        $router = $this->app['router'];
        
        // Apply caching to API routes
        $router->middlewareGroup('api.cache', [
            \App\Http\Middleware\CacheApiResponse::class,
        ]);

        // Cache invalidation events
        $this->registerCacheInvalidationEvents();
    }

    /**
     * Register cache invalidation events
     */
    private function registerCacheInvalidationEvents()
    {
        // Invalidate match cache when match is updated
        \Event::listen('match.updated', function ($match) {
            CacheService::invalidateMatchCache($match->match_id);
        });

        // Invalidate team cache when team is updated
        \Event::listen('team.updated', function ($team) {
            CacheService::invalidateTeamCache($team->team_id);
        });

        // Invalidate player cache when player is updated
        \Event::listen('player.updated', function ($player) {
            CacheService::invalidatePlayerCache($player->player_id);
        });

        // Invalidate leaderboard cache when points are updated
        \Event::listen('points.updated', function () {
            CacheService::invalidateLeaderboardCache();
        });
    }
}
