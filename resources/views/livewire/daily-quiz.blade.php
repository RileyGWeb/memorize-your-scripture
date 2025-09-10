<div class="w-full">
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

            <!-- Difficulty Selection -->
            <div class="px-4 py-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-3 text-center">Choose Difficulty</h3>
                <div class="flex items-center justify-center">
                    <div class="flex w-full max-w-md">
                        <div class="relative flex-1">
                            <input wire:model.defer="difficulty" class="peer hidden" id="quiz-easy" type="radio" value="easy" />
                            <label for="quiz-easy" class="flex justify-center cursor-pointer flex-col px-4 py-3 border-r border-stroke peer-checked:bg-darkBlue text-text peer-checked:text-white">
                                <span class="font-bold text-center">Easy</span>
                                <span class="text-sm text-center">80% accuracy<br>required</span>
                            </label>
                        </div>
                        <div class="relative flex-1">
                            <input wire:model.defer="difficulty" class="peer hidden" id="quiz-normal" type="radio" value="normal" />
                            <label for="quiz-normal" class="flex justify-center cursor-pointer flex-col px-4 py-3 border-r border-stroke peer-checked:bg-darkBlue text-text peer-checked:text-white">
                                <span class="font-bold text-center">Normal</span>
                                <span class="text-sm text-center">95% accuracy<br>required</span>
                            </label>
                        </div>
                        <div class="relative flex-1">
                            <input wire:model.defer="difficulty" class="peer hidden" id="quiz-strict" type="radio" value="strict" />
                            <label for="quiz-strict" class="flex justify-center cursor-pointer flex-col px-4 py-3 peer-checked:bg-darkBlue text-text peer-checked:text-white">
                                <span class="font-bold text-center">Strict</span>
                                <span class="text-sm text-center">100% accuracy<br>required</span>
                            </label>
                        </div>
                    </div>
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
            <a href="/memorization-tool" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                Start Memorizing
            </a>
        </div>
    </x-content-card>
@endif
</div>
