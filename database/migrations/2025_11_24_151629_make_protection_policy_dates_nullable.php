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
        // Critical Illness Policies
        Schema::table('critical_illness_policies', function (Blueprint $table) {
            $table->date('policy_start_date')->nullable()->change();
            $table->integer('policy_term_years')->nullable()->change();
        });

        // Income Protection Policies
        Schema::table('income_protection_policies', function (Blueprint $table) {
            $table->date('policy_start_date')->nullable()->change();
            $table->integer('deferred_period_weeks')->nullable()->change();
        });

        // Disability Policies
        Schema::table('disability_policies', function (Blueprint $table) {
            $table->date('policy_start_date')->nullable()->change();
            $table->integer('deferred_period_weeks')->nullable()->change();
        });

        // Sickness/Illness Policies
        Schema::table('sickness_illness_policies', function (Blueprint $table) {
            $table->date('policy_start_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Critical Illness Policies
        Schema::table('critical_illness_policies', function (Blueprint $table) {
            $table->date('policy_start_date')->nullable(false)->change();
            $table->integer('policy_term_years')->nullable(false)->change();
        });

        // Income Protection Policies
        Schema::table('income_protection_policies', function (Blueprint $table) {
            $table->date('policy_start_date')->nullable(false)->change();
            $table->integer('deferred_period_weeks')->nullable(false)->change();
        });

        // Disability Policies
        Schema::table('disability_policies', function (Blueprint $table) {
            $table->date('policy_start_date')->nullable(false)->change();
            $table->integer('deferred_period_weeks')->nullable(false)->change();
        });

        // Sickness/Illness Policies
        Schema::table('sickness_illness_policies', function (Blueprint $table) {
            $table->date('policy_start_date')->nullable(false)->change();
        });
    }
};
