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
        Schema::create('retirement_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('current_age');
            $table->integer('target_retirement_age');
            $table->decimal('current_annual_salary', 15, 2)->nullable();
            $table->decimal('target_retirement_income', 15, 2)->nullable();
            $table->decimal('essential_expenditure', 10, 2)->nullable();
            $table->decimal('lifestyle_expenditure', 10, 2)->nullable();
            $table->integer('life_expectancy')->nullable();
            $table->integer('spouse_life_expectancy')->nullable();
            $table->enum('risk_tolerance', ['cautious', 'balanced', 'adventurous'])->default('balanced');
            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retirement_profiles');
    }
};
