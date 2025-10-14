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
        Schema::create('dc_pensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('scheme_name');
            $table->enum('scheme_type', ['workplace', 'sipp', 'personal']);
            $table->string('provider');
            $table->string('member_number')->nullable();
            $table->decimal('current_fund_value', 15, 2)->default(0.00);
            $table->decimal('employee_contribution_percent', 5, 2)->nullable();
            $table->decimal('employer_contribution_percent', 5, 2)->nullable();
            $table->decimal('monthly_contribution_amount', 10, 2)->nullable();
            $table->string('investment_strategy')->nullable();
            $table->decimal('platform_fee_percent', 5, 4)->nullable();
            $table->integer('retirement_age')->nullable();
            $table->decimal('projected_value_at_retirement', 15, 2)->nullable();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dc_pensions');
    }
};
