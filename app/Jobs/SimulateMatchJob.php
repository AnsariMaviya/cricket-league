<?php

namespace App\Jobs;

use App\Models\CricketMatch;
use App\Services\MatchSimulationEngine;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SimulateMatchJob implements ShouldQueue
{
    use Queueable;

    public $matchId;
    public $delaySeconds;

    public function __construct($matchId, $delaySeconds = 2)
    {
        $this->matchId = $matchId;
        $this->delaySeconds = $delaySeconds;
    }

    public function handle(): void
    {
        try {
            $match = CricketMatch::find($this->matchId);
            
            if (!$match || $match->status !== 'live') {
                Log::info("Match {$this->matchId} is not live, stopping simulation");
                return;
            }

            // Check if simulation should stop (controlled by cache flag)
            $stopFlag = Cache::get("stop_simulation_{$this->matchId}");
            if ($stopFlag) {
                Log::info("Simulation stopped for match {$this->matchId}");
                Cache::forget("stop_simulation_{$this->matchId}");
                return;
            }

            $engine = new MatchSimulationEngine();
            $engine->match = $match;
            $engine->currentInnings = $match->innings()
                ->where('status', 'in_progress')
                ->first();

            if (!$engine->currentInnings) {
                Log::info("No active innings for match {$this->matchId}");
                return;
            }

            // Simulate one ball
            $ball = $engine->simulateBall();
            
            if (!$ball) {
                Log::info("Ball simulation returned null for match {$this->matchId}");
                return;
            }

            Log::info("Ball simulated for match {$this->matchId}", [
                'over' => $ball->over_number,
                'runs' => $ball->runs_scored
            ]);

            // Check if match is still live after this ball
            $match = $match->fresh();
            if ($match->status === 'live') {
                // Schedule next ball simulation
                dispatch(new SimulateMatchJob($this->matchId, $this->delaySeconds))
                    ->delay(now()->addSeconds($this->delaySeconds));
            } else {
                Log::info("Match {$this->matchId} completed");
            }

        } catch (\Exception $e) {
            Log::error("Error simulating match {$this->matchId}: " . $e->getMessage());
        }
    }
}
