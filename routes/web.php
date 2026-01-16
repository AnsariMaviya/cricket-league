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

Route::get('/venues', [HomeController::class, 'index'])->name('venues.index');
Route::get('/venues/create', [HomeController::class, 'index'])->name('venues.create');
Route::get('/venues/{venue}', [HomeController::class, 'index'])->name('venues.show');
Route::get('/venues/{venue}/edit', [HomeController::class, 'index'])->name('venues.edit');

Route::get('/matches', [HomeController::class, 'index'])->name('matches.index');
Route::get('/matches/create', [HomeController::class, 'index'])->name('matches.create');
Route::get('/matches/{match}', [HomeController::class, 'index'])->name('matches.show');
Route::get('/matches/{match}/edit', [HomeController::class, 'index'])->name('matches.edit');

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
