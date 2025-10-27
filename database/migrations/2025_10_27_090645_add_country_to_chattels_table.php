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
        Schema::table('chattels', function (Blueprint $table) {
            $table->string('country', 255)->default('United Kingdom')->after('ownership_type')->comment('Country where chattel is located');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chattels', function (Blueprint $table) {
            $table->dropColumn('country');
        });
    }
};
