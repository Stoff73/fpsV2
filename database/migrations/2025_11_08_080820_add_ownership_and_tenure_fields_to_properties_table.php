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
        Schema::table('properties', function (Blueprint $table) {
            // Ownership style: joint_tenancy or tenants_in_common
            // Joint Tenancy: Equal rights to whole property, passes to survivor automatically
            // Tenants in Common: Specified shares, passes via will
            $table->enum('joint_ownership_type', ['joint_tenancy', 'tenants_in_common'])
                ->nullable()
                ->after('ownership_type')
                ->comment('Type of joint ownership - only applicable when ownership_type is joint');

            // Tenure type: freehold or leasehold
            $table->enum('tenure_type', ['freehold', 'leasehold'])
                ->default('freehold')
                ->after('joint_ownership_type')
                ->comment('Property tenure type');

            // Remaining lease term (years) - only for leasehold properties
            $table->unsignedInteger('lease_remaining_years')
                ->nullable()
                ->after('tenure_type')
                ->comment('Remaining years on lease - only for leasehold properties');

            // Lease expiry date - calculated or entered
            $table->date('lease_expiry_date')
                ->nullable()
                ->after('lease_remaining_years')
                ->comment('Lease expiry date - only for leasehold properties');

            // Joint owner name (free text) - for cases where joint owner doesn't have account
            $table->string('joint_owner_name')
                ->nullable()
                ->after('joint_owner_id')
                ->comment('Joint owner name - used when joint owner not in system');

            // Trust name (free text) - for cases where trust not formally registered
            $table->string('trust_name')
                ->nullable()
                ->after('trust_id')
                ->comment('Trust name - used when trust not formally registered in system');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'joint_ownership_type',
                'tenure_type',
                'lease_remaining_years',
                'lease_expiry_date',
                'joint_owner_name',
                'trust_name',
            ]);
        });
    }
};
