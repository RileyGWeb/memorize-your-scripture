@props(['class' => ''])

<nav x-data="{ open: false }" class="bg-bg border-b border-gray-100 fixed top-0 left-0 w-full h-16 z-50 shadow {{ $class }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full">
        <div class="flex justify-between items-center h-full">
            <div class="flex items-center">
                <a href="/" class="flex">
                    <img src="{{ asset('images/icons/bible.png') }}" alt="logo" class="w-6 h-6" />
                    <div class="flex flex-col leading-none ml-1">
                        <span class="font-black tracking-widest text-xs -mb-1">MEM</span>
                        <span class="font-light text-xs">ORIZE</span>
                    </div>
                </a>
            </div>

            <div class="flex items-center">
                @auth
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none transition">
                            <img class="w-8 h-8 rounded-full object-cover mr-2" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                            <span>{{ Auth::user()->name }}</span>
                            <svg class="ml-2 w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                        <div 
                            x-show="open"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            @click.outside="open = false"
                            class="absolute right-0 mt-2 w-40 bg-white border border-gray-300 rounded-lg shadow py-2 z-50"
                            x-cloak
                        >
                            <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                {{ __('Profile') }}
                            </a>
                            <div class="border-t border-gray-200 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}" x-data>
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    {{ __('Log Out') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <x-button variant="outline" @click="loginModal = true" class="mr-2">
                        {{ __('Sign in') }}
                    </x-button>
                    <x-button @click="registerModal = true">
                        {{ __('Register') }}
                    </x-button>
                @endauth
            </div>
        </div>
    </div>
</nav>
