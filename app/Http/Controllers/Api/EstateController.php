<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Agents\EstateAgent;
use App\Http\Controllers\Controller;
use App\Models\Estate\Asset;
use App\Models\Estate\Gift;
use App\Models\Estate\IHTProfile;
use App\Models\Estate\Liability;
use App\Services\Estate\CashFlowProjector;
use App\Services\Estate\IHTCalculator;
use App\Services\Estate\NetWorthAnalyzer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EstateController extends Controller
{
    public function __construct(
        private EstateAgent $estateAgent,
        private IHTCalculator $ihtCalculator,
        private NetWorthAnalyzer $netWorthAnalyzer,
        private CashFlowProjector $cashFlowProjector
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
        $ihtProfile = IHTProfile::where('user_id', $user->id)->first();

        return response()->json([
            'success' => true,
            'data' => [
                'assets' => $assets,
                'liabilities' => $liabilities,
                'gifts' => $gifts,
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
                'message' => 'Analysis failed: ' . $e->getMessage(),
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
                'message' => 'Failed to generate recommendations: ' . $e->getMessage(),
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
                'message' => 'Failed to build scenarios: ' . $e->getMessage(),
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
            $ihtProfile = IHTProfile::where('user_id', $user->id)->first();

            if (!$ihtProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'IHT profile not found. Please set up your profile first.',
                ], 404);
            }

            $ihtLiability = $this->ihtCalculator->calculateIHTLiability($assets, $ihtProfile);

            return response()->json([
                'success' => true,
                'data' => $ihtLiability,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate IHT: ' . $e->getMessage(),
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
                'message' => 'Failed to calculate net worth: ' . $e->getMessage(),
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
                'message' => 'Failed to get cash flow: ' . $e->getMessage(),
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
                'message' => 'Failed to create asset: ' . $e->getMessage(),
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
                'message' => 'Failed to update asset: ' . $e->getMessage(),
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
                'message' => 'Failed to delete asset: ' . $e->getMessage(),
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
                'message' => 'Failed to create liability: ' . $e->getMessage(),
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
                'message' => 'Failed to update liability: ' . $e->getMessage(),
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
                'message' => 'Failed to delete liability: ' . $e->getMessage(),
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
            'gift_date' => 'required|date',
            'recipient' => 'required|string|max:255',
            'gift_type' => 'required|in:pet,exempt_transfer,small_gift,marriage_gift',
            'gift_value' => 'required|numeric|min:0',
            'status' => 'sometimes|in:within_7_years,survived_7_years',
            'taper_relief_applicable' => 'boolean',
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
                'message' => 'Failed to create gift: ' . $e->getMessage(),
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
            'gift_date' => 'sometimes|date',
            'recipient' => 'sometimes|string|max:255',
            'gift_type' => 'sometimes|in:pet,exempt_transfer,small_gift,marriage_gift',
            'gift_value' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:within_7_years,survived_7_years',
            'taper_relief_applicable' => 'boolean',
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
                'message' => 'Failed to update gift: ' . $e->getMessage(),
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
                'message' => 'Failed to delete gift: ' . $e->getMessage(),
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
                'message' => 'Failed to save IHT profile: ' . $e->getMessage(),
            ], 500);
        }
    }
}
