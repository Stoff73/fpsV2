<?php

declare(strict_types=1);

namespace App\Services\Estate;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FutureValueCalculator
{
    /**
     * Get life expectancy for user based on UK ONS actuarial tables
     *
     * @param  User  $user
     * @return array [years_remaining, death_age, death_year]
     */
    public function getLifeExpectancy(User $user): array
    {
        if (! $user->date_of_birth) {
            // Default to age 85 if no DOB
            return [
                'years_remaining' => 30,
                'death_age' => 85,
                'death_year' => now()->year + 30,
            ];
        }

        $currentAge = Carbon::parse($user->date_of_birth)->age;
        $gender = strtolower($user->gender ?? 'male');

        $lifeExpectancy = $this->lookupLifeExpectancy($currentAge, $gender);

        return [
            'years_remaining' => round($lifeExpectancy, 1),
            'death_age' => $currentAge + (int) round($lifeExpectancy),
            'death_year' => now()->year + (int) round($lifeExpectancy),
            'current_age' => $currentAge,
        ];
    }

    /**
     * Lookup life expectancy from UK ONS tables (2021-2023 data)
     *
     * @param  int  $age  Current age
     * @param  string  $gender  'male' or 'female'
     * @return float Years remaining
     */
    private function lookupLifeExpectancy(int $age, string $gender): float
    {
        $tables = config('uk_life_expectancy');

        // Normalize gender
        $gender = in_array($gender, ['male', 'female']) ? $gender : 'male';

        // Get the lookup table
        $table = $tables[$gender] ?? $tables['male'];

        // If exact age exists, return it
        if (isset($table[$age])) {
            return $table[$age];
        }

        // Linear interpolation for ages not in table
        $ages = array_keys($table);
        $minAge = min($ages);
        $maxAge = max($ages);

        if ($age < $minAge) {
            // Younger than table, use minimum age + difference
            return $table[$minAge] + ($minAge - $age);
        }

        if ($age > $maxAge) {
            // Older than table, extrapolate (reduce by ~1 year per age)
            return max(1, $table[$maxAge] - ($age - $maxAge));
        }

        // Find surrounding ages and interpolate
        $lowerAge = null;
        $upperAge = null;

        foreach ($ages as $tableAge) {
            if ($tableAge < $age) {
                $lowerAge = $tableAge;
            } elseif ($tableAge > $age && $upperAge === null) {
                $upperAge = $tableAge;
                break;
            }
        }

        if ($lowerAge !== null && $upperAge !== null) {
            $lowerValue = $table[$lowerAge];
            $upperValue = $table[$upperAge];
            $fraction = ($age - $lowerAge) / ($upperAge - $lowerAge);

            return $lowerValue + ($upperValue - $lowerValue) * $fraction;
        }

        // Fallback
        return 20.0;
    }

    /**
     * Project mortgage balance at future date
     *
     * Handles:
     * - Interest-only mortgages (balance stays the same)
     * - Repayment mortgages (amortization)
     * - Maturity dates (mortgage paid off if term ends)
     *
     * @param  float  $currentBalance
     * @param  string  $mortgageType  'interest_only' or 'repayment'
     * @param  int  $remainingTermMonths
     * @param  float  $interestRate  Annual rate as percentage
     * @param  float  $monthlyPayment
     * @param  int  $years  Years to project
     * @return float Projected balance (0 if matured)
     */
    public function projectMortgageBalance(
        float $currentBalance,
        string $mortgageType,
        int $remainingTermMonths,
        float $interestRate,
        float $monthlyPayment,
        int $years
    ): float {
        // Convert years to months
        $monthsToProject = $years * 12;

        // Check if mortgage matures before projection date
        if ($remainingTermMonths <= $monthsToProject) {
            // Mortgage will be paid off by projection date
            return 0;
        }

        // Interest-only mortgage
        if ($mortgageType === 'interest_only') {
            // Balance stays the same (capital not repaid)
            return $currentBalance;
        }

        // Repayment mortgage - amortize
        if ($monthlyPayment > 0 && $interestRate > 0) {
            $monthlyRate = ($interestRate / 100) / 12;

            $remainingBalance = $currentBalance;
            for ($month = 1; $month <= $monthsToProject; $month++) {
                $interestPayment = $remainingBalance * $monthlyRate;
                $principalPayment = $monthlyPayment - $interestPayment;
                $remainingBalance -= $principalPayment;

                if ($remainingBalance <= 0) {
                    return 0;
                }
            }

            return max(0, $remainingBalance);
        }

        // Fallback: linear amortization
        $monthlyReduction = $currentBalance / $remainingTermMonths;
        $projectedBalance = $currentBalance - ($monthlyReduction * $monthsToProject);

        return max(0, $projectedBalance);
    }


