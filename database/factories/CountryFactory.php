<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Country>
 */
class CountryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $countries = [
            ['name' => 'India', 'short_name' => 'IND'],
            ['name' => 'Australia', 'short_name' => 'AUS'],
            ['name' => 'England', 'short_name' => 'ENG'],
            ['name' => 'Pakistan', 'short_name' => 'PAK'],
            ['name' => 'South Africa', 'short_name' => 'SA'],
            ['name' => 'New Zealand', 'short_name' => 'NZ'],
            ['name' => 'West Indies', 'short_name' => 'WI'],
            ['name' => 'Sri Lanka', 'short_name' => 'SL'],
            ['name' => 'Bangladesh', 'short_name' => 'BAN'],
            ['name' => 'Afghanistan', 'short_name' => 'AFG'],
        ];

        $country = $this->faker->unique()->randomElement($countries);

        return [
            'name' => $country['name'],
            'short_name' => $country['short_name'],
        ];
    }
}
