<?php

declare(strict_types=1);

namespace App\Agents;

use App\Services\Coordination\ConflictResolver;
use App\Services\Coordination\PriorityRanker;
use App\Services\Coordination\HolisticPlanner;
use App\Services\Coordination\CashFlowCoordinator;

/**
 * CoordinatingAgent
 *
 * Orchestrates cross-module analysis by coordinating all module agents.
 * Resolves conflicts, ranks recommendations, and generates holistic financial plans.
 */
class CoordinatingAgent extends BaseAgent
{
    public function __construct(
        private ConflictResolver $conflictResolver,
        private PriorityRanker $priorityRanker,
        private HolisticPlanner $holisticPlanner,
        private CashFlowCoordinator $cashFlowCoordinator,
    ) {}

    /**
     * Analyze user data and generate insights (BaseAgent requirement)
     *
     * @param int $userId
     * @return array
     */
    public function analyze(int $userId): array
    {
        return $this->orchestrateAnalysis($userId);
    }

    /**
     * Generate personalized recommendations (BaseAgent requirement)
     *
     * @param array $analysisData
     * @return array
     */
    public function generateRecommendations(array $analysisData): array
    {
        $userContext = $this->getUserContext($analysisData['user_id'] ?? 0);
        return $this->priorityRanker->rankRecommendations(
            $this->extractRecommendations($analysisData),
            $userContext
        );
    }

    /**
     * Build what-if scenarios (BaseAgent requirement)
     *
     * @param int $userId
     * @param array $parameters
     * @return array
     */
    public function buildScenarios(int $userId, array $parameters): array
    {
        // For coordinating agent, scenarios would involve changing multiple module inputs
        // This is a placeholder for future implementation
        return [
            'message' => 'Cross-module scenarios not yet implemented',
            'scenarios' => [],
        ];
    }

    /**
     * Orchestrate comprehensive analysis across all modules
     *
     * @param int $userId
     * @param array|null $moduleAgents Optional array of instantiated module agents
     * @return array Coordinated analysis results
     */
    public function orchestrateAnalysis(int $userId, ?array $moduleAgents = null): array
    {
        // Collect analysis from all modules
        $allAnalysis = $this->collectModuleAnalysis($userId, $moduleAgents);

        // Calculate available surplus
        $availableSurplus = $this->cashFlowCoordinator->calculateAvailableSurplus($userId);
        $allAnalysis['available_surplus'] = $availableSurplus;

        // Extract recommendations from all modules
        $allRecommendations = $this->extractRecommendations($allAnalysis);

        // Identify conflicts
        $conflicts = $this->conflictResolver->identifyConflicts($allRecommendations);

        // Resolve conflicts
        $resolvedRecommendations = $this->resolveConflicts($allRecommendations, $conflicts);

        // Rank recommendations
        $userContext = $this->getUserContext($userId);
        $rankedRecommendations = $this->rankRecommendations($resolvedRecommendations, $userContext);

        // Optimize cashflow allocation
        $demands = $this->extractDemands($rankedRecommendations);
        $cashFlowAllocation = $this->cashFlowCoordinator->optimizeContributionAllocation($availableSurplus, $demands);
        $shortfallAnalysis = $this->cashFlowCoordinator->identifyCashFlowShortfalls($cashFlowAllocation);

        return [
            'user_id' => $userId,
            'analysis_date' => now()->toIso8601String(),
            'module_analysis' => $allAnalysis,
            'available_surplus' => $availableSurplus,
            'conflicts' => $conflicts,
            'ranked_recommendations' => $rankedRecommendations,
            'cashflow_allocation' => $cashFlowAllocation,
            'shortfall_analysis' => $shortfallAnalysis,
            'summary' => [
                'total_recommendations' => count($rankedRecommendations),
                'conflicts_identified' => count($conflicts),
                'total_monthly_demand' => $cashFlowAllocation['total_demand'] ?? 0,
                'cashflow_surplus' => $availableSurplus,
                'has_shortfall' => $shortfallAnalysis['has_shortfall'] ?? false,
            ],
        ];
    }

    /**
     * Generate holistic financial plan
     *
     * @param int $userId
     * @param array|null $moduleAgents Optional array of instantiated module agents
     * @return array Complete holistic plan
     */
    public function generateHolisticPlan(int $userId, ?array $moduleAgents = null): array
    {
        // Get orchestrated analysis
        $analysis = $this->orchestrateAnalysis($userId, $moduleAgents);

        // Generate holistic plan
        $plan = $this->holisticPlanner->createHolisticPlan($userId, $analysis['module_analysis']);

        // Add ranked recommendations to plan
        $actionPlan = $this->priorityRanker->createActionPlan($analysis['ranked_recommendations']);

        return array_merge($plan, [
            'ranked_recommendations' => $analysis['ranked_recommendations'],
            'action_plan' => $actionPlan['action_plan'],
            'action_plan_summary' => $actionPlan['summary'],
            'cashflow_allocation' => $analysis['cashflow_allocation'],
            'shortfall_analysis' => $analysis['shortfall_analysis'],
            'conflicts' => $analysis['conflicts'],
        ]);
    }

