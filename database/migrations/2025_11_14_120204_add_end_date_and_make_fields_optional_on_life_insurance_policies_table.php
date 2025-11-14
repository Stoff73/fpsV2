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
        Schema::table('life_insurance_policies', function (Blueprint $table) {
            // Add end_date field (required)
            $table->date('policy_end_date')->after('policy_term_years');

            // Make policy_start_date nullable
            $table->date('policy_start_date')->nullable()->change();

            // Make policy_term_years nullable
            $table->integer('policy_term_years')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('life_insurance_policies', function (Blueprint $table) {
            // Remove end_date field
            $table->dropColumn('policy_end_date');

            // Make policy_start_date required again
            $table->date('policy_start_date')->nullable(false)->change();

            // Make policy_term_years required again
            $table->integer('policy_term_years')->nullable(false)->change();
        });
    }
};
