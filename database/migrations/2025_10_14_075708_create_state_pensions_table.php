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
        Schema::create('state_pensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('ni_years_completed')->default(0);
            $table->integer('ni_years_required')->default(35);
            $table->decimal('state_pension_forecast_annual', 10, 2)->nullable();
            $table->integer('state_pension_age')->nullable();
            $table->json('ni_gaps')->nullable();
            $table->decimal('gap_fill_cost', 10, 2)->nullable();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('state_pensions');
    }
};
