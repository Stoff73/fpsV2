<?php

declare(strict_types=1);

namespace App\Services;

/**
 * UK Tax and National Insurance Calculator
 * Uses active tax year rates from TaxConfigService
 */
class UKTaxCalculator
{
    /**
     * Tax configuration service
     */
    private TaxConfigService $taxConfig;

    /**
     * Constructor
     */
    public function __construct(TaxConfigService $taxConfig)
    {
        $this->taxConfig = $taxConfig;
    }
    /**
     * Calculate net income after income tax and National Insurance.
     *
     * @param  float  $employmentIncome  Employment income (PAYE)
     * @param  float  $selfEmploymentIncome  Self-employment income
     * @param  float  $rentalIncome  Rental income (property)
     * @param  float  $dividendIncome  Dividend income
     * @param  float  $otherIncome  Other taxable income
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
     * Calculate UK Income Tax using active tax year rates from TaxConfigService.
     * Supports:
     * - Income tax bands (basic, higher, additional)
     * - Personal allowance
     * - Dividend allowance and dividend-specific rates
     */
    private function calculateIncomeTax(float $nonDividendIncome, float $dividendIncome): float
    {
        // Get tax configuration from service
        $incomeTax = $this->taxConfig->getIncomeTax();
        $dividendTax = $this->taxConfig->getDividendTax();

        $personalAllowance = $incomeTax['personal_allowance'];
        $dividendAllowance = $dividendTax['allowance'];

        // Get income tax bands (stored as array in seeder)
        $bands = $incomeTax['bands'];

        // Calculate absolute thresholds
        // Basic rate band ends at personal_allowance + band max
        $basicRateLimit = $personalAllowance + $bands[0]['max']; // £12,570 + £37,700 = £50,270
        // Higher rate band ends at personal_allowance + band max
        $higherRateLimit = $personalAllowance + $bands[1]['max']; // £12,570 + £150,000 = £162,570 (for historical)

        // Convert percentage rates to decimals (20% -> 0.20)
        $basicRate = $bands[0]['rate'] / 100;
        $higherRate = $bands[1]['rate'] / 100;
        $additionalRate = $bands[2]['rate'] / 100;

        // Get dividend tax rates (flattened structure, convert percentages to decimals)
        $basicDividendRate = $dividendTax['basic_rate'] / 100;         // 8.75% -> 0.0875
        $higherDividendRate = $dividendTax['higher_rate'] / 100;       // 33.75% -> 0.3375
        $additionalDividendRate = $dividendTax['additional_rate'] / 100; // 39.35% -> 0.3935

        $tax = 0;

        // Calculate tax on non-dividend income
        if ($nonDividendIncome > $personalAllowance) {
            $taxableIncome = $nonDividendIncome - $personalAllowance;

            // Basic rate
            if ($taxableIncome > 0) {
                $basicRateTaxable = min($taxableIncome, $basicRateLimit - $personalAllowance);
                $tax += $basicRateTaxable * $basicRate;
            }

            // Higher rate
            if ($taxableIncome > ($basicRateLimit - $personalAllowance)) {
                $higherRateTaxable = min(
                    $taxableIncome - ($basicRateLimit - $personalAllowance),
                    $higherRateLimit - $basicRateLimit
                );
                $tax += $higherRateTaxable * $higherRate;
            }

            // Additional rate
            if ($taxableIncome > ($higherRateLimit - $personalAllowance)) {
                $additionalRateTaxable = $taxableIncome - ($higherRateLimit - $personalAllowance);
                $tax += $additionalRateTaxable * $additionalRate;
            }
        }

        // Calculate dividend tax
        if ($dividendIncome > $dividendAllowance) {
            $taxableDividends = $dividendIncome - $dividendAllowance;
            $totalIncome = $nonDividendIncome + $dividendIncome;

            // Determine dividend tax rate based on total income band
            if ($totalIncome <= $basicRateLimit) {
                // Basic rate dividend tax
                $tax += $taxableDividends * $basicDividendRate;
            } elseif ($totalIncome <= $higherRateLimit) {
                // Some in basic, some in higher
                $basicRateDividends = max(0, $basicRateLimit - $nonDividendIncome);
                $higherRateDividends = $taxableDividends - $basicRateDividends;

                $tax += $basicRateDividends * $basicDividendRate;
                $tax += $higherRateDividends * $higherDividendRate;
            } else {
                // Additional rate
                $tax += $taxableDividends * $additionalDividendRate;
            }
        }

        return $tax;
    }

    /**
     * Calculate Class 1 National Insurance (Employees).
     * Uses active tax year rates from TaxConfigService.
     */
    private function calculateClass1NI(float $employmentIncome): float
    {
        // Get National Insurance configuration
        $niConfig = $this->taxConfig->getNationalInsurance();
        $class1Employee = $niConfig['class_1']['employee'];

        $primaryThreshold = $class1Employee['primary_threshold'];
        $upperEarningsLimit = $class1Employee['upper_earnings_limit'];
        $mainRate = $class1Employee['main_rate'];
        $additionalRate = $class1Employee['additional_rate'];

        if ($employmentIncome <= $primaryThreshold) {
            return 0;
        }

        $ni = 0;

        // Main rate
        if ($employmentIncome > $primaryThreshold) {
            $mainRateEarnings = min($employmentIncome - $primaryThreshold, $upperEarningsLimit - $primaryThreshold);
            $ni += $mainRateEarnings * $mainRate;
        }

        // Additional rate
        if ($employmentIncome > $upperEarningsLimit) {
            $additionalRateEarnings = $employmentIncome - $upperEarningsLimit;
            $ni += $additionalRateEarnings * $additionalRate;
        }

        return $ni;
    }

    /**
     * Calculate Class 4 National Insurance (Self-Employed).
     * Uses active tax year rates from TaxConfigService.
     */
    private function calculateClass4NI(float $selfEmploymentIncome): float
    {
        // Get National Insurance configuration
        $niConfig = $this->taxConfig->getNationalInsurance();
        $class4 = $niConfig['class_4'];

        $lowerProfitsLimit = $class4['lower_profits_limit'];
        $upperProfitsLimit = $class4['upper_profits_limit'];
        $mainRate = $class4['main_rate'];
        $additionalRate = $class4['additional_rate'];

        if ($selfEmploymentIncome <= $lowerProfitsLimit) {
            return 0;
        }

        $ni = 0;

        // Main rate
        if ($selfEmploymentIncome > $lowerProfitsLimit) {
            $mainRateEarnings = min($selfEmploymentIncome - $lowerProfitsLimit, $upperProfitsLimit - $lowerProfitsLimit);
            $ni += $mainRateEarnings * $mainRate;
        }

        // Additional rate
        if ($selfEmploymentIncome > $upperProfitsLimit) {
            $additionalRateEarnings = $selfEmploymentIncome - $upperProfitsLimit;
            $ni += $additionalRateEarnings * $additionalRate;
        }

        return $ni;
    }
}
