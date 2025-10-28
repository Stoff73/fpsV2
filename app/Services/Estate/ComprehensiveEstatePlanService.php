<?php

declare(strict_types=1);

namespace App\Services\Estate;

use App\Models\Estate\IHTProfile;
use App\Models\FamilyMember;
use App\Models\User;
use App\Services\UserProfile\ProfileCompletenessChecker;
use Illuminate\Support\Collection;

/**
 * Generates a comprehensive estate plan combining:
 * - User profile and estate overview
 * - Gifting strategy (PETs and annual exemptions)
 * - Life policy strategy (whole of life vs self-insurance)
 * - Trust strategy (CLTs and various trust types)
 * - Optimized combined recommendation
 */
class ComprehensiveEstatePlanService
{
    public function __construct(
        private PersonalizedGiftingStrategyService $giftingStrategy,
        private PersonalizedTrustStrategyService $trustStrategy,
        private NetWorthAnalyzer $netWorthAnalyzer,
        private IHTCalculator $ihtCalculator,
        private EstateAssetAggregatorService $assetAggregator,
        private ProfileCompletenessChecker $completenessChecker
    ) {}

    /**
     * Generate comprehensive estate plan
     */
    public function generateComprehensiveEstatePlan(User $user): array
    {
        // Check profile completeness
        $profileCompleteness = $this->completenessChecker->checkCompleteness($user);
        $completenessScore = $profileCompleteness['completeness_score'];
        $isComplete = $profileCompleteness['is_complete'];

        // Get IHT profile
        $ihtProfile = IHTProfile::where('user_id', $user->id)->first();
        if (! $ihtProfile) {
            $config = config('uk_tax_config.inheritance_tax');
            $ihtProfile = new IHTProfile([
                'user_id' => $user->id,
                'marital_status' => $user->marital_status ?? 'single',
                'available_nrb' => $config['nil_rate_band'],
                'nrb_transferred_from_spouse' => 0,
                'charitable_giving_percent' => 0,
            ]);
        }

        // Gather all assets
        $aggregatedAssets = $this->assetAggregator->gatherUserAssets($user);
        $assets = $this->convertToAssetModels($aggregatedAssets, $user);

        // Calculate current IHT position
        $ihtAnalysis = $this->ihtCalculator->calculateIHTLiability(
            $assets,
            $ihtProfile,
            collect([]), // gifts
            collect([]), // trusts
            0, // liabilities
            null, // will
            $user
        );
        $currentIHTLiability = $ihtAnalysis['iht_liability'];

        // Calculate years until death (life expectancy)
        $yearsUntilDeath = $this->calculateYearsUntilDeath($user);

        // Generate individual strategies
        $giftingPlan = $this->giftingStrategy->generatePersonalizedStrategy(
            $assets,
            $currentIHTLiability,
            $ihtProfile,
            $user,
            $yearsUntilDeath
        );

        $trustPlan = $this->trustStrategy->generatePersonalizedTrustStrategy(
            $assets,
            $currentIHTLiability,
            $ihtProfile,
            $user,
            $yearsUntilDeath
        );

        // Get life policy strategy data (if available)
        $lifePolicyPlan = $this->getLifePolicyStrategy($user, $currentIHTLiability);

        // Generate optimized combined strategy
        $optimizedStrategy = $this->generateOptimizedStrategy(
            $giftingPlan,
            $trustPlan,
            $lifePolicyPlan,
            $currentIHTLiability,
            $ihtProfile
        );

        // Build comprehensive plan
        return [
            'plan_metadata' => [
                'generated_date' => now()->format('d F Y'),
                'generated_time' => now()->format('H:i'),
                'plan_version' => 'v1.0',
                'user_name' => $user->name,
                'completeness_score' => $completenessScore,
                'is_complete' => $isComplete,
                'plan_type' => $isComplete ? 'Personalized' : 'Generic',
            ],
            'completeness_warning' => $this->generateCompletenessWarning($profileCompleteness),
            'executive_summary' => $this->generateExecutiveSummary(
                $user,
                $ihtAnalysis,
                $optimizedStrategy,
                $profileCompleteness
            ),
            'user_profile' => $this->buildUserProfile($user),
            'balance_sheet' => $this->buildBalanceSheet($user, $assets, $ihtAnalysis),
            'estate_overview' => $this->buildEstateOverview($aggregatedAssets, $ihtAnalysis),
            'current_iht_position' => $this->buildIHTPosition($ihtAnalysis, $ihtProfile),
            'gifting_strategy' => $giftingPlan,
            'trust_strategy' => $trustPlan,
            'life_policy_strategy' => $lifePolicyPlan,
            'optimized_recommendation' => $optimizedStrategy,
            'implementation_timeline' => $this->buildImplementationTimeline($optimizedStrategy),
            'next_steps' => $this->generateNextSteps($optimizedStrategy, $profileCompleteness),
        ];
    }

