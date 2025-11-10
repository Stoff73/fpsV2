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
            // Change all expenditure fields from DECIMAL to DOUBLE
            $table->double('monthly_expenditure')->nullable()->change();
            $table->double('annual_expenditure')->nullable()->change();
            $table->double('food_groceries')->default(0)->change();
            $table->double('transport_fuel')->default(0)->change();
            $table->double('healthcare_medical')->default(0)->change();
            $table->double('insurance')->default(0)->change();
            $table->double('mobile_phones')->default(0)->change();
            $table->double('internet_tv')->default(0)->change();
            $table->double('subscriptions')->default(0)->change();
            $table->double('clothing_personal_care')->default(0)->change();
            $table->double('entertainment_dining')->default(0)->change();
            $table->double('holidays_travel')->default(0)->change();
            $table->double('pets')->default(0)->change();
            $table->double('childcare')->default(0)->change();
            $table->double('school_fees')->default(0)->change();
            $table->double('children_activities')->default(0)->change();
            $table->double('gifts_charity')->default(0)->change();
            $table->double('regular_savings')->default(0)->change();
            $table->double('other_expenditure')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert back to DECIMAL
            $table->decimal('monthly_expenditure', 15, 2)->nullable()->change();
            $table->decimal('annual_expenditure', 15, 2)->nullable()->change();
            $table->decimal('food_groceries', 10, 2)->default(0)->change();
            $table->decimal('transport_fuel', 10, 2)->default(0)->change();
            $table->decimal('healthcare_medical', 10, 2)->default(0)->change();
            $table->decimal('insurance', 10, 2)->default(0)->change();
            $table->decimal('mobile_phones', 10, 2)->default(0)->change();
            $table->decimal('internet_tv', 10, 2)->default(0)->change();
            $table->decimal('subscriptions', 10, 2)->default(0)->change();
            $table->decimal('clothing_personal_care', 10, 2)->default(0)->change();
            $table->decimal('entertainment_dining', 10, 2)->default(0)->change();
            $table->decimal('holidays_travel', 10, 2)->default(0)->change();
            $table->decimal('pets', 10, 2)->default(0)->change();
            $table->decimal('childcare', 10, 2)->default(0)->change();
            $table->decimal('school_fees', 10, 2)->default(0)->change();
            $table->decimal('children_activities', 10, 2)->default(0)->change();
            $table->decimal('gifts_charity', 10, 2)->default(0)->change();
            $table->decimal('regular_savings', 10, 2)->default(0)->change();
            $table->decimal('other_expenditure', 10, 2)->default(0)->change();
        });
    }
};
