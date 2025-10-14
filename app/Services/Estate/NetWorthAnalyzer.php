<?php

declare(strict_types=1);

namespace App\Services\Estate;

use App\Models\Estate\Asset;
use App\Models\Estate\Liability;
use App\Models\Estate\NetWorthStatement;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class NetWorthAnalyzer
{
    /**
     * Calculate current net worth for a user
     */
    public function calculateNetWorth(int $userId): array
    {
        // Get all assets
        $assets = Asset::where('user_id', $userId)->get();
        $totalAssets = $assets->sum('current_value');

        // Get all liabilities
        $liabilities = Liability::where('user_id', $userId)->get();
        $totalLiabilities = $liabilities->sum('current_balance');

        // Calculate net worth
        $netWorth = $totalAssets - $totalLiabilities;

        // Analyze asset composition
        $assetComposition = $this->analyzeAssetComposition($assets);

        // Analyze liability composition
        $liabilityComposition = $this->analyzeLiabilityComposition($liabilities);

        // Calculate ratios
        $debtToAssetRatio = $totalAssets > 0 ? ($totalLiabilities / $totalAssets) : 0;
        $netWorthRatio = $totalAssets > 0 ? ($netWorth / $totalAssets) : 0;

        return [
            'total_assets' => round($totalAssets, 2),
            'total_liabilities' => round($totalLiabilities, 2),
            'net_worth' => round($netWorth, 2),
            'debt_to_asset_ratio' => round($debtToAssetRatio, 4),
            'net_worth_ratio' => round($netWorthRatio, 4),
            'asset_composition' => $assetComposition,
            'liability_composition' => $liabilityComposition,
            'statement_date' => Carbon::now()->format('Y-m-d'),
        ];
    }

    /**
     * Analyze asset composition by type
     */
    public function analyzeAssetComposition(Collection $assets): array
    {
        $totalValue = $assets->sum('current_value');

        if ($totalValue == 0) {
            return [];
        }

        $byType = $assets->groupBy('asset_type')->map(function ($group, $type) use ($totalValue) {
            $typeValue = $group->sum('current_value');
            return [
                'type' => $type,
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
     * Analyze liability composition by type
     */
    private function analyzeLiabilityComposition(Collection $liabilities): array
    {
        $totalValue = $liabilities->sum('current_balance');

        if ($totalValue == 0) {
            return [];
        }

        $byType = $liabilities->groupBy('liability_type')->map(function ($group, $type) use ($totalValue) {
            $typeValue = $group->sum('current_balance');
            $totalMonthlyPayment = $group->sum('monthly_payment');

            return [
                'type' => $type,
                'balance' => round($typeValue, 2),
                'percentage' => round(($typeValue / $totalValue) * 100, 2),
                'count' => $group->count(),
                'monthly_payment' => round($totalMonthlyPayment, 2),
            ];
        })->values()->toArray();

        // Sort by balance descending
        usort($byType, fn ($a, $b) => $b['balance'] <=> $a['balance']);

        return $byType;
    }

    /**
     * Identify concentration risk in assets
     */
    public function identifyConcentrationRisk(Collection $assets): array
    {
        $totalValue = $assets->sum('current_value');
        $risks = [];

        if ($totalValue == 0) {
            return [
                'has_concentration_risk' => false,
                'risks' => [],
            ];
        }

        // Check for single asset concentration (>50% of total)
        foreach ($assets as $asset) {
            $percentage = ($asset->current_value / $totalValue) * 100;

            if ($percentage > 50) {
                $risks[] = [
                    'type' => 'Single Asset Concentration',
                    'asset_name' => $asset->asset_name,
                    'asset_type' => $asset->asset_type,
                    'value' => round($asset->current_value, 2),
                    'percentage' => round($percentage, 2),
                    'severity' => 'High',
                    'recommendation' => 'Consider diversifying - single asset represents over 50% of total wealth',
                ];
            } elseif ($percentage > 30) {
                $risks[] = [
                    'type' => 'Asset Concentration',
                    'asset_name' => $asset->asset_name,
                    'asset_type' => $asset->asset_type,
                    'value' => round($asset->current_value, 2),
                    'percentage' => round($percentage, 2),
                    'severity' => 'Medium',
                    'recommendation' => 'Monitor concentration - asset represents over 30% of total wealth',
                ];
            }
        }

        // Check for asset type concentration
        $byType = $assets->groupBy('asset_type');
        foreach ($byType as $type => $group) {
            $typeValue = $group->sum('current_value');
            $percentage = ($typeValue / $totalValue) * 100;

            if ($percentage > 70) {
                $risks[] = [
                    'type' => 'Asset Type Concentration',
                    'asset_type' => $type,
                    'value' => round($typeValue, 2),
                    'percentage' => round($percentage, 2),
                    'severity' => 'High',
                    'recommendation' => "Over-concentrated in {$type} - consider diversifying across asset classes",
                ];
            }
        }

        return [
            'has_concentration_risk' => count($risks) > 0,
            'risk_count' => count($risks),
            'risks' => $risks,
        ];
    }

    /**
     * Track net worth trend over time
     */
    public function trackNetWorthTrend(int $userId, int $months = 12): array
    {
        $startDate = Carbon::now()->subMonths($months);

        $statements = NetWorthStatement::where('user_id', $userId)
            ->where('statement_date', '>=', $startDate)
            ->orderBy('statement_date', 'asc')
            ->get();

        if ($statements->isEmpty()) {
            return [
                'has_history' => false,
                'message' => 'No historical net worth statements found',
                'trend' => [],
            ];
        }

        $trend = $statements->map(function ($statement) {
            return [
                'date' => $statement->statement_date->format('Y-m-d'),
                'total_assets' => round($statement->total_assets, 2),
                'total_liabilities' => round($statement->total_liabilities, 2),
                'net_worth' => round($statement->net_worth, 2),
            ];
        })->toArray();

        // Calculate overall change
        $firstStatement = $statements->first();
        $lastStatement = $statements->last();

        $change = $lastStatement->net_worth - $firstStatement->net_worth;
        $percentChange = $firstStatement->net_worth != 0
            ? ($change / abs($firstStatement->net_worth)) * 100
            : 0;

        return [
            'has_history' => true,
            'period_months' => $months,
            'statements_count' => $statements->count(),
            'first_statement_date' => $firstStatement->statement_date->format('Y-m-d'),
            'last_statement_date' => $lastStatement->statement_date->format('Y-m-d'),
            'starting_net_worth' => round($firstStatement->net_worth, 2),
            'ending_net_worth' => round($lastStatement->net_worth, 2),
            'change' => round($change, 2),
            'percent_change' => round($percentChange, 2),
            'trend' => $trend,
        ];
    }

    /**
     * Save net worth statement
     */
    public function saveNetWorthStatement(int $userId, array $data): NetWorthStatement
    {
        return NetWorthStatement::create([
            'user_id' => $userId,
            'statement_date' => $data['statement_date'] ?? Carbon::now()->format('Y-m-d'),
            'total_assets' => $data['total_assets'],
            'total_liabilities' => $data['total_liabilities'],
            'net_worth' => $data['net_worth'],
        ]);
    }

    /**
     * Generate net worth summary
     */
    public function generateSummary(int $userId): array
    {
        $netWorth = $this->calculateNetWorth($userId);
        $assets = Asset::where('user_id', $userId)->get();
        $concentrationRisk = $this->identifyConcentrationRisk($assets);
        $trend = $this->trackNetWorthTrend($userId, 12);

        // Calculate health score (0-100)
        $healthScore = $this->calculateNetWorthHealthScore($netWorth, $concentrationRisk);

        return [
            'net_worth' => $netWorth,
            'concentration_risk' => $concentrationRisk,
            'trend' => $trend,
            'health_score' => $healthScore,
        ];
    }

    /**
     * Calculate net worth health score
     */
    private function calculateNetWorthHealthScore(array $netWorth, array $concentrationRisk): array
    {
        $score = 100;
        $factors = [];

        // Debt to asset ratio impact (max -30 points)
        $debtRatio = $netWorth['debt_to_asset_ratio'];
        if ($debtRatio > 0.5) {
            $deduction = min(30, ($debtRatio - 0.5) * 60);
            $score -= $deduction;
            $factors[] = [
                'factor' => 'High Debt Ratio',
                'impact' => -round($deduction, 0),
                'detail' => "Debt-to-asset ratio of " . round($debtRatio * 100, 1) . "% is concerning",
            ];
        }

        // Concentration risk impact (max -40 points)
        if ($concentrationRisk['has_concentration_risk']) {
            $highRisks = count(array_filter($concentrationRisk['risks'], fn ($r) => $r['severity'] === 'High'));
            $mediumRisks = count(array_filter($concentrationRisk['risks'], fn ($r) => $r['severity'] === 'Medium'));

            $deduction = ($highRisks * 20) + ($mediumRisks * 10);
            $score -= $deduction;
            $factors[] = [
                'factor' => 'Concentration Risk',
                'impact' => -$deduction,
                'detail' => "{$highRisks} high and {$mediumRisks} medium concentration risks identified",
            ];
        }

        // Positive net worth bonus (+10 points)
        if ($netWorth['net_worth'] > 0) {
            $score = min(100, $score + 10);
            $factors[] = [
                'factor' => 'Positive Net Worth',
                'impact' => 10,
                'detail' => 'Assets exceed liabilities',
            ];
        }

        $score = max(0, min(100, $score));

        return [
            'score' => round($score, 0),
            'grade' => $this->getHealthGrade($score),
            'factors' => $factors,
        ];
    }

    /**
     * Get health grade based on score
     */
    private function getHealthGrade(float $score): string
    {
        return match (true) {
            $score >= 90 => 'Excellent',
            $score >= 75 => 'Good',
            $score >= 60 => 'Fair',
            $score >= 40 => 'Needs Attention',
            default => 'Poor',
        };
    }
}
