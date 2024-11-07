<!-- Add this after your view modal -->
<div>
    <!-- Breadcrumbs -->
    <div class="text-sm breadcrumbs">
        <ul>
            <li><a>Home</a></li>
            <li><a>Staff</a></li>
        </ul>
    </div>

    <!-- Main Content Container -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 space-y-6">
        <!-- Search and Actions Row -->
        <div class="flex flex-col md:flex-row justify-between gap-4">
            <!-- Search -->
            <div class="w-full md:w-1/2">
                <x-mary-input 
                    icon="o-magnifying-glass" 
                    placeholder="Search staff..." 
                    wire:model.live.debounce.300ms="search"
                    class="w-full"
                />
            </div>
            
            <!-- Actions -->
            <div class="flex items-center gap-4">
                <x-mary-button 
                    icon="o-plus"
                    label="Add Staff" 
                    class="btn-primary"
                    wire:click="create"
                    spinner="create"
                />
                
                <!-- Per Page Selector -->
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Show</span>
                    <x-mary-select
                        wire:model.live="perPage"
                        :options="[
                            ['id' => 10, 'name' => '10'],
                            ['id' => 25, 'name' => '25'],
                            ['id' => 50, 'name' => '50'],
                            ['id' => 100, 'name' => '100']
                        ]"
                        class="w-20"
                    />
                </div>
            </div>
        </div>

        <!-- Table Container with fixed width columns and horizontal scroll -->
        <div class="overflow-x-auto border rounded-lg">
            <table class="w-full whitespace-nowrap">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th wire:click="sortBy('staff_number')" 
                            class="px-4 py-3 w-[150px] cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 text-left">
                            <div class="flex items-center gap-1">
                                Staff Number
                                @if($sortField === 'staff_number')
                                    <span>{!! $sortDirection === 'asc' ? '↑' : '↓' !!}</span>
                                @endif
                            </div>
                        </th>
                        <th class="px-4 py-3 w-[80px] text-left">Image</th>
                        <th class="px-4 py-3 w-[200px] text-left">Name</th>
                        <th class="px-4 py-3 w-[250px] text-left">Email</th>
                        <th class="px-4 py-3 w-[150px] text-left">Role</th>
                        <th class="px-4 py-3 w-[150px] text-left">Status</th>
                        <th wire:click="sortBy('created_at')" 
                            class="px-4 py-3 w-[150px] cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 text-left">
                            <div class="flex items-center gap-1">
                                Date Joined
                                @if($sortField === 'created_at')
                                    <span>{!! $sortDirection === 'asc' ? '↑' : '↓' !!}</span>
                                @endif
                            </div>
                        </th>
                        <th class="px-4 py-3 w-[120px] text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($staffMembers as $staff)
                        <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-3">{{ $staff->staff_number }}</td>
                            <td class="px-4 py-3">
                                @if($staff->user->avatar)
                                    <img src="{{ asset($staff->user->avatar) }}" 
                                         class="w-10 h-10 rounded-full object-cover ring-2 ring-gray-200"
                                         alt="{{ $staff->user->name }}"
                                         title="{{ $staff->user->name }}">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center ring-2 ring-gray-200">
                                        <span class="text-gray-500 text-sm font-medium">
                                            {{ substr($staff->user->name, 0, 1) }}
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-medium text-gray-900 dark:text-white">
                                    {{ $staff->user->name }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                {{ $staff->user->email }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize
                                    @switch($staff->user->roles->first()->name ?? '')
                                        @case('admin')
                                            bg-red-100 text-red-800 border border-red-200
                                            @break
                                        @case('manager')
                                            bg-blue-100 text-blue-800 border border-blue-200
                                            @break
                                        @case('supervisor')
                                            bg-purple-100 text-purple-800 border border-purple-200
                                            @break
                                        @default
                                            bg-green-100 text-green-800 border border-green-200
                                    @endswitch
                                ">
                                    {{ $staff->user->roles->first() ? ucfirst($staff->user->roles->first()->name) : 'No Role' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $staff->user->email_verified_at 
                                        ? 'bg-green-100 text-green-800 border border-green-200' 
                                        : 'bg-yellow-100 text-yellow-800 border border-yellow-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full 
                                        {{ $staff->user->email_verified_at ? 'bg-green-600' : 'bg-yellow-600' }} 
                                        mr-1.5">
                                    </span>
                                    {{ $staff->user->email_verified_at ? 'Active' : 'Not verified' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                {{ $staff->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <x-mary-button 
                                        icon="o-eye" 
                                        class="btn-ghost btn-sm"
                                        wire:click="viewStaff({{ $staff->id }})"
                                        spinner="viewStaff({{ $staff->id }})"
                                        title="View Details"
                                    />
                                    <x-mary-button 
                                        icon="o-pencil" 
                                        class="btn-ghost btn-sm"
                                        wire:click="edit({{ $staff->id }})"
                                        spinner="edit({{ $staff->id }})"
                                        title="Edit Staff"
                                    />
                                    <x-mary-button 
                                        icon="o-trash" 
                                        class="btn-ghost btn-sm text-red-500"
                                        wire:click="delete({{ $staff->id }})"
                                        wire:confirm="Are you sure you want to delete this staff member?"
                                        title="Delete Staff"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No staff members found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Bottom section with pagination -->
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 mt-4">
            <!-- Entries per page -->
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-600 dark:text-gray-400">Show</span>
                <select wire:model.live="perPage" class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span class="text-sm text-gray-600 dark:text-gray-400">entries</span>
            </div>

            <!-- Pagination Links -->
            @if($staffMembers->hasPages())
                <div class="inline-flex rounded-md shadow-sm">
                    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
                        {{-- Previous Page Link --}}
                        <button 
                            wire:click="previousPage" 
                            wire:loading.attr="disabled" 
                            class="{{ $staffMembers->onFirstPage() ? 'opacity-50 cursor-not-allowed' : '' }} relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-md leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300"
                            {{ $staffMembers->onFirstPage() ? 'disabled' : '' }}
                        >
                            Previous
                        </button>

                        {{-- Page Numbers --}}
                        <div class="hidden md:flex">
                            @foreach ($staffMembers->getUrlRange(1, $staffMembers->lastPage()) as $page => $url)
                                <button 
                                    wire:click="gotoPage({{ $page }})"
                                    class="{{ $page == $staffMembers->currentPage() ? 'bg-blue-50 border-blue-500 text-blue-600 z-10' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }} relative inline-flex items-center px-4 py-2 border text-sm font-medium dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300"
                                >
                                    {{ $page }}
                                </button>
                            @endforeach
                        </div>

                        {{-- Next Page Link --}}
                        <button 
                            wire:click="nextPage" 
                            wire:loading.attr="disabled" 
                            class="{{ !$staffMembers->hasMorePages() ? 'opacity-50 cursor-not-allowed' : '' }} relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-r-md leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300"
                            {{ !$staffMembers->hasMorePages() ? 'disabled' : '' }}
                        >
                            Next
                        </button>
                    </nav>
                </div>
            @endif
        </div>
    </div>

    <!-- View Modal -->
    <x-mary-modal wire:model="viewModal" max-width="2xl">
        @if($selectedStaff)
            <div class="p-6">
                <h2 class="text-2xl font-bold mb-6">Staff Details</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Profile Image -->
                    <div class="md:col-span-2 flex justify-center">
                        @if($selectedStaff->user->avatar)
                            <img src="{{ asset($selectedStaff->user->avatar) }}" class="w-32 h-32 rounded-full">
                        @else
                            <div class="w-32 h-32 rounded-full bg-gray-200 flex items-center justify-center">
                                <span class="text-4xl text-gray-500">{{ substr($selectedStaff->user->name, 0, 1) }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Staff Details -->
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Staff Number</label>
                            <p class="text-lg font-semibold">{{ $selectedStaff->staff_number }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Name</label>
                            <p class="text-lg font-semibold">{{ $selectedStaff->user->name }}</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Email</label>
                            <p class="text-lg font-semibold">{{ $selectedStaff->user->email }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Role</label>
                            <p class="text-lg">
                                <span class="px-3 py-1 text-sm font-medium rounded-full
                                    @switch($selectedStaff->user->roles->first()->name ?? '')
                                        @case('admin')
                                            bg-red-100 text-red-800
                                            @break
                                        @case('manager')
                                            bg-blue-100 text-blue-800
                                            @break
                                        @case('supervisor')
                                            bg-purple-100 text-purple-800
                                            @break
                                        @default
                                            bg-green-100 text-green-800
                                    @endswitch
                                ">
                                    {{ $selectedStaff->user->roles->first() ? ucfirst($selectedStaff->user->roles->first()->name) : 'No Role' }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </x-mary-modal>

    <!-- Edit Modal -->
    <x-mary-modal wire:model="editModal" max-width="2xl">
        <form wire:submit="update" class="p-6 space-y-6">
            <h2 class="text-2xl font-bold">Edit Staff</h2>

            <!-- Add this section for avatar -->
            <div class="flex justify-center mb-6">
                <div class="relative w-32 h-32">
                    <input type="file" wire:model="avatar" id="edit_profile" class="hidden">
                    <label for="edit_profile" class="cursor-pointer block">
                        @if($avatar && !is_string($avatar))
                            <img src="{{ $avatar->temporaryUrl() }}" 
                                 class="w-32 h-32 rounded-full object-cover bg-gray-100">
                        @elseif($selectedStaff && $selectedStaff->user->avatar)
                            <img src="{{ asset($selectedStaff->user->avatar) }}" 
                                 class="w-32 h-32 rounded-full object-cover bg-gray-100">
                        @else
                            <div class="w-32 h-32 rounded-full bg-gray-100 flex items-center justify-center">
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                        @endif

                        <!-- Camera Icon Overlay -->
                        <div class="absolute inset-0 rounded-full bg-black bg-opacity-40 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity duration-200">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                    </label>
                    @error('avatar') 
                        <p class="mt-2 text-sm text-red-600 text-center">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <x-mary-input 
                    label="Name" 
                    wire:model="name" 
                    placeholder="Enter name" 
                />
                @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <x-mary-input 
                    label="Email" 
                    wire:model="email" 
                    placeholder="Enter email" 
                />
                @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <x-mary-input 
                    label="Staff Number" 
                    wire:model="staff_number" 
                    readonly 
                />
                @error('staff_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <x-mary-select
                    label="Role"
                    wire:model="role"
                    :options="$roles"
                    placeholder="Select role"
                />
                @error('role') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <x-mary-input 
                    label="New Password (optional)" 
                    wire:model="password" 
                    type="password"
                    placeholder="Enter new password" 
                />
                @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <x-mary-input 
                    label="Confirm Password" 
                    wire:model="password_confirmation" 
                    type="password"
                    placeholder="Confirm new password" 
                />
            </div>

            <div class="flex justify-end gap-4">
                <x-mary-button 
                    label="Cancel" 
                    @click="$wire.editModal = false" 
                />
                <x-mary-button 
                    label="Update" 
                    class="btn-primary" 
                    type="submit" 
                    spinner 
                />
            </div>
        </form>
    </x-mary-modal>

    <!-- Create Modal -->
    <x-mary-modal wire:model="createModal" max-width="2xl">
        <form wire:submit="store" class="p-6">
            <h2 class="text-2xl font-bold mb-6">Add New Staff</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Profile Image -->
                <div class="md:col-span-2">
                    <div class="flex justify-center">
                        <div class="relative w-32 h-32">
                            <input type="file" wire:model="avatar" id="create_profile" class="hidden">
                            @if($avatar)
                                <img src="{{ $avatar->temporaryUrl() }}" class="w-32 h-32 rounded-full object-cover">
                            @else
                                <div class="w-32 h-32 rounded-full bg-gray-200 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                            @endif
                            <label for="create_profile" class="absolute inset-0 flex items-center justify-center cursor-pointer hover:bg-black/20 rounded-full">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </label>
                        </div>
                    </div>
                    @error('avatar') <span class="text-red-500 text-sm block text-center mt-2">{{ $message }}</span> @enderror
                </div>

                <!-- Form Fields -->
                <div class="space-y-4">
                    <div>
                        <x-mary-input 
                            label="Name"
                            wire:model="name"
                            placeholder="Enter staff name"
                        />
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <x-mary-input 
                            label="Staff Number"
                            wire:model="staff_number"
                            readonly
                        />
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <x-mary-input 
                            type="email"
                            label="Email"
                            wire:model="email"
                            placeholder="Enter email address"
                        />
                        @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <x-mary-select
                            label="Role"
                            wire:model="role"
                            :options="$roles"
                            placeholder="Select role"
                        />
                        @error('role') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Password Fields -->
                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-mary-input 
                            type="password"
                            label="Password"
                            wire:model="password"
                            placeholder="Enter password"
                        />
                        @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <x-mary-input 
                            type="password"
                            label="Confirm Password"
                            wire:model="password_confirmation"
                            placeholder="Confirm password"
                        />
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end gap-4 mt-6">
                <x-mary-button 
                    label="Cancel"
                    @click="$wire.createModal = false"
                    class="btn-outline"
                />
                <x-mary-button 
                    type="submit"
                    label="Create"
                    class="btn-primary"
                    spinner
                />
            </div>
        </form>
    </x-mary-modal>
</div>
