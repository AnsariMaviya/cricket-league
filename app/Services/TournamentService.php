<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\TournamentTeam;
use App\Models\TournamentStage;
use App\Models\CricketMatch;
use App\Models\Team;
use Illuminate\Support\Facades\DB;

class TournamentService
{
    public function createTournament(array $data)
    {
        return DB::transaction(function () use ($data) {
            $tournament = Tournament::create([
                'name' => $data['name'],
                'tournament_type' => $data['tournament_type'] ?? 'league',
                'format' => $data['format'] ?? 'T20',
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? null,
                'max_teams' => $data['max_teams'] ?? 8,
                'prize_pool' => $data['prize_pool'] ?? null,
                'description' => $data['description'] ?? null,
                'logo_url' => $data['logo_url'] ?? null,
                'rules' => $data['rules'] ?? null,
            ]);

            // Create default stage if league type
            if ($tournament->tournament_type === 'league') {
                TournamentStage::create([
                    'tournament_id' => $tournament->tournament_id,
                    'stage_name' => 'League Stage',
                    'stage_order' => 1,
                    'stage_format' => 'league',
                    'teams_qualify' => 4,
                ]);
            }

            return $tournament;
        });
    }

    public function addTeamToTournament($tournamentId, $teamId, $groupName = null)
    {
        $tournament = Tournament::findOrFail($tournamentId);
        
        if ($tournament->current_teams >= $tournament->max_teams) {
            throw new \Exception('Tournament is full');
        }

        $tournamentTeam = TournamentTeam::create([
            'tournament_id' => $tournamentId,
            'team_id' => $teamId,
            'group_name' => $groupName,
        ]);

        $tournament->increment('current_teams');

        return $tournamentTeam;
    }

    public function removeTeamFromTournament($tournamentId, $teamId)
    {
        $tournament = Tournament::findOrFail($tournamentId);
        
        TournamentTeam::where('tournament_id', $tournamentId)
            ->where('team_id', $teamId)
            ->delete();

        $tournament->decrement('current_teams');

        return true;
    }

    public function getPointsTable($tournamentId, $groupName = null)
    {
        $query = TournamentTeam::where('tournament_id', $tournamentId)
            ->with('team');

        if ($groupName) {
            $query->where('group_name', $groupName);
        }

        return $query->orderByDesc('points')
            ->orderByDesc('net_run_rate')
            ->get();
    }

    public function updateStandings($matchId)
    {
        $match = CricketMatch::with(['firstTeam', 'secondTeam'])->findOrFail($matchId);
        
        if (!$match->tournament_id || $match->status !== 'completed') {
            return;
        }

        $tournamentId = $match->tournament_id;
        
        // Determine winner and points
        $winnerTeamId = null;
        $tie = false;
        
        if ($match->outcome && strpos($match->outcome, 'won by') !== false) {
            if (strpos($match->outcome, $match->firstTeam->team_name) !== false) {
                $winnerTeamId = $match->first_team_id;
            } elseif (strpos($match->outcome, $match->secondTeam->team_name) !== false) {
                $winnerTeamId = $match->second_team_id;
            }
        } elseif ($match->outcome && strpos($match->outcome, 'tie') !== false) {
            $tie = true;
        }

        // Update both teams
        $this->updateTeamStats($tournamentId, $match->first_team_id, $match, $winnerTeamId, $tie);
        $this->updateTeamStats($tournamentId, $match->second_team_id, $match, $winnerTeamId, $tie);

        // Recalculate positions
        $this->recalculatePositions($tournamentId);
    }

    protected function updateTeamStats($tournamentId, $teamId, $match, $winnerTeamId, $tie)
    {
        $tournamentTeam = TournamentTeam::where('tournament_id', $tournamentId)
            ->where('team_id', $teamId)
            ->first();

        if (!$tournamentTeam) {
            return;
        }

        $tournamentTeam->matches_played += 1;

        if ($winnerTeamId === $teamId) {
            $tournamentTeam->wins += 1;
            $tournamentTeam->points += 2;
        } elseif ($tie) {
            $tournamentTeam->ties += 1;
            $tournamentTeam->points += 1;
        } else {
            $tournamentTeam->losses += 1;
        }

        // Update NRR calculation data
        if ($teamId === $match->first_team_id) {
            $runsScored = $this->parseScore($match->first_team_score);
            $runsConceded = $this->parseScore($match->second_team_score);
        } else {
            $runsScored = $this->parseScore($match->second_team_score);
            $runsConceded = $this->parseScore($match->first_team_score);
        }

        $tournamentTeam->runs_scored += $runsScored;
        $tournamentTeam->runs_conceded += $runsConceded;
        $tournamentTeam->overs_faced += $match->overs;
        $tournamentTeam->overs_bowled += $match->overs;

        // Calculate NRR
        $runRate = $tournamentTeam->overs_faced > 0 
            ? $tournamentTeam->runs_scored / $tournamentTeam->overs_faced 
            : 0;
        $concededRate = $tournamentTeam->overs_bowled > 0 
            ? $tournamentTeam->runs_conceded / $tournamentTeam->overs_bowled 
            : 0;
        $tournamentTeam->net_run_rate = $runRate - $concededRate;

        $tournamentTeam->save();
    }

