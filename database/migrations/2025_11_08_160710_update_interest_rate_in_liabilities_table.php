<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change interest_rate from DECIMAL(5,4) to DECIMAL(5,2) to support percentages up to 999.99%
        DB::statement("ALTER TABLE liabilities MODIFY COLUMN interest_rate DECIMAL(5,2) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to DECIMAL(5,4)
        DB::statement("ALTER TABLE liabilities MODIFY COLUMN interest_rate DECIMAL(5,4) NULL");
    }
};
