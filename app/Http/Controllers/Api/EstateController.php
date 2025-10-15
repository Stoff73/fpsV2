<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Agents\EstateAgent;
use App\Http\Controllers\Controller;
use App\Models\Estate\Asset;
use App\Models\Estate\Gift;
use App\Models\Estate\IHTProfile;
use App\Models\Estate\Liability;
use App\Models\Estate\Trust;
use App\Services\Estate\CashFlowProjector;
use App\Services\Estate\IHTCalculator;
use App\Services\Estate\NetWorthAnalyzer;
use App\Services\Estate\TrustService;
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
        private TrustService $trustService
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

        return response()->json([
            'success' => true,
            'data' => [
                'assets' => $assets,
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
            $gifts = Gift::where('user_id', $user->id)->get();
            $trusts = Trust::where('user_id', $user->id)->where('is_active', true)->get();
            $ihtProfile = IHTProfile::where('user_id', $user->id)->first();

            // Create default profile if it doesn't exist
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

            $ihtLiability = $this->ihtCalculator->calculateIHTLiability($assets, $ihtProfile, $gifts, $trusts);

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
}
