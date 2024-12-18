<button class="flex items-center justify-center items-center w-full py-2.5 relative hover:bg-slate-100 active:bg-slate-400 gap-2">
    <p class="font-bold">{{ $text }}</p>
    <img src="{{ asset('images/icons/svg/' . $icon . '.svg') }}" 
    alt="dropdown carat icon" class="w-3 h-3" />
</button>