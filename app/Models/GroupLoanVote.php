<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupLoanVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_loan_id',
        'customer_id',
        'vote', // 'agree' or 'disagree'
    ];

    public function groupLoan()
    {
        return $this->belongsTo(GroupLoan::class);
    }

    /**
     * Relationship: A vote is cast by a specific user.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
