<?php

namespace App\Services;

use App\Models\Player;
use App\Models\PlayerCareerStats;
use App\Models\PlayerMatchStats;
use App\Models\CricketMatch;
use App\Models\Team;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class StatsService
{
    public function updatePlayerCareerStats($playerId)
    {
        $player = Player::findOrFail($playerId);
        
        // Get all match stats for this player
        $matchStats = PlayerMatchStats::where('player_id', $playerId)->get();
        
        if ($matchStats->isEmpty()) {
            return null;
        }

        $careerStats = PlayerCareerStats::firstOrCreate(
            ['player_id' => $playerId],
            []
        );

        // Batting stats
        $battingStats = $matchStats->where('balls_faced', '>', 0);
        $careerStats->total_matches = $matchStats->pluck('match_id')->unique()->count();
        $careerStats->total_innings_batted = $battingStats->count();
        $careerStats->total_runs = $matchStats->sum('runs_scored');
        $careerStats->total_balls_faced = $matchStats->sum('balls_faced');
        $careerStats->total_fours = $matchStats->sum('fours');
        $careerStats->total_sixes = $matchStats->sum('sixes');
        $careerStats->highest_score = $matchStats->max('runs_scored');
        
        // Calculate batting average
        $dismissals = $battingStats->count() - $matchStats->where('not_out', true)->count();
        $careerStats->batting_average = $dismissals > 0 
            ? round($careerStats->total_runs / $dismissals, 2) 
            : 0;
        
        // Calculate strike rate
        $careerStats->batting_strike_rate = $careerStats->total_balls_faced > 0 
            ? round(($careerStats->total_runs / $careerStats->total_balls_faced) * 100, 2) 
            : 0;
        
        // Milestones
        $careerStats->fifties = $matchStats->where('runs_scored', '>=', 50)->where('runs_scored', '<', 100)->count();
        $careerStats->centuries = $matchStats->where('runs_scored', '>=', 100)->count();
        $careerStats->ducks = $matchStats->where('runs_scored', 0)->where('balls_faced', '>', 0)->count();
        $careerStats->not_outs = $matchStats->where('not_out', true)->count();

        // Bowling stats
        $bowlingStats = $matchStats->where('balls_bowled', '>', 0);
        $careerStats->total_innings_bowled = $bowlingStats->count();
        $careerStats->total_wickets = $matchStats->sum('wickets_taken');
        $careerStats->total_balls_bowled = $matchStats->sum('balls_bowled');
        $careerStats->total_runs_conceded = $matchStats->sum('runs_conceded');
        $careerStats->total_maidens = $matchStats->sum('maidens');
        
        // Calculate bowling average
        $careerStats->bowling_average = $careerStats->total_wickets > 0 
            ? round($careerStats->total_runs_conceded / $careerStats->total_wickets, 2) 
            : 0;
        
        // Calculate economy rate
        $oversBowled = $careerStats->total_balls_bowled / 6;
        $careerStats->bowling_economy = $oversBowled > 0 
            ? round($careerStats->total_runs_conceded / $oversBowled, 2) 
            : 0;
        
        // Calculate bowling strike rate
        $careerStats->bowling_strike_rate = $careerStats->total_wickets > 0 
            ? round($careerStats->total_balls_bowled / $careerStats->total_wickets, 2) 
            : 0;
        
        // Best bowling figures
        $bestBowling = $matchStats->where('wickets_taken', '>', 0)
            ->sortByDesc('wickets_taken')
            ->sortBy('runs_conceded')
            ->first();
        if ($bestBowling) {
            $careerStats->best_bowling_figures = $bestBowling->wickets_taken . '/' . $bestBowling->runs_conceded;
        }
        
        // Bowling milestones
        $careerStats->five_wicket_hauls = $matchStats->where('wickets_taken', '>=', 5)->count();
        $careerStats->ten_wicket_hauls = 0; // Would need match-level tracking for test matches

        // Fielding stats
        $careerStats->total_catches = $matchStats->sum('catches');
        $careerStats->total_run_outs = $matchStats->sum('run_outs');
        $careerStats->total_stumpings = $matchStats->sum('stumpings');

        $careerStats->save();

        return $careerStats;
    }

    public function getPlayerStats($playerId, $format = null)
    {
        $player = Player::with('careerStats')->findOrFail($playerId);
        
        $stats = [
            'player' => $player,
            'career_stats' => $player->careerStats,
            'recent_form' => $this->getRecentForm($playerId, 10),
        ];

        if ($format) {
            $stats['format_stats'] = $this->getFormatSpecificStats($playerId, $format);
        }

        return $stats;
    }

    protected function getRecentForm($playerId, $limit = 10)
    {
        return PlayerMatchStats::where('player_id', $playerId)
            ->with('match')
            ->orderByDesc('created_at')
            ->take($limit)
            ->get();
    }

    protected function getFormatSpecificStats($playerId, $format)
    {
        return DB::table('player_match_stats')
            ->join('matches', 'player_match_stats.match_id', '=', 'matches.match_id')
            ->where('player_match_stats.player_id', $playerId)
            ->where('matches.match_type', $format)
            ->select(
                DB::raw('COUNT(DISTINCT player_match_stats.match_id) as matches'),
                DB::raw('SUM(player_match_stats.runs_scored) as runs'),
                DB::raw('SUM(player_match_stats.wickets_taken) as wickets'),
                DB::raw('AVG(player_match_stats.strike_rate) as avg_strike_rate')
            )
            ->first();
    }

    public function getTopBatsmen($limit = 10, $format = null)
    {
        $query = PlayerCareerStats::with('player')
            ->orderByDesc('total_runs');

        if ($format) {
            // Would need to filter by format if tracking format-specific career stats
        }

        return $query->take($limit)->get();
    }

    public function getTopBowlers($limit = 10, $format = null)
    {
        $query = PlayerCareerStats::with('player')
            ->orderByDesc('total_wickets');

        if ($format) {
            // Would need to filter by format
        }

        return $query->take($limit)->get();
    }

    public function getMatchDetailedStats($matchId)
    {
        $match = CricketMatch::with([
            'firstTeam',
            'secondTeam',
            'venue',
            'innings',
            'playerStats.player',
            'balls',
            'commentary'
        ])->findOrFail($matchId);

        // Get all player stats (innings tracking happens at match level, not player_match_stats level)
        $allBatting = PlayerMatchStats::where('match_id', $matchId)
            ->where('balls_faced', '>', 0)
            ->with('player')
            ->get();

        $allBowling = PlayerMatchStats::where('match_id', $matchId)
            ->where('balls_bowled', '>', 0)
            ->with('player')
            ->get();

        // Split by team for first and second innings
        $innings1Batting = $allBatting->filter(function($stat) use ($match) {
            return $stat->player->team_id == $match->first_team_id;
        })->values();

        $innings2Batting = $allBatting->filter(function($stat) use ($match) {
            return $stat->player->team_id == $match->second_team_id;
        })->values();

        $innings1Bowling = $allBowling->filter(function($stat) use ($match) {
            return $stat->player->team_id == $match->second_team_id; // Bowlers from opposite team
        })->values();

        $innings2Bowling = $allBowling->filter(function($stat) use ($match) {
            return $stat->player->team_id == $match->first_team_id; // Bowlers from opposite team
        })->values();

        // Calculate extras from all balls
        $totalExtras = DB::table('ball_by_ball')
            ->where('match_id', $matchId)
            ->sum('extra_runs');

        // For now, estimate extras split (better to track in match innings table)
        $innings1Extras = (int)($totalExtras / 2);
        $innings2Extras = $totalExtras - $innings1Extras;

        return [
            'match' => $match,
            'innings1_batting' => $innings1Batting,
            'innings1_bowling' => $innings1Bowling,
            'innings2_batting' => $innings2Batting,
            'innings2_bowling' => $innings2Bowling,
            'innings1_extras' => $innings1Extras,
            'innings2_extras' => $innings2Extras,
            'partnerships' => $this->getPartnerships($matchId),
            'fall_of_wickets' => $this->getFallOfWickets($matchId),
            'over_breakdown' => $this->getOverBreakdown($matchId),
        ];
    }

    protected function getPartnerships($matchId)
    {
        // This would require tracking partnerships in real-time during simulation
        // For now, return placeholder
        return [];
    }

    protected function getFallOfWickets($matchId)
    {
        return DB::table('ball_by_ball')
            ->where('match_id', $matchId)
            ->where('is_wicket', true)
            ->join('players as batsman', 'ball_by_ball.batsman_id', '=', 'batsman.player_id')
            ->select(
                'ball_by_ball.over_number',
                'ball_by_ball.wicket_type',
                'batsman.name as batsman_name',
                'ball_by_ball.runs_scored'
            )
            ->orderBy('ball_by_ball.ball_id')
            ->get();
    }

    protected function getOverBreakdown($matchId)
    {
        return DB::table('ball_by_ball')
            ->where('match_id', $matchId)
            ->select(
                DB::raw('FLOOR(over_number) as over_num'),
                DB::raw('SUM(runs_scored + extra_runs) as runs'),
                DB::raw('SUM(CASE WHEN is_wicket THEN 1 ELSE 0 END) as wickets')
            )
            ->groupBy(DB::raw('FLOOR(over_number)'))
            ->orderBy('over_num')
            ->get();
    }

    public function getTeamHeadToHead($team1Id, $team2Id)
    {
        $matches = CricketMatch::where(function ($query) use ($team1Id, $team2Id) {
            $query->where('first_team_id', $team1Id)->where('second_team_id', $team2Id);
        })->orWhere(function ($query) use ($team1Id, $team2Id) {
            $query->where('first_team_id', $team2Id)->where('second_team_id', $team1Id);
        })->where('status', 'completed')->get();

        $team1Wins = 0;
        $team2Wins = 0;
        $ties = 0;

        foreach ($matches as $match) {
            if (strpos($match->outcome, Team::find($team1Id)->team_name) !== false) {
                $team1Wins++;
            } elseif (strpos($match->outcome, Team::find($team2Id)->team_name) !== false) {
                $team2Wins++;
            } else {
                $ties++;
            }
        }

        return [
            'total_matches' => $matches->count(),
            'team1_wins' => $team1Wins,
            'team2_wins' => $team2Wins,
            'ties' => $ties,
            'matches' => $matches,
        ];
    }
}
