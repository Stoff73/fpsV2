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
        Schema::table('trusts', function (Blueprint $table) {
            // Add household_id for multi-user support
            $table->foreignId('household_id')->nullable()->after('user_id')->constrained()->onDelete('set null');

            // Add fields for comprehensive trust tracking
            $table->date('last_valuation_date')->nullable()->after('current_value');
            $table->date('next_tax_return_due')->nullable()->after('last_periodic_charge_amount');
            $table->decimal('total_asset_value', 15, 2)->nullable()->after('next_tax_return_due')->comment('Aggregated value of all assets held in trust');

            $table->index('household_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trusts', function (Blueprint $table) {
            $table->dropForeign(['household_id']);
            $table->dropIndex(['household_id']);

            $table->dropColumn([
                'household_id',
                'last_valuation_date',
                'next_tax_return_due',
                'total_asset_value',
            ]);
        });
    }
};
