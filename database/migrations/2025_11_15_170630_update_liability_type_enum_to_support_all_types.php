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
        // Update liability_type enum to support all granular types used in the form
        DB::statement("ALTER TABLE liabilities MODIFY COLUMN liability_type ENUM('mortgage', 'secured_loan', 'personal_loan', 'credit_card', 'overdraft', 'hire_purchase', 'student_loan', 'business_loan', 'other') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original 4 canonical types
        // Note: This may fail if there are records with the granular types
        DB::statement("ALTER TABLE liabilities MODIFY COLUMN liability_type ENUM('mortgage', 'loan', 'credit_card', 'other') NOT NULL");
    }
};
