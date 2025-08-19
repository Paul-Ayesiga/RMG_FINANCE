<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $password = '';
    public bool $confirmUserDeleteModal = false;

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }

}; ?>

<section class="space-y-0">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-white">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-white mb-5">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <x-danger-button  @click="$wire.confirmUserDeleteModal = true">{{ __('Delete Account') }}</x-danger-button>

    <x-mary-modal  wire:model="confirmUserDeleteModal" class="dark:bg-inherit">
        <form wire:submit="deleteUser" class="p-6">

            <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-white">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only dark:text-white" />

                <x-mary-password
                    password-icon="o-lock-closed" password-visible-icon="o-lock-open"
                    wire:model="password"
                    id="password"
                    name="password"
                    type="password"
                    class="w-full h-10 px-4 py-2 text-sm bg-gray-100 dark:bg-inherit border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-gray-400"
                    placeholder="{{ __('Password') }}"
                />

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button  @click="$wire.confirmUserDeleteModal = false">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Delete Account') }}
                </x-danger-button>
            </div>
        </form>
    </x-mary-modal>
</section>
