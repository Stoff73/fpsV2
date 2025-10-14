<?php

declare(strict_types=1);

namespace Database\Factories\Investment;

use App\Models\Investment\InvestmentGoal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvestmentGoal>
 */
class InvestmentGoalFactory extends Factory
{
    protected $model = InvestmentGoal::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'goal_name' => $this->faker->randomElement([
                'Retirement Fund',
                'House Deposit',
                'Children\'s Education',
                'Financial Independence',
                'Emergency Fund',
            ]),
            'goal_type' => $this->faker->randomElement(['retirement', 'education', 'wealth', 'home']),
            'target_amount' => $this->faker->randomFloat(2, 50000, 2000000),
            'target_date' => $this->faker->dateTimeBetween('+5 years', '+40 years'),
            'priority' => $this->faker->randomElement(['high', 'medium', 'low']),
            'is_essential' => $this->faker->boolean(30), // 30% chance of true
            'linked_account_ids' => [],
        ];
    }

    public function retirement(): static
    {
        return $this->state(fn (array $attributes) => [
            'goal_type' => 'retirement',
            'goal_name' => 'Retirement Fund',
            'is_essential' => true,
            'priority' => 'high',
        ]);
    }

    public function education(): static
    {
        return $this->state(fn (array $attributes) => [
            'goal_type' => 'education',
            'goal_name' => 'Children\'s Education',
            'priority' => 'high',
        ]);
    }
}
