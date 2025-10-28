<?php

namespace Database\Factories\Estate;

use App\Models\Estate\Trust;
use App\Models\Household;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrustFactory extends Factory
{
    protected $model = Trust::class;

    public function definition(): array
    {
        $trustTypes = [
            'bare',
            'interest_in_possession',
            'discretionary',
            'accumulation_maintenance',
            'life_insurance',
            'discounted_gift',
            'loan',
            'mixed',
            'settlor_interested',
        ];

        $trustType = $this->faker->randomElement($trustTypes);
        $isRPT = in_array($trustType, ['discretionary', 'accumulation_maintenance']);

        return [
            'user_id' => User::factory(),
            'household_id' => Household::factory(),
            'trust_name' => $this->faker->company.' Trust',
            'trust_type' => $trustType,
            'trust_creation_date' => $this->faker->dateTimeBetween('-20 years', '-1 year'),
            'initial_value' => $this->faker->randomFloat(2, 50000, 1000000),
            'current_value' => $this->faker->randomFloat(2, 100000, 2000000),
            'discount_amount' => $trustType === 'discounted_gift' ? $this->faker->randomFloat(2, 10000, 100000) : null,
            'retained_income_annual' => $trustType === 'discounted_gift' ? $this->faker->randomFloat(2, 5000, 25000) : null,
            'loan_amount' => $trustType === 'loan' ? $this->faker->randomFloat(2, 50000, 500000) : null,
            'loan_interest_bearing' => $trustType === 'loan' ? $this->faker->boolean() : false,
            'loan_interest_rate' => $trustType === 'loan' && $this->faker->boolean() ? $this->faker->randomFloat(4, 0.01, 0.05) : null,
            'sum_assured' => $trustType === 'life_insurance' ? $this->faker->randomFloat(2, 100000, 1000000) : null,
            'annual_premium' => $trustType === 'life_insurance' ? $this->faker->randomFloat(2, 1000, 10000) : null,
            'is_relevant_property_trust' => $isRPT,
            'last_periodic_charge_date' => $isRPT && $this->faker->boolean() ? $this->faker->dateTimeBetween('-5 years', 'now') : null,
            'last_periodic_charge_amount' => $isRPT && $this->faker->boolean() ? $this->faker->randomFloat(2, 1000, 50000) : null,
            'last_valuation_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'next_tax_return_due' => $this->faker->dateTimeBetween('now', '+6 months'),
            'total_asset_value' => $this->faker->randomFloat(2, 100000, 2000000),
            'beneficiaries' => $this->faker->name.', '.$this->faker->name,
            'trustees' => $this->faker->name.', '.$this->faker->name,
            'purpose' => $this->faker->sentence,
            'notes' => $this->faker->optional()->paragraph,
            'is_active' => $this->faker->boolean(90), // 90% active
        ];
    }

    /**
     * Indicate that the trust is a relevant property trust (discretionary).
     */
    public function relevantPropertyTrust(): static
    {
        return $this->state(fn (array $attributes) => [
            'trust_type' => 'discretionary',
            'is_relevant_property_trust' => true,
        ]);
    }

    /**
     * Indicate that the trust is a bare trust.
     */
    public function bareTrust(): static
    {
        return $this->state(fn (array $attributes) => [
            'trust_type' => 'bare',
            'is_relevant_property_trust' => false,
        ]);
    }

    /**
     * Indicate that the trust is a life insurance trust.
     */
    public function lifeInsuranceTrust(): static
    {
        return $this->state(fn (array $attributes) => [
            'trust_type' => 'life_insurance',
            'is_relevant_property_trust' => false,
            'sum_assured' => $this->faker->randomFloat(2, 100000, 1000000),
            'annual_premium' => $this->faker->randomFloat(2, 1000, 10000),
        ]);
    }

    /**
     * Indicate that the trust is a loan trust.
     */
    public function loanTrust(): static
    {
        return $this->state(fn (array $attributes) => [
            'trust_type' => 'loan',
            'is_relevant_property_trust' => false,
            'loan_amount' => $this->faker->randomFloat(2, 50000, 500000),
            'loan_interest_bearing' => true,
            'loan_interest_rate' => $this->faker->randomFloat(4, 0.01, 0.05),
        ]);
    }

    /**
     * Indicate that the trust is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the trust is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