    /**
     * Resolve conflicts between recommendations
     *
     * @param array $allRecommendations
     * @return array Resolved recommendations
     */
    public function resolveConflicts(array $allRecommendations, array $conflicts): array
    {
        $resolved = $allRecommendations;

        foreach ($conflicts as $conflict) {
            switch ($conflict['type']) {
                case 'protection_vs_savings_conflict':
                    $resolution = $this->conflictResolver->resolveProtectionVsSavings($allRecommendations);
                    $resolved['conflict_resolutions'][] = $resolution;
                    break;

                case 'cashflow_conflict':
                    $resolution = $this->conflictResolver->resolveContributionConflicts(
                        $allRecommendations['available_surplus'] ?? 0,
                        $conflict['demands']
                    );
                    $resolved['conflict_resolutions'][] = [
                        'type' => 'cashflow',
                        'resolution' => $resolution,
                    ];
                    break;

                case 'isa_allowance_conflict':
                    $isaAllowance = 20000; // 2024/25
                    $resolution = $this->conflictResolver->resolveISAAllocation($isaAllowance, $conflict['demands']);
                    $resolved['conflict_resolutions'][] = [
                        'type' => 'isa_allowance',
                        'resolution' => $resolution,
                    ];
                    break;
            }
        }

        return $resolved;
    }

    /**
     * Rank recommendations by priority
     *
     * @param array $recommendations
     * @param array $userContext
     * @return array Ranked recommendations
     */
    public function rankRecommendations(array $recommendations, array $userContext): array
    {
        return $this->priorityRanker->rankRecommendations($recommendations, $userContext);
    }

    /**
     * Collect analysis from all module agents
     *
     * @param int $userId
     * @param array|null $moduleAgents
     * @return array
     */
    private function collectModuleAnalysis(int $userId, ?array $moduleAgents): array
    {
        $analysis = [];

        // For now, return placeholder data
        // In full implementation, would call each module agent's analyze() method
        // Example: $analysis['protection'] = $protectionAgent->analyze($userId);

        $analysis['protection'] = [
            'adequacy_score' => 65,
            'coverage_gap' => 150000,
            'recommendations' => [],
        ];

        $analysis['savings'] = [
            'emergency_fund_months' => 4,
            'total_savings' => 15000,
            'recommendations' => [],
        ];

        $analysis['investment'] = [
            'total_portfolio_value' => 50000,
            'portfolio_health_score' => 70,
            'annual_return_percent' => 7.5,
            'recommendations' => [],
        ];

        $analysis['retirement'] = [
            'readiness_score' => 55,
            'total_pension_value' => 120000,
            'projected_annual_income' => 18000,
            'recommendations' => [],
        ];

        $analysis['estate'] = [
            'net_worth' => 350000,
            'iht_liability' => 10000,
            'monthly_income' => 4500,
            'monthly_expenses' => 3200,
            'monthly_surplus' => 1300,
            'recommendations' => [],
        ];

        $analysis['user'] = [
            'age' => 42,
        ];

        return $analysis;
    }

    /**
     * Extract recommendations from module analysis
     *
     * @param array $allAnalysis
     * @return array
     */
    private function extractRecommendations(array $allAnalysis): array
    {
        $recommendations = [
            'module_scores' => [],
        ];

        foreach ($allAnalysis as $module => $analysis) {
            if ($module === 'available_surplus' || $module === 'user') {
                continue;
            }

            // Store module scores for conflict resolution
            $recommendations['module_scores'][$module] = $analysis;

            // Extract module recommendations
            if (isset($analysis['recommendations']) && is_array($analysis['recommendations'])) {
                $recommendations[$module] = $analysis['recommendations'];
            }
        }

        $recommendations['available_surplus'] = $allAnalysis['available_surplus'] ?? 0;

        return $recommendations;
    }

    /**
     * Get user context for priority ranking
     *
     * @param int $userId
     * @return array
     */
    private function getUserContext(int $userId): array
    {
        // In full implementation, fetch from user profile/preferences table
        return [
            'module_priorities' => [
                'protection' => 80,
                'savings' => 75,
                'retirement' => 70,
                'investment' => 60,
                'estate' => 50,
            ],
        ];
    }

    /**
     * Extract contribution demands from recommendations
     *
     * @param array $recommendations
     * @return array
     */
    private function extractDemands(array $recommendations): array
    {
        $demands = [];

        foreach ($recommendations as $rec) {
            if (!isset($rec['module'])) {
                continue;
            }

            $module = $rec['module'];
            $category = $this->mapModuleToCategory($module);

            // Extract monetary demand
            $amount = $rec['recommended_monthly_contribution']
                ?? $rec['recommended_monthly_premium']
                ?? 0;

            if ($amount > 0) {
                if (!isset($demands[$category])) {
                    $demands[$category] = [
                        'amount' => 0,
                        'urgency' => $rec['urgency_score'] ?? 50,
                    ];
                }
                $demands[$category]['amount'] += $amount;
                $demands[$category]['urgency'] = max($demands[$category]['urgency'], $rec['urgency_score'] ?? 50);
            }
        }

        return $demands;
    }

    /**
     * Map module name to cashflow category
     *
     * @param string $module
     * @return string
     */
    private function mapModuleToCategory(string $module): string
    {
        return match ($module) {
            'protection' => 'protection',
            'savings' => 'emergency_fund',
            'investment' => 'investment',
            'retirement' => 'pension',
            'estate' => 'estate',
            default => $module,
        };
    }
}
