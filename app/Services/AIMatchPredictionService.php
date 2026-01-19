<?php

namespace App\Services;

use App\Models\CricketMatch;
use App\Models\MatchPrediction;
use App\Models\PlayerMatchStats;
use App\Models\Team;
use Illuminate\Support\Facades\DB;

class AIMatchPredictionService
{
    public function predictMatch(CricketMatch $match)
    {
        $team1Analysis = $this->analyzeTeam($match->first_team_id);
        $team2Analysis = $this->analyzeTeam($match->second_team_id);
        
        $venueAdvantage = $this->calculateVenueAdvantage($match);
        $recentForm = $this->calculateRecentForm($match->first_team_id, $match->second_team_id);
        $headToHead = $this->getHeadToHeadRecord($match->first_team_id, $match->second_team_id);
        
        $team1Score = $this->calculateTeamScore($team1Analysis, $venueAdvantage['team1'], $recentForm['team1'], $headToHead['team1']);
        $team2Score = $this->calculateTeamScore($team2Analysis, $venueAdvantage['team2'], $recentForm['team2'], $headToHead['team2']);
        
        $totalScore = $team1Score + $team2Score;
        $team1Probability = ($team1Score / $totalScore) * 100;
        $team2Probability = ($team2Score / $totalScore) * 100;
        
        $predictedWinner = $team1Probability > $team2Probability ? 
            $match->first_team_id : $match->second_team_id;
        
        $confidenceScore = round(max($team1Probability, $team2Probability));
        
        $factors = [
            'team1_analysis' => $team1Analysis,
            'team2_analysis' => $team2Analysis,
            'venue_advantage' => $venueAdvantage,
            'recent_form' => $recentForm,
            'head_to_head' => $headToHead,
            'team1_probability' => round($team1Probability, 2),
            'team2_probability' => round($team2Probability, 2),
        ];
        
        $prediction = MatchPrediction::updateOrCreate(
            ['match_id' => $match->match_id],
            [
                'predicted_winner_id' => $predictedWinner,
                'confidence_score' => $confidenceScore,
                'factors' => $factors,
                'is_ai_generated' => true,
            ]
        );
        
        return $prediction;
    }
    
    protected function analyzeTeam($teamId)
    {
        $recentMatches = PlayerMatchStats::where('team_id', $teamId)
            ->whereHas('match', function ($query) {
                $query->where('status', 'completed')
                    ->where('match_date', '>=', now()->subMonths(6));
            })
            ->get();
        
        $battingStrength = $recentMatches->avg('strike_rate') ?? 100;
        $bowlingStrength = 10 - ($recentMatches->avg('economy') ?? 8);
        $consistency = $this->calculateConsistency($recentMatches);
        
        return [
            'batting_strength' => round($battingStrength / 10, 2),
            'bowling_strength' => round($bowlingStrength, 2),
            'consistency' => $consistency,
            'overall_rating' => round(($battingStrength / 10 + $bowlingStrength + $consistency) / 3, 2),
        ];
    }
    
    protected function calculateConsistency($stats)
    {
        if ($stats->isEmpty()) return 5;
        
        $runs = $stats->pluck('runs_scored')->toArray();
        if (empty($runs)) return 5;
        
        $mean = array_sum($runs) / count($runs);
        $variance = array_sum(array_map(function($x) use ($mean) { 
            return pow($x - $mean, 2); 
        }, $runs)) / count($runs);
        
        $stdDev = sqrt($variance);
        $coefficient = $mean > 0 ? ($stdDev / $mean) : 1;
        
        return round((1 - min($coefficient, 1)) * 10, 2);
    }
    
    protected function calculateVenueAdvantage($match)
    {
        $team1HomeMatches = CricketMatch::where('venue_id', $match->venue_id)
            ->where(function ($query) use ($match) {
                $query->where('first_team_id', $match->first_team_id)
                      ->orWhere('second_team_id', $match->first_team_id);
            })
            ->where('status', 'completed')
            ->count();
        
        $team2HomeMatches = CricketMatch::where('venue_id', $match->venue_id)
            ->where(function ($query) use ($match) {
                $query->where('first_team_id', $match->second_team_id)
                      ->orWhere('second_team_id', $match->second_team_id);
            })
            ->where('status', 'completed')
            ->count();
        
        $team1Advantage = $team1HomeMatches > $team2HomeMatches ? 1.2 : 1.0;
        $team2Advantage = $team2HomeMatches > $team1HomeMatches ? 1.2 : 1.0;
        
        return [
            'team1' => $team1Advantage,
            'team2' => $team2Advantage,
        ];
    }
    
    protected function calculateRecentForm($team1Id, $team2Id)
    {
        $team1Wins = $this->getRecentWins($team1Id);
        $team2Wins = $this->getRecentWins($team2Id);
        
        return [
            'team1' => $team1Wins,
            'team2' => $team2Wins,
        ];
    }
    
