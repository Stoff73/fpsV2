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
        Schema::table('dc_pensions', function (Blueprint $table) {
            // Add annual_salary field after current_fund_value
            // This is required for workplace pensions to calculate percentage-based contributions
            $table->decimal('annual_salary', 10, 2)->nullable()->after('current_fund_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dc_pensions', function (Blueprint $table) {
            $table->dropColumn('annual_salary');
        });
    }
};
