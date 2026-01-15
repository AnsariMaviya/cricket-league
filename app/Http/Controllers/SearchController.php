<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Team;
use App\Models\Player;
use App\Models\Venue;
use App\Models\CricketMatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q');
        $type = $request->get('type', 'all');
        $perPage = $request->get('per_page', 10);

        if (empty($query)) {
            return response()->json([
                'error' => 'Search query is required'
            ], 400);
        }

        $results = [];

        switch ($type) {
            case 'players':
                $results['players'] = $this->searchPlayers($query, $perPage);
                break;
            case 'teams':
                $results['teams'] = $this->searchTeams($query, $perPage);
                break;
            case 'matches':
                $results['matches'] = $this->searchMatches($query, $perPage);
                break;
            case 'venues':
                $results['venues'] = $this->searchVenues($query, $perPage);
                break;
            case 'countries':
                $results['countries'] = $this->searchCountries($query, $perPage);
                break;
            default:
                $results = array_merge(
                    $this->searchPlayers($query, 5),
                    $this->searchTeams($query, 5),
                    $this->searchMatches($query, 5),
                    $this->searchVenues($query, 5),
                    $this->searchCountries($query, 5)
                );
        }

        return response()->json([
            'query' => $query,
            'type' => $type,
            'results' => $results,
            'total_results' => array_sum(array_map('count', $results))
        ]);
    }

    private function searchPlayers($query, $perPage)
    {
        $cacheKey = "search_players_{$query}_{$perPage}";
        
        return Cache::remember($cacheKey, 300, function () use ($query, $perPage) {
            return Player::with(['team.country'])
                ->where('name', 'LIKE', "%{$query}%")
                ->orWhere('role', 'LIKE', "%{$query}%")
                ->orWhere('batting_style', 'LIKE', "%{$query}%")
                ->orWhere('bowling_style', 'LIKE', "%{$query}%")
                ->paginate($perPage)
                ->through(function ($player) {
                    return [
                        'id' => $player->player_id,
                        'name' => $player->name,
                        'role' => $player->role,
                        'team' => $player->team->team_name,
                        'country' => $player->team->country->name,
                        'age' => $player->dob?->age,
                        'profile_url' => route('players.show', $player)
                    ];
                });
        });
    }

    private function searchTeams($query, $perPage)
    {
        $cacheKey = "search_teams_{$query}_{$perPage}";
        
        return Cache::remember($cacheKey, 300, function () use ($query, $perPage) {
            return Team::with(['country', 'players'])
                ->where('team_name', 'LIKE', "%{$query}%")
                ->orWhere('in_match', 'LIKE', "%{$query}%")
                ->orWhereHas('country', function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%");
                })
                ->paginate($perPage)
                ->through(function ($team) {
                    return [
                        'id' => $team->team_id,
                        'name' => $team->team_name,
                        'country' => $team->country->name,
                        'players_count' => $team->players_count,
                        'status' => $team->in_match,
                        'url' => route('teams.index')
                    ];
                });
        });
    }

    private function searchMatches($query, $perPage)
    {
        $cacheKey = "search_matches_{$query}_{$perPage}";
        
        return Cache::remember($cacheKey, 300, function () use ($query, $perPage) {
            return CricketMatch::with(['firstTeam', 'secondTeam', 'venue'])
                ->where('match_type', 'LIKE', "%{$query}%")
                ->orWhere('status', 'LIKE', "%{$query}%")
                ->orWhere('outcome', 'LIKE', "%{$query}%")
                ->orWhereHas('firstTeam', function ($q) use ($query) {
                    $q->where('team_name', 'LIKE', "%{$query}%");
                })
                ->orWhereHas('secondTeam', function ($q) use ($query) {
                    $q->where('team_name', 'LIKE', "%{$query}%");
                })
                ->orWhereHas('venue', function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%");
                })
                ->paginate($perPage)
                ->through(function ($match) {
                    return [
                        'id' => $match->match_id,
                        'teams' => $match->firstTeam->team_name . ' vs ' . $match->secondTeam->team_name,
                        'venue' => $match->venue->name,
                        'type' => $match->match_type,
                        'status' => $match->status,
                        'date' => $match->match_date?->format('M d, Y'),
                        'score' => ($match->first_team_score ?? '-') . ' / ' . ($match->second_team_score ?? '-'),
                        'url' => route('matches.index')
                    ];
                });
        });
    }

    private function searchVenues($query, $perPage)
    {
        $cacheKey = "search_venues_{$query}_{$perPage}";
        
        return Cache::remember($cacheKey, 300, function () use ($query, $perPage) {
            return Venue::withCount('matches')
                ->where('name', 'LIKE', "%{$query}%")
                ->orWhere('address', 'LIKE', "%{$query}%")
                ->orWhere('city', 'LIKE', "%{$query}%")
                ->paginate($perPage)
                ->through(function ($venue) {
                    return [
                        'id' => $venue->venue_id,
                        'name' => $venue->name,
                        'address' => $venue->address,
                        'city' => $venue->city,
                        'capacity' => $venue->capacity ? number_format($venue->capacity) : null,
                        'matches_count' => $venue->matches_count,
                        'url' => route('venues.index')
                    ];
                });
        });
    }

    private function searchCountries($query, $perPage)
    {
        $cacheKey = "search_countries_{$query}_{$perPage}";
        
        return Cache::remember($cacheKey, 300, function () use ($query, $perPage) {
            return Country::withCount('teams')
                ->where('name', 'LIKE', "%{$query}%")
                ->orWhere('short_name', 'LIKE', "%{$query}%")
                ->paginate($perPage)
                ->through(function ($country) {
                    return [
                        'id' => $country->country_id,
                        'name' => $country->name,
                        'short_name' => $country->short_name,
                        'teams_count' => $country->teams_count,
                        'url' => route('countries.index')
                    ];
                });
        });
    }
}
