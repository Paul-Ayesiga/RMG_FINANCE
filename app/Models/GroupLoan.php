<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupLoan extends Model
{
    use HasFactory;

    protected $guarded =[];

    public function group(): BelongsTo{
        return $this->belongsTo(Group::class);
    }

    public function repaymentSchedules()
    {
        return $this->hasMany(GroupLoanRepaymentSchedule::class);
    }

    public function votes()
    {
        return $this->hasMany(GroupLoanVote::class);
    }

    public function generateRepaymentSchedule()
    {
        $totalAmount = $this->loan_amount;
        $interest = ($this->loan_amount * $this->interest_rate) / 100;
        $amountWithInterest = $totalAmount + $interest;

        $duration = 12; // e.g., 12 months
        $monthlyPayment = $amountWithInterest / $duration;

        for ($i = 1; $i <= $duration; $i++) {
            $this->repaymentSchedules()->create([
                'due_date' => now()->addMonths($i),
                'amount' => round($monthlyPayment, 2),
            ]);
        }
    }

}
