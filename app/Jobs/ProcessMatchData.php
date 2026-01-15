<?php

namespace App\Jobs;

use App\Models\CricketMatch;
use App\Models\Team;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProcessMatchData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $match;

    /**
     * Create a new job instance.
     */
    public function __construct(CricketMatch $match)
    {
        $this->match = $match;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Clear relevant caches
            $this->clearRelatedCaches();
            
            // Update team statistics
            $this->updateTeamStatistics();
            
            // Update venue statistics
            $this->updateVenueStatistics();
            
            // Log the processing
            Log::info('Match data processed', [
                'match_id' => $this->match->match_id,
                'teams' => [
                    'first' => $this->match->firstTeam->team_name,
                    'second' => $this->match->secondTeam->team_name
                ],
                'status' => $this->match->status
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error processing match data', [
                'match_id' => $this->match->match_id,
                'error' => $e->getMessage()
            ]);
            
            // Retry the job after 5 minutes
            $this->release(300);
        }
    }

    /**
     * Clear caches related to this match
     */
    private function clearRelatedCaches(): void
    {
        $cacheKeys = [
            'dashboard_stats',
            'recent_matches',
            'upcoming_matches',
            'analytics_dashboard',
            'api_dashboard_stats',
            'api_match_details_' . $this->match->match_id,
        ];
        
        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
        
        // Clear search caches
        for ($i = 1; $i <= 5; $i++) {
            Cache::forget("search_matches_{$this->match->firstTeam->team_name}_{$i}");
            Cache::forget("search_matches_{$this->match->secondTeam->team_name}_{$i}");
            Cache::forget("search_matches_{$this->match->venue->name}_{$i}");
        }
    }

    /**
     * Update team statistics
     */
    private function updateTeamStatistics(): void
    {
        $teams = [$this->match->firstTeam, $this->match->secondTeam];
        
        foreach ($teams as $team) {
            // Clear team-specific caches
            Cache::forget("api_team_details_{$team->team_id}");
            
            // Update team statistics in cache
            $stats = $this->calculateTeamStats($team);
            Cache::put("team_stats_{$team->team_id}", $stats, 3600);
        }
    }

    /**
     * Update venue statistics
     */
    private function updateVenueStatistics(): void
    {
        $venue = $this->match->venue;
        
        // Clear venue-specific caches
        Cache::forget("api_venue_details_{$venue->venue_id}");
        
        // Update venue statistics in cache
        $stats = $this->calculateVenueStats($venue);
        Cache::put("venue_stats_{$venue->venue_id}", $stats, 3600);
    }

    /**
     * Calculate team statistics
     */
    private function calculateTeamStats($team): array
    {
        $allMatches = $team->homeMatches->concat($team->awayMatches);
        $completedMatches = $allMatches->where('status', 'completed');
        
        $wins = 0;
        $losses = 0;
        $draws = 0;
        
        foreach ($completedMatches as $match) {
            if ($match->outcome) {
                if (stripos($match->outcome, $team->team_name) !== false) {
                    if (stripos($match->outcome, 'won') !== false) {
                        $wins++;
                    } elseif (stripos($match->outcome, 'lost') !== false) {
                        $losses++;
                    } else {
                        $draws++;
                    }
                }
            }
        }
        
        $totalPlayed = $wins + $losses + $draws;
        $winRate = $totalPlayed > 0 ? ($wins / $totalPlayed) * 100 : 0;
        
        return [
            'matches_played' => $totalPlayed,
            'wins' => $wins,
            'losses' => $losses,
            'draws' => $draws,
            'win_rate' => round($winRate, 2),
            'last_updated' => now()->toISOString(),
        ];
    }

    /**
     * Calculate venue statistics
     */
    private function calculateVenueStats($venue): array
    {
        $matches = $venue->matches;
        $completedMatches = $matches->where('status', 'completed');
        
        $averageRuns = 0;
        if ($completedMatches->count() > 0) {
            $totalRuns = 0;
            $validScores = 0;
            
            foreach ($completedMatches as $match) {
                if ($match->first_team_score) {
                    $score = explode('/', $match->first_team_score)[0];
                    if (is_numeric($score)) {
                        $totalRuns += (int)$score;
                        $validScores++;
                    }
                }
                if ($match->second_team_score) {
                    $score = explode('/', $match->second_team_score)[0];
                    if (is_numeric($score)) {
                        $totalRuns += (int)$score;
                        $validScores++;
                    }
                }
            }
            
            $averageRuns = $validScores > 0 ? $totalRuns / $validScores : 0;
        }
        
        return [
            'total_matches' => $matches->count(),
            'completed_matches' => $completedMatches->count(),
            'average_runs_per_match' => round($averageRuns, 2),
            'utilization_rate' => $matches->count() > 0 ? round(($completedMatches->count() / $matches->count()) * 100, 2) : 0,
            'last_updated' => now()->toISOString(),
        ];
    }
}
