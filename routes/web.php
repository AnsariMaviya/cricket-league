<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\Api\V1\ApiController;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Search routes
Route::get('/search', [SearchController::class, 'search'])->name('search');

// Analytics routes - serve SPA for analytics page
Route::get('/analytics', [HomeController::class, 'index'])->name('analytics.dashboard');
Route::get('/analytics/data', [AnalyticsController::class, 'apiAnalytics'])->name('analytics.data');

// SPA routes - serve Vue app for all frontend pages
Route::get('/countries', [HomeController::class, 'index'])->name('countries.index');
Route::get('/countries/create', [HomeController::class, 'index'])->name('countries.create');
Route::get('/countries/{country}', [HomeController::class, 'index'])->name('countries.show');
Route::get('/countries/{country}/edit', [HomeController::class, 'index'])->name('countries.edit');

Route::get('/teams', [HomeController::class, 'index'])->name('teams.index');
Route::get('/teams/create', [HomeController::class, 'index'])->name('teams.create');
Route::get('/teams/{team}', [HomeController::class, 'index'])->name('teams.show');
Route::get('/teams/{team}/edit', [HomeController::class, 'index'])->name('teams.edit');

Route::get('/players', [HomeController::class, 'index'])->name('players.index');
Route::get('/players/create', [HomeController::class, 'index'])->name('players.create');
Route::get('/players/{player}', [HomeController::class, 'index'])->name('players.show');
Route::get('/players/{player}/edit', [HomeController::class, 'index'])->name('players.edit');

Route::get('/reset-match-16', function() {
    $match = App\Models\CricketMatch::find(16);
    $match->status = 'scheduled';
    $match->current_innings = 0;
    $match->current_over = 0;
    $match->started_at = null;
    $match->save();
    
    // Delete existing match data
    $match->innings()->delete();
    $match->balls()->delete();
    $match->playerStats()->delete();
    $match->commentary()->delete();
    
    return 'Match 16 reset to scheduled! <a href="/matches">Go to Matches</a>';
});

Route::get('/venues', [HomeController::class, 'index'])->name('venues.index');
Route::get('/venues/create', [HomeController::class, 'index'])->name('venues.create');
Route::get('/venues/{venue}', [HomeController::class, 'index'])->name('venues.show');
Route::get('/venues/{venue}/edit', [HomeController::class, 'index'])->name('venues.edit');

Route::get('/matches', [HomeController::class, 'index'])->name('matches.index');
Route::get('/matches/create', [HomeController::class, 'index'])->name('matches.create');
Route::get('/matches/{match}', [HomeController::class, 'index'])->name('matches.show');
Route::get('/matches/{match}/edit', [HomeController::class, 'index'])->name('matches.edit');

Route::get('/live-matches', [HomeController::class, 'index'])->name('live-matches.index');
Route::get('/live-matches/{id}', [HomeController::class, 'index'])->name('live-matches.show');

Route::get('/predictions', [HomeController::class, 'index'])->name('predictions.index');
Route::get('/predictions/{id}', [HomeController::class, 'index'])->name('predictions.show');

// API routes for CRUD operations (keep these for the actual API calls)
Route::prefix('api/v1')->group(function () {
    Route::get('/countries', [ApiController::class, 'countries']);
    Route::post('/countries', [ApiController::class, 'createCountry']);
    Route::put('/countries/{id}', [ApiController::class, 'updateCountry']);
    Route::delete('/countries/{id}', [ApiController::class, 'deleteCountry']);
    
    Route::get('/teams', [ApiController::class, 'teams']);
    Route::post('/teams', [ApiController::class, 'createTeam']);
    Route::put('/teams/{id}', [ApiController::class, 'updateTeam']);
    Route::delete('/teams/{id}', [ApiController::class, 'deleteTeam']);
    
    Route::get('/players', [ApiController::class, 'players']);
    Route::post('/players', [ApiController::class, 'createPlayer']);
    Route::put('/players/{id}', [ApiController::class, 'updatePlayer']);
    Route::delete('/players/{id}', [ApiController::class, 'deletePlayer']);
    
    Route::get('/venues', [ApiController::class, 'venues']);
    Route::post('/venues', [ApiController::class, 'createVenue']);
    Route::put('/venues/{id}', [ApiController::class, 'updateVenue']);
    Route::delete('/venues/{id}', [ApiController::class, 'deleteVenue']);
    
    Route::get('/matches', [ApiController::class, 'matches']);
    Route::post('/matches', [ApiController::class, 'createMatch']);
    Route::put('/matches/{id}', [ApiController::class, 'updateMatch']);
    Route::delete('/matches/{id}', [ApiController::class, 'deleteMatch']);
});

