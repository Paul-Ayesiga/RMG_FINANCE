<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Mary\Traits\Toast;
use Livewire\Attributes\Validate;
use Illuminate\Support\Collection;

class RolePermissionManager extends Component
{
    use Toast;

    public $roles;
    public $permissions;
    public $selectedRole = null;
    public Collection $selectedPermissions;
    
    // For creating/editing roles
    public $showRoleModal = false;
    public $editingRole = false;
    
    #[Validate([
        'roleForm.name' => 'required|min:3|unique:roles,name',
        'roleForm.permissions' => 'nullable|array',
        'roleForm.permissions.*' => 'exists:permissions,id'
    ])]
    public $roleForm = [
        'name' => '',
        'permissions' => [],
    ];

    // For creating permissions
    public $showPermissionModal = false;
    #[Validate([
        'permissionForm.name' => 'min:3|unique:permissions,name'
    ])]
    public $permissionForm = [
        'name' => '',
    ];

    // Add a loading property
    public $isLoading = false;

 // Add this method to clear cache when mounting component
public function mount()
{
    // Clear permission cache
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    
    // Ensure super-admin has all permissions
    $superAdmin = Role::where('name', 'super-admin')->first();
    if ($superAdmin) {
        $allPermissions = Permission::all();
        $superAdmin->syncPermissions($allPermissions);
    }
    
    $this->loadRolesAndPermissions();
    $this->selectedPermissions = collect([]);
}

// Update loadRolesAndPermissions to always get fresh data
    public function loadRolesAndPermissions()
    {
    // Clear permission cache
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    
    $this->roles = Role::with('permissions')->get();
        $this->permissions = Permission::orderBy('name')->get();
    }


    public function openRoleModal()
    {
        $this->roleForm = [
            'name' => '',
            'permissions' => [],
        ];
        $this->showRoleModal = true;
    }

    public function createRole()
    {
        // Validate only the role form
        $this->validateOnly('roleForm.name', [
            'roleForm.name' => 'required|min:3|unique:roles,name',
            'roleForm.permissions' => 'nullable|array',
            'roleForm.permissions.*' => 'exists:permissions,id'
        ]);

        try {
            $role = Role::create(['name' => $this->roleForm['name']]);
            
            if (!empty($this->roleForm['permissions'])) {
                $role->givePermissionTo($this->roleForm['permissions']);
            }
            
            $this->loadRolesAndPermissions();
            $this->showRoleModal = false;
            $this->roleForm = [
                'name' => '',
                'permissions' => [],
            ];
            
            $this->toast(
                type: 'success',
                title: 'Role created successfully',
                position: 'toast-top toast-end'
            );
        } catch (\Exception $e) {
            $this->toast(
                type: 'error',
                title: 'Error creating role: ' . $e->getMessage(),
                position: 'toast-top toast-end'
            );
        }
    }

    public function togglePermission($permissionId)
    {
        $this->isLoading = true;
        
        if (!$this->selectedRole) {
            $this->toast(
                type: 'error',
                title: 'Please select a role first',
                position: 'toast-top toast-end'
            );
            $this->isLoading = false;
            return;
        }

        try {
            // Start database transaction
            \DB::beginTransaction();

            if ($this->selectedPermissions->contains($permissionId)) {
                // Remove the permission
                $this->selectedRole->revokePermissionTo($permissionId);
                $this->selectedPermissions = $this->selectedPermissions->filter(fn($id) => $id != $permissionId);
            } else {
                // Add the permission
                $this->selectedRole->givePermissionTo($permissionId);
                $this->selectedPermissions->push($permissionId);
            }

            \DB::commit();
            
            $this->toast(
                type: 'success',
                title: 'Permission updated successfully',
                position: 'toast-top toast-end'
            );
        } catch (\Exception $e) {
            \DB::rollBack();
            
            $this->toast(
                type: 'error',
                title: 'Error updating permission: ' . $e->getMessage(),
                position: 'toast-top toast-end'
            );
            
            // Reload the current role's permissions in case of error
            $this->selectRole($this->selectedRole->id);
        } finally {
            $this->isLoading = false;
        }
    }

    // Also update the selectRole method to ensure we're getting fresh data
    public function selectRole($roleId)
    {
        $this->isLoading = true;
        
        try {
            // Clear cache for this role's permissions
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            
            $this->selectedRole = Role::with('permissions')->findOrFail($roleId);
            $this->selectedPermissions = collect($this->selectedRole->permissions->pluck('id'));
        } catch (\Exception $e) {
            $this->toast(
                type: 'error',
                title: 'Error loading role: ' . $e->getMessage(),
                position: 'toast-top toast-end'
            );
        } finally {
            $this->isLoading = false;
        }
    }

    public function createPermission()
    {
        // Validate only the permission form
        $this->validateOnly('permissionForm.name', [
            'permissionForm.name' => 'required|min:3|unique:permissions,name'
        ]);

        try {
            \DB::beginTransaction();

            // Create the new permission
            $permission = Permission::create(['name' => $this->permissionForm['name']]);

            // Find and assign to super-admin role
            $superAdmin = Role::where('name', 'super-admin')->first();
            if ($superAdmin) {
                $superAdmin->givePermissionTo($permission);
            }

            \DB::commit();

            $this->loadRolesAndPermissions();
            $this->showPermissionModal = false;
            $this->permissionForm = [
                'name' => '',
            ];
            
            $this->toast(
                type: 'success',
                title: 'Permission created and assigned to super-admin',
                position: 'toast-top toast-end'
            );
        } catch (\Exception $e) {
            \DB::rollBack();
            
            $this->toast(
                type: 'error',
                title: 'Error creating permission: ' . $e->getMessage(),
                position: 'toast-top toast-end'
            );
        }
    }

    public function deleteRole($roleId)
    {
        try {
            $role = Role::findOrFail($roleId);
            if ($role->name === 'super-admin') {
                throw new \Exception('Cannot delete super-admin role');
            }
            $role->delete();
            $this->loadRolesAndPermissions();
            $this->selectedRole = null;
            
            $this->toast(
                type: 'success',
                title: 'Role deleted successfully',
                position: 'toast-top toast-end'
            );
        } catch (\Exception $e) {
            $this->toast(
                type: 'error',
                title: 'Error deleting role: ' . $e->getMessage(),
                position: 'toast-top toast-end'
            );
        }
    }

    public function render()
    {
        return view('livewire.admin.role-permission-manager');
    }
}
