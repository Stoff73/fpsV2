<?php

declare(strict_types=1);

namespace App\Services\Property;

use App\Models\Property;
use App\Models\User;

class PropertyTaxService
{
    /**
     * Calculate Stamp Duty Land Tax (SDLT) for UK property purchase
     *
     * Rates for 2024/25:
     * - Main residence: 0% up to £250k, 5% £250k-£925k, 10% £925k-£1.5m, 12% above £1.5m
     * - First-time buyer relief: 0% up to £425k (properties up to £625k)
     * - Additional property: +3% surcharge on all bands
     *
     * @param  string  $propertyType  ('main_residence', 'second_home', 'buy_to_let')
     */
    public function calculateSDLT(float $purchasePrice, string $propertyType, bool $isFirstHome = false): array
    {
        $bands = [];
        $totalSDLT = 0;

        // First-time buyer relief (properties up to £625k, relief up to £425k)
        if ($isFirstHome && $purchasePrice <= 625000) {
            $bands[] = [
                'from' => 0,
                'to' => min($purchasePrice, 425000),
                'rate' => 0,
                'tax' => 0,
            ];

            if ($purchasePrice > 425000) {
                $bandValue = $purchasePrice - 425000;
                $tax = $bandValue * 0.05;
                $totalSDLT += $tax;

                $bands[] = [
                    'from' => 425000,
                    'to' => $purchasePrice,
                    'rate' => 5,
                    'tax' => $tax,
                ];
            }
        } else {
            // Standard or additional property rates
            $isAdditional = in_array($propertyType, ['secondary_residence', 'buy_to_let']);
            $surcharge = $isAdditional ? 3 : 0;

            // Band 1: 0 - £250,000
            if ($purchasePrice > 0) {
                $bandValue = min($purchasePrice, 250000);
                $rate = 0 + $surcharge;
                $tax = $bandValue * ($rate / 100);
                $totalSDLT += $tax;

                $bands[] = [
                    'from' => 0,
                    'to' => 250000,
                    'rate' => $rate,
                    'tax' => $tax,
                ];
            }

            // Band 2: £250,000 - £925,000
            if ($purchasePrice > 250000) {
                $bandValue = min($purchasePrice - 250000, 675000);
                $rate = 5 + $surcharge;
                $tax = $bandValue * ($rate / 100);
                $totalSDLT += $tax;

                $bands[] = [
                    'from' => 250000,
                    'to' => min($purchasePrice, 925000),
                    'rate' => $rate,
                    'tax' => $tax,
                ];
            }

            // Band 3: £925,000 - £1,500,000
            if ($purchasePrice > 925000) {
                $bandValue = min($purchasePrice - 925000, 575000);
                $rate = 10 + $surcharge;
                $tax = $bandValue * ($rate / 100);
                $totalSDLT += $tax;

                $bands[] = [
                    'from' => 925000,
                    'to' => min($purchasePrice, 1500000),
                    'rate' => $rate,
                    'tax' => $tax,
                ];
            }

            // Band 4: Above £1,500,000
            if ($purchasePrice > 1500000) {
                $bandValue = $purchasePrice - 1500000;
                $rate = 12 + $surcharge;
                $tax = $bandValue * ($rate / 100);
                $totalSDLT += $tax;

                $bands[] = [
                    'from' => 1500000,
                    'to' => $purchasePrice,
                    'rate' => $rate,
                    'tax' => $tax,
                ];
            }
        }

        $effectiveRate = $purchasePrice > 0 ? ($totalSDLT / $purchasePrice) * 100 : 0;

        return [
            'purchase_price' => $purchasePrice,
            'property_type' => $propertyType,
            'is_first_home' => $isFirstHome,
            'total_sdlt' => round($totalSDLT, 2),
            'effective_rate' => round($effectiveRate, 2),
            'bands' => $bands,
        ];
    }

    /**
     * Calculate Capital Gains Tax (CGT) on property disposal
     *
     * CGT rates for 2024/25:
     * - Annual exempt amount: £3,000
     * - Basic rate taxpayers: 18% on residential property
     * - Higher/additional rate taxpayers: 24% on residential property
     */
    public function calculateCGT(Property $property, float $disposalPrice, float $disposalCosts, User $user): array
    {
        $purchasePrice = $property->purchase_price ?? 0;
        $sdltPaid = $property->sdlt_paid ?? 0;

        // Calculate total acquisition costs
        $acquisitionCosts = $purchasePrice + $sdltPaid;

        // Calculate gain
        $gain = $disposalPrice - $acquisitionCosts - $disposalCosts;

        // Apply annual exempt amount (£3,000 for 2024/25)
        $annualExemptAmount = 3000;
        $taxableGain = (float) max(0, $gain - $annualExemptAmount);

        // Determine CGT rate based on user's income
        $totalIncome = $user->annual_employment_income +
            $user->annual_self_employment_income +
            $user->annual_rental_income +
            $user->annual_dividend_income +
            $user->annual_other_income;

        // Basic rate threshold for 2024/25: £50,270
        $basicRateThreshold = 50270;

        $cgtRate = $totalIncome > $basicRateThreshold ? 24 : 18;
        $cgtLiability = $taxableGain * ($cgtRate / 100);

        $effectiveRate = $gain > 0 ? ($cgtLiability / $gain) * 100 : 0;

        return [
            'disposal_price' => $disposalPrice,
            'acquisition_cost' => $acquisitionCosts,
            'disposal_costs' => $disposalCosts,
            'gain' => $gain,  // Alias
            'gross_gain' => $gain,
            'annual_exempt_amount' => $annualExemptAmount,
            'taxable_gain' => $taxableGain,
            'cgt_rate' => (float) $cgtRate,
            'cgt_liability' => round($cgtLiability, 2),
            'effective_rate' => round($effectiveRate, 2),
        ];
    }

