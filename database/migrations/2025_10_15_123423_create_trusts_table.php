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
        Schema::create('trusts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('trust_name');
            $table->enum('trust_type', [
                'bare',
                'interest_in_possession',
                'discretionary',
                'accumulation_maintenance',
                'life_insurance',
                'discounted_gift',
                'loan',
                'mixed',
                'settlor_interested',
            ]);
            $table->date('trust_creation_date');
            $table->decimal('initial_value', 15, 2);
            $table->decimal('current_value', 15, 2);

            // For Discounted Gift Trusts
            $table->decimal('discount_amount', 15, 2)->nullable()->comment('Actuarial discount for retained income');
            $table->decimal('retained_income_annual', 15, 2)->nullable()->comment('Annual income retained by settlor');

            // For Loan Trusts
            $table->decimal('loan_amount', 15, 2)->nullable()->comment('Outstanding loan balance');
            $table->boolean('loan_interest_bearing')->default(false);
            $table->decimal('loan_interest_rate', 5, 4)->nullable();

            // For Life Insurance Trusts
            $table->decimal('sum_assured', 15, 2)->nullable()->comment('Life insurance policy sum assured');
            $table->decimal('annual_premium', 15, 2)->nullable();

            // IHT tracking
            $table->boolean('is_relevant_property_trust')->default(false)->comment('Subject to 10-year periodic charges');
            $table->date('last_periodic_charge_date')->nullable();
            $table->decimal('last_periodic_charge_amount', 15, 2)->nullable();

            // General fields
            $table->text('beneficiaries')->nullable()->comment('List of beneficiaries');
            $table->text('trustees')->nullable()->comment('List of trustees');
            $table->text('purpose')->nullable()->comment('Purpose of the trust');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('user_id');
            $table->index('trust_type');
            $table->index('is_relevant_property_trust');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trusts');
    }
};
