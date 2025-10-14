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
        Schema::create('protection_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->decimal('annual_income', 15, 2);
            $table->decimal('monthly_expenditure', 10, 2);
            $table->decimal('mortgage_balance', 15, 2)->default(0);
            $table->decimal('other_debts', 15, 2)->default(0);
            $table->integer('number_of_dependents')->default(0);
            $table->json('dependents_ages')->nullable();
            $table->integer('retirement_age')->default(67);
            $table->string('occupation')->nullable();
            $table->boolean('smoker_status')->default(false);
            $table->string('health_status')->default('good');
            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protection_profiles');
    }
};
