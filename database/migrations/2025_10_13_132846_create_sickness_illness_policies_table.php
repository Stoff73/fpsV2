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
        Schema::create('sickness_illness_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider');
            $table->string('policy_number')->nullable();
            $table->decimal('benefit_amount', 10, 2);
            $table->enum('benefit_frequency', ['monthly', 'weekly', 'lump_sum'])->default('lump_sum');
            $table->integer('deferred_period_weeks')->nullable();
            $table->integer('benefit_period_months')->nullable();
            $table->decimal('premium_amount', 10, 2);
            $table->enum('premium_frequency', ['monthly', 'quarterly', 'annually'])->default('monthly');
            $table->date('policy_start_date');
            $table->integer('policy_term_years')->nullable();
            $table->json('conditions_covered')->nullable();
            $table->text('exclusions')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sickness_illness_policies');
    }
};
