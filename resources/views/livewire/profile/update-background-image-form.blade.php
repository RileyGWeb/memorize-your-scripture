<x-form-section submit="updateBackground">
    <x-slot name="title">
        {{ __('Background Image') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Change your profile background image.') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <x-label for="background" value="{{ __('Background Theme') }}" />
            
            <div class="mt-2 grid grid-cols-2 gap-3 sm:grid-cols-3">
                @foreach($backgroundOptions as $key => $label)
                    <label class="cursor-pointer">
                        <input type="radio" wire:model="selectedBackground" value="{{ $key }}" class="sr-only">
                        <div class="h-20 rounded-lg border-2 {{ $selectedBackground === $key ? 'border-indigo-500' : 'border-gray-300' }} hover:border-indigo-400 bg-cover bg-center relative" 
                             style="background-image: url('{{ asset('images/' . $key . '.webp') }}')">
                            <div class="absolute inset-0 bg-black bg-opacity-30 rounded-lg flex items-center justify-center">
                                <span class="text-white font-medium text-sm">{{ $label }}</span>
                            </div>
                            @if($selectedBackground === $key)
                                <div class="absolute top-1 right-1 text-indigo-500">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                    </label>
                @endforeach
            </div>
            
            <x-input-error for="selectedBackground" class="mt-2" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-action-message class="me-3" on="saved">
            {{ __('Background Updated.') }}
        </x-action-message>

        <x-button wire:loading.attr="disabled">
            {{ __('Update Background') }}
        </x-button>
    </x-slot>
</x-form-section>
