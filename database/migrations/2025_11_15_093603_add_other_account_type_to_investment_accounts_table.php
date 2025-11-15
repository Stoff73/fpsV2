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
        // Add 'other' to account_type enum and add account_type_other field
        DB::statement("ALTER TABLE investment_accounts MODIFY COLUMN account_type ENUM('isa', 'gia', 'nsi', 'onshore_bond', 'offshore_bond', 'vct', 'eis', 'other') NOT NULL");

        Schema::table('investment_accounts', function (Blueprint $table) {
            $table->string('account_type_other')->nullable()->after('account_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investment_accounts', function (Blueprint $table) {
            $table->dropColumn('account_type_other');
        });

        // Remove 'other' from account_type enum
        DB::statement("ALTER TABLE investment_accounts MODIFY COLUMN account_type ENUM('isa', 'gia', 'nsi', 'onshore_bond', 'offshore_bond', 'vct', 'eis') NOT NULL");
    }
};
