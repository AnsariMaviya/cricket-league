<?php

namespace App\Http\Controllers;

use App\Services\StatsService;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    protected $statsService;

    public function __construct()
    {
        $this->statsService = new StatsService();
    }

    public function getPlayerStats($playerId, Request $request)
    {
        $format = $request->query('format');
        $stats = $this->statsService->getPlayerStats($playerId, $format);

        return response()->json($stats);
    }

    public function updatePlayerStats($playerId)
    {
        $stats = $this->statsService->updatePlayerCareerStats($playerId);

        return response()->json([
            'success' => true,
            'career_stats' => $stats,
        ]);
    }

    public function getTopBatsmen(Request $request)
    {
        $limit = $request->query('limit', 10);
        $format = $request->query('format');
        
        $topBatsmen = $this->statsService->getTopBatsmen($limit, $format);

        return response()->json($topBatsmen);
    }

    public function getTopBowlers(Request $request)
    {
        $limit = $request->query('limit', 10);
        $format = $request->query('format');
        
        $topBowlers = $this->statsService->getTopBowlers($limit, $format);

        return response()->json($topBowlers);
    }

    public function getMatchStats($matchId)
    {
        $stats = $this->statsService->getMatchDetailedStats($matchId);

        return response()->json($stats);
    }

    public function getTeamHeadToHead($team1Id, $team2Id)
    {
        $stats = $this->statsService->getTeamHeadToHead($team1Id, $team2Id);

        return response()->json($stats);
    }
}
