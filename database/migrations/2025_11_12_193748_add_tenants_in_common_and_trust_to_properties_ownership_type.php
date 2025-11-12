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
        // Add 'tenants_in_common' and 'trust' to ownership_type enum
        // Preserving all existing values: individual, joint, tenants_in_common, trust
        DB::statement("ALTER TABLE properties MODIFY COLUMN ownership_type ENUM('individual', 'joint', 'tenants_in_common', 'trust') DEFAULT 'individual'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'tenants_in_common' and 'trust' from ownership_type enum
        // WARNING: This will fail if any properties have these ownership types
        DB::statement("ALTER TABLE properties MODIFY COLUMN ownership_type ENUM('individual', 'joint') DEFAULT 'individual'");
    }
};
