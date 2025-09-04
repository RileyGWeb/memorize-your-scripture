<div class="max-w-4xl mx-auto">
    @if(!$quizCompleted)
        <!-- Quiz Progress -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold text-gray-900">Daily Quiz</h1>
                <div class="text-sm text-gray-600">
                    Question {{ $currentIndex + 1 }} of {{ count($quizData['verses']) }}
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="w-full bg-gray-200 rounded-full h-2 mb-6">
                <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                     style="width: {{ (($currentIndex + 1) / count($quizData['verses'])) * 100 }}%"></div>
            </div>

            @if($currentVerse)
                <!-- Verse Reference -->
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">
                        {{ $this->getVerseReference() }}
                    </h2>
                    <p class="text-gray-600">Type out this verse from memory</p>
                </div>

                @if(!$showAnswer)
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
                            <!-- Accuracy Score -->
                            <div class="text-center">
                                <div class="inline-block bg-blue-100 px-4 py-2 rounded-lg">
                                    <span class="text-blue-900 font-semibold">
                                        Accuracy: {{ $this->calculateAccuracy($userInput, $actualVerseText) }}%
                                    </span>
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
            @endif
        </div>
    @else
        <!-- Quiz Completed -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-8 text-center">
            <div class="mb-6">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Quiz Complete!</h1>
                <p class="text-gray-600">Great job working on your scripture memorization</p>
            </div>

            @if(!empty($results))
                <div class="bg-blue-50 p-6 rounded-lg mb-6">
                    <h2 class="text-xl font-semibold text-blue-900 mb-4">Your Results</h2>
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-bold text-blue-800">{{ count($results) }}</div>
                            <div class="text-blue-600">Questions</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-blue-800">
                                {{ collect($results)->avg('accuracy') ? round(collect($results)->avg('accuracy'), 1) : 0 }}%
                            </div>
                            <div class="text-blue-600">Average Accuracy</div>
                        </div>
                    </div>
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
