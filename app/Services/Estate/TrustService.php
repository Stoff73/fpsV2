<?php

declare(strict_types=1);

namespace App\Services\Estate;

use App\Models\Estate\Trust;
use App\Services\TaxConfigService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TrustService
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
     * Calculate the next periodic charge date for a relevant property trust
     */
    public function calculateNextPeriodicChargeDate(Trust $trust): ?Carbon
    {
        if (! $trust->isRelevantPropertyTrust()) {
            return null;
        }

        $creationDate = Carbon::parse($trust->trust_creation_date);
        $lastChargeDate = $trust->last_periodic_charge_date
            ? Carbon::parse($trust->last_periodic_charge_date)
            : null;

        if ($lastChargeDate) {
            return $lastChargeDate->copy()->addYears(10);
        }

        // First charge is 10 years after creation
        return $creationDate->copy()->addYears(10);
    }

    /**
     * Calculate periodic charge for a relevant property trust (10-year anniversary)
     *
     * Charge is up to 6% of trust value, based on cumulative transfers
     */
    public function calculatePeriodicCharge(Trust $trust): array
    {
        if (! $trust->isRelevantPropertyTrust()) {
            return [
                'charge_amount' => 0,
                'effective_rate' => 0,
                'next_charge_date' => null,
            ];
        }

        $trustsConfig = $this->taxConfig->getTrusts();
        $ihtConfig = $this->taxConfig->getInheritanceTax();
        $nrb = $ihtConfig['nil_rate_band'];

        // Simplified calculation - in practice this is complex
        // Rate is up to 6% based on how much trust exceeds NRB
        $trustValue = $trust->current_value;
        $excessOverNRB = max(0, $trustValue - $nrb);

        // Effective rate calculation (simplified)
        $effectiveRate = min($trustsConfig['periodic_charges']['max_rate'],
            ($excessOverNRB / $trustValue) * 0.06);

        $chargeAmount = $trustValue * $effectiveRate;

        return [
            'charge_amount' => round($chargeAmount, 2),
            'effective_rate' => $effectiveRate,
            'trust_value' => round($trustValue, 2),
            'nrb' => round($nrb, 2),
            'excess_over_nrb' => round($excessOverNRB, 2),
            'next_charge_date' => $this->calculateNextPeriodicChargeDate($trust)?->format('Y-m-d'),
        ];
    }

    /**
     * Calculate the IHT value of all trusts for a user
     * Returns the amount that should be included in estate value
     */
    public function calculateTotalIHTValue(Collection $trusts): float
    {
        return $trusts->sum(function ($trust) {
            return $trust->getIHTValue();
        });
    }

    /**
     * Analyze trust efficiency for IHT planning
     */
    public function analyzeTrustEfficiency(Trust $trust): array
    {
        $trustsConfig = $this->taxConfig->getTrusts();
        $trustConfig = $trustsConfig['types'][$trust->trust_type] ?? null;

        $valueInEstate = $trust->getIHTValue();
        $valueOutsideEstate = $trust->current_value - $valueInEstate;
        $efficiencyPercent = $trust->current_value > 0
            ? ($valueOutsideEstate / $trust->current_value) * 100
            : 0;

        $growth = $trust->current_value - $trust->initial_value;
        $growthRate = $trust->initial_value > 0
            ? (($trust->current_value - $trust->initial_value) / $trust->initial_value) * 100
            : 0;

        $yearsActive = Carbon::parse($trust->trust_creation_date)->diffInYears(Carbon::now());

        return [
            'trust_id' => $trust->id,
            'trust_name' => $trust->trust_name,
            'trust_type' => $trust->trust_type,
            'trust_type_name' => $trustConfig['name'] ?? $trust->trust_type,
            'initial_value' => round($trust->initial_value, 2),
            'current_value' => round($trust->current_value, 2),
            'growth' => round($growth, 2),
            'growth_rate_percent' => round($growthRate, 2),
            'value_in_estate' => round($valueInEstate, 2),
            'value_outside_estate' => round($valueOutsideEstate, 2),
            'iht_efficiency_percent' => round($efficiencyPercent, 2),
            'years_active' => $yearsActive,
            'is_relevant_property_trust' => $trust->isRelevantPropertyTrust(),
            'periodic_charge_info' => $trust->isRelevantPropertyTrust()
                ? $this->calculatePeriodicCharge($trust)
                : null,
        ];
    }

    /**
     * Get trust recommendations based on user's estate and circumstances
     */
    public function getTrustRecommendations(float $estateValue, float $ihtLiability, array $circumstances = []): array
    {
        $recommendations = [];
        $trustsConfig = $this->taxConfig->getTrusts();
        $trustTypes = $trustsConfig['types'];

        // If significant IHT liability
        if ($ihtLiability > 50000) {
            // Recommend life insurance trust to cover liability
            $recommendations[] = [
                'trust_type' => 'life_insurance',
                'priority' => 'high',
                'reason' => 'Cover IHT liability of £'.number_format($ihtLiability, 0).' without depleting estate assets',
                'description' => $trustTypes['life_insurance']['description'],
                'benefits' => [
                    'Policy proceeds paid outside estate',
                    'Provides liquid funds to pay IHT',
                    'Beneficiaries inherit estate intact',
                ],
            ];

            // If estate is large, consider discounted gift trust
            if ($estateValue > 1000000) {
                $recommendations[] = [
                    'trust_type' => 'discounted_gift',
                    'priority' => 'high',
                    'reason' => 'Reduce estate value while retaining income',
                    'description' => $trustTypes['discounted_gift']['description'],
                    'benefits' => [
                        'Immediate IHT reduction (30-60% discount typical)',
                        'Retain regular income stream',
                        'Growth outside estate from day one',
                    ],
                ];
            }

            // Loan trust for flexibility
            $recommendations[] = [
                'trust_type' => 'loan',
                'priority' => 'medium',
                'reason' => 'Freeze estate value while maintaining access',
                'description' => $trustTypes['loan']['description'],
                'benefits' => [
                    'No 7-year wait for original loan',
                    'Growth accrues outside estate immediately',
                    'Can repay loan if need capital',
                ],
            ];
        }

        // If have children
        if ($circumstances['has_children'] ?? false) {
            $recommendations[] = [
                'trust_type' => 'bare',
                'priority' => 'medium',
                'reason' => 'Pass assets to children with certainty',
                'description' => $trustTypes['bare']['description'],
                'benefits' => [
                    'Simple and low-cost',
                    'PET treatment (7-year rule)',
                    'Child entitled at age 18',
                ],
            ];
        }

        // If want flexibility for beneficiaries
        if ($circumstances['needs_flexibility'] ?? false) {
            $recommendations[] = [
                'trust_type' => 'discretionary',
                'priority' => 'medium',
                'reason' => 'Maximum flexibility for trustees',
                'description' => $trustTypes['discretionary']['description'],
                'benefits' => [
                    'Trustees decide distributions',
                    'Protect vulnerable beneficiaries',
                    'Assets outside settlor estate',
                ],
                'drawbacks' => [
                    'Periodic charges every 10 years (up to 6%)',
                    'Entry charge (20% over NRB)',
                    'Exit charges on distributions',
                ],
            ];
        }

        return $recommendations;
    }

    /**
     * Calculate discounted gift trust discount based on age and income
     * This is a simplified calculation - real calculations use actuarial tables
     */
    public function estimateDiscountedGiftDiscount(int $age, float $giftValue, float $annualIncome): array
    {
        // Life expectancy approximation
        $lifeExpectancy = max(5, 90 - $age);

        // Total expected income payments
        $totalExpectedIncome = $annualIncome * $lifeExpectancy;

        // Discount is NPV of future income stream (simplified - no discounting applied)
        // In practice, this uses actuarial tables and discount rates
        $discount = min($giftValue * 0.60, $totalExpectedIncome * 0.8); // Max 60% discount

        $giftedValue = $giftValue - $discount;
        $discountPercent = ($discount / $giftValue) * 100;

        return [
            'gift_value' => round($giftValue, 2),
            'discount_amount' => round($discount, 2),
            'discount_percent' => round($discountPercent, 2),
            'gifted_value' => round($giftedValue, 2), // This is the PET
            'retained_value' => round($discount, 2), // This stays in estate
            'annual_income' => round($annualIncome, 2),
            'estimated_life_expectancy' => $lifeExpectancy,
        ];
    }
}
