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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('customer_number')->unique(); // Unique customer number
            $table->date('date_of_birth')->nullable(); // Customer's date of birth
            $table->enum('gender', ['male', 'female', 'other'])->nullable(); // Customer's gender
            $table->string('phone_number')->nullable(); // Customer's phone number
            $table->text('address')->nullable(); // Full address
            $table->string('identification_number')->unique(); // National ID or equivalent
            $table->string('occupation')->nullable(); // Customer's occupation
            $table->string('employer')->nullable(); // Name of the employer
            $table->decimal('annual_income', 15, 2)->nullable(); // Annual income
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable(); // Marital status
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
