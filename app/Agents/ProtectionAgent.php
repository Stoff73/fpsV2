<?php

declare(strict_types=1);

namespace App\Agents;

use App\Models\User;
use App\Services\Protection\AdequacyScorer;
use App\Services\Protection\CoverageGapAnalyzer;
use App\Services\Protection\RecommendationEngine;
use App\Services\Protection\ScenarioBuilder;
use Illuminate\Support\Facades\Cache;

class ProtectionAgent extends BaseAgent
{
    /**
     * Create a new Protection Agent instance.
     */
    public function __construct(
        private CoverageGapAnalyzer $gapAnalyzer,
        private AdequacyScorer $adequacyScorer,
        private RecommendationEngine $recommendationEngine,
        private ScenarioBuilder $scenarioBuilder
    ) {}

    /**
     * Analyze user's protection situation.
     */
    public function analyze(int $userId): array
    {
        $cacheKey = "protection_analysis_{$userId}";

        return $this->remember($cacheKey, function () use ($userId) {
            $user = User::with([
                'protectionProfile',
                'lifeInsurancePolicies',
                'criticalIllnessPolicies',
                'incomeProtectionPolicies',
                'disabilityPolicies',
                'sicknessIllnessPolicies',
            ])->findOrFail($userId);

            if (! $user->protectionProfile) {
                return $this->response(
                    false,
                    'Protection profile not found. Please create a protection profile first.',
                    []
                );
            }

            $profile = $user->protectionProfile;

            // Calculate protection needs
            $needs = $this->gapAnalyzer->calculateProtectionNeeds($profile);

            // Calculate current coverage
            $coverage = $this->gapAnalyzer->calculateTotalCoverage(
                $user->lifeInsurancePolicies,
                $user->criticalIllnessPolicies,
                $user->incomeProtectionPolicies,
                $user->disabilityPolicies,
                $user->sicknessIllnessPolicies
            );

            // Calculate gaps
            $gaps = $this->gapAnalyzer->calculateCoverageGap($needs, $coverage);

            // Calculate adequacy score
            $adequacyScore = $this->adequacyScorer->calculateAdequacyScore($gaps, $needs);
            $scoreInsights = $this->adequacyScorer->generateScoreInsights($adequacyScore, $gaps);

            // Generate recommendations
            $recommendations = $this->recommendationEngine->generateRecommendations($gaps, $profile);

            // Build scenarios
            $scenarios = [
                'death' => $this->scenarioBuilder->modelDeathScenario($profile, $coverage),
                'critical_illness' => $this->scenarioBuilder->modelCriticalIllnessScenario($profile, $coverage),
                'disability' => $this->scenarioBuilder->modelDisabilityScenario($profile, $coverage),
            ];

            // Calculate total annual income from user's actual income fields (all sources for reference)
            // Note: Human capital calculation excludes rental/dividend income as these continue after death
            $totalAnnualIncome = ($user->annual_employment_income ?? 0)
                               + ($user->annual_self_employment_income ?? 0)
                               + ($user->annual_rental_income ?? 0)
                               + ($user->annual_dividend_income ?? 0)
                               + ($user->annual_other_income ?? 0);

            // Calculate current age
            $currentAge = $user->date_of_birth ?
                (int) $user->date_of_birth->diffInYears(now()) : 40;

            // Calculate debt breakdown
            $mortgageDebt = $user->mortgages()->sum('outstanding_balance');
            $otherDebt = $user->liabilities()->sum('current_balance');

            return $this->response(
                true,
                'Protection analysis completed successfully.',
                [
                    'profile' => [
                        'annual_income' => (float) $profile->annual_income,
                        'total_annual_income' => (float) $totalAnnualIncome,
                        'monthly_expenditure' => (float) $profile->monthly_expenditure,
                        'mortgage_balance' => (float) $profile->mortgage_balance,
                        'other_debts' => (float) $profile->other_debts,
                        'number_of_dependents' => $profile->number_of_dependents,
                        'retirement_age' => $profile->retirement_age,
                        'current_age' => $currentAge,
                    ],
                    'needs' => $needs,
                    'coverage' => $coverage,
                    'gaps' => $gaps,
                    'adequacy_score' => $scoreInsights,
                    'recommendations' => $recommendations,
                    'scenarios' => $scenarios,
                    'debt_breakdown' => [
                        'mortgage' => (float) $mortgageDebt,
                        'other' => (float) $otherDebt,
                        'total' => (float) ($mortgageDebt + $otherDebt),
                    ],
                    'policies' => [
                        'life_insurance' => $user->lifeInsurancePolicies->map(fn ($p) => [
                            'id' => $p->id,
                            'policy_type' => $p->policy_type,
                            'provider' => $p->provider,
                            'sum_assured' => (float) $p->sum_assured,
                            'premium_amount' => (float) $p->premium_amount,
                            'premium_frequency' => $p->premium_frequency,
                        ]),
                        'critical_illness' => $user->criticalIllnessPolicies->map(fn ($p) => [
                            'id' => $p->id,
                            'policy_type' => $p->policy_type,
                            'provider' => $p->provider,
                            'sum_assured' => (float) $p->sum_assured,
                            'premium_amount' => (float) $p->premium_amount,
                            'premium_frequency' => $p->premium_frequency,
                        ]),
                        'income_protection' => $user->incomeProtectionPolicies->map(fn ($p) => [
                            'id' => $p->id,
                            'provider' => $p->provider,
                            'benefit_amount' => (float) $p->benefit_amount,
                            'benefit_frequency' => $p->benefit_frequency,
                            'deferred_period_weeks' => $p->deferred_period_weeks,
                        ]),
                        'disability' => $user->disabilityPolicies->map(fn ($p) => [
                            'id' => $p->id,
                            'provider' => $p->provider,
                            'benefit_amount' => (float) $p->benefit_amount,
                            'benefit_frequency' => $p->benefit_frequency,
                            'deferred_period_weeks' => $p->deferred_period_weeks,
                            'coverage_type' => $p->coverage_type,
                        ]),
                        'sickness_illness' => $user->sicknessIllnessPolicies->map(fn ($p) => [
                            'id' => $p->id,
                            'provider' => $p->provider,
                            'benefit_amount' => (float) $p->benefit_amount,
                            'benefit_frequency' => $p->benefit_frequency,
                            'conditions_covered' => $p->conditions_covered,
                        ]),
                    ],
                ]
            );
        });
    }

