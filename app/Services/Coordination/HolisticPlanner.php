<?php

declare(strict_types=1);

namespace App\Services\Coordination;

/**
 * HolisticPlanner
 *
 * Creates comprehensive financial plans by integrating insights from all modules.
 * Generates executive summaries, net worth projections, and overall risk assessments.
 */
class HolisticPlanner
{
    /**
     * Create holistic financial plan
     *
     * @param  array  $allAnalysis  Analysis results from all 5 modules
     * @return array Comprehensive plan
     */
    public function createHolisticPlan(int $userId, array $allAnalysis): array
    {
        $executiveSummary = $this->generateExecutiveSummary($allAnalysis);
        $netWorthProjection = $this->projectNetWorthTrajectory($allAnalysis, 20);
        $riskAssessment = $this->assessOverallRisk($allAnalysis);
        $financialSnapshot = $this->createFinancialSnapshot($allAnalysis);

        return [
            'user_id' => $userId,
            'generated_at' => now()->toIso8601String(),
            'executive_summary' => $executiveSummary,
            'financial_snapshot' => $financialSnapshot,
            'net_worth_projection' => $netWorthProjection,
            'risk_assessment' => $riskAssessment,
            'module_summaries' => $this->createModuleSummaries($allAnalysis),
        ];
    }

    /**
     * Generate executive summary with key strengths, vulnerabilities, and priorities
     *
     * @param  array  $plan  Complete plan data
     * @return array Executive summary
     */
    public function generateExecutiveSummary(array $plan): array
    {
        $strengths = $this->identifyKeyStrengths($plan);
        $vulnerabilities = $this->identifyKeyVulnerabilities($plan);
        $priorities = $this->identifyTopPriorities($plan, 5);

        return [
            'overview' => $this->generateOverviewText($plan),
            'key_strengths' => $strengths,
            'key_vulnerabilities' => $vulnerabilities,
            'top_priorities' => $priorities,
            'overall_score' => $this->calculateOverallScore($plan),
        ];
    }

    /**
     * Project net worth trajectory over time
     *
     * @param  array  $allData  All module data
     * @param  int  $years  Number of years to project
     * @return array Net worth projection
     */
    public function projectNetWorthTrajectory(array $allData, int $years): array
    {
        $currentNetWorth = $allData['estate']['net_worth'] ?? 0;
        $projections = [];

        // Baseline: current trajectory without recommendations
        $baselineProjections = $this->projectBaseline($allData, $years, $currentNetWorth);

        // Optimized: trajectory with recommendations implemented
        $optimizedProjections = $this->projectOptimized($allData, $years, $currentNetWorth);

        return [
            'current_net_worth' => $currentNetWorth,
            'baseline_projections' => $baselineProjections,
            'optimized_projections' => $optimizedProjections,
            'improvement' => end($optimizedProjections)['value'] - end($baselineProjections)['value'],
            'improvement_percent' => $this->calculateImprovementPercent($baselineProjections, $optimizedProjections),
        ];
    }

