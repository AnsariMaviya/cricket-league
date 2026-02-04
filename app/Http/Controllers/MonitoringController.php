<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MonitoringService;

class MonitoringController extends Controller
{
    /**
     * Get monitoring dashboard data
     */
    public function dashboard(Request $request)
    {
        $data = MonitoringService::getDashboardData();
        
        return response()->json([
            'dashboard' => $data,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Get system health
     */
    public function health(Request $request)
    {
        $health = MonitoringService::getSystemHealth();
        
        return response()->json([
            'health' => $health,
            'status' => 'healthy',
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Get recent events
     */
    public function events(Request $request)
    {
        $limit = $request->get('limit', 100);
        $events = MonitoringService::getRecentEvents($limit);
        
        return response()->json([
            'events' => $events,
            'count' => count($events),
            'limit' => $limit,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Get API statistics
     */
    public function apiStats(Request $request)
    {
        $stats = MonitoringService::getApiStats();
        
        return response()->json([
            'api_stats' => $stats,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Get performance metrics
     */
    public function performance(Request $request)
    {
        $metrics = MonitoringService::getPerformanceMetrics();
        
        return response()->json([
            'performance_metrics' => $metrics,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Get cache statistics
     */
    public function cacheStats(Request $request)
    {
        $stats = MonitoringService::getCacheStats();
        
        return response()->json([
            'cache_stats' => $stats,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Get security events
     */
    public function securityEvents(Request $request)
    {
        $limit = $request->get('limit', 50);
        $events = MonitoringService::getSecurityEvents($limit);
        
        return response()->json([
            'security_events' => $events,
            'count' => count($events),
            'limit' => $limit,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Get slow queries
     */
    public function slowQueries(Request $request)
    {
        $limit = $request->get('limit', 50);
        $queries = MonitoringService::getSlowQueries($limit);
        
        return response()->json([
            'slow_queries' => $queries,
            'count' => count($queries),
            'limit' => $limit,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Generate monitoring report
     */
    public function report(Request $request)
    {
        $period = $request->get('period', '24h');
        $report = MonitoringService::generateReport($period);
        
        return response()->json([
            'report' => $report,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Clear monitoring data
     */
    public function clearData(Request $request)
    {
        MonitoringService::clearMonitoringData();
        
        return response()->json([
            'message' => 'Monitoring data cleared successfully',
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log custom event
     */
    public function logEvent(Request $request)
    {
        $request->validate([
            'event' => 'required|string|max:255',
            'data' => 'array',
            'level' => 'string|in:emergency,alert,critical,error,warning,notice,info,debug',
        ]);

        $event = $request->get('event');
        $data = $request->get('data', []);
        $level = $request->get('level', 'info');

        MonitoringService::logEvent($event, $data, $level);

        return response()->json([
            'message' => 'Event logged successfully',
            'event' => $event,
            'level' => $level,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log performance metric
     */
    public function logPerformance(Request $request)
    {
        $request->validate([
            'action' => 'required|string|max:255',
            'duration' => 'required|numeric|min:0',
            'context' => 'array',
        ]);

        $action = $request->get('action');
        $duration = $request->get('duration');
        $context = $request->get('context', []);

        MonitoringService::logPerformance($action, $duration, $context);

        return response()->json([
            'message' => 'Performance metric logged successfully',
            'action' => $action,
            'duration' => $duration,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log security event
     */
    public function logSecurityEvent(Request $request)
    {
        $request->validate([
            'event' => 'required|string|max:255',
            'context' => 'array',
        ]);

        $event = $request->get('event');
        $context = $request->get('context', []);

        MonitoringService::logSecurityEvent($event, $context);

        return response()->json([
            'message' => 'Security event logged successfully',
            'event' => $event,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Get monitoring summary
     */
    public function summary(Request $request)
    {
        $health = MonitoringService::getSystemHealth();
        $cacheStats = MonitoringService::getCacheStats();
        $apiStats = MonitoringService::getApiStats();
        $recentEvents = MonitoringService::getRecentEvents(10);

        $summary = [
            'system_health' => [
                'status' => 'healthy',
                'memory_usage' => $health['memory_usage'],
                'cpu_usage' => $health['cpu_usage'],
                'active_connections' => $health['active_connections'],
            ],
            'cache_performance' => [
                'hit_rate' => $cacheStats['hit_rate'] ?? 0,
                'total_operations' => $cacheStats['total_operations'] ?? 0,
            ],
            'api_performance' => [
                'total_requests' => array_sum(array_column($apiStats, 'total_requests')),
                'total_errors' => array_sum(array_column($apiStats, 'error_requests')),
                'error_rate' => 0,
            ],
            'recent_activity' => array_slice($recentEvents, 0, 5),
            'timestamp' => now()->toDateTimeString(),
        ];

        // Calculate error rate
        $totalRequests = $summary['api_performance']['total_requests'];
        if ($totalRequests > 0) {
            $summary['api_performance']['error_rate'] = round(
                ($summary['api_performance']['total_errors'] / $totalRequests) * 100,
                2
            );
        }

        return response()->json([
            'summary' => $summary,
        ]);
    }
}
