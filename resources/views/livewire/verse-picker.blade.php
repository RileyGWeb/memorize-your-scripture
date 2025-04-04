<div>
    <div class="flex items-center border-b border-stroke">
        <p class="p-2 border-r border-stroke h-full whitespace-nowrap">Type in your verse(s):</p>
        <input type="text" wire:model.live="input" class="text-base bg-transparent border-none text-center w-full focus:ring-0" placeholder="John 3:16-18, 22">
    </div>
    <div id="selection" class="flex items-center border-b border-stroke">
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
        <p class="text-red-500 mt-2">{{ $errorMessage }}</p>
    </div>
    @endif
    @if($book && $chapter && count($verseRanges) > 0)
        <x-content-card-button :href="route('memorization-tool.fetch')" text="Next" icon="arrow" />
    @endif
</div>
