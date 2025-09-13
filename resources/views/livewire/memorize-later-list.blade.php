<div class="w-full">
    @if($verses->count() > 0 && auth()->check())
        <div class="bg-bg rounded-xl shadow-sm border border-gray-200">
            <div class="mb-4 p-3 pb-0">
                <h3 class="font-bold text-lg text-gray-800">Memorize Later...</h3>
                <p class="text-gray-600 text-sm">Grab a verse you've added to Memorize Later!</p>
            </div>

            <div class="grid grid-cols-2">
                @foreach($verses as $verse)
                    <div class="group cursor-pointer border-r border-b border-stroke last:border-r-0 {{ $loop->index % 2 == 0 ? '' : 'border-r-0' }} {{ $loop->index < 2 ? 'border-t' : '' }}" wire:click="selectVerse({{ $verse->id }})">
                        <div class="bg-bg hover:bg-gray-100 hover:shadow-md p-3 transition-colors duration-200 relative">
                            <!-- Verse Reference -->
                            <div class="font-semibold text-gray-800 text-sm mb-1">
                                {{ $this->formatVerseReference($verse) }}
                            </div>

                            <!-- Note Preview -->
                            @if($verse->note)
                                <div class="text-xs text-gray-600 mb-2">
                                    <span class="font-medium">Note - {{ $this->formatRelativeDate($verse->added_at) }}</span>
                                </div>
                                <div class="text-xs text-gray-700">
                                    {{ $this->formatNotePreview($verse->note) }}
                                </div>
                            @else
                                <div class="text-xs text-gray-500">
                                    Added {{ $this->formatRelativeDate($verse->added_at) }}
                                </div>
                            @endif

                            <!-- Click indicator - always show -->
                            <div class="absolute bottom-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                @endforeach
                
                @if($verses->count() % 2 == 1)
                    <!-- Placeholder for odd number of items -->
                    <div class="border-b border-stroke {{ $verses->count() < 2 ? 'border-t' : '' }}">
                        <div class="p-3 h-full bg-bg"></div>
                    </div>
                @endif
            </div>

            @if($verses->count() > 0)
                <div class="p-3">
                    <p class="text-sm text-blue-800">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Click a verse to start memorizing it!
                    </p>
                </div>
            @endif
        </div>
    @endif
</div>
