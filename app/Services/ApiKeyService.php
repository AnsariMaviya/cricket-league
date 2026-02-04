<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ApiKeyService
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
    private static function executeRedisOperation(callable $operation)
    {
        try {
            if (self::isRedisAvailable()) {
                return $operation();
            }
        } catch (\Exception $e) {
            Log::warning('Redis not available for API key service: ' . $e->getMessage());
        }
        return null;
    }
    /**
     * Generate new API key
     */
    public static function generateApiKey(string $name, array $permissions = [], int $rateLimit = 1000): array
    {
        $apiKey = [
            'key_id' => Str::uuid()->toString(),
            'api_key' => self::generateKeyString(),
            'name' => $name,
            'permissions' => $permissions,
            'rate_limit' => $rateLimit,
            'created_at' => now()->toISOString(),
            'expires_at' => null,
            'is_active' => true,
            'last_used_at' => null,
            'usage_count' => 0,
        ];

        // Store in Redis
        self::executeRedisOperation(function() use ($apiKey) {
            $key = 'api_keys:' . $apiKey['key_id'];
            Redis::hmset($key, $apiKey);
            Redis::expire($key, 86400 * 365); // 1 year expiration

            // Also store by API key for quick lookup
            $lookupKey = 'api_key_lookup:' . $apiKey['api_key'];
            Redis::setex($lookupKey, 86400 * 365, $apiKey['key_id']);
        });

        return $apiKey;
    }

    /**
     * Generate random API key string
     */
    private static function generateKeyString(): string
    {
        return 'ck_' . Str::random(32);
    }

    /**
     * Validate API key
     */
    public static function validateApiKey(string $apiKey): ?array
    {
        $lookupKey = 'api_key_lookup:' . $apiKey;
        $keyId = Redis::get($lookupKey);

        if (!$keyId) {
            return null;
        }

        $key = 'api_keys:' . $keyId;
        $apiKeyData = Redis::hgetall($key);

        if (empty($apiKeyData) || !$apiKeyData['is_active']) {
            return null;
        }

        // Check if expired
        if ($apiKeyData['expires_at'] && Carbon::parse($apiKeyData['expires_at'])->isPast()) {
            return null;
        }

        // Update usage statistics
        self::updateUsageStats($keyId);

        return $apiKeyData;
    }

    /**
     * Update usage statistics
     */
    private static function updateUsageStats(string $keyId): void
    {
        $key = 'api_keys:' . $keyId;
        Redis::hincrby($key, 'usage_count', 1);
        Redis::hset($key, 'last_used_at', now()->toISOString());
    }

    /**
     * Check API key permissions
     */
    public static function hasPermission(array $apiKeyData, string $permission): bool
    {
        if (empty($apiKeyData['permissions'])) {
            return true; // No restrictions
        }

        return in_array($permission, $apiKeyData['permissions']) || 
               in_array('*', $apiKeyData['permissions']);
    }

    /**
     * Revoke API key
     */
    public static function revokeApiKey(string $keyId): bool
    {
        $key = 'api_keys:' . $keyId;
        $apiKeyData = Redis::hgetall($key);

        if (empty($apiKeyData)) {
            return false;
        }

        // Mark as inactive
        Redis::hset($key, 'is_active', false);
        Redis::hset($key, 'revoked_at', now()->toISOString());

        // Remove lookup
        $lookupKey = 'api_key_lookup:' . $apiKeyData['api_key'];
        Redis::del($lookupKey);

        return true;
    }

    /**
     * Get API key by ID
     */
    public static function getApiKey(string $keyId): ?array
    {
        $key = 'api_keys:' . $keyId;
        $apiKeyData = Redis::hgetall($key);

        return empty($apiKeyData) ? null : $apiKeyData;
    }

    /**
     * Get all API keys
     */
    public static function getAllApiKeys(): array
    {
        $keys = Redis::keys('api_keys:*');
        $apiKeys = [];

        foreach ($keys as $key) {
            $keyData = Redis::hgetall($key);
            if (!empty($keyData)) {
                // Don't expose the actual API key in list view
                unset($keyData['api_key']);
                $apiKeys[] = $keyData;
            }
        }

        return $apiKeys;
    }

    /**
     * Update API key
     */
    public static function updateApiKey(string $keyId, array $updates): bool
    {
        $key = 'api_keys:' . $keyId;
        $exists = Redis::exists($key);

        if (!$exists) {
            return false;
        }

        // Don't allow updating certain fields
        unset($updates['key_id'], $updates['api_key'], $updates['created_at']);

        // Add updated timestamp
        $updates['updated_at'] = now()->toISOString();

        Redis::hmset($key, $updates);

        return true;
    }

    /**
     * Set API key expiration
     */
    public static function setExpiration(string $keyId, Carbon $expiresAt): bool
    {
        $key = 'api_keys:' . $keyId;
        $exists = Redis::exists($key);

        if (!$exists) {
            return false;
        }

        Redis::hset($key, 'expires_at', $expiresAt->toISOString());

        return true;
    }

    /**
     * Get API key usage statistics
     */
    public static function getUsageStats(string $keyId): array
    {
        $key = 'api_keys:' . $keyId;
        $apiKeyData = Redis::hgetall($key);

        if (empty($apiKeyData)) {
            return [];
        }

        // Get hourly usage for the last 24 hours
        $hourlyStats = [];
        for ($i = 0; $i < 24; $i++) {
            $hour = now()->subHours($i)->format('Y-m-d:H');
            $usageKey = 'api_usage:' . $keyId . ':' . $hour;
            $hourlyStats[$hour] = Redis::get($usageKey) ?? 0;
        }

        return [
            'key_id' => $keyId,
            'name' => $apiKeyData['name'],
            'usage_count' => $apiKeyData['usage_count'] ?? 0,
            'last_used_at' => $apiKeyData['last_used_at'],
            'hourly_usage' => array_reverse($hourlyStats, true),
        ];
    }

    /**
     * Record API usage
     */
    public static function recordUsage(string $keyId, string $endpoint, int $statusCode): void
    {
        $hour = now()->format('Y-m-d:H');
        $usageKey = 'api_usage:' . $keyId . ':' . $hour;
        
        Redis::incr($usageKey);
        Redis::expire($usageKey, 86400 * 7); // Keep for 7 days

        // Record endpoint usage
        $endpointKey = 'api_endpoint_usage:' . $keyId . ':' . md5($endpoint);
        Redis::hincrby($endpointKey, 'requests', 1);
        
        if ($statusCode >= 400) {
            Redis::hincrby($endpointKey, 'errors', 1);
        }
        
        Redis::hset($endpointKey, 'last_used', now()->toISOString());
        Redis::expire($endpointKey, 86400 * 7);
    }

    /**
     * Get endpoint usage statistics
     */
    public static function getEndpointUsage(string $keyId): array
    {
        $pattern = 'api_endpoint_usage:' . $keyId . ':*';
        $keys = Redis::keys($pattern);
        $usage = [];

        foreach ($keys as $key) {
            $data = Redis::hgetall($key);
            if (!empty($data)) {
                $usage[] = $data;
            }
        }

        return $usage;
    }

    /**
     * Check rate limit for API key
     */
    public static function checkRateLimit(string $keyId, int $windowMinutes = 60): bool
    {
        $apiKeyData = self::getApiKey($keyId);
        
        if (!$apiKeyData) {
            return false;
        }

        $rateLimit = $apiKeyData['rate_limit'] ?? 1000;
        $window = now()->subMinutes($windowMinutes)->timestamp;
        
        $currentKey = 'api_rate_limit:' . $keyId . ':' . now()->format('Y-m-d:H:i');
        $currentCount = Redis::get($currentKey) ?? 0;
        
        if ($currentCount >= $rateLimit) {
            return false;
        }
        
        Redis::incr($currentKey);
        Redis::expire($currentKey, 60);
        
        return true;
    }

    /**
     * Get rate limit status
     */
    public static function getRateLimitStatus(string $keyId): array
    {
        $apiKeyData = self::getApiKey($keyId);
        
        if (!$apiKeyData) {
            return [];
        }

        $rateLimit = $apiKeyData['rate_limit'] ?? 1000;
        $currentKey = 'api_rate_limit:' . $keyId . ':' . now()->format('Y-m-d:H:i');
        $currentCount = Redis::get($currentKey) ?? 0;
        
        return [
            'limit' => $rateLimit,
            'remaining' => max(0, $rateLimit - $currentCount),
            'reset_time' => now()->addMinute()->toISOString(),
        ];
    }

    /**
     * Clean up expired API keys
     */
    public static function cleanupExpiredKeys(): int
    {
        $keys = Redis::keys('api_keys:*');
        $cleaned = 0;

        foreach ($keys as $key) {
            $keyData = Redis::hgetall($key);
            
            if (!empty($keyData) && 
                $keyData['expires_at'] && 
                Carbon::parse($keyData['expires_at'])->isPast()) {
                
                $keyId = $keyData['key_id'];
                self::revokeApiKey($keyId);
                $cleaned++;
            }
        }

        return $cleaned;
    }

    /**
     * Generate API key for external service
     */
    public static function generateExternalServiceKey(string $serviceName, array $config = []): array
    {
        $permissions = [
            'api:read',
            'matches:read',
            'players:read',
            'teams:read',
        ];

        $apiKey = self::generateApiKey(
            $serviceName,
            $permissions,
            $config['rate_limit'] ?? 5000
        );

        // Set expiration if provided
        if (isset($config['expires_at'])) {
            self::setExpiration($apiKey['key_id'], $config['expires_at']);
        }

        return $apiKey;
    }

    /**
     * Validate external service API key
     */
    public static function validateExternalServiceKey(string $apiKey, string $serviceName): ?array
    {
        $apiKeyData = self::validateApiKey($apiKey);
        
        if (!$apiKeyData) {
            return null;
        }

        // Check if the key name matches the service name
        if ($apiKeyData['name'] !== $serviceName) {
            return null;
        }

        // Check if key has required permissions
        $requiredPermissions = [
            'api:read',
            'matches:read',
            'players:read',
            'teams:read',
        ];

        foreach ($requiredPermissions as $permission) {
            if (!self::hasPermission($apiKeyData, $permission)) {
                return null;
            }
        }

        return $apiKeyData;
    }

    /**
     * Get API key audit log
     */
    public static function getAuditLog(string $keyId): array
    {
        $logKey = 'api_audit:' . $keyId;
        $logs = Redis::lrange($logKey, 0, 99);
        
        $auditLog = [];
        foreach ($logs as $log) {
            $auditLog[] = json_decode($log, true);
        }
        
        return $auditLog;
    }

    /**
     * Log API key activity
     */
    public static function logActivity(string $keyId, string $action, array $context = []): void
    {
        $logEntry = [
            'timestamp' => now()->toISOString(),
            'action' => $action,
            'context' => $context,
        ];

        $logKey = 'api_audit:' . $keyId;
        Redis::lpush($logKey, json_encode($logEntry));
        Redis::ltrim($logKey, 0, 99);
        Redis::expire($logKey, 86400 * 30); // Keep for 30 days
    }
}
