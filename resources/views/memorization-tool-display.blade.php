{{-- resources/views/memorization-tool-display.blade.php --}}
<x-layouts.app>
    <x-content-card>
        <x-content-card-title title="Verse Results" />
        <x-divider />

        @if(session('error'))
            <p class="text-red-500">{{ session('error') }}</p>
        @endif

        @php
            // The controller passes $verseData
        @endphp

        @if(isset($verseData['data']) && is_array($verseData['data']))
            @foreach($verseData['data'] as $passage)
                <div class="my-4 p-4 bg-gray-100 rounded">
                    @if(isset($passage['reference']))
                        <p class="text-xl font-semibold">{{ $passage['reference'] }}</p>
                    @endif

                    @if(isset($passage['content']))
                        <div class="leading-relaxed mt-2">
                            {!! $passage['content'] !!}
                        </div>
                    @endif
                </div>
            @endforeach
        @else
            <p>No verse content found.</p>
        @endif

        {{-- Possibly a link back to the picker so they can select another verse --}}
        <x-content-card-button :href="route('memorization-tool.picker')" text="Pick Another Verse" icon="arrow" />
    </x-content-card>
</x-layouts.app>
