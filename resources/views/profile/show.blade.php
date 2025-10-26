<x-layouts.app>
    <x-content-card>
        <x-content-card-title 
            title="Profile" 
            subtitle="Manage your account settings and preferences." 
        />
    </x-content-card>

    @if (Laravel\Fortify\Features::canUpdateProfileInformation())
        <x-content-card>
            <livewire:profile.update-profile-information-form lazy />
        </x-content-card>
    @endif

    @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
        <x-content-card>
            <livewire:profile.update-password-form lazy />
        </x-content-card>
    @endif

    @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
        <x-content-card>
            <livewire:profile.two-factor-authentication-form lazy />
        </x-content-card>
    @endif

    {{-- Background Image - Coming Soon --}}
    <x-content-card>
        <livewire:profile.update-background-image-form lazy />
    </x-content-card>

    @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
        <x-content-card>
            <livewire:profile.delete-user-form lazy />
        </x-content-card>
    @endif
</x-layouts.app>
