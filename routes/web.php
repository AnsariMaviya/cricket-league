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

// Analytics routes
Route::get('/analytics', [AnalyticsController::class, 'dashboard'])->name('analytics.dashboard');
Route::get('/analytics/data', [AnalyticsController::class, 'apiAnalytics'])->name('analytics.data');

// Resource routes
Route::resource('countries', CountryController::class)->parameters(['countries' => 'country:country_id']);
Route::resource('teams', TeamController::class)->parameters(['teams' => 'team:team_id']);
Route::resource('players', PlayerController::class)->parameters(['players' => 'player:player_id']);
Route::resource('venues', VenueController::class)->parameters(['venues' => 'venue:venue_id']);
Route::resource('matches', MatchController::class)->parameters(['matches' => 'match:match_id']);

// Additional routes
Route::get('/results', [MatchController::class, 'results'])->name('results');

// API routes
Route::prefix('api/v1')->group(function () {
    Route::get('/stats', [ApiController::class, 'stats']);
    Route::get('/countries', [ApiController::class, 'countries']);
    Route::get('/teams', [ApiController::class, 'teams']);
    Route::get('/players', [ApiController::class, 'players']);
    Route::get('/matches', [ApiController::class, 'matches']);
    Route::get('/venues', [ApiController::class, 'venues']);
    Route::get('/matches/{id}', [ApiController::class, 'matchDetails']);
    Route::get('/teams/{id}', [ApiController::class, 'teamDetails']);
    Route::get('/players/{id}', [ApiController::class, 'playerDetails']);
});

// Authentication routes (from Laravel UI)
Auth::routes();
