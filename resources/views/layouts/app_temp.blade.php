<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Cricket League Management System</title>

    <!-- Tailwind CSS -->
    @vite('resources/css/app.css')
    
    <!-- Scripts -->
    @vite('resources/js/app.js')
</head>
<body class="bg-gray-100">
    @yield('content')
</body>
</html>
