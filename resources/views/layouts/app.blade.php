<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Guard PlayStation') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#0b0f19] text-slate-100 selection:bg-[#3790f4] selection:text-white">
    <div class="min-h-screen bg-[#0b0f19]">
        
        {{-- Sidebar Vertikal (Fixed di Kiri) --}}
        @include('layouts.navigation')

        {{-- Seluruh Konten Digeser Kanan Selebar Sidebar (ml-64) --}}
        <div class="ml-64 min-h-screen bg-[#0b0f19] flex flex-col">
            
            {{-- Slot Header (Dark Mode) --}}
            @if (isset($header))
                <header class="bg-slate-900/80 backdrop-blur-md border-b border-slate-800 shadow-sm sticky top-0 z-30">
                    <div class="max-w-7xl mx-auto py-5 px-6 text-slate-100">
                        {{ $header }}
                    </div>
                </header>
            @endif

            {{-- Slot Main Content (Dark Mode) --}}
            <main class="flex-1 p-6 bg-[#0b0f19]">
                {{ $slot }}
            </main>
            
        </div>

    </div>
</body>
</html>