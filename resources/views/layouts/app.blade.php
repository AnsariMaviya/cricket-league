<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Cricket League') - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1e40af',
                        secondary: '#059669',
                        accent: '#dc2626',
                    }
                }
            }
        }
    </script>
</head>
<body class="font-sans antialiased bg-gray-100 min-h-screen">
    <nav class="bg-gradient-to-r from-blue-900 to-blue-700 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center space-x-2">
                        <svg class="w-8 h-8 text-yellow-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <span class="text-white font-bold text-xl">Cricket League</span>
                    </a>
                </div>
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="{{ route('home') }}" class="text-white hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('home') ? 'bg-blue-600' : '' }}">
                            Home
                        </a>
                        <a href="{{ route('countries.index') }}" class="text-white hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('countries.*') ? 'bg-blue-600' : '' }}">
                            Countries
                        </a>
                        <a href="{{ route('teams.index') }}" class="text-white hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('teams.*') ? 'bg-blue-600' : '' }}">
                            Teams
                        </a>
                        <a href="{{ route('players.index') }}" class="text-white hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('players.*') ? 'bg-blue-600' : '' }}">
                            Players
                        </a>
                        <a href="{{ route('matches.index') }}" class="text-white hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('matches.*') ? 'bg-blue-600' : '' }}">
                            Matches
                        </a>
                        <a href="{{ route('venues.index') }}" class="text-white hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('venues.*') ? 'bg-blue-600' : '' }}">
                            Venues
                        </a>
                        <a href="{{ route('results') }}" class="text-white hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('results') ? 'bg-blue-600' : '' }}">
                            Results
                        </a>
                    </div>
                </div>
                <div class="md:hidden">
                    <button type="button" onclick="toggleMobileMenu()" class="text-white hover:text-gray-300 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <div id="mobile-menu" class="hidden md:hidden bg-blue-800">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="{{ route('home') }}" class="text-white block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-600">Home</a>
                <a href="{{ route('countries.index') }}" class="text-white block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-600">Countries</a>
                <a href="{{ route('teams.index') }}" class="text-white block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-600">Teams</a>
                <a href="{{ route('players.index') }}" class="text-white block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-600">Players</a>
                <a href="{{ route('matches.index') }}" class="text-white block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-600">Matches</a>
                <a href="{{ route('venues.index') }}" class="text-white block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-600">Venues</a>
                <a href="{{ route('results') }}" class="text-white block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-600">Results</a>
            </div>
        </div>
    </nav>

    @if(session('success'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    </div>
    @endif

    <main class="py-8">
        @yield('content')
    </main>

    <footer class="bg-gray-800 text-white py-8 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <p>&copy; {{ date('Y') }} Cricket League. All rights reserved.</p>
                <p class="text-gray-400 text-sm mt-2">Built with Laravel {{ app()->version() }}</p>
            </div>
        </div>
    </footer>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        }
    </script>
    @stack('scripts')
</body>
</html>
