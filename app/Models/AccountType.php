<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountType extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'name' ,
        'description',
        'interest_rate',
        'min_balance',
        'max_withdrawal',
        'maturity_period',
        'monthly_deposit',
        'overdraft_limit'
    ];
    
    // protected $guarded = [];

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }
}
