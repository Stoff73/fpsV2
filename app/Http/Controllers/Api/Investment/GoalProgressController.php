<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Investment;

use App\Http\Controllers\Controller;
use App\Models\Investment\InvestmentGoal;
use App\Services\Investment\Goals\GoalProgressAnalyzer;
use App\Services\Investment\Goals\ShortfallAnalyzer;
use App\Services\Investment\Goals\GoalProbabilityCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Goal Progress Controller
 * Manages API endpoints for investment goal progress tracking and analysis
 */
class GoalProgressController extends Controller
{
    public function __construct(
        private GoalProgressAnalyzer $progressAnalyzer,
        private ShortfallAnalyzer $shortfallAnalyzer,
        private GoalProbabilityCalculator $probabilityCalculator
    ) {}

    /**
     * Analyze progress for a specific goal
     *
     * GET /api/investment/goals/{goalId}/progress
     *
     * @param  Request  $request
     * @param  int  $goalId
     * @return JsonResponse
     */
    public function analyzeGoalProgress(Request $request, int $goalId): JsonResponse
    {
        $user = $request->user();

        try {
            $goal = InvestmentGoal::find($goalId);

            if (! $goal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Goal not found',
                ], 404);
            }

            // Verify ownership
            if ($goal->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to goal',
                ], 403);
            }

            $cacheKey = "goal_progress_{$goalId}";

            $result = Cache::remember($cacheKey, 1800, function () use ($goal) {
                return $this->progressAnalyzer->analyzeGoalProgress($goal);
            });

            if (! $result['success']) {
                return response()->json($result, 422);
            }

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Goal progress analysis failed', [
                'user_id' => $user->id,
                'goal_id' => $goalId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to analyze goal progress',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Analyze progress for all user goals
     *
     * GET /api/investment/goals/progress/all
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function analyzeAllGoals(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            $cacheKey = "all_goals_progress_{$user->id}";

            $result = Cache::remember($cacheKey, 1800, function () use ($user) {
                return $this->progressAnalyzer->analyzeAllGoals($user->id);
            });

            if (! $result['success']) {
                return response()->json($result, 404);
            }

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('All goals progress analysis failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to analyze goals progress',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Analyze goal shortfall and get mitigation strategies
     *
     * GET /api/investment/goals/{goalId}/shortfall
     *
     * @param  Request  $request
     * @param  int  $goalId
     * @return JsonResponse
     */
    public function analyzeShortfall(Request $request, int $goalId): JsonResponse
    {
        $user = $request->user();

        try {
            $goal = InvestmentGoal::find($goalId);

            if (! $goal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Goal not found',
                ], 404);
            }

            // Verify ownership
            if ($goal->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to goal',
                ], 403);
            }

            // Get current value
            $currentValue = $goal->current_value ?? 0;

            $result = $this->shortfallAnalyzer->analyzeShortfall($goal, $currentValue);

            if (! $result['success']) {
                return response()->json($result, 422);
            }

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Shortfall analysis failed', [
                'user_id' => $user->id,
                'goal_id' => $goalId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to analyze shortfall',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate what-if scenarios for a goal
     *
     * POST /api/investment/goals/{goalId}/what-if
     *
     * @param  Request  $request
     * @param  int  $goalId
     * @return JsonResponse
     */
    public function generateWhatIfScenarios(Request $request, int $goalId): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'scenarios' => 'nullable|array',
            'scenarios.*.name' => 'required_with:scenarios|string',
            'scenarios.*.contribution' => 'required_with:scenarios|numeric|min:0',
            'scenarios.*.return' => 'required_with:scenarios|numeric|min:0|max:0.5',
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
            $goal = InvestmentGoal::find($goalId);

            if (! $goal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Goal not found',
                ], 404);
            }

            // Verify ownership
            if ($goal->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to goal',
                ], 403);
            }

            $currentValue = $goal->current_value ?? 0;
            $scenarios = $validated['scenarios'] ?? [];

            $result = $this->shortfallAnalyzer->generateWhatIfScenarios($goal, $currentValue, $scenarios);

            if (! $result['success']) {
                return response()->json($result, 422);
            }

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('What-if scenario generation failed', [
                'user_id' => $user->id,
                'goal_id' => $goalId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate what-if scenarios',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate goal success probability
     *
     * POST /api/investment/goals/calculate-probability
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function calculateProbability(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'current_value' => 'required|numeric|min:0',
            'target_value' => 'required|numeric|min:0',
            'monthly_contribution' => 'required|numeric|min:0',
            'expected_return' => 'required|numeric|min:0|max:0.5',
            'volatility' => 'nullable|numeric|min:0|max:1',
            'years_to_goal' => 'required|integer|min:1|max:50',
            'iterations' => 'nullable|integer|min:100|max:5000',
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
            $result = $this->probabilityCalculator->calculateGoalProbability(
                $validated['current_value'],
                $validated['target_value'],
                $validated['monthly_contribution'],
                $validated['expected_return'],
                $validated['volatility'] ?? 0.15,
                $validated['years_to_goal'],
                $validated['iterations'] ?? 1000
            );

            if (! $result['success']) {
                return response()->json($result, 422);
            }

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Probability calculation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate probability',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate required contribution for target probability
     *
     * POST /api/investment/goals/required-contribution
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function calculateRequiredContribution(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'current_value' => 'required|numeric|min:0',
            'target_value' => 'required|numeric|min:0',
            'current_contribution' => 'required|numeric|min:0',
            'expected_return' => 'required|numeric|min:0|max:0.5',
            'volatility' => 'nullable|numeric|min:0|max:1',
            'years_to_goal' => 'required|integer|min:1|max:50',
            'target_probability' => 'nullable|numeric|min:0.5|max:0.99',
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
            $result = $this->probabilityCalculator->calculateRequiredContribution(
                $validated['current_value'],
                $validated['target_value'],
                $validated['current_contribution'],
                $validated['expected_return'],
                $validated['volatility'] ?? 0.15,
                $validated['years_to_goal'],
                $validated['target_probability'] ?? 0.85
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Required contribution calculation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate required contribution',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get glide path recommendation
     *
     * GET /api/investment/goals/glide-path
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function getGlidePath(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'years_to_goal' => 'required|integer|min:0|max:50',
            'current_equity_percent' => 'required|numeric|min:0|max:100',
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
            $result = $this->probabilityCalculator->calculateGlidePath(
                $validated['years_to_goal'],
                $validated['current_equity_percent']
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Glide path calculation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate glide path',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear goal progress caches
     *
     * DELETE /api/investment/goals/clear-cache
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function clearCache(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            // Clear all goal-related caches for this user
            $goals = InvestmentGoal::where('user_id', $user->id)->pluck('id');

            $cacheKeys = ["all_goals_progress_{$user->id}"];

            foreach ($goals as $goalId) {
                $cacheKeys[] = "goal_progress_{$goalId}";
            }

            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }

            return response()->json([
                'success' => true,
                'message' => 'Goal progress caches cleared',
                'cleared_count' => count($cacheKeys),
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
     * Clear user's goal progress cache (static method for use by other controllers)
     *
     * @param  int  $userId  User ID
     * @return void
     */
    public static function clearUserGoalProgressCache(int $userId): void
    {
        $goals = InvestmentGoal::where('user_id', $userId)->pluck('id');

        $cacheKeys = ["all_goals_progress_{$userId}"];

        foreach ($goals as $goalId) {
            $cacheKeys[] = "goal_progress_{$goalId}";
        }

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }
}
