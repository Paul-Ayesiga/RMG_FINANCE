<div class="p-3">
    <x-mary-header title="Loan Management" separator progress-indicator>
        <x-slot:middle>
            <x-mary-input
                label=""
                placeholder="Search by loans..."
                wire:model.live.debounce="search"
                clearable
                icon="o-magnifying-glass"
                class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none"
            />
        </x-slot:middle>
    </x-mary-header>

    {{-- loans overview table --}}
    <x-mary-card title="" subtitle="" shadow separator progress-indicator>
        {{-- datatable options --}}
        <x-mary-card class="shadow-lg bg-white h-auto mb-10 dark:bg-inherit">
            <!-- Action buttons -->
            <div class="inline-flex flex-wrap items-center mb-2 space-x-2">
                <!-- Filter Button with Badge -->
                <x-mary-button label="Filter" icon="o-funnel" class="bg-blue-200 btn-sm mx-2 rounded-md border-none dark:text-white dark:bg-slate-700"
                    wire:click="$set('filtersDrawer', true)" badge="{{$activeFiltersCount}}" />

                <!-- Status Filter Dropdown -->
                <x-mary-dropdown>
                    <x-slot name="trigger">
                        <x-mary-button
                            label="Status"
                            icon="o-adjustments-vertical"
                            class="bg-blue-200 btn-sm border-none dark:text-white dark:bg-slate-700"
                            badge="{{ !empty($statusFilter) ? '!' : '' }}"
                        />
                    </x-slot>
                    <!-- Add All/Reset option -->
                    <x-mary-menu-item wire:click="setStatusFilter('')">
                        <x-mary-button
                            label="All"
                            class="btn-sm rounded-md mx-1 {{ empty($statusFilter) ? 'bg-blue-500 text-white' : '' }}"
                        />
                    </x-mary-menu-item>
                    @foreach(['pending', 'approved', 'active', 'rejected', 'closed'] as $status)
                        <x-mary-menu-item wire:click="setStatusFilter('{{ $status }}')">
                            <x-mary-button
                                label="{{ ucfirst($status) }}"
                                class="btn-sm rounded-md mx-1 {{ $statusFilter === $status ? 'bg-blue-500 text-white' : '' }}"
                            />
                        </x-mary-menu-item>
                    @endforeach
                </x-mary-dropdown>

                <!-- Date Range Picker -->
                <div class="inline-flex items-center space-x-2">
                    <x-mary-dropdown>
                        <x-slot name="trigger">
                            <x-mary-button
                                label="Date Range"
                                icon="o-calendar"
                                class="bg-blue-200 btn-sm border-none dark:text-white dark:bg-slate-700"
                                badge="{{ !empty($dateRange['from']) || !empty($dateRange['to']) ? '!' : '' }}"
                            />
                        </x-slot>
                        <div class="p-4 space-y-3">
                            <div class="space-y-2">
                                <label class="text-sm font-medium">From Date</label>
                                <x-mary-menu-item @click.stop="">
                                <x-mary-input
                                    type="date"
                                    wire:model="dateRange.from"
                                    class="btn-sm w-full"
                                />
                                </x-mary-menu-item>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium">To Date</label>
                                <x-mary-menu-item @click.stop="">
                                <x-mary-input
                                    type="date"
                                    wire:model="dateRange.to"
                                    class="btn-sm w-full"
                                />
                                </x-mary-menu-item>
                            </div>
                            <div class="flex justify-end space-x-2 mt-4">
                                <x-mary-button
                                    label="Clear"
                                    wire:click="clearDateRange"
                                    class="btn-sm"
                                />
                                <x-mary-button
                                    label="Apply"
                                    wire:click="applyDateFilter"
                                    class="btn-sm bg-blue-500 text-white"
                                />
                            </div>
                        </div>
                    </x-mary-dropdown>
                </div>
            </div>

            {{-- export buttons --}}
            <div class="inline-flex flex-wrap items-center mb-2">
                <x-mary-dropdown>
                    <x-slot name="trigger">
                        <x-mary-button label="Export" icon="o-arrow-down-tray" class="bg-blue-200 btn-sm border-none dark:text-white dark:bg-slate-700" />
                    </x-slot>
                    <x-mary-button label="PDF" class="btn-sm rounded-md mx-1 dark:bg-inherit" wire:click="exportToPDF" />
                    <x-mary-button label="XLS" class="btn-sm rounded-md mx-2 dark:bg-inherit" wire:click="exportToExcel" />
                </x-mary-dropdown>
            </div>

            <!-- Column Visibility Dropdown -->
            <div class="inline-flex flex-wrap items-center mb-2">
                <x-mary-dropdown>
                    <x-slot name="trigger">
                        <x-mary-button label="" icon="o-eye" class="bg-blue-200 btn-sm border-none dark:text-white dark:bg-slate-700" />
                    </x-slot>
                    @foreach($columns as $column => $visible)
                        <x-mary-menu-item wire:click="toggleColumnVisibility('{{ $column }}')">
                            @if($visible)
                                <x-mary-icon name="o-eye" class="text-green-500" />
                            @else
                                <x-mary-icon name="o-eye-slash" class="text-gray-500" />
                            @endif
                            <span class="ml-2">{{ ucfirst(str_replace(['_', '.'], ' ', $column)) }}</span>
                        </x-mary-menu-item>
                    @endforeach
                </x-mary-dropdown>
            </div>

            {{-- active filters --}}
            <div class="mb-4 mt-5">
                @if(count($activeFilters) > 0)
                    <x-mary-button
                        wire:click="clearAllFilters"
                        label="Clear All Filters"
                        class="mt-2 btn-danger btn-sm"
                    />
                @endif
                <div class="flex flex-wrap gap-2">
                    @foreach($activeFilters as $filter => $value)
                        <span class="inline-flex items-center px-3 py-1 text-sm font-medium text-white bg-blue-500 rounded-full mt-3">
                            {{ $value }}
                            <button
                                type="button"
                                wire:click="removeFilter('{{ $filter }}')"
                                class="ml-2 text-white hover:text-gray-300"
                            >
                                &times;
                            </button>
                        </span>
                    @endforeach
                </div>
            </div>
        </x-mary-card>

        <!-- Loans Table -->
        <x-mary-table
            :headers="$headers"
            :rows="$loans"
            :sort-by="$sortBy"
            with-pagination
            :per-page="$perPage"
            :per-page-values="[10, 25, 50, 100]"
            wire:model="selected"
            selectable
            striped
            >
            @scope('cell_status', $loan)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    @switch($loan->status)
                        @case('pending')
                            bg-yellow-100 text-yellow-800
                            @break
                        @case('approved')
                            bg-green-100 text-green-800
                            @break
                        @case('active')
                            bg-blue-100 text-blue-800
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

            @scope('cell_amount', $loan)
                {{ number_format($loan->amount, 2) }}
            @endscope

            @scope('cell_created_at', $loan)
                {{ $loan->created_at->format('Y-m-d H:i') }}
            @endscope

            @scope('cell_disbursement_date', $loan)
                {{ $loan->disbursement_date?->format('Y-m-d') ?? '-' }}
            @endscope

            @scope('actions', $loan)
                <div class="inline-flex gap-2">
                    <x-mary-button
                        icon="o-eye"
                        wire:click="viewLoan({{ $loan->id }})"
                        class="btn-sm bg-blue-400 text-white"
                        title="View Details"
                        tooltip="View Details"
                    />

                    @can('approve loans')
                        @if($loan->status === 'pending')
                            <x-mary-button
                                icon="o-check"
                                wire:click="openApprovalModal({{ $loan->id }})"
                                class="btn-sm bg-green-600 text-white"
                                title="Approve"
                                tooltip="Approve"
                            />
                            <x-mary-button
                                icon="o-x-mark"
                                wire:click="openRejectModal({{ $loan->id }})"
                                class="btn-sm bg-red-600 text-white"
                                title="Reject"
                                tooltip="Reject"
                            />
                        @endif
                    @endcan

                    @can('disburse loans')
                        @if($loan->status === 'approved')
                            <x-mary-button
                                icon="o-banknotes"
                                wire:click="openDisbursementModal({{ $loan->id }})"
                                class="btn-sm bg-purple-600 text-white"
                                title="Disburse"
                                tooltip="disburse"
                            />
                        @endif
                    @endcan
                </div>
            @endscope
        </x-mary-table>
    </x-mary-card>

    <!-- View Loan Modal -->
    <x-mary-modal wire:model="viewLoanModal" title="Loan Details">
        @if($selectedLoan)
            <div class="space-y-6">
                <!-- Customer & Loan Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-bold mb-3">Customer Information</h3>
                        <div class="space-y-2">
                            <p><span class="font-semibold">Name:</span> {{ $selectedLoan->customer->user->name }}</p>
                            <p><span class="font-semibold">Email:</span> {{ $selectedLoan->customer->user->email }}</p>
                            <p><span class="font-semibold">Phone:</span> {{ $selectedLoan->customer->phone_number }}</p>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-bold mb-3">Loan Information</h3>
                        <div class="space-y-2">
                            <p><span class="font-semibold">Product:</span> {{ $selectedLoan->loanProduct->name }}</p>
                            <p><span class="font-semibold">Amount:</span> {{ number_format($selectedLoan->amount, 2) }}</p>
                            <p><span class="font-semibold">Interest Rate:</span> {{ $selectedLoan->interest_rate }}%</p>
                            <p><span class="font-semibold">Term:</span> {{ $selectedLoan->term }} months</p>
                            <p><span class="font-semibold">Status:</span>
                                @if($selectedLoan->status)
                                    <div class="badge gap-2 {{ match($selectedLoan->status) {
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
                                        {{ ucfirst($selectedLoan->status) }}
                                    </div>
                                @else
                                    <span class="text-gray-500">Not available</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Documents -->
                @if($selectedLoan->documents->isNotEmpty())
                    <div>
                        <h3 class="font-bold mb-3">Documents</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($selectedLoan->documents as $document)
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="truncate">{{ $document->file_name }}</span>
                                    <x-mary-button
                                        icon="o-arrow-down-tray"
                                        class="btn-sm ml-2 flex-shrink-0"
                                        wire:click="downloadDocument({{ $document->id }})"
                                        spinner="downloadDocument"
                                    />
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Payment Schedule -->
                @if($selectedLoan->schedules->isNotEmpty())
                    <div>
                        <h3 class="font-bold mb-3">Payment Schedule</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                        <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Principal</th>
                                        <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Interest</th>
                                        <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($selectedLoan->schedules as $schedule)
                                        <tr>
                                            <td class="px-4 py-3 whitespace-nowrap">{{ $schedule->due_date->format('Y-m-d') }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap">{{ number_format($schedule->principal_amount, 2) }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap">{{ number_format($schedule->interest_amount, 2) }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap">{{ number_format($schedule->total_amount, 2) }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="badge {{ match($schedule->status) {
                                                    'pending' => 'badge-warning',
                                                    'paid' => 'badge-success',
                                                    'partial' => 'badge-info',
                                                    'overdue' => 'badge-error',
                                                    default => 'badge-default'
                                                } }}">
                                                    {{ ucfirst($schedule->status) }}
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </x-mary-modal>

    <!-- Approval Modal -->
    <x-mary-modal wire:model="approveLoanModal" title="Approve Loan">
        @if($selectedLoan)
            <div class="space-y-4">
                <p class="text-lg">Are you sure you want to approve this loan?</p>
                <div class="bg-gray-50 p-4 rounded">
                    <p><span class="font-semibold">Customer:</span> {{ $selectedLoan->customer->user->name }}</p>
                    <p><span class="font-semibold">Amount:</span> {{ number_format($selectedLoan->amount, 2) }}</p>
                    <p><span class="font-semibold">Product:</span> {{ $selectedLoan->loanProduct->name }}</p>
                </div>
            </div>

            <x-slot:actions>
                <x-mary-button label="Cancel" wire:click="$set('approveLoanModal', false)" />
                <x-mary-button label="Approve" class="btn-success" wire:click="approveLoan" spinner="approveLoan" />
            </x-slot:actions>
        @endif
    </x-mary-modal>

    <!-- Disbursement Modal -->
    <x-mary-modal wire:model="disburseLoanModal" title="Disburse Loan">
        @if($selectedLoan)
            <div class="space-y-4">
                <div class="bg-gray-50 p-4 rounded">
                    <p><span class="font-semibold">Customer:</span> {{ $selectedLoan->customer->user->name }}</p>
                    <p><span class="font-semibold">Amount:</span> {{ number_format($selectedLoan->amount, 2) }}</p>
                    <p><span class="font-semibold">Disbursement Account:</span> {{ $selectedLoan->account->account_number }}</p>
                </div>

                <x-mary-textarea
                    label="Disbursement Note"
                    wire:model="disbursementNote"
                    placeholder="Enter any notes about this disbursement..."
                />
            </div>

            <x-slot:actions>
                <x-mary-button label="Cancel" wire:click="$set('disburseLoanModal', false)" />
                <x-mary-button label="Disburse" class="btn-primary" wire:click="disburseLoan" spinner="disburseLoan" />
            </x-slot:actions>
        @endif
    </x-mary-modal>

    <!-- Reject Modal -->
    <x-mary-modal wire:model="rejectLoanModal" title="Reject Loan">
        @if($selectedLoan)
            <div class="space-y-4">
                <div class="bg-gray-50 p-4 rounded">
                    <p><span class="font-semibold">Customer:</span> {{ $selectedLoan->customer->name }}</p>
                    <p><span class="font-semibold">Amount:</span> {{ number_format($selectedLoan->amount, 2) }}</p>
                    <p><span class="font-semibold">Product:</span> {{ $selectedLoan->loanProduct->name }}</p>
                </div>

                <x-mary-textarea
                    label="Rejection Reason"
                    wire:model="rejectionReason"
                    placeholder="Please provide a reason for rejecting this loan..."
                    required
                />
            </div>

            <x-slot:actions>
                <x-mary-button label="Cancel" wire:click="$set('rejectLoanModal', false)" />
                <x-mary-button label="Reject" class="btn-error" wire:click="rejectLoan" spinner="rejectLoan" />
            </x-slot:actions>
        @endif
    </x-mary-modal>

    <x-mary-drawer wire:model="filtersDrawer" title="Filters" separator with-close-button close-on-escape class="w-11/12 lg:w-3/4 md:w-1/2">

    </x-mary-drawer>
</div>
