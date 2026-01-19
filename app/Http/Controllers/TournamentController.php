<?php

namespace App\Http\Controllers;

use App\Services\TournamentService;
use App\Models\Tournament;
use Illuminate\Http\Request;

class TournamentController extends Controller
{
    protected $tournamentService;

    public function __construct()
    {
        $this->tournamentService = new TournamentService();
    }

    public function index()
    {
        $tournaments = Tournament::with('teams')
            ->orderByDesc('start_date')
            ->get();

        return response()->json($tournaments);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tournament_type' => 'required|in:league,knockout,round_robin,hybrid',
            'format' => 'required|in:T20,ODI,Test',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'max_teams' => 'integer|min:2|max:32',
            'prize_pool' => 'nullable|numeric',
            'description' => 'nullable|string',
            'logo_url' => 'nullable|url',
        ]);

        $tournament = $this->tournamentService->createTournament($validated);

        return response()->json([
            'success' => true,
            'tournament' => $tournament->load('stages'),
        ], 201);
    }

    public function show($id)
    {
        $tournament = Tournament::with(['teams', 'stages', 'matches'])
            ->findOrFail($id);

        return response()->json($tournament);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'status' => 'in:upcoming,ongoing,completed',
            'end_date' => 'nullable|date',
            'description' => 'nullable|string',
            'prize_pool' => 'nullable|numeric',
        ]);

        $tournament = Tournament::findOrFail($id);
        $tournament->update($validated);

        return response()->json([
            'success' => true,
            'tournament' => $tournament,
        ]);
    }

    public function destroy($id)
    {
        $tournament = Tournament::findOrFail($id);
        $tournament->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tournament deleted successfully',
        ]);
    }

    public function addTeam(Request $request, $id)
    {
        $validated = $request->validate([
            'team_id' => 'required|exists:teams,team_id',
            'group_name' => 'nullable|string|max:10',
        ]);

        try {
            $tournamentTeam = $this->tournamentService->addTeamToTournament(
                $id,
                $validated['team_id'],
                $validated['group_name'] ?? null
            );

            return response()->json([
                'success' => true,
                'tournament_team' => $tournamentTeam->load('team'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function removeTeam($id, $teamId)
    {
        $this->tournamentService->removeTeamFromTournament($id, $teamId);

        return response()->json([
            'success' => true,
            'message' => 'Team removed from tournament',
        ]);
    }

    public function getPointsTable($id, Request $request)
    {
        $groupName = $request->query('group');
        $pointsTable = $this->tournamentService->getPointsTable($id, $groupName);

        return response()->json($pointsTable);
    }

    public function generateFixtures(Request $request, $id)
    {
        $validated = $request->validate([
            'venue_ids' => 'array',
            'venue_ids.*' => 'exists:venues,venue_id',
        ]);

        try {
            $matches = $this->tournamentService->generateFixtures(
                $id,
                $validated['venue_ids'] ?? []
            );

            return response()->json([
                'success' => true,
                'message' => count($matches) . ' fixtures generated',
                'fixtures_count' => count($matches),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function getStats($id)
    {
        $stats = $this->tournamentService->getTournamentStats($id);

        return response()->json($stats);
    }

    public function updateStandings($id)
    {
        // This will recalculate standings for all completed matches
        $tournament = Tournament::with('matches')->findOrFail($id);
        
        foreach ($tournament->matches()->where('status', 'completed')->get() as $match) {
            $this->tournamentService->updateStandings($match->match_id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Standings updated',
        ]);
    }
}
