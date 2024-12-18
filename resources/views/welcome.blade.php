<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Memorize your Scripture</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased dark:bg-black dark:text-white/50 bg-no-repeat bg-center bg-cover min-h-screen" style="background-image: url({{ asset('images/sunrise.webp') }});">
        <x-content-card>
            <h1 class="text-4xl font-bold text-center">Memorize your Scripture</h1>
            <p class="text-center">Memorize your favorite scripture verses with ease.</p>
        </x-content-card>
    </body>
</html>
