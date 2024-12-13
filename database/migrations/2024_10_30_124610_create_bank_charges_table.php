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
        Schema::create('bank_charges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['deposit', 'withdraw', 'transfer']);
            $table->decimal('rate', 10, 2);
            $table->boolean('is_percentage')->default(false);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_charges');
    }
};
