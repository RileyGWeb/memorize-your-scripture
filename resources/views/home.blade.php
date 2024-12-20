<x-layouts.app>
    <x-content-card>
        <x-content-card-title title="Welcome!" subtitle="We'rew glad you're here." :streak=true />

        <x-divider />

        <livewire:memorize-later />
    </x-content-card>

    <x-content-card>
        <x-content-card-button text="How to get started" icon="dropdown" link="/memorization-tool" />

        <x-divider />

        <x-content-card-button text="Memorize scripture" icon="dropdown" />

        <x-divider />

        <x-content-card-button text="Your memory bank" icon="dropdown" />

        <x-divider />

        <x-content-card-button text="Get quizzed" icon="dropdown" />
        
    </x-content-card>
</x-layouts.app>