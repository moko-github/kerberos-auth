<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-gray-100 flex flex-col items-center justify-center p-4">

    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900">{{ config('app.name', 'Laravel') }}</h1>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
            {{ $slot }}
        </div>

    </div>

    @livewireScripts
</body>
</html>
