<div class="max-w-7xl mx-6 w-full">
    @if(!$quizCompleted)
        <!-- Quiz Progress -->
        <div class="bg-bg rounded-xl shadow-lg border border-stroke p-4 mb-4 sticky top-2 z-10 bg-bg">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold text-gray-900">Daily Quiz</h1>
                <div class="text-right">
                    <div class="text-lg font-semibold text-gray-900">
                        {{ $currentIndex + 1 }} of {{ count($quizData['verses']) }}
                    </div>
                    @if($totalAnswered > 0)
                        <div class="text-sm text-gray-600">
                            Score: {{ $score }}/{{ $totalAnswered }} ({{ $this->getCurrentPercentage() }}%)
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="w-full bg-gray-200 rounded-full h-3 mb-6">
                <div class="bg-blue-600 h-3 rounded-full transition-all duration-300" 
                     style="width: {{ (($currentIndex + 1) / count($quizData['verses'])) * 100 }}%"></div>
            </div>

            <!-- Difficulty Selection -->
            <div class="flex items-center justify-center h-20">
                <div class="flex items-center justify-center h-full w-full">
                    <div class="flex w-full h-full max-w-md">
                        <div class="relative h-full w-full">
                            <input wire:model.live="difficulty" class="peer hidden" id="easy" type="radio" value="easy" />
                            <label for="easy" class="flex h-full justify-center cursor-pointer flex-col px-4 py-2.5 border-r border-stroke peer-checked:bg-blue-600 text-gray-700 peer-checked:text-white">
                                <span class="font-bold">Easy</span>
                                <span class="text-sm">80% accuracy<br>required</span>
                            </label>
                        </div>
                        <div class="relative h-full w-full">
                            <input wire:model.live="difficulty" class="peer hidden" id="normal" type="radio" value="normal" />
                            <label for="normal" class="flex h-full justify-center cursor-pointer flex-col px-4 py-2.5 border-r border-stroke peer-checked:bg-blue-600 text-gray-700 peer-checked:text-white">
                                <span class="font-bold">Normal</span>
                                <span class="text-sm">95% accuracy<br>required</span>
                            </label>
                        </div>
                        <div class="relative h-full w-full">
                            <input wire:model.live="difficulty" class="peer hidden" id="strict" type="radio" value="strict" />
                            <label for="strict" class="flex h-full justify-center cursor-pointer flex-col px-4 py-2.5 peer-checked:bg-blue-600 text-gray-700 peer-checked:text-white">
                                <span class="font-bold">Strict</span>
                                <span class="text-sm">100% accuracy<br>required</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back Button Card -->
        <x-content-card>
            <x-content-card-button href="/" text="Back to Home" icon="arrow-narrow-left" iconSize="lg" wire:navigate />
        </x-content-card>

        <div class="bg-bg rounded-xl shadow-lg mt-4 border border-gray-100 p-6 mb-4">
            @if($currentVerse)
                <!-- Verse Reference -->
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">
                        {{ $this->getVerseReference() }}
                    </h2>
                    <p class="text-gray-600">Type out this verse from memory</p>
                    <div class="mt-2 text-sm text-gray-500">
                        Difficulty: <span class="font-semibold capitalize">{{ $difficulty }}</span> 
                        ({{ $this->getRequiredAccuracy() }}% accuracy required)
                    </div>
                </div>                    @if(!$showAnswer)
                        <!-- Input Area -->
                        <div class="mb-6">
                            <textarea 
                                wire:model="userInput"
                                placeholder="Start typing the verse..."
                                class="w-full h-40 p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none text-lg leading-relaxed"
                                autofocus
                            ></textarea>
                        </div>

                        <div class="text-center">
                            <button 
                                wire:click="submitAnswer"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-semibold transition-colors"
                                {{ empty($userInput) ? 'disabled' : '' }}
                            >
                                Submit Answer
                            </button>
                        </div>
                    @else
                        <!-- Answer Review -->
                        <div class="space-y-6">
                            <!-- User's Answer -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-semibold text-gray-900 mb-2">Your Answer:</h3>
                                <p class="text-gray-800 leading-relaxed">{{ $userInput ?: 'No answer provided' }}</p>
                            </div>

                            <!-- Correct Answer -->
                            <div class="bg-green-50 p-4 rounded-lg">
                                <h3 class="font-semibold text-green-900 mb-2">Correct Answer:</h3>
                                @if($isLoading)
                                    <div class="flex items-center text-green-700">
                                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Loading verse text...
                                    </div>
                                @elseif($apiError)
                                    <p class="text-red-600">{{ $apiError }}</p>
                                @else
                                    <p class="text-green-800 leading-relaxed">{{ $actualVerseText }}</p>
                                @endif
                            </div>

                            @if($actualVerseText && !$isLoading)
                                <!-- Accuracy Score with Pass/Fail -->
                                @php
                                    $accuracy = $this->calculateAccuracy($userInput, $actualVerseText);
                                    $requiredAccuracy = $this->getRequiredAccuracy();
                                    $passed = $accuracy >= $requiredAccuracy;
                                @endphp
                                <div class="text-center">
                                    <div class="inline-block p-4 rounded-lg {{ $passed ? 'bg-green-100' : 'bg-red-100' }}">
                                        <div class="text-lg font-semibold {{ $passed ? 'text-green-900' : 'text-red-900' }}">
                                            Accuracy: {{ $accuracy }}%
                                        </div>
                                        <div class="text-sm {{ $passed ? 'text-green-700' : 'text-red-700' }}">
                                            Required: {{ $requiredAccuracy }}% ({{ ucfirst($difficulty) }})
                                        </div>
                                        <div class="mt-2 font-bold text-lg {{ $passed ? 'text-green-800' : 'text-red-800' }}">
                                            {{ $passed ? '✓ PASSED' : '✗ FAILED' }}
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Next Button -->
                            <div class="text-center">
                                <button 
                                    wire:click="nextQuestion"
                                    class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-semibold transition-colors"
                                >
                                    @if($currentIndex + 1 >= count($quizData['verses']))
                                        Finish Quiz
                                    @else
                                        Next Question
                                    @endif
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
    @else
        <!-- Back Button Card for Completion -->
        <div class="bg-bg rounded-xl shadow-lg border border-gray-100 p-4 mb-4">
            <a href="/" class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors" wire:navigate>
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Home
            </a>
        </div>

        <!-- Quiz Completed -->
        <div class="bg-bg rounded-xl shadow-lg border border-gray-100 p-8 text-center">
            <div class="mb-6">
                @php
                    $percentage = $totalAnswered > 0 ? ($score / $totalAnswered) * 100 : 0;
                    $grade = '';
                    if ($percentage >= 97) $grade = 'A+';
                    elseif ($percentage >= 93) $grade = 'A';
                    elseif ($percentage >= 90) $grade = 'A-';
                    elseif ($percentage >= 87) $grade = 'B+';
                    elseif ($percentage >= 83) $grade = 'B';
                    elseif ($percentage >= 80) $grade = 'B-';
                    elseif ($percentage >= 77) $grade = 'C+';
                    elseif ($percentage >= 73) $grade = 'C';
                    elseif ($percentage >= 70) $grade = 'C-';
                    elseif ($percentage >= 67) $grade = 'D+';
                    elseif ($percentage >= 63) $grade = 'D';
                    elseif ($percentage >= 60) $grade = 'D-';
                    else $grade = 'F';
                    
                    $gradeColor = $percentage >= 80 ? 'text-green-600' : ($percentage >= 70 ? 'text-yellow-600' : 'text-red-600');
                    $bgColor = $percentage >= 80 ? 'bg-green-100' : ($percentage >= 70 ? 'bg-yellow-100' : 'bg-red-100');
                @endphp
                
                <div class="w-24 h-24 {{ $bgColor }} rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-3xl font-bold {{ $gradeColor }}">{{ $grade }}</span>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Quiz Complete!</h1>
                <p class="text-gray-600">Great job working on your scripture memorization</p>
            </div>

            @if(!empty($results))
                <div class="bg-blue-50 p-6 rounded-lg mb-6">
                    <h2 class="text-xl font-semibold text-blue-900 mb-4">Your Results</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center mb-4">
                        <div>
                            <div class="text-2xl font-bold text-blue-800">{{ count($results) }}</div>
                            <div class="text-blue-600">Questions</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-blue-800">{{ $score }}</div>
                            <div class="text-blue-600">Passed</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-blue-800">{{ round($percentage, 1) }}%</div>
                            <div class="text-blue-600">Pass Rate</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold {{ $gradeColor }}">{{ $grade }}</div>
                            <div class="text-blue-600">Grade</div>
                        </div>
                    </div>
                    
                    <!-- Difficulty Breakdown -->
                    @php
                        $difficultyGroups = collect($results)->groupBy('difficulty');
                        $avgAccuracy = collect($results)->avg('accuracy');
                    @endphp
                    
                    @if($difficultyGroups->count() > 1)
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-blue-900 mb-2">By Difficulty</h3>
                            <div class="grid grid-cols-3 gap-4 text-center">
                                @foreach(['easy', 'normal', 'strict'] as $diff)
                                    @if($difficultyGroups->has($diff))
                                        @php
                                            $group = $difficultyGroups[$diff];
                                            $passed = $group->where('passed', true)->count();
                                            $total = $group->count();
                                        @endphp
                                        <div class="bg-bg p-3 rounded">
                                            <div class="text-sm font-semibold text-gray-600 uppercase">{{ $diff }}</div>
                                            <div class="text-lg font-bold text-blue-800">{{ $passed }}/{{ $total }}</div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    @if($avgAccuracy)
                        <div class="text-center">
                            <div class="text-sm text-gray-600">
                                Average Text Accuracy: {{ round($avgAccuracy, 1) }}%
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <div class="space-x-4">
                <button 
                    wire:click="restartQuiz"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors"
                >
                    Take Another Quiz
                </button>
                <a 
                    href="/memorization-tool"
                    class="inline-block bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors"
                >
                    Practice More Verses
                </a>
            </div>
        </div>
    @endif
</div>
