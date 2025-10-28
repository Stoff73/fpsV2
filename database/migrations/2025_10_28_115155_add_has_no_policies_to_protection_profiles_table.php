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
        Schema::table('protection_profiles', function (Blueprint $table) {
            $table->boolean('has_no_policies')->default(false)->after('health_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('protection_profiles', function (Blueprint $table) {
            $table->dropColumn('has_no_policies');
        });
    }
};
