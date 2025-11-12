<?php

declare(strict_types=1);

namespace App\Services\Investment\Fees;

use App\Models\Investment\Holding;
use App\Models\Investment\InvestmentAccount;
use Illuminate\Support\Collection;

/**
 * Fee Analyzer
 * Calculates total fees (platform fees, fund OCF, transaction costs) and their impact
 *
 * Fee Types:
 * - Platform fees (% of portfolio value)
 * - Fund OCF (Ongoing Charges Figure)
 * - Transaction costs (buying/selling)
 * - Advisory fees (if applicable)
 *
 * Analysis:
 * - Total annual fee cost
 * - Fee as percentage of portfolio
 * - Fee drag on returns over time
 * - Comparison with industry averages
 */
class FeeAnalyzer
{
    /**
     * Analyze fees for user's entire investment portfolio
     *
     * @param  int  $userId  User ID
     * @return array Fee analysis
     */
    public function analyzePortfolioFees(int $userId): array
    {
        $accounts = InvestmentAccount::where('user_id', $userId)
            ->with('holdings')
            ->get();

        if ($accounts->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No investment accounts found',
            ];
        }

        $totalValue = 0;
        $totalAnnualFees = 0;
        $accountAnalyses = [];

        foreach ($accounts as $account) {
            $analysis = $this->analyzeAccountFees($account);

            if ($analysis['success']) {
                $accountAnalyses[] = $analysis;
                $totalValue += $analysis['account_value'];
                $totalAnnualFees += $analysis['total_annual_fees'];
            }
        }

        if ($totalValue == 0) {
            return [
                'success' => false,
                'message' => 'No portfolio value to analyze',
            ];
        }

        $weightedAverageFeePercent = ($totalAnnualFees / $totalValue) * 100;

        // Calculate fee drag over time
        $feeDrag = $this->calculateFeeDrag($totalValue, $weightedAverageFeePercent, 10, 0.06);

        // Industry benchmarks
        $benchmark = $this->getBenchmarkFees($totalValue);

