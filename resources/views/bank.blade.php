{{-- resources/views/bank.blade.php --}}
<x-layouts.app>
    <x-content-card>
        <x-content-card-title title="Your Memory Bank" />

        <x-divider />

        <!-- Alpine Data Component -->
        <div 
            x-data="memoryBank({
                items: @js($items),
            })"
            class="grid grid-cols-2"
        >
            <!-- Grid of Memorized Items -->
            <template x-for="(item, index) in items" :key="item.id">
                <div
                    class="p-4 border cursor-pointer hover:bg-gray-50 transition"
                    @click="showModal(item)"
                >
                    <!-- Book, Chapter, Verses, Difficulty, Date -->
                    <div class="font-semibold text-sm">
                        <!-- e.g. "John 3:16-18" -->
                        <span x-text="formatReference(item.book, item.chapter, item.verses)"></span>
                    </div>
                    <div class="text-xs text-gray-500 flex justify-between mt-1">
                        <span x-text="'Difficulty: ' + item.difficulty"></span>
                        <span x-text="formatDate(item.memorized_at)"></span>
                    </div>

                    <!-- Truncated verse text -->
                    <p class="mt-2 text-sm text-gray-700 line-clamp-3"
                       x-text="truncate(item.verse_text, 100)">
                    </p>
                </div>
            </template>
        </div>
    </x-content-card>

    <!-- Modal -->
    <div 
        x-data="{ open: false }" 
        x-show="open"
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
        x-cloak
        @memorybank-show-modal.window="open = true"
        @memorybank-hide-modal.window="open = false"
    >
        <div
            class="bg-white w-11/12 max-w-md p-6 rounded shadow relative"
            @click.outside="open = false"
        >
            <!-- Close Button -->
            <button 
                class="absolute top-2 right-2 text-gray-400 hover:text-gray-600"
                @click="open = false"
            >
                &times;
            </button>

            <div class="mb-4">
                <!-- Reference & Metadata -->
                <h2 class="text-lg font-bold" x-text="$store.mb.selectedItemRef"></h2>
                <p class="text-sm text-gray-500 flex justify-between mt-1">
                    <span x-text="'Difficulty: ' + $store.mb.selectedItemDifficulty"></span>
                    <span>Memorized on: <span x-text="$store.mb.selectedItemDate"></span></spam>
                </p>
            </div>

            <div class="leading-relaxed text-gray-800 border-t pt-4">
                <!-- Full Verse Text -->
                <p x-text="$store.mb.selectedItemText"></p>
            </div>

            <!-- Share / Other Actions -->
            <div class="mt-4 flex gap-2 justify-end">
                <x-button variant="outline" @click="shareItem()">Share</x-button>
                <x-button @click="open = false">Close</x-button>
            </div>
        </div>
    </div>

    <!-- Alpine Store & Script -->
    <script>
    document.addEventListener('alpine:init', () => {
        // Global Alpine store for selected memory item details
        Alpine.store('mb', {
            selectedItemRef: '',
            selectedItemDifficulty: '',
            selectedItemDate: '',
            selectedItemText: '',
        });
    });

    function memoryBank({ items }) {
        return {
            items,

            showModal(item) {
                // Set reference, difficulty, date, etc.
                const ref = this.formatReference(item.book, item.chapter, item.verses);
                this.$store.mb.selectedItemRef = ref;
                this.$store.mb.selectedItemDifficulty = item.difficulty;
                this.$store.mb.selectedItemDate = this.formatDate(item.memorized_at);
                
                // Show "Loading..." in the modal while we fetch from the server
                this.$store.mb.selectedItemText = 'Loading verse...';

                // Convert item.verses from JSON to array if needed
                let verses = Array.isArray(item.verses)
                    ? item.verses
                    : JSON.parse(item.verses || '[]');
                
                // Make a GET request to our new route, passing book/chapter/verses
                fetch(`{{ route('memory-bank.fetch-verse') }}?` + new URLSearchParams({
                    book: item.book,
                    chapter: item.chapter,
                    // The verses param must be repeated for each entry, e.g. verses[]=16&verses[]=17
                    // We'll do that manually:
                    ...verses.reduce((acc, v, i) => {
                    acc['verses['+i+']'] = v;
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

                // Finally, open the modal
                this.$dispatch('memorybank-show-modal');
            },

            formatReference(book, chapter, versesJson) {
                if (!versesJson) return `${book} ${chapter}`;
                
                // If 'verses' is stored as JSON, parse it
                let verses = Array.isArray(versesJson) ? versesJson : JSON.parse(versesJson);

                // For a simple approach:
                // If it's a list of single verses, e.g. [16,17,18], show e.g. "3:16-18"
                // If it's just one verse, "3:16"
                // This is simplistic – modify as needed if your data is stored as ranges.
                if (verses.length === 1) {
                    return `${book} ${chapter}:${verses[0]}`;
                } else {
                    // Sort them in case they're out of order
                    verses.sort((a, b) => a - b);
                    let first = verses[0];
                    let last = verses[verses.length - 1];

                    // If the array is continuous, show "16-18"
                    // Otherwise, join them with commas
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

            truncate(text, length) {
                if (!text) return '';
                if (text.length <= length) return text;
                return text.slice(0, length) + '...';
            },

            shareItem() {
                // Attempt to use Web Share API, fallback to clipboard
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
