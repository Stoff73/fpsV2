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
        Schema::create('liabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('liability_type', ['mortgage', 'loan', 'credit_card', 'other']);
            $table->string('liability_name');
            $table->decimal('current_balance', 15, 2);
            $table->decimal('monthly_payment', 10, 2)->nullable();
            $table->decimal('interest_rate', 5, 4)->nullable();
            $table->date('maturity_date')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liabilities');
    }
};
