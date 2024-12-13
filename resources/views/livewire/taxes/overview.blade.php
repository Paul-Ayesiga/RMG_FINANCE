<div>
    <!-- Breadcrumbs -->
    <div class="text-sm breadcrumbs">
        <ul>
            <li><a>Home</a></li>
            <li><a>Settings</a></li>
            <li>Taxes</li>
        </ul>
    </div>

    <!-- Main Content Container -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 space-y-6">
        <!-- Search Row -->
        <div class="flex justify-between items-center">
            <div class="w-1/3">
                <x-mary-input 
                    icon="o-magnifying-glass" 
                    placeholder="Search taxes..." 
                    wire:model.live.debounce.300ms="search"
                />
            </div>
            <div>
                <x-mary-button 
                    icon="o-plus"
                    label="Add Tax" 
                    class="btn-primary"
                    wire:click="create"
                    spinner="create"
                />
            </div>
        </div>

        <!-- Table Section -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th wire:click="sortBy('name')" class="px-4 py-3 cursor-pointer">
                            Name
                            @if($sortField === 'name')
                                <span>{!! $sortDirection === 'asc' ? '↑' : '↓' !!}</span>
                            @endif
                        </th>
                        <th class="px-4 py-3">Rate</th>
                        <th class="px-4 py-3">Description</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($taxes as $tax)
                        <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-3">{{ $tax->name }}</td>
                            <td class="px-4 py-3">
                                {{ $tax->rate }}{{ $tax->is_percentage ? '%' : ' ' . config('app.currency') }}
                            </td>
                            <td class="px-4 py-3">{{ Str::limit($tax->description, 50) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 font-semibold text-sm rounded-sm text-white
                                    {{ $tax->is_active ? 'bg-green-500' : 'bg-red-500' }}">
                                    {{ $tax->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center space-x-2">
                                    <x-mary-button 
                                        icon="o-pencil" 
                                        class="btn-ghost btn-sm"
                                        wire:click="edit({{ $tax->id }})"
                                        spinner="edit({{ $tax->id }})"
                                    />
                                    <x-mary-button 
                                        icon="o-trash" 
                                        class="btn-ghost btn-sm text-red-500"
                                        wire:click="delete({{ $tax->id }})"
                                        wire:confirm="Are you sure you want to delete this tax?"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No taxes found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $taxes->links() }}
        </div>
    </div>

    <!-- Create Modal -->
    <x-mary-modal wire:model="createModal">
        <div class="p-4">
            <h2 class="text-lg font-semibold mb-4">Create Tax</h2>
            
            <form wire:submit="store" class="space-y-4">
                <div>
                    <x-mary-input 
                        label="Name" 
                        wire:model="name" 
                        placeholder="Enter tax name"
                    />
                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <x-mary-input 
                        label="Rate" 
                        wire:model="rate"
                        type="number"
                        step="0.01"
                        placeholder="Enter rate"
                    />
                    @error('rate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <x-mary-checkbox 
                        label="Is Percentage?" 
                        wire:model="is_percentage"
                    />
                </div>

                <div>
                    <x-mary-textarea 
                        label="Description" 
                        wire:model="description"
                        placeholder="Enter description"
                    />
                    @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <x-mary-checkbox 
                        label="Is Active?" 
                        wire:model="is_active"
                    />
                </div>

                <div class="flex justify-end space-x-2">
                    <x-mary-button label="Cancel" @click="$wire.createModal = false" />
                    <x-mary-button label="Create" type="submit" class="btn-primary" spinner="store"/>
                </div>
            </form>
        </div>
    </x-mary-modal>

    <!-- Edit Modal -->
    <x-mary-modal wire:model="editModal">
        <div class="p-4">
            <h2 class="text-lg font-semibold mb-4">Edit Tax</h2>
            
            <form wire:submit="update" class="space-y-4">
                <div>
                    <x-mary-input 
                        label="Name" 
                        wire:model="name" 
                        placeholder="Enter tax name"
                    />
                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <x-mary-input 
                        label="Rate" 
                        wire:model="rate"
                        type="number"
                        step="0.01"
                        placeholder="Enter rate"
                    />
                    @error('rate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <x-mary-checkbox 
                        label="Is Percentage?" 
                        wire:model="is_percentage"
                    />
                </div>

                <div>
                    <x-mary-textarea 
                        label="Description" 
                        wire:model="description"
                        placeholder="Enter description"
                    />
                    @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <x-mary-checkbox 
                        label="Is Active?" 
                        wire:model="is_active"
                    />
                </div>

                <div class="flex justify-end space-x-2">
                    <x-mary-button label="Cancel" @click="$wire.editModal = false" />
                    <x-mary-button label="Update" type="submit" class="btn-primary" spinner="update"/>
                </div>
            </form>
        </div>
    </x-mary-modal>
</div> 