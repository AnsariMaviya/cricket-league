@extends('layouts.app')

@section('title', 'Countries')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Countries</h1>
        <a href="{{ route('countries.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition">
            + Add Country
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @forelse($countries as $country)
        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">{{ $country->name }}</h2>
                    <span class="bg-blue-100 text-blue-800 text-sm font-medium px-2.5 py-0.5 rounded">{{ $country->short_name }}</span>
                </div>
                <p class="text-gray-600 text-sm mb-4">{{ $country->teams_count }} Teams</p>
                <div class="flex space-x-2">
                    <a href="{{ route('teams.index', ['country_id' => $country->country_id]) }}" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-3 rounded text-sm transition">
                        View Teams
                    </a>
                    <a href="{{ route('countries.edit', $country) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-3 rounded text-sm transition">
                        Edit
                    </a>
                    <form action="{{ route('countries.destroy', $country) }}" method="POST" onsubmit="return confirm('Are you sure?')">
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
            <p class="text-gray-500 text-lg">No countries found.</p>
            <a href="{{ route('countries.create') }}" class="text-blue-600 hover:underline mt-2 inline-block">Add your first country</a>
        </div>
        @endforelse
    </div>
</div>
@endsection
