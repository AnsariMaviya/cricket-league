<?php

namespace App\Observers;

use App\Models\CricketMatch;
use App\Events\MatchUpdated;
use App\Jobs\ProcessMatchData;

class MatchObserver
{
    public function created(CricketMatch $match)
    {
        // Dispatch job to process match data
        ProcessMatchData::dispatch($match);
    }

    public function updated(CricketMatch $match)
    {
        // Broadcast match update event for real-time updates
        broadcast(new MatchUpdated($match))->toOthers();
        
        // Dispatch job to process match data
        ProcessMatchData::dispatch($match);
    }

    public function deleted(CricketMatch $match)
    {
        // Clean up related data if needed
    }
}
