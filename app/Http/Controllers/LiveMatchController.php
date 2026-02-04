<?php

namespace App\Http\Controllers;

use App\Models\CricketMatch;
use App\Models\MatchCommentary;
use App\Services\MatchSimulationEngine;
use App\Services\LiveScoreboardService;
use App\Services\CacheService;
use App\Services\MonitoringService;
use App\Services\QueryOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LiveMatchController extends Controller
{
    protected $simulationEngine;
    protected $scoreboardService;

    public function __construct()
    {
        $this->simulationEngine = new MatchSimulationEngine();
        $this->scoreboardService = new LiveScoreboardService();
    }

    public function startMatch($matchId)
    {
        $startTime = microtime(true);
        
        $match = CricketMatch::findOrFail($matchId);
        
        // If match is not scheduled, reset it first (for stopped/completed matches)
        if ($match->status !== 'scheduled') {
            // Reset match to scheduled status and clear previous data
            $match->status = 'scheduled';
            $match->current_innings = 0;
            $match->current_over = 0;
            $match->started_at = null;
            $match->first_team_score = null;
            $match->second_team_score = null;
            $match->current_batsman_1 = null;
            $match->current_batsman_2 = null;
            $match->current_bowler = null;
            $match->target_score = null;
            $match->outcome = null;
            $match->save();
            
            // Delete existing match data
            $match->innings()->delete();
            $match->balls()->delete();
            $match->playerStats()->delete();
            $match->commentary()->delete();
        }

        $result = $this->simulationEngine->startMatch($match);
        
        // Cache the updated match
        CacheService::cacheMatch($match->match_id, $result->fresh());
        
        // Log performance
        $duration = microtime(true) - $startTime;
        MonitoringService::logPerformance('match_start', $duration, ['match_id' => $matchId]);
        MonitoringService::logApiRequest('/api/v1/live-matches/{id}/start', 'POST', 200, $duration);
        
        // Invalidate related caches
        CacheService::invalidateMatchCache($matchId);
        
        return response()->json([
            'message' => 'Match started successfully',
            'match' => $result->load(['venue', 'firstTeam', 'secondTeam']),
        ]);
    }

    public function simulateBall($matchId)
    {
        $match = CricketMatch::findOrFail($matchId);
        
        if ($match->status !== 'live') {
            return response()->json(['error' => 'Match is not live'], 400);
        }

        $this->simulationEngine->match = $match;
        $this->simulationEngine->currentInnings = $match->innings()
            ->where('status', 'in_progress')
            ->first();

        if (!$this->simulationEngine->currentInnings) {
            return response()->json(['error' => 'No active innings'], 400);
        }

        // If match doesn't have players assigned, restart the match
        if (!$match->current_batsman_1 || !$match->current_batsman_2 || !$match->current_bowler) {
            // Reset match and start properly
            $match->status = 'scheduled';
            $match->current_innings = 0;
            $match->current_over = 0;
            $match->started_at = null;
            $match->save();
            
            // Delete existing innings
            $match->innings()->delete();
            $match->balls()->delete();
            $match->playerStats()->delete();
            $match->commentary()->delete();
            
            // Start match properly
            $this->simulationEngine->startMatch($match);
        }

        $ball = $this->simulationEngine->simulateBall();

        // Return lightweight scoreboard update with ball data
        $match = $match->fresh(['firstTeam', 'secondTeam']);
        $currentInnings = $match->innings()->where('status', 'in_progress')->first();
        
        return response()->json([
            'success' => true,
            'ball' => $ball,
            'score' => [
                'runs' => $currentInnings->total_runs ?? 0,
                'wickets' => $currentInnings->wickets ?? 0,
                'overs' => $currentInnings->overs ?? 0,
                'current_over' => $match->current_over,
                'first_team_score' => $match->first_team_score,
                'second_team_score' => $match->second_team_score,
            ],
            'status' => $match->status,
        ]);
    }

    public function autoSimulate($matchId, Request $request)
    {
        $match = CricketMatch::findOrFail($matchId);
        $delaySeconds = $request->input('delay', 3);

        // Start match if it's scheduled
        if ($match->status === 'scheduled') {
            $this->simulationEngine->startMatch($match);
        } elseif ($match->status !== 'live') {
            return response()->json(['error' => 'Match cannot be simulated'], 400);
        }
        
        // Ensure match state is loaded for live matches
        $this->simulationEngine->match = $match;
        $this->simulationEngine->currentInnings = $match->innings()
            ->where('status', 'in_progress')
            ->first();

        if (!$this->simulationEngine->currentInnings) {
            return response()->json(['error' => 'No active innings'], 400);
        }

        $result = $this->simulationEngine->autoSimulate($match, $delaySeconds);

        return response()->json([
            'success' => true,
            'message' => 'Match simulation completed',
            'match' => $result,
        ]);
    }

    public function getScoreboard($matchId)
    {
        $scoreboard = $this->scoreboardService->getScoreboard($matchId);
        return response()->json($scoreboard);
    }

    public function getMiniScoreboard($matchId)
    {
        return response()->json(
            $this->scoreboardService->getMiniScoreboard($matchId)
        );
    }

    public function getOverSummary($matchId, $overNumber)
    {
        return response()->json(
            $this->scoreboardService->getOverSummary($matchId, $overNumber)
        );
    }

    public function getMatchSummary($matchId)
    {
        return response()->json(
            $this->scoreboardService->getMatchSummary($matchId)
        );
    }

    public function getLiveMatches()
    {
        $liveMatches = CricketMatch::where('status', 'live')
            ->with(['firstTeam', 'secondTeam', 'venue'])
            ->get()
            ->map(function ($match) {
                return $this->scoreboardService->getMiniScoreboard($match->match_id);
            });

        return response()->json($liveMatches);
    }

    public function getUpcomingMatches()
    {
        $matches = CricketMatch::where('status', 'scheduled')
            ->with(['firstTeam', 'secondTeam', 'venue'])
            ->orderBy('match_date')
            ->take(10)
            ->get();

        return response()->json($matches);
    }

    public function startAutoSimulation($matchId, Request $request)
    {
        $match = CricketMatch::findOrFail($matchId);
        $delaySeconds = $request->input('delay', 2); // Default 2 seconds between balls
        
        if ($match->status !== 'live') {
            return response()->json(['error' => 'Match must be live to start simulation'], 400);
        }

        // Clear any stop flag
        \Illuminate\Support\Facades\Cache::forget("stop_simulation_{$matchId}");
        
        // Dispatch the background job
        \App\Jobs\SimulateMatchJob::dispatch($matchId, $delaySeconds);
        
        return response()->json([
            'success' => true,
            'message' => 'Auto-simulation started in background',
            'delay_seconds' => $delaySeconds
        ]);
    }

    public function stopAutoSimulation($matchId)
    {
        // Set a flag to stop the simulation
        \Illuminate\Support\Facades\Cache::put("stop_simulation_{$matchId}", true, 300);
        
        return response()->json([
            'success' => true,
            'message' => 'Auto-simulation will stop after current ball'
        ]);
    }

    public function stopMatch($matchId)
    {
        $match = CricketMatch::findOrFail($matchId);
        
        // Stop any running simulation
        \Illuminate\Support\Facades\Cache::put("stop_simulation_{$matchId}", true, 300);
        
        return response()->json([
            'success' => true,
            'message' => 'Match simulation stopped'
        ]);
    }

    public function getAllCommentary($matchId)
    {
        $commentary = MatchCommentary::where('match_id', $matchId)
            ->orderByDesc('commentary_id')
            ->get();

        return response()->json($commentary);
    }
}
