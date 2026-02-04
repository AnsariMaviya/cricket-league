<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Services\CacheService;

class CacheApiResponse
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, int $ttl = 300)
    {
        // Only cache GET requests
        if (!$request->isMethod('GET')) {
            return $next($request);
        }

        // Generate cache key
        $cacheKey = $this->generateCacheKey($request);

        // Check if response is already cached
        $cachedResponse = CacheService::getCachedApiResponse($request->path(), $request->query());
        
        if ($cachedResponse) {
            CacheService::recordCacheHit();
            return response($cachedResponse['content'])
                ->header('Content-Type', $cachedResponse['content_type'])
                ->header('X-Cache', 'HIT');
        }

        // Process request
        $response = $next($request);

        // Cache successful responses
        if ($response->isSuccessful()) {
            $cacheData = [
                'content' => $response->getContent(),
                'content_type' => $response->headers->get('Content-Type'),
                'status' => $response->getStatusCode(),
            ];

            CacheService::cacheApiResponse($request->path(), $request->query(), $cacheData, $ttl);
            CacheService::recordCacheMiss();
        }

        return $response->header('X-Cache', 'MISS');
    }

    /**
     * Generate cache key for request
     */
    private function generateCacheKey(Request $request): string
    {
        $key = 'api:' . $request->path();
        
        if ($request->query()) {
            $key .= ':' . md5(serialize($request->query()));
        }

        return $key;
    }
}
