<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration was missing from the Nov 24 patch. The provider column
     * was made nullable via manual SQL ALTER TABLE but no migration was created.
     * This caused DC pension creation to fail with "Column 'provider' cannot be null"
     * after running migrate:fresh.
     */
    public function up(): void
    {
        Schema::table('dc_pensions', function (Blueprint $table) {
            $table->string('provider', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dc_pensions', function (Blueprint $table) {
            $table->string('provider', 255)->nullable(false)->change();
        });
    }
};
