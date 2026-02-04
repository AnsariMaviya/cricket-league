<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class MonitoringService
{
    /**
     * Log application events with structured data
     */
    public static function logEvent(string $event, array $data = [], string $level = 'info')
    {
        $logData = [
            'event' => $event,
            'timestamp' => now()->toISOString(),
            'environment' => app()->environment(),
            'request_id' => request()->header('X-Request-ID') ?? uniqid(),
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'data' => $data,
        ];

        switch ($level) {
            case 'emergency':
                Log::emergency($event, $logData);
                break;
            case 'alert':
                Log::alert($event, $logData);
                break;
            case 'critical':
                Log::critical($event, $logData);
                break;
            case 'error':
                Log::error($event, $logData);
                break;
            case 'warning':
                Log::warning($event, $logData);
                break;
            case 'notice':
                Log::notice($event, $logData);
                break;
            case 'info':
                Log::info($event, $logData);
                break;
            case 'debug':
                Log::debug($event, $logData);
                break;
            default:
                Log::info($event, $logData);
        }

        // Store in Redis for real-time monitoring
        self::storeEventInRedis($event, $logData);
    }

    /**
     * Store event in Redis for monitoring dashboard
     */
    private static function storeEventInRedis(string $event, array $data)
    {
        try {
            if (extension_loaded('redis')) {
                $key = 'monitoring:events:' . date('Y-m-d:H');
                Redis::lpush($key, json_encode([
                    'event' => $event,
                    'timestamp' => $data['timestamp'],
                    'data' => $data,
                ]));
                
                // Keep only last 1000 events per hour
                Redis::ltrim($key, 0, 999);
                
                // Set expiration to 24 hours
                Redis::expire($key, 86400);
            }
        } catch (\Exception $e) {
            // Redis not available, continue without monitoring storage
            \Log::warning('Redis not available for monitoring: ' . $e->getMessage());
        }
    }

    /**
     * Log performance metrics
     */
    public static function logPerformance(string $action, float $duration, array $context = [])
    {
        self::logEvent('performance', [
            'action' => $action,
            'duration_ms' => round($duration * 1000, 2),
            'context' => $context,
        ], 'info');

        // Store performance metrics in Redis
        try {
            if (extension_loaded('redis')) {
                $key = 'monitoring:performance:' . $action;
                Redis::lpush($key, json_encode([
                    'duration' => $duration,
                    'timestamp' => now()->toISOString(),
                    'context' => $context,
                ]));
                
                // Keep only last 1000 metrics
                Redis::ltrim($key, 0, 999);
                Redis::expire($key, 86400);
            }
        } catch (\Exception $e) {
            Log::warning('Redis not available for performance monitoring: ' . $e->getMessage());
        }
    }

    /**
     * Log API requests
     */
    public static function logApiRequest(string $endpoint, string $method, int $statusCode, float $duration, array $context = [])
    {
        self::logEvent('api_request', [
            'endpoint' => $endpoint,
            'method' => $method,
            'status_code' => $statusCode,
            'duration_ms' => round($duration * 1000, 2),
            'context' => $context,
        ], $statusCode >= 400 ? 'warning' : 'info');

        // Store API metrics
        try {
            if (extension_loaded('redis')) {
                $key = 'monitoring:api:' . $endpoint;
                Redis::hincrby($key, 'total_requests', 1);
                
                if ($statusCode >= 400) {
                    Redis::hincrby($key, 'error_requests', 1);
                }
                
                Redis::hset($key, 'last_request', now()->toISOString());
                Redis::expire($key, 86400);
            }
        } catch (\Exception $e) {
            Log::warning('Redis not available for API monitoring: ' . $e->getMessage());
        }
    }

    /**
     * Log database queries
     */
    public static function logDatabaseQuery(string $query, float $duration, array $bindings = [])
    {
        self::logEvent('database_query', [
            'query' => $query,
            'duration_ms' => round($duration * 1000, 2),
            'bindings' => $bindings,
        ], $duration > 0.1 ? 'warning' : 'info');

        // Store slow queries
        if ($duration > 0.1) {
            try {
                if (extension_loaded('redis')) {
                    $key = 'monitoring:slow_queries';
                    Redis::lpush($key, json_encode([
                        'query' => $query,
                        'duration' => $duration,
                        'timestamp' => now()->toISOString(),
                        'bindings' => $bindings,
                    ]));
                    
                    Redis::ltrim($key, 0, 999);
                    Redis::expire($key, 86400);
                }
            } catch (\Exception $e) {
                Log::warning('Redis not available for slow query monitoring: ' . $e->getMessage());
            }
        }
    }

    /**
     * Log cache operations
     */
    public static function logCacheOperation(string $operation, string $key, bool $hit, float $duration = 0)
    {
        self::logEvent('cache_operation', [
            'operation' => $operation,
            'key' => $key,
            'hit' => $hit,
            'duration_ms' => round($duration * 1000, 2),
        ], 'info');

        // Update cache statistics
        try {
            if (extension_loaded('redis')) {
                $statsKey = 'monitoring:cache_stats';
                Redis::hincrby($statsKey, 'total_operations', 1);
                
                if ($hit) {
                    Redis::hincrby($statsKey, 'hits', 1);
                } else {
                    Redis::hincrby($statsKey, 'misses', 1);
                }
                
                Redis::expire($statsKey, 86400);
            }
        } catch (\Exception $e) {
            Log::warning('Redis not available for cache monitoring: ' . $e->getMessage());
        }
    }

    /**
     * Log user activities
     */
    public static function logUserActivity(string $activity, array $context = [])
    {
        self::logEvent('user_activity', [
            'activity' => $activity,
            'context' => $context,
        ], 'info');

        // Store user activity
        if (auth()->check()) {
            $key = 'monitoring:user_activity:' . auth()->id();
            Redis::lpush($key, json_encode([
                'activity' => $activity,
                'timestamp' => now()->toISOString(),
                'context' => $context,
            ]));
            
            Redis::ltrim($key, 0, 99);
            Redis::expire($key, 86400);
        }
    }

    /**
     * Log system health
     */
    public static function logSystemHealth(array $metrics)
    {
        self::logEvent('system_health', [
            'metrics' => $metrics,
        ], 'info');

        // Store health metrics
        $key = 'monitoring:health:' . date('Y-m-d-H-i');
        Redis::hset($key, $metrics);
        Redis::expire($key, 86400);
    }

    /**
     * Log security events
     */
    public static function logSecurityEvent(string $event, array $context = [])
    {
        self::logEvent('security', [
            'security_event' => $event,
            'context' => $context,
        ], 'warning');

        // Store security events
        $key = 'monitoring:security';
        Redis::lpush($key, json_encode([
            'event' => $event,
            'timestamp' => now()->toISOString(),
            'context' => $context,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]));
        
        Redis::ltrim($key, 0, 999);
        Redis::expire($key, 86400 * 7); // Keep for 7 days
    }

    /**
     * Get monitoring dashboard data
     */
    public static function getDashboardData(): array
    {
        return [
            'system_health' => self::getSystemHealth(),
            'recent_events' => self::getRecentEvents(),
            'api_stats' => self::getApiStats(),
            'performance_metrics' => self::getPerformanceMetrics(),
            'cache_stats' => self::getCacheStats(),
            'security_events' => self::getSecurityEvents(),
            'slow_queries' => self::getSlowQueries(),
        ];
    }

    /**
     * Get system health metrics
     */
    public static function getSystemHealth(): array
    {
        $health = [
            'timestamp' => now()->toISOString(),
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'cpu_usage' => sys_getloadavg()[0] ?? 0,
            'disk_usage' => disk_free_space('/'),
            'redis_memory' => Redis::info('memory'),
            'active_connections' => Redis::info('clients')['connected_clients'] ?? 0,
        ];

        // Log current health
        self::logSystemHealth($health);

        return $health;
    }

    /**
     * Get recent events
     */
    public static function getRecentEvents(int $limit = 100): array
    {
        $events = [];
        $currentHour = date('Y-m-d:H');
        
        // Get events from current hour
        $key = 'monitoring:events:' . $currentHour;
        $eventData = Redis::lrange($key, 0, $limit - 1);
        
        foreach ($eventData as $event) {
            $events[] = json_decode($event, true);
        }
        
        return $events;
    }

    /**
     * Get API statistics
     */
    public static function getApiStats(): array
    {
        $keys = Redis::keys('monitoring:api:*');
        $stats = [];
        
        foreach ($keys as $key) {
            $endpoint = str_replace('monitoring:api:', '', $key);
            $stats[$endpoint] = Redis::hgetall($key);
        }
        
        return $stats;
    }

    /**
     * Get performance metrics
     */
    public static function getPerformanceMetrics(): array
    {
        $keys = Redis::keys('monitoring:performance:*');
        $metrics = [];
        
        foreach ($keys as $key) {
            $action = str_replace('monitoring:performance:', '', $key);
            $data = Redis::lrange($key, 0, 99);
            
            $durations = [];
            foreach ($data as $item) {
                $itemData = json_decode($item, true);
                $durations[] = $itemData['duration'];
            }
            
            if (!empty($durations)) {
                $metrics[$action] = [
                    'count' => count($durations),
                    'avg_duration' => array_sum($durations) / count($durations),
                    'min_duration' => min($durations),
                    'max_duration' => max($durations),
                ];
            }
        }
        
        return $metrics;
    }

    /**
     * Get cache statistics
     */
    public static function getCacheStats(): array
    {
        $stats = Redis::hgetall('monitoring:cache_stats');
        
        if (!empty($stats)) {
            $total = $stats['total_operations'] ?? 0;
            $hits = $stats['hits'] ?? 0;
            $misses = $stats['misses'] ?? 0;
            
            $stats['hit_rate'] = $total > 0 ? round(($hits / $total) * 100, 2) : 0;
        }
        
        return $stats;
    }

    /**
     * Get security events
     */
    public static function getSecurityEvents(int $limit = 50): array
    {
        $events = Redis::lrange('monitoring:security', 0, $limit - 1);
        $securityEvents = [];
        
        foreach ($events as $event) {
            $securityEvents[] = json_decode($event, true);
        }
        
        return $securityEvents;
    }

    /**
     * Get slow queries
     */
    public static function getSlowQueries(int $limit = 50): array
    {
        $queries = Redis::lrange('monitoring:slow_queries', 0, $limit - 1);
        $slowQueries = [];
        
        foreach ($queries as $query) {
            $slowQueries[] = json_decode($query, true);
        }
        
        return $slowQueries;
    }

    /**
     * Clear monitoring data
     */
    public static function clearMonitoringData(): void
    {
        $patterns = [
            'monitoring:events:*',
            'monitoring:performance:*',
            'monitoring:api:*',
            'monitoring:slow_queries',
            'monitoring:security',
            'monitoring:cache_stats',
            'monitoring:health:*',
            'monitoring:user_activity:*',
        ];
        
        foreach ($patterns as $pattern) {
            $keys = Redis::keys($pattern);
            if ($keys) {
                Redis::del($keys);
            }
        }
    }

    /**
     * Generate monitoring report
     */
    public static function generateReport(string $period = '24h'): array
    {
        $periods = [
            '1h' => 1,
            '24h' => 24,
            '7d' => 168,
            '30d' => 720,
        ];
        
        $hours = $periods[$period] ?? 24;
        
        return [
            'period' => $period,
            'generated_at' => now()->toISOString(),
            'summary' => [
                'total_events' => self::getTotalEvents($hours),
                'total_api_requests' => self::getTotalApiRequests($hours),
                'avg_response_time' => self::getAvgResponseTime($hours),
                'error_rate' => self::getErrorRate($hours),
                'cache_hit_rate' => self::getCacheHitRate(),
            ],
            'top_endpoints' => self::getTopEndpoints($hours),
            'slow_queries' => self::getSlowQueries(10),
            'security_events' => self::getSecurityEvents(10),
        ];
    }

    /**
     * Get total events for period
     */
    private static function getTotalEvents(int $hours): int
    {
        $total = 0;
        for ($i = 0; $i < $hours; $i++) {
            $hour = date('Y-m-d:H', strtotime("-{$i} hours"));
            $key = 'monitoring:events:' . $hour;
            $total += Redis::llen($key);
        }
        return $total;
    }

    /**
     * Get total API requests for period
     */
    private static function getTotalApiRequests(int $hours): int
    {
        $total = 0;
        $keys = Redis::keys('monitoring:api:*');
        
        foreach ($keys as $key) {
            $stats = Redis::hgetall($key);
            $total += $stats['total_requests'] ?? 0;
        }
        
        return $total;
    }

    /**
     * Get average response time
     */
    private static function getAvgResponseTime(int $hours): float
    {
        $totalTime = 0;
        $count = 0;
        
        $keys = Redis::keys('monitoring:performance:*');
        foreach ($keys as $key) {
            $data = Redis::lrange($key, 0, -1);
            foreach ($data as $item) {
                $itemData = json_decode($item, true);
                $totalTime += $itemData['duration'];
                $count++;
            }
        }
        
        return $count > 0 ? round(($totalTime / $count) * 1000, 2) : 0;
    }

    /**
     * Get error rate
     */
    private static function getErrorRate(int $hours): float
    {
        $totalRequests = 0;
        $errorRequests = 0;
        
        $keys = Redis::keys('monitoring:api:*');
        foreach ($keys as $key) {
            $stats = Redis::hgetall($key);
            $totalRequests += $stats['total_requests'] ?? 0;
            $errorRequests += $stats['error_requests'] ?? 0;
        }
        
        return $totalRequests > 0 ? round(($errorRequests / $totalRequests) * 100, 2) : 0;
    }

    /**
     * Get cache hit rate
     */
    private static function getCacheHitRate(): float
    {
        $stats = self::getCacheStats();
        return $stats['hit_rate'] ?? 0;
    }

    /**
     * Get top endpoints
     */
    private static function getTopEndpoints(int $hours): array
    {
        $endpoints = [];
        $keys = Redis::keys('monitoring:api:*');
        
        foreach ($keys as $key) {
            $endpoint = str_replace('monitoring:api:', '', $key);
            $stats = Redis::hgetall($key);
            $endpoints[$endpoint] = $stats['total_requests'] ?? 0;
        }
        
        arsort($endpoints);
        return array_slice($endpoints, 0, 10, true);
    }
}
