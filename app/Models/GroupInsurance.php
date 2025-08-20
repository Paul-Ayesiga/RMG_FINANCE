<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupInsurance extends Model
{
    use HasFactory;

    protected $fillable = ['group_id', 'insurance_plan_id', 'status'];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function insurancePlan()
    {
        return $this->belongsTo(InsurancePlan::class);
    }
}