    protected function parseScore($scoreString)
    {
        // Extract runs from score string like "150/7" or "150 all out"
        if (preg_match('/(\d+)/', $scoreString, $matches)) {
            return (int) $matches[1];
        }
        return 0;
    }

    protected function recalculatePositions($tournamentId)
    {
        $teams = TournamentTeam::where('tournament_id', $tournamentId)
            ->orderByDesc('points')
            ->orderByDesc('net_run_rate')
            ->get();

        $position = 1;
        foreach ($teams as $team) {
            $team->position = $position++;
            $team->save();
        }
    }

    public function generateFixtures($tournamentId, $venueIds = [])
    {
        $tournament = Tournament::with('teams')->findOrFail($tournamentId);
        $teams = $tournament->teams;

        if ($teams->count() < 2) {
            throw new \Exception('Need at least 2 teams to generate fixtures');
        }

        $matches = [];
        $matchNumber = 1;

        if ($tournament->tournament_type === 'league' || $tournament->tournament_type === 'round_robin') {
            // Round-robin: each team plays every other team
            $teamIds = $teams->pluck('team_id')->toArray();
            
            for ($i = 0; $i < count($teamIds); $i++) {
                for ($j = $i + 1; $j < count($teamIds); $j++) {
                    $venueId = $venueIds[array_rand($venueIds)] ?? null;
                    
                    $matches[] = [
                        'tournament_id' => $tournamentId,
                        'match_number' => $matchNumber++,
                        'first_team_id' => $teamIds[$i],
                        'second_team_id' => $teamIds[$j],
                        'venue_id' => $venueId,
                        'match_type' => $tournament->format,
                        'overs' => $this->getOversForFormat($tournament->format),
                        'status' => 'scheduled',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        // Insert matches in bulk
        CricketMatch::insert($matches);

        return $matches;
    }

    protected function getOversForFormat($format)
    {
        return match($format) {
            'T20' => 20,
            'ODI' => 50,
            'Test' => 90, // Test match simplified
            default => 20,
        };
    }

    public function getTournamentStats($tournamentId)
    {
        $tournament = Tournament::with(['matches', 'tournamentTeams.team'])->findOrFail($tournamentId);

        // Get top scorers
        $topBatsmen = DB::table('player_match_stats')
            ->join('players', 'player_match_stats.player_id', '=', 'players.player_id')
            ->join('matches', 'player_match_stats.match_id', '=', 'matches.match_id')
            ->where('matches.tournament_id', $tournamentId)
            ->select(
                'players.player_id',
                'players.name',
                DB::raw('SUM(player_match_stats.runs_scored) as total_runs'),
                DB::raw('COUNT(DISTINCT player_match_stats.match_id) as matches'),
                DB::raw('MAX(player_match_stats.runs_scored) as highest_score')
            )
            ->groupBy('players.player_id', 'players.name')
            ->orderByDesc('total_runs')
            ->take(10)
            ->get();

        // Get top wicket-takers
        $topBowlers = DB::table('player_match_stats')
            ->join('players', 'player_match_stats.player_id', '=', 'players.player_id')
            ->join('matches', 'player_match_stats.match_id', '=', 'matches.match_id')
            ->where('matches.tournament_id', $tournamentId)
            ->select(
                'players.player_id',
                'players.name',
                DB::raw('SUM(player_match_stats.wickets_taken) as total_wickets'),
                DB::raw('COUNT(DISTINCT player_match_stats.match_id) as matches'),
                DB::raw('SUM(player_match_stats.runs_conceded) as runs_conceded')
            )
            ->groupBy('players.player_id', 'players.name')
            ->orderByDesc('total_wickets')
            ->take(10)
            ->get();

        return [
            'tournament' => $tournament,
            'top_batsmen' => $topBatsmen,
            'top_bowlers' => $topBowlers,
            'points_table' => $this->getPointsTable($tournamentId),
        ];
    }
}
