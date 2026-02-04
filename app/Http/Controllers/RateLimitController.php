<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Middleware\AdvancedRateLimiter;

class RateLimitController extends Controller
{
    protected $rateLimiter;

    public function __construct()
    {
        $this->rateLimiter = new AdvancedRateLimiter();
    }

    /**
     * Get rate limit statistics
     */
    public function getStats(Request $request)
    {
        $stats = $this->rateLimiter->getRateLimitStats();
        
        return response()->json([
            'rate_limit_stats' => $stats,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Get top clients by request count
     */
    public function getTopClients(Request $request)
    {
        $limit = $request->get('limit', 10);
        $clients = $this->rateLimiter->getTopClients($limit);
        
        return response()->json([
            'top_clients' => $clients,
            'limit' => $limit,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Get blocked clients
     */
    public function getBlockedClients(Request $request)
    {
        $clients = $this->rateLimiter->getBlockedClients();
        
        return response()->json([
            'blocked_clients' => $clients,
            'count' => count($clients),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Block a client
     */
    public function blockClient(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'minutes' => 'integer|min:1|max:1440',
        ]);

        $identifier = $request->get('identifier');
        $minutes = $request->get('minutes', 60);

        $this->rateLimiter->blockClient($identifier, $minutes);

        return response()->json([
            'message' => 'Client blocked successfully',
            'identifier' => $identifier,
            'blocked_until' => now()->addMinutes($minutes)->toDateTimeString(),
        ]);
    }

    /**
     * Unblock a client
     */
    public function unblockClient(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
        ]);

        $identifier = $request->get('identifier');
        $this->rateLimiter->unblockClient($identifier);

        return response()->json([
            'message' => 'Client unblocked successfully',
            'identifier' => $identifier,
        ]);
    }

    /**
     * Clear rate limit data
     */
    public function clearData(Request $request)
    {
        $this->rateLimiter->clearRateLimitData();

        return response()->json([
            'message' => 'Rate limit data cleared successfully',
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Get rate limit configuration
     */
    public function getConfig(Request $request)
    {
        $config = [
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

        return response()->json([
            'rate_limit_config' => $config,
            'description' => [
                'default' => 'General API endpoints',
                'api' => 'Standard API endpoints',
                'live' => 'Live match endpoints',
                'simulation' => 'Match simulation endpoints',
                'prediction' => 'Prediction endpoints',
            ],
        ]);
    }

    /**
     * Test rate limit
     */
    public function testRateLimit(Request $request)
    {
        $key = 'rate_limit:test:' . $request->ip();
        $config = [
            'requests' => 5,
            'minutes' => 1,
            'burst' => 2,
        ];

        $current = $this->rateLimiter->getCurrentRequests($key, $config['minutes']);
        $remaining = max(0, $config['requests'] - $current);

        return response()->json([
            'message' => 'Rate limit test endpoint',
            'current_requests' => $current,
            'limit' => $config['requests'],
            'remaining' => $remaining,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}
