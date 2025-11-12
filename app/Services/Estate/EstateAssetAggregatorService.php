<?php

declare(strict_types=1);

namespace App\Services\Estate;

use App\Models\BusinessInterest;
use App\Models\Chattel;
use App\Models\DBPension;
use App\Models\DCPension;
use App\Models\Estate\Asset;
use App\Models\Estate\Liability;
use App\Models\Investment\InvestmentAccount;
use App\Models\Mortgage;
use App\Models\Property;
use App\Models\SavingsAccount;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Service for aggregating estate assets and liabilities across all modules
 *
 * Consolidates asset gathering logic previously duplicated in EstateController and IHTController
 */
class EstateAssetAggregatorService
{
    /**
     * Gather all assets for a user from all modules
     *
     * Returns a collection of standardized asset objects suitable for IHT calculations
     */
    public function gatherUserAssets(User $user): Collection
    {
        $assets = Asset::where('user_id', $user->id)->get();

        // Investment accounts
        $investmentAccounts = InvestmentAccount::where('user_id', $user->id)->get();
        $investmentAssets = $investmentAccounts->map(function ($account) use ($user) {
            return (object) [
                'user_id' => $user->id,
                'asset_type' => 'investment',
                'asset_name' => $account->provider.' - '.strtoupper($account->account_type),
                'current_value' => $account->current_value,
                'ownership_type' => $account->ownership_type ?? 'individual',
                'is_iht_exempt' => false, // ISAs are NOT IHT-exempt
            ];
        });

        // Properties
        $properties = Property::where('user_id', $user->id)->get();
        $propertyAssets = $properties->map(function ($property) use ($user) {
            // Database already stores the user's share of the value
            // Do NOT apply ownership_percentage calculation here
            return (object) [
                'user_id' => $user->id,
                'asset_type' => 'property',
                'asset_name' => $property->address_line_1 ?: 'Property',
                'current_value' => $property->current_value,
                'ownership_type' => $property->ownership_type ?? 'individual',
                'property_type' => $property->property_type ?? 'unknown', // Include property type for RNRB eligibility
                'is_iht_exempt' => false,
            ];
        });

        // Savings/Cash
        $savingsAccounts = SavingsAccount::where('user_id', $user->id)->get();
        $savingsAssets = $savingsAccounts->map(function ($account) use ($user) {
            return (object) [
                'user_id' => $user->id,
                'asset_type' => 'cash',
                'asset_name' => $account->institution.' - '.ucfirst($account->account_type),
                'current_value' => $account->current_balance,
                'ownership_type' => $account->ownership_type ?? 'individual',
                'is_iht_exempt' => false, // Cash ISAs are NOT IHT-exempt
            ];
        });

        // Business Interests
        $businessInterests = BusinessInterest::where('user_id', $user->id)->get();
        $businessAssets = $businessInterests->map(function ($business) use ($user) {
            $ownershipPercentage = $business->ownership_percentage ?? 100;
            $userValue = $business->current_valuation * ($ownershipPercentage / 100);

            return (object) [
                'user_id' => $user->id,
                'asset_type' => 'business',
                'asset_name' => $business->business_name,
                'current_value' => $userValue,
                'ownership_type' => 'individual', // Business interests typically individual
                'is_iht_exempt' => false, // May qualify for Business Relief (BR) at 50% or 100%
            ];
        });

        // Chattels (personal property)
        $chattels = Chattel::where('user_id', $user->id)->get();
        $chattelAssets = $chattels->map(function ($chattel) use ($user) {
            $ownershipPercentage = $chattel->ownership_percentage ?? 100;
            $userValue = $chattel->current_value * ($ownershipPercentage / 100);

            return (object) [
                'user_id' => $user->id,
                'asset_type' => 'chattel',
                'asset_name' => $chattel->name,
                'current_value' => $userValue,
                'ownership_type' => 'individual',
                'is_iht_exempt' => false,
            ];
        });

        // DC Pensions (not IHT liable but needed for income projections in gifting strategy)
        $dcPensions = DCPension::where('user_id', $user->id)->get();
        $dcPensionAssets = $dcPensions->map(function ($pension) use ($user) {
            return (object) [
                'user_id' => $user->id,
                'asset_type' => 'dc_pension',
                'asset_name' => $pension->scheme_name,
                'current_value' => $pension->current_fund_value,
                'ownership_type' => 'individual',
                'is_iht_exempt' => true, // DC pensions outside estate if beneficiary nominated
            ];
        });

        // DB Pensions (for income projections only - no transfer value in estate)
        $dbPensions = DBPension::where('user_id', $user->id)->get();
        $dbPensionAssets = $dbPensions->map(function ($pension) use ($user) {
            return (object) [
                'user_id' => $user->id,
                'asset_type' => 'db_pension',
                'asset_name' => $pension->scheme_name,
                'current_value' => 0, // DB pensions have no IHT value (die with member)
                'ownership_type' => 'individual',
                'is_iht_exempt' => true,
                'annual_income' => $pension->expected_annual_pension ?? 0, // For income projections
            ];
        });

        return $assets
            ->concat($investmentAssets)
            ->concat($propertyAssets)
            ->concat($savingsAssets)
            ->concat($businessAssets)
            ->concat($chattelAssets)
            ->concat($dcPensionAssets)
            ->concat($dbPensionAssets);
    }