    /**
     * Convert aggregated assets to Asset models
     */
    private function convertToAssetModels(Collection $aggregatedAssets, User $user): Collection
    {
        return $aggregatedAssets->map(function ($asset) use ($user) {
            return new \App\Models\Estate\Asset([
                'user_id' => $user->id,
                'asset_type' => $asset->asset_type,
                'asset_name' => $asset->asset_name,
                'current_value' => $asset->current_value,
                'is_iht_exempt' => $asset->is_iht_exempt ?? false,
            ]);
        });
    }

    /**
     * Calculate years until death based on life expectancy
     */
    private function calculateYearsUntilDeath(User $user): int
    {
        if (! $user->date_of_birth) {
            return 20; // Default
        }

        $age = $user->age ?? \Carbon\Carbon::parse($user->date_of_birth)->age;

        // UK life expectancy tables (simplified)
        $lifeExpectancy = $user->gender === 'female' ? 83 : 79;

        return max(1, $lifeExpectancy - $age);
    }

    /**
     * Get life policy strategy (simplified - would call actual service)
     */
    private function getLifePolicyStrategy(User $user, float $ihtLiability): ?array
    {
        if ($ihtLiability <= 0) {
            return null;
        }

        // Simplified life policy recommendation
        return [
            'recommended_approach' => 'Whole of Life Policy',
            'sum_assured_required' => $ihtLiability,
            'estimated_monthly_premium' => $this->estimateLifePremium($user, $ihtLiability),
            'policy_type' => 'Whole of Life',
            'written_in_trust' => true,
            'benefits' => [
                'Guaranteed payout to cover IHT liability',
                'Written in trust - proceeds outside estate',
                'No investment risk',
                'Peace of mind for beneficiaries',
            ],
        ];
    }

    /**
     * Estimate monthly life insurance premium (simplified)
     */
    private function estimateLifePremium(User $user, float $sumAssured): float
    {
        $age = $user->age ?? 55;
        $monthlyRatePer1000 = 0.50 + ($age - 40) * 0.05; // Simplified

        return ($sumAssured / 1000) * $monthlyRatePer1000;
    }

    /**
     * Build user profile section
     */
    private function buildUserProfile(User $user): array
    {
        $spouse = null;
        if ($user->spouse_id) {
            $spouse = FamilyMember::find($user->spouse_id);
        }

        return [
            'name' => $user->name,
            'email' => $user->email,
            'date_of_birth' => $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('d/m/Y') : 'Not provided',
            'age' => $user->age ?? 'Not calculated',
            'gender' => ucfirst($user->gender ?? 'Not specified'),
            'marital_status' => ucfirst(str_replace('_', ' ', $user->marital_status ?? 'single')),
            'spouse' => $spouse ? [
                'name' => $spouse->name,
                'relationship' => 'Spouse',
            ] : null,
        ];
    }

    /**
     * Build estate overview with detailed asset breakdown
     */
    private function buildEstateOverview(Collection $assets, array $ihtAnalysis): array
    {
        $assetsByType = $assets->groupBy('asset_type');

        $breakdown = [];
        $detailedAssets = [];

        foreach ($assetsByType as $type => $typeAssets) {
            $breakdown[] = [
                'type' => ucfirst($type),
                'count' => $typeAssets->count(),
                'value' => $typeAssets->sum('current_value'),
            ];

            // Add detailed list of assets by type
            $detailedAssets[$type] = $typeAssets->map(function ($asset) {
                return [
                    'name' => $asset->asset_name,
                    'value' => $asset->current_value,
                    'is_iht_exempt' => $asset->is_iht_exempt ?? false,
                ];
            })->toArray();
        }

        return [
            'total_assets' => $assets->sum('current_value'),
            'total_liabilities' => $ihtAnalysis['liabilities'] ?? 0,
            'net_estate' => $ihtAnalysis['net_estate_value'] ?? 0,
            'asset_count' => $assets->count(),
            'breakdown' => $breakdown,
            'detailed_assets' => $detailedAssets,
        ];
    }

