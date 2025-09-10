<x-layouts.app>
    <div x-data="memTool({
            segments: @js($segments),
            reference: @js($reference),
            lineHeightPx: @js($lineHeightPx),
        })" x-init="init()" class="space-y-4">
        <x-content-card class="sticky top-2 z-10 bg-white">
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
                        <div class="relative h-full w-full" :class="{ 'w-full': hidden && difficulty === 'easy' }" x-show="(!hidden) || (difficulty === 'easy')">
                            <input x-model="difficulty" class="peer hidden" id="easy" type="radio" checked value="easy" />
                            <label for="easy" class="flex h-full justify-center cursor-pointer flex-col px-4 py-2.5 border-r border-stroke peer-checked:bg-darkBlue text-text peer-checked:text-white">
                                <span class="font-bold">Easy</span>
                                <span class="text-base">80% accuracy<br>required</span>
                            </label>
                        </div>
                        <div class="relative h-full w-full" :class="{ 'w-full': hidden && difficulty === 'normal' }" x-show="(!hidden) || (difficulty === 'normal')">
                            <input x-model="difficulty" class="peer hidden" id="normal" type="radio" value="normal" />
                            <label for="normal" class="flex h-full justify-center cursor-pointer flex-col px-4 py-2.5 border-r border-stroke peer-checked:bg-darkBlue text-text peer-checked:text-white">
                                <span class="font-bold">Normal</span>
                                <span class="text-base">95% accuracy<br>required</span>
                            </label>
                        </div>
                        <div class="relative h-full w-full" :class="{ 'w-full': hidden && difficulty === 'strict' }" x-show="(!hidden) || (difficulty === 'strict')">
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
                            <svg class="w-20 h-20 transform -rotate-90">
                                <circle cx="40" cy="40" :r="radius" stroke="#e5e7eb" stroke-width="8" fill="transparent" />
                                <circle cx="40" cy="40" :r="radius" stroke="currentColor" :stroke-dasharray="circumference" :stroke-dashoffset="strokeOffset" stroke-width="8" fill="transparent" stroke-linecap="round" transition="stroke-dashoffset 0.3s ease" :class="progressColorBackground" />
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-lg font-semibold" :class="progressColorBackground" x-text="`${Math.round(overallAccuracy)}%`"></span>
                            </div>
                        </div>
                        <div class="w-full">
                            <div class="text-base text-text px-4 py-2">
                                <span x-text="`${typedChars} / ${totalChars} characters`"></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3 border-l border-stroke">
                                <div class="h-3 rounded-full transition-all duration-300" :style="`width: ${(typedChars / totalChars) * 100}%; background-color: ${progressColor};`"></div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </x-content-card>
        <x-content-card>
            <x-content-card-button href="/memorization-tool" text="Change verse" icon="arrow-narrow-left" iconSize="lg" />
        </x-content-card>
        <x-content-card x-ref="mainContentCard">
            <div :class="{ 'rounded-xl shadow-xl shadow-green-400': showCongrats }">
                <div class="flex border-b border-stroke">
                    <div class="font-semibold grow px-4 py-2">
                        <span x-text="reference"></span>
                    </div>
                    <div class="border-l border-stroke flex items-center font-semibold">
                        <template x-if="!hidden">
                            <button @click="hideVerse()" class="px-4 py-2">Hide It!</button>
                        </template>
                        <template x-if="hidden">
                            <button @click="showVerse()" class="px-4 py-2">Show It!</button>
                        </template>
                    </div>
                </div>
                <template x-if="!hidden">
                    <div class="leading-[1.5] text-lg whitespace-pre-wrap flex p-2">
                        <div x-html="buildDisplayFull()"></div>
                    </div>
                </template>
                <template x-if="hidden">
                    <div class="p-4">
                        <template x-for="(segment, index) in segments" :key="index">
                            <div class="space-y-4">
                                <div :id="'segment-' + index" class="relative" x-data="{ flashClass: '' }">
                                    <sup class="text-base font-semibold text-gray-500" x-text="segment.verse"></sup>
                                    <div class="relative" :style="`height: ${segment.numLines * lineHeightPx}px;`">
                                        <div x-ref="dummyText" class="text-lg leading-[1.5]" style="visibility:hidden; position:absolute; top:-9999px; width:100%;" x-html="segmentStates[index].typedText.replace(/\\n/g, '<br>')"></div>
                                        <hr class="lined-hr" style="margin: 0; border: none; border-bottom: 1px solid #ccc; height: 0; position: absolute; top: 24px; width: 100%;" />
                                        <hr class="lined-hr" style="margin: 0; border: none; border-bottom: 1px solid #ccc; height: 0; position: absolute; top: 48px; width: 100%;" x-show="segment.numLines > 1" />
                                        <hr class="lined-hr" style="margin: 0; border: none; border-bottom: 1px solid #ccc; height: 0; position: absolute; top: 72px; width: 100%;" x-show="segment.numLines > 2" />
                                        <hr class="lined-hr" style="margin: 0; border: none; border-bottom: 1px solid #ccc; height: 0; position: absolute; top: 96px; width: 100%;" x-show="segment.numLines > 3" />
                                        <hr class="lined-hr" style="margin: 0; border: none; border-bottom: 1px solid #ccc; height: 0; position: absolute; top: 120px; width: 100%;" x-show="segment.numLines > 4" />
                                        <hr class="lined-hr" style="margin: 0; border: none; border-bottom: 1px solid #ccc; height: 0; position: absolute; top: 144px; width: 100%;" x-show="segment.numLines > 5" />
                                        <hr class="lined-hr" style="margin: 0; border: none; border-bottom: 1px solid #ccc; height: 0; position: absolute; top: 168px; width: 100%;" x-show="segment.numLines > 6" />
                                        <textarea 
                                            x-model="segmentStates[index].typedText" 
                                            @input="checkAccuracy(index)"
                                            placeholder=""
                                            class="absolute inset-0 w-full h-full text-lg leading-[1.5] bg-transparent outline-none resize-none"
                                            style="color: transparent; caret-color: black; border: none; box-shadow: none;">
                                        </textarea>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </x-content-card>
    </div>
    <script>
function memTool({ segments, reference, lineHeightPx, bibleTranslation }) {
    return {
        segments,
        reference,
        lineHeightPx,
        bibleTranslation: bibleTranslation || 'NIV',
        difficulty: 'easy',
        hidden: false,
        segmentStates: [],
        showCongrats: false,
        totalChars: 0,
        radius: 40,
        circumference: 0,
        saved: false,
        init() {
            this.segmentStates = this.segments.map(() => ({ typedText: '', accuracy: 0 }));
            this.circumference = 2 * Math.PI * this.radius;
            this.totalChars = this.segments.reduce((sum, seg) => sum + seg.text.length, 0);
        },
        hideVerse() {
            this.segmentStates = this.segments.map(() => ({ typedText: '', accuracy: 0 }));
            this.saved = false;
            this.hidden = true;
        },
        showVerse() {
            this.segmentStates = this.segments.map(() => ({ typedText: '', accuracy: 0 }));
            this.saved = false;
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
            let matched = 0;
            let len = Math.min(typed.length, correct.length);
            for (let i = 0; i < len; i++) {
                if (typed[i].toLowerCase() === correct[i].toLowerCase()) {
                    matched++;
                }
            }
            let acc = (matched / correct.length) * 100;
            this.segmentStates[index].accuracy = acc;
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
        .lined-hr {
            margin: 0;
            border: none;
            border-bottom: 1px solid #ccc;
            height: 0;
        }
    </style>
</x-layouts.app>
