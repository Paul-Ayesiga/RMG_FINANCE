<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;
use Livewire\Attributes\On;
use Mary\Traits\Toast;

new class extends Component
{
    use Toast;
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/login', navigate: true);
    }

    public $notifications = [];
    public $unreadNotifications = 0;

    #[On('new-notification')]
    public function boot()
    {
        $this->loadNotifications();
    }

  
    #[On('new-notification')]
    public function loadNotifications()
    {
        cache()->forget('user_notifications_'.auth()->id());
        cache()->forget('user_unread_count_'.auth()->id());

        $this->notifications = auth()->user()->notifications()
            ->latest()
            ->take(5)
            ->get();

        $this->unreadNotifications = auth()->user()
            ->unreadNotifications
            ->count();
    }


    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->loadNotifications();
    }

     #[On('echo:system-notification,systemNotification')]
     #[On('echo:account-status,AccountStatusUpdated')]
    public function notifyNewNotification()
    {
        $this->toast(
            type: 'success',
            title: 'You have a new notification.',
            description: '',  // Description added
            position: 'toast-top toast-right',
            icon: 'o-information-circle',
            css: 'alert alert-success rounded-lg text-white shadow-lg p-1 flex items-center space-x-3',
            timeout: 3000,
            redirectTo: null
        );
    }

    public function markAsRead($notificationId)
    {
        auth()->user()->notifications()->findOrFail($notificationId)->markAsRead();
        $this->loadNotifications();
    }

     public function delete($notificationId)
    {
        auth()->user()->notifications()->findOrFail($notificationId)->delete();
        $this->loadNotifications();
    }

    public function clearAll()
    {
        auth()->user()->notifications()->delete();
        $this->loadNotifications();
    }

}; ?>

<div>
<nav x-data="{ open: false }">
    <!-- Settings Dropdown -->
    <div class="hidden sm:flex sm:items-center sm:ms-6 gap-4">
        <!-- Notifications Dropdown -->
        <x-dropdown align="right" width="480">
            <x-slot name="trigger">
                <button class="relative inline-flex items-center p-2 text-sm font-medium text-center text-gray-500 hover:text-gray-700 focus:outline-none transition ease-in-out duration-150 dark:text-gray-400 dark:hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                    </svg>
                    @if($unreadNotifications > 0)
                        <div class="absolute inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full -top-1 -right-1">
                            {{ $unreadNotifications }}
                        </div>
                    @endif
                </button>
            </x-slot>

            <x-slot name="content">
                <div wire:poll.1s  class="p-4" style="min-width: 480px">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">Notifications</h3>
                        @if($notifications->count() > 0)
                            <div class="flex gap-2">
                                <button wire:click.stop="markAllAsRead" class="text-xs text-blue-600 hover:text-blue-700 dark:text-blue-500 dark:hover:text-blue-400">
                                    Mark all as read
                                </button>
                                <button wire:click.stop="clearAll" class="text-xs text-red-600 hover:text-red-700 dark:text-red-500 dark:hover:text-red-400">
                                    Clear all
                                </button>
                            </div>
                        @endif
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-600">
                        @forelse($notifications as $notification)
                            <div class="py-3 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg px-2 relative group">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-8 w-8 {{ $notification->read_at ? 'text-gray-400' : 'text-blue-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $notification->data['title'] ?? 'Notification' }}
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $notification->data['message'] ?? '' }}
                                        </p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    <!-- Action buttons -->
                                    <div class="absolute right-2 top-3 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        @unless($notification->read_at)
                                            <button wire:click.stop="markAsRead('{{ $notification->id }}')" class="text-blue-600 hover:text-blue-700 dark:text-blue-500 dark:hover:text-blue-400">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                        @endunless
                                        <button wire:click.stop="delete('{{ $notification->id }}')" class="text-red-600 hover:text-red-700 dark:text-red-500 dark:hover:text-red-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400 py-4 text-center">
                                No notifications yet
                            </p>
                        @endforelse
                    </div>
                </div>
            </x-slot>
        </x-dropdown>

        <!-- Existing Profile Dropdown -->
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150 dark:bg-gray-700 dark:hover:text-white dark:text-white">
                    <!-- Check if the user is an admin or customer -->
                    @if (Auth::check())
                        <!-- Admin Avatar and Name -->
                        @if(!empty(auth()->user()->avatar))
                            <x-mary-avatar wire:poll.1s
                                image="{{ auth()->user()->avatar }}"
                                class="!w-10 mr-2"
                                x-data="{ name: '{{ asset(auth()->user()->avatar ?? 'user.png') }}'}"
                                x-on:profile-updated.window="name = $event.detail.avatar"
                            />
                        @else
                            <x-mary-avatar wire:poll
                                image="{{ asset('user.png') }}"
                                class="!w-10 mr-2"
                            />
                        @endif
                        <!-- Add role badge with dynamic colors -->
                        @foreach(auth()->user()->getRoleNames() as $role)
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $role === 'admin'
                                ? 'bg-red-50 text-red-700 ring-red-700/10 dark:bg-red-400/10 dark:text-red-400 dark:ring-red-400/30'
                                : 'bg-green-50 text-green-700 ring-green-700/10 dark:bg-green-400/10 dark:text-green-400 dark:ring-green-400/30'
                            }} mr-1">
                                {{ ucfirst($role) }}
                            </span>
                        @endforeach
                        {{-- <div x-data="{name: '{{ auth()->guard('admin')->user()->name }}'}"></div> --}}
                        {{-- <span x-text="userName"></span> --}}

                    @elseif (auth()->check())
                        <!-- Customer Avatar and Name -->
                        @if(!empty(auth()->user()->avatar))
                            <x-mary-avatar wire:poll
                                image="{{ auth()->user()->avatar }}"
                                class="!w-10 mr-2"
                                x-data="{ name: '{{ asset(auth()->user()->avatar ?? 'user.png') }}'}"
                                x-on:profile-updated.window="name = $event.detail.avatar"
                            />
                        @else
                            <x-mary-avatar wire:poll
                                image="{{ asset('user.png') }}"
                                class="!w-10 mr-2"
                            />
                        @endif
                        <span wire:poll>{{ auth()->user()->name }}</span>
                        {{-- <span wire:poll x-text="{ name: '{{ asset(auth()->user()->name)}}'}"></span> --}}
                    @endif
                    <div class="ms-1">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </button>
            </x-slot>

            <x-slot name="content">
                <!-- Profile Link -->
                <x-dropdown-link :href="route('profile')" wire:navigate>
                    {{ __('Profile') }}
                </x-dropdown-link>

                <!-- Logout Link -->
                <button wire:click="logout" class="w-full text-start">
                    <x-dropdown-link>
                        {{ __('Log Out') }}
                    </x-dropdown-link>
                </button>
            </x-slot>
        </x-dropdown>
    </div>

    <!-- Mobile view buttons -->
    {{-- <div class="lg:hidden flex items-center gap-2">
        <x-mary-button icon="o-bell" class="btn-circle btn-ghost btn-xs" tooltip-left="notifications"/>
        <x-mary-button icon="o-power" class="btn-circle btn-ghost btn-xs" tooltip-left="logoff" wire:click="logout"/>
    </div> --}}
</nav>

</div>

