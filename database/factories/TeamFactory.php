<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Team>
 */
class TeamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $teams = [
            'Mumbai Indians', 'Chennai Super Kings', 'Royal Challengers Bangalore',
            'Kolkata Knight Riders', 'Delhi Capitals', 'Rajasthan Royals',
            'Sunrisers Hyderabad', 'Punjab Kings', 'Sydney Sixers',
            'Melbourne Stars', 'Perth Scorchers', 'Brisbane Heat',
            'London Spirit', 'Oval Invincibles', 'Manchester Originals',
            'Welsh Fire', 'Southern Brave', 'Northern Superchargers',
            'Trinbago Knight Riders', 'Guyana Amazon Warriors', 'Barbados Royals'
        ];

        return [
            'team_name' => $this->faker->unique()->randomElement($teams),
            'country_id' => Country::factory(),
            'in_match' => $this->faker->randomElement(['IPL', 'BBL', 'The Hundred', 'CPL', null]),
        ];
    }
}
