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
        <main class="pb-4 pt-24 px-2 gap-4 flex flex-col items-center w-full max-w-sm mx-auto z-10 relative">
            {{ $slot }}
        </main>

        <div id="overlay" class="fixed inset-0 bg-black/20 z-5"></div>

        <footer class="fixed bottom-0 left-0 w-full bg-white shadow-md py-2 px-4 flex justify-between items-center">
            <div class="text-sm text-gray-600">
                <a href="{{ route('about') }}" class="hover:underline">About</a>
                <span class="mx-2">|</span>
                <a href="{{ route('privacy-policy') }}" class="hover:underline">Privacy Policy</a>
                <span class="mx-2">|</span>
                <span>&copy; Riley G. Dev {{ date('Y') }}</span>
            </div>
        </footer>
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
                        <label for="login-email" class="block text-sm font-medium">Email</label>
                        <input id="login-email" type="email" name="email" required autofocus
                               class="border w-full px-3 py-2 rounded-lg mt-1" />
                    </div>
                    <div class="mb-4">
                        <label for="login-password" class="block text-sm font-medium">Password</label>
                        <input id="login-password" type="password" name="password" required
                               class="border w-full px-3 py-2 rounded-lg mt-1" />
                    </div>
                    <div class="mb-4 flex items-center">
                        <input type="checkbox" id="login-remember-me" name="remember" class="mr-1 rounded">
                        <label for="login-remember-me" class="text-sm">Remember me</label>
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
                        <label for="register-name" class="block text-sm font-medium">Name</label>
                        <input id="register-name" type="text" name="name" required autofocus
                               class="border w-full px-3 py-2 rounded-lg mt-1" />
                    </div>
                    <div class="mb-4">
                        <label for="register-email" class="block text-sm font-medium">Email</label>
                        <input id="register-email" type="email" name="email" required
                               class="border w-full px-3 py-2 rounded-lg mt-1" />
                    </div>
                    <div class="mb-4">
                        <label for="register-password" class="block text-sm font-medium">Password</label>
                        <input id="register-password" type="password" name="password" required
                               class="border w-full px-3 py-2 rounded-lg mt-1" />
                    </div>
                    <div class="mb-4">
                        <label for="register-password-confirmation" class="block text-sm font-medium">Confirm Password</label>
                        <input id="register-password-confirmation" type="password" name="password_confirmation" required
                               class="border w-full px-3 py-2 rounded-lg mt-1" />
                    </div>
                    <x-button type="submit" variant="outline">Register</x-button>
                </form>
            </div>
        </div>
        @livewireScripts
    </body>
</html>
