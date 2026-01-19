<?php

namespace App\Http\Controllers;

use App\Models\CricketMatch;
use App\Models\MatchPrediction;
use App\Models\UserPrediction;
use App\Services\AIMatchPredictionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PredictionController extends Controller
{
    protected $predictionService;

    public function __construct()
    {
        $this->predictionService = new AIMatchPredictionService();
    }

    public function generatePrediction($matchId)
    {
        $match = CricketMatch::with(['firstTeam', 'secondTeam'])->findOrFail($matchId);
        
        if ($match->status !== 'scheduled') {
            return response()->json(['error' => 'Predictions only available for scheduled matches'], 400);
        }

        $prediction = $this->predictionService->predictMatch($match);

        return response()->json([
            'success' => true,
            'prediction' => $prediction,
        ]);
    }

    public function getUserPrediction($matchId)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $prediction = UserPrediction::where('user_id', Auth::id())
            ->where('match_id', $matchId)
            ->with('predictedWinner')
            ->first();

        return response()->json($prediction);
    }

    public function submitPrediction(Request $request, $matchId)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $request->validate([
            'predicted_winner_id' => 'required|exists:teams,team_id',
        ]);

        $match = CricketMatch::findOrFail($matchId);

        if ($match->status !== 'scheduled') {
            return response()->json(['error' => 'Cannot predict on this match'], 400);
        }

        // Store prediction
        $prediction = UserPrediction::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'match_id' => $matchId
            ],
            [
                'predicted_winner_id' => $request->predicted_winner_id,
                'confidence_level' => $request->confidence_level ?? 50
            ]
        );

        return response()->json([
            'success' => true,
            'prediction' => $prediction
        ]);
    }

    public function analyzePlayer($playerId)
    {
        $analysis = $this->predictionService->analyzePlayerPerformance($playerId);

        if (!$analysis) {
            return response()->json(['error' => 'No data available for this player'], 404);
        }

        return response()->json($analysis);
    }

    public function recommendTeam($teamId, Request $request)
    {
        $matchType = $request->input('match_type', 'T20');
        
        $recommendation = $this->predictionService->recommendTeam($teamId, $matchType);

        return response()->json($recommendation);
    }
}
