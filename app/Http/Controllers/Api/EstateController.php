<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Agents\EstateAgent;
use App\Http\Controllers\Controller;
use App\Models\Estate\Asset;
use App\Models\Estate\Bequest;
use App\Models\Estate\Gift;
use App\Models\Estate\IHTProfile;
use App\Models\Estate\Liability;
use App\Models\Estate\Trust;
use App\Models\Estate\Will;
use App\Models\Investment\InvestmentAccount;
use App\Models\Mortgage;
use App\Services\Estate\CashFlowProjector;
use App\Services\Estate\GiftingStrategyOptimizer;
use App\Services\Estate\IHTCalculator;
use App\Services\Estate\LifeCoverCalculator;
use App\Services\Estate\NetWorthAnalyzer;
use App\Services\Estate\SecondDeathIHTCalculator;
use App\Services\Estate\TrustService;
use App\Services\Trust\IHTPeriodicChargeCalculator;
use App\Services\Trust\TrustAssetAggregatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EstateController extends Controller
{
    public function __construct(
        private EstateAgent $estateAgent,
        private IHTCalculator $ihtCalculator,
        private NetWorthAnalyzer $netWorthAnalyzer,
        private CashFlowProjector $cashFlowProjector,
        private TrustService $trustService,
        private TrustAssetAggregatorService $trustAssetAggregator,
        private IHTPeriodicChargeCalculator $periodicChargeCalculator,
        private \App\Services\Estate\ActuarialLifeTableService $actuarialService,
        private \App\Services\Estate\SpouseNRBTrackerService $nrbTracker,
        private \App\Services\Estate\FutureValueCalculator $fvCalculator,
        private SecondDeathIHTCalculator $secondDeathCalculator,
        private GiftingStrategyOptimizer $giftingOptimizer,
        private LifeCoverCalculator $lifeCoverCalculator,
        private \App\Services\Estate\LifePolicyStrategyService $lifePolicyStrategy
    ) {}

    /**
     * Get all estate planning data for authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $assets = Asset::where('user_id', $user->id)->get();
        $liabilities = Liability::where('user_id', $user->id)->get();
        $gifts = Gift::where('user_id', $user->id)->get();
        $trusts = Trust::where('user_id', $user->id)->get();
        $ihtProfile = IHTProfile::where('user_id', $user->id)->first();

        // Pull investment accounts and categorize for IHT
        $investmentAccounts = InvestmentAccount::where('user_id', $user->id)->get();
        $investmentAccountsFormatted = $investmentAccounts->map(function ($account) {
            // Determine IHT exemption status based on account type
            // VCT and EIS may qualify for Business Relief if held 2+ years
            // For now, we'll mark them as potentially exempt with a note
            $isIhtExempt = false;
            $exemptionReason = null;

            if (in_array($account->account_type, ['vct', 'eis'])) {
                $exemptionReason = 'May qualify for Business Relief if held for 2+ years (manual verification required)';
            }

            return [
                'id' => 'investment_' . $account->id,
                'source' => 'investment_module',
                'investment_account_id' => $account->id,
                'asset_type' => 'investment',
                'asset_name' => $account->provider . ' - ' . strtoupper($account->account_type) . ($account->platform ? ' (' . $account->platform . ')' : ''),
                'account_type' => $account->account_type,
                'current_value' => $account->current_value,
                'is_iht_exempt' => $isIhtExempt,
                'exemption_reason' => $exemptionReason,
                'valuation_date' => $account->updated_at->format('Y-m-d'),
                'ownership_type' => 'sole', // Default, user can change if joint
                'provider' => $account->provider,
                'platform' => $account->platform,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'assets' => $assets,
                'investment_accounts' => $investmentAccountsFormatted,
                'liabilities' => $liabilities,
                'gifts' => $gifts,
                'trusts' => $trusts,
                'iht_profile' => $ihtProfile,
            ],
        ]);
    }

    /**
     * Run comprehensive estate planning analysis
     */
    public function analyze(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            $analysis = $this->estateAgent->analyze($user->id);

            return response()->json([
                'success' => true,
                'data' => $analysis,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Analysis failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get personalized recommendations
     */
    public function recommendations(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            $analysis = $this->estateAgent->analyze($user->id);
            $recommendations = $this->estateAgent->generateRecommendations($analysis);

            return response()->json([
                'success' => true,
                'data' => $recommendations,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate recommendations: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Build what-if scenarios
     */
    public function scenarios(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            $scenarios = $this->estateAgent->buildScenarios($user->id, $request->all());

            return response()->json([
                'success' => true,
                'data' => $scenarios,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to build scenarios: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate IHT liability
     */
    public function calculateIHT(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            $assets = Asset::where('user_id', $user->id)->get();

            // Include investment accounts in estate calculation
            $investmentAccounts = InvestmentAccount::where('user_id', $user->id)->get();

            // Convert investment accounts to asset-compatible format for IHT calculation
            // NOTE: ISAs are NOT IHT-exempt - they are only exempt from income/CGT
            // All investment accounts are included in IHT estate value
            $investmentAssets = $investmentAccounts->map(function ($account) {
                return (object) [
                    'asset_type' => 'investment',
                    'asset_name' => $account->provider . ' - ' . strtoupper($account->account_type),
                    'current_value' => $account->current_value,
                    'is_iht_exempt' => false, // ISAs are IHT taxable
                ];
            });

            // Include properties from Property module
            $properties = \App\Models\Property::where('user_id', $user->id)->get();
            $propertyAssets = $properties->map(function ($property) {
                $ownershipPercentage = $property->ownership_percentage ?? 100;
                $userValue = $property->current_value * ($ownershipPercentage / 100);

                return (object) [
                    'asset_type' => 'property',
                    'asset_name' => $property->address_line_1 ?: 'Property',
                    'current_value' => $userValue,
                    'is_iht_exempt' => false,
                ];
            });

            // Include savings/cash accounts from Savings module
            $savingsAccounts = \App\Models\SavingsAccount::where('user_id', $user->id)->get();
            $savingsAssets = $savingsAccounts->map(function ($account) {
                // NOTE: Cash ISAs are NOT IHT-exempt - they are only exempt from income tax
                // All cash accounts are included in IHT estate value
                return (object) [
                    'asset_type' => 'cash',
                    'asset_name' => $account->institution . ' - ' . ucfirst($account->account_type),
                    'current_value' => $account->current_balance,
                    'is_iht_exempt' => false, // Cash ISAs are IHT taxable
                ];
            });

            // Merge all assets
            $allAssets = $assets
                ->concat($investmentAssets)
                ->concat($propertyAssets)
                ->concat($savingsAssets);

            // Get liabilities (mortgages, loans, etc.)
            $liabilities = \App\Models\Estate\Liability::where('user_id', $user->id)->get();
            $mortgages = \App\Models\Mortgage::where('user_id', $user->id)->get();

            // Calculate total liabilities
            $totalLiabilities = $liabilities->sum('current_balance') + $mortgages->sum('outstanding_balance');

            // Debug logging
            \Log::info('IHT Calculation Debug:', [
                'manual_assets_count' => $assets->count(),
                'manual_assets_total' => $assets->sum('current_value'),
                'investment_accounts_count' => $investmentAccounts->count(),
                'investment_accounts_total' => $investmentAccounts->sum('current_value'),
                'properties_count' => $properties->count(),
                'properties_total' => $propertyAssets->sum('current_value'),
                'savings_accounts_count' => $savingsAccounts->count(),
                'savings_total' => $savingsAssets->sum('current_value'),
                'total_assets_count' => $allAssets->count(),
                'total_assets_value' => $allAssets->sum('current_value'),
                'mortgages_count' => $mortgages->count(),
                'mortgages_total' => $mortgages->sum('outstanding_balance'),
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

    /**
     * Get net worth analysis
     */
    public function getNetWorth(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            $netWorth = $this->netWorthAnalyzer->generateSummary($user->id);

            return response()->json([
                'success' => true,
                'data' => $netWorth,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate net worth: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get cash flow for a tax year
     */
    public function getCashFlow(Request $request): JsonResponse
    {
        $user = $request->user();
        $taxYear = $request->query('taxYear', '2025/26');

        try {
            $cashFlow = $this->cashFlowProjector->createPersonalPL($user->id, $taxYear);

            return response()->json([
                'success' => true,
                'data' => $cashFlow,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get cash flow: '.$e->getMessage(),
            ], 500);
        }
    }

    // ============ ASSET CRUD ============

    /**
     * Store a new asset
     */
    public function storeAsset(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'asset_type' => 'required|in:property,pension,investment,savings,business,life_insurance,personal,other',
            'asset_name' => 'required|string|max:255',
            'current_value' => 'required|numeric|min:0',
            'ownership_type' => 'required|in:sole,joint_tenants,tenants_in_common,trust',
            'beneficiary_designation' => 'nullable|string|max:255',
            'is_iht_exempt' => 'boolean',
            'exemption_reason' => 'nullable|string|max:255',
            'valuation_date' => 'required|date',
            'property_address' => 'nullable|string|max:500',
            'mortgage_outstanding' => 'nullable|numeric|min:0',
            'is_main_residence' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        try {
            $validated['user_id'] = $user->id;
            $asset = Asset::create($validated);

            // Invalidate cache
            Cache::forget("estate_analysis_{$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Asset created successfully',
                'data' => $asset,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create asset: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an asset
     */
    public function updateAsset(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'asset_type' => 'sometimes|in:property,pension,investment,savings,business,life_insurance,personal,other',
            'asset_name' => 'sometimes|string|max:255',
            'current_value' => 'sometimes|numeric|min:0',
            'ownership_type' => 'sometimes|in:sole,joint_tenants,tenants_in_common,trust',
            'beneficiary_designation' => 'nullable|string|max:255',
            'is_iht_exempt' => 'boolean',
            'exemption_reason' => 'nullable|string|max:255',
            'valuation_date' => 'sometimes|date',
            'property_address' => 'nullable|string|max:500',
            'mortgage_outstanding' => 'nullable|numeric|min:0',
            'is_main_residence' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        try {
            $asset = Asset::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $asset->update($validated);

            // Invalidate cache
            Cache::forget("estate_analysis_{$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Asset updated successfully',
                'data' => $asset->fresh(),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Asset not found or unauthorized',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update asset: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an asset
     */
    public function destroyAsset(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        try {
            $asset = Asset::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $asset->delete();

            // Invalidate cache
            Cache::forget("estate_analysis_{$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Asset deleted successfully',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Asset not found or unauthorized',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete asset: '.$e->getMessage(),
            ], 500);
        }
    }

    // ============ LIABILITY CRUD ============

    /**
     * Store a new liability
     */
    public function storeLiability(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'liability_type' => 'required|in:mortgage,secured_loan,personal_loan,credit_card,overdraft,hire_purchase,student_loan,business_loan,other',
            'liability_name' => 'required|string|max:255',
            'current_balance' => 'required|numeric|min:0',
            'monthly_payment' => 'nullable|numeric|min:0',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'maturity_date' => 'nullable|date',
            'secured_against' => 'nullable|string|max:255',
            'is_priority_debt' => 'boolean',
            'mortgage_type' => 'nullable|string|max:50',
            'fixed_until' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        try {
            $validated['user_id'] = $user->id;
            $liability = Liability::create($validated);

            // Invalidate cache
            Cache::forget("estate_analysis_{$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Liability created successfully',
                'data' => $liability,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create liability: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a liability
     */
    public function updateLiability(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'liability_type' => 'sometimes|in:mortgage,secured_loan,personal_loan,credit_card,overdraft,hire_purchase,student_loan,business_loan,other',
            'liability_name' => 'sometimes|string|max:255',
            'current_balance' => 'sometimes|numeric|min:0',
            'monthly_payment' => 'nullable|numeric|min:0',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'maturity_date' => 'nullable|date',
            'secured_against' => 'nullable|string|max:255',
            'is_priority_debt' => 'boolean',
            'mortgage_type' => 'nullable|string|max:50',
            'fixed_until' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        try {
            $liability = Liability::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $liability->update($validated);

            // Invalidate cache
            Cache::forget("estate_analysis_{$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Liability updated successfully',
                'data' => $liability->fresh(),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Liability not found or unauthorized',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update liability: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a liability
     */
    public function destroyLiability(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        try {
            $liability = Liability::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $liability->delete();

            // Invalidate cache
            Cache::forget("estate_analysis_{$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Liability deleted successfully',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Liability not found or unauthorized',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete liability: '.$e->getMessage(),
            ], 500);
        }
    }

    // ============ GIFT CRUD ============

    /**
     * Store a new gift
     */
    public function storeGift(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'gift_date' => 'required|date|before_or_equal:today',
            'recipient' => 'required|string|max:255',
            'gift_type' => 'required|in:pet,clt,exempt,small_gift,annual_exemption',
            'gift_value' => 'required|numeric|min:0',
            'status' => 'sometimes|in:within_7_years,survived_7_years',
            'taper_relief_applicable' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        try {
            $validated['user_id'] = $user->id;
            $gift = Gift::create($validated);

            // Invalidate cache
            Cache::forget("estate_analysis_{$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Gift created successfully',
                'data' => $gift,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create gift: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a gift
     */
    public function updateGift(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'gift_date' => 'sometimes|date|before_or_equal:today',
            'recipient' => 'sometimes|string|max:255',
            'gift_type' => 'sometimes|in:pet,clt,exempt,small_gift,annual_exemption',
            'gift_value' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:within_7_years,survived_7_years',
            'taper_relief_applicable' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        try {
            $gift = Gift::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $gift->update($validated);

            // Invalidate cache
            Cache::forget("estate_analysis_{$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Gift updated successfully',
                'data' => $gift->fresh(),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gift not found or unauthorized',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update gift: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a gift
     */
    public function destroyGift(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        try {
            $gift = Gift::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $gift->delete();

            // Invalidate cache
            Cache::forget("estate_analysis_{$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Gift deleted successfully',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gift not found or unauthorized',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete gift: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get planned gifting strategy based on life expectancy
     */
    public function getPlannedGiftingStrategy(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            // Validate user has required data
            if (!$user->date_of_birth || !$user->gender) {
                return response()->json([
                    'success' => false,
                    'message' => 'Date of birth and gender are required to calculate life expectancy',
                    'requires_profile_update' => true,
                ], 422);
            }

            // ========== USE EXISTING IHT PLANNING CALCULATION ==========
            // Just call the existing method instead of duplicating logic
            $ihtPlanningResponse = $this->calculateSecondDeathIHTPlanning($request);
            $ihtPlanningData = $ihtPlanningResponse->getData(true);

            if (!$ihtPlanningData['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to calculate IHT planning data',
                ], 500);
            }

            // Extract the projection data (has current and at_death IHT liability)
            $projection = $ihtPlanningData['projection'];
            $lifeExpectancy = $projection['life_expectancy'];
            $yearsUntilDeath = (int) $lifeExpectancy['years_remaining'];

            // Current IHT liability
            $currentIHTLiability = $projection['current']['iht_liability'];

            // Projected IHT liability at death
            $projectedIHTLiability = $projection['at_death']['iht_liability'];
            $projectedEstateValue = $projection['at_death']['net_estate'];

            // Get IHT profile
            $ihtProfile = IHTProfile::where('user_id', $user->id)->first();
            if (!$ihtProfile) {
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

            // Get current tax year for cash flow
            $currentTaxYear = (int) date('Y');
            $cashFlow = $this->cashFlowProjector->createPersonalPL($user->id, (string) $currentTaxYear);
            $annualExpenditure = $cashFlow['total_expenses'] ?? 0;

            // Calculate total NRB available
            $taxConfig = config('uk_tax_config');
            $ihtConfig = $taxConfig['inheritance_tax'];
            $giftingConfig = $taxConfig['gifting_exemptions'];

            // CRITICAL: For LIFETIME GIFTING, only the user's own NRB (£325k) applies
            // Spouse NRB transfer ONLY applies on death for IHT calculation, NOT for lifetime gifts
            $totalNRBAvailable = $ihtConfig['nil_rate_band']; // £325,000 - user's own NRB only

            // Calculate RNRB if own home
            $rnrbAvailable = 0;
            if ($ihtProfile->own_home) {
                $rnrbAvailable = $ihtConfig['residence_nil_rate_band'];
            }

            // Only calculate detailed strategy if there's actual projected IHT liability
            $strategy = null;
            if ($projectedIHTLiability > 0) {
                $strategy = $this->giftingOptimizer->calculateOptimalGiftingStrategy(
                    projectedEstateValue: $projectedEstateValue,
                    currentIHTLiability: $projectedIHTLiability,
                    yearsUntilDeath: $yearsUntilDeath,
                    user: $user,
                    totalNRBAvailable: $totalNRBAvailable,
                    rnrbAvailable: $rnrbAvailable,
                    annualExpenditure: $annualExpenditure
                );
            }

            // Get current age and life expectancy details (already fetched above)
            $currentAge = $user->date_of_birth->age;
            $estimatedAgeAtDeath = $currentAge + $yearsUntilDeath;
            $estimatedDateOfDeath = now()->addYears($yearsUntilDeath);

            // Calculate number of complete 7-year PET cycles available
            $complete7YearCycles = floor($yearsUntilDeath / 7);

            // Calculate annual exemption totals
            $annualExemption = $giftingConfig['annual_exemption'];
            $totalAnnualExemptionGifts = $annualExemption * $yearsUntilDeath;
            $annualExemptionIHTSaved = $totalAnnualExemptionGifts * $ihtConfig['standard_rate'];

            // Create a clear annual gifting schedule
            $annualGiftingSchedule = [];
            for ($year = 0; $year < $yearsUntilDeath; $year++) {
                $annualGiftingSchedule[] = [
                    'year' => $year,
                    'age' => $currentAge + $year,
                    'annual_exemption' => $annualExemption,
                    'date' => now()->addYears($year)->format('Y-m-d'),
                ];
            }

            // Create PET cycle framework (educational, not specific amounts)
            $petCycleFramework = [];
            for ($cycle = 0; $cycle < $complete7YearCycles; $cycle++) {
                $giftYear = $cycle * 7;
                $exemptYear = $giftYear + 7;
                $petCycleFramework[] = [
                    'cycle_number' => $cycle + 1,
                    'gift_year' => $giftYear,
                    'gift_age' => $currentAge + $giftYear,
                    'exempt_year' => $exemptYear,
                    'exempt_age' => $currentAge + $exemptYear,
                    'description' => "PET Cycle " . ($cycle + 1) . ": Gift at age " . ($currentAge + $giftYear) . ", becomes IHT-free at age " . ($currentAge + $exemptYear),
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'life_expectancy_analysis' => [
                        'current_age' => $currentAge,
                        'life_expectancy_years' => $lifeExpectancy['years_remaining'],
                        'estimated_age_at_death' => $lifeExpectancy['death_age'],
                        'estimated_date_of_death' => $estimatedDateOfDeath->format('Y-m-d'),
                        'years_until_expected_death' => $yearsUntilDeath,
                        'complete_7_year_pet_cycles' => $complete7YearCycles,
                    ],
                    'current_estate' => [
                        'total_assets' => round($projection['current']['assets'], 2),
                        'total_liabilities' => round($projection['current']['mortgages'] + $projection['current']['liabilities'], 2),
                        'net_worth' => round($projection['current']['net_estate'], 2),
                        'iht_liability' => round($currentIHTLiability, 2),
                    ],
                    'projected_estate_at_death' => [
                        'total_assets' => round($projection['at_death']['assets'], 2),
                        'total_liabilities' => round($projection['at_death']['mortgages'] + $projection['at_death']['liabilities'], 2),
                        'net_estate' => round($projectedEstateValue, 2),
                        'iht_liability' => round($projectedIHTLiability, 2),
                        'years_from_now' => $yearsUntilDeath,
                    ],
                    'annual_exemption_plan' => [
                        'annual_amount' => $annualExemption,
                        'years_available' => $yearsUntilDeath,
                        'total_over_lifetime' => round($totalAnnualExemptionGifts, 2),
                        'total_iht_saved' => round($annualExemptionIHTSaved, 2),
                        'schedule' => array_slice($annualGiftingSchedule, 0, 10), // First 10 years for display
                        'total_entries' => count($annualGiftingSchedule),
                    ],
                    'pet_cycle_framework' => [
                        'cycles_available' => $complete7YearCycles,
                        'nil_rate_band' => $totalNRBAvailable,
                        'maximum_per_cycle' => $totalNRBAvailable, // Can gift up to NRB per cycle
                        'total_potential' => $totalNRBAvailable * $complete7YearCycles,
                        'cycles' => $petCycleFramework,
                        'has_iht_liability' => $projectedIHTLiability > 0,
                    ],
                    'gifting_strategy' => $strategy,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate planned gifting strategy: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get life policy strategy (Whole of Life vs. Self-Insurance)
     *
     * Reuses existing IHT planning data to calculate optimal insurance strategy
     */
    public function getLifePolicyStrategy(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            // Validate user has required data
            if (!$user->date_of_birth || !$user->gender) {
                return response()->json([
                    'success' => false,
                    'message' => 'Date of birth and gender are required to calculate life expectancy and premiums',
                    'requires_profile_update' => true,
                ], 422);
            }

            // ========== REUSE EXISTING IHT PLANNING DATA ==========
            // Get IHT planning data (second death for married, standard for single)
            $isMarried = $user->marital_status === 'married' && $user->spouse_id !== null;

            if ($isMarried) {
                // For married users, use second death IHT calculation
                $ihtPlanningResponse = $this->calculateSecondDeathIHTPlanning($request);
                $ihtPlanningData = $ihtPlanningResponse->getData(true);

                if (!$ihtPlanningData['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to retrieve IHT planning data',
                    ], 500);
                }

                // Extract second death data
                $secondDeathAnalysis = $ihtPlanningData['second_death_analysis'] ?? null;
                if (!$secondDeathAnalysis) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Second death analysis not available',
                    ], 500);
                }

                // Use second death (survivor) data
                $survivorData = $secondDeathAnalysis['second_death'];
                $ihtLiability = $secondDeathAnalysis['iht_calculation']['iht_liability'];
                $yearsUntilDeath = (int) $survivorData['years_until_death'];
                $currentAge = \Carbon\Carbon::parse($user->date_of_birth)->age;

                // Get spouse data for joint policy calculation
                $spouse = $user->spouse_id ? \App\Models\User::find($user->spouse_id) : null;
                $spouseAge = $spouse && $spouse->date_of_birth ? \Carbon\Carbon::parse($spouse->date_of_birth)->age : null;
                $spouseGender = $spouse ? $spouse->gender : null;

            } else {
                // For single users, use standard IHT calculation with projection
                $ihtResponse = $this->calculateIHT($request);
                $ihtData = $ihtResponse->getData(true);

                if (!$ihtData['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to retrieve IHT calculation',
                    ], 500);
                }

                // Extract projection data
                $projection = $ihtData['projection'] ?? null;
                if (!$projection) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Life expectancy projection not available',
                    ], 500);
                }

                $ihtLiability = $projection['at_death']['iht_liability'];
                $yearsUntilDeath = (int) $projection['life_expectancy']['years_remaining'];
                $currentAge = $projection['life_expectancy']['current_age'];

                $spouseAge = null;
                $spouseGender = null;
            }

            // If no IHT liability, return message
            if ($ihtLiability <= 0) {
                return response()->json([
                    'success' => true,
                    'no_iht_liability' => true,
                    'message' => 'You have no projected IHT liability. Life insurance for IHT planning is not required.',
                    'data' => null,
                ]);
            }

            // Calculate life policy strategy using the service
            $strategy = $this->lifePolicyStrategy->calculateStrategy(
                coverAmount: $ihtLiability,
                yearsUntilDeath: $yearsUntilDeath,
                currentAge: $currentAge,
                gender: $user->gender,
                spouseAge: $spouseAge,
                spouseGender: $spouseGender
            );

            return response()->json([
                'success' => true,
                'data' => $strategy,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate life policy strategy: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ============ IHT PROFILE ============

    /**
     * Store or update IHT profile
     */
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

    /**
     * Get all trusts for authenticated user
     */
    public function getTrusts(Request $request): JsonResponse
    {
        $user = $request->user();
        $trusts = Trust::where('user_id', $user->id)->get();

        return response()->json([
            'success' => true,
            'data' => $trusts,
        ]);
    }

    /**
     * Create a new trust
     */
    public function createTrust(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'trust_name' => 'required|string|max:255',
            'trust_type' => 'required|in:bare,interest_in_possession,discretionary,accumulation_maintenance,life_insurance,discounted_gift,loan,mixed,settlor_interested',
            'trust_creation_date' => 'required|date',
            'initial_value' => 'required|numeric|min:0',
            'current_value' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'retained_income_annual' => 'nullable|numeric|min:0',
            'loan_amount' => 'nullable|numeric|min:0',
            'loan_interest_bearing' => 'nullable|boolean',
            'loan_interest_rate' => 'nullable|numeric|min:0',
            'sum_assured' => 'nullable|numeric|min:0',
            'annual_premium' => 'nullable|numeric|min:0',
            'beneficiaries' => 'nullable|string',
            'trustees' => 'nullable|string',
            'purpose' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['user_id'] = $request->user()->id;

        // Determine if it's a relevant property trust
        $validated['is_relevant_property_trust'] = in_array($validated['trust_type'], [
            'discretionary',
            'accumulation_maintenance',
        ]);

        $trust = Trust::create($validated);

        // Invalidate cache
        Cache::forget("estate_analysis_{$request->user()->id}");

        return response()->json([
            'success' => true,
            'message' => 'Trust created successfully',
            'data' => $trust,
        ], 201);
    }

    /**
     * Update an existing trust
     */
    public function updateTrust(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $trust = Trust::where('user_id', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'trust_name' => 'sometimes|required|string|max:255',
            'trust_type' => 'sometimes|required|in:bare,interest_in_possession,discretionary,accumulation_maintenance,life_insurance,discounted_gift,loan,mixed,settlor_interested',
            'trust_creation_date' => 'sometimes|required|date',
            'initial_value' => 'sometimes|required|numeric|min:0',
            'current_value' => 'sometimes|required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'retained_income_annual' => 'nullable|numeric|min:0',
            'loan_amount' => 'nullable|numeric|min:0',
            'loan_interest_bearing' => 'nullable|boolean',
            'loan_interest_rate' => 'nullable|numeric|min:0',
            'sum_assured' => 'nullable|numeric|min:0',
            'annual_premium' => 'nullable|numeric|min:0',
            'beneficiaries' => 'nullable|string',
            'trustees' => 'nullable|string',
            'purpose' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $trust->update($validated);

        // Invalidate cache
        Cache::forget("estate_analysis_{$user->id}");

        return response()->json([
            'success' => true,
            'message' => 'Trust updated successfully',
            'data' => $trust->fresh(),
        ]);
    }

    /**
     * Delete a trust
     */
    public function deleteTrust(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $trust = Trust::where('user_id', $user->id)->findOrFail($id);

        $trust->delete();

        // Invalidate cache
        Cache::forget("estate_analysis_{$user->id}");

        return response()->json([
            'success' => true,
            'message' => 'Trust deleted successfully',
        ]);
    }

    /**
     * Get trust analysis and efficiency metrics
     */
    public function analyzeTrust(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $trust = Trust::where('user_id', $user->id)->findOrFail($id);

        $analysis = $this->trustService->analyzeTrustEfficiency($trust);

        return response()->json([
            'success' => true,
            'data' => $analysis,
        ]);
    }

    /**
     * Get trust recommendations based on user's estate
     */
    public function getTrustRecommendations(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get estate value and IHT liability
        $assets = Asset::where('user_id', $user->id)->get();
        $liabilities = Liability::where('user_id', $user->id)->get();
        $gifts = Gift::where('user_id', $user->id)->get();
        $ihtProfile = IHTProfile::where('user_id', $user->id)->first();

        // Create default profile if missing
        if (! $ihtProfile) {
            // For married users, default to full spouse NRB (£325,000)
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

        $ihtCalculation = $this->ihtCalculator->calculateIHTLiability($assets, $ihtProfile, $gifts);

        $circumstances = [
            'has_children' => $request->input('has_children', false),
            'needs_flexibility' => $request->input('needs_flexibility', false),
        ];

        $recommendations = $this->trustService->getTrustRecommendations(
            $ihtCalculation['gross_estate_value'],
            $ihtCalculation['iht_liability'],
            $circumstances
        );

        return response()->json([
            'success' => true,
            'data' => [
                'estate_value' => $ihtCalculation['gross_estate_value'],
                'iht_liability' => $ihtCalculation['iht_liability'],
                'recommendations' => $recommendations,
            ],
        ]);
    }

    /**
     * Calculate discounted gift trust discount estimate
     */
    public function calculateDiscountedGiftDiscount(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'age' => 'required|integer|min:18|max:100',
            'gift_value' => 'required|numeric|min:1',
            'annual_income' => 'required|numeric|min:0',
        ]);

        $estimate = $this->trustService->estimateDiscountedGiftDiscount(
            $validated['age'],
            $validated['gift_value'],
            $validated['annual_income']
        );

        return response()->json([
            'success' => true,
            'data' => $estimate,
        ]);
    }

    /**
     * Get all assets held in a specific trust
     */
    public function getTrustAssets(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $trust = Trust::where('user_id', $user->id)->findOrFail($id);

        $aggregation = $this->trustAssetAggregator->aggregateAssetsForTrust($trust);

        return response()->json([
            'success' => true,
            'data' => $aggregation,
        ]);
    }

    /**
     * Calculate IHT periodic charges for a trust
     */
    public function calculateTrustIHTImpact(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $trust = Trust::where('user_id', $user->id)->findOrFail($id);

        // Get aggregated asset value
        $aggregation = $this->trustAssetAggregator->aggregateAssetsForTrust($trust);

        // Update trust's total asset value
        $trust->update(['total_asset_value' => $aggregation['total_value']]);

        // Calculate periodic charge
        $periodicCharge = $this->periodicChargeCalculator->calculatePeriodicCharge($trust);

        // Calculate next tax return due date
        $taxReturn = $this->periodicChargeCalculator->calculateTaxReturnDueDates($trust);

        return response()->json([
            'success' => true,
            'data' => [
                'trust' => $trust->fresh(),
                'total_asset_value' => $aggregation['total_value'],
                'periodic_charge' => $periodicCharge,
                'tax_return' => $taxReturn,
                'is_relevant_property_trust' => $trust->isRelevantPropertyTrust(),
            ],
        ]);
    }

    /**
     * Get upcoming tax returns for all user's trusts
     */
    public function getUpcomingTaxReturns(Request $request): JsonResponse
    {
        $user = $request->user();
        $monthsAhead = $request->input('months_ahead', 12);

        // Get upcoming periodic charges
        $upcomingCharges = $this->periodicChargeCalculator->getUpcomingCharges($user->id, $monthsAhead);

        // Get all active trusts with tax return due dates
        $trusts = Trust::where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        $taxReturns = $trusts->map(function ($trust) {
            $taxReturn = $this->periodicChargeCalculator->calculateTaxReturnDueDates($trust);

            return [
                'trust_id' => $trust->id,
                'trust_name' => $trust->trust_name,
                'trust_type' => $trust->trust_type,
                'tax_year_end' => $taxReturn['tax_year_end'],
                'return_due_date' => $taxReturn['return_due_date'],
                'days_until_due' => $taxReturn['days_until_due'],
                'is_overdue' => $taxReturn['is_overdue'],
            ];
        })->sortBy('return_due_date');

        return response()->json([
            'success' => true,
            'data' => [
                'upcoming_periodic_charges' => $upcomingCharges,
                'tax_returns' => $taxReturns->values(),
            ],
        ]);
    }

    // ============ WILL & BEQUEST CRUD ============

    /**
     * Get user's will
     */
    public function getWill(Request $request): JsonResponse
    {
        $user = $request->user();
        $will = Will::where('user_id', $user->id)->with('bequests')->first();

        // If no will exists, create default
        if (! $will) {
            $isMarried = in_array($user->marital_status, ['married']) && $user->spouse_id !== null;
            $will = Will::create([
                'user_id' => $user->id,
                'death_scenario' => 'user_only',
                'spouse_primary_beneficiary' => $isMarried,
                'spouse_bequest_percentage' => $isMarried ? 100.00 : 0.00,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $will,
        ]);
    }

    /**
     * Create or update will
     */
    public function storeOrUpdateWill(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'death_scenario' => 'required|in:user_only,both_simultaneous',
            'spouse_primary_beneficiary' => 'boolean',
            'spouse_bequest_percentage' => 'nullable|numeric|min:0|max:100',
            'executor_notes' => 'nullable|string',
            'last_reviewed_date' => 'nullable|date',
        ]);

        $validated['user_id'] = $user->id;

        $will = Will::updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        // Invalidate IHT cache
        Cache::forget("estate_analysis_{$user->id}");

        return response()->json([
            'success' => true,
            'message' => 'Will saved successfully',
            'data' => $will->fresh()->load('bequests'),
        ]);
    }

    /**
     * Get all bequests for user's will
     */
    public function getBequests(Request $request): JsonResponse
    {
        $user = $request->user();
        $will = Will::where('user_id', $user->id)->first();

        if (! $will) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $bequests = Bequest::where('will_id', $will->id)->orderBy('priority_order')->get();

        return response()->json([
            'success' => true,
            'data' => $bequests,
        ]);
    }

    /**
     * Create a bequest
     */
    public function storeBequest(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get or create will first
        $will = Will::firstOrCreate(
            ['user_id' => $user->id],
            [
                'death_scenario' => 'user_only',
                'spouse_primary_beneficiary' => false,
                'spouse_bequest_percentage' => 0.00,
            ]
        );

        $validated = $request->validate([
            'beneficiary_name' => 'required|string|max:255',
            'beneficiary_user_id' => 'nullable|exists:users,id',
            'bequest_type' => 'required|in:percentage,specific_amount,specific_asset,residuary',
            'percentage_of_estate' => 'nullable|numeric|min:0|max:100',
            'specific_amount' => 'nullable|numeric|min:0',
            'specific_asset_description' => 'nullable|string',
            'asset_id' => 'nullable|exists:assets,id',
            'priority_order' => 'nullable|integer|min:1',
            'conditions' => 'nullable|string',
        ]);

        $validated['will_id'] = $will->id;
        $validated['user_id'] = $user->id;

        // Auto-set priority order if not provided
        if (! isset($validated['priority_order'])) {
            $maxPriority = Bequest::where('will_id', $will->id)->max('priority_order') ?? 0;
            $validated['priority_order'] = $maxPriority + 1;
        }

        $bequest = Bequest::create($validated);

        // Invalidate cache
        Cache::forget("estate_analysis_{$user->id}");

        return response()->json([
            'success' => true,
            'message' => 'Bequest created successfully',
            'data' => $bequest,
        ], 201);
    }

    /**
     * Update a bequest
     */
    public function updateBequest(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'beneficiary_name' => 'sometimes|string|max:255',
            'beneficiary_user_id' => 'nullable|exists:users,id',
            'bequest_type' => 'sometimes|in:percentage,specific_amount,specific_asset,residuary',
            'percentage_of_estate' => 'nullable|numeric|min:0|max:100',
            'specific_amount' => 'nullable|numeric|min:0',
            'specific_asset_description' => 'nullable|string',
            'asset_id' => 'nullable|exists:assets,id',
            'priority_order' => 'nullable|integer|min:1',
            'conditions' => 'nullable|string',
        ]);

        $bequest = Bequest::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $bequest->update($validated);

        // Invalidate cache
        Cache::forget("estate_analysis_{$user->id}");

        return response()->json([
            'success' => true,
            'message' => 'Bequest updated successfully',
            'data' => $bequest->fresh(),
        ]);
    }

    /**
     * Delete a bequest
     */
    public function deleteBequest(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $bequest = Bequest::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $bequest->delete();

        // Invalidate cache
        Cache::forget("estate_analysis_{$user->id}");

        return response()->json([
            'success' => true,
            'message' => 'Bequest deleted successfully',
        ]);
    }

    /**
     * Calculate IHT for surviving spouse scenario
     *
     * This endpoint calculates IHT as if the user is a surviving spouse,
     * projecting their estate to expected death date and including
     * transferred NRB from deceased spouse.
     */
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

            // Get all user's assets
            $assets = Asset::where('user_id', $user->id)->get();
            $investmentAccounts = InvestmentAccount::where('user_id', $user->id)->get();
            $properties = \App\Models\Property::where('user_id', $user->id)->get();
            $savingsAccounts = \App\Models\SavingsAccount::where('user_id', $user->id)->get();

            // Convert to unified asset format
            $investmentAssets = $investmentAccounts->map(function ($account) {
                return (object) [
                    'asset_type' => 'investment',
                    'asset_name' => $account->provider.' - '.strtoupper($account->account_type),
                    'current_value' => $account->current_value,
                    'is_iht_exempt' => false,
                ];
            });

            $propertyAssets = $properties->map(function ($property) {
                $ownershipPercentage = $property->ownership_percentage ?? 100;
                $userValue = $property->current_value * ($ownershipPercentage / 100);

                return (object) [
                    'asset_type' => 'property',
                    'asset_name' => $property->address_line_1 ?: 'Property',
                    'current_value' => $userValue,
                    'is_iht_exempt' => false,
                ];
            });

            $savingsAssets = $savingsAccounts->map(function ($account) {
                return (object) [
                    'asset_type' => 'cash',
                    'asset_name' => $account->institution.' - '.ucfirst($account->account_type),
                    'current_value' => $account->current_balance,
                    'is_iht_exempt' => false,
                ];
            });

            $allAssets = $assets
                ->concat($investmentAssets)
                ->concat($propertyAssets)
                ->concat($savingsAssets);

            // Get liabilities
            $liabilities = \App\Models\Estate\Liability::where('user_id', $user->id)->get();
            $mortgages = \App\Models\Mortgage::where('user_id', $user->id)->get();
            $totalLiabilities = $liabilities->sum('current_balance') + $mortgages->sum('outstanding_balance');

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

    /**
     * Calculate comprehensive second death IHT planning for married couples
     *
     * This endpoint provides:
     * - Second death scenario calculation (both spouses' estates combined)
     * - Dual gifting timelines (user + spouse)
     * - Automatic optimal gifting strategy (PETs prioritized, CLTs as last resort)
     * - Life cover recommendations (full, less gifting, self-insurance)
     * - IHT mitigation strategies prioritized by effectiveness
     *
     * @param Request $request
     * @return JsonResponse
     */
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
                $userAssets = $this->gatherUserAssets($user);
                $userLiabilities = $this->calculateUserLiabilities($user);
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
                $defaultGiftingStrategy = $this->generateDefaultGiftingStrategy($effectiveIHTLiability, $user);

                $mitigationStrategies = $this->generateIHTMitigationStrategies(
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
            $userAssets = $this->gatherUserAssets($user);
            $userLiabilities = $this->calculateUserLiabilities($user);
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
                $spouseAssets = $this->gatherUserAssets($spouse);
                $spouseLiabilities = $this->calculateUserLiabilities($spouse);
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
            $survivorExpenditure = $this->getUserExpenditure($survivor);

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
            $existingUserCover = $this->getExistingLifeCover($user);
            $existingSpouseCover = $this->getExistingLifeCover($spouse);
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
            $mitigationStrategies = $this->generateIHTMitigationStrategies(
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
                'user_gifting_timeline' => $this->buildGiftingTimeline($userGifts, $user->name),
                'spouse_gifting_timeline' => $dataSharingEnabled && $spouseGifts ?
                    $this->buildGiftingTimeline($spouseGifts, $spouse->name) :
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
    private function gatherUserAssets(\App\Models\User $user): \Illuminate\Support\Collection
    {
        $assets = Asset::where('user_id', $user->id)->get();

        // Investment accounts
        $investmentAccounts = InvestmentAccount::where('user_id', $user->id)->get();
        $investmentAssets = $investmentAccounts->map(function ($account) {
            return (object) [
                'asset_type' => 'investment',
                'asset_name' => $account->provider.' - '.strtoupper($account->account_type),
                'current_value' => $account->current_value,
                'is_iht_exempt' => false,
            ];
        });

        // Properties
        $properties = \App\Models\Property::where('user_id', $user->id)->get();
        $propertyAssets = $properties->map(function ($property) {
            $ownershipPercentage = $property->ownership_percentage ?? 100;
            $userValue = $property->current_value * ($ownershipPercentage / 100);

            return (object) [
                'asset_type' => 'property',
                'asset_name' => $property->address_line_1 ?: 'Property',
                'current_value' => $userValue,
                'is_iht_exempt' => false,
            ];
        });

        // Savings/Cash
        $savingsAccounts = \App\Models\SavingsAccount::where('user_id', $user->id)->get();
        $savingsAssets = $savingsAccounts->map(function ($account) {
            return (object) [
                'asset_type' => 'cash',
                'asset_name' => $account->institution.' - '.ucfirst($account->account_type),
                'current_value' => $account->current_balance,
                'is_iht_exempt' => false,
            ];
        });

        // Business Interests
        $businessInterests = \App\Models\BusinessInterest::where('user_id', $user->id)->get();
        $businessAssets = $businessInterests->map(function ($business) {
            $ownershipPercentage = $business->ownership_percentage ?? 100;
            $userValue = $business->current_valuation * ($ownershipPercentage / 100);

            return (object) [
                'asset_type' => 'business',
                'asset_name' => $business->business_name,
                'current_value' => $userValue,
                'is_iht_exempt' => false, // May qualify for Business Relief (BR) at 50% or 100%
            ];
        });

        // Chattels (personal property)
        $chattels = \App\Models\Chattel::where('user_id', $user->id)->get();
        $chattelAssets = $chattels->map(function ($chattel) {
            $ownershipPercentage = $chattel->ownership_percentage ?? 100;
            $userValue = $chattel->current_value * ($ownershipPercentage / 100);

            return (object) [
                'asset_type' => 'chattel',
                'asset_name' => $chattel->name,
                'current_value' => $userValue,
                'is_iht_exempt' => false,
            ];
        });

        // DC Pensions (not IHT liable but needed for income projections in gifting strategy)
        $dcPensions = \App\Models\DCPension::where('user_id', $user->id)->get();
        $dcPensionAssets = $dcPensions->map(function ($pension) {
            return (object) [
                'asset_type' => 'dc_pension',
                'asset_name' => $pension->scheme_name,
                'current_value' => $pension->current_fund_value,
                'is_iht_exempt' => true, // DC pensions outside estate if beneficiary nominated
            ];
        });

        // DB Pensions (for income projections only - no transfer value in estate)
        $dbPensions = \App\Models\DBPension::where('user_id', $user->id)->get();
        $dbPensionAssets = $dbPensions->map(function ($pension) {
            return (object) [
                'asset_type' => 'db_pension',
                'asset_name' => $pension->scheme_name,
                'current_value' => 0, // DB pensions have no IHT value (die with member)
                'is_iht_exempt' => true,
                'annual_income' => $pension->expected_annual_pension ?? 0, // For income projections
            ];
        });

        return $assets
            ->concat($investmentAssets)
            ->concat($propertyAssets)
            ->concat($savingsAssets)
            ->concat($businessAssets)
            ->concat($chattelAssets)
            ->concat($dcPensionAssets)
            ->concat($dbPensionAssets);
    }

    /**
     * Calculate total liabilities for a user
     */
    private function calculateUserLiabilities(\App\Models\User $user): float
    {
        $liabilities = Liability::where('user_id', $user->id)->sum('current_balance');
        $mortgages = \App\Models\Mortgage::where('user_id', $user->id)->sum('outstanding_balance');

        return $liabilities + $mortgages;
    }

    /**
     * Get total existing life cover for a user
     */
    private function getExistingLifeCover(\App\Models\User $user): float
    {
        $lifeInsurance = \App\Models\LifeInsurancePolicy::where('user_id', $user->id)
            ->where('policy_status', 'active')
            ->sum('sum_assured');

        $criticalIllness = \App\Models\CriticalIllnessPolicy::where('user_id', $user->id)
            ->where('policy_status', 'active')
            ->where('has_life_cover', true)
            ->sum('sum_assured');

        return $lifeInsurance + $criticalIllness;
    }

    /**
     * Get user expenditure data
     */
    private function getUserExpenditure(\App\Models\User $user): array
    {
        // Try ExpenditureProfile first
        $expenditureProfile = \App\Models\ExpenditureProfile::where('user_id', $user->id)->first();
        if ($expenditureProfile) {
            return [
                'monthly_expenditure' => $expenditureProfile->total_monthly_expenditure,
                'annual_expenditure' => $expenditureProfile->total_monthly_expenditure * 12,
            ];
        }

        // Fall back to ProtectionProfile if available
        $protectionProfile = \App\Models\ProtectionProfile::where('user_id', $user->id)->first();
        if ($protectionProfile && $protectionProfile->monthly_expenditure) {
            return [
                'monthly_expenditure' => $protectionProfile->monthly_expenditure,
                'annual_expenditure' => $protectionProfile->monthly_expenditure * 12,
            ];
        }

        // No expenditure data available
        return [
            'monthly_expenditure' => 0,
            'annual_expenditure' => 0,
        ];
    }

    /**
     * Build gifting timeline for display
     */
    private function buildGiftingTimeline(?\Illuminate\Support\Collection $gifts, string $name): array
    {
        if (! $gifts || $gifts->isEmpty()) {
            return [
                'name' => $name,
                'total_gifts' => 0,
                'gifts_within_7_years' => [],
                'timeline_events' => [],
            ];
        }

        // Filter gifts within 7 years
        $recentGifts = $gifts->filter(function ($gift) {
            $yearsAgo = \Carbon\Carbon::now()->diffInYears($gift->gift_date);

            return $yearsAgo < 7;
        })->sortBy('gift_date');

        $timelineEvents = $recentGifts->map(function ($gift) {
            $yearsAgo = \Carbon\Carbon::now()->diffInYears($gift->gift_date);
            $yearsRemaining = max(0, 7 - $yearsAgo);
            $becomeExemptDate = \Carbon\Carbon::parse($gift->gift_date)->addYears(7);

            return [
                'gift_id' => $gift->id,
                'date' => $gift->gift_date->format('Y-m-d'),
                'recipient' => $gift->recipient,
                'value' => $gift->gift_value,
                'type' => $gift->gift_type,
                'years_ago' => $yearsAgo,
                'years_remaining_until_exempt' => $yearsRemaining,
                'becomes_exempt_on' => $becomeExemptDate->format('Y-m-d'),
                'status' => $yearsRemaining === 0 ? 'Exempt' : 'Within 7 years',
            ];
        })->values();

        return [
            'name' => $name,
            'total_gifts' => $recentGifts->sum('gift_value'),
            'gift_count' => $recentGifts->count(),
            'gifts_within_7_years' => $timelineEvents,
        ];
    }

    /**
     * Calculate projected IHT liability based on projected net estate
     *
     * @param  float  $projectedNetEstate
     * @param  IHTProfile  $profile
     * @return float Projected IHT liability
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

    /**
     * Generate default gifting strategy for users without full second death analysis
     */
    private function generateDefaultGiftingStrategy(float $ihtLiability, \App\Models\User $user): ?array
    {
        if ($ihtLiability === 0) {
            return null;
        }

        $strategies = [];
        $totalIhtSaved = 0;

        // 1. Annual Exemption Strategy (£3,000/year)
        $annualExemption = 3000;
        $yearsToProject = min(20, 30); // Project 20 years or to age 90
        $totalAnnualGifting = $annualExemption * $yearsToProject;
        $annualIhtSaved = $totalAnnualGifting * 0.40;

        $strategies[] = [
            'strategy_name' => 'Annual Exemption Gifting',
            'total_gifted' => $totalAnnualGifting,
            'iht_saved' => $annualIhtSaved,
            'implementation_steps' => [
                "Gift £3,000 per year using annual exemption",
                "Both spouses can gift £3,000 each (£6,000 total per year)",
                "Unused allowance from previous year can be carried forward one year",
                "Total potential saving over {$yearsToProject} years: £" . number_format($annualIhtSaved, 0),
            ],
        ];
        $totalIhtSaved += $annualIhtSaved;

        // 2. Potentially Exempt Transfers (PETs) - Lump Sum Gifting
        $targetReduction = min($ihtLiability / 0.40, 1000000); // Max £1m or enough to eliminate IHT
        $petGifting = $targetReduction;
        $petIhtSaved = $petGifting * 0.40;

        $strategies[] = [
            'strategy_name' => 'Potentially Exempt Transfers (PETs)',
            'total_gifted' => $petGifting,
            'iht_saved' => $petIhtSaved,
            'implementation_steps' => [
                "Make lump sum gifts of £" . number_format($petGifting, 0) . " to family members",
                "Gifts become fully exempt after 7 years",
                "Taper relief applies if death occurs between 3-7 years",
                "Consider gifting over multiple years to use annual exemptions",
                "Potential IHT saving: £" . number_format($petIhtSaved, 0) . " (if you survive 7 years)",
            ],
        ];
        $totalIhtSaved += $petIhtSaved;

        // 3. Normal Expenditure Out of Income
        $strategies[] = [
            'strategy_name' => 'Gifts from Surplus Income',
            'total_gifted' => 0, // Depends on user's income
            'iht_saved' => 0, // Variable
            'implementation_steps' => [
                "Establish a regular pattern of gifts from surplus income",
                "Must be from income (not capital)",
                "Must leave you with sufficient income to maintain your standard of living",
                "No 7-year rule - immediately exempt from IHT",
                "Ideal for pension income or rental income",
                "Add income and expenditure data to calculate your surplus",
            ],
        ];

        return [
            'strategies' => $strategies,
            'summary' => [
                'total_gifted' => $totalAnnualGifting + $petGifting,
                'total_iht_saved' => $totalIhtSaved,
                'implementation_timeframe' => $yearsToProject . ' years',
            ],
        ];
    }

    /**
     * Generate prioritized IHT mitigation strategies
     *
     * Only shows strategies that are applicable and effective
     */
    private function generateIHTMitigationStrategies(
        array $secondDeathAnalysis,
        ?array $giftingStrategy,
        ?array $lifeCoverRecommendations,
        IHTProfile $profile
    ): array {
        $strategies = [];

        // Handle different data structures (second death vs standard IHT)
        if (isset($secondDeathAnalysis['second_death'])) {
            // Second death analysis structure
            $estateValue = $secondDeathAnalysis['second_death']['projected_combined_estate_at_second_death'];
            $ihtLiability = $secondDeathAnalysis['iht_calculation']['iht_liability'];
            $rnrb = $secondDeathAnalysis['iht_calculation']['rnrb'];
            $rnrbEligible = $secondDeathAnalysis['iht_calculation']['rnrb_eligible'];
            $taxableNetEstate = $secondDeathAnalysis['iht_calculation']['taxable_net_estate'] ?? 0;
        } elseif (isset($secondDeathAnalysis['iht_calculation'])) {
            // Wrapper structure: ['iht_calculation' => $ihtData]
            $ihtData = $secondDeathAnalysis['iht_calculation'];
            $estateValue = $ihtData['net_estate_value'] ?? 0;
            $ihtLiability = $ihtData['iht_liability'] ?? 0;
            $rnrb = $ihtData['rnrb'] ?? 0;
            $rnrbEligible = $ihtData['rnrb_eligible'] ?? false;
            $taxableNetEstate = $ihtData['taxable_net_estate'] ?? 0;
        } else {
            // Direct IHT calculation structure (already unwrapped)
            $estateValue = $secondDeathAnalysis['net_estate_value'] ?? 0;
            $ihtLiability = $secondDeathAnalysis['iht_liability'] ?? 0;
            $rnrb = $secondDeathAnalysis['rnrb'] ?? 0;
            $rnrbEligible = $secondDeathAnalysis['rnrb_eligible'] ?? false;
            $taxableNetEstate = $secondDeathAnalysis['taxable_net_estate'] ?? 0;
        }

        // For married users with spouse exemption, use taxable_net_estate instead of iht_liability
        // This represents what WILL be taxable on second death (after spouse exemption on first death)
        $effectiveIHTLiability = $ihtLiability;
        if ($ihtLiability === 0 && $taxableNetEstate > 0) {
            // Married user with spouse exemption - calculate potential IHT on taxable net estate
            // This is the estate that will be subject to IHT on second death
            $config = config('uk_tax_config.inheritance_tax');
            $totalNRB = $secondDeathAnalysis['iht_calculation']['total_nrb'] ?? $config['nil_rate_band'];
            $totalAllowance = $totalNRB + $rnrb;
            $potentialTaxableEstate = max(0, $taxableNetEstate - $totalAllowance);
            $effectiveIHTLiability = $potentialTaxableEstate * 0.40; // 40% IHT rate
        }

        // Only show strategies if there's actual or potential IHT liability
        if ($effectiveIHTLiability === 0 && $taxableNetEstate === 0) {
            return [[
                'message' => 'No IHT liability projected - no mitigation strategies needed',
                'status' => 'success',
            ]];
        }

        // 1. Gifting Strategy (if effective)
        if ($giftingStrategy && isset($giftingStrategy['summary']['total_iht_saved']) && $giftingStrategy['summary']['total_iht_saved'] > 0) {
            // Build useful summary of gifting strategy
            $giftingSummary = [];

            foreach ($giftingStrategy['strategies'] as $strategy) {
                // Only include strategies that actually save IHT
                if (($strategy['iht_saved'] ?? 0) > 0) {
                    $giftingSummary[] = sprintf(
                        '%s: Gift £%s over lifetime → Saves £%s IHT',
                        $strategy['strategy_name'],
                        number_format($strategy['total_gifted'] ?? 0, 0),
                        number_format($strategy['iht_saved'] ?? 0, 0)
                    );
                }
            }

            $strategies[] = [
                'priority' => 1,
                'strategy_name' => 'Gifting Strategy',
                'effectiveness' => 'High',
                'iht_saved' => $giftingStrategy['summary']['total_iht_saved'],
                'implementation_complexity' => 'Medium',
                'description' => 'Reduce estate value through strategic lifetime gifting to eliminate or reduce IHT liability',
                'specific_actions' => $giftingSummary,
                'total_gifted' => $giftingStrategy['summary']['total_gifted'] ?? 0,
                'reduction_percentage' => $giftingStrategy['summary']['reduction_percentage'] ?? 0,
            ];
        }

        // 2. Life Insurance (if needed after gifting)
        $totalIhtSaved = $giftingStrategy['summary']['total_iht_saved'] ?? 0;
        $ihtAfterGifting = max(0, $effectiveIHTLiability - $totalIhtSaved);
        if ($ihtAfterGifting > 10000 && $lifeCoverRecommendations && isset($lifeCoverRecommendations['scenarios']['cover_less_gifting']['annual_premium'])) {
            // Only recommend if material amount remains and life cover data available
            $strategies[] = [
                'priority' => 2,
                'strategy_name' => 'Life Insurance in Trust',
                'effectiveness' => 'High',
                'cover_needed' => $ihtAfterGifting,
                'estimated_annual_premium' => $lifeCoverRecommendations['scenarios']['cover_less_gifting']['annual_premium'],
                'implementation_complexity' => 'Low',
                'description' => 'Whole of Life policy written in trust to provide funds for IHT payment',
                'specific_actions' => [
                    'Arrange joint life second death policy for £'.number_format($ihtAfterGifting, 0),
                    'Write policy in trust (outside estate)',
                    'Review cover annually for inflation',
                ],
            ];
        } elseif ($ihtAfterGifting > 10000) {
            // Recommend life insurance even without specific quotes
            $strategies[] = [
                'priority' => 2,
                'strategy_name' => 'Life Insurance in Trust',
                'effectiveness' => 'High',
                'cover_needed' => $ihtAfterGifting,
                'implementation_complexity' => 'Low',
                'description' => 'Whole of Life policy written in trust to provide funds for IHT payment',
                'specific_actions' => [
                    'Arrange joint life second death policy for £'.number_format($ihtAfterGifting, 0),
                    'Write policy in trust (outside estate)',
                    'Review cover annually for inflation',
                    'Obtain quotes from multiple providers',
                ],
            ];
        }

        // 3. RNRB Strategy (only if not already claimed and estate qualifies)
        if (! $rnrbEligible && $estateValue <= 2000000) {
            $strategies[] = [
                'priority' => 3,
                'strategy_name' => 'Claim Residence Nil Rate Band (RNRB)',
                'effectiveness' => 'Medium',
                'iht_saved' => 175000 * 0.40, // £70,000
                'implementation_complexity' => 'Low',
                'description' => 'Ensure main residence passes to direct descendants to claim £175,000 RNRB',
                'specific_actions' => [
                    'Update will to leave main residence to children/grandchildren',
                    'Ensure estate value stays below £2m (RNRB taper threshold)',
                    'Document residence ownership and direct descendant beneficiaries',
                ],
            ];
        }

        // 4. Charitable Giving (if not already at 10%+)
        if ($profile->charitable_giving_percent < 10 && $effectiveIHTLiability > 0) {
            $charitableAmount = $estateValue * 0.10;
            $rateDifference = 0.04; // 40% - 36%
            $saving = ($estateValue - ($secondDeathAnalysis['iht_calculation']['total_nrb'] + $rnrb)) * $rateDifference;

            if ($saving > 5000) {
                // Only show if material saving
                $strategies[] = [
                    'priority' => 4,
                    'strategy_name' => 'Charitable Giving (10%+ of Estate)',
                    'effectiveness' => 'Medium',
                    'iht_saved' => $saving,
                    'charitable_amount_required' => $charitableAmount,
                    'implementation_complexity' => 'Low',
                    'description' => 'Leave 10%+ of estate to charity to reduce IHT rate from 40% to 36%',
                    'specific_actions' => [
                        'Update will to leave £'.number_format($charitableAmount, 0).' (10% of estate) to registered charities',
                        'Select charities and notify executors',
                        'Review annually as estate value changes',
                    ],
                ];
            }
        }

        // 5. Trust Planning (always relevant for larger estates)
        if ($effectiveIHTLiability > 100000) {
            $strategies[] = [
                'priority' => 5,
                'strategy_name' => 'Discretionary Trust Planning',
                'effectiveness' => 'High',
                'iht_saved' => null, // Variable depending on implementation
                'implementation_complexity' => 'High',
                'description' => 'Transfer assets into discretionary trusts to remove them from your estate',
                'specific_actions' => [
                    'Consider a Discounted Gift Trust for lump sum investments',
                    'Set up a Loan Trust to retain access to capital',
                    'Use Bare Trusts for gifts to children/grandchildren',
                    'Seek professional advice on trust structure',
                    'Note: Gifts to discretionary trusts are Chargeable Lifetime Transfers (20% immediate charge if over NRB)',
                ],
            ];
        }

        // 6. Pension Planning (pensions are outside the estate)
        if ($effectiveIHTLiability > 50000) {
            $strategies[] = [
                'priority' => 6,
                'strategy_name' => 'Pension Planning',
                'effectiveness' => 'High',
                'iht_saved' => null, // Depends on pension value
                'implementation_complexity' => 'Low',
                'description' => 'Pensions are outside your estate if beneficiaries are nominated',
                'specific_actions' => [
                    'Maximize pension contributions (£60,000 annual allowance)',
                    'Use carry forward from previous 3 years if available',
                    'Nominate beneficiaries on all pension schemes',
                    'Consider drawdown instead of annuity (drawdown can pass to beneficiaries)',
                    'Delay taking pension if you have other income sources',
                    'Pension funds are IHT-free if you die before age 75',
                ],
            ];
        }

        // 7. Spend and Enjoy (often overlooked!)
        if ($effectiveIHTLiability > 50000) {
            $strategies[] = [
                'priority' => 7,
                'strategy_name' => 'Enjoy Your Wealth',
                'effectiveness' => 'High',
                'iht_saved' => null, // Variable
                'implementation_complexity' => 'Very Low',
                'description' => 'The simplest way to reduce IHT is to spend and enjoy your wealth',
                'specific_actions' => [
                    'Travel, experiences, and lifestyle improvements are IHT-free',
                    'Home improvements increase your quality of life (and may increase RNRB eligibility)',
                    'Support family members with education, weddings, or helping with house deposits',
                    'Consider gifting assets that will appreciate (e.g., property to children)',
                    'Remember: you cannot take it with you!',
                ],
            ];
        }

        // 8. Business Relief (if applicable)
        if ($effectiveIHTLiability > 100000) {
            $strategies[] = [
                'priority' => 8,
                'strategy_name' => 'Business Relief (BR) Investments',
                'effectiveness' => 'High',
                'iht_saved' => null, // Depends on investment amount
                'implementation_complexity' => 'Medium',
                'description' => 'Invest in BR-qualifying assets to reduce estate value (100% IHT relief after 2 years)',
                'specific_actions' => [
                    'Consider AIM-listed shares (many qualify for BR)',
                    'Invest in BR-qualifying investment funds',
                    'Hold for minimum 2 years to qualify',
                    'Higher risk - not suitable for everyone',
                    'Seek professional financial advice before investing',
                    'Note: Not a substitute for diversification',
                ],
            ];
        }

        // Sort by priority
        usort($strategies, fn ($a, $b) => $a['priority'] <=> $b['priority']);

        return $strategies;
    }
}
