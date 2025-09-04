
@php
    $userStreak = auth()->check() ? auth()->user()->getLoginStreakForDisplay() : null;
@endphp

<div class="px-4 py-2 relative">
    <h1>{{ $title }}</h1>
    @if (isset($subtitle) && $subtitle)
        <p>{{ $subtitle }}</p>
    @endif
    @if ($userStreak)
        <span class="text-textLight text-base italic absolute top-2 right-4">Streak: {{ $userStreak }} days</span>
    @endif
</div>