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
        // Create Countries
        $countries = [
            ['name' => 'India', 'short_name' => 'IND'],
            ['name' => 'Australia', 'short_name' => 'AUS'],
            ['name' => 'England', 'short_name' => 'ENG'],
            ['name' => 'Pakistan', 'short_name' => 'PAK'],
            ['name' => 'South Africa', 'short_name' => 'SA'],
            ['name' => 'New Zealand', 'short_name' => 'NZ'],
            ['name' => 'West Indies', 'short_name' => 'WI'],
            ['name' => 'Sri Lanka', 'short_name' => 'SL'],
        ];

        foreach ($countries as $country) {
            Country::create($country);
        }

        // Create Teams
        $teams = [
            ['team_name' => 'Mumbai Indians', 'country_id' => 1, 'in_match' => 'IPL'],
            ['team_name' => 'Chennai Super Kings', 'country_id' => 1, 'in_match' => 'IPL'],
            ['team_name' => 'Royal Challengers Bangalore', 'country_id' => 1, 'in_match' => 'IPL'],
            ['team_name' => 'Kolkata Knight Riders', 'country_id' => 1, 'in_match' => 'IPL'],
            ['team_name' => 'Sydney Sixers', 'country_id' => 2, 'in_match' => 'BBL'],
            ['team_name' => 'Melbourne Stars', 'country_id' => 2, 'in_match' => 'BBL'],
            ['team_name' => 'London Spirit', 'country_id' => 3, 'in_match' => 'The Hundred'],
            ['team_name' => 'Oval Invincibles', 'country_id' => 3, 'in_match' => 'The Hundred'],
        ];

        foreach ($teams as $team) {
            Team::create($team);
        }

        // Create Players
        $players = [
            ['name' => 'Rohit Sharma', 'team_id' => 1, 'dob' => '1987-04-30', 'role' => 'Batsman', 'batting_style' => 'Right-handed'],
            ['name' => 'Jasprit Bumrah', 'team_id' => 1, 'dob' => '1993-12-06', 'role' => 'Bowler', 'bowling_style' => 'Right-arm fast'],
            ['name' => 'MS Dhoni', 'team_id' => 2, 'dob' => '1981-07-07', 'role' => 'Wicket-keeper', 'batting_style' => 'Right-handed'],
            ['name' => 'Ravindra Jadeja', 'team_id' => 2, 'dob' => '1988-12-06', 'role' => 'All-rounder', 'batting_style' => 'Left-handed', 'bowling_style' => 'Left-arm spin'],
            ['name' => 'Virat Kohli', 'team_id' => 3, 'dob' => '1988-11-05', 'role' => 'Batsman', 'batting_style' => 'Right-handed'],
            ['name' => 'Glenn Maxwell', 'team_id' => 3, 'dob' => '1988-10-14', 'role' => 'All-rounder', 'batting_style' => 'Right-handed', 'bowling_style' => 'Right-arm spin'],
            ['name' => 'Andre Russell', 'team_id' => 4, 'dob' => '1988-04-29', 'role' => 'All-rounder', 'batting_style' => 'Right-handed', 'bowling_style' => 'Right-arm fast'],
            ['name' => 'Sunil Narine', 'team_id' => 4, 'dob' => '1988-05-26', 'role' => 'Bowler', 'bowling_style' => 'Right-arm spin'],
        ];

        foreach ($players as $player) {
            Player::create($player);
        }

        // Create Venues
        $venues = [
            ['name' => 'Wankhede Stadium', 'address' => 'Mumbai, Maharashtra', 'city' => 'Mumbai', 'capacity' => 33000],
            ['name' => 'M. A. Chidambaram Stadium', 'address' => 'Chennai, Tamil Nadu', 'city' => 'Chennai', 'capacity' => 50000],
            ['name' => 'M. Chinnaswamy Stadium', 'address' => 'Bangalore, Karnataka', 'city' => 'Bangalore', 'capacity' => 40000],
            ['name' => 'Eden Gardens', 'address' => 'Kolkata, West Bengal', 'city' => 'Kolkata', 'capacity' => 68000],
            ['name' => 'Melbourne Cricket Ground', 'address' => 'Melbourne, Victoria', 'city' => 'Melbourne', 'capacity' => 100024],
            ['name' => 'Lords Cricket Ground', 'address' => 'London, England', 'city' => 'London', 'capacity' => 30000],
        ];

        foreach ($venues as $venue) {
            Venue::create($venue);
        }

        // Create Matches
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
            ],
            [
                'venue_id' => 4,
                'first_team_id' => 4,
                'second_team_id' => 1,
                'match_type' => 'T20',
                'overs' => 20,
                'match_date' => '2024-05-01',
                'status' => 'scheduled',
            ],
            [
                'venue_id' => 1,
                'first_team_id' => 1,
                'second_team_id' => 3,
                'match_type' => 'T20',
                'overs' => 20,
                'match_date' => '2024-05-05',
                'status' => 'scheduled',
            ],
        ];

        foreach ($matches as $match) {
            CricketMatch::create($match);
        }
    }
}
