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
         Schema::create('loan_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('interest_rate', 8, 2);
            $table->decimal('minimum_amount', 15, 2);
            $table->decimal('maximum_amount', 15, 2);
            $table->integer('minimum_term');
            $table->integer('maximum_term');
            $table->json('allowed_frequencies')->nullable();
            $table->decimal('processing_fee', 8, 2)->default(0);
            $table->decimal('late_payment_fee_percentage', 8, 2)->default(0);
            $table->decimal('early_payment_fee_percentage', 8, 2)->default(0);
            $table->string('status')->default('active');
            $table->json('requirements')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_products');
    }
};
