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
        Schema::table('assets', function (Blueprint $table) {
            // Liquidity classification for gifting strategy
            $table->enum('liquidity', ['liquid', 'semi_liquid', 'illiquid'])->default('liquid')->after('current_value');

            // Whether asset can be gifted
            $table->boolean('is_giftable')->default(true)->after('liquidity');

            // Reason if not giftable (e.g., "Main residence - gift with reservation of benefit")
            $table->string('not_giftable_reason')->nullable()->after('is_giftable');

            // Whether this is the main residence (cannot be gifted while living in it)
            $table->boolean('is_main_residence')->default(false)->after('not_giftable_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['liquidity', 'is_giftable', 'not_giftable_reason', 'is_main_residence']);
        });
    }
};
