<?php

declare(strict_types=1);

namespace App\Services\UserProfile;

use App\Models\User;
use App\Services\Shared\CrossModuleAssetAggregator;
use App\Services\UKTaxCalculator;

class UserProfileService
{
    public function __construct(
        private CrossModuleAssetAggregator $assetAggregator,
        private UKTaxCalculator $taxCalculator
    ) {}

    /**
     * Get the complete profile for a user including all related data
     */
    public function getCompleteProfile(User $user): array
    {
        // Load all relationships
        $user->load([
            'household',
            'spouse',
            'familyMembers',
            'properties',
            'mortgages',
            'liabilities',
            'businessInterests',
            'chattels',
            'cashAccounts',
            'investmentAccounts.holdings',
            'dcPensions',
            'dbPensions',
            'statePension',
        ]);

        // Calculate asset summary
        $assetsSummary = $this->calculateAssetsSummary($user);

        // Calculate liabilities summary
        $liabilitiesSummary = $this->calculateLiabilitiesSummary($user);

        return [
            'personal_info' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'date_of_birth' => $user->date_of_birth?->format('Y-m-d'),
                'age' => $user->date_of_birth?->age,
                'gender' => $user->gender,
                'marital_status' => $user->marital_status,
                'national_insurance_number' => $user->national_insurance_number,
                'address' => [
                    'line_1' => $user->address_line_1,
                    'line_2' => $user->address_line_2,
                    'city' => $user->city,
                    'county' => $user->county,
                    'postcode' => $user->postcode,
                ],
                'phone' => $user->phone,
                'education_level' => $user->education_level,
                'good_health' => $user->good_health,
                'smoker' => $user->smoker,
            ],
            'household' => $user->household,
            'spouse' => $user->spouse ? [
                'id' => $user->spouse->id,
                'name' => $user->spouse->name,
                'email' => $user->spouse->email,
            ] : null,
            'income_occupation' => array_merge(
                [
                    'occupation' => $user->occupation,
                    'employer' => $user->employer,
                    'industry' => $user->industry,
                    'employment_status' => $user->employment_status,
                    'target_retirement_age' => $user->target_retirement_age,
                    'retirement_date' => $user->retirement_date,
                    'annual_employment_income' => $user->annual_employment_income,
                    'annual_self_employment_income' => $user->annual_self_employment_income,
                    'annual_rental_income' => $this->calculateAnnualRentalIncome($user),
                    'annual_dividend_income' => $user->annual_dividend_income,
                    'annual_interest_income' => $user->annual_interest_income,
                    'annual_other_income' => $user->annual_other_income,
                    'total_annual_income' => (
                        ($user->annual_employment_income ?? 0) +
                        ($user->annual_self_employment_income ?? 0) +
                        $this->calculateAnnualRentalIncome($user) +
                        ($user->annual_dividend_income ?? 0) +
                        ($user->annual_interest_income ?? 0) +
                        ($user->annual_other_income ?? 0)
                    ),
                ],
                $this->taxCalculator->calculateNetIncome(
                    (float) ($user->annual_employment_income ?? 0),
                    (float) ($user->annual_self_employment_income ?? 0),
                    (float) $this->calculateAnnualRentalIncome($user),
                    (float) ($user->annual_dividend_income ?? 0),
                    (float) ($user->annual_interest_income ?? 0),
                    (float) ($user->annual_other_income ?? 0)
                )
            ),
            'expenditure' => [
                'monthly_expenditure' => $user->monthly_expenditure,
                'annual_expenditure' => $user->annual_expenditure,
            ],
            'family_members' => $user->familyMembers->map(function ($member) use ($user) {
                $memberArray = $member->toArray();

                // If this is a spouse and user has a spouse_id, get the spouse's email
                if ($member->relationship === 'spouse' && $user->spouse_id) {
                    $spouse = User::find($user->spouse_id);
                    $memberArray['email'] = $spouse ? $spouse->email : null;
                }

                return $memberArray;
            }),
            'domicile_info' => $user->getDomicileInfo(),
            'assets_summary' => $assetsSummary,
            'liabilities_summary' => $liabilitiesSummary,
            'net_worth' => $assetsSummary['total'] - $liabilitiesSummary['total'],
        ];
    }

    /**
     * Update personal information
     */
    public function updatePersonalInfo(User $user, array $data): User
    {
        $user->update($data);

        return $user->fresh();
    }

    /**
     * Update income and occupation information
     */
    public function updateIncomeOccupation(User $user, array $data): User
    {
        // Calculate annual rental income from properties
        $annualRentalIncome = $this->calculateAnnualRentalIncome($user);

        // Override the annual_rental_income with calculated value
        $data['annual_rental_income'] = $annualRentalIncome;

        $user->update($data);

        return $user->fresh();
    }

    /**
     * Update domicile information and calculate deemed domicile status
     */
    public function updateDomicileInfo(User $user, array $data): User
    {
        // Update the basic fields
        $user->update([
            'domicile_status' => $data['domicile_status'],
            'country_of_birth' => $data['country_of_birth'],
            'uk_arrival_date' => $data['uk_arrival_date'] ?? null,
        ]);

        // Refresh to get updated values
        $user = $user->fresh();

        // Calculate and update years_uk_resident
        $yearsResident = $user->calculateYearsUKResident();
        if ($yearsResident !== null) {
            $user->years_uk_resident = $yearsResident;
        }

        // Calculate and set deemed_domicile_date if applicable
        if ($user->isDeemedDomiciled() && ! $user->deemed_domicile_date && $user->uk_arrival_date) {
            // Calculate the date when they became deemed domiciled (15 years after arrival)
            $arrivalDate = \Carbon\Carbon::parse($user->uk_arrival_date);
            $user->deemed_domicile_date = $arrivalDate->copy()->addYears(15);
        }

        // If they are no longer deemed domiciled (e.g., status changed to uk_domiciled), clear the date
        if (! $user->isDeemedDomiciled() && $user->domicile_status !== 'uk_domiciled') {
            $user->deemed_domicile_date = null;
        }

        $user->save();

        return $user->fresh();
    }

    /**
     * Calculate total annual rental income from user's properties
     */
    private function calculateAnnualRentalIncome(User $user): float
    {
        return $user->properties->sum(function ($property) {
            $monthlyRental = $property->monthly_rental_income ?? 0;
            $ownershipPercentage = $property->ownership_percentage ?? 100;

            // Calculate annual rental income adjusted for ownership percentage
            return ($monthlyRental * 12) * ($ownershipPercentage / 100);
        });
    }

    /**
     * Calculate total assets for the user
     */
    private function calculateAssetsSummary(User $user): array
    {
        // Use CrossModuleAssetAggregator for cross-module assets
        $breakdown = $this->assetAggregator->getAssetBreakdown($user->id);

        // Calculate Estate-specific assets (business, chattels)
        $businessTotal = $user->businessInterests->sum(function ($business) {
            return $business->current_valuation * ($business->ownership_percentage / 100);
        });

        $chattelsTotal = $user->chattels->sum(function ($chattel) {
            return $chattel->current_value * ($chattel->ownership_percentage / 100);
        });

        // Calculate pensions
        $pensionsTotal = $user->dcPensions->sum('current_fund_value');

        return [
            'cash' => [
                'total' => $breakdown['cash']['total'],
                'count' => $breakdown['cash']['count'],
            ],
            'investments' => [
                'total' => $breakdown['investment']['total'],
                'count' => $breakdown['investment']['count'],
            ],
            'properties' => [
                'total' => $breakdown['property']['total'],
                'count' => $breakdown['property']['count'],
            ],
            'business' => [
                'total' => $businessTotal,
                'count' => $user->businessInterests->count(),
            ],
            'chattels' => [
                'total' => $chattelsTotal,
                'count' => $user->chattels->count(),
            ],
            'pensions' => [
                'total' => $pensionsTotal,
                'count' => $user->dcPensions->count(),
            ],
            'total' => $breakdown['cash']['total'] + $breakdown['investment']['total'] + $breakdown['property']['total'] + $businessTotal + $chattelsTotal + $pensionsTotal,
        ];
    }

    /**
     * Calculate total liabilities for the user
     */
    private function calculateLiabilitiesSummary(User $user): array
    {
        // Get mortgages from both Mortgage table and Estate\Liability table (type='mortgage')
        $mortgageRecords = $user->mortgages; // From mortgages table
        $mortgageLiabilities = $user->liabilities->where('liability_type', 'mortgage'); // From liabilities table

        $mortgagesTotal = $mortgageRecords->sum('outstanding_balance') +
                         $mortgageLiabilities->sum('current_balance');

        // Combine mortgage items from both sources
        $mortgageItems = collect();

        // Add Mortgage table records
        foreach ($mortgageRecords as $mortgage) {
            $mortgageItems->push([
                'id' => $mortgage->id,
                'lender' => $mortgage->lender_name,
                'outstanding_balance' => $mortgage->outstanding_balance,
                'interest_rate' => $mortgage->interest_rate,
                'monthly_payment' => $mortgage->monthly_payment,
                'property_id' => $mortgage->property_id,
                'source' => 'mortgage_table',
            ]);
        }

        // Add Estate\Liability mortgage records
        foreach ($mortgageLiabilities as $liability) {
            $mortgageItems->push([
                'id' => $liability->id,
                'lender' => $liability->liability_name,
                'outstanding_balance' => $liability->current_balance,
                'interest_rate' => $liability->interest_rate,
                'monthly_payment' => $liability->monthly_payment,
                'property_id' => null,
                'source' => 'liability_table',
            ]);
        }

        // Get other liabilities (exclude mortgages)
        $otherLiabilities = $user->liabilities->whereNotIn('liability_type', ['mortgage']);
        $otherLiabilitiesTotal = $otherLiabilities->sum('current_balance');

        return [
            'mortgages' => [
                'total' => $mortgagesTotal,
                'count' => $mortgageItems->count(),
                'items' => $mortgageItems,
            ],
            'other' => [
                'total' => $otherLiabilitiesTotal,
                'count' => $otherLiabilities->count(),
                'items' => $otherLiabilities->map(function ($liability) {
                    return [
                        'id' => $liability->id,
                        'liability_type' => $liability->liability_type,
                        'liability_name' => $liability->liability_name,
                        'description' => $liability->liability_name,
                        'amount' => $liability->current_balance,
                        'monthly_payment' => $liability->monthly_payment,
                        'interest_rate' => $liability->interest_rate,
                        'notes' => $liability->notes,
                    ];
                }),
            ],
            'total' => $mortgagesTotal + $otherLiabilitiesTotal,
        ];
    }
}
