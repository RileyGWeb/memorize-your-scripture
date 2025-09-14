<x-content-card>
    <x-content-card-title title="Random Verse" subtitle="Discover new scripture or revisit favorites" />
    
    <x-divider />
    
    <div class="">
        <!-- Toggle for Random Type -->
        <div class="flex items-center justify-center space-x-6 p-4 border-b border-stroke">
            <label class="flex items-center space-x-2 cursor-pointer">
                <input type="radio" wire:model.live="randomType" value="popular" class="text-blue-600 focus:ring-blue-500">
                <span class="text-sm font-medium">Popular verses</span>
            </label>
            <label class="flex items-center space-x-2 cursor-pointer">
                <input type="radio" wire:model.live="randomType" value="random" class="text-blue-600 focus:ring-blue-500">
                <span class="text-sm font-medium">Truly random</span>
            </label>
        </div>

        <!-- Get Random Verse Button -->
        @if(!$verseData)
            <div class="flex justify-center">
                <button wire:click="getRandomVerse" 
                        wire:loading.attr="disabled"
                        class="flex items-center justify-center w-full py-2.5 relative hover:bg-gray-50 active:bg-gray-100 gap-2 font-bold disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="getRandomVerse">Get Random Verse</span>
                    <span wire:loading wire:target="getRandomVerse" class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Loading...
                    </span>
                    <x-icon icon="arrow-narrow-right" class="w-4 h-4" color="#000" />
                </button>
            </div>
        @endif

        <!-- Verse Display -->
        @if($verseData)
            <div class="space-y-4 mt-6">
                <div class="bg-gray-50 p-6 rounded-lg border-l-4 border-blue-500">
                    <div class="text-lg font-semibold text-gray-800 mb-2">{{ $verseData['reference'] ?? '' }}</div>
                    <div class="text-gray-700 leading-relaxed">{{ $verseData['text'] ?? '' }}</div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ route('memorization-tool.display', [
                        'book' => $verseData['book'] ?? '',
                        'chapter' => $verseData['chapter'] ?? '',
                        'verses' => $verseData['verse'] ?? ''
                    ]) }}"
                       class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg text-center transition-colors duration-200">
                        Memorize Now
                    </a>
                    @auth
                        <button wire:click="addToMemorizeLater" 
                                wire:loading.attr="disabled" 
                                wire:target="addToMemorizeLater"
                                class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 disabled:bg-gray-400 text-white font-medium rounded-lg transition-colors duration-200">
                            <span wire:loading.remove wire:target="addToMemorizeLater">Memorize Later</span>
                            <span wire:loading wire:target="addToMemorizeLater">Adding...</span>
                        </button>
                    @endauth
                    <button wire:click="getRandomVerse" 
                            class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200">
                        Get Another
                    </button>
                </div>
            </div>
        @endif

        <!-- Success Message -->
        @if($successMessage)
            <div class="text-green-600 text-center p-4 bg-green-50 rounded-lg">
                {{ $successMessage }}
            </div>
        @endif

        <!-- Error Display -->
        @if($error)
            <div class="text-red-600 text-center p-4 bg-red-50 rounded-lg">
                {{ $error }}
            </div>
        @endif
    </div>
</x-content-card>
