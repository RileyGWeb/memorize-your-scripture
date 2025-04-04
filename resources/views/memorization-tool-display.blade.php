<x-layouts.app>
    <div 
            x-data="memTool({
                correctText: @js($correctText),
                displayFull: @js($displayFull),
                displayHidden: @js($displayHidden),
                reference: @js($reference),
                numLines: @js($numLines),
                lineHeightPx: @js($lineHeightPx),
            })"
            x-init="init()">
    <x-content-card>
        <x-content-card-title title="Verse Memorization" subtitle="When you've got it down, hide it and try to type it from memory." />
        <x-divider />

        <div class="flex items-center justify-center">
            <form class="flex w-full">

                <!-- EASY -->
                <div class="relative"
                    :class="{ 'w-full': hidden && difficulty === 'easy' }"
                    x-show="(!hidden) || (difficulty === 'easy')">
                    <input x-model="difficulty" 
                        class="peer hidden" 
                        id="easy" 
                        type="radio" 
                        checked 
                        value="easy" />
                    <label for="easy"
                        class="flex cursor-pointer flex-col px-4 py-2.5 border-r border-stroke
                            peer-checked:bg-darkBlue text-text peer-checked:text-white">
                        <span class="font-bold">Easy</span>
                        <span class="text-base">80% accuracy<br>required</span>
                    </label>
                </div>

                <!-- NORMAL -->
                <div class="relative"
                    :class="{ 'w-full': hidden && difficulty === 'normal' }"
                    x-show="(!hidden) || (difficulty === 'normal')">
                    <input x-model="difficulty" 
                        class="peer hidden" 
                        id="normal" 
                        type="radio" 
                        value="normal" />
                    <label for="normal"
                        class="flex cursor-pointer flex-col px-4 py-2.5 border-r border-stroke
                            peer-checked:bg-darkBlue text-text peer-checked:text-white">
                        <span class="font-bold">Normal</span>
                        <span class="text-base">95% accuracy<br>required</span>
                    </label>
                </div>

                <!-- STRICT -->
                <div class="relative"
                    :class="{ 'w-full': hidden && difficulty === 'strict' }"
                    x-show="(!hidden) || (difficulty === 'strict')">
                    <input x-model="difficulty" 
                        class="peer hidden" 
                        id="strict" 
                        type="radio" 
                        value="strict" />
                    <label for="strict"
                        class="flex cursor-pointer flex-col px-4 py-2.5
                            peer-checked:bg-darkBlue text-text peer-checked:text-white">
                        <span class="font-bold">Strict</span>
                        <span class="text-base">100% accuracy<br>required</span>
                    </label>
                </div>

                <!-- Stats row appears only when verse is hidden -->
                <template x-if="hidden">
                    <div class="flex items-center justify-center">
                        
                        <!-- Accuracy Circle -->
                        <div class="relative w-20 h-20 flex items-center justify-center border-r border-stroke">
                            <!-- We'll use an SVG ring for the circular progress -->
                            <svg class="w-20 h-20 transform -rotate-90" viewBox="0 0 100 100">
                                <!-- Background circle -->
                                <circle
                                    class="text-gray-300"
                                    cx="50" cy="50" r="40"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="3"
                                />
                                <!-- Progress circle -->
                                <circle
                                    cx="50" cy="50" r="40"
                                    fill="none"
                                    stroke-width="3"
                                    :stroke="progressColor"
                                    stroke-linecap="round"
                                    :stroke-dasharray="circumference"
                                    :stroke-dashoffset="strokeOffset"
                                />
                            </svg>
                            
                            <!-- Display accuracy inside the circle -->
                            <div class="absolute flex items-center justify-center w-12 h-12 text-sm"
                                :class="progressColorBackground">
                                <span x-text="accuracy.toFixed(0) + '%'"></span>
                            </div>
                        </div>

                        <!-- Character Counter -->
                        <div class="w-20 h-20 flex items-center justify-center flex flex-col -space-y-3">
                            <span x-text="typedChars" class="relative -left-3"></span>
                            <span>/</span>
                            <span x-text="totalChars" class="relative left-4"></span>
                        </div>
                    </div>
                </template>


            </form>
        </div>
    </x-content-card>

    <x-content-card>
        <x-content-card-button href="/memorization-tool" text="Change verse" icon="arrow-back" iconSize="md" />
    </x-content-card>

    <x-content-card>
        <div :class="{ 'rounded-xl shadow-xl shadow-green-400': showCongrats }">
            
            <div class="flex border-b border-stroke">
                <div class="font-semibold grow px-4 py-2">
                    <span x-text="reference"></span>
                </div>
                <div class="px-4 py-2 border-l border-stroke flex items-center font-semibold">
                    <template x-if="!hidden">
                        <button @click="hideVerse()">Hide It!</button>
                    </template>
                    <template x-if="hidden">
                        <button variant="outline" @click="showVerse()">Show It!</button>
                    </template>
                </div>
            </div>


            <!-- Main container: if not hidden, show normal text; if hidden, show lined paper + borderless textarea -->
            <div class="relative p-4 pb-6">

                <!-- Show normal text if not hidden -->
                <template x-if="!hidden">
                    <div class="leading-[1.5] text-lg whitespace-pre-wrap flex">
                        <!-- Use x-html so we can render <sup> tags -->
                        <div x-html="displayFull"></div>
                    </div>
                </template>

                <!-- Show lined paper if hidden -->
                <template x-if="hidden">
                    <div :class="flashClass" @transitionend="clearFlash()" class="relative">
                        <div class="leading-[1.5] text-lg whitespace-pre-wrap flex absolute">
                            <div x-html="displayHidden"></div>
                        </div>
                        <!-- A container for the repeated lines (hr). 
                                We'll explicitly set its height to match how many lines the text needs, 
                                times the lineHeight (in px). -->
                        <div class="relative w-full" 
                                style="height: {{ $numLines * $lineHeightPx }}px;">
                            
                            <!-- Each line is a div with an absolutely positioned <hr> in the middle -->
                            @for($i = 0; $i < $numLines; $i++)
                                <div style="height: {{ $lineHeightPx }}px; position: relative;">
                                    <hr class="lined-hr"
                                        style="position:absolute; left:0; right:0; bottom:0;"/>
                                </div>
                            @endfor
                            
                            <!-- The text area sits absolutely on top, covering all lines. -->
                            <textarea
                                x-ref="typingArea"
                                rows="{{ $numLines }}"
                                class="absolute inset-0 w-full h-full text-lg 
                                        leading-[1.5] bg-transparent outline-none 
                                        resize-none no-border p-0 indent-2 overflow-hidden"
                                x-model="typedText"
                                @input="checkAccuracy"
                            ></textarea>
                        </div>
                    </div>
                </template>
            </div>

        </div>
    </x-content-card>

    <x-content-card>
        <!-- Congrats if done -->
        <template x-if="showCongrats">
            <div class="p-4 rounded-xl text-center">
                <h2 class="text-xl font-semibold text-green-700 mb-2">Congrats!</h2>
                <p class="text-green-700 mb-3">
                    You successfully memorized <span x-text="reference"></span>!
                </p>
                <x-button @click="resetAll">Try Another</x-button>
            </div>
        </template>

        <!-- Hidden dummy container for measuring text lines -->
        <div x-ref="dummyText"
                class="text-lg leading-[1.5]"
                style="visibility:hidden; position:absolute; top:-9999px; width:100%;">
            <span x-text="correctText"></span>
        </div>
    </x-content-card>
