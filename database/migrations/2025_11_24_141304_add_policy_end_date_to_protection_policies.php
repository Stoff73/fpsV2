<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds policy_end_date to all protection policy tables that are missing it.
     * This field is required to track when policies expire.
     */
    public function up(): void
    {
        // Add policy_end_date to critical_illness_policies
        Schema::table('critical_illness_policies', function (Blueprint $table) {
            $table->date('policy_end_date')->nullable()->after('policy_start_date');
        });

        // Add policy_end_date and premium_frequency to income_protection_policies
        Schema::table('income_protection_policies', function (Blueprint $table) {
            $table->date('policy_end_date')->nullable()->after('policy_start_date');
            $table->enum('premium_frequency', ['monthly', 'quarterly', 'annually'])->default('monthly')->after('premium_amount');
        });

        // Add policy_end_date to disability_policies
        Schema::table('disability_policies', function (Blueprint $table) {
            $table->date('policy_end_date')->nullable()->after('policy_start_date');
        });

        // Add policy_end_date to sickness_illness_policies
        Schema::table('sickness_illness_policies', function (Blueprint $table) {
            $table->date('policy_end_date')->nullable()->after('policy_start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('critical_illness_policies', function (Blueprint $table) {
            $table->dropColumn('policy_end_date');
        });

        Schema::table('income_protection_policies', function (Blueprint $table) {
            $table->dropColumn(['policy_end_date', 'premium_frequency']);
        });

        Schema::table('disability_policies', function (Blueprint $table) {
            $table->dropColumn('policy_end_date');
        });

        Schema::table('sickness_illness_policies', function (Blueprint $table) {
            $table->dropColumn('policy_end_date');
        });
    }
};
