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
        Schema::create('account_types', function (Blueprint $table) {
            $table->id();
            $table->string('category')->default('default_category')->after('id');
            $table->string('name'); // Name of the account type
            $table->text('description')->nullable(); // Description of the account type
            $table->decimal('interest_rate', 5, 2)->nullable(); // Interest rate (percentage)
            $table->decimal('min_balance', 15, 2)->nullable(); // Minimum balance required
            $table->decimal('max_withdrawal', 15, 2)->nullable(); // Maximum withdrawal limit
            $table->integer('maturity_period')->nullable(); // Maturity period in months (if applicable)
            $table->decimal('monthly_deposit', 15, 2)->nullable(); // Monthly deposit amount (if applicable)
            $table->decimal('overdraft_limit', 15, 2)->nullable(); // Overdraft limit (if applicable)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_types');
    }
};
