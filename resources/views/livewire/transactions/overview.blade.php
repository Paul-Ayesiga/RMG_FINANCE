<div>
    <!-- Breadcrumbs -->
    <div class="text-sm breadcrumbs">
        <ul>
            <li><a>Home</a></li>
            <li><a>Transactions</a></li>
        </ul>
    </div>

    <!-- Main Content Container -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 space-y-6">
        <!-- Search Row -->
        <div class="flex justify-center">
            <div class="w-full max-w-2xl">
                <x-mary-input 
                    icon="o-magnifying-glass" 
                    placeholder="Search transactions..." 
                    wire:model.live.debounce.300ms="search"
                    class="w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 shadow-sm focus:border-primary-500 focus:ring-0 focus:outline-none transition-all duration-200 ease-in-out"
                />
            </div>
        </div>

        <!-- Filters Grid -->
        <div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Type Filter -->
                <div>
                    @php
                        $types = [
                            ['id' => '', 'name' => 'All Types'],
                            ['id' => 'deposit', 'name' => 'Deposit'],
                            ['id' => 'withdrawal', 'name' => 'Withdrawal'],
                            ['id' => 'transfer', 'name' => 'Transfer']
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

        <!-- Table Controls -->
        <div class="flex flex-col sm:flex-row justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-4 space-y-4 sm:space-y-0">
            <div class="flex items-center space-x-4">
                @php
                    $perPageOptions = [
                        ['id' => 10, 'name' => '10'],
                        ['id' => 25, 'name' => '25'],
                        ['id' => 50, 'name' => '50'],
                        ['id' => 100, 'name' => '100']
                    ];
                @endphp
                
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Show</span>
                    <x-mary-select
                        wire:model.live="perPage"
                        :options="$perPageOptions"
                        class="w-20 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:border-primary-500 focus:ring-0 focus:outline-none transition-all duration-200 ease-in-out"
                    />
                    <span class="text-sm text-gray-600 dark:text-gray-400">entries</span>
                </div>
            </div>

            <!-- Export Button -->
            <x-mary-button 
                icon="o-arrow-down-tray"
                label="Export" 
                class="btn-primary w-full sm:w-auto"
                wire:click="export"
            />
        </div>

        <!-- Table Section -->
        <div class="overflow-x-auto">
            <table class="w-full min-w-[800px] text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
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
                    @forelse($transactions as $transaction)
                        <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-3">{{ $transaction->reference_number }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 font-semibold text-sm rounded-sm text-white
                                    {{ $transaction->type === 'deposit' ? 'bg-green-500' : 
                                      ($transaction->type === 'withdrawal' ? 'bg-yellow-500' : 'bg-blue-500') }}">
                                    {{ ucfirst($transaction->type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">${{ number_format($transaction->amount, 2) }}</td>
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
                                        wire:click="copyToClipboard('{{ $transaction->reference }}')"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No transactions found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $transactions->links() }}
        </div>
    </div>

    <!-- Transaction View Modal -->
    <x-mary-modal wire:model="viewModal">
        @if($selectedTransaction)
            <div class="p-4">
                <h2 class="text-lg font-semibold mb-4">Transaction Details</h2>
                
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
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
                            <p class="font-medium">${{ number_format($selectedTransaction->amount, 2) }}</p>
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
                            <p class="font-medium">{{ $selectedTransaction->account->account_number }}</p>
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
</div> 