<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Team;
use App\Models\Player;
use App\Models\Venue;
use App\Models\CricketMatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CricketLeagueTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test dashboard page loads
     */
    public function test_dashboard_page_loads(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Cricket League');
    }

    /**
     * Test countries index page
     */
    public function test_countries_index(): void
    {
        Country::factory()->create(['name' => 'Test Country']);

        $response = $this->get('/countries');

        $response->assertStatus(200);
        $response->assertSee('Test Country');
    }

    /**
     * Test teams index page
     */
    public function test_teams_index(): void
    {
        $country = Country::factory()->create();
        Team::factory()->create([
            'team_name' => 'Test Team',
            'country_id' => $country->country_id
        ]);

        $response = $this->get('/teams');

        $response->assertStatus(200);
        $response->assertSee('Test Team');
    }

    /**
     * Test players index page
     */
    public function test_players_index(): void
    {
        $country = Country::factory()->create();
        $team = Team::factory()->create(['country_id' => $country->country_id]);
        Player::factory()->create([
            'name' => 'Test Player',
            'team_id' => $team->team_id
        ]);

        $response = $this->get('/players');

        $response->assertStatus(200);
        $response->assertSee('Test Player');
    }

    /**
     * Test venues index page
     */
    public function test_venues_index(): void
    {
        Venue::factory()->create(['name' => 'Test Venue']);

        $response = $this->get('/venues');

        $response->assertStatus(200);
        $response->assertSee('Test Venue');
    }

    /**
     * Test matches index page
     */
    public function test_matches_index(): void
    {
        $country = Country::factory()->create();
        $team1 = Team::factory()->create(['country_id' => $country->country_id]);
        $team2 = Team::factory()->create(['country_id' => $country->country_id]);
        $venue = Venue::factory()->create();
        
        CricketMatch::factory()->create([
            'first_team_id' => $team1->team_id,
            'second_team_id' => $team2->team_id,
            'venue_id' => $venue->venue_id
        ]);

        $response = $this->get('/matches');

        $response->assertStatus(200);
        $response->assertSee($team1->team_name);
        $response->assertSee($team2->team_name);
    }

    /**
     * Test API endpoints
     */
    public function test_api_stats_endpoint(): void
    {
        Country::factory()->count(5)->create();
        Team::factory()->count(10)->create();
        Player::factory()->count(50)->create();
        Venue::factory()->count(8)->create();
        CricketMatch::factory()->count(20)->create();

        $response = $this->get('/api/v1/stats');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'countries',
                'teams',
                'players',
                'venues',
                'matches',
                'completed_matches',
                'upcoming_matches',
                'live_matches'
            ],
            'timestamp'
        ]);
    }

    /**
     * Test API countries endpoint
     */
    public function test_api_countries_endpoint(): void
    {
        Country::factory()->count(3)->create();

        $response = $this->get('/api/v1/countries');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'data' => [
                    '*' => [
                        'country_id',
                        'name',
                        'short_name',
                        'teams_count',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'current_page',
                'data',
                'first_page_url',
                'from',
                'last_page',
                'last_page_url',
                'links',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total'
            ],
            'timestamp'
        ]);
    }

    /**
     * Test search functionality
     */
    public function test_search_functionality(): void
    {
        $country = Country::factory()->create(['name' => 'India']);
        $team = Team::factory()->create([
            'team_name' => 'Mumbai Indians',
            'country_id' => $country->country_id
        ]);
        $player = Player::factory()->create([
            'name' => 'Rohit Sharma',
            'team_id' => $team->team_id
        ]);

        $response = $this->get('/search?q=Rohit&type=players');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'query',
            'type',
            'results' => [
                'players' => [
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'role',
                            'team',
                            'country',
                            'age',
                            'profile_url'
                        ]
                    ]
                ]
            ],
            'total_results'
        ]);
    }

    /**
     * Test analytics dashboard
     */
    public function test_analytics_dashboard(): void
    {
        Country::factory()->count(5)->create();
        Team::factory()->count(10)->create();
        Player::factory()->count(50)->create();
        Venue::factory()->count(8)->create();
        CricketMatch::factory()->count(20)->create();

        $response = $this->get('/analytics');

        $response->assertStatus(200);
        $response->assertSee('Analytics Dashboard');
    }

    /**
     * Test model relationships
     */
    public function test_country_team_relationship(): void
    {
        $country = Country::factory()->create();
        $team = Team::factory()->create(['country_id' => $country->country_id]);

        $this->assertEquals($country->country_id, $team->country->country_id);
        $this->assertEquals($country->country_id, $team->country_id);
    }

    /**
     * Test team player relationship
     */
    public function test_team_player_relationship(): void
    {
        $country = Country::factory()->create();
        $team = Team::factory()->create(['country_id' => $country->country_id]);
        $player = Player::factory()->create(['team_id' => $team->team_id]);

        $this->assertEquals($team->team_id, $player->team->team_id);
        $this->assertEquals($team->team_id, $player->team_id);
    }

    /**
     * Test match relationships
     */
    public function test_match_relationships(): void
    {
        $country = Country::factory()->create();
        $team1 = Team::factory()->create(['country_id' => $country->country_id]);
        $team2 = Team::factory()->create(['country_id' => $country->country_id]);
        $venue = Venue::factory()->create();
        
        $match = CricketMatch::factory()->create([
            'first_team_id' => $team1->team_id,
            'second_team_id' => $team2->team_id,
            'venue_id' => $venue->venue_id
        ]);

        $this->assertEquals($team1->team_id, $match->firstTeam->team_id);
        $this->assertEquals($team2->team_id, $match->secondTeam->team_id);
        $this->assertEquals($venue->venue_id, $match->venue->venue_id);
    }

    /**
     * Test caching functionality
     */
    public function test_caching_functionality(): void
    {
        Country::factory()->count(5)->create();

        // First call should cache the data
        $response1 = $this->get('/api/v1/stats');
        $response1->assertStatus(200);

        // Second call should use cached data
        $response2 = $this->get('/api/v1/stats');
        $response2->assertStatus(200);

        // Both responses should have the same data
        $this->assertEquals(
            $response1->json('data'),
            $response2->json('data')
        );
    }
}
