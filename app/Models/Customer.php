<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Customer extends Model
{
    protected $fillable = [
        'user_id',
        'customer_number',
        'date_of_birth',
        'gender',
        'phone_number',
        'address',
        'identification_number',
        'occupation',
        'employer',
        'annual_income',
        'marital_status',
        // Add other fields you want to be fillable
    ];

    protected $guarded=[];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }


    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function loanVotes()
    {
        return $this->hasMany(GroupLoanVote::class);
    }
}

