<?php

declare(strict_types=1);

namespace App\Services\Investment;

use Illuminate\Support\Collection;

class TaxEfficiencyCalculator
{
    /**
     * Calculate unrealized gains across all holdings
     */
    public function calculateUnrealizedGains(Collection $holdings): array
    {
        $gains = $holdings->map(function ($holding) {
            // Skip holdings without cost_basis (no price data provided)
            if ($holding->cost_basis === null || $holding->cost_basis === 0) {
                return null;
            }

            $gain = $holding->current_value - $holding->cost_basis;
            $gainPercent = $holding->cost_basis > 0
                ? ($gain / $holding->cost_basis) * 100
                : 0;

            return [
                'security_name' => $holding->security_name,
                'cost_basis' => round($holding->cost_basis, 2),
                'current_value' => round($holding->current_value, 2),
                'unrealized_gain' => round($gain, 2),
                'gain_percent' => round($gainPercent, 2),
            ];
        })->filter(fn ($h) => $h !== null && $h['unrealized_gain'] > 0);

        $totalGain = $gains->sum('unrealized_gain');

        return [
            'total_unrealized_gains' => round($totalGain, 2),
            'holdings_with_gains' => $gains->values()->toArray(),
            'count' => $gains->count(),
        ];
    }

    /**
     * Calculate dividend tax liability
     */
    public function calculateDividendTax(float $dividendIncome, float $totalIncome): float
    {
        $config = config('uk_tax_config.dividend_tax');
        $allowance = $config['dividend_allowance'];

        // Dividend income above allowance
        $taxableDividends = max(0, $dividendIncome - $allowance);

        if ($taxableDividends == 0) {
            return 0;
        }

        // Determine tax band based on total income
        $incomeTaxConfig = config('uk_tax_config.income_tax.bands');
        $personalAllowance = $incomeTaxConfig['personal_allowance'];

        // Simplified calculation - in reality would need to work through bands
        $tax = 0;

        if ($totalIncome <= $personalAllowance + 37700) {
            // Basic rate
            $tax = $taxableDividends * $config['basic_rate'];
        } elseif ($totalIncome <= 125140) {
            // Higher rate
            $tax = $taxableDividends * $config['higher_rate'];
        } else {
            // Additional rate
            $tax = $taxableDividends * $config['additional_rate'];
        }

        return round($tax, 2);
    }

    /**
     * Calculate CGT liability on realized gains
     */
    public function calculateCGTLiability(float $realizedGains, float $totalIncome = 0): float
    {
        $config = config('uk_tax_config.capital_gains_tax');
        $annualExemption = $config['annual_exempt_amount'];

        // Gains above annual exemption
        $taxableGains = max(0, $realizedGains - $annualExemption);

        if ($taxableGains == 0) {
            return 0;
        }

        // Determine rate based on income
        $incomeTaxConfig = config('uk_tax_config.income_tax.bands');
        $higherRateThreshold = $incomeTaxConfig['personal_allowance'] + 37700;

        $rate = $totalIncome > $higherRateThreshold
            ? $config['higher_rate']
            : $config['basic_rate'];

        $cgtLiability = $taxableGains * $rate;

        return round($cgtLiability, 2);
    }

    /**
     * Identify tax loss harvesting opportunities
     */
    public function identifyHarvestingOpportunities(Collection $holdings): array
    {
        // Find holdings with losses that could be harvested
        $lossHoldings = $holdings->filter(function ($holding) {
            // Skip holdings without cost_basis
            if ($holding->cost_basis === null || $holding->cost_basis === 0) {
                return false;
            }

            $gain = $holding->current_value - $holding->cost_basis;

            return $gain < -100; // Only significant losses worth harvesting
        })->map(function ($holding) {
            $loss = $holding->current_value - $holding->cost_basis;

            return [
                'security_name' => $holding->security_name,
                'cost_basis' => round($holding->cost_basis, 2),
                'current_value' => round($holding->current_value, 2),
                'unrealized_loss' => round($loss, 2),
                'loss_percent' => round(($loss / $holding->cost_basis) * 100, 2),
                'recommendation' => 'Consider selling to realize loss for tax purposes',
            ];
        })->values();

        $totalLosses = abs($lossHoldings->sum('unrealized_loss'));

        return [
            'opportunities_count' => $lossHoldings->count(),
            'total_harvestable_losses' => round($totalLosses, 2),
            'potential_tax_saving' => round($totalLosses * 0.20, 2), // Assume 20% CGT rate
            'holdings' => $lossHoldings->toArray(),
        ];
    }

    /**
     * Calculate tax efficiency score (0-100)
     */
    public function calculateTaxEfficiencyScore(Collection $accounts, Collection $holdings): int
    {
        $score = 100;

        // Check ISA usage
        $totalValue = $accounts->sum('current_value');
        $isaValue = $accounts->where('account_type', 'isa')->sum('current_value');

        if ($totalValue > 0) {
            $isaPercent = ($isaValue / $totalValue) * 100;

            if ($isaPercent < 30) {
                $score -= 20; // Should use more ISA
            } elseif ($isaPercent < 50) {
                $score -= 10;
            } else {
                $score += 10; // Bonus for good ISA usage
            }
        }

        // Check for holdings with large unrealized gains (tax inefficient to sell)
        $largeGainHoldings = $holdings->filter(function ($holding) {
            $gain = $holding->current_value - $holding->cost_basis;
            $gainPercent = $holding->cost_basis > 0 ? ($gain / $holding->cost_basis) * 100 : 0;

            return $gainPercent > 50;
        })->count();

        if ($largeGainHoldings > 3) {
            $score -= 20; // Many holdings with large gains
        } elseif ($largeGainHoldings > 1) {
            $score -= 10; // Some holdings with large gains
        }

        return max(0, min(100, $score));
    }
}