</div>

    <script>
        function memTool({ 
        correctText,         // The no-numbers verse text used for comparing accuracy
        displayFull,         // Full text (with <sup>numbers</sup>) for "shown" mode
        displayHidden,       // Full text but with underscores, plus <sup>numbers</sup>, for "hidden" mode
        reference,           // e.g. "John 3:16"
        numLines,            // estimated lines for the lined-paper approach
        lineHeightPx         // line height in pixels
        }) {
        return {
            // Props
            correctText,
            displayFull,
            displayHidden,
            reference,
            numLines,
            lineHeightPx,

            // Reactive State
            difficulty: 'easy',    // user-chosen difficulty
            hidden: false,         // toggles "Hide it!" vs. "Show it!"
            typedText: '',         // user's typed input
            accuracy: 0,           // e.g. 87.5
            showCongrats: false,
            flashClass: '',        // toggles green/red flash
            typedChars: 0,         // number of correct typed characters
            totalChars: 0,         // total length of correctText

            // Circular ring config
            radius: 40,            // circle radius
            circumference: 0,      // 2 * Math.PI * radius, set in init

            // Lifecycle
            init() {
            // Called after Alpine sets up the component
            this.measureText();              // measure lines if you do your line-based approach
            this.circumference = 2 * Math.PI * this.radius;
            this.totalChars = this.correctText.length;
            },

            // The user toggles to hidden mode
            hideVerse() {
            this.hidden = true;
            this.typedText = '';
            this.accuracy = 0;
            this.typedChars = 0;
            this.showCongrats = false;
            this.flashClass = '';
            
            this.$nextTick(() => {
                // Once hidden, auto-focus the textarea
                this.$refs.typingArea?.focus();
            });
            },

            // The user toggles to show mode
            showVerse() {
            this.hidden = false;
            },

            // Called on each input event in the textarea
            checkAccuracy() {
            const typed = this.typedText;
            let len = Math.min(typed.length, this.correctText.length);
            let matched = 0;

            // Count how many typed chars match the correct text
            for (let i = 0; i < len; i++) {
                if (typed[i].toLowerCase() === this.correctText[i].toLowerCase()) {
                matched++;
                }
            }
            // Set accuracy
            this.accuracy = (matched / this.correctText.length) * 100;
            // If you want typedChars to be total typed, not just matched, do: this.typedChars = typed.length;
            // If you want typedChars to be only correct, do:
            this.typedChars = matched;

            // Flash red/green on the last typed char
            if (typed.length > 0) {
                const typedChar = typed[typed.length - 1];
                const correctChar = (typed.length - 1 < this.correctText.length)
                ? this.correctText[typed.length - 1]
                : null;

                if (correctChar && typedChar.toLowerCase() === correctChar.toLowerCase()) {
                this.flashClass = 'flash-green'; // triggers green flash
                } else {
                this.flashClass = 'flash-red';   // triggers red flash
                }
            }

            // If user meets or exceeds required accuracy, show "congrats"
            if (this.accuracy >= this.requiredAccuracy()) {
                this.showCongrats = true;
            }
            },

            // The required accuracy depends on the chosen difficulty
            requiredAccuracy() {
            if (this.difficulty === 'easy') return 80;
            if (this.difficulty === 'normal') return 95;
            if (this.difficulty === 'strict') return 100;
            return 80;
            },

            // Called after CSS transition ends for the flash
            clearFlash() {
            this.flashClass = '';
            },

            // Recalculate how many lines the verse might need
            measureText() {
            this.$nextTick(() => {
                let dummy = this.$refs.dummyText;
                if (!dummy) return;

                let style = window.getComputedStyle(dummy);
                let lineH = parseFloat(style.lineHeight) || 24;
                this.lineHeightPx = lineH;

                let totalH = dummy.scrollHeight;
                let lines = Math.ceil(totalH / lineH);
                if (lines < 1) lines = 1;
                this.numLines = lines;
            });
            },

            // If the user "completes" or resets
            resetAll() {
            this.hidden = false;
            this.typedText = '';
            this.accuracy = 0;
            this.typedChars = 0;
            this.showCongrats = false;
            this.measureText();
            },

            // Computed properties for the circular ring
            get strokeOffset() {
            // fraction from 0..1
            const progress = this.accuracy / 100;
            return this.circumference - (progress * this.circumference);
            },
            get progressColor() {
            // threshold-based color from red to green
            if (this.accuracy < 40) return '#ef4444';   // or 'red'
            if (this.accuracy <= 80) return '#facc15';   // or 'yellow'
            return '#22c55e';                           // or 'green'
            },
            get progressColorBackground() {
            // if you want a BG color class behind the ring text
            if (this.accuracy < 40) return 'text-red-600';
            if (this.accuracy <= 80) return 'text-yellow-600';
            return 'text-green-600';
            }
        }
    }
    </script>

    <style>
    .flash-green {
        background-color: #9ae69a;
        transition: background-color 0.3s ease;
    }
    .flash-red {
        background-color: #efa1a1;
        transition: background-color 0.3s ease;
    }
    .no-border {
        border: none !important;
        box-shadow: none !important;
    }
    .lined-hr {
        margin: 0;
        border: none;
        border-bottom: 1px solid #ccc;
        height: 0;
    }
    </style>
</x-layouts.app>
