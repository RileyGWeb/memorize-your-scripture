<x-layouts.app>
    <div x-data="memTool({
            segments: @js($segments),
            reference: @js($reference),
            lineHeightPx: @js($lineHeightPx),
            quizMode: @js($quizMode ?? false),
            quizData: @js($quizData ?? null),
            bibleTranslation: @js($bibleTranslation ?? 'NIV'),
        })" x-init="init()" class="space-y-4">
        
        @if(isset($quizMode) && $quizMode)
        <!-- Quiz Progress Indicator -->
        <x-content-card class="sticky top-2 z-20 bg-green-50 border-green-200">
            <div class="flex items-center justify-between px-4 py-3">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="font-semibold text-green-800">Quiz in Progress</span>
                    </div>
                    <div class="text-green-700">
                        <span class="font-medium">Verse {{ $currentIndex + 1 }} of {{ $totalVerses }}</span>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    @if(isset($quizData['results']) && count($quizData['results']) > 0)
                        @php
                            $totalScore = array_sum(array_column($quizData['results'], 'score'));
                            $averageScore = $totalScore / count($quizData['results']);
                        @endphp
                        <div class="text-green-700">
                            <span class="font-medium">Average: {{ round($averageScore) }}%</span>
                        </div>
                    @endif
                    <div class="text-green-700">
                        <span class="font-medium capitalize">{{ $quizData['difficulty'] ?? 'easy' }}</span>
                    </div>
                </div>
            </div>
            <!-- Progress Bar -->
            <div class="px-4 pb-3">
                <div class="w-full bg-green-200 rounded-full h-2">
                    @php
                        $progress = ($currentIndex / $totalVerses) * 100;
                    @endphp
                    <div class="bg-green-600 h-2 rounded-full transition-all duration-300" style="width: {{ $progress }}%"></div>
                </div>
            </div>
        </x-content-card>
        @endif

        <x-content-card class="sticky top-2 z-10 bg-white {{ isset($quizMode) && $quizMode ? 'mt-24' : '' }}">
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
                        <div class="relative flex items-center justify-center border-r border-stroke">
                            <svg class="w-16 h-16 m-2 transform -rotate-90">
                                <circle cx="32" cy="32" :r="radius" stroke="#e5e7eb" stroke-width="3" fill="transparent" />
                                <circle cx="32" cy="32" :r="radius" stroke="currentColor" :stroke-dasharray="circumference" :stroke-dashoffset="strokeOffset" stroke-width="3" fill="transparent" stroke-linecap="round" transition="stroke-dashoffset 0.3s ease" :class="progressColorBackground" />
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-sm font-semibold" :class="progressColorBackground" x-text="`${Math.round(overallAccuracy)}%`"></span>
                            </div>
                        </div>
                        <div class="w-full">
                            <div class="text-base text-text px-4 py-2">
                                <span x-text="`${typedChars} / ${totalChars} characters`"></span>
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
                            <button @click="hideVerse()" @focus="scrollToMainContent()" class="px-4 py-2">Hide It!</button>
                        </template>
                        <template x-if="hidden">
                            <button @click="showVerse()" @focus="scrollToMainContent()" class="px-4 py-2">Show It!</button>
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
                                            style="border: none; box-shadow: none;">
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
        function memTool({ segments, reference, lineHeightPx, bibleTranslation, quizMode, quizData }) {
            return {
                segments,
                reference,
                lineHeightPx,
                bibleTranslation: bibleTranslation || 'NIV',
                quizMode: quizMode || false,
                quizData: quizData || null,
                difficulty: 'easy',
                hidden: false,
                segmentStates: [],
                showCongrats: false,
                totalChars: 0,
                radius: 29,
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
                    
                    // Check if all segments meet required accuracy
                    if (this.checkAllSegments()) {
                        this.showCongrats = true;
                    }
                },
                checkAllSegments() {
                    return this.segmentStates.every(state => {
                        return state.accuracy >= this.requiredAccuracy();
                    });
                },
                requiredAccuracy() {
                    if (this.difficulty === 'easy') return 80;
                    if (this.difficulty === 'normal') return 95;
                    if (this.difficulty === 'strict') return 100;
                    return 95; // default
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
                },
                scrollToMainContent() {
                    this.$nextTick(() => {
                        const mainCard = this.$refs.mainContentCard;
                        if (mainCard) {
                            const stickyHeaderHeight = document.querySelector('.sticky.top-2')?.getBoundingClientRect().height || 0;
                            const rect = mainCard.getBoundingClientRect();
                            const offset = rect.top + window.pageYOffset - stickyHeaderHeight - 16;
                            window.scrollTo({
                                top: offset,
                                behavior: 'smooth'
                            });
                        }
                    });
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
