<div>
 <!-- Breadcrumbs -->
    <div class="text-sm breadcrumbs mb-7">
        <ul>
            <li><a href="{{route('customer-dashboard')}}" wire:navigate>Home</a></li>
            <li><a href="{{ route('my-accounts')}}" wire:navigate>MyAccounts</a></li>
            {{-- <li><a></a></li> --}}
        </ul>
    </div>
@if($account->status === 'active')
<div
    x-data="{
        tabSelected: 1,
        tabId: $id('tabs'),
        tabButtonClicked(tabButton) {
            this.tabSelected = tabButton.id.replace(this.tabId + '-', '');
            this.tabRepositionMarker(tabButton);
        },
        tabRepositionMarker(tabButton) {
            const rect = tabButton.getBoundingClientRect();
            const containerRect = this.$refs.tabButtons.getBoundingClientRect();
            this.$refs.tabMarker.style.width = `${rect.width}px`;
            this.$refs.tabMarker.style.height = `${rect.height}px`;
            this.$refs.tabMarker.style.left = `${rect.left - containerRect.left}px`;
            this.$refs.tabMarker.style.top = `${rect.top - containerRect.top}px`;
        },
        tabContentActive(tabContent) {
            return this.tabSelected == tabContent.id.replace(this.tabId + '-content-', '');
        }
    }"
    x-init="tabRepositionMarker($refs.tabButtons.firstElementChild);"
    class="relative w-full max-w-3xl mx-auto z-10"
