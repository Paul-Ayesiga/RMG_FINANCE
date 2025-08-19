<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppliedTax extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    public function taxable(): MorphTo
    {
        return $this->morphTo();
    }
}
