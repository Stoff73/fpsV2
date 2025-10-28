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
        // MySQL doesn't support modifying ENUM directly with change()
        // We need to use raw SQL to update the ENUM
        DB::statement("ALTER TABLE holdings MODIFY COLUMN asset_type ENUM('equity', 'bond', 'fund', 'etf', 'alternative', 'uk_equity', 'us_equity', 'international_equity', 'cash', 'property') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE holdings MODIFY COLUMN asset_type ENUM('equity', 'bond', 'fund', 'etf', 'alternative') NOT NULL");
    }
};
