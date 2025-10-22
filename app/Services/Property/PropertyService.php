<?php

declare(strict_types=1);

namespace App\Services\Property;

use App\Models\Property;

class PropertyService
{
    /**
     * Calculate property equity (current value - outstanding mortgage balance)
     *
     * @param Property $property
     * @return float
     */
    public function calculateEquity(Property $property): float
    {
        $currentValue = $property->current_value ?? 0;

        // Get total outstanding mortgage balance from detailed records
        $mortgageBalance = $property->mortgages()
            ->sum('outstanding_balance');

        // Fall back to simple outstanding_mortgage field if no detailed records exist
        if ($mortgageBalance == 0 && $property->outstanding_mortgage > 0) {
            $mortgageBalance = $property->outstanding_mortgage;
        }

        // Apply ownership percentage
        $userShare = $currentValue * ($property->ownership_percentage / 100);
        $mortgageShare = $mortgageBalance * ($property->ownership_percentage / 100);

        return max(0, $userShare - $mortgageShare);
    }

    /**
     * Calculate total annual costs for the property
     *
     * @param Property $property
     * @return float
     */
    public function calculateTotalAnnualCosts(Property $property): float
    {
        $costs = 0;

        // Mortgage costs (annual)
        $mortgages = $property->mortgages;
        foreach ($mortgages as $mortgage) {
            $costs += ($mortgage->monthly_payment ?? 0) * 12;
        }

        // Property-specific costs
        $costs += $property->annual_service_charge ?? 0;
        $costs += $property->annual_ground_rent ?? 0;
        $costs += $property->annual_insurance ?? 0;
        $costs += $property->annual_maintenance_reserve ?? 0;
        $costs += $property->other_annual_costs ?? 0;

        return $costs;
    }

    /**
     * Calculate net rental yield (%)
     *
     * @param Property $property
     * @return float
     */
    public function calculateNetRentalYield(Property $property): float
    {
        $currentValue = $property->current_value ?? 0;

        if ($currentValue == 0) {
            return 0;
        }

        // Annual rental income (adjusted for occupancy)
        $annualRentalIncome = $property->annual_rental_income ?? 0;
        $occupancyRate = ($property->occupancy_rate_percent ?? 100) / 100;
        $actualIncome = $annualRentalIncome * $occupancyRate;

        // Annual costs
        $annualCosts = $this->calculateTotalAnnualCosts($property);

        // Net rental income
        $netIncome = $actualIncome - $annualCosts;

        // Calculate yield as percentage
        $yield = ($netIncome / $currentValue) * 100;

        return round($yield, 2);
    }

