<a 
    href="{{ $href }}"
    class="flex items-center justify-center items-center w-full py-2.5 relative hover:bg-gray-50 active:bg-gray-100 gap-2">
    <p class="font-bold">{{ $text }}</p>
    <img src="{{ asset('images/icons/svg/' . $icon . '.svg') }}" 
    alt="dropdown carat icon" class="w-3 h-3" />
</a>