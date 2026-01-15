@extends('layouts.app')

@section('title', 'Home')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl shadow-xl p-8 mb-8 text-white">
        <h1 class="text-4xl font-bold mb-4">Welcome to Cricket League</h1>
        <p class="text-xl text-blue-100">Your ultimate destination for cricket league management</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition">
            <div class="text-3xl font-bold text-blue-600">{{ $stats['countries'] }}</div>
            <div class="text-gray-600 mt-2">Countries</div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition">
            <div class="text-3xl font-bold text-green-600">{{ $stats['teams'] }}</div>
            <div class="text-gray-600 mt-2">Teams</div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition">
            <div class="text-3xl font-bold text-purple-600">{{ $stats['players'] }}</div>
            <div class="text-gray-600 mt-2">Players</div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition">
            <div class="text-3xl font-bold text-orange-600">{{ $stats['venues'] }}</div>
            <div class="text-gray-600 mt-2">Venues</div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition">
            <div class="text-3xl font-bold text-red-600">{{ $stats['matches'] }}</div>
            <div class="text-gray-600 mt-2">Matches</div>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-8">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-blue-600 text-white px-6 py-4">
                <h2 class="text-xl font-semibold">Recent Matches</h2>
            </div>
            <div class="p-6">
                @forelse($recentMatches as $match)
                <div class="flex items-center justify-between py-3 border-b last:border-0">
                    <div>
                        <span class="font-semibold">{{ $match->firstTeam->team_name ?? 'TBA' }}</span>
                        <span class="text-gray-500 mx-2">vs</span>
                        <span class="font-semibold">{{ $match->secondTeam->team_name ?? 'TBA' }}</span>
                    </div>
                    <div class="text-right">
                        <span class="inline-block px-2 py-1 text-xs rounded-full
                            @if($match->status === 'completed') bg-green-100 text-green-800
                            @elseif($match->status === 'live') bg-red-100 text-red-800
                            @elseif($match->status === 'scheduled') bg-blue-100 text-blue-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($match->status) }}
                        </span>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">No matches yet</p>
                @endforelse
                <a href="{{ route('matches.index') }}" class="block text-center text-blue-600 hover:text-blue-800 mt-4">View All Matches</a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-green-600 text-white px-6 py-4">
                <h2 class="text-xl font-semibold">Upcoming Matches</h2>
            </div>
            <div class="p-6">
                @forelse($upcomingMatches as $match)
                <div class="flex items-center justify-between py-3 border-b last:border-0">
                    <div>
                        <span class="font-semibold">{{ $match->firstTeam->team_name ?? 'TBA' }}</span>
                        <span class="text-gray-500 mx-2">vs</span>
                        <span class="font-semibold">{{ $match->secondTeam->team_name ?? 'TBA' }}</span>
                    </div>
                    <div class="text-right text-sm text-gray-600">
                        {{ $match->match_date ? $match->match_date->format('M d, Y') : 'TBA' }}
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">No upcoming matches</p>
                @endforelse
                <a href="{{ route('matches.index') }}" class="block text-center text-green-600 hover:text-green-800 mt-4">View Schedule</a>
            </div>
        </div>
    </div>

    <div class="mt-8 grid md:grid-cols-3 gap-6">
        <a href="{{ route('countries.index') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition group">
            <div class="flex items-center space-x-4">
                <div class="bg-blue-100 p-3 rounded-full group-hover:bg-blue-200 transition">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-lg">Manage Countries</h3>
                    <p class="text-gray-600 text-sm">Add and manage participating countries</p>
                </div>
            </div>
        </a>

        <a href="{{ route('teams.index') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition group">
            <div class="flex items-center space-x-4">
                <div class="bg-green-100 p-3 rounded-full group-hover:bg-green-200 transition">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-lg">Manage Teams</h3>
                    <p class="text-gray-600 text-sm">Create and organize teams</p>
                </div>
            </div>
        </a>

        <a href="{{ route('players.index') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition group">
            <div class="flex items-center space-x-4">
                <div class="bg-purple-100 p-3 rounded-full group-hover:bg-purple-200 transition">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-lg">Manage Players</h3>
                    <p class="text-gray-600 text-sm">Add players to your teams</p>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection
