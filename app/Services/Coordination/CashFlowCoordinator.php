<?php

declare(strict_types=1);

namespace App\Services\Coordination;

use App\Models\User;

/**
 * CashFlowCoordinator
 *
 * Coordinates cashflow allocation across all modules.
 * Calculates available surplus and optimizes contribution allocation.
 */
class CashFlowCoordinator
{
    /**
     * Calculate available monthly surplus
     *
     * @return float Monthly surplus after all expenses
     */
    public function calculateAvailableSurplus(int $userId): float
    {
        // Get user data
        // Note: In full implementation, this would query a PersonalFinance or CashFlow table
        // For now, return a placeholder value that can be overridden by passing data directly

        return 1000; // Placeholder - £1000 monthly surplus
    }

    /**
     * Optimize contribution allocation across all needs
     *
     * @param  float  $surplus  Available monthly surplus
     * @param  array  $demands  Contribution demands from all modules
     * @return array Optimized allocation
     */
    public function optimizeContributionAllocation(float $surplus, array $demands): array
    {
        // Priority order: Emergency fund → Protection → Pension → Investment → Estate
        $priorityOrder = [
            'emergency_fund' => 1,
            'protection' => 2,
            'pension' => 3,
            'investment' => 4,
            'estate' => 5,
        ];

        // Sort demands by priority
        $sortedDemands = [];
        foreach ($demands as $category => $demand) {
            $sortedDemands[] = [
                'category' => $category,
                'amount' => $demand['amount'] ?? 0,
                'urgency' => $demand['urgency'] ?? 50,
                'priority' => $priorityOrder[$category] ?? 999,
            ];
        }

        // Sort by urgency first (if critical), then by priority
        usort($sortedDemands, function ($a, $b) {
            if ($a['urgency'] >= 80 && $b['urgency'] < 80) {
                return -1;
            }
            if ($b['urgency'] >= 80 && $a['urgency'] < 80) {
                return 1;
            }

            return $a['priority'] <=> $b['priority'];
        });

        // Allocate surplus in priority order
        $allocation = [];
        $remaining = $surplus;

        foreach ($sortedDemands as $demand) {
            $category = $demand['category'];

            if ($remaining <= 0) {
                $allocation[$category] = [
                    'allocated' => 0,
                    'requested' => $demand['amount'],
                    'shortfall' => $demand['amount'],
                    'percent_funded' => 0,
                ];

                continue;
            }

            if ($remaining >= $demand['amount']) {
                // Fully fund this demand
                $allocation[$category] = [
                    'allocated' => $demand['amount'],
                    'requested' => $demand['amount'],
                    'shortfall' => 0,
                    'percent_funded' => 100,
                ];
                $remaining -= $demand['amount'];
            } else {
                // Partially fund with remaining surplus
                $allocation[$category] = [
                    'allocated' => $remaining,
                    'requested' => $demand['amount'],
                    'shortfall' => $demand['amount'] - $remaining,
                    'percent_funded' => round(($remaining / $demand['amount']) * 100, 2),
                ];
                $remaining = 0;
            }
        }

        $totalDemand = array_sum(array_column($sortedDemands, 'amount'));

        return [
            'total_demand' => $totalDemand,
            'available_surplus' => $surplus,
            'allocation' => $allocation,
            'total_shortfall' => max(0, $totalDemand - $surplus),
            'surplus_remaining' => max(0, $remaining),
            'allocation_efficiency' => $surplus > 0 ? round((($surplus - $remaining) / $surplus) * 100, 2) : 0,
        ];
    }

