<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Team;
use App\Models\Player;
use App\Models\Venue;
use App\Models\CricketMatch;

class HomeController extends Controller
{
    public function index()
    {
        $stats = [
            'countries' => Country::count(),
            'teams' => Team::count(),
            'players' => Player::count(),
            'venues' => Venue::count(),
            'matches' => CricketMatch::count(),
        ];

        $recentMatches = CricketMatch::with(['firstTeam', 'secondTeam', 'venue'])
            ->orderBy('match_date', 'desc')
            ->limit(5)
            ->get();

        $upcomingMatches = CricketMatch::with(['firstTeam', 'secondTeam', 'venue'])
            ->where('status', 'scheduled')
            ->orderBy('match_date', 'asc')
            ->limit(5)
            ->get();

        return view('home', compact('stats', 'recentMatches', 'upcomingMatches'));
    }
}
