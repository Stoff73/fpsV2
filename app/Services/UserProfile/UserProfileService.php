<?php

declare(strict_types=1);

namespace App\Services\UserProfile;

use App\Models\User;

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
                'annual_rental_income' => $user->annual_rental_income,
                'annual_dividend_income' => $user->annual_dividend_income,
                'annual_other_income' => $user->annual_other_income,
                'total_annual_income' => (
                    ($user->annual_employment_income ?? 0) +
                    ($user->annual_self_employment_income ?? 0) +
                    ($user->annual_rental_income ?? 0) +
                    ($user->annual_dividend_income ?? 0) +
                    ($user->annual_other_income ?? 0)
                ),
            ],
            'family_members' => $user->familyMembers,
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
        $user->update($data);

        return $user->fresh();
    }

    /**
     * Calculate total assets for the user
     */
    private function calculateAssetsSummary(User $user): array
    {
        $cashTotal = $user->cashAccounts->sum(function ($account) {
            return $account->current_balance * ($account->ownership_percentage / 100);
        });

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
            'cash' => $cashTotal,
            'investments' => $investmentsTotal,
            'properties' => $propertiesTotal,
            'business_interests' => $businessTotal,
            'chattels' => $chattelsTotal,
            'pensions' => $pensionsTotal,
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
            'mortgages' => $mortgagesTotal,
            'total' => $mortgagesTotal,
        ];
    }
}
