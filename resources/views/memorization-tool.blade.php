{{-- resources/views/memorization-tool.blade.php --}}
<x-layouts.app>
    <x-content-card>
        <x-content-card-button href="/" text="Back to Home" icon="arrow-narrow-left" iconSize="lg" />
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

    <!-- Memorize Later List for verse selection -->
    <livewire:memorize-later-list :showOnMemorizationTool="true" />
</x-layouts.app>
