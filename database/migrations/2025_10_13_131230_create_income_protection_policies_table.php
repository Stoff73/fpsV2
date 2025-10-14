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
        Schema::create('income_protection_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider');
            $table->string('policy_number')->nullable();
            $table->decimal('benefit_amount', 10, 2);
            $table->enum('benefit_frequency', ['monthly', 'weekly'])->default('monthly');
            $table->integer('deferred_period_weeks');
            $table->integer('benefit_period_months')->nullable();
            $table->decimal('premium_amount', 10, 2);
            $table->string('occupation_class')->nullable();
            $table->date('policy_start_date');
            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('income_protection_policies');
    }
};
