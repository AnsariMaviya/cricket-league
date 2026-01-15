@extends('layouts.app')

@section('title', 'Edit Match')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-red-600 text-white px-6 py-4">
            <h1 class="text-xl font-semibold">Edit Match</h1>
        </div>
        <form action="{{ route('matches.update', $match) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            <div class="grid md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label for="first_team_id" class="block text-sm font-medium text-gray-700 mb-2">Team 1</label>
                    <select name="first_team_id" id="first_team_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                        @foreach($teams as $team)
                        <option value="{{ $team->team_id }}" {{ old('first_team_id', $match->first_team_id) == $team->team_id ? 'selected' : '' }}>
                            {{ $team->team_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label for="second_team_id" class="block text-sm font-medium text-gray-700 mb-2">Team 2</label>
                    <select name="second_team_id" id="second_team_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                        @foreach($teams as $team)
                        <option value="{{ $team->team_id }}" {{ old('second_team_id', $match->second_team_id) == $team->team_id ? 'selected' : '' }}>
                            {{ $team->team_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label for="venue_id" class="block text-sm font-medium text-gray-700 mb-2">Venue</label>
                    <select name="venue_id" id="venue_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                        @foreach($venues as $venue)
                        <option value="{{ $venue->venue_id }}" {{ old('venue_id', $match->venue_id) == $venue->venue_id ? 'selected' : '' }}>
                            {{ $venue->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label for="match_date" class="block text-sm font-medium text-gray-700 mb-2">Match Date</label>
                    <input type="date" name="match_date" id="match_date" value="{{ old('match_date', $match->match_date?->format('Y-m-d')) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                <div class="mb-4">
                    <label for="match_type" class="block text-sm font-medium text-gray-700 mb-2">Match Type</label>
                    <select name="match_type" id="match_type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="T20" {{ old('match_type', $match->match_type) == 'T20' ? 'selected' : '' }}>T20</option>
                        <option value="ODI" {{ old('match_type', $match->match_type) == 'ODI' ? 'selected' : '' }}>ODI</option>
                        <option value="Test" {{ old('match_type', $match->match_type) == 'Test' ? 'selected' : '' }}>Test</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="overs" class="block text-sm font-medium text-gray-700 mb-2">Overs</label>
                    <input type="number" name="overs" id="overs" value="{{ old('overs', $match->overs) }}" required min="1"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                <div class="mb-4">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" id="status" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="scheduled" {{ old('status', $match->status) == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="live" {{ old('status', $match->status) == 'live' ? 'selected' : '' }}>Live</option>
                        <option value="completed" {{ old('status', $match->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ old('status', $match->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="first_team_score" class="block text-sm font-medium text-gray-700 mb-2">Team 1 Score</label>
                    <input type="text" name="first_team_score" id="first_team_score" value="{{ old('first_team_score', $match->first_team_score) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                <div class="mb-4">
                    <label for="second_team_score" class="block text-sm font-medium text-gray-700 mb-2">Team 2 Score</label>
                    <input type="text" name="second_team_score" id="second_team_score" value="{{ old('second_team_score', $match->second_team_score) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
            </div>
            <div class="mb-6">
                <label for="outcome" class="block text-sm font-medium text-gray-700 mb-2">Outcome</label>
                <input type="text" name="outcome" id="outcome" value="{{ old('outcome', $match->outcome) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div class="flex space-x-4">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-6 rounded-lg transition">
                    Update Match
                </button>
                <a href="{{ route('matches.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-6 rounded-lg transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
