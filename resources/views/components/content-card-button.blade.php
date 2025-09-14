@props([
    'href' => '#',
    'text' => '',
    'icon' => '',
    'iconSize' => 'md',
])

@php
    switch ($iconSize) {
        case 'sm':
            $iconClass = 'w-3 h-3';
            break;
        case 'lg':
            $iconClass = 'w-5 h-5';
            break;
        default:
            $iconClass = 'w-4 h-4';
            break;
    }
@endphp

<a 
    href="{{ $href }}"
    {{ $attributes->merge(['class' => 'flex items-center justify-center w-full py-2.5 relative hover:bg-gray-50 active:bg-gray-100 gap-2']) }}
>
    <p class="font-bold">{{ $text }}</p>
    <x-icon :icon="$icon" class="{{ $iconClass }}" color="#000" />
</a>
