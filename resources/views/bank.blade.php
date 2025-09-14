<x-layouts.app>
    <div x-data="memoryBank({ items: @js($items) })" class="w-full space-y-4">

        <x-content-card>
            <x-content-card-title title="Your Memory Bank" subtitle="See everything you've memorized. Search by reference (John 3:16) or by verse text (For God so loved the world...)" />
            <x-divider />
            <x-content-card-button href="/" text="Back home" icon="arrow-narrow-left" iconSize="lg" wire:navigate />
            <x-divider />
        </x-content-card>

        <!-- Wrap the search field and the grid in one container with x-data -->
        <div class="w-full">
            <!-- Grid displaying items -->
            <x-content-card>
                @auth
                <div class="px-2 border-b border-border">
                    <input type="text" x-model="search" placeholder="Search..." class="w-full p-2 bg-transparent border-none focus:outline-none focus:ring-0" />
                </div>
                <div class="grid grid-cols-2 w-full">
                    <template x-for="(item, index) in items" :key="item.id">
                        <div
                            class="p-4 cursor-pointer hover:bg-gray-50 transition"
                            :class="{
                                'border-r': index % 2 === 0,
                                'border-b': index < items.length - 1
                            }"
                            @click="showModal(item)">
                            <!-- Book, Chapter, Verses, Difficulty, Date -->
                            <div class="font-semibold text-sm">
                                <span x-text="formatReference(item.book, item.chapter, item.verses)"></span>
                            </div>
                            <div class="text-xs text-gray-500 flex justify-between mt-1">
                                <span x-text="item.difficulty"></span>
                                <span x-text="formatDate(item.memorized_at)"></span>
                            </div>
                        </div>
                    </template>
                </div>
                @else
                <div class="p-4">
                    <p class="text-center text-gray-500">Whoops, you're not logged in!</p>
                    <p class="text-center text-gray-500">Sign up or register, and your memorized verses will show up here.</p>
                </div>
                @endauth
            </x-content-card>
        </div>

        <!-- Modal and Alpine store remain unchanged -->
        <div 
            x-data="{ open: false }" 
            x-show="open"
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
            x-cloak
            @memorybank-show-modal.window="open = true"
            @memorybank-hide-modal.window="open = false">
            <div
                class="bg-white w-11/12 max-w-md p-6 rounded shadow relative"
                @click.outside="open = false">
                <button 
                    class="absolute top-2 right-2 text-gray-400 hover:text-gray-600"
                    @click="open = false">
                    &times;
                </button>
                <div class="mb-4">
                    <h2 class="text-lg font-bold" x-text="$store.mb.selectedItemRef"></h2>
                    <p class="text-sm text-gray-500 flex justify-between mt-1">
                        <span x-text="'Difficulty: ' + $store.mb.selectedItemDifficulty"></span>
                        <span>Memorized on: <span x-text="$store.mb.selectedItemDate"></span></span>
                        <span x-text="'Translation: ' + $store.mb.selectedItemVersion"></span>
                    </p>
                </div>
                <div class="leading-relaxed text-gray-800 border-t pt-4">
                    <p>Full verse:</p>
                    <p x-text="$store.mb.selectedItemText"></p>
                </div>
                <div class="leading-relaxed text-sm text-gray-700 mt-2">
                    <p>You typed:</p>
                    <p x-text="$store.mb.selectedUserText"></p>
                </div>
                <div class="mt-4 flex gap-2 justify-end">
                    <x-button variant="outline" @click="shareItem()">Share</x-button>
                    <x-button @click="open = false">Close</x-button>
                </div>
            </div>
        </div>

    </div>

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('mb', {
            selectedItemRef: '',
            selectedItemDifficulty: '',
            selectedItemDate: '',
            selectedItemText: '',
        });
    });

    function memoryBank({ items }) {
        return {
            originalItems: items,
            items,
            search: "",
            searchInVerseText: false,
            
            init() {
                this.$watch('search', (value) => {
                    if (!value) {
                        this.items = this.originalItems;
                        this.searchInVerseText = false;
                        return;
                    }
                    let filtered = this.originalItems.filter(item => {
                        let ref = this.formatReference(item.book, item.chapter, item.verses);
                        return ref.toLowerCase().includes(value.toLowerCase());
                    });
                    if (filtered.length > 0) {
                        this.items = filtered;
                        this.searchInVerseText = false;
                    } else {
                        this.searchInVerseText = true;
                        fetch(`{{ route('memory-bank.search-verses') }}?q=` + encodeURIComponent(value))
                            .then(response => response.json())
                            .then(data => {
                                if (data.error) {
                                    console.error(data.error);
                                } else {
                                    this.items = data.items;
                                }
                            })
                            .catch(err => {
                                console.error(err);
                            });
                    }
                });
            },
            
            showModal(item) {
                const ref = this.formatReference(item.book, item.chapter, item.verses);
                this.$store.mb.selectedItemRef = ref;
                this.$store.mb.selectedItemDifficulty = item.difficulty;
                this.$store.mb.selectedItemVersion = item.bible_translation;
                this.$store.mb.selectedItemDate = this.formatDate(item.memorized_at);
                this.$store.mb.selectedUserText = item.user_text;
                this.$store.mb.selectedItemText = 'Loading verse...';

                let verses = Array.isArray(item.verses)
                    ? item.verses
                    : JSON.parse(item.verses || '[]');
                fetch(`{{ route('memory-bank.fetch-verse') }}?` + new URLSearchParams({
                    book: item.book,
                    chapter: item.chapter,
                    bible_translation: item.bible_translation || 'de4e12af7f28f599-02',
                    ...verses.reduce((acc, v, i) => {
                        acc[`verses[${i}]`] = v;
                        return acc;
                    }, {})
                }))
                .then(r => r.json())
                .then(data => {
                    if (data.error) {
                        this.$store.mb.selectedItemText = 'Error fetching verse text.';
                    } else {
                        this.$store.mb.selectedItemText = data.verse_text;
                    }
                })
                .catch(err => {
                    console.error(err);
                    this.$store.mb.selectedItemText = 'An error occurred.';
                });
                this.$dispatch('memorybank-show-modal');
            },

            formatReference(book, chapter, versesJson) {
                if (!versesJson) return `${book} ${chapter}`;
                let verses = Array.isArray(versesJson) ? versesJson : JSON.parse(versesJson || '[]');
                if (verses.length === 1) {
                    return `${book} ${chapter}:${verses[0]}`;
                } else {
                    verses.sort((a, b) => a - b);
                    let first = verses[0];
                    let last = verses[verses.length - 1];
                    let continuous = true;
                    for (let i = 0; i < verses.length - 1; i++) {
                        if (verses[i+1] !== verses[i] + 1) {
                            continuous = false;
                            break;
                        }
                    }
                    if (continuous) {
                        return `${book} ${chapter}:${first}-${last}`;
                    } else {
                        return `${book} ${chapter}:${verses.join(',')}`;
                    }
                }
            },

            formatDate(dateStr) {
                if (!dateStr) return '';
                const d = new Date(dateStr);
                return d.toLocaleDateString([], { year: 'numeric', month: 'short', day: 'numeric' });
            },

            shareItem() {
                const textToShare = `${this.$store.mb.selectedItemRef}\n\n${this.$store.mb.selectedItemText}`;
                if (navigator.share) {
                    navigator.share({ text: textToShare })
                        .catch(err => console.log('Share canceled', err));
                } else {
                    navigator.clipboard.writeText(textToShare)
                        .then(() => alert('Copied to clipboard!'))
                        .catch(() => alert('Failed to copy text.'));
                }
            },
        }
    }
    </script>
</x-layouts.app>
