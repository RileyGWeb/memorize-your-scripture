<x-layouts.app>
    @guest
        @if(!session('new_user_card_dismissed'))
            <div id="new-user-card" class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg relative">
                <div class="flex items-center justify-between">
                    <div>
                        <p><strong class="font-semibold">New here?</strong></p>
                        <p>Click "memorize scripture" to get started!</p>
                    </div>
                    <button id="dismiss-card" class="text-green-600 hover:text-green-800 ml-4">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        @endif
    @endguest

    <x-content-card>
        <x-content-card-title 
            title="Welcome{{ auth()->check() ? ', ' . auth()->user()->name : '' }}!" 
            subtitle="We're glad you're here." 
            :streak=true 
        />

        <x-divider />

        <livewire:memorize-later />
    </x-content-card>

    <x-content-card>
        <x-content-card-button href="/memorization-tool" text="Memorize scripture" icon="arrow-narrow-right" iconSize="lg" wire:navigate />

        <x-divider />

        <x-content-card-button href="/bank" text="Your memory bank" icon="bank" iconSize="md" wire:navigate />

        <x-divider />

        <x-content-card-button href="/quiz" text="Get quizzed" icon="academic-cap" iconSize="lg" wire:navigate />
    </x-content-card>

    <!-- Random Verse Card -->
    <livewire:random-verse />

    <!-- Memorize Later List -->
    <livewire:memorize-later-list lazy>
        <div class="w-full">
            <div class="bg-bg rounded-xl shadow-sm border border-gray-200">
                <div class="mb-4 p-3 pb-0">
                    <h3 class="font-bold text-lg text-gray-800">Memorize Later...</h3>
                    <p class="text-gray-600 text-sm">Grab a verse you've added to Memorize Later!</p>
                </div>
                <div class="flex items-center justify-center py-8">
                    <div class="animate-pulse text-gray-500">Loading your saved verses...</div>
                </div>
            </div>
        </div>
    </livewire:memorize-later-list>

    <!-- Daily Quiz -->
    @auth
        <livewire:daily-quiz lazy>
            <x-content-card>
                <x-content-card-title title="Daily Quiz" />
                <x-divider />
                <div class="flex items-center justify-center py-8">
                    <div class="animate-pulse text-gray-500">Loading today's quiz...</div>
                </div>
            </x-content-card>
        </livewire:daily-quiz>
    @endauth

    @guest
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const dismissButton = document.getElementById('dismiss-card');
                const newUserCard = document.getElementById('new-user-card');
                
                if (dismissButton && newUserCard) {
                    dismissButton.addEventListener('click', function() {
                        fetch('{{ route('dismiss-new-user-card') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                newUserCard.style.display = 'none';
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                    });
                }
            });
        </script>
    @endguest
</x-layouts.app>