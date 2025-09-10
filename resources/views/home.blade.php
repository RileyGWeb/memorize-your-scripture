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
        <x-content-card-button href="/memorization-tool" text="Memorize scripture" icon="arrow-narrow-right" iconSize="lg" />

        <x-divider />

        <x-content-card-button href="/bank" text="Your memory bank" icon="bank" iconSize="md" />

        <x-divider />

        <x-content-card-button href="/quiz" text="Get quizzed" icon="academic-cap" iconSize="lg" />
        
    </x-content-card>

    <!-- Memorize Later List -->
    <livewire:memorize-later-list />

    <!-- Daily Quiz -->
    <livewire:daily-quiz />

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