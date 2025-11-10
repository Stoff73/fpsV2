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
        Schema::create('efficient_frontier_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('calculation_date');
            $table->json('holdings_snapshot'); // Snapshot of holdings at calculation time
            $table->json('frontier_points'); // Array of {return, risk} points on frontier
            $table->json('tangency_portfolio'); // Optimal portfolio (max Sharpe)
            $table->json('min_variance_portfolio'); // Minimum risk portfolio
            $table->json('current_portfolio_position'); // Current allocation on frontier
            $table->decimal('risk_free_rate', 5, 4)->nullable(); // UK Gilts rate
            $table->timestamps();

            $table->index(['user_id', 'calculation_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('efficient_frontier_calculations');
    }
};
