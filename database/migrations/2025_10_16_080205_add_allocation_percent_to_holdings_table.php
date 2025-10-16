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
        Schema::table('holdings', function (Blueprint $table) {
            // Add allocation_percent field (percentage of account value)
            $table->decimal('allocation_percent', 5, 2)->after('asset_type')->nullable();

            // Make quantity nullable (not required if using allocation_percent)
            $table->decimal('quantity', 15, 6)->nullable()->change();

            // Make purchase_price and current_price nullable (optional fields)
            $table->decimal('purchase_price', 15, 4)->nullable()->change();
            $table->decimal('current_price', 15, 4)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('holdings', function (Blueprint $table) {
            $table->dropColumn('allocation_percent');

            // Revert nullable changes
            $table->decimal('quantity', 15, 6)->nullable(false)->change();
            $table->decimal('purchase_price', 15, 4)->nullable(false)->change();
            $table->decimal('current_price', 15, 4)->nullable(false)->change();
        });
    }
};
