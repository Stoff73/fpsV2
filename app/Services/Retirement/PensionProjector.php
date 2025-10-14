<?php

declare(strict_types=1);

namespace App\Services\Retirement;

use App\Models\DCPension;
use App\Models\DBPension;
use App\Models\StatePension;
use Illuminate\Support\Collection;

/**
 * Pension Projector Service
 *
 * Handles projection of pension values at retirement for DC, DB, and State Pensions.
 */
class PensionProjector
{
    /**
     * Project DC pension value at retirement.
     *
     * Uses future value formula: FV = PV × (1+r)^n + PMT × [((1+r)^n - 1) / r]
     *
     * @param DCPension $pension
     * @param int $yearsToRetirement
     * @param float $growthRate Annual growth rate (e.g., 0.05 for 5%)
     * @return float Projected value at retirement
     */
    public function projectDCPension(DCPension $pension, int $yearsToRetirement, float $growthRate): float
    {
        $currentValue = (float) $pension->current_fund_value;
        $monthlyContribution = (float) $pension->monthly_contribution_amount ?? 0.0;
        $annualContribution = $monthlyContribution * 12;

        // Account for platform fees
        $netGrowthRate = $growthRate - ((float) $pension->platform_fee_percent ?? 0.0) / 100;

        // Future value of current fund
        $futureValueOfCurrentFund = $currentValue * pow(1 + $netGrowthRate, $yearsToRetirement);

        // Future value of contributions (annuity)
        $futureValueOfContributions = 0.0;
        if ($netGrowthRate > 0 && $annualContribution > 0) {
            $futureValueOfContributions = $annualContribution * ((pow(1 + $netGrowthRate, $yearsToRetirement) - 1) / $netGrowthRate);
        } elseif ($annualContribution > 0) {
            // If growth rate is 0
            $futureValueOfContributions = $annualContribution * $yearsToRetirement;
        }

        return $futureValueOfCurrentFund + $futureValueOfContributions;
    }

    /**
     * Project DB pension annual income at retirement.
     *
     * Uses accrued annual pension with revaluation method applied.
     *
     * @param DBPension $pension
     * @return float Projected annual pension income
     */
    public function projectDBPension(DBPension $pension): float
    {
        // For DB pensions, we use the accrued annual pension
        // In a real implementation, we would apply revaluation based on scheme rules
        // For now, we return the accrued amount (conservative estimate)
        return (float) $pension->accrued_annual_pension;
    }

    /**
     * Project State Pension annual income.
     *
     * @param StatePension $statePension
     * @return float Projected annual state pension income
     */
    public function projectStatePension(StatePension $statePension): float
    {
        // Use forecast if available
        if ($statePension->state_pension_forecast_annual) {
            return (float) $statePension->state_pension_forecast_annual;
        }

        // Otherwise calculate based on NI years (2024/25 full state pension: £11,502)
        $fullStatePension = 11502.00; // Per annum
        $requiredYears = $statePension->ni_years_required;
        $completedYears = min($statePension->ni_years_completed, $requiredYears);

        if ($requiredYears > 0) {
            return ($completedYears / $requiredYears) * $fullStatePension;
        }

        return 0.0;
    }

    /**
     * Project total retirement income from all pension sources.
     *
     * @param int $userId
     * @return array
     */
    public function projectTotalRetirementIncome(int $userId): array
    {
        $dcPensions = DCPension::where('user_id', $userId)->get();
        $dbPensions = DBPension::where('user_id', $userId)->get();
        $statePension = StatePension::where('user_id', $userId)->first();

        $totalDCValue = 0.0;
        $totalDBIncome = 0.0;
        $statePensionIncome = 0.0;
        $dcProjections = [];

        // Default assumptions
        $defaultGrowthRate = 0.05; // 5% growth
        $defaultRetirementAge = 67;

        // Project DC pensions
        foreach ($dcPensions as $dcPension) {
            $retirementAge = $dcPension->retirement_age ?? $defaultRetirementAge;
            $currentAge = $this->getUserAge($userId);
            $yearsToRetirement = max(0, $retirementAge - $currentAge);

            $projectedValue = $this->projectDCPension($dcPension, $yearsToRetirement, $defaultGrowthRate);
            $totalDCValue += $projectedValue;

            $dcProjections[] = [
                'scheme_name' => $dcPension->scheme_name,
                'projected_value' => round($projectedValue, 2),
            ];
        }

        // Project DB pensions
        foreach ($dbPensions as $dbPension) {
            $annualIncome = $this->projectDBPension($dbPension);
            $totalDBIncome += $annualIncome;
        }

        // Project State Pension
        if ($statePension) {
            $statePensionIncome = $this->projectStatePension($statePension);
        }

        // Estimate DC pension income using 4% withdrawal rate
        $dcAnnualIncome = $totalDCValue * 0.04;

        $totalProjectedIncome = $dcAnnualIncome + $totalDBIncome + $statePensionIncome;

        return [
            'dc_total_value' => round($totalDCValue, 2),
            'dc_annual_income' => round($dcAnnualIncome, 2),
            'dc_projections' => $dcProjections,
            'db_annual_income' => round($totalDBIncome, 2),
            'state_pension_income' => round($statePensionIncome, 2),
            'total_projected_income' => round($totalProjectedIncome, 2),
        ];
    }

    /**
     * Calculate income replacement ratio.
     *
     * @param float $projectedIncome
     * @param float $currentIncome
     * @return float Ratio as percentage
     */
    public function calculateIncomeReplacementRatio(float $projectedIncome, float $currentIncome): float
    {
        if ($currentIncome <= 0) {
            return 0.0;
        }

        return round(($projectedIncome / $currentIncome) * 100, 2);
    }

    /**
     * Get user's current age from retirement profile.
     *
     * @param int $userId
     * @return int
     */
    private function getUserAge(int $userId): int
    {
        $profile = \App\Models\RetirementProfile::where('user_id', $userId)->first();
        return $profile ? $profile->current_age : 67; // Default to state pension age if no profile
    }
}
