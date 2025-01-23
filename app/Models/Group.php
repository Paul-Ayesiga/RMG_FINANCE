<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $guarded =[];

    public function members()
    {
        return $this->hasMany(GroupMember::class);
    }

    public function groupLoans()
    {
        return $this->hasMany(GroupLoan::class);
    }
    public function groupInsurances()
    {
        return $this->hasMany(GroupInsurance::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'group_members')->withPivot('role');
    }

}
