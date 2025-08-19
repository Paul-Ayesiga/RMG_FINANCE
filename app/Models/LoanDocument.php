<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoanDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'document_type',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'status', // pending, approved, rejected
        'notes',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }
}
