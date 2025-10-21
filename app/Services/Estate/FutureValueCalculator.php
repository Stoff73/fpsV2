<?php

declare(strict_types=1);

namespace App\Services\Estate;

use Illuminate\Support\Collection;

class FutureValueCalculator
{
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
            $currentValue = $asset->current_value ?? 0;
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
