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
        Schema::create('risk_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('calculation_date');
            $table->decimal('portfolio_value', 15, 2);
            $table->decimal('var_95_1month', 15, 2)->nullable(); // VaR 95% confidence, 1-month
            $table->decimal('cvar_95_1month', 15, 2)->nullable(); // CVaR 95%, 1-month
            $table->decimal('var_99_1month', 15, 2)->nullable(); // VaR 99%, 1-month
            $table->decimal('cvar_99_1month', 15, 2)->nullable(); // CVaR 99%, 1-month
            $table->decimal('max_drawdown', 5, 2)->nullable(); // Maximum drawdown %
            $table->decimal('current_drawdown', 5, 2)->nullable(); // Current drawdown %
            $table->decimal('sharpe_ratio', 6, 4)->nullable(); // Sharpe ratio
            $table->decimal('sortino_ratio', 6, 4)->nullable(); // Sortino ratio
            $table->decimal('calmar_ratio', 6, 4)->nullable(); // Calmar ratio
            $table->decimal('information_ratio', 6, 4)->nullable(); // Information ratio
            $table->decimal('treynor_ratio', 6, 4)->nullable(); // Treynor ratio
            $table->timestamps();

            $table->index(['user_id', 'calculation_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_metrics');
    }
};