    /**
     * Calculate rental income tax liability
     */
    public function calculateRentalIncomeTax(Property $property, User $user): array
    {
        // Rental income
        $annualRentalIncome = $property->annual_rental_income ?? 0;
        $occupancyRate = ($property->occupancy_rate_percent ?? 100) / 100;
        $actualIncome = $annualRentalIncome * $occupancyRate;

        // Allowable expenses
        $allowableExpenses = 0;
        $allowableExpenses += $property->annual_service_charge ?? 0;
        $allowableExpenses += $property->annual_ground_rent ?? 0;
        $allowableExpenses += $property->annual_insurance ?? 0;
        $allowableExpenses += $property->annual_maintenance_reserve ?? 0;
        $allowableExpenses += $property->other_annual_costs ?? 0;

        // Mortgage interest (20% tax relief from 2020/21 onwards)
        $mortgageInterest = 0;
        foreach ($property->mortgages as $mortgage) {
            $annualPayment = ($mortgage->monthly_payment ?? 0) * 12;
            $interestRate = ($mortgage->interest_rate ?? 0) / 100;
            $outstandingBalance = $mortgage->outstanding_balance ?? 0;
            $annualInterest = $outstandingBalance * $interestRate;
            $mortgageInterest += $annualInterest;
        }

        // Mortgage interest tax credit (20% of interest)
        $mortgageInterestCredit = $mortgageInterest * 0.20;

        // Calculate taxable profit (cannot deduct mortgage interest directly)
        $taxableProfit = max(0, $actualIncome - $allowableExpenses);

        // Determine user's marginal tax rate
        $totalIncome = $user->annual_employment_income +
            $user->annual_self_employment_income +
            $user->annual_rental_income +
            $user->annual_dividend_income +
            $user->annual_other_income;

        // Tax bands for 2024/25:
        // Personal allowance: £12,570
        // Basic rate (20%): £12,571 - £50,270
        // Higher rate (40%): £50,271 - £125,140
        // Additional rate (45%): above £125,140

        $marginalTaxRate = 0;
        if ($totalIncome > 125140) {
            $marginalTaxRate = 45;
        } elseif ($totalIncome > 50270) {
            $marginalTaxRate = 40;
        } elseif ($totalIncome > 12570) {
            $marginalTaxRate = 20;
        }

        // Tax liability before mortgage interest credit
        $taxBeforeCredit = $taxableProfit * ($marginalTaxRate / 100);

        // Apply mortgage interest tax credit
        $taxLiability = max(0, $taxBeforeCredit - $mortgageInterestCredit);

        return [
            // Flat keys for quick access
            'gross_income' => $actualIncome,
            'allowable_expenses' => $allowableExpenses,
            'mortgage_interest_relief' => round($mortgageInterestCredit, 2),
            'taxable_profit' => $taxableProfit,
            'marginal_tax_rate' => $marginalTaxRate,
            'tax_before_credit' => round($taxBeforeCredit, 2),
            'tax_liability' => round($taxLiability, 2),
            'net_rental_profit' => round($actualIncome - $allowableExpenses - $taxLiability, 2),

            // Detailed nested structures
            'rental_income' => [
                'gross_annual' => $annualRentalIncome,
                'occupancy_rate_percent' => $property->occupancy_rate_percent ?? 100,
                'actual_income' => $actualIncome,
            ],
            'allowable_expenses_detail' => [
                'service_charge' => $property->annual_service_charge ?? 0,
                'ground_rent' => $property->annual_ground_rent ?? 0,
                'insurance' => $property->annual_insurance ?? 0,
                'maintenance' => $property->annual_maintenance_reserve ?? 0,
                'other_costs' => $property->other_annual_costs ?? 0,
                'total' => $allowableExpenses,
            ],
            'mortgage_interest' => [
                'annual_interest' => round($mortgageInterest, 2),
                'tax_credit_20_percent' => round($mortgageInterestCredit, 2),
            ],
        ];
    }
}
