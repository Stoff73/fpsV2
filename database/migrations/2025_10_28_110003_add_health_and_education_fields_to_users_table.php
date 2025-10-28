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
            // Add health-related fields
            $table->boolean('good_health')->default(true)->after('occupation');
            $table->boolean('smoker')->default(false)->after('good_health');

            // Add education level field
            $table->enum('education_level', [
                'secondary',
                'a_level',
                'undergraduate',
                'postgraduate',
                'professional',
                'other'
            ])->nullable()->after('smoker');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['good_health', 'smoker', 'education_level']);
        });
    }
};
