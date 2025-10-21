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
use App\Services\Estate\CashFlowProjector;
use App\Services\Estate\IHTCalculator;
use App\Services\Estate\NetWorthAnalyzer;
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
        private IHTPeriodicChargeCalculator $periodicChargeCalculator
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
                $ihtProfile = new IHTProfile([
                    'user_id' => $user->id,
                    'marital_status' => $user->marital_status ?? 'single',
                    'own_home' => false,
                    'home_value' => 0,
                    'nrb_transferred_from_spouse' => 0,
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

            return response()->json([
                'success' => true,
                'data' => $ihtLiability,
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
        $taxYear = $request->query('taxYear', '2024/25');

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
            $ihtProfile = new IHTProfile([
                'user_id' => $user->id,
                'marital_status' => 'single',
                'own_home' => false,
                'home_value' => 0,
                'nrb_transferred_from_spouse' => 0,
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
}
