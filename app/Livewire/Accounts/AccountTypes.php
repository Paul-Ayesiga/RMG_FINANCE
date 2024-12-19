<?php

namespace App\Livewire\Accounts;

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
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;

#[Lazy()]
class accountTypes extends Component
{
    use Toast;
    use WithPagination;
    use WithFileUploads, WithMediaSync;

    public ?AccountType $accountType;

    public $search = '';
    public array $sortBy = ['column' => 'id', 'direction' => 'asc'];
    public $perPage = 5;
    public array $selected = [];

    public bool $filledbulk = false;
    public bool $emptybulk = false;
    public bool $addAccountTypeModal = false;
    public bool $previewAccountTypeModal = false;
    public bool $editAccountTypeModal = false;
    public bool $deleteAccountTypeModal = false;

    public array $activeFilters = [];

    #[Computed]
    public $accountTypeToPreview = null;

    #[Computed]
    public $accountTypeToDelete = null;

    // New fields from your customer creation form
    #[Validate('required|string|max:255')]
    public $name;

    #[Validate('required')]
    public $description;

    #[Validate('required')]
    public $interest_rate;

    #[Validate('nullable|numeric|min:0')]
    public $min_balance;

    #[Validate('nullable|numeric|min:0')]
    public $max_withdrawal;

    #[Validate('nullable|numeric|min:0')]
    public $maturity_period;

    #[Validate('nullable|numeric|min:0')]
    public $monthly_deposit;

    #[Validate('nullable|numeric|min:0')]
    public $overdraft_limit;

    #[Validate('required|string')]
    public $category;

    public $columns = [
        'accounts_count' => true,
        'category' => true,
        'name' => true,
        'description' => false,
        'interest_rate' => true,
        'min_balance' => true,
        'max_withdrawal' => true,
        'maturity_period' => true,
        'monthly_deposit' => true,
        'overdraft_limit' => true
    ];

