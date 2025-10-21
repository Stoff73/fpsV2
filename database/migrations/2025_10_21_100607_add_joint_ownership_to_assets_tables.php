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
        // Add joint ownership fields to properties
        Schema::table('properties', function (Blueprint $table) {
            $table->bigInteger('joint_owner_id')->nullable()->after('user_id');
            $table->index('joint_owner_id');
        });

        // Add joint ownership fields to investment_accounts
        Schema::table('investment_accounts', function (Blueprint $table) {
            $table->bigInteger('joint_owner_id')->nullable()->after('user_id');
            $table->index('joint_owner_id');
        });

        // Add joint ownership fields to savings_accounts (cash)
        Schema::table('savings_accounts', function (Blueprint $table) {
            $table->bigInteger('joint_owner_id')->nullable()->after('user_id');
            $table->index('joint_owner_id');
        });

        // Add joint ownership fields to business_interests
        Schema::table('business_interests', function (Blueprint $table) {
            $table->bigInteger('joint_owner_id')->nullable()->after('user_id');
            $table->index('joint_owner_id');
        });

        // Add joint ownership fields to chattels
        Schema::table('chattels', function (Blueprint $table) {
            $table->bigInteger('joint_owner_id')->nullable()->after('user_id');
            $table->index('joint_owner_id');
        });

        // Add joint ownership fields to mortgages
        Schema::table('mortgages', function (Blueprint $table) {
            $table->bigInteger('joint_owner_id')->nullable()->after('user_id');
            $table->index('joint_owner_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex(['joint_owner_id']);
            $table->dropColumn('joint_owner_id');
        });

        Schema::table('investment_accounts', function (Blueprint $table) {
            $table->dropIndex(['joint_owner_id']);
            $table->dropColumn('joint_owner_id');
        });

        Schema::table('savings_accounts', function (Blueprint $table) {
            $table->dropIndex(['joint_owner_id']);
            $table->dropColumn('joint_owner_id');
        });

        Schema::table('business_interests', function (Blueprint $table) {
            $table->dropIndex(['joint_owner_id']);
            $table->dropColumn('joint_owner_id');
        });

        Schema::table('chattels', function (Blueprint $table) {
            $table->dropIndex(['joint_owner_id']);
            $table->dropColumn('joint_owner_id');
        });

        Schema::table('mortgages', function (Blueprint $table) {
            $table->dropIndex(['joint_owner_id']);
            $table->dropColumn('joint_owner_id');
        });
    }
};