    /**
     * Assess overall risk profile
     *
     * @param  array  $allAnalysis  All module analysis
     * @return array Risk assessment
     */
    public function assessOverallRisk(array $allAnalysis): array
    {
        $riskAreas = [];

        // Protection risk
        $protectionAdequacy = $allAnalysis['protection']['adequacy_score'] ?? 100;
        if ($protectionAdequacy < 50) {
            $riskAreas[] = [
                'area' => 'Protection',
                'severity' => 'high',
                'description' => 'Significant protection gap exposes family to financial hardship.',
                'score' => $protectionAdequacy,
            ];
        } elseif ($protectionAdequacy < 75) {
            $riskAreas[] = [
                'area' => 'Protection',
                'severity' => 'medium',
                'description' => 'Protection coverage could be improved.',
                'score' => $protectionAdequacy,
            ];
        }

        // Emergency fund risk
        $emergencyFundMonths = $allAnalysis['savings']['emergency_fund_months'] ?? 6;
        if ($emergencyFundMonths < 3) {
            $riskAreas[] = [
                'area' => 'Emergency Fund',
                'severity' => 'high',
                'description' => 'Insufficient emergency fund creates cashflow risk.',
                'score' => ($emergencyFundMonths / 6) * 100,
            ];
        }

        // Retirement risk
        $retirementReadiness = $allAnalysis['retirement']['readiness_score'] ?? 100;
        if ($retirementReadiness < 50) {
            $riskAreas[] = [
                'area' => 'Retirement',
                'severity' => 'high',
                'description' => 'On track to face significant retirement income shortfall.',
                'score' => $retirementReadiness,
            ];
        } elseif ($retirementReadiness < 70) {
            $riskAreas[] = [
                'area' => 'Retirement',
                'severity' => 'medium',
                'description' => 'Retirement planning needs improvement.',
                'score' => $retirementReadiness,
            ];
        }

        // Investment risk (concentration, over-allocation)
        if (isset($allAnalysis['investment']['risk_warnings'])) {
            foreach ($allAnalysis['investment']['risk_warnings'] as $warning) {
                $riskAreas[] = [
                    'area' => 'Investment',
                    'severity' => $warning['severity'] ?? 'medium',
                    'description' => $warning['description'] ?? 'Investment risk identified.',
                    'score' => $warning['score'] ?? 50,
                ];
            }
        }

        // IHT risk
        $ihtLiability = $allAnalysis['estate']['iht_liability'] ?? 0;
        if ($ihtLiability > 100000) {
            $riskAreas[] = [
                'area' => 'Inheritance Tax',
                'severity' => 'medium',
                'description' => 'Significant IHT liability on death.',
                'score' => 50,
            ];
        }

        $overallRiskScore = $this->calculateOverallRiskScore($allAnalysis);

        return [
            'overall_risk_score' => $overallRiskScore,
            'risk_level' => $this->getRiskLevel($overallRiskScore),
            'risk_areas' => $riskAreas,
            'total_risk_areas' => count($riskAreas),
        ];
    }

    /**
     * Create current financial snapshot
     */
    private function createFinancialSnapshot(array $allAnalysis): array
    {
        return [
            'net_worth' => $allAnalysis['estate']['net_worth'] ?? 0,
            'liquid_assets' => $allAnalysis['savings']['total_savings'] ?? 0,
            'investment_value' => $allAnalysis['investment']['total_portfolio_value'] ?? 0,
            'pension_value' => $allAnalysis['retirement']['total_pension_value'] ?? 0,
            'property_value' => $allAnalysis['estate']['property_value'] ?? 0,
            'liabilities' => $allAnalysis['estate']['total_liabilities'] ?? 0,
            'monthly_income' => $allAnalysis['estate']['monthly_income'] ?? 0,
            'monthly_expenses' => $allAnalysis['estate']['monthly_expenses'] ?? 0,
            'monthly_surplus' => $allAnalysis['estate']['monthly_surplus'] ?? 0,
        ];
    }

