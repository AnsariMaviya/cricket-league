<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;
use App\Models\Team;
use App\Models\Player;
use App\Models\Venue;
use App\Models\CricketMatch;

class CricketLeagueSeeder extends Seeder
{
    public function run(): void
    {
        // Create Countries - Bulk insert for performance
        $countries = [
            ['name' => 'India', 'short_name' => 'IND', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Australia', 'short_name' => 'AUS', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'England', 'short_name' => 'ENG', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pakistan', 'short_name' => 'PAK', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'South Africa', 'short_name' => 'SA', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'New Zealand', 'short_name' => 'NZ', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'West Indies', 'short_name' => 'WI', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sri Lanka', 'short_name' => 'SL', 'created_at' => now(), 'updated_at' => now()],
        ];

        Country::insert($countries);

        // Create Teams - Bulk insert for performance
        $teams = [
            ['team_name' => 'Mumbai Indians', 'country_id' => 1, 'in_match' => 'IPL', 'created_at' => now(), 'updated_at' => now()],
            ['team_name' => 'Chennai Super Kings', 'country_id' => 1, 'in_match' => 'IPL', 'created_at' => now(), 'updated_at' => now()],
            ['team_name' => 'Royal Challengers Bangalore', 'country_id' => 1, 'in_match' => 'IPL', 'created_at' => now(), 'updated_at' => now()],
            ['team_name' => 'Kolkata Knight Riders', 'country_id' => 1, 'in_match' => 'IPL', 'created_at' => now(), 'updated_at' => now()],
            ['team_name' => 'Sydney Sixers', 'country_id' => 2, 'in_match' => 'BBL', 'created_at' => now(), 'updated_at' => now()],
            ['team_name' => 'Melbourne Stars', 'country_id' => 2, 'in_match' => 'BBL', 'created_at' => now(), 'updated_at' => now()],
            ['team_name' => 'London Spirit', 'country_id' => 3, 'in_match' => 'The Hundred', 'created_at' => now(), 'updated_at' => now()],
            ['team_name' => 'Oval Invincibles', 'country_id' => 3, 'in_match' => 'The Hundred', 'created_at' => now(), 'updated_at' => now()],
        ];

        Team::insert($teams);

        // Create Players - Bulk insert for performance
        $players = [
            ['name' => 'Rohit Sharma', 'team_id' => 1, 'dob' => '1987-04-30', 'role' => 'Batsman', 'batting_style' => 'Right-handed', 'bowling_style' => null, 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Jasprit Bumrah', 'team_id' => 1, 'dob' => '1993-12-06', 'role' => 'Bowler', 'batting_style' => null, 'bowling_style' => 'Right-arm fast', 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MS Dhoni', 'team_id' => 2, 'dob' => '1981-07-07', 'role' => 'Wicket-keeper', 'batting_style' => 'Right-handed', 'bowling_style' => null, 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ravindra Jadeja', 'team_id' => 2, 'dob' => '1988-12-06', 'role' => 'All-rounder', 'batting_style' => 'Left-handed', 'bowling_style' => 'Left-arm spin', 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Virat Kohli', 'team_id' => 3, 'dob' => '1988-11-05', 'role' => 'Batsman', 'batting_style' => 'Right-handed', 'bowling_style' => null, 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Glenn Maxwell', 'team_id' => 3, 'dob' => '1988-10-14', 'role' => 'All-rounder', 'batting_style' => 'Right-handed', 'bowling_style' => 'Right-arm spin', 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Andre Russell', 'team_id' => 4, 'dob' => '1988-04-29', 'role' => 'All-rounder', 'batting_style' => 'Right-handed', 'bowling_style' => 'Right-arm fast', 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sunil Narine', 'team_id' => 4, 'dob' => '1988-05-26', 'role' => 'Bowler', 'batting_style' => null, 'bowling_style' => 'Right-arm spin', 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
        ];

        Player::insert($players);

        // Create Venues - Bulk insert for performance
        $venues = [
            ['name' => 'Wankhede Stadium', 'address' => 'Mumbai, Maharashtra', 'city' => 'Mumbai', 'capacity' => 33000, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'M. A. Chidambaram Stadium', 'address' => 'Chennai, Tamil Nadu', 'city' => 'Chennai', 'capacity' => 50000, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'M. Chinnaswamy Stadium', 'address' => 'Bangalore, Karnataka', 'city' => 'Bangalore', 'capacity' => 40000, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Eden Gardens', 'address' => 'Kolkata, West Bengal', 'city' => 'Kolkata', 'capacity' => 68000, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Melbourne Cricket Ground', 'address' => 'Melbourne, Victoria', 'city' => 'Melbourne', 'capacity' => 100024, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Lords Cricket Ground', 'address' => 'London, England', 'city' => 'London', 'capacity' => 30000, 'created_at' => now(), 'updated_at' => now()],
        ];

        Venue::insert($venues);

        // Create Matches - Bulk insert for performance
        $matches = [
            [
                'venue_id' => 1,
                'first_team_id' => 1,
                'second_team_id' => 2,
                'match_type' => 'T20',
                'overs' => 20,
                'match_date' => '2024-04-15',
                'status' => 'completed',
                'first_team_score' => '185/4',
                'second_team_score' => '182/7',
                'outcome' => 'Mumbai Indians won by 3 runs',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'venue_id' => 2,
                'first_team_id' => 2,
                'second_team_id' => 3,
                'match_type' => 'T20',
                'overs' => 20,
                'match_date' => '2024-04-18',
                'status' => 'completed',
                'first_team_score' => '192/5',
                'second_team_score' => '195/3',
                'outcome' => 'Royal Challengers Bangalore won by 7 wickets',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'venue_id' => 3,
                'first_team_id' => 3,
                'second_team_id' => 4,
                'match_type' => 'T20',
                'overs' => 20,
                'match_date' => '2024-04-22',
                'status' => 'completed',
                'first_team_score' => '175/6',
                'second_team_score' => '178/4',
                'outcome' => 'Kolkata Knight Riders won by 6 wickets',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'venue_id' => 4,
                'first_team_id' => 4,
                'second_team_id' => 1,
                'match_type' => 'T20',
                'overs' => 20,
                'match_date' => '2024-05-01',
                'status' => 'scheduled',
                'first_team_score' => null,
                'second_team_score' => null,
                'outcome' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'venue_id' => 1,
                'first_team_id' => 1,
                'second_team_id' => 3,
                'match_type' => 'T20',
                'overs' => 20,
                'match_date' => '2024-05-05',
                'status' => 'scheduled',
                'first_team_score' => null,
                'second_team_score' => null,
                'outcome' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        CricketMatch::insert($matches);
    }
}
