<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankCharge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type', // deposit, withdraw, transfer
        'rate',
        'is_percentage',
        'description',
        'is_active'
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_percentage' => 'boolean',
        'is_active' => 'boolean'
    ];
}
