<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\ApiKeyService;
use App\Services\ErrorHandlingService;

class ApiKeyAuthMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $permission = null)
    {
        $apiKey = $request->header('X-API-Key') ?? $request->get('api_key');

        if (!$apiKey) {
            return response()->json([
                'error' => 'API Key Required',
                'message' => 'API key is required for this endpoint',
            ], 401);
        }

        // Validate API key
        $apiKeyData = ApiKeyService::validateApiKey($apiKey);

        if (!$apiKeyData) {
            return response()->json([
                'error' => 'Invalid API Key',
                'message' => 'The provided API key is invalid or has been revoked',
            ], 401);
        }

        // Check rate limit
        if (!ApiKeyService::checkRateLimit($apiKeyData['key_id'])) {
            return response()->json([
                'error' => 'Rate Limit Exceeded',
                'message' => 'API rate limit has been exceeded',
                'rate_limit' => ApiKeyService::getRateLimitStatus($apiKeyData['key_id']),
            ], 429);
        }

        // Check specific permission if provided
        if ($permission && !ApiKeyService::hasPermission($apiKeyData, $permission)) {
            return response()->json([
                'error' => 'Insufficient Permissions',
                'message' => 'API key does not have permission to access this resource',
                'required_permission' => $permission,
            ], 403);
        }

        // Add API key data to request for later use
        $request->merge(['api_key_data' => $apiKeyData]);

        // Record usage
        ApiKeyService::recordUsage(
            $apiKeyData['key_id'],
            $request->path(),
            200 // Will be updated if response has different status
        );

        $response = $next($request);

        // Update usage with actual status code
        ApiKeyService::recordUsage(
            $apiKeyData['key_id'],
            $request->path(),
            $response->getStatusCode()
        );

        // Add rate limit headers
        $rateLimitStatus = ApiKeyService::getRateLimitStatus($apiKeyData['key_id']);
        $response->headers->set('X-RateLimit-Limit', $rateLimitStatus['limit']);
        $response->headers->set('X-RateLimit-Remaining', $rateLimitStatus['remaining']);
        $response->headers->set('X-RateLimit-Reset', $rateLimitStatus['reset_time']);

        return $response;
    }
}
