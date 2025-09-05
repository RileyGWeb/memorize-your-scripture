
@php
    $userStreak = auth()->check() ? auth()->user()->getLoginStreakForDisplay() : null;
    $showStreak = isset($streak) && $streak && $userStreak;
@endphp

<div class="px-4 py-2 relative">
    <h1>{{ $title }}</h1>
    @if (isset($subtitle) && $subtitle)
        <p>{{ $subtitle }}</p>
    @endif
    @if ($showStreak)
        <span class="text-textLight text-base italic absolute top-2 right-4">Streak: {{ $userStreak }} days</span>
    @endif
</div>