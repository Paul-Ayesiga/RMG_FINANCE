<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoanProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'interest_rate',
        'minimum_amount',
        'maximum_amount',
        'minimum_term',
        'maximum_term',
        'allowed_frequencies', // weekly, monthly, etc. json
        'processing_fee',
        'late_payment_fee_percentage',
        'early_payment_fee_percentage',
        'status', // active, inactive
        'requirements', // JSON field for required documents
    ];

    protected $casts = [
        'requirements' => 'array',
        'allowed_frequencies' => 'array',
        'minimum_amount' => 'float',
        'maximum_amount' => 'float',
        'minimum_term' => 'integer',
        'maximum_term' => 'integer',
        'interest_rate' => 'float',
        'processing_fee' => 'float',
    ];

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    // Add payment frequency constants
    const FREQUENCY_DAILY = 'daily';
    const FREQUENCY_WEEKLY = 'weekly';
    const FREQUENCY_BIWEEKLY = 'biweekly';
    const FREQUENCY_MONTHLY = 'monthly';
    const FREQUENCY_QUARTERLY = 'quarterly';

    public static function getPaymentFrequencies(): array
    {
        return [
            [
                'id' => 'daily',
                'name' => 'Daily'
            ],
            [
                'id' => 'weekly',
                'name' => 'Weekly'
            ],
            [
                'id' => 'biweekly',
                'name' => 'Bi-weekly'
            ],
            [
                'id' => 'monthly',
                'name' => 'Monthly'
            ],
            [
                'id' => 'quarterly',
                'name' => 'Quarterly'
            ]
        ];
    }
}
