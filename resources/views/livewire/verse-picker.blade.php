<div>
    <input type="text" wire:model.live="input" class="border p-2" placeholder="e.g. John 3:16 -18, 22, 24-27">

    @if($book && $chapter && !empty($verseRanges))
    <div id="selection" class="mt-4 p-4 border rounded">
        <div>
            <p class="font-semibold">Your Selection</p>
        </div>
        <div class="mt-2">
            <p class="font-bold">Book:</p>
            <p>{{ $book }}</p>
        </div>
        <div class="mt-2">
            <p class="font-bold">Chapter & Verse(s):</p>
            <p>
                {{ $chapter }}:
                @foreach($verseRanges as $i => $range)
                    @if($range[0] === $range[1])
                        {{ $range[0] }}
                    @else
                        {{ $range[0] }}-{{ $range[1] }}
                    @endif
                    @if(!$loop->last), @endif
                @endforeach
            </p>
        </div>
    </div>
    @endif
</div>
