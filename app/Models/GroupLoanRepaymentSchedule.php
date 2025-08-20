<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupLoanRepaymentSchedule extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'group_loan_id',
        'due_date',
        'amount',
    ];

    /**
     * Relationship: A repayment schedule belongs to a group loan.
     */
    public function groupLoan()
    {
        return $this->belongsTo(GroupLoan::class);
    }
}
