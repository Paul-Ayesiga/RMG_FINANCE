<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppliedCharge extends Model
{
    protected $fillable = [
        'bank_charge_id',
        'amount',
        'rate_used',
        'was_percentage'
    ];

    public function chargeable(): MorphTo
    {
        return $this->morphTo();
    }

    public function bankCharge(): BelongsTo
    {
        return $this->belongsTo(BankCharge::class);
    }
}
