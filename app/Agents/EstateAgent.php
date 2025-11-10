<?php

declare(strict_types=1);

namespace App\Agents;

use App\Models\Estate\Asset;
use App\Models\Estate\Gift;
use App\Models\Estate\IHTProfile;
use App\Models\Estate\Liability;
use App\Models\User;
use App\Services\Estate\CashFlowProjector;
use App\Services\Estate\GiftingStrategy;
use App\Services\Estate\IHTCalculator;
use App\Services\Estate\NetWorthAnalyzer;
use App\Services\TaxConfigService;
use App\Services\UserProfile\ProfileCompletenessChecker;

class EstateAgent extends BaseAgent
{
    public function __construct(
        private IHTCalculator $ihtCalculator,
        private GiftingStrategy $giftingStrategy,
        private NetWorthAnalyzer $netWorthAnalyzer,
        private CashFlowProjector $cashFlowProjector,
        private ProfileCompletenessChecker $completenessChecker,
        private TaxConfigService $taxConfig
    ) {}

    /**
     * Analyze estate planning situation
     */
    public function analyze(int $userId): array
    {
        return $this->remember("estate_analysis_{$userId}", function () use ($userId) {
            // Get user data
            $assets = Asset::where('user_id', $userId)->get();
            $liabilities = Liability::where('user_id', $userId)->get();
            $gifts = Gift::where('user_id', $userId)->get();
            $ihtProfile = IHTProfile::where('user_id', $userId)->first();

            // If no IHT profile exists, create a default one
            if (! $ihtProfile) {
                $ihtProfile = new IHTProfile([
                    'marital_status' => 'single',
                    'has_spouse' => false,
                    'own_home' => false,
                    'home_value' => 0,
                    'nrb_transferred_from_spouse' => 0,
                    'charitable_giving_percent' => 0,
                ]);
            }

            // Calculate net worth
            $netWorthAnalysis = $this->netWorthAnalyzer->calculateNetWorth($userId);

            // Calculate IHT liability
            $ihtLiability = $this->ihtCalculator->calculateIHTLiability($assets, $ihtProfile);

            // Analyze gifts
            $petAnalysis = $this->giftingStrategy->analyzePETs($gifts);

            // Calculate PET liability
            $petLiability = $this->ihtCalculator->calculatePETLiability($gifts);

            // Get concentration risk
            $concentrationRisk = $this->netWorthAnalyzer->identifyConcentrationRisk($assets);

            // Get cash flow for current tax year
            $currentTaxYear = (int) date('Y');
            $cashFlow = $this->cashFlowProjector->createPersonalPL($userId, (string) $currentTaxYear);

            // Calculate discretionary income
            $discretionaryIncome = $this->cashFlowProjector->calculateDiscretionaryIncome($userId, (string) $currentTaxYear);

            // Calculate probate readiness score
            $probateReadinessScore = $this->calculateProbateReadinessScore([
                'has_iht_profile' => $ihtProfile->exists,
                'has_assets' => $assets->isNotEmpty(),
                'has_will_info' => $ihtProfile->exists && $ihtProfile->own_home,
                'iht_liability' => $ihtLiability['iht_liability'],
            ]);

            // Check profile completeness
            $user = User::findOrFail($userId);
            $profileCompleteness = $this->completenessChecker->checkCompleteness($user);

            return [
                'net_worth' => $netWorthAnalysis,
                'iht_liability' => $ihtLiability,
                'pet_analysis' => $petAnalysis,
                'pet_liability' => $petLiability,
                'concentration_risk' => $concentrationRisk,
                'cash_flow' => $cashFlow,
                'discretionary_income' => $discretionaryIncome,
                'probate_readiness_score' => $probateReadinessScore,
                'summary' => $this->generateSummary($netWorthAnalysis, $ihtLiability, $probateReadinessScore),
                'profile_completeness' => $profileCompleteness,
            ];
        });
    }

