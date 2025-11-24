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
        Schema::table('life_insurance_policies', function (Blueprint $table) {
            // Make policy_end_date nullable
            // Whole of life policies don't have an end date - coverage continues until death
            $table->date('policy_end_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('life_insurance_policies', function (Blueprint $table) {
            // Make policy_end_date required again
            $table->date('policy_end_date')->nullable(false)->change();
        });
    }
};