    /**
     * Build balance sheet from user profile
     */
    private function buildBalanceSheet(User $user, Collection $assets, array $ihtAnalysis): array
    {
        // Group assets by type for balance sheet presentation
        $assetsByType = $assets->groupBy('asset_type');

        $balanceSheetAssets = [];

        // Property assets
        if ($assetsByType->has('property')) {
            $balanceSheetAssets['Property'] = [
                'items' => $assetsByType['property']->map(fn ($a) => [
                    'name' => $a->asset_name,
                    'value' => $a->current_value,
                ])->toArray(),
                'total' => $assetsByType['property']->sum('current_value'),
            ];
        }

        // Investment assets
        if ($assetsByType->has('investment')) {
            $balanceSheetAssets['Investments'] = [
                'items' => $assetsByType['investment']->map(fn ($a) => [
                    'name' => $a->asset_name,
                    'value' => $a->current_value,
                ])->toArray(),
                'total' => $assetsByType['investment']->sum('current_value'),
            ];
        }

        // Cash/Savings assets
        if ($assetsByType->has('cash')) {
            $balanceSheetAssets['Cash & Savings'] = [
                'items' => $assetsByType['cash']->map(fn ($a) => [
                    'name' => $a->asset_name,
                    'value' => $a->current_value,
                ])->toArray(),
                'total' => $assetsByType['cash']->sum('current_value'),
            ];
        }

        // Pension assets
        if ($assetsByType->has('pension')) {
            $balanceSheetAssets['Pensions'] = [
                'items' => $assetsByType['pension']->map(fn ($a) => [
                    'name' => $a->asset_name,
                    'value' => $a->current_value,
                ])->toArray(),
                'total' => $assetsByType['pension']->sum('current_value'),
            ];
        }

        // Business interests
        if ($assetsByType->has('business')) {
            $balanceSheetAssets['Business Interests'] = [
                'items' => $assetsByType['business']->map(fn ($a) => [
                    'name' => $a->asset_name,
                    'value' => $a->current_value,
                ])->toArray(),
                'total' => $assetsByType['business']->sum('current_value'),
            ];
        }

        // Other assets (chattels, etc.)
        $otherTypes = $assetsByType->keys()->diff(['property', 'investment', 'cash', 'pension', 'business']);
        if ($otherTypes->isNotEmpty()) {
            $otherAssets = $assets->whereIn('asset_type', $otherTypes->toArray());
            $balanceSheetAssets['Other Assets'] = [
                'items' => $otherAssets->map(fn ($a) => [
                    'name' => $a->asset_name,
                    'value' => $a->current_value,
                ])->toArray(),
                'total' => $otherAssets->sum('current_value'),
            ];
        }

        $totalAssets = $assets->sum('current_value');
        $totalLiabilities = $ihtAnalysis['liabilities'] ?? 0;
        $netWorth = $totalAssets - $totalLiabilities;

        return [
            'as_at_date' => now()->format('d F Y'),
            'assets' => $balanceSheetAssets,
            'total_assets' => $totalAssets,
            'liabilities' => [
                'total' => $totalLiabilities,
                'breakdown' => [], // Could be expanded if liability details are available
            ],
            'net_worth' => $netWorth,
            'monthly_income' => $user->net_monthly_income ?? 0,
            'monthly_expenditure' => $user->monthly_expenditure ?? 0,
            'annual_income' => $user->gross_annual_income ?? 0,
        ];
    }

    /**
     * Build IHT position
     */
    private function buildIHTPosition(array $ihtAnalysis, IHTProfile $profile): array
    {
        return [
            'gross_estate' => $ihtAnalysis['net_estate_value'] ?? 0,
            'available_nrb' => $profile->available_nrb ?? 325000,
            'rnrb' => $ihtAnalysis['rnrb'] ?? 0,
            'total_allowances' => $ihtAnalysis['total_allowance'] ?? 325000,
            'taxable_estate' => $ihtAnalysis['taxable_estate'] ?? 0,
            'iht_liability' => $ihtAnalysis['iht_liability'] ?? 0,
            'effective_rate' => $ihtAnalysis['net_estate_value'] > 0
                ? ($ihtAnalysis['iht_liability'] / $ihtAnalysis['net_estate_value']) * 100
                : 0,
        ];
    }

