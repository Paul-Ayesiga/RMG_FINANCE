<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/login', navigate: true);
    }
}; ?>

<div>
    <x-slot:actions>
        <x-mary-button icon="o-power" class="btn-circle btn-ghost btn-xs" tooltip-left="logoff"  wire:click="logout" />
    </x-slot:actions>
</div>
