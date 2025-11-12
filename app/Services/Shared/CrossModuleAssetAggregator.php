<?php

declare(strict_types=1);

namespace App\Services\Shared;

use App\Models\Investment\InvestmentAccount;
use App\Models\Mortgage;
use App\Models\Property;
use App\Models\SavingsAccount;
use Illuminate\Support\Collection;

/**
 * Cross-Module Asset Aggregator
 *
 * Centralized service for aggregating assets and liabilities from multiple modules.
 * Eliminates duplication between NetWorthAnalyzer and NetWorthService.
 *
 * This service provides a single source of truth for:
 * - Property values (from Property module)
 * - Investment values (from Investment module)
 * - Cash/Savings values (from Savings module)
 * - Mortgage liabilities (from Property module)
 */
class CrossModuleAssetAggregator
{
    /**
     * Get all cross-module assets for a user
     *
     * Returns a collection of asset objects in standardized format:
     * - asset_type: string
     * - asset_name: string
     * - current_value: float
     */
    public function getAllAssets(int $userId): Collection
    {
        $allAssets = collect();

        // Get properties from Property module
        $properties = $this->getPropertyAssets($userId);
        $allAssets = $allAssets->concat($properties);

        // Get investment accounts from Investment module
        $investments = $this->getInvestmentAssets($userId);
        $allAssets = $allAssets->concat($investments);

        // Get savings/cash accounts from Savings module
        $savings = $this->getSavingsAssets($userId);
        $allAssets = $allAssets->concat($savings);

        return $allAssets;
    }

    /**
     * Get property assets applying ownership percentage
     *
     * Properties store the FULL property value. Ownership percentage indicates
     * the user's share (e.g., 50% of a jointly owned property).
     */
    public function getPropertyAssets(int $userId): Collection
    {
        return Property::where('user_id', $userId)->get()->map(function ($property) {
            // Apply ownership percentage (default 100 if not set)
            $ownershipPercentage = $property->ownership_percentage ?? 100;
            $userShare = ($property->current_value * $ownershipPercentage) / 100;

            return (object) [
                'asset_type' => 'property',
                'asset_name' => $property->address_line_1 ?: 'Property',
                'current_value' => (float) $userShare,
                'is_iht_exempt' => false,
                'source_id' => $property->id,
                'source_model' => 'Property',
            ];
        });
    }

    /**
     * Get investment account assets
     *
     * IMPORTANT: current_value is ALREADY stored as the user's share in the database
     * (divided by ownership_percentage when saving). No need to multiply again.
     */
    public function getInvestmentAssets(int $userId): Collection
    {
        return InvestmentAccount::where('user_id', $userId)->get()->map(function ($account) {
            // Determine if ISA (ISAs are NOT IHT-exempt - only exempt from income/CGT)
            $isISA = in_array($account->account_type, ['isa', 'stocks_and_shares_isa', 'lifetime_isa']);

            return (object) [
                'asset_type' => 'investment',
                'asset_name' => $account->provider.' - '.strtoupper($account->account_type),
                'current_value' => (float) $account->current_value,
                'is_iht_exempt' => false, // ISAs are IHT taxable
                'account_type' => $account->account_type,
                'source_id' => $account->id,
                'source_model' => 'InvestmentAccount',
            ];
        });
    }

    /**
     * Get savings/cash account assets
     */
    public function getSavingsAssets(int $userId): Collection
    {
        return SavingsAccount::where('user_id', $userId)->get()->map(function ($account) {
            // Cash ISAs are NOT IHT-exempt - only exempt from income tax
            return (object) [
                'asset_type' => 'cash',
                'asset_name' => $account->institution.' - '.ucfirst($account->account_type),
                'current_value' => $account->current_balance,
                'is_iht_exempt' => false, // Cash ISAs are IHT taxable
                'account_type' => $account->account_type,
                'source_id' => $account->id,
                'source_model' => 'SavingsAccount',
            ];
        });
    }

    /**
     * Calculate total asset values by type
     */
    public function getAssetTotals(int $userId): array
    {
        return [
            'property' => $this->calculatePropertyTotal($userId),
            'investment' => $this->calculateInvestmentTotal($userId),
            'cash' => $this->calculateCashTotal($userId),
        ];
    }

    /**
     * Calculate total property value applying ownership percentage
     *
     * Properties store the FULL property value. We calculate the user's share
     * by applying their ownership_percentage.
     */
    public function calculatePropertyTotal(int $userId): float
    {
        return (float) Property::where('user_id', $userId)
            ->get()
            ->sum(function ($property) {
                $ownershipPercentage = $property->ownership_percentage ?? 100;
                return ($property->current_value * $ownershipPercentage) / 100;
            });
    }

    /**
     * Calculate total investment value
     *
     * IMPORTANT: current_value is ALREADY stored as the user's share in the database
     * (divided by ownership_percentage when saving). No need to multiply again.
     */
    public function calculateInvestmentTotal(int $userId): float
    {
        return (float) InvestmentAccount::where('user_id', $userId)
            ->sum('current_value');
    }

    /**
     * Calculate total cash/savings value
     */
    public function calculateCashTotal(int $userId): float
    {
        return (float) SavingsAccount::where('user_id', $userId)
            ->sum('current_balance');
    }

    /**
     * Get all mortgages for a user
     */
    public function getMortgages(int $userId): Collection
    {
        return Mortgage::where('user_id', $userId)->get();
    }

    /**
     * Calculate total mortgage liabilities
     */
    public function calculateMortgageTotal(int $userId): float
    {
        return (float) Mortgage::where('user_id', $userId)
            ->sum('outstanding_balance');
    }

    /**
     * Get asset breakdown with counts
     */
    public function getAssetBreakdown(int $userId): array
    {
        return [
            'property' => [
                'count' => Property::where('user_id', $userId)->count(),
                'total' => $this->calculatePropertyTotal($userId),
            ],
            'investment' => [
                'count' => InvestmentAccount::where('user_id', $userId)->count(),
                'total' => $this->calculateInvestmentTotal($userId),
            ],
            'cash' => [
                'count' => SavingsAccount::where('user_id', $userId)->count(),
                'total' => $this->calculateCashTotal($userId),
            ],
            'mortgages' => [
                'count' => Mortgage::where('user_id', $userId)->count(),
                'total' => $this->calculateMortgageTotal($userId),
            ],
        ];
    }
}
