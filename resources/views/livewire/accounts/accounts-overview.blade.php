<div>
     <!-- HEADER -->
    <x-mary-header title="Accounts Overview" separator progress-indicator>
            <x-slot:middle>
                <x-mary-input
                    label=""
                    placeholder="Search accounts ..."
                    wire:model.live.debounce="search"
                    clearable
                    icon="o-magnifying-glass"
                    class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none"
                />
            </x-slot:middle>
            <x-slot:actions>
                <x-mary-button label="Create Account" @click="$wire.addAccountModal = true"  icon="o-plus" class="bg-blue-700 mb-3 text-white rounded-md mr-10" />
            </x-slot:actions>
    </x-mary-header>


    {{-- accounts overview table --}}
    <x-mary-card title="" subtitle="" shadow separator progress-indicator>
        {{-- datatable options like xls, bulk delete --}}
        <x-mary-card class="shadow-lg bg-white  h-auto mb-10 dark:bg-inherit">
            <!-- Action buttons -->
            <div class="inline-flex flex-wrap items-center mb-2 space-x-2">
                <!-- Bulk Button -->
                <x-mary-button label="Bulk?" icon="o-trash" class="btn-error btn-sm mx-3" wire:click="bulk" />

                <!-- Filter Button with Badge -->
                <x-mary-button label="Filter" icon="o-funnel" class="bg-blue-200 btn-sm mx-2 rounded-md border-none dark:text-white dark:bg-slate-700"
                    wire:click="$set('filtersDrawer', true)" badge="{{$activeFiltersCount}}" />
            </div>
            {{-- export buttons --}}
             <div class="inline-flex flex-wrap items-center mb-2">
                <x-mary-dropdown>
                    <x-slot name="trigger">
                        <x-mary-button label="export" icon="o-arrow-down-tray" class="bg-blue-200 btn-sm border-none dark:text-white dark:bg-slate-700" />
                    </x-slot>
                    <x-mary-button label="PDF" class="btn-sm rounded-md mx-1 dark:bg-inherit" wire:click="exportToPDF" />
                    <!-- Export to Excel Button -->
                    <x-mary-button label="XLS" class="btn-sm rounded-md mx-2 dark:bg-inherit" wire:click="exportToExcel" />
                    </x-mary-dropdown>
            </div>

            <!-- Column Visibility Dropdown -->
            <div class="inline-flex flex-wrap items-center mb-2">
                <x-mary-dropdown>
                    <x-slot name="trigger">
                        <x-mary-button label="" icon="o-eye" class="bg-blue-200 btn-sm border-none dark:text-white dark:bg-slate-700" />
                    </x-slot>
                    @foreach(['customer.user.name','accountType.name', 'account_number', 'balance', 'status'] as $column)
                        <x-mary-menu-item wire:click="toggleColumnVisibility('{{ $column }}')">
                            @if($columns[$column])
                                <x-mary-icon name="o-eye" class="text-green-500" />
                            @else
                                <x-mary-icon name="o-eye-slash" class="text-gray-500" />
                            @endif
                            <span class="ml-2">{{ ucfirst(str_replace('_', ' ', $column)) }}</span>
                        </x-mary-menu-item>
                    @endforeach
                </x-mary-dropdown>
            </div>

            {{-- active filters --}}
            <div class="mb-4 mt-5">
                @if(count($activeFilters) > 0)
                    <x-mary-button wire:click="clearAllFilters" label="Clear All Filters" class="mt-2 btn-danger btn-sm"/>
                @endif
                <div class="flex flex-wrap gap-2">
                    @foreach($activeFilters as $filter => $value)
                        <span class="inline-flex items-center px-3 py-1 text-sm font-medium text-white bg-blue-500 rounded-full mt-3">
                            {{ $value }}
                            <button type="button" wire:click="removeFilter('{{ $filter }}')" class="ml-2 text-white hover:text-gray-300">
                                &times;
                            </button>
                        </span>
                    @endforeach
                </div>
            </div>
        </x-mary-card>
        {{-- end of datatable options --}}

        <x-mary-table :headers="$headers" :rows="$accounts" :sort-by="$sortBy" with-pagination  per-page="perPage"
            :per-page-values="[1,3, 5, 10]"  wire:model="selected" selectable striped>
            {{-- @scope('cell_user_avatar', $customer)
                <x-mary-avatar image="{{ $customer->avatar ?? asset('user.png')}}" class="!w-10" />
            @endscope --}}
           @scope('cell_status', $account)
                @php
                $statuses = [
                    'pending',
                    'active',
                    'inactive',
                    'closed'
                ];
                @endphp
                <select
                    wire:model.live="accountStatuses.{{ $account->id }}"
                    wire:change="updateStatus({{ $account->id }}, $event.target.value)"
                    class="select select-bordered select-sm w-full max-w-xs"
                >
                    @foreach($statuses as $status)
                        <option
                            value="{{ $status }}"
                            {{ $account->status === $status ? 'selected' : '' }}
                        >
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            @endscope
            {{-- Special `actions` slot --}}
            @scope('actions', $account)
                <div class="inline-flex">
                    <x-mary-button icon="o-eye"  wire:click="openPreviewModal({{$account->id}})" spinner class="btn-sm bg-blue-400 dark:text-white" />
                    <x-mary-button icon="o-trash"  wire:click="openDeleteModal({{$account->id}})"   spinner class="btn-sm bg-red-600 dark:text-white" />
                </div>

                {{-- Single account Modal --}}
                <x-mary-modal wire:model="previewAccountModal" title="Account Details" separator>
                    <div class="p-4 sm:p-6 bg-gray-50 rounded-lg shadow-md">
                        <!-- Account Type Header -->
                        <div class="text-center mb-4">
                            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">{{ $this->accountToPreview->account_number ?? 'Account Number' }}</h2>
                            <p class="text-sm sm:text-base text-gray-500">Balance: ${{ number_format($this->accountToPreview->balance ?? 0, 2) }}</p>
                        </div>

                        <!-- Account Owner -->
                        <div class="mb-4">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-2">Account Owner</h3>
                            <p class="text-sm sm:text-base text-gray-600">{{ $this->accountToPreview->customer->user->name ?? 'N/A' }}</p>
                        </div>

                        <!-- Account Category & Type -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <div>
                                <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-2">Account Category</h3>
                                <p class="text-sm sm:text-base text-gray-600">{{ $this->accountToPreview->accountType->category ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-2">Account Type</h3>
                                <p class="text-sm sm:text-base text-gray-600">{{ $this->accountToPreview->accountType->name ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <!-- Account Details -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <div>
                                <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-2">Interest Rate</h3>
                                <p class="text-sm sm:text-base text-gray-600">{{ $this->accountToPreview->accountType->interest_rate ?? '0' }}%</p>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-2">Minimum Balance</h3>
                                <p class="text-sm sm:text-base text-gray-600">${{ number_format($this->accountToPreview->accountType->min_balance ?? 0, 2) }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <div>
                                <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-2">Maximum Withdrawal</h3>
                                <p class="text-sm sm:text-base text-gray-600">${{ number_format($this->accountToPreview->accountType->max_withdrawal ?? 0, 2) }}</p>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-2">Overdraft Limit</h3>
                                <p class="text-sm sm:text-base text-gray-600">${{ number_format($this->accountToPreview->accountType->overdraft_limit ?? 0, 2) }}</p>
                            </div>
                        </div>

                        <!-- Account Status -->
                        <div class="mb-4">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-2">Status</h3>
                            @if($this->accountToPreview)
                                <div class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-medium
                                    @switch($this->accountToPreview->status)
                                        @case('active')
                                            bg-green-100 text-green-800
                                            @break
                                        @case('inactive')
                                            bg-yellow-100 text-yellow-800
                                            @break
                                        @case('closed')
                                            bg-red-100 text-red-800
                                            @break
                                        @case('pending')
                                            bg-blue-100 text-blue-800
                                            @break
                                        @default
                                            bg-gray-100 text-gray-800
                                    @endswitch
                                ">
                                    {{ ucfirst($this->accountToPreview->status) }}
                                </div>
                            @else
                                <div class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-medium bg-gray-100 text-gray-800">
                                    N/A
                                </div>
                            @endif
                        </div>

                        <!-- Creation Date -->
                        <div>
                            <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-2">Created On</h3>
                            <p class="text-sm sm:text-base text-gray-600">{{ $this->accountToPreview ? $this->accountToPreview->created_at->format('F j, Y') : 'N/A' }}</p>
                        </div>
                    </div>

                    <x-slot:actions>
                        <x-mary-button label="Close" @click.stop="$wire.previewAccountModal = false" class="bg-gray-500 rounded-md text-white font-bold border-none w-full sm:w-auto" />
                    </x-slot:actions>
                </x-mary-modal>

                {{-- End --}}

                {{-- single accountdelete modal --}}
                <x-mary-modal wire:model="deleteAccountModal" title="Deletion yet To Happen" subtitle="" separator>
                    <div class="p-4 text-center">
                        <p class="text-base sm:text-lg">Are you sure you want to perform this action? It's irreversible.</p>
                    </div>
                    <x-slot:actions>
                        <div class="flex justify-center gap-2 sm:gap-4">
                            <x-mary-button label="Cancel" @click.stop="$wire.deleteAccountModal = false" class="w-auto" />
                            <x-mary-button label="Delete" wire:click="confirmDelete({{$this->accountToDelete}})" class="bg-red-600 rounded-md text-white font-bold w-auto" spinner="confirmDelete"/>
                        </div>
                    </x-slot:actions>
                </x-mary-modal>
                {{-- end --}}
            @endscope

            <x-slot:empty>
                <x-mary-icon name="o-cube" label="It is empty." />
            </x-slot:empty>
        </x-mary-table>

    </x-mary-card>
    {{-- end of clients table --}}

        {{-- when selected bulk deletion modal --}}
            <x-mary-modal wire:model="filledbulk"  title="Bulk Deletion yet To Happen" subtitle="" separator>
                <div class="p-4 text-center">
                    <p class="text-base sm:text-lg">Are you sure you want to perform this action? It's irreversible.</p>
                </div>
                <x-slot:actions>
                    <div class="flex flex-col sm:flex-row justify-center gap-2 sm:gap-4">
                        <x-mary-button label="Cancel" @click="$wire.filledbulk = false" class="w-full sm:w-auto" />
                        <x-mary-button label="Delete" wire:click="deleteSelected" class="bg-red-600 rounded-md text-white font-bold w-full sm:w-auto" spinner/>
                    </div>
                </x-slot:actions>
            </x-mary-modal>
            {{-- when selected bulk deletion modal --}}
            <x-mary-modal wire:model="emptybulk"  title="Ooops! No rows selected " subtitle="" separator>
                <div class="p-4 text-center">
                    <p class="text-base sm:text-lg">Select some rows to delete</p>
                </div>
                <x-slot:actions>
                    <div class="flex justify-center">
                        <x-mary-button label="Okay" @click="$wire.emptybulk = false" class="btn btn-accent w-full sm:w-auto" />
                    </div>
                </x-slot:actions>
            </x-mary-modal>
        {{-- end of bulk delete modal --}}


    {{-- add Account Type --}}
    <x-mary-modal wire:model="addAccountModal">
        <h1 class="text-2xl sm:text-4xl font-bold mb-4">Add Account</h1>
        <x-mary-menu-separator />
        <x-mary-form wire:submit="saveAccount" class="space-y-4">
            <x-mary-choices label="Clients" wire:model="customerId" :options="$customers" single searchable class="border-b-2 border-white shadow-lg focus:border-none focus:outline-dashed" search-function="searchCustomer" >
                @scope('item', $customer)
                    <x-mary-list-item :item="$customer->user" sub-value="description">
                    <x-slot:avatar>
                        <x-mary-avatar image="{{ asset($customer->user->avatar) ?? asset('user.png')}}" class="!w-8 sm:!w-10" />
                    </x-slot:avatar>
                    <x-slot:actions>
                        <x-mary-icon name="o-check-circle" class="p-1 sm:p-2 w-8 h-8 sm:w-10 sm:h-10 rounded-full text-blue-700" />
                    </x-slot:actions>
                    </x-mary-list-item>
                @endscope

                @scope('selection', $customer)
                    {{ $customer->user->name }}
                @endscope
                <x-slot:append>
                    <x-mary-button label="" icon="o-plus" class="bg-slate-400 text-white border-b-2 border-white shadow-lg focus:border-none focus:outline-dashed"/>
                </x-slot:append>
                </x-mary-choices>

                {{-- Category Selection (keeping x-mary-choices) --}}
                <x-mary-choices
                    label="Account Category"
                    wire:model.live="selectedCategory"
                    :options="$this->getCategories()"
                    single
                    class="border-b-2 border-white shadow-lg focus:border-none focus:outline-dashed"
                >
                    @scope('item', $category)
                        <x-mary-list-item :item="$category">
                            <x-slot:avatar>
                                <x-mary-icon name="o-building-library" class="bg-blue-100 p-2 w-8 h-8 rounded-full" />
                            </x-slot:avatar>
                            {{ $category['name'] }}
                        </x-mary-list-item>
                    @endscope

                    @scope('selection', $category)
                        {{ $category['name'] }}
                    @endscope
                </x-mary-choices>

                {{-- Account Type Selection (changed to x-mary-select) --}}
                <x-mary-select
                    label="Account Type"
                    wire:model="accountTypeId"
                    :options="$filteredAccountTypes"
                    placeholder="Select account type"
                    option-label="name"
                    option-value="id"
                    :disabled="!$selectedCategory"
                    class="border-b-2 border-white shadow-lg focus:border-none focus:outline-dashed"
                >
                    <x-slot:description>
                        @if(!$selectedCategory)
                            <div class="text-sm text-gray-500">
                                Please select an account category first
                            </div>
                        @elseif($filteredAccountTypes->isEmpty())
                            <div class="text-sm text-gray-500">
                                No account types found for this category
                            </div>
                        @endif
                    </x-slot:description>
                </x-mary-select>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-mary-input label="Balance ( minimum : 15000)" placeholder="e.g 15,000" wire:model="balance" class="border-b-2 border-white shadow-lg focus:border-none focus:outline-dashed"/>
                    @php
                        $statuses = [
                                [
                                    'id' => 'pending',
                                    'name' => 'pending'
                                ],
                                [
                                'id' => 'active',
                                'name' => 'active'
                                ],
                                [
                                    'id' => 'inactive',
                                    'name' => 'inactive'
                                ],
                                [
                                    'id' => 'closed',
                                    'name' => 'closed'
                                ],
                            ];
                    @endphp
                    <x-mary-select wire:model="status"  label="status" :options="$statuses" placeholder="select status" class="border-b-2 border-white shadow-lg focus:border-none focus:outline-dashed"/>
                </div>
                <div class="mt-4 flex flex-col sm:flex-row gap-2">
                    <x-mary-button label="Add" type="submit" spinner="saveAccount" icon="o-paper-airplane" class="bg-blue-300 dark:text-white w-full sm:w-auto" />
                    <x-mary-button label="Cancel" @click="$wire.addAccountModal = false;" class="w-full sm:w-auto" />
                </div>
            </div>
        </x-mary-form>
    </x-mary-modal>
    {{-- End of Add Account Type --}}

</div>