    /**
     * Generate estate planning recommendations
     */
    public function generateRecommendations(array $analysisData): array
    {
        $recommendations = [];

        // IHT recommendations
        if ($analysisData['iht_liability']['iht_liability'] > 0) {
            $recommendations[] = [
                'category' => 'Inheritance Tax',
                'priority' => 'High',
                'title' => 'IHT Liability Identified',
                'description' => "Your estate has a potential IHT liability of {$this->formatCurrency($analysisData['iht_liability']['iht_liability'])}",
                'action' => 'Consider gifting strategies to reduce IHT exposure',
                'impact' => "Potential saving: {$this->formatCurrency($analysisData['iht_liability']['iht_liability'])}",
            ];

            // Add specific gifting recommendations
            $giftingRec = $this->giftingStrategy->recommendOptimalGiftingStrategy(
                $analysisData['net_worth']['total_assets'],
                IHTProfile::where('user_id', $analysisData['net_worth']['statement_date'])->first() ?? new IHTProfile
            );

            foreach ($giftingRec['recommendations'] as $rec) {
                $recommendations[] = [
                    'category' => 'Gifting Strategy',
                    'priority' => 'Medium',
                    'title' => $rec['strategy'],
                    'description' => $rec['description'],
                    'action' => $rec['benefit'],
                    'impact' => "Potential saving: {$this->formatCurrency($rec['potential_saving'] ?? 0)}",
                ];
            }
        }

        // RNRB recommendations
        if (! $analysisData['iht_liability']['rnrb_eligible']) {
            $recommendations[] = [
                'category' => 'Residence Nil Rate Band',
                'priority' => 'Medium',
                'title' => 'RNRB Not Utilized',
                'description' => 'You may not be utilizing the Residence Nil Rate Band of £175,000',
                'action' => 'Ensure your main residence passes to direct descendants in your will',
                'impact' => 'Potential saving: £70,000 (40% of £175,000)',
            ];
        }

        // Net worth recommendations
        if ($analysisData['net_worth']['debt_to_asset_ratio'] > 0.5) {
            $recommendations[] = [
                'category' => 'Debt Management',
                'priority' => 'High',
                'title' => 'High Debt-to-Asset Ratio',
                'description' => "Your debt-to-asset ratio is {$this->formatPercentage($analysisData['net_worth']['debt_to_asset_ratio'] * 100)}",
                'action' => 'Focus on debt reduction to improve estate value',
                'impact' => 'Improves net worth and reduces estate complexity',
            ];
        }

        // Concentration risk recommendations
        if ($analysisData['concentration_risk']['has_concentration_risk']) {
            foreach ($analysisData['concentration_risk']['risks'] as $risk) {
                if ($risk['severity'] === 'High') {
                    $recommendations[] = [
                        'category' => 'Asset Diversification',
                        'priority' => 'Medium',
                        'title' => $risk['type'],
                        'description' => $risk['recommendation'],
                        'action' => 'Diversify holdings to reduce concentration risk',
                        'impact' => 'Improves estate resilience and risk management',
                    ];
                }
            }
        }

        // Charitable giving recommendation
        $ihtProfile = IHTProfile::first(); // In production, get by user_id
        if ($ihtProfile && $ihtProfile->charitable_giving_percent < 10 && $analysisData['iht_liability']['iht_liability'] > 0) {
            $savingFrom36Rate = ($analysisData['iht_liability']['taxable_estate'] * 0.04); // 4% rate reduction

            $recommendations[] = [
                'category' => 'Charitable Giving',
                'priority' => 'Low',
                'title' => 'Consider Charitable Giving',
                'description' => 'Leaving 10% of your estate to charity reduces IHT rate from 40% to 36%',
                'action' => 'Include charitable bequest in your will',
                'impact' => "Potential saving: {$this->formatCurrency($savingFrom36Rate)}",
            ];
        }

        // Sort by priority
        $priorityOrder = ['High' => 1, 'Medium' => 2, 'Low' => 3];
        usort($recommendations, fn ($a, $b) => $priorityOrder[$a['priority']] <=> $priorityOrder[$b['priority']]);

        return [
            'recommendation_count' => count($recommendations),
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Build estate planning scenarios
     */
    public function buildScenarios(int $userId, array $parameters): array
    {
        $assets = Asset::where('user_id', $userId)->get();
        $ihtProfile = IHTProfile::where('user_id', $userId)->first() ?? new IHTProfile;

        $scenarios = [];

        // Scenario 1: Current situation (baseline)
        $baselineIHT = $this->ihtCalculator->calculateIHTLiability($assets, $ihtProfile);
        $scenarios[] = [
            'name' => 'Current Situation (Baseline)',
            'description' => 'Your current estate planning position',
            'estate_value' => $baselineIHT['gross_estate_value'],
            'iht_liability' => $baselineIHT['iht_liability'],
            'net_estate' => $baselineIHT['gross_estate_value'] - $baselineIHT['iht_liability'],
        ];

        // Scenario 2: With annual gifting
        if (isset($parameters['annual_gifting_years'])) {
            $years = $parameters['annual_gifting_years'];
            $giftingConfig = $this->taxConfig->getGiftingExemptions();
            $annualGift = $giftingConfig['annual_exemption'] ?? 3000;
            $totalGifted = $annualGift * $years;

            $reducedEstateValue = max(0, $assets->sum('current_value') - $totalGifted);
            $reducedAssets = $assets->map(function ($asset) use ($totalGifted, $assets) {
                $proportion = $assets->sum('current_value') > 0 ? $asset->current_value / $assets->sum('current_value') : 0;
                $asset->current_value = max(0, $asset->current_value - ($totalGifted * $proportion));

                return $asset;
            });

            $scenarioIHT = $this->ihtCalculator->calculateIHTLiability($reducedAssets, $ihtProfile);

            $scenarios[] = [
                'name' => "Annual Gifting ({$years} years)",
                'description' => "Gift £{$annualGift} per year for {$years} years",
                'estate_value' => $scenarioIHT['gross_estate_value'],
                'iht_liability' => $scenarioIHT['iht_liability'],
                'net_estate' => $scenarioIHT['gross_estate_value'] - $scenarioIHT['iht_liability'],
                'saving_vs_baseline' => $baselineIHT['iht_liability'] - $scenarioIHT['iht_liability'],
            ];
        }

        // Scenario 3: With charitable giving
        if (isset($parameters['charitable_percent']) && $parameters['charitable_percent'] >= 10) {
            $charitableProfile = clone $ihtProfile;
            $charitableProfile->charitable_giving_percent = $parameters['charitable_percent'];

            $scenarioIHT = $this->ihtCalculator->calculateIHTLiability($assets, $charitableProfile);

            $scenarios[] = [
                'name' => "Charitable Giving ({$parameters['charitable_percent']}%)",
                'description' => "Leave {$parameters['charitable_percent']}% of estate to charity (reduces rate to 36%)",
                'estate_value' => $scenarioIHT['gross_estate_value'],
                'iht_liability' => $scenarioIHT['iht_liability'],
                'net_estate' => $scenarioIHT['gross_estate_value'] - $scenarioIHT['iht_liability'],
                'saving_vs_baseline' => $baselineIHT['iht_liability'] - $scenarioIHT['iht_liability'],
            ];
        }

        // Scenario 4: With spouse NRB transfer
        if (isset($parameters['spouse_nrb_transfer']) && $parameters['spouse_nrb_transfer'] > 0) {
            $spouseProfile = clone $ihtProfile;
            $spouseProfile->nrb_transferred_from_spouse = $parameters['spouse_nrb_transfer'];

            $scenarioIHT = $this->ihtCalculator->calculateIHTLiability($assets, $spouseProfile);

            $scenarios[] = [
                'name' => 'With Spouse NRB Transfer',
                'description' => "Transfer unused NRB of £{$parameters['spouse_nrb_transfer']} from deceased spouse",
                'estate_value' => $scenarioIHT['gross_estate_value'],
                'iht_liability' => $scenarioIHT['iht_liability'],
                'net_estate' => $scenarioIHT['gross_estate_value'] - $scenarioIHT['iht_liability'],
                'saving_vs_baseline' => $baselineIHT['iht_liability'] - $scenarioIHT['iht_liability'],
            ];
        }

        return [
            'scenario_count' => count($scenarios),
            'scenarios' => $scenarios,
            'best_scenario' => $this->identifyBestScenario($scenarios),
        ];
    }

    /**
     * Generate summary of estate planning position
     */
    private function generateSummary(array $netWorth, array $ihtLiability, array $probateScore): array
    {
        return [
            'net_worth' => $netWorth['net_worth'],
            'iht_liability' => $ihtLiability['iht_liability'],
            'iht_rate' => $ihtLiability['iht_rate'],
            'probate_readiness_score' => $probateScore['score'],
            'probate_readiness_grade' => $probateScore['grade'],
            'key_metrics' => [
                'estate_value' => $ihtLiability['gross_estate_value'],
                'net_estate_after_iht' => $ihtLiability['gross_estate_value'] - $ihtLiability['iht_liability'],
                'effective_iht_rate' => $ihtLiability['effective_rate'],
            ],
        ];
    }

    /**
     * Calculate probate readiness score
     */
    private function calculateProbateReadinessScore(array $factors): array
    {
        $score = 0;
        $maxScore = 100;
        $issues = [];

        // Has IHT profile (20 points)
        if ($factors['has_iht_profile']) {
            $score += 20;
        } else {
            $issues[] = 'IHT profile not set up';
        }

        // Has documented assets (30 points)
        if ($factors['has_assets']) {
            $score += 30;
        } else {
            $issues[] = 'No assets documented';
        }

        // Has will information (30 points)
        if ($factors['has_will_info']) {
            $score += 30;
        } else {
            $issues[] = 'Will information incomplete';
        }

        // IHT planning done (20 points)
        if ($factors['iht_liability'] == 0) {
            $score += 20;
        } elseif ($factors['iht_liability'] < 50000) {
            $score += 10;
        } else {
            $issues[] = 'Significant IHT liability not addressed';
        }

        $grade = match (true) {
            $score >= 80 => 'Excellent',
            $score >= 60 => 'Good',
            $score >= 40 => 'Fair',
            default => 'Needs Attention',
        };

        return [
            'score' => $score,
            'max_score' => $maxScore,
            'grade' => $grade,
            'issues' => $issues,
        ];
    }

    /**
     * Identify the best scenario from comparison
     */
    private function identifyBestScenario(array $scenarios): array
    {
        $best = null;
        $maxNetEstate = 0;

        foreach ($scenarios as $scenario) {
            if ($scenario['net_estate'] > $maxNetEstate) {
                $maxNetEstate = $scenario['net_estate'];
                $best = $scenario;
            }
        }

        return $best ?? [];
    }
}