    /**
     * Create module summaries
     */
    private function createModuleSummaries(array $allAnalysis): array
    {
        return [
            'protection' => [
                'status' => $this->getModuleStatus($allAnalysis['protection']['adequacy_score'] ?? 0),
                'adequacy_score' => $allAnalysis['protection']['adequacy_score'] ?? 0,
                'coverage_gap' => $allAnalysis['protection']['coverage_gap'] ?? 0,
                'key_message' => $this->getProtectionMessage($allAnalysis['protection'] ?? []),
            ],
            'savings' => [
                'status' => $this->getModuleStatus(($allAnalysis['savings']['emergency_fund_months'] ?? 0) / 6 * 100),
                'emergency_fund_months' => $allAnalysis['savings']['emergency_fund_months'] ?? 0,
                'total_savings' => $allAnalysis['savings']['total_savings'] ?? 0,
                'key_message' => $this->getSavingsMessage($allAnalysis['savings'] ?? []),
            ],
            'investment' => [
                'status' => $this->getModuleStatus($allAnalysis['investment']['portfolio_health_score'] ?? 70),
                'portfolio_value' => $allAnalysis['investment']['total_portfolio_value'] ?? 0,
                'annual_return' => $allAnalysis['investment']['annual_return_percent'] ?? 0,
                'key_message' => $this->getInvestmentMessage($allAnalysis['investment'] ?? []),
            ],
            'retirement' => [
                'status' => $this->getModuleStatus($allAnalysis['retirement']['readiness_score'] ?? 0),
                'readiness_score' => $allAnalysis['retirement']['readiness_score'] ?? 0,
                'projected_income' => $allAnalysis['retirement']['projected_annual_income'] ?? 0,
                'key_message' => $this->getRetirementMessage($allAnalysis['retirement'] ?? []),
            ],
            'estate' => [
                'status' => $this->getModuleStatus(100 - min(100, ($allAnalysis['estate']['iht_liability'] ?? 0) / 10000)),
                'net_worth' => $allAnalysis['estate']['net_worth'] ?? 0,
                'iht_liability' => $allAnalysis['estate']['iht_liability'] ?? 0,
                'key_message' => $this->getEstateMessage($allAnalysis['estate'] ?? []),
            ],
        ];
    }

    /**
     * Identify key strengths
     */
    private function identifyKeyStrengths(array $plan): array
    {
        $strengths = [];

        // Protection strength
        if (($plan['protection']['adequacy_score'] ?? 0) >= 80) {
            $strengths[] = [
                'area' => 'Protection',
                'description' => 'Excellent protection coverage in place.',
                'score' => $plan['protection']['adequacy_score'],
            ];
        }

        // Emergency fund strength
        if (($plan['savings']['emergency_fund_months'] ?? 0) >= 6) {
            $strengths[] = [
                'area' => 'Emergency Fund',
                'description' => 'Strong emergency fund provides financial resilience.',
                'score' => 100,
            ];
        }

        // Retirement strength
        if (($plan['retirement']['readiness_score'] ?? 0) >= 80) {
            $strengths[] = [
                'area' => 'Retirement',
                'description' => 'On track for comfortable retirement.',
                'score' => $plan['retirement']['readiness_score'],
            ];
        }

        // Investment diversification
        if (isset($plan['investment']['diversification_score']) && $plan['investment']['diversification_score'] >= 80) {
            $strengths[] = [
                'area' => 'Investment',
                'description' => 'Well-diversified investment portfolio.',
                'score' => $plan['investment']['diversification_score'],
            ];
        }

        // Positive net worth
        if (($plan['estate']['net_worth'] ?? 0) > 100000) {
            $strengths[] = [
                'area' => 'Net Worth',
                'description' => 'Strong positive net worth position.',
                'score' => 85,
            ];
        }

        return array_slice($strengths, 0, 5); // Top 5 strengths
    }

    /**
     * Identify key vulnerabilities
     */
    private function identifyKeyVulnerabilities(array $plan): array
    {
        $vulnerabilities = [];

        // Protection gap
        if (($plan['protection']['adequacy_score'] ?? 100) < 50) {
            $vulnerabilities[] = [
                'area' => 'Protection',
                'severity' => 'high',
                'description' => 'Significant protection gap exposes family to financial risk.',
                'score' => $plan['protection']['adequacy_score'],
            ];
        }

        // Emergency fund
        if (($plan['savings']['emergency_fund_months'] ?? 6) < 3) {
            $vulnerabilities[] = [
                'area' => 'Emergency Fund',
                'severity' => 'high',
                'description' => 'Insufficient emergency reserves.',
                'score' => ($plan['savings']['emergency_fund_months'] ?? 0) / 6 * 100,
            ];
        }

        // Retirement shortfall
        if (($plan['retirement']['readiness_score'] ?? 100) < 50) {
            $vulnerabilities[] = [
                'area' => 'Retirement',
                'severity' => 'high',
                'description' => 'On track for retirement income shortfall.',
                'score' => $plan['retirement']['readiness_score'],
            ];
        }

        // IHT liability
        if (($plan['estate']['iht_liability'] ?? 0) > 100000) {
            $vulnerabilities[] = [
                'area' => 'Inheritance Tax',
                'severity' => 'medium',
                'description' => 'Significant IHT liability on death.',
                'score' => 50,
            ];
        }

        // High debt
        if (($plan['estate']['total_liabilities'] ?? 0) > ($plan['estate']['net_worth'] ?? 1) * 0.5) {
            $vulnerabilities[] = [
                'area' => 'Debt',
                'severity' => 'medium',
                'description' => 'High debt relative to net worth.',
                'score' => 40,
            ];
        }

        return array_slice($vulnerabilities, 0, 5); // Top 5 vulnerabilities
    }

