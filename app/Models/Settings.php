<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $fillable = [
        'user_id',
        'currency',
        'language',
        'timezone',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
