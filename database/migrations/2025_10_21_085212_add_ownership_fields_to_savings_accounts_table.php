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
        Schema::table('savings_accounts', function (Blueprint $table) {
            $table->enum('ownership_type', ['individual', 'joint'])->default('individual')->after('user_id');
            $table->decimal('ownership_percentage', 5, 2)->default(100.00)->after('ownership_type');

            // Add indexes
            $table->index('ownership_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('savings_accounts', function (Blueprint $table) {
            $table->dropIndex(['ownership_type']);
            $table->dropColumn(['ownership_type', 'ownership_percentage']);
        });
    }
};
