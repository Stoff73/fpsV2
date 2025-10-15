<?php

declare(strict_types=1);

namespace App\Services\Savings;

use App\Models\SavingsAccount;

class RateComparator
{
    /**
     * Compare account rate to market benchmarks
     *
     * @return array{account_rate: float, market_rate: float, difference: float, is_competitive: bool, category: string}
     */
    public function compareToMarketRates(SavingsAccount $account): array
    {
        $benchmarks = $this->getMarketBenchmarks();
        $accountType = $account->account_type;
        $accountRate = (float) $account->interest_rate;

        // Get appropriate benchmark based on account type and ISA status
        $marketRate = $this->getBenchmarkForAccount($account, $benchmarks);

        $difference = $accountRate - $marketRate;
        $isCompetitive = $difference >= -0.005; // Within 0.5% is considered competitive

        // Categorize the rate
        $category = match (true) {
            $difference >= 0.01 => 'Excellent', // 1%+ above market
            $difference >= 0 => 'Good', // At or above market
            $difference >= -0.01 => 'Fair', // Within 1% of market
            default => 'Poor', // More than 1% below market
        };

        return [
            'account_rate' => round($accountRate, 4),
            'market_rate' => round($marketRate, 4),
            'difference' => round($difference, 4),
            'is_competitive' => $isCompetitive,
            'category' => $category,
        ];
    }

    /**
     * Get market benchmark rates by account type
     *
     * @return array<string, float>
     */
    public function getMarketBenchmarks(): array
    {
        // These are typical UK market rates for 2024/25
        // In production, these could be updated periodically from external sources
        return [
            'easy_access' => 0.0450, // 4.50%
            'easy_access_isa' => 0.0475, // 4.75%
            'notice' => 0.0500, // 5.00%
            'notice_isa' => 0.0525, // 5.25%
            'fixed_1_year' => 0.0525, // 5.25%
            'fixed_1_year_isa' => 0.0550, // 5.50%
            'fixed_2_year' => 0.0500, // 5.00%
            'fixed_2_year_isa' => 0.0525, // 5.25%
            'fixed_3_year' => 0.0475, // 4.75%
            'fixed_3_year_isa' => 0.0500, // 5.00%
        ];
    }

    /**
     * Calculate potential interest difference over a year
     */
    public function calculateInterestDifference(SavingsAccount $account, float $marketRate): float
    {
        $balance = (float) $account->current_balance;
        $accountRate = (float) $account->interest_rate;

        $currentInterest = $balance * $accountRate;
        $potentialInterest = $balance * $marketRate;

        return round($potentialInterest - $currentInterest, 2);
    }

    /**
     * Get appropriate benchmark for an account
     */
    private function getBenchmarkForAccount(SavingsAccount $account, array $benchmarks): float
    {
        $accountType = $account->account_type;
        $isIsa = $account->is_isa;

        // Determine benchmark key based on account characteristics
        $benchmarkKey = match ($account->access_type) {
            'immediate' => $isIsa ? 'easy_access_isa' : 'easy_access',
            'notice' => $isIsa ? 'notice_isa' : 'notice',
            'fixed' => $this->getFixedRateBenchmark($account, $isIsa),
            default => $isIsa ? 'easy_access_isa' : 'easy_access',
        };

        return $benchmarks[$benchmarkKey] ?? 0.0400; // Default to 4% if not found
    }

    /**
     * Get benchmark for fixed-rate accounts based on term
     */
    private function getFixedRateBenchmark(SavingsAccount $account, bool $isIsa): string
    {
        if (! $account->maturity_date) {
            return $isIsa ? 'fixed_1_year_isa' : 'fixed_1_year';
        }

        $now = now();
        $maturityDate = $account->maturity_date;
        $yearsToMaturity = $now->diffInYears($maturityDate);

        return match (true) {
            $yearsToMaturity >= 3 => $isIsa ? 'fixed_3_year_isa' : 'fixed_3_year',
            $yearsToMaturity >= 2 => $isIsa ? 'fixed_2_year_isa' : 'fixed_2_year',
            default => $isIsa ? 'fixed_1_year_isa' : 'fixed_1_year',
        };
    }
}
