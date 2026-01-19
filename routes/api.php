<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LiveMatchController;
use App\Http\Controllers\PredictionController;

Route::prefix('v1')->group(function () {
    
    Route::prefix('live-matches')->group(function () {
        Route::get('/', [LiveMatchController::class, 'getLiveMatches']);
        Route::get('/upcoming', [LiveMatchController::class, 'getUpcomingMatches']);
        Route::get('/{matchId}/scoreboard', [LiveMatchController::class, 'getScoreboard']);
        Route::get('/{matchId}/mini-scoreboard', [LiveMatchController::class, 'getMiniScoreboard']);
        Route::get('/{matchId}/summary', [LiveMatchController::class, 'getMatchSummary']);
        Route::get('/{matchId}/over/{overNumber}', [LiveMatchController::class, 'getOverSummary']);
        
        Route::post('/{matchId}/start', [LiveMatchController::class, 'startMatch']);
        Route::post('/{matchId}/simulate-ball', [LiveMatchController::class, 'simulateBall']);
        Route::post('/{matchId}/start-auto-simulation', [LiveMatchController::class, 'startAutoSimulation']);
        Route::post('/{matchId}/stop-auto-simulation', [LiveMatchController::class, 'stopAutoSimulation']);
        Route::post('/{matchId}/stop', [LiveMatchController::class, 'stopMatch']);
    });

    Route::prefix('predictions')->group(function () {
        Route::get('/match/{matchId}', [PredictionController::class, 'generatePrediction']);
        Route::get('/match/{matchId}/user', [PredictionController::class, 'getUserPrediction']);
        Route::post('/match/{matchId}', [PredictionController::class, 'submitPrediction']);
        Route::get('/player/{playerId}/analysis', [PredictionController::class, 'analyzePlayer']);
        Route::get('/team/{teamId}/recommend', [PredictionController::class, 'recommendTeam']);
    });
});
