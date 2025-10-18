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
        Schema::create('business_interests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('household_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('trust_id')->nullable()->constrained()->onDelete('set null');

            // Business details
            $table->string('business_name');
            $table->string('company_number')->nullable()->comment('Companies House registration number');
            $table->enum('business_type', ['sole_trader', 'partnership', 'limited_company', 'llp', 'other']);
            $table->enum('ownership_type', ['individual', 'joint'])->default('individual');
            $table->decimal('ownership_percentage', 5, 2)->default(100.00);

            // Valuation
            $table->decimal('current_valuation', 15, 2);
            $table->date('valuation_date');
            $table->string('valuation_method')->nullable()->comment('e.g., Market value, Book value, Expert valuation');

            // Financial metrics
            $table->decimal('annual_revenue', 15, 2)->nullable();
            $table->decimal('annual_profit', 15, 2)->nullable();
            $table->decimal('annual_dividend_income', 15, 2)->nullable()->comment('Dividend income received from this business');

            // Additional info
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('household_id');
            $table->index('trust_id');
            $table->index('business_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_interests');
    }
};
