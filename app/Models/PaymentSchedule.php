<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'due_date',
        'principal_amount',
        'interest_amount',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'status', // pending, paid, partial, overdue
        'paid_at',
        'late_fee',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'paid_at' => 'datetime',
        'principal_amount' => 'float',
        'interest_amount' => 'float',
        'total_amount' => 'float',
        'paid_amount' => 'float',
        'remaining_amount' => 'float',
        'late_fee' => 'float',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }
}
