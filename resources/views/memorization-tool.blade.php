{{-- resources/views/memorization-tool.blade.php --}}
<x-layouts.app>
    <x-content-card>
        <x-content-card-button href="/" text="Back to Home" icon="arrow-narrow-left" iconSize="lg" wire:navigate />
    </x-content-card>
    <x-content-card>
        <x-content-card-title title="Memorization Tool" />
        <x-divider />

        @if(session('error'))
            <p class="text-red-500">{{ session('error') }}</p>
        @endif

        {{-- The Unified VersePicker Livewire component --}}
        <livewire:unified-verse-picker 
            :showNextButton="true" 
            :nextButtonRoute="route('memorization-tool.fetch')" 
            placeholder="John 3:16-18" 
        />
    </x-content-card>

    <!-- Random Verse Generator -->
    <livewire:random-verse />

    <!-- Memorize Later List for verse selection -->
    <livewire:memorize-later-list lazy>
        <div class="w-full">
            <div class="bg-bg rounded-xl shadow-sm border border-gray-200">
                <div class="mb-4 p-3 pb-0">
                    <h3 class="font-bold text-lg text-gray-800">Memorize Later...</h3>
                    <p class="text-gray-600 text-sm">Grab a verse you've added to Memorize Later!</p>
                </div>
                <div class="flex items-center justify-center py-8">
                    <div class="animate-pulse text-gray-500">Loading your saved verses...</div>
                </div>
            </div>
        </div>
    </livewire:memorize-later-list>
</x-layouts.app>
