<?php

namespace App\Jobs;

use App\Services\StatsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class GenerateMatchScorecard implements ShouldQueue
{
    use Queueable;

    protected $matchId;

    public function __construct($matchId)
    {
        $this->matchId = $matchId;
    }

    public function handle(): void
    {
        $statsService = app(StatsService::class);
        $scorecard = $statsService->getMatchDetailedStats($this->matchId);
        
        // Cache forever for completed matches
        Cache::forever("match_scorecard_{$this->matchId}", $scorecard);
    }
}