    /**
     * Generate optimized combined strategy
     */
    private function generateOptimizedStrategy(
        array $giftingPlan,
        array $trustPlan,
        ?array $lifePolicyPlan,
        float $currentIHTLiability,
        IHTProfile $profile
    ): array {
        $recommendations = [];
        $totalIHTSaving = 0;
        $totalCosts = 0;

        // Priority 1: Immediate actions (Annual exemption + Trust within NRB)
        $recommendations[] = [
            'priority' => 1,
            'category' => 'Immediate Actions (Year 1)',
            'actions' => [
                [
                    'action' => 'Start using annual gifting exemption',
                    'details' => 'Gift £3,000 per year to beneficiaries using annual exemption',
                    'iht_saving' => 3000 * 0.40,
                    'cost' => 0,
                    'timeframe' => 'Annual',
                ],
                [
                    'action' => 'Establish discretionary trust within NRB',
                    'details' => 'Transfer £'.number_format($profile->available_nrb ?? 325000, 0).' to discretionary trust',
                    'iht_saving' => ($profile->available_nrb ?? 325000) * 0.40,
                    'cost' => 0,
                    'timeframe' => 'Once-off (Year 1)',
                ],
            ],
        ];

        $totalIHTSaving += 1200 + (($profile->available_nrb ?? 325000) * 0.40);

        // Priority 2: Medium-term strategy (PET cycles)
        if ($giftingPlan['summary']['total_gifted'] > 0) {
            $recommendations[] = [
                'priority' => 2,
                'category' => 'Medium-term Gifting (Years 1-7)',
                'actions' => [
                    [
                        'action' => 'Implement PET gifting cycles',
                        'details' => 'Gift liquid assets totaling £'.number_format($giftingPlan['summary']['total_gifted'], 0).' over 7 years',
                        'iht_saving' => $giftingPlan['summary']['total_iht_saved'],
                        'cost' => 0,
                        'timeframe' => '7 years',
                    ],
                ],
            ];
            $totalIHTSaving += $giftingPlan['summary']['total_iht_saved'];
        }

        // Priority 3: Life insurance for remaining liability
        if ($lifePolicyPlan) {
            $remainingLiability = max(0, $currentIHTLiability - $totalIHTSaving);
            if ($remainingLiability > 10000) {
                $recommendations[] = [
                    'priority' => 3,
                    'category' => 'Life Insurance Protection',
                    'actions' => [
                        [
                            'action' => 'Establish Whole of Life policy in trust',
                            'details' => 'Sum assured: £'.number_format($remainingLiability, 0).' | Premium: £'.number_format($lifePolicyPlan['estimated_monthly_premium'], 2).'/month',
                            'iht_saving' => 0, // Doesn't reduce IHT, but covers the cost
                            'cost' => $lifePolicyPlan['estimated_monthly_premium'] * 12,
                            'timeframe' => 'Ongoing',
                        ],
                    ],
                ];
                $totalCosts += $lifePolicyPlan['estimated_monthly_premium'] * 12;
            }
        }

        // Priority 4: Property planning (if applicable)
        $propertyStrategy = collect($trustPlan['strategies'])->firstWhere('strategy_name', 'Property Trust Planning');
        if ($propertyStrategy && isset($propertyStrategy['applicable']) && $propertyStrategy['applicable']) {
            $recommendations[] = [
                'priority' => 4,
                'category' => 'Long-term Property Planning',
                'actions' => [
                    [
                        'action' => 'Plan for downsizing main residence',
                        'details' => 'When dependants leave home, consider downsizing to release equity for gifting',
                        'iht_saving' => 'Variable',
                        'cost' => 0,
                        'timeframe' => 'Future (when appropriate)',
                    ],
                ],
            ];
        }

        return [
            'strategy_name' => 'Optimized Combined Estate Plan',
            'recommendations' => $recommendations,
            'summary' => [
                'current_iht_liability' => $currentIHTLiability,
                'total_iht_saving' => $totalIHTSaving,
                'remaining_liability' => max(0, $currentIHTLiability - $totalIHTSaving),
                'annual_costs' => $totalCosts,
                'net_benefit' => $totalIHTSaving - $totalCosts,
                'effectiveness_percentage' => $currentIHTLiability > 0 ? ($totalIHTSaving / $currentIHTLiability) * 100 : 0,
            ],
        ];
    }

