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
        Schema::create('group_loan_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_loan_id') // Link to GroupLoan
            ->constrained()
                ->onDelete('cascade');
            $table->foreignId('customer_id') // Link to User
            ->constrained()
                ->onDelete('cascade');
            $table->enum('vote', ['agree', 'disagree'])->default('agree'); // Vote type: agree or disagree
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_loan_votes');
    }
};
