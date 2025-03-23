<x-layouts.app>
    <x-content-card>
        <x-content-card-title title="Verse Memorization" />
        <x-divider />

        <!-- Alpine Root -->
        <div 
            x-data="memorizeTool({
                verseText: @js($verseData['data'][0]['content'] ?? ''), 
                reference: @js($verseData['data'][0]['reference'] ?? 'Unknown Reference')
            })" 
            class="flex flex-col gap-4"
        >
            <!-- SECTION 1: Difficulty & Instructions -->
            <template x-if="!showCongrats">
                <div class="p-4 border border-gray-300 rounded bg-gray-50">
                    <h2 class="text-lg font-bold mb-2">Verse Preview</h2>
                    <p class="text-sm text-gray-600">
                        When you’ve got it down, hide it and try to type it from memory.
                    </p>

                    <div class="mt-2 flex items-center gap-4">
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" x-model="difficulty" value="easy" />
                            <span>Easy (80%+)</span>
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" x-model="difficulty" value="normal" />
                            <span>Normal (95%+)</span>
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" x-model="difficulty" value="strict" />
                            <span>Strict (100%)</span>
                        </label>
                    </div>
                </div>
            </template>

            <!-- SECTION 2: The Verse Display / Typing Area -->
            <div class="p-4 border border-gray-300 rounded bg-white">
                <!-- Reference -->
                <div class="text-xl font-semibold mb-2">
                    <span x-text="reference"></span>
                </div>

                <!-- The toggled 'Hide it' / 'Show it' button -->
                <template x-if="!hidden">
                    <x-button @click="hideVerse" class="mb-3">
                        Hide it!
                    </x-button>
                </template>
                <template x-if="hidden">
                    <x-button variant="outline" @click="showVerse" class="mb-3">
                        Show it!
                    </x-button>
                </template>

                <!-- Verse content or the "typing input" -->
                <div>
                    <template x-if="!hidden">
                        <div class="leading-relaxed" x-html="verseText"></div>
                    </template>

                    <template x-if="hidden">
                        <textarea
                            x-model="typedText"
                            class="w-full border border-gray-300 rounded p-2"
                            rows="5"
                            placeholder="Type the verse from memory..."
                            @input="checkAccuracy"
                        ></textarea>
                    </template>
                </div>
            </div>

            <!-- Show accuracy if hidden -->
            <template x-if="hidden && !showCongrats">
                <div class="mt-2 text-center">
                    <span>Accuracy: </span>
                    <span x-text="accuracy.toFixed(1) + '%'"></span>
                </div>
            </template>

            <!-- SECTION 3: Congrats / Next steps -->
            <template x-if="showCongrats">
                <div class="p-4 border border-green-400 bg-green-50 rounded text-center">
                    <h2 class="text-xl font-semibold text-green-700 mb-2">Congrats!</h2>
                    <p class="text-green-700 mb-3">
                        You successfully memorized <span x-text="reference"></span>!
                    </p>
                    <p>Added to your bank:</p>
                    <p class="font-semibold" x-text="reference"></p>

                    <div class="mt-4 flex gap-2 justify-center">
                        <x-button variant="outline" :href="route('memorization-tool.picker')">
                            Do Another
                        </x-button>
                        <x-button :href="route('home')">
                            Back Home
                        </x-button>
                    </div>
                </div>
            </template>
        </div>
    </x-content-card>

    <script>
        console.log('Memorization Tool initialized!')
    window.memorizeTool = function({ verseText, reference }) {
        return {
            verseText,
            reference,
            hidden: false,
            typedText: '',
            difficulty: 'easy',
            accuracy: 0,
            showCongrats: false,

            hideVerse() {
                this.hidden = true
                this.typedText = ''
                this.accuracy = 0

                // Optionally auto-focus the textarea after a tiny nextTick:
                this.$nextTick(() => {
                    const textarea = this.$root.querySelector('textarea')
                    if (textarea) textarea.focus()
                })
            },
            showVerse() {
                this.hidden = false
            },

            // Called on input event
            checkAccuracy() {
                const typed = this.typedText.trim()
                const actual = this.stripHtml(this.verseText).trim()

                // Simple approach: compare typed vs. actual chars
                // For better logic, you could strip punctuation, handle case, etc.
                this.accuracy = this.computeAccuracy(typed, actual)

                // Check if accuracy meets threshold
                if (this.accuracy >= this.requiredAccuracy()) {
                    // Mark as memorized (show "congrats")
                    this.showCongrats = true
                    // Save to DB
                    this.saveToMemoryBank()
                }
            },

            stripHtml(html) {
                let div = document.createElement('div')
                div.innerHTML = html
                return div.textContent || div.innerText || ''
            },

            computeAccuracy(typed, actual) {
                // Basic char-based accuracy
                // e.g. typedLength = 50, matchedChars = 45 => 90%
                // If you want a word-based approach, you'd split by whitespace & match words.
                let len = Math.max(actual.length, 1)
                let matched = 0
                for (let i = 0; i < typed.length; i++) {
                    if (i < actual.length && typed[i].toLowerCase() === actual[i].toLowerCase()) {
                        matched++
                    }
                }
                return (matched / len) * 100
            },

            requiredAccuracy() {
                if (this.difficulty === 'easy') return 80
                if (this.difficulty === 'normal') return 95
                if (this.difficulty === 'strict') return 100
                return 80
            },

            saveToMemoryBank() {
                // Call your API route or Livewire action.
                // Example fetch POST request to store memory:
                fetch("{{ route('memorization-tool.save') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        book: this.parseBook(reference),
                        chapter: this.parseChapter(reference),
                        verses: this.parseVerses(reference),
                        difficulty: this.difficulty,
                        accuracy_score: this.accuracy
                    })
                }).then(r => r.json())
                  .then(data => {
                      // Could handle errors, etc.
                      console.log('Saved to memory bank:', data)
                  })
                  .catch(err => console.error(err))
            },

            // Example naive reference parsing: "John 3:16"
            parseBook(ref) {
                // Quick approach: split by space, get first token
                return ref.split(' ')[0] ?? 'Unknown'
            },
            parseChapter(ref) {
                // e.g. "John 3:16" => '3'
                let match = ref.match(/\s(\d+):/)
                return match ? match[1] : 0
            },
            parseVerses(ref) {
                // e.g. "John 3:16-18,22"
                // Here we just return an array of verse numbers, ignoring ranges for brevity
                let match = ref.match(/:(.*)/)
                if (!match) return []
                // "16-18,22" => parse logic:
                let chunk = match[1]
                let allVerses = []
                let segments = chunk.split(',')
                segments.forEach(seg => {
                    if (seg.includes('-')) {
                        let [start, end] = seg.split('-').map(Number)
                        for (let v = start; v <= end; v++) {
                            allVerses.push(v)
                        }
                    } else {
                        allVerses.push(Number(seg))
                    }
                })
                return allVerses
            }
        }
    }
    </script>
</x-layouts.app>
