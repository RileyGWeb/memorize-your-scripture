@props([
    'type' => 'button',
    'variant' => 'solid',  // default to 'solid' if not passed
])

@php
    // Common base classes that both variants share:
    $baseClasses = 'inline-flex items-center px-4 py-1.5
                    rounded-md font-semibold text-xs uppercase tracking-widest
                    focus:outline-none focus:ring-2 focus:ring-indigo-500
                    focus:ring-offset-2
                    disabled:opacity-50 transition ease-in-out duration-150';

    if ($variant === 'solid') {
        $variantClasses = 'bg-gray-800
                           border border-transparent
                           text-white
                           hover:bg-gray-700
                           focus:bg-gray-700
                           active:bg-gray-900';
    } else {
        $variantClasses = 'bg-transparent
                           border border-gray-800
                           text-gray-800
                           hover:bg-gray-800
                           hover:text-white
                           focus:bg-gray-700
                           active:bg-gray-900';
    }
@endphp

<button {{ $attributes->merge([
    'type' => $type,
    'class' => $baseClasses . ' ' . $variantClasses
]) }}>
    {{ $slot }}
</button>
