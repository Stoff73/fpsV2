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
        Schema::table('liabilities', function (Blueprint $table) {
            // Add ownership_type field after user_id
            $table->enum('ownership_type', ['individual', 'joint', 'trust'])
                ->default('individual')
                ->after('user_id');

            // Add joint_owner_id field after ownership_type
            $table->unsignedBigInteger('joint_owner_id')
                ->nullable()
                ->after('ownership_type');

            // Add trust_id field after joint_owner_id
            $table->unsignedBigInteger('trust_id')
                ->nullable()
                ->after('joint_owner_id');

            // Add foreign key constraints
            $table->foreign('joint_owner_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('trust_id')
                ->references('id')
                ->on('trusts')
                ->onDelete('set null');

            // Add indexes for faster lookups
            $table->index('joint_owner_id');
            $table->index('trust_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('liabilities', function (Blueprint $table) {
            // Drop foreign keys and indexes first
            $table->dropForeign(['joint_owner_id']);
            $table->dropForeign(['trust_id']);
            $table->dropIndex(['joint_owner_id']);
            $table->dropIndex(['trust_id']);

            // Drop columns
            $table->dropColumn(['ownership_type', 'joint_owner_id', 'trust_id']);
        });
    }
};
