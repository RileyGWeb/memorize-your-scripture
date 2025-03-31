<x-layouts.app>
    <x-content-card>
        <x-content-card-title title="Verse Memorization" />
        <x-divider />

        <div
            x-data="memTool({
                correctText: @js($correctText),
                displayFull: @js($displayFull),
                displayHidden: @js($displayHidden),
                reference: @js($reference),
                numLines: @js($numLines),
                lineHeightPx: @js($lineHeightPx),
            })"
            x-init="init()"
            class="flex flex-col gap-4"
        >
            <div class="p-4 border border-gray-300 rounded bg-gray-50">
                <p class="text-sm text-gray-600">
                    When you’ve got it down, click "Hide" and try to type it from memory.
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

                <div class="mt-3">
                    <template x-if="!hidden">
                        <x-button @click="hideVerse()">Hide It!</x-button>
                    </template>
                    <template x-if="hidden">
                        <x-button variant="outline" @click="showVerse()">Show It!</x-button>
                    </template>
                </div>
            </div>

            <div class="text-xl font-semibold">
                <span x-text="reference"></span>
            </div>

            <!-- Main container: if not hidden, show normal text; if hidden, show lined paper + borderless textarea -->
            <div class="relative border border-gray-300 bg-white rounded p-4">

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
                                       resize-none no-border py-0 px-3 overflow-hidden"
                                x-model="typedText"
                                @input="checkAccuracy"
                            ></textarea>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Accuracy Display -->
            <template x-if="hidden && !showCongrats">
                <div class="text-center">
                    <span>Accuracy: </span>
                    <span x-text="accuracy.toFixed(1) + '%'"></span>
                </div>
            </template>

            <!-- Congrats if done -->
            <template x-if="showCongrats">
                <div class="p-4 border border-green-400 bg-green-50 rounded text-center">
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
        </div>
    </x-content-card>

    <script>
    function memTool({ correctText, reference, displayFull, displayHidden }) {
        return {
            correctText,
            displayFull,
            displayHidden,
            reference,
            
            difficulty: 'easy',
            hidden: false,
            typedText: '',
            accuracy: 0,
            showCongrats: false,
            flashClass: '',

            // We'll measure lineHeight in px to space each line exactly
            lineHeightPx: 24, // default fallback
            numLines: 1,

            init() {
                // Measure how many lines the text needs with the same styling
                this.measureText();
            },

            hideVerse() {
                this.hidden = true;
                this.typedText = '';
                this.accuracy = 0;
                this.showCongrats = false;
                this.flashClass = '';
                this.$nextTick(() => {
                    // Focus the text area
                    this.$refs.typingArea.focus();
                });
            },

            showVerse() {
                this.hidden = false;
            },

            checkAccuracy() {
                const typed = this.typedText;
                let len = Math.min(typed.length, this.correctText.length);
                let matched = 0;
                for (let i = 0; i < len; i++) {
                    if (typed[i].toLowerCase() === this.correctText[i].toLowerCase()) {
                        matched++;
                    }
                }
                this.accuracy = (matched / this.correctText.length) * 100;

                // Flash
                if (typed.length > 0) {
                    let typedChar = typed[typed.length - 1];
                    let correctChar = (typed.length - 1 < this.correctText.length)
                        ? this.correctText[typed.length - 1]
                        : null;
                    if (correctChar && typedChar.toLowerCase() === correctChar.toLowerCase()) {
                        this.flashClass = 'flash-green';
                    } else {
                        this.flashClass = 'flash-red';
                    }
                }

                // Check if done
                if (this.accuracy >= this.requiredAccuracy()) {
                    this.showCongrats = true;
                }
            },

            requiredAccuracy() {
                if (this.difficulty === 'easy') return 80;
                if (this.difficulty === 'normal') return 95;
                if (this.difficulty === 'strict') return 100;
                return 80;
            },

            measureText() {
                this.$nextTick(() => {
                    let dummy = this.$refs.dummyText;
                    if (!dummy) return;
                    // Get computed lineHeight
                    let style = window.getComputedStyle(dummy);
                    let lineH = parseFloat(style.lineHeight) || 24;
                    this.lineHeightPx = lineH;

                    // Total height used by dummy
                    let totalH = dummy.scrollHeight;
                    // # lines
                    let lines = Math.ceil(totalH / lineH);
                    if (lines < 1) lines = 1;
                    this.numLines = lines;
                });
            },

            clearFlash() {
                this.flashClass = '';
            },

            resetAll() {
                this.hidden = false;
                this.typedText = '';
                this.accuracy = 0;
                this.showCongrats = false;
                this.measureText();
            },
        }
    }

    document.addEventListener('alpine:init', () => {
        Alpine.data('memTool', memTool);
    });
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
