<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CountryService;
use App\Services\TeamService;
use App\Services\PlayerService;
use App\Services\VenueService;
use App\Services\MatchService;
use App\Models\Country;
use App\Models\Team;
use App\Models\Player;
use App\Models\Venue;
use App\Models\CricketMatch;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use App\Traits\HasApiResponses;

class ApiController extends Controller
{
    use HasApiResponses;

    protected CountryService $countryService;
    protected TeamService $teamService;
    protected PlayerService $playerService;
    protected VenueService $venueService;
    protected MatchService $matchService;

    public function __construct(
        CountryService $countryService,
        TeamService $teamService,
        PlayerService $playerService,
        VenueService $venueService,
        MatchService $matchService
    ) {
        $this->countryService = $countryService;
        $this->teamService = $teamService;
        $this->playerService = $playerService;
        $this->venueService = $venueService;
        $this->matchService = $matchService;
    }

    /**
     * Get dashboard statistics
     */
    public function stats(): JsonResponse
    {
        $stats = Cache::remember('api_dashboard_stats', 3600, function () {
            // Use single query with grouping for match counts
            $matchCounts = CricketMatch::selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = "scheduled" THEN 1 ELSE 0 END) as scheduled,
                SUM(CASE WHEN status = "live" THEN 1 ELSE 0 END) as live
            ')->first();

            return [
                'countries' => Country::count(),
                'teams' => Team::count(),
                'players' => Player::count(),
                'venues' => Venue::count(),
                'matches' => $matchCounts->total,
                'completed_matches' => $matchCounts->completed,
                'upcoming_matches' => $matchCounts->scheduled,
                'live_matches' => $matchCounts->live,
            ];
        });

