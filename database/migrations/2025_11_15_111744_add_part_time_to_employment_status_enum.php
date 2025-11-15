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
        // Modify the employment_status enum to include 'part_time'
        DB::statement("ALTER TABLE users MODIFY COLUMN employment_status ENUM('employed', 'part_time', 'self_employed', 'retired', 'unemployed', 'other') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'part_time' from the enum
        DB::statement("ALTER TABLE users MODIFY COLUMN employment_status ENUM('employed', 'self_employed', 'retired', 'unemployed', 'other') NULL");
    }
};
