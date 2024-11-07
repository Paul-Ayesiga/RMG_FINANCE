<?php

namespace App\Livewire\Staff;

use App\Models\User;
use App\Models\Staff;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Lazy;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Illuminate\Auth\Events\Registered;
use Mary\Traits\Toast;
use Spatie\Permission\Models\Role;

#[Lazy()]
class Overview extends Component
{
    use WithPagination, WithFileUploads;
    use Toast;

    public $search = '';
    public $dateRange = '';
    public $perPage = 1;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    
    // Modals
    public bool $viewModal = false;
    public bool $createModal = false;
    public bool $editModal = false;
    
    // Form Data
    public $selectedStaff = null;
    public $avatar;
    public $name = '';
    public $email = '';
    public $staff_number = '';
    public $password = '';
    public $password_confirmation = '';

    // Add property for role
    public $role = 'staff';

    // Add property for available roles
    public $availableRoles = [];

    // Add this to customize the theme (optional)
    protected $paginationTheme = 'tailwind';

    protected $rules = [
        'name' => 'required|min:3',
        'email' => 'required|email|unique:users,email',
        'staff_number' => 'required|unique:staff,staff_number',
        'password' => 'required|string|confirmed',
        'avatar' => 'nullable|image|max:2000', // 1MB Max
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount()
    {
        // Fetch roles when component is mounted
        $this->loadRoles();
    }

    protected function loadRoles()
    {
        // Fetch all roles except super-admin for security
        $this->availableRoles = Role::where('name', '!=', 'super-admin')
            ->get()
            ->map(function($role) {
                return [
                    'id' => $role->name,
                    'name' => ucfirst($role->name),
                    'description' => $role->description // if you have this field
                ];
            })
            ->toArray();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function viewStaff(Staff $staff)
    {
        $this->selectedStaff = $staff;
        $this->viewModal = true;
    }

    public function create()
    {
        $this->reset(['name', 'email', 'staff_number', 'password', 'password_confirmation', 'avatar', 'role']);
        $this->staff_number = $this->generateUniqueStaffNumber();
        $this->createModal = true;
    }

    protected function generateUniqueStaffNumber()
    {
        do {
            // Generate a random 6-digit number
            $number = 'STF' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Staff::where('staff_number', $number)->exists());

        return $number;
    }

    public function store()
    {
        $this->validate([
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email',
            'staff_number' => 'required|unique:staff,staff_number',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'avatar' => 'nullable|image|max:2048'
        ]);

        try {
            \DB::beginTransaction();

            // Handle avatar upload
            $avatarPath = null;
            if ($this->avatar) {
                $avatarPath = $this->avatar->store('staff', 'public');
            }

            // Create user
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'avatar' => $avatarPath ? Storage::url($avatarPath) : null,
            ]);

            // Assign role
            $user->assignRole($this->role);

            // Create staff record
            Staff::create([
                'user_id' => $user->id,
                'staff_number' => $this->staff_number,
            ]);

            \DB::commit();

            $this->createModal = false;
            $this->reset(['name', 'email', 'staff_number', 'password', 'password_confirmation', 'avatar', 'role']);
            
            $this->success('Staff member created successfully');

        } catch (\Exception $e) {
            \DB::rollBack();
            $this->error('Error creating staff: ' . $e->getMessage());
        }
    }

    public function edit(Staff $staff)
    {
        $this->selectedStaff = $staff;
        $this->name = $staff->user->name;
        $this->email = $staff->user->email;
        $this->staff_number = $staff->staff_number;
        $this->role = $staff->user->roles->first()->name ?? '';
        $this->avatar = null;
        $this->editModal = true;
    }

    public function update()
    {
        // Get valid role names for validation
        $validRoles = collect($this->availableRoles)->pluck('id')->implode(',');
        
        $validationRules = [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email,' . $this->selectedStaff->user_id,
            'staff_number' => 'required|unique:staff,staff_number,' . $this->selectedStaff->id,
            'password' => 'nullable|min:8|confirmed',
            'role' => 'required|in:' . $validRoles // Dynamic role validation
        ];

        // Only validate avatar if a new file is being uploaded
        if ($this->avatar && !is_string($this->avatar)) {
            $validationRules['avatar'] = 'image';
        }

        $this->validate($validationRules);

        try {
            \DB::beginTransaction();

            if ($this->avatar && !is_string($this->avatar)) {
                if ($this->selectedStaff->user->avatar) {
                    Storage::delete(str_replace('/storage/', 'public/', $this->selectedStaff->user->avatar));
                }
                $avatarPath = $this->avatar->store('avatars', 'public');
            }

            $this->selectedStaff->user->update([
                'name' => $this->name,
                'email' => $this->email,
                'avatar' => isset($avatarPath) ? Storage::url($avatarPath) : $this->selectedStaff->user->avatar,
            ]);

            if ($this->password) {
                $this->selectedStaff->user->update([
                    'password' => Hash::make($this->password)
                ]);
            }

            // Update role
            $currentRole = $this->selectedStaff->user->roles->first();
            if ($currentRole && $currentRole->name !== $this->role) {
                $this->selectedStaff->user->removeRole($currentRole);
                $this->selectedStaff->user->assignRole($this->role);
            }

            $this->selectedStaff->update([
                'staff_number' => $this->staff_number,
            ]);

            \DB::commit();
            $this->editModal = false;
            $this->success('Staff updated successfully');

        } catch (\Exception $e) {
            \DB::rollBack();
            $this->error('Error updating staff: ' . $e->getMessage());
        }
    }

    public function delete(Staff $staff)
    {
        if ($staff->user->avatar) {
            Storage::delete(str_replace('/storage/', 'public/', $staff->user->avatar));
        }
        
        $staff->user->delete(); // This will cascade delete the staff record
        
        $this->error('staff deleted successfully');
    }

    public function render()
    {
        $staff = Staff::query()
            ->with(['user', 'user.roles'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('staff_number', 'like', '%' . $this->search . '%')
                      ->orWhereHas('user', function ($userQuery) {
                          $userQuery->where('name', 'like', '%' . $this->search . '%')
                                  ->orWhere('email', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->dateRange, function ($query) {
                if (str_contains($this->dateRange, ' to ')) {
                    [$startDate, $endDate] = explode(' to ', $this->dateRange);
                    $query->whereDate('created_at', '>=', $startDate)
                          ->whereDate('created_at', '<=', $endDate);
                }
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.staff.overview', [
            'staffMembers' => $staff,
            'roles' => $this->availableRoles
        ]);
    }
} 