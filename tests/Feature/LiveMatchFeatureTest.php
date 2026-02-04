<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\CricketMatch;
use App\Models\Team;
use App\Models\Venue;
use App\Models\Player;
use App\Services\MatchSimulationEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use App\Events\MatchUpdated;
use App\Events\BallSimulated;
use App\Events\ScoreboardUpdated;

class LiveMatchFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->venue = Venue::factory()->create();
        $this->team1 = Team::factory()->create();
        $this->team2 = Team::factory()->create();
        
        // Create players for both teams
        Player::factory()->count(11)->create(['team_id' => $this->team1->team_id]);
        Player::factory()->count(11)->create(['team_id' => $this->team2->team_id]);
        
        $this->match = CricketMatch::factory()->create([
            'venue_id' => $this->venue->venue_id,
            'first_team_id' => $this->team1->team_id,
            'second_team_id' => $this->team2->team_id,
            'status' => 'scheduled',
        ]);
    }

    public function test_user_can_view_live_matches_page()
    {
        $response = $this->get('/live-matches');

        $response->assertStatus(200);
        $response->assertViewIs('app');
    }

    public function test_user_can_view_live_match_detail_page()
    {
        $response = $this->get("/live-matches/{$this->match->match_id}");

        $response->assertStatus(200);
        $response->assertViewIs('app');
    }

    public function test_api_returns_live_matches()
    {
        // Create matches with different statuses
        $liveMatch = CricketMatch::factory()->create(['status' => 'live']);
        $scheduledMatch = CricketMatch::factory()->create(['status' => 'scheduled']);
        $completedMatch = CricketMatch::factory()->create(['status' => 'completed']);

        $response = $this->getJson('/api/v1/live-matches');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'match_id',
                'first_team',
                'second_team',
                'venue',
                'status',
                'match_date',
            ]
        ]);
    }

    public function test_api_returns_upcoming_matches()
    {
        // Create scheduled matches
        CricketMatch::factory()->count(3)->create(['status' => 'scheduled']);
        CricketMatch::factory()->create(['status' => 'live']);
        CricketMatch::factory()->create(['status' => 'completed']);

        $response = $this->getJson('/api/v1/live-matches/upcoming');

        $response->assertStatus(200);
        $responseData = $response->json();
        $this->assertCount(3, $responseData);
        
        // All returned matches should be scheduled
        foreach ($responseData as $match) {
            $this->assertEquals('scheduled', $match['status']);
        }
    }

    public function test_api_returns_match_scoreboard()
    {
        $response = $this->getJson("/api/v1/live-matches/{$this->match->match_id}/scoreboard");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'match' => [
                'match_id',
                'first_team',
                'second_team',
                'venue',
                'status',
                'current_over',
                'current_innings',
            ],
            'innings',
            'current_batsmen',
            'current_bowler',
            'partnership',
        ]);
    }

    public function test_api_returns_mini_scoreboard()
    {
        $response = $this->getJson("/api/v1/live-matches/{$this->match->match_id}/mini-scoreboard");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'match_id',
            'first_team',
            'second_team',
            'first_team_score',
            'second_team_score',
            'status',
            'current_over',
        ]);
    }

    public function test_api_returns_match_summary()
    {
        $response = $this->getJson("/api/v1/live-matches/{$this->match->match_id}/summary");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'match',
            'summary',
            'key_stats',
        ]);
    }

    public function test_api_returns_over_summary()
    {
        $response = $this->getJson("/api/v1/live-matches/{$this->match->match_id}/over/1");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'over_number',
            'balls',
            'runs',
            'wickets',
            'extras',
        ]);
    }

    public function test_api_can_start_match()
    {
        Event::fake();

        $response = $this->postJson("/api/v1/live-matches/{$this->match->match_id}/start");

        $response->assertStatus(200);
        $this->assertEquals('live', $this->match->fresh()->status);
        
        Event::assertDispatched(MatchUpdated::class);
    }

    public function test_api_can_simulate_ball()
    {
        // Start the match first
        $simulationEngine = new MatchSimulationEngine();
        $simulationEngine->startMatch($this->match);

        Event::fake();

        $response = $this->postJson("/api/v1/live-matches/{$this->match->match_id}/simulate-ball");

        $response->assertStatus(200);
        
        // Check that ball was simulated
        $this->assertGreaterThan(0, $this->match->balls()->count());
        
        Event::assertDispatched(BallSimulated::class);
        Event::assertDispatched(ScoreboardUpdated::class);
    }

    public function test_api_can_start_auto_simulation()
    {
        Event::fake();

        $response = $this->postJson("/api/v1/live-matches/{$this->match->match_id}/start-auto-simulation");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Auto-simulation started']);
        
        Event::assertDispatched(MatchUpdated::class);
    }

    public function test_api_can_stop_auto_simulation()
    {
        // Start auto-simulation first
        $this->postJson("/api/v1/live-matches/{$this->match->match_id}/start-auto-simulation");

        $response = $this->postJson("/api/v1/live-matches/{$this->match->match_id}/stop-auto-simulation");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Auto-simulation stopped']);
    }

    public function test_api_can_stop_match()
    {
        // Start the match first
        $simulationEngine = new MatchSimulationEngine();
        $simulationEngine->startMatch($this->match);

        $response = $this->postJson("/api/v1/live-matches/{$this->match->match_id}/stop");

        $response->assertStatus(200);
        $this->assertEquals('stopped', $this->match->fresh()->status);
    }

    public function test_start_match_requires_valid_match_id()
    {
        $response = $this->postJson("/api/v1/live-matches/999/start");

        $response->assertStatus(404);
    }

    public function test_simulate_ball_requires_live_match()
    {
        $response = $this->postJson("/api/v1/live-matches/{$this->match->match_id}/simulate-ball");

        $response->assertStatus(400);
        $response->assertJson(['message' => 'Match is not live']);
    }

    public function test_scoreboard_includes_team_details()
    {
        $response = $this->getJson("/api/v1/live-matches/{$this->match->match_id}/scoreboard");

        $response->assertStatus(200);
        $data = $response->json();
        
        $this->assertArrayHasKey('first_team', $data['match']);
        $this->assertArrayHasKey('second_team', $data['match']);
        $this->assertEquals($this->team1->team_name, $data['match']['first_team']['team_name']);
        $this->assertEquals($this->team2->team_name, $data['match']['second_team']['team_name']);
    }

    public function test_scoreboard_includes_venue_details()
    {
        $response = $this->getJson("/api/v1/live-matches/{$this->match->match_id}/scoreboard");

        $response->assertStatus(200);
        $data = $response->json();
        
        $this->assertArrayHasKey('venue', $data['match']);
        $this->assertEquals($this->venue->name, $data['match']['venue']['name']);
    }

    public function test_live_match_broadcasts_events()
    {
        Event::fake();

        $simulationEngine = new MatchSimulationEngine();
        $simulationEngine->startMatch($this->match);

        // Simulate a ball
        $this->postJson("/api/v1/live-matches/{$this->match->match_id}/simulate-ball");

        Event::assertDispatched(BallSimulated::class);
        Event::assertDispatched(ScoreboardUpdated::class);
    }

    public function test_completed_match_returns_final_scoreboard()
    {
        // Complete the match
        $this->match->status = 'completed';
        $this->match->first_team_score = '180/5';
        $this->match->second_team_score = '175/8';
        $this->match->outcome = 'Team A won by 5 runs';
        $this->match->save();

        $response = $this->getJson("/api/v1/live-matches/{$this->match->match_id}/scoreboard");

        $response->assertStatus(200);
        $data = $response->json();
        
        $this->assertEquals('completed', $data['match']['status']);
        $this->assertEquals('180/5', $data['match']['first_team_score']);
        $this->assertEquals('175/8', $data['match']['second_team_score']);
        $this->assertEquals('Team A won by 5 runs', $data['match']['outcome']);
    }

    public function test_api_handles_concurrent_requests()
    {
        // Start the match
        $simulationEngine = new MatchSimulationEngine();
        $simulationEngine->startMatch($this->match);

        // Simulate multiple concurrent requests
        $responses = collect(range(1, 5))->map(function () {
            return $this->postJson("/api/v1/live-matches/{$this->match->match_id}/simulate-ball");
        });

        // All requests should succeed
        $responses->each(function ($response) {
            $response->assertStatus(200);
        });
    }
}