    /**
     * Identify top priorities
     */
    private function identifyTopPriorities(array $plan, int $limit): array
    {
        // This will be populated by ranked recommendations from PriorityRanker
        // For now, return generic priorities based on scores
        $priorities = [];

        if (($plan['protection']['adequacy_score'] ?? 100) < 60) {
            $priorities[] = [
                'priority' => 1,
                'area' => 'Protection',
                'action' => 'Review and increase protection coverage',
                'urgency' => 'immediate',
            ];
        }

        if (($plan['savings']['emergency_fund_months'] ?? 6) < 3) {
            $priorities[] = [
                'priority' => 2,
                'area' => 'Savings',
                'action' => 'Build emergency fund to 3-6 months expenses',
                'urgency' => 'immediate',
            ];
        }

        if (($plan['retirement']['readiness_score'] ?? 100) < 60) {
            $priorities[] = [
                'priority' => 3,
                'area' => 'Retirement',
                'action' => 'Increase pension contributions',
                'urgency' => 'short_term',
            ];
        }

        return array_slice($priorities, 0, $limit);
    }

    /**
     * Generate overview text
     */
    private function generateOverviewText(array $plan): string
    {
        $netWorth = $plan['estate']['net_worth'] ?? 0;
        $overallScore = $this->calculateOverallScore($plan);

        if ($overallScore >= 80) {
            return 'Your overall financial position is strong with a net worth of £'.number_format($netWorth, 0).'. Continue your current strategy with minor optimizations.';
        } elseif ($overallScore >= 60) {
            return 'Your financial position is generally good with a net worth of £'.number_format($netWorth, 0).', but there are areas for improvement.';
        } elseif ($overallScore >= 40) {
            return 'Your financial position needs attention. With a net worth of £'.number_format($netWorth, 0).', focus on addressing key vulnerabilities.';
        } else {
            return 'Your financial position requires immediate action. Priority should be given to protection and emergency fund.';
        }
    }

    /**
     * Calculate overall financial health score
     */
    private function calculateOverallScore(array $plan): float
    {
        $scores = [
            ($plan['protection']['adequacy_score'] ?? 0) * 0.25,
            (($plan['savings']['emergency_fund_months'] ?? 0) / 6 * 100) * 0.20,
            ($plan['investment']['portfolio_health_score'] ?? 70) * 0.20,
            ($plan['retirement']['readiness_score'] ?? 0) * 0.25,
            (100 - min(100, ($plan['estate']['iht_liability'] ?? 0) / 10000)) * 0.10,
        ];

        return round(array_sum($scores), 2);
    }

    /**
     * Project baseline net worth (current trajectory)
     */
    private function projectBaseline(array $allData, int $years, float $currentNetWorth): array
    {
        $projections = [];
        $netWorth = $currentNetWorth;
        $annualSavings = ($allData['estate']['monthly_surplus'] ?? 0) * 12;
        $growthRate = 0.04; // 4% average growth

        for ($year = 0; $year <= $years; $year++) {
            $projections[] = [
                'year' => $year,
                'age' => ($allData['user']['age'] ?? 30) + $year,
                'value' => round($netWorth, 2),
            ];

            $netWorth = ($netWorth + $annualSavings) * (1 + $growthRate);
        }

        return $projections;
    }

