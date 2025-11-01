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
        Schema::create('rebalancing_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('holding_id')->nullable()->constrained('holdings')->onDelete('set null');
            $table->foreignId('investment_account_id')->nullable()->constrained()->onDelete('set null');

            // Action details
            $table->enum('action_type', ['buy', 'sell'])->index();
            $table->string('security_name');
            $table->string('ticker')->nullable();
            $table->string('isin')->nullable();

            // Trade details
            $table->decimal('shares_to_trade', 15, 6);
            $table->decimal('trade_value', 15, 2);
            $table->decimal('current_price', 15, 4);
            $table->decimal('current_holding', 15, 6)->default(0);

            // Target allocation
            $table->decimal('target_value', 15, 2);
            $table->decimal('target_weight', 5, 4); // e.g., 0.2500 for 25%

            // Priority and rationale
            $table->integer('priority')->default(5); // 1 = highest, 5 = lowest
            $table->text('rationale')->nullable();

            // CGT information
            $table->decimal('cgt_cost_basis', 15, 2)->nullable();
            $table->decimal('cgt_gain_or_loss', 15, 2)->nullable();
            $table->decimal('cgt_liability', 15, 2)->nullable();

            // Execution tracking
            $table->enum('status', ['pending', 'executed', 'cancelled', 'expired'])->default('pending')->index();
            $table->timestamp('executed_at')->nullable();
            $table->decimal('executed_price', 15, 4)->nullable();
            $table->decimal('executed_shares', 15, 6)->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'action_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rebalancing_actions');
    }
};