        return $this->successResponse($stats, 'Dashboard statistics retrieved successfully');
    }

    /**
     * Get countries with pagination
     */
    public function countries(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            'search' => 'string|max:255'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $countries = $this->countryService->getAllCountries($request->all());
        return $this->successResponse($countries, 'Countries retrieved successfully');
    }

    /**
     * Get teams with pagination and filtering
     */
    public function teams(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            'search' => 'string|max:255',
            'country_id' => 'integer|exists:countries,country_id',
            'status' => 'string|in:scheduled,live,completed,cancelled'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $teams = $this->teamService->getAllTeams($request->all());
        return $this->successResponse($teams, 'Teams retrieved successfully');
    }

    /**
     * Get players with pagination and filtering
     */
    public function players(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            'search' => 'string|max:255',
            'team_id' => 'integer|exists:teams,team_id',
            'role' => 'string|in:Batsman,Bowler,All-rounder,Wicket-keeper',
            'min_age' => 'integer|min:15',
            'max_age' => 'integer|max:50'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $players = $this->playerService->getAllPlayers($request->all());
        return $this->successResponse($players, 'Players retrieved successfully');
    }

    /**
     * Get matches with pagination and filtering
     */
    public function matches(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            'search' => 'string|max:255',
            'venue_id' => 'integer|exists:venues,venue_id',
            'team_id' => 'integer|exists:teams,team_id',
            'status' => 'string|in:scheduled,live,completed,cancelled',
            'match_type' => 'string|in:T20,ODI,Test',
            'date_from' => 'date',
            'date_to' => 'date|after_or_equal:date_from'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $matches = $this->matchService->getAllMatches($request->all());
        return $this->successResponse($matches, 'Matches retrieved successfully');
    }

    /**
     * Get venues with pagination
     */
    public function venues(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            'search' => 'string|max:255',
            'city' => 'string|max:255'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $venues = $this->venueService->getAllVenues($request->all());
        return $this->successResponse($venues, 'Venues retrieved successfully');
    }

    /**
     * Get match details by ID
     */
    public function matchDetails($id): JsonResponse
    {
        try {
            $match = $this->matchService->getMatchById($id);
            return $this->successResponse($match, 'Match details retrieved successfully');
        } catch (\Exception $e) {
            return $this->notFoundResponse('Match');
        }
    }

    /**
     * Get team details by ID
     */
    public function teamDetails($id): JsonResponse
    {
        try {
            $team = $this->teamService->getTeamById($id);
            return $this->successResponse($team, 'Team details retrieved successfully');
        } catch (\Exception $e) {
            return $this->notFoundResponse('Team');
        }
    }

    /**
     * Get player details by ID
     */
    public function playerDetails($id): JsonResponse
    {
        try {
            $player = $this->playerService->getPlayerById($id);
            return $this->successResponse($player, 'Player details retrieved successfully');
        } catch (\Exception $e) {
            return $this->notFoundResponse('Player');
        }
    }

    /**
     * Create a new country
     */
    public function createCountry(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:countries',
            'short_name' => 'required|string|max:10|unique:countries',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $country = Country::create($request->all());
        return $this->successResponse($country, 'Country created successfully', 201);
    }

    /**
     * Update country
     */
    public function updateCountry(Request $request, $id): JsonResponse
    {
        $country = Country::find($id);
        if (!$country) {
            return $this->notFoundResponse('Country');
        }

        $country->update($request->all());
        return $this->successResponse($country, 'Country updated successfully');
    }

    /**
     * Delete country
     */
    public function deleteCountry($id): JsonResponse
    {
        $country = Country::find($id);
        if (!$country) {
            return $this->notFoundResponse('Country');
        }

        $country->delete();
        return $this->successResponse(null, 'Country deleted successfully');
    }

    /**
     * Create a new team
     */
    public function createTeam(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'team_name' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,country_id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $team = Team::create($request->all());
        return $this->successResponse($team, 'Team created successfully', 201);
    }

    /**
     * Update team
     */
    public function updateTeam(Request $request, $id): JsonResponse
    {
        $team = Team::find($id);
        if (!$team) {
            return $this->notFoundResponse('Team');
        }

        $team->update($request->all());
        return $this->successResponse($team, 'Team updated successfully');
    }

    /**
     * Delete team
     */
    public function deleteTeam($id): JsonResponse
    {
        $team = Team::find($id);
        if (!$team) {
            return $this->notFoundResponse('Team');
        }

        $team->delete();
        return $this->successResponse(null, 'Team deleted successfully');
    }

    /**
     * Create a new player
     */
    public function createPlayer(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'team_id' => 'required|exists:teams,team_id',
            'role' => 'required|in:Batsman,Bowler,All-rounder,Wicket-keeper',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $player = Player::create($request->all());
        return $this->successResponse($player, 'Player created successfully', 201);
    }

    /**
     * Update player
     */
    public function updatePlayer(Request $request, $id): JsonResponse
    {
        $player = Player::find($id);
        if (!$player) {
            return $this->notFoundResponse('Player');
        }

        $player->update($request->all());
        return $this->successResponse($player, 'Player updated successfully');
    }

    /**
     * Delete player
     */
    public function deletePlayer($id): JsonResponse
    {
        $player = Player::find($id);
        if (!$player) {
            return $this->notFoundResponse('Player');
        }

        $player->delete();
        return $this->successResponse(null, 'Player deleted successfully');
    }

    /**
     * Create a new venue
     */
    public function createVenue(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $venue = Venue::create($request->all());
        return $this->successResponse($venue, 'Venue created successfully', 201);
    }

    /**
     * Update venue
     */
    public function updateVenue(Request $request, $id): JsonResponse
    {
        $venue = Venue::find($id);
        if (!$venue) {
            return $this->notFoundResponse('Venue');
        }

        $venue->update($request->all());
        return $this->successResponse($venue, 'Venue updated successfully');
    }

    /**
     * Delete venue
     */
    public function deleteVenue($id): JsonResponse
    {
        $venue = Venue::find($id);
        if (!$venue) {
            return $this->notFoundResponse('Venue');
        }

        $venue->delete();
        return $this->successResponse(null, 'Venue deleted successfully');
    }

    /**
     * Create a new match
     */
    public function createMatch(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'venue_id' => 'required|exists:venues,venue_id',
            'first_team_id' => 'required|exists:teams,team_id',
            'second_team_id' => 'required|exists:teams,team_id|different:first_team_id',
            'match_type' => 'required|in:T20,ODI,Test',
            'overs' => 'required|integer|min:1',
            'match_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $match = $this->matchService->createMatch($request->all());
        return $this->successResponse($match, 'Match created successfully', 201);
    }

    /**
     * Update match
     */
    public function updateMatch(Request $request, $id): JsonResponse
    {
        $match = CricketMatch::find($id);
        if (!$match) {
            return $this->notFoundResponse('Match');
        }

        $match->update($request->all());
        $match->load(['firstTeam', 'secondTeam', 'venue']);
        
        return $this->successResponse($match, 'Match updated successfully');
    }

    /**
     * Delete match
     */
    public function deleteMatch($id): JsonResponse
    {
        $match = CricketMatch::find($id);
        if (!$match) {
            return $this->notFoundResponse('Match');
        }

        $match->delete();
        return $this->successResponse(null, 'Match deleted successfully');
    }

    /**
     * Global search across all entities
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|max:255',
            'type' => 'string|in:all,countries,teams,players,venues,matches',
            'per_page' => 'integer|min:1|max:50'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $query = $request->get('q');
        $type = $request->get('type', 'all');
        $perPage = $request->get('per_page', 20);
        $results = [];

        try {
            if ($type === 'all' || $type === 'countries') {
                $countries = Country::where('name', 'like', "%{$query}%")
                    ->orWhere('short_name', 'like', "%{$query}%")
                    ->limit($type === 'countries' ? $perPage : 10)
                    ->get();
                $results = array_merge($results, $countries->map(function ($country) {
                    return array_merge($country->toArray(), ['type' => 'country']);
                })->toArray());
            }

            if ($type === 'all' || $type === 'teams') {
                $teams = Team::where('team_name', 'like', "%{$query}%")
                    ->orWhere('in_match', 'like', "%{$query}%")
                    ->with('country')
                    ->limit($type === 'teams' ? $perPage : 10)
                    ->get();
                $results = array_merge($results, $teams->map(function ($team) {
                    return array_merge($team->toArray(), ['type' => 'team']);
                })->toArray());
            }

            if ($type === 'all' || $type === 'players') {
                $players = Player::where('name', 'like', "%{$query}%")
                    ->orWhere('role', 'like', "%{$query}%")
                    ->orWhere('batting_style', 'like', "%{$query}%")
                    ->orWhere('bowling_style', 'like', "%{$query}%")
                    ->with(['team'])
                    ->limit($type === 'players' ? $perPage : 10)
                    ->get();
                $results = array_merge($results, $players->map(function ($player) {
                    return array_merge($player->toArray(), ['type' => 'player']);
                })->toArray());
            }

            if ($type === 'all' || $type === 'venues') {
                $venues = Venue::where('name', 'like', "%{$query}%")
                    ->orWhere('city', 'like', "%{$query}%")
                    ->orWhere('address', 'like', "%{$query}%")
                    ->limit($type === 'venues' ? $perPage : 10)
                    ->get();
                $results = array_merge($results, $venues->map(function ($venue) {
                    return array_merge($venue->toArray(), ['type' => 'venue']);
                })->toArray());
            }

            if ($type === 'all' || $type === 'matches') {
                $matches = CricketMatch::where('status', 'like', "%{$query}%")
                    ->orWhere('outcome', 'like', "%{$query}%")
                    ->orWhere('match_type', 'like', "%{$query}%")
                    ->orWhereHas('firstTeam', function ($q) use ($query) {
                        $q->where('team_name', 'like', "%{$query}%");
                    })
                    ->orWhereHas('secondTeam', function ($q) use ($query) {
                        $q->where('team_name', 'like', "%{$query}%");
                    })
                    ->orWhereHas('venue', function ($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%");
                    })
                    ->with(['firstTeam', 'secondTeam', 'venue'])
                    ->limit($type === 'matches' ? $perPage : 10)
                    ->get();
                $results = array_merge($results, $matches->map(function ($match) {
                    return array_merge($match->toArray(), ['type' => 'match']);
                })->toArray());
            }

            return $this->successResponse(array_slice($results, 0, $perPage), 'Search results retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Search failed: ' . $e->getMessage(), 500);
        }
    }
}
