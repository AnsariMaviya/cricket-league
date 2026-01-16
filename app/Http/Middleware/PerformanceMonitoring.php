<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PerformanceMonitoring
{
    /**
     * Handle an incoming request and monitor performance.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Start timing
        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        
        // Enable query logging
        DB::enableQueryLog();
        
        // Process request
        $response = $next($request);
        
        // Calculate metrics
        $endTime = microtime(true);
        $endMemory = memory_get_usage();
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        $memoryUsed = ($endMemory - $startMemory) / 1024 / 1024; // Convert to MB
        $queries = DB::getQueryLog();
        $queryCount = count($queries);
        $totalQueryTime = collect($queries)->sum('time');
        
        // Log performance metrics for slow requests
        if ($executionTime > 500 || $queryCount > 20) {
            Log::channel('performance')->warning('Slow Request Detected', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'execution_time' => round($executionTime, 2) . 'ms',
                'memory_used' => round($memoryUsed, 2) . 'MB',
                'query_count' => $queryCount,
                'total_query_time' => round($totalQueryTime, 2) . 'ms',
                'status_code' => $response->getStatusCode(),
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip(),
            ]);
        }
        
        // Add performance headers in development
        if (config('app.debug')) {
            $response->headers->set('X-Debug-Time', round($executionTime, 2) . 'ms');
            $response->headers->set('X-Debug-Memory', round($memoryUsed, 2) . 'MB');
            $response->headers->set('X-Debug-Queries', $queryCount);
            $response->headers->set('X-Debug-Query-Time', round($totalQueryTime, 2) . 'ms');
        }
        
        return $response;
    }
}
