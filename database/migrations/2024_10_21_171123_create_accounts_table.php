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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade'); // Foreign key to customers table
            $table->foreignId('account_type_id')->constrained()->onDelete('cascade'); // Foreign key to account_types table
            $table->string('account_number')->unique(); // Unique account number
            $table->decimal('balance', 20, 2)->default(0); // Current balance
            $table->enum('status', ['pending','active', 'inactive', 'closed'])->default('pending'); // Status of the account
            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
