<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'TechCorp Indonesia') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @if(file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
        @endif
    </head>
    <body class="font-sans text-gray-800 bg-gray-50 antialiased">
        <div class="min-h-screen flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">TechCorp Indonesia</h1>
                <p class="text-sm text-gray-500 mt-1">Masuk ke Employee Directory</p>
            </div>

            <div class="w-full sm:max-w-md bg-white border border-gray-200 rounded-lg p-8 shadow-sm">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
