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
        Schema::create('group_loan_repayment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_loan_id')->constrained()->onDelete('cascade'); // Links to group loans
            $table->date('due_date'); // Repayment due date
            $table->decimal('amount', 10, 2); // Amount for each repayment
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_loan_repayment_schedules');
    }
};
