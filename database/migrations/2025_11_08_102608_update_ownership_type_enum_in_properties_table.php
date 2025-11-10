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
        // MySQL doesn't support modifying ENUMs directly, so we need to use raw SQL
        DB::statement("ALTER TABLE properties MODIFY COLUMN ownership_type ENUM('individual', 'joint', 'tenants_in_common', 'trust') NOT NULL DEFAULT 'individual'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original values (will fail if any rows have tenants_in_common or trust)
        DB::statement("ALTER TABLE properties MODIFY COLUMN ownership_type ENUM('individual', 'joint') NOT NULL DEFAULT 'individual'");
    }
};
