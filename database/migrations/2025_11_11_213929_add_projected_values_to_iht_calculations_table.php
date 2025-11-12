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
        Schema::table('iht_calculations', function (Blueprint $table) {
            // Projected values at death
            $table->decimal('projected_gross_assets', 15, 2)->default(0)->after('effective_rate');
            $table->decimal('projected_liabilities', 15, 2)->default(0)->after('projected_gross_assets');
            $table->decimal('projected_net_estate', 15, 2)->default(0)->after('projected_liabilities');
            $table->decimal('projected_taxable_estate', 15, 2)->default(0)->after('projected_net_estate');
            $table->decimal('projected_iht_liability', 15, 2)->default(0)->after('projected_taxable_estate');
            $table->unsignedSmallInteger('years_to_death')->default(0)->after('projected_iht_liability');
            $table->unsignedTinyInteger('estimated_age_at_death')->default(0)->after('years_to_death');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('iht_calculations', function (Blueprint $table) {
            $table->dropColumn([
                'projected_gross_assets',
                'projected_liabilities',
                'projected_net_estate',
                'projected_taxable_estate',
                'projected_iht_liability',
                'years_to_death',
                'estimated_age_at_death',
            ]);
        });
    }
};
