<?php

declare(strict_types=1);

namespace App\Services\Investment\RiskProfile;

/**
 * Capacity For Loss Analyzer
 * Assesses ability to absorb investment losses based on financial circumstances
 *
 * Factors Considered:
 * - Age and time to retirement
 * - Income stability and level
 * - Emergency fund adequacy
 * - Debt levels
 * - Dependents
 * - Existing assets
 * - Essential vs discretionary portfolio
 *
 * Capacity Levels:
 * 1. Very Low - Cannot afford any losses
 * 2. Low - Limited ability to absorb losses
 * 3. Moderate - Can withstand moderate losses
 * 4. High - Good capacity for losses
 * 5. Very High - Excellent capacity for substantial losses
 */
class CapacityForLossAnalyzer
{
    /**
     * Analyze capacity for loss
     *
     * @param  array  $financialData  User financial data
     * @return array Capacity analysis
     */
    public function analyzeCapacity(array $financialData): array
    {
        $scores = [];

        // Age and time horizon score
        $scores['age'] = $this->scoreAge($financialData['age'] ?? null, $financialData['retirement_age'] ?? 67);

        // Income score
        $scores['income'] = $this->scoreIncome(
            $financialData['annual_income'] ?? 0,
            $financialData['income_stability'] ?? 'unknown'
        );

        // Emergency fund score
        $scores['emergency_fund'] = $this->scoreEmergencyFund(
            $financialData['emergency_fund'] ?? 0,
            $financialData['monthly_expenses'] ?? 0
        );

        // Debt score
        $scores['debt'] = $this->scoreDebt(
            $financialData['total_debt'] ?? 0,
            $financialData['annual_income'] ?? 0
        );

        // Dependents score
        $scores['dependents'] = $this->scoreDependents($financialData['dependents'] ?? 0);

        // Portfolio purpose score
        $scores['portfolio_purpose'] = $this->scorePortfolioPurpose($financialData['portfolio_purpose'] ?? 'growth');

        // Existing wealth score
        $scores['wealth'] = $this->scoreWealth(
            $financialData['net_worth'] ?? 0,
            $financialData['portfolio_value'] ?? 0
        );

        // Calculate overall capacity
        $overallScore = $this->calculateOverallCapacity($scores);
        $capacityLevel = $this->determineCapacityLevel($overallScore);

        return [
            'capacity_score' => round($overallScore, 1),
            'capacity_level' => $capacityLevel['level'],
            'label' => $capacityLevel['label'],
            'description' => $capacityLevel['description'],
            'component_scores' => $scores,
            'warnings' => $this->generateWarnings($scores, $financialData),
            'recommendations' => $this->generateRecommendations($capacityLevel['level'], $scores),
        ];
    }

    /**
     * Score age and time to retirement
     *
     * @param  int|null  $age  Current age
     * @param  int  $retirementAge  Retirement age
     * @return array Age score
     */
    private function scoreAge(?int $age, int $retirementAge): array
    {
        if ($age === null) {
            return ['score' => 3, 'factor' => 'Unknown age', 'weight' => 0.20];
        }

        $yearsToRetirement = max(0, $retirementAge - $age);

        if ($yearsToRetirement > 20) {
            $score = 5;
            $factor = 'Excellent time horizon (20+ years)';
        } elseif ($yearsToRetirement > 15) {
            $score = 4;
            $factor = 'Good time horizon (15-20 years)';
        } elseif ($yearsToRetirement > 10) {
            $score = 3;
            $factor = 'Moderate time horizon (10-15 years)';
        } elseif ($yearsToRetirement > 5) {
            $score = 2;
            $factor = 'Limited time horizon (5-10 years)';
        } else {
            $score = 1;
            $factor = 'Very limited time horizon (<5 years)';
        }

        return [
            'score' => $score,
            'factor' => $factor,
            'years_to_retirement' => $yearsToRetirement,
            'weight' => 0.20,
        ];
    }

    /**
     * Score income level and stability
     *
     * @param  float  $annualIncome  Annual income
     * @param  string  $stability  Income stability
     * @return array Income score
     */
    private function scoreIncome(float $annualIncome, string $stability): array
    {
        $baseScore = match (true) {
            $annualIncome >= 150000 => 5,
            $annualIncome >= 100000 => 4,
            $annualIncome >= 50000 => 3,
            $annualIncome >= 30000 => 2,
            default => 1,
        };

        $stabilityMultiplier = match ($stability) {
            'very_stable' => 1.0,
            'stable' => 0.9,
            'moderate' => 0.8,
            'variable' => 0.7,
            'unstable' => 0.6,
            default => 0.8,
        };

        $adjustedScore = min(5, $baseScore * $stabilityMultiplier);

        return [
            'score' => round($adjustedScore, 1),
            'factor' => sprintf('Income £%s (%s)', number_format($annualIncome, 0), $stability),
            'weight' => 0.20,
        ];
    }