    /**
     * Generate completeness warning
     */
    private function generateCompletenessWarning(?array $profileCompleteness): ?array
    {
        if (! $profileCompleteness || $profileCompleteness['is_complete']) {
            return null;
        }

        $score = $profileCompleteness['completeness_score'];
        $missingFields = $profileCompleteness['missing_fields'] ?? [];

        // Determine severity
        $severity = match (true) {
            $score < 50 => 'critical',
            $score < 100 => 'warning',
            default => 'success',
        };

        // Build disclaimer text
        $disclaimer = match ($severity) {
            'critical' => 'This estate plan is highly generic due to incomplete profile information. Key data is missing, which significantly limits the accuracy of IHT calculations and personalization of recommendations. Please complete your profile to receive a comprehensive and tailored estate strategy.',
            'warning' => 'This estate plan is partially generic as some profile information is incomplete. Completing the missing fields will enable more accurate IHT calculations and personalized recommendations.',
            default => 'Your profile is complete. This estate plan is fully personalized based on your circumstances.',
        };

        // Extract top priority missing fields
        $topMissingFields = [];
        foreach ($missingFields as $key => $field) {
            if ($field['priority'] === 'high' && $field['required']) {
                $topMissingFields[] = [
                    'field' => $key,
                    'message' => $field['message'],
                    'link' => $field['link'],
                ];
            }
        }

        return [
            'score' => $score,
            'severity' => $severity,
            'disclaimer' => $disclaimer,
            'missing_fields' => $topMissingFields,
            'recommendations' => $profileCompleteness['recommendations'] ?? [],
        ];
    }

    /**
     * Generate executive summary
     */
    private function generateExecutiveSummary(User $user, array $ihtAnalysis, array $optimizedStrategy, ?array $profileCompleteness): array
    {
        return [
            'title' => 'Estate Planning Report for '.$user->name,
            'current_position' => [
                'net_estate' => $ihtAnalysis['net_estate_value'] ?? 0,
                'iht_liability' => $ihtAnalysis['iht_liability'] ?? 0,
            ],
            'recommended_strategy' => $optimizedStrategy['strategy_name'],
            'potential_saving' => $optimizedStrategy['summary']['total_iht_saving'],
            'annual_cost' => $optimizedStrategy['summary']['annual_costs'],
            'key_actions' => count($optimizedStrategy['recommendations']),
        ];
    }

    /**
     * Build implementation timeline
     */
    private function buildImplementationTimeline(array $optimizedStrategy): array
    {
        $timeline = [];

        foreach ($optimizedStrategy['recommendations'] as $rec) {
            foreach ($rec['actions'] as $action) {
                $timeline[] = [
                    'priority' => $rec['priority'],
                    'category' => $rec['category'],
                    'action' => $action['action'],
                    'timeframe' => $action['timeframe'],
                    'iht_saving' => is_numeric($action['iht_saving']) ? $action['iht_saving'] : 0,
                ];
            }
        }

        return $timeline;
    }

    /**
     * Generate next steps
     */
    private function generateNextSteps(array $optimizedStrategy, ?array $profileCompleteness): array
    {
        $steps = [
            'Immediate (Within 1 month)' => [],
            'Short-term (1-6 months)' => [],
            'Medium-term (6-12 months)' => [],
            'Long-term (12+ months)' => [],
        ];

        // Add profile completeness steps if incomplete
        if ($profileCompleteness && ! $profileCompleteness['is_complete']) {
            $completenessScore = $profileCompleteness['completeness_score'];

            if ($completenessScore < 70) {
                $steps['Immediate (Within 1 month)'][] = '⚠️ PRIORITY: Complete your profile information for accurate estate planning';

                // Add specific missing fields
                $missingFields = $profileCompleteness['missing_fields'] ?? [];
                foreach ($missingFields as $key => $field) {
                    if ($field['priority'] === 'high' && $field['required']) {
                        $steps['Immediate (Within 1 month)'][] = '  → '.$field['message'];
                    }
                }
            }
        }

        // Categorize actions by timeframe
        foreach ($optimizedStrategy['recommendations'] as $rec) {
            if ($rec['priority'] === 1) {
                $steps['Immediate (Within 1 month)'][] = $rec['category'];
            } elseif ($rec['priority'] === 2) {
                $steps['Short-term (1-6 months)'][] = $rec['category'];
            } elseif ($rec['priority'] === 3) {
                $steps['Medium-term (6-12 months)'][] = $rec['category'];
            } else {
                $steps['Long-term (12+ months)'][] = $rec['category'];
            }
        }

        return $steps;
    }
}
