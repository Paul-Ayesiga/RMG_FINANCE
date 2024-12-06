<?php

namespace App\Livewire\CustomerFolder\MyAccounts;

use App\Models\Account;
use App\Models\AccountType;
use Livewire\Component;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Validate;
use Mary\Traits\Toast;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Mary\Traits\WithMediaSync;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Lazy;
use Illuminate\Support\Collection;
use App\Models\Transaction;
use Livewire\Attributes\Computed;
use App\Notifications\NewAccountCreated;
use App\Events\PrivateNotify;


#[Lazy()]
class Overview extends Component
{
    use Toast;
    use WithPagination;
    use WithFileUploads, WithMediaSync;

    public ?Account $account;

    public $search = '';
    public array $sortBy = ['column' => 'id', 'direction' => 'asc'];
    public $perPage = 5;
    public array $selected = [];

    // receipt modal
    public bool $showReceiptModal = false;
    public $receiptData = null;
    public $receiptType = null;

    public bool $addAccountModal = false;
    public bool $previewAccountModal = false;
    public bool $depositModal = false;
    public bool $withdrawalModal = false;
    public bool $transferModal = false;

    public array $activeFilters = [];

    #[Computed]
    public $accountToPreview = null;

    #[Computed]
    public $accountToTransferFrom = null;

    #[Computed]
    public $depositToAccount = null;

    #[Computed]
    public $withdrawFromAccount = null;

    #[Validate('required')]
    public $balance;

    #[Validate('required')]
    public $status = 'pending';

    public $columns = [
        'accountType.name' => true,
        'account_number' => true,
        'balance' => true,
        'status' => true,
    ];

    #[Validate('required')]
    public ? int $accountTypeId = null;

    #[Validate('required')]
    public ? int $transferCustomerAccountId = null;

    #[Validate('required')]
    public ? int $transferOtherAccountId = null;

    public Collection $accountTypes;

    public Collection $transferCustomerAccounts;

    public Collection $transferOtherAccounts;

    public $transferFromAccountId= null;

    // deposit amount
    #[Validate('required')]
    public $depositAmount;

    // withdrawal amount
    #[Validate('required')]
    public $withdrawalAmount;

    // trnasfer amount
    #[Validate('required')]
    public $transferAmount;

    public $selectedCategory = null;
    public Collection $filteredAccountTypes;

    public function placeholder()
    {
        return <<<'HTML'
                    <!-- Skeleton Loader for My Accounts Page -->
                    <div class="min-h-screen bg-gray-50 p-4 dark:bg-inherit">
                        <!-- Page Header Skeleton -->
                        <div class="animate-pulse flex justify-between items-center mb-6">
                            <div class="h-6 w-32 bg-gray-300 rounded"></div>
                        </div>

                        <!-- Search Bar Skeleton -->
                        <div class="animate-pulse flex items-center space-x-4 mb-4">
                            <div class="h-10 w-96 bg-gray-300 rounded"></div>
                        </div>

                        <!-- Table Header Skeleton -->
                        <div class="animate-pulse grid grid-cols-4 gap-4 mb-2">
                            <div class="h-6 w-full bg-gray-300 rounded"></div>
                            <div class="h-6 w-full bg-gray-300 rounded"></div>
                            <div class="h-6 w-full bg-gray-300 rounded"></div>
                            <div class="h-6 w-full bg-gray-300 rounded"></div>
                        </div>

                        <!-- Table Rows Skeleton -->
                        <div class="animate-pulse space-y-4">
                            <div class="grid grid-cols-4 gap-4">
                                <div class="h-6 w-full bg-gray-300 rounded"></div>
                                <div class="h-6 w-full bg-gray-300 rounded"></div>
                                <div class="h-6 w-full bg-gray-300 rounded"></div>
                                <div class="h-6 w-full bg-gray-300 rounded"></div>
                            </div>
                        </div>
                    </div>
                HTML;
    }

    public function toggleColumnVisibility($column)
    {
        $this->columns[$column] = !$this->columns[$column];
    }

    public function mount()
    {
        $this->accountTypes = collect([]);
        $this->filteredAccountTypes = collect([]);
        $this->searchAccountType();
        $this->searchTransferCustomerAccounts();
        $this->searchTransferOtherAccounts();
    }

