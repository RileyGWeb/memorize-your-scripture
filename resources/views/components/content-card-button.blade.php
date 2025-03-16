@props([
    'href' => '#',
    'text' => '',
    'icon' => '',
    'iconSize' => 'md', // default
])

@php
    // Map the iconSize prop to Tailwind classes
    switch ($iconSize) {
        case 'sm':
            $iconClass = 'w-3 h-3';
            break;
        case 'lg':
            $iconClass = 'w-5 h-5';
            break;
        default:
            // 'md' or anything else
            $iconClass = 'w-4 h-4';
            break;
    }
@endphp

<a 
    href="{{ $href }}"
    class="flex items-center justify-center w-full py-2.5 relative hover:bg-gray-50 active:bg-gray-100 gap-2"
>
    <p class="font-bold">{{ $text }}</p>
    <img src="{{ asset('images/icons/svg/' . $icon . '.svg') }}" 
         alt="{{ $icon }} icon" 
         class="{{ $iconClass }}" />
</a>
