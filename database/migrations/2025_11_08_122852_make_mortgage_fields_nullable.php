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
        Schema::table('mortgages', function (Blueprint $table) {
            // Make fields nullable to match validation rules
            $table->string('lender_name')->nullable()->change();
            $table->string('mortgage_type')->nullable()->change();
            $table->decimal('original_loan_amount', 15, 2)->nullable()->change();
            $table->date('start_date')->nullable()->change();
            $table->date('maturity_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mortgages', function (Blueprint $table) {
            // Revert to NOT NULL
            $table->string('lender_name')->nullable(false)->change();
            $table->string('mortgage_type')->nullable(false)->change();
            $table->decimal('original_loan_amount', 15, 2)->nullable(false)->change();
            $table->date('start_date')->nullable(false)->change();
            $table->date('maturity_date')->nullable(false)->change();
        });
    }
};
