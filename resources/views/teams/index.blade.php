@extends('layouts.app')

@section('title', 'Teams')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <h1 class="text-3xl font-bold text-gray-900">Teams</h1>
        <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto">
            <form action="{{ route('teams.index') }}" method="GET" class="flex gap-2">
                <select name="country_id" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Countries</option>
                    @foreach($countries as $country)
                    <option value="{{ $country->country_id }}" {{ request('country_id') == $country->country_id ? 'selected' : '' }}>
                        {{ $country->name }}
                    </option>
                    @endforeach
                </select>
                <button type="submit" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg transition">
                    Filter
                </button>
            </form>
            <a href="{{ route('teams.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition text-center">
                + Add Team
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @forelse($teams as $team)
        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
            <div class="bg-gradient-to-r from-green-600 to-green-700 text-white p-4">
                <h2 class="text-xl font-semibold">{{ $team->team_name }}</h2>
                <p class="text-green-100 text-sm">{{ $team->country->name ?? 'N/A' }}</p>
            </div>
            <div class="p-4">
                <p class="text-gray-600 text-sm mb-4">{{ $team->players_count }} Players</p>
                @if($team->in_match)
                <p class="text-gray-500 text-xs mb-4">{{ $team->in_match }}</p>
                @endif
                <div class="flex space-x-2">
                    <a href="{{ route('players.index', ['team_id' => $team->team_id]) }}" class="flex-1 bg-green-600 hover:bg-green-700 text-white text-center py-2 px-3 rounded text-sm transition">
                        Players
                    </a>
                    <a href="{{ route('teams.edit', $team) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-3 rounded text-sm transition">
                        Edit
                    </a>
                    <form action="{{ route('teams.destroy', $team) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-100 hover:bg-red-200 text-red-600 py-2 px-3 rounded text-sm transition">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12">
            <p class="text-gray-500 text-lg">No teams found.</p>
            <a href="{{ route('teams.create') }}" class="text-blue-600 hover:underline mt-2 inline-block">Add your first team</a>
        </div>
        @endforelse
    </div>
</div>
@endsection
