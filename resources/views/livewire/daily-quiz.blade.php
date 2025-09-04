<div class="w-full">
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 mb-6">
        <!-- Header -->
        <div class="flex justify-between items-start mb-4">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Daily Quiz!</h2>
                <p class="text-gray-600 text-sm">Daily juice to keep those verses in your brain (and heart).</p>
            </div>
            <button 
                wire:click="toggleQuizOptions"
                class="p-2 text-gray-400 hover:text-gray-600 transition-colors"
            >
                <svg class="w-5 h-5 transform {{ $showQuizOptions ? 'rotate-180' : '' }} transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
        </div>

        @if($showQuizOptions)
            <!-- Number Selector -->
            <div class="mb-6">
                <div class="flex items-center justify-center space-x-4">
                    <span class="text-gray-600 text-sm">Change number</span>
                    <div class="flex items-center space-x-3">
                        <button 
                            wire:click="decreaseNumber"
                            class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                            </svg>
                        </button>
                        <span class="text-2xl font-bold text-gray-900 min-w-[3rem] text-center">{{ $numberOfQuestions }}</span>
                        <button 
                            wire:click="increaseNumber"
                            class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Quiz Options Grid -->
        <div class="grid grid-cols-2 gap-4">
            <!-- Random Quiz -->
            <button 
                wire:click="startQuiz('random')"
                class="group bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg p-4 text-left transition-all hover:shadow-md"
            >
                <h3 class="font-semibold text-blue-900 mb-1">{{ $numberOfQuestions }} randoms</h3>
                <p class="text-blue-700 text-sm">{{ $numberOfQuestions }} random verses from your bank</p>
            </button>

            <!-- Most Recent Quiz -->
            <button 
                wire:click="startQuiz('recent')"
                class="group bg-green-50 hover:bg-green-100 border border-green-200 rounded-lg p-4 text-left transition-all hover:shadow-md"
            >
                <h3 class="font-semibold text-green-900 mb-1">{{ $numberOfQuestions }} most recent</h3>
                <p class="text-green-700 text-sm">The last {{ $numberOfQuestions }} verses you memorized</p>
            </button>

            <!-- Longest Quiz -->
            <button 
                wire:click="startQuiz('longest')"
                class="group bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-lg p-4 text-left transition-all hover:shadow-md"
            >
                <h3 class="font-semibold text-purple-900 mb-1">{{ $numberOfQuestions }} longest</h3>
                <p class="text-purple-700 text-sm">The {{ $numberOfQuestions }} longest verses in your bank.</p>
            </button>

            <!-- Shortest Quiz -->
            <button 
                wire:click="startQuiz('shortest')"
                class="group bg-orange-50 hover:bg-orange-100 border border-orange-200 rounded-lg p-4 text-left transition-all hover:shadow-md"
            >
                <h3 class="font-semibold text-orange-900 mb-1">{{ $numberOfQuestions }} shortest</h3>
                <p class="text-orange-700 text-sm">The {{ $numberOfQuestions }} shortest verses in your bank.</p>
            </button>
        </div>

        @if(session('error'))
            <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-red-700 text-sm">{{ session('error') }}</p>
            </div>
        @endif

        @if($this->getMemoryBankCount() === 0)
            <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-center">
                <p class="text-yellow-800 text-sm mb-2">You haven't memorized any verses yet!</p>
                <a href="/memorization-tool" class="inline-flex items-center text-yellow-700 hover:text-yellow-900 font-medium">
                    Start memorizing verses
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        @endif
    </div>
</div>
