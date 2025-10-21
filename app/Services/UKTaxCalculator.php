<?php

declare(strict_types=1);

namespace App\Services;

/**
 * UK Tax and National Insurance Calculator
 * Uses 2025/26 tax year rates
 */
class UKTaxCalculator
{
    /**
     * Calculate net income after income tax and National Insurance.
     *
     * @param float $employmentIncome Employment income (PAYE)
     * @param float $selfEmploymentIncome Self-employment income
     * @param float $rentalIncome Rental income (property)
     * @param float $dividendIncome Dividend income
     * @param float $otherIncome Other taxable income
     * @return array Net income breakdown with tax and NI details
     */
    public function calculateNetIncome(
        float $employmentIncome = 0,
        float $selfEmploymentIncome = 0,
        float $rentalIncome = 0,
        float $dividendIncome = 0,
        float $otherIncome = 0
    ): array {
        $grossIncome = $employmentIncome + $selfEmploymentIncome + $rentalIncome + $dividendIncome + $otherIncome;

        // Calculate Income Tax (non-dividend income)
        $nonDividendIncome = $employmentIncome + $selfEmploymentIncome + $rentalIncome + $otherIncome;
        $incomeTax = $this->calculateIncomeTax($nonDividendIncome, $dividendIncome);

        // Calculate National Insurance
        $class1NI = $this->calculateClass1NI($employmentIncome); // Employees
        $class4NI = $this->calculateClass4NI($selfEmploymentIncome); // Self-employed
        $totalNI = $class1NI + $class4NI;

        $totalDeductions = $incomeTax + $totalNI;
        $netIncome = $grossIncome - $totalDeductions;

        return [
            'gross_income' => round($grossIncome, 2),
            'income_tax' => round($incomeTax, 2),
            'national_insurance' => round($totalNI, 2),
            'total_deductions' => round($totalDeductions, 2),
            'net_income' => round($netIncome, 2),
            'effective_tax_rate' => $grossIncome > 0 ? round(($totalDeductions / $grossIncome) * 100, 2) : 0,
            'breakdown' => [
                'employment_income' => round($employmentIncome, 2),
                'self_employment_income' => round($selfEmploymentIncome, 2),
                'rental_income' => round($rentalIncome, 2),
                'dividend_income' => round($dividendIncome, 2),
                'other_income' => round($otherIncome, 2),
                'class_1_ni' => round($class1NI, 2),
                'class_4_ni' => round($class4NI, 2),
            ],
        ];
    }

    /**
     * Calculate UK Income Tax (2025/26 rates).
     * Personal Allowance: £12,570
     * Basic Rate (20%): £12,571 - £50,270
     * Higher Rate (40%): £50,271 - £125,140
     * Additional Rate (45%): £125,141+
     * Dividend Allowance: £500
     * Dividend Tax: 8.75% (basic), 33.75% (higher), 39.35% (additional)
     */
    private function calculateIncomeTax(float $nonDividendIncome, float $dividendIncome): float
    {
        $personalAllowance = 12570;
        $basicRateLimit = 50270;
        $higherRateLimit = 125140;
        $dividendAllowance = 500;

        $tax = 0;

        // Calculate tax on non-dividend income
        if ($nonDividendIncome > $personalAllowance) {
            $taxableIncome = $nonDividendIncome - $personalAllowance;

            // Basic rate (20%)
            if ($taxableIncome > 0) {
                $basicRateTaxable = min($taxableIncome, $basicRateLimit - $personalAllowance);
                $tax += $basicRateTaxable * 0.20;
            }

            // Higher rate (40%)
            if ($taxableIncome > ($basicRateLimit - $personalAllowance)) {
                $higherRateTaxable = min(
                    $taxableIncome - ($basicRateLimit - $personalAllowance),
                    $higherRateLimit - $basicRateLimit
                );
                $tax += $higherRateTaxable * 0.40;
            }

            // Additional rate (45%)
            if ($taxableIncome > ($higherRateLimit - $personalAllowance)) {
                $additionalRateTaxable = $taxableIncome - ($higherRateLimit - $personalAllowance);
                $tax += $additionalRateTaxable * 0.45;
            }
        }

        // Calculate dividend tax
        if ($dividendIncome > $dividendAllowance) {
            $taxableDividends = $dividendIncome - $dividendAllowance;
            $totalIncome = $nonDividendIncome + $dividendIncome;

            // Determine dividend tax rate based on total income band
            if ($totalIncome <= $basicRateLimit) {
                // Basic rate dividend tax (8.75%)
                $tax += $taxableDividends * 0.0875;
            } elseif ($totalIncome <= $higherRateLimit) {
                // Some in basic, some in higher
                $basicRateDividends = max(0, $basicRateLimit - $nonDividendIncome);
                $higherRateDividends = $taxableDividends - $basicRateDividends;

                $tax += $basicRateDividends * 0.0875; // Basic rate
                $tax += $higherRateDividends * 0.3375; // Higher rate (33.75%)
            } else {
                // Higher/additional rate
                $tax += $taxableDividends * 0.3935; // Additional rate (39.35%)
            }
        }

        return $tax;
    }

    /**
     * Calculate Class 1 National Insurance (Employees - 2025/26).
     * Primary Threshold: £12,570
     * Upper Earnings Limit: £50,270
     * Main Rate: 8% (£12,571 - £50,270)
     * Additional Rate: 2% (above £50,270)
     */
    private function calculateClass1NI(float $employmentIncome): float
    {
        if ($employmentIncome <= 12570) {
            return 0;
        }

        $ni = 0;

        // Main rate (8%)
        if ($employmentIncome > 12570) {
            $mainRateEarnings = min($employmentIncome - 12570, 50270 - 12570);
            $ni += $mainRateEarnings * 0.08;
        }

        // Additional rate (2%)
        if ($employmentIncome > 50270) {
            $additionalRateEarnings = $employmentIncome - 50270;
            $ni += $additionalRateEarnings * 0.02;
        }

        return $ni;
    }

    /**
     * Calculate Class 4 National Insurance (Self-Employed - 2025/26).
     * Lower Profits Limit: £12,570
     * Upper Profits Limit: £50,270
     * Main Rate: 6% (£12,571 - £50,270)
     * Additional Rate: 2% (above £50,270)
     */
    private function calculateClass4NI(float $selfEmploymentIncome): float
    {
        if ($selfEmploymentIncome <= 12570) {
            return 0;
        }

        $ni = 0;

        // Main rate (6%)
        if ($selfEmploymentIncome > 12570) {
            $mainRateEarnings = min($selfEmploymentIncome - 12570, 50270 - 12570);
            $ni += $mainRateEarnings * 0.06;
        }

        // Additional rate (2%)
        if ($selfEmploymentIncome > 50270) {
            $additionalRateEarnings = $selfEmploymentIncome - 50270;
            $ni += $additionalRateEarnings * 0.02;
        }

        return $ni;
    }
}
