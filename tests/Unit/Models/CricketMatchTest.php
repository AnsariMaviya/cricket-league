<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\CricketMatch;
use App\Models\Team;
use App\Models\Venue;
use App\Models\Tournament;
use App\Models\TournamentStage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CricketMatchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->venue = Venue::factory()->create();
        $this->team1 = Team::factory()->create();
        $this->team2 = Team::factory()->create();
        $this->tournament = Tournament::factory()->create();
        $this->stage = TournamentStage::factory()->create();
    }

    public function test_cricket_match_can_be_created()
    {
        $match = CricketMatch::factory()->create([
            'venue_id' => $this->venue->venue_id,
            'first_team_id' => $this->team1->team_id,
            'second_team_id' => $this->team2->team_id,
            'tournament_id' => $this->tournament->tournament_id,
            'stage_id' => $this->stage->stage_id,
        ]);

        $this->assertInstanceOf(CricketMatch::class, $match);
        $this->assertEquals($this->venue->venue_id, $match->venue_id);
        $this->assertEquals($this->team1->team_id, $match->first_team_id);
        $this->assertEquals($this->team2->team_id, $match->second_team_id);
        $this->assertEquals('scheduled', $match->status);
    }

    public function test_cricket_match_has_relationships()
    {
        $match = CricketMatch::factory()->create([
            'venue_id' => $this->venue->venue_id,
            'first_team_id' => $this->team1->team_id,
            'second_team_id' => $this->team2->team_id,
            'tournament_id' => $this->tournament->tournament_id,
            'stage_id' => $this->stage->stage_id,
        ]);

        // Test venue relationship
        $this->assertInstanceOf(Venue::class, $match->venue);
        $this->assertEquals($this->venue->venue_id, $match->venue->venue_id);

        // Test team relationships
        $this->assertInstanceOf(Team::class, $match->firstTeam);
        $this->assertInstanceOf(Team::class, $match->secondTeam);
        $this->assertEquals($this->team1->team_id, $match->firstTeam->team_id);
        $this->assertEquals($this->team2->team_id, $match->secondTeam->team_id);

        // Test tournament relationship
        $this->assertInstanceOf(Tournament::class, $match->tournament);
        $this->assertEquals($this->tournament->tournament_id, $match->tournament->tournament_id);

        // Test stage relationship
        $this->assertInstanceOf(TournamentStage::class, $match->stage);
        $this->assertEquals($this->stage->stage_id, $match->stage->stage_id);
    }

    public function test_cricket_match_casts_attributes_correctly()
    {
        $match = CricketMatch::factory()->create([
            'venue_id' => $this->venue->venue_id,
            'first_team_id' => $this->team1->team_id,
            'second_team_id' => $this->team2->team_id,
            'match_date' => '2024-01-15',
            'current_over' => 15.3,
            'is_knockout' => true,
            'started_at' => '2024-01-15 14:00:00',
            'ended_at' => '2024-01-15 18:00:00',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $match->match_date);
        $this->assertEquals('15.3', $match->current_over);
        $this->assertTrue($match->is_knockout);
        $this->assertInstanceOf(\Carbon\Carbon::class, $match->started_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $match->ended_at);
    }

    public function test_cricket_match_fillable_attributes()
    {
        $matchData = [
            'venue_id' => $this->venue->venue_id,
            'first_team_id' => $this->team1->team_id,
            'second_team_id' => $this->team2->team_id,
            'tournament_id' => $this->tournament->tournament_id,
            'stage_id' => $this->stage->stage_id,
            'match_number' => 1,
            'is_knockout' => false,
            'match_type' => 'T20',
            'overs' => 20,
            'status' => 'scheduled',
            'current_innings' => 0,
            'current_over' => 0,
        ];

        $match = CricketMatch::create($matchData);

        foreach ($matchData as $key => $value) {
            $this->assertEquals($value, $match->$key);
        }
    }

    public function test_cricket_match_can_be_started()
    {
        $match = CricketMatch::factory()->create([
            'venue_id' => $this->venue->venue_id,
            'first_team_id' => $this->team1->team_id,
            'second_team_id' => $this->team2->team_id,
            'status' => 'scheduled',
        ]);

        $match->status = 'live';
        $match->started_at = now();
        $match->current_innings = 1;
        $match->current_over = 0;
        $match->save();

        $this->assertEquals('live', $match->status);
        $this->assertNotNull($match->started_at);
        $this->assertEquals(1, $match->current_innings);
        $this->assertEquals(0, $match->current_over);
    }

    public function test_cricket_match_can_be_completed()
    {
        $match = CricketMatch::factory()->create([
            'venue_id' => $this->venue->venue_id,
            'first_team_id' => $this->team1->team_id,
            'second_team_id' => $this->team2->team_id,
            'status' => 'live',
        ]);

        $match->status = 'completed';
        $match->outcome = 'Team A won by 5 wickets';
        $match->ended_at = now();
        $match->save();

        $this->assertEquals('completed', $match->status);
        $this->assertEquals('Team A won by 5 wickets', $match->outcome);
        $this->assertNotNull($match->ended_at);
    }

    public function test_cricket_match_scope_by_status()
    {
        // Create matches with different statuses
        CricketMatch::factory()->create(['status' => 'scheduled']);
        CricketMatch::factory()->create(['status' => 'live']);
        CricketMatch::factory()->create(['status' => 'completed']);
        CricketMatch::factory()->create(['status' => 'scheduled']);

        $scheduledMatches = CricketMatch::where('status', 'scheduled')->get();
        $liveMatches = CricketMatch::where('status', 'live')->get();
        $completedMatches = CricketMatch::where('status', 'completed')->get();

        $this->assertCount(2, $scheduledMatches);
        $this->assertCount(1, $liveMatches);
        $this->assertCount(1, $completedMatches);
    }

    public function test_cricket_match_toss_simulation()
    {
        $match = CricketMatch::factory()->create([
            'venue_id' => $this->venue->venue_id,
            'first_team_id' => $this->team1->team_id,
            'second_team_id' => $this->team2->team_id,
        ]);

        // Simulate toss
        $tossWinner = rand(1, 2) === 1 ? $this->team1->team_id : $this->team2->team_id;
        $tossDecision = rand(1, 2) === 1 ? 'bat' : 'bowl';

        $match->toss_winner = $tossWinner;
        $match->toss_decision = $tossDecision;
        $match->save();

        $this->assertNotNull($match->toss_winner);
        $this->assertNotNull($match->toss_decision);
        $this->assertContains($match->toss_decision, ['bat', 'bowl']);
        $this->assertContains($match->toss_winner, [$this->team1->team_id, $this->team2->team_id]);
    }
}
