<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beneficiary extends Model
{
    use HasFactory;

    protected $fillable = ['account_id', 'user_id', 'nickname','bank_name','account_number'];

    // Relationship with the Account model
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    // Relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
