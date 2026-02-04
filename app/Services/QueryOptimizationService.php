<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class QueryOptimizationService
{
    /**
     * Log slow queries
     */
    public static function logSlowQuery($query, $time, $threshold = 1000)
    {
        if ($time > $threshold) {
            Log::warning('Slow query detected', [
                'query' => $query,
                'execution_time' => $time,
                'threshold' => $threshold,
                'timestamp' => now()->toDateTimeString(),
            ]);
        }
    }

    /**
     * Get optimized match data with relationships
     */
    public static function getOptimizedMatch($matchId)
    {
        return DB::table('matches as m')
            ->select([
                'm.match_id',
                'm.venue_id',
                'm.first_team_id',
                'm.second_team_id',
                'm.tournament_id',
                'm.stage_id',
                'm.match_number',
                'm.match_type',
                'm.overs',
                'm.first_team_score',
                'm.second_team_score',
                'm.outcome',
                'm.match_date',
                'm.status',
                'm.current_innings',
                'm.current_over',
                'm.current_batsman_1',
                'm.current_batsman_2',
                'm.current_bowler',
                'm.target_score',
                'm.toss_winner',
                'm.toss_decision',
                'm.viewers_count',
                'm.started_at',
                'm.ended_at',
                'v.name as venue_name',
                'v.city as venue_city',
                'v.capacity as venue_capacity',
                't1.team_name as first_team_name',
                't1.logo as first_team_logo',
                't2.team_name as second_team_name',
                't2.logo as second_team_logo',
                'tr.name as tournament_name',
                'ts.name as stage_name',
            ])
            ->leftJoin('venues as v', 'm.venue_id', '=', 'v.venue_id')
            ->leftJoin('teams as t1', 'm.first_team_id', '=', 't1.team_id')
            ->leftJoin('teams as t2', 'm.second_team_id', '=', 't2.team_id')
            ->leftJoin('tournaments as tr', 'm.tournament_id', '=', 'tr.tournament_id')
            ->leftJoin('tournament_stages as ts', 'm.stage_id', '=', 'ts.stage_id')
            ->where('m.match_id', $matchId)
            ->first();
    }

    /**
     * Get optimized matches list with relationships
     */
    public static function getOptimizedMatches($limit = 50, $offset = 0, $status = null)
    {
        $query = DB::table('matches as m')
            ->select([
                'm.match_id',
                'm.venue_id',
                'm.first_team_id',
                'm.second_team_id',
                'm.match_date',
                'm.status',
                'm.first_team_score',
                'm.second_team_score',
                'm.current_over',
                'm.viewers_count',
                'v.name as venue_name',
                't1.team_name as first_team_name',
                't1.logo as first_team_logo',
                't2.team_name as second_team_name',
                't2.logo as second_team_logo',
            ])
            ->leftJoin('venues as v', 'm.venue_id', '=', 'v.venue_id')
            ->leftJoin('teams as t1', 'm.first_team_id', '=', 't1.team_id')
            ->leftJoin('teams as t2', 'm.second_team_id', '=', 't2.team_id')
            ->orderBy('m.match_date', 'desc')
            ->limit($limit)
            ->offset($offset);

        if ($status) {
            $query->where('m.status', $status);
        }

        return $query->get();
    }

    /**
     * Get optimized player statistics with relationships
     */
    public static function getOptimizedPlayerStats($playerId, $limit = 50)
    {
        return DB::table('player_match_stats as pms')
            ->select([
                'pms.*',
                'm.match_date',
                'm.status',
                't1.team_name as opponent_team',
                'v.name as venue_name',
            ])
            ->leftJoin('matches as m', 'pms.match_id', '=', 'm.match_id')
            ->leftJoin('teams as t1', function($join) use ($playerId) {
                $join->on('m.first_team_id', '=', 't1.team_id')
                     ->orOn('m.second_team_id', '=', 't1.team_id');
            })
            ->leftJoin('venues as v', 'm.venue_id', '=', 'v.venue_id')
            ->where('pms.player_id', $playerId)
            ->where('t1.team_id', '!=', function($query) use ($playerId) {
                $query->select('team_id')
                      ->from('players')
                      ->where('player_id', $playerId);
            })
            ->orderBy('m.match_date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get optimized team statistics
     */
    public static function getOptimizedTeamStats($teamId, $limit = 50)
    {
        return DB::table('matches as m')
            ->select([
                'm.match_id',
                'm.match_date',
                'm.status',
                'm.first_team_score',
                'm.second_team_score',
                'm.outcome',
                'v.name as venue_name',
                'opponent.team_name as opponent_team',
                'opponent.logo as opponent_logo',
                'm.first_team_id',
                'm.second_team_id',
            ])
            ->leftJoin('venues as v', 'm.venue_id', '=', 'v.venue_id')
            ->leftJoin('teams as opponent', function($join) use ($teamId) {
                $join->on('m.first_team_id', '=', 'opponent.team_id')
                     ->where('m.second_team_id', '=', $teamId)
                     ->orOn('m.second_team_id', '=', 'opponent.team_id')
                     ->where('m.first_team_id', '=', $teamId);
            })
            ->where(function($query) use ($teamId) {
                $query->where('m.first_team_id', $teamId)
                      ->orWhere('m.second_team_id', $teamId);
            })
            ->where('m.status', 'completed')
            ->orderBy('m.match_date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get optimized leaderboard with user relationships
     */
    public static function getOptimizedLeaderboard($type = 'points', $limit = 100)
    {
        $query = DB::table('users as u')
            ->select([
                'u.id',
                'u.name',
                'u.email',
                'up.points',
                'up.level',
                'up.created_at as last_updated',
            ])
            ->join('user_points as up', 'u.id', '=', 'up.user_id')
            ->orderBy('up.points', 'desc')
            ->limit($limit);

        if ($type === 'weekly') {
            $query->where('up.updated_at', '>=', now()->subDays(7));
        } elseif ($type === 'monthly') {
            $query->where('up.updated_at', '>=', now()->subDays(30));
        }

        return $query->get();
    }

    /**
     * Get optimized commentary with ball details
     */
    public static function getOptimizedCommentary($matchId, $limit = 100)
    {
        return DB::table('match_commentary as mc')
            ->select([
                'mc.commentary_id',
                'mc.match_id',
                'mc.over_number',
                'mc.ball_number',
                'mc.commentary_text',
                'mc.commentary_type',
                'mc.created_at',
                'bbb.runs_scored',
                'bbb.is_wicket',
                'bbb.wicket_type',
                'p1.player_name as batsman_name',
                'p2.player_name as bowler_name',
            ])
            ->leftJoin('ball_by_ball as bbb', function($join) {
                $join->on('mc.match_id', '=', 'bbb.match_id')
                     ->on('mc.over_number', '=', 'bbb.over_number')
                     ->on('mc.ball_number', '=', 'bbb.ball_number');
            })
            ->leftJoin('players as p1', 'bbb.batsman_id', '=', 'p1.player_id')
            ->leftJoin('players as p2', 'bbb.bowler_id', '=', 'p2.player_id')
            ->where('mc.match_id', $matchId)
            ->orderBy('mc.over_number', 'desc')
            ->orderBy('mc.ball_number', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get optimized ball-by-ball data with relationships
     */
    public static function getOptimizedBallByBall($matchId, $overNumber = null)
    {
        $query = DB::table('ball_by_ball as bbb')
            ->select([
                'bbb.*',
                'p1.player_name as batsman_name',
                'p2.player_name as bowler_name',
                'p3.player_name as non_striker_name',
            ])
            ->leftJoin('players as p1', 'bbb.batsman_id', '=', 'p1.player_id')
            ->leftJoin('players as p2', 'bbb.bowler_id', '=', 'p2.player_id')
            ->leftJoin('players as p3', 'bbb.non_striker_id', '=', 'p3.player_id')
            ->where('bbb.match_id', $matchId);

        if ($overNumber) {
            $query->where('bbb.over_number', $overNumber);
        }

        return $query->orderBy('bbb.over_number', 'asc')
                    ->orderBy('bbb.ball_number', 'asc')
                    ->get();
    }

    /**
     * Get optimized partnerships data
     */
    public static function getOptimizedPartnerships($matchId)
    {
        return DB::table('partnerships as p')
            ->select([
                'p.*',
                'p1.player_name as batsman1_name',
                'p2.player_name as batsman2_name',
            ])
            ->leftJoin('players as p1', 'p.batsman1_id', '=', 'p1.player_id')
            ->leftJoin('players as p2', 'p.batsman2_id', '=', 'p2.player_id')
            ->where('p.match_id', $matchId)
            ->orderBy('p.start_over', 'asc')
            ->orderBy('p.start_ball', 'asc')
            ->get();
    }

    /**
     * Get optimized fall of wickets data
     */
    public static function getOptimizedFallOfWickets($matchId)
    {
        return DB::table('fall_of_wickets as fow')
            ->select([
                'fow.*',
                'p.player_name as batsman_name',
                'p2.player_name as bowler_name',
            ])
            ->leftJoin('players as p', 'fow.batsman_id', '=', 'p.player_id')
            ->leftJoin('players as p2', 'fow.bowler_id', '=', 'p2.player_id')
            ->where('fow.match_id', $matchId)
            ->orderBy('fow.over_number', 'asc')
            ->orderBy('fow.ball_number', 'asc')
            ->get();
    }

    /**
     * Get optimized tournament standings
     */
    public static function getOptimizedTournamentStandings($tournamentId)
    {
        return DB::table('tournament_teams as tt')
            ->select([
                'tt.*',
                't.team_name',
                't.logo',
                'c.name as country_name',
                'c.flag as country_flag',
                DB::raw('(SELECT COUNT(*) FROM matches WHERE tournament_id = ' . $tournamentId . ' AND (first_team_id = tt.team_id OR second_team_id = tt.team_id) AND status = "completed") as matches_played'),
                DB::raw('(SELECT COUNT(*) FROM matches WHERE tournament_id = ' . $tournamentId . ' AND ((first_team_id = tt.team_id AND first_team_score > second_team_score) OR (second_team_id = tt.team_id AND second_team_score > first_team_score)) AND status = "completed") as matches_won'),
                DB::raw('(SELECT COUNT(*) FROM matches WHERE tournament_id = ' . $tournamentId . ' AND ((first_team_id = tt.team_id AND first_team_score < second_team_score) OR (second_team_id = tt.team_id AND second_team_score < first_team_score)) AND status = "completed") as matches_lost'),
            ])
            ->leftJoin('teams as t', 'tt.team_id', '=', 't.team_id')
            ->leftJoin('countries as c', 't.country_id', '=', 'c.country_id')
            ->where('tt.tournament_id', $tournamentId)
            ->orderBy('tt.points', 'desc')
            ->orderBy('tt.net_run_rate', 'desc')
            ->get();
    }

    /**
     * Batch insert for performance
     */
    public static function batchInsert($table, $data, $batchSize = 1000)
    {
        $chunks = array_chunk($data, $batchSize);
        
        foreach ($chunks as $chunk) {
            DB::table($table)->insert($chunk);
        }
    }

    /**
     * Get query execution time
     */
    public static function getQueryTime($callback)
    {
        $startTime = microtime(true);
        
        $result = $callback();
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        
        return [
            'result' => $result,
            'execution_time' => $executionTime,
        ];
    }

    /**
     * Analyze query performance
     */
    public static function analyzeQueryPerformance($query)
    {
        $startTime = microtime(true);
        
        $result = $query->get();
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;
        
        // Log if slow
        self::logSlowQuery($query->toSql(), $executionTime);
        
        return [
            'data' => $result,
            'execution_time' => $executionTime,
            'query' => $query->toSql(),
            'bindings' => $query->getBindings(),
        ];
    }
}
