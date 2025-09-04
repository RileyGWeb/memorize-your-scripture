<div>
    <!-- Success Message -->
    @if($successMessage)
        <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-400 text-green-800 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                {{ $successMessage }}
            </div>
        </div>
    @endif

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

    <!-- Expanded Form -->
    @if($isExpanded)
        <div class="p-4 border-t-2 border-gray-200"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100">
            
            <form wire:submit="saveVerse" class="space-y-4">
                <!-- Verse Input Section -->
                <div class="space-y-3">
                    <div>
                        <h3 class="font-bold text-lg text-gray-800">Add to Memorize Later</h3>
                        <p class="text-gray-600 text-sm">On the go? Don't let those verses escape!</p>
                    </div>
                    
                    <div class="relative">
                        <input wire:model="verse" 
                               type="text" 
                               placeholder="John 3:16-18"
                               class="w-full py-3 pl-4 pr-12 bg-bg border-2 border-gray-900 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 text-gray-800"
                               required>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500">Examples: "John 3:16", "Psalms 23:1-6", "1 John 4:7"</p>
                    @error('verse') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-200"></div>

                <!-- Note Input Section -->
                <div class="space-y-3">
                    <div>
                        <h3 class="font-bold text-lg text-gray-800">Make a quick note</h3>
                        <p class="text-gray-600 text-sm">Memorizing something more than just a verse? Jot it down below...</p>
                    </div>
                    
                    <textarea wire:model="note" 
                              placeholder="What are you thinking?... (optional)"
                              rows="4"
                              class="w-full p-4 bg-bg border-2 border-gray-900 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none transition-all duration-200 text-gray-800"></textarea>
                    @error('note') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Save Button -->
                <div class="flex justify-center pt-2">
                    <button type="submit" 
                            class="bg-gray-800 hover:bg-gray-900 active:bg-black text-white font-bold py-4 px-8 rounded-lg transition-all duration-200 focus:ring-4 focus:ring-gray-300 focus:outline-none shadow-lg hover:shadow-xl">
                        Save Verse
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
