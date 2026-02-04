<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\ErrorHandlingService;
use Throwable;

class GlobalExceptionHandler
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (Throwable $exception) {
            $errorResponse = ErrorHandlingService::handleException($exception, $request);
            
            return response()->json(
                $errorResponse['response'],
                $errorResponse['status_code']
            );
        }
    }
}
