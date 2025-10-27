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
        Schema::table('users', function (Blueprint $table) {
            // Monthly expenditure for protection planning and cash flow analysis
            $table->decimal('monthly_expenditure', 15, 2)
                ->nullable()
                ->after('annual_other_income')
                ->comment('Monthly household expenditure');

            // Annual expenditure for comprehensive planning
            $table->decimal('annual_expenditure', 15, 2)
                ->nullable()
                ->after('monthly_expenditure')
                ->comment('Annual household expenditure');

            // Flag to track if user has reviewed liabilities section
            $table->boolean('liabilities_reviewed')
                ->default(false)
                ->after('annual_expenditure')
                ->comment('Whether user has reviewed liabilities (even if zero)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'monthly_expenditure',
                'annual_expenditure',
                'liabilities_reviewed',
            ]);
        });
    }
};
