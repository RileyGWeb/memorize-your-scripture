@props([
    'icon' => 'dropdown-arrow',
    'size' => 24,
    'color' => null,
])

<img 
    src="{{ asset('images/icons/svg/' . $icon . '.svg') }}"
    width="{{ $size }}"
    height="{{ $size }}"
    {{ $attributes->merge(['class' => $color ? 'text-' . $color : '']) }}
    alt="{{ $icon }}"
>
