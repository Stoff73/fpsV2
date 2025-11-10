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
        Schema::table('wills', function (Blueprint $table) {
            // Add executor_name field
            $table->string('executor_name')->nullable()->after('spouse_bequest_percentage');

            // Rename last_reviewed_date to will_last_updated for clarity
            $table->renameColumn('last_reviewed_date', 'will_last_updated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wills', function (Blueprint $table) {
            // Remove executor_name
            $table->dropColumn('executor_name');

            // Rename back to original
            $table->renameColumn('will_last_updated', 'last_reviewed_date');
        });
    }
};
