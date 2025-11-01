<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Investment;

use App\Http\Controllers\Controller;
use App\Services\Investment\RiskProfile\CapacityForLossAnalyzer;
use App\Services\Investment\RiskProfile\RiskProfiler;
use App\Services\Investment\RiskProfile\RiskQuestionnaire;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Risk Profile Controller
 * Manages API endpoints for investment risk profiling
 */
class RiskProfileController extends Controller
{
    public function __construct(
        private RiskQuestionnaire $questionnaire,
        private RiskProfiler $profiler,
        private CapacityForLossAnalyzer $capacityAnalyzer
    ) {}

    /**
     * Get risk questionnaire
     *
     * GET /api/investment/risk-profile/questionnaire
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function getQuestionnaire(Request $request): JsonResponse
    {
        try {
            $questionnaire = $this->questionnaire->getQuestionnaire();

            return response()->json([
                'success' => true,
                'data' => $questionnaire,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve questionnaire', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve questionnaire',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate risk score from questionnaire answers
     *
     * POST /api/investment/risk-profile/calculate-score
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function calculateScore(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'answers' => 'required|array|min:1',
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
            $scoreResult = $this->questionnaire->calculateRiskScore($validated['answers']);

            return response()->json([
                'success' => true,
                'data' => $scoreResult,
            ]);
        } catch (\Exception $e) {
            Log::error('Risk score calculation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate risk score',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate complete risk profile
     *
     * POST /api/investment/risk-profile/generate
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function generateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'answers' => 'required|array|min:1',
            'financial_data' => 'nullable|array',
            'financial_data.age' => 'nullable|integer|min:18|max:100',
            'financial_data.retirement_age' => 'nullable|integer|min:50|max:75',
            'financial_data.annual_income' => 'nullable|numeric|min:0',
            'financial_data.income_stability' => 'nullable|in:very_stable,stable,moderate,variable,unstable',
            'financial_data.emergency_fund' => 'nullable|numeric|min:0',
            'financial_data.monthly_expenses' => 'nullable|numeric|min:0',
            'financial_data.total_debt' => 'nullable|numeric|min:0',
            'financial_data.dependents' => 'nullable|integer|min:0',
            'financial_data.portfolio_purpose' => 'nullable|in:discretionary,wealth_building,growth,balanced,income,essential,retirement_income',
            'financial_data.net_worth' => 'nullable|numeric|min:0',
            'financial_data.portfolio_value' => 'nullable|numeric|min:0',
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
            $profile = $this->profiler->generateRiskProfile(
                $validated['answers'],
                $validated['financial_data'] ?? []
            );

            // Cache the result
            Cache::put("risk_profile_{$user->id}", $profile, 7200); // 2 hours

            return response()->json([
                'success' => true,
                'data' => $profile,
            ]);
        } catch (\Exception $e) {
            Log::error('Risk profile generation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate risk profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Analyze capacity for loss
     *
     * POST /api/investment/risk-profile/capacity
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function analyzeCapacity(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'age' => 'nullable|integer|min:18|max:100',
            'retirement_age' => 'nullable|integer|min:50|max:75',
            'annual_income' => 'required|numeric|min:0',
            'income_stability' => 'nullable|in:very_stable,stable,moderate,variable,unstable',
            'emergency_fund' => 'nullable|numeric|min:0',
            'monthly_expenses' => 'required|numeric|min:0',
            'total_debt' => 'nullable|numeric|min:0',
            'dependents' => 'nullable|integer|min:0',
            'portfolio_purpose' => 'nullable|in:discretionary,wealth_building,growth,balanced,income,essential,retirement_income',
            'net_worth' => 'nullable|numeric|min:0',
            'portfolio_value' => 'nullable|numeric|min:0',
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
            $capacity = $this->capacityAnalyzer->analyzeCapacity($validated);

            return response()->json([
                'success' => true,
                'data' => $capacity,
            ]);
        } catch (\Exception $e) {
            Log::error('Capacity analysis failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to analyze capacity for loss',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get cached risk profile
     *
     * GET /api/investment/risk-profile
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function getProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            $profile = Cache::get("risk_profile_{$user->id}");

            if (! $profile) {
                return response()->json([
                    'success' => false,
                    'message' => 'No risk profile found. Please complete the questionnaire.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $profile,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve risk profile', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve risk profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear risk profile cache
     *
     * DELETE /api/investment/risk-profile/clear-cache
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function clearCache(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            Cache::forget("risk_profile_{$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Risk profile cache cleared',
            ]);
        } catch (\Exception $e) {
            Log::error('Cache clearing failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear user's risk profile cache (static method for use by other controllers)
     *
     * @param  int  $userId  User ID
     * @return void
     */
    public static function clearUserRiskProfileCache(int $userId): void
    {
        Cache::forget("risk_profile_{$userId}");
    }
}
