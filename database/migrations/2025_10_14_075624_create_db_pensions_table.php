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
        Schema::create('db_pensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('scheme_name');
            $table->enum('scheme_type', ['final_salary', 'career_average', 'public_sector']);
            $table->decimal('accrued_annual_pension', 10, 2)->default(0.00);
            $table->decimal('pensionable_service_years', 5, 2)->nullable();
            $table->decimal('pensionable_salary', 10, 2)->nullable();
            $table->integer('normal_retirement_age')->nullable();
            $table->string('revaluation_method')->nullable();
            $table->decimal('spouse_pension_percent', 5, 2)->nullable();
            $table->decimal('lump_sum_entitlement', 15, 2)->nullable();
            $table->enum('inflation_protection', ['cpi', 'rpi', 'fixed', 'none'])->default('none');
            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('db_pensions');
    }
};
