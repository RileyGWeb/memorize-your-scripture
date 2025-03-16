@props(['class' => ''])

<header 
    class="bg-bg fixed top-0 left-0 w-full h-16 flex items-center justify-between px-4 z-50 border-b border-stroke shadow {{ $class }}">

    <!-- Logo Area -->
    <a href="/" class="flex items-center gap-1">
        <img src="{{ asset('images/icons/bible.png') }}" alt="logo" class="w-8 h-8">
        <div class="flex flex-col leading-none">
            <span class="font-black tracking-widest text-xs">MEM</span>
            <span class="font-light text-xs">ORIZE</span>
        </div>
    </a>

    <!-- Right Side: Auth / Guest -->
    <div class="flex items-center gap-4">
        @auth
            <!-- Logged in: show Alpine dropdown with profile pic -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="flex items-center">
                    <!-- If you have user profile pics, put it here. Otherwise, placeholder icon: -->
                    <img src="{{ asset('images/icons/user.png') }}" 
                         alt="Profile" 
                         class="w-8 h-8 rounded-full border border-gray-300 mr-2" />
                    {{-- For jetstream profile photos:
                         <img src="{{ Auth::user()->profile_photo_url }}" class="w-8 h-8 rounded-full mr-2" alt="{{ Auth::user()->name }}" /> --}}

                    <span class="font-medium">
                        {{ Auth::user()->name }}
                    </span>
                </button>

                <!-- Dropdown -->
                <div x-show="open" 
                     @click.outside="open = false" 
                     class="absolute right-0 mt-2 w-40 bg-white border border-gray-300 rounded shadow py-2 z-50"
                     x-cloak>
                    <a href="#" class="block px-4 py-2 hover:bg-gray-100">
                        Profile
                    </a>
                    <a href="#" class="block px-4 py-2 hover:bg-gray-100">
                        Settings
                    </a>

                    <div class="border-t my-2"></div>

                    <!-- Logout Form -->
                    <form method="POST" action="{{ route('logout') }}" class="block">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 hover:bg-gray-100">
                            Log Out
                        </button>
                    </form>
                </div>
            </div>
        @else
            <!-- Guest: show sign in / register buttons that open Alpine modals -->
            <button type="button" @click="loginModal = true" class="px-3 py-1 rounded">
                Sign In
            </button>
            <button type="button" @click="registerModal = true"
                    class="border border-black px-3 py-1 rounded">
                Register
            </button>
        @endauth
    </div>
</header>
