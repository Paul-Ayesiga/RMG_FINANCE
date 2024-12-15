<?php

namespace App\Livewire\Clients;

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
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Computed;
use Illuminate\Validation\Rules;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;

#[Lazy()]
class Index extends Component
{
    use Toast;
    use WithPagination;
    use WithFileUploads, WithMediaSync;

    public $activeTab = 'basicInfo';
    public $tabs = ['basicInfo', 'moreDetails', 'profileImage', 'addresses', 'payments'];

    public ?Customer $customer;

    public $search = '';
    public array $sortBy = ['column' => 'id', 'direction' => 'asc'];
    public $perPage = 5;
    public array $selected = [];

    public bool $filledbulk = false;
    public bool $emptybulk = false;
    public bool $addCustomerDrawer = false;
    public bool $previewCustomerModal = false;
    public bool $deleteCustomerModal = false;

    public array $activeFilters = [];

    #[Computed]
    public $customerPreview;

    #[Computed]
    public $customerToDelete = null;

    // New fields from your customer creation form
    // #[Validate('required')]
    public $phone_number;

    // #[Validate('required')]
    public $address;

    #[Validate('nullable')]
    public $secondaryAdress;

    // #[Validate('required')]
    public $gender;

    // #[Validate('required')]
    public $maritalStatus;

    // #[Validate('required')]
    public $birthDate;

    #[Validate('required')]
    public $identification_number;

    // #[Validate('required')]
    public $occupation;

    // #[Validate('required')]
    public $employer;

    // #[Validate('required')]
    public $annual_income;

    // user fields
    #[Validate('required')]
    public $name;

    #[Validate('required')]
    public $email;

    #[Validate('nullable')]
    public $photo;
    public string $userRole = 'customer';
    public string $password = '';
    public string $password_confirmation = '';

    public $columns = [
        // 'user.avatar' => true,
        'id' => true,
        'user.name' => true,
        'user.email' => true,
        'phone_number' => true,
        'address' => true,
    ];


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
            // ['key' => 'user.avatar', 'label' => 'Photo', 'class' => 'w-1'],
            ['key' => 'id', 'label' => '#'],
            ['key' => 'user.name', 'label' => 'Name'],
            ['key' => 'user.email', 'label' => 'Email'],
            ['key' => 'phone_number', 'label' => 'Phone No'],
            ['key' => 'address', 'label' => 'Address'],
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

    public function customers(): LengthAwarePaginator
    {
        return Customer::with('user') // Eager load the User relationship
            ->when($this->search, fn(Builder $q) => $q->whereHas('user', function ($query) {
                $query->where('name', 'like', "%$this->search%");
            }))
            ->orderBy(...array_values($this->sortBy))
            ->paginate($this->perPage);
    }

