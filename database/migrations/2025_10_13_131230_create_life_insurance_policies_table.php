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
        Schema::create('life_insurance_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('policy_type', ['term', 'whole_of_life', 'decreasing_term', 'family_income_benefit', 'level_term'])->default('term');
            $table->string('provider');
            $table->string('policy_number')->nullable();
            $table->decimal('sum_assured', 15, 2);
            $table->decimal('premium_amount', 10, 2);
            $table->enum('premium_frequency', ['monthly', 'quarterly', 'annually'])->default('monthly');
            $table->date('policy_start_date');
            $table->integer('policy_term_years');
            $table->decimal('indexation_rate', 5, 4)->nullable()->default(0);
            $table->boolean('in_trust')->default(false);
            $table->text('beneficiaries')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('life_insurance_policies');
    }
};
