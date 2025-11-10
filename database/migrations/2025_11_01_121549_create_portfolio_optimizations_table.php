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
        Schema::create('portfolio_optimizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('optimization_date');
            $table->string('optimization_type'); // min_variance, max_sharpe, risk_parity, etc.
            $table->json('current_allocation'); // Current portfolio weights
            $table->json('optimal_allocation'); // Recommended weights
            $table->json('rebalancing_actions'); // Specific buy/sell trades
            $table->json('constraints_used'); // Constraints applied (min/max, sector limits)
            $table->decimal('expected_return', 6, 4)->nullable(); // Expected return of optimal
            $table->decimal('expected_risk', 6, 4)->nullable(); // Expected std dev of optimal
            $table->decimal('expected_sharpe', 6, 4)->nullable(); // Expected Sharpe ratio
            $table->decimal('improvement_vs_current', 6, 4)->nullable(); // Sharpe improvement
            $table->string('status')->default('pending'); // pending, accepted, rejected
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'optimization_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portfolio_optimizations');
    }
};
