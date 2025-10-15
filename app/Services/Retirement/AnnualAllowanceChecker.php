<?php

declare(strict_types=1);

namespace App\Services\Retirement;

use App\Models\DCPension;

/**
 * Annual Allowance Checker Service
 *
 * Checks pension annual allowance, tapering for high earners, carry forward, and MPAA.
 */
class AnnualAllowanceChecker
{
    // 2024/25 UK pension allowances
    private const STANDARD_ANNUAL_ALLOWANCE = 60000;

    private const MINIMUM_TAPERED_ALLOWANCE = 10000;

    private const THRESHOLD_INCOME = 200000;

    private const ADJUSTED_INCOME_THRESHOLD = 260000;

    private const MPAA = 10000;

    /**
     * Check annual allowance for a user in a given tax year.
     *
     * @param  string  $taxYear  Tax year (e.g., '2024/25')
     */
    public function checkAnnualAllowance(int $userId, string $taxYear): array
    {
        $dcPensions = DCPension::where('user_id', $userId)->get();

        // Calculate total annual contributions
        $totalContributions = $this->calculateTotalAnnualContributions($dcPensions);

        // Get user's income (from retirement profile or assumed)
        $income = $this->getUserIncome($userId);
        $thresholdIncome = $income; // Simplified - should include other sources
        $adjustedIncome = $income + $totalContributions; // Simplified calculation

        // Check if tapering applies
        $standardAllowance = self::STANDARD_ANNUAL_ALLOWANCE;
        $availableAllowance = $standardAllowance;
        $isTapered = false;
        $taperingDetails = null;

        if ($thresholdIncome > self::THRESHOLD_INCOME && $adjustedIncome > self::ADJUSTED_INCOME_THRESHOLD) {
            $isTapered = true;
            $availableAllowance = $this->calculateTapering($thresholdIncome, $adjustedIncome);
            $taperingDetails = [
                'threshold_income' => $thresholdIncome,
                'adjusted_income' => $adjustedIncome,
                'reduction' => $standardAllowance - $availableAllowance,
            ];
        }

        // Calculate carry forward from previous 3 years
        $carryForward = $this->getCarryForward($userId, $taxYear);

        // Calculate remaining allowance
        $allowanceUsed = min($totalContributions, $availableAllowance + $carryForward);
        $remainingAllowance = max(0, $availableAllowance - $totalContributions);
        $excessContributions = max(0, $totalContributions - ($availableAllowance + $carryForward));

        return [
            'tax_year' => $taxYear,
            'standard_allowance' => $standardAllowance,
            'available_allowance' => $availableAllowance,
            'is_tapered' => $isTapered,
            'tapering_details' => $taperingDetails,
            'total_contributions' => round($totalContributions, 2),
            'carry_forward_available' => round($carryForward, 2),
            'allowance_used' => round($allowanceUsed, 2),
            'remaining_allowance' => round($remainingAllowance, 2),
            'excess_contributions' => round($excessContributions, 2),
            'has_excess' => $excessContributions > 0,
        ];
    }

    /**
     * Calculate tapered annual allowance for high earners.
     *
     * Reduction: £1 for every £2 over adjusted income threshold.
     * Minimum allowance: £10,000
     *
     * @return float Tapered allowance
     */
    public function calculateTapering(float $thresholdIncome, float $adjustedIncome): float
    {
        if ($thresholdIncome <= self::THRESHOLD_INCOME || $adjustedIncome <= self::ADJUSTED_INCOME_THRESHOLD) {
            return self::STANDARD_ANNUAL_ALLOWANCE;
        }

        // Calculate reduction
        $excessIncome = $adjustedIncome - self::ADJUSTED_INCOME_THRESHOLD;
        $reduction = $excessIncome / 2;

        // Apply reduction but ensure minimum allowance
        $taperedAllowance = self::STANDARD_ANNUAL_ALLOWANCE - $reduction;

        return max(self::MINIMUM_TAPERED_ALLOWANCE, $taperedAllowance);
    }

    /**
     * Get carry forward allowance from previous 3 tax years.
     *
     * Note: This is a simplified implementation. In reality, we would need to track
     * actual contributions and unused allowance for each of the previous 3 years.
     *
     * @return float Total carry forward available
     */
    public function getCarryForward(int $userId, string $taxYear): float
    {
        // Simplified: Assume unused allowance from previous years
        // In a full implementation, we would track this in a separate table
        // For now, return a conservative estimate of £60,000 (1 year's unused allowance)
        return 60000.00;
    }

    /**
     * Check if user has triggered Money Purchase Annual Allowance (MPAA).
     *
     * MPAA is triggered when user has flexibly accessed a pension.
     */
    public function checkMPAA(int $userId): array
    {
        // In a full implementation, we would track flexible access events
        // For now, return false (not triggered)
        $isTriggered = false;

        return [
            'is_triggered' => $isTriggered,
            'mpaa_amount' => self::MPAA,
            'message' => $isTriggered
                ? 'MPAA triggered - your annual allowance is reduced to £'.number_format(self::MPAA).' per year.'
                : 'MPAA not triggered - standard annual allowance applies.',
        ];
    }

    /**
     * Calculate total annual pension contributions from all DC pensions.
     *
     * @param  \Illuminate\Support\Collection  $dcPensions
     */
    private function calculateTotalAnnualContributions($dcPensions): float
    {
        $total = 0.0;

        foreach ($dcPensions as $pension) {
            $monthlyContribution = (float) $pension->monthly_contribution_amount ?? 0.0;
            $total += $monthlyContribution * 12;
        }

        return $total;
    }

    /**
     * Get user's annual income.
     */
    private function getUserIncome(int $userId): float
    {
        $profile = \App\Models\RetirementProfile::where('user_id', $userId)->first();

        return $profile ? (float) $profile->current_annual_salary : 0.0;
    }
}
