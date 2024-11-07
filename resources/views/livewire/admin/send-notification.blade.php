<div class="p-4 sm:p-6 lg:p-8">
    @if (session()->has('success'))
        <div class="mb-4">
            <x-mary-alert type="success" message="{{ session('success') }}" />
        </div>
    @endif
    
    <div class="max-w-3xl mx-auto">
        <x-mary-card title="Send System Notification">
            <div class="space-y-4">
                <!-- Notification Type -->
                <div>
                    <x-mary-select 
                        wire:model="type" 
                        class="w-full" 
                        label="Notification Type"
                        :options="[
                            ['id' => 'info', 'name' => 'Information'],
                            ['id' => 'success', 'name' => 'Success'], 
                            ['id' => 'warning', 'name' => 'Warning'],
                            ['id' => 'error', 'name' => 'Error']
                        ]"
                    />
                </div>

                <!-- Title -->
                <div>
                    <x-mary-input 
                        wire:model="title" 
                        type="text" 
                        class="w-full" 
                        label="Title"
                        placeholder="Enter notification title"
                    />
                    @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Message -->
                <div>
                    <x-mary-textarea 
                        wire:model="message" 
                        class="w-full" 
                        label="Message"
                        rows="4" 
                        placeholder="Enter notification message"
                    />
                    @error('message') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Recipients Selection -->
                <div class="space-y-4">
                    <div class="flex items-center space-x-2">
                        <x-mary-checkbox 
                            wire:model="notifyAll" 
                            id="notify-all-checkbox"
                            label="Notify All Users"
                        />
                    </div>

                    @unless($notifyAll)
                        <!-- Role Filter -->
                        <div>
                            <x-mary-select 
                                wire:model.live="roleFilter" 
                                class="w-full" 
                                label="Filter by Role"
                                :options="[
                                    ['id' => '', 'name' => 'All Roles'],
                                    ...$roles->map(fn($role) => [
                                        'id' => $role->name,
                                        'name' => ucfirst($role->name)
                                    ])->toArray()
                                ]"
                            />
                        </div>

                        <!-- User Search -->
                        <div>
                            <x-mary-input 
                                wire:model.live.debounce.300ms="search" 
                                type="search" 
                                class="w-full"
                                label="Search Users"
                                placeholder="Search by name or email"
                            />
                        </div>

                        <!-- Users List -->
                        <div class="border dark:border-gray-700 rounded-lg max-h-60 overflow-y-auto">
                            @foreach($users as $user)
                                <div class="flex items-center space-x-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-800 {{ !$loop->last ? 'border-b dark:border-gray-700' : '' }}">
                                    <x-mary-checkbox 
                                        wire:model="selectedUsers" 
                                        value="{{ $user->id }}"
                                        id="user-checkbox-{{ $user->id }}"
                                       
                                    />
                                    <div class="flex items-center gap-2">
                                        <x-mary-avatar image="{{ $user->avatar ?? asset('user.png') }}" class="!w-8"/>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $user->name }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $user->email }}
                                            </div>
                                        </div>
                                        <!-- Role badges -->
                                        <div class="flex gap-1">
                                            @foreach($user->getRoleNames() as $role)
                                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $role === 'admin' 
                                                    ? 'bg-red-50 text-red-700 ring-red-700/10 dark:bg-red-400/10 dark:text-red-400 dark:ring-red-400/30'
                                                    : 'bg-green-50 text-green-700 ring-green-700/10 dark:bg-green-400/10 dark:text-green-400 dark:ring-green-400/30' 
                                                }}">
                                                    {{ ucfirst($role) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endunless
                </div>

                <!-- Send Button -->
                <div class="flex justify-end">
                    <x-mary-button 
                        wire:click="sendNotification"
                        wire:loading.attr="disabled"
                        class="btn-primary"
                    >
                        <span wire:loading.remove>Send Notification</span>
                        <span wire:loading>Sending...</span>
                    </x-mary-button>
                </div>
            </div>
        </x-mary-card>
    </div>
</div>
