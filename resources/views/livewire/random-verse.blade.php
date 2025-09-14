<x-content-card>
        <x-content-card-title title="Random Verse" subtitle="Discover new scripture or revisit favorites" />
    
    <x-divider />
    
    <div class="">
        <!-- Toggle for Random Type -->
        <div class="border-b border-stroke">
            <ul class="grid items-stretch w-full md:grid-cols-2">
                <li>
                    <input type="radio" id="random-popular" wire:model.live="randomType" value="popular" class="hidden peer" />
                    <label for="random-popular" class="inline-flex items-center justify-between w-full px-5 py-2.5 text-textLight bg-bg border-r border-stroke cursor-pointer hover:text-text hover:bg-white peer-checked:text-blue peer-checked:bg-white transition-all duration-200">                           
                        <!-- <div class="block"> -->
                            <div class="w-full text-lg w-full text-center">Popular verses</div>
                        <!-- </div> -->
                    </label>
                </li>
                <li>
                    <input type="radio" id="random-truly" wire:model.live="randomType" value="random" class="hidden peer">
                    <label for="random-truly" class="inline-flex items-center justify-between w-full px-5 py-2.5 text-textLight bg-bg cursor-pointer hover:text-text hover:bg-white peer-checked:border-blue peer-checked:text-blue peer-checked:bg-white transition-all duration-200">
                        <!-- <div class="block"> -->
                            <div class="w-full text-lg w-full text-center">Truly random</div>
                        <!-- </div> -->
                    </label>
                </li>
            </ul>
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
            <div class="space-y-4 p-4">
                <div class="">
                    <div class="text-lg font-semibold text-text mb-2">{{ $verseData['reference'] ?? '' }}</div>
                    <div class="text-text leading-relaxed verse-content">
                        {!! $verseData['text'] ?? '' !!}
                    </div>
                </div>

                <style>
                    .verse-content sup {
                        font-size: 0.75em;
                        vertical-align: baseline;
                        position: relative;
                        top: -0.2em;
                        font-weight: 600;
                        color: #6b7280;
                        margin-right: 2px;
                    }
                </style>

                <!-- Action Buttons -->
                <div class="grid grid-cols-1 @auth md:grid-cols-3 @else md:grid-cols-2 @endauth gap-3">
                    <button wire:click="memorizeNow"
                            wire:loading.attr="disabled"
                            wire:target="memorizeNow"
                            class="flex items-center justify-center py-2.5 relative hover:bg-gray-50 active:bg-gray-100 gap-2 font-bold text-text transition-colors duration-200 border border-stroke rounded-lg disabled:opacity-50">
                        <span wire:loading.remove wire:target="memorizeNow">Memorize Now</span>
                        <span wire:loading wire:target="memorizeNow">Loading...</span>
                        <x-icon icon="arrow-narrow-right" class="w-4 h-4" color="#292D32" />
                    </button>
                    @auth
                        <button wire:click="addToMemorizeLater" 
                                wire:loading.attr="disabled" 
                                wire:target="addToMemorizeLater"
                                class="flex items-center justify-center py-2.5 relative hover:bg-gray-50 active:bg-gray-100 gap-2 font-bold text-text transition-colors duration-200 border border-stroke rounded-lg disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="addToMemorizeLater">Memorize Later</span>
                            <span wire:loading wire:target="addToMemorizeLater">Adding...</span>
                            <x-icon icon="clock" class="w-4 h-4" color="#292D32" />
                        </button>
                    @endauth
                    <button wire:click="getRandomVerse" 
                            class="flex items-center justify-center py-2.5 relative hover:bg-gray-50 active:bg-gray-100 gap-2 font-bold text-text transition-colors duration-200 border border-stroke rounded-lg">
                        <span>Get Another</span>
                        <x-icon icon="refresh" class="w-4 h-4" color="#292D32" />
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
