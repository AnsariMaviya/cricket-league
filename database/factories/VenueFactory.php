<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Venue>
 */
class VenueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $venues = [
            ['name' => 'Wankhede Stadium', 'address' => 'Mumbai, Maharashtra', 'city' => 'Mumbai', 'capacity' => 33000],
            ['name' => 'M. A. Chidambaram Stadium', 'address' => 'Chennai, Tamil Nadu', 'city' => 'Chennai', 'capacity' => 50000],
            ['name' => 'M. Chinnaswamy Stadium', 'address' => 'Bangalore, Karnataka', 'city' => 'Bangalore', 'capacity' => 40000],
            ['name' => 'Eden Gardens', 'address' => 'Kolkata, West Bengal', 'city' => 'Kolkata', 'capacity' => 68000],
            ['name' => 'Melbourne Cricket Ground', 'address' => 'Melbourne, Victoria', 'city' => 'Melbourne', 'capacity' => 100024],
            ['name' => 'Lords Cricket Ground', 'address' => 'London, England', 'city' => 'London', 'capacity' => 30000],
            ['name' => 'Sydney Cricket Ground', 'address' => 'Sydney, New South Wales', 'city' => 'Sydney', 'capacity' => 48000],
            ['name' => 'Old Trafford', 'address' => 'Manchester, England', 'city' => 'Manchester', 'capacity' => 26000],
        ];

        $venue = $this->faker->unique()->randomElement($venues);

        return [
            'name' => $venue['name'],
            'address' => $venue['address'],
            'city' => $venue['city'],
            'capacity' => $venue['capacity'],
        ];
    }
}
