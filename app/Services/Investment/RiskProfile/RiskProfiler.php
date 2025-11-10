<?php

declare(strict_types=1);

namespace App\Services\Investment\RiskProfile;

/**
 * Risk Profiler
 * Determines investment risk profile from questionnaire responses and capacity for loss
 *
 * Risk Profiles:
 * 1. Very Conservative (Score 1-2)
 * 2. Conservative (Score 3-4)
 * 3. Moderate (Score 5-6)
 * 4. Growth (Score 7-8)
 * 5. Aggressive (Score 9-10)
 *
 * Profile includes:
 * - Risk classification
 * - Recommended asset allocation
 * - Expected return and volatility
 * - Suitable investment types
 * - Time horizon recommendations
 */
class RiskProfiler
{
    public function __construct(
        private RiskQuestionnaire $questionnaire,
        private CapacityForLossAnalyzer $capacityAnalyzer
    ) {}

    /**
     * Generate complete risk profile from questionnaire answers
     *
     * @param  array  $answers  Questionnaire answers
     * @param  array  $financialData  User financial data (optional)
     * @return array Complete risk profile
     */
    public function generateRiskProfile(array $answers, array $financialData = []): array
    {
        // Calculate questionnaire score
        $scoreResult = $this->questionnaire->calculateRiskScore($answers);

        // Determine risk tolerance profile
        $toleranceProfile = $this->determineRiskTolerance($scoreResult['normalized_score']);

        // Calculate capacity for loss (if financial data provided)
        $capacityProfile = null;
        if (! empty($financialData)) {
            $capacityProfile = $this->capacityAnalyzer->analyzeCapacity($financialData);
        }

        // Final risk profile (adjusted for capacity)
        $finalProfile = $this->reconcileToleranceAndCapacity($toleranceProfile, $capacityProfile);

        // Get asset allocation recommendation
        $assetAllocation = $this->getAssetAllocation($finalProfile['risk_level']);

        // Get expected returns and volatility
        $expectations = $this->getReturnExpectations($finalProfile['risk_level']);

        return [
            'risk_score' => $scoreResult['normalized_score'],
            'risk_level' => $finalProfile['risk_level'],
            'risk_label' => $finalProfile['label'],
            'risk_description' => $finalProfile['description'],
            'tolerance_profile' => $toleranceProfile,
            'capacity_profile' => $capacityProfile,
            'reconciliation_note' => $finalProfile['reconciliation_note'] ?? null,
            'asset_allocation' => $assetAllocation,
            'return_expectations' => $expectations,
            'suitable_investments' => $this->getSuitableInvestments($finalProfile['risk_level']),
            'time_horizon' => $this->getRecommendedTimeHorizon($finalProfile['risk_level']),
            'rebalancing_frequency' => $this->getRebalancingFrequency($finalProfile['risk_level']),
            'score_breakdown' => $scoreResult,
        ];
    }

    /**
     * Determine risk tolerance from score
     *
     * @param  float  $score  Normalized risk score (1-10)
     * @return array Risk tolerance profile
     */
    private function determineRiskTolerance(float $score): array
    {
        if ($score <= 2) {
            return [
                'risk_level' => 1,
                'label' => 'Very Conservative',
                'description' => 'You prioritize capital preservation above all else. You are uncomfortable with any volatility and prefer guaranteed returns.',
                'characteristics' => [
                    'Cannot tolerate losses',
                    'Prefers guaranteed returns',
                    'Very short-term focus',
                    'Extremely risk-averse',
                ],
            ];
        } elseif ($score <= 4) {
            return [
                'risk_level' => 2,
                'label' => 'Conservative',
                'description' => 'You prefer stability and are willing to accept modest returns to minimize risk. Small losses cause significant concern.',
                'characteristics' => [
                    'Low tolerance for volatility',
                    'Prefers stable income',
                    'Risk-averse',
                    'Focus on capital preservation',
                ],
            ];
        } elseif ($score <= 6) {
            return [
                'risk_level' => 3,
                'label' => 'Moderate',
                'description' => 'You seek balance between growth and stability. You can accept moderate volatility for potentially higher returns.',
                'characteristics' => [
                    'Balanced approach',
                    'Can tolerate moderate losses',
                    'Medium-term perspective',
                    'Seeks growth with safety',
                ],
            ];
        } elseif ($score <= 8) {
            return [
                'risk_level' => 4,
                'label' => 'Growth',
                'description' => 'You prioritize long-term growth and are comfortable with significant volatility. Short-term losses do not concern you.',
                'characteristics' => [
                    'High tolerance for volatility',
                    'Long-term focus',
                    'Seeks capital appreciation',
                    'Can withstand market downturns',
                ],
            ];
        } else {
            return [
                'risk_level' => 5,
                'label' => 'Aggressive',
                'description' => 'You actively seek maximum returns and are comfortable with high volatility and potential substantial losses in pursuit of gains.',
                'characteristics' => [
                    'Very high risk tolerance',
                    'Seeks maximum growth',
                    'Long-term investor',
                    'Comfortable with large losses',
                ],
            ];
        }
    }