        return [
            'success' => true,
            'total_portfolio_value' => $totalValue,
            'total_annual_fees' => $totalAnnualFees,
            'average_fee_percent' => round($weightedAverageFeePercent, 3),
            'accounts' => $accountAnalyses,
            'fee_drag' => $feeDrag,
            'benchmark' => $benchmark,
            'assessment' => $this->assessFeeLevel($weightedAverageFeePercent, $benchmark),
            'potential_savings' => $this->calculatePotentialSavings($totalAnnualFees, $benchmark, $totalValue),
        ];
    }

    /**
     * Analyze fees for a single investment account
     *
     * @param  InvestmentAccount  $account  Investment account
     * @return array Fee analysis
     */
    public function analyzeAccountFees(InvestmentAccount $account): array
    {
        $holdings = $account->holdings;

        if ($holdings->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No holdings in account',
            ];
        }

        $accountValue = $holdings->sum('current_value');

        if ($accountValue == 0) {
            return [
                'success' => false,
                'message' => 'Account has zero value',
            ];
        }

        // Calculate platform fee
        $platformFee = $this->calculatePlatformFee($accountValue, $account->platform_name ?? 'Unknown');

        // Calculate weighted average OCF
        $weightedOCF = $this->calculateWeightedOCF($holdings, $accountValue);

        // Calculate transaction costs (estimated)
        $transactionCosts = $this->estimateTransactionCosts($accountValue, $account->turnover_rate ?? 0.10);

        // Advisory fees (if applicable)
        $advisoryFee = $account->advisory_fee ?? 0;

        // Total annual fees
        $totalAnnualFees = $platformFee + ($weightedOCF * $accountValue) + $transactionCosts + $advisoryFee;
        $totalFeePercent = ($totalAnnualFees / $accountValue) * 100;

        return [
            'success' => true,
            'account_id' => $account->id,
            'account_name' => $account->account_name,
            'platform_name' => $account->platform_name ?? 'Unknown',
            'account_type' => $account->account_type,
            'account_value' => $accountValue,
            'fees' => [
                'platform_fee' => round($platformFee, 2),
                'fund_ocf' => round($weightedOCF * $accountValue, 2),
                'transaction_costs' => round($transactionCosts, 2),
                'advisory_fee' => round($advisoryFee, 2),
            ],
            'total_annual_fees' => round($totalAnnualFees, 2),
            'total_fee_percent' => round($totalFeePercent, 3),
            'weighted_ocf' => round($weightedOCF * 100, 3),
            'holdings_count' => $holdings->count(),
        ];
    }

    /**
     * Calculate fee breakdown by holding
     *
     * @param  int  $userId  User ID
     * @return array Holdings fee breakdown
     */
    public function analyzeHoldingFees(int $userId): array
    {
        $accounts = InvestmentAccount::where('user_id', $userId)
            ->with('holdings')
            ->get();

        $allHoldings = collect();
        foreach ($accounts as $account) {
            $allHoldings = $allHoldings->merge($account->holdings);
        }

        if ($allHoldings->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No holdings found',
            ];
        }

        $holdingsAnalysis = [];

        foreach ($allHoldings as $holding) {
            $ocf = $holding->ocf ?? $this->estimateOCF($holding->asset_type);
            $annualFee = $holding->current_value * $ocf;

            $holdingsAnalysis[] = [
                'holding_id' => $holding->id,
                'security_name' => $holding->security_name ?? $holding->ticker,
                'ticker' => $holding->ticker,
                'asset_type' => $holding->asset_type,
                'current_value' => $holding->current_value,
                'ocf' => round($ocf * 100, 3),
                'annual_fee' => round($annualFee, 2),
                'account_name' => $holding->holdable->account_name ?? 'Unknown',
            ];
        }

        // Sort by annual fee descending
        usort($holdingsAnalysis, fn ($a, $b) => $b['annual_fee'] <=> $a['annual_fee']);

        return [
            'success' => true,
            'holdings' => $holdingsAnalysis,
            'highest_cost_holdings' => array_slice($holdingsAnalysis, 0, 10),
            'total_holdings' => count($holdingsAnalysis),
        ];
    }

    /**
     * Calculate platform fee based on portfolio value and platform
     *
     * @param  float  $portfolioValue  Portfolio value
     * @param  string  $platformName  Platform name
     * @return float Annual platform fee
     */
    private function calculatePlatformFee(float $portfolioValue, string $platformName): float
    {
        // Platform fee tiers (UK typical)
        $platformFees = match (strtolower($platformName)) {
            'vanguard' => $this->calculateTieredFee($portfolioValue, [
                [0, 250000, 0.0015],
                [250000, PHP_FLOAT_MAX, 0.00375],
            ]),
            'hargreaves lansdown', 'hl' => $this->calculateTieredFee($portfolioValue, [
                [0, 250000, 0.0045],
                [250000, 1000000, 0.0025],
                [1000000, PHP_FLOAT_MAX, 0.0010],
            ]),
            'aj bell' => $this->calculateCappedFee($portfolioValue, 0.0025, 3.50, 7.50),
            'interactive investor', 'ii' => 9.99 * 12, // Flat monthly fee
            'fidelity' => $this->calculateCappedFee($portfolioValue, 0.0035, 0, 45),
            'charles stanley direct' => $this->calculateTieredFee($portfolioValue, [
                [0, 50000, 0.0025],
                [50000, 500000, 0.0015],
                [500000, PHP_FLOAT_MAX, 0.0010],
            ]),
            default => $portfolioValue * 0.0030, // Industry average ~0.30%
        };

        return $platformFees;
    }

    /**
     * Calculate tiered fee structure
     *
     * @param  float  $value  Portfolio value
     * @param  array  $tiers  Fee tiers [[min, max, rate], ...]
     * @return float Annual fee
     */
    private function calculateTieredFee(float $value, array $tiers): float
    {
        $totalFee = 0;

        foreach ($tiers as [$min, $max, $rate]) {
            if ($value <= $min) {
                break;
            }

            $tierValue = min($value, $max) - $min;
            $totalFee += $tierValue * $rate;

            if ($value <= $max) {
                break;
            }
        }

        return $totalFee;
    }

    /**
     * Calculate capped fee (percentage with min/max)
     *
     * @param  float  $value  Portfolio value
     * @param  float  $rate  Fee rate
     * @param  float  $minFee  Minimum annual fee
     * @param  float  $maxFee  Maximum annual fee
     * @return float Annual fee
     */
    private function calculateCappedFee(float $value, float $rate, float $minFee, float $maxFee): float
    {
        $fee = $value * $rate;

        return max($minFee, min($fee, $maxFee));
    }

    /**
     * Calculate weighted average OCF across holdings
     *
     * @param  Collection  $holdings  Holdings
     * @param  float  $totalValue  Total portfolio value
     * @return float Weighted average OCF
     */
    private function calculateWeightedOCF(Collection $holdings, float $totalValue): float
    {
        $weightedOCF = 0;

        foreach ($holdings as $holding) {
            $weight = $holding->current_value / $totalValue;
            $ocf = $holding->ocf ?? $this->estimateOCF($holding->asset_type);
            $weightedOCF += $weight * $ocf;
        }

        return $weightedOCF;
    }

    /**
     * Estimate OCF for asset type if not provided
     *
     * @param  string  $assetType  Asset type
     * @return float Estimated OCF
     */
    private function estimateOCF(string $assetType): float
    {
        return match ($assetType) {
            'index_fund', 'etf' => 0.001, // 0.10% for passive
            'active_fund' => 0.0075, // 0.75% for active
            'equity', 'stock' => 0.0, // No OCF for direct equities
            'bond' => 0.0005, // 0.05% for bond funds
            'alternative' => 0.015, // 1.5% for alternatives
            default => 0.005, // 0.50% default
        };
    }

    /**
     * Estimate annual transaction costs
     *
     * @param  float  $portfolioValue  Portfolio value
     * @param  float  $turnoverRate  Annual turnover rate (0-1)
     * @return float Estimated annual transaction costs
     */
    private function estimateTransactionCosts(float $portfolioValue, float $turnoverRate): float
    {
        // Typical transaction cost: 0.10% per transaction
        $costPerTransaction = 0.001;
        $annualTradedValue = $portfolioValue * $turnoverRate;

        return $annualTradedValue * $costPerTransaction;
    }

    /**
     * Calculate fee drag on returns over time
     *
     * @param  float  $initialValue  Initial portfolio value
     * @param  float  $feePercent  Annual fee percentage
     * @param  int  $years  Number of years
     * @param  float  $grossReturn  Gross annual return
     * @return array Fee drag analysis
     */
    private function calculateFeeDrag(float $initialValue, float $feePercent, int $years, float $grossReturn): array
    {
        $feeRate = $feePercent / 100;
        $netReturn = $grossReturn - $feeRate;

        $valueWithoutFees = $initialValue * pow(1 + $grossReturn, $years);
        $valueWithFees = $initialValue * pow(1 + $netReturn, $years);

        $feeDragValue = $valueWithoutFees - $valueWithFees;
        $feeDragPercent = ($feeDragValue / $valueWithoutFees) * 100;

        return [
            'years' => $years,
            'gross_return_percent' => $grossReturn * 100,
            'net_return_percent' => $netReturn * 100,
            'value_without_fees' => round($valueWithoutFees, 2),
            'value_with_fees' => round($valueWithFees, 2),
            'fee_drag_value' => round($feeDragValue, 2),
            'fee_drag_percent' => round($feeDragPercent, 1),
            'interpretation' => sprintf(
                'Over %d years, fees reduce your final portfolio by £%s (%.1f%%)',
                $years,
                number_format($feeDragValue, 0),
                $feeDragPercent
            ),
        ];
    }

    /**
     * Get benchmark fees for portfolio size
     *
     * @param  float  $portfolioValue  Portfolio value
     * @return array Benchmark fees
     */
    private function getBenchmarkFees(float $portfolioValue): array
    {
        // UK industry benchmarks (2025)
        if ($portfolioValue < 50000) {
            return [
                'typical_range' => [0.40, 0.80],
                'excellent' => 0.30,
                'good' => 0.50,
                'average' => 0.65,
                'high' => 0.80,
            ];
        } elseif ($portfolioValue < 250000) {
            return [
                'typical_range' => [0.30, 0.60],
                'excellent' => 0.25,
                'good' => 0.40,
                'average' => 0.50,
                'high' => 0.65,
            ];
        } else {
            return [
                'typical_range' => [0.20, 0.45],
                'excellent' => 0.15,
                'good' => 0.30,
                'average' => 0.40,
                'high' => 0.50,
            ];
        }
    }

    /**
     * Assess fee level against benchmark
     *
     * @param  float  $feePercent  Current fee percentage
     * @param  array  $benchmark  Benchmark fees
     * @return array Assessment
     */
    private function assessFeeLevel(float $feePercent, array $benchmark): array
    {
        if ($feePercent <= $benchmark['excellent']) {
            $level = 'excellent';
            $message = 'Excellent - Your fees are significantly below average';
        } elseif ($feePercent <= $benchmark['good']) {
            $level = 'good';
            $message = 'Good - Your fees are below average';
        } elseif ($feePercent <= $benchmark['average']) {
            $level = 'average';
            $message = 'Average - Your fees are in line with industry norms';
        } elseif ($feePercent <= $benchmark['high']) {
            $level = 'high';
            $message = 'High - Consider reviewing your platform and fund choices';
        } else {
            $level = 'very_high';
            $message = 'Very High - Significant savings available by switching platform or funds';
        }

        return [
            'level' => $level,
            'message' => $message,
            'your_fee' => round($feePercent, 3),
            'benchmark_average' => $benchmark['average'],
            'difference' => round($feePercent - $benchmark['average'], 3),
        ];
    }

    /**
     * Calculate potential savings
     *
     * @param  float  $currentFees  Current annual fees
     * @param  array  $benchmark  Benchmark fees
     * @param  float  $portfolioValue  Portfolio value
     * @return array Potential savings
     */
    private function calculatePotentialSavings(float $currentFees, array $benchmark, float $portfolioValue): array
    {
        $currentFeePercent = ($currentFees / $portfolioValue) * 100;

        // Calculate savings vs good benchmark
        $goodFees = $portfolioValue * ($benchmark['good'] / 100);
        $savingsVsGood = max(0, $currentFees - $goodFees);

        // Calculate savings vs excellent benchmark
        $excellentFees = $portfolioValue * ($benchmark['excellent'] / 100);
        $savingsVsExcellent = max(0, $currentFees - $excellentFees);

        return [
            'savings_vs_good' => round($savingsVsGood, 2),
            'savings_vs_excellent' => round($savingsVsExcellent, 2),
            'savings_10_years_good' => round($this->calculateCompoundSavings($portfolioValue, $savingsVsGood, 10, 0.06), 2),
            'savings_10_years_excellent' => round($this->calculateCompoundSavings($portfolioValue, $savingsVsExcellent, 10, 0.06), 2),
            'has_savings_opportunity' => $savingsVsGood > 100,
        ];
    }

    /**
     * Calculate compound savings over time
     *
     * @param  float  $portfolioValue  Portfolio value
     * @param  float  $annualSavings  Annual fee savings
     * @param  int  $years  Years
     * @param  float  $returnRate  Expected return rate
     * @return float Compound savings value
     */
    private function calculateCompoundSavings(float $portfolioValue, float $annualSavings, int $years, float $returnRate): float
    {
        if ($annualSavings == 0) {
            return 0;
        }

        $feePercent = ($annualSavings / $portfolioValue) * 100;

        return $portfolioValue * (pow(1 + $returnRate, $years) - pow(1 + $returnRate - ($feePercent / 100), $years));
    }
}
