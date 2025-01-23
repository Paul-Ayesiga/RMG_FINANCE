<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Super admin
        $clintonAccount = User::firstOrCreate([
            'email' => 'ayesigapo@gmail.com',
            'email_verified_at' => Carbon::today(),

        ], [
            'name' => 'Super-Admin',
            'password' => Hash::make('kellyrina2023'),
        ]);
        //assign roles
        $clintonAccount->assignRole('super-admin');
        $clintonAccount->removeRole('customer');

        //Customer admin
        // $paulAccount = User::firstOrCreate([
        //     'email' => 'ayesigapo@gmail.com',
        // ], [
        //     'name' => 'Paul Ayesiga',
        //     'password' => Hash::make('Secret@1'),
        // ]);
        // //assign roles
        // $paulAccount->assignRole('customer');
        // $paulAccount->removeRole('super-admin');

    }
}
