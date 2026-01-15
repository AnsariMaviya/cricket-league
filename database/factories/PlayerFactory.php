<?php

namespace Database\Factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Player>
 */
class PlayerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstNames = ['Virat', 'Rohit', 'MS', 'Jasprit', 'Ravindra', 'Hardik', 'KL', 'Rishabh', 'Shikhar', 'Bhuvneshwar'];
        $lastNames = ['Kohli', 'Sharma', 'Dhoni', 'Bumrah', 'Jadeja', 'Pandya', 'Rahul', 'Pant', 'Dhawan', 'Kumar'];
        
        return [
            'name' => $this->faker->randomElement($firstNames) . ' ' . $this->faker->randomElement($lastNames),
            'team_id' => Team::factory(),
            'dob' => $this->faker->dateTimeBetween('-35 years', '-18 years'),
            'role' => $this->faker->randomElement(['Batsman', 'Bowler', 'All-rounder', 'Wicket-keeper']),
            'batting_style' => $this->faker->randomElement(['Right-handed', 'Left-handed']),
            'bowling_style' => $this->faker->randomElement(['Right-arm fast', 'Left-arm fast', 'Right-arm spin', 'Left-arm spin', null]),
        ];
    }
}
