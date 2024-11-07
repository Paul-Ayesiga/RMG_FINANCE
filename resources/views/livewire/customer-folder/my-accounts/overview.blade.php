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
                <x-mary-button label="Create Account" @click="$wire.addAccountModal = true"  icon="o-plus" class="bg-blue-700 mb-3 text-white rounded-md mr-10" />
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

        <x-mary-table :headers="$headers" :rows="$accounts" :sort-by="$sortBy" with-pagination per-page="perPage"
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
            
            {{-- Special `actions` slot --}}
            @scope('actions', $account)
                <div class="inline-flex">
                    <x-mary-button icon="o-eye" wire:click.stop="OpenPreviewAccountModal({{$account->id}})" spinner="OpenPreviewAccountModal({{$account->id}})" class="btn-sm bg-blue-400 dark:text-white" />
                    @if($account->status === 'active')
                            <x-mary-dropdown>
                                <x-slot:trigger>
                                    <x-mary-button label="Transactions" class="btn-sm bg-slate-600 text-white" tooltip="Transactions"/>
                                </x-slot:trigger>
                                 <x-mary-button label="Deposit" class="btn-sm bg-green-500 text-white" icon="o-arrow-down-tray"  wire:click.stop="openDepositModal({{$account->id}})" spinner="openDepositModal({{$account->id}})"/>
                                <x-mary-button label="Withdrawal" class="btn-sm bg-red-500 text-white" icon="o-arrow-up-tray"  wire:click.stop="openWithdrawModal({{$account->id}})" spinner="openWithdrawModal({{$account->id}})"/>
                                <x-mary-button label="Transfer" class="btn-sm bg-blue-500 text-white" icon="o-arrows-right-left" wire:click.stop="openTransferModal({{$account->id}})" spinner="openTransferModal({{$account->id}})"/>
                                <x-mary-button label="Transaction History" class="btn-sm bg-orange-900 text-white" icon="o-arrows-right-left" wire:click.stop="toggleTransactionHistory({{$account->id}})" spinner="toggleTransactionHistory({{$account->id}})"/>
                            </x-mary-dropdown>
                    @endif
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
                                @else
                                    bg-gray-100 text-gray-800
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

                <!-- transaction modal -->
                <!-- Deposit Modal -->
                <x-mary-modal wire:model="depositModal" title="Deposit Funds" separator>
                    <x-mary-form wire:submit="deposit" x-data="{ depositMethod: 'default' }">
                    <div class="p-4">
                        <div class="mb-4 text-center">
                            <label class="block text-sm font-medium text-gray-700">Deposit Method</label>
                            <div class="mt-2 flex justify-center space-x-4">
                                <button type="button" @click="depositMethod = 'default'" :class="{'bg-blue-500 text-white': depositMethod === 'default', 'bg-gray-200 text-gray-700': depositMethod !== 'default'}" class="px-4 py-2 rounded-md">Default</button>
                                <button type="button" @click="depositMethod = 'card'" :class="{'bg-blue-500 text-white': depositMethod === 'card', 'bg-gray-200 text-gray-700': depositMethod !== 'card'}" class="px-4 py-2 rounded-md">Card</button>
                                <button type="button" @click="depositMethod = 'mobile_money'" :class="{'bg-blue-500 text-white': depositMethod === 'mobile_money', 'bg-gray-200 text-gray-700': depositMethod !== 'mobile_money'}" class="px-4 py-2 rounded-md">Mobile Money</button>
                            </div>
                        </div>

                        <div x-show="depositMethod === 'default'" class="text-center">
                            <x-mary-input label="Amount" wire:model="depositAmount" type="number" step="0.01" placeholder="Enter deposit amount" />
                        </div>

                        <div x-show="depositMethod === 'card'" class="text-center">
                            <x-mary-input label="Card Number" wire:model="cardNumber" type="text" placeholder="Enter card number" />
                            <x-mary-input label="Expiry Date" wire:model="cardExpiry" type="text" placeholder="MM/YY" />
                            <x-mary-input label="CVV" wire:model="cardCvv" type="number" placeholder="Enter CVV" />
                            <x-mary-input label="Amount" wire:model="depositAmount" type="number" step="0.01" placeholder="Enter deposit amount" />
                        </div>

                        <div x-show="depositMethod === 'mobile_money'" class="text-center">
                            <x-mary-input label="Phone Number" wire:model="mobileNumber" type="tel" placeholder="Enter mobile number" />
                            <x-mary-input label="Amount" wire:model="depositAmount" type="number" step="0.01" placeholder="Enter deposit amount" />
                        </div>
                    </div>
                    <x-slot:actions>
                        @if($this->depositToAccount)
                        <x-mary-button icon="o-arrow-down-tray" label="Deposit" wire:click="deposit({{$this->depositToAccount->id}})" class="bg-green-500 text-white text-sm px-3 py-1" spinner="deposit"/>
                        @endif
                        <x-mary-button label="Cancel" @click="$wire.depositModal = false" class="bg-gray-500 text-white text-sm px-3 py-1" />
                    </x-slot:actions>
                    </x-mary-form>
                </x-mary-modal>

                <!-- Withdrawal Modal -->
                <x-mary-modal wire:model="withdrawalModal" title="Withdraw Funds" separator>
                    <x-mary-form wire:submit="withdraw" x-data="{ withdrawalMethod: 'default' }">
                    <div class="p-4">
                        <div class="mb-4 text-center">
                            <label class="block text-sm font-medium text-gray-700">Withdrawal Method</label>
                            <div class="mt-2 flex justify-center space-x-4">
                                <button type="button" @click="withdrawalMethod = 'default'" :class="{'bg-blue-500 text-white': withdrawalMethod === 'default', 'bg-gray-200 text-gray-700': withdrawalMethod !== 'default'}" class="px-4 py-2 rounded-md">Default</button>
                                <button type="button" @click="withdrawalMethod = 'card'" :class="{'bg-blue-500 text-white': withdrawalMethod === 'card', 'bg-gray-200 text-gray-700': withdrawalMethod !== 'card'}" class="px-4 py-2 rounded-md">Card</button>
                                <button type="button" @click="withdrawalMethod = 'mobile_money'" :class="{'bg-blue-500 text-white': withdrawalMethod === 'mobile_money', 'bg-gray-200 text-gray-700': withdrawalMethod !== 'mobile_money'}" class="px-4 py-2 rounded-md">Mobile Money</button>
                            </div>
                        </div>

                        <div x-show="withdrawalMethod === 'default'">
                            <x-mary-input label="Amount" wire:model="withdrawalAmount" type="number" step="0.01" placeholder="Enter withdrawal amount" />
                        </div>

                        <div x-show="withdrawalMethod === 'card'">
                            <x-mary-input label="Card Number" wire:model="cardNumber" type="text" placeholder="Enter card number" />
                            <x-mary-input label="Expiry Date" wire:model="cardExpiry" type="text" placeholder="MM/YY" />
                            <x-mary-input label="CVV" wire:model="cardCvv" type="number" placeholder="Enter CVV" />
                            <x-mary-input label="Amount" wire:model="withdrawalAmount" type="number" step="0.01" placeholder="Enter withdrawal amount" />
                        </div>

                        <div x-show="withdrawalMethod === 'mobile_money'">
                            <x-mary-input label="Phone Number" wire:model="mobileNumber" type="tel" placeholder="Enter mobile number" />
                            <x-mary-input label="Amount" wire:model="withdrawalAmount" type="number" step="0.01" placeholder="Enter withdrawal amount" />
                        </div>
                    </div>
                    <x-slot:actions>
                        @if($this->withdrawFromAccount)
                        <x-mary-button icon="o-arrow-up-tray" label="Withdraw" wire:click="withdraw({{$this->withdrawFromAccount->id}})" class="bg-red-500 text-white text-sm px-3 py-1" spinner="withdraw({{$this->withdrawFromAccount->id}})" />
                        @endif
                        <x-mary-button label="Cancel" @click="$wire.withdrawalModal = false" class="bg-gray-500 text-white text-sm px-3 py-1" />
                    </x-slot:actions>
                    </x-mary-form>
                </x-mary-modal>

                <!-- transfer modal -->
                <x-mary-modal wire:model="transferModal" title="Transfer Funds" separator>
                    <x-mary-form wire:submit="transfer" x-data="{ transferType: 'my_accounts' }">
                        <!-- Source Account Section -->
                        <div class="flex items-center justify-between mb-6 bg-gray-50 p-4 rounded-lg">
                            <div class="flex-1">
                                <h2 class="text-lg font-semibold text-gray-700 mb-2">From Account</h2>
                                @if($this->accountToTransferFrom)
                                    <div class="flex items-center space-x-3">
                                        <x-mary-icon name="o-credit-card" class="w-10 h-10 text-blue-500" />
                                        <div>
                                            <p class="font-medium text-gray-900">{{$this->accountToTransferFrom->account_number }}</p>
                                            <p class="text-sm text-gray-600">{{ $this->accountToTransferFrom->accountType->name }}</p>
                                            <p class="font-bold text-blue-600 mt-1">Balance: {{ $this->accountToTransferFrom->balance }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Transfer Type Selector -->
                        <div class="mb-6">
                            <div class="flex justify-center space-x-4 mb-4">
                                <button type="button" 
                                    @click="transferType = 'my_accounts'; $wire.set('transferOtherAccountId', null)" 
                                    :class="{'bg-blue-500 text-white': transferType === 'my_accounts', 'bg-gray-100 text-gray-700': transferType !== 'my_accounts'}"
                                    class="px-6 py-2 rounded-full font-medium transition-colors duration-200">
                                    My Accounts
                                </button>
                                <button type="button" 
                                    @click="transferType = 'other_accounts'; $wire.set('transferCustomerAccountId', null)" 
                                    :class="{'bg-blue-500 text-white': transferType === 'other_accounts', 'bg-gray-100 text-gray-700': transferType !== 'other_accounts'}"
                                    class="px-6 py-2 rounded-full font-medium transition-colors duration-200">
                                    Other Accounts
                                </button>
                            </div>

                            <!-- My Accounts Selection -->
                            <div x-show="transferType === 'my_accounts'" class="space-y-4">
                                <x-mary-choices 
                                    label="Select Your Account" 
                                    wire:model="transferCustomerAccountId" 
                                    :options="$this->transferCustomerAccounts->where('id', '!=', $this->transferFromAccountId)" 
                                    single 
                                    searchable 
                                    class="border-none shadow-sm"
                                    search-function="searchTransferCustomerAccounts">
                                    @scope('item', $transferCustomerAccount)
                                        <x-mary-list-item :item="$transferCustomerAccount" sub-value="account_number">
                                            <x-slot:avatar>
                                                <x-mary-icon name="o-credit-card" class="bg-blue-100 p-2 w-8 h-8 rounded-full" />
                                            </x-slot:avatar>
                                            <x-slot:actions>
                                                <x-mary-badge :value="$transferCustomerAccount->balance" class="bg-blue-100 text-blue-800" />
                                            </x-slot:actions>
                                        </x-mary-list-item>
                                    @endscope
                                    
                                    @scope('selection', $transferCustomerAccount)
                                        {{ $transferCustomerAccount->account_number }} ({{ $transferCustomerAccount->accountType->name }})
                                    @endscope
                                </x-mary-choices>
                            </div>

                            <!-- Other Accounts Selection -->
                            <div x-show="transferType === 'other_accounts'" class="space-y-4">
                                <x-mary-choices 
                                    label="Select Recipient Account" 
                                    wire:model="transferOtherAccountId" 
                                    :options="$this->transferOtherAccounts->where('id', '!=', $this->transferFromAccountId)" 
                                    single 
                                    searchable 
                                    class="border-none shadow-sm"
                                    search-function="searchTransferOtherAccounts">
                                    @scope('item', $transferOtherAccount)
                                        <x-mary-list-item :item="$transferOtherAccount" sub-value="account_number">
                                            <x-slot:avatar>
                                                <x-mary-icon name="o-user" class="bg-gray-100 p-2 w-8 h-8 rounded-full" />
                                            </x-slot:avatar>
                                        </x-mary-list-item>
                                    @endscope
                                    
                                    @scope('selection', $transferOtherAccount)
                                        {{ $transferOtherAccount->account_number }} ({{ $transferOtherAccount->accountType->name }})
                                    @endscope
                                </x-mary-choices>
                            </div>
                        </div>

                        <!-- Amount Input -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <x-mary-input 
                                label="Transfer Amount" 
                                wire:model="transferAmount" 
                                type="number" 
                                step="0.01" 
                                placeholder="Enter amount to transfer"
                                class="border-none shadow-sm" />
                        </div>

                        <x-slot:actions>
                            <x-mary-button 
                                icon="o-paper-airplane" 
                                label="Transfer" 
                                wire:click="transfer({{$this->transferFromAccountId}})" 
                                class="bg-blue-500 hover:bg-blue-600 text-white" 
                                spinner="transfer"/>
                            <x-mary-button 
                                label="Cancel" 
                                @click="$wire.transferModal = false" 
                                class="bg-gray-200 hover:bg-gray-300 text-gray-700" />
                        </x-slot:actions>
                    </x-mary-form>
                </x-mary-modal>

                <!-- transaction history -->
                <x-mary-modal wire:model="showTransactionHistory" title="Transfer Funds To" separator>
               
                </x-mary-modal>

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
    
    {{-- Add this section to your existing transaction-manager.blade.php --}}

    <!-- Add this near the end of your file -->
    <x-mary-modal wire:model="showReceiptModal" title="Transaction Receipt" separator>
        <div class="p-6 bg-white">
            <!-- Receipt Header -->
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold">Transaction Receipt</h2>
                <p class="text-gray-600">{{ ucfirst($receiptType) }} Confirmation</p>
            </div>

            <!-- Receipt Body -->
            <div class="space-y-4">
                <!-- Date -->
                <div class="flex justify-between">
                    <span class="font-semibold">Date:</span>
                    <span>{{ $receiptData['date'] ?? '' }}</span>
                </div>

                <!-- Reference Number -->
                <div class="flex justify-between">
                    <span class="font-semibold">Reference:</span>
                    <span>{{ $receiptData['reference'] ?? '' }}</span>
                </div>

                @if($receiptType === 'transfer')
                    <!-- Transfer-specific details -->
                    <div class="flex justify-between">
                        <span class="font-semibold">From Account:</span>
                        <span>{{ $receiptData['from_account'] ?? '' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">To Account:</span>
                        <span>{{ $receiptData['to_account'] ?? '' }}</span>
                    </div>
                @else
                    <!-- Deposit/Withdrawal account -->
                    <div class="flex justify-between">
                        <span class="font-semibold">Account:</span>
                        <span>{{ $receiptData['account_number'] ?? '' }}</span>
                    </div>
                @endif

                <!-- Transaction Details -->
                <div class="space-y-2 border-t pt-2">
                    <!-- Base Amount -->
                    <div class="flex justify-between">
                        <span class="font-semibold">Base Amount:</span>
                        <span class="text-lg {{ $receiptType === 'withdrawal' ? 'text-red-600' : 'text-green-600' }}">
                            {{ $receiptType === 'withdrawal' ? '-' : '+' }}{{ number_format($receiptData['amount'] ?? 0, 2) }}
                        </span>
                    </div>

                    <!-- Charges Breakdown -->
                    @if(!empty($receiptData['charges']))
                        <div class="space-y-1">
                            <p class="font-semibold text-sm text-gray-600">Bank Charges:</p>
                            @foreach($receiptData['charges'] as $charge)
                                <div class="flex justify-between text-sm pl-4">
                                    <span class="text-gray-600">{{ $charge['name'] }} ({{ $charge['rate'] }}):</span>
                                    <span class="text-red-600">-{{ number_format($charge['amount'], 2) }}</span>
                                </div>
                            @endforeach
                            <div class="flex justify-between text-sm font-medium border-t border-dashed pt-1">
                                <span>Total Charges:</span>
                                <span class="text-red-600">-{{ number_format($receiptData['total_charges'] ?? 0, 2) }}</span>
                            </div>
                        </div>
                    @endif

                    <!-- Taxes Breakdown -->
                    @if(!empty($receiptData['taxes']))
                        <div class="space-y-1">
                            <p class="font-semibold text-sm text-gray-600">Taxes:</p>
                            @foreach($receiptData['taxes'] as $tax)
                                <div class="flex justify-between text-sm pl-4">
                                    <span class="text-gray-600">{{ $tax['name'] }} ({{ $tax['rate'] }}):</span>
                                    <span class="text-red-600">-{{ number_format($tax['amount'], 2) }}</span>
                                </div>
                            @endforeach
                            <div class="flex justify-between text-sm font-medium border-t border-dashed pt-1">
                                <span>Total Taxes:</span>
                                <span class="text-red-600">-{{ number_format($receiptData['total_taxes'] ?? 0, 2) }}</span>
                            </div>
                        </div>
                    @endif

                    <!-- Total Amount -->
                    <div class="flex justify-between font-bold text-lg border-t pt-2">
                        <span>Total Amount:</span>
                        <span class="{{ $receiptType === 'withdrawal' ? 'text-red-600' : 'text-green-600' }}">
                            {{ $receiptType === 'withdrawal' ? '-' : '+' }}{{ number_format($receiptData['total_amount'] ?? 0, 2) }}
                        </span>
                    </div>

                    <!-- New Balance -->
                    <div class="flex justify-between border-t pt-2">
                        <span class="font-semibold">Available Balance:</span>
                        <span>{{ number_format($receiptData['balance'] ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-6 text-center text-sm text-gray-600">
                <p>Thank you for banking with us!</p>
                <p>Please keep this receipt for your records.</p>
            </div>
        </div>

        <x-slot:actions>
            <x-mary-button label="Print" icon="o-printer" @click="window.print()" class="bg-blue-500 text-white" />
            <x-mary-button label="Close" @click="$wire.showReceiptModal = false" class="bg-gray-500 text-white" />
        </x-slot:actions>
    </x-mary-modal>
</div>

