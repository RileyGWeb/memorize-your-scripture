<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ loginModal: false, registerModal: false }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,700;0,900;1,400&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-no-repeat bg-center bg-cover min-h-screen" 
          style="background-image: url({{ asset('images/sunrise.webp') }});">
        <x-header />
        <main class="pb-4 pt-20 px-2 gap-2 flex flex-col items-center w-full max-w-sm mx-auto">
            {{ $slot }}
        </main>
        @stack('modals')
        <div x-show="loginModal" 
             class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50" 
             x-cloak>
            <div class="bg-white p-6 rounded shadow-md relative w-96">
                <button class="absolute top-2 right-2 text-gray-600" @click="loginModal = false">✕</button>
                <h2 class="text-xl font-bold mb-4">Sign In</h2>
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium">Email</label>
                        <input id="email" type="email" name="email" required autofocus
                               class="border w-full px-3 py-2 rounded-lg mt-1" />
                    </div>
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium">Password</label>
                        <input id="password" type="password" name="password" required
                               class="border w-full px-3 py-2 rounded-lg mt-1" />
                    </div>
                    <div class="mb-4 flex items-center">
                        <input type="checkbox" id="remember_me" name="remember" class="mr-1 rounded">
                        <label for="remember_me" class="text-sm">Remember me</label>
                    </div>
                    <x-button type="submit">Log In</x-button>
                </form>
            </div>
        </div>
        <div x-show="registerModal" 
             class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50" 
             x-cloak>
            <div class="bg-white p-6 rounded shadow-md relative w-96">
                <button class="absolute top-2 right-2 text-gray-600" @click="registerModal = false">✕</button>
                <h2 class="text-xl font-bold mb-4">Register</h2>
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium">Name</label>
                        <input id="name" type="text" name="name" required autofocus
                               class="border w-full px-3 py-2 rounded-lg mt-1" />
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium">Email</label>
                        <input id="email" type="email" name="email" required
                               class="border w-full px-3 py-2 rounded-lg mt-1" />
                    </div>
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium">Password</label>
                        <input id="password" type="password" name="password" required
                               class="border w-full px-3 py-2 rounded-lg mt-1" />
                    </div>
                    <div class="mb-4">
                        <label for="password_confirmation" class="block text-sm font-medium">Confirm Password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required
                               class="border w-full px-3 py-2 rounded-lg mt-1" />
                    </div>
                    <x-button type="submit" variant="outline">Register</x-button>
                </form>
            </div>
        </div>
        @livewireScripts
    </body>
</html>
