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
            // Make platform_fee_percent nullable to allow empty submissions
            $table->decimal('platform_fee_percent', 5, 4)->nullable()->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investment_accounts', function (Blueprint $table) {
            $table->decimal('platform_fee_percent', 5, 4)->default(0)->change();
        });
    }
};
