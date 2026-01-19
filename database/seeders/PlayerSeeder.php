<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\Player;

class PlayerSeeder extends Seeder
{
    public function run(): void
    {
        $teams = Team::all();

        if ($teams->isEmpty()) {
            $this->command->warn('No teams found. Please create teams first.');
            return;
        }

        $playerNames = [
            'batsman' => [
                'Virat Kohli', 'Steve Smith', 'Joe Root', 'Kane Williamson', 'Babar Azam',
                'David Warner', 'Rohit Sharma', 'Jos Buttler', 'AB de Villiers', 'Martin Guptill'
            ],
            'bowler' => [
                'Jasprit Bumrah', 'Pat Cummins', 'Kagiso Rabada', 'Trent Boult', 'Mitchell Starc',
                'Rashid Khan', 'Yuzvendra Chahal', 'Jofra Archer', 'Shaheen Afridi', 'Josh Hazlewood'
            ],
            'all-rounder' => [
                'Ben Stokes', 'Shakib Al Hasan', 'Ravindra Jadeja', 'Glenn Maxwell', 'Andre Russell',
                'Hardik Pandya', 'Chris Woakes', 'Jason Holder', 'Sam Curran', 'Mitchell Marsh'
            ],
            'wicket-keeper' => [
                'MS Dhoni', 'Quinton de Kock', 'Jos Buttler', 'Rishabh Pant', 'KL Rahul',
                'Alex Carey', 'Ben Foakes', 'Mohammad Rizwan', 'Nicholas Pooran', 'Ishan Kishan'
            ]
        ];

        $battingStyles = ['Right-hand bat', 'Left-hand bat'];
        $bowlingStyles = ['Right-arm fast', 'Left-arm fast', 'Right-arm medium', 'Right-arm off-break', 'Right-arm leg-break', 'Left-arm orthodox'];

        foreach ($teams as $team) {
            $this->command->info("Adding players to {$team->team_name}...");

            // Check if team already has players
            $existingPlayersCount = Player::where('team_id', $team->team_id)->count();
            if ($existingPlayersCount >= 11) {
                $this->command->warn("  Team already has {$existingPlayersCount} players. Skipping.");
                continue;
            }

            // Add 4 Batsmen
            for ($i = 0; $i < 4; $i++) {
                Player::create([
                    'name' => $playerNames['batsman'][$i] . ' (' . $team->team_name . ')',
                    'team_id' => $team->team_id,
                    'dob' => now()->subYears(rand(22, 35))->format('Y-m-d'),
                    'role' => 'Batsman',
                    'batting_style' => $battingStyles[array_rand($battingStyles)],
                    'bowling_style' => null,
                ]);
            }

            // Add 3 Bowlers
            for ($i = 0; $i < 3; $i++) {
                Player::create([
                    'name' => $playerNames['bowler'][$i] . ' (' . $team->team_name . ')',
                    'team_id' => $team->team_id,
                    'dob' => now()->subYears(rand(22, 35))->format('Y-m-d'),
                    'role' => 'Bowler',
                    'batting_style' => $battingStyles[array_rand($battingStyles)],
                    'bowling_style' => $bowlingStyles[array_rand($bowlingStyles)],
                ]);
            }

            // Add 3 All-rounders
            for ($i = 0; $i < 3; $i++) {
                Player::create([
                    'name' => $playerNames['all-rounder'][$i] . ' (' . $team->team_name . ')',
                    'team_id' => $team->team_id,
                    'dob' => now()->subYears(rand(22, 35))->format('Y-m-d'),
                    'role' => 'All-rounder',
                    'batting_style' => $battingStyles[array_rand($battingStyles)],
                    'bowling_style' => $bowlingStyles[array_rand($bowlingStyles)],
                ]);
            }

            // Add 1 Wicket-keeper
            Player::create([
                'name' => $playerNames['wicket-keeper'][0] . ' (' . $team->team_name . ')',
                'team_id' => $team->team_id,
                'dob' => now()->subYears(rand(22, 35))->format('Y-m-d'),
                'role' => 'Wicket-keeper',
                'batting_style' => $battingStyles[array_rand($battingStyles)],
                'bowling_style' => null,
            ]);

            $this->command->info("  Added 11 players to {$team->team_name}");
        }

        $this->command->info('Player seeding completed!');
    }
}
