<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add 'trust' to ownership_type enum for all asset tables except ISAs
     * (ISAs can only be individually owned per UK tax rules)
     */
    public function up(): void
    {
        // Tables to modify (all except ISA-specific investment accounts)
        $tables = [
            'properties',
            'savings_accounts',
            'business_interests',
            'cash_accounts',
            'chattels',
        ];

        foreach ($tables as $table) {
            DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `ownership_type` ENUM('individual', 'joint', 'trust') NOT NULL DEFAULT 'individual'");
        }

        // Investment accounts need conditional trust ownership
        // (ISAs can't be in trust, but GIAs, bonds, etc. can)
        DB::statement("ALTER TABLE `investment_accounts` MODIFY COLUMN `ownership_type` ENUM('individual', 'joint', 'trust') NOT NULL DEFAULT 'individual'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'properties',
            'savings_accounts',
            'business_interests',
            'cash_accounts',
            'chattels',
            'investment_accounts',
        ];

        foreach ($tables as $table) {
            DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `ownership_type` ENUM('individual', 'joint') NOT NULL DEFAULT 'individual'");
        }
    }
};
