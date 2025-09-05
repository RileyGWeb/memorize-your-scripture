<div>
    <!-- Toggle Button -->
    <button wire:click="toggleExpanded" 
            class="flex items-center justify-center w-full py-2.5 relative hover:bg-slate-100 active:bg-slate-200 rounded-lg transition-colors duration-200">
        <p class="font-bold">Add to Memorize Later</p>
        <div class="absolute right-4 transition-transform duration-200 {{ $isExpanded ? 'rotate-180' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>
    </button>

    <!-- Success Message (between cards) -->
    @if($successMessage)
        <div class="mt-4 p-4 bg-green-50 border-l-4 border-green-400 text-green-800 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                {{ $successMessage }}
            </div>
        </div>
    @endif

    <!-- Expanded Form -->
    @if($isExpanded)
        <div class="p-4 border-t border-stroke"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100">
            
            <form wire:submit="saveVerse" class="space-y-4">
                <!-- Verse Picker Section -->
                <div class="space-y-3">
                    <div>
                        <h3 class="font-bold text-lg text-gray-800">Add to Memorize Later</h3>
                        <p class="text-gray-600 text-sm">On the go? Don't let those verses escape!</p>
                    </div>
                    
                    <!-- Verse Picker UI -->
                    <div class="border border-stroke rounded-lg overflow-hidden">
                        <div class="flex items-center border-b border-stroke">
                            <p class="p-2 border-r border-stroke h-full whitespace-nowrap">Type in your verse(s):</p>
                            <input type="text" wire:model.live="verse" class="text-base bg-transparent border-none text-center w-full focus:ring-0" placeholder="John 3:16-18">
                        </div>
                        <div class="flex items-center border-b border-stroke">
                            <div class="flex flex-col items-center p-2 border-r border-stroke h-full">
                                <p class="flex-shrink">Your Selection</p>
                            </div>
                            <div class="flex flex-col grow items-center p-2 border-r border-stroke">
                                <p class="font-bold">Book:</p>
                                <p>{{ $book }}</p>
                            </div>
                            <div class="flex flex-col grow items-center p-2">
                                <p class="font-bold">Chapter & Verse(s):</p>
                                <p> {{-- unformatted because it was adding a space after the : --}}
                                    @if ($chapter){{ $chapter . ':'}}@endif@foreach($verseRanges as $i => $range)@if($range[0] === $range[1]){{ $range[0] }}@else{{ $range[0] }}-{{ $range[1] }}@endif@if(!$loop->last),@endif@endforeach
                                </p>
                            </div>
                        </div>

                        @if($errorMessage)
                        <div class="p-2">
                            @if($suggestedBook)
                                <p class="text-red-500 mt-2">
                                    {{ substr($errorMessage, 0, strpos($errorMessage, 'Did you mean')) }}Did you mean 
                                    <button 
                                        wire:click="applySuggestion" 
                                        type="button"
                                        class="font-bold text-blue-600 hover:text-blue-800 hover:underline cursor-pointer transition-colors duration-200 px-1 py-0.5 rounded hover:bg-blue-50"
                                    >
                                        '{{ $suggestedBook }}'
                                    </button>?
                                </p>
                            @else
                                <p class="text-red-500 mt-2">{{ $errorMessage }}</p>
                            @endif
                        </div>
                        @endif
                    </div>
                    @error('verse') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Note Input Section -->
                <div class="space-y-3">
                    <div>
                        <h3 class="font-bold text-lg text-gray-800">Note (optional)</h3>
                    </div>
                    
                    <textarea wire:model="note" 
                              placeholder="Why do you like this verse? (optional)"
                              rows="4"
                              class="w-full p-4 bg-bg border border-gray-900 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none transition-all duration-200 text-gray-800"></textarea>
                    @error('note') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Save Button -->
                <div class="flex justify-center pt-2">
                    <button type="submit" 
                            class="bg-gray-800 hover:bg-gray-900 active:bg-black text-white font-bold py-2 px-8 rounded-lg transition-all duration-200 focus:ring-4 focus:ring-gray-300 focus:outline-none shadow-lg hover:shadow-xl"
                            @if(!$book || !$chapter || empty($verseRanges)) disabled @endif>
                        Save Verse
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