    /**
     * Project optimized net worth (with recommendations)
     */
    private function projectOptimized(array $allData, int $years, float $currentNetWorth): array
    {
        $projections = [];
        $netWorth = $currentNetWorth;
        $annualSavings = ($allData['estate']['monthly_surplus'] ?? 0) * 12;
        $growthRate = 0.06; // 6% with optimized strategy

        for ($year = 0; $year <= $years; $year++) {
            $projections[] = [
                'year' => $year,
                'age' => ($allData['user']['age'] ?? 30) + $year,
                'value' => round($netWorth, 2),
            ];

            $netWorth = ($netWorth + $annualSavings) * (1 + $growthRate);
        }

        return $projections;
    }

    /**
     * Calculate improvement percentage
     */
    private function calculateImprovementPercent(array $baseline, array $optimized): float
    {
        $baselineFinal = end($baseline)['value'];
        $optimizedFinal = end($optimized)['value'];

        if ($baselineFinal == 0) {
            return 0;
        }

        return round((($optimizedFinal - $baselineFinal) / $baselineFinal) * 100, 2);
    }

    /**
     * Calculate overall risk score
     */
    private function calculateOverallRiskScore(array $allAnalysis): float
    {
        $riskFactors = [
            100 - ($allAnalysis['protection']['adequacy_score'] ?? 100),
            100 - (($allAnalysis['savings']['emergency_fund_months'] ?? 6) / 6 * 100),
            100 - ($allAnalysis['retirement']['readiness_score'] ?? 100),
            min(100, ($allAnalysis['estate']['iht_liability'] ?? 0) / 10000),
        ];

        return round(array_sum($riskFactors) / count($riskFactors), 2);
    }

    /**
     * Get risk level label
     */
    private function getRiskLevel(float $score): string
    {
        if ($score >= 70) {
            return 'High Risk';
        } elseif ($score >= 50) {
            return 'Moderate Risk';
        } elseif ($score >= 30) {
            return 'Low Risk';
        } else {
            return 'Minimal Risk';
        }
    }

    /**
     * Get module status
     */
    private function getModuleStatus(float $score): string
    {
        if ($score >= 80) {
            return 'excellent';
        } elseif ($score >= 60) {
            return 'good';
        } elseif ($score >= 40) {
            return 'needs_improvement';
        } else {
            return 'critical';
        }
    }

    // Module-specific message methods
    private function getProtectionMessage(array $data): string
    {
        $score = $data['adequacy_score'] ?? 0;
        if ($score >= 80) {
            return 'Your protection coverage is excellent.';
        } elseif ($score >= 60) {
            return 'Your protection coverage is adequate but could be improved.';
        } else {
            return 'Your protection coverage needs immediate attention.';
        }
    }

    private function getSavingsMessage(array $data): string
    {
        $months = $data['emergency_fund_months'] ?? 0;
        if ($months >= 6) {
            return 'Your emergency fund is well-established.';
        } elseif ($months >= 3) {
            return 'Your emergency fund covers basic needs but could be stronger.';
        } else {
            return 'Your emergency fund needs to be built up urgently.';
        }
    }

    private function getInvestmentMessage(array $data): string
    {
        $value = $data['total_portfolio_value'] ?? 0;
        if ($value > 100000) {
            return 'You have built a substantial investment portfolio.';
        } elseif ($value > 10000) {
            return 'Your investment portfolio is growing.';
        } else {
            return 'Consider building your investment portfolio for long-term growth.';
        }
    }

    private function getRetirementMessage(array $data): string
    {
        $score = $data['readiness_score'] ?? 0;
        if ($score >= 80) {
            return 'You are on track for a comfortable retirement.';
        } elseif ($score >= 60) {
            return 'Your retirement planning is progressing but needs boosting.';
        } else {
            return 'Your retirement planning requires significant attention.';
        }
    }

    private function getEstateMessage(array $data): string
    {
        $iht = $data['iht_liability'] ?? 0;
        if ($iht == 0) {
            return 'No inheritance tax liability anticipated.';
        } elseif ($iht < 100000) {
            return 'Moderate inheritance tax liability identified.';
        } else {
            return 'Significant inheritance tax planning opportunities exist.';
        }
    }
}
