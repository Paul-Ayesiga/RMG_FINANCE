<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function managedLoanProducts()
    {
        return $this->hasMany(Loan_Product::class, 'managed_by');
    }

    public function approvedLoans()
    {
        return $this->hasMany(Loans::class, 'approved_by');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'processed_by');
    }
}