    /**
     * Score emergency fund adequacy
     *
     * @param  float  $emergencyFund  Emergency fund value
     * @param  float  $monthlyExpenses  Monthly expenses
     * @return array Emergency fund score
     */
    private function scoreEmergencyFund(float $emergencyFund, float $monthlyExpenses): array
    {
        if ($monthlyExpenses == 0) {
            return ['score' => 3, 'factor' => 'Unknown expenses', 'weight' => 0.15];
        }

        $monthsCovered = $emergencyFund / $monthlyExpenses;

        if ($monthsCovered >= 12) {
            $score = 5;
            $factor = 'Excellent emergency fund (12+ months)';
        } elseif ($monthsCovered >= 6) {
            $score = 4;
            $factor = 'Good emergency fund (6-12 months)';
        } elseif ($monthsCovered >= 3) {
            $score = 3;
            $factor = 'Adequate emergency fund (3-6 months)';
        } elseif ($monthsCovered >= 1) {
            $score = 2;
            $factor = 'Limited emergency fund (1-3 months)';
        } else {
            $score = 1;
            $factor = 'Insufficient emergency fund (<1 month)';
        }

        return [
            'score' => $score,
            'factor' => $factor,
            'months_covered' => round($monthsCovered, 1),
            'weight' => 0.15,
        ];
    }

    /**
     * Score debt levels
     *
     * @param  float  $totalDebt  Total debt
     * @param  float  $annualIncome  Annual income
     * @return array Debt score
     */
    private function scoreDebt(float $totalDebt, float $annualIncome): array
    {
        if ($annualIncome == 0) {
            return ['score' => 3, 'factor' => 'Unknown income', 'weight' => 0.15];
        }

        $debtToIncomeRatio = ($totalDebt / $annualIncome) * 100;

        if ($debtToIncomeRatio == 0) {
            $score = 5;
            $factor = 'No debt';
        } elseif ($debtToIncomeRatio <= 100) {
            $score = 4;
            $factor = 'Low debt (≤1x income)';
        } elseif ($debtToIncomeRatio <= 200) {
            $score = 3;
            $factor = 'Moderate debt (1-2x income)';
        } elseif ($debtToIncomeRatio <= 300) {
            $score = 2;
            $factor = 'High debt (2-3x income)';
        } else {
            $score = 1;
            $factor = 'Very high debt (>3x income)';
        }

        return [
            'score' => $score,
            'factor' => $factor,
            'debt_to_income_ratio' => round($debtToIncomeRatio, 1),
            'weight' => 0.15,
        ];
    }

    /**
     * Score dependents
     *
     * @param  int  $dependents  Number of dependents
     * @return array Dependents score
     */
    private function scoreDependents(int $dependents): array
    {
        if ($dependents == 0) {
            return ['score' => 5, 'factor' => 'No dependents', 'weight' => 0.10];
        } elseif ($dependents == 1) {
            return ['score' => 4, 'factor' => '1 dependent', 'weight' => 0.10];
        } elseif ($dependents == 2) {
            return ['score' => 3, 'factor' => '2 dependents', 'weight' => 0.10];
        } elseif ($dependents <= 4) {
            return ['score' => 2, 'factor' => sprintf('%d dependents', $dependents), 'weight' => 0.10];
        } else {
            return ['score' => 1, 'factor' => sprintf('%d dependents', $dependents), 'weight' => 0.10];
        }
    }

    /**
     * Score portfolio purpose
     *
     * @param  string  $purpose  Portfolio purpose
     * @return array Purpose score
     */
    private function scorePortfolioPurpose(string $purpose): array
    {
        return match ($purpose) {
            'discretionary', 'wealth_building' => [
                'score' => 5,
                'factor' => 'Discretionary investment',
                'weight' => 0.10,
            ],
            'growth' => [
                'score' => 4,
                'factor' => 'Long-term growth',
                'weight' => 0.10,
            ],
            'balanced' => [
                'score' => 3,
                'factor' => 'Balanced objective',
                'weight' => 0.10,
            ],
            'income' => [
                'score' => 2,
                'factor' => 'Income generation needed',
                'weight' => 0.10,
            ],
            'essential', 'retirement_income' => [
                'score' => 1,
                'factor' => 'Essential for living expenses',
                'weight' => 0.10,
            ],
            default => [
                'score' => 3,
                'factor' => 'Unknown purpose',
                'weight' => 0.10,
            ],
        };
    }

