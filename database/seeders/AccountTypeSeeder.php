<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AccountType;

class AccountTypeSeeder extends Seeder
{
    public function run()
    {
        $accountTypes = [
            // Checking Accounts
            [
                'category' => 'Checking Accounts',
                'name' => 'Standard Checking',
                'description' => 'Basic account with access to funds via debit card, checks, and online banking.',
                'interest_rate' => 0.01,
                'min_balance' => 500,
                'monthly_deposit' => 0,
                'max_withdrawal' => 10000,
                'maturity_period' => 0,
                'overdraft_limit' => 100
            ],
            [
                'category' => 'Checking Accounts',
                'name' => 'Interest-Bearing Checking',
                'description' => 'Checking accounts that pay interest, with higher balance requirements.',
                'interest_rate' => 0.25,
                'min_balance' => 1500,
                'monthly_deposit' => 0,
                'max_withdrawal' => 15000,
                'maturity_period' => 0,
                'overdraft_limit' => 500
            ],
            // Savings Accounts
            [
                'category' => 'Savings Accounts',
                'name' => 'Basic Savings',
                'description' => 'Standard savings account with a modest interest rate.',
                'interest_rate' => 0.5,
                'min_balance' => 100,
                'monthly_deposit' => 50,
                'max_withdrawal' => 5000,
                'maturity_period' => 0,
                'overdraft_limit' => 0
            ],
            [
                'category' => 'Savings Accounts',
                'name' => 'High-Yield Savings',
                'description' => 'Higher interest rates with larger deposit requirements.',
                'interest_rate' => 2.0,
                'min_balance' => 10000,
                'monthly_deposit' => 1000,
                'max_withdrawal' => 25000,
                'maturity_period' => 0,
                'overdraft_limit' => 0
            ],
            // Certificates of Deposit
            [
                'category' => 'Certificates of Deposit',
                'name' => 'Standard CD',
                'description' => 'Fixed-term deposits with higher interest rates. Early withdrawal penalties apply.',
                'interest_rate' => 3.5,
                'min_balance' => 1000,
                'monthly_deposit' => 0,
                'max_withdrawal' => 0,
                'maturity_period' => 12,
                'overdraft_limit' => 0
            ],
            // Add more account types as needed...
        ];

        foreach ($accountTypes as $type) {
            AccountType::create($type);
        }
    }
} 