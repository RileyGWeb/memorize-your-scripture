<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ 
    loginModal: {{ ($errors->any() && old('email') && !old('name')) ? 'true' : 'false' }}, 
    registerModal: {{ ($errors->any() && old('name')) ? 'true' : 'false' }} 
}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,700;0,900;1,400&display=swap" rel="stylesheet">
        
        <!-- PWA -->
        <link rel="manifest" href="/manifest.webmanifest">
        <meta name="theme-color" content="#111827">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="Scripture Memorizer">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-no-repeat bg-center bg-cover min-h-screen pb-48" 
          style="background-image: url({{ asset('images/sunrise.webp') }});">
        <x-header />
        <main class="pb-4 pt-24 px-2 gap-4 flex flex-col items-center w-full max-w-sm mx-auto z-10 relative">
            {{ $slot }}
        </main>

        <div id="overlay" class="fixed inset-0 bg-black/20 z-5"></div>

        <footer class="fixed bottom-0 left-0 w-full bg-white shadow-md py-2 px-4 flex justify-between items-center z-[51] border-t border-stroke">
            <div class="text-sm text-gray-600">
                <a href="{{ route('about') }}" class="hover:underline" wire:navigate>About</a>
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
                
                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                        <ul class="text-sm text-red-600">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-4">
                        <label for="login-email" class="block text-sm font-medium">Email</label>
                        <input id="login-email" type="email" name="email" value="{{ old('email') }}" required autofocus
                               class="border w-full px-3 py-2 rounded-lg mt-1 @error('email') border-red-500 @enderror" />
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="login-password" class="block text-sm font-medium">Password</label>
                        <input id="login-password" type="password" name="password" required
                               class="border w-full px-3 py-2 rounded-lg mt-1 @error('password') border-red-500 @enderror" />
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4 flex items-center">
                        <input type="checkbox" id="login-remember-me" name="remember" {{ old('remember') ? 'checked' : '' }} class="mr-1 rounded">
                        <label for="login-remember-me" class="text-sm">Remember me</label>
                    </div>
                    <x-button type="submit">Log In</x-button>
                </form>
                <div class="mt-4 text-center">
                    <p class="text-sm text-gray-600">
                        Don't have an account? 
                        <button @click="loginModal = false; registerModal = true" class="text-blue-600 hover:text-blue-800 underline">
                            Register here
                        </button>
                    </p>
                </div>
            </div>
        </div>
        <div x-show="registerModal" 
             class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50" 
             x-cloak>
            <div class="bg-white p-6 rounded shadow-md relative w-96">
                <button class="absolute top-2 right-2 text-gray-600" @click="registerModal = false">✕</button>
                <h2 class="text-xl font-bold mb-4">Register</h2>
                
                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                        <ul class="text-sm text-red-600">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="mb-4">
                        <label for="register-name" class="block text-sm font-medium">Name</label>
                        <input id="register-name" type="text" name="name" value="{{ old('name') }}" required autofocus
                               class="border w-full px-3 py-2 rounded-lg mt-1 @error('name') border-red-500 @enderror" />
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="register-email" class="block text-sm font-medium">Email</label>
                        <input id="register-email" type="email" name="email" value="{{ old('email') }}" required
                               class="border w-full px-3 py-2 rounded-lg mt-1 @error('email') border-red-500 @enderror" />
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="register-password" class="block text-sm font-medium">Password</label>
                        <input id="register-password" type="password" name="password" required
                               class="border w-full px-3 py-2 rounded-lg mt-1 @error('password') border-red-500 @enderror" />
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="register-password-confirmation" class="block text-sm font-medium">Confirm Password</label>
                        <input id="register-password-confirmation" type="password" name="password_confirmation" required
                               class="border w-full px-3 py-2 rounded-lg mt-1 @error('password_confirmation') border-red-500 @enderror" />
                        @error('password_confirmation')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <x-button type="submit" variant="outline">Register</x-button>
                </form>
                <div class="mt-4 text-center">
                    <p class="text-sm text-gray-600">
                        Already have an account? 
                        <button @click="registerModal = false; loginModal = true" class="text-blue-600 hover:text-blue-800 underline">
                            Sign in here
                        </button>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- PWA Install Prompt -->
        @livewire('install-prompt')
        
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
