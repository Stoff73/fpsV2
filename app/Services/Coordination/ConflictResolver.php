<?php

declare(strict_types=1);

namespace App\Services\Coordination;

/**
 * ConflictResolver
 *
 * Identifies and resolves conflicts between recommendations from different modules.
 * Handles competing demands for:
 * - Cashflow allocation (emergency fund vs. pension vs. investment)
 * - ISA allowance (Cash ISA vs. Stocks & Shares ISA)
 * - Protection vs. savings priorities
 */
class ConflictResolver
{
    /**
     * Identify conflicts between recommendations from different modules
     *
     * @param  array  $recommendations  All recommendations from all modules
     * @return array Array of identified conflicts
     */
    public function identifyConflicts(array $recommendations): array
    {
        $conflicts = [];

        // Check for cashflow conflicts
        $cashflowConflict = $this->detectCashflowConflicts($recommendations);
        if ($cashflowConflict) {
            $conflicts[] = $cashflowConflict;
        }

        // Check for ISA allowance conflicts
        $isaConflict = $this->detectISAConflicts($recommendations);
        if ($isaConflict) {
            $conflicts[] = $isaConflict;
        }

        // Check for protection vs. savings conflicts
        $protectionSavingsConflict = $this->detectProtectionVsSavingsConflicts($recommendations);
        if ($protectionSavingsConflict) {
            $conflicts[] = $protectionSavingsConflict;
        }

        return $conflicts;
    }

    /**
     * Resolve conflicts between Protection and Savings recommendations
     *
     * @param  array  $recommendations  All recommendations
     * @return array Resolved recommendations
     */
    public function resolveProtectionVsSavings(array $recommendations): array
    {
        $protectionRecs = $this->filterByModule($recommendations, 'protection');
        $savingsRecs = $this->filterByModule($recommendations, 'savings');

        // If both recommend increased contributions, balance based on adequacy scores
        $protectionAdequacy = $recommendations['module_scores']['protection']['adequacy_score'] ?? 0;
        $emergencyFundAdequacy = $recommendations['module_scores']['savings']['emergency_fund_adequacy'] ?? 0;

        // Priority to whichever has lower adequacy score
        if ($protectionAdequacy < 50 && $emergencyFundAdequacy < 50) {
            // Both critical - split available funds
            return [
                'resolution' => 'split_priority',
                'allocation' => [
                    'protection' => 0.6, // Slight priority to protection (risk of death)
                    'savings' => 0.4,
                ],
                'reasoning' => 'Both protection and emergency fund are critically low. Prioritize protection slightly as it addresses catastrophic risk.',
            ];
        } elseif ($protectionAdequacy < $emergencyFundAdequacy) {
            return [
                'resolution' => 'protection_priority',
                'allocation' => [
                    'protection' => 0.8,
                    'savings' => 0.2,
                ],
                'reasoning' => 'Protection gap is more severe than emergency fund shortfall.',
            ];
        } else {
            return [
                'resolution' => 'savings_priority',
                'allocation' => [
                    'protection' => 0.2,
                    'savings' => 0.8,
                ],
                'reasoning' => 'Emergency fund is more critical than protection gap.',
            ];
        }
    }

    /**
     * Resolve contribution conflicts when multiple modules demand contributions
     *
     * @param  float  $availableSurplus  Monthly surplus available for contributions
     * @param  array  $demands  Contribution demands from each module
     * @return array Optimized allocation
     */
    public function resolveContributionConflicts(float $availableSurplus, array $demands): array
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
        foreach ($demands as $category => $amount) {
            $sortedDemands[] = [
                'category' => $category,
                'amount' => $amount,
                'priority' => $priorityOrder[$category] ?? 999,
            ];
        }

        usort($sortedDemands, fn ($a, $b) => $a['priority'] <=> $b['priority']);

        // Allocate surplus in priority order
        $allocation = [];
        $remaining = $availableSurplus;

        foreach ($sortedDemands as $demand) {
            if ($remaining <= 0) {
                $allocation[$demand['category']] = 0;

                continue;
            }

            if ($remaining >= $demand['amount']) {
                // Fully fund this demand
                $allocation[$demand['category']] = $demand['amount'];
                $remaining -= $demand['amount'];
            } else {
                // Partially fund with remaining surplus
                $allocation[$demand['category']] = $remaining;
                $remaining = 0;
            }
        }

