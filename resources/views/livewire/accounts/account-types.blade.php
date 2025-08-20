<div class="p-3">
     <!-- HEADER -->
    <x-mary-header title="Account Types" separator progress-indicator>
            <x-slot:middle>
                <x-mary-input
                    label=""
                    placeholder="Search account types.."
                    wire:model.live="search"
                    clearable
                    icon="o-magnifying-glass"
                    class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none"
                />
            </x-slot:middle>
            <x-slot:actions>
                <x-mary-button label="Create Account Type" @click="$wire.addAccountTypeModal = true"  icon="o-plus" class="bg-blue-700 mb-3 text-white rounded-md mr-10" />
            </x-slot:actions>
    </x-mary-header>


    {{-- account types table --}}
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
                    @foreach(['accounts_count','name', 'description', 'interest_rate', 'min_balance', 'max_withdrawal','maturity_period','monthly_deposit','overdraft_limit'] as $column)
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

        <x-mary-table :headers="$headers" :rows="$accountTypes"  :sort-by="$sortBy" with-pagination  per-page="perPage"
            :per-page-values="[1,3, 5, 10]"  wire:model="selected" selectable striped>
            @scope('cell_account_count', $accountType)
                <x-mary-badge :value="$accountType->accounts->count()" class="badge-success" />
            @endscope
            @scope('cell_min_balance',$accountType, $currency)
                {{ convertCurrency($accountType->min_balance, 'UGX', $currency) }}
            @endscope
            {{-- Special `actions` slot --}}
            @scope('actions', $accountType)
                <div class="inline-flex">
                    <x-mary-button icon="o-eye" wire:click.stop="OpenPreviewAccountTypeModal({{$accountType->id}})" spinner="OpenPreviewAccountTypeModal({{$accountType->id}})" class="btn-sm bg-blue-400 dark:text-white" />
                    <x-mary-button icon="o-pencil" @click.stop="$wire.dispatch('edit-account-type',{accountTypeId:{{$accountType->id}} })" spinner="editAccountType({{$accountType->id}})" class="btn-sm bg-yellow-400 dark:text-white" />
                    <x-mary-button icon="o-trash" wire:click.stop="openDeleteAccountTypeModal({{$accountType->id}})" spinner="openDeleteAccountTypeModal({{$accountType->id}})" class="btn-sm bg-red-600 dark:text-white" />
                </div>

                {{-- Single Client Modal --}}
                <x-mary-modal wire:model="previewAccountTypeModal" title="Account Type Details" separator>
    @if($this->accountTypeToPreview)
        <div class="p-4 sm:p-6 bg-gray-50 rounded-lg shadow-md" @click.stop>
            <div class="text-center mb-4">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800">{{ $this->accountTypeToPreview->name ?? 'Account Type Name' }}</h2>
                <p class="text-sm sm:text-base text-gray-500">{{ $this->accountTypeToPreview ->description ?? 'No description provided for this account type.' }}</p>
            </div>

            <div class="border-t border-gray-200 mt-4 pt-4">
                <h3 class="font-semibold text-gray-700 text-lg mb-3">Details</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <span class="block font-medium text-gray-600">Minimum Balance:</span>
                        <span class="block text-gray-800">{{ convertCurrency($this->accountTypeToPreview->min_balance ,'UGX' , $currency) ?? 'Not specified' }}</span>
                    </div>
                    <div>
                        <span class="block font-medium text-gray-600">Interest Rate:</span>
                        <span class="block text-gray-800">{{ $this->accountTypeToPreview->interest_rate ?? 'Not specified' }}</span>
                    </div>
                    <div>
                        <span class="block font-medium text-gray-600">Maximum Withdrawal Limit:</span>
                        <span class="block text-gray-800">{{ $this->accountTypeToPreview->max_withdrawal ?? 'Not specified' }}</span>
                    </div>
                    <div>
                        <span class="block font-medium text-gray-600">Monthly Fees:</span>
                        <span class="block text-gray-800">{{ $this->accountTypeToPreview->monthly_fees ?? 'Not specified' }}</span>
                    </div>
                    <div>
                        <span class="block font-medium text-gray-600">Transaction Limit:</span>
                        <span class="block text-gray-800">{{ $this->accountTypeToPreview->transaction_limit ?? 'Not specified' }}</span>
                    </div>
                    <div>
                        <span class="block font-medium text-gray-600">Overdraft Facility:</span>
                        <span class="block text-gray-800">{{ $this->accountTypeToPreview->overdraft_limit ?? 'Not specified' }}</span>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="p-4 text-center text-gray-500">
            Account type details not available.
        </div>
    @endif

    <x-slot:actions>
        <x-mary-button label="Close" @click.stop="$wire.previewAccountTypeModal = false" class="bg-gray-500 rounded-md text-white font-bold border-none" />
    </x-slot:actions>
