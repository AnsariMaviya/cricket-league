@extends('layouts.app')

@section('title', $player->name)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-purple-600 to-purple-800 text-white p-8">
            <div class="flex flex-col md:flex-row items-center gap-6">
                @if($player->profile_image)
                <img src="{{ asset('storage/' . $player->profile_image) }}" alt="{{ $player->name }}" class="w-32 h-32 rounded-full border-4 border-white object-cover">
                @else
                <div class="w-32 h-32 rounded-full border-4 border-white bg-purple-400 flex items-center justify-center">
                    <span class="text-4xl font-bold">{{ substr($player->name, 0, 1) }}</span>
                </div>
                @endif
                <div class="text-center md:text-left">
                    <h1 class="text-3xl font-bold">{{ $player->name }}</h1>
                    <p class="text-purple-200 text-lg">{{ $player->team->team_name ?? 'No Team' }}</p>
                    @if($player->role)
                    <span class="inline-block mt-2 bg-purple-500 px-3 py-1 rounded-full text-sm">{{ $player->role }}</span>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="p-8">
            <div class="grid md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <h2 class="text-xl font-semibold text-gray-900 border-b pb-2">Personal Information</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Full Name</span>
                            <span class="font-medium">{{ $player->name }}</span>
                        </div>
                        @if($player->dob)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Date of Birth</span>
                            <span class="font-medium">{{ $player->dob->format('F d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Age</span>
                            <span class="font-medium">{{ $player->dob->age }} years</span>
                        </div>
                        @endif
                    </div>
                </div>
                
                <div class="space-y-4">
                    <h2 class="text-xl font-semibold text-gray-900 border-b pb-2">Team Information</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Team</span>
                            <span class="font-medium">{{ $player->team->team_name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Country</span>
                            <span class="font-medium">{{ $player->team->country->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Country Code</span>
                            <span class="font-medium">{{ $player->team->country->short_name ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-4 md:col-span-2">
                    <h2 class="text-xl font-semibold text-gray-900 border-b pb-2">Playing Style</h2>
                    <div class="grid md:grid-cols-3 gap-4">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <span class="text-gray-600 text-sm">Role</span>
                            <p class="font-medium">{{ $player->role ?? 'Not specified' }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <span class="text-gray-600 text-sm">Batting Style</span>
                            <p class="font-medium">{{ $player->batting_style ?? 'Not specified' }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <span class="text-gray-600 text-sm">Bowling Style</span>
                            <p class="font-medium">{{ $player->bowling_style ?? 'Not specified' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex space-x-4 mt-8">
                <a href="{{ route('players.edit', $player) }}" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-6 rounded-lg transition">
                    Edit Player
                </a>
                <a href="{{ route('players.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-6 rounded-lg transition">
                    Back to Players
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
