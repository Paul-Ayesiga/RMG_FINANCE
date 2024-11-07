<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tax extends Model
{
    use HasFactory;

    protected $table = 'taxes';

    // protected $fillable = [
    //     'name',
    //     'rate',
    //     'is_percentage',
    //     'description',
    //     'is_active'
    // ];

    protected $guarded = [];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_percentage' => 'boolean',
        'is_active' => 'boolean'
    ];
} 