    /**
     * Generate personalized recommendations based on analysis.
     */
    public function generateRecommendations(array $analysisData): array
    {
        if (! isset($analysisData['data']['recommendations'])) {
            return $this->response(
                false,
                'Analysis data is incomplete. Please run analysis first.',
                []
            );
        }

        return $this->response(
            true,
            'Recommendations generated successfully.',
            [
                'recommendations' => $analysisData['data']['recommendations'],
            ]
        );
    }

    /**
     * Build what-if scenarios for user planning.
     */
    public function buildScenarios(int $userId, array $parameters): array
    {
        $user = User::with([
            'protectionProfile',
            'lifeInsurancePolicies',
            'criticalIllnessPolicies',
            'incomeProtectionPolicies',
            'disabilityPolicies',
            'sicknessIllnessPolicies',
        ])->findOrFail($userId);

        if (! $user->protectionProfile) {
            return $this->response(
                false,
                'Protection profile not found.',
                []
            );
        }

        $profile = $user->protectionProfile;

        $coverage = $this->gapAnalyzer->calculateTotalCoverage(
            $user->lifeInsurancePolicies,
            $user->criticalIllnessPolicies,
            $user->incomeProtectionPolicies,
            $user->disabilityPolicies,
            $user->sicknessIllnessPolicies
        );

        $scenarios = [];

        // Build requested scenarios
        if (isset($parameters['scenario_types'])) {
            foreach ($parameters['scenario_types'] as $scenarioType) {
                $scenarios[$scenarioType] = match ($scenarioType) {
                    'death' => $this->scenarioBuilder->modelDeathScenario($profile, $coverage),
                    'critical_illness' => $this->scenarioBuilder->modelCriticalIllnessScenario($profile, $coverage),
                    'disability' => $this->scenarioBuilder->modelDisabilityScenario($profile, $coverage),
                    'premium_change' => isset($parameters['new_coverage']) ?
                        $this->scenarioBuilder->modelPremiumChangeScenario($coverage, $parameters['new_coverage']) :
                        null,
                    default => null,
                };
            }
        } else {
            // Default: build all scenarios
            $scenarios = [
                'death' => $this->scenarioBuilder->modelDeathScenario($profile, $coverage),
                'critical_illness' => $this->scenarioBuilder->modelCriticalIllnessScenario($profile, $coverage),
                'disability' => $this->scenarioBuilder->modelDisabilityScenario($profile, $coverage),
            ];
        }

        return $this->response(
            true,
            'Scenarios built successfully.',
            [
                'scenarios' => array_filter($scenarios),
            ]
        );
    }

    /**
     * Invalidate cache for user's protection analysis.
     */
    public function invalidateCache(int $userId): void
    {
        $cacheKey = "protection_analysis_{$userId}";
        Cache::forget($cacheKey);
    }
}
