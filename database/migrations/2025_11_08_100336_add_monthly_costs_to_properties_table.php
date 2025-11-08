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
        Schema::table('properties', function (Blueprint $table) {
            // Monthly utility costs
            $table->decimal('monthly_council_tax', 10, 2)->nullable()->after('lease_end_date');
            $table->decimal('monthly_gas', 10, 2)->nullable()->after('monthly_council_tax');
            $table->decimal('monthly_electricity', 10, 2)->nullable()->after('monthly_gas');
            $table->decimal('monthly_water', 10, 2)->nullable()->after('monthly_electricity');

            // Monthly insurance costs
            $table->decimal('monthly_building_insurance', 10, 2)->nullable()->after('monthly_water');
            $table->decimal('monthly_contents_insurance', 10, 2)->nullable()->after('monthly_building_insurance');

            // Monthly property costs
            $table->decimal('monthly_service_charge', 10, 2)->nullable()->after('monthly_contents_insurance');
            $table->decimal('monthly_maintenance_reserve', 10, 2)->nullable()->after('monthly_service_charge');
            $table->decimal('other_monthly_costs', 10, 2)->nullable()->after('monthly_maintenance_reserve');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'monthly_council_tax',
                'monthly_gas',
                'monthly_electricity',
                'monthly_water',
                'monthly_building_insurance',
                'monthly_contents_insurance',
                'monthly_service_charge',
                'monthly_maintenance_reserve',
                'other_monthly_costs',
            ]);
        });
    }
};
