@props([
    'icon' => 'dropdown-arrow',
    'size' => 24,
    'color' => '#000',
])

@php
    $iconPath = public_path('images/icons/svg/' . $icon . '.svg');
    $svgContent = file_exists($iconPath) ? file_get_contents($iconPath) : '';
    
    // Apply color to the SVG content
    if ($color && $svgContent) {
        $svgContent = preg_replace('/stroke="[^"]*"/', 'stroke="' . $color . '"', $svgContent);
        // Also handle currentColor
        $svgContent = str_replace('currentColor', $color, $svgContent);
    }
@endphp

@if($svgContent)
    {!! $svgContent !!}
@else
    <!-- Fallback if SVG not found -->
    <div class="w-6 h-6 bg-gray-300 rounded" title="Icon not found: {{ $icon }}"></div>
@endif
