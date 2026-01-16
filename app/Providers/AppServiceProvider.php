<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        
        // Query monitoring in development - disabled for performance
        // Enable only when debugging specific issues
        // if (config('app.debug') && env('ENABLE_QUERY_LOGGING', false)) {
        //     $this->enableQueryLogging();
        // }
    }
    
    /**
     * Enable comprehensive query logging and monitoring
     */
    private function enableQueryLogging(): void
    {
        // Track total queries per request
        DB::listen(function ($query) {
            // Log slow queries (> 100ms)
            if ($query->time > 100) {
                Log::channel('slow-queries')->warning('Slow Query Detected', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time . 'ms',
                    'url' => request()->fullUrl(),
                    'method' => request()->method(),
                ]);
            }
            
            // Log all queries in development (optional - can be verbose)
            if (config('app.env') === 'local' && config('logging.log_all_queries', false)) {
                Log::channel('queries')->debug('Query Executed', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time . 'ms',
                ]);
            }
        });
        
        // Track N+1 query problems
        DB::enableQueryLog();
    }
}
