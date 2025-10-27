<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Estate;

use App\Http\Controllers\Controller;
use App\Models\Estate\Asset;
use App\Models\Estate\Gift;
use App\Models\Estate\IHTProfile;
use App\Models\Estate\Liability;
use App\Models\Estate\Trust;
use App\Models\Estate\Will;
use App\Models\Investment\InvestmentAccount;
use App\Models\Mortgage;
use App\Models\User;
use App\Services\Estate\EstateAssetAggregatorService;
use App\Services\Estate\GiftingStrategyOptimizer;
use App\Services\Estate\GiftingTimelineService;
use App\Services\Estate\IHTCalculator;
use App\Services\Estate\IHTStrategyGeneratorService;
use App\Services\Estate\LifeCoverCalculator;
use App\Services\Estate\NetWorthAnalyzer;
use App\Services\Estate\SecondDeathIHTCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class IHTController extends Controller
{
    public function __construct(
        private IHTCalculator $ihtCalculator,
        private NetWorthAnalyzer $netWorthAnalyzer,
        private \App\Services\Estate\ActuarialLifeTableService $actuarialService,
        private \App\Services\Estate\SpouseNRBTrackerService $nrbTracker,
        private \App\Services\Estate\FutureValueCalculator $fvCalculator,
        private SecondDeathIHTCalculator $secondDeathCalculator,
        private EstateAssetAggregatorService $assetAggregator,
        private GiftingStrategyOptimizer $giftingOptimizer,
        private LifeCoverCalculator $lifeCoverCalculator,
        private IHTStrategyGeneratorService $strategyGenerator,
        private GiftingTimelineService $giftingTimeline
    ) {}

    public function calculateIHT(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            // Gather all user assets from all modules using the aggregator service
            $allAssets = $this->assetAggregator->gatherUserAssets($user);

            // Calculate total liabilities using the aggregator service
            $totalLiabilities = $this->assetAggregator->calculateUserLiabilities($user);
            $mortgages = $this->assetAggregator->getUserMortgages($user);
            $liabilities = $this->assetAggregator->getUserLiabilities($user);

            // Debug logging
            \Log::info('IHT Calculation Debug:', [
                'total_assets_count' => $allAssets->count(),
                'total_assets_value' => $allAssets->sum('current_value'),
                'mortgages_count' => $mortgages->count(),
                'mortgages_total' => $mortgages->sum('outstanding_balance'),
                'liabilities_count' => $liabilities->count(),
                'liabilities_total' => $liabilities->sum('current_balance'),
                'total_liabilities' => $totalLiabilities,
            ]);

            $gifts = Gift::where('user_id', $user->id)->get();
            $trusts = Trust::where('user_id', $user->id)->where('is_active', true)->get();
            $ihtProfile = IHTProfile::where('user_id', $user->id)->first();

            // Create default profile if it doesn't exist
            if (! $ihtProfile) {
                // For married users, default to full spouse NRB (£325,000)
                // This will be verified once spouse accounts are linked
                $isMarried = in_array($user->marital_status, ['married']);
                $config = config('uk_tax_config.inheritance_tax');
                $defaultSpouseNRB = $isMarried ? $config['nil_rate_band'] : 0;

                $ihtProfile = new IHTProfile([
                    'user_id' => $user->id,
                    'marital_status' => $user->marital_status ?? 'single',
                    'own_home' => false,
                    'home_value' => 0,
                    'nrb_transferred_from_spouse' => $defaultSpouseNRB,
                    'charitable_giving_percent' => 0,
                ]);
            }

            // Get or create default Will
            $will = Will::where('user_id', $user->id)->first();
            if (! $will) {
                // Create default will
                $isMarried = in_array($user->marital_status, ['married']) && $user->spouse_id !== null;
                $will = new Will([
                    'user_id' => $user->id,
                    'death_scenario' => 'user_only',
                    'spouse_primary_beneficiary' => $isMarried,
                    'spouse_bequest_percentage' => $isMarried ? 100.00 : 0.00,
                ]);
            }

            $ihtLiability = $this->ihtCalculator->calculateIHTLiability($allAssets, $ihtProfile, $gifts, $trusts, $totalLiabilities, $will, $user);

            // Calculate actuarial projection (Current vs Death at Expected Age)
            $lifeExpectancy = $this->fvCalculator->getLifeExpectancy($user);
            $yearsToProject = (int) $lifeExpectancy['years_remaining'];
            $growthRate = config('uk_life_expectancy.default_growth_rates.assets', 0.045);

            // Current values
            $currentAssets = $allAssets->sum('current_value');
            $currentMortgages = $mortgages->sum('outstanding_balance');
            $currentLiabilities = $liabilities->sum('current_balance');

            // Projected values at death
            $projectedAssets = $this->fvCalculator->calculateFutureValue($currentAssets, $growthRate, $yearsToProject);

            // Project mortgages (handle maturity and type)
            $projectedMortgages = 0;
            foreach ($mortgages as $mortgage) {
                $projectedMortgages += $this->fvCalculator->projectMortgageBalance(
                    (float) $mortgage->outstanding_balance,
                    $mortgage->mortgage_type ?? 'repayment',
                    (int) ($mortgage->remaining_term_months ?? 0),
                    (float) ($mortgage->interest_rate ?? 0),
                    (float) ($mortgage->monthly_payment ?? 0),
                    $yearsToProject
                );
            }

            // Other liabilities stay constant (conservative assumption)
            $projectedLiabilities = $currentLiabilities;

            // Calculate projected IHT at death
            $projectedNetEstate = $projectedAssets - $projectedMortgages - $projectedLiabilities;
            $projectedIHTLiability = $this->calculateProjectedIHT($projectedNetEstate, $ihtProfile);

            // Build projection comparison
            $projection = [
                'life_expectancy' => $lifeExpectancy,
                'growth_rate_used' => $growthRate,
                'current' => [
                    'assets' => round($currentAssets, 2),
                    'mortgages' => round($currentMortgages, 2),
                    'other_liabilities' => round($currentLiabilities, 2),
                    'net_estate' => round($currentAssets - $currentMortgages - $currentLiabilities, 2),
                    'iht_liability' => round($ihtLiability['iht_liability'], 2),
                ],
                'at_death' => [
                    'age' => $lifeExpectancy['death_age'],
                    'year' => $lifeExpectancy['death_year'],
                    'years_from_now' => $yearsToProject,
                    'assets' => round($projectedAssets, 2),
                    'mortgages' => round($projectedMortgages, 2),
                    'other_liabilities' => round($projectedLiabilities, 2),
                    'net_estate' => round($projectedNetEstate, 2),
                    'iht_liability' => round($projectedIHTLiability, 2),
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $ihtLiability,
                'projection' => $projection,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate IHT: '.$e->getMessage(),
            ], 500);
        }
    }
    public function storeOrUpdateIHTProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'marital_status' => 'required|in:single,married,widowed,divorced',
            'has_spouse' => 'boolean',
            'own_home' => 'boolean',
            'home_value' => 'nullable|numeric|min:0',
            'nrb_transferred_from_spouse' => 'nullable|numeric|min:0',
            'charitable_giving_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $validated['user_id'] = $user->id;

            $profile = IHTProfile::updateOrCreate(
                ['user_id' => $user->id],
                $validated
            );

            // Invalidate cache
            Cache::forget("estate_analysis_{$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'IHT profile saved successfully',
                'data' => $profile,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save IHT profile: '.$e->getMessage(),
            ], 500);
        }
    }
    public function calculateSurvivingSpouseIHT(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            // Validate that user is married and has a linked spouse
            if (! in_array($user->marital_status, ['married', 'widowed']) || ! $user->spouse_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'User must be married or widowed with a linked spouse account to use this feature.',
                ], 400);
            }

            // Get spouse
            $spouse = User::find($user->spouse_id);
            if (! $spouse) {
                return response()->json([
                    'success' => false,
                    'message' => 'Spouse account not found.',
                ], 404);
            }

            // Validate user has required data for actuarial calculation
            if (! $user->date_of_birth || ! $user->gender) {
                return response()->json([
                    'success' => false,
                    'message' => 'User must have date of birth and gender set to calculate life expectancy.',
                ], 400);
            }

            // Get all user's assets using the aggregator service
            $allAssets = $this->assetAggregator->gatherUserAssets($user);

            // Calculate total liabilities using the aggregator service
            $totalLiabilities = $this->assetAggregator->calculateUserLiabilities($user);

            // Get gifts and trusts
            $gifts = Gift::where('user_id', $user->id)->get();
            $trusts = Trust::where('user_id', $user->id)->where('is_active', true)->get();

            // Get or create IHT profile
            $ihtProfile = IHTProfile::where('user_id', $user->id)->first();
            if (! $ihtProfile) {
                // For married users, default to full spouse NRB (£325,000)
                $isMarried = in_array($user->marital_status, ['married']);
                $config = config('uk_tax_config.inheritance_tax');
                $defaultSpouseNRB = $isMarried ? $config['nil_rate_band'] : 0;

                $ihtProfile = new IHTProfile([
                    'user_id' => $user->id,
                    'marital_status' => $user->marital_status ?? 'married',
                    'own_home' => false,
                    'home_value' => 0,
                    'nrb_transferred_from_spouse' => $defaultSpouseNRB,
                    'charitable_giving_percent' => 0,
                ]);
            }

            // Get will
            $will = Will::where('user_id', $user->id)->first();

            // Get custom growth rates from request (optional)
            $customGrowthRates = $request->input('growth_rates', null);

            // Calculate surviving spouse IHT
            $survivingSpouseAnalysis = $this->ihtCalculator->calculateSurvivingSpouseIHT(
                survivor: $user,
                deceased: $spouse,
                assets: $allAssets,
                survivorProfile: $ihtProfile,
                gifts: $gifts,
                trusts: $trusts,
                liabilities: $totalLiabilities,
                will: $will,
                actuarialService: $this->actuarialService,
                nrbTracker: $this->nrbTracker,
                fvCalculator: $this->fvCalculator,
                customGrowthRates: $customGrowthRates
            );

            return response()->json([
                'success' => true,
                'data' => $survivingSpouseAnalysis,
            ]);
        } catch (\Exception $e) {
            \Log::error('Surviving Spouse IHT Calculation Error:', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate surviving spouse IHT: '.$e->getMessage(),
            ], 500);
        }
    }
    public function calculateSecondDeathIHTPlanning(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            // 1. Validate user is married and has spouse
            if (! in_array($user->marital_status, ['married'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This feature is only available for married users.',
                    'show_spouse_exemption_notice' => false,
                ], 400);
            }

            // Check if user has spouse linked
            $hasSpouse = $user->spouse_id !== null;

            // If no spouse linked, calculate their IHT with spouse exemption + provide basic strategies
            if (! $hasSpouse) {
                // Calculate standard IHT for this user (will include spouse exemption if applicable)
                $userAssets = $this->assetAggregator->gatherUserAssets($user);
                $userLiabilities = $this->assetAggregator->calculateUserLiabilities($user);
                $userGifts = Gift::where('user_id', $user->id)->get();
                $userTrusts = Trust::where('user_id', $user->id)->where('is_active', true)->get();
                $userProfile = IHTProfile::where('user_id', $user->id)->first();
                $userWill = Will::where('user_id', $user->id)->first();

                if (! $userProfile) {
                    // For married users, default to full spouse NRB (£325,000)
                    $isMarried = in_array($user->marital_status, ['married']);
                    $config = config('uk_tax_config.inheritance_tax');
                    $defaultSpouseNRB = $isMarried ? $config['nil_rate_band'] : 0;

                    $userProfile = new IHTProfile([
                        'user_id' => $user->id,
                        'marital_status' => $user->marital_status ?? 'married',
                        'own_home' => false,
                        'home_value' => 0,
                        'nrb_transferred_from_spouse' => $defaultSpouseNRB,
                        'charitable_giving_percent' => 0,
                    ]);
                }

                $ihtCalculation = $this->ihtCalculator->calculateIHTLiability(
                    $userAssets,
                    $userProfile,
                    $userGifts,
                    $userTrusts,
                    $userLiabilities,
                    $userWill,
                    $user
                );

                // Calculate effective IHT liability for display (potential second death liability)
                $config = config('uk_tax_config.inheritance_tax');
                $totalNRB = $ihtCalculation['total_nrb'] ?? $config['nil_rate_band'];
                $rnrb = $ihtCalculation['rnrb'] ?? 0;
                $totalAllowance = $totalNRB + $rnrb;
                $taxableNetEstate = $ihtCalculation['taxable_net_estate'] ?? 0;
                $potentialTaxableEstate = max(0, $taxableNetEstate - $totalAllowance);
                $effectiveIHTLiability = $potentialTaxableEstate * 0.40;

                // Generate basic mitigation strategies for married user without linked spouse
                // Create default gifting strategy recommendations
                $defaultGiftingStrategy = $this->strategyGenerator->generateDefaultGiftingStrategy($effectiveIHTLiability, $user);

                $mitigationStrategies = $this->strategyGenerator->generateIHTMitigationStrategies(
                    ['iht_calculation' => $ihtCalculation],
                    $defaultGiftingStrategy, // Basic gifting recommendations
                    null, // No life cover calculations yet (need spouse for actuarial data)
                    $userProfile
                );

                // Calculate estate projection (now vs death)
                $projection = null;
                if ($user->date_of_birth) {
                    $lifeExpectancy = $this->fvCalculator->getLifeExpectancy($user);
                    $yearsToProject = (int) $lifeExpectancy['years_remaining'];
                    $growthRate = 0.045; // 4.5% annual growth

                    // Current values
                    $currentAssets = $userAssets->sum('current_value');
                    $mortgages = Mortgage::where('user_id', $user->id)->get();
                    $currentMortgages = $mortgages->sum('outstanding_balance');
                    $currentLiabilities = $userLiabilities;

                    // Project future values
                    $projectedAssets = $this->fvCalculator->calculateFutureValue($currentAssets, $growthRate, $yearsToProject);

                    // Project mortgages (handle maturity and type)
                    $projectedMortgages = 0;
                    foreach ($mortgages as $mortgage) {
                        $projectedMortgages += $this->fvCalculator->projectMortgageBalance(
                            (float) $mortgage->outstanding_balance,
                            $mortgage->mortgage_type ?? 'repayment',
                            (int) ($mortgage->remaining_term_months ?? 0),
                            (float) ($mortgage->interest_rate ?? 0),
                            (float) ($mortgage->monthly_payment ?? 0),
                            $yearsToProject
                        );
                    }

                    // Other liabilities stay constant
                    $projectedLiabilities = $currentLiabilities;

                    // Net estates
                    $currentNetEstate = $currentAssets - $currentMortgages - $currentLiabilities;
                    $projectedNetEstate = $projectedAssets - $projectedMortgages - $projectedLiabilities;

                    // IHT calculations
                    $currentIHT = $ihtCalculation['iht_liability'] ?? 0;

                    // Calculate projected IHT based on projected net estate
                    $totalNRB = $ihtCalculation['total_nrb'] ?? $config['nil_rate_band'];
                    $rnrb = $ihtCalculation['rnrb'] ?? 0;
                    $projectedTaxableEstate = max(0, $projectedNetEstate - $totalNRB - $rnrb);
                    $projectedIHT = $projectedTaxableEstate * 0.40;

                    $projection = [
                        'life_expectancy' => $lifeExpectancy,
                        'growth_rate' => $growthRate,
                        'current' => [
                            'assets' => $currentAssets,
                            'mortgages' => $currentMortgages,
                            'liabilities' => $currentLiabilities,
                            'net_estate' => $currentNetEstate,
                            'iht_liability' => $currentIHT,
                        ],
                        'at_death' => [
                            'assets' => $projectedAssets,
                            'mortgages' => $projectedMortgages,
                            'liabilities' => $projectedLiabilities,
                            'net_estate' => $projectedNetEstate,
                            'iht_liability' => $projectedIHT,
                            'years_from_now' => $yearsToProject,
                        ],
                    ];
                }

                return response()->json([
                    'success' => true,
                    'show_spouse_exemption_notice' => true,
                    'spouse_exemption_message' => 'Transfers to spouse are exempt from IHT with no limit. Link your spouse account to unlock full second death planning features.',
                    'requires_spouse_link' => true,
                    'missing_data' => ['spouse_account'],
                    'user_iht_calculation' => $ihtCalculation,
                    'effective_iht_liability' => $effectiveIHTLiability,
                    'potential_taxable_estate' => $potentialTaxableEstate,
                    'mitigation_strategies' => $mitigationStrategies,
                    'projection' => $projection,
                ]);
            }

            $spouse = \App\Models\User::find($user->spouse_id);
            if (! $spouse) {
                return response()->json([
                    'success' => false,
                    'message' => 'Spouse account not found.',
                ], 404);
            }

            // Check for data sharing permission
            $dataSharingEnabled = $user->hasAcceptedSpousePermission();

            // 2. Gather all user assets
            $userAssets = $this->assetAggregator->gatherUserAssets($user);
            $userLiabilities = $this->assetAggregator->calculateUserLiabilities($user);
            $userGifts = Gift::where('user_id', $user->id)->get();
            $userTrusts = Trust::where('user_id', $user->id)->where('is_active', true)->get();
            $userProfile = IHTProfile::where('user_id', $user->id)->first();
            $userWill = Will::where('user_id', $user->id)->first();

            // Create default profile if missing
            if (! $userProfile) {
                // For married users, default to full spouse NRB (£325,000)
                $isMarried = in_array($user->marital_status, ['married']);
                $config = config('uk_tax_config.inheritance_tax');
                $defaultSpouseNRB = $isMarried ? $config['nil_rate_band'] : 0;

                $userProfile = new IHTProfile([
                    'user_id' => $user->id,
                    'marital_status' => $user->marital_status ?? 'married',
                    'own_home' => false,
                    'home_value' => 0,
                    'nrb_transferred_from_spouse' => $defaultSpouseNRB,
                    'charitable_giving_percent' => 0,
                ]);
            }

            // 3. Gather spouse assets if data sharing enabled
            $spouseAssets = collect();
            $spouseLiabilities = 0;
            $spouseGifts = null;
            $spouseTrusts = null;
            $spouseProfile = null;
            $spouseWill = null;

            if ($dataSharingEnabled) {
                $spouseAssets = $this->assetAggregator->gatherUserAssets($spouse);
                $spouseLiabilities = $this->assetAggregator->calculateUserLiabilities($spouse);
                $spouseGifts = Gift::where('user_id', $spouse->id)->get();
                $spouseTrusts = Trust::where('user_id', $spouse->id)->where('is_active', true)->get();
                $spouseProfile = IHTProfile::where('user_id', $spouse->id)->first();
                $spouseWill = Will::where('user_id', $spouse->id)->first();
            }

            // 4. Calculate second death IHT scenario
            $secondDeathAnalysis = $this->secondDeathCalculator->calculateSecondDeathIHT(
                $user,
                $spouse,
                $userAssets,
                $spouseAssets,
                $userProfile,
                $spouseProfile,
                $userGifts,
                $spouseGifts,
                $userTrusts,
                $spouseTrusts,
                $userLiabilities,
                $spouseLiabilities,
                $userWill,
                $spouseWill,
                $dataSharingEnabled
            );

            // Check for missing data
            if (! $secondDeathAnalysis['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $secondDeathAnalysis['error'] ?? 'Missing required data for second death calculation',
                    'missing_data' => $secondDeathAnalysis['missing_data'] ?? [],
                    'show_spouse_exemption_notice' => true,
                ], 400);
            }

            // 5. Calculate optimal gifting strategy
            $projectedIHTLiability = $secondDeathAnalysis['iht_calculation']['iht_liability'];
            $projectedEstateValue = $secondDeathAnalysis['second_death']['projected_combined_estate_at_second_death'];
            $yearsUntilSecondDeath = $secondDeathAnalysis['second_death']['years_until_death'];

            // CRITICAL: For LIFETIME GIFTING, use ONLY the survivor's own NRB (£325k)
            // Even though total_nrb includes inherited spouse NRB for IHT on death,
            // lifetime PET gifts are limited to the individual's own NRB only
            $config = config('uk_tax_config.inheritance_tax');
            $totalNRB = $config['nil_rate_band']; // £325,000 - survivor's own NRB only
            $rnrb = $secondDeathAnalysis['iht_calculation']['rnrb'];

            // Determine which user survives (for income/expenditure check)
            $survivor = $secondDeathAnalysis['second_death']['name'] === $user->name ? $user : $spouse;

            // Get expenditure data for survivor
            $survivorExpenditure = $this->assetAggregator->getUserExpenditure($survivor);

            $giftingStrategy = $this->giftingOptimizer->calculateOptimalGiftingStrategy(
                $projectedEstateValue,
                $projectedIHTLiability,
                $yearsUntilSecondDeath,
                $survivor,
                $totalNRB,
                $rnrb,
                $survivorExpenditure['annual_expenditure']
            );

            // 6. Calculate life cover recommendations
            $ihtAfterGifting = max(0, $projectedIHTLiability - $giftingStrategy['summary']['total_iht_saved']);

            // Get existing life cover
            $existingUserCover = $this->assetAggregator->getExistingLifeCover($user);
            $existingSpouseCover = $this->assetAggregator->getExistingLifeCover($spouse);
            $totalExistingCover = $existingUserCover + $existingSpouseCover;

            $lifeCoverRecommendations = $this->lifeCoverCalculator->calculateLifeCoverRecommendations(
                $projectedIHTLiability,
                $ihtAfterGifting,
                $yearsUntilSecondDeath,
                $user,
                $spouse,
                $totalExistingCover
            );

            // 7. Generate IHT mitigation strategies (prioritized and filtered)
            $mitigationStrategies = $this->strategyGenerator->generateIHTMitigationStrategies(
                $secondDeathAnalysis,
                $giftingStrategy,
                $lifeCoverRecommendations,
                $userProfile
            );

            // 8. Calculate estate projection (now vs death)
            $projection = null;
            if ($user->date_of_birth) {
                $lifeExpectancy = $this->fvCalculator->getLifeExpectancy($user);
                $yearsToProject = (int) $lifeExpectancy['years_remaining'];
                $growthRate = 0.045; // 4.5% annual growth

                // Current values
                $currentAssets = $userAssets->sum('current_value');
                $mortgages = Mortgage::where('user_id', $user->id)->get();
                $currentMortgages = $mortgages->sum('outstanding_balance');
                $currentLiabilities = $userLiabilities;

                // Project future values
                $projectedAssets = $this->fvCalculator->calculateFutureValue($currentAssets, $growthRate, $yearsToProject);

                // Project mortgages (handle maturity and type)
                $projectedMortgages = 0;
                foreach ($mortgages as $mortgage) {
                    $projectedMortgages += $this->fvCalculator->projectMortgageBalance(
                        (float) $mortgage->outstanding_balance,
                        $mortgage->mortgage_type ?? 'repayment',
                        (int) ($mortgage->remaining_term_months ?? 0),
                        (float) ($mortgage->interest_rate ?? 0),
                        (float) ($mortgage->monthly_payment ?? 0),
                        $yearsToProject
                    );
                }

                // Other liabilities stay constant (conservative assumption)
                $projectedLiabilities = $currentLiabilities;

                // Net estates
                $currentNetEstate = $currentAssets - $currentMortgages - $currentLiabilities;
                $projectedNetEstate = $projectedAssets - $projectedMortgages - $projectedLiabilities;

                // IHT calculations (use second death analysis for projection)
                $currentIHT = $secondDeathAnalysis['iht_calculation']['iht_liability'] ?? 0;
                $projectedIHT = $secondDeathAnalysis['iht_calculation']['iht_liability'] ?? 0;

                $projection = [
                    'life_expectancy' => $lifeExpectancy,
                    'growth_rate' => $growthRate,
                    'current' => [
                        'assets' => $currentAssets,
                        'mortgages' => $currentMortgages,
                        'liabilities' => $currentLiabilities,
                        'net_estate' => $currentNetEstate,
                        'iht_liability' => $currentIHT,
                    ],
                    'at_death' => [
                        'assets' => $projectedAssets,
                        'mortgages' => $projectedMortgages,
                        'liabilities' => $projectedLiabilities,
                        'net_estate' => $projectedNetEstate,
                        'iht_liability' => $projectedIHT,
                        'years_from_now' => $yearsToProject,
                    ],
                ];
            }

            // 9. Return comprehensive analysis
            return response()->json([
                'success' => true,
                'show_spouse_exemption_notice' => true,
                'spouse_exemption_message' => 'Transfers to spouse are exempt from IHT with no limit. This calculation shows IHT payable on second death when both estates are combined.',
                'data_sharing_enabled' => $dataSharingEnabled,
                'second_death_analysis' => $secondDeathAnalysis,
                'gifting_strategy' => $giftingStrategy,
                'life_cover_recommendations' => $lifeCoverRecommendations,
                'mitigation_strategies' => $mitigationStrategies,
                'projection' => $projection,
                'user_gifting_timeline' => $this->giftingTimeline->buildGiftingTimeline($userGifts, $user->name),
                'spouse_gifting_timeline' => $dataSharingEnabled && $spouseGifts ?
                    $this->giftingTimeline->buildGiftingTimeline($spouseGifts, $spouse->name) :
                    [
                        'show_empty_timeline' => true,
                        'message' => 'Enable data sharing with your spouse to track their gifting history for comprehensive IHT planning.',
                    ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Second Death IHT Planning Error:', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate second death IHT planning: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Gather all assets for a user (from all modules)
     */
    private function calculateProjectedIHT(float $projectedNetEstate, IHTProfile $profile): float
    {
        $config = config('uk_tax_config.inheritance_tax');

        // Apply allowances
        $nrb = $config['nil_rate_band'];
        $totalNRB = $nrb + $profile->nrb_transferred_from_spouse;

        // RNRB (assuming still eligible at death)
        $rnrb = $config['residence_nil_rate_band'];
        if ($profile->own_home && $projectedNetEstate > 0) {
            // Double RNRB if married
            if (in_array($profile->marital_status, ['married'])) {
                $rnrb = $rnrb * 2;
            }
        } else {
            $rnrb = 0;
        }

        $totalAllowances = $totalNRB + $rnrb;
        $taxableEstate = max(0, $projectedNetEstate - $totalAllowances);

        $ihtRate = $config['standard_rate'];
        if ($profile->charitable_giving_percent >= 10) {
            $ihtRate = $config['reduced_rate_charity'];
        }

        return $taxableEstate * $ihtRate;
    }
}
