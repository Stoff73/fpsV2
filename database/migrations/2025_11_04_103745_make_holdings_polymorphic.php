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
        Schema::table('holdings', function (Blueprint $table) {
            // Add polymorphic columns
            $table->unsignedBigInteger('holdable_id')->nullable()->after('id');
            $table->string('holdable_type')->nullable()->after('holdable_id');

            // Add index for polymorphic relationship
            $table->index(['holdable_type', 'holdable_id']);
        });

        // Migrate existing data: investment_account_id -> polymorphic
        DB::statement("
            UPDATE holdings
            SET holdable_id = investment_account_id,
                holdable_type = 'App\\\\Models\\\\Investment\\\\InvestmentAccount'
            WHERE investment_account_id IS NOT NULL
        ");

        Schema::table('holdings', function (Blueprint $table) {
            // Make polymorphic columns non-nullable now that data is migrated
            $table->unsignedBigInteger('holdable_id')->nullable(false)->change();
            $table->string('holdable_type')->nullable(false)->change();

            // Drop old column and foreign key
            $table->dropForeign(['investment_account_id']);
            $table->dropColumn('investment_account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('holdings', function (Blueprint $table) {
            // Re-add investment_account_id column
            $table->unsignedBigInteger('investment_account_id')->nullable()->after('id');
        });

        // Migrate data back from polymorphic to investment_account_id
        DB::statement("
            UPDATE holdings
            SET investment_account_id = holdable_id
            WHERE holdable_type = 'App\\\\Models\\\\Investment\\\\InvestmentAccount'
        ");

        Schema::table('holdings', function (Blueprint $table) {
            // Make investment_account_id non-nullable
            $table->unsignedBigInteger('investment_account_id')->nullable(false)->change();

            // Re-add foreign key
            $table->foreign('investment_account_id')
                ->references('id')
                ->on('investment_accounts')
                ->onDelete('cascade');

            // Drop polymorphic columns
            $table->dropIndex(['holdable_type', 'holdable_id']);
            $table->dropColumn(['holdable_id', 'holdable_type']);
        });
    }
};
