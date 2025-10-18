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
        Schema::create('cash_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('household_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('trust_id')->nullable()->constrained()->onDelete('set null');

            // Account details
            $table->string('account_name');
            $table->string('institution_name');
            $table->string('account_number')->nullable();
            $table->string('sort_code', 10)->nullable();

            // Account type and purpose
            $table->enum('account_type', ['current_account', 'savings_account', 'cash_isa', 'fixed_term_deposit', 'ns_and_i', 'other']);
            $table->enum('purpose', ['emergency_fund', 'savings_goal', 'operating_cash', 'other'])->nullable();

            // Ownership
            $table->enum('ownership_type', ['individual', 'joint'])->default('individual');
            $table->decimal('ownership_percentage', 5, 2)->default(100.00);

            // Balance and interest
            $table->decimal('current_balance', 15, 2)->default(0.00);
            $table->decimal('interest_rate', 5, 4)->nullable()->comment('Annual interest rate as decimal');
            $table->date('rate_valid_until')->nullable();

            // ISA tracking
            $table->boolean('is_isa')->default(false);
            $table->decimal('isa_subscription_current_year', 10, 2)->default(0.00);
            $table->string('tax_year', 7)->nullable()->comment('e.g., 2024/25');

            // Additional info
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('household_id');
            $table->index('trust_id');
            $table->index('account_type');
            $table->index('is_isa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_accounts');
    }
};
