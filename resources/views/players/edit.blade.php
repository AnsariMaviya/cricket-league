@extends('layouts.app')

@section('title', 'Edit Player')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-purple-600 text-white px-6 py-4">
            <h1 class="text-xl font-semibold">Edit Player: {{ $player->name }}</h1>
        </div>
        <form action="{{ route('players.update', $player) }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf
            @method('PUT')
            <div class="grid md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Player Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $player->name) }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div class="mb-4">
                    <label for="team_id" class="block text-sm font-medium text-gray-700 mb-2">Team</label>
                    <select name="team_id" id="team_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        @foreach($teams as $team)
                        <option value="{{ $team->team_id }}" {{ old('team_id', $player->team_id) == $team->team_id ? 'selected' : '' }}>
                            {{ $team->team_name }} ({{ $team->country->name ?? 'N/A' }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label for="dob" class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                    <input type="date" name="dob" id="dob" value="{{ old('dob', $player->dob?->format('Y-m-d')) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div class="mb-4">
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <select name="role" id="role" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">Select Role</option>
                        <option value="Batsman" {{ old('role', $player->role) == 'Batsman' ? 'selected' : '' }}>Batsman</option>
                        <option value="Bowler" {{ old('role', $player->role) == 'Bowler' ? 'selected' : '' }}>Bowler</option>
                        <option value="All-rounder" {{ old('role', $player->role) == 'All-rounder' ? 'selected' : '' }}>All-rounder</option>
                        <option value="Wicket-keeper" {{ old('role', $player->role) == 'Wicket-keeper' ? 'selected' : '' }}>Wicket-keeper</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="batting_style" class="block text-sm font-medium text-gray-700 mb-2">Batting Style</label>
                    <select name="batting_style" id="batting_style" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">Select Batting Style</option>
                        <option value="Right-handed" {{ old('batting_style', $player->batting_style) == 'Right-handed' ? 'selected' : '' }}>Right-handed</option>
                        <option value="Left-handed" {{ old('batting_style', $player->batting_style) == 'Left-handed' ? 'selected' : '' }}>Left-handed</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="bowling_style" class="block text-sm font-medium text-gray-700 mb-2">Bowling Style</label>
                    <select name="bowling_style" id="bowling_style" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">Select Bowling Style</option>
                        <option value="Right-arm fast" {{ old('bowling_style', $player->bowling_style) == 'Right-arm fast' ? 'selected' : '' }}>Right-arm fast</option>
                        <option value="Left-arm fast" {{ old('bowling_style', $player->bowling_style) == 'Left-arm fast' ? 'selected' : '' }}>Left-arm fast</option>
                        <option value="Right-arm spin" {{ old('bowling_style', $player->bowling_style) == 'Right-arm spin' ? 'selected' : '' }}>Right-arm spin</option>
                        <option value="Left-arm spin" {{ old('bowling_style', $player->bowling_style) == 'Left-arm spin' ? 'selected' : '' }}>Left-arm spin</option>
                    </select>
                </div>
            </div>
            <div class="mb-6">
                <label for="profile_image" class="block text-sm font-medium text-gray-700 mb-2">Profile Image</label>
                <input type="file" name="profile_image" id="profile_image" accept="image/*"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                @if($player->profile_image)
                <p class="text-sm text-gray-500 mt-1">Current image will be kept if no new image is uploaded.</p>
                @endif
            </div>
            <div class="flex space-x-4">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-6 rounded-lg transition">
                    Update Player
                </button>
                <a href="{{ route('players.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-6 rounded-lg transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
