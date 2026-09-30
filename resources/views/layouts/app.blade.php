<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Rental PS Kita') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100 text-gray-900">
    <div class="min-h-screen bg-gray-100">
        
        {{-- Sidebar Vertikal (Fixed di Kiri) --}}
        @include('layouts.navigation')

        {{-- Seluruh Konten Digeser Kanan Selebar Sidebar (ml-64) --}}
        <div class="ml-64 min-h-screen bg-gray-100 flex flex-col">
            
            {{-- Slot Header --}}
            @if (isset($header))
                <header class="bg-white border-b border-gray-200 shadow-xs">
                    <div class="max-w-7xl mx-auto py-5 px-6">
                        {{ $header }}
                    </div>
                </header>
            @endif

            {{-- Slot Main Content --}}
            <main class="flex-1 p-6 bg-gray-100">
                {{ $slot }}
            </main>
            
        </div>

    </div>
</body>
</html>