    /**
     * Get comprehensive property summary
     *
     * @param Property $property
     * @return array
     */
    public function getPropertySummary(Property $property): array
    {
        $property->load(['mortgages', 'user', 'household', 'trust']);

        $equity = $this->calculateEquity($property);
        $annualCosts = $this->calculateTotalAnnualCosts($property);
        $rentalYield = $this->calculateNetRentalYield($property);

        // Calculate loan-to-value ratio
        $currentValue = $property->current_value ?? 0;
        // Get mortgage balance from detailed records, or fall back to simple outstanding_mortgage field
        $mortgageBalance = $property->mortgages()->sum('outstanding_balance');
        if ($mortgageBalance == 0 && $property->outstanding_mortgage > 0) {
            $mortgageBalance = $property->outstanding_mortgage;
        }
        $ltv = $currentValue > 0 ? ($mortgageBalance / $currentValue) * 100 : 0;

        // Calculate total return (capital growth + rental yield)
        $purchasePrice = $property->purchase_price ?? 0;
        $capitalGrowth = $currentValue - $purchasePrice;
        $capitalGrowthPercent = $purchasePrice > 0 ? ($capitalGrowth / $purchasePrice) * 100 : 0;

        return [
            // Top-level commonly accessed fields
            'id' => $property->id,
            'property_id' => $property->id,  // Alias for backward compatibility
            'property_type' => $property->property_type,
            'ownership_type' => $property->ownership_type,
            'ownership_percentage' => (float) $property->ownership_percentage,
            'household_id' => $property->household_id,
            'trust_id' => $property->trust_id,
            'current_value' => (float) $currentValue,
            'purchase_price' => (float) $purchasePrice,  // Add to top level for easier access
            'purchase_date' => $property->purchase_date?->format('Y-m-d'),
            'valuation_date' => $property->valuation_date?->format('Y-m-d'),
            'equity' => (float) $equity,
            'mortgage_balance' => (float) $mortgageBalance,
            'outstanding_mortgage' => (float) ($property->outstanding_mortgage ?? 0),  // Include simple field for reference
            'net_rental_yield' => (float) $rentalYield,  // Top-level for easy access in frontend

            // Address fields (flat for form compatibility)
            'address_line_1' => $property->address_line_1,
            'address_line_2' => $property->address_line_2,
            'city' => $property->city,
            'county' => $property->county,
            'postcode' => $property->postcode,

            // Cost fields (flat for form compatibility)
            'annual_service_charge' => $property->annual_service_charge ?? 0,
            'annual_ground_rent' => $property->annual_ground_rent ?? 0,
            'annual_insurance' => $property->annual_insurance ?? 0,
            'annual_maintenance_reserve' => $property->annual_maintenance_reserve ?? 0,
            'other_annual_costs' => $property->other_annual_costs ?? 0,
            'sdlt_paid' => $property->sdlt_paid ?? 0,

            // Rental fields (flat for form compatibility)
            'monthly_rental_income' => $property->monthly_rental_income ?? 0,
            'annual_rental_income' => $property->annual_rental_income ?? 0,
            'occupancy_rate_percent' => $property->occupancy_rate_percent ?? 100,
            'tenant_name' => $property->tenant_name,
            'lease_start_date' => $property->lease_start_date?->format('Y-m-d'),
            'lease_end_date' => $property->lease_end_date?->format('Y-m-d'),

            // Detailed nested structures
            'address' => [
                'line_1' => $property->address_line_1,
                'line_2' => $property->address_line_2,
                'city' => $property->city,
                'county' => $property->county,
                'postcode' => $property->postcode,
                'full_address' => trim(implode(', ', array_filter([
                    $property->address_line_1,
                    $property->address_line_2,
                    $property->city,
                    $property->county,
                    $property->postcode,
                ]))),
            ],
            'valuation' => [
                'purchase_price' => $purchasePrice,
                'purchase_date' => $property->purchase_date?->format('Y-m-d'),
                'current_value' => $currentValue,
                'valuation_date' => $property->valuation_date?->format('Y-m-d'),
                'capital_growth' => $capitalGrowth,
                'capital_growth_percent' => round($capitalGrowthPercent, 2),
            ],
            'financial' => [
                'equity' => $equity,
                'mortgage_balance' => $mortgageBalance,
                'loan_to_value_percent' => round($ltv, 2),
                'annual_costs' => $annualCosts,
            ],
            'rental' => [
                'annual_rental_income' => $property->annual_rental_income ?? 0,
                'monthly_rental_income' => $property->monthly_rental_income ?? 0,
                'occupancy_rate_percent' => $property->occupancy_rate_percent ?? 100,
                'net_rental_yield_percent' => $rentalYield,
                'tenant_name' => $property->tenant_name,
                'lease_start_date' => $property->lease_start_date?->format('Y-m-d'),
                'lease_end_date' => $property->lease_end_date?->format('Y-m-d'),
            ],
            'costs' => [
                'annual_service_charge' => $property->annual_service_charge ?? 0,
                'annual_ground_rent' => $property->annual_ground_rent ?? 0,
                'annual_insurance' => $property->annual_insurance ?? 0,
                'annual_maintenance_reserve' => $property->annual_maintenance_reserve ?? 0,
                'other_annual_costs' => $property->other_annual_costs ?? 0,
                'total_annual_costs' => $annualCosts,
            ],
            'mortgages' => $property->mortgages->map(function ($mortgage) {
                return [
                    'id' => $mortgage->id,
                    'lender_name' => $mortgage->lender_name,
                    'outstanding_balance' => $mortgage->outstanding_balance,
                    'monthly_payment' => $mortgage->monthly_payment,
                    'interest_rate' => $mortgage->interest_rate,
                    'maturity_date' => $mortgage->maturity_date?->format('Y-m-d'),
                ];
            })->toArray(),
        ];
    }
}
