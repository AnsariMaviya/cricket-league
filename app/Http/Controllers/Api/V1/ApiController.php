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
            return [
                'countries' => Country::count(),
                'teams' => Team::count(),
                'players' => Player::count(),
                'venues' => Venue::count(),
                'matches' => CricketMatch::count(),
                'completed_matches' => CricketMatch::where('status', 'completed')->count(),
                'upcoming_matches' => CricketMatch::where('status', 'scheduled')->count(),
                'live_matches' => CricketMatch::where('status', 'live')->count(),
            ];
        });

        return $this->apiResponse($stats, 'Dashboard statistics retrieved successfully');
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
            return $this->apiResponse(null, 'Validation failed', 422);
        }

        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');
        $countryId = $request->get('country_id');

        $query = Team::with(['country', 'players']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('team_name', 'LIKE', "%{$search}%")
                  ->orWhere('in_match', 'LIKE', "%{$search}%");
            });
        }

        if ($countryId) {
            $query->where('country_id', $countryId);
        }

        $teams = $query->paginate($perPage);

        return $this->apiResponse($teams, 'Teams retrieved successfully');
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
            return $this->apiResponse(null, 'Validation failed', 422);
        }

        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');
        $teamId = $request->get('team_id');
        $role = $request->get('role');
        $minAge = $request->get('min_age');
        $maxAge = $request->get('max_age');

        $query = Player::with(['team.country']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('role', 'LIKE', "%{$search}%")
                  ->orWhere('batting_style', 'LIKE', "%{$search}%")
                  ->orWhere('bowling_style', 'LIKE', "%{$search}%");
            });
        }

        if ($teamId) {
            $query->where('team_id', $teamId);
        }

        if ($role) {
            $query->where('role', $role);
        }

        if ($minAge || $maxAge) {
            $query->whereHas('team', function ($q) use ($minAge, $maxAge) {
                if ($minAge) {
                    $q->where('dob', '<=', now()->subYears($minAge));
                }
                if ($maxAge) {
                    $q->where('dob', '>=', now()->subYears($maxAge));
                }
            });
        }

        $players = $query->paginate($perPage);

        return $this->apiResponse($players, 'Players retrieved successfully');
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
            return $this->apiResponse(null, 'Validation failed', 422);
        }

        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');
        $venueId = $request->get('venue_id');
        $teamId = $request->get('team_id');
        $status = $request->get('status');
        $matchType = $request->get('match_type');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = CricketMatch::with(['firstTeam.country', 'secondTeam.country', 'venue']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('match_type', 'LIKE', "%{$search}%")
                  ->orWhere('outcome', 'LIKE', "%{$search}%")
                  ->orWhereHas('firstTeam', function ($subQ) use ($search) {
                      $subQ->where('team_name', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('secondTeam', function ($subQ) use ($search) {
                      $subQ->where('team_name', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('venue', function ($subQ) use ($search) {
                      $subQ->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($venueId) {
            $query->where('venue_id', $venueId);
        }

        if ($teamId) {
            $query->where(function ($q) use ($teamId) {
                $q->where('first_team_id', $teamId)
                  ->orWhere('second_team_id', $teamId);
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($matchType) {
            $query->where('match_type', $matchType);
        }

        if ($dateFrom) {
            $query->whereDate('match_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('match_date', '<=', $dateTo);
        }

        $matches = $query->orderBy('match_date', 'desc')->paginate($perPage);

        return $this->apiResponse($matches, 'Matches retrieved successfully');
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
            return $this->apiResponse(null, 'Validation failed', 422);
        }

        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');
        $city = $request->get('city');

        $query = Venue::withCount('matches');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%");
            });
        }

        if ($city) {
            $query->where('city', 'LIKE', "%{$city}%");
        }

        $venues = $query->paginate($perPage);

        return $this->apiResponse($venues, 'Venues retrieved successfully');
    }

    /**
     * Get match details by ID
     */
    public function matchDetails($id): JsonResponse
    {
        $cacheKey = "api_match_details_{$id}";
        
        $match = Cache::remember($cacheKey, 600, function () use ($id) {
            return CricketMatch::with([
                'firstTeam.country',
                'secondTeam.country',
                'venue',
                'firstTeam.players',
                'secondTeam.players'
            ])->find($id);
        });

        if (!$match) {
            return $this->apiResponse(null, 'Match not found', 404);
        }

        return $this->apiResponse($match, 'Match details retrieved successfully');
    }

    /**
     * Get team details by ID
     */
    public function teamDetails($id): JsonResponse
    {
        $cacheKey = "api_team_details_{$id}";
        
        $team = Cache::remember($cacheKey, 600, function () use ($id) {
            return Team::with([
                'country',
                'players',
                'homeMatches' => function ($q) {
                    $q->with('venue')->orderBy('match_date', 'desc')->limit(10);
                },
                'awayMatches' => function ($q) {
                    $q->with('venue')->orderBy('match_date', 'desc')->limit(10);
                }
            ])->find($id);
        });

        if (!$team) {
            return $this->apiResponse(null, 'Team not found', 404);
        }

        // Combine home and away matches
        $allMatches = $team->homeMatches->concat($team->awayMatches)
            ->sortByDesc('match_date')
            ->values();

        $teamData = $team->toArray();
        $teamData['recent_matches'] = $allMatches->take(10);
        unset($teamData['home_matches'], $teamData['away_matches']);

        return $this->apiResponse($teamData, 'Team details retrieved successfully');
    }

    /**
     * Get player details by ID
     */
    public function playerDetails($id): JsonResponse
    {
        $cacheKey = "api_player_details_{$id}";
        
        $player = Cache::remember($cacheKey, 600, function () use ($id) {
            return Player::with([
                'team.country',
                'team.homeMatches' => function ($q) use ($id) {
                    $q->with('venue')->orderBy('match_date', 'desc')->limit(10);
                },
                'team.awayMatches' => function ($q) use ($id) {
                    $q->with('venue')->orderBy('match_date', 'desc')->limit(10);
                }
            ])->find($id);
        });

        if (!$player) {
            return $this->apiResponse(null, 'Player not found', 404);
        }

        $playerData = $player->toArray();
        $playerData['age'] = $player->dob?->age;

        return $this->apiResponse($playerData, 'Player details retrieved successfully');
    }
}
