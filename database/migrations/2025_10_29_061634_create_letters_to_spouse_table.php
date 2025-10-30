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
        Schema::create('letters_to_spouse', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Part 1: What to do immediately
            $table->text('immediate_actions')->nullable();
            $table->string('executor_name')->nullable();
            $table->string('executor_contact')->nullable();
            $table->string('attorney_name')->nullable();
            $table->string('attorney_contact')->nullable();
            $table->string('financial_advisor_name')->nullable();
            $table->string('financial_advisor_contact')->nullable();
            $table->string('accountant_name')->nullable();
            $table->string('accountant_contact')->nullable();
            $table->text('immediate_funds_access')->nullable();
            $table->string('employer_hr_contact')->nullable();
            $table->text('employer_benefits_info')->nullable();

            // Part 2: Accessing and managing accounts
            $table->text('password_manager_info')->nullable();
            $table->text('phone_plan_info')->nullable();
            $table->text('bank_accounts_info')->nullable();
            $table->text('investment_accounts_info')->nullable();
            $table->text('insurance_policies_info')->nullable();
            $table->text('real_estate_info')->nullable();
            $table->text('vehicles_info')->nullable();
            $table->text('valuable_items_info')->nullable();
            $table->text('cryptocurrency_info')->nullable();
            $table->text('liabilities_info')->nullable();
            $table->text('recurring_bills_info')->nullable();

            // Part 3: Long-term plans
            $table->text('estate_documents_location')->nullable();
            $table->text('beneficiary_info')->nullable();
            $table->text('children_education_plans')->nullable();
            $table->text('financial_guidance')->nullable();
            $table->text('social_security_info')->nullable();

            // Part 4: Funeral and final wishes
            $table->enum('funeral_preference', ['burial', 'cremation', 'not_specified'])->default('not_specified');
            $table->text('funeral_service_details')->nullable();
            $table->text('obituary_wishes')->nullable();
            $table->text('additional_wishes')->nullable();

            $table->timestamps();

            // Index for faster lookups
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letters_to_spouse');
    }
};
