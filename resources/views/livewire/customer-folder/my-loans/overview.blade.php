<div>
    <x-mary-header title="My Loans" separator progress-indicator>
            <x-slot:middle>
                <x-mary-input
                    label=""
                    placeholder="Search loans ..."
                    wire:model.live.debounce="search"
                    clearable
                    icon="o-magnifying-glass"
                    class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none"
                />
            </x-slot:middle>
              
                <x-slot:actions>
                  
                        <x-mary-button 
                            label="New Loan" 
                            wire:click="openLoanModal" 
                            icon="o-plus" 
                            class="bg-blue-700 mb-3 text-white rounded-md mr-10" 
                        />
                   
                </x-slot:actions>
               
    </x-mary-header>

    <!-- Add this section to show pending loans count -->
    @if($this->getPendingLoansCount() > 0)
        <div class="mb-4">
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                <p class="font-bold">Pending Applications</p>
                <p>You currently have {{ $this->getPendingLoansCount() }} pending loan application(s). Maximum allowed is 2.</p>
            </div>
        </div>
    @endif

      {{-- loans overview table --}}
    <x-mary-card title="" subtitle="" shadow separator progress-indicator>
        {{-- datatable options like xls, bulk delete --}}
        <x-mary-card class="shadow-lg bg-white h-auto mb-10 dark:bg-inherit">
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
                    @foreach(['loanProduct.name', 'amount', 'status', 'disbursement_date', 'next_payment_date'] as $column)
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

        <x-mary-table :headers="$headers" :rows="$loans" :sort-by="$sortBy" with-pagination per-page="perPage"
            :per-page-values="[1,3, 5, 10]" wire:model="selected" selectable striped>
            @scope('cell_status', $loan)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    @switch($loan->status)
                        @case('pending')
                            bg-yellow-100 text-yellow-800
                            @break
                        @case('approved')
                            bg-green-100 text-green-800
                            @break
                        @case('rejected')
                            bg-red-100 text-red-800
                            @break
                        @default
                            bg-gray-100 text-gray-800
                    @endswitch
                ">
                    {{ ucfirst($loan->status) }}
                </span>
            @endscope
            {{-- Special `actions` slot --}}
            @scope('actions', $loan)
                <div class="inline-flex">
                    <x-mary-button icon="o-eye" wire:click="viewLoan({{$loan->id}})" spinner class="btn-sm bg-blue-400 dark:text-white"/>
                    @if($loan->status === 'active')
                        <x-mary-button icon="o-currency-dollar" wire:click="openRepaymentModal({{$loan->id}})" spinner class="btn-sm bg-green-600 dark:text-white"  tooltip="make repayment"/>
                    @endif
                </div>
            @endscope

            <x-slot:empty>
                <x-mary-icon name="o-cube" label="No loans found." />
            </x-slot:empty>
        </x-mary-table>

    </x-mary-card>
    {{-- end of loans table --}}

  @can('apply for loans')
    <!-- Apply for Loan Modal -->
    <x-mary-modal wire:model="addLoanModal" title="Apply for Loan" separator>
        <x-mary-form wire:submit="applyForLoan">
            <div class="space-y-4">
                <!-- Loan product selection -->
                <x-mary-choices 
                    label="Loan Products" 
                    wire:model.live="loanProductId" 
                    :options="$loanProducts" 
                    single 
                    searchable 
                    class="border-b-2 border-white shadow-lg focus:border-none focus:outline-dashed" 
                    search-function="searchLoanProduct"
                >
                    @scope('item', $loanProduct)
                        <x-mary-list-item :item="$loanProduct" sub-value="description">
                            <x-slot:avatar>
                                <x-mary-icon name="o-hashtag" class="bg-orange-100 p-2 w-8 h8 rounded-full" />
                            </x-slot:avatar>
                            <p>{{$loanProduct->name}}</p>
                            <x-slot:actions>
                                <x-mary-badge :value="$loanProduct->interest_rate" />
                            </x-slot:actions>
                        </x-mary-list-item>
                    @endscope

                    @scope('selection', $loanProduct)
                        {{ $loanProduct->name }}
                    @endscope
                </x-mary-choices>

                <!-- Updated account selection -->
                <x-mary-choices 
                    label="Select Disbursement Account" 
                    wire:model="accountId" 
                    :options="$accounts" 
                    single 
                    searchable 
                    class="border-b-2 border-white shadow-lg focus:border-none focus:outline-dashed" 
                    search-function="searchLoanDisbursementAccount"
                >
                    @scope('item', $account)
                        <x-mary-list-item :item="$account" sub-value="account_number">
                            <x-slot:avatar>
                                <x-mary-icon name="o-credit-card" class="bg-orange-100 p-2 w-8 h8 rounded-full" />
                            </x-slot:avatar>
                            <x-slot:actions>
                                <x-mary-badge :value="number_format($account->balance, 2)" />
                            </x-slot:actions>
                        </x-mary-list-item>
                    @endscope

                    @scope('selection', $account)
                        {{ $account->account_number }}
                    @endscope
                </x-mary-choices>

                <!-- Amount and Term inputs -->
                @if($loanProductId)
                    <x-mary-input
                        type="number"
                        label="Loan Amount"
                        wire:model="amount"
                        step="0.01"
                        hint="Amount must be between {{ number_format($minAmount, 2) }} and {{ number_format($maxAmount, 2) }}"
                    />

                    <x-mary-input
                        type="number"
                        label="Term (months)"
                        wire:model="term"
                        hint="Term must be between {{ $minTerm }} and {{ $maxTerm }} months"
                    />

                    <!-- Update the payment frequency select -->
                    <x-mary-select
                        label="Payment Frequency"
                        wire:model="paymentFrequency"
                        :options="$allowedFrequencies"
                        option-label="name"
                        option-value="id"
                        placeholder="Select payment frequency"
                    />
                    <x-mary-file
                        label="Required Documents"
                        wire:model="documents"
                        multiple
                        help="Upload all required documents (PDF, Images)"
                    />
                @endif

               
            </div>

            <x-slot:actions>
                <x-mary-button label="Cancel" wire:click="$set('addLoanModal', false)" />
                <x-mary-button label="Apply" class="btn-primary" type="submit" spinner="applyForLoan" :disabled="!$loanProductId"/>
            </x-slot:actions>
        </x-mary-form>
    </x-mary-modal>
  @endcan
    <!-- View Loan Details Modal -->
    <x-mary-modal wire:model="viewLoanModal" title="Loan Details" separator>
        @if($selectedLoan)
            <div class="space-y-6">
                <!-- Loan Summary -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h3 class="font-bold mb-2">Loan Information</h3>
                        <div class="space-y-2">
                            <p><span class="font-semibold">Product:</span> {{ $selectedLoan->loanProduct->name }}</p>
                            <p><span class="font-semibold">Amount:</span> {{ number_format($selectedLoan->amount, 2) }}</p>
                            <p><span class="font-semibold">Interest Rate:</span> {{ $selectedLoan->interest_rate }}%</p>
                            <p><span class="font-semibold">Term:</span> {{ $selectedLoan->term }} months</p>
                            <p><span class="font-semibold">Status:</span> {{ $selectedLoan->status }}</p>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-bold mb-2">Payment Summary</h3>
                        <div class="space-y-2">
                            <p><span class="font-semibold">Total Payable:</span> {{ number_format($selectedLoan->total_payable, 2) }}</p>
                            <p><span class="font-semibold">Total Interest:</span> {{ number_format($selectedLoan->total_interest, 2) }}</p>
                            <p><span class="font-semibold">Processing Fee:</span> {{ number_format($selectedLoan->processing_fee, 2) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Payment Schedule -->
                <div>
                    <h3 class="font-bold mb-2">Payment Schedule</h3>
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
                                @foreach($selectedLoan->schedules as $schedule)
                                    <tr>
                                        <td>{{ $schedule->due_date->format('Y-m-d') }}</td>
                                        <td>{{ number_format($schedule->total_amount, 2) }}</td>
                                        <td>{{ number_format($schedule->paid_amount, 2) }}</td>
                                        <td>{{ number_format($schedule->remaining_amount, 2) }}</td>
                                        <td>
                                        @if($schedule->status)
                                            <div class="badge gap-2 {{ match($schedule->status) {
                                                'pending' => 'badge-warning',
                                                'approved' => 'badge-success',
                                                'active' => 'badge-info',
                                                'rejected' => 'badge-error',
                                                default => 'badge-default'
                                            } }}">
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    class="inline-block h-4 w-4 stroke-current">
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                                {{ ucfirst($schedule->status) }}
                                            </div>
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
            </div>
        @endif

        <x-slot:actions>
            <x-mary-button label="Close" wire:click="$set('viewLoanModal', false)" />
        </x-slot:actions>
    </x-mary-modal>

    <!-- Repayment Modal -->
    <x-mary-modal wire:model="repaymentModal" title="Make Payment" separator>
        @if($selectedLoan)
            <div x-data="{ paymentMethod: 'default' }">
                <div class="p-4">
                    <div class="space-y-4">
                        <div class="text-sm">
                            <p><span class="font-semibold">Next Payment Due:</span> 
                                {{ $selectedLoan->schedules->where('status', '!=', 'paid')->first()?->due_date->format('Y-m-d') }}
                            </p>
                            <p><span class="font-semibold">Amount Due:</span> 
                                {{ number_format($selectedLoan->schedules->where('status', '!=', 'paid')->first()?->remaining_amount ?? 0, 2) }}
                            </p>
                        </div>

                        <div class="mb-4 text-center">
                            <label class="block text-sm font-medium text-gray-700">Payment Method</label>
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
                                            {{ number_format($selectedLoan->schedules->where('status', '!=', 'paid')->sum('remaining_amount'), 2) }}
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
                                        @scope('item', $account)
                                            <x-mary-list-item :item="$account" sub-value="account_number">
                                                <x-slot:avatar>
                                                    <x-mary-icon name="o-credit-card" class="bg-orange-100 p-2 w-8 h8 rounded-full" />
                                                </x-slot:avatar>
                                                <p>{{$account->account_number}}</p>
                                                <x-slot:actions>
                                                    <x-mary-badge :value="number_format($account->balance, 2)" />
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
                                            />
                                            <x-mary-button 
                                                label="Full Payment" 
                                                wire:click.prevent="setFullRepaymentAmount"
                                                class="{{ $isFullPayment ? 'btn-primary' : 'btn-secondary' }} flex-1"
                                                icon="o-check-circle"
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
                                        :label="$isFullPayment ? 'Pay Full Amount' : 'Make Partial Payment'" 
                                        class="btn-primary" 
                                        type="submit" 
                                        icon="o-credit-card"
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
                                    <x-mary-button label="Pay with Mobile Money" class="btn-primary" type="submit" />
                                </x-slot:actions>
                            </x-mary-form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </x-mary-modal>

    <!-- Payment Receipt Modal -->
    <x-mary-modal wire:model="showReceiptModal" title="Payment Receipt" separator>
        @if($receiptData)
            <div class="space-y-4">
                <div class="text-center">
                    <h3 class="text-lg font-bold">Payment Confirmation</h3>
                    <p class="text-gray-600">Thank you for your payment</p>
                </div>

                <div class="space-y-2">
                    <p><span class="font-semibold">Date:</span> {{ $receiptData['date'] }}</p>
                    <p><span class="font-semibold">Loan ID:</span> {{ $receiptData['loan_id'] }}</p>
                    <p><span class="font-semibold">Amount Paid:</span> {{ number_format($receiptData['amount'], 2) }}</p>
                    @if($receiptData['early_payment_fee_percentage'] > 0)
                        <p><span class="font-semibold">Early Payment Fee:</span> {{ number_format($receiptData['early_payment_fee'], 2) }}</p>
                    @endif
                    @if($receiptData['late_payment_fee_percentage'] > 0)
                        <p><span class="font-semibold">Late Payment Fee:</span> {{ number_format($receiptData['late_payment_fee'], 2) }}</p>
                    @endif
                    <p><span class="font-semibold">Total Amount:</span> {{ number_format($receiptData['total_amount'], 2) }}</p>
                    <p><span class="font-semibold">Reference:</span> {{ $receiptData['reference'] }}</p>
                    <p><span class="font-semibold">Remaining Balance:</span> {{ number_format($receiptData['remaining_balance'], 2) }}</p>
                </div>
            </div>

            <x-slot:actions>
                <x-mary-button label="Print" icon="o-printer" @click="window.print()" />
                <x-mary-button label="Close" wire:click="$set('showReceiptModal', false)" />
            </x-slot:actions>
        @endif
    </x-mary-modal>
