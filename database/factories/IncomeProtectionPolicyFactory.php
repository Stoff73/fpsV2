<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IncomeProtectionPolicy>
 */
class IncomeProtectionPolicyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $providers = [
            'Aviva',
            'Legal & General',
            'Royal London',
            'Vitality',
            'AIG Life',
            'LV=',
            'Scottish Widows',
            'Zurich',
            'The Exeter',
            'British Friendly',
        ];

        $occupationClasses = [
            'Class 1 (Low Risk)',
            'Class 2 (Medium Risk)',
            'Class 3 (High Risk)',
            'Class 4 (Very High Risk)',
        ];

        $policyStartDate = $this->faker->dateTimeBetween('-5 years', 'now');

        return [
            'user_id' => User::factory(),
            'provider' => $this->faker->randomElement($providers),
            'policy_number' => 'IP'.$this->faker->unique()->numerify('######'),
            'benefit_amount' => $this->faker->numberBetween(1000, 5000),
            'benefit_frequency' => $this->faker->randomElement(['monthly', 'weekly']),
            'deferred_period_weeks' => $this->faker->randomElement([4, 8, 13, 26, 52]),
            'benefit_period_months' => $this->faker->randomElement([12, 24, 36, 48, 60]),
            'premium_amount' => $this->faker->randomFloat(2, 20, 100),
            'occupation_class' => $this->faker->randomElement($occupationClasses),
            'policy_start_date' => $policyStartDate,
        ];
    }
}
