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
            // Drop the old enum constraint and recreate with all liability types
            $table->dropColumn('liability_type');
        });

        Schema::table('liabilities', function (Blueprint $table) {
            $table->enum('liability_type', [
                'mortgage',
                'secured_loan',
                'personal_loan',
                'credit_card',
                'overdraft',
                'hire_purchase',
                'student_loan',
                'business_loan',
                'other',
            ])->after('user_id');

            // Add missing columns
            $table->string('secured_against')->nullable()->after('maturity_date');
            $table->boolean('is_priority_debt')->default(false)->after('secured_against');
            $table->string('mortgage_type', 50)->nullable()->after('is_priority_debt');
            $table->date('fixed_until')->nullable()->after('mortgage_type');
            $table->text('notes')->nullable()->after('fixed_until');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('liabilities', function (Blueprint $table) {
            $table->dropColumn([
                'secured_against',
                'is_priority_debt',
                'mortgage_type',
                'fixed_until',
                'notes',
            ]);
        });

        Schema::table('liabilities', function (Blueprint $table) {
            $table->dropColumn('liability_type');
        });

        Schema::table('liabilities', function (Blueprint $table) {
            $table->enum('liability_type', ['mortgage', 'loan', 'credit_card', 'other'])->after('user_id');
        });
    }
};
