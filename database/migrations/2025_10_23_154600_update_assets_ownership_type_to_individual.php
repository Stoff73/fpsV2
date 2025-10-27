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
        // Update ownership_type enum from 'sole' to 'individual' to match other tables
        Schema::table('assets', function (Blueprint $table) {
            $table->enum('ownership_type', ['individual', 'joint', 'trust'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to old enum values
        Schema::table('assets', function (Blueprint $table) {
            $table->enum('ownership_type', ['sole', 'joint', 'trust'])->change();
        });
    }
};
