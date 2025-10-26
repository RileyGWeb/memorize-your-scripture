<x-action-section>
    <x-slot name="title">
        {{ __('Background Image') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Customize your app background image.') }}
    </x-slot>

    <x-slot name="content">
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-900">Coming Soon</h3>
            <p class="mt-1 text-sm text-gray-500">Background image customization will be available in a future update.</p>
        </div>
    </x-slot>
</x-action-section>
