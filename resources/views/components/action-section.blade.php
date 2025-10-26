<div {{ $attributes->merge(['class' => '']) }}>
    <div class="px-4 py-4">
        <!-- Title Section -->
        <div class="mb-4">
            <h2 class="text-lg font-semibold text-gray-900">{{ $title }}</h2>
            <p class="mt-1 text-sm text-gray-600">{{ $description }}</p>
        </div>

        <x-divider />

        <!-- Content -->
        <div class="mt-4">
            {{ $content }}
        </div>
    </div>
</div>
