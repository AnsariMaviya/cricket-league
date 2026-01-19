<?php

namespace App\Services;

use App\Models\CricketMatch;
use App\Models\MatchInnings;
use App\Models\BallByBall;
use App\Models\PlayerMatchStats;
use App\Models\MatchCommentary;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LiveScoreboardService
{
    public function getScoreboard($matchId)
    {
        $cacheKey = "scoreboard_{$matchId}";
        
        // Cache for 10 seconds - WebSocket handles real-time updates
        return Cache::remember($cacheKey, 10, function () use ($matchId) {
            $match = CricketMatch::with([
                'firstTeam', 
                'secondTeam', 
                'venue'
            ])->findOrFail($matchId);

            $innings = MatchInnings::where('match_id', $matchId)
                ->orderBy('innings_number')
                ->get();

            $currentInnings = $innings->where('status', 'in_progress')->first();
            
            $battingStats = [];
            $bowlingStats = [];
            
            if ($currentInnings) {
                $battingStats = PlayerMatchStats::where('match_id', $matchId)
                    ->where('team_id', $currentInnings->batting_team_id)
                    ->where('balls_faced', '>', 0)
                    ->with('player')
                    ->orderByDesc('balls_faced')
                    ->get()
                    ->map(function ($stat) {
                        return [
                            'player_name' => $stat->player->name,
                            'runs' => $stat->runs_scored,
                            'balls' => $stat->balls_faced,
                            'fours' => $stat->fours,
                            'sixes' => $stat->sixes,
                            'strike_rate' => $stat->strike_rate,
                            'dismissal' => $stat->dismissal_text ?? 'not out',
                        ];
                    });

                $bowlingStats = PlayerMatchStats::where('match_id', $matchId)
                    ->where('team_id', $currentInnings->bowling_team_id)
                    ->where('balls_bowled', '>', 0)
                    ->with('player')
                    ->orderByDesc('balls_bowled')
                    ->get();
            }

            $recentBalls = BallByBall::where('match_id', $matchId)
                ->with(['batsman', 'bowler'])
                ->orderByDesc('ball_id')
                ->take(6)
                ->get();

            $commentary = MatchCommentary::where('match_id', $matchId)
                ->orderByDesc('created_at')
                ->take(20)
                ->get();

            // Get current players in single query
            $currentPlayerIds = array_filter([
                $match->current_batsman_1,
                $match->current_batsman_2,
                $match->current_bowler
            ]);
            
            $currentPlayersStats = [];
            if (!empty($currentPlayerIds)) {
                $currentPlayersStats = PlayerMatchStats::where('match_id', $matchId)
                    ->whereIn('player_id', $currentPlayerIds)
                    ->with('player')
                    ->get()
                    ->keyBy('player_id');
            }
            
            
            $currentBatsmen = [];
            if ($match->current_batsman_1 && isset($currentPlayersStats[$match->current_batsman_1])) {
                $currentBatsmen[] = $currentPlayersStats[$match->current_batsman_1];
            }
            if ($match->current_batsman_2 && isset($currentPlayersStats[$match->current_batsman_2])) {
                $currentBatsmen[] = $currentPlayersStats[$match->current_batsman_2];
            }

            $currentBowler = null;
            if ($match->current_bowler && isset($currentPlayersStats[$match->current_bowler])) {
                $currentBowler = $currentPlayersStats[$match->current_bowler];
            }

            return [
                'match' => $match,
                'innings' => $innings,
                'current_innings' => $currentInnings,
                'batting_stats' => $battingStats,
                'bowling_stats' => $bowlingStats,
                'current_batsmen' => $currentBatsmen,
                'current_bowler' => $currentBowler,
                'recent_balls' => $recentBalls,
                'commentary' => $commentary,
                'run_rate' => $currentInnings ? $currentInnings->run_rate : 0,
                'required_run_rate' => $this->calculateRequiredRunRate($match, $currentInnings),
            ];
        });
    }

    public function updateScoreboard($matchId)
    {
        Cache::forget("match_scoreboard_{$matchId}");
        return $this->getScoreboard($matchId);
    }

    protected function calculateRequiredRunRate($match, $currentInnings)
    {
        if (!$currentInnings || $match->current_innings !== 2 || !$match->target_score) {
            return 0;
        }

        $runsNeeded = $match->target_score - $currentInnings->total_runs;
        $oversRemaining = $match->overs - $currentInnings->overs;

        if ($oversRemaining <= 0) return 0;

        return round($runsNeeded / $oversRemaining, 2);
    }

    public function getMiniScoreboard($matchId)
    {
        $match = CricketMatch::with(['firstTeam', 'secondTeam'])->findOrFail($matchId);
        
        return [
            'match_id' => $match->match_id,
            'status' => $match->status,
            'first_team' => [
                'name' => $match->firstTeam->team_name,
                'score' => $match->first_team_score,
            ],
            'second_team' => [
                'name' => $match->secondTeam->team_name,
                'score' => $match->second_team_score,
            ],
            'current_over' => $match->current_over,
            'outcome' => $match->outcome,
        ];
    }

    public function getOverSummary($matchId, $overNumber)
    {
        $balls = BallByBall::where('match_id', $matchId)
            ->where('over_number', $overNumber)
            ->orderBy('ball_number')
            ->with(['batsman', 'bowler'])
            ->get();

        $totalRuns = $balls->sum('runs_scored') + $balls->sum('extra_runs');
        $wickets = $balls->where('is_wicket', true)->count();

        return [
            'over_number' => $overNumber,
            'balls' => $balls,
            'total_runs' => $totalRuns,
            'wickets' => $wickets,
        ];
    }

    public function getMatchSummary($matchId)
    {
        $match = CricketMatch::with(['firstTeam', 'secondTeam', 'venue'])->findOrFail($matchId);
        
        $innings = MatchInnings::where('match_id', $matchId)
            ->with(['battingTeam', 'bowlingTeam'])
            ->get();

        $topBatsmen = PlayerMatchStats::where('match_id', $matchId)
            ->orderByDesc('runs_scored')
            ->take(5)
            ->with('player')
            ->get();

        $topBowlers = PlayerMatchStats::where('match_id', $matchId)
            ->orderByDesc('wickets_taken')
            ->take(5)
            ->with('player')
            ->get();

        return [
            'match' => $match,
            'innings' => $innings,
            'top_batsmen' => $topBatsmen,
            'top_bowlers' => $topBowlers,
            'mom' => $this->calculateManOfTheMatch($matchId),
        ];
    }

    protected function calculateManOfTheMatch($matchId)
    {
        $stats = PlayerMatchStats::where('match_id', $matchId)
            ->with('player')
            ->get();

        $bestPerformer = $stats->sortByDesc(function ($stat) {
            return ($stat->runs_scored * 0.5) + ($stat->wickets_taken * 20) + 
                   ($stat->catches * 10) + ($stat->run_outs * 15);
        })->first();

        return $bestPerformer;
    }
}
