<div class="w-full">
    <x-content-card>
        <x-content-card-title 
            title="Quiz Setup" 
            subtitle="Configure your quiz settings before starting." 
        />
        <x-divider />

        <!-- Quiz Type Display -->
        <div class="px-4 py-4">
            <div class="text-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">{{ $quizTypeLabel }}</h3>
                <p class="text-textLight text-base">You'll be quizzed on {{ $numberOfQuestions }} {{ $numberOfQuestions === 1 ? 'verse' : 'verses' }}</p>
            </div>
        </div>

        <x-divider />

        <!-- Number Selector -->
        <div class="px-4 py-4">
            <div class="flex items-center justify-center space-x-4">
                <span class="text-textLight text-base">Number of verses</span>
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
            <div class="text-center mt-2">
                <span class="text-textLight text-sm">Total verses memorized: {{ $this->getMemoryBankCount() }}</span>
            </div>
        </div>

        <x-divider />

        <!-- Difficulty Selection -->
        <div class="px-4 py-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-3 text-center">Choose Difficulty</h3>
            <div class="flex items-center justify-center">
                <div class="flex w-full max-w-md">
                    <div class="relative flex-1">
                        <input wire:model.live="difficulty" class="peer hidden" id="setup-easy" type="radio" value="easy" />
                        <label for="setup-easy" class="flex justify-center cursor-pointer flex-col px-4 py-3 border-r border-stroke peer-checked:bg-darkBlue text-text peer-checked:text-white">
                            <span class="font-bold text-center">Easy</span>
                            <span class="text-sm text-center">80% accuracy<br>required</span>
                        </label>
                    </div>
                    <div class="relative flex-1">
                        <input wire:model.live="difficulty" class="peer hidden" id="setup-normal" type="radio" value="normal" />
                        <label for="setup-normal" class="flex justify-center cursor-pointer flex-col px-4 py-3 border-r border-stroke peer-checked:bg-darkBlue text-text peer-checked:text-white">
                            <span class="font-bold text-center">Normal</span>
                            <span class="text-sm text-center">95% accuracy<br>required</span>
                        </label>
                    </div>
                    <div class="relative flex-1">
                        <input wire:model.live="difficulty" class="peer hidden" id="setup-strict" type="radio" value="strict" />
                        <label for="setup-strict" class="flex justify-center cursor-pointer flex-col px-4 py-3 peer-checked:bg-darkBlue text-text peer-checked:text-white">
                            <span class="font-bold text-center">Strict</span>
                            <span class="text-sm text-center">100% accuracy<br>required</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <x-divider />

        <!-- Action Buttons -->
        <div class="px-4 py-4">
            <div class="flex gap-3">
                <a href="/daily-quiz" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold text-center transition-colors">
                    Back
                </a>
                <button 
                    wire:click="startQuiz"
                    class="flex-1 bg-darkBlue hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors"
                >
                    Start Quiz
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
</div>
