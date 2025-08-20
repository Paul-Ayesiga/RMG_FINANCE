<div class="p-3">
    <!-- HEADER -->
    <x-mary-header title="Loan Products" separator progress-indicator>
        <x-slot:middle>
            <x-mary-input
                label=""
                placeholder="Search loan products..."
                wire:model.live.debounce="search"
                clearable
                icon="o-magnifying-glass"
                class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none"
            />
        </x-slot:middle>
        <x-slot:actions>
            @can('create loan products')
            <x-mary-button
                label="Create Loan Product"
                @click="$wire.addLoanProductModal = true"
                icon="o-plus"
                class="bg-blue-700 mb-3 text-white rounded-md mr-10"
            />
            @endcan
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card title="" subtitle="" shadow separator progress-indicator>
        <!-- Action buttons card -->
        <x-mary-card class="shadow-lg bg-white h-auto mb-10 dark:bg-inherit">
            <!-- Action buttons -->
            <div class="inline-flex flex-wrap items-center mb-2 space-x-2">
                <!-- Bulk Button -->
                <x-mary-button label="Bulk?" icon="o-trash" class="btn-error btn-sm mx-3" wire:click="bulk" />

                <!-- Filter Button with Badge -->
                <x-mary-button label="Filter" icon="o-funnel" class="bg-blue-200 btn-sm mx-2 rounded-md border-none dark:text-white dark:bg-slate-700"
                    wire:click="$set('filtersDrawer', true)" badge="{{$activeFiltersCount}}" />
            </div>

            <!-- Column Visibility Dropdown -->
            <div class="inline-flex flex-wrap items-center mb-2">
                <x-mary-dropdown>
                    <x-slot name="trigger">
                        <x-mary-button label="" icon="o-eye" class="bg-blue-200 btn-sm border-none dark:text-white dark:bg-slate-700" />
                    </x-slot>
                    @foreach(['loans_count', 'name', 'description', 'interest_rate', 'minimum_amount', 'maximum_amount', 'minimum_term', 'maximum_term', 'processing_fee', 'status'] as $column)
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

            <!-- Active Filters -->
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

        <!-- Main Table -->
        <x-mary-table
            :headers="$headers"
            :rows="$loanProducts"
            :sort-by="$sortBy"
            with-pagination
            per-page="perPage"
            :per-page-values="[1,3, 5, 10]"
            wire:model="selected"
            selectable
            striped
            >
            @scope('cell_loans_count', $loanProduct)
                <x-mary-badge :value="$loanProduct->loans_count" class="badge-success" />
            @endscope

            @scope('cell_status', $loanProduct)
                <x-mary-badge :value="$loanProduct->status" class="badge-{{ $loanProduct->status === 'active' ? 'success' : 'error' }}" />
            @endscope

            @scope('actions', $loanProduct)
                <div class="inline-flex">
                    <x-mary-button
                        icon="o-eye"
                        wire:click.stop="OpenPreviewLoanProductModal({{$loanProduct->id}})"
                        class="btn-sm bg-blue-400 dark:text-white"
                    />
                    <x-mary-button
                        icon="o-pencil"
                        @click.stop="$wire.dispatch('edit-loan-product',{loanProductId:{{$loanProduct->id}} })"
                        class="btn-sm bg-yellow-400 dark:text-white"
                    />
                    <x-mary-button
                        icon="o-trash"
                        wire:click.stop="openDeleteLoanProductModal({{$loanProduct->id}})"
                        class="btn-sm bg-red-600 dark:text-white"
                    />
                </div>
            @endscope
        </x-mary-table>
    </x-mary-card>

    <!-- Add Loan Product Modal -->
    <x-mary-modal wire:model="addLoanProductModal">
        <h1 class="text-2xl sm:text-4xl font-bold">Add Loan Product</h1>
        <x-mary-menu-separator />
        <x-mary-form wire:submit="saveLoanProduct">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-wireui-input
                    label="Name"
                    placeholder="Loan Product Name"
                    wire:model="name"
                    class="col-span-1 sm:col-span-2"

                />

                <x-wireui-textarea
                    label="Description"
                    placeholder="Loan Product Description"
                    wire:model="description"
                    class="col-span-1 sm:col-span-2 focus:outline-none focus:border-none"

                />

                <x-wireui-input
                    type="number"
                    step="0.01"
                    label="Interest Rate (%)"
                    placeholder="e.g., 12.5"
                    wire:model="interest_rate"

                />

                <x-wireui-input
                    type="number"
                    step="0.01"
                    label="Minimum Amount"
                    placeholder="e.g., 1000"
                    wire:model="minimum_amount"

                />

                <x-wireui-input
                    type="number"
                    step="0.01"
                    label="Maximum Amount"
                    placeholder="e.g., 50000"
                    wire:model="maximum_amount"

                />

                <x-wireui-input
                    type="number"
                    label="Minimum Term (months)"
                    placeholder="e.g., 6"
                    wire:model="minimum_term"

                />

                <x-wireui-input
                    type="number"
                    label="Maximum Term (months)"
                    placeholder="e.g., 60"
                    wire:model="maximum_term"

                />

                <x-wireui-input
                    type="number"
                    step="0.01"
                    label="Processing Fee (%)"
                    placeholder="e.g., 2.5"
                    wire:model="processing_fee"
                />

                <x-wireui-input
                    type="number"
                    step="0.01"
                    label="Late Payment Fee (%)"
                    placeholder="e.g., 1.5"
                    wire:model="late_payment_fee_percentage"
                />

                <x-wireui-input
                    type="number"
                    step="0.01"
                    label="Early Payment Fee (%)"
                    placeholder="e.g., 1.0"
                    wire:model="early_payment_fee_percentage"
                />

                @php
                    $statusOptions = [
                        [
                            'id' => 'active',
                            'name' => 'Active'
                        ],
                        [
                            'id' => 'inactive',
                            'name' => 'Inactive'
                        ]
                    ];
                @endphp

                <x-wireui-select
                    label="Status"
                    :options="$statusOptions"
                    option-label="name"
                    option-value="id"
                    wire:model="status"
                />

               <x-mary-choices
                    label="Payment Frequencies"
                    wire:model="allowed_frequencies"
                    :options="$frequenciesSearchable"
                    option-label="name"
                    option-value="id"
                    placeholder="Search frequencies..."
                    search-function="searchFrequencies"
                    no-result-text="No payment frequencies found"
                    searchable
                    multiple
                    class="col-span-1 sm:col-span-2 focus:outline-gray-400 border-gray-400"
                />
            </div>

            <div class="mt-4 flex flex-col sm:flex-row gap-2">
                <x-wireui-button
                    label="Add"
                    type="submit"
                    spinner="saveLoanProduct"
                    icon="paper-airplane"
                    class="bg-blue-300 w-full sm:w-auto"
                />
                <x-wireui-button
                    label="Cancel"
                    @click="$wire.addLoanProductModal = false;"
                    class="w-full sm:w-auto bg-gray-500"
                />
            </div>
        </x-mary-form>
    </x-mary-modal>

    <!-- Edit Loan Product Modal -->
     <x-mary-modal wire:model="editLoanProductModal">
        <h1 class="text-2xl sm:text-4xl font-bold">Edit Loan Product</h1>
        <x-mary-menu-separator />
        <x-mary-form wire:submit="updateLoanProduct">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-wireui-input
                    label="Name"
                    placeholder="Loan Product Name"
                    wire:model="name"
                    class="col-span-1 sm:col-span-2"
                />

                <x-wireui-textarea
                    label="Description"
                    placeholder="Loan Product Description"
                    wire:model="description"
                    class="col-span-1 sm:col-span-2"
                />

                <x-wireui-input
                    type="number"
                    step="0.01"
                    label="Interest Rate (%)"
                    placeholder="e.g., 12.5"
                    wire:model="interest_rate"
                />

                <x-wireui-input
                    type="number"
                    step="0.01"
                    label="Minimum Amount"
                    placeholder="e.g., 1000"
                    wire:model="minimum_amount"
                />

                <x-wireui-input
                    type="number"
                    step="0.01"
                    label="Maximum Amount"
                    placeholder="e.g., 50000"
                    wire:model="maximum_amount"
                />

                <x-wireui-input
                    type="number"
                    label="Minimum Term (months)"
                    placeholder="e.g., 6"
                    wire:model="minimum_term"
                />

                <x-wireui-input
                    type="number"
                    label="Maximum Term (months)"
                    placeholder="e.g., 60"
                    wire:model="maximum_term"
                />

                <x-wireui-input
                    type="number"
                    step="0.01"
                    label="Processing Fee (%)"
                    placeholder="e.g., 2.5"
                    wire:model="processing_fee"
                />

                <x-wireui-input
                    type="number"
                    step="0.01"
                    label="Late Payment Fee (%)"
                    placeholder="e.g., 1.5"
                    wire:model="late_payment_fee_percentage"
                />

                <x-wireui-input
                    type="number"
                    step="0.01"
                    label="Early Payment Fee (%)"
                    placeholder="e.g., 1.0"
                    wire:model="early_payment_fee_percentage"
                />

                @php
                    $statusOptions = [
                        [
                            'id' => 'active',
                            'name' => 'Active'
                        ],
                        [
                            'id' => 'inactive',
                            'name' => 'Inactive'
                        ]
                    ];
                @endphp

                <x-wireui-select
                    label="Status"
                    :options="$statusOptions"
                    option-label="name"
                    option-value="id"
                    wire:model="status"
                />

               <x-mary-choices
                    label="Payment Frequencies"
                    wire:model="allowed_frequencies"
                    :options="$frequenciesSearchable"
                    option-label="name"
                    option-value="id"
                    placeholder="Search frequencies..."
                    search-function="searchFrequencies"
                    no-result-text="No payment frequencies found"
                    searchable
                    multiple
                    class="col-span-1 sm:col-span-2"
                />
            </div>

            <div class="mt-4 flex flex-col sm:flex-row gap-2">
                <x-wireui-button
                    label="Update"
                    type="submit"
                    spinner="updateLoanProduct"
                    icon="paper-airplane"
                    class="bg-blue-300 w-full sm:w-auto"
                />
                <x-mary-button
                    label="Cancel"
                    @click="$wire.editLoanProductModal = false;"
                    class="w-full sm:w-auto bg-gray-500"
                />
            </div>
        </x-mary-form>
    </x-mary-modal>

    <!-- Preview Loan Product Modal -->
    <x-mary-modal wire:model="previewLoanProductModal" title="Loan Product Details" separator>
        <div class="p-4 sm:p-6 bg-gray-50 rounded-lg shadow-md">
            <div class="text-center mb-4">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800">{{ $loanProductToPreview->name ?? '' }}</h2>
                <p class="text-sm sm:text-base text-gray-500">{{ $loanProductToPreview->description ?? '' }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                <div class="bg-white p-3 rounded-lg">
                    <span class="font-medium text-gray-600 text-sm">Interest Rate:</span>
                    <span class="block text-gray-800">{{ $loanProductToPreview->interest_rate ?? '' }}%</span>
                </div>
                <div class="bg-white p-3 rounded-lg">
                    <span class="font-medium text-gray-600 text-sm">Amount Range:</span>
                    <span class="block text-gray-800">{{ number_format($loanProductToPreview->minimum_amount ?? 0) }} - {{ number_format($loanProductToPreview->maximum_amount ?? 0) }}</span>
                </div>
                <div class="bg-white p-3 rounded-lg">
                    <span class="font-medium text-gray-600 text-sm">Term Range (months):</span>
                    <span class="block text-gray-800">{{ $loanProductToPreview->minimum_term ?? '' }} - {{ $loanProductToPreview->maximum_term ?? '' }}</span>
                </div>
                <div class="bg-white p-3 rounded-lg">
                    <span class="font-medium text-gray-600 text-sm">Processing Fee:</span>
                    <span class="block text-gray-800">{{ $loanProductToPreview->processing_fee ?? '' }}</span>
                </div>
                <div class="bg-white p-3 rounded-lg">
                    <span class="font-medium text-gray-600 text-sm">Late Payment Fee:</span>
                    <span class="block text-gray-800">{{ $loanProductToPreview->late_payment_fee_percentage ?? '' }}%</span>
                </div>
                <div class="bg-white p-3 rounded-lg">
                    <span class="font-medium text-gray-600 text-sm">Early Payment Fee:</span>
                    <span class="block text-gray-800">{{ $loanProductToPreview->early_payment_fee_percentage ?? '' }}%</span>
                </div>
                <div class="bg-white p-3 rounded-lg">
                    <span class="font-medium text-gray-600 text-sm">Status:</span>
                    <span class="block text-gray-800">{{ ucfirst($loanProductToPreview->status ?? '') }}</span>
                </div>
                <div class="bg-white p-3 rounded-lg">
                    <span class="font-medium text-gray-600 text-sm">Payment Frequencies:</span>
                    <span class="block text-gray-800 text-sm">{{ $loanProductToPreview && json_decode($loanProductToPreview->allowed_frequencies) ? implode(', ', json_decode($loanProductToPreview->allowed_frequencies)) : '' }}</span>
                </div>
                <div class="bg-white p-3 rounded-lg col-span-1 sm:col-span-2">
                    <span class="font-medium text-gray-600 text-sm">Requirements:</span>
                    <span class="block text-gray-800 text-sm">{{ implode(', ', $loanProductToPreview->requirements ?? []) }}</span>
                </div>
            </div>
        </div>

        <x-slot:actions>
            <x-mary-button
                label="Close"
                @click="$wire.previewLoanProductModal = false"
                class="bg-gray-500 rounded-md text-white font-bold border-none w-full sm:w-auto"
            />
        </x-slot:actions>
    </x-mary-modal>

    <!-- Delete Confirmation Modal -->
    <x-mary-modal wire:model="deleteLoanProductModal" title="Confirm Deletion" separator>
        <div class="text-sm sm:text-base">
            Are you sure you want to delete this loan product? This action cannot be undone.
        </div>
        <x-slot:actions>
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 w-full sm:w-auto">
                <x-mary-button
                    label="Cancel"
                    @click="$wire.deleteLoanProductModal = false"
                    class="w-full sm:w-auto"
                />
                <x-mary-button
                    label="Delete"
                    wire:click="confirmDelete({{$loanProductToDelete}})"
                    class="bg-red-600 rounded-md text-white font-bold w-full sm:w-auto"
                    spinner="confirmDelete({{$loanProductToDelete}})"
                />
            </div>
        </x-slot:actions>
    </x-mary-modal>

    <!-- Bulk Delete Modal -->
    <x-mary-modal wire:model="filledbulk" title="Bulk Deletion" separator>
        <div class="text-sm sm:text-base">
            Are you sure you want to delete the selected loan products? This action cannot be undone.
        </div>
        <x-slot:actions>
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 w-full sm:w-auto">
                <x-mary-button
                    label="Cancel"
                    @click="$wire.filledbulk = false"
                    class="w-full sm:w-auto"
                />
                <x-mary-button
                    label="Delete"
                    wire:click="deleteSelected"
                    class="bg-red-600 rounded-md text-white font-bold w-full sm:w-auto"
                    spinner
                />
            </div>
        </x-slot:actions>
    </x-mary-modal>

    <!-- Empty Bulk Selection Modal -->
    <x-mary-modal wire:model="emptybulk" title="No Selection" separator>
        <div class="text-sm sm:text-base">
            Please select at least one loan product to perform bulk deletion.
        </div>
        <x-slot:actions>
            <x-mary-button
                label="Okay"
                @click="$wire.emptybulk = false"
                class="btn btn-accent w-full sm:w-auto"
            />
        </x-slot:actions>
    </x-mary-modal>

    <x-mary-drawer wire:model="filtersDrawer" title="Filters" separator with-close-button close-on-escape class="w-11/12 lg:w-3/4 md:w-1/2">

    </x-mary-drawer>
</div>
