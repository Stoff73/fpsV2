<?php

declare(strict_types=1);

namespace Database\Factories\Investment;

use App\Models\Investment\RiskProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RiskProfile>
 */
class RiskProfileFactory extends Factory
{
    protected $model = RiskProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'risk_tolerance' => $this->faker->randomElement(['cautious', 'balanced', 'adventurous']),
            'capacity_for_loss_percent' => $this->faker->randomFloat(2, 10, 50),
            'time_horizon_years' => $this->faker->numberBetween(5, 40),
            'knowledge_level' => $this->faker->randomElement(['novice', 'intermediate', 'experienced']),
            'attitude_to_volatility' => $this->faker->randomElement([
                'Very uncomfortable with any fluctuations',
                'Comfortable with small fluctuations',
                'Comfortable with moderate fluctuations',
                'Comfortable with significant fluctuations',
            ]),
            'esg_preference' => $this->faker->boolean(40), // 40% chance of true
        ];
    }

    public function cautious(): static
    {
        return $this->state(fn (array $attributes) => [
            'risk_tolerance' => 'cautious',
            'capacity_for_loss_percent' => $this->faker->randomFloat(2, 10, 20),
            'attitude_to_volatility' => 'Comfortable with small fluctuations',
        ]);
    }

    public function balanced(): static
    {
        return $this->state(fn (array $attributes) => [
            'risk_tolerance' => 'balanced',
            'capacity_for_loss_percent' => $this->faker->randomFloat(2, 20, 35),
            'attitude_to_volatility' => 'Comfortable with moderate fluctuations',
        ]);
    }

    public function adventurous(): static
    {
        return $this->state(fn (array $attributes) => [
            'risk_tolerance' => 'adventurous',
            'capacity_for_loss_percent' => $this->faker->randomFloat(2, 35, 50),
            'attitude_to_volatility' => 'Comfortable with significant fluctuations',
        ]);
    }
}
