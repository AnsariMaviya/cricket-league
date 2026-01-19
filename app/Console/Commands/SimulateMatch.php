<?php

namespace App\Console\Commands;

use App\Models\CricketMatch;
use App\Services\MatchSimulationEngine;
use Illuminate\Console\Command;

class SimulateMatch extends Command
{
    protected $signature = 'match:simulate {match_id} {--delay=3 : Delay between balls in seconds}';
    protected $description = 'Simulate a cricket match with ball-by-ball updates';

    public function handle()
    {
        $matchId = $this->argument('match_id');
        $delay = $this->option('delay');

        $match = CricketMatch::find($matchId);

        if (!$match) {
            $this->error("Match with ID {$matchId} not found!");
            return 1;
        }

        if ($match->status !== 'scheduled') {
            $this->error("Match is not in scheduled status!");
            return 1;
        }

        $this->info("Starting match simulation for: {$match->firstTeam->team_name} vs {$match->secondTeam->team_name}");
        
        $engine = new MatchSimulationEngine();
        $engine->startMatch($match);

        $this->info("Match started! Simulating...");
        $this->newLine();

        $progressBar = $this->output->createProgressBar($match->overs * 6 * 2);

        while ($match->fresh()->status === 'live') {
            $ball = $engine->simulateBall();
            
            if ($ball) {
                $this->line($ball->commentary);
                $progressBar->advance();
                sleep($delay);
            } else {
                break;
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        $match = $match->fresh();
        $this->info("Match completed!");
        $this->info("Result: {$match->outcome}");
        $this->info("Final Score: {$match->first_team_score} vs {$match->second_team_score}");

        return 0;
    }
}
