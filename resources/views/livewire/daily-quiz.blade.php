<div class="w-full">
@auth
    @if($this->getMemoryBankCount() > 0)
        <x-content-card>
            <x-content-card-title 
                title="Daily Quiz!" 
                subtitle="Daily juice to keep those verses in your brain (and heart)." 
            />
                <x-divider />

                <!-- Number Selector -->
                <div class="px-4 py-4">
                    <div class="flex items-center justify-center space-x-4">
                        <span class="text-textLight text-base">Change number</span>
                        <div class="flex items-center space-x-3">
                            <button 
                                wire:click="decreaseNumber"
                                class="w-10 h-10 rounded border border-stroke hover:bg-gray-50 flex items-center justify-center text-text transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                </svg>
                            </button>
                            <span class="text-2xl font-bold text-text min-w-[3rem] text-center">{{ $numberOfQuestions }}</span>
                            <button 
                                wire:click="increaseNumber"
                                class="w-10 h-10 rounded border border-stroke hover:bg-gray-50 flex items-center justify-center text-text transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="text-center">
                        <span class="text-textLight text-base">Total verses memorized: {{ $this->getMemoryBankCount() }}</span>
                    </div>
                </div>

                <x-divider />

                <!-- Quiz Options Grid -->
                <div class="p-4">
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Random Quiz -->
                        <button 
                            wire:click="startQuiz('random')"
                            class="group bg-white hover:bg-gray-50 border border-stroke rounded-lg p-4 text-left transition-all"
                        >
                            <h3 class="font-bold text-text mb-1">{{ $numberOfQuestions }} randoms</h3>
                            <p class="text-textLight text-base">{{ $numberOfQuestions }} random verses from your bank</p>
                        </button>

                        <!-- Most Recent Quiz -->
                        <button 
                            wire:click="startQuiz('recent')"
                            class="group bg-white hover:bg-gray-50 border border-stroke rounded-lg p-4 text-left transition-all"
                        >
                            <h3 class="font-bold text-text mb-1">{{ $numberOfQuestions }} most recent</h3>
                            <p class="text-textLight text-base">The last {{ $numberOfQuestions }} verses you memorized</p>
                        </button>

                        <!-- Longest Quiz -->
                        <button 
                            wire:click="startQuiz('longest')"
                            class="group bg-white hover:bg-gray-50 border border-stroke rounded-lg p-4 text-left transition-all"
                        >
                            <h3 class="font-bold text-text mb-1">{{ $numberOfQuestions }} longest</h3>
                            <p class="text-textLight text-base">The {{ $numberOfQuestions }} longest verses in your bank.</p>
                        </button>

                        <!-- Shortest Quiz -->
                        <button 
                            wire:click="startQuiz('shortest')"
                            class="group bg-white hover:bg-gray-50 border border-stroke rounded-lg p-4 text-left transition-all"
                        >
                            <h3 class="font-bold text-text mb-1">{{ $numberOfQuestions }} shortest</h3>
                            <p class="text-textLight text-base">The {{ $numberOfQuestions }} shortest verses in your bank.</p>
                        </button>
                    </div>
                </div>
            
            @if(session('error'))
                <x-divider />
                <div class="px-4 py-3">
                    <div class="p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-red-700 text-base">{{ session('error') }}</p>
                    </div>
                </div>
            @endif
        </x-content-card>
    @else
        <x-content-card>
            <x-content-card-title 
                title="Daily Quiz" 
                subtitle="You need to memorize some verses first!" 
            />
            <x-divider />
            <div class="px-4 py-3 text-center">
                <p class="text-gray-600 mb-4">You need to memorize at least one verse before you can take a quiz.</p>
                <a href="/memorization-tool" class="inline-block bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-lg font-semibold transition-colors">
                    Start Memorizing
                </a>
            </div>
        </x-content-card>
    @endif
@endauth

@guest
    <x-content-card>
        <x-content-card-title 
            title="Quiz Requires Login" 
            subtitle="Please log in to access the quiz feature." 
        />
        <x-divider />
        <div class="px-4 py-3 text-center">
            <p class="text-gray-600 mb-4">You need to be logged in to take quizzes and track your progress.</p>
            <div class="space-y-3">
                <button 
                    @click="loginModal = true" 
                    class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors"
                >
                    Log In
                </button>
                <div class="text-textLight">
                    Don't have an account? 
                    <button 
                        @click="registerModal = true" 
                        class="text-blue-600 hover:text-blue-700 font-medium"
                    >
                        Sign up here
                    </button>
                </div>
            </div>
        </div>
    </x-content-card>
@endguest
</div>