    public function searchAccountType(string $value = '')
    {
        $selectedAccountType = AccountType::where('id', $this->accountTypeId)->get();

        $this->accountTypes = AccountType::query()
            ->where('name', 'ilike', "%$value%")
            ->take(5)
            ->orderBy('name')
            ->get()
            ->merge($selectedAccountType);
    }

    // open preview account modal
    public function OpenPreviewAccountModal($id)
    {
        $account = Account::findOrFail($id);
        $this->accountToPreview = $account;
        $this->previewAccountModal = true;
    }

    public function openTransferModal($id){

        $account = Account::findOrFail($id);
        $this->accountToTransferFrom = $account;
        $this->transferModal = true;

        $this->transferFromAccountId  = $id;

    }

    public function searchTransferCustomerAccounts(string $value = '')
    {
        $customerId = Auth::user()->customer->id;
        $this->transferCustomerAccounts = Account::query()
            ->where('customer_id', $customerId)
            ->where('status', 'active')
            ->whereNot('id', $this->transferFromAccountId)  // Exclude the current account
            ->where(function ($query) use ($value) {
                $query->where('account_number', 'ilike', "%$value%")
                    ->orWhereHas('accountType', function ($subQuery) use ($value) {
                        $subQuery->where('name', 'ilike', "%$value%");
                    });
            })
            ->take(5)
            ->orderBy('account_number')
            ->get();
    }

    public function searchTransferOtherAccounts(string $value = '')
    {
        $selectedAccount = Account::where('id', $this->transferOtherAccountId)->get();

        $this->transferOtherAccounts = Account::whereNot('id',$this->transferFromAccountId)
            ->where('status','active')
            ->where(function ($query) use ($value) {
                $query->where('account_number', 'ilike', "%$value%")
                    ->orWhereHas('accountType', function ($subQuery) use ($value) {
                        $subQuery->where('name', 'ilike', "%$value%");
                    });
            })
            ->take(5)
            ->orderBy('account_number')
            ->get()
            ->merge($selectedAccount);
    }


    public function headers()
    {
        return collect([
            ['key' => 'accountType.name', 'label' => 'Account Type'],
            ['key' => 'account_number', 'label' => 'Account NO.'],
            ['key' => 'balance', 'label' => 'Balance'],
            ['key' => 'status', 'label' => 'Status'],
        ])->filter(function ($header) {
            return $this->columns[$header['key']] ?? false;
        })->toArray();
    }

    public function updated($property): void
    {
        if (!is_array($property) && $property != "") {
            $this->resetPage();
        }
        $this->updateActiveFilters();
    }

    public function accounts(): LengthAwarePaginator
    {
        $customerId = Auth::user()->customer->id;

        return Account::query()
            ->with('accountType')
            ->where('customer_id', $customerId)
            ->when($this->search, function (Builder $query) {
                $query->where('account_number', 'ilike', "%{$this->search}%")
                    ->orWhereHas('accountType', function (Builder $subQuery) {
                        $subQuery->where('name', 'ilike', "%{$this->search}%");
                    });
            })
            ->orderBy(...array_values($this->sortBy))
            ->paginate($this->perPage);
    }

    #[On('reset')]
    public function resetForm()
    {
        $this->transferAmount = null;
        $this->transferCustomerAccountId = null;
        $this->transferOtherAccountId = null;
    }

    public function saveAccount()
    {
        // Get selected account type details
        $accountType = $this->filteredAccountTypes->firstWhere('id', $this->accountTypeId);

        // Validate input fields
        $this->validate([
            'accountTypeId' => 'required',
            'balance' => [
                'required',
                'numeric',
                'min:' . ($accountType['min_balance'] ?? 0)
            ],
            'status' => 'required',
        ]);

        // Create the account
       $account = Account::create([
            'customer_id' => Auth::user()->customer->id,
            'account_type_id' => $this->accountTypeId,
            'balance' => $this->balance,
            'status' => $this->status,
            'account_number' => $this->generateAccountNumber(),
        ]);

        // Show success message
        $this->toast(
            type: 'success',
            title: 'Account created successfully',
            position: 'toast-top toast-end',
            icon: 'o-check-badge',
            css: 'alert alert-success text-white shadow-lg rounded-sm p-3',
            timeout: 3000
        );

        // Reset form and close modal
        $this->addAccountModal = false;
        $user = User::where('id', Auth::id())->first();
        $user->notify(new NewAccountCreated('New Account', 'Your account has been successfully created! Your account number is ' . $account->account_number . '.'));
        PrivateNotify::dispatch($user, 'A new account has been created for you!');
    }

