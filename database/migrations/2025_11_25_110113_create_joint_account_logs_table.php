<?php

declare(strict_types=1);

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
        Schema::create('joint_account_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // User who made the edit
            $table->foreignId('joint_owner_id')->constrained('users')->onDelete('cascade'); // The joint owner
            $table->morphs('loggable'); // Polymorphic: Property, Mortgage, InvestmentAccount, SavingsAccount
            $table->json('changes'); // JSON of before/after values
            $table->string('action')->default('update'); // update, create, delete
            $table->timestamps();

            // Index for efficient querying (shortened names for MySQL 64 char limit)
            $table->index(['user_id', 'loggable_type', 'loggable_id'], 'jal_user_loggable_idx');
            $table->index(['joint_owner_id', 'loggable_type', 'loggable_id'], 'jal_joint_owner_loggable_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('joint_account_logs');
    }
};
