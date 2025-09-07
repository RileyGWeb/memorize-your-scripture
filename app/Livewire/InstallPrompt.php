<?php

namespace App\Livewire;

use Livewire\Component;

class InstallPrompt extends Component
{
    public $showInstallPrompt = false;

    public function mount()
    {
        // The actual install prompt visibility will be handled by JavaScript
        // This component just provides the UI structure
        $this->showInstallPrompt = true;
    }

    public function hidePrompt()
    {
        $this->showInstallPrompt = false;
    }

    public function render()
    {
        return view('livewire.install-prompt');
    }
}
