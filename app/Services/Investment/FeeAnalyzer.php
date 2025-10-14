<?php

declare(strict_types=1);

namespace App\Services\Investment;

use Illuminate\Support\Collection;

class FeeAnalyzer
{
    /**
     * Calculate total fees across all accounts and holdings
     */
    public function calculateTotalFees(Collection $accounts, Collection $holdings): array
    {
        $portfolioValue = $accounts->sum('current_value');

        if ($portfolioValue == 0) {
            return [
                'total_annual_fees' => 0.0,
                'fee_breakdown' => [],
                'fee_drag_percent' => 0.0,
            ];
        }

        // Calculate platform fees
        $platformFees = $accounts->sum(function ($account) {
            return $account->current_value * ($account->platform_fee_percent / 100);
        });

        // Calculate fund OCF (Ongoing Charges Figure)
        $fundFees = $holdings->sum(function ($holding) {
            return $holding->current_value * ($holding->ocf_percent / 100);
        });

        // Estimated transaction costs (simplified)
        $transactionCosts = $portfolioValue * 0.001; // 0.1% estimated

        $totalFees = $platformFees + $fundFees + $transactionCosts;
        $feeDragPercent = ($totalFees / $portfolioValue) * 100;

        return [
            'portfolio_value' => round($portfolioValue, 2),
            'total_annual_fees' => round($totalFees, 2),
            'fee_breakdown' => [
                [
                    'type' => 'Platform Fees',
                    'amount' => round($platformFees, 2),
                    'percent_of_portfolio' => round(($platformFees / $portfolioValue) * 100, 4),
                ],
                [
                    'type' => 'Fund Charges (OCF)',
                    'amount' => round($fundFees, 2),
                    'percent_of_portfolio' => round(($fundFees / $portfolioValue) * 100, 4),
                ],
                [
                    'type' => 'Transaction Costs',
                    'amount' => round($transactionCosts, 2),
                    'percent_of_portfolio' => round(($transactionCosts / $portfolioValue) * 100, 4),
                ],
            ],
            'fee_drag_percent' => round($feeDragPercent, 4),
            'fees_over_10_years' => round($totalFees * 10, 2), // Simplified - doesn't account for growth
            'fees_over_20_years' => round($totalFees * 20, 2),
        ];
    }

    /**
     * Calculate fee drag on returns
     */
    public function calculateFeeDrag(float $totalFees, float $portfolioValue): float
    {
        if ($portfolioValue == 0) {
            return 0;
        }

        return round(($totalFees / $portfolioValue) * 100, 4);
    }

    /**
     * Compare to low-cost alternatives
     */
    public function compareToLowCostAlternatives(Collection $holdings): array
    {
        $totalValue = $holdings->sum('current_value');

        if ($totalValue == 0) {
            return [
                'current_ocf' => 0.0,
                'low_cost_ocf' => 0.0,
                'annual_saving' => 0.0,
            ];
        }

        // Calculate current weighted average OCF
        $currentOCF = $holdings->sum(function ($holding) use ($totalValue) {
            return ($holding->current_value / $totalValue) * $holding->ocf_percent;
        });

        // Assume low-cost index funds average 0.15% OCF
        $lowCostOCF = 0.15;

        $currentAnnualCost = $totalValue * ($currentOCF / 100);
        $lowCostAnnualCost = $totalValue * ($lowCostOCF / 100);
        $annualSaving = $currentAnnualCost - $lowCostAnnualCost;

        return [
            'current_average_ocf' => round($currentOCF, 4),
            'low_cost_average_ocf' => $lowCostOCF,
            'current_annual_cost' => round($currentAnnualCost, 2),
            'low_cost_annual_cost' => round($lowCostAnnualCost, 2),
            'annual_saving' => round($annualSaving, 2),
            'ten_year_saving' => round($annualSaving * 10, 2),
            'recommendation' => $annualSaving > 100 ? 'Consider switching to lower-cost funds' : 'Fees are competitive',
        ];
    }

    /**
     * Identify high-fee holdings
     */
    public function identifyHighFeeHoldings(Collection $holdings): array
    {
        $highFeeThreshold = 0.75; // 0.75% OCF is considered high

        $highFeeHoldings = $holdings->filter(function ($holding) use ($highFeeThreshold) {
            return $holding->ocf_percent > $highFeeThreshold;
        })->map(function ($holding) {
            return [
                'security_name' => $holding->security_name,
                'ocf_percent' => round($holding->ocf_percent, 4),
                'current_value' => round($holding->current_value, 2),
                'annual_cost' => round($holding->current_value * ($holding->ocf_percent / 100), 2),
                'recommendation' => 'Consider lower-cost alternative',
            ];
        })->values()->toArray();

        return [
            'high_fee_count' => count($highFeeHoldings),
            'holdings' => $highFeeHoldings,
            'total_value_in_high_fee_funds' => round(array_sum(array_column($highFeeHoldings, 'current_value')), 2),
        ];
    }
}
