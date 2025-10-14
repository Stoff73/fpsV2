<?php

declare(strict_types=1);

namespace App\Services\Protection;

use App\Models\ProtectionProfile;

class RecommendationEngine
{
    /**
     * Generate recommendations based on coverage gaps.
     *
     * @param array $gaps
     * @param ProtectionProfile $profile
     * @return array
     */
    public function generateRecommendations(array $gaps, ProtectionProfile $profile): array
    {
        $recommendations = [];

        // Life insurance gap
        if ($gaps['gaps_by_category']['human_capital_gap'] > 10000) {
            $recommendations[] = $this->createRecommendation(
                priority: $this->calculatePriority($gaps['gaps_by_category']['human_capital_gap'], $profile),
                category: 'Life Insurance',
                action: 'Increase life insurance coverage',
                rationale: sprintf(
                    'Current coverage falls short by £%s. This gap could leave your dependents financially vulnerable.',
                    number_format($gaps['gaps_by_category']['human_capital_gap'], 2)
                ),
                impact: 'High',
                estimatedCost: $this->estimateLifePremium($gaps['gaps_by_category']['human_capital_gap'], $profile)
            );
        }

        // Debt protection gap
        if ($gaps['gaps_by_category']['debt_protection_gap'] > 0) {
            $recommendations[] = $this->createRecommendation(
                priority: $this->calculatePriority($gaps['gaps_by_category']['debt_protection_gap'], $profile),
                category: 'Life Insurance',
                action: 'Add decreasing term cover for debts',
                rationale: sprintf(
                    'Outstanding debts of £%s should be covered separately to protect your estate.',
                    number_format($profile->mortgage_balance + $profile->other_debts, 2)
                ),
                impact: 'High',
                estimatedCost: $this->estimateDebtProtectionPremium($profile)
            );
        }

        // Critical illness gap
        if ($gaps['gaps_by_category']['human_capital_gap'] > 0 && empty($profile->user->criticalIllnessPolicies)) {
            $recommendations[] = $this->createRecommendation(
                priority: 2,
                category: 'Critical Illness',
                action: 'Consider critical illness cover',
                rationale: 'Critical illness cover would provide a lump sum if you are diagnosed with a serious condition.',
                impact: 'Medium',
                estimatedCost: $this->estimateCriticalIllnessPremium($profile->annual_income * 3, $profile)
            );
        }

        // Income protection gap
        if ($gaps['gaps_by_category']['income_protection_gap'] > 0) {
            $recommendations[] = $this->createRecommendation(
                priority: $this->calculatePriority($gaps['gaps_by_category']['income_protection_gap'], $profile),
                category: 'Income Protection',
                action: 'Add income protection insurance',
                rationale: sprintf(
                    'Income protection would replace £%s per year if you cannot work due to illness or injury.',
                    number_format($gaps['gaps_by_category']['income_protection_gap'], 2)
                ),
                impact: 'High',
                estimatedCost: $this->estimateIncomeProtectionPremium($gaps['gaps_by_category']['income_protection_gap'], $profile)
            );
        }

        // Education funding gap
        if ($gaps['gaps_by_category']['education_funding_gap'] > 0 && $profile->number_of_dependents > 0) {
            $recommendations[] = $this->createRecommendation(
                priority: 3,
                category: 'Life Insurance',
                action: 'Consider family income benefit policy',
                rationale: sprintf(
                    'A family income benefit policy could provide regular income to cover education costs for your %d dependent(s).',
                    $profile->number_of_dependents
                ),
                impact: 'Medium',
                estimatedCost: $this->estimateFamilyIncomeBenefitPremium($profile)
            );
        }

        // Trust recommendation
        if ($profile->user->lifeInsurancePolicies()->where('in_trust', false)->exists()) {
            $recommendations[] = $this->createRecommendation(
                priority: 4,
                category: 'Trust Planning',
                action: 'Place policies in trust',
                rationale: 'Policies not in trust may be subject to inheritance tax and probate delays.',
                impact: 'Medium',
                estimatedCost: 0
            );
        }

        // Policy optimization
        $totalPremiums = $this->calculateTotalPremiums($profile);
        if ($totalPremiums > $profile->annual_income * 0.05) {
            $recommendations[] = $this->createRecommendation(
                priority: 5,
                category: 'Policy Optimization',
                action: 'Review and optimize existing policies',
                rationale: sprintf(
                    'Total premiums of £%s per year exceed 5%% of income. Consider reviewing for better value.',
                    number_format($totalPremiums, 2)
                ),
                impact: 'Low',
                estimatedCost: 0
            );
        }

        // Sort by priority
        usort($recommendations, fn($a, $b) => $a['priority'] <=> $b['priority']);

        return $recommendations;
    }

