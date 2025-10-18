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
            // Spouse linking (we'll add foreign key after households table is created)
            $table->foreignId('spouse_id')->nullable()->after('marital_status');
            $table->foreignId('household_id')->nullable()->after('spouse_id');
            $table->boolean('is_primary_account')->default(true)->after('household_id');

            // Role-based access control
            $table->enum('role', ['user', 'admin'])->default('user')->after('is_primary_account');

            // Additional profile fields
            $table->string('national_insurance_number', 13)->nullable()->after('role');
            $table->string('address_line_1')->nullable()->after('national_insurance_number');
            $table->string('address_line_2')->nullable()->after('address_line_1');
            $table->string('city')->nullable()->after('address_line_2');
            $table->string('county')->nullable()->after('city');
            $table->string('postcode', 10)->nullable()->after('county');
            $table->string('phone')->nullable()->after('postcode');

            // Employment and income
            $table->string('occupation')->nullable()->after('phone');
            $table->string('employer')->nullable()->after('occupation');
            $table->string('industry')->nullable()->after('employer');
            $table->enum('employment_status', ['employed', 'self_employed', 'retired', 'unemployed', 'other'])->nullable()->after('industry');
            $table->decimal('annual_employment_income', 15, 2)->nullable()->after('employment_status');
            $table->decimal('annual_self_employment_income', 15, 2)->nullable()->after('annual_employment_income');
            $table->decimal('annual_rental_income', 15, 2)->nullable()->after('annual_self_employment_income');
            $table->decimal('annual_dividend_income', 15, 2)->nullable()->after('annual_rental_income');
            $table->decimal('annual_other_income', 15, 2)->nullable()->after('annual_dividend_income');

            $table->index('spouse_id');
            $table->index('household_id');
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['spouse_id']);
            $table->dropIndex(['household_id']);
            $table->dropIndex(['role']);

            $table->dropColumn([
                'spouse_id',
                'household_id',
                'is_primary_account',
                'role',
                'national_insurance_number',
                'address_line_1',
                'address_line_2',
                'city',
                'county',
                'postcode',
                'phone',
                'occupation',
                'employer',
                'industry',
                'employment_status',
                'annual_employment_income',
                'annual_self_employment_income',
                'annual_rental_income',
                'annual_dividend_income',
                'annual_other_income',
            ]);
        });
    }
};
