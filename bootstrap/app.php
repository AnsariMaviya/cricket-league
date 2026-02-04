<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'api/*'
        ]);
        
        // Register middleware aliases
        $middleware->alias([
            'api.cache' => \App\Http\Middleware\CacheApiResponse::class,
            'api.rate_limit' => \App\Http\Middleware\AdvancedRateLimiter::class,
            'api.sanitize' => \App\Http\Middleware\InputSanitizationMiddleware::class,
            'api.monitor' => \App\Http\Middleware\QueryPerformanceMonitor::class,
            'api.auth_key' => \App\Http\Middleware\ApiKeyAuthMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withProviders([
        \App\Providers\CacheServiceProvider::class,
    ])->create();
