<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Investment;

use App\Http\Controllers\Controller;
use App\Services\Investment\AssetLocation\AssetLocationOptimizer;
use App\Services\Investment\AssetLocation\TaxDragCalculator;
use App\Services\Investment\AssetLocation\AccountTypeRecommender;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Asset Location Controller
 * Manages API endpoints for asset location optimization across account types
 */
class AssetLocationController extends Controller
{
    public function __construct(
        private AssetLocationOptimizer $optimizer,
        private TaxDragCalculator $taxDragCalculator,
        private AccountTypeRecommender $recommender
    ) {}

    /**
     * Get comprehensive asset location analysis
     *
     * GET /api/investment/asset-location/analyze
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function analyzeAssetLocation(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'isa_allowance_used' => 'nullable|numeric|min:0|max:20000',
            'cgt_allowance_used' => 'nullable|numeric|min:0',
            'dividend_allowance_used' => 'nullable|numeric|min:0',
            'expected_return' => 'nullable|numeric|min:0|max:0.5',
            'prefer_pension' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        try {
            $cacheKey = "asset_location_analysis_{$user->id}";

            $result = Cache::remember($cacheKey, 1800, function () use ($user, $validated) {
                return $this->optimizer->analyzeAssetLocation($user->id, $validated);
            });

            if (! $result['success']) {
                return response()->json($result, 404);
            }

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Asset location analysis failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to analyze asset location',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get placement recommendations for all holdings
     *
     * GET /api/investment/asset-location/recommendations
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function getRecommendations(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'priority' => 'nullable|in:high,medium,low',
            'min_saving' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        try {
            $userTaxProfile = $this->buildDefaultTaxProfile($user);
            $result = $this->recommender->generateRecommendations($user->id, $userTaxProfile);

            // Filter by priority if specified
            if (isset($validated['priority'])) {
                $result['recommendations'] = array_filter(
                    $result['recommendations'],
                    fn ($rec) => $rec['priority'] === $validated['priority']
                );
                $result['recommendations'] = array_values($result['recommendations']);
            }

            // Filter by minimum saving if specified
            if (isset($validated['min_saving'])) {
                $result['recommendations'] = array_filter(
                    $result['recommendations'],
                    fn ($rec) => $rec['potential_annual_saving'] >= $validated['min_saving']
                );
                $result['recommendations'] = array_values($result['recommendations']);
            }

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Asset location recommendations failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate recommendations',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate portfolio tax drag
     *
     * GET /api/investment/asset-location/tax-drag
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function calculateTaxDrag(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            $userTaxProfile = $this->buildDefaultTaxProfile($user);
            $result = $this->taxDragCalculator->calculatePortfolioTaxDrag($user->id, $userTaxProfile);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Tax drag calculation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate tax drag',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get optimization score
     *
     * GET /api/investment/asset-location/optimization-score
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function getOptimizationScore(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            $cacheKey = "asset_location_score_{$user->id}";

            $result = Cache::remember($cacheKey, 3600, function () use ($user) {
                $analysis = $this->optimizer->analyzeAssetLocation($user->id);

                if (! $analysis['success']) {
                    return $analysis;
                }

                return [
                    'success' => true,
                    'optimization_score' => $analysis['optimization_score'],
                    'summary' => $analysis['summary'],
                    'potential_savings' => $analysis['potential_savings'],
                ];
            });

            if (! $result['success']) {
                return response()->json($result, 404);
            }

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Optimization score calculation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate optimization score',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Compare account types for a specific holding
     *
     * POST /api/investment/asset-location/compare-accounts
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function compareAccountTypes(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'holding_id' => 'required|integer|exists:holdings,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        try {
            $holding = \App\Models\Investment\Holding::find($validated['holding_id']);

            // Verify ownership
            if ($holding->investmentAccount->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to holding',
                ], 403);
            }

            $userTaxProfile = $this->buildDefaultTaxProfile($user);
            $comparison = $this->taxDragCalculator->compareAccountTypes($holding, $userTaxProfile);

            return response()->json([
                'success' => true,
                'data' => $comparison,
            ]);
        } catch (\Exception $e) {
            Log::error('Account type comparison failed', [
                'user_id' => $user->id,
                'holding_id' => $validated['holding_id'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to compare account types',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear asset location caches
     *
     * DELETE /api/investment/asset-location/clear-cache
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function clearCache(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            $cacheKeys = [
                "asset_location_analysis_{$user->id}",
                "asset_location_score_{$user->id}",
            ];

            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }

            return response()->json([
                'success' => true,
                'message' => 'Asset location caches cleared',
            ]);
        } catch (\Exception $e) {
            Log::error('Cache clearing failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear caches',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Build default tax profile for user
     *
     * @param  \App\Models\User  $user  User
     * @return array Tax profile
     */
    private function buildDefaultTaxProfile($user): array
    {
        $annualIncome = $user->gross_annual_income ?? 50000;
        $age = $user->date_of_birth
            ? (new \DateTime())->diff(new \DateTime($user->date_of_birth))->y
            : 45;

        return [
            'annual_income' => $annualIncome,
            'income_tax_rate' => $this->calculateIncomeTaxRate($annualIncome),
            'cgt_rate' => $annualIncome <= 50270 ? 0.10 : 0.20,
            'isa_allowance_remaining' => 20000,
            'cgt_allowance_used' => 0,
            'dividend_allowance_used' => 0,
            'psa_used' => 0,
            'expected_return' => 0.06,
            'years_to_retirement' => max(0, 67 - $age),
            'expected_withdrawal_tax_rate' => 0.20,
            'prefer_pension' => false,
        ];
    }

    /**
     * Calculate income tax rate
     *
     * @param  float  $income  Annual income
     * @return float Tax rate
     */
    private function calculateIncomeTaxRate(float $income): float
    {
        if ($income <= 12570) {
            return 0.0;
        } elseif ($income <= 50270) {
            return 0.20;
        } elseif ($income <= 125140) {
            return 0.40;
        } else {
            return 0.45;
        }
    }

    /**
     * Clear user's asset location cache (static method for use by other controllers)
     *
     * @param  int  $userId  User ID
     * @return void
     */
    public static function clearUserAssetLocationCache(int $userId): void
    {
        $cacheKeys = [
            "asset_location_analysis_{$userId}",
            "asset_location_score_{$userId}",
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }
}
