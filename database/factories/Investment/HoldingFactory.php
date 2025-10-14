<?php

declare(strict_types=1);

namespace Database\Factories\Investment;

use App\Models\Investment\Holding;
use App\Models\Investment\InvestmentAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Holding>
 */
class HoldingFactory extends Factory
{
    protected $model = Holding::class;

    public function definition(): array
    {
        $quantity = $this->faker->randomFloat(6, 10, 500);
        $purchasePrice = $this->faker->randomFloat(4, 50, 500);
        $currentPrice = $purchasePrice * $this->faker->randomFloat(2, 0.8, 1.5);
        $costBasis = $quantity * $purchasePrice;
        $currentValue = $quantity * $currentPrice;

        return [
            'investment_account_id' => InvestmentAccount::factory(),
            'asset_type' => $this->faker->randomElement(['equity', 'bond', 'fund', 'etf', 'alternative']),
            'security_name' => $this->faker->randomElement([
                'Vanguard S&P 500 ETF',
                'iShares Core FTSE 100 ETF',
                'Vanguard Global Bond Index Fund',
                'HSBC FTSE All-World Index Fund',
                'Legal & General UK Index Trust',
                'BlackRock Gold & General Fund',
            ]),
            'ticker' => strtoupper($this->faker->bothify('???')),
            'isin' => strtoupper($this->faker->bothify('GB##########')),
            'quantity' => $quantity,
            'purchase_price' => $purchasePrice,
            'purchase_date' => $this->faker->dateTimeBetween('-5 years', '-1 month'),
            'current_price' => $currentPrice,
            'current_value' => $currentValue,
            'cost_basis' => $costBasis,
            'dividend_yield' => $this->faker->randomFloat(4, 0, 5),
            'ocf_percent' => $this->faker->randomFloat(4, 0.05, 1.5),
        ];
    }

    public function equity(): static
    {
        return $this->state(fn (array $attributes) => [
            'asset_type' => 'equity',
            'dividend_yield' => $this->faker->randomFloat(4, 1, 4),
        ]);
    }

    public function bond(): static
    {
        return $this->state(fn (array $attributes) => [
            'asset_type' => 'bond',
            'dividend_yield' => $this->faker->randomFloat(4, 3, 6),
        ]);
    }
}
