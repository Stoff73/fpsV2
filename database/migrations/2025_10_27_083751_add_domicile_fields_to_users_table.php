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
            // Domicile status: UK residence-based system (post-April 2025)
            $table->enum('domicile_status', ['uk_domiciled', 'non_uk_domiciled'])
                ->nullable()
                ->after('marital_status')
                ->comment('UK residence-based domicile status');

            // Country of birth for domicile tracking
            $table->string('country_of_birth', 255)
                ->nullable()
                ->after('domicile_status')
                ->comment('Country where user was born');

            // UK arrival date for tracking deemed domicile (15 of 20 years rule)
            $table->date('uk_arrival_date')
                ->nullable()
                ->after('country_of_birth')
                ->comment('Date user arrived in UK (for non-UK born individuals)');

            // Calculated field: years UK resident
            $table->integer('years_uk_resident')
                ->nullable()
                ->after('uk_arrival_date')
                ->comment('Calculated: number of years UK resident');

            // Date when user became deemed domiciled (if applicable)
            $table->date('deemed_domicile_date')
                ->nullable()
                ->after('years_uk_resident')
                ->comment('Date user became deemed domiciled under 15/20 year rule');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'domicile_status',
                'country_of_birth',
                'uk_arrival_date',
                'years_uk_resident',
                'deemed_domicile_date',
            ]);
        });
    }
};
