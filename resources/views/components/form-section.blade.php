@props(['submit'])

<div {{ $attributes->merge(['class' => '']) }}>
    <form wire:submit="{{ $submit }}">
        <div class="px-4 py-4">
            <!-- Title Section -->
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-gray-900">{{ $title }}</h2>
                <p class="mt-1 text-sm text-gray-600">{{ $description }}</p>
            </div>

            <x-divider />

            <!-- Form Content -->
            <div class="mt-4 space-y-4">
                {{ $form }}
            </div>
        </div>

        @if (isset($actions))
            <x-divider />
            <div class="flex items-center justify-end px-4 py-3 bg-gray-50/50">
                {{ $actions }}
            </div>
        @endif
    </form>
</div>
