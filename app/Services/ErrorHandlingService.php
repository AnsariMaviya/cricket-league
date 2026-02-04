<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Exception;
use Throwable;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class ErrorHandlingService
{
    /**
     * Handle and log exceptions
     */
    public static function handleException(Throwable $exception, Request $request = null): array
    {
        $errorData = [
            'error_id' => uniqid('error_', true),
            'timestamp' => now()->toISOString(),
            'exception_class' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => self::formatTrace($exception->getTrace()),
            'request' => self::formatRequest($request),
            'user' => self::formatUser(),
        ];

        // Log the error
        self::logError($errorData, $exception);

        // Determine error type and response
        $response = self::formatErrorResponse($exception, $errorData);

        return $response;
    }

    /**
     * Format exception trace
     */
    private static function formatTrace(array $trace): array
    {
        $formattedTrace = [];
        
        foreach (array_slice($trace, 0, 10) as $item) {
            $formattedTrace[] = [
                'file' => $item['file'] ?? 'unknown',
                'line' => $item['line'] ?? 0,
                'function' => $item['function'] ?? 'unknown',
                'class' => $item['class'] ?? null,
                'type' => $item['type'] ?? null,
            ];
        }
        
        return $formattedTrace;
    }

    /**
     * Format request information
     */
    private static function formatRequest(?Request $request): ?array
    {
        if (!$request) {
            return null;
        }

        return [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'headers' => self::sanitizeHeaders($request->headers->all()),
            'parameters' => self::sanitizeParameters($request->all()),
        ];
    }

    /**
     * Format user information
     */
    private static function formatUser(): ?array
    {
        if (!auth()->check()) {
            return null;
        }

        $user = auth()->user();
        
        return [
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name ?? null,
        ];
    }

    /**
     * Sanitize headers for logging
     */
    private static function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = ['authorization', 'cookie', 'x-api-key'];
        
        foreach ($headers as $key => $value) {
            if (in_array(strtolower($key), $sensitiveHeaders)) {
                $headers[$key] = ['***REDACTED***'];
            }
        }
        
        return $headers;
    }

    /**
     * Sanitize parameters for logging
     */
    private static function sanitizeParameters(array $parameters): array
    {
        $sensitiveKeys = ['password', 'password_confirmation', 'api_key', 'token', 'secret'];
        
        foreach ($parameters as $key => $value) {
            if (in_array(strtolower($key), $sensitiveKeys)) {
                $parameters[$key] = '***REDACTED***';
            } elseif (is_array($value)) {
                $parameters[$key] = self::sanitizeParameters($value);
            }
        }
        
        return $parameters;
    }

    /**
     * Log error with appropriate level
     */
    private static function logError(array $errorData, Throwable $exception): void
    {
        $context = [
            'error_id' => $errorData['error_id'],
            'exception' => $errorData['exception_class'],
            'message' => $errorData['message'],
            'file' => $errorData['file'],
            'line' => $errorData['line'],
            'request' => $errorData['request'],
            'user' => $errorData['user'],
        ];

        // Determine log level based on exception type
        $level = self::getLogLevel($exception);
        
        Log::log($level, 'Exception occurred', $context);

        // Store in Redis for error tracking
        self::storeErrorInRedis($errorData);
    }

    /**
     * Get log level for exception
     */
    private static function getLogLevel(Throwable $exception): string
    {
        if ($exception instanceof ValidationException) {
            return 'warning';
        }
        
        if ($exception instanceof AuthenticationException) {
            return 'info';
        }
        
        if ($exception instanceof AuthorizationException) {
            return 'warning';
        }
        
        if ($exception instanceof ModelNotFoundException) {
            return 'warning';
        }
        
        if ($exception instanceof NotFoundHttpException) {
            return 'info';
        }
        
        if ($exception instanceof MethodNotAllowedHttpException) {
            return 'warning';
        }
        
        if ($exception instanceof TooManyRequestsHttpException) {
            return 'warning';
        }
        
        return 'error';
    }

    /**
     * Store error in Redis for tracking
     */
    private static function storeErrorInRedis(array $errorData): void
    {
        try {
            if (extension_loaded('redis')) {
                $key = 'errors:' . date('Y-m-d');
                \Illuminate\Support\Facades\Redis::lpush($key, json_encode($errorData));
                
                // Keep only last 1000 errors per day
                \Illuminate\Support\Facades\Redis::ltrim($key, 0, 999);
                
                // Set expiration to 30 days
                \Illuminate\Support\Facades\Redis::expire($key, 2592000);
            }
        } catch (\Exception $e) {
            \Log::warning('Redis not available for error storage: ' . $e->getMessage());
        }
    }

    /**
     * Format error response
     */
    private static function formatErrorResponse(Throwable $exception, array $errorData): array
    {
        $statusCode = self::getStatusCode($exception);
        $errorCode = self::getErrorCode($exception);
        
        $response = [
            'error' => true,
            'error_id' => $errorData['error_id'],
            'error_code' => $errorCode,
            'message' => self::getUserFriendlyMessage($exception),
            'timestamp' => $errorData['timestamp'],
        ];

        // Add validation errors if applicable
        if ($exception instanceof ValidationException) {
            $response['errors'] = $exception->errors();
        }

        // Add debug information in development
        if (app()->environment(['local', 'testing'])) {
            $response['debug'] = [
                'exception' => $errorData['exception_class'],
                'file' => $errorData['file'],
                'line' => $errorData['line'],
                'trace' => $errorData['trace'],
            ];
        }

        return [
            'status_code' => $statusCode,
            'response' => $response,
        ];
    }

    /**
     * Get HTTP status code for exception
     */
    private static function getStatusCode(Throwable $exception): int
    {
        if ($exception instanceof HttpException) {
            return $exception->getStatusCode();
        }
        
        if ($exception instanceof ValidationException) {
            return 422;
        }
        
        if ($exception instanceof AuthenticationException) {
            return 401;
        }
        
        if ($exception instanceof AuthorizationException) {
            return 403;
        }
        
        if ($exception instanceof ModelNotFoundException) {
            return 404;
        }
        
        if ($exception instanceof NotFoundHttpException) {
            return 404;
        }
        
        if ($exception instanceof MethodNotAllowedHttpException) {
            return 405;
        }
        
        if ($exception instanceof TooManyRequestsHttpException) {
            return 429;
        }
        
        return 500;
    }

    /**
     * Get error code for exception
     */
    private static function getErrorCode(Throwable $exception): string
    {
        if ($exception instanceof ValidationException) {
            return 'VALIDATION_ERROR';
        }
        
        if ($exception instanceof AuthenticationException) {
            return 'AUTHENTICATION_ERROR';
        }
        
        if ($exception instanceof AuthorizationException) {
            return 'AUTHORIZATION_ERROR';
        }
        
        if ($exception instanceof ModelNotFoundException) {
            return 'NOT_FOUND';
        }
        
        if ($exception instanceof NotFoundHttpException) {
            return 'NOT_FOUND';
        }
        
        if ($exception instanceof MethodNotAllowedHttpException) {
            return 'METHOD_NOT_ALLOWED';
        }
        
        if ($exception instanceof TooManyRequestsHttpException) {
            return 'RATE_LIMIT_EXCEEDED';
        }
        
        return 'INTERNAL_SERVER_ERROR';
    }

    /**
     * Get user-friendly error message
     */
    private static function getUserFriendlyMessage(Throwable $exception): string
    {
        if ($exception instanceof ValidationException) {
            return 'The provided data is invalid. Please check your input and try again.';
        }
        
        if ($exception instanceof AuthenticationException) {
            return 'Authentication required. Please log in to continue.';
        }
        
        if ($exception instanceof AuthorizationException) {
            return 'You do not have permission to perform this action.';
        }
        
        if ($exception instanceof ModelNotFoundException) {
            return 'The requested resource was not found.';
        }
        
        if ($exception instanceof NotFoundHttpException) {
            return 'The requested page was not found.';
        }
        
        if ($exception instanceof MethodNotAllowedHttpException) {
            return 'The request method is not allowed for this endpoint.';
        }
        
        if ($exception instanceof TooManyRequestsHttpException) {
            return 'Too many requests. Please try again later.';
        }
        
        return 'An unexpected error occurred. Please try again later.';
    }

    /**
     * Create custom exception
     */
    public static function createException(string $message, string $code = 'CUSTOM_ERROR', int $statusCode = 400): Exception
    {
        return new CustomException($message, $code, $statusCode);
    }

    /**
     * Get error statistics
     */
    public static function getErrorStats(): array
    {
        $stats = [];
        $keys = \Illuminate\Support\Facades\Redis::keys('errors:*');
        
        foreach ($keys as $key) {
            $date = str_replace('errors:', '', $key);
            $errors = \Illuminate\Support\Facades\Redis::lrange($key, 0, -1);
            
            $errorCounts = [];
            foreach ($errors as $error) {
                $errorData = json_decode($error, true);
                $errorCode = $errorData['exception_class'] ?? 'Unknown';
                $errorCounts[$errorCode] = ($errorCounts[$errorCode] ?? 0) + 1;
            }
            
            $stats[$date] = [
                'total_errors' => count($errors),
                'error_breakdown' => $errorCounts,
            ];
        }
        
        return $stats;
    }

    /**
     * Get recent errors
     */
    public static function getRecentErrors(int $limit = 50): array
    {
        $key = 'errors:' . date('Y-m-d');
        $errors = \Illuminate\Support\Facades\Redis::lrange($key, 0, $limit - 1);
        
        $recentErrors = [];
        foreach ($errors as $error) {
            $recentErrors[] = json_decode($error, true);
        }
        
        return $recentErrors;
    }

    /**
     * Clear error logs
     */
    public static function clearErrorLogs(): void
    {
        $keys = \Illuminate\Support\Facades\Redis::keys('errors:*');
        if ($keys) {
            \Illuminate\Support\Facades\Redis::del($keys);
        }
    }
}

/**
 * Custom Exception Class
 */
class CustomException extends Exception
{
    protected $errorCode;
    protected $statusCode;

    public function __construct(string $message, string $errorCode = 'CUSTOM_ERROR', int $statusCode = 400, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->errorCode = $errorCode;
        $this->statusCode = $statusCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
