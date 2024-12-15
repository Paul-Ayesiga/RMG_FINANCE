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



    public bool $addAccountModal = false;
    public bool $previewAccountModal = false;


    public array $activeFilters = [];

    #[Computed]
    public $accountToPreview = null;


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
    public ? int $transferOtherAccountId = null;

    public Collection $accountTypes;

    public Collection $transferOtherAccounts;

    public $transferFromAccountId= null;

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
                'min:' . ($accountType->min_balance ?? 0)
            ],
            'status' => 'required',
        ],[
            'accountTypeId.required' => 'Account Type required',
            'balance.required' => 'Initial deposit required',
            'balance.numeric' => 'Only numbers allowed',
            'balance.min' => 'Initial Deposit for this account type must be ' . ($accountType->min_balance ?? 'at least') . ' and above'
        ]);

        // Create the account
       DB::transaction(function () {
            $account = Account::create([
                'customer_id' => Auth::user()->customer->id,
                'account_type_id' => $this->accountTypeId,
                'balance' => $this->balance,
                'status' => $this->status,
                'account_number' => $this->generateAccountNumber(),
            ]);

            // Show success message
            $this->notification()->send([
                'icon' => 'success',
                'title' => 'Account created successfully',
            ]);

            // Reset form and close modal
            $this->addAccountModal = false;
            $user = User::where('id', Auth::id())->first();
            $user->notify(new NewAccountCreated('New Account', 'Your account has been successfully created! Your account number is ' . $account->account_number . '.'));
            PrivateNotify::dispatch($user, 'A new account has been created for you!');
        });
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
                ->get();
        } else {
            $this->filteredAccountTypes = collect([]);
        }
    }

    public function render()
    {
        return view('livewire.customer-folder.my-accounts.overview',[
            'accounts' => $this->accounts(),
            'headers' => $this->headers(),
            'selected' => $this->selected,
            'activeFiltersCount' => $this->activeFiltersCount(),
        ]);
    }


}
