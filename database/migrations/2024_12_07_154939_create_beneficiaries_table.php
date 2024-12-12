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
        Schema::create('beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id')->nullable(); // References the account being saved as a beneficiary
            $table->unsignedBigInteger('user_id'); // References the user who saved the beneficiary
            $table->string('nickname')->nullable(); // Optional nickname for the beneficiary
            $table->string('bank_name')->nullable(); // Optional nickname for the beneficiary
            $table->string('account_number')->nullable(); // Optional nickname for the beneficiary
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiaries');
    }
};
