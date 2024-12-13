<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StandingOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'start_date',
        'end_date',
        'frequency',
        'status',
        'created_by',
        'host_account_id'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function accounts()
    {
        return $this->belongsToMany(Account::class, 'account_standing_order')
        ->withPivot('account_number', 'standing_order_id'); // Include account number for beneficiaries
    }
    public function beneficiaries()
    {
        return $this->belongsToMany(Beneficiary::class, 'account_standing_order')
        ->withPivot('account_number', 'standing_order_id'); // Include account number for beneficiaries
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function host_account()
    {
        return $this->belongsTo(Account::class, 'host_account_id');
    }
}
