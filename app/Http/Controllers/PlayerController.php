<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    public function index(Request $request)
    {
        $query = Player::with(['team.country']);
        
        if ($request->has('team_id') && $request->team_id) {
            $query->where('team_id', $request->team_id);
        }

        $players = $query->orderBy('team_id')->get();
        $teams = Team::all();
        
        return view('players.index', compact('players', 'teams'));
    }

    public function show(Player $player)
    {
        $player->load(['team.country']);
        return view('players.show', compact('player'));
    }

    public function create()
    {
        $teams = Team::with('country')->get();
        return view('players.create', compact('teams'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'team_id' => 'required|exists:teams,team_id',
            'dob' => 'nullable|date',
            'role' => 'nullable|string|max:50',
            'batting_style' => 'nullable|string|max:50',
            'bowling_style' => 'nullable|string|max:50',
            'profile_image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('profile_image')) {
            $validated['profile_image'] = $request->file('profile_image')
                ->store('players', 'public');
        }

        Player::create($validated);

        return redirect()->route('players.index')
            ->with('success', 'Player created successfully.');
    }

    public function edit(Player $player)
    {
        $teams = Team::with('country')->get();
        return view('players.edit', compact('player', 'teams'));
    }

    public function update(Request $request, Player $player)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'team_id' => 'required|exists:teams,team_id',
            'dob' => 'nullable|date',
            'role' => 'nullable|string|max:50',
            'batting_style' => 'nullable|string|max:50',
            'bowling_style' => 'nullable|string|max:50',
            'profile_image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('profile_image')) {
            $validated['profile_image'] = $request->file('profile_image')
                ->store('players', 'public');
        }

        $player->update($validated);

        return redirect()->route('players.index')
            ->with('success', 'Player updated successfully.');
    }

    public function destroy(Player $player)
    {
        $player->delete();

        return redirect()->route('players.index')
            ->with('success', 'Player deleted successfully.');
    }
}
