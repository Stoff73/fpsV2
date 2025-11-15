<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'mixed' to mortgage_type enum
        DB::statement("ALTER TABLE mortgages MODIFY COLUMN mortgage_type ENUM('repayment', 'interest_only', 'part_and_part', 'mixed') NOT NULL");

        // Add 'mixed' to rate_type enum
        DB::statement("ALTER TABLE mortgages MODIFY COLUMN rate_type ENUM('fixed', 'variable', 'tracker', 'discount', 'mixed') NOT NULL");

        Schema::table('mortgages', function (Blueprint $table) {
            // Mixed mortgage type fields (repayment vs interest-only split)
            $table->decimal('repayment_percentage', 5, 2)->nullable()->after('mortgage_type')
                ->comment('Percentage of mortgage on repayment basis (0-100)');
            $table->decimal('interest_only_percentage', 5, 2)->nullable()->after('repayment_percentage')
                ->comment('Percentage of mortgage on interest-only basis (0-100)');

            // Mixed rate type fields (fixed vs variable split)
            $table->decimal('fixed_rate_percentage', 5, 2)->nullable()->after('rate_type')
                ->comment('Percentage of mortgage at fixed rate (0-100)');
            $table->decimal('variable_rate_percentage', 5, 2)->nullable()->after('fixed_rate_percentage')
                ->comment('Percentage of mortgage at variable rate (0-100)');

            // Separate interest rates for mixed rate mortgages
            $table->decimal('fixed_interest_rate', 5, 4)->nullable()->after('variable_rate_percentage')
                ->comment('Interest rate for fixed portion (annual rate as decimal)');
            $table->decimal('variable_interest_rate', 5, 4)->nullable()->after('fixed_interest_rate')
                ->comment('Interest rate for variable portion (annual rate as decimal)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mortgages', function (Blueprint $table) {
            $table->dropColumn([
                'repayment_percentage',
                'interest_only_percentage',
                'fixed_rate_percentage',
                'variable_rate_percentage',
                'fixed_interest_rate',
                'variable_interest_rate',
            ]);
        });

        // Remove 'mixed' from mortgage_type enum
        DB::statement("ALTER TABLE mortgages MODIFY COLUMN mortgage_type ENUM('repayment', 'interest_only', 'part_and_part') NOT NULL");

        // Remove 'mixed' from rate_type enum
        DB::statement("ALTER TABLE mortgages MODIFY COLUMN rate_type ENUM('fixed', 'variable', 'tracker', 'discount') NOT NULL");
    }
};
