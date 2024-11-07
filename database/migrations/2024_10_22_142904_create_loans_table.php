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
         Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('loan_product_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->decimal('interest_rate', 8, 2);
            $table->integer('term');
            $table->string('payment_frequency');
            $table->string('status')->default('pending');
            $table->timestamp('disbursement_date')->nullable();
            $table->timestamp('first_payment_date')->nullable();
            $table->timestamp('last_payment_date')->nullable();
            $table->decimal('total_payable', 15, 2);
            $table->decimal('total_interest', 15, 2);
            $table->decimal('processing_fee', 8, 2)->default(0);
            $table->decimal('late_payment_fee', 8, 2)->default(0);
            $table->decimal('early_payment_fee', 8, 2)->default(0);
            $table->foreignId('approved_by')->nullable()->constrained('staff');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('staff');
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
