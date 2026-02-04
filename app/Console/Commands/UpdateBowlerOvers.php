<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PlayerMatchStats;

class UpdateBowlerOvers extends Command
{
    protected $signature = 'update:bowler-overs';
    protected $description = 'Update all bowler overs to decimal values';

    public function handle()
    {
        $this->info('Updating bowler overs to decimal values...');
        
        $stats = PlayerMatchStats::where('balls_bowled', '>', 0)->get();
        
        foreach ($stats as $stat) {
            $oldOvers = $stat->overs_bowled;
            $newOvers = $stat->balls_bowled / 6;
            
            $stat->overs_bowled = $newOvers;
            $stat->save();
            
            $this->info("Updated Player {$stat->player_id}: {$oldOvers} → {$newOvers}");
        }
        
        $this->info('Done! All bowler overs updated.');
    }
}
