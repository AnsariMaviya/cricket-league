@extends('layouts.app')

@section('title', 'Add Player')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-purple-600 text-white px-6 py-4">
            <h1 class="text-xl font-semibold">Add New Player</h1>
        </div>
        <form action="{{ route('players.store') }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf
            <div class="grid md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Player Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 @error('name') border-red-500 @enderror">
                    @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-4">
                    <label for="team_id" class="block text-sm font-medium text-gray-700 mb-2">Team</label>
                    <select name="team_id" id="team_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 @error('team_id') border-red-500 @enderror">
                        <option value="">Select Team</option>
                        @foreach($teams as $team)
                        <option value="{{ $team->team_id }}" {{ old('team_id') == $team->team_id ? 'selected' : '' }}>
                            {{ $team->team_name }} ({{ $team->country->name ?? 'N/A' }})
                        </option>
                        @endforeach
                    </select>
                    @error('team_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-4">
                    <label for="dob" class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                    <input type="date" name="dob" id="dob" value="{{ old('dob') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div class="mb-4">
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <select name="role" id="role" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">Select Role</option>
                        <option value="Batsman" {{ old('role') == 'Batsman' ? 'selected' : '' }}>Batsman</option>
                        <option value="Bowler" {{ old('role') == 'Bowler' ? 'selected' : '' }}>Bowler</option>
                        <option value="All-rounder" {{ old('role') == 'All-rounder' ? 'selected' : '' }}>All-rounder</option>
                        <option value="Wicket-keeper" {{ old('role') == 'Wicket-keeper' ? 'selected' : '' }}>Wicket-keeper</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="batting_style" class="block text-sm font-medium text-gray-700 mb-2">Batting Style</label>
                    <select name="batting_style" id="batting_style" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">Select Batting Style</option>
                        <option value="Right-handed" {{ old('batting_style') == 'Right-handed' ? 'selected' : '' }}>Right-handed</option>
                        <option value="Left-handed" {{ old('batting_style') == 'Left-handed' ? 'selected' : '' }}>Left-handed</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="bowling_style" class="block text-sm font-medium text-gray-700 mb-2">Bowling Style</label>
                    <select name="bowling_style" id="bowling_style" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">Select Bowling Style</option>
                        <option value="Right-arm fast" {{ old('bowling_style') == 'Right-arm fast' ? 'selected' : '' }}>Right-arm fast</option>
                        <option value="Left-arm fast" {{ old('bowling_style') == 'Left-arm fast' ? 'selected' : '' }}>Left-arm fast</option>
                        <option value="Right-arm spin" {{ old('bowling_style') == 'Right-arm spin' ? 'selected' : '' }}>Right-arm spin</option>
                        <option value="Left-arm spin" {{ old('bowling_style') == 'Left-arm spin' ? 'selected' : '' }}>Left-arm spin</option>
                    </select>
                </div>
            </div>
            <div class="mb-6">
                <label for="profile_image" class="block text-sm font-medium text-gray-700 mb-2">Profile Image</label>
                <input type="file" name="profile_image" id="profile_image" accept="image/*"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>
            <div class="flex space-x-4">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-6 rounded-lg transition">
                    Save Player
                </button>
                <a href="{{ route('players.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-6 rounded-lg transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
