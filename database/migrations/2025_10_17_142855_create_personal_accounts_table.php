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
        Schema::create('personal_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Account type and period
            $table->enum('account_type', ['profit_and_loss', 'cashflow', 'balance_sheet']);
            $table->date('period_start');
            $table->date('period_end');

            // Line item details
            $table->string('line_item')->comment('e.g., Employment Income, Mortgage Payment, Cash in Bank');
            $table->enum('category', ['income', 'expense', 'asset', 'liability', 'equity', 'cash_inflow', 'cash_outflow'])->nullable();
            $table->decimal('amount', 15, 2);

            // Additional context
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('account_type');
            $table->index(['period_start', 'period_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_accounts');
    }
};
