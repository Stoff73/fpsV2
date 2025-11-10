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
        Schema::table('users', function (Blueprint $table) {
            // Drop old boolean columns
            $table->dropColumn(['good_health', 'smoker']);
        });

        Schema::table('users', function (Blueprint $table) {
            // Add new enum columns with detailed options
            $table->enum('health_status', [
                'yes',
                'no_previous',
                'no_existing',
                'no_both',
            ])->default('yes')->after('occupation');

            $table->enum('smoking_status', [
                'never',
                'quit_recent',
                'quit_long_ago',
                'yes',
            ])->default('never')->after('health_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop new enum columns
            $table->dropColumn(['health_status', 'smoking_status']);
        });

        Schema::table('users', function (Blueprint $table) {
            // Restore old boolean columns
            $table->boolean('good_health')->default(true)->after('occupation');
            $table->boolean('smoker')->default(false)->after('good_health');
        });
    }
};
