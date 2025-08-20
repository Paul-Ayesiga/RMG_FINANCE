<div
    x-data="{
        tabSelected: 1,
        tabId: $id('tabs'),
        hostAccount: null, // Add host account data
        selectedAccounts: [], // Array to store selected accounts
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
        },
        isHostAccountValid() {
            // Validate if host account is not selected in other accounts
            return !this.selectedAccounts.includes(this.hostAccount);
        }
    }"
    x-init="tabRepositionMarker($refs.tabButtons.firstElementChild);"
    class="relative w-full max-w-3xl mx-auto z-10">

     <div class="mb-6">
        <div class="overflow-x-auto">
            <!-- Loading Spinner: Displayed globally while any request is loading -->
            <!-- Table -->
            <table class="min-w-full table-auto border-separate border-spacing-0 shadow-lg rounded-lg dark:bg-inherit">
                <thead>
                    <tr class="bg-gray-200 text-left">
                        <th class="py-2 px-4 border-b text-sm font-semibold text-gray-700">#</th>
                        <th class="py-2 px-4 border-b text-sm font-semibold text-gray-700">Host Account</th>
                        <th class="py-2 px-4 border-b text-sm font-semibold text-gray-700">Amount</th>
                        <th class="py-2 px-4 border-b text-sm font-semibold text-gray-700">Start Date</th>
                        <th class="py-2 px-4 border-b text-sm font-semibold text-gray-700">End Date</th>
                        <th class="py-2 px-4 border-b text-sm font-semibold text-gray-700">Frequency</th>
                        <th class="py-2 px-4 border-b text-sm font-semibold text-gray-700">Status</th>
                        <th class="py-2 px-4 border-b text-sm font-semibold text-gray-700">Receiver</th>
                        <th class="py-2 px-4 border-b text-sm font-semibold text-gray-700 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody> <!-- This will remove rows while loading -->
                    @forelse($this->standingOrders as $order)
                        <tr class="hover:bg-gray-100 dark:hover:bg-black">
                            <td class="py-2 px-4 border-b text-sm text-gray-800 dark:text-white">{{ $loop->iteration }}</td>
                            <td class="py-2 px-4 border-b text-sm text-gray-800 dark:text-white">{{ $order->host_account->account_number}}</td>
                            <td class="py-2 px-4 border-b text-sm text-gray-800 dark:text-white">UGX {{ number_format(convertCurrency($order->amount, 'UGX', $currency), 2) }}</td>
                            <td class="py-2 px-4 border-b text-sm text-gray-800 dark:text-white">{{ $order->start_date->format('Y-m-d') }}</td>
                            <td class="py-2 px-4 border-b text-sm text-gray-800 dark:text-white">{{ $order->end_date ? $order->end_date->format('Y-m-d') : 'N/A' }}</td>
                            <td class="py-2 px-4 border-b text-sm text-gray-800 dark:text-white capitalize">{{ $order->frequency }}</td>
                            <td class="py-2 px-4 border-b text-sm text-gray-800 dark:text-white capitalize">{{ $order->status }}</td>
                            <td class="py-2 px-4 border-b text-sm text-gray-800 dark:text-white">
                                @foreach ($order->accounts as $record)
                                    {{ $record->pivot->account_id ? 'RMGbank' : 'BeneficiaryOtherBank' }} ({{ $record->pivot->account_id ? $record->account_number : $record->pivot->account_number }})
                                @endforeach
                            </td>
                            <td class="py-2 px-2 border-b text-sm text-center">
                                <!-- Edit Button with Loading Spinner -->
                                <x-wireui-button
                                    wire:click="editStandingOrder({{ $order->id }})"
                                    wire:loading.target="editStandingOrder({{ $order->id }})"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50 cursor-not-allowed"
                                    spinner="editStandingOrder({{ $order->id }})"
                                    icon="pencil"
                                    class="bg-orange-500 text-white mb-2"
                                    flat label="Edit"
                                />

                                <!-- Delete Button with Loading Spinner -->
                                <x-wireui-button
                                    wire:confirm
                                    wire:click="deleteStandingOrder({{ $order->id }})"
                                    wire:loading.target="deleteStandingOrder({{ $order->id }})"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50 cursor-not-allowed"
                                    spinner="deleteStandingOrder({{ $order->id }})"
                                    icon="trash"
                                    class="bg-red-900 text-white"
                                    flat label="Delete"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-4 text-center text-gray-500">No standing orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>
    </div>


    <form wire:submit.prevent="createStandingOrder">
        @csrf

         <!-- Add edit and delete actions -->
        <div class="mb-4">
            @isset($standingOrderId)
                <button type="button" wire:confirm="deleteStandingOrder({{ $standingOrderId }})" class="bg-red-500 text-white rounded py-2 px-4 mt-4">Delete Standing Order</button>
            @endisset
        </div>

        <!-- Tabs for Account or Beneficiary Selection -->
        <div x-ref="tabButtons" class="relative inline-flex items-center justify-center w-full h-12 grid-cols-2 p-1 bg-gray-100 rounded-lg select-none shadow-sm mx-auto  dark:bg-inherit">
            <button :id="$id(tabId)" @click="tabButtonClicked($el);" type="button" class="relative z-20 flex items-center justify-center w-full h-10 px-3 text-sm font-medium transition-all rounded-md cursor-pointer whitespace-nowrap dark:text-white">
                Your Accounts
            </button>
            <button :id="$id(tabId)" @click="tabButtonClicked($el);" type="button" class="relative z-20 flex items-center justify-center w-full h-10 px-3 text-sm font-medium transition-all rounded-md cursor-pointer whitespace-nowrap dark:text-white">
                Beneficiaries
            </button>
           <div
                x-ref="tabMarker"
                class="absolute left-0 top-0 z-10 h-10 duration-300 ease-out"
                x-cloak
            >
            <div class="w-full h-full bg-white rounded-md shadow-sm dark:bg-blue-700"></div>
        </div>
        </div>

           <!-- Host Account -->
        <div class="form-group mx-auto mb-4 mt-5">
            <x-wireui-select
                label="Select Host Account"
                placeholder="Select host account"
                :async-data="route('api.accounts')"
                option-label="account_number"
                option-value="id"
                wire:model="host_account"
                errorless
            />
            @error('host_account') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Account Tab Content -->
        <div :id="$id(tabId + '-content')" x-show="tabContentActive($el)" class="relative mt-4 mx-auto mb-4">
            <div class="form-group">
            <x-wireui-select
                label="Select from your accounts to receive money"
                placeholder="Select accounts"
                :async-data="route('api.accounts')"
                option-label="account_number"
                option-value="id"
                multiselect
                errorless
                always-fetch
                wire:model="selected_accounts"
            />
            @error('selected_accounts') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Beneficiary Tab Content -->
        <div :id="$id(tabId + '-content')" x-show="tabContentActive($el)" class="relative mt-4 mx-auto mb-4" x-cloak>
            <div class="form-group">
                <label for="beneficiaries" class="block text-lg font-semibold text-gray-700">Select Beneficiaries with RMG accounts</label>
                <x-wireui-select
                    wire:model="selected_beneficiaries"
                    placeholder="Select beneficiaries"
                    :async-data="route('api.beneficiaries')"
                    option-label="nickname"
                    option-value="id"
                    option-description="account_number"
                    always-fetch
                    multiselect
                    errorless
                />
                @error('selected_beneficiaries') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Amount -->
        <div class="form-group mx-auto mb-4">
            <label for="amount" class="block  font-semibold text-gray-700">Amount</label>
            <input wire:model="amount" type="number" step="0.01" class="form-control mt-2 block w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" id="amount" placeholder="Amount">
            @error('amount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Start Date -->
        <div class="w-full inline-flex flex-wrap">
            <div class="lg:w-1/2 mb-4 sm:w-full">
                <label for="start_date" class="block  font-semibold text-gray-700">Start Date</label>
                <input wire:model="start_date" type="date" class="form-control mt-2 block w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" id="start_date">
                @error('start_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- End Date -->
            <div class="lg:w-1/2 mb-4 sm:w-full">
                <label for="end_date" class="block  font-semibold text-gray-700">End Date (optional)</label>
                <input wire:model="end_date" type="date" class="form-control mt-2 block w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" id="end_date">
                @error('end_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Frequency -->
        <div class="form-group mx-auto mb-4">
            <x-wireui-select
                wire:model="frequency"
                label="Select frequency ( monthly - default)"
                placeholder="Select one frequency"
                :options="['daily', 'weekly', 'monthly', 'yearly']"
                errorless
            />
            @error('frequency') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Submit Button -->
        <div class="mt-6 mx-auto">
            <x-wireui-button wire:click="createStandingOrder" spinner="createStandingOrder" class="btn btn-primary w-full py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"   label="{{ $standingOrderId ? 'Update Standing Order' : 'Create Standing Order' }}"/>
        </div>
    </form>
</div>
