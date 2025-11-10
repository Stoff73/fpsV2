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
        Schema::table('mortgages', function (Blueprint $table) {
            // Change from DECIMAL(5,4) to DECIMAL(5,2) for proper percentage storage
            // Interest rates are percentages: 3.50%, 15.00%, etc.
            // DECIMAL(5,2) allows max 999.99% which is more than sufficient
            $table->decimal('interest_rate', 5, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mortgages', function (Blueprint $table) {
            // Revert to DECIMAL(5,4)
            $table->decimal('interest_rate', 5, 4)->nullable()->change();
        });
    }
};