</div>


@script
<script src="{{ asset('js/jquery.js') }}"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://checkout.flutterwave.com/v3.js"></script>
<script>
  $(document).ready(function() {
    $("#makePaymentForm").submit(function(e) {
      e.preventDefault();

      alert('well');
      
      // Get form data
      const formData = {
        name: $('#name').val(),
        email: $('#email').val(),
        phone: $('#phone').val(),
        amount: $('#amount').val(),
        // Add CSRF token for Laravel
        _token: '{{ csrf_token() }}'
      };

      // Show loading state
      const submitBtn = $(this).find('button[type="submit"]');
      const originalBtnText = submitBtn.text();
      submitBtn.prop('disabled', true).text('Processing...');

      $.ajax({
        type: 'POST',
        url: '', // Make sure to define this route
        data: formData,
        success: function(response) {
          if (response.success) {
            // Initialize Flutterwave payment
            FlutterwaveCheckout({
              public_key: response.public_key,
              tx_ref: response.tx_ref,
              amount: response.amount,
              currency: response.currency,
              payment_options: "card",
              customer: {
                email: response.email,
                phone_number: response.phone,
                name: response.name,
              },
              customizations: {
                title: "Loan Repayment",
                description: "Loan repayment via card",
                logo: response.logo_url,
              },
              callback: function(paymentResponse) {
                // Verify payment on backend
                $.post('', {
                  transaction_id: paymentResponse.transaction_id,
                  tx_ref: response.tx_ref,
                  _token: '{{ csrf_token() }}'
                })
                .done(function(verificationResponse) {
                  if (verificationResponse.success) {
                    // Show success message
                    alert('Payment successful!');
                    // Close the payment modal
                    window.livewire.emit('closeRepaymentModal');
                  } else {
                    alert('Payment verification failed. Please contact support.');
                  }
                })
                .fail(function() {
                  alert('Payment verification failed. Please contact support.');
                });
              },
              onclose: function() {
                submitBtn.prop('disabled', false).text(originalBtnText);
              }
            });
          } else {
            alert(response.message || 'Something went wrong. Please try again.');
            submitBtn.prop('disabled', false).text(originalBtnText);
          }
        },
        error: function(xhr) {
          alert('Error processing payment. Please try again.');
          submitBtn.prop('disabled', false).text(originalBtnText);
        }
      });
    });
  });
</script>
    <script>
    function makePayment() {
        FlutterwaveCheckout({
        public_key: "FLWPUBK_TEST-02b9b5fc6406bd4a41c3ff141cc45e93-X",
        tx_ref: "txref-DI0NzMx13",
        amount: 2500,
        currency: "NGN",
        payment_options: "card, banktransfer, ussd",
        meta: {
            source: "docs-inline-test",
            consumer_mac: "92a3-912ba-1192a",
        },
        customer: {
            email: "test@mailinator.com",
            phone_number: "08100000000",
            name: "Ayomide Jimi-Oni",
        },
        customizations: {
            title: "Flutterwave Developers",
            description: "Test Payment",
            logo: "https://checkout.flutterwave.com/assets/img/rave-logo.png",
        },
        callback: function (data){
            console.log("payment callback:", data);
        },
        onclose: function() {
            console.log("Payment cancelled!");
        }
        });
    }
</script>
@endscript
