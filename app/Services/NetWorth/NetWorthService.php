<?php

declare(strict_types=1);

namespace App\Services\NetWorth;

use App\Models\BusinessInterest;
use App\Models\Chattel;
use App\Models\Investment\InvestmentAccount;
use App\Models\PersonalAccount;
use App\Models\Property;
use App\Models\User;
use App\Services\Shared\CrossModuleAssetAggregator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class NetWorthService
{
    public function __construct(
        private CrossModuleAssetAggregator $assetAggregator
    ) {}

    /**
     * Calculate net worth for a user
     */
    public function calculateNetWorth(User $user, ?Carbon $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?? Carbon::now();
        $userId = $user->id;

        // Use CrossModuleAssetAggregator for cross-module assets
        $assetTotals = $this->assetAggregator->getAssetTotals($userId);
        $propertyValue = $assetTotals['property'];
        $investmentValue = $assetTotals['investment'];
        $cashValue = $assetTotals['cash'];

        // Calculate Estate-specific assets
        $businessValue = $this->calculateBusinessValue($userId);
        $chattelValue = $this->calculateChattelValue($userId);

        $totalAssets = $propertyValue + $investmentValue + $cashValue + $businessValue + $chattelValue;

        // Use CrossModuleAssetAggregator for mortgages
        $mortgages = $this->assetAggregator->calculateMortgageTotal($userId);
        $otherLiabilities = $this->calculateOtherLiabilities($userId);

        $totalLiabilities = $mortgages + $otherLiabilities;

        // Calculate net worth
        $netWorth = $totalAssets - $totalLiabilities;

        return [
            'total_assets' => round($totalAssets, 2),
            'total_liabilities' => round($totalLiabilities, 2),
            'net_worth' => round($netWorth, 2),
            'as_of_date' => $asOfDate->toDateString(),
            'breakdown' => [
                'property' => round($propertyValue, 2),
                'investments' => round($investmentValue, 2),
                'cash' => round($cashValue, 2),
                'business' => round($businessValue, 2),
                'chattels' => round($chattelValue, 2),
            ],
            'liabilities_breakdown' => [
                'mortgages' => round($mortgages, 2),
                'other' => round($otherLiabilities, 2),
            ],
        ];
    }

    /**
     * Calculate total business value (with ownership percentage)
     */
    private function calculateBusinessValue(int $userId): float
    {
        return BusinessInterest::where('user_id', $userId)
            ->get()
            ->sum(function ($business) {
                $ownershipPercentage = $business->ownership_percentage ?? 100;

                return $business->current_valuation * ($ownershipPercentage / 100);
            });
    }

    /**
     * Calculate total chattel value (with ownership percentage)
     */
    private function calculateChattelValue(int $userId): float
    {
        return Chattel::where('user_id', $userId)
            ->get()
            ->sum(function ($chattel) {
                $ownershipPercentage = $chattel->ownership_percentage ?? 100;

                return $chattel->current_value * ($ownershipPercentage / 100);
            });
    }

    /**
     * Calculate other liabilities from personal accounts
     */
    private function calculateOtherLiabilities(int $userId): float
    {
        return (float) PersonalAccount::where('user_id', $userId)
            ->where('account_type', 'liability')
            ->sum('amount');
    }

    /**
     * Get net worth trend over specified number of months
     */
    public function getNetWorthTrend(User $user, int $months = 12): array
    {
        $trend = [];
        $currentDate = Carbon::now();

        // For now, generate current net worth
        // In production, this would pull from historical snapshots
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = $currentDate->copy()->subMonths($i);

            // Calculate net worth for this month
            // In production, this would come from stored snapshots
            $netWorthData = $this->calculateNetWorth($user, $date);

            $trend[] = [
                'date' => $date->format('Y-m-d'),
                'month' => $date->format('M Y'),
                'net_worth' => $netWorthData['net_worth'],
            ];
        }

        return $trend;
    }

    /**
     * Get asset breakdown with percentages
     */
    public function getAssetBreakdown(User $user): array
    {
        $netWorth = $this->calculateNetWorth($user);
        $breakdown = $netWorth['breakdown'];
        $totalAssets = $netWorth['total_assets'];

        $percentages = [];
        foreach ($breakdown as $type => $value) {
            if ($totalAssets > 0) {
                $percentages[$type] = [
                    'value' => $value,
                    'percentage' => round(($value / $totalAssets) * 100, 2),
                ];
            } else {
                $percentages[$type] = [
                    'value' => 0,
                    'percentage' => 0,
                ];
            }
        }

        return $percentages;
    }

    /**
     * Get assets summary with counts and totals
     */
    public function getAssetsSummary(User $user): array
    {
        $userId = $user->id;

        // Use CrossModuleAssetAggregator for cross-module asset breakdowns
        $breakdown = $this->assetAggregator->getAssetBreakdown($userId);

        return [
            'property' => [
                'count' => $breakdown['property']['count'],
                'total_value' => $breakdown['property']['total'],
            ],
            'investments' => [
                'count' => $breakdown['investment']['count'],
                'total_value' => $breakdown['investment']['total'],
            ],
            'cash' => [
                'count' => $breakdown['cash']['count'],
                'total_value' => $breakdown['cash']['total'],
            ],
            'business' => [
                'count' => BusinessInterest::where('user_id', $userId)->count(),
                'total_value' => $this->calculateBusinessValue($userId),
            ],
            'chattels' => [
                'count' => Chattel::where('user_id', $userId)->count(),
                'total_value' => $this->calculateChattelValue($userId),
            ],
        ];
    }

    /**
     * Get joint assets for a user
     */
    public function getJointAssets(User $user): array
    {
        $userId = $user->id;
        $jointAssets = [];

        // Get joint properties
        $properties = Property::where('user_id', $userId)
            ->where('ownership_type', 'joint')
            ->get()
            ->map(function ($property) {
                return [
                    'type' => 'property',
                    'id' => $property->id,
                    'description' => $property->address_line_1,
                    'value' => $property->current_value,
                    'ownership_percentage' => $property->ownership_percentage,
                    'co_owner' => null, // Co-owner tracking not in schema
                ];
            });

        // Get joint investments
        $investments = InvestmentAccount::where('user_id', $userId)
            ->where('ownership_type', 'joint')
            ->get()
            ->map(function ($investment) {
                return [
                    'type' => 'investment',
                    'id' => $investment->id,
                    'description' => $investment->provider.' - '.$investment->account_type,
                    'value' => $investment->current_value,
                    'ownership_percentage' => $investment->ownership_percentage,
                    'co_owner' => null, // Co-owner tracking not in schema
                ];
            });

        // Get joint savings accounts (savings_accounts table doesn't have ownership_type, so skip for now)
        // TODO: Add ownership_type to savings_accounts table if joint accounts needed
        $cashAccounts = collect([]);

        // Get joint businesses
        $businesses = BusinessInterest::where('user_id', $userId)
            ->where('ownership_type', 'joint')
            ->get()
            ->map(function ($business) {
                return [
                    'type' => 'business',
                    'id' => $business->id,
                    'description' => $business->business_name,
                    'value' => $business->current_valuation,
                    'ownership_percentage' => $business->ownership_percentage,
                    'co_owner' => null, // Co-owner tracking not in schema
                ];
            });

        // Get joint chattels
        $chattels = Chattel::where('user_id', $userId)
            ->where('ownership_type', 'joint')
            ->get()
            ->map(function ($chattel) {
                return [
                    'type' => 'chattel',
                    'id' => $chattel->id,
                    'description' => $chattel->name,
                    'value' => $chattel->current_value,
                    'ownership_percentage' => $chattel->ownership_percentage,
                    'co_owner' => null, // Co-owner tracking not in schema
                ];
            });

        return array_merge(
            $properties->toArray(),
            $investments->toArray(),
            $cashAccounts->toArray(),
            $businesses->toArray(),
            $chattels->toArray()
        );
    }

    /**
     * Get cached net worth or calculate and cache
     */
    public function getCachedNetWorth(User $user): array
    {
        $cacheKey = "net_worth:user_{$user->id}:date_".Carbon::now()->toDateString();

        return Cache::remember($cacheKey, 1800, function () use ($user) {
            return $this->calculateNetWorth($user);
        });
    }

    /**
     * Invalidate net worth cache for a user
     */
    public function invalidateCache(int $userId): void
    {
        $cacheKey = "net_worth:user_{$userId}:date_".Carbon::now()->toDateString();
        Cache::forget($cacheKey);
    }
}