    /**
     * Calculate future value of an asset given current value, growth rate, and years
     *
     * Formula: FV = PV * (1 + r)^n
     *
     * @param  float  $presentValue  Current value of asset
     * @param  float  $annualGrowthRate  Annual growth rate (as decimal, e.g., 0.05 for 5%)
     * @param  int  $years  Number of years into the future
     * @return float Future value
     */
    public function calculateFutureValue(float $presentValue, float $annualGrowthRate, int $years): float
    {
        if ($years <= 0) {
            return $presentValue;
        }

        return $presentValue * pow(1 + $annualGrowthRate, $years);
    }

    /**
     * Calculate future value of multiple assets
     *
     * @param  Collection  $assets  Collection of assets with current_value
     * @param  float  $annualGrowthRate  Annual growth rate
     * @param  int  $years  Years into future
     * @return array Future value breakdown by asset
     */
    public function calculatePortfolioFutureValue(Collection $assets, float $annualGrowthRate, int $years): array
    {
        $projections = [];
        $totalCurrentValue = 0;
        $totalFutureValue = 0;

        foreach ($assets as $asset) {
            $currentValue = $asset->current_value ?? 0;
            $futureValue = $this->calculateFutureValue($currentValue, $annualGrowthRate, $years);

            $totalCurrentValue += $currentValue;
            $totalFutureValue += $futureValue;

            $projections[] = [
                'asset_name' => $asset->asset_name ?? 'Unknown Asset',
                'asset_type' => $asset->asset_type ?? 'unknown',
                'current_value' => round($currentValue, 2),
                'future_value' => round($futureValue, 2),
                'growth_amount' => round($futureValue - $currentValue, 2),
                'growth_rate' => $annualGrowthRate,
                'years' => $years,
            ];
        }

        return [
            'total_current_value' => round($totalCurrentValue, 2),
            'total_future_value' => round($totalFutureValue, 2),
            'total_growth' => round($totalFutureValue - $totalCurrentValue, 2),
            'growth_rate' => $annualGrowthRate,
            'years' => $years,
            'asset_projections' => $projections,
        ];
    }

    /**
     * Calculate future value with different growth rates by asset type
     *
     * @param  Collection  $assets  Assets collection
     * @param  array  $growthRatesByType  Growth rates keyed by asset type
     * @param  int  $years  Years into future
     * @return array Future value breakdown
     */
    public function calculatePortfolioFutureValueByAssetType(Collection $assets, array $growthRatesByType, int $years): array
    {
        $projections = [];
        $totalCurrentValue = 0;
        $totalFutureValue = 0;

        foreach ($assets as $asset) {
            $currentValue = (float) ($asset->current_value ?? 0);
            $assetType = $asset->asset_type ?? 'other';

            // Get growth rate for this asset type, or use default
            $growthRate = $growthRatesByType[$assetType] ?? $growthRatesByType['default'] ?? 0.05;

            $futureValue = $this->calculateFutureValue($currentValue, $growthRate, $years);

            $totalCurrentValue += $currentValue;
            $totalFutureValue += $futureValue;

            $projections[] = [
                'asset_name' => $asset->asset_name ?? 'Unknown Asset',
                'asset_type' => $assetType,
                'current_value' => round($currentValue, 2),
                'future_value' => round($futureValue, 2),
                'growth_amount' => round($futureValue - $currentValue, 2),
                'growth_rate' => $growthRate,
                'years' => $years,
            ];
        }

        return [
            'total_current_value' => round($totalCurrentValue, 2),
            'total_future_value' => round($totalFutureValue, 2),
            'total_growth' => round($totalFutureValue - $totalCurrentValue, 2),
            'years' => $years,
            'asset_projections' => $projections,
        ];
    }

