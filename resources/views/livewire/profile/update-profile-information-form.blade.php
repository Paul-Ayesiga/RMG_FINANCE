<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;
use Livewire\Volt\Component;

new class extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public $photo;
    public User $user;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        // $this->photo = Auth::user()->avatar;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'photo' => ['nullable','image','max:3034']
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($this->photo) {
            $url = $this->photo->store('users', 'public');
            $user->update(['avatar' => "/storage/$url"]);
        }

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-white">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-white">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form wire:submit="updateProfileInformation" class="mt-6 space-y-6">
            {{-- <x-mary-card title="Cover Image"  separator class="shadow-lg bg-white"> --}}
                @if(!empty(auth()->user()->avatar))
                <x-mary-file wire:model="photo" accept="image/png image/jpeg" crop-after-change hint="click to change">
                    <img src="{{ auth()->user()->avatar }}" class="h-40 rounded-lg" />
                </x-mary-file>
                @else
                 <x-mary-file wire:model="photo" accept="image/png image/jpeg" crop-after-change hint="click to change">
                    <img src="{{ asset('user.png') }}" class="h-40 rounded-lg" />
                </x-mary-file>
                @endif
            {{-- </x-mary-card> --}}
        <div>
            <x-input-label for="name" :value="__('Name')" class="dark:text-white" />
            <x-text-input wire:model="name" id="name" name="name" type="text" class="mt-1 block w-full py-2 px-2"  autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" class="dark:text-white" />
            <x-text-input wire:model="email" id="email" name="email" type="email" class="mt-1 block w-full  py-2 px-2" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800 dark:text-white">
                        {{ __('Your email address is unverified.') }}

                        <button wire:click.prevent="sendVerification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            {{-- <x-primary-button>{{ __('Save') }}</x-primary-button> --}}
             <x-mary-button class="bg-gradient-to-r from-blue-500 to-gray-600 text-white font-bold  btn-sm" icon=""  spinner="updateProfileInformation" type="submit">
                {{ __('Update') }}
            </x-mary-button>

            <x-action-message class="me-3 dark:text-green-400" on="profile-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</section>
