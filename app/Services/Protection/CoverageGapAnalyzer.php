<?php

declare(strict_types=1);

namespace App\Services\Protection;

use App\Models\ProtectionProfile;
use Illuminate\Support\Collection;

class CoverageGapAnalyzer
{
    /**
     * Calculate human capital value.
     *
     * @param float $income
     * @param int $age
     * @param int $retirementAge
     * @return float
     */
    public function calculateHumanCapital(float $income, int $age, int $retirementAge): float
    {
        $yearsToRetirement = max(0, $retirementAge - $age);
        $multiplier = 10; // Standard rule of thumb
        $effectiveYears = min($yearsToRetirement, 10);

        return $income * $multiplier * $effectiveYears;
    }

    /**
     * Calculate debt protection need.
     *
     * @param ProtectionProfile $profile
     * @return float
     */
    public function calculateDebtProtectionNeed(ProtectionProfile $profile): float
    {
        return $profile->mortgage_balance + $profile->other_debts;
    }

    /**
     * Calculate education funding need.
     *
     * @param int $numChildren
     * @param array $ages
     * @return float
     */
    public function calculateEducationFunding(int $numChildren, array $ages): float
    {
        $totalFunding = 0;
        $annualCostPerChild = 9000; // £9,000 per year per child
        $educationEndAge = 21;

        foreach ($ages as $age) {
            $yearsRemaining = max(0, $educationEndAge - $age);
            $totalFunding += $annualCostPerChild * $yearsRemaining;
        }

        return $totalFunding;
    }

    /**
     * Calculate final expenses.
     *
     * @return float
     */
    public function calculateFinalExpenses(): float
    {
        return 7500; // Fixed amount for funeral and final expenses
    }

    /**
     * Calculate total coverage from policies.
     *
     * @param Collection $lifePolicies
     * @param Collection $criticalIllnessPolicies
     * @param Collection $incomeProtectionPolicies
     * @param Collection $disabilityPolicies
     * @param Collection $sicknessIllnessPolicies
     * @return array
     */
    public function calculateTotalCoverage(
        Collection $lifePolicies,
        Collection $criticalIllnessPolicies,
        Collection $incomeProtectionPolicies,
        Collection $disabilityPolicies,
        Collection $sicknessIllnessPolicies
    ): array {
        $lifeCoverage = $lifePolicies->sum('sum_assured');
        $criticalIllnessCoverage = $criticalIllnessPolicies->sum('sum_assured');

        // Income protection: annualized benefit amount
        $incomeProtectionCoverage = 0;
        foreach ($incomeProtectionPolicies as $policy) {
            if ($policy->benefit_frequency === 'monthly') {
                $incomeProtectionCoverage += $policy->benefit_amount * 12;
            } elseif ($policy->benefit_frequency === 'weekly') {
                $incomeProtectionCoverage += $policy->benefit_amount * 52;
            }
        }

        // Disability coverage: annualized benefit amount
        $disabilityCoverage = 0;
        foreach ($disabilityPolicies as $policy) {
            if ($policy->benefit_frequency === 'monthly') {
                $disabilityCoverage += $policy->benefit_amount * 12;
            } elseif ($policy->benefit_frequency === 'weekly') {
                $disabilityCoverage += $policy->benefit_amount * 52;
            }
        }

        // Sickness/Illness coverage: can be lump sum, monthly, or weekly
        $sicknessIllnessCoverage = 0;
        foreach ($sicknessIllnessPolicies as $policy) {
            if ($policy->benefit_frequency === 'monthly') {
                $sicknessIllnessCoverage += $policy->benefit_amount * 12;
            } elseif ($policy->benefit_frequency === 'weekly') {
                $sicknessIllnessCoverage += $policy->benefit_amount * 52;
            } elseif ($policy->benefit_frequency === 'lump_sum') {
                $sicknessIllnessCoverage += $policy->benefit_amount;
            }
        }

        return [
            'life_coverage' => $lifeCoverage,
            'critical_illness_coverage' => $criticalIllnessCoverage,
            'income_protection_coverage' => $incomeProtectionCoverage,
            'disability_coverage' => $disabilityCoverage,
            'sickness_illness_coverage' => $sicknessIllnessCoverage,
            'total_coverage' => $lifeCoverage + $criticalIllnessCoverage,
            'total_income_coverage' => $incomeProtectionCoverage + $disabilityCoverage + $sicknessIllnessCoverage,
        ];
    }

    /**
     * Calculate coverage gaps.
     *
     * @param array $needs
     * @param array $coverage
     * @return array
     */
    public function calculateCoverageGap(array $needs, array $coverage): array
    {
        $totalNeed = $needs['human_capital']
                   + $needs['debt_protection']
                   + $needs['education_funding']
                   + $needs['final_expenses'];

        $totalCoverage = $coverage['total_coverage'];
        $gap = max(0, $totalNeed - $totalCoverage);

        // Calculate total income-based coverage from all income replacement policies
        $totalIncomeCoverage = $coverage['income_protection_coverage']
                             + $coverage['disability_coverage']
                             + $coverage['sickness_illness_coverage'];

        return [
            'total_need' => $totalNeed,
            'total_coverage' => $totalCoverage,
            'total_gap' => $gap,
            'gaps_by_category' => [
                'human_capital_gap' => max(0, $needs['human_capital'] - $coverage['life_coverage']),
                'debt_protection_gap' => max(0, $needs['debt_protection'] - $coverage['life_coverage']),
                'education_funding_gap' => max(0, $needs['education_funding'] - $coverage['life_coverage']),
                'income_protection_gap' => max(0, $needs['income_protection_need'] - $totalIncomeCoverage),
                'disability_coverage_gap' => max(0, $needs['income_protection_need'] - $totalIncomeCoverage),
                'sickness_illness_gap' => max(0, $needs['income_protection_need'] * 0.5 - $coverage['sickness_illness_coverage']),
            ],
            'coverage_percentage' => $totalNeed > 0 ? ($totalCoverage / $totalNeed) * 100 : 100,
        ];
    }

    /**
     * Calculate total protection needs.
     *
     * @param ProtectionProfile $profile
     * @return array
     */
    public function calculateProtectionNeeds(ProtectionProfile $profile): array
    {
        $age = $profile->user->date_of_birth ?
               (int) $profile->user->date_of_birth->diffInYears(now()) : 40;

        $humanCapital = $this->calculateHumanCapital(
            $profile->annual_income,
            $age,
            $profile->retirement_age
        );

        $debtProtection = $this->calculateDebtProtectionNeed($profile);

        $educationFunding = $this->calculateEducationFunding(
            $profile->number_of_dependents,
            $profile->dependents_ages ?? []
        );

        $finalExpenses = $this->calculateFinalExpenses();

        // Income protection need: typically 50-70% of gross income
        $incomeProtectionNeed = $profile->annual_income * 0.6;

        return [
            'human_capital' => $humanCapital,
            'debt_protection' => $debtProtection,
            'education_funding' => $educationFunding,
            'final_expenses' => $finalExpenses,
            'income_protection_need' => $incomeProtectionNeed,
            'total_need' => $humanCapital + $debtProtection + $educationFunding + $finalExpenses,
        ];
    }
}
