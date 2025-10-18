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
            $table->foreignId('household_id')->nullable()->after('user_id')->constrained()->onDelete('set null');
            $table->foreignId('trust_id')->nullable()->after('household_id')->constrained()->onDelete('set null');
            $table->enum('ownership_type', ['individual', 'joint'])->default('individual')->after('trust_id');
            $table->decimal('ownership_percentage', 5, 2)->default(100.00)->after('ownership_type');

            $table->index('household_id');
            $table->index('trust_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investment_accounts', function (Blueprint $table) {
            $table->dropForeign(['household_id']);
            $table->dropForeign(['trust_id']);
            $table->dropIndex(['household_id']);
            $table->dropIndex(['trust_id']);

            $table->dropColumn([
                'household_id',
                'trust_id',
                'ownership_type',
                'ownership_percentage',
            ]);
        });
    }
};
