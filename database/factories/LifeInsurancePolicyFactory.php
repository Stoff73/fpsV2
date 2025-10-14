<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LifeInsurancePolicy>
 */
class LifeInsurancePolicyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'policy_type' => $this->faker->randomElement(['term', 'whole_of_life', 'decreasing_term', 'family_income_benefit', 'level_term']),
            'provider' => $this->faker->company(),
            'policy_number' => $this->faker->unique()->numerify('LI######'),
            'sum_assured' => $this->faker->numberBetween(100000, 1000000),
            'premium_amount' => $this->faker->numberBetween(20, 200),
            'premium_frequency' => $this->faker->randomElement(['monthly', 'quarterly', 'annually']),
            'policy_start_date' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'policy_term_years' => $this->faker->numberBetween(10, 30),
            'indexation_rate' => null,
            'in_trust' => $this->faker->boolean(30),
            'beneficiaries' => $this->faker->name(),
        ];
    }
}
