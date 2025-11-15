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
            // Expenditure mode tracking
            $table->enum('expenditure_entry_mode', ['simple', 'category'])
                ->default('category')
                ->after('other_expenditure')
                ->comment('Whether user uses simple total or detailed category breakdown');

            $table->enum('expenditure_sharing_mode', ['joint', 'separate'])
                ->default('joint')
                ->after('expenditure_entry_mode')
                ->comment('For married users: joint 50/50 split or separate values');

            // New Children & Education category fields
            $table->decimal('school_lunches', 10, 2)
                ->default(0)
                ->after('school_fees')
                ->comment('Monthly school lunch costs');

            $table->decimal('school_extras', 10, 2)
                ->default(0)
                ->after('school_lunches')
                ->comment('Uniforms, trips, equipment etc.');

            $table->decimal('university_fees', 10, 2)
                ->default(0)
                ->after('school_extras')
                ->comment('Includes residential, books and any other costs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'expenditure_entry_mode',
                'expenditure_sharing_mode',
                'school_lunches',
                'school_extras',
                'university_fees',
            ]);
        });
    }
};
