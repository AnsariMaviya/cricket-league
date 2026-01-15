@extends('layouts.app')

@section('title', 'Add Venue')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-orange-600 text-white px-6 py-4">
            <h1 class="text-xl font-semibold">Add New Venue</h1>
        </div>
        <form action="{{ route('venues.store') }}" method="POST" class="p-6">
            @csrf
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Venue Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 @error('name') border-red-500 @enderror">
                @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                <input type="text" name="address" id="address" value="{{ old('address') }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 @error('address') border-red-500 @enderror">
                @error('address')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label for="city" class="block text-sm font-medium text-gray-700 mb-2">City (optional)</label>
                    <input type="text" name="city" id="city" value="{{ old('city') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                </div>
                <div class="mb-4">
                    <label for="capacity" class="block text-sm font-medium text-gray-700 mb-2">Capacity (optional)</label>
                    <input type="number" name="capacity" id="capacity" value="{{ old('capacity') }}" min="0"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                </div>
            </div>
            <div class="flex space-x-4">
                <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2 px-6 rounded-lg transition">
                    Save Venue
                </button>
                <a href="{{ route('venues.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-6 rounded-lg transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
