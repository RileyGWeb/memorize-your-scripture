<?php

namespace App\Livewire\Profile;

use Livewire\Component;

class UpdateBackgroundImageForm extends Component
{
    public $backgroundOptions = [
        'sunrise' => 'Sunrise',
        'mountain' => 'Mountain',
        'ocean' => 'Ocean',
        'forest' => 'Forest',
        'desert' => 'Desert',
    ];

    public $selectedBackground = 'sunrise';

    public function mount()
    {
        $this->selectedBackground = auth()->user()->background_image ?? 'sunrise';
    }

    public function updateBackground()
    {
        $this->validate([
            'selectedBackground' => 'required|in:sunrise,mountain,ocean,forest,desert',
        ]);

        auth()->user()->update([
            'background_image' => $this->selectedBackground,
        ]);

        $this->dispatch('saved');
    }

    public function render()
    {
        return view('livewire.profile.update-background-image-form');
    }
}
