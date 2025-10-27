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
use App\Services\Estate\NetWorthAnalyzer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EstateController extends Controller
{
    public function __construct(
        private EstateAgent $estateAgent,
        private NetWorthAnalyzer $netWorthAnalyzer,
        private CashFlowProjector $cashFlowProjector,
        private \App\Services\Estate\ComprehensiveEstatePlanService $comprehensiveEstatePlan
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
     * Generate comprehensive estate plan combining all strategies
     */
    public function getComprehensiveEstatePlan(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            $plan = $this->comprehensiveEstatePlan->generateComprehensiveEstatePlan($user);

            return response()->json([
                'success' => true,
                'data' => $plan,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate comprehensive estate plan: '.$e->getMessage(),
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
}