        return [
            'total_demand' => array_sum(array_column($sortedDemands, 'amount')),
            'available_surplus' => $availableSurplus,
            'allocation' => $allocation,
            'shortfall' => max(0, array_sum(array_column($sortedDemands, 'amount')) - $availableSurplus),
            'surplus_remaining' => max(0, $remaining),
        ];
    }

    /**
     * Resolve ISA allowance conflicts between Cash ISA and Stocks & Shares ISA
     *
     * @param  float  $isaAllowance  Total ISA allowance (£20,000 for 2024/25)
     * @param  array  $demands  ISA demands from Savings and Investment modules
     * @return array Optimal ISA allocation
     */
    public function resolveISAAllocation(float $isaAllowance, array $demands): array
    {
        $cashISADemand = $demands['cash_isa'] ?? 0;
        $stocksSharesISADemand = $demands['stocks_shares_isa'] ?? 0;
        $totalDemand = $cashISADemand + $stocksSharesISADemand;

        // Get user context for optimal split
        $emergencyFundAdequacy = $demands['emergency_fund_adequacy'] ?? 100;
        $investmentGoalUrgency = $demands['investment_goal_urgency'] ?? 50;
        $riskTolerance = $demands['risk_tolerance'] ?? 'medium';

        // Determine optimal split based on user context
        if ($emergencyFundAdequacy < 50) {
            // Emergency fund critical - prioritize Cash ISA
            $cashISAAllocation = min($cashISADemand, $isaAllowance);
            $stocksSharesISAAllocation = max(0, $isaAllowance - $cashISAAllocation);
            $reasoning = 'Emergency fund is critically low. Prioritize Cash ISA for liquidity.';
        } elseif ($riskTolerance === 'low') {
            // Low risk tolerance - favor Cash ISA
            $cashISAAllocation = min($cashISADemand, $isaAllowance * 0.7);
            $stocksSharesISAAllocation = min($stocksSharesISADemand, $isaAllowance - $cashISAAllocation);
            $reasoning = 'Low risk tolerance. Favor Cash ISA (70%) over Stocks & Shares ISA (30%).';
        } elseif ($investmentGoalUrgency > 75 && $riskTolerance === 'high') {
            // High growth goals - prioritize Stocks & Shares ISA
            $stocksSharesISAAllocation = min($stocksSharesISADemand, $isaAllowance * 0.9);
            $cashISAAllocation = min($cashISADemand, $isaAllowance - $stocksSharesISAAllocation);
            $reasoning = 'High growth goals with high risk tolerance. Prioritize Stocks & Shares ISA (90%).';
        } else {
            // Balanced approach
            if ($totalDemand <= $isaAllowance) {
                // Can satisfy both
                $cashISAAllocation = $cashISADemand;
                $stocksSharesISAAllocation = $stocksSharesISADemand;
                $reasoning = 'Sufficient ISA allowance to satisfy both Cash ISA and Stocks & Shares ISA demands.';
            } else {
                // Proportional split based on demands
                $cashISAAllocation = ($cashISADemand / $totalDemand) * $isaAllowance;
                $stocksSharesISAAllocation = ($stocksSharesISADemand / $totalDemand) * $isaAllowance;
                $reasoning = 'ISA allowance split proportionally between Cash ISA and Stocks & Shares ISA based on demands.';
            }
        }

        return [
            'total_allowance' => $isaAllowance,
            'total_demand' => $totalDemand,
            'allocation' => [
                'cash_isa' => round($cashISAAllocation, 2),
                'stocks_shares_isa' => round($stocksSharesISAAllocation, 2),
            ],
            'unallocated' => round(max(0, $isaAllowance - $cashISAAllocation - $stocksSharesISAAllocation), 2),
            'shortfall' => round(max(0, $totalDemand - $isaAllowance), 2),
            'reasoning' => $reasoning,
        ];
    }

    /**
     * Detect cashflow conflicts (total demands exceed surplus)
     *
     * @return array|null Conflict details or null
     */
    private function detectCashflowConflicts(array $recommendations): ?array
    {
        $demands = [];
        $totalDemand = 0;

        // Extract contribution demands from each module
        foreach ($recommendations as $module => $moduleRecs) {
            if (! is_array($moduleRecs) || $module === 'module_scores') {
                continue;
            }

            foreach ($moduleRecs as $rec) {
                if (isset($rec['recommended_monthly_contribution'])) {
                    $category = $this->mapModuleToCategory($module);
                    $demands[$category] = ($demands[$category] ?? 0) + $rec['recommended_monthly_contribution'];
                    $totalDemand += $rec['recommended_monthly_contribution'];
                }
            }
        }

        $availableSurplus = $recommendations['available_surplus'] ?? 0;

        if ($totalDemand > $availableSurplus && $totalDemand > 0) {
            return [
                'type' => 'cashflow_conflict',
                'total_demand' => $totalDemand,
                'available_surplus' => $availableSurplus,
                'shortfall' => $totalDemand - $availableSurplus,
                'demands' => $demands,
                'severity' => $this->calculateConflictSeverity($totalDemand, $availableSurplus),
            ];
        }

        return null;
    }

    /**
     * Detect ISA allowance conflicts (demands exceed £20,000 allowance)
     *
     * @return array|null Conflict details or null
     */
    private function detectISAConflicts(array $recommendations): ?array
    {
        $isaAllowance = 20000; // 2024/25 tax year
        $cashISADemand = 0;
        $stocksSharesISADemand = 0;

        // Check Savings module for Cash ISA demand
        if (isset($recommendations['savings'])) {
            foreach ($recommendations['savings'] as $rec) {
                if (isset($rec['recommended_cash_isa_contribution'])) {
                    $cashISADemand += $rec['recommended_cash_isa_contribution'];
                }
            }
        }

        // Check Investment module for Stocks & Shares ISA demand
        if (isset($recommendations['investment'])) {
            foreach ($recommendations['investment'] as $rec) {
                if (isset($rec['recommended_isa_contribution'])) {
                    $stocksSharesISADemand += $rec['recommended_isa_contribution'];
                }
            }
        }

        $totalISADemand = $cashISADemand + $stocksSharesISADemand;

        if ($totalISADemand > $isaAllowance) {
            return [
                'type' => 'isa_allowance_conflict',
                'total_allowance' => $isaAllowance,
                'total_demand' => $totalISADemand,
                'shortfall' => $totalISADemand - $isaAllowance,
                'demands' => [
                    'cash_isa' => $cashISADemand,
                    'stocks_shares_isa' => $stocksSharesISADemand,
                ],
                'severity' => $this->calculateConflictSeverity($totalISADemand, $isaAllowance),
            ];
        }

        return null;
    }

    /**
     * Detect protection vs. savings conflicts
     *
     * @return array|null Conflict details or null
     */
    private function detectProtectionVsSavingsConflicts(array $recommendations): ?array
    {
        $protectionDemand = 0;
        $savingsDemand = 0;

        // Check Protection module
        if (isset($recommendations['protection'])) {
            foreach ($recommendations['protection'] as $rec) {
                if (isset($rec['recommended_monthly_premium'])) {
                    $protectionDemand += $rec['recommended_monthly_premium'];
                }
            }
        }

        // Check Savings module
        if (isset($recommendations['savings'])) {
            foreach ($recommendations['savings'] as $rec) {
                if (isset($rec['recommended_monthly_contribution'])) {
                    $savingsDemand += $rec['recommended_monthly_contribution'];
                }
            }
        }

        // Conflict exists if both have demands and adequacy scores are low
        $protectionAdequacy = $recommendations['module_scores']['protection']['adequacy_score'] ?? 100;
        $emergencyFundAdequacy = $recommendations['module_scores']['savings']['emergency_fund_adequacy'] ?? 100;

        if ($protectionDemand > 0 && $savingsDemand > 0 && ($protectionAdequacy < 75 || $emergencyFundAdequacy < 75)) {
            return [
                'type' => 'protection_vs_savings_conflict',
                'protection_demand' => $protectionDemand,
                'savings_demand' => $savingsDemand,
                'protection_adequacy' => $protectionAdequacy,
                'emergency_fund_adequacy' => $emergencyFundAdequacy,
                'severity' => min($protectionAdequacy, $emergencyFundAdequacy) < 50 ? 'high' : 'medium',
            ];
        }

        return null;
    }

    /**
     * Filter recommendations by module
     */
    private function filterByModule(array $recommendations, string $module): array
    {
        return $recommendations[$module] ?? [];
    }

    /**
     * Map module name to contribution category
     */
    private function mapModuleToCategory(string $module): string
    {
        $mapping = [
            'protection' => 'protection',
            'savings' => 'emergency_fund',
            'investment' => 'investment',
            'retirement' => 'pension',
            'estate' => 'estate',
        ];

        return $mapping[$module] ?? $module;
    }

    /**
     * Calculate conflict severity
     */
    private function calculateConflictSeverity(float $demand, float $available): string
    {
        if ($available == 0) {
            return 'critical';
        }

        $ratio = $demand / $available;

        if ($ratio >= 2.0) {
            return 'critical';
        } elseif ($ratio >= 1.5) {
            return 'high';
        } elseif ($ratio >= 1.2) {
            return 'medium';
        } else {
            return 'low';
        }
    }
}