    /**
     * Identify cashflow shortfalls
     *
     * @param  array  $allocation  Allocation result
     * @return array Shortfall analysis
     */
    public function identifyCashFlowShortfalls(array $allocation): array
    {
        $shortfalls = [];

        if ($allocation['total_shortfall'] <= 0) {
            return [
                'has_shortfall' => false,
                'total_shortfall' => 0,
                'shortfalls' => [],
                'recommendations' => ['Your cashflow is sufficient to meet all recommended contributions.'],
            ];
        }

        // Identify specific shortfalls
        foreach ($allocation['allocation'] as $category => $details) {
            if ($details['shortfall'] > 0) {
                $shortfalls[] = [
                    'category' => $category,
                    'shortfall' => $details['shortfall'],
                    'percent_funded' => $details['percent_funded'],
                ];
            }
        }

        $recommendations = $this->generateShortfallRecommendations($allocation['total_shortfall'], $shortfalls);

        return [
            'has_shortfall' => true,
            'total_shortfall' => $allocation['total_shortfall'],
            'shortfalls' => $shortfalls,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Get current contributions across all modules
     *
     * @return float Total current monthly contributions
     */
    private function getCurrentContributions(int $userId): float
    {
        $total = 0;

        // This would sum up:
        // - Current protection premiums
        // - Current savings contributions
        // - Current investment contributions
        // - Current pension contributions
        // For now, return 0 (would need to query each module's data)

        return $total;
    }

    /**
     * Generate recommendations to address shortfalls
     */
    private function generateShortfallRecommendations(float $totalShortfall, array $shortfalls): array
    {
        $recommendations = [];

        // Recommend income increase
        $recommendations[] = 'Consider increasing income by £'.number_format($totalShortfall, 2).' per month to fully fund all recommendations.';

        // Recommend expense reduction
        $recommendations[] = 'Review monthly expenses to identify £'.number_format($totalShortfall, 2).' in potential savings.';

        // Prioritize critical areas
        $criticalShortfalls = array_filter($shortfalls, fn ($s) => in_array($s['category'], ['emergency_fund', 'protection']));
        if (count($criticalShortfalls) > 0) {
            $recommendations[] = 'Priority should be given to funding critical areas: '.implode(', ', array_column($criticalShortfalls, 'category')).'.';
        }

        // Phased approach
        if ($totalShortfall > 500) {
            $recommendations[] = 'Consider a phased approach: start with highest priority items and gradually increase contributions as income grows.';
        }

        // One-time windfall
        $recommendations[] = 'Use any bonuses, tax refunds, or windfalls to fund initial gaps or build reserves.';

        return $recommendations;
    }

    /**
     * Create cashflow allocation chart data
     *
     * @return array Chart data for ApexCharts
     */
    public function createCashFlowChartData(int $userId, array $allocation): array
    {
        // Note: In full implementation, fetch from PersonalFinance model
        $monthlyIncome = 4500; // Placeholder
        $monthlyExpenses = 3200; // Placeholder

        $categories = [];
        $allocatedAmounts = [];

        // Base expenses
        $categories[] = 'Living Expenses';
        $allocatedAmounts[] = $monthlyExpenses;

        // Allocated contributions
        foreach ($allocation['allocation'] as $category => $details) {
            if ($details['allocated'] > 0) {
                $categories[] = ucwords(str_replace('_', ' ', $category));
                $allocatedAmounts[] = $details['allocated'];
            }
        }

        // Remaining surplus
        if ($allocation['surplus_remaining'] > 0) {
            $categories[] = 'Unallocated Surplus';
            $allocatedAmounts[] = $allocation['surplus_remaining'];
        }

        return [
            'series' => [
                [
                    'name' => 'Monthly Allocation',
                    'data' => $allocatedAmounts,
                ],
            ],
            'categories' => $categories,
            'total_income' => $monthlyIncome,
            'total_allocated' => array_sum($allocatedAmounts),
            'allocation_percent' => $monthlyIncome > 0 ? round((array_sum($allocatedAmounts) / $monthlyIncome) * 100, 2) : 0,
        ];
    }

    /**
     * Calculate sustainable contribution level
     *
     * Based on 50/30/20 rule: 50% needs, 30% wants, 20% savings/investments
     */
    public function calculateSustainableContributions(float $monthlyIncome, float $monthlyExpenses): array
    {
        $needsPercent = 0.50;
        $wantsPercent = 0.30;
        $savingsPercent = 0.20;

        $maxNeeds = $monthlyIncome * $needsPercent;
        $maxWants = $monthlyIncome * $wantsPercent;
        $recommendedSavings = $monthlyIncome * $savingsPercent;

        $currentExpenseRatio = $monthlyIncome > 0 ? $monthlyExpenses / $monthlyIncome : 0;

        return [
            'monthly_income' => $monthlyIncome,
            'current_expenses' => $monthlyExpenses,
            'current_expense_ratio' => round($currentExpenseRatio * 100, 2),
            'recommended_savings_amount' => $recommendedSavings,
            'recommended_savings_percent' => 20,
            'is_sustainable' => $monthlyExpenses <= $maxNeeds + $maxWants,
            'expense_reduction_needed' => max(0, $monthlyExpenses - ($maxNeeds + $maxWants)),
        ];
    }
}
