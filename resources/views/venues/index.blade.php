@extends('layouts.app')

@section('title', 'Venues')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Venues</h1>
        <a href="{{ route('venues.create') }}" class="bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2 px-4 rounded-lg transition">
            + Add Venue
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($venues as $venue)
        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
            <div class="bg-gradient-to-r from-orange-600 to-orange-700 text-white p-4">
                <h2 class="text-xl font-semibold">{{ $venue->name }}</h2>
            </div>
            <div class="p-4">
                <p class="text-gray-600 text-sm mb-2"><strong>Address:</strong> {{ $venue->address }}</p>
                @if($venue->city)
                <p class="text-gray-600 text-sm mb-2"><strong>City:</strong> {{ $venue->city }}</p>
                @endif
                @if($venue->capacity)
                <p class="text-gray-600 text-sm mb-2"><strong>Capacity:</strong> {{ number_format($venue->capacity) }}</p>
                @endif
                <p class="text-gray-500 text-sm mb-4">{{ $venue->matches_count }} Matches</p>
                <div class="flex space-x-2">
                    <a href="{{ route('matches.index', ['venue_id' => $venue->venue_id]) }}" class="flex-1 bg-orange-600 hover:bg-orange-700 text-white text-center py-2 px-3 rounded text-sm transition">
                        View Matches
                    </a>
                    <a href="{{ route('venues.edit', $venue) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-3 rounded text-sm transition">
                        Edit
                    </a>
                    <form action="{{ route('venues.destroy', $venue) }}" method="POST" onsubmit="return confirm('Are you sure?')">
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
            <p class="text-gray-500 text-lg">No venues found.</p>
            <a href="{{ route('venues.create') }}" class="text-orange-600 hover:underline mt-2 inline-block">Add your first venue</a>
        </div>
        @endforelse
    </div>
</div>
@endsection
