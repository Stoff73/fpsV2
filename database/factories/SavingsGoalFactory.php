<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SavingsGoal>
 */
class SavingsGoalFactory extends Factory
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
            'goal_name' => fake()->words(3, true),
            'target_amount' => fake()->randomFloat(2, 1000, 50000),
            'current_saved' => fake()->randomFloat(2, 0, 10000),
            'target_date' => fake()->dateTimeBetween('+1 month', '+2 years'),
            'priority' => fake()->randomElement(['high', 'medium', 'low']),
            'linked_account_id' => null,
            'auto_transfer_amount' => null,
        ];
    }
}
