<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PlayerMatchStats;

class CheckBowlerStats extends Command
{
    protected $signature = 'check:bowler-stats {matchId}';
    protected $description = 'Check bowler stats for a match';

    public function handle()
    {
        $matchId = $this->argument('matchId');
        
        $stats = PlayerMatchStats::where('match_id', $matchId)
            ->whereNotNull('balls_bowled')
            ->where('balls_bowled', '>', 0)
            ->get(['player_id', 'balls_bowled', 'overs_bowled']);

        $this->info("Current bowler stats for match {$matchId}:");
        foreach ($stats as $stat) {
            $this->info("Player {$stat->player_id}: balls_bowled={$stat->balls_bowled}, overs_bowled={$stat->overs_bowled}");
            
            $calculated = $stat->balls_bowled / 6;
            $this->info("  Should be: {$calculated}");
        }
    }
}
