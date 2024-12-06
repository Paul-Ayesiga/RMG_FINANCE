<?php

namespace App\Livewire\Accounts;

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
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Lazy;
use Illuminate\Validation\Rules;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Collection;
use App\Notifications\AccountStatusNotification;
use App\Events\AccountStatusUpdated;
use App\Events\PrivateNotify;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use App\Notifications\NewAccountCreated;


#[Lazy()]
class AccountsOverview extends Component
{
    use Toast;
    use WithPagination;
    use WithFileUploads, WithMediaSync;

    public ?Account $account;

    public $search = '';
    public array $sortBy = ['column' => 'id', 'direction' => 'asc'];
    public $perPage = 5;
    public array $selected = [];

    public bool $filledbulk = false;
    public bool $emptybulk = false;
    public bool $addAccountModal = false;
    public bool $previewAccountModal = false;
    public bool $editAccountModal = false;
    public bool $deleteAccountModal = false;

    public array $activeFilters = [];

    // New fields from your customer creation form

    #[Validate('required')]
    public $balance;

    #[Validate('required')]
    public $status;

    public $columns = [
        'customer.user.name' => true,
        'accountType.name' => true,
        'account_number' => true,
        'balance' => true,
        'status' => true,
    ];

    #[Computed]
    public ? int $accountToDelete = null;

    #[Computed]
    public $accountToPreview = null;

     #[Validate('required')]
    public ? int $customerId = null;

    #[Validate('required')]
    public ? int $accountTypeId = null;

    public Collection $customers;
    // public Collection $accountTypes;

    public $selectedCategory = null;
    public $filteredAccountTypes = [];

    // Add this property to store account statuses
    public $accountStatuses = [];

    public function placeholder()
    {
        // return view('livewire.placeholder');
            return <<<'HTML'
                    <!-- Skeleton Loader for clients Page -->
                    <div class="min-h-screen bg-gray-50 p-4 dark:bg-inherit">
                        <!-- Page Header Skeleton -->
                        <div class="animate-pulse flex justify-between items-center mb-6">
                            <div class="h-6 w-32 bg-gray-300 rounded"></div>
                            <div class="h-10 w-48 bg-gray-300 rounded"></div>
                        </div>

                        <!-- Search Bar and Buttons Skeleton -->
                        <div class="animate-pulse flex items-center space-x-4 mb-4">
                            <div class="h-10 w-96 bg-gray-300 rounded"></div>
                            <div class="h-10 w-32 bg-gray-300 rounded"></div>
                        </div>

                        <!-- Action Buttons Skeleton -->
                        <div class="animate-pulse flex items-center space-x-4 mb-6">
                            <div class="h-8 w-20 bg-red-300 rounded"></div>
                            <div class="h-8 w-20 bg-yellow-300 rounded"></div>
                            <div class="h-8 w-20 bg-purple-300 rounded"></div>
                            <div class="h-8 w-20 bg-green-300 rounded"></div>
                            <div class="h-8 w-20 bg-gray-300 rounded"></div>
                        </div>

                        <!-- Table Header Skeleton -->
                        <div class="animate-pulse grid grid-cols-5 gap-4 mb-2">
                            <div class="h-6 w-full bg-gray-300 rounded"></div>
                            <div class="h-6 w-full bg-gray-300 rounded"></div>
                            <div class="h-6 w-full bg-gray-300 rounded"></div>
                            <div class="h-6 w-full bg-gray-300 rounded"></div>
                            <div class="h-6 w-full bg-gray-300 rounded"></div>
                        </div>

                        <!-- Table Rows Skeleton -->
                        <div class="animate-pulse space-y-4">
                            <div class="grid grid-cols-5 gap-4">
                                <div class="h-6 w-full bg-gray-300 rounded"></div>
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
        $this->searchCustomer();
        // $this->accountTypes = collect([]);
        $this->filteredAccountTypes = collect([]);

        // Initialize account statuses in mount
        $this->accounts()->each(function ($account) {
            $this->accountStatuses[$account->id] = $account->status;
        });
    }

