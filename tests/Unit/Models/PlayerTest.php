<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Player;
use App\Models\Team;
use App\Models\PlayerCareerStats;
use App\Models\PlayerMatchStats;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PlayerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->team = Team::factory()->create();
    }

    public function test_player_can_be_created()
    {
        $player = Player::factory()->create([
            'team_id' => $this->team->team_id,
        ]);

        $this->assertInstanceOf(Player::class, $player);
        $this->assertEquals($this->team->team_id, $player->team_id);
        $this->assertNotNull($player->player_name);
        $this->assertNotNull($player->role);
    }

    public function test_player_has_team_relationship()
    {
        $player = Player::factory()->create([
            'team_id' => $this->team->team_id,
        ]);

        $this->assertInstanceOf(Team::class, $player->team);
        $this->assertEquals($this->team->team_id, $player->team->team_id);
    }

    public function test_player_has_career_stats()
    {
        $player = Player::factory()->create([
            'team_id' => $this->team->team_id,
        ]);

        $careerStats = PlayerCareerStats::factory()->create([
            'player_id' => $player->player_id,
        ]);

        $this->assertInstanceOf(PlayerCareerStats::class, $player->careerStats);
        $this->assertEquals($player->player_id, $player->careerStats->player_id);
    }

    public function test_player_has_match_stats()
    {
        $player = Player::factory()->create([
            'team_id' => $this->team->team_id,
        ]);

        $matchStats = PlayerMatchStats::factory()->count(3)->create([
            'player_id' => $player->player_id,
        ]);

        $this->assertCount(3, $player->matchStats);
        $this->assertEquals($player->player_id, $player->matchStats->first()->player_id);
    }

    public function test_player_fillable_attributes()
    {
        $playerData = [
            'team_id' => $this->team->team_id,
            'player_name' => 'John Doe',
            'role' => 'Batsman',
            'batting_style' => 'Right-handed',
            'bowling_style' => 'Right-arm fast',
            'jersey_number' => 10,
            'is_active' => true,
        ];

        $player = Player::create($playerData);

        foreach ($playerData as $key => $value) {
            $this->assertEquals($value, $player->$key);
        }
    }

    public function test_player_casts_boolean_attributes()
    {
        $player = Player::factory()->create([
            'team_id' => $this->team->team_id,
            'is_active' => true,
            'is_captain' => false,
        ]);

        $this->assertTrue($player->is_active);
        $this->assertFalse($player->is_captain);
    }

    public function test_player_scope_by_role()
    {
        Player::factory()->create(['role' => 'Batsman']);
        Player::factory()->create(['role' => 'Bowler']);
        Player::factory()->create(['role' => 'Batsman']);
        Player::factory()->create(['role' => 'All-rounder']);

        $batsmen = Player::where('role', 'Batsman')->get();
        $bowlers = Player::where('role', 'Bowler')->get();
        $allRounders = Player::where('role', 'All-rounder')->get();

        $this->assertCount(2, $batsmen);
        $this->assertCount(1, $bowlers);
        $this->assertCount(1, $allRounders);
    }

    public function test_player_scope_active()
    {
        Player::factory()->create(['is_active' => true]);
        Player::factory()->create(['is_active' => false]);
        Player::factory()->create(['is_active' => true]);

        $activePlayers = Player::where('is_active', true)->get();
        $inactivePlayers = Player::where('is_active', false)->get();

        $this->assertCount(2, $activePlayers);
        $this->assertCount(1, $inactivePlayers);
    }

    public function test_player_can_be_made_captain()
    {
        $player = Player::factory()->create([
            'team_id' => $this->team->team_id,
            'is_captain' => false,
        ]);

        $player->is_captain = true;
        $player->save();

        $this->assertTrue($player->is_captain);
    }

    public function test_player_can_be_deactivated()
    {
        $player = Player::factory()->create([
            'team_id' => $this->team->team_id,
            'is_active' => true,
        ]);

        $player->is_active = false;
        $player->save();

        $this->assertFalse($player->is_active);
    }
}
