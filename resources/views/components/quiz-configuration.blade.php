@props([
    'numberOfQuestions' => 10,
    'memoryBankCount' => 0,
    'showQuizTypes' => true,
    'showDifficulty' => false,
    'difficulty' => 'easy',
    'quizTypeLabel' => null,
    'showActionButtons' => false,
    'showQuizTypeNavigation' => false,
    'backUrl' => '/daily-quiz',
    'componentRef' => null
])

<!-- Number Selector -->
<div class="p-0">
    @if($quizTypeLabel)
        <div class="text-center py-2 border-b border-stroke relative">
            @if($showQuizTypeNavigation)
                <!-- Left Arrow -->
                <button 
                    wire:click="previousQuizType"
                    class="absolute left-4 top-1/2 transform -translate-y-1/2 w-8 h-8 flex items-center justify-center text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-full transition-all"
                    title="Previous quiz type"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                
                <!-- Right Arrow -->
                <button 
                    wire:click="nextQuizType"
                    class="absolute right-4 top-1/2 transform -translate-y-1/2 w-8 h-8 flex items-center justify-center text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-full transition-all"
                    title="Next quiz type"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            @endif
            
            <h3 class="text-lg font-semibold text-gray-900">{{ $quizTypeLabel }}</h3>
            <p class="text-textLight text-base">You'll be quizzed on {{ $numberOfQuestions }} {{ $numberOfQuestions === 1 ? 'verse' : 'verses' }}</p>
        </div>
    @endif
    
    <div class="flex items-center justify-between space-x-4">
        <div class="text-textLight text-base px-4 w-full text-center w-full">
            Total verses: {{ $memoryBankCount }}
        </div>
        <div class="flex items-center">
            <!-- -5 Button -->
            <button 
                wire:click="decreaseNumberBy5"
                class="w-10 h-10 border-l border-stroke hover:bg-gray-50 flex items-center justify-center text-text transition-colors text-xs font-medium"
                title="Decrease by 5"
            >
                -5
            </button>
            <!-- -1 Button -->
            <button 
                wire:click="decreaseNumber"
                class="w-10 h-10 border-x border-stroke hover:bg-gray-50 flex items-center justify-center text-text transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                </svg>
            </button>
            <span class="text-2xl font-bold text-text min-w-[3rem] text-center">{{ $numberOfQuestions }}</span>
            <!-- +1 Button -->
            <button 
                wire:click="increaseNumber"
                class="w-10 h-10 border-l border-stroke hover:bg-gray-50 flex items-center justify-center text-text transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
            </button>
            <!-- +5 Button -->
            <button 
                wire:click="increaseNumberBy5"
                class="w-10 h-10 border-l border-stroke hover:bg-gray-50 flex items-center justify-center text-text transition-colors text-xs font-medium"
                title="Increase by 5"
            >
                +5
            </button>
        </div>
    </div>
</div>

@if($showQuizTypes)
    <x-divider />

    <!-- Quiz Options Grid -->
    <div class="p-0">
        <div class="grid grid-cols-2 border-stroke divide-x divide-y">
            <!-- Random Quiz -->
            <button 
                wire:click="startQuiz('random')"
                class="group bg-bg hover:bg-gray-50 p-4 text-left transition-all"
            >
                <h3 class="font-bold text-text mb-1">{{ $numberOfQuestions }} randoms</h3>
                <p class="text-textLight text-base">{{ $numberOfQuestions }} random verses from your bank</p>
            </button>

            <!-- Most Recent Quiz -->
            <button 
                wire:click="startQuiz('recent')"
                class="group bg-bg hover:bg-gray-50 p-4 text-left transition-all"
            >
                <h3 class="font-bold text-text mb-1">{{ $numberOfQuestions }} most recent</h3>
                <p class="text-textLight text-base">The last {{ $numberOfQuestions }} verses you memorized</p>
            </button>

            <!-- Longest Quiz -->
            <button 
                wire:click="startQuiz('longest')"
                class="group bg-bg hover:bg-gray-50 p-4 text-left transition-all"
            >
                <h3 class="font-bold text-text mb-1">{{ $numberOfQuestions }} longest</h3>
                <p class="text-textLight text-base">The {{ $numberOfQuestions }} longest verses in your bank.</p>
            </button>

            <!-- Shortest Quiz -->
            <button 
                wire:click="startQuiz('shortest')"
                class="group bg-bg hover:bg-gray-50 p-4 text-left transition-all"
            >
                <h3 class="font-bold text-text mb-1">{{ $numberOfQuestions }} shortest</h3>
                <p class="text-textLight text-base">The {{ $numberOfQuestions }} shortest verses in your bank.</p>
            </button>
        </div>
        
        <!-- All Verses Option (Full Width) -->
        <div class="w-full">
            <button 
                wire:click="startQuiz('all')"
                class="group bg-bg hover:bg-gray-50 border-t border-stroke p-4 text-left transition-all w-full"
            >
                <h3 class="font-bold text-text mb-1">All {{ $memoryBankCount }} verses</h3>
                <p class="text-textLight text-base">Quiz yourself on every verse you've memorized</p>
            </button>
        </div>
    </div>
@endif

@if($showDifficulty)
    <x-divider />

    <!-- Difficulty Selection -->
    <div class="p-0">
        <h3 class="text-lg font-semibold text-gray-900 py-2 text-center border-b border-stroke">Choose Difficulty</h3>
        <div class="flex items-center justify-center">
            <div class="flex w-full max-w-md">
                <div class="relative flex-1">
                    <input wire:model.live="difficulty" class="peer hidden" id="{{ $componentRef ? $componentRef . '-' : '' }}easy" type="radio" value="easy" />
                    <label for="{{ $componentRef ? $componentRef . '-' : '' }}easy" class="flex justify-center cursor-pointer flex-col px-4 py-3 border-r border-stroke peer-checked:bg-darkBlue text-text peer-checked:text-white">
                        <span class="font-bold text-center">Easy</span>
                        <span class="text-sm text-center">80% accuracy<br>required</span>
                    </label>
                </div>
                <div class="relative flex-1">
                    <input wire:model.live="difficulty" class="peer hidden" id="{{ $componentRef ? $componentRef . '-' : '' }}normal" type="radio" value="normal" />
                    <label for="{{ $componentRef ? $componentRef . '-' : '' }}normal" class="flex justify-center cursor-pointer flex-col px-4 py-3 border-r border-stroke peer-checked:bg-darkBlue text-text peer-checked:text-white">
                        <span class="font-bold text-center">Normal</span>
                        <span class="text-sm text-center">95% accuracy<br>required</span>
                    </label>
                </div>
                <div class="relative flex-1">
                    <input wire:model.live="difficulty" class="peer hidden" id="{{ $componentRef ? $componentRef . '-' : '' }}strict" type="radio" value="strict" />
                    <label for="{{ $componentRef ? $componentRef . '-' : '' }}strict" class="flex justify-center cursor-pointer flex-col px-4 py-3 peer-checked:bg-darkBlue text-text peer-checked:text-white">
                        <span class="font-bold text-center">Strict</span>
                        <span class="text-sm text-center">100% accuracy<br>required</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
@endif

@if($showActionButtons)
    <x-divider />

    <!-- Action Buttons -->
    <div class="px-4 py-4">
        <div class="flex gap-3">
            <a href="{{ $backUrl }}" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold text-center transition-colors">
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
@endif
