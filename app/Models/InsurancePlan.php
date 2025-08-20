<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsurancePlan extends Model
{

    use HasFactory;

    protected $fillable = ['name', 'description', 'coverage_amount', 'premium'];

    public function groupInsurances()
    {
        return $this->hasMany(GroupInsurance::class);
    }
}
