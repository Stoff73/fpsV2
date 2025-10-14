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
        Schema::create('critical_illness_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('policy_type', ['standalone', 'accelerated', 'additional'])->default('standalone');
            $table->string('provider');
            $table->string('policy_number')->nullable();
            $table->decimal('sum_assured', 15, 2);
            $table->decimal('premium_amount', 10, 2);
            $table->enum('premium_frequency', ['monthly', 'quarterly', 'annually'])->default('monthly');
            $table->date('policy_start_date');
            $table->integer('policy_term_years');
            $table->json('conditions_covered')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('critical_illness_policies');
    }
};
