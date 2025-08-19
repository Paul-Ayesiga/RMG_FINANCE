<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends User
{
    public function staffMembers()
    {
        return $this->hasMany(Staff::class, 'admin_id');
    }

    public function managedLoanProducts()
    {
        return $this->hasMany(LoanProduct::class, 'created_by');
    }
}

