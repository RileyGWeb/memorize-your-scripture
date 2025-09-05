@props(['class' => ''])

<nav x-data="{ 
        open: false, 
        translationOpen: false, 
        searchTranslation: '' 
    }" class="bg-bg border-b border-gray-100 fixed top-0 left-0 w-full h-20 z-50 shadow {{ $class }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full">
        <div class="flex justify-between items-center h-12">

            <div class="flex items-center space-x-4">
                <a href="/" class="flex items-center">
                    <img src="{{ asset('images/icons/bible.png') }}" alt="logo" class="w-6 h-6" />
                    <div class="flex flex-col leading-none ml-1">
                        <span class="font-black tracking-widest text-xs -mb-1">MEM</span>
                        <span class="font-light text-xs">ORIZE</span>
                    </div>
                </a>
            </div>

            <div class="flex items-center space-x-4">

                @auth
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none transition">
                            <img class="border border-stroke w-8 h-8 rounded-full object-cover mr-2" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
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
                    <div class="flex items-center gap-2">                        
                        <x-button variant="outline" @click="loginModal = true">
                            {{ __('Sign in') }}
                        </x-button>
                        <x-button @click="registerModal = true">
                            {{ __('Register') }}
                        </x-button>
                    </div>
                @endauth

            </div>
        </div>

        <!-- Bible Translation Dropdown -->
        <div class="relative h-8 border-border border-t flex items-center justify-center w-full py-2" x-data="{ open: false, search: '' }">
            <button @click="open = !open" class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none">
                <span x-text="$store.bible.selectedTranslation"></span>
                <svg class="ml-1 w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div x-show="open" @click.outside="open = false" x-cloak class="absolute left-0 right-0 top-8 mt-2 w-full z-50">
                <div class="my-2 w-full bg-bg overflow-hidden rounded-xl shadow-xl">
                    <div class="p-2">
                        <input type="text" placeholder="Search translations..." x-model="search" class="w-full p-2 bg-transparent border-none focus:outline-none focus:ring-0" />
                    </div>
                    <ul class="max-h-64 overflow-y-auto border-border border-t">
                        <template x-for="(id, name) in $store.bible.translationsFiltered(search)" :key="name">
                            <li>
                                <button @click="
                                    $store.bible.setTranslation(name, id); 
                                    document.cookie = `bibleId=${id};path=/;max-age=${60*60*24*365}`;
                                    console.log('Bible ID set to:', id);
                                    window.location.reload();
                                    " class="w-full text-left px-4 py-2 hover:bg-gray-100" x-text="name"></button>
                            </li>
                        </template>
                    </ul>
                </div>
                <x-content-card classes="shadow-xl">
                    <x-content-card-title title='"I cant find my favorite translation!"' subtitle="A surprising amount of Bible translations require obtaining licensure from rights holders. It's a lengthy process, I'm working on it!" />
                    <x-divider />
                    <x-content-card-button href="/contact" text="Contact me!" icon="arrow-narrow-right" iconSize="lg" />
                </x-content-card>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('bible', {
                selectedTranslation: '{{ config("bible.default_name") }}',
                selectedId:           '{{ config("bible.default_id")   }}',
                translations: {
                    "American Standard Version (Byzantine Text)": "685d1470fe4d5c3b-01",
                    "Berean Standard Bible": "bba9f40183526463-01",
                    "Brenton English Septuagint (Updated Spelling and Formatting)": "6bab4d6c61b31b80-01",
                    "Brenton English translation of the Septuagint": "65bfdebd704a8324-01",
                    "Cambridge Paragraph Bible of the KJV": "55212e3cf5d04d49-01",
                    "Douay-Rheims American 1899": "179568874c45066f-01",
                    "English Majority Text Version": "55ec700d9e0d77ea-01",
                    "Free Bible Version": "65eec8e0b60e656b-01",
                    "Geneva Bible": "c315fa9f71d4af3a-01",
                    "JPS TaNaKH 1917": "bf8f1c7f3f9045a5-01",
                    "King James (Authorised) Version": "de4e12af7f28f599-01",
                    "King James (Authorised) Version (Alt)": "de4e12af7f28f599-02",
                    "Literal Standard Version": "01b29f4b342acc35-01",
                    "Revised Version 1885": "40072c4a5aba4022-01",
                    "Targum Onkelos Etheridge": "ec290b5045ff54a5-01",
                    "The English New Testament According to Family 35": "2f0fd81d7b85b923-01",
                    "The Holy Bible, American Standard Version": "06125adad2d5898a-01",
                    "The Orthodox Jewish Bible": "c89622d31b60c444-02",
                    "The Text-Critical English New Testament": "32339cf2f720ff8e-01",
                    "Translation for Translators": "66c22495370cdfc0-01",
                    "World English Bible": "9879dbb7cfe39e4d-01",
                    "World English Bible (Alt 2)": "9879dbb7cfe39e4d-02",
                    "World English Bible (Alt 3)": "9879dbb7cfe39e4d-03",
                    "World English Bible (Alt 4)": "9879dbb7cfe39e4d-04",
                    "World English Bible British Edition": "7142879509583d59-01",
                    "World English Bible British Edition (Alt 2)": "7142879509583d59-02",
                    "World English Bible British Edition (Alt 3)": "7142879509583d59-03",
                    "World English Bible British Edition (Alt 4)": "7142879509583d59-04",
                    "World English Bible Updated": "72f4e6dc683324df-01",
                    "World English Bible Updated (Alt 2)": "72f4e6dc683324df-02",
                    "World English Bible Updated (Alt 3)": "72f4e6dc683324df-03",
                    "World English Bible, American English Edition, without Strong's Numbers": "32664dc3288a28df-01",
                    "World English Bible, American English Edition, without Strong's Numbers (Alt 2)": "32664dc3288a28df-02",
                    "World English Bible, American English Edition, without Strong's Numbers (Alt 3)": "32664dc3288a28df-03",
                    "World Messianic Bible": "f72b840c855f362c-04",
                    "World Messianic Bible British Edition": "04da588535d2f823-04"
                },
                setTranslation(name, id) {
                    this.selectedTranslation = name;
                    this.selectedId = id;
                    // persist for next page load:
                    localStorage.setItem('bibleName', name);
                    localStorage.setItem('bibleId', id);
                },

                translationsFiltered(search) {
                    const s = search.toLowerCase();
                    return Object.fromEntries(
                    Object.entries(this.translations)
                        .filter(([name]) => name.toLowerCase().includes(s))
                    );
                }
            });
            const savedId   = localStorage.getItem('bibleId');
            const savedName = localStorage.getItem('bibleName');
            if (savedId && savedName) {
                Alpine.store('bible').selectedId           = savedId;
                Alpine.store('bible').selectedTranslation = savedName;
            }
        });

    </script>
</nav>
