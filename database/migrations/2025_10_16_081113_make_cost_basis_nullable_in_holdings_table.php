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
        Schema::table('holdings', function (Blueprint $table) {
            // Make cost_basis nullable since it's only calculated when prices are provided
            $table->decimal('cost_basis', 15, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('holdings', function (Blueprint $table) {
            // Revert back to NOT NULL
            $table->decimal('cost_basis', 15, 2)->nullable(false)->change();
        });
    }
};
