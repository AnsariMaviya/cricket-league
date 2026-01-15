@extends('layouts.app')

@section('title', 'Players')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <h1 class="text-3xl font-bold text-gray-900">Players</h1>
        <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto">
            <form action="{{ route('players.index') }}" method="GET" class="flex gap-2">
                <select name="team_id" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">All Teams</option>
                    @foreach($teams as $team)
                    <option value="{{ $team->team_id }}" {{ request('team_id') == $team->team_id ? 'selected' : '' }}>
                        {{ $team->team_name }}
                    </option>
                    @endforeach
                </select>
                <button type="submit" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg transition">
                    Filter
                </button>
            </form>
            <a href="{{ route('players.create') }}" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-lg transition text-center">
                + Add Player
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @forelse($players as $player)
        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
            <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white p-4">
                <h2 class="text-xl font-semibold">{{ $player->name }}</h2>
                <p class="text-purple-100 text-sm">{{ $player->team->team_name ?? 'N/A' }}</p>
            </div>
            <div class="p-4">
                @if($player->role)
                <p class="text-gray-600 text-sm mb-2"><strong>Role:</strong> {{ $player->role }}</p>
                @endif
                @if($player->dob)
                <p class="text-gray-600 text-sm mb-2"><strong>Age:</strong> {{ $player->dob->age }} years</p>
                @endif
                <div class="flex space-x-2 mt-4">
                    <a href="{{ route('players.show', $player) }}" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white text-center py-2 px-3 rounded text-sm transition">
                        Profile
                    </a>
                    <a href="{{ route('players.edit', $player) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-3 rounded text-sm transition">
                        Edit
                    </a>
                    <form action="{{ route('players.destroy', $player) }}" method="POST" onsubmit="return confirm('Are you sure?')">
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
            <p class="text-gray-500 text-lg">No players found.</p>
            <a href="{{ route('players.create') }}" class="text-purple-600 hover:underline mt-2 inline-block">Add your first player</a>
        </div>
        @endforelse
    </div>
</div>
@endsection
