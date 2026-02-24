<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,700;0,900;1,400&display=swap" rel="stylesheet">

        <!-- PWA -->
        <link rel="manifest" href="/manifest.webmanifest">
        <meta name="theme-color" content="#111827">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <!-- Scripts -->
        @if(file_exists(public_path('hot')))
            {{-- Development mode with Vite dev server --}}
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            {{-- Production mode with built assets --}}
            @php
                $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
            @endphp
            <link rel="stylesheet" href="{{ asset('build/' . $manifest['resources/css/app.css']['file']) }}">
            <script type="module" src="{{ asset('build/' . $manifest['resources/js/app.js']['file']) }}"></script>
        @endif

        <!-- Styles -->
        @livewireStyles

        {{-- this is NOT the real layout --}}
    </head>
    <body class="font-sans antialiased bg-gray-100">
        <x-banner />

        <div class="min-h-screen bg-gray-100">
            @livewire('navigation-menu')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        @stack('modals')

        <!-- PWA Install Prompt -->

        @livewireScripts

        <!-- PWA Update Prompt -->
        <script>
            window.addEventListener('sw:need-refresh', () => {
                if (confirm('A new version is available. Refresh now?')) location.reload();
            });
            window.addEventListener('sw:offline-ready', () => console.log('App ready to work offline.'))
        </script>
    </body>
</html>
