<div class="p-2 sm:p-4">
    <!-- Header -->
    <x-mary-header title="Role & Permission Manager" separator>
        <x-slot:actions>
            <div class="flex flex-wrap gap-2">
                <x-mary-button
                    label="New Role"
                    icon="o-plus"
                    class="btn-primary"
                    wire:click="openRoleModal"
                />
                <x-mary-button
                    label="New Permission"
                    icon="o-plus"
                    class="btn-secondary"
                    wire:click="$set('showPermissionModal', true)"
                />
            </div>
        </x-slot:actions>
    </x-mary-header>

    <!-- Loader -->
    <div wire:loading class="flex justify-center my-4">
        <div class="bg-white rounded-lg px-4 py-2 shadow-lg flex items-center gap-2">
            <x-mary-icon name="o-arrow-path" class="w-4 h-4 animate-spin text-blue-600" />
            <span class="text-sm text-gray-600">Processing...</span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 mt-4" wire:loading.class="opacity-50">
        <!-- Roles List -->
        <div class="lg:col-span-3">
            <div class="bg-white rounded-lg shadow p-4 dark:bg-inherit">
                <h3 class="text-lg font-semibold mb-4">Roles</h3>
                <div class="space-y-2">
                    @foreach($roles as $role)
                        <div class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50
                            {{ $selectedRole && $selectedRole->id === $role->id ? 'bg-blue-50' : '' }}">
                            <button
                                wire:click="selectRole({{ $role->id }})"
                                class="flex-1 text-left text-sm sm:text-base"
                            >
                                {{ ucfirst($role->name) }}
                            </button>
                            @if($role->name !== 'super-admin')
                                <button
                                    wire:click="deleteRole({{ $role->id }})"
                                    class="text-red-500 hover:text-red-700"
                                >
                                    <x-mary-icon name="o-trash" class="w-4 h-4 sm:w-5 sm:h-5" />
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Permissions Grid -->
        <div class="lg:col-span-9">
            <div class="bg-white rounded-lg shadow p-4 dark:bg-inherit">
                <h3 class="text-lg font-semibold mb-4">
                    @if($selectedRole)
                        Permissions for {{ ucfirst($selectedRole->name) }}
                    @else
                        Select a role to manage permissions
                    @endif
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 {{ $selectedRole ? '' : 'opacity-50' }}">
                    @foreach($permissions->groupBy(function($permission) {
                        return explode(' ', $permission->name)[0];
                    }) as $group => $groupPermissions)
                        <div class="border rounded-lg p-3 sm:p-4">
                            <h4 class="font-semibold mb-2 capitalize text-sm sm:text-base">{{ $group }}</h4>
                            @foreach($groupPermissions as $permission)
                                <div class="flex items-center space-x-2 mb-2">
                                    <x-mary-checkbox
                                        :checked="$selectedPermissions->contains($permission->id)"
                                        wire:click="togglePermission({{ $permission->id }})"
                                        :disabled="!$selectedRole || ($selectedRole->name === 'super-admin')"
                                    />
                                    <span class="text-xs sm:text-sm">{{ ucfirst($permission->name) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <x-mary-modal wire:model="showRoleModal" max-width="md">
        <x-mary-form wire:submit="createRole">
            <h2 class="text-lg font-semibold mb-4">Create New Role</h2>

            <x-mary-input
                wire:model="roleForm.name"
                label="Role Name"
                placeholder="Enter role name"
                class="mb-4"
            />

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Initial Permissions (Optional)</label>
                <div class="max-h-60 overflow-y-auto">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($permissions->groupBy(function($permission) {
                            return explode(' ', $permission->name)[0];
                        }) as $group => $groupPermissions)
                            <div class="mb-4">
                                <h4 class="font-semibold mb-2 capitalize text-sm">{{ $group }}</h4>
                                @foreach($groupPermissions as $permission)
                                    <div class="flex items-center space-x-2 mb-2">
                                        <x-mary-checkbox
                                            wire:model="roleForm.permissions"
                                            :value="$permission->id"
                                            :label="ucfirst($permission->name)"
                                        />
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
                <x-mary-button type="submit" label="Create" class="btn-primary" />
                <x-mary-button @click="$wire.showRoleModal = false" label="Cancel" />
            </div>
        </x-mary-form>
    </x-mary-modal>

    <x-mary-modal wire:model="showPermissionModal" max-width="sm">
        <x-mary-form wire:submit="createPermission">
            <h2 class="text-lg font-semibold mb-4">Create New Permission</h2>
            <x-mary-input
                wire:model="permissionForm.name"
                label="Permission Name"
                placeholder="Enter permission name"
            />
            <div class="mt-4 flex flex-wrap gap-2">
                <x-mary-button type="submit" label="Create" class="btn-primary" />
                <x-mary-button @click="$wire.showPermissionModal = false" label="Cancel" />
            </div>
        </x-mary-form>
    </x-mary-modal>
</div>
