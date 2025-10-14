<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProtectionProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProtectionProfile>
 */
class ProtectionProfileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = ProtectionProfile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'annual_income' => $this->faker->numberBetween(25000, 150000),
            'monthly_expenditure' => $this->faker->numberBetween(2000, 8000),
            'mortgage_balance' => $this->faker->numberBetween(0, 500000),
            'other_debts' => $this->faker->numberBetween(0, 50000),
            'number_of_dependents' => $this->faker->numberBetween(0, 4),
            'dependents_ages' => $this->faker->randomElement([
                [],
                [5],
                [5, 10],
                [3, 8, 15],
            ]),
            'retirement_age' => $this->faker->numberBetween(65, 70),
            'occupation' => $this->faker->jobTitle(),
            'smoker_status' => $this->faker->boolean(20), // 20% chance of smoker
            'health_status' => $this->faker->randomElement(['excellent', 'good', 'fair', 'poor']),
        ];
    }
}