    /**
     * Get default growth rates by asset type (from UK tax config assumptions)
     *
     * @return array Growth rates by asset type
     */
    public function getDefaultGrowthRates(): array
    {
        $assumptions = config('uk_tax_config.assumptions');

        return [
            'property' => 0.03, // 3% property appreciation
            'investment' => $assumptions['investment_growth_rate'] ?? 0.05, // 5%
            'cash' => $assumptions['cash_savings_rate'] ?? 0.04, // 4%
            'savings' => $assumptions['cash_savings_rate'] ?? 0.04, // 4%
            'pension' => $assumptions['investment_growth_rate'] ?? 0.05, // 5%
            'business' => 0.04, // 4% conservative business growth
            'other' => 0.03, // 3% default
            'default' => 0.03, // 3% fallback
        ];
    }

    /**
     * Calculate real future value (adjusted for inflation)
     *
     * @param  float  $presentValue  Current value
     * @param  float  $nominalGrowthRate  Nominal growth rate
     * @param  float  $inflationRate  Inflation rate
     * @param  int  $years  Years into future
     * @return float Real future value (inflation-adjusted)
     */
    public function calculateRealFutureValue(
        float $presentValue,
        float $nominalGrowthRate,
        float $inflationRate,
        int $years
    ): float {
        if ($years <= 0) {
            return $presentValue;
        }

        // Real growth rate = ((1 + nominal) / (1 + inflation)) - 1
        $realGrowthRate = ((1 + $nominalGrowthRate) / (1 + $inflationRate)) - 1;

        return $this->calculateFutureValue($presentValue, $realGrowthRate, $years);
    }

    /**
     * Project estate value at expected death date
     *
     * @param  Collection  $assets  Current assets
     * @param  int  $yearsUntilDeath  Years until expected death
     * @param  array|null  $growthRatesByType  Optional custom growth rates
     * @return array Estate projection
     */
    public function projectEstateAtDeath(Collection $assets, int $yearsUntilDeath, ?array $growthRatesByType = null): array
    {
        $growthRates = $growthRatesByType ?? $this->getDefaultGrowthRates();

        $projection = $this->calculatePortfolioFutureValueByAssetType($assets, $growthRates, $yearsUntilDeath);

        return [
            'current_estate_value' => $projection['total_current_value'],
            'projected_estate_value_at_death' => $projection['total_future_value'],
            'projected_growth' => $projection['total_growth'],
            'years_until_death' => $yearsUntilDeath,
            'growth_rates_used' => $growthRates,
            'asset_projections' => $projection['asset_projections'],
        ];
    }

    /**
     * Calculate compound annual growth rate (CAGR) needed to reach target value
     *
     * @param  float  $presentValue  Current value
     * @param  float  $targetValue  Target future value
     * @param  int  $years  Years to reach target
     * @return float Required CAGR
     */
    public function calculateRequiredGrowthRate(float $presentValue, float $targetValue, int $years): float
    {
        if ($years <= 0 || $presentValue <= 0) {
            return 0;
        }

        // CAGR = (FV / PV)^(1/n) - 1
        return pow(($targetValue / $presentValue), (1 / $years)) - 1;
    }
}
