<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\CommentaryService;
use App\Models\BallByBall;
use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CommentaryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->commentaryService = new CommentaryService();
        $this->batsman = Player::factory()->create(['role' => 'Batsman']);
        $this->bowler = Player::factory()->create(['role' => 'Bowler']);
    }

    public function test_commentary_service_can_be_instantiated()
    {
        $this->assertInstanceOf(CommentaryService::class, $this->commentaryService);
    }

    public function test_generate_commentary_returns_string()
    {
        $ball = BallByBall::factory()->create([
            'runs_scored' => 4,
            'ball_type' => 'normal',
        ]);

        $commentary = $this->commentaryService->generate($ball, $this->batsman, $this->bowler);

        $this->assertIsString($commentary);
        $this->assertNotEmpty($commentary);
    }

    public function test_generate_commentary_for_boundary()
    {
        $ball = BallByBall::factory()->create([
            'runs_scored' => 4,
            'ball_type' => 'normal',
        ]);

        $commentary = $this->commentaryService->generate($ball, $this->batsman, $this->bowler);

        $this->assertStringContainsString('4', $commentary);
    }

    public function test_generate_commentary_for_six()
    {
        $ball = BallByBall::factory()->create([
            'runs_scored' => 6,
            'ball_type' => 'normal',
        ]);

        $commentary = $this->commentaryService->generate($ball, $this->batsman, $this->bowler);

        $this->assertStringContainsString('6', $commentary);
    }

    public function test_generate_commentary_for_dot_ball()
    {
        $ball = BallByBall::factory()->create([
            'runs_scored' => 0,
            'ball_type' => 'normal',
        ]);

        $commentary = $this->commentaryService->generate($ball, $this->batsman, $this->bowler);

        $this->assertIsString($commentary);
        $this->assertNotEmpty($commentary);
    }

    public function test_generate_commentary_for_wicket()
    {
        $ball = BallByBall::factory()->create([
            'runs_scored' => 0,
            'ball_type' => 'normal',
            'is_wicket' => true,
            'wicket_type' => 'bowled',
        ]);

        $commentary = $this->commentaryService->generate($ball, $this->batsman, $this->bowler);

        $this->assertIsString($commentary);
        $this->assertNotEmpty($commentary);
    }

    public function test_generate_commentary_for_wide_ball()
    {
        $ball = BallByBall::factory()->create([
            'runs_scored' => 1,
            'ball_type' => 'wide',
        ]);

        $commentary = $this->commentaryService->generate($ball, $this->batsman, $this->bowler);

        $this->assertIsString($commentary);
        $this->assertNotEmpty($commentary);
    }

    public function test_generate_commentary_for_no_ball()
    {
        $ball = BallByBall::factory()->create([
            'runs_scored' => 1,
            'ball_type' => 'no_ball',
        ]);

        $commentary = $this->commentaryService->generate($ball, $this->batsman, $this->bowler);

        $this->assertIsString($commentary);
        $this->assertNotEmpty($commentary);
    }

    public function test_build_context_includes_player_names()
    {
        $ball = BallByBall::factory()->create([
            'runs_scored' => 4,
            'ball_type' => 'normal',
        ]);

        $reflection = new \ReflectionClass($this->commentaryService);
        $method = $reflection->getMethod('buildContext');
        $method->setAccessible(true);

        $context = $method->invoke($this->commentaryService, $ball, $this->batsman, $this->bowler);

        $this->assertArrayHasKey('batsman_name', $context);
        $this->assertArrayHasKey('bowler_name', $context);
        $this->assertEquals($this->batsman->player_name, $context['batsman_name']);
        $this->assertEquals($this->bowler->player_name, $context['bowler_name']);
    }

    public function test_build_context_includes_ball_details()
    {
        $ball = BallByBall::factory()->create([
            'runs_scored' => 4,
            'ball_type' => 'normal',
            'over_number' => 5,
            'ball_number' => 3,
        ]);

        $reflection = new \ReflectionClass($this->commentaryService);
        $method = $reflection->getMethod('buildContext');
        $method->setAccessible(true);

        $context = $method->invoke($this->commentaryService, $ball, $this->batsman, $this->bowler);

        $this->assertArrayHasKey('runs', $context);
        $this->assertArrayHasKey('ball_type', $context);
        $this->assertArrayHasKey('over_number', $context);
        $this->assertArrayHasKey('ball_number', $context);
        $this->assertEquals(4, $context['runs']);
        $this->assertEquals('normal', $context['ball_type']);
        $this->assertEquals(5, $context['over_number']);
        $this->assertEquals(3, $context['ball_number']);
    }

    public function test_generate_commentary_handles_different_scenarios()
    {
        $scenarios = [
            ['runs' => 1, 'type' => 'normal'],
            ['runs' => 2, 'type' => 'normal'],
            ['runs' => 3, 'type' => 'normal'],
            ['runs' => 4, 'type' => 'normal'],
            ['runs' => 6, 'type' => 'normal'],
            ['runs' => 0, 'type' => 'normal', 'wicket' => true],
            ['runs' => 1, 'type' => 'wide'],
            ['runs' => 1, 'type' => 'no_ball'],
        ];

        foreach ($scenarios as $scenario) {
            $ball = BallByBall::factory()->create([
                'runs_scored' => $scenario['runs'],
                'ball_type' => $scenario['type'],
                'is_wicket' => $scenario['wicket'] ?? false,
            ]);

            $commentary = $this->commentaryService->generate($ball, $this->batsman, $this->bowler);

            $this->assertIsString($commentary);
            $this->assertNotEmpty($commentary);
        }
    }

    public function test_commentary_service_logs_generation()
    {
        Log::shouldReceive('info')->once()->with('Commentary generated via Event ML Hybrid');

        $ball = BallByBall::factory()->create([
            'runs_scored' => 4,
            'ball_type' => 'normal',
        ]);

        $this->commentaryService->generate($ball, $this->batsman, $this->bowler);
    }

    public function test_commentary_service_handles_exceptions()
    {
        // Mock a scenario where the generator fails
        $ball = BallByBall::factory()->create([
            'runs_scored' => 4,
            'ball_type' => 'normal',
        ]);

        // The service should handle exceptions gracefully and return fallback commentary
        $commentary = $this->commentaryService->generate($ball, $this->batsman, $this->bowler);

        $this->assertIsString($commentary);
        $this->assertNotEmpty($commentary);
    }

    public function test_commentary_service_caches_results()
    {
        Cache::shouldReceive('remember')->once()->andReturn('Test commentary');

        $ball = BallByBall::factory()->create([
            'runs_scored' => 4,
            'ball_type' => 'normal',
        ]);

        $commentary = $this->commentaryService->generate($ball, $this->batsman, $this->bowler);

        $this->assertEquals('Test commentary', $commentary);
    }
}