>
    <!-- Tab Buttons -->
    <div
        x-ref="tabButtons"
        class="relative grid grid-cols-2 gap-2 p-1 text-gray-500 bg-gray-100 rounded-lg sm:grid-cols-3 md:grid-cols-5 dark:bg-inherit"
        >
        <button
            :id="$id(tabId)"
            @click="tabButtonClicked($el);"
            type="button"
            class="relative z-20 flex items-center justify-center w-full h-10 px-3 text-sm font-medium transition-all rounded-md cursor-pointer whitespace-nowrap dark:text-white"
        >
            Account Details
        </button>
        <button
            :id="$id(tabId)"
            @click="tabButtonClicked($el);"
            type="button"
            class="relative z-20 flex items-center justify-center w-full h-10 px-3 text-sm font-medium transition-all rounded-md cursor-pointer whitespace-nowrap dark:text-white"
        >
            Withdrawals
        </button>
        <button
            :id="$id(tabId)"
            @click="tabButtonClicked($el);"
            type="button"
            class="relative z-20 flex items-center justify-center w-full h-10 px-3 text-sm font-medium transition-all rounded-md cursor-pointer whitespace-nowrap dark:text-white"
        >
            Deposits
        </button>
        <button
            :id="$id(tabId)"
            @click="tabButtonClicked($el);"
            type="button"
            class="relative z-20 flex items-center justify-center w-full h-10 px-3 text-sm font-medium transition-all rounded-md cursor-pointer whitespace-nowrap dark:text-white"
        >
            Transfers
        </button>
        <button
            :id="$id(tabId)"
            @click="tabButtonClicked($el);"
            type="button"
            class="relative z-20 flex items-center justify-center w-full h-10 px-3 text-sm font-medium transition-all rounded-md cursor-pointer whitespace-nowrap dark:text-white"
        >
            History
        </button>
        <div
            x-ref="tabMarker"
            class="absolute left-0 top-0 z-10 h-10 duration-300 ease-out"
            x-cloak
        >
            <div class="w-full h-full bg-white rounded-md shadow-sm dark:bg-blue-700"></div>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="relative w-full mt-4 content">
        <!-- Account Details Tab -->
        <div
            :id="$id(tabId + '-content')"
            x-show="tabContentActive($el)"
            class="relative"
            >
            <div class="p-6 bg-white border rounded-lg shadow-sm dark:bg-inherit dark:text-white">
                {{-- <h3 class="text-lg font-semibold">Account Details</h3> --}}

                <p class="text-gray-600 mb-3 dark:text-white">View and edit your account details here.</p>
                <div class="max-w-3xl mx-auto p-6 bg-white border rounded-lg shadow-sm dark:bg-inherit dark:text-white">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6 dark:text-white">Account Details</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Customer Information -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-700 mb-2 dark:text-white">Full Name</h3>
                        <p class="text-gray-600 dark:text-slate-100">{{$account->customer->user->name}}</p>
                    </div>

                    <!-- Account Type -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-700 mb-2 dark:text-white">Account Type</h3>
                        <p class="text-gray-600 dark:text-slate-100">{{ $account->accountType->name}}</p>
                    </div>

                    <!-- Account Number -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-700 mb-2 dark:text-white">Account Number</h3>
                        <p class="text-gray-600 dark:text-slate-100">{{$account->account_number}}</p>
                    </div>

                    <!-- Account Balance -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-700 mb-2 dark:text-white">Balance</h3>
                        <p class="text-green-600 font-bold">{{$currency}} {{ number_format(convertCurrency($account->balance,'UGX', $currency),0) }}</p>
                    </div>

                    <!-- Account Status -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-700 mb-2 dark:text-white">Status</h3>
                        @if($account->status === 'active')
                            <x-wireui-badge class="bg-green-600 font-semibold capitalize p-3" lg label="{{ $account->status}}"/>
                        @endif
                    </div>


                    <!-- Created At -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-700 mb-2 dark:text-white">Created At</h3>
                        <p class="text-gray-600 dark:text-slate-100">{{ $account->created_at->format('F j, Y g:i A') }}</p>
                    </div>
                </div>

                    <!-- Actions -->
                    <div class="mt-8 flex justify-end gap-4">
                        <x-wireui-button  icon="pencil" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-md hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-400" label="Edit Account"/>

                        <x-wireui-button right-icon="exclamation-triangle"  class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg shadow-md hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-400" label="Close Account"/>
                    </div>
                </div>

            </div>
        </div>

        <!-- Withdrawals Tab -->
        <div
            :id="$id(tabId + '-content')"
            x-show="tabContentActive($el)"
            class="relative"
            x-cloak
            >
            <div class="p-6 bg-white border rounded-lg shadow-sm dark:bg-inherit">
                <h3 class="text-lg font-semibold">Withdrawals</h3>
                <p class="text-gray-600 dark:text-white">View your withdrawalss details here.</p>
                <x-mary-form wire:submit="withdraw" x-data="{ withdrawalMethod: 'default' }">
                    <div class="p-2">
                        <div class="mb-4 text-center dark:text-yellow-100">
                            <label class="block text-sm font-medium text-gray-700 dark:text-yellow-100">Withdrawal Method</label>
                            <div class="mt-2 flex justify-center space-x-4">
                                <!-- Default Method Button -->
                                <button type="button" @click="withdrawalMethod = 'default'" :class="{
                                    'bg-blue-500 text-white': withdrawalMethod === 'default',
                                    'bg-gray-200 text-gray-700': withdrawalMethod !== 'default'
                                }" class="px-4 py-2 rounded-md">Cash</button>

                                <!-- Card Method Button -->
                                <button type="button" @click="withdrawalMethod = 'card'" :class="{
                                    'bg-blue-500 text-white': withdrawalMethod === 'card',
                                    'bg-gray-200 text-gray-700': withdrawalMethod !== 'card'
                                }" class="px-4 py-2 rounded-md">Card</button>

                                <!-- Mobile Money Method Button -->
                                <button type="button" @click="withdrawalMethod = 'mobile_money'" :class="{
                                    'bg-blue-500 text-white': withdrawalMethod === 'mobile_money',
                                    'bg-gray-200 text-gray-700': withdrawalMethod !== 'mobile_money'
                                }" class="px-4 py-2 rounded-md">Mobile Money</button>
                            </div>
                        </div>

                        <!-- Default Withdrawal Form -->
                        <div x-show="withdrawalMethod === 'default'">
                            {{-- <x-mary-input wire:model="withdrawalAmount" type="number" step="0.01" placeholder="Enter withdrawal amount"  :prefix="$currency" money inline class="w-full h-10 px-4 py-2 text-sm bg-gray-100 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-gray-400"/> --}}
                            <x-mary-input wire:model="withdrawalAmount" type="number" step="0.01" placeholder="Enter withdrawal amount" class="bg-gray-100 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-gray-400">
                                <x-slot:prepend>
                                <div class="inline-flex items-center gap-2">
                                        <form wire:submit.prevent="updateCurrency">
                                            <div class="relative">
                                                <select
                                                    wire:model.live="currency"
                                                    class="h-full bg-base-200 border border-gray-300 rounded-l-md px-4 py-3 text-sm text-gray-700 focus:outline-none focus:ring focus:ring-primary-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-primary-500">
                                                    @foreach (config('currencies.supported') as $code => $details)
                                                        <option wire:loading.remove value="{{ $code }}">
                                                            {{ $details['name'] }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <!-- Spinner -->
                                                <div
                                                    wire:loading
                                                    wire:target="currency"
                                                    class="absolute inset-y-0 right-4 top-2.5 flex items-center">
                                                    <svg class="animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                </x-slot:prepend>
                            </x-mary-input>
                        </div>

                        <!-- Card Withdrawal Form -->
                        <div x-show="withdrawalMethod === 'card'">
                            <x-mary-input label="Card Number" wire:model="cardNumber" type="text" placeholder="Enter card number" />
                            <x-mary-input label="Expiry Date" wire:model="cardExpiry" type="text" placeholder="MM/YY" />
                            <x-mary-input label="CVV" wire:model="cardCvv" type="number" placeholder="Enter CVV" />
                            <x-mary-input label="Amount" wire:model="withdrawalAmount" type="number" step="0.01" placeholder="Enter withdrawal amount" />
                        </div>

                        <!-- Mobile Money Withdrawal Form -->
                        <div x-show="withdrawalMethod === 'mobile_money'">
                            <x-mary-input label="Phone Number" wire:model="mobileNumber" type="tel" placeholder="Enter mobile number" />
                            <x-mary-input label="Amount" wire:model="withdrawalAmount" type="number" step="0.01" placeholder="Enter withdrawal amount" />
                        </div>
                    </div>

                    <x-slot:actions>
                        {{-- @if($this->withdrawFromAccount) --}}
                            <x-wireui-button icon="arrow-left" label="Withdraw" wire:click="withdraw({{$account->id}})" class="bg-red-500 text-white text-sm px-3 py-1 focus:outline-none focus:ring-2 focus:ring-red-400 " spinner="withdraw({{$account->id}})" />
                        {{-- @endif --}}
                    </x-slot:actions>
                </x-mary-form>


            </div>
        </div>

        <!-- Deposits Tab -->
        <div
            :id="$id(tabId + '-content')"
            x-show="tabContentActive($el)"
            class="relative"
            x-cloak
            >
            <div class="p-6 bg-white border rounded-lg shadow-sm dark:bg-inherit">
                <h3 class="text-lg font-semibold">Deposits</h3>
                <p class="text-gray-600 mb-2 dark:text-white">View your deposits details here.</p>
                  <x-mary-form wire:submit="deposit" x-data="{ depositMethod: 'default' }">
                    <div class="p-4">
                        <!-- Deposit Method Selection -->
                        <div class="mb-6 text-center">
                            <label class="block text-sm font-medium text-gray-700 mb-3 dark:text-yellow-100">Deposit Method</label>
                            <div class="flex flex-wrap justify-center gap-4">
                                <button
                                    type="button"
                                    @click="depositMethod = 'default'"
                                    :class="{'bg-blue-500 text-white ring-2 ring-blue-400': depositMethod === 'default', 'bg-gray-200 text-gray-700': depositMethod !== 'default'}"
                                    class="px-4 py-2 rounded-md focus:outline-none">
                                    Cash
                                </button>
                                <button
                                    type="button"
                                    @click="depositMethod = 'card'"
                                    :class="{'bg-blue-500 text-white ring-2 ring-blue-400': depositMethod === 'card', 'bg-gray-200 text-gray-700': depositMethod !== 'card'}"
                                    class="px-4 py-2 rounded-md focus:outline-none">
                                    Card
                                </button>
                                <button
                                    type="button"
                                    @click="depositMethod = 'mobile_money'"
                                    :class="{'bg-blue-500 text-white ring-2 ring-blue-400': depositMethod === 'mobile_money', 'bg-gray-200 text-gray-700': depositMethod !== 'mobile_money'}"
                                    class="px-4 py-2 rounded-md focus:outline-none">
                                    Mobile Money
                                </button>
                            </div>
                        </div>

                        <!-- default Deposit Method -->
                        <div x-show="depositMethod === 'default'" class="text-center" x-cloak>
                           <x-mary-input wire:model="depositAmount" type="number" step="0.01" placeholder="Enter deposit amount" class="bg-gray-100 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-gray-400">
                                <x-slot:prepend>
                                <div class="inline-flex items-center gap-2">
                                        <form wire:submit.prevent="updateCurrency">
                                            <div class="relative">
                                                <select
                                                    wire:model.live="currency"
                                                    class="h-full bg-base-200 border border-gray-300 rounded-l-md px-4 py-3 text-sm text-gray-700 focus:outline-none focus:ring focus:ring-primary-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-primary-500">
                                                    @foreach (config('currencies.supported') as $code => $details)
                                                        <option wire:loading.remove value="{{ $code }}">
                                                            {{ $details['name'] }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <!-- Spinner -->
                                                <div
                                                    wire:loading
                                                    wire:target="currency"
                                                    class="absolute inset-y-0 right-4 top-2.5 flex items-center">
                                                    <svg class="animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                </x-slot:prepend>
                            </x-mary-input>
                        </div>

                        <!-- Card Deposit Method -->
                        <div x-show="depositMethod === 'card'" class="text-center" x-cloak>
                            <x-mary-input
                                label="Card Number"
                                wire:model="cardNumber"
                                type="text"
                                placeholder="Enter card number" />
                            <x-mary-input
                                label="Expiry Date"
                                wire:model="cardExpiry"
                                type="text"
                                placeholder="MM/YY" />
                            <x-mary-input
                                label="CVV"
                                wire:model="cardCvv"
                                type="number"
                                placeholder="Enter CVV" />
                            <x-mary-input
                                label="Amount"
                                wire:model="depositAmount"
                                type="number"
                                step="0.01"
                                placeholder="Enter deposit amount" />
                        </div>

                        <!-- Mobile Money Deposit Method -->
                        <div x-show="depositMethod === 'mobile_money'" class="text-center" x-cloak>
                            <x-mary-input
                                label="Phone Number"
                                wire:model="mobileNumber"
                                type="tel"
                                placeholder="Enter mobile number" />
                            <x-mary-input
                                label="Amount"
                                wire:model="depositAmount"
                                type="number"
                                step="0.01"
                                placeholder="Enter deposit amount" />
                        </div>
                    </div>

                    <!-- Actions -->
                    <x-slot:actions>
                        @if($this->depositToAccount)
                            <x-wireui-button
                                icon="arrow-right"
                                label="Deposit"
                                wire:click="deposit({{$account->id}})"
                                class="bg-green-500 text-white text-sm px-3 py-1 focus:outline-none focus:ring-2 focus:ring-green-400"
                                spinner="deposit({{$account->id}})" />
                        @endif
                    </x-slot:actions>
                </x-mary-form>
            </div>
        </div>

        <!-- Transfers Tab -->
        <div
            :id="$id(tabId + '-content')"
            x-show="tabContentActive($el)"
            class="relative"
            x-cloak
            >
            <div class="p-1 bg-white border rounded-lg shadow-sm dark:bg-inherit">
                <h3 class="text-lg font-semibold mb-3 p-5">Transfers</h3>
                <div x-data="{ activeTab: 'local-rgmbank', isTabOpen: true }" class="flex flex-col lg:flex-row">
                    <!-- Left Side Tabs (Vertical) on Large Screens, Horizontal on Mobile -->
                    <div :class="isTabOpen ? 'w-64' : 'w-16'" class="transition-all duration-300 flex-shrink-0 lg:w-64 lg:block w-full">
                        <div class="bg-slate-100 text-white h-full p-4 space-y-4 dark:bg-inherit">
                            <!-- Toggle Button (Visible on mobile) -->
                            <button
                                @click="isTabOpen = !isTabOpen"
                                class="lg:hidden text-white focus:outline-none mb-4">
                                {{-- <i :class="isTabOpen ? 'fas fa-chevron-left' : 'fas fa-chevron-right'"></i> --}}
                            </button>

                            <!-- Tab Buttons -->
                            <button
                                @click="activeTab = 'local-rgmbank'"
                                :class="activeTab === 'local-rgmbank' ? 'bg-blue-500 text-white' : 'bg-gray-700 text-gray-300'"
                                class="w-full text-left px-4 py-2 rounded-md flex items-center">
                                <i class="fas fa-university mr-2"></i> Local RGMBank Account
                            </button>
                            <button
                                @click="activeTab = 'local-bank'"
                                :class="activeTab === 'local-bank' ? 'bg-blue-500 text-white' : 'bg-gray-700 text-gray-300'"
                                class="w-full text-left px-4 py-2 rounded-md flex items-center">
                                <i class="fas fa-building mr-2"></i> Other LocalBank Account
                            </button>
                            <button
                                @click="activeTab = 'international'"
                                :class="activeTab === 'international' ? 'bg-blue-500 text-white' : 'bg-gray-700 text-gray-300'"
                                class="w-full text-left px-4 py-2 rounded-md flex items-center">
                                <i class="fas fa-globe mr-2"></i> International Transfers
                            </button>
                        </div>
                    </div>

                    <!-- Right Side Content -->
                    <div class="flex-1 p-1 bg-gray-100 dark:bg-inherit">
                        <div class="space-y-6">
                            <!-- Local RGMBank Content -->
                            <div x-show="activeTab === 'local-rgmbank'" :id="$id('local-rgmbank-content')" class="relative bg-white p-3 border rounded-lg shadow-sm dark:bg-inherit dark:text-white mt-6" x-cloak>
                                <h3 class="text-lg font-semibold">Local RMG Bank Account</h3>
                                <div x-data="{
                                        activeAccordion: '',
                                        setActiveAccordion(id) {
                                            this.activeAccordion = (this.activeAccordion == id) ? '' : id
                                        }
                                    }" class="relative w-full max-w-md mx-auto text-xs">

                                    <!-- Accordion for "Transfer to Own Accounts" -->
                                    <div x-data="{ id: $id('ownAccount') }" :class="{ 'border-neutral-200/60 text-neutral-800 dark:text-white' : activeAccordion==id, 'border-transparent text-neutral-600 dark:text-white  dark:hover:text-yellow-100' : activeAccordion!=id }" class="duration-200 ease-out bg-white border rounded-md cursor-pointer group dark:bg-inherit dark:text-white" x-cloak>
                                        <button @click="setActiveAccordion(id)" class="flex items-center justify-between w-full px-5 py-4 font-semibold text-left select-none">
                                            <span>Transfer to Own Accounts</span>
                                            <div :class="{ 'rotate-90': activeAccordion==id }" class="relative flex items-center justify-center w-2.5 h-2.5 duration-300 ease-out dark:text-white">
                                                <div class="absolute w-0.5 h-full bg-neutral-500 group-hover:bg-neutral-800 rounded-full"></div>
                                                <div :class="{ 'rotate-90': activeAccordion==id }" class="absolute w-full h-0.5 ease duration-500 bg-neutral-500 group-hover:bg-neutral-800 rounded-full"></div>
                                            </div>
                                        </button>
                                        <div x-show="activeAccordion==id" x-collapse x-cloak>
                                            <div class="p-5 pt-0 opacity-70">
                                                <!-- Form or content for transferring to own accounts -->
                                                <form  wire:submit="transfer">
                                                    <div class="space-y-4">
                                                        <!-- Label for From Account -->
                                                        <label for="ownFromAccount" class="block text-lg font-semibold text-gray-700 dark:text-white">From Account</label>

                                                        <!-- Account Details -->
                                                        <div class="bg-white p-4 border rounded-md shadow-sm">
                                                            <!-- Account Number -->
                                                            <p class="text-sm font-medium text-gray-800">
                                                                <span class="font-semibold">Account Number:</span> {{ $account->account_number }}
                                                            </p>

                                                            <!-- Account Type -->
                                                            <p class="text-sm font-medium text-gray-800">
                                                                <span class="font-semibold">Account Type:</span> {{ $account->accountType->name }}
                                                            </p>

                                                            <!-- Account Balance -->
                                                            <p class="text-sm font-medium text-gray-800">
                                                                <span class="font-semibold">Balance:</span> UGX {{ number_format($account->balance, 2) }}
                                                            </p>
                                                        </div>
                                                    </div>


                                                    <div class="mt-4">
                                                       <label for="ToAccount" class="block text-lg font-semibold text-gray-700 mb-2 dark:text-white">To Account</label>
                                                        <x-mary-choices
                                                            label="Select Your Account"
                                                            wire:model="transferCustomerAccountId"
                                                            :options="$this->transferCustomerAccounts->where('id', '!=', $this->transferFromAccountId)"
                                                            single
                                                            searchable
                                                            class="border-sm shadow-sm"
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
                                                    <div class="mt-4">
                                                        <label for="amount" class="block text-gray-700 dark:text-white">Amount</label>
                                                        <input type="number" id="amount" wire:model="transferAmount" class="mt-1 block w-full px-4 py-2 border rounded-md">
                                                        @error('transferAmount')
                                                            <p class="text-red-400 italic text-sm">{{ $message }}</p>
                                                        @enderror
                                                    </div>
                                                    <div class="mt-4">
                                                         <x-wireui-button wire:click="transfer({{$account->id}})" spinner.longest="transfer" primary label="transfer" class="bg-blue-500 text-white px-4 py-2 rounded-md"/>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Accordion for "Transfer to Other Accounts" -->
                                    <div x-data="{ id: $id('otherAccount') }" :class="{ 'border-neutral-200/60 text-neutral-800 dark:text-white' : activeAccordion==id, 'border-transparent text-neutral-600 hover:text-neutral-800 dark:hover:text-yellow-100' : activeAccordion!=id }" class="duration-200 ease-out bg-white border rounded-md cursor-pointer group dark:bg-inherit dark:text-white" x-cloak>
                                        <button @click="setActiveAccordion(id)" class="flex items-center justify-between w-full px-5 py-4 font-semibold text-left select-none">
                                            <span>Transfer to Other Accounts</span>
                                            <div :class="{ 'rotate-90': activeAccordion==id }" class="relative flex items-center justify-center w-2.5 h-2.5 duration-300 ease-out">
                                                <div class="absolute w-0.5 h-full bg-neutral-500 group-hover:bg-neutral-800 rounded-full"></div>
                                                <div :class="{ 'rotate-90': activeAccordion==id }" class="absolute w-full h-0.5 ease duration-500 bg-neutral-500 group-hover:bg-neutral-800 rounded-full dark:bg-white"></div>
                                            </div>
                                        </button>
                                        <div x-show="activeAccordion==id" x-collapse x-cloak>
                                            <div class="p-5 pt-0 opacity-70">
                                                <!-- Form or content for transferring to other accounts -->
                                                <form wire:submit="transfer" class="space-y-4">
                                                    <div>
                                                        <label for="beneficiaryName" class="block text-gray-700 dark:text-white">Beneficiary Name</label>
                                                        <input type="text" id="beneficiaryName" wire:model="beneficiaryName" class="mt-1 block w-full px-4 py-2 border rounded-md" placeholder="Enter Name">
                                                        @error('beneficiaryName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                                    </div>

                                                    <div>
                                                        <label for="accountNumber" class="block text-gray-700 dark:text-white">Beneficiary Account Number</label>
                                                        <input type="text" id="accountNumber" wire:model="accountNumber" class="mt-1 block w-full px-4 py-2 border rounded-md" placeholder="Enter Account Number">
                                                        @error('accountNumber') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                                    </div>

                                                    <div>
                                                        <label for="amount" class="block text-gray-700 dark:text-white">Amount</label>
                                                        <input type="number" id="amount" wire:model="transferAmount" class="mt-1 block w-full px-4 py-2 border rounded-md" >
                                                        @error('transferAmount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                                    </div>

                                                    <!-- Add Checkbox for Saving Beneficiary -->
                                                    <div class="flex items-center mt-4">
                                                        <input type="checkbox" id="saveBeneficiary" wire:model="saveBeneficiary" class="h-4 w-4 text-blue-900 border-gray-300 rounded">
                                                        <label for="saveBeneficiary" class="ml-2 text-gray-700 dark:text-yellow-200">Save this beneficiary for future use</label>
                                                    </div>

                                                    <div>
                                                        <x-wireui-button wire:click="transfer({{$account->id}})" spinner.longest="transfer({{$account->id}})"   class="bg-blue-500 text-white px-4 py-2 rounded-md" label="transfer"/>
                                                    </div>

                                                </form>

                                            </div>
                                        </div>
                                    </div>

                                    {{-- transfer to beneficiaries --}}
                                    <div x-data="{ id: $id('beneficiaries') }" :class="{ 'border-neutral-200/60 text-neutral-800 dark:text-white' : activeAccordion==id, 'border-transparent text-neutral-600 hover:text-neutral-800 dark:hover:text-yellow-100' : activeAccordion!=id }" class="duration-200 ease-out bg-white border rounded-md cursor-pointer group dark:bg-inherit dark:text-white" x-cloak>
                                        <button @click="setActiveAccordion(id)" class="flex items-center justify-between w-full px-5 py-4 font-semibold text-left select-none">
                                            <span>Transfer to Beneficiary Accounts</span>
                                            <div :class="{ 'rotate-90': activeAccordion==id }" class="relative flex items-center justify-center w-2.5 h-2.5 duration-300 ease-out">
                                                <div class="absolute w-0.5 h-full bg-neutral-500 group-hover:bg-neutral-800 rounded-full"></div>
                                                <div :class="{ 'rotate-90': activeAccordion==id }" class="absolute w-full h-0.5 ease duration-500 bg-neutral-500 group-hover:bg-neutral-800 rounded-full dark:bg-white"></div>
                                            </div>
                                        </button>
                                        <div x-show="activeAccordion==id" x-collapse x-cloak>
                                            <div class="p-5 pt-0 opacity-70 overflow-y-scroll">
                                                <form wire:submit.prevent="transfer({{ $account->id }})" class="space-y-4">
                                                    <x-wireui-select  wire:model="beneficiarySelectedIndex" label="Beneficiaries" placeholder="Select one beneficiary"  min-items-for-search  without-items-count >
                                                        @foreach ($beneficiaries as $index => $beneficiary)
                                                            <x-wireui-select.option label="{{ $beneficiary['nickname'] }} ({{ $beneficiary['account_number'] }})" value="{{ $index }}" />
                                                        @endforeach
                                                    </x-select>


                                                    <div>
                                                        <label for="amount" class="block text-gray-700 dark:text-white">Amount</label>
                                                        <input type="number" id="amount" wire:model="transferAmount" class="mt-1 block w-full px-4 py-2 border rounded-md">
                                                        @error('transferAmount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                                    </div>


                                                    <div>
                                                        <x-wireui-button wire:click="transfer({{$account->id}})" spinner.longest="transfer({{$account->id}})" class="bg-blue-500 text-white px-4 py-2 rounded-md" label="Transfer"/>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- Other Local Bank Content -->
                            <div x-show="activeTab === 'local-bank'" :id="$id('local-bank-content')" class="relative bg-white p-6 border rounded-lg shadow-sm dark:bg-inherit" x-cloak>
                                <h3 class="text-lg font-semibold">Other Local Bank Account</h3>
                                <p class="text-gray-600">Manage your transfers to other local bank accounts here.</p>
                                    <form wire:submit="transferToOtherLocalBank" class="space-y-4">
                                        <div>
                                            <label for="beneficiaryName" class="block text-gray-700 dark:text-white">Beneficiary Name</label>
                                            <input type="text" id="beneficiaryName" wire:model="beneficiaryName" class="mt-1 block w-full px-4 py-2 border rounded-md" placeholder="Enter Name">
                                            @error('beneficiaryName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label for="accountNumber" class="block text-gray-700 dark:text-white">Beneficiary Account Number</label>
                                            <input type="text" id="accountNumber" wire:model="accountNumber" class="mt-1 block w-full px-4 py-2 border rounded-md" placeholder="Enter Account Number">
                                            @error('accountNumber') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label for="bankName" class="block text-gray-700 dark:text-white">Bank Name</label>
                                            <input type="text" id="bankName" wire:model="bankName" class="mt-1 block w-full px-4 py-2 border rounded-md" placeholder="Enter Bank Name">
                                            @error('bankName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label for="amount" class="block text-gray-700 dark:text-white">Amount</label>
                                            <input type="number" id="amount" wire:model="transferAmount" class="mt-1 block w-full px-4 py-2 border rounded-md" >
                                            @error('transferAmount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Add Checkbox for Saving Beneficiary -->
                                        <div class="flex items-center mt-4">
                                            <input type="checkbox" id="saveBeneficiary" wire:model="saveBeneficiary" class="h-4 w-4 text-blue-900 border-gray-300 rounded">
                                            <label for="saveBeneficiary" class="ml-2 text-gray-700 dark:text-yellow-200">Save this beneficiary for future use</label>
                                        </div>

                                        <div>
                                            <x-wireui-button wire:click="transferToOtherLocalBank({{$account->id}})" spinner.longest="transfer({{$account->id}})"   class="bg-blue-500 text-white px-4 py-2 rounded-md" label="transfer"/>
                                        </div>

                                    </form>

                            </div>

                            <!-- International Content -->
                            <div x-show="activeTab === 'international'" :id="$id('international-content')" class="relative bg-white p-6 border rounded-lg shadow-sm dark:bg-inherit" x-cloak>
                                <h3 class="text-lg font-semibold">International Transfers</h3>
                                <p class="text-gray-600 dark:text-white">Manage your international transfers here.</p>
                                <!-- Add content for International transfers here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- History Tab -->
        <div
            :id="$id(tabId + '-content')"
            x-show="tabContentActive($el)"
            class="relative"
            x-cloak
            >
            <div class="p-6 bg-white border rounded-lg shadow-sm">
                <h3 class="text-lg font-semibold">Account History</h3>
                <p class="text-gray-600">Review your account history here.</p>
            </div>

            <!-- Filters Section -->
            <div class="space-y-6 mt-4">
                {{-- search and export --}}
                <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                    <!-- Search Input -->
                    <div class="w-full md:w-1/2 lg:w-1/3">
                        <x-mary-input
                            icon="o-magnifying-glass"
                            placeholder="Search reference number..."
                            wire:model.live.debounce.300ms="search"
                            class="w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 shadow-sm focus:border-primary-500 focus:ring-0 focus:outline-none transition-all duration-200 ease-in-out"
                        />
                    </div>
                    <!-- Export Button -->
                    <x-wireui-button
                        icon="arrow-down-tray"
                        label="Export"
                        class="bg-primary w-full sm:w-auto"
                        wire:click="export"
                        spinner="export"
                    />
                </div>

                <!-- Filters Grid -->
                <div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Type Filter -->
                        <div>
                            @php
                                $types = [
                                    ['id' => '', 'name' => 'All Types'],
                                    ['id' => 'deposit', 'name' => 'deposit'],
                                    ['id' => 'withdrawal', 'name' => 'withdrawal'],
                                    ['id' => 'transfer', 'name' => 'transfer'],
                                    ['id' => 'loanPayment', 'name' => 'loanPayment']
                                ];
                            @endphp

                            <x-mary-select
                                wire:model.live="type"
                                :options="$types"
                                placeholder="Filter by type"
                                class="w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:border-primary-500 focus:ring-0 focus:outline-none transition-all duration-200 ease-in-out"
                            />
                        </div>

                        <!-- Status Filter -->
                        <div>
                            @php
                                $statuses = [
                                    ['id' => '', 'name' => 'All Status'],
                                    ['id' => 'completed', 'name' => 'Completed'],
                                    ['id' => 'pending', 'name' => 'Pending'],
                                    ['id' => 'failed', 'name' => 'Failed']
                                ];
                            @endphp

                            <x-mary-select
                                wire:model.live="status"
                                :options="$statuses"
                                placeholder="Filter by status"
                                class="w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:border-primary-500 focus:ring-0 focus:outline-none transition-all duration-200 ease-in-out"
                            />
                        </div>

                        <!-- Date Range -->
                        <div>
                            @php
                                $dateConfig = [
                                    'mode' => 'range',
                                    'dateFormat' => 'Y-m-d',
                                    'altFormat' => 'M j, Y',
                                    'enableTime' => false,
                                ];
                            @endphp

                            <x-mary-datepicker
                                wire:model.live="dateRange"
                                icon="o-calendar"
                                :config="$dateConfig"
                                placeholder="Select date range"
                                class="w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:border-primary-500 focus:ring-0 focus:outline-none transition-all duration-200 ease-in-out"
                            />
                        </div>
                    </div>
                </div>

                <!-- Reset Filters Button -->
                <div x-data="{
                        tooltipVisible: false,
                        tooltipText: 'Filters reset successfully!',
                        tooltipArrow: true,
                        tooltipPosition: 'left', // Set position to 'left'
                        showTooltip() {
                            this.tooltipVisible = true;
                            setTimeout(() => {
                                this.tooltipVisible = false;
                            }, 2000);
                        }
                    }"
                    class="relative mt-4 flex justify-end"
                    x-init="
                        @this.on('showTooltip', () => showTooltip())  <!-- Listen for the Livewire event -->
                    ">

                    <!-- Tooltip -->
                    <div x-show="tooltipVisible"
                        :class="{
                            'top-0 left-1/2 -translate-x-1/2 -mt-0.5 -translate-y-full': tooltipPosition == 'top',
                            'top-1/2 -translate-y-1/2 left-0 -translate-x-[13px]': tooltipPosition == 'left',
                            'bottom-0 left-1/2 -translate-x-1/2 -mb-0.5 translate-y-full': tooltipPosition == 'bottom',
                            'top-1/2 -translate-y-1/2 -mr-0.5 right-0 translate-x-full': tooltipPosition == 'right'
                        }"
                        class="absolute w-auto text-sm" x-cloak>
                        <div x-show="tooltipVisible"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 scale-90 -translate-x-2"
                            x-transition:enter-end="opacity-100 scale-100 translate-x-0"
                            x-transition:leave="transition ease-in duration-300"
                            x-transition:leave-start="opacity-100 scale-100 translate-x-0"
                            x-transition:leave-end="opacity-0 scale-90 -translate-x-2"
                            class="relative px-2 py-1 text-white rounded bg-gradient-to-t from-blue-600 to-purple-600 bg-opacity-90">
                            <p x-text="tooltipText" class="flex-shrink-0 block text-xs whitespace-nowrap"></p>
                        </div>
                    </div>

                    <!-- Reset Button -->
                    <x-mary-button
                        label="Reset Filters"
                        icon="o-arrow-path-rounded-square"
                        class="bg-red-500 text-white hover:bg-red-600"
                        wire:click="resetFilters"
                        spinner="resetFilters"
                    />
                </div>


                <!-- Pagination Options -->
                <div class="flex items-center space-x-4">
                    @php
                        $perPageOptions = [
                            ['id' => 10, 'name' => '10'],
                            ['id' => 25, 'name' => '25'],
                            ['id' => 50, 'name' => '50'],
                            ['id' => 100, 'name' => '100']
                        ];
                    @endphp
                    <span class="text-sm text-gray-600 dark:text-gray-400">Show</span>
                    <x-mary-select
                        wire:model.live="perPage"
                        :options="$perPageOptions"
                        class="w-20 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:border-primary-500 focus:ring-0 focus:outline-none transition-all duration-200 ease-in-out"
                    />
                    <span class="text-sm text-gray-600 dark:text-gray-400">entries</span>
                </div>
            </div>

           <div class="overflow-x-scroll">
                <table class="w-full min-w-[800px] text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <!-- Master Checkbox -->
                            <th class="px-4 py-3">
                                <input type="checkbox"
                                    class="form-checkbox"
                                    wire:model="selectAll"
                                    wire:click="toggleSelectAll" />
                            </th>
                            <th wire:click="sortBy('reference')" class="px-4 py-3 cursor-pointer">
                                Reference
                                @if($sortField === 'reference')
                                    <span>{!! $sortDirection === 'asc' ? '↑' : '↓' !!}</span>
                                @endif
                            </th>
                            <th wire:click="sortBy('type')" class="px-4 py-3 cursor-pointer">
                                Type
                                @if($sortField === 'type')
                                    <span>{!! $sortDirection === 'asc' ? '↑' : '↓' !!}</span>
                                @endif
                            </th>
                            <th wire:click="sortBy('amount')" class="px-4 py-3 cursor-pointer">
                                Amount
                                @if($sortField === 'amount')
                                    <span>{!! $sortDirection === 'asc' ? '↑' : '↓' !!}</span>
                                @endif
                            </th>
                            <th wire:click="sortBy('status')" class="px-4 py-3 cursor-pointer">
                                Status
                                @if($sortField === 'status')
                                    <span>{!! $sortDirection === 'asc' ? '↑' : '↓' !!}</span>
                                @endif
                            </th>
                            <th wire:click="sortBy('created_at')" class="px-4 py-3 cursor-pointer">
                                Date
                                @if($sortField === 'created_at')
                                    <span>{!! $sortDirection === 'asc' ? '↑' : '↓' !!}</span>
                                @endif
                            </th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($accountTransactionsBlade as $transaction)
                            <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <!-- Row Checkbox -->
                                <td class="px-4 py-3">
                                    <input type="checkbox"
                                        class="form-checkbox"
                                        wire:model="selectedTransactions"
                                        value="{{ $transaction->id }}" />
                                </td>
                                <td class="px-4 py-3">{{ $transaction->reference_number }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 font-semibold text-sm rounded-sm text-white
                                        {{ $transaction->type === 'deposit' ? 'bg-green-500' :
                                        ($transaction->type === 'withdrawal' ? 'bg-yellow-500' : 'bg-blue-500') }}">
                                        {{ ucfirst($transaction->type) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">{{$currency}} {{ number_format(convertCurrency($transaction->amount, 'UGX' , $currency), 2) }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 font-semibold text-sm rounded-sm text-white
                                        {{ $transaction->status === 'completed' ? 'bg-green-500' :
                                        ($transaction->status === 'pending' ? 'bg-yellow-500' : 'bg-red-500') }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center space-x-2">
                                        <x-mary-button
                                            icon="o-eye"
                                            class="btn-ghost btn-sm"
                                            wire:click="viewTransaction({{ $transaction->id }})"
                                            spinner="viewTransaction({{ $transaction->id }})"
                                        />
                                        <x-mary-button
                                            icon="o-document-duplicate"
                                            class="btn-ghost btn-sm"
                                            wire:click="copyToClipboard('{{ $transaction->reference_number }}')"
                                        />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    No transactions found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            <div class="mt-4">
                {{ $accountTransactionsBlade->links() }}  <!-- Pagination controls -->
            </div>
            </div>
        </div>
    </div>

    <!-- Transaction View Modal -->
    <x-mary-modal wire:model="viewModal" separator>
        @if($selectedTransaction)
            <div class="p-4">
                <h2 class="text-lg font-semibold mb-4">Transaction Details</h2>

                <div class="space-y-4">
                    <div class="grid lg:grid-cols-2 md:grid-cols-1 gap-4">
                        <div>
                            <label class="text-sm text-gray-600 dark:text-gray-400">Reference</label>
                            <p class="font-medium">{{ $selectedTransaction->reference_number }}</p>
                        </div>

                        <div>
                            <label class="text-sm text-gray-600 dark:text-gray-400">Type</label>
                            <p>
                                <span class="px-2 py-0.5 font-semibold text-sm rounded-sm text-white
                                    {{ $selectedTransaction->type === 'deposit' ? 'bg-green-500' :
                                    ($selectedTransaction->type === 'withdrawal' ? 'bg-yellow-500' : 'bg-blue-500') }}">
                                    {{ ucfirst($selectedTransaction->type) }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <label class="text-sm text-gray-600 dark:text-gray-400">Amount</label>
                            <p class="font-medium">{{$currency}} {{ number_format(convertCurrency($selectedTransaction->amount,'UGX',$currency), 2) }}</p>
                        </div>

                        <div>
                            <label class="text-sm text-gray-600 dark:text-gray-400">Status</label>
                            <p>
                                <span class="px-2 py-0.5 font-semibold text-sm rounded-sm text-white
                                    {{ $selectedTransaction->status === 'completed' ? 'bg-green-500' :
                                    ($selectedTransaction->status === 'pending' ? 'bg-yellow-500' : 'bg-red-500') }}">
                                    {{ ucfirst($selectedTransaction->status) }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <label class="text-sm text-gray-600 dark:text-gray-400">Account Number</label>
                            <p class="font-medium text-wrap">{{ $selectedTransaction->account->account_number }}</p>
                        </div>

                        <div>
                            <label class="text-sm text-gray-600 dark:text-gray-400">Date</label>
                            <p class="font-medium">{{ $selectedTransaction->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>

                    @if($selectedTransaction->description)
                        <div>
                            <label class="text-sm text-gray-600 dark:text-gray-400">Description</label>
                            <p class="font-medium">{{ $selectedTransaction->description }}</p>
                        </div>
                    @endif
                </div>

                <div class="mt-6 flex justify-end">
                    <x-mary-button label="Close" @click="$wire.viewModal = false" />
                </div>
            </div>
        @endif
    </x-mary-modal>

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
                                    <span class="text-red-600">-{{ number_format(convertCurrency($charge['amount'],'UGX',$currency ), 2)}}</span>
                                </div>
                            @endforeach
                            <div class="flex justify-between text-sm font-medium border-t border-dashed pt-1">
                                <span>Total Charges:</span>
                                <span class="text-red-600">-{{ number_format(convertCurrency($receiptData['total_charges'] ,'UGX',$currency ) ?? 0, 2)}}</span>
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
                                    <span class="text-red-600">-{{ number_format(convertCurrency($tax['amount'], 'UGX', $currency),2) }}</span>
                                </div>
                            @endforeach
                            <div class="flex justify-between text-sm font-medium border-t border-dashed pt-1">
                                <span>Total Taxes:</span>
                                <span class="text-red-600">-{{ number_format(convertCurrency($receiptData['total_taxes'], 'UGX', $currency) ?? 0, 2) }}</span>
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
@else
    <div class="text-center bg-white p-6">
        <h1 class="font-bold text-red-500 text-xl">Sorry Your Account is not active</h1>, <h1 class="text-bold">Please contact RMG Finance Team For Assistance</h1>

        <div class="text-center mt-5">
            <a href="{{route('my-accounts')}}" wire:navigate>
            <x-wireui-button label="return back To Account List"  primary  class="bg-blue-500 text-white px-4 py-2 rounded-md" />
            </a>
        </div>
    </div>

@endif

</div>
