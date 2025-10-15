<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SicknessIllnessPolicy>
 */
class SicknessIllnessPolicyFactory extends Factory
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
            'Benenden Health',
        ];

        $conditions = [
            'Common Cold and Flu',
            'Gastroenteritis',
            'Back Pain',
            'Musculoskeletal Disorders',
            'Stress and Anxiety',
            'Depression',
            'Respiratory Infections',
            'Minor Injuries',
            'Headaches and Migraines',
            'Minor Surgical Procedures',
        ];

        $exclusions = [
            'Pre-existing conditions',
            'Self-inflicted injuries',
            'Injuries from hazardous activities',
            'Pandemic-related illnesses',
            'Mental health conditions (excluded)',
            'Pregnancy-related complications',
        ];

        $policyStartDate = $this->faker->dateTimeBetween('-5 years', 'now');
        $hasPolicyNumber = $this->faker->boolean(85);
        $hasDeferredPeriod = $this->faker->boolean(70);
        $hasBenefitPeriod = $this->faker->boolean(75);
        $hasPolicyTerm = $this->faker->boolean(65);
        $hasConditionsCovered = $this->faker->boolean(80);
        $hasExclusions = $this->faker->boolean(90);

        return [
            'user_id' => User::factory(),
            'provider' => $this->faker->randomElement($providers),
            'policy_number' => $hasPolicyNumber ? ('SI'.$this->faker->numerify('######')) : null,
            'benefit_amount' => $this->faker->numberBetween(10000, 100000),
            'benefit_frequency' => $this->faker->randomElement(['monthly', 'weekly', 'lump_sum']),
            'deferred_period_weeks' => $hasDeferredPeriod ? $this->faker->numberBetween(0, 8) : null,
            'benefit_period_months' => $hasBenefitPeriod ? $this->faker->numberBetween(6, 24) : null,
            'premium_amount' => $this->faker->randomFloat(2, 15, 60),
            'premium_frequency' => 'monthly',
            'policy_start_date' => $policyStartDate,
            'policy_term_years' => $hasPolicyTerm ? $this->faker->numberBetween(10, 20) : null,
            'conditions_covered' => $hasConditionsCovered ? $this->faker->randomElements($conditions, $this->faker->numberBetween(3, 8)) : null,
            'exclusions' => $hasExclusions ? implode('; ', $this->faker->randomElements($exclusions, $this->faker->numberBetween(2, 5))) : null,
        ];
    }
}
