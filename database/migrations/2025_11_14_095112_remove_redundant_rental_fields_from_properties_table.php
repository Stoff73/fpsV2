<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Remove redundant rental income fields from properties table.
     * We only need monthly_rental_income - annual amounts can be calculated.
     */
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('annual_rental_income');
            $table->dropColumn('occupancy_rate_percent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->decimal('annual_rental_income', 15, 2)->nullable()->after('monthly_rental_income');
            $table->integer('occupancy_rate_percent')->nullable()->after('annual_rental_income');
        });
    }
};