    /**
     * Calculate total liabilities for a user
     * IMPORTANT: Applies 50/50 split for joint liabilities to avoid double counting
     */
    public function calculateUserLiabilities(User $user): float
    {
        // Get liabilities - database already stores the user's share
        $liabilitiesCollection = Liability::where('user_id', $user->id)->get();
        $liabilities = $liabilitiesCollection->sum(function ($liability) {
            $isJoint = ($liability->ownership_type ?? 'individual') === 'joint';
            // Database already stores the user's share - do NOT divide by 2
            $value = $liability->current_balance;
            \Log::info('Liability: ' . ($liability->institution ?? 'Unknown') . ' | Type: ' . ($liability->type ?? 'Unknown') . ' | Joint: ' . ($isJoint ? 'YES' : 'NO') . ' | Value: £' . $value);
            return $value;
        });

        // Get mortgages - database already stores the user's share
        $mortgagesCollection = Mortgage::where('user_id', $user->id)->get();
        $mortgages = $mortgagesCollection->sum(function ($mortgage) {
            $property = $mortgage->property;
            $isJoint = $property && ($property->ownership_type ?? 'individual') === 'joint';
            // Database already stores the user's share - do NOT divide by 2
            $value = $mortgage->outstanding_balance;
            \Log::info('Mortgage: ' . ($property->address_line_1 ?? 'Unknown') . ' | Property Joint: ' . ($isJoint ? 'YES' : 'NO') . ' | Value: £' . $value);
            return $value;
        });

        $total = $liabilities + $mortgages;
        \Log::info('=== USER ' . $user->name . ' TOTAL LIABILITIES: £' . $total . ' ===');

        return $total;
    }

    /**
     * Get mortgages collection for a user
     */
    public function getUserMortgages(User $user): Collection
    {
        return Mortgage::where('user_id', $user->id)->get();
    }

    /**
     * Get liabilities collection for a user
     */
    public function getUserLiabilities(User $user): Collection
    {
        return Liability::where('user_id', $user->id)->get();
    }

    /**
     * Get total existing life cover for a user
     */
    public function getExistingLifeCover(User $user): float
    {
        $lifeInsurance = \App\Models\LifeInsurancePolicy::where('user_id', $user->id)
            ->sum('sum_assured');

        $criticalIllness = \App\Models\CriticalIllnessPolicy::where('user_id', $user->id)
            ->sum('sum_assured');

        return $lifeInsurance + $criticalIllness;
    }

    /**
     * Get user expenditure data
     */
    public function getUserExpenditure(User $user): array
    {
        // Try ExpenditureProfile first
        $expenditureProfile = \App\Models\ExpenditureProfile::where('user_id', $user->id)->first();
        if ($expenditureProfile) {
            return [
                'monthly_expenditure' => $expenditureProfile->total_monthly_expenditure,
                'annual_expenditure' => $expenditureProfile->total_monthly_expenditure * 12,
            ];
        }

        // Fall back to ProtectionProfile if available
        $protectionProfile = \App\Models\ProtectionProfile::where('user_id', $user->id)->first();
        if ($protectionProfile && $protectionProfile->monthly_expenditure) {
            return [
                'monthly_expenditure' => $protectionProfile->monthly_expenditure,
                'annual_expenditure' => $protectionProfile->monthly_expenditure * 12,
            ];
        }

        // No expenditure data available
        return [
            'monthly_expenditure' => 0,
            'annual_expenditure' => 0,
        ];
    }
}
