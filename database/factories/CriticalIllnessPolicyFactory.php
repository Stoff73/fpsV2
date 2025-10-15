<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CriticalIllnessPolicy>
 */
class CriticalIllnessPolicyFactory extends Factory
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
        ];

        $conditions = [
            'Cancer',
            'Heart Attack',
            'Stroke',
            'Coronary Artery Bypass',
            'Kidney Failure',
            'Major Organ Transplant',
            'Multiple Sclerosis',
            'Parkinson\'s Disease',
            'Motor Neurone Disease',
            'Alzheimer\'s Disease',
            'Blindness',
            'Deafness',
            'Loss of Limbs',
            'Paralysis',
            'Third Degree Burns',
        ];

        $policyStartDate = $this->faker->dateTimeBetween('-5 years', 'now');

        return [
            'user_id' => User::factory(),
            'policy_type' => $this->faker->randomElement(['standalone', 'accelerated']),
            'provider' => $this->faker->randomElement($providers),
            'policy_number' => 'CI'.$this->faker->unique()->numerify('######'),
            'sum_assured' => $this->faker->numberBetween(50000, 500000),
            'premium_amount' => $this->faker->randomFloat(2, 30, 150),
            'premium_frequency' => $this->faker->randomElement(['monthly', 'quarterly', 'annually']),
            'policy_start_date' => $policyStartDate,
            'policy_term_years' => $this->faker->numberBetween(10, 25),
            'conditions_covered' => $this->faker->randomElements($conditions, $this->faker->numberBetween(5, 12)),
        ];
    }
}
