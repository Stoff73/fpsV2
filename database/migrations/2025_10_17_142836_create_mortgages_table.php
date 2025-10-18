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
        Schema::create('mortgages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Lender information
            $table->string('lender_name');
            $table->string('mortgage_account_number')->nullable();

            // Mortgage terms
            $table->enum('mortgage_type', ['repayment', 'interest_only', 'part_and_part']);
            $table->decimal('original_loan_amount', 15, 2);
            $table->decimal('outstanding_balance', 15, 2);
            $table->decimal('interest_rate', 5, 4)->comment('Annual interest rate as decimal, e.g., 3.5% = 3.5000');
            $table->enum('rate_type', ['fixed', 'variable', 'tracker', 'discount']);
            $table->date('rate_fix_end_date')->nullable()->comment('Date when fixed rate ends');

            // Payment details
            $table->decimal('monthly_payment', 10, 2);
            $table->date('start_date');
            $table->date('maturity_date');
            $table->integer('remaining_term_months');

            // Additional info
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('property_id');
            $table->index('user_id');
            $table->index('mortgage_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mortgages');
    }
};