    private function generateAccountNumber(): string
    {
        $prefix = 'R#ACC';
        $year = date('Y');
        $uniqueId = uniqid();
        $randomPart = mt_rand(1000, 9999);

        // Combine all parts to create a unique account number
        $accountNumber = $prefix . $year . substr($uniqueId, -6) . $randomPart;

        // Ensure the generated account number is unique
        while (Account::where('account_number', $accountNumber)->exists()) {
            $uniqueId = uniqid();
            $randomPart = mt_rand(1000, 9999);
            $accountNumber = $prefix . $year . substr($uniqueId, -6) . $randomPart;
        }

        return $accountNumber;
    }

    public function activeFiltersCount(): int
    {
        $count = 0;
        if (!empty($this->search)) $count++;
        return $count;
    }

    public function updateActiveFilters()
    {
        $this->activeFilters = [];
        if (!empty($this->search)) {
            $this->activeFilters['search'] = "Search: " . $this->search;
        }
    }

    public function removeFilter($filter)
    {
        if ($filter == 'search') {
            $this->search = '';
        }
        $this->updateActiveFilters();
        $this->resetPage();
    }

    public function clearAllFilters()
    {
        $this->search = '';
        $this->updateActiveFilters();
        $this->resetPage();
    }


    public function openDepositModal($id)
    {
        $account = Account::find($id);
        $this->depositToAccount = $account;
        $this->depositModal = true;
    }

    // deposit transaction
    public function deposit($accountId)
    {
        $this->validate([
            'depositAmount' => 'required|numeric|min:1000',
        ]);

        $account = Account::findOrFail($accountId);

        try {
            // Get the transaction object from deposit method
            $transaction = $account->deposit($this->depositAmount);

            // Check if transaction was successful and is an object
            if (!$transaction || !is_object($transaction)) {
                throw new \Exception('Transaction failed to process');
            }

            // Get breakdowns of charges and taxes
            $charges = $account->appliedCharges()
                ->where('created_at', $transaction->created_at)
                ->with('bankCharge:id,name')
                ->get()
                ->map(function ($charge) {
                    return [
                        'name' => $charge->bankCharge->name,
                        'amount' => $charge->amount,
                        'rate' => $charge->rate_used . ($charge->was_percentage ? '%' : '')
                    ];
                });

            $taxes = $account->appliedTaxes()
                ->where('created_at', $transaction->created_at)
                ->with('tax:id,name')
                ->get()
                ->map(function ($tax) {
                    return [
                        'name' => $tax->tax->name,
                        'amount' => $tax->amount,
                        'rate' => $tax->rate_used . ($tax->was_percentage ? '%' : '')
                    ];
                });

            // Set receipt data and show modal
            $this->receiptData = [
                'date' => now()->format('Y-m-d H:i:s'),
                'account_number' => $account->account_number,
                'amount' => $this->depositAmount,
                'charges' => $charges->toArray(),
                'total_charges' => $transaction->charges,
                'taxes' => $taxes->toArray(),
                'total_taxes' => $transaction->taxes,
                'total_amount' => $transaction->total_amount,
                'reference' => $transaction->reference_number ?? 'DEP' . time(),
                'balance' => $account->balance
            ];

            $this->receiptType = 'deposit';
            $this->depositAmount = null;
            $this->depositModal = false;
            $this->showReceiptModal = true;

        } catch (\Exception $e) {
            dd($e->getMessage());
            $this->toast(
                type: 'error',
                title: $e->getMessage(),
                position: 'toast-top toast-end',
                icon: 'o-x-circle',
                css: 'alert alert-error text-white shadow-lg rounded-sm p-3',
                timeout: 3000
            );
        }
    }


