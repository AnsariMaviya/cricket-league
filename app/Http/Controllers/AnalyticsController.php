<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Team;
use App\Models\Player;
use App\Models\Venue;
use App\Models\CricketMatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Get comprehensive analytics dashboard
     */
    public function dashboard()
    {
        $cacheKey = 'analytics_dashboard';
        
        $analytics = Cache::remember($cacheKey, 1800, function () {
            return [
                'overview' => $this->getOverviewStats(),
                'team_performance' => $this->getTeamPerformanceStats(),
                'player_statistics' => $this->getPlayerStatistics(),
                'venue_analysis' => $this->getVenueAnalysis(),
                'match_trends' => $this->getMatchTrends(),
                'country_rankings' => $this->getCountryRankings(),
            ];
        });

        return view('analytics.dashboard', compact('analytics'));
    }

    /**
     * Get overview statistics
     */
    private function getOverviewStats()
    {
        return [
            'total_countries' => Country::count(),
            'total_teams' => Team::count(),
            'total_players' => Player::count(),
            'total_venues' => Venue::count(),
            'total_matches' => CricketMatch::count(),
            'completed_matches' => CricketMatch::where('status', 'completed')->count(),
            'upcoming_matches' => CricketMatch::where('status', 'scheduled')->count(),
            'live_matches' => CricketMatch::where('status', 'live')->count(),
            'average_players_per_team' => Team::withCount('players')->avg('players_count'),
            'average_matches_per_venue' => Venue::withCount('matches')->avg('matches_count'),
        ];
    }

    /**
     * Get team performance statistics
     */
    private function getTeamPerformanceStats()
    {
        $teams = Team::with(['country', 'homeMatches', 'awayMatches'])->get();
        
        $teamStats = $teams->map(function ($team) {
            $allMatches = $team->homeMatches->concat($team->awayMatches);
            $completedMatches = $allMatches->where('status', 'completed');
            
            $wins = 0;
            $losses = 0;
            $draws = 0;
            
            foreach ($completedMatches as $match) {
                if ($match->outcome) {
                    if (stripos($match->outcome, $team->team_name) !== false) {
                        if (stripos($match->outcome, 'won') !== false) {
                            $wins++;
                        } elseif (stripos($match->outcome, 'lost') !== false) {
                            $losses++;
                        } else {
                            $draws++;
                        }
                    }
                }
            }
            
            $totalPlayed = $wins + $losses + $draws;
            $winRate = $totalPlayed > 0 ? ($wins / $totalPlayed) * 100 : 0;
            
            return [
                'team_id' => $team->team_id,
                'team_name' => $team->team_name,
                'country' => $team->country->name,
                'players_count' => $team->players_count,
                'matches_played' => $totalPlayed,
                'wins' => $wins,
                'losses' => $losses,
                'draws' => $draws,
                'win_rate' => round($winRate, 2),
                'recent_form' => $this->getTeamRecentForm($team),
            ];
        });
        
        return [
            'top_teams' => $teamStats->sortByDesc('win_rate')->take(10)->values(),
            'most_active' => $teamStats->sortByDesc('matches_played')->take(10)->values(),
            'largest_squads' => $teamStats->sortByDesc('players_count')->take(10)->values(),
        ];
    }

    /**
     * Get player statistics
     */
    private function getPlayerStatistics()
    {
        $playersByRole = Player::select('role', DB::raw('count(*) as count'))
            ->whereNotNull('role')
            ->groupBy('role')
            ->orderBy('count', 'desc')
            ->get();
        
        $playersByAge = Player::select(DB::raw('CASE 
                WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) < 25 THEN "Under 25"
                WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 25 AND 30 THEN "25-30"
                WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 31 AND 35 THEN "31-35"
                ELSE "Over 35"
            END as age_group'), DB::raw('count(*) as count'))
            ->whereNotNull('dob')
            ->groupBy('age_group')
            ->orderBy('age_group')
            ->get();
        
        $playersByBattingStyle = Player::select('batting_style', DB::raw('count(*) as count'))
            ->whereNotNull('batting_style')
            ->groupBy('batting_style')
            ->orderBy('count', 'desc')
            ->get();
        
        $playersByBowlingStyle = Player::select('bowling_style', DB::raw('count(*) as count'))
            ->whereNotNull('bowling_style')
            ->groupBy('bowling_style')
            ->orderBy('count', 'desc')
            ->get();
        
        return [
            'by_role' => $playersByRole,
            'by_age' => $playersByAge,
            'by_batting_style' => $playersByBattingStyle,
            'by_bowling_style' => $playersByBowlingStyle,
            'total_with_dob' => Player::whereNotNull('dob')->count(),
            'average_age' => Player::whereNotNull('dob')->avg(DB::raw('TIMESTAMPDIFF(YEAR, dob, CURDATE())')),
        ];
    }

    /**
     * Get venue analysis
     */
    private function getVenueAnalysis()
    {
        $venues = Venue::withCount('matches')->get();
        
        $venueStats = $venues->map(function ($venue) {
            $matches = $venue->matches;
            $completedMatches = $matches->where('status', 'completed');
            
            $averageRuns = 0;
            if ($completedMatches->count() > 0) {
                $totalRuns = 0;
                $validScores = 0;
                
                foreach ($completedMatches as $match) {
                    if ($match->first_team_score) {
                        $score = explode('/', $match->first_team_score)[0];
                        if (is_numeric($score)) {
                            $totalRuns += (int)$score;
                            $validScores++;
                        }
                    }
                    if ($match->second_team_score) {
                        $score = explode('/', $match->second_team_score)[0];
                        if (is_numeric($score)) {
                            $totalRuns += (int)$score;
                            $validScores++;
                        }
                    }
                }
                
                $averageRuns = $validScores > 0 ? $totalRuns / $validScores : 0;
            }
            
            return [
                'venue_id' => $venue->venue_id,
                'name' => $venue->name,
                'city' => $venue->city,
                'capacity' => $venue->capacity,
                'matches_hosted' => $venue->matches_count,
                'completed_matches' => $completedMatches->count(),
                'average_runs_per_match' => round($averageRuns, 2),
                'utilization_rate' => $venue->matches_count > 0 ? round(($completedMatches->count() / $venue->matches_count) * 100, 2) : 0,
            ];
        });
        
        return [
            'most_used' => $venueStats->sortByDesc('matches_hosted')->take(10)->values(),
            'highest_scoring' => $venueStats->sortByDesc('average_runs_per_match')->take(10)->values(),
            'largest_capacities' => $venueStats->where('capacity', '>', 0)->sortByDesc('capacity')->take(10)->values(),
        ];
    }

    /**
     * Get match trends
     */
    private function getMatchTrends()
    {
        $matchesByMonth = CricketMatch::select(
                DB::raw('DATE_FORMAT(match_date, "%Y-%m") as month'),
                DB::raw('count(*) as count')
            )
            ->whereNotNull('match_date')
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        $matchesByType = CricketMatch::select('match_type', DB::raw('count(*) as count'))
            ->groupBy('match_type')
            ->orderBy('count', 'desc')
            ->get();
        
        $matchesByStatus = CricketMatch::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->orderBy('count', 'desc')
            ->get();
        
        return [
            'monthly_trends' => $matchesByMonth,
            'by_type' => $matchesByType,
            'by_status' => $matchesByStatus,
        ];
    }

    /**
     * Get country rankings
     */
    private function getCountryRankings()
    {
        $countries = Country::with(['teams' => function ($query) {
                $query->withCount('players');
            }])->get();
        
        $countryStats = $countries->map(function ($country) {
            $totalTeams = $country->teams->count();
            $totalPlayers = $country->teams->sum('players_count');
            
            // Get all matches for teams from this country
            $teamIds = $country->teams->pluck('team_id');
            $matches = CricketMatch::where(function ($query) use ($teamIds) {
                $query->whereIn('first_team_id', $teamIds)
                      ->orWhereIn('second_team_id', $teamIds);
            })->get();
            
            return [
                'country_id' => $country->country_id,
                'name' => $country->name,
                'short_name' => $country->short_name,
                'teams_count' => $totalTeams,
                'players_count' => $totalPlayers,
                'matches_involvement' => $matches->count(),
                'completed_matches' => $matches->where('status', 'completed')->count(),
                'average_players_per_team' => $totalTeams > 0 ? round($totalPlayers / $totalTeams, 2) : 0,
            ];
        });
        
        return [
            'by_teams' => $countryStats->sortByDesc('teams_count')->values(),
            'by_players' => $countryStats->sortByDesc('players_count')->values(),
            'by_matches' => $countryStats->sortByDesc('matches_involvement')->values(),
        ];
    }

    /**
     * Get team recent form (last 5 matches)
     */
    private function getTeamRecentForm($team)
    {
        $allMatches = $team->homeMatches->concat($team->awayMatches)
            ->where('status', 'completed')
            ->sortByDesc('match_date')
            ->take(5);
        
        $form = [];
        foreach ($allMatches as $match) {
            if ($match->outcome) {
                if (stripos($match->outcome, $team->team_name) !== false) {
                    if (stripos($match->outcome, 'won') !== false) {
                        $form[] = 'W';
                    } elseif (stripos($match->outcome, 'lost') !== false) {
                        $form[] = 'L';
                    } else {
                        $form[] = 'D';
                    }
                } else {
                    $form[] = 'L';
                }
            }
        }
        
        return $form;
    }

    /**
     * API endpoint for analytics data
     */
    public function apiAnalytics(Request $request)
    {
        $type = $request->get('type', 'overview');
        
        switch ($type) {
            case 'overview':
                return response()->json($this->getOverviewStats());
            case 'team_performance':
                return response()->json($this->getTeamPerformanceStats());
            case 'player_statistics':
                return response()->json($this->getPlayerStatistics());
            case 'venue_analysis':
                return response()->json($this->getVenueAnalysis());
            case 'match_trends':
                return response()->json($this->getMatchTrends());
            case 'country_rankings':
                return response()->json($this->getCountryRankings());
            default:
                return response()->json(['error' => 'Invalid analytics type'], 400);
        }
    }
}
