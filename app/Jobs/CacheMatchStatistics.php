<?php

namespace App\Jobs;

use App\Models\CricketMatch;
use App\Services\StatsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class CacheMatchStatistics implements ShouldQueue
{
    use Queueable;

    protected $matchId;

    public function __construct($matchId)
    {
        $this->matchId = $matchId;
    }

    public function handle(): void
    {
        $match = CricketMatch::find($this->matchId);
        
        if ($match && $match->status === 'completed') {
            $statsService = app(StatsService::class);
            
            // Pre-calculate and cache all statistics
            $scorecard = $statsService->getMatchDetailedStats($this->matchId);
            Cache::forever("match_scorecard_{$this->matchId}", $scorecard);
            
            // Update player career stats
            $playerIds = $match->playerStats()->pluck('player_id')->unique();
            foreach ($playerIds as $playerId) {
                $statsService->updatePlayerCareerStats($playerId);
            }
        }
    }
}