    // Add accountTypeId property to store the ID of the account type being edited
    public $accountTypeId;

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
    }

    public function headers()
    {
        return collect([
            ['key' => 'accounts_count', 'label' => 'Accounts Available'],
            ['key' => 'category', 'label' => 'Category'],
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'description', 'label' => 'Description'],
            ['key' => 'interest_rate', 'label' => 'Interest Rate'],
            ['key' => 'min_balance', 'label' => 'min balance'],
            ['key' => 'max_withdrawal', 'label' => 'max_withdrawal'],
            ['key' => 'maturity_period', 'label' => 'maturity_period'],
            ['key' => 'monthly_deposit', 'label' => 'Monthly Deposit'],
            ['key' => 'overdraft_limit', 'label' => 'Overdraft Limit'],
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

    public function accountTypes(): LengthAwarePaginator
    {
         return AccountType::query()
            ->withCount('accounts') // This will add a 'products_count' attribute
            ->when($this->search, fn(Builder $q) => $q->where('name', 'like', "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate($this->perPage);
    }

    #[On('reset')]
    public function resetForm()
    {

    }

    public function saveAccountType()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            AccountType::create([
                'category' => $this->category,
                'name' => $this->name,
                'description' => $this->description,
                'interest_rate' => $this->interest_rate,
                'min_balance' => $this->min_balance,
                'max_withdrawal' => $this->max_withdrawal,
                'maturity_period' => $this->maturity_period,
                'monthly_deposit' => $this->monthly_deposit,
                'overdraft_limit' => $this->overdraft_limit,
            ]);

            $this->toast(
                    type: 'success',
                    title: 'Account Type created with success',
                    description: null,
                    position: 'toast-top toast-end',
                    icon: 'o-check-badge',
                    css: 'alert alert-success text-white shadow-lg rounded-sm p-3',
                    timeout: 3000,
                    redirectTo: null
                );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error
            // \Log::error('Failed to create account type: ' . $e->getMessage());
            $this->toast(
                type: 'error',
                title: 'Failed to create account type',
                description: 'An error occurred while creating the account type.',
                position: 'toast-top toast-end',
                icon: 'o-x-circle',
                css: 'alert alert-error text-white shadow-lg rounded-sm p-3',
                timeout: 3000,
                redirectTo: null
            );
        }

        // Reset form fields after saving
        $this->reset();
        // Close the modal
        $this->addAccountTypeModal = false;
    }

    #[On('edit-account-type')]
    public function editAccountType($accountTypeId)
    {
        // Store the account type ID
        $this->accountTypeId = $accountTypeId;

        // Find the account type
        $accountType = AccountType::findOrFail($accountTypeId);

        // Set the form fields
        $this->category = $accountType->category;
        $this->name = $accountType->name;
        $this->description = $accountType->description;
        $this->min_balance = $accountType->min_balance;
        $this->interest_rate = $accountType->interest_rate;
        $this->max_withdrawal = $accountType->max_withdrawal;
        $this->monthly_deposit = $accountType->monthly_deposit;
        $this->maturity_period = $accountType->maturity_period;
        $this->overdraft_limit = $accountType->overdraft_limit;

        $this->editAccountTypeModal = true;
    }

    public function updateAccountType()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            // Find the account type using the stored ID
            $accountType = AccountType::findOrFail($this->accountTypeId);

            // Update the account type
            $accountType->update([
                'category' => $this->category,
                'name' => $this->name,
                'description' => $this->description,
                'min_balance' => $this->min_balance,
                'interest_rate' => $this->interest_rate,
                'max_withdrawal' => $this->max_withdrawal,
                'monthly_deposit' => $this->monthly_deposit,
                'maturity_period' => $this->maturity_period,
                'overdraft_limit' => $this->overdraft_limit,
            ]);

            $this->editAccountTypeModal = false;

            // Reset all form fields
            $this->reset([
                'accountTypeId',
                'category',
                'name',
                'description',
                'min_balance',
                'interest_rate',
                'max_withdrawal',
                'monthly_deposit',
                'maturity_period',
                'overdraft_limit'
            ]);

            $this->toast(
                type: 'success',
                title: 'Account Type updated successfully',
                position: 'toast-top toast-end',
                icon: 'o-check-badge',
                css: 'alert alert-success text-white shadow-lg rounded-sm p-3',
                timeout: 3000
            );
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->toast(
                type: 'error',
                title: 'Error updating account type',
                description: $e->getMessage(),
                position: 'toast-top toast-end',
                icon: 'o-x-circle',
                css: 'alert alert-error text-white shadow-lg rounded-sm p-3',
                timeout: 3000
            );
        }
    }

    public function OpenPreviewAccountTypeModal($id)
    {
        $accountType = AccountType::findOrFail($id);
        $this->accountTypeToPreview = $accountType;
        $this->previewAccountTypeModal = true;
    }

    public function openDeleteAccountTypeModal($id)
    {
        $this->accountTypeToDelete = $id;
        $this->deleteAccountTypeModal = true;
    }

    public function confirmDelete($id)
    {
        try {
            $accountType = AccountType::findOrFail($id);

            // Begin a database transaction
            DB::beginTransaction();

            // Delete all associated accounts and their transactions
            foreach ($accountType->accounts as $account) {
                // Delete all transactions associated with this account
                $account->transactions()->delete();

                // Delete the account itself
                $account->delete();
            }

            // Delete the account type
            $accountType->delete();

            // Commit the transaction
            DB::commit();

            $this->deleteAccountTypeModal = false;
            $this->toast(
                type: 'success',
                title: 'Account Type, associated accounts, and all transactions deleted successfully',
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

            $this->deleteAccountTypeModal = false;
            $this->toast(
                type: 'error',
                title: 'Failed to delete Account Type and associated data',
                description: $e->getMessage(),
                position: 'toast-top toast-end',
                icon: 'o-x-circle',
                css: 'alert alert-error text-white shadow-lg rounded-sm p-3',
                timeout: 3000,
                redirectTo: null
            );
        }
    }

    public function bulk()
    {
        if (!empty($this->selected)) {
            $this->filledbulk = true;
        } else {
            $this->emptybulk = true;
        }
    }

    public function deleteSelected()
    {
        DB::transaction(function () {
            // Assuming you have a model for the items, e.g., Item::destroy($this->selected)
            AccountType::destroy($this->selected);

            // Reset the selected array after deletion
            $this->selected = [];

            // Optionally add some feedback to the user
            $this->filledbulk = false;
            $this->toast(
                    type: 'error',
                    title: 'Account Types deleted with success',
                    description: null,
                    position: 'toast-top toast-end',
                    icon: 'o-check-badge',
                    css: 'alert alert-danger text-white shadow-lg rounded-sm p-3',
                    timeout: 3000,
                    redirectTo: null
                );
        });
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
        return view('livewire.accounts.account-types', [
            'accountTypes' => $this->accountTypes(),
            'headers' => $this->headers(),
            'selected' => $this->selected,
            'activeFiltersCount' => $this->activeFiltersCount(),
        ]);
    }
}
