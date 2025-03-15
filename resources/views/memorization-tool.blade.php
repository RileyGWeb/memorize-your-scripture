{{-- resources/views/memorization-tool.blade.php --}}
<x-layouts.app>
    <x-content-card>
        <x-content-card-title title="Memorization Tool" />
        <x-divider />

        @if(session('error'))
            <p class="text-red-500">{{ session('error') }}</p>
        @endif

        {{-- The VersePicker Livewire component --}}
        <livewire:verse-picker />
    </x-content-card>
</x-layouts.app>
