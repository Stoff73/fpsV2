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
     * Split the full name field into first_name, middle_name, and last_name
     */
    public function up(): void
    {
        Schema::table('family_members', function (Blueprint $table) {
            $table->string('first_name')->after('relationship')->nullable();
            $table->string('middle_name')->after('first_name')->nullable();
            $table->string('last_name')->after('middle_name')->nullable();
        });

        // Migrate existing data: split name into first and last name (basic approach)
        DB::statement("
            UPDATE family_members
            SET
                first_name = SUBSTRING_INDEX(name, ' ', 1),
                last_name = CASE
                    WHEN LOCATE(' ', name) > 0 THEN SUBSTRING(name, LOCATE(' ', name) + 1)
                    ELSE name
                END
            WHERE name IS NOT NULL AND name != ''
        ");

        // Now make first_name and last_name required
        Schema::table('family_members', function (Blueprint $table) {
            $table->string('first_name')->nullable(false)->change();
            $table->string('last_name')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('family_members', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'middle_name', 'last_name']);
        });
    }
};
