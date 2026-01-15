<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Country;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $query = Team::with('country')->withCount('players');
        
        if ($request->has('country_id') && $request->country_id) {
            $query->where('country_id', $request->country_id);
        }

        $teams = $query->orderBy('country_id')->get();
        $countries = Country::all();
        
        return view('teams.index', compact('teams', 'countries'));
    }

    public function show(Team $team)
    {
        $team->load(['country', 'players']);
        return view('teams.show', compact('team'));
    }

    public function create()
    {
        $countries = Country::all();
        return view('teams.create', compact('countries'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'team_name' => 'required|string|max:100',
            'country_id' => 'required|exists:countries,country_id',
            'in_match' => 'nullable|string|max:50',
        ]);

        Team::create($validated);

        return redirect()->route('teams.index')
            ->with('success', 'Team created successfully.');
    }

    public function edit(Team $team)
    {
        $countries = Country::all();
        return view('teams.edit', compact('team', 'countries'));
    }

    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'team_name' => 'required|string|max:100',
            'country_id' => 'required|exists:countries,country_id',
            'in_match' => 'nullable|string|max:50',
        ]);

        $team->update($validated);

        return redirect()->route('teams.index')
            ->with('success', 'Team updated successfully.');
    }

    public function destroy(Team $team)
    {
        $team->delete();

        return redirect()->route('teams.index')
            ->with('success', 'Team deleted successfully.');
    }
}