    /**
     * Create a standardized recommendation array.
     *
     * @param int $priority
     * @param string $category
     * @param string $action
     * @param string $rationale
     * @param string $impact
     * @param float $estimatedCost
     * @return array
     */
    private function createRecommendation(
        int $priority,
        string $category,
        string $action,
        string $rationale,
        string $impact,
        float $estimatedCost
    ): array {
        return [
            'priority' => $priority,
            'category' => $category,
            'action' => $action,
            'rationale' => $rationale,
            'impact' => $impact,
            'estimated_cost' => $estimatedCost,
        ];
    }

    /**
     * Calculate recommendation priority.
     *
     * @param float $gap
     * @param ProtectionProfile $profile
     * @return int
     */
    private function calculatePriority(float $gap, ProtectionProfile $profile): int
    {
        $gapRatio = $profile->annual_income > 0 ? $gap / $profile->annual_income : 0;

        return match (true) {
            $gapRatio > 5 => 1, // Critical
            $gapRatio > 2 => 2, // High
            $gapRatio > 1 => 3, // Medium
            default => 4,       // Low
        };
    }

    /**
     * Estimate life insurance premium.
     *
     * @param float $sumAssured
     * @param ProtectionProfile $profile
     * @return float
     */
    private function estimateLifePremium(float $sumAssured, ProtectionProfile $profile): float
    {
        // Simplified premium estimation: £0.50 per £1,000 sum assured per year
        // Adjust for smoker status (+50%) and age
        $basePremium = ($sumAssured / 1000) * 0.50;

        if ($profile->smoker_status) {
            $basePremium *= 1.5;
        }

        // Adjust for age (very simplified)
        $age = $profile->user->date_of_birth ?
               (int) $profile->user->date_of_birth->diffInYears(now()) : 40;

        if ($age > 50) {
            $basePremium *= 1.5;
        } elseif ($age > 40) {
            $basePremium *= 1.2;
        }

        return round($basePremium / 12, 2); // Monthly premium
    }

    /**
     * Estimate debt protection premium.
     *
     * @param ProtectionProfile $profile
     * @return float
     */
    private function estimateDebtProtectionPremium(ProtectionProfile $profile): float
    {
        $debtAmount = $profile->mortgage_balance + $profile->other_debts;
        return $this->estimateLifePremium($debtAmount, $profile) * 0.8; // Decreasing term is cheaper
    }

    /**
     * Estimate critical illness premium.
     *
     * @param float $sumAssured
     * @param ProtectionProfile $profile
     * @return float
     */
    private function estimateCriticalIllnessPremium(float $sumAssured, ProtectionProfile $profile): float
    {
        // Critical illness is typically 2-3x more expensive than life insurance
        return $this->estimateLifePremium($sumAssured, $profile) * 2.5;
    }

    /**
     * Estimate income protection premium.
     *
     * @param float $annualBenefit
     * @param ProtectionProfile $profile
     * @return float
     */
    private function estimateIncomeProtectionPremium(float $annualBenefit, ProtectionProfile $profile): float
    {
        // Typically 1-3% of annual benefit
        $basePremium = $annualBenefit * 0.02;

        if ($profile->smoker_status) {
            $basePremium *= 1.3;
        }

        return round($basePremium / 12, 2); // Monthly premium
    }

    /**
     * Estimate family income benefit premium.
     *
     * @param ProtectionProfile $profile
     * @return float
     */
    private function estimateFamilyIncomeBenefitPremium(ProtectionProfile $profile): float
    {
        // FIB is typically cheaper than level term
        $annualIncome = $profile->monthly_expenditure * 12;
        return $this->estimateLifePremium($annualIncome * 10, $profile) * 0.7;
    }

    /**
     * Calculate total current premiums.
     *
     * @param ProtectionProfile $profile
     * @return float
     */
    private function calculateTotalPremiums(ProtectionProfile $profile): float
    {
        $totalPremiums = 0;

        foreach ($profile->user->lifeInsurancePolicies as $policy) {
            $premium = $policy->premium_amount;
            if ($policy->premium_frequency === 'monthly') {
                $premium *= 12;
            } elseif ($policy->premium_frequency === 'quarterly') {
                $premium *= 4;
            }
            $totalPremiums += $premium;
        }

        foreach ($profile->user->criticalIllnessPolicies as $policy) {
            $premium = $policy->premium_amount;
            if ($policy->premium_frequency === 'monthly') {
                $premium *= 12;
            } elseif ($policy->premium_frequency === 'quarterly') {
                $premium *= 4;
            }
            $totalPremiums += $premium;
        }

        foreach ($profile->user->incomeProtectionPolicies as $policy) {
            $premium = $policy->premium_amount;
            $totalPremiums += $premium * 12; // Stored as monthly
        }

        return $totalPremiums;
    }
}