    protected function getRecentWins($teamId)
    {
        $recentMatches = CricketMatch::where(function ($query) use ($teamId) {
                $query->where('first_team_id', $teamId)
                      ->orWhere('second_team_id', $teamId);
            })
            ->where('status', 'completed')
            ->orderByDesc('match_date')
            ->take(5)
            ->get();
        
        $wins = $recentMatches->filter(function ($match) use ($teamId) {
            return strpos($match->outcome, Team::find($teamId)->team_name) !== false;
        })->count();
        
        return $wins;
    }
    
    protected function getHeadToHeadRecord($team1Id, $team2Id)
    {
        $matches = CricketMatch::where(function ($query) use ($team1Id, $team2Id) {
                $query->where('first_team_id', $team1Id)
                      ->where('second_team_id', $team2Id);
            })
            ->orWhere(function ($query) use ($team1Id, $team2Id) {
                $query->where('first_team_id', $team2Id)
                      ->where('second_team_id', $team1Id);
            })
            ->where('status', 'completed')
            ->get();
        
        $team1 = Team::find($team1Id);
        $team2 = Team::find($team2Id);
        
        $team1Wins = $matches->filter(function ($match) use ($team1) {
            return strpos($match->outcome, $team1->team_name) !== false;
        })->count();
        
        $team2Wins = $matches->filter(function ($match) use ($team2) {
            return strpos($match->outcome, $team2->team_name) !== false;
        })->count();
        
        return [
            'team1' => $team1Wins,
            'team2' => $team2Wins,
            'total_matches' => $matches->count(),
        ];
    }
    
    protected function calculateTeamScore($analysis, $venueAdvantage, $recentForm, $headToHead)
    {
        $baseScore = $analysis['overall_rating'] * 10;
        $venueBonus = $venueAdvantage * 5;
        $formBonus = $recentForm * 2;
        $h2hBonus = $headToHead * 1.5;
        
        return $baseScore + $venueBonus + $formBonus + $h2hBonus;
    }
    
    public function analyzePlayerPerformance($playerId, $matchesCount = 10)
    {
        $stats = PlayerMatchStats::where('player_id', $playerId)
            ->whereHas('match', function ($query) {
                $query->where('status', 'completed');
            })
            ->orderByDesc('created_at')
            ->take($matchesCount)
            ->get();
        
        if ($stats->isEmpty()) {
            return null;
        }
        
        $avgRuns = $stats->avg('runs_scored');
        $avgStrikeRate = $stats->avg('strike_rate');
        $avgWickets = $stats->avg('wickets_taken');
        $avgEconomy = $stats->avg('economy');
        
        $trend = $this->calculateTrend($stats->pluck('runs_scored')->toArray());
        
        return [
            'average_runs' => round($avgRuns, 2),
            'average_strike_rate' => round($avgStrikeRate, 2),
            'average_wickets' => round($avgWickets, 2),
            'average_economy' => round($avgEconomy, 2),
            'performance_trend' => $trend,
            'consistency_score' => $this->calculateConsistency($stats),
            'matches_analyzed' => $stats->count(),
        ];
    }
    
    protected function calculateTrend($data)
    {
        if (count($data) < 2) return 'stable';
        
        $recentAvg = array_sum(array_slice($data, 0, 3)) / min(3, count($data));
        $olderAvg = array_sum(array_slice($data, -3)) / min(3, count($data));
        
        $difference = $recentAvg - $olderAvg;
        
        if ($difference > 10) return 'improving';
        if ($difference < -10) return 'declining';
        return 'stable';
    }
    
    public function recommendTeam($teamId, $matchType = 'T20')
    {
        $players = PlayerMatchStats::where('team_id', $teamId)
            ->whereHas('match', function ($query) {
                $query->where('status', 'completed')
                    ->where('match_date', '>=', now()->subMonths(3));
            })
            ->select('player_id', DB::raw('AVG(runs_scored) as avg_runs, AVG(strike_rate) as avg_sr, AVG(wickets_taken) as avg_wickets, AVG(economy) as avg_economy'))
            ->groupBy('player_id')
            ->with('player')
            ->get();
        
        $batsmen = $players->filter(function ($stat) {
            return $stat->player->role && strpos($stat->player->role, 'Batsman') !== false;
        })->sortByDesc('avg_runs')->take(6);
        
        $allRounders = $players->filter(function ($stat) {
            return $stat->player->role && strpos($stat->player->role, 'All-Rounder') !== false;
        })->sortByDesc(function ($stat) {
            return ($stat->avg_runs * 0.5) + ($stat->avg_wickets * 10);
        })->take(2);
        
        $bowlers = $players->filter(function ($stat) {
            return $stat->player->role && strpos($stat->player->role, 'Bowler') !== false;
        })->sortByDesc('avg_wickets')->take(3);
        
        return [
            'batsmen' => $batsmen->pluck('player'),
            'all_rounders' => $allRounders->pluck('player'),
            'bowlers' => $bowlers->pluck('player'),
            'total_players' => $batsmen->count() + $allRounders->count() + $bowlers->count(),
        ];
    }
}