    /**
     * Score existing wealth
     *
     * @param  float  $netWorth  Net worth
     * @param  float  $portfolioValue  Portfolio value
     * @return array Wealth score
     */
    private function scoreWealth(float $netWorth, float $portfolioValue): array
    {
        if ($portfolioValue == 0) {
            return ['score' => 3, 'factor' => 'Unknown portfolio', 'weight' => 0.10];
        }

        $portfolioPercentage = ($portfolioValue / max(1, $netWorth)) * 100;

        if ($portfolioPercentage <= 20) {
            $score = 5;
            $factor = 'Portfolio is small part of wealth (≤20%)';
        } elseif ($portfolioPercentage <= 40) {
            $score = 4;
            $factor = 'Portfolio is moderate part of wealth (20-40%)';
        } elseif ($portfolioPercentage <= 60) {
            $score = 3;
            $factor = 'Portfolio is significant part of wealth (40-60%)';
        } elseif ($portfolioPercentage <= 80) {
            $score = 2;
            $factor = 'Portfolio is major part of wealth (60-80%)';
        } else {
            $score = 1;
            $factor = 'Portfolio represents most of wealth (>80%)';
        }

        return [
            'score' => $score,
            'factor' => $factor,
            'portfolio_percentage' => round($portfolioPercentage, 1),
            'weight' => 0.10,
        ];
    }

    /**
     * Calculate overall capacity score
     *
     * @param  array  $scores  Component scores
     * @return float Overall score
     */
    private function calculateOverallCapacity(array $scores): float
    {
        $weightedSum = 0;
        $totalWeight = 0;

        foreach ($scores as $component) {
            $weightedSum += $component['score'] * $component['weight'];
            $totalWeight += $component['weight'];
        }

        return $totalWeight > 0 ? ($weightedSum / $totalWeight) : 3;
    }

    /**
     * Determine capacity level
     *
     * @param  float  $score  Overall capacity score
     * @return array Capacity level
     */
    private function determineCapacityLevel(float $score): array
    {
        if ($score <= 1.5) {
            return [
                'level' => 1,
                'label' => 'Very Low Capacity',
                'description' => 'You cannot afford to experience investment losses. Your financial situation requires capital preservation.',
            ];
        } elseif ($score <= 2.5) {
            return [
                'level' => 2,
                'label' => 'Low Capacity',
                'description' => 'You have limited ability to absorb losses. Conservative investments are most appropriate.',
            ];
        } elseif ($score <= 3.5) {
            return [
                'level' => 3,
                'label' => 'Moderate Capacity',
                'description' => 'You can withstand moderate investment losses. Balanced approach is suitable.',
            ];
        } elseif ($score <= 4.5) {
            return [
                'level' => 4,
                'label' => 'High Capacity',
                'description' => 'You have good capacity to absorb losses. Growth-focused investing is appropriate.',
            ];
        } else {
            return [
                'level' => 5,
                'label' => 'Very High Capacity',
                'description' => 'You have excellent capacity for losses. Aggressive investing strategies are suitable.',
            ];
        }
    }

    /**
     * Generate warnings
     *
     * @param  array  $scores  Component scores
     * @param  array  $financialData  Financial data
     * @return array Warnings
     */
    private function generateWarnings(array $scores, array $financialData): array
    {
        $warnings = [];

        if ($scores['emergency_fund']['score'] < 3) {
            $warnings[] = [
                'severity' => 'high',
                'message' => 'Insufficient emergency fund. Build 3-6 months expenses before investing.',
            ];
        }

        if ($scores['debt']['score'] < 3) {
            $warnings[] = [
                'severity' => 'medium',
                'message' => 'High debt levels. Consider debt reduction before aggressive investing.',
            ];
        }

        if ($scores['age']['score'] < 2) {
            $warnings[] = [
                'severity' => 'high',
                'message' => 'Limited time horizon. Focus on capital preservation.',
            ];
        }

        if ($scores['portfolio_purpose']['score'] == 1) {
            $warnings[] = [
                'severity' => 'high',
                'message' => 'Portfolio is essential for income. Cannot afford losses.',
            ];
        }

        return $warnings;
    }

    /**
     * Generate recommendations
     *
     * @param  int  $capacityLevel  Capacity level
     * @param  array  $scores  Component scores
     * @return array Recommendations
     */
    private function generateRecommendations(int $capacityLevel, array $scores): array
    {
        $recommendations = [];

        if ($capacityLevel <= 2 && $scores['emergency_fund']['score'] < 3) {
            $recommendations[] = 'Priority: Build emergency fund to 6 months expenses before investing';
        }

        if ($capacityLevel <= 2 && $scores['debt']['score'] < 3) {
            $recommendations[] = 'Consider debt reduction before increasing investment risk';
        }

        if ($capacityLevel >= 4 && $scores['age']['score'] >= 4) {
            $recommendations[] = 'Strong capacity allows for growth-focused equity investments';
        }

        if ($capacityLevel <= 2) {
            $recommendations[] = 'Focus on capital preservation: bonds, gilts, and cash';
        }

        return $recommendations;
    }
}
