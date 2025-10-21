<?php

declare(strict_types=1);

namespace App\Services\UserProfile;

use App\Models\User;
use App\Models\SavingsAccount;

class UserProfileService
{
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
            ],
            'household' => $user->household,
            'spouse' => $user->spouse ? [
                'id' => $user->spouse->id,
                'name' => $user->spouse->name,
                'email' => $user->spouse->email,
            ] : null,
            'income_occupation' => [
                'occupation' => $user->occupation,
                'employer' => $user->employer,
                'industry' => $user->industry,
                'employment_status' => $user->employment_status,
                'annual_employment_income' => $user->annual_employment_income,
                'annual_self_employment_income' => $user->annual_self_employment_income,
                'annual_rental_income' => $this->calculateAnnualRentalIncome($user),
                'annual_dividend_income' => $user->annual_dividend_income,
                'annual_other_income' => $user->annual_other_income,
                'total_annual_income' => (
                    ($user->annual_employment_income ?? 0) +
                    ($user->annual_self_employment_income ?? 0) +
                    $this->calculateAnnualRentalIncome($user) +
                    ($user->annual_dividend_income ?? 0) +
                    ($user->annual_other_income ?? 0)
                ),
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
        // Cash - use SavingsAccount model directly (no ownership_percentage on this table)
        $cashTotal = SavingsAccount::where('user_id', $user->id)->sum('current_balance');
        $cashCount = SavingsAccount::where('user_id', $user->id)->count();

        $investmentsTotal = $user->investmentAccounts->sum(function ($account) {
            return $account->current_value * ($account->ownership_percentage / 100);
        });

        $propertiesTotal = $user->properties->sum(function ($property) {
            return $property->current_value * ($property->ownership_percentage / 100);
        });

        $businessTotal = $user->businessInterests->sum(function ($business) {
            return $business->current_valuation * ($business->ownership_percentage / 100);
        });

        $chattelsTotal = $user->chattels->sum(function ($chattel) {
            return $chattel->current_value * ($chattel->ownership_percentage / 100);
        });

        $pensionsTotal = $user->dcPensions->sum('current_fund_value');

        return [
            'cash' => [
                'total' => $cashTotal,
                'count' => $cashCount,
            ],
            'investments' => [
                'total' => $investmentsTotal,
                'count' => $user->investmentAccounts->count(),
            ],
            'properties' => [
                'total' => $propertiesTotal,
                'count' => $user->properties->count(),
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
            'total' => $cashTotal + $investmentsTotal + $propertiesTotal + $businessTotal + $chattelsTotal + $pensionsTotal,
        ];
    }

    /**
     * Calculate total liabilities for the user
     */
    private function calculateLiabilitiesSummary(User $user): array
    {
        $mortgagesTotal = $user->mortgages->sum('outstanding_balance');

        // TODO: Add other liabilities when implemented (credit cards, loans, etc.)

        return [
            'mortgages' => [
                'total' => $mortgagesTotal,
                'count' => $user->mortgages->count(),
                'items' => $user->mortgages->map(function ($mortgage) {
                    return [
                        'id' => $mortgage->id,
                        'lender' => $mortgage->lender,
                        'outstanding_balance' => $mortgage->outstanding_balance,
                        'interest_rate' => $mortgage->interest_rate,
                        'monthly_payment' => $mortgage->monthly_payment,
                        'property_id' => $mortgage->property_id,
                    ];
                }),
            ],
            'total' => $mortgagesTotal,
        ];
    }
}
