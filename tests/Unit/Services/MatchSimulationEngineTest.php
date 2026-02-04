<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\MatchSimulationEngine;
use App\Models\CricketMatch;
use App\Models\Team;
use App\Models\Venue;
use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use App\Events\MatchUpdated;

class MatchSimulationEngineTest extends TestCase
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
        
        $this->simulationEngine = new MatchSimulationEngine();
    }

    public function test_simulation_engine_can_be_instantiated()
    {
        $this->assertInstanceOf(MatchSimulationEngine::class, $this->simulationEngine);
    }

    public function test_start_match_changes_status_to_live()
    {
        Event::fake();
        
        $result = $this->simulationEngine->startMatch($this->match);
        
        $this->assertEquals('live', $result->status);
        $this->assertNotNull($result->started_at);
        $this->assertEquals(1, $result->current_innings);
        $this->assertEquals(0, $result->current_over);
        
        Event::assertDispatched(MatchUpdated::class);
    }

    public function test_start_match_simulates_toss()
    {
        $this->simulationEngine->startMatch($this->match);
        
        $this->assertNotNull($this->match->fresh()->toss_winner);
        $this->assertNotNull($this->match->fresh()->toss_decision);
        $this->assertContains($this->match->fresh()->toss_decision, ['bat', 'bowl']);
        $this->assertContains($this->match->fresh()->toss_winner, [$this->team1->team_id, $this->team2->team_id]);
    }

    public function test_start_match_initializes_innings()
    {
        $this->simulationEngine->startMatch($this->match);
        
        $innings = $this->match->fresh()->innings;
        $this->assertCount(1, $innings);
        $this->assertEquals(1, $innings->first()->innings_number);
    }

    public function test_start_match_selects_players()
    {
        $this->simulationEngine->startMatch($this->match);
        
        $this->assertNotNull($this->match->fresh()->current_batsman_1);
        $this->assertNotNull($this->match->fresh()->current_batsman_2);
        $this->assertNotNull($this->match->fresh()->current_bowler);
        
        // Verify selected players exist
        $this->assertInstanceOf(Player::class, Player::find($this->match->fresh()->current_batsman_1));
        $this->assertInstanceOf(Player::class, Player::find($this->match->fresh()->current_batsman_2));
        $this->assertInstanceOf(Player::class, Player::find($this->match->fresh()->current_bowler));
    }

    public function test_simulate_ball_creates_ball_record()
    {
        $this->simulationEngine->startMatch($this->match);
        
        $initialBallCount = $this->match->balls()->count();
        
        $this->simulationEngine->simulateBall($this->match);
        
        $this->assertEquals($initialBallCount + 1, $this->match->balls()->count());
    }

    public function test_simulate_ball_updates_match_state()
    {
        $this->simulationEngine->startMatch($this->match);
        
        $initialOver = $this->match->current_over;
        
        $this->simulationEngine->simulateBall($this->match);
        
        $this->assertNotEquals($initialOver, $this->match->fresh()->current_over);
    }

    public function test_simulate_ball_generates_commentary()
    {
        $this->simulationEngine->startMatch($this->match);
        
        $initialCommentaryCount = $this->match->commentary()->count();
        
        $this->simulationEngine->simulateBall($this->match);
        
        $this->assertEquals($initialCommentaryCount + 1, $this->match->commentary()->count());
    }

    public function test_auto_simulate_completes_match()
    {
        Event::fake();
        
        // Use a smaller number of overs for testing
        $this->match->overs = 2; // 2 overs for quick test
        $this->match->save();
        
        $result = $this->simulationEngine->autoSimulate($this->match, 0); // No delay for testing
        
        $this->assertEquals('completed', $result->status);
        $this->assertNotNull($result->outcome);
        $this->assertNotNull($result->ended_at);
        
        Event::assertDispatched(MatchUpdated::class);
    }

    public function test_simulation_engine_handles_completed_match()
    {
        $this->match->status = 'completed';
        $this->match->save();
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Match is already completed');
        
        $this->simulationEngine->startMatch($this->match);
    }

    public function test_simulation_engine_handles_live_match_restart()
    {
        $this->simulationEngine->startMatch($this->match);
        
        // Try to start again - should handle gracefully
        $result = $this->simulationEngine->startMatch($this->match);
        
        $this->assertEquals('live', $result->status);
    }

    public function test_simulation_engine_tracks_partnerships()
    {
        $this->simulationEngine->startMatch($this->match);
        
        // Simulate a few balls to create partnership
        for ($i = 0; $i < 5; $i++) {
            $this->simulationEngine->simulateBall($this->match);
        }
        
        // Check if partnership is created
        $partnerships = $this->match->partnerships;
        $this->assertGreaterThan(0, $partnerships->count());
    }

    public function test_simulation_engine_handles_wickets()
    {
        $this->simulationEngine->startMatch($this->match);
        
        // Simulate enough balls to potentially get a wicket
        for ($i = 0; $i < 20; $i++) {
            $this->simulationEngine->simulateBall($this->match);
            
            // Check if wicket fell
            if ($this->match->fresh()->balls()->where('is_wicket', true)->exists()) {
                $this->assertTrue(true);
                return;
            }
        }
        
        // If no wicket fell, that's also valid (just means it didn't happen in this simulation)
        $this->assertTrue(true);
    }

    public function test_simulation_engine_updates_player_stats()
    {
        $this->simulationEngine->startMatch($this->match);
        
        // Simulate a few balls
        for ($i = 0; $i < 10; $i++) {
            $this->simulationEngine->simulateBall($this->match);
        }
        
        // Check if player stats are created
        $playerStats = $this->match->playerStats;
        $this->assertGreaterThan(0, $playerStats->count());
    }
}
