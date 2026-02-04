<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\QueryOptimizationService;

class QueryPerformanceMonitor
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Only monitor in local/development environment
        if (!app()->environment(['local', 'testing'])) {
            return $next($request);
        }

        // Enable query log
        \DB::enableQueryLog();

        $startTime = microtime(true);

        $response = $next($request);

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        // Get query log
        $queries = \DB::getQueryLog();
        \DB::disableQueryLog();

        // Analyze queries
        $this->analyzeQueries($queries, $request, $executionTime);

        // Add performance headers
        $response->headers->set('X-Query-Time', round($executionTime, 2));
        $response->headers->set('X-Query-Count', count($queries));

        return $response;
    }

    /**
     * Analyze queries and log performance issues
     */
    private function analyzeQueries(array $queries, Request $request, float $totalTime)
    {
        $totalQueryTime = 0;
        $slowQueries = [];
        $duplicateQueries = [];

        foreach ($queries as $query) {
            $queryTime = $query['time'] ?? 0;
            $totalQueryTime += $queryTime;

            // Log slow queries (>100ms)
            if ($queryTime > 100) {
                $slowQueries[] = [
                    'query' => $query['query'],
                    'time' => $queryTime,
                    'bindings' => $query['bindings'],
                ];
            }

            // Check for duplicate queries
            $queryKey = md5($query['query'] . serialize($query['bindings']));
            if (!isset($duplicateQueries[$queryKey])) {
                $duplicateQueries[$queryKey] = [
                    'query' => $query['query'],
                    'count' => 0,
                    'total_time' => 0,
                ];
            }
            $duplicateQueries[$queryKey]['count']++;
            $duplicateQueries[$queryKey]['total_time'] += $queryTime;
        }

        // Log performance issues
        if ($totalTime > 1000) { // Total request time > 1 second
            Log::warning('Slow request detected', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'total_time' => $totalTime,
                'query_time' => $totalQueryTime,
                'query_count' => count($queries),
                'slow_queries' => count($slowQueries),
            ]);
        }

        // Log slow queries
        if (!empty($slowQueries)) {
            Log::warning('Slow queries detected', [
                'url' => $request->fullUrl(),
                'slow_queries' => $slowQueries,
            ]);
        }

        // Log duplicate queries (potential N+1 problems)
        $duplicates = array_filter($duplicateQueries, function($query) {
            return $query['count'] > 1;
        });

        if (!empty($duplicates)) {
            Log::warning('Duplicate queries detected (possible N+1 problem)', [
                'url' => $request->fullUrl(),
                'duplicates' => array_values($duplicates),
            ]);
        }

        // Log if query time is more than 50% of total time
        if ($totalQueryTime > ($totalTime * 0.5)) {
            Log::info('High database time ratio', [
                'url' => $request->fullUrl(),
                'total_time' => $totalTime,
                'query_time' => $totalQueryTime,
                'ratio' => round(($totalQueryTime / $totalTime) * 100, 2) . '%',
            ]);
        }
    }
}
