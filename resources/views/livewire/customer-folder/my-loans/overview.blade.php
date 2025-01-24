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

        <x-mary-table :headers="$headers" :rows="$loans" link="/customer/my-loans/{id}/do-something"  :sort-by="$sortBy" with-pagination per-page="perPage"
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
            @scope('cell_amount', $loan, $currency)
                {{ convertCurrency($loan->amount, 'UGX', $currency) }}
            @endscope
            {{-- Special `actions` slot --}}
            @scope('actions', $loan)
                <div class="inline-flex">
                    <x-mary-button icon="o-eye" spinner class="btn-sm bg-blue-400 dark:text-white" link="{{route('visit-loan',$loan->id)}}"/>
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
                    class="bg-gray-100 dark:bg-inherit border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-gray-400 transition-all duration-200 ease-in-out"
                    search-function="searchLoanProduct"
                    >
                    @scope('item', $loanProduct)
                        <x-mary-list-item :item="$loanProduct" sub-value="description">
                            <x-slot:avatar>
                                <x-mary-icon name="o-hashtag" class="bg-orange-100 p-2 w-8 h8 rounded-full" />
                            </x-slot:avatar>
                            <p>{{$loanProduct->name}}</p>
                            <x-slot:actions>
                                {{-- <x-mary-badge :value="$loanProduct->interest_rate" /> --}}
                                <x-wireui-button wire:click="openPreviewLoanProductModal({{$loanProduct->id}})" spinner="openPreviewLoanProductModal({{$loanProduct->id}})" class="btn-sm bg-orange-400" label="view details"/>
                            </x-slot:actions>
                        </x-mary-list-item>
                    @endscope

                    @scope('selection', $loanProduct)
                       <p class="m-0"> {{ $loanProduct->name }}
                       </p>
                    @endscope
                </x-mary-choices>

                <!-- Updated account selection -->
                <x-mary-choices
                    label="Select Disbursement Account"
                    wire:model="accountId"
                    :options="$accounts"
                    single
                    searchable
                    class="bg-gray-100 dark:bg-inherit border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-gray-400 transition-all duration-200 ease-in-out "
                    search-function="searchLoanDisbursementAccount"
                    >
                    @scope('item', $account, $currency)
                        <x-mary-list-item :item="$account" sub-value="account_number">
                            <x-slot:avatar>
                                <x-mary-icon name="o-credit-card" class="bg-orange-100 p-2 w-8 h8 rounded-full" />
                            </x-slot:avatar>
                            <x-slot:actions>
                                <x-mary-badge :value="number_format(convertCurrency($account->balance,'UGX', $currency), 2)" />
                            </x-slot:actions>
                        </x-mary-list-item>
                    @endscope

                    @scope('selection', $account)
                       <p class="m-0">{{ $account->account_number }}</p>
                    @endscope
                </x-mary-choices>

                <!-- Amount and Term inputs -->
                @if($loanProductId)
                    <x-mary-input
                        type="number"
                        label="Loan Amount"
                        wire:model="amount"
                        step="0.01"
                        hint="Amount must be between {{ number_format(convertCurrency($minAmount, 'UGX', $currency), 2) }} and {{ number_format(convertCurrency($maxAmount, 'UGX', $currency), 2) }}"
                        class="bg-gray-100 dark:bg-inherit border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-gray-400 transition-all duration-200 ease-in-out "

                    />

                    <x-mary-input
                        type="number"
                        label="Term (months)"
                        wire:model="term"
                        hint="Term must be between {{ $minTerm }} and {{ $maxTerm }} months"
                        class="bg-gray-100 dark:bg-inherit border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-gray-400 transition-all duration-200 ease-in-out "

                    />

                    <!-- Update the payment frequency select -->
                    <x-mary-select
                        label="Payment Frequency"
                        wire:model="paymentFrequency"
                        :options="$allowedFrequencies"
                        option-label="name"
                        option-value="id"
                        placeholder="Select payment frequency"
                        class="bg-gray-100 dark:bg-inherit border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-gray-400 transition-all duration-200 ease-in-out "

                    />
                    <x-mary-file
                        label="Required Documents"
                        wire:model="documents"
                        multiple
                        help="Upload all required documents (PDF, Images)"
                        class="bg-gray-100 dark:bg-inherit border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-gray-400 "
                    />
                @endif
            </div>

            <x-slot:actions>
                <x-wireui-button label="Cancel" wire:click="$set('addLoanModal', false)" class="bg-gray-400"/>
                <x-wireui-button label="Apply" class="bg-blue-400 text-white" type="submit" spinner="applyForLoan" :disabled="!$loanProductId"/>
            </x-slot:actions>
        </x-mary-form>
    </x-mary-modal>
  @endcan

    <x-mary-modal wire:model="loanProductDetails">
        <div class="p-4 sm:p-6 bg-gray-50 rounded-lg shadow-md">
            <!-- Account Type Header -->
            <div class="text-center mb-4">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800">{{ $this->loanProductToPreview->name ?? 'NaN' }}</h2>
            </div>
        </div>
    </x-mary-modal>

   <x-mary-drawer wire:model="filtersDrawer" title="Filters" separator with-close-button close-on-escape class="w-11/12 lg:w-3/4 md:w-1/2">

    </x-mary-drawer>
</div>

