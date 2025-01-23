<div>
     <!-- HEADER -->
    <x-mary-header title="Accounts Overview" separator>
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
                @can('create accounts')
                    <x-mary-button label="Create Account" @click="$wire.addAccountModal = true"  icon="o-plus" class="bg-blue-700 mb-3 text-white rounded-md mr-10" />
                @endcan
            </x-slot:actions>
    </x-mary-header>


    {{-- accounts overview table --}}
    <x-mary-card title="" subtitle="" shadow separator progress-indicator>
        {{-- datatable options like xls, bulk delete --}}
        <x-mary-card class="shadow-lg bg-white  h-auto mb-10 dark:bg-inherit">
            <!-- Action buttons -->
            <div class="inline-flex flex-wrap items-center mb-2 space-x-2">
                <!-- Filter Button with Badge -->
                <x-mary-button label="Filter" icon="o-funnel" class="bg-blue-200 btn-sm mx-2 rounded-md border-none dark:text-white dark:bg-slate-700"
                     @click="$wire.filtersDrawer = true" badge="{{$activeFiltersCount}}" />
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
                    @foreach(['accountType.name', 'account_number', 'balance', 'status'] as $column)
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

        <x-mary-table :headers="$headers" :rows="$accounts" link="/customer/my-accounts/{id}/do-something" :sort-by="$sortBy" with-pagination per-page="perPage"
            :per-page-values="[1,3, 5, 10]"  wire:model="selected" selectable striped wire:poll.30s>
           @scope('cell_status', $account)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    @if($account->status === 'active')
                        bg-green-100 text-green-800
                    @elseif($account->status === 'inactive')
                        bg-yellow-100 text-yellow-800
                    @elseif($account->status === 'closed')
                        bg-red-100 text-red-800
                    @elseif($account->status === 'pending')
                        bg-blue-100 text-blue-800
                    @else
                        bg-gray-100 text-gray-800
                    @endif
                ">
                    {{ ucfirst($account->status) }}
                </span>
            @endscope

            @scope('cell_balance', $account, $currency)
                {{ number_format(convertCurrency($account->balance,'UGX', $currency),0)}}
            @endscope

            {{-- Special `actions` slot --}}
            @scope('actions', $account)
                <div class="inline-flex">
                    <x-mary-button icon="o-eye" wire:click.stop="OpenPreviewAccountModal({{$account->id}})" spinner="OpenPreviewAccountModal({{$account->id}})" class="btn-sm bg-blue-400 dark:text-white" />
                </div>

                {{-- Single account Modal --}}
                <x-mary-modal wire:model="previewAccountModal" title="Account Details" separator>
                    <div class="p-6 bg-gray-50 rounded-lg shadow-md">
                        <!-- Account Type Header -->
                        <div class="text-center mb-4">
                            <h2 class="text-2xl font-bold text-gray-800">{{ $this->accountToPreview->account_number ?? 'Account Number' }}</h2>
                            <p class="text-gray-500">{{ $this->accountToPreview->balance ?? 'balance in the account' }}</p>
                        </div>

                        <!-- Account Status -->
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-gray-700 mb-2">Status</h3>
                            @if($this->accountToPreview)
                            <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                @if($this->accountToPreview->status === 'active')
                                    bg-green-100 text-green-800
                                @elseif($this->accountToPreview->status === 'inactive')
                                    bg-yellow-100 text-yellow-800
                                @elseif($this->accountToPreview->status === 'closed')
                                    bg-red-100 text-red-800
                                @elseif($this->accountToPreview->status === 'pending')
                                    bg-blue-100 text-blue-800

                                @endif
                            ">
                                {{ ucfirst($this->accountToPreview->status) }}
                            </div>
                            @endif
                        </div>
                    </div>

                    <x-slot:actions>
                        <x-mary-button label="Close" @click.stop="$wire.previewAccountModal = false" class="bg-gray-500 rounded-md text-white font-bold border-none" />
                    </x-slot:actions>
                </x-mary-modal>
                {{-- End --}}

            @endscope

            <x-slot:empty>
                <x-mary-icon name="o-cube" label="It is empty." />
            </x-slot:empty>
        </x-mary-table>

    </x-mary-card>
    {{-- end of accounts table --}}

    {{-- add Account Type --}}
    <x-mary-modal wire:model="addAccountModal">
        <h1 class="text-4xl font-bold">Add Account</h1>
        <x-mary-menu-separator />
        <x-mary-form wire:submit="saveAccount">
            {{-- Category Selection --}}
            <x-mary-choices
                label="Account Category"
                wire:model.live="selectedCategory"
                :options="$this->getCategories()"
                single
                class="border-b-2 border-white shadow-lg focus:border-none focus:outline-dashed mb-4"
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

            {{-- Account Type Selection --}}
            <x-mary-select
                label="Account Type"
                wire:model.debounce="accountTypeId"
                :options="$filteredAccountTypes"
                placeholder="Select account type"
                option-label="name"
                option-value="id"
                :disabled="!$selectedCategory"
                class="border-b-2 border-white shadow-lg focus:border-none focus:outline-dashed mb-4"
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

            {{-- Balance Input --}}
            <div class="grid grid-cols-2 gap-4">
                <x-mary-input
                    label="Balance"
                    placeholder="e.g 15,000"
                    wire:model="balance"
                    class="border-b-2 border-white shadow-lg focus:border-none focus:outline-dashed"
                />
            </div>

            {{-- Form Actions --}}
            <div class="mt-4">
                <x-mary-button
                    label="Add"
                    type="submit"
                    spinner="saveAccount"
                    icon="o-paper-airplane"
                    class="bg-blue-300 dark:text-white"
                />
                <x-mary-button
                    label="Cancel"
                    @click="$wire.addAccountModal = false;"
                />
            </div>
        </x-mary-form>
    </x-mary-modal>
    {{-- End of Add Account Type --}}


    <x-mary-drawer wire:model="filtersDrawer" title="Filters" separator with-close-button close-on-escape class="w-11/12 lg:w-3/4 md:w-1/2">

    </x-mary-drawer>
</div>