    public function openWithdrawModal($id)
    {
        $account = Account::find($id);
        $this->withdrawFromAccount = $account;
        // dd($this->withdrawFromAccount);
        $this->withdrawalModal = true;
    }

    public function withdraw($accountId)
    {
        $account = Account::findOrFail($accountId);

        $this->validate([
            'withdrawalAmount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:' . ($account->accountType->max_withdrawal ?? PHP_FLOAT_MAX),
            ],
        ], [
            'withdrawalAmount.max' => 'Maximum withdrawal limit is ' . number_format($account->accountType->max_withdrawal ?? PHP_FLOAT_MAX, 2),
        ]);

        // Check withdrawal limit
        $withdrawalCount = $account->transactions()
            ->where('type', 'withdrawal')
            ->whereDate('created_at', today())
            ->count();

        if ($withdrawalCount >= 4) {
            $this->toast(
                type: 'error',
                title: 'Withdrawal limit reached',
                position: 'toast-top toast-end'
            );
            return;
        }

        try {
            DB::beginTransaction();

            // Get the transaction object from withdraw method
            $transaction = $account->withdraw($this->withdrawalAmount);

            if (!$transaction || !is_object($transaction)) {
                throw new \Exception('Transaction failed to process');
            }

            // Get breakdowns of charges and taxes
            $charges = $account->appliedCharges()
                ->where('created_at', $transaction->created_at)
                ->with('bankCharge:id,name')
                ->get()
                ->map(function ($charge) {
                    return [
                        'name' => $charge->bankCharge->name,
                        'amount' => $charge->amount,
                        'rate' => $charge->rate_used . ($charge->was_percentage ? '%' : '')
                    ];
                });

            $taxes = $account->appliedTaxes()
                ->where('created_at', $transaction->created_at)
                ->with('tax:id,name')
                ->get()
                ->map(function ($tax) {
                    return [
                        'name' => $tax->tax->name,
                        'amount' => $tax->amount,
                        'rate' => $tax->rate_used . ($tax->was_percentage ? '%' : '')
                    ];
                });

            // Set receipt data
            $this->receiptData = [
                'date' => now()->format('Y-m-d H:i:s'),
                'account_number' => $account->account_number,
                'amount' => $this->withdrawalAmount,
                'charges' => $charges->toArray(),
                'total_charges' => $transaction->charges,
                'taxes' => $taxes->toArray(),
                'total_taxes' => $transaction->taxes,
                'total_amount' => $transaction->total_amount,
                'reference' => $transaction->reference_number ?? 'WTH' . time(),
                'balance' => $account->balance
            ];

            DB::commit();

            $this->receiptType = 'withdrawal';
            $this->withdrawalModal = false;
            $this->showReceiptModal = true;
            $this->withdrawalAmount = null;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->toast(
                type: 'error',
                title: $e->getMessage(),
                position: 'toast-top toast-end'
            );
        }
    }

    public function transfer($id)
    {
        $sourceAccount = Account::findOrFail($id);

        // Validate based on transfer type
        $this->validate([
            'transferAmount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:' . ($sourceAccount->accountType->max_withdrawal ?? PHP_FLOAT_MAX),
            ],
        ], [
            'transferAmount.max' => 'Maximum transfer limit is ' . number_format($sourceAccount->accountType->max_withdrawal ?? PHP_FLOAT_MAX, 2),
        ]);

        // Get the destination account based on the selected type
        $destinationAccountId = $this->transferCustomerAccountId ?? $this->transferOtherAccountId;
        if (!$destinationAccountId) {
            $this->toast(
                type: 'error',
                title: 'Please select a destination account',
                position: 'toast-top toast-end',
                icon: 'o-x-circle',
                css: 'alert alert-error text-white shadow-lg rounded-sm p-3',
                timeout: 3000
            );
            return;
        }

        $destinationAccount = Account::findOrFail($destinationAccountId);

        if ($sourceAccount->id === $destinationAccount->id) {
            $this->toast(
                type: 'error',
                title: 'Cannot transfer to same account',
                position: 'toast-top toast-end',
                icon: 'o-x-circle',
                css: 'alert alert-error text-white shadow-lg rounded-sm p-3',
                timeout: 3000
            );
            return;
        }

        try {
            // Attempt the transfer
            $transaction = $sourceAccount->transfer($destinationAccount, $this->transferAmount);

            if (!$transaction || !is_object($transaction)) {
                throw new \Exception('Transfer failed to process');
            }

            // Determine if it's an internal transfer
            $isInternalTransfer = $sourceAccount->customer_id === $destinationAccount->customer_id;

            // Get charges breakdown
            $charges = $sourceAccount->appliedCharges()
                ->where('created_at', $transaction->created_at)
                ->with('bankCharge:id,name')
                ->get()
                ->map(function ($charge) {
                    return [
                        'name' => $charge->bankCharge->name,
                        'amount' => $charge->amount,
                        'rate' => $charge->rate_used . ($charge->was_percentage ? '%' : '')
                    ];
                });

            // Get taxes breakdown (only for external transfers)
            $taxes = $isInternalTransfer ? collect([]) : $sourceAccount->appliedTaxes()
                ->where('created_at', $transaction->created_at)
                ->with('tax:id,name')
                ->get()
                ->map(function ($tax) {
                    return [
                        'name' => $tax->tax->name,
                        'amount' => $tax->amount,
                        'rate' => $tax->rate_used . ($tax->was_percentage ? '%' : '')
                    ];
                });

            // Set receipt data and show modal
            $this->receiptData = [
                'date' => now()->format('Y-m-d H:i:s'),
                'from_account' => $sourceAccount->account_number,
                'to_account' => $destinationAccount->account_number,
                'amount' => $this->transferAmount,
                'charges' => $charges->toArray(),
                'total_charges' => $transaction->charges,
                'taxes' => $taxes->toArray(),
                'total_taxes' => $transaction->taxes,
                'total_amount' => $transaction->total_amount,
                'reference' => $transaction->reference_number ?? 'TRF' . time(),
                'balance' => $sourceAccount->balance,
                'is_internal' => $isInternalTransfer
            ];

            $this->receiptType = 'transfer';
            $this->showReceiptModal = true;
            $this->transferModal = false;
            $this->resetForm();

        } catch (\Exception $e) {
            $this->toast(
                type: 'error',
                title: $e->getMessage(),
                position: 'toast-top toast-end',
                icon: 'o-x-circle',
                css: 'alert alert-error text-white shadow-lg rounded-sm p-3',
                timeout: 3000
            );
        }
    }

    public function render()
    {
        return view('livewire.customer-folder.my-accounts.overview',[
            'accounts' => $this->accounts(),
            'headers' => $this->headers(),
            'selected' => $this->selected,
            'activeFiltersCount' => $this->activeFiltersCount(),
            'transferFromAccountId' => $this->transferFromAccountId
        ]);
    }

    public function getCategories()
    {
        return [
            [
                'id' => 'Checking Accounts',
                'name' => 'Checking Accounts'
            ],
            [
                'id' => 'Savings Accounts',
                'name' => 'Savings Accounts'
            ],
            [
                'id' => 'Certificates of Deposit',
                'name' => 'Certificates of Deposit'
            ],
            [
                'id' => 'Individual Retirement Accounts',
                'name' => 'Individual Retirement Accounts'
            ],
            [
                'id' => 'Health Savings Accounts',
                'name' => 'Health Savings Accounts'
            ],
            [
                'id' => 'Brokerage and Investment Accounts',
                'name' => 'Brokerage and Investment Accounts'
            ],
            [
                'id' => 'Credit Accounts',
                'name' => 'Credit Accounts'
            ],
            [
                'id' => 'Business Accounts',
                'name' => 'Business Accounts'
            ],
            [
                'id' => 'Specialty Accounts',
                'name' => 'Specialty Accounts'
            ]
        ];
    }

    public function updatedSelectedCategory($value)
    {
        $this->accountTypeId = null; // Reset the selected account type
        if ($value) {
            $this->filteredAccountTypes = AccountType::where('category', $value)
                ->get()
                ->map(function($accountType) {
                    return [
                        'id' => $accountType->id,
                        'name' => $accountType->name . ' (' . $accountType->interest_rate . '% interest)',
                        'description' => $accountType->description,
                        'interest_rate' => $accountType->interest_rate
                    ];
                });
        } else {
            $this->filteredAccountTypes = collect([]);
        }
    }
}
