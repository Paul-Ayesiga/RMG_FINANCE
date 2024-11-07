<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('applied_charges', function (Blueprint $table) {
            $table->id();
            $table->morphs('chargeable'); // This will create chargeable_type and chargeable_id
            $table->foreignId('bank_charge_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->decimal('rate_used', 10, 2);
            $table->boolean('was_percentage');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applied_charges');
    }
};