    public function searchCustomer(string $value = '')
    {
        $selectedCustomer = Customer::with('user')->where('id', $this->customerId)->get();

        $this->customers = Customer::query()
            ->with('user')
            ->when($value, function ($query) use ($value) {
                return $query->whereHas('user', function ($subQuery) use ($value) {
                    $subQuery->where('name', 'ilike', "%$value%");
                });
            })
            ->get()
            ->merge($selectedCustomer);
    }



    public function headers()
    {
        return collect([
            // ['key' => 'user.avatar', 'label' => 'Photo', 'class' => 'w-1'],
            ['key' => 'customer.user.name', 'label' => 'Owner'],
            ['key' => 'accountType.name', 'label' => 'Account Type'],
            ['key' => 'account_number', 'label' => 'Account NO.'],
            ['key' => 'balance', 'label' => 'balance'],
            ['key' => 'status', 'label' => 'status'],
            ['key' => 'created_at', 'label' => 'creation date'],
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
         return Account::query()
            ->with(['customer', 'accountType'])
            ->when($this->search, function (Builder $query) {
                $query->where('account_number', 'ilike', "%{$this->search}%")
                    ->orWhereHas('customer.user', function (Builder $subQuery) {
                        $subQuery->where('name', 'ilike', "%{$this->search}%");
                    });
            })
            ->orderBy(...array_values($this->sortBy))
            ->paginate($this->perPage);
    }

    #[On('reset')]
    public function resetForm()
    {

    }

    public function saveAccount()
    {
        // Validate input fields
        $this->validate();

        // Get the selected account type
        $accountType = AccountType::findOrFail($this->accountTypeId);

        // Check if initial balance meets minimum balance requirement
        if ($this->balance < $accountType->min_balance) {
            $this->toast(
                type: 'error',
                title: "Initial balance must be at least $" . number_format($accountType->min_balance, 2),
                position: 'toast-top toast-end',
                icon: 'o-x-circle',
                css: 'alert alert-error text-white shadow-lg rounded-sm p-3',
                timeout: 3000
            );
            return;
        }

        // Create the account
        $account = Account::create([
            'customer_id' => $this->customerId,
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
        $user = Customer::find($this->customerId)->user; // Fetch the user from the customer_id
        $user->notify(new NewAccountCreated('New Account', 'Your account has been successfully created! Your account number is ' . $account->account_number . '.'));
        PrivateNotify::dispatch($user, 'A new account has been created for you!');
        // event(new NewAccount(Auth::user()));
     }

   // ... existing code ...

    public function updateStatus($accountId, $value)
    {
        try {
            DB::beginTransaction();

            $account = Account::with(['customer.user'])->findOrFail($accountId);
            $oldStatus = $account->status;

            // Update the status
            $account->update([
                'status' => $value
            ]);

            // Update the local status
            $this->accountStatuses[$accountId] = $value;

            // Send notification if status has changed
            if ($oldStatus !== $value) {
                $user = $account->customer->user;

                // Determine notification data based on new status
                $notificationData = match($value) {
                    'active' => [
                        'title' => 'Account Activated',
                        'message' => "Your account {$account->account_number} has been successfully activated.",
                        'status' => $value
                    ],
                    'inactive' => [
                        'title' => 'Account Suspended',
                        'message' => "Your account {$account->account_number} has been suspended. Please contact support for assistance.",
                        'status' => $value
                    ],
                    'closed' => [
                        'title' => 'Account Closed',
                        'message' => "Your account {$account->account_number} has been closed.",
                        'status' => $value
                    ],
                    default => [
                        'title' => 'Account Status Update',
                        'message' => "Your account {$account->account_number} status has been changed to {$value}.",
                        'status' => $value
                    ]
                };
                // event(new AccountStatusUpdated('updated successfully'));

                // Send notification
                $user->notify(new AccountStatusNotification(
                    $account,
                    $notificationData['title'],
                    $notificationData['message'],
                    $notificationData['status']
                ));

                PrivateNotify::dispatch($user, $notificationData['message']);

            }

            DB::commit();

            // Show success toast to admin
            $this->toast(
                type: 'success',
                title: "Status updated to {$value}",
                position: 'toast-top toast-end',
                icon: 'o-check-badge',
                css: 'alert alert-success text-white shadow-lg rounded-sm p-3',
                timeout: 3000
            );

        } catch (\Exception $e) {
            DB::rollBack();

            // Log the error
            // \Log::error('Failed to update account status: ' . $e->getMessage());

            dd($e->getMessage());
            // Show error toast to admin
            $this->toast(
                type: 'error',
                title: 'Failed to update status',
                position: 'toast-top toast-end',
                icon: 'o-x-circle',
                css: 'alert alert-error text-white shadow-lg rounded-sm p-3',
                timeout: 3000
            );
        }
    }

    public function notifyAccountStatusUpdated()
    {
        $this->toast('success', 'you have a new notification');
    }

    private function generateAccountNumber(): string
    {
        // Generate a unique account number with R#ACC prefix
        return 'R#ACC' . date('Y') . str_pad(Account::count() + 1, 8, '0', STR_PAD_LEFT);
    }


    public function openDeleteModal($id)
    {
        $this->accountToDelete = $id;
        $this->deleteAccountModal = true;
    }

    // confirm delete for single account
    public function confirmDelete($id)
    {
        try {
            $account = Account::findOrFail($id);

            // Begin a database transaction
            DB::beginTransaction();

            // Delete associated transactions
            $account->transactions()->delete();

            // Delete the account
            $account->delete();

            // Commit the transaction
            DB::commit();

            $this->deleteAccountModal = false;
            $this->toast(
                type: 'success',
                title: 'Account and associated transactions deleted successfully',
                description: null,
                position: 'toast-top toast-end',
                icon: 'o-check-badge',
                css: 'alert alert-success text-white shadow-lg rounded-sm p-3',
                timeout: 3000,
                redirectTo: null
            );
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();

            $this->deleteAccountModal = false;
            $this->toast(
                type: 'error',
                title: 'Failed to delete account and transactions',
                description: $e->getMessage(),
                position: 'toast-top toast-end',
                icon: 'o-x-circle',
                css: 'alert alert-error text-white shadow-lg rounded-sm p-3',
                timeout: 3000,
                redirectTo: null
            );
        }
    }

    public function openPreviewModal($id)
    {
        $account = Account::findOrFail($id);
        $this->accountToPreview = $account;
        $this->previewAccountModal = true;
    }

    public function bulk()
    {
        if (!empty($this->selected)) {
            $this->filledbulk = true;
        } else {
            $this->emptybulk = true;
        }
    }

    // confirm delete for bulk accounts
    public function deleteSelected()
    {
         // Assuming you have a model for the items, e.g., Item::destroy($this->selected)
        Account::destroy($this->selected);

        // Reset the selected array after deletion
        $this->selected = [];

        // Optionally add some feedback to the user
        $this->filledbulk = false;
        $this->toast(
                type: 'error',
                title: 'Accounts deleted with success',
                description: null,
                position: 'toast-top toast-end',
                icon: 'o-check-badge',
                css: 'alert alert-danger text-white shadow-lg rounded-sm p-3',
                timeout: 3000,
                redirectTo: null
            );
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

    public function exportToExcel()
    {
        // Implement export logic as needed
    }

    public function render()
    {
        return view('livewire.accounts.accounts-overview', [
            'accounts' => $this->accounts(),
            'headers' => $this->headers(),
            'selected' => $this->selected,
            'activeFiltersCount' => $this->activeFiltersCount(),
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
                        'interest_rate' => $accountType->interest_rate,
                        'min_balance' => $accountType->min_balance,
                        'max_withdrawal' => $accountType->max_withdrawal,
                        'maturity_period' => $accountType->maturity_period,
                        'monthly_deposit' => $accountType->monthly_deposit,
                        'overdraft_limit' => $accountType->overdraft_limit,
                    ];
                });
        } else {
            $this->filteredAccountTypes = collect([]);
        }
    }
}
