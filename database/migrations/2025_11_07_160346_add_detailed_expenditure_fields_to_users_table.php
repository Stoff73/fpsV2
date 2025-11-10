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
            // Essential Living Expenses
            $table->decimal('food_groceries', 10, 2)->default(0)->after('annual_expenditure');
            $table->decimal('transport_fuel', 10, 2)->default(0)->after('food_groceries');
            $table->decimal('healthcare_medical', 10, 2)->default(0)->after('transport_fuel');
            $table->decimal('insurance', 10, 2)->default(0)->after('healthcare_medical');

            // Communication & Technology
            $table->decimal('mobile_phones', 10, 2)->default(0)->after('insurance');
            $table->decimal('internet_tv', 10, 2)->default(0)->after('mobile_phones');
            $table->decimal('subscriptions', 10, 2)->default(0)->after('internet_tv');

            // Personal & Lifestyle
            $table->decimal('clothing_personal_care', 10, 2)->default(0)->after('subscriptions');
            $table->decimal('entertainment_dining', 10, 2)->default(0)->after('clothing_personal_care');
            $table->decimal('holidays_travel', 10, 2)->default(0)->after('entertainment_dining');
            $table->decimal('pets', 10, 2)->default(0)->after('holidays_travel');

            // Children & Education
            $table->decimal('childcare', 10, 2)->default(0)->after('pets');
            $table->decimal('school_fees', 10, 2)->default(0)->after('childcare');
            $table->decimal('children_activities', 10, 2)->default(0)->after('school_fees');

            // Other Expenses
            $table->decimal('gifts_charity', 10, 2)->default(0)->after('children_activities');
            $table->decimal('regular_savings', 10, 2)->default(0)->after('gifts_charity');
            $table->decimal('other_expenditure', 10, 2)->default(0)->after('regular_savings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'food_groceries',
                'transport_fuel',
                'healthcare_medical',
                'insurance',
                'mobile_phones',
                'internet_tv',
                'subscriptions',
                'clothing_personal_care',
                'entertainment_dining',
                'holidays_travel',
                'pets',
                'childcare',
                'school_fees',
                'children_activities',
                'gifts_charity',
                'regular_savings',
                'other_expenditure',
            ]);
        });
    }
};
