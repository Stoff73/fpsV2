<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL doesn't support ALTER COLUMN for ENUMs directly
        // We need to use raw SQL to modify the enum
        DB::statement("ALTER TABLE users MODIFY COLUMN health_status ENUM('yes', 'yes_previous', 'no_previous', 'no_existing', 'no_both') DEFAULT 'yes'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        // First update any 'yes_previous' values to 'yes' to avoid data loss
        DB::statement("UPDATE users SET health_status = 'yes' WHERE health_status = 'yes_previous'");

        // Then remove the enum value
        DB::statement("ALTER TABLE users MODIFY COLUMN health_status ENUM('yes', 'no_previous', 'no_existing', 'no_both') DEFAULT 'yes'");
    }
};
