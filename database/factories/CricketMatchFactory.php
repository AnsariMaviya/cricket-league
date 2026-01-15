<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CricketMatch>
 */
class CricketMatchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(['scheduled', 'live', 'completed', 'cancelled']);
        $matchType = $this->faker->randomElement(['T20', 'ODI', 'Test']);
        
        $overs = match($matchType) {
            'T20' => 20,
            'ODI' => 50,
            'Test' => 90,
            default => 20
        };

        $data = [
            'venue_id' => Venue::factory(),
            'first_team_id' => Team::factory(),
            'second_team_id' => Team::factory(),
            'match_type' => $matchType,
            'overs' => $overs,
            'match_date' => $this->faker->dateTimeBetween('-1 year', '+6 months'),
            'status' => $status,
        ];

        if ($status === 'completed') {
            $team1Score = $this->faker->numberBetween(120, 250);
            $team2Score = $this->faker->numberBetween(120, 250);
            
            $data['first_team_score'] = $team1Score . '/' . $this->faker->numberBetween(5, 10);
            $data['second_team_score'] = $team2Score . '/' . $this->faker->numberBetween(5, 10);
            
            $winner = $team1Score > $team2Score ? 'first' : 'second';
            $margin = abs($team1Score - $team2Score);
            $winType = $this->faker->randomElement(['runs', 'wickets']);
            
            $data['outcome'] = "Team {$winner} won by {$margin} {$winType}";
        }

        return $data;
    }

    /**
     * Indicate that the match is scheduled.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
            'first_team_score' => null,
            'second_team_score' => null,
            'outcome' => null,
        ]);
    }

    /**
     * Indicate that the match is live.
     */
    public function live(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'live',
            'first_team_score' => $this->faker->numberBetween(80, 150) . '/' . $this->faker->numberBetween(2, 6),
            'second_team_score' => null,
            'outcome' => null,
        ]);
    }

    /**
     * Indicate that the match is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }
}
