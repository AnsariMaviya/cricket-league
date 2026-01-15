<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use Illuminate\Http\Request;

class VenueController extends Controller
{
    public function index()
    {
        $venues = Venue::withCount('matches')->get();
        return view('venues.index', compact('venues'));
    }

    public function show(Venue $venue)
    {
        $venue->load(['matches.firstTeam', 'matches.secondTeam']);
        return view('venues.show', compact('venue'));
    }

    public function create()
    {
        return view('venues.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'address' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'capacity' => 'nullable|integer|min:0',
        ]);

        Venue::create($validated);

        return redirect()->route('venues.index')
            ->with('success', 'Venue created successfully.');
    }

    public function edit(Venue $venue)
    {
        return view('venues.edit', compact('venue'));
    }

    public function update(Request $request, Venue $venue)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'address' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'capacity' => 'nullable|integer|min:0',
        ]);

        $venue->update($validated);

        return redirect()->route('venues.index')
            ->with('success', 'Venue updated successfully.');
    }

    public function destroy(Venue $venue)
    {
        $venue->delete();

        return redirect()->route('venues.index')
            ->with('success', 'Venue deleted successfully.');
    }
}
