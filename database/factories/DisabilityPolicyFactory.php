<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DisabilityPolicy>
 */
class DisabilityPolicyFactory extends Factory
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
            'British Friendly',
        ];

        $occupationClasses = [
            'Class 1 (Low Risk)',
            'Class 2 (Medium Risk)',
            'Class 3 (High Risk)',
            'Class 4 (Very High Risk)',
        ];

        $policyStartDate = $this->faker->dateTimeBetween('-5 years', 'now');
        $hasOccupationClass = $this->faker->boolean(70);
        $hasPolicyNumber = $this->faker->boolean(90);
        $hasBenefitPeriod = $this->faker->boolean(80);
        $hasPolicyTerm = $this->faker->boolean(60);

        return [
            'user_id' => User::factory(),
            'provider' => $this->faker->randomElement($providers),
            'policy_number' => $hasPolicyNumber ? ('DP'.$this->faker->numerify('######')) : null,
            'benefit_amount' => $this->faker->numberBetween(1000, 4000),
            'benefit_frequency' => $this->faker->randomElement(['monthly', 'weekly']),
            'deferred_period_weeks' => $this->faker->randomElement([4, 8, 13, 26]),
            'benefit_period_months' => $hasBenefitPeriod ? $this->faker->randomElement([12, 24, 36, 48, 60]) : null,
            'premium_amount' => $this->faker->randomFloat(2, 20, 80),
            'premium_frequency' => 'monthly',
            'occupation_class' => $hasOccupationClass ? $this->faker->randomElement($occupationClasses) : null,
            'policy_start_date' => $policyStartDate,
            'policy_term_years' => $hasPolicyTerm ? $this->faker->numberBetween(10, 25) : null,
            'coverage_type' => $this->faker->randomElement(['accident_only', 'accident_and_sickness']),
        ];
    }
}