    /**
     * Reconcile risk tolerance with capacity for loss
     *
     * @param  array  $tolerance  Tolerance profile
     * @param  array|null  $capacity  Capacity profile
     * @return array Reconciled profile
     */
    private function reconcileToleranceAndCapacity(array $tolerance, ?array $capacity): array
    {
        if (! $capacity || ! isset($capacity['capacity_level'])) {
            // No capacity data - use tolerance as-is
            return $tolerance;
        }

        $toleranceLevel = $tolerance['risk_level'];
        $capacityLevel = $capacity['capacity_level'];

        // If capacity is lower than tolerance, reduce risk level
        if ($capacityLevel < $toleranceLevel) {
            $adjustedLevel = $capacityLevel;
            $adjustedProfile = $this->getRiskProfileByLevel($adjustedLevel);

            $adjustedProfile['reconciliation_note'] = sprintf(
                'Risk level adjusted from %s to %s due to capacity for loss constraints. Your risk tolerance is higher than your financial capacity to absorb losses.',
                $tolerance['label'],
                $adjustedProfile['label']
            );

            return $adjustedProfile;
        }

        // If capacity is higher or equal, use tolerance
        if ($capacityLevel > $toleranceLevel + 1) {
            $tolerance['reconciliation_note'] = sprintf(
                'Your capacity for loss is high (%s), but we recommend %s based on your comfort level. You could take more risk if desired.',
                $capacity['label'],
                $tolerance['label']
            );
        }

        return $tolerance;
    }

    /**
     * Get risk profile by level
     *
     * @param  int  $level  Risk level (1-5)
     * @return array Risk profile
     */
    private function getRiskProfileByLevel(int $level): array
    {
        // Convert level to equivalent score and get profile
        $score = ($level * 2) - 0.5; // Level 1 = 1.5, Level 2 = 3.5, etc.

        return $this->determineRiskTolerance($score);
    }

    /**
     * Get recommended asset allocation
     *
     * @param  int  $riskLevel  Risk level (1-5)
     * @return array Asset allocation
     */
    private function getAssetAllocation(int $riskLevel): array
    {
        return match ($riskLevel) {
            1 => [ // Very Conservative
                'equities' => 10,
                'bonds' => 70,
                'cash' => 20,
                'alternatives' => 0,
                'description' => 'Heavily weighted towards bonds and cash for capital preservation',
            ],
            2 => [ // Conservative
                'equities' => 30,
                'bonds' => 55,
                'cash' => 10,
                'alternatives' => 5,
                'description' => 'Bond-focused with modest equity exposure',
            ],
            3 => [ // Moderate
                'equities' => 50,
                'bonds' => 40,
                'cash' => 5,
                'alternatives' => 5,
                'description' => 'Balanced portfolio with equal emphasis on growth and stability',
            ],
            4 => [ // Growth
                'equities' => 75,
                'bonds' => 20,
                'cash' => 0,
                'alternatives' => 5,
                'description' => 'Equity-focused for long-term capital growth',
            ],
            5 => [ // Aggressive
                'equities' => 90,
                'bonds' => 5,
                'cash' => 0,
                'alternatives' => 5,
                'description' => 'Maximum equity exposure for aggressive growth',
            ],
            default => [
                'equities' => 50,
                'bonds' => 40,
                'cash' => 5,
                'alternatives' => 5,
                'description' => 'Balanced default allocation',
            ],
        };
    }

    /**
     * Get return expectations
     *
     * @param  int  $riskLevel  Risk level
     * @return array Expected returns and volatility
     */
    private function getReturnExpectations(int $riskLevel): array
    {
        return match ($riskLevel) {
            1 => [
                'expected_return_min' => 1.0,
                'expected_return_max' => 3.0,
                'expected_return_typical' => 2.0,
                'expected_volatility' => 3.0,
                'worst_year' => -2.0,
                'best_year' => 8.0,
            ],
            2 => [
                'expected_return_min' => 2.0,
                'expected_return_max' => 4.5,
                'expected_return_typical' => 3.5,
                'expected_volatility' => 6.0,
                'worst_year' => -5.0,
                'best_year' => 12.0,
            ],
            3 => [
                'expected_return_min' => 3.5,
                'expected_return_max' => 6.5,
                'expected_return_typical' => 5.0,
                'expected_volatility' => 10.0,
                'worst_year' => -10.0,
                'best_year' => 18.0,
            ],
            4 => [
                'expected_return_min' => 5.0,
                'expected_return_max' => 8.5,
                'expected_return_typical' => 6.5,
                'expected_volatility' => 15.0,
                'worst_year' => -20.0,
                'best_year' => 28.0,
            ],
            5 => [
                'expected_return_min' => 6.0,
                'expected_return_max' => 12.0,
                'expected_return_typical' => 8.0,
                'expected_volatility' => 20.0,
                'worst_year' => -35.0,
                'best_year' => 40.0,
            ],
            default => [
                'expected_return_min' => 3.5,
                'expected_return_max' => 6.5,
                'expected_return_typical' => 5.0,
                'expected_volatility' => 10.0,
                'worst_year' => -10.0,
                'best_year' => 18.0,
            ],
        };
    }

