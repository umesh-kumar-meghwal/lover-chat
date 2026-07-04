<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Laravel') }}</title>
        
        <!-- Scripts and Styles -->
            <script src="https://cdn.tailwindcss.com"></script>

    </head>
    <body class="font-sans antialiased">
    
    <!-- BEAUTIFUL MODERN OFFLINE ALERT (Automatically handled by Livewire) -->
    <div wire:offline class="fixed top-5 left-1/2 -translate-x-1/2 z-50 bg-red-500 text-white px-5 py-3 rounded-2xl shadow-2xl flex items-center space-x-3 border border-red-400/30 animate-bounce">
        <!-- Pulse Status Indicator -->
        <span class="relative flex h-3 w-3">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-white"></span>
        </span>
        
        <div class="text-left">
            <p class="text-xs font-extrabold tracking-wide leading-tight">Internet Disconnected</p>
            <p class="text-[10px] text-red-100 mt-0.5">Please check your network. Reconnecting...</p>
        </div>
    </div>

    <!-- Layout Wrapper -->
    <div class="min-h-screen bg-gray-100">
        @include('layouts.navigation')

        <!-- Page Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>
    </div>

    @live
</html>