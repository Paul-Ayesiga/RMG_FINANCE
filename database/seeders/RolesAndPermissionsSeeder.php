<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Customer Management
            'view customers',
            'create customers',
            'edit customers',
            'delete customers',
            'approve customers',
            'reject customers',

            // Staff Management
            'view staff',
            'create staff',
            'edit staff',
            'delete staff',
            'assign staff roles',

            // Loan Management
            'view loans',
            'create loans',
            'edit loans',
            'delete loans',
            'approve loans',
            'reject loans',
            'disburse loans',
            'view loan products',
            'create loan products',
            'edit loan products',
            'delete loan products',

            // Account Management
            'view accounts',
            'create accounts',
            'edit accounts',
            'delete accounts',
            'freeze accounts',
            'unfreeze accounts',
            'view account types',
            'create account types',
            'edit account types',
            'delete account types',

            // Transaction Management
            'view transactions',
            'create transactions',
            'edit transactions',
            'delete transactions',
            'approve transactions',
            'reject transactions',
            'reverse transactions',

            // Savings Management
            'view savings',
            'create savings',
            'edit savings',
            'delete savings',
            'view savings products',
            'create savings products',
            'edit savings products',
            'delete savings products',

            // Investment Management
            'view investments',
            'create investments',
            'edit investments',
            'delete investments',
            'view investment products',
            'create investment products',
            'edit investment products',
            'delete investment products',

            // Credit Management
            'view credit reports',
            'create credit reports',
            'edit credit reports',
            'delete credit reports',

            // Customer-specific permissions
            'view personal loans',
            'apply for loans',
            'view personal accounts',
            'make deposits',
            'make withdrawals',
            'make transfers',
            'view personal transactions',
            'view personal savings',
            'create personal savings',
            'view personal investments',
            'create personal investments',
            'view personal credit reports',
            'request credit reports',

            // Report Management
            'view reports',
            'create reports',
            'export reports',
            
            // System Settings
            'view settings',
            'edit settings',
            'manage system parameters',
            'view audit logs',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $superAdminRole = Role::create(['name' => 'super-admin']);
        $superAdminRole->givePermissionTo(Permission::all());

        $staffRole = Role::create(['name' => 'staff']);
        $staffRole->givePermissionTo([
            'view customers',
            'create customers',
            'edit customers',
            'delete customers',
            'view staff',
            'edit staff',
            'view loans',
            'edit loans',
            'approve loans',
            'reject loans',
            'disburse loans',
            'view accounts',
            'edit accounts',
            'freeze accounts',
            'unfreeze accounts',
            'view transactions',
            'approve transactions',
            'reject transactions',
            'view savings',
            'edit savings',
            'view investments',
            'edit investments',
            'view credit reports',
            'view reports',
            'export reports',
        ]);

        $customerRole = Role::create(['name' => 'customer']);
        $customerRole->givePermissionTo([
            'view personal loans',
            'apply for loans',
            'view personal accounts',
            'make deposits',
            'make withdrawals',
            'make transfers',
            'view personal transactions',
            'view personal savings',
            'create personal savings',
            'view personal investments',
            'create personal investments',
            'view personal credit reports',
            'request credit reports',
        ]);

        // Create manager role with elevated permissions
        $managerRole = Role::create(['name' => 'manager']);
        $managerRole->givePermissionTo([
            // All staff permissions plus:
            'create staff',
            'delete staff',
            'assign staff roles',
            'create loan products',
            'edit loan products',
            'create account types',
            'edit account types',
            'reverse transactions',
            'create reports',
            'view audit logs',
            'view settings',
            'edit settings',
        ]);

        // Create auditor role with read-only permissions
        $auditorRole = Role::create(['name' => 'auditor']);
        $auditorRole->givePermissionTo([
            'view customers',
            'view staff',
            'view loans',
            'view accounts',
            'view transactions',
            'view savings',
            'view investments',
            'view credit reports',
            'view reports',
            'view audit logs',
        ]);
    }
}