</x-mary-modal>

                {{-- End --}}

                {{-- single accountdelete modal --}}
                <x-mary-modal wire:model="deleteAccountTypeModal" title="Deletion yet To Happen" subtitle="" separator >
                    <div class="p-4 text-center">
                        <p class="text-lg">Are you sure you want to perform this action? It's irreversible.</p>
                    </div>
                    <x-slot:actions>
                        <div class="flex justify-center space-x-4">
                            <x-mary-button label="Cancel" @click.stop="$wire.deleteAccountTypeModal = false" class="bg-gray-300 text-gray-800" />
                            <x-mary-button label="Delete" wire:click.stop="confirmDelete({{$this->accountTypeToDelete}})" class="bg-red-600 rounded-md text-white font-bold" spinner="confirmDelete({{$this->accountTypeToDelete}})"/>
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
                    <p class="text-lg">Are you sure you want to perform this action? It's irreversible.</p>
                </div>
                <x-slot:actions>
                    <div class="flex justify-center space-x-4">
                        <x-mary-button label="Cancel" @click="$wire.filledbulk = false" class="bg-gray-300 text-gray-800" />
                        <x-mary-button label="Delete" wire:click="deleteSelected" class="bg-red-600 rounded-md text-white font-bold" spinner/>
                    </div>
                </x-slot:actions>
            </x-mary-modal>
            {{-- when selected bulk deletion modal --}}
            <x-mary-modal wire:model="emptybulk"  title="Ooops! No rows selected " subtitle="" separator >
                <div class="p-4 text-center">
                    <p class="text-lg">Select some rows to delete</p>
                </div>
                <x-slot:actions>
                    <div class="flex justify-center">
                        <x-mary-button label="Okay" @click="$wire.emptybulk = false" class="btn btn-accent" />
                    </div>
                </x-slot:actions>
            </x-mary-modal>
        {{-- end of bulk delete modal --}}


    {{-- add Account Type --}}
    <x-mary-modal wire:model="addAccountTypeModal">
        <h1 class="text-2xl sm:text-4xl font-bold mb-4">Add Account Type</h1>
        <x-mary-menu-separator />
        <x-mary-form wire:submit="saveAccountType" class="space-y-4">
            <!-- Category Selection -->
            <x-mary-select
                label="Category"
                wire:model="category"
                :options="[
                    ['id' => 'Checking Accounts', 'name' => 'Checking Accounts'],
                    ['id' => 'Savings Accounts', 'name' => 'Savings Accounts'],
                    ['id' => 'Certificates of Deposit', 'name' => 'Certificates of Deposit'],
                    ['id' => 'Individual Retirement Accounts', 'name' => 'Individual Retirement Accounts'],
                    ['id' => 'Health Savings Accounts', 'name' => 'Health Savings Accounts'],
                    ['id' => 'Brokerage and Investment Accounts', 'name' => 'Brokerage and Investment Accounts'],
                    ['id' => 'Credit Accounts', 'name' => 'Credit Accounts'],
                    ['id' => 'Business Accounts', 'name' => 'Business Accounts'],
                    ['id' => 'Specialty Accounts', 'name' => 'Specialty Accounts']
                ]"
                class="w-full border-b-2 border-white shadow-lg focus:border-none focus:outline-none"
            />

            <!-- Existing fields -->
            <x-mary-input label="Name" placeholder="Account Type Name" wire:model="name" class="w-full border-b-2 border-white shadow-lg focus:border-none focus:outline-none" />
            <x-mary-textarea label="Description" placeholder="Account Type Description" wire:model="description" class="w-full border-b-2 border-white shadow-lg focus:border-none focus:outline-none" />
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-mary-input label="Interest Rate (%)" placeholder="e.g., 2.50" wire:model="interest_rate" class="w-full border-b-2 border-white shadow-lg focus:border-none focus:outline-none"/>
                <x-mary-input label="Minimum Balance" placeholder="e.g., 1000.00" wire:model="min_balance" class="w-full border-b-2 border-white shadow-lg focus:border-none focus:outline-none"/>
                <x-mary-input label="Maximum Withdrawal" placeholder="e.g., 5000.00" wire:model="max_withdrawal" class="w-full border-b-2 border-white shadow-lg focus:border-none focus:outline-none"/>
                <x-mary-input label="Maturity Period (months)" placeholder="e.g., 12" wire:model="maturity_period" class="w-full border-b-2 border-white shadow-lg focus:border-none focus:outline-none"/>
                <x-mary-input label="Monthly Deposit" placeholder="e.g., 200.00" wire:model="monthly_deposit" class="w-full border-b-2 border-white shadow-lg focus:border-none focus:outline-none"/>
                <x-mary-input label="Overdraft Limit" placeholder="e.g., 1000.00" wire:model="overdraft_limit" class="w-full border-b-2 border-white shadow-lg focus:border-none focus:outline-none"/>
            </div>
            <div class="flex justify-end space-x-2 mt-4">
                <x-mary-button label="Add" type="submit" spinner="saveAccountType" icon="o-paper-airplane" class="bg-blue-300" />
                <x-mary-button label="Cancel" @click="$wire.addAccountTypeModal = false;" />
            </div>
        </x-mary-form>
    </x-mary-modal>
    {{-- End of Add Account Type --}}

    {{-- edit accountType --}}
     <x-mary-modal wire:model="editAccountTypeModal">
        <h1 class="text-2xl sm:text-4xl font-bold mb-4">Edit Account Type</h1>
        <x-mary-menu-separator />
        <x-mary-form wire:submit="updateAccountType" class="space-y-4">
            <x-mary-select
                label="Category"
                wire:model="category"
                :options="[
                    ['id' => 'Checking Accounts', 'name' => 'Checking Accounts'],
                    ['id' => 'Savings Accounts', 'name' => 'Savings Accounts'],
                    ['id' => 'Certificates of Deposit', 'name' => 'Certificates of Deposit'],
                    ['id' => 'Individual Retirement Accounts', 'name' => 'Individual Retirement Accounts'],
                    ['id' => 'Health Savings Accounts', 'name' => 'Health Savings Accounts'],
                    ['id' => 'Brokerage and Investment Accounts', 'name' => 'Brokerage and Investment Accounts'],
                    ['id' => 'Credit Accounts', 'name' => 'Credit Accounts'],
                    ['id' => 'Business Accounts', 'name' => 'Business Accounts'],
                    ['id' => 'Specialty Accounts', 'name' => 'Specialty Accounts']
                ]"
                class="w-full border-b-2 border-white shadow-lg focus:border-none focus:outline-none"
            />
            <x-mary-input label="Name" placeholder="Account Type Name" wire:model="name" class="w-full border-b-2 border-white shadow-lg focus:border-none focus:outline-none" />
            <x-mary-textarea label="Description" placeholder="Account Type Description" wire:model="description" class="w-full border-b-2 border-white shadow-lg focus:border-none focus:outline-none" />
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-mary-input label="Interest Rate (%)" placeholder="e.g., 2.50" wire:model="interest_rate" class="w-full border-b-2 border-white shadow-lg focus:border-none focus:outline-none"/>
                <x-mary-input label="Minimum Balance" placeholder="e.g., 1000.00" wire:model="min_balance" class="w-full border-b-2 border-white shadow-lg focus:border-none focus:outline-none"/>
                <x-mary-input label="Maximum Withdrawal" placeholder="e.g., 5000.00" wire:model="max_withdrawal" class="w-full border-b-2 border-white shadow-lg focus:border-none focus:outline-none"/>
                <x-mary-input label="Maturity Period (months)" placeholder="e.g., 12" wire:model="maturity_period" class="w-full border-b-2 border-white shadow-lg focus:border-none focus:outline-none"/>
                <x-mary-input label="Monthly Deposit" placeholder="e.g., 200.00" wire:model="monthly_deposit" class="w-full border-b-2 border-white shadow-lg focus:border-none focus:outline-none"/>
                <x-mary-input label="Overdraft Limit" placeholder="e.g., 1000.00" wire:model="overdraft_limit" class="w-full border-b-2 border-white shadow-lg focus:border-none focus:outline-none"/>
            </div>
            <div class="flex justify-end space-x-2 mt-4">
                <x-mary-button label="Update" type="submit" spinner="updateAccountType" icon="o-paper-airplane" class="bg-blue-300" />
                <x-mary-button label="Cancel" @click="$wire.editAccountTypeModal = false;" />
            </div>
        </x-mary-form>
    </x-mary-modal>


    <x-mary-drawer wire:model="filtersDrawer" title="Filters" separator with-close-button close-on-escape class="w-11/12 lg:w-3/4 md:w-1/2">

    </x-mary-drawer>
</div>
