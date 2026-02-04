<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AdvancedRateLimiter
{
    /**
     * Check if Redis is available
     */
    private static function isRedisAvailable(): bool
    {
        return extension_loaded('redis');
    }

    /**
     * Execute Redis operation with error handling
     */
    private static function executeRedisOperation(callable $operation, $default = null)
    {
        try {
            if (self::isRedisAvailable()) {
                return $operation();
            }
        } catch (\Exception $e) {
            Log::warning('Redis not available for rate limiting: ' . $e->getMessage());
        }
        return $default;
    }
    /**
     * Rate limiting configurations
     */
    protected $limits = [
        'default' => [
            'requests' => 60,
            'minutes' => 1,
            'burst' => 10,
        ],
        'api' => [
            'requests' => 1000,
            'minutes' => 60,
            'burst' => 50,
        ],
        'live' => [
            'requests' => 300,
            'minutes' => 1,
            'burst' => 20,
        ],
        'simulation' => [
            'requests' => 100,
            'minutes' => 1,
            'burst' => 10,
        ],
        'prediction' => [
            'requests' => 50,
            'minutes' => 1,
            'burst' => 5,
        ],
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $limit = 'default')
    {
        $key = $this->resolveRequestSignature($request);
        $config = $this->limits[$limit] ?? $this->limits['default'];

        // Check rate limit
        if ($this->isRateLimited($key, $config)) {
            return $this->buildResponse($key, $config);
        }

        // Record request
        $this->recordRequest($key, $config);

        $response = $next($request);

        // Add rate limit headers
        $this->addRateLimitHeaders($response, $key, $config);

        return $response;
    }

    /**
     * Resolve request signature
     */
    protected function resolveRequestSignature(Request $request): string
    {
        $identifier = $request->ip();
        
        // Add user ID if authenticated
        if ($request->user()) {
            $identifier = 'user:' . $request->user()->id;
        }

        // Add API key if present
        if ($request->header('X-API-Key')) {
            $identifier = 'api:' . md5($request->header('X-API-Key'));
        }

        return 'rate_limit:' . sha1($identifier);
    }

    /**
     * Check if request is rate limited
     */
    protected function isRateLimited(string $key, array $config): bool
    {
        $current = $this->getCurrentRequests($key);
        
        return $current >= $config['requests'] || $this->isBurstLimited($key, $config);
    }

    /**
     * Record a request
     */
    protected function recordRequest(string $key, array $config): void
    {
        $redisKey = $key . ':' . now()->format('Y-m-d H:i');
        
        self::executeRedisOperation(function() use ($redisKey, $config) {
            // Increment counter
            Redis::incr($redisKey);
            
            // Set expiration
            Redis::expire($redisKey, $config['minutes'] * 60);
            
            // Track burst requests
            $this->trackBurstRequests($key, $config);
        });
    }

    /**
     * Track burst requests
     */
    protected function trackBurstRequests(string $key, array $config): void
    {
        $burstKey = $key . ':burst';
        
        // Add current request to burst window
        Redis::lpush($burstKey, now()->timestamp);
        
        // Remove old requests outside burst window
        $cutoff = now()->subSeconds(10)->timestamp;
        Redis::lrem($burstKey, 0, $cutoff);
        
        // Trim list to burst window size
        Redis::ltrim($burstKey, 0, $config['burst'] - 1);
        
        // Set expiration
        Redis::expire($burstKey, 60);
    }

    /**
     * Check if burst limit exceeded
     */
    protected function isBurstLimited(string $key, array $config): bool
    {
        $burstKey = $key . ':burst';
        $burstCount = Redis::llen($burstKey);
        
        return $burstCount >= $config['burst'];
    }

    /**
     * Build rate limit response
     */
    protected function buildResponse(string $key, array $config)
    {
        $current = $this->getCurrentRequests($key, $config['minutes']);
        $resetTime = now()->addMinutes(1)->timestamp;
        
        return response()->json([
            'error' => 'Too Many Requests',
            'message' => 'Rate limit exceeded',
            'retry_after' => 60,
            'limit' => $config['requests'],
            'remaining' => max(0, $config['requests'] - $current),
            'reset' => $resetTime,
        ], 429)->header('Retry-After', 60);
    }

    /**
     * Add rate limit headers
     */
    protected function addRateLimitHeaders($response, string $key, array $config): void
    {
        $current = $this->getCurrentRequests($key, $config['minutes']);
        $resetTime = now()->addMinutes(1)->timestamp;
        
        $response->headers->set('X-RateLimit-Limit', $config['requests']);
        $response->headers->set('X-RateLimit-Remaining', max(0, $config['requests'] - $current));
        $response->headers->set('X-RateLimit-Reset', $resetTime);
    }

    /**
     * Get rate limit statistics
     */
    public function getRateLimitStats(): array
    {
        $stats = [];
        
        foreach (['default', 'api', 'live', 'simulation', 'prediction'] as $type) {
            $pattern = 'rate_limit:*';
            $keys = Redis::keys($pattern);
            
            $totalRequests = 0;
            $activeClients = 0;
            
            foreach ($keys as $key) {
                $requests = Redis::get($key) ?? 0;
                $totalRequests += $requests;
                $activeClients++;
            }
            
            $stats[$type] = [
                'total_requests' => $totalRequests,
                'active_clients' => $activeClients,
                'average_per_client' => $activeClients > 0 ? round($totalRequests / $activeClients, 2) : 0,
            ];
        }
        
        return $stats;
    }

    /**
     * Clear rate limit data
     */
    public function clearRateLimitData(): void
    {
        $keys = Redis::keys('rate_limit:*');
        if ($keys) {
            Redis::del($keys);
        }
    }

    /**
     * Get top clients by request count
     */
    public function getTopClients(int $limit = 10): array
    {
        $keys = Redis::keys('rate_limit:*');
        $clients = [];
        
        foreach ($keys as $key) {
            $requests = Redis::get($key) ?? 0;
            $clients[] = [
                'key' => $key,
                'requests' => $requests,
            ];
        }
        
        // Sort by request count
        usort($clients, function ($a, $b) {
            return $b['requests'] - $a['requests'];
        });
        
        return array_slice($clients, 0, $limit);
    }

    /**
     * Block a client temporarily
     */
    public function blockClient(string $identifier, int $minutes = 60): void
    {
        $blockKey = 'rate_limit:block:' . $identifier;
        Redis::setex($blockKey, $minutes * 60, 'blocked');
    }

    /**
     * Check if client is blocked
     */
    public function isClientBlocked(string $identifier): bool
    {
        $blockKey = 'rate_limit:block:' . $identifier;
        return Redis::exists($blockKey);
    }

    /**
     * Unblock a client
     */
    public function unblockClient(string $identifier): void
    {
        $blockKey = 'rate_limit:block:' . $identifier;
        Redis::del($blockKey);
    }

    /**
     * Get blocked clients
     */
    public function getBlockedClients(): array
    {
        $keys = Redis::keys('rate_limit:block:*');
        $clients = [];
        
        foreach ($keys as $key) {
            $identifier = str_replace('rate_limit:block:', '', $key);
            $ttl = Redis::ttl($key);
            
            $clients[] = [
                'identifier' => $identifier,
                'blocked_until' => now()->addSeconds($ttl)->toDateTimeString(),
                'remaining_seconds' => $ttl,
            ];
        }
        
        return $clients;
    }
}
