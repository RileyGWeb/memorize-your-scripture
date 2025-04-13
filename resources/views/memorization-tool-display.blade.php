<x-layouts.app>
    <div x-data="memTool({
            segments: @js($segments),
            reference: @js($reference),
            lineHeightPx: @js($lineHeightPx)
        })" x-init="init()">
        <x-content-card>
            <template x-if="hidden">
                <x-content-card-title title="Verse Memorization" subtitle="Type the verse(s) from memory. If you can't, show it again! But remember, you'll have to start over." />
            </template>
            <template x-if="!hidden">
                <x-content-card-title title="Verse Memorization" subtitle="When you've got it down, hide it and try to type it from memory." />
            </template>
            <x-divider />
            <div class="flex items-center justify-center h-20">
                <div class="flex items-center justify-center h-full w-full">
                    <form class="flex w-full h-full">
                        <div class="relative h-full" :class="{ 'w-full': hidden && difficulty === 'easy' }" x-show="(!hidden) || (difficulty === 'easy')">
                            <input x-model="difficulty" class="peer hidden" id="easy" type="radio" checked value="easy" />
                            <label for="easy" class="flex h-full justify-center cursor-pointer flex-col px-4 py-2.5 border-r border-stroke peer-checked:bg-darkBlue text-text peer-checked:text-white">
                                <span class="font-bold">Easy</span>
                                <span class="text-base">80% accuracy<br>required</span>
                            </label>
                        </div>
                        <div class="relative h-full" :class="{ 'w-full': hidden && difficulty === 'normal' }" x-show="(!hidden) || (difficulty === 'normal')">
                            <input x-model="difficulty" class="peer hidden" id="normal" type="radio" value="normal" />
                            <label for="normal" class="flex h-full justify-center cursor-pointer flex-col px-4 py-2.5 border-r border-stroke peer-checked:bg-darkBlue text-text peer-checked:text-white">
                                <span class="font-bold">Normal</span>
                                <span class="text-base">95% accuracy<br>required</span>
                            </label>
                        </div>
                        <div class="relative h-full" :class="{ 'w-full': hidden && difficulty === 'strict' }" x-show="(!hidden) || (difficulty === 'strict')">
                            <input x-model="difficulty" class="peer hidden" id="strict" type="radio" value="strict" />
                            <label for="strict" class="flex h-full justify-center cursor-pointer flex-col px-4 py-2.5 peer-checked:bg-darkBlue text-text peer-checked:text-white">
                                <span class="font-bold">Strict</span>
                                <span class="text-base">100% accuracy<br>required</span>
                            </label>
                        </div>
                    </form>
                </div>
                <template x-if="hidden">
                    <div class="flex items-center justify-center">
                        <div class="relative w-20 h-20 flex items-center justify-center border-r border-stroke">
                            <svg class="w-20 h-20 transform -rotate-90" viewBox="0 0 100 100">
                                <circle class="text-gray-300" cx="50" cy="50" r="40" fill="none" stroke="currentColor" stroke-width="3" />
                                <circle cx="50" cy="50" r="40" fill="none" stroke-width="3" :stroke="progressColor" stroke-linecap="round" :stroke-dasharray="circumference" :stroke-dashoffset="strokeOffset" />
                            </svg>
                            <div class="absolute flex items-center justify-center w-12 h-12 text-sm" :class="progressColorBackground">
                                <span x-text="overallAccuracy.toFixed(0) + '%'"></span>
                            </div>
                        </div>
                        <div class="w-20 h-20 flex items-center justify-center flex flex-col -space-y-3">
                            <span x-text="typedChars" class="relative -left-4 text-center"></span>
                            <span>/</span>
                            <span x-text="totalChars" class="relative left-4"></span>
                        </div>
                    </div>
                </template>
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
                            <button @click="showVerse()">Show It!</button>
                        </template>
                    </div>
                </div>
                <template x-if="!hidden">
                    <div class="leading-[1.5] text-lg whitespace-pre-wrap flex p-2">
                        <div x-html="buildDisplayFull()"></div>
                    </div>
                </template>
                <template x-if="hidden">
                    <template x-for="(seg, index) in segments" :key="index">
                        <div class="mb-4 relative border p-4">
                            <div class="absolute top-2 right-2 text-xs" x-text="segmentStates[index].accuracy.toFixed(0) + '%'"></div>
                            <div class="mb-2">
                                <sup x-text="seg.verse"></sup>
                            </div>
                            <div class="relative w-full" :style="'height:' + (lineHeightPx * seg.numLines) + 'px;'">
                                <template x-for="i in seg.numLines" :key="i">
                                    <div :style="'height:' + lineHeightPx + 'px; position:relative;'">
                                        <hr class="lined-hr" style="position:absolute; left:0; right:0; bottom:0;" />
                                    </div>
                                </template>
                                <textarea x-model="segmentStates[index].typedText" @input="checkAccuracy(index)" :rows="seg.numLines" class="absolute inset-0 w-full h-full text-lg leading-[1.5] bg-transparent outline-none resize-none no-border p-0 indent-2 overflow-hidden"></textarea>
                            </div>
                        </div>
                    </template>
                </template>
            </div>
        </x-content-card>
        <x-content-card>
            <template x-if="showCongrats">
                <div class="p-4 rounded-xl text-center">
                    <h2 class="text-xl font-semibold text-green-700 mb-2">Congrats!</h2>
                    <p class="text-green-700 mb-3">
                        You successfully memorized <span x-text="reference"></span>!
                    </p>
                    <x-button @click="resetAll()">Try Another</x-button>
                </div>
            </template>
            <div x-ref="dummyText" class="text-lg leading-[1.5]" style="visibility:hidden; position:absolute; top:-9999px; width:100%;">
                <span x-text="buildDisplayFull()"></span>
            </div>
        </x-content-card>
    </div>
    <script>
        function memTool({ segments, reference, numLines, lineHeightPx }) {
            return {
                segments,
                reference,
                numLines,
                lineHeightPx,
                difficulty: 'easy',
                hidden: false,
                segmentStates: [],
                showCongrats: false,
                flashClass: '',
                totalChars: 0,
                radius: 40,
                circumference: 0,
                init() {
                    this.segmentStates = this.segments.map(() => ({ typedText: '', accuracy: 0 }));
                    this.circumference = 2 * Math.PI * this.radius;
                    this.totalChars = this.segments.reduce((sum, seg) => sum + seg.text.length, 0);
                },
                hideVerse() {
                    // Clear any typed text and reset the state before hiding
                    this.segmentStates = this.segments.map(() => ({ typedText: '', accuracy: 0 }));
                    this.hidden = true;
                },
                showVerse() {
                    // Clear any typed text and reset the state before showing the verse again
                    this.segmentStates = this.segments.map(() => ({ typedText: '', accuracy: 0 }));
                    this.hidden = false;
                },
                buildDisplayFull() {
                    return this.segments
                        .map(seg => `<p class="m-2 text-xl font-light"><sup>${seg.verse}</sup> ${seg.text}</p>`)
                        .join("");
                },
                checkAccuracy(index) {
                    let typed = this.segmentStates[index].typedText;
                    let correct = this.segments[index].text;
                    let len = Math.min(typed.length, correct.length);
                    let matched = 0;
                    for (let i = 0; i < len; i++) {
                        if (typed[i].toLowerCase() === correct[i].toLowerCase()) {
                            matched++;
                        }
                    }
                    let acc = (matched / correct.length) * 100;
                    this.segmentStates[index].accuracy = acc;
                    this.checkAllSegments();
                },
                checkAllSegments() {
                    this.showCongrats = this.segmentStates.every((state, i) => {
                        let correct = this.segments[i].text;
                        return state.accuracy >= this.requiredAccuracy();
                    });
                },
                requiredAccuracy() {
                    if (this.difficulty === 'easy') return 80;
                    if (this.difficulty === 'normal') return 95;
                    if (this.difficulty === 'strict') return 100;
                    return 80;
                },
                resetAll() {
                    this.hidden = false;
                    this.segmentStates = this.segments.map(() => ({ typedText: '', accuracy: 0 }));
                    this.showCongrats = false;
                },
                get overallAccuracy() {
                    if (this.segmentStates.length === 0) return 0;
                    let sum = 0;
                    for (let i = 0; i < this.segmentStates.length; i++) {
                        sum += this.segmentStates[i].accuracy;
                    }
                    return sum / this.segmentStates.length;
                },
                get strokeOffset() {
                    const progress = this.overallAccuracy / 100;
                    return this.circumference - (progress * this.circumference);
                },
                get progressColor() {
                    let acc = this.overallAccuracy;
                    if (acc < 40) return '#ef4444';
                    if (acc <= 80) return '#facc15';
                    return '#22c55e';
                },
                get progressColorBackground() {
                    let acc = this.overallAccuracy;
                    if (acc < 40) return 'text-red-600';
                    if (acc <= 80) return 'text-yellow-600';
                    return 'text-green-600';
                },
                get typedChars() {
                    return this.segmentStates.reduce((sum, state) => sum + state.typedText.length, 0);
                }
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
