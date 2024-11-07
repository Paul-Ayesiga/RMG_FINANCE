<?php

namespace App\Traits;

use App\Models\Loan;
use Illuminate\Support\Facades\Auth;

trait LoanChecks
{
    public function hasMaxPendingLoans(): bool
    {
        $pendingLoansCount = Loan::where('customer_id', Auth::user()->customer->id)
            ->whereIn('status', ['pending', 'under_review'])
            ->count();

        return $pendingLoansCount >= 2;
    }

    public function getPendingLoansCount(): int
    {
        return Loan::where('customer_id', Auth::user()->customer->id)
            ->whereIn('status', ['pending', 'under_review'])
            ->count();
    }

    public function canApplyForLoan(): array
    {
        if ($this->hasMaxPendingLoans()) {
            return [
                'can_apply' => false,
                'message' => 'You have reached the maximum number of pending loan applications (2). Please wait for the existing applications to be processed.'
            ];
        }

        return [
            'can_apply' => true,
            'message' => ''
        ];
    }
}
