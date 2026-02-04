<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CacheService
{
    /**
     * Cache TTL constants (in seconds)
     */
    const TTL_SHORT = 300;      // 5 minutes
    const TTL_MEDIUM = 1800;    // 30 minutes
    const TTL_LONG = 3600;      // 1 hour
    const TTL_DAILY = 86400;    // 24 hours

    /**
     * Cache key prefixes
     */
    const PREFIX_MATCH = 'match:';
    const PREFIX_TEAM = 'team:';
    const PREFIX_PLAYER = 'player:';
    const PREFIX_SCOREBOARD = 'scoreboard:';
    const PREFIX_COMMENTARY = 'commentary:';
    const PREFIX_STATS = 'stats:';
    const PREFIX_PREDICTION = 'prediction:';
    const PREFIX_LEADERBOARD = 'leaderboard:';

    /**
     * Get cached data or execute callback
     */
    public static function remember(string $key, $callback, int $ttl = self::TTL_MEDIUM)
    {
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Get cached data or execute callback with tags
     */
    public static function rememberWithTags(string $key, array $tags, $callback, int $ttl = self::TTL_MEDIUM)
    {
        return Cache::tags($tags)->remember($key, $ttl, $callback);
    }

    /**
     * Cache match data
     */
    public static function cacheMatch($matchId, $data, int $ttl = self::TTL_MEDIUM)
    {
        $key = self::PREFIX_MATCH . $matchId;
        Cache::put($key, $data, $ttl);
        
        // Also cache in Redis for real-time access (if available)
        try {
            // Check if Redis extension is available
            if (extension_loaded('redis')) {
                Redis::setex("realtime:{$key}", self::TTL_SHORT, json_encode($data));
            } else {
                Log::warning('Redis extension not loaded, skipping Redis caching');
            }
        } catch (\Exception $e) {
            // Redis not available, continue without Redis caching
            Log::warning('Redis not available for caching: ' . $e->getMessage());
        }
    }

    /**
     * Get cached match data
     */
    public static function getMatch($matchId)
    {
        $key = self::PREFIX_MATCH . $matchId;
        
        // Try Redis first for real-time data (if available)
        try {
            if (extension_loaded('redis')) {
                $redisData = Redis::get("realtime:{$key}");
                if ($redisData) {
                    return json_decode($redisData, true);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Redis not available for getting cache: ' . $e->getMessage());
        }
        
        // Fallback to Laravel cache
        return Cache::get($key);
    }

    /**
     * Cache scoreboard data
     */
    public static function cacheScoreboard($matchId, $data, int $ttl = self::TTL_SHORT)
    {
        $key = self::PREFIX_SCOREBOARD . $matchId;
        
        // Cache in Redis for real-time updates (if available)
        try {
            if (extension_loaded('redis')) {
                Redis::setex("realtime:{$key}", $ttl, json_encode($data));
            }
        } catch (\Exception $e) {
            Log::warning('Redis not available for scoreboard caching: ' . $e->getMessage());
        }
        
        // Also cache in regular cache
        Cache::put($key, $data, $ttl);
    }

    /**
     * Get cached scoreboard data
     */
    public static function getScoreboard($matchId)
    {
        $key = self::PREFIX_SCOREBOARD . $matchId;
        
        // Try Redis first (if available)
        try {
            if (extension_loaded('redis')) {
                $redisData = Redis::get("realtime:{$key}");
                if ($redisData) {
                    return json_decode($redisData, true);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Redis not available for getting scoreboard: ' . $e->getMessage());
        }
        
        return Cache::get($key);
    }

    /**
     * Cache team data
     */
    public static function cacheTeam($teamId, $data, int $ttl = self::TTL_LONG)
    {
        $key = self::PREFIX_TEAM . $teamId;
        Cache::put($key, $data, $ttl);
    }

    /**
     * Get cached team data
     */
    public static function getTeam($teamId)
    {
        $key = self::PREFIX_TEAM . $teamId;
        return Cache::get($key);
    }

    /**
     * Cache player data
     */
    public static function cachePlayer($playerId, $data, int $ttl = self::TTL_LONG)
    {
        $key = self::PREFIX_PLAYER . $playerId;
        Cache::put($key, $data, $ttl);
    }

    /**
     * Get cached player data
     */
    public static function getPlayer($playerId)
    {
        $key = self::PREFIX_PLAYER . $playerId;
        return Cache::get($key);
    }

    /**
     * Cache commentary data
     */
    public static function cacheCommentary($matchId, $data, int $ttl = self::TTL_SHORT)
    {
        $key = self::PREFIX_COMMENTARY . $matchId;
        
        // Cache in Redis for real-time updates (if available)
        try {
            if (extension_loaded('redis')) {
                Redis::setex("realtime:{$key}", $ttl, json_encode($data));
            }
        } catch (\Exception $e) {
            Log::warning('Redis not available for commentary caching: ' . $e->getMessage());
        }
        
        Cache::put($key, $data, $ttl);
    }

    /**
     * Get cached commentary data
     */
    public static function getCommentary($matchId)
    {
        $key = self::PREFIX_COMMENTARY . $matchId;
        
        // Try Redis first (if available)
        try {
            if (extension_loaded('redis')) {
                $redisData = Redis::get("realtime:{$key}");
                if ($redisData) {
                    return json_decode($redisData, true);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Redis not available for getting commentary: ' . $e->getMessage());
        }
        
        return Cache::get($key);
    }

    /**
     * Cache player statistics
     */
    public static function cachePlayerStats($playerId, $data, int $ttl = self::TTL_MEDIUM)
    {
        $key = self::PREFIX_STATS . 'player:' . $playerId;
        Cache::put($key, $data, $ttl);
    }

    /**
     * Get cached player statistics
     */
    public static function getPlayerStats($playerId)
    {
        $key = self::PREFIX_STATS . 'player:' . $playerId;
        return Cache::get($key);
    }

    /**
     * Cache match statistics
     */
    public static function cacheMatchStats($matchId, $data, int $ttl = self::TTL_MEDIUM)
    {
        $key = self::PREFIX_STATS . 'match:' . $matchId;
        Cache::put($key, $data, $ttl);
    }

    /**
     * Get cached match statistics
     */
    public static function getMatchStats($matchId)
    {
        $key = self::PREFIX_STATS . 'match:' . $matchId;
        return Cache::get($key);
    }

    /**
     * Cache prediction data
     */
    public static function cachePrediction($matchId, $data, int $ttl = self::TTL_MEDIUM)
    {
        $key = self::PREFIX_PREDICTION . $matchId;
        Cache::put($key, $data, $ttl);
    }

    /**
     * Get cached prediction data
     */
    public static function getPrediction($matchId)
    {
        $key = self::PREFIX_PREDICTION . $matchId;
        return Cache::get($key);
    }

    /**
     * Cache leaderboard data
     */
    public static function cacheLeaderboard($type, $data, int $ttl = self::TTL_MEDIUM)
    {
        $key = self::PREFIX_LEADERBOARD . $type;
        Cache::put($key, $data, $ttl);
    }

    /**
     * Get cached leaderboard data
     */
    public static function getLeaderboard($type)
    {
        $key = self::PREFIX_LEADERBOARD . $type;
        return Cache::get($key);
    }

    /**
     * Invalidate match-related cache
     */
    public static function invalidateMatchCache($matchId)
    {
        $keys = [
            self::PREFIX_MATCH . $matchId,
            self::PREFIX_SCOREBOARD . $matchId,
            self::PREFIX_COMMENTARY . $matchId,
            self::PREFIX_STATS . 'match:' . $matchId,
            self::PREFIX_PREDICTION . $matchId,
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
            try {
                if (extension_loaded('redis')) {
                    Redis::del("realtime:{$key}");
                }
            } catch (\Exception $e) {
                Log::warning('Redis not available for cache invalidation: ' . $e->getMessage());
            }
        }
    }

    /**
     * Invalidate team-related cache
     */
    public static function invalidateTeamCache($teamId)
    {
        $keys = [
            self::PREFIX_TEAM . $teamId,
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Invalidate player-related cache
     */
    public static function invalidatePlayerCache($playerId)
    {
        $keys = [
            self::PREFIX_PLAYER . $playerId,
            self::PREFIX_STATS . 'player:' . $playerId,
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Invalidate leaderboard cache
     */
    public static function invalidateLeaderboardCache()
    {
        $patterns = [
            self::PREFIX_LEADERBOARD . '*',
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }

    /**
     * Warm up cache for live matches
     */
    public static function warmUpLiveMatchesCache($matches)
    {
        foreach ($matches as $match) {
            // Cache basic match data
            self::cacheMatch($match->match_id, $match->toArray(), self::TTL_MEDIUM);
            
            // Cache scoreboard data
            $scoreboard = app(LiveScoreboardService::class)->getScoreboard($match->match_id);
            if ($scoreboard) {
                self::cacheScoreboard($match->match_id, $scoreboard, self::TTL_SHORT);
            }
        }
    }

    /**
     * Get cache statistics
     */
    public static function getCacheStats()
    {
        return [
            'redis_memory' => Redis::info('memory'),
            'redis_keys' => Redis::dbSize(),
            'cache_hits' => Cache::get('cache_hits', 0),
            'cache_misses' => Cache::get('cache_misses', 0),
        ];
    }

    /**
     * Record cache hit
     */
    public static function recordCacheHit()
    {
        Cache::increment('cache_hits');
    }

    /**
     * Record cache miss
     */
    public static function recordCacheMiss()
    {
        Cache::increment('cache_misses');
    }

    /**
     * Clear all cache
     */
    public static function clearAll()
    {
        Cache::flush();
        
        // Clear Redis keys
        $redisKeys = Redis::keys('realtime:*');
        if ($redisKeys) {
            Redis::del($redisKeys);
        }
    }

    /**
     * Get cache hit ratio
     */
    public static function getCacheHitRatio()
    {
        $hits = Cache::get('cache_hits', 0);
        $misses = Cache::get('cache_misses', 0);
        $total = $hits + $misses;
        
        if ($total === 0) {
            return 0;
        }
        
        return round(($hits / $total) * 100, 2);
    }

    /**
     * Cache API response
     */
    public static function cacheApiResponse($endpoint, $params, $data, int $ttl = self::TTL_SHORT)
    {
        $key = 'api:' . md5($endpoint . serialize($params));
        Cache::put($key, $data, $ttl);
    }

    /**
     * Get cached API response
     */
    public static function getCachedApiResponse($endpoint, $params)
    {
        $key = 'api:' . md5($endpoint . serialize($params));
        return Cache::get($key);
    }

    /**
     * Invalidate API cache
     */
    public static function invalidateApiCache($endpoint = null)
    {
        if ($endpoint) {
            $pattern = 'api:' . md5($endpoint . '*');
            Cache::forget($pattern);
        } else {
            // Clear all API cache
            $keys = Redis::keys('api:*');
            if ($keys) {
                Redis::del($keys);
            }
        }
    }
}
