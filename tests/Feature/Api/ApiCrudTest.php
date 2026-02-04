<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Country;
use App\Models\Team;
use App\Models\Player;
use App\Models\Venue;
use App\Models\CricketMatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class ApiCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user, ['*']);
    }

    // Countries API Tests
    public function test_can_get_countries()
    {
        Country::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/countries');

        $response->assertStatus(200);
        $response->assertJsonCount(5);
        $response->assertJsonStructure([
            '*' => [
                'country_id',
                'name',
                'code',
                'flag',
                'created_at',
                'updated_at'
            ]
        ]);
    }

    public function test_can_create_country()
    {
        $countryData = [
            'name' => 'Test Country',
            'code' => 'TC',
            'flag' => '🏳️'
        ];

        $response = $this->postJson('/api/v1/countries', $countryData);

        $response->assertStatus(201);
        $response->assertJson($countryData);
        $this->assertDatabaseHas('countries', $countryData);
    }

    public function test_can_update_country()
    {
        $country = Country::factory()->create();
        $updateData = ['name' => 'Updated Country'];

        $response = $this->putJson("/api/v1/countries/{$country->country_id}", $updateData);

        $response->assertStatus(200);
        $response->assertJson($updateData);
        $this->assertDatabaseHas('countries', $updateData);
    }

    public function test_can_delete_country()
    {
        $country = Country::factory()->create();

        $response = $this->deleteJson("/api/v1/countries/{$country->country_id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('countries', ['country_id' => $country->country_id]);
    }

    // Teams API Tests
    public function test_can_get_teams()
    {
        Team::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/teams');

        $response->assertStatus(200);
        $response->assertJsonCount(5);
        $response->assertJsonStructure([
            '*' => [
                'team_id',
                'team_name',
                'country_id',
                'logo',
                'created_at',
                'updated_at'
            ]
        ]);
    }

    public function test_can_create_team()
    {
        $country = Country::factory()->create();
        $teamData = [
            'team_name' => 'Test Team',
            'country_id' => $country->country_id,
            'logo' => 'test-logo.png'
        ];

        $response = $this->postJson('/api/v1/teams', $teamData);

        $response->assertStatus(201);
        $response->assertJson($teamData);
        $this->assertDatabaseHas('teams', $teamData);
    }

    public function test_can_update_team()
    {
        $team = Team::factory()->create();
        $updateData = ['team_name' => 'Updated Team'];

        $response = $this->putJson("/api/v1/teams/{$team->team_id}", $updateData);

        $response->assertStatus(200);
        $response->assertJson($updateData);
        $this->assertDatabaseHas('teams', $updateData);
    }

    public function test_can_delete_team()
    {
        $team = Team::factory()->create();

        $response = $this->deleteJson("/api/v1/teams/{$team->team_id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('teams', ['team_id' => $team->team_id]);
    }

    // Players API Tests
    public function test_can_get_players()
    {
        Player::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/players');

        $response->assertStatus(200);
        $response->assertJsonCount(5);
        $response->assertJsonStructure([
            '*' => [
                'player_id',
                'team_id',
                'player_name',
                'role',
                'batting_style',
                'bowling_style',
                'jersey_number',
                'is_active',
                'created_at',
                'updated_at'
            ]
        ]);
    }

    public function test_can_create_player()
    {
        $team = Team::factory()->create();
        $playerData = [
            'team_id' => $team->team_id,
            'player_name' => 'Test Player',
            'role' => 'Batsman',
            'batting_style' => 'Right-handed',
            'bowling_style' => 'Right-arm fast',
            'jersey_number' => 10,
            'is_active' => true
        ];

        $response = $this->postJson('/api/v1/players', $playerData);

        $response->assertStatus(201);
        $response->assertJson($playerData);
        $this->assertDatabaseHas('players', $playerData);
    }

    public function test_can_update_player()
    {
        $player = Player::factory()->create();
        $updateData = ['player_name' => 'Updated Player'];

        $response = $this->putJson("/api/v1/players/{$player->player_id}", $updateData);

        $response->assertStatus(200);
        $response->assertJson($updateData);
        $this->assertDatabaseHas('players', $updateData);
    }

    public function test_can_delete_player()
    {
        $player = Player::factory()->create();

        $response = $this->deleteJson("/api/v1/players/{$player->player_id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('players', ['player_id' => $player->player_id]);
    }

    // Venues API Tests
    public function test_can_get_venues()
    {
        Venue::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/venues');

        $response->assertStatus(200);
        $response->assertJsonCount(5);
        $response->assertJsonStructure([
            '*' => [
                'venue_id',
                'name',
                'city',
                'country',
                'capacity',
                'created_at',
                'updated_at'
            ]
        ]);
    }

    public function test_can_create_venue()
    {
        $venueData = [
            'name' => 'Test Stadium',
            'city' => 'Test City',
            'country' => 'Test Country',
            'capacity' => 50000
        ];

        $response = $this->postJson('/api/v1/venues', $venueData);

        $response->assertStatus(201);
        $response->assertJson($venueData);
        $this->assertDatabaseHas('venues', $venueData);
    }

    public function test_can_update_venue()
    {
        $venue = Venue::factory()->create();
        $updateData = ['name' => 'Updated Stadium'];

        $response = $this->putJson("/api/v1/venues/{$venue->venue_id}", $updateData);

        $response->assertStatus(200);
        $response->assertJson($updateData);
        $this->assertDatabaseHas('venues', $updateData);
    }

    public function test_can_delete_venue()
    {
        $venue = Venue::factory()->create();

        $response = $this->deleteJson("/api/v1/venues/{$venue->venue_id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('venues', ['venue_id' => $venue->venue_id]);
    }

    // Matches API Tests
    public function test_can_get_matches()
    {
        CricketMatch::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/matches');

        $response->assertStatus(200);
        $response->assertJsonCount(5);
        $response->assertJsonStructure([
            '*' => [
                'match_id',
                'venue_id',
                'first_team_id',
                'second_team_id',
                'match_date',
                'status',
                'created_at',
                'updated_at'
            ]
        ]);
    }

    public function test_can_create_match()
    {
        $venue = Venue::factory()->create();
        $team1 = Team::factory()->create();
        $team2 = Team::factory()->create();
        
        $matchData = [
            'venue_id' => $venue->venue_id,
            'first_team_id' => $team1->team_id,
            'second_team_id' => $team2->team_id,
            'match_date' => now()->addDays(7)->toDateString(),
            'overs' => 20,
            'match_type' => 'T20'
        ];

        $response = $this->postJson('/api/v1/matches', $matchData);

        $response->assertStatus(201);
        $response->assertJson($matchData);
        $this->assertDatabaseHas('matches', $matchData);
    }

    public function test_can_update_match()
    {
        $match = CricketMatch::factory()->create();
        $updateData = ['status' => 'live'];

        $response = $this->putJson("/api/v1/matches/{$match->match_id}", $updateData);

        $response->assertStatus(200);
        $response->assertJson($updateData);
        $this->assertDatabaseHas('matches', $updateData);
    }

    public function test_can_delete_match()
    {
        $match = CricketMatch::factory()->create();

        $response = $this->deleteJson("/api/v1/matches/{$match->match_id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('matches', ['match_id' => $match->match_id]);
    }

    // Validation Tests
    public function test_create_country_validation()
    {
        $response = $this->postJson('/api/v1/countries', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'code']);
    }

    public function test_create_team_validation()
    {
        $response = $this->postJson('/api/v1/teams', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['team_name', 'country_id']);
    }

    public function test_create_player_validation()
    {
        $response = $this->postJson('/api/v1/players', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['team_id', 'player_name', 'role']);
    }

    public function test_create_venue_validation()
    {
        $response = $this->postJson('/api/v1/venues', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'city', 'country']);
    }

    public function test_create_match_validation()
    {
        $response = $this->postJson('/api/v1/matches', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['venue_id', 'first_team_id', 'second_team_id', 'match_date']);
    }

    // Not Found Tests
    public function test_get_nonexistent_country_returns_404()
    {
        $response = $this->getJson('/api/v1/countries/999');

        $response->assertStatus(404);
    }

    public function test_get_nonexistent_team_returns_404()
    {
        $response = $this->getJson('/api/v1/teams/999');

        $response->assertStatus(404);
    }

    public function test_get_nonexistent_player_returns_404()
    {
        $response = $this->getJson('/api/v1/players/999');

        $response->assertStatus(404);
    }

    public function test_get_nonexistent_venue_returns_404()
    {
        $response = $this->getJson('/api/v1/venues/999');

        $response->assertStatus(404);
    }

    public function test_get_nonexistent_match_returns_404()
    {
        $response = $this->getJson('/api/v1/matches/999');

        $response->assertStatus(404);
    }

    // Pagination Tests
    public function test_countries_pagination()
    {
        Country::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/countries?per_page=10');

        $response->assertStatus(200);
        $response->assertJsonCount(10);
        $response->assertJsonStructure([
            'data',
            'links',
            'meta'
        ]);
    }

    public function test_teams_pagination()
    {
        Team::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/teams?per_page=10');

        $response->assertStatus(200);
        $response->assertJsonCount(10);
        $response->assertJsonStructure([
            'data',
            'links',
            'meta'
        ]);
    }

    public function test_players_pagination()
    {
        Player::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/players?per_page=10');

        $response->assertStatus(200);
        $response->assertJsonCount(10);
        $response->assertJsonStructure([
            'data',
            'links',
            'meta'
        ]);
    }

    // Search Tests
    public function test_can_search_countries()
    {
        Country::factory()->create(['name' => 'Test Country']);
        Country::factory()->create(['name' => 'Another Country']);

        $response = $this->getJson('/api/v1/countries?search=Test');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['name' => 'Test Country']);
    }

    public function test_can_search_teams()
    {
        Team::factory()->create(['team_name' => 'Test Team']);
        Team::factory()->create(['team_name' => 'Another Team']);

        $response = $this->getJson('/api/v1/teams?search=Test');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['team_name' => 'Test Team']);
    }

    public function test_can_search_players()
    {
        Player::factory()->create(['player_name' => 'Test Player']);
        Player::factory()->create(['player_name' => 'Another Player']);

        $response = $this->getJson('/api/v1/players?search=Test');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['player_name' => 'Test Player']);
    }
}