// Additional API routes
Route::prefix('api/v1')->group(function () {
    Route::get('/stats', [ApiController::class, 'stats']);
    Route::get('/search', [ApiController::class, 'search']);
    Route::get('/matches/{id}', [ApiController::class, 'matchDetails']);
    Route::get('/teams/{id}', [ApiController::class, 'teamDetails']);
    Route::get('/players/{id}', [ApiController::class, 'playerDetails']);
});

// Additional routes
Route::get('/results', [MatchController::class, 'results'])->name('results');

// Live Match API Routes
Route::prefix('api/v1')->group(function () {
    Route::prefix('live-matches')->group(function () {
        Route::get('/', [\App\Http\Controllers\LiveMatchController::class, 'getLiveMatches']);
        Route::get('/upcoming', [\App\Http\Controllers\LiveMatchController::class, 'getUpcomingMatches']);
        Route::get('/{matchId}/scoreboard', [\App\Http\Controllers\LiveMatchController::class, 'getScoreboard']);
        Route::get('/{matchId}/mini-scoreboard', [\App\Http\Controllers\LiveMatchController::class, 'getMiniScoreboard']);
        Route::get('/{matchId}/summary', [\App\Http\Controllers\LiveMatchController::class, 'getMatchSummary']);
        Route::get('/{matchId}/over/{overNumber}', [\App\Http\Controllers\LiveMatchController::class, 'getOverSummary']);
        Route::get('/{matchId}/commentary', [\App\Http\Controllers\LiveMatchController::class, 'getAllCommentary']);
        
        Route::post('/{matchId}/start', [\App\Http\Controllers\LiveMatchController::class, 'startMatch']);
        Route::post('/{matchId}/simulate-ball', [\App\Http\Controllers\LiveMatchController::class, 'simulateBall']);
        Route::post('/{matchId}/start-auto-simulation', [\App\Http\Controllers\LiveMatchController::class, 'startAutoSimulation']);
        Route::post('/{matchId}/stop-auto-simulation', [\App\Http\Controllers\LiveMatchController::class, 'stopAutoSimulation']);
        Route::post('/{matchId}/stop', [\App\Http\Controllers\LiveMatchController::class, 'stopMatch']);
    });

    Route::prefix('predictions')->group(function () {
        Route::get('/match/{matchId}', [\App\Http\Controllers\PredictionController::class, 'generatePrediction']);
        Route::get('/match/{matchId}/user', [\App\Http\Controllers\PredictionController::class, 'getUserPrediction']);
        Route::post('/match/{matchId}', [\App\Http\Controllers\PredictionController::class, 'submitPrediction']);
        Route::get('/player/{playerId}/analysis', [\App\Http\Controllers\PredictionController::class, 'analyzePlayer']);
        Route::get('/team/{teamId}/recommend', [\App\Http\Controllers\PredictionController::class, 'recommendTeam']);
    });


    // Tournament routes
    Route::prefix('tournaments')->group(function () {
        Route::get('/', [\App\Http\Controllers\TournamentController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\TournamentController::class, 'store']);
        Route::get('/{id}', [\App\Http\Controllers\TournamentController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\TournamentController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\TournamentController::class, 'destroy']);
        Route::post('/{id}/teams', [\App\Http\Controllers\TournamentController::class, 'addTeam']);
        Route::delete('/{id}/teams/{teamId}', [\App\Http\Controllers\TournamentController::class, 'removeTeam']);
        Route::get('/{id}/points-table', [\App\Http\Controllers\TournamentController::class, 'getPointsTable']);
        Route::post('/{id}/generate-fixtures', [\App\Http\Controllers\TournamentController::class, 'generateFixtures']);
        Route::get('/{id}/stats', [\App\Http\Controllers\TournamentController::class, 'getStats']);
        Route::post('/{id}/update-standings', [\App\Http\Controllers\TournamentController::class, 'updateStandings']);
    });

    // Stats routes
    Route::prefix('stats')->group(function () {
        Route::get('/players/{playerId}', [\App\Http\Controllers\StatsController::class, 'getPlayerStats']);
        Route::post('/players/{playerId}/update', [\App\Http\Controllers\StatsController::class, 'updatePlayerStats']);
        Route::get('/top-batsmen', [\App\Http\Controllers\StatsController::class, 'getTopBatsmen']);
        Route::get('/top-bowlers', [\App\Http\Controllers\StatsController::class, 'getTopBowlers']);
        Route::get('/matches/{matchId}', [\App\Http\Controllers\StatsController::class, 'getMatchStats']);
        Route::get('/teams/head-to-head/{team1Id}/{team2Id}', [\App\Http\Controllers\StatsController::class, 'getTeamHeadToHead']);
    });
});

// Catch-all route for Vue SPA - MUST be last
Route::get('/{vue_capture?}', function () {
    return view('app');
})->where('vue_capture', '[\/\w\.-]*');
