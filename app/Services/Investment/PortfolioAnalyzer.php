<?php

declare(strict_types=1);

namespace App\Services\Investment;

use App\Models\Investment\Holding;
use App\Models\Investment\InvestmentAccount;
use App\Models\Investment\RiskProfile;
use Illuminate\Support\Collection;

class PortfolioAnalyzer
{
    /**
     * Calculate total portfolio value across all accounts
     */
    public function calculateTotalValue(Collection $accounts): float
    {
        return $accounts->sum('current_value');
    }

    /**
     * Calculate portfolio returns (YTD, 1Y, 3Y, 5Y)
     */
    public function calculateReturns(Collection $holdings): array
    {
        $totalCostBasis = $holdings->sum('cost_basis');
        $totalCurrentValue = $holdings->sum('current_value');

        if ($totalCostBasis == 0) {
            return [
                'total_cost_basis' => 0.0,
                'total_current_value' => 0.0,
                'total_gain' => 0.0,
                'total_return_percent' => 0.0,
                'ytd_return' => 0.0,
                'one_year_return' => 0.0,
            ];
        }

        $totalGain = $totalCurrentValue - $totalCostBasis;
        $totalReturnPercent = ($totalGain / $totalCostBasis) * 100;

        return [
            'total_cost_basis' => round($totalCostBasis, 2),
            'total_current_value' => round($totalCurrentValue, 2),
            'total_gain' => round($totalGain, 2),
            'total_return_percent' => round($totalReturnPercent, 2),
            'ytd_return' => round($totalReturnPercent, 2), // Simplified - in production would filter by date
            'one_year_return' => round($totalReturnPercent, 2), // Simplified
        ];
    }

    /**
     * Calculate asset allocation by type
     */
    public function calculateAssetAllocation(Collection $holdings): array
    {
        $totalValue = $holdings->sum('current_value');

        if ($totalValue == 0) {
            return [];
        }

        $byType = $holdings->groupBy('asset_type')->map(function ($group, $type) use ($totalValue) {
            $typeValue = $group->sum('current_value');
            return [
                'asset_type' => $type,
                'value' => round($typeValue, 2),
                'percentage' => round(($typeValue / $totalValue) * 100, 2),
                'count' => $group->count(),
            ];
        })->values()->toArray();

        // Sort by value descending
        usort($byType, fn ($a, $b) => $b['value'] <=> $a['value']);

        return $byType;
    }

    /**
     * Calculate geographic allocation
     * Note: This is a simplified implementation. In production, you'd have region data.
     */
    public function calculateGeographicAllocation(Collection $holdings): array
    {
        // Placeholder - would require additional holding data for geographic info
        return [
            ['region' => 'UK', 'percentage' => 40],
            ['region' => 'US', 'percentage' => 35],
            ['region' => 'Europe', 'percentage' => 15],
            ['region' => 'Emerging Markets', 'percentage' => 10],
        ];
    }

    /**
     * Calculate diversification score (0-100)
     */
    public function calculateDiversificationScore(array $allocation): int
    {
        if (empty($allocation)) {
            return 0;
        }

        $score = 100;

        // Penalty for concentration in single asset type
        foreach ($allocation as $asset) {
            if ($asset['percentage'] >= 90) {
                $score -= 70; // Extreme concentration
            } elseif ($asset['percentage'] >= 80) {
                $score -= 60; // Heavy concentration
            } elseif ($asset['percentage'] >= 60) {
                $score -= 40;
            } elseif ($asset['percentage'] >= 50) {
                $score -= 30;
            } elseif ($asset['percentage'] >= 40) {
                $score -= 20;
            } elseif ($asset['percentage'] >= 30) {
                $score -= 10;
            }
        }

        // Bonus for having multiple asset types
        $assetTypeCount = count($allocation);
        if ($assetTypeCount >= 4) {
            $score += 10;
        } elseif ($assetTypeCount >= 3) {
            $score += 5;
        } elseif ($assetTypeCount <= 1) {
            $score -= 20; // Penalty for being in only one asset type
        }

        return max(0, min(100, $score));
    }

    /**
     * Calculate portfolio risk metrics
     */
    public function calculatePortfolioRisk(Collection $holdings, ?RiskProfile $profile): array
    {
        $allocation = $this->calculateAssetAllocation($holdings);

        // For empty holdings, return default medium risk
        if (empty($allocation)) {
            return [
                'risk_level' => 'medium',
                'equity_percentage' => 0.0,
                'estimated_volatility' => 0.0,
            ];
        }

        // Simplified risk calculation based on asset allocation
        $equityPercent = collect($allocation)->firstWhere('asset_type', 'equity')['percentage'] ?? 0;

        $riskLevel = match (true) {
            $equityPercent >= 70 => 'high',
            $equityPercent >= 30 => 'medium',
            default => 'low',
        };

        $volatilityEstimate = $equityPercent * 0.15; // Simplified: ~15% volatility for equities

        $result = [
            'risk_level' => $riskLevel,
            'equity_percentage' => round($equityPercent, 2),
            'estimated_volatility' => round($volatilityEstimate, 2),
        ];

        if ($profile) {
            $result['matches_risk_profile'] = $this->matchesRiskProfile($equityPercent, $profile);
        }

        return $result;
    }

    /**
     * Check if current allocation matches risk profile
     */
    private function matchesRiskProfile(float $equityPercent, RiskProfile $profile): bool
    {
        $targetRange = match ($profile->risk_tolerance) {
            'cautious' => ['min' => 10, 'max' => 30],
            'balanced' => ['min' => 50, 'max' => 70],
            'adventurous' => ['min' => 75, 'max' => 90],
            default => ['min' => 0, 'max' => 100],
        };

        return $equityPercent >= $targetRange['min'] && $equityPercent <= $targetRange['max'];
    }
}
