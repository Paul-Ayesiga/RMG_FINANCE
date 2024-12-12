<div x-data="{ open: false, swipedId: null }" class="relative">
    <!-- Notification Button with Count -->
    <button @click="open = !open" class="relative btn btn-circle btn-ghost btn-sm">
        <x-mary-icon name="o-bell" />
        @if($unreadNotifications > 0)
            <span wire:poll.1s class="absolute top-0 right-0 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-600 rounded-full transform translate-x-1 -translate-y-1">
                {{ $unreadNotifications }}
            </span>
        @endif
    </button>

    <!-- Drawer -->
    <div x-show="open" x-transition class="fixed inset-0 z-50 flex justify-end items-start bg-gray-800 bg-opacity-50">
        <div @click.away="open = false" class="bg-white w-[500px] h-full p-5 overflow-y-auto">
            <h3 class="text-lg font-semibold mb-4">Notifications</h3>

            <!-- Mark All as Read Button -->
            @if ($unreadNotifications > 0)
                <button wire:click="markAllAsRead"
                    class="w-full bg-blue-600 text-white py-2 rounded-lg mb-4">
                    Mark All as Read
                </button>
            @endif

            <!-- Notifications List -->
            <ul>
                @foreach ($notifications as $notification)
                    <li x-data="{ swiped: false }"
                        @keydown.window="
                            if ($event.key === 'ArrowLeft') swiped = true;
                            if ($event.key === 'ArrowRight') swiped = false;
                        "
                        class="py-3 px-2 relative group rounded-lg">

                        <div class="flex items-center">
                            <!-- Icon -->
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 {{ $notification->read_at ? 'text-gray-400' : 'text-blue-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>

                            <!-- Notification Details -->
                            <div class="ml-3 flex-1 {{ $notification->read_at ? 'opacity-50' : '' }}">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $notification->data['title'] ?? 'Notification' }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 truncate" title="{{ $notification->data['message'] ?? 'No message' }}">
                                    {{ Str::limit($notification->data['message'] ?? 'No message', 20, '...') }}
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div
                                x-show="swiped"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 translate-x-5"
                                x-transition:enter-end="opacity-100 translate-x-0"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 translate-x-0"
                                x-transition:leave-end="opacity-0 translate-x-5"
                                class="flex space-x-2 absolute right-0 top-1/2 transform -translate-y-1/2"
                            >
                                <!-- Mark as Read -->
                                @if (is_null($notification->read_at))
                                    <button
                                        wire:click="markAsRead('{{ $notification->id }}')"
                                        class="bg-green-600 text-white py-1 px-3 rounded-full text-xs shadow-md hover:bg-green-700 hover:shadow-lg transform hover:scale-105 transition-all duration-200 ease-in-out"
                                    >
                                        Mark as Read
                                    </button>
                                @endif

                                <!-- Delete -->
                                <button
                                    wire:click="delete('{{ $notification->id }}')"
                                    class="bg-red-600 text-white py-1 px-3 rounded-full text-xs shadow-md hover:bg-red-700 hover:shadow-lg transform hover:scale-105 transition-all duration-200 ease-in-out"
                                >
                                    Delete
                                </button>
                        </div>

                    </li>
                @endforeach
            </ul>

            <!-- Close Drawer -->
            <div class="mt-4">
                <button @click="open = false" class="w-full bg-red-600 text-white py-2 rounded-lg">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
