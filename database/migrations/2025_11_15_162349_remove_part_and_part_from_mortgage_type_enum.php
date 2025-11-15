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
        // Remove 'part_and_part' from mortgage_type enum, keeping only repayment, interest_only, and mixed
        DB::statement("ALTER TABLE mortgages MODIFY COLUMN mortgage_type ENUM('repayment', 'interest_only', 'mixed') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add 'part_and_part' to mortgage_type enum
        DB::statement("ALTER TABLE mortgages MODIFY COLUMN mortgage_type ENUM('repayment', 'interest_only', 'part_and_part', 'mixed') NOT NULL");
    }
};
