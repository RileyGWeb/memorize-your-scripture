<x-layouts.app>
    {{-- Debug information --}}
    @if(session('memorize_debug'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            Debug: {{ session('memorize_debug') }}
        </div>
    @endif
    
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
            @if(!isset($quizMode) || !$quizMode)
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
            @else
                <div class="flex items-center justify-center h-full w-full flex-col opacity-50">
                    <span class="font-bold">Quiz Mode</span>
                    <span class="text-base">Difficulty: {{ $quizData['difficulty'] ?? 'easy' }}</span>
                </div>
            @endif
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
            <div>
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
        <x-content-card>
            <template x-if="showCongrats">
                <div class="p-4 rounded-xl text-center border-2 border-[#5ECE0B]">
                    <h2 class="text-xl font-semibold mb-2 flex items-center justify-center w-full gap-1">
                        <img src="{{ asset('images/icons/svg/badge-check.svg') }}" />
                        <span>Congrats!</span>
                    </h2>
                    <div class="flex flex-col my-3">
                        @auth
                            <span class="text-sm">Added to your bank:</span>
                        @else
                            <span class="text-sm">Memorized:</span>
                        @endauth
                        <span class="text-xl font-bold" x-text="reference"></span>
                        <span class="text-sm text-gray-600 mt-1">
                            Difficulty: <span class="font-semibold capitalize" x-text="getDifficultyDisplayName(difficulty)"></span>
                        </span>
                    </div>
                    @auth

                    @else
                    <div class="flex flex-col my-3">
                        <span class="text-sm">Sign up or register to have your memorized verses saved to your memory bank!</span>
                    </div>
                    @endif
                    <div class="flex gap-2 w-full items-stretch justify-center flex-col">
                        @if(isset($quizMode) && $quizMode)
                            <!-- Quiz Mode Buttons -->
                            <template x-if="quizNextData && !quizNextData.quiz_complete">
                                <x-button @click="nextQuizVerse()" class="bg-green-600 hover:bg-green-700">Next Verse</x-button>
                            </template>
                            <template x-if="quizNextData && quizNextData.quiz_complete">
                                <x-button @click="nextQuizVerse()" class="bg-blue-600 hover:bg-blue-700">View Results</x-button>
                            </template>
                            <x-button href="/daily-quiz" wire:navigate>Exit Quiz</x-button>
                        @else
                            <!-- Regular Mode Buttons -->
                            <x-button @click="resetAll()">Do Another</x-button>
                            @if(!isset($quizMode) || !$quizMode)
                            <template x-if="shouldShowIncreaseDifficultyButton()">
                                <x-button @click="openDifficultyModal()" class="bg-blue-600 hover:bg-blue-700">Increase Difficulty</x-button>
                            </template>
                            @endif
                            <x-button href="/" wire:navigate>Back Home</x-button>
                        @endif
                    </div>
                </div>
            </template>
            
            <!-- Difficulty Selection Modal -->
            <template x-if="showDifficultyModal">
                <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click="closeDifficultyModal()">
                    <div class="bg-bg rounded-xl p-6 max-w-md w-full mx-4" @click.stop>
                        <h3 class="text-lg font-semibold mb-4 text-center">Choose Difficulty</h3>
                        <div class="space-y-3">
                            <template x-for="difficultyOption in ['easy', 'normal', 'strict']" :key="difficultyOption">
                                <button class="w-full rounded-lg p-4 flex items-center justify-between transition-all duration-200"
                                     :class="{
                                         'bg-gray-400 cursor-not-allowed text-gray-600': !canSelectDifficulty(difficultyOption),
                                         'bg-green-600 text-white': isDifficultyCompleted(difficultyOption),
                                         'bg-gray-700 hover:bg-gray-600 cursor-pointer text-white': canSelectDifficulty(difficultyOption) && !isDifficultyCompleted(difficultyOption),
                                         'bg-gray-500 text-gray-300': !canSelectDifficulty(difficultyOption) && !isDifficultyCompleted(difficultyOption)
                                     }"
                                     @click="canSelectDifficulty(difficultyOption) ? selectDifficulty(difficultyOption) : null"
                                     :disabled="!canSelectDifficulty(difficultyOption)">
                                    <div class="flex items-center space-x-3">
                                        <span class="font-medium capitalize" x-text="getDifficultyDisplayName(difficultyOption)"></span>
                                        <span class="text-sm opacity-80">
                                            (<span x-text="difficultyOption === 'easy' ? '80%' : difficultyOption === 'normal' ? '95%' : '100%'"></span> accuracy required)
                                        </span>
                                    </div>
                                    <div class="flex items-center">
                                        <template x-if="isDifficultyCompleted(difficultyOption)">
                                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        </template>
                                        <template x-if="!canSelectDifficulty(difficultyOption) && !isDifficultyCompleted(difficultyOption)">
                                            <svg class="w-5 h-5 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 616 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        </template>
                                    </div>
                                </button>
                            </template>
                        </div>
                        <div class="mt-6 flex justify-center">
                            <x-button @click="closeDifficultyModal()" class="bg-gray-700 hover:bg-gray-600 text-white">Cancel</x-button>
                        </div>
                    </div>
                </div>
            </template>
            
            <div x-ref="dummyText" class="text-lg leading-[1.5]" style="visibility:hidden; position:absolute; top:-9999px; width:100%;">
                <span x-text="buildDisplayFull()"></span>
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
                quizNextData: null,
                difficulty: 'easy',
                hidden: false,
                segmentStates: [],
                showCongrats: false,
                showDifficultyModal: false,
                completedDifficulties: [],
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
                    
                    // Handle empty typed text
                    if (typed.length === 0) {
                        this.segmentStates[index].accuracy = 0;
                        return;
                    }
                    
                    let matched = 0;
                    let maxLength = Math.max(typed.length, correct.length);
                    
                    // Count correct characters at each position
                    for (let i = 0; i < maxLength; i++) {
                        let typedChar = (typed[i] || '').toLowerCase();
                        let correctChar = (correct[i] || '').toLowerCase();
                        if (typedChar === correctChar) {
                            matched++;
                        }
                    }
                    
                    // Calculate accuracy based on the correct text length
                    let acc = (matched / correct.length) * 100;
                    
                    // If typed text is significantly longer than correct, penalize
                    if (typed.length > correct.length * 1.1) {
                        acc = acc * 0.9; // 10% penalty for excessive length
                    }
                    
                    this.segmentStates[index].accuracy = Math.max(0, Math.min(100, acc));
                    
                    // Check if all segments meet required accuracy
                    if (this.checkAllSegments()) {
                        this.showCongrats = true;
                        
                        // Save to memory bank if not already saved
                        if (!this.saved) {
                            this.saved = true;
                            
                            // Handle quiz mode vs regular memorization
                            if (this.quizMode) {
                                // For quiz mode, call the quiz next endpoint
                                fetch("{{ route('daily-quiz.next') }}", {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                                    },
                                    body: JSON.stringify({
                                        score: this.overallAccuracy,
                                        difficulty: this.difficulty,
                                        user_text: this.userTypedText
                                    })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    console.log('Quiz progress saved:', data);
                                    this.quizNextData = data; // Store response for next button
                                })
                                .catch(err => console.error(err));
                            } else {
                                // For regular memorization, save to memory bank
                                fetch("{{ route('memorization-tool.save') }}", {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                                    },
                                    body: JSON.stringify({
                                        book: this.reference.split(" ")[0],
                                        chapter: parseInt(this.reference.split(" ")[1].split(":")[0]),
                                        verses: this.segments.map(seg => seg.verse),
                                        difficulty: this.difficulty,
                                        accuracy_score: this.overallAccuracy,
                                        bible_translation: this.bibleTranslation,
                                        user_text: this.userTypedText
                                    })
                                })
                                .then(response => response.json())
                                .then(data => console.log('Memory saved:', data))
                                .catch(err => console.error(err));
                            }
                        }
                        
                        // Add a more visible celebration effect
                        setTimeout(() => {
                            // Scroll to the main content to make sure it's visible
                            this.scrollToMainContent();
                        }, 100);
                    } else {
                        // Reset congrats if accuracy drops below threshold
                        this.showCongrats = false;
                    }
                },
                checkAllSegments() {
                    // Must have at least one segment
                    if (this.segmentStates.length === 0) return false;
                    
                    // Check if all segments have some text typed
                    const allHaveText = this.segmentStates.every(state => 
                        state.typedText.trim().length > 0
                    );
                    
                    if (!allHaveText) return false;
                    
                    // Check if all segments meet the accuracy threshold
                    const requiredAcc = this.requiredAccuracy();
                    const allMeetAccuracy = this.segmentStates.every(state => 
                        state.accuracy >= requiredAcc
                    );
                    
                    // Also check overall accuracy as a backup
                    const overallMeetsThreshold = this.overallAccuracy >= requiredAcc;
                    
                    return allMeetAccuracy || overallMeetsThreshold;
                },
                requiredAccuracy() {
                    if (this.difficulty === 'easy') return 80;
                    if (this.difficulty === 'normal') return 95;
                    if (this.difficulty === 'strict') return 100;
                    return 95; // default
                },
                resetAll() {
                    this.hidden = false;
                    this.segmentStates = this.segments.map(() => ({ typedText: '', accuracy: 0 }));
                    this.showCongrats = false;
                    this.saved = false;
                },
                
                nextQuizVerse() {
                    if (this.quizNextData) {
                        if (this.quizNextData.quiz_complete) {
                            window.location.href = this.quizNextData.redirect_url;
                        } else {
                            window.location.href = this.quizNextData.redirect_url;
                        }
                    }
                },
                async fetchCompletedDifficulties() {
                    try {
                        const response = await fetch(`/api/completed-difficulties?book=${this.reference.split(" ")[0]}&chapter=${parseInt(this.reference.split(" ")[1].split(":")[0])}&verses=${this.segments.map(seg => seg.verse).join(',')}`);
                        const data = await response.json();
                        this.completedDifficulties = data.difficulties || [];
                    } catch (error) {
                        console.error('Error fetching completed difficulties:', error);
                        this.completedDifficulties = [];
                    }
                },
                
                openDifficultyModal() {
                    this.fetchCompletedDifficulties();
                    this.showDifficultyModal = true;
                },
                
                closeDifficultyModal() {
                    this.showDifficultyModal = false;
                },
                
                selectDifficulty(newDifficulty) {
                    this.difficulty = newDifficulty;
                    this.resetAll();
                    this.closeDifficultyModal();
                },
                
                isDifficultyCompleted(difficulty) {
                    return this.completedDifficulties.includes(difficulty);
                },
                
                canSelectDifficulty(difficulty) {
                    const difficultyOrder = ['easy', 'normal', 'strict'];
                    const currentIndex = difficultyOrder.indexOf(this.difficulty);
                    const targetIndex = difficultyOrder.indexOf(difficulty);
                    return targetIndex > currentIndex;
                },
                
                shouldShowIncreaseDifficultyButton() {
                    return this.difficulty !== 'strict';
                },
                
                getDifficultyDisplayName(difficulty) {
                    const names = {
                        'easy': 'Easy',
                        'normal': 'Normal', 
                        'strict': 'Strict'
                    };
                    return names[difficulty] || difficulty;
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
                get userTypedText() {
                    return this.segmentStates.map(state => state.typedText).join("\n");
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
