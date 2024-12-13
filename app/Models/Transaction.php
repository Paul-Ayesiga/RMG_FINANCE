<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
     protected $fillable = [
        'account_id',
        'type', // deposit, withdrawal, transfer
        'amount',
        'reference_number',
        'status',
        'source_account_id', // for transfers
        'destination_account_id', // for transfers
        'description',
        'charges',
        'taxes',
        'total_amount',
        'charges_breakdown',
        'taxes_breakdown'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
