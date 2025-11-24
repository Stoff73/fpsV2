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
        // Use raw SQL to modify enum column (avoids Doctrine DBAL enum issues)
        DB::statement("ALTER TABLE dc_pensions MODIFY COLUMN scheme_type ENUM('workplace', 'sipp', 'personal') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE dc_pensions MODIFY COLUMN scheme_type ENUM('workplace', 'sipp', 'personal') NOT NULL");
    }
};