    /**
     * Get suitable investment types
     *
     * @param  int  $riskLevel  Risk level
     * @return array Suitable investments
     */
    private function getSuitableInvestments(int $riskLevel): array
    {
        return match ($riskLevel) {
            1 => [
                'recommended' => ['Cash ISA', 'Premium Bonds', 'Short-term gilts', 'Money market funds'],
                'avoid' => ['Individual stocks', 'High-yield bonds', 'Emerging markets', 'Small caps'],
            ],
            2 => [
                'recommended' => ['UK gilt funds', 'Investment grade corporate bonds', 'Diversified bond funds', 'Low-volatility equity funds'],
                'avoid' => ['Individual stocks', 'High-yield bonds', 'Sector-specific funds', 'Emerging markets'],
            ],
            3 => [
                'recommended' => ['Global equity index funds', 'UK equity funds', 'Balanced funds', 'Mixed asset funds'],
                'avoid' => ['Single stocks', 'Leveraged products', 'Concentrated sector bets'],
            ],
            4 => [
                'recommended' => ['Global equity funds', 'Emerging market funds', 'Small cap funds', 'Growth funds'],
                'avoid' => ['Leveraged products', 'Derivatives', 'Highly speculative investments'],
            ],
            5 => [
                'recommended' => ['Individual stocks', 'Emerging markets', 'Small caps', 'Thematic funds', 'Alternative investments'],
                'avoid' => ['Capital-protected products', 'Cash holdings'],
            ],
            default => [
                'recommended' => ['Global equity index funds', 'Balanced funds'],
                'avoid' => ['Highly speculative investments'],
            ],
        };
    }

    /**
     * Get recommended time horizon
     *
     * @param  int  $riskLevel  Risk level
     * @return array Time horizon recommendation
     */
    private function getRecommendedTimeHorizon(int $riskLevel): array
    {
        return match ($riskLevel) {
            1 => [
                'minimum_years' => 0,
                'recommended_years' => 1,
                'description' => 'Short-term focus - suitable for immediate or near-term needs',
            ],
            2 => [
                'minimum_years' => 2,
                'recommended_years' => 3,
                'description' => 'Short to medium-term - 2-5 years',
            ],
            3 => [
                'minimum_years' => 5,
                'recommended_years' => 7,
                'description' => 'Medium-term - 5-10 years recommended',
            ],
            4 => [
                'minimum_years' => 7,
                'recommended_years' => 10,
                'description' => 'Long-term - 7-15 years or more',
            ],
            5 => [
                'minimum_years' => 10,
                'recommended_years' => 15,
                'description' => 'Very long-term - 10+ years required',
            ],
            default => [
                'minimum_years' => 5,
                'recommended_years' => 7,
                'description' => 'Medium-term recommended',
            ],
        };
    }

    /**
     * Get recommended rebalancing frequency
     *
     * @param  int  $riskLevel  Risk level
     * @return array Rebalancing recommendation
     */
    private function getRebalancingFrequency(int $riskLevel): array
    {
        return match ($riskLevel) {
            1 => [
                'frequency' => 'quarterly',
                'threshold' => 5,
                'description' => 'Rebalance quarterly or when allocation drifts by 5%',
            ],
            2 => [
                'frequency' => 'semi_annual',
                'threshold' => 7,
                'description' => 'Rebalance semi-annually or when allocation drifts by 7%',
            ],
            3 => [
                'frequency' => 'annual',
                'threshold' => 10,
                'description' => 'Rebalance annually or when allocation drifts by 10%',
            ],
            4 => [
                'frequency' => 'annual',
                'threshold' => 15,
                'description' => 'Rebalance annually or when allocation drifts by 15%',
            ],
            5 => [
                'frequency' => 'biennial',
                'threshold' => 20,
                'description' => 'Rebalance every 2 years or when allocation drifts by 20%',
            ],
            default => [
                'frequency' => 'annual',
                'threshold' => 10,
                'description' => 'Annual rebalancing recommended',
            ],
        };
    }
}
