@extends('layouts.app')

@section('title', 'Add Team')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-green-600 text-white px-6 py-4">
            <h1 class="text-xl font-semibold">Add New Team</h1>
        </div>
        <form action="{{ route('teams.store') }}" method="POST" class="p-6">
            @csrf
            <div class="mb-4">
                <label for="team_name" class="block text-sm font-medium text-gray-700 mb-2">Team Name</label>
                <input type="text" name="team_name" id="team_name" value="{{ old('team_name') }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('team_name') border-red-500 @enderror">
                @error('team_name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="country_id" class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                <select name="country_id" id="country_id" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('country_id') border-red-500 @enderror">
                    <option value="">Select Country</option>
                    @foreach($countries as $country)
                    <option value="{{ $country->country_id }}" {{ old('country_id') == $country->country_id ? 'selected' : '' }}>
                        {{ $country->name }}
                    </option>
                    @endforeach
                </select>
                @error('country_id')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-6">
                <label for="in_match" class="block text-sm font-medium text-gray-700 mb-2">Status (optional)</label>
                <input type="text" name="in_match" id="in_match" value="{{ old('in_match') }}" placeholder="e.g., IPL, World Cup"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div class="flex space-x-4">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg transition">
                    Save Team
                </button>
                <a href="{{ route('teams.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-6 rounded-lg transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
