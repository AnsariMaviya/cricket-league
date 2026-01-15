<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\MatchController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::resource('countries', CountryController::class)->parameters(['countries' => 'country:country_id']);
Route::resource('teams', TeamController::class)->parameters(['teams' => 'team:team_id']);
Route::resource('players', PlayerController::class)->parameters(['players' => 'player:player_id']);
Route::resource('venues', VenueController::class)->parameters(['venues' => 'venue:venue_id']);
Route::resource('matches', MatchController::class)->parameters(['matches' => 'match:match_id']);

Route::get('/results', [MatchController::class, 'results'])->name('results');
