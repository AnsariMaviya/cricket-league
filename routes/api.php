<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LiveMatchController;
use App\Http\Controllers\PredictionController;

Route::prefix('v1')->group(function () {
    
    Route::prefix('live-matches')->group(function () {
        Route::get('/', [LiveMatchController::class, 'getLiveMatches'])
            ->middleware(['api.cache', 'api.rate_limit:live', 'api.monitor']);
        Route::get('/upcoming', [LiveMatchController::class, 'getUpcomingMatches'])
            ->middleware(['api.cache', 'api.rate_limit:live', 'api.monitor']);
        Route::get('/{matchId}/scoreboard', [LiveMatchController::class, 'getScoreboard'])
            ->middleware(['api.cache', 'api.rate_limit:live', 'api.monitor']);
        Route::get('/{matchId}/mini-scoreboard', [LiveMatchController::class, 'getMiniScoreboard'])
            ->middleware(['api.cache', 'api.rate_limit:live', 'api.monitor']);
        Route::get('/{matchId}/summary', [LiveMatchController::class, 'getMatchSummary'])
            ->middleware(['api.cache', 'api.rate_limit:live', 'api.monitor']);
        Route::get('/{matchId}/over/{overNumber}', [LiveMatchController::class, 'getOverSummary'])
            ->middleware(['api.cache', 'api.rate_limit:live', 'api.monitor']);
        
        Route::post('/{matchId}/start', [LiveMatchController::class, 'startMatch'])
            ->middleware(['api.sanitize', 'api.rate_limit:simulation', 'api.monitor']);
        Route::post('/{matchId}/simulate-ball', [LiveMatchController::class, 'simulateBall'])
            ->middleware(['api.sanitize', 'api.rate_limit:simulation', 'api.monitor']);
        Route::post('/{matchId}/start-auto-simulation', [LiveMatchController::class, 'startAutoSimulation'])
            ->middleware(['api.sanitize', 'api.rate_limit:simulation', 'api.monitor']);
        Route::post('/{matchId}/stop-auto-simulation', [LiveMatchController::class, 'stopAutoSimulation'])
            ->middleware(['api.sanitize', 'api.rate_limit:simulation', 'api.monitor']);
        Route::post('/{matchId}/stop', [LiveMatchController::class, 'stopMatch'])
            ->middleware(['api.sanitize', 'api.rate_limit:simulation', 'api.monitor']);
    });

    Route::prefix('predictions')->group(function () {
        Route::get('/match/{matchId}', [PredictionController::class, 'generatePrediction'])
            ->middleware(['api.cache', 'api.rate_limit:prediction', 'api.monitor']);
        Route::get('/match/{matchId}/user', [PredictionController::class, 'getUserPrediction'])
            ->middleware(['api.cache', 'api.rate_limit:prediction', 'api.monitor']);
        Route::post('/match/{matchId}', [PredictionController::class, 'submitPrediction'])
            ->middleware(['api.sanitize', 'api.rate_limit:prediction', 'api.monitor']);
        Route::get('/player/{playerId}/analysis', [PredictionController::class, 'analyzePlayer'])
            ->middleware(['api.cache', 'api.rate_limit:prediction', 'api.monitor']);
        Route::get('/team/{teamId}/recommend', [PredictionController::class, 'recommendTeam'])
            ->middleware(['api.cache', 'api.rate_limit:prediction', 'api.monitor']);
    });

    // Monitoring and management routes
    Route::prefix('monitoring')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\MonitoringController::class, 'dashboard']);
        Route::get('/health', [App\Http\Controllers\MonitoringController::class, 'health']);
        Route::get('/events', [App\Http\Controllers\MonitoringController::class, 'events']);
        Route::get('/api-stats', [App\Http\Controllers\MonitoringController::class, 'apiStats']);
        Route::get('/performance', [App\Http\Controllers\MonitoringController::class, 'performance']);
        Route::get('/cache-stats', [App\Http\Controllers\MonitoringController::class, 'cacheStats']);
        Route::get('/security-events', [App\Http\Controllers\MonitoringController::class, 'securityEvents']);
        Route::get('/slow-queries', [App\Http\Controllers\MonitoringController::class, 'slowQueries']);
        Route::get('/report', [App\Http\Controllers\MonitoringController::class, 'report']);
        Route::get('/summary', [App\Http\Controllers\MonitoringController::class, 'summary']);
    })->middleware(['api.rate_limit:api']);

    // Rate limiting management routes
    Route::prefix('rate-limit')->group(function () {
        Route::get('/stats', [App\Http\Controllers\RateLimitController::class, 'getStats']);
        Route::get('/top-clients', [App\Http\Controllers\RateLimitController::class, 'getTopClients']);
        Route::get('/blocked-clients', [App\Http\Controllers\RateLimitController::class, 'getBlockedClients']);
        Route::post('/block-client', [App\Http\Controllers\RateLimitController::class, 'blockClient']);
        Route::post('/unblock-client', [App\Http\Controllers\RateLimitController::class, 'unblockClient']);
        Route::post('/clear-data', [App\Http\Controllers\RateLimitController::class, 'clearData']);
        Route::get('/config', [App\Http\Controllers\RateLimitController::class, 'getConfig']);
        Route::get('/test', [App\Http\Controllers\RateLimitController::class, 'testRateLimit']);
    })->middleware(['api.rate_limit:api']);
});
