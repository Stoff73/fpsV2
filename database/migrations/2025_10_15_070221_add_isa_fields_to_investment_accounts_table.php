<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('investment_accounts', function (Blueprint $table) {
            // ISA-specific fields
            $table->string('isa_type', 50)->nullable()->after('platform_fee_percent'); // stocks_and_shares, lifetime, innovative_finance
            $table->decimal('isa_subscription_current_year', 15, 2)->nullable()->default(0)->after('isa_type'); // Amount subscribed this tax year
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investment_accounts', function (Blueprint $table) {
            $table->dropColumn(['isa_type', 'isa_subscription_current_year']);
        });
    }
};
