<div>
 <!-- Breadcrumbs -->
    <div class="text-sm breadcrumbs mb-7">
        <ul>
            <li><a href="{{route('customer-dashboard')}}" wire:navigate>Home</a></li>
            <li><a href="{{ route('my-loans')}}" wire:navigate>MyLoans</a></li>
            {{-- <li><a></a></li> --}}
        </ul>
    </div>
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
        class="relative grid grid-cols-2 gap-2 p-1 text-gray-500 bg-gray-100 rounded-lg sm:grid-cols-3 md:grid-cols-5 select-none  dark:bg-inherit"
        >
        <button
            :id="$id(tabId)"
            @click="tabButtonClicked($el);"
            type="button"
            class="relative z-20 flex items-center justify-center w-full h-10 px-3 text-sm font-medium transition-all rounded-md cursor-pointer whitespace-nowrap dark:text-white"
        >
            Loan Details
        </button>
        <button
            :id="$id(tabId)"
            @click="tabButtonClicked($el);"
            type="button"
            class="relative z-20 flex items-center justify-center w-full h-10 px-3 text-sm font-medium transition-all rounded-md cursor-pointer whitespace-nowrap dark:text-white"
        >
            Payment Schedule
        </button>
        <button
            :id="$id(tabId)"
            @click="tabButtonClicked($el);"
            type="button"
            class="relative z-20 flex items-center justify-center w-full h-10 px-3 text-sm font-medium transition-all rounded-md cursor-pointer whitespace-nowrap dark:text-white"
        >
            Make Payment
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

       <!-- Loan Details Tab -->
        <div :id="$id(tabId + '-content')" x-show="tabContentActive($el)" class="relative">
            <div class="p-6 bg-white border rounded-lg shadow-sm dark:bg-inherit dark:text-white">
                <p class="text-gray-600 mb-3 dark:text-white">View your Loans details here.</p>
                <div class="max-w-3xl mx-auto p-6 bg-white border rounded-lg shadow-sm dark:bg-inherit dark:text-white">
                    <div class="grid grid-cols-2 md:grid-cols-1 gap-6">
                        <div>
                            <h2 class="text-2xl font-semibold text-gray-800 mb-6 dark:text-white">Loan Details</h2>
                        </div>
                        @if($loan->status == 'active')
                            <div class="mb-4">
                                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                                    <div class="text-sm">
                                        <p><span class="font-semibold text-justify" role="alert">Next Payment Due:</span>
                                            {{ $loan->schedules->where('status', '!=', 'paid')->first()?->due_date->format('F j, Y g:i A')  }}
                                        </p>
                                        <p><span class="font-semibold">Amount Due:</span>
                                            {{ $currency .' '. number_format(convertCurrency($loan->schedules->where('status', '!=', 'paid')->first()?->remaining_amount, 'UGX', $currency), 2) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Customer Information -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-700 mb-2 dark:text-white">Loan Owner</h3>
                            <p class="text-gray-600 dark:text-slate-100">{{$loan->customer->user->name}}</p>
                        </div>

                        <!-- Loan Type -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-700 mb-2 dark:text-white">Loan Product</h3>
                            <p class="text-gray-600 dark:text-slate-100">{{ $loan->loanProduct->name}}</p>
                        </div>

                        <!-- Disbursement Account Number -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-700 mb-2 dark:text-white">Disbursement Account Number</h3>
                            <p class="text-gray-600 dark:text-slate-100">{{$loan->account->account_number}}</p>
                        </div>

                        <!-- Principal Amount -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-700 mb-2 dark:text-white">Principal Amount</h3>
                            <p class="text-green-600 font-bold">{{ $currency .' '. number_format(convertCurrency($loan->amount, 'UGX', $currency), 2) }}</p>
                        </div>

                        <!-- Total Payable Amount -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-700 mb-2 dark:text-white">Total Payable Amount</h3>
                            <p class="text-green-600 font-bold">{{ $currency .' '. number_format(convertCurrency($loan->total_payable, 'UGX', $currency), 0) }}</p>
                        </div>

                        <!-- Interest Rate -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-700 mb-2 dark:text-white">Interest Rate</h3>
                            <p class="text-gray-600">{{ $loan->interest_rate }}</p>
                        </div>

                        <!-- Total Term -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-700 mb-2 dark:text-white">Total Term ( months )</h3>
                            <p class="text-gray-600">{{ $loan->term }}</p>
                        </div>

                        <!-- Due Term -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-700 mb-2 dark:text-white">Due Term</h3>
                            <p class="text-gray-600 ">N/A</p>
                        </div>

                        <!-- Total Interest Rate -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-700 mb-2 dark:text-white">Total Interest Rate</h3>
                            <p class="text-green-600 font-bold">{{ $currency .' '. number_format(convertCurrency($loan->total_interest, 'UGX', $currency), 2) }}</p>
                        </div>

                        <!-- Payment Frequency -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-700 mb-2 dark:text-white">Payment Frequency</h3>
                            <p class="text-gray-600 ">{{ $loan->payment_frequency }}</p>
                        </div>

                        <!-- Processing Fee -->
                        @if($loan->processing_fee)
                            <div>
                                <h3 class="text-lg font-medium text-gray-700 mb-2 dark:text-white">Processing fee</h3>
                                <p class="text-gray-600 ">{{ $currency .' '. number_format(convertCurrency($loan->processing_fee, 'UGX', $currency), 2) }}</p>
                            </div>
                        @endif

                        <!-- Early Payment Fee -->
                        @if($loan->early_payment_fee)
                            <div>
                                <h3 class="text-lg font-medium text-gray-700 mb-2 dark:text-white">Early payment fee</h3>
                                <p class="text-gray-600 ">{{ $currency .' '. number_format(convertCurrency($loan->early_payment_fee, 'UGX', $currency), 2) }}</p>
                            </div>
                        @endif

                        <!-- Late Payment Fee -->
                        @if($loan->late_payment_fee)
                            <div>
                                <h3 class="text-lg font-medium text-gray-700 mb-2 dark:text-white">Late payment fee</h3>
                                <p class="text-gray-600 ">{{ $currency .' '. number_format(convertCurrency($loan->late_payment_fee, 'UGX', $currency), 2) }}</p>
                            </div>
                        @endif

                        <!-- Disbursement Date -->
                        @if($loan->disbursement_date)
                            <div>
                                <h3 class="text-lg font-medium text-gray-700 mb-2 dark:text-white">Disbursement Date</h3>
                                <p class="text-blue-600">{{ $loan->disbursement_date->format('F j, Y g:i A') }}</p>
                            </div>
                        @endif

                        <!-- Last Payment Date -->
                        @if($loan->last_payment_date)
                            <div>
                                <h3 class="text-lg font-medium text-gray-700 mb-2 dark:text-white">Last Payment Date</h3>
                                <p class="text-blue-600 font-bold">{{ $loan->last_payment_date->format('F j, Y g:i A') }}</p>
                            </div>
                        @endif

                        <!-- Account Status -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-700 mb-2 dark:text-white">Status</h3>
                            @if($loan->status === 'active')
                                <x-wireui-badge class="bg-green-500 font-semibold capitalize p-3" lg label="{{ $loan->status }}">
                                    <x-slot name="prepend" class="relative flex items-center w-2 h-2">
                                        <span class="absolute inline-flex w-full h-full rounded-full opacity-75 bg-white animate-ping"></span>
                                        <span class="relative inline-flex w-2 h-2 rounded-full bg-white"></span>
                                    </x-slot>
                                </x-wireui-badge>
                            @elseif($loan->status === 'paid')
                                <x-wireui-badge class="bg-indigo-500 font-semibold capitalize p-3" lg label="{{ $loan->status }}"/>
                                <p>This loan was closed on : {{ $loan->closed_at->format('F j, Y g:i A') }}</p>
                            @elseif($loan->status === 'pending')
                                <x-wireui-badge class="bg-yellow-300 font-semibold capitalize p-3" lg label="{{ $loan->status }}"/>
                            @elseif($loan->status === 'rejected')
                                <x-wireui-badge class="bg-red-500 font-semibold capitalize p-3" lg label="{{ $loan->status }}"/>
                            @elseif($loan->status === 'approved')
                                <x-wireui-badge class="bg-lime-300 font-semibold capitalize p-3" lg label="{{ $loan->status }}"/>
                                <p class="italic tooltip-info text-sm">Your loan is approved, please wait for disbursement</p>
                            @endif
                        </div>

                        <!-- Created At -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-700 mb-2 dark:text-white">Applied For On</h3>
                            <p class="text-blue-600 dark:text-slate-100">{{ $loan->created_at->format('F j, Y g:i A') }}</p>
                        </div>

                        @if($loan->approved_at)
                            <div>
                                <h3 class="text-lg font-medium text-gray-700 mb-2 dark:text-white">Approved At</h3>
                                <p class="text-blue-600 dark:text-slate-100">{{ $loan->approved_at->format('F j, Y g:i A') }}</p>
                            </div>
                        @endif

                        @if($loan->reject_at)
                            <div>
                                <h3 class="text-lg font-medium text-gray-700 mb-2 dark:text-white">Reject At</h3>
                                <p class="text-blue-600 dark:text-slate-100">{{ $loan->rejected_at->format('F j, Y g:i A') }}</p>
                            </div>
                            <div class="mb-4">
                                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                                    <h3 class="text-lg font-medium text-gray-700 mb-2 dark:text-white">Rejection Reason</h3>
                                    <p class="text-blue-600 dark:text-slate-100">{{ $loan->rejection_reason }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

       <!-- Payment Schedules Tab -->
        <div :id="$id(tabId + '-content')" x-show="tabContentActive($el)" class="relative" x-cloak>
            <div class="p-6 bg-white border rounded-lg shadow-sm dark:bg-inherit">
                <h3 class="text-lg font-semibold mb-3">Payment Schedules</h3>
                <p class="text-gray-600 dark:text-white mb-5">View your loan payment dates here.</p>
                @if($loan->status == 'active')
                    <div>
                        <div class="overflow-x-auto">
                            <table class="table table-compact w-full">
                                <thead>
                                    <tr>
                                        <th>Due Date</th>
                                        <th>Amount</th>
                                        <th>Paid</th>
                                        <th>Remaining</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($loan->schedules as $schedule)
                                        <tr>
                                            <td>{{ $schedule->due_date->format('Y-m-d') }}</td>
                                            <td>{{ $currency . ' ' . number_format(convertCurrency($schedule->total_amount, 'UGX', $currency), 2) }}</td> <!-- Using convertCurrency with $currency -->
                                            <td>{{ $currency . ' ' . number_format(convertCurrency($schedule->paid_amount, 'UGX', $currency), 2) }}</td> <!-- Using convertCurrency with $currency -->
                                            <td>{{ $currency . ' ' . number_format(convertCurrency($schedule->remaining_amount, 'UGX', $currency), 2) }}</td> <!-- Using convertCurrency with $currency -->
                                            <td>
                                                @if($schedule->status)
                                                    @if($schedule->status == 'pending')
                                                        <x-wireui-badge icon-size="sm" sm icon="x-mark" orange label="pending" />
                                                    @elseif($schedule->status == 'paid')
                                                        <x-wireui-badge icon-size="sm" sm icon="check" green label="paid" />
                                                    @else
                                                        <x-wireui-badge icon-size="sm" sm icon="clock" blue label="partial" />
                                                    @endif
                                                @else
                                                    <span class="text-gray-500">Not available</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <h1 class="text-pretty text-red-300 dark:text-yellow-200 font-bold text-center">Your Loan is closed or not active!, Contact Support Centre</h1>
                @endif
            </div>
        </div>

        <!-- Make Payment Tab -->
        <div :id="$id(tabId + '-content')" x-show="tabContentActive($el)" class="relative" x-cloak>
            <div class="p-6 bg-white border rounded-lg shadow-sm dark:bg-inherit">
                <h3 class="text-lg font-semibold mb-3">Make Loan Payment</h3>
                <p class="text-gray-600 dark:text-white mb-5"> You can make loan repayments here.</p>
                @if($loan->status == 'active')
                    <div x-data="{ paymentMethod: 'default' }">
                        <div class="p-4">
                            <div class="space-y-4">
                                <div class="mb-4">
                                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                                        <div class="text-sm">
                                            <p><span class="font-semibold text-justify" role="alert">Next Payment Due:</span>
                                                {{ $loan->schedules->where('status', '!=', 'paid')->first()?->due_date->format('F j, Y g:i A')  }}
                                            </p>
                                            <p><span class="font-semibold">Amount Due:</span>
                                                {{ $currency . ' ' . number_format(convertCurrency($loan->schedules->where('status', '!=', 'paid')->first()?->remaining_amount ?? 0, 'UGX', $currency), 2) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4 text-center">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-yellow-100 mb-3">Payment Method</label>
                                    <div class="mt-2 flex justify-center space-x-4">
                                        <button type="button" @click="paymentMethod = 'default'" :class="{'bg-blue-500 text-white': paymentMethod === 'default', 'bg-gray-200 text-gray-700': paymentMethod !== 'default'}" class="px-4 py-2 rounded-md">Default</button>
                                        <button type="button" @click="paymentMethod = 'card'" :class="{'bg-blue-500 text-white': paymentMethod === 'card', 'bg-gray-200 text-gray-700': paymentMethod !== 'card'}" class="px-4 py-2 rounded-md">Card</button>
                                        <button type="button" @click="paymentMethod = 'mobile_money'" :class="{'bg-blue-500 text-white': paymentMethod === 'mobile_money', 'bg-gray-200 text-gray-700': paymentMethod !== 'mobile_money'}" class="px-4 py-2 rounded-md">Mobile Money</button>
                                    </div>
                                </div>

                                <div x-show="paymentMethod === 'default'">
                                    <x-mary-form wire:submit="makePaymentFromAccount">
                                        <div class="text-center">
                                            <!-- Total Outstanding Amount Display -->
                                            <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                                                <p class="text-sm text-gray-600">Total Outstanding Amount</p>
                                                <p class="text-2xl font-bold text-gray-800">
                                                    {{ $currency . ' ' . number_format(convertCurrency($loan->schedules->where('status', '!=', 'paid')->sum('remaining_amount'), 'UGX', $currency), 2) }}
                                                </p>
                                            </div>

                                            <!-- Account Selection -->
                                            <x-mary-choices
                                                label="Select Account"
                                                wire:model="selectedAccount"
                                                :options="$userAccounts"
                                                single
                                                searchable
                                                class="border-b-2 border-white shadow-lg focus:border-none focus:outline-dashed"
                                                search-function="searchLoanToPaymentAccounts"
                                            >
                                                @scope('item', $account, $currency)
                                                    <x-mary-list-item :item="$account" sub-value="account_number">
                                                        <x-slot:avatar>
                                                            <x-mary-icon name="o-credit-card" class="bg-orange-100 p-2 w-8 h-8 rounded-full" />
                                                        </x-slot:avatar>
                                                        <p>{{$account->account_number}}</p>
                                                        <x-slot:actions>
                                                            <x-mary-badge :value="number_format(convertCurrency($account->balance, 'UGX', $currency), 2)" />
                                                        </x-slot:actions>
                                                    </x-mary-list-item>
                                                @endscope

                                                @scope('selection', $account)
                                                    {{ $account->account_number }}
                                                @endscope
                                            </x-mary-choices>

                                            <!-- Payment Type Selection -->
                                            <div class="mt-4 mb-4">
                                                <div class="flex justify-center space-x-2">
                                                    <x-mary-button
                                                        label="Partial Payment"
                                                        wire:click.prevent="resetPaymentAmount"
                                                        class="{{ !$isFullPayment ? 'btn-primary' : 'btn-secondary' }} flex-1"
                                                        icon="o-banknotes"
                                                        responsive
                                                    />
                                                    <x-mary-button
                                                        label="Full Payment"
                                                        wire:click.prevent="setFullRepaymentAmount"
                                                        class="{{ $isFullPayment ? 'btn-primary' : 'btn-secondary' }} flex-1"
                                                        icon="o-check-circle"
                                                        responsive
                                                    />
                                                </div>
                                            </div>

                                            <!-- Amount Input -->
                                            <div class="mt-4 space-y-4">
                                                <x-mary-input
                                                    type="text"
                                                    label="Amount to Pay"
                                                    wire:model="paymentAmount"
                                                   
                                                    placeholder="Enter amount to pay"
                                                />

                                                <!-- Payment Type Indicator -->
                                                @if($isFullPayment)
                                                    <div class="bg-green-50 text-green-700 p-2 rounded-md text-sm">
                                                        <x-mary-icon name="o-check-circle" class="inline-block mr-1" />
                                                        This will fully settle your loan
                                                    </div>
                                                @else
                                                    <div class="bg-blue-50 text-blue-700 p-2 rounded-md text-sm">
                                                        <x-mary-icon name="o-information-circle" class="inline-block mr-1" />
                                                        This is a partial payment
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <x-slot:actions>
                                            <x-mary-button label="Cancel" wire:click="$set('repaymentModal', false)" />
                                            <x-mary-button
                                                :label="$isFullPayment ? 'Pay Full Amount' : 'Partial Payment'"
                                                class="btn-primary"
                                                type="submit"
                                                icon="o-credit-card"
                                                spinner="makePaymentFromAccount"
                                            />
                                        </x-slot:actions>
                                    </x-mary-form>
                                </div>

                                <div x-show="paymentMethod === 'card'">
                                    <form id="makePaymentForm" class="space-y-6">
                                        <div class="space-y-4">
                                            <div class="relative">
                                                <input
                                                    type="text"
                                                    label="Name"
                                                    name="name"
                                                    id="name"
                                                    placeholder="Enter your name"
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                                    required
                                                />
                                                <label for="name" class="absolute -top-2 left-2 bg-white px-1 text-xs text-gray-600">Name</label>
                                            </div>

                                            <div class="relative">
                                                <input
                                                    type="email"
                                                    label="Email"
                                                    name="email"
                                                    id="email"
                                                    placeholder="Enter your email"
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                                    required
                                                />
                                                <label for="email" class="absolute -top-2 left-2 bg-white px-1 text-xs text-gray-600">Email</label>
                                            </div>

                                            <div class="relative">
                                                <input
                                                    type="tel"
                                                    label="Phone"
                                                    name="phone"
                                                    id="phone"
                                                    placeholder="Enter your phone number"
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                                    required
                                                />
                                                <label for="phone" class="absolute -top-2 left-2 bg-white px-1 text-xs text-gray-600">Phone</label>
                                            </div>

                                            <div class="relative">
                                                <input
                                                    type="number"
                                                    label="Amount"
                                                    name="amount"
                                                    id="amount"
                                                    placeholder="Enter amount"
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                                    required
                                                />
                                                <label for="amount" class="absolute -top-2 left-2 bg-white px-1 text-xs text-gray-600">Amount</label>
                                            </div>
                                        </div>

                                        <div class="flex justify-end space-x-4 mt-6">
                                            <button type="button" @click="$wire.repaymentModal = false" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2">
                                                Cancel
                                            </button>
                                            <button type="submit" class="px-6 py-2 bg-primary text-white rounded-md hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                                                Pay with Card
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <div x-show="paymentMethod === 'mobile_money'">
                                    <x-mary-form wire:submit.prevent="makePaymentWithMobileMoney">
                                        <div class="text-center">
                                            <x-mary-input
                                                type="text"
                                                label="Mobile Money Number"
                                                wire:model="mobileMoneyNumber"
                                                placeholder="Enter mobile money number"
                                            />
                                            <x-mary-select
                                                label="Network"
                                                wire:model="mobileMoneyNetwork"
                                                :options="[['id' => 'MTN','name' => 'MTN'], ['id' => 'AIRTEL','name' => 'AIRTEL']]"
                                                placeholder="Select Network"
                                            />
                                            <x-mary-input
                                                type="text"
                                                label="Amount"
                                                wire:model="paymentAmount"
                                                placeholder="Enter amount to pay"
                                            />
                                        </div>
                                        <x-slot:actions>
                                            <x-mary-button label="Cancel" wire:click="$set('repaymentModal', false)" />
                                            <x-mary-button label="Pay with MM" class="btn-primary" type="submit" />
                                        </x-slot:actions>
                                    </x-mary-form>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <h1 class=" text-pretty text-red-300 dark:text-yellow-200 font-bold text-center">Your Loan is closed or not active!, Contact Support Centre</h1>
                @endif
            </div>
        </div>

          <!-- Add this near the end of your file -->
        <x-mary-modal wire:model="showReceiptModal" title="Transaction Receipt" separator class="z-50">
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
</div>

</div>

