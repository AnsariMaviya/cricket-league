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
            // IPL Teams (India)
            ['team_name' => 'Mumbai Indians', 'country_id' => 1, 'in_match' => 'IPL', 'created_at' => now(), 'updated_at' => now()],
            ['team_name' => 'Chennai Super Kings', 'country_id' => 1, 'in_match' => 'IPL', 'created_at' => now(), 'updated_at' => now()],
            ['team_name' => 'Royal Challengers Bangalore', 'country_id' => 1, 'in_match' => 'IPL', 'created_at' => now(), 'updated_at' => now()],
            ['team_name' => 'Kolkata Knight Riders', 'country_id' => 1, 'in_match' => 'IPL', 'created_at' => now(), 'updated_at' => now()],
            ['team_name' => 'Delhi Capitals', 'country_id' => 1, 'in_match' => 'IPL', 'created_at' => now(), 'updated_at' => now()],
            ['team_name' => 'Punjab Kings', 'country_id' => 1, 'in_match' => 'IPL', 'created_at' => now(), 'updated_at' => now()],
            ['team_name' => 'Rajasthan Royals', 'country_id' => 1, 'in_match' => 'IPL', 'created_at' => now(), 'updated_at' => now()],
            ['team_name' => 'Sunrisers Hyderabad', 'country_id' => 1, 'in_match' => 'IPL', 'created_at' => now(), 'updated_at' => now()],
            
            // BBL Teams (Australia)
            ['team_name' => 'Sydney Sixers', 'country_id' => 2, 'in_match' => 'BBL', 'created_at' => now(), 'updated_at' => now()],
            ['team_name' => 'Melbourne Stars', 'country_id' => 2, 'in_match' => 'BBL', 'created_at' => now(), 'updated_at' => now()],
            ['team_name' => 'Perth Scorchers', 'country_id' => 2, 'in_match' => 'BBL', 'created_at' => now(), 'updated_at' => now()],
            ['team_name' => 'Adelaide Strikers', 'country_id' => 2, 'in_match' => 'BBL', 'created_at' => now(), 'updated_at' => now()],
            
            // The Hundred Teams (England)
            ['team_name' => 'London Spirit', 'country_id' => 3, 'in_match' => 'The Hundred', 'created_at' => now(), 'updated_at' => now()],
            ['team_name' => 'Oval Invincibles', 'country_id' => 3, 'in_match' => 'The Hundred', 'created_at' => now(), 'updated_at' => now()],
            ['team_name' => 'Manchester Originals', 'country_id' => 3, 'in_match' => 'The Hundred', 'created_at' => now(), 'updated_at' => now()],
            ['team_name' => 'Birmingham Phoenix', 'country_id' => 3, 'in_match' => 'The Hundred', 'created_at' => now(), 'updated_at' => now()],
        ];

        Team::insert($teams);

        // Create Players - Bulk insert for performance (3-4 players per team)
        $players = [
            // Mumbai Indians
            ['name' => 'Rohit Sharma', 'team_id' => 1, 'dob' => '1987-04-30', 'role' => 'Batsman', 'batting_style' => 'Right-handed', 'bowling_style' => null, 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Jasprit Bumrah', 'team_id' => 1, 'dob' => '1993-12-06', 'role' => 'Bowler', 'batting_style' => null, 'bowling_style' => 'Right-arm fast', 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Suryakumar Yadav', 'team_id' => 1, 'dob' => '1990-09-14', 'role' => 'Batsman', 'batting_style' => 'Right-handed', 'bowling_style' => null, 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            
            // Chennai Super Kings
            ['name' => 'MS Dhoni', 'team_id' => 2, 'dob' => '1981-07-07', 'role' => 'Wicket-keeper', 'batting_style' => 'Right-handed', 'bowling_style' => null, 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ravindra Jadeja', 'team_id' => 2, 'dob' => '1988-12-06', 'role' => 'All-rounder', 'batting_style' => 'Left-handed', 'bowling_style' => 'Left-arm spin', 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ruturaj Gaikwad', 'team_id' => 2, 'dob' => '1997-01-31', 'role' => 'Batsman', 'batting_style' => 'Right-handed', 'bowling_style' => null, 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            
            // Royal Challengers Bangalore
            ['name' => 'Virat Kohli', 'team_id' => 3, 'dob' => '1988-11-05', 'role' => 'Batsman', 'batting_style' => 'Right-handed', 'bowling_style' => null, 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Glenn Maxwell', 'team_id' => 3, 'dob' => '1988-10-14', 'role' => 'All-rounder', 'batting_style' => 'Right-handed', 'bowling_style' => 'Right-arm spin', 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Faf du Plessis', 'team_id' => 3, 'dob' => '1984-07-13', 'role' => 'Batsman', 'batting_style' => 'Right-handed', 'bowling_style' => null, 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            
            // Kolkata Knight Riders
            ['name' => 'Andre Russell', 'team_id' => 4, 'dob' => '1988-04-29', 'role' => 'All-rounder', 'batting_style' => 'Right-handed', 'bowling_style' => 'Right-arm fast', 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sunil Narine', 'team_id' => 4, 'dob' => '1988-05-26', 'role' => 'Bowler', 'batting_style' => null, 'bowling_style' => 'Right-arm spin', 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Shreyas Iyer', 'team_id' => 4, 'dob' => '1994-12-06', 'role' => 'Batsman', 'batting_style' => 'Right-handed', 'bowling_style' => null, 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            
            // Delhi Capitals
            ['name' => 'Rishabh Pant', 'team_id' => 5, 'dob' => '1997-10-04', 'role' => 'Wicket-keeper', 'batting_style' => 'Left-handed', 'bowling_style' => null, 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Axar Patel', 'team_id' => 5, 'dob' => '1994-01-20', 'role' => 'All-rounder', 'batting_style' => 'Left-handed', 'bowling_style' => 'Left-arm spin', 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Prithvi Shaw', 'team_id' => 5, 'dob' => '1999-11-09', 'role' => 'Batsman', 'batting_style' => 'Right-handed', 'bowling_style' => null, 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            
            // Punjab Kings
            ['name' => 'Shikhar Dhawan', 'team_id' => 6, 'dob' => '1985-12-05', 'role' => 'Batsman', 'batting_style' => 'Left-handed', 'bowling_style' => null, 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kagiso Rabada', 'team_id' => 6, 'dob' => '1995-05-25', 'role' => 'Bowler', 'batting_style' => null, 'bowling_style' => 'Right-arm fast', 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Liam Livingstone', 'team_id' => 6, 'dob' => '1993-08-04', 'role' => 'All-rounder', 'batting_style' => 'Right-handed', 'bowling_style' => 'Right-arm spin', 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            
            // Rajasthan Royals
            ['name' => 'Jos Buttler', 'team_id' => 7, 'dob' => '1990-09-08', 'role' => 'Wicket-keeper', 'batting_style' => 'Right-handed', 'bowling_style' => null, 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Yuzvendra Chahal', 'team_id' => 7, 'dob' => '1990-07-23', 'role' => 'Bowler', 'batting_style' => null, 'bowling_style' => 'Right-arm leg-spin', 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sanju Samson', 'team_id' => 7, 'dob' => '1994-11-11', 'role' => 'Wicket-keeper', 'batting_style' => 'Right-handed', 'bowling_style' => null, 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            
            // Sunrisers Hyderabad
            ['name' => 'Kane Williamson', 'team_id' => 8, 'dob' => '1990-08-08', 'role' => 'Batsman', 'batting_style' => 'Right-handed', 'bowling_style' => null, 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Rashid Khan', 'team_id' => 8, 'dob' => '1998-09-20', 'role' => 'Bowler', 'batting_style' => null, 'bowling_style' => 'Right-arm leg-spin', 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Abhishek Sharma', 'team_id' => 8, 'dob' => '2000-09-05', 'role' => 'All-rounder', 'batting_style' => 'Left-handed', 'bowling_style' => 'Left-arm spin', 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            
            // Sydney Sixers
            ['name' => 'Steve Smith', 'team_id' => 9, 'dob' => '1989-06-02', 'role' => 'Batsman', 'batting_style' => 'Right-handed', 'bowling_style' => null, 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Josh Hazlewood', 'team_id' => 9, 'dob' => '1991-01-08', 'role' => 'Bowler', 'batting_style' => null, 'bowling_style' => 'Right-arm fast', 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            
            // Melbourne Stars
            ['name' => 'Marcus Stoinis', 'team_id' => 10, 'dob' => '1989-08-16', 'role' => 'All-rounder', 'batting_style' => 'Right-handed', 'bowling_style' => 'Right-arm medium', 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Adam Zampa', 'team_id' => 10, 'dob' => '1992-03-31', 'role' => 'Bowler', 'batting_style' => null, 'bowling_style' => 'Right-arm leg-spin', 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            
            // Perth Scorchers
            ['name' => 'Mitchell Marsh', 'team_id' => 11, 'dob' => '1991-10-20', 'role' => 'All-rounder', 'batting_style' => 'Right-handed', 'bowling_style' => 'Right-arm medium', 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ashton Agar', 'team_id' => 11, 'dob' => '1993-10-14', 'role' => 'All-rounder', 'batting_style' => 'Left-handed', 'bowling_style' => 'Left-arm spin', 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            
            // Adelaide Strikers
            ['name' => 'Travis Head', 'team_id' => 12, 'dob' => '1993-12-29', 'role' => 'Batsman', 'batting_style' => 'Left-handed', 'bowling_style' => null, 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Rashid Khan', 'team_id' => 12, 'dob' => '1998-09-20', 'role' => 'Bowler', 'batting_style' => null, 'bowling_style' => 'Right-arm leg-spin', 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            
            // London Spirit
            ['name' => 'Eoin Morgan', 'team_id' => 13, 'dob' => '1986-09-10', 'role' => 'Batsman', 'batting_style' => 'Left-handed', 'bowling_style' => null, 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mark Wood', 'team_id' => 13, 'dob' => '1990-01-11', 'role' => 'Bowler', 'batting_style' => null, 'bowling_style' => 'Right-arm fast', 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            
            // Oval Invincibles
            ['name' => 'Sam Curran', 'team_id' => 14, 'dob' => '1998-06-03', 'role' => 'All-rounder', 'batting_style' => 'Left-handed', 'bowling_style' => 'Left-arm medium', 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Jason Roy', 'team_id' => 14, 'dob' => '1990-07-21', 'role' => 'Batsman', 'batting_style' => 'Right-handed', 'bowling_style' => null, 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            
            // Manchester Originals
            ['name' => 'Jos Buttler', 'team_id' => 15, 'dob' => '1990-09-08', 'role' => 'Wicket-keeper', 'batting_style' => 'Right-handed', 'bowling_style' => null, 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Phil Salt', 'team_id' => 15, 'dob' => '1996-08-28', 'role' => 'Wicket-keeper', 'batting_style' => 'Right-handed', 'bowling_style' => null, 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            
            // Birmingham Phoenix
            ['name' => 'Moeen Ali', 'team_id' => 16, 'dob' => '1987-06-18', 'role' => 'All-rounder', 'batting_style' => 'Left-handed', 'bowling_style' => 'Right-arm off-spin', 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Liam Livingstone', 'team_id' => 16, 'dob' => '1993-08-04', 'role' => 'All-rounder', 'batting_style' => 'Right-handed', 'bowling_style' => 'Right-arm spin', 'profile_image' => null, 'created_at' => now(), 'updated_at' => now()],
        ];

        Player::insert($players);

        // Create Venues - Bulk insert for performance
        $venues = [
            // India
            ['name' => 'Wankhede Stadium', 'address' => 'Mumbai, Maharashtra', 'city' => 'Mumbai', 'capacity' => 33000, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'M. A. Chidambaram Stadium', 'address' => 'Chennai, Tamil Nadu', 'city' => 'Chennai', 'capacity' => 50000, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'M. Chinnaswamy Stadium', 'address' => 'Bangalore, Karnataka', 'city' => 'Bangalore', 'capacity' => 40000, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Eden Gardens', 'address' => 'Kolkata, West Bengal', 'city' => 'Kolkata', 'capacity' => 68000, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Arun Jaitley Stadium', 'address' => 'Delhi', 'city' => 'Delhi', 'capacity' => 41820, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Rajiv Gandhi Stadium', 'address' => 'Hyderabad, Telangana', 'city' => 'Hyderabad', 'capacity' => 55000, 'created_at' => now(), 'updated_at' => now()],
            
            // Australia
            ['name' => 'Melbourne Cricket Ground', 'address' => 'Melbourne, Victoria', 'city' => 'Melbourne', 'capacity' => 100024, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sydney Cricket Ground', 'address' => 'Sydney, NSW', 'city' => 'Sydney', 'capacity' => 48000, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Perth Stadium', 'address' => 'Perth, WA', 'city' => 'Perth', 'capacity' => 60000, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Adelaide Oval', 'address' => 'Adelaide, SA', 'city' => 'Adelaide', 'capacity' => 53500, 'created_at' => now(), 'updated_at' => now()],
            
            // England
            ['name' => 'Lords Cricket Ground', 'address' => 'London, England', 'city' => 'London', 'capacity' => 30000, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'The Oval', 'address' => 'London, England', 'city' => 'London', 'capacity' => 25500, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Old Trafford', 'address' => 'Manchester, England', 'city' => 'Manchester', 'capacity' => 26000, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Edgbaston', 'address' => 'Birmingham, England', 'city' => 'Birmingham', 'capacity' => 25000, 'created_at' => now(), 'updated_at' => now()],
        ];

        Venue::insert($venues);

        // Create Matches - Bulk insert for performance (15 matches total)
        $matches = [
            // Completed Matches
            ['venue_id' => 1, 'first_team_id' => 1, 'second_team_id' => 2, 'match_type' => 'T20', 'overs' => 20, 'match_date' => '2024-04-15', 'status' => 'completed', 'first_team_score' => '185/4', 'second_team_score' => '182/7', 'outcome' => 'Mumbai Indians won by 3 runs', 'created_at' => now(), 'updated_at' => now()],
            ['venue_id' => 2, 'first_team_id' => 2, 'second_team_id' => 3, 'match_type' => 'T20', 'overs' => 20, 'match_date' => '2024-04-18', 'status' => 'completed', 'first_team_score' => '192/5', 'second_team_score' => '195/3', 'outcome' => 'Royal Challengers Bangalore won by 7 wickets', 'created_at' => now(), 'updated_at' => now()],
            ['venue_id' => 3, 'first_team_id' => 3, 'second_team_id' => 4, 'match_type' => 'T20', 'overs' => 20, 'match_date' => '2024-04-22', 'status' => 'completed', 'first_team_score' => '175/6', 'second_team_score' => '178/4', 'outcome' => 'Kolkata Knight Riders won by 6 wickets', 'created_at' => now(), 'updated_at' => now()],
            ['venue_id' => 4, 'first_team_id' => 1, 'second_team_id' => 4, 'match_type' => 'T20', 'overs' => 20, 'match_date' => '2024-04-25', 'status' => 'completed', 'first_team_score' => '168/7', 'second_team_score' => '165/9', 'outcome' => 'Mumbai Indians won by 3 runs', 'created_at' => now(), 'updated_at' => now()],
            ['venue_id' => 5, 'first_team_id' => 5, 'second_team_id' => 6, 'match_type' => 'T20', 'overs' => 20, 'match_date' => '2024-04-28', 'status' => 'completed', 'first_team_score' => '201/3', 'second_team_score' => '198/6', 'outcome' => 'Delhi Capitals won by 3 runs', 'created_at' => now(), 'updated_at' => now()],
            ['venue_id' => 6, 'first_team_id' => 7, 'second_team_id' => 8, 'match_type' => 'T20', 'overs' => 20, 'match_date' => '2024-05-02', 'status' => 'completed', 'first_team_score' => '188/5', 'second_team_score' => '185/8', 'outcome' => 'Rajasthan Royals won by 3 runs', 'created_at' => now(), 'updated_at' => now()],
            ['venue_id' => 7, 'first_team_id' => 9, 'second_team_id' => 10, 'match_type' => 'T20', 'overs' => 20, 'match_date' => '2024-05-05', 'status' => 'completed', 'first_team_score' => '176/6', 'second_team_score' => '180/4', 'outcome' => 'Melbourne Stars won by 6 wickets', 'created_at' => now(), 'updated_at' => now()],
            ['venue_id' => 8, 'first_team_id' => 11, 'second_team_id' => 12, 'match_type' => 'T20', 'overs' => 20, 'match_date' => '2024-05-08', 'status' => 'completed', 'first_team_score' => '195/4', 'second_team_score' => '192/7', 'outcome' => 'Perth Scorchers won by 3 runs', 'created_at' => now(), 'updated_at' => now()],
            
            // Scheduled Matches
            ['venue_id' => 1, 'first_team_id' => 1, 'second_team_id' => 3, 'match_type' => 'T20', 'overs' => 20, 'match_date' => '2026-02-01', 'status' => 'scheduled', 'first_team_score' => null, 'second_team_score' => null, 'outcome' => null, 'created_at' => now(), 'updated_at' => now()],
            ['venue_id' => 2, 'first_team_id' => 2, 'second_team_id' => 4, 'match_type' => 'T20', 'overs' => 20, 'match_date' => '2026-02-05', 'status' => 'scheduled', 'first_team_score' => null, 'second_team_score' => null, 'outcome' => null, 'created_at' => now(), 'updated_at' => now()],
            ['venue_id' => 5, 'first_team_id' => 5, 'second_team_id' => 7, 'match_type' => 'T20', 'overs' => 20, 'match_date' => '2026-02-10', 'status' => 'scheduled', 'first_team_score' => null, 'second_team_score' => null, 'outcome' => null, 'created_at' => now(), 'updated_at' => now()],
            ['venue_id' => 6, 'first_team_id' => 6, 'second_team_id' => 8, 'match_type' => 'T20', 'overs' => 20, 'match_date' => '2026-02-15', 'status' => 'scheduled', 'first_team_score' => null, 'second_team_score' => null, 'outcome' => null, 'created_at' => now(), 'updated_at' => now()],
            ['venue_id' => 11, 'first_team_id' => 13, 'second_team_id' => 14, 'match_type' => 'T20', 'overs' => 20, 'match_date' => '2026-02-20', 'status' => 'scheduled', 'first_team_score' => null, 'second_team_score' => null, 'outcome' => null, 'created_at' => now(), 'updated_at' => now()],
            ['venue_id' => 12, 'first_team_id' => 15, 'second_team_id' => 16, 'match_type' => 'T20', 'overs' => 20, 'match_date' => '2026-02-25', 'status' => 'scheduled', 'first_team_score' => null, 'second_team_score' => null, 'outcome' => null, 'created_at' => now(), 'updated_at' => now()],
            ['venue_id' => 7, 'first_team_id' => 9, 'second_team_id' => 11, 'match_type' => 'T20', 'overs' => 20, 'match_date' => '2026-03-01', 'status' => 'scheduled', 'first_team_score' => null, 'second_team_score' => null, 'outcome' => null, 'created_at' => now(), 'updated_at' => now()],
        ];

        CricketMatch::insert($matches);
    }
}
