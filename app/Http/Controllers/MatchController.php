<?php

namespace App\Http\Controllers;

use App\Models\CricketMatch;
use App\Models\Team;
use App\Models\Venue;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    public function index(Request $request)
    {
        $query = CricketMatch::with(['venue', 'firstTeam', 'secondTeam']);
        
        if ($request->has('venue_id') && $request->venue_id) {
            $query->where('venue_id', $request->venue_id);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $matches = $query->orderBy('match_date', 'desc')->get();
        $venues = Venue::all();
        
        return view('matches.index', compact('matches', 'venues'));
    }

    public function results()
    {
        $matches = CricketMatch::with(['venue', 'firstTeam', 'secondTeam'])
            ->where('status', 'completed')
            ->orderBy('match_date', 'desc')
            ->get();
        
        return view('matches.results', compact('matches'));
    }

    public function show(CricketMatch $match)
    {
        $match->load(['venue', 'firstTeam.country', 'secondTeam.country']);
        return view('matches.show', compact('match'));
    }

    public function create()
    {
        $teams = Team::with('country')->get();
        $venues = Venue::all();
        return view('matches.create', compact('teams', 'venues'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'venue_id' => 'required|exists:venues,venue_id',
            'first_team_id' => 'required|exists:teams,team_id|different:second_team_id',
            'second_team_id' => 'required|exists:teams,team_id|different:first_team_id',
            'match_type' => 'required|string|max:50',
            'overs' => 'required|integer|min:1',
            'match_date' => 'nullable|date',
            'status' => 'required|in:scheduled,live,completed,cancelled',
            'first_team_score' => 'nullable|string|max:20',
            'second_team_score' => 'nullable|string|max:20',
            'outcome' => 'nullable|string|max:255',
        ]);

        CricketMatch::create($validated);

        return redirect()->route('matches.index')
            ->with('success', 'Match created successfully.');
    }

    public function edit(CricketMatch $match)
    {
        $teams = Team::with('country')->get();
        $venues = Venue::all();
        return view('matches.edit', compact('match', 'teams', 'venues'));
    }

    public function update(Request $request, CricketMatch $match)
    {
        $validated = $request->validate([
            'venue_id' => 'required|exists:venues,venue_id',
            'first_team_id' => 'required|exists:teams,team_id|different:second_team_id',
            'second_team_id' => 'required|exists:teams,team_id|different:first_team_id',
            'match_type' => 'required|string|max:50',
            'overs' => 'required|integer|min:1',
            'match_date' => 'nullable|date',
            'status' => 'required|in:scheduled,live,completed,cancelled',
            'first_team_score' => 'nullable|string|max:20',
            'second_team_score' => 'nullable|string|max:20',
            'outcome' => 'nullable|string|max:255',
        ]);

        $match->update($validated);

        return redirect()->route('matches.index')
            ->with('success', 'Match updated successfully.');
    }

    public function destroy(CricketMatch $match)
    {
        $match->delete();

        return redirect()->route('matches.index')
            ->with('success', 'Match deleted successfully.');
    }
}