    #[On('reset')]
    public function resetForm()
    {
        $this->name = null;
        $this->email = null;
        $this->phone_number = null;
        $this->address = null;
        $this->photo = null;
        // Reset additional fields
        $this->gender = null;
        $this->maritalStatus = null;
        $this->birthDate = null;
        $this->identification_number = null;
        $this->occupation = null;
        $this->employer = null;
        $this->annual_income = null;
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'photo' => ['nullable','image','max:1024'],
            'userRole' => ['required'],
            // 'accepted' => ['required'] // Validate the acceptance of terms
        ]);

        $this->validate();

        DB::beginTransaction();

        try {
            $validated['password'] = Hash::make($validated['password']);

            $user = User::create($validated);
            event(new Registered($user));

            $user->assignRole('customer');

            if ($this->photo) {
                $url = $this->photo->store('users', 'public');
                $user->update(['avatar' => "/storage/$url"]);
            }

            $this->dispatch('profile-updated', name: $user->name);

            $customer = Customer::create([
                'user_id' => $user->id,
                'customer_number' => $this->generateCustomerNumber(),
                'phone_number' => $this->phone_number,
                'address' => $this->address,
                'gender' => $this->gender, // Save new field
                'marital_status' => $this->maritalStatus, // Save new field
                'date_of_birth' => $this->birthDate, // Save new field
                'identification_number' => $this->identification_number, // Save new field
                'occupation' => $this->occupation, // Save new field
                'employer' => $this->employer, // Save new field
                'annual_income' => $this->annual_income, // Save new field
            ]);

            DB::commit();

            $this->addCustomerDrawer = false;
            $this->resetForm();
            $this->toast(
                    type: 'success',
                    title: 'Client created with success',
                    description: null,
                    position: 'toast-top toast-end',
                    icon: 'o-check-badge',
                    css: 'alert alert-success text-white shadow-lg rounded-sm p-3',
                    timeout: 3000,
                    redirectTo: null
                );
        } catch (\Exception $e) {
            DB::rollBack();
            $this->toast(
                type: 'error',
                title: 'Failed to create client',
                description: $e->getMessage(),
                position: 'toast-top toast-end',
                icon: 'o-x-circle',
                css: 'alert alert-error text-white shadow-lg rounded-sm p-3',
                timeout: 3000,
                redirectTo: null
            );
        }
    }

    private function generateCustomerNumber(): string
    {
        // Get the last customer number, if it exists
        $lastCustomer = Customer::orderBy('id', 'desc')->first();

        // Extract the number part from the last customer number
        $lastNumber = $lastCustomer ? (int)str_replace(['RMG#', '-'], '', $lastCustomer->customer_number) : 0;

        // Generate a new unique number by incrementing the last number
        $nextNumber = $lastNumber + 1;

        // Get the current year for added uniqueness
        $year = date('Y');

        // Generate a random string (e.g., 3 characters) for added complexity
        $randomString = strtoupper(substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 3));

        // Create the new customer number
        return 'RMG#' . $year . '-' . $nextNumber . '-' . $randomString;
    }

    public function OpenPreviewCustomerModal($id)
    {
        $customer = Customer::with('user')->findOrFail($id);
        $this->customerPreview = $customer;
        $this->previewCustomerModal = true;
    }

    public function OpenDeleteCustomerModal($id)
    {
        $this->customerToDelete = $id;
        $this->deleteCustomerModal = true;
    }

    public function confirmCustomerdelete($id)
    {
        try {
            // Begin a database transaction
            DB::beginTransaction();

            $customer = Customer::findOrFail($id);

            // Delete all related records
            $customer->user()->delete();
            // $customer->loans()->delete();
            // Add more relationship deletions as needed

            // Delete the associated user
            if ($customer->user) {
                $customer->user->delete();
            }

            // Finally, delete the customer
            $customer->delete();

            // Commit the transaction
            DB::commit();

            $this->deleteCustomerModal = false;
            $this->resetPage();
            $this->toast(
                type: 'success',
                title: 'Client and all related data deleted permanently',
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

            $this->toast(
                type: 'error',
                title: 'Failed to delete client',
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
        Customer::destroy($this->selected);

        $this->selected = [];
        $this->filledbulk = false;
        $this->success('Selected Customers deleted successfully');
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

// tab switching
    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function next($currentTab)
    {
        $index = array_search($currentTab, $this->tabs);
        if ($this->validateCurrentTab($currentTab) && isset($this->tabs[$index + 1])) {
            $this->activeTab = $this->tabs[$index + 1];
        }
    }

    public function previous($currentTab)
    {
        $index = array_search($currentTab, $this->tabs);
        if (isset($this->tabs[$index - 1])) {
            $this->activeTab = $this->tabs[$index - 1];
        }
    }

    private function validateCurrentTab($currentTab)
    {
        switch ($currentTab) {
            case 'basicInfo':
                $this->validate([
                    'name' => 'required|string',
                    'email' => 'required|email',
                    'password' => 'required|string|confirmed',
                    'phone_number' => 'required|string',
                ]);
                break;
            case 'moreDetails':
                $this->validate([
                    'gender' => 'required',
                    'maritalStatus' => 'required',
                    'birthDate' => 'required|date',
                    'identification_number' => 'required|string',
                ]);
                break;
            case 'profileImage':
                $this->validate([
                    'photo' => 'required|image',
                ]);
                break;
            case 'addresses':
                $this->validate([
                    'address' => 'required',
                ]);
                break;
            case 'payments':
                $this->validate([
                    'paymentMethod' => 'nullable',
                    'cardNumber' => 'nullable',
                ]);
                break;
            // case 'notes':
            // Add more validation rules for other tabs
        }
        return true;
    }
// end of tab switching

    public function render()
    {
        return view('livewire.clients.index', [
            'customers' => $this->customers(),
            'headers' => $this->headers(),
            'selected' => $this->selected,
            'activeFiltersCount' => $this->activeFiltersCount(),
            'customerPreview' => $this->customerPreview,
        ]);
    }
}
