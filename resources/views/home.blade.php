<x-layouts.app>
    <x-content-card>
        <x-content-card-title title="Welcome!" subtitle="We'rew glad you're here." :streak=true />

        <x-divider />

        <livewire:memorize-later />
    </x-content-card>

    <x-content-card>
        <x-content-card-button href="what" text="How to get started" icon="question-mark-circle" iconSize="md" />

        <x-divider />

        <x-content-card-button href="/memorization-tool" text="Memorize scripture" icon="arrow-narrow-right" iconSize="lg" />

        <x-divider />

        <x-content-card-button href="what" text="Your memory bank" icon="bank" iconSize="md" />

        <x-divider />

        <x-content-card-button href="what" text="Get quizzed" icon="academic-cap" iconSize="lg" />
        
    </x-content-card>
</x-layouts.app>