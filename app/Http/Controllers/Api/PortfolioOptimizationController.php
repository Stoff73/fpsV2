<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Investment\Analytics\EfficientFrontierCalculator;
use App\Services\Investment\Analytics\MarkowitzOptimizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Portfolio Optimization API Controller
 * Provides endpoints for Modern Portfolio Theory analysis and optimization
 */
class PortfolioOptimizationController extends Controller
{
    public function __construct(
        private EfficientFrontierCalculator $frontierCalculator,
        private MarkowitzOptimizer $optimizer
    ) {}

    /**
     * Calculate efficient frontier for user's portfolio
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function calculateEfficientFrontier(Request $request): JsonResponse
    {
        $user = $request->user();
        $userId = $user->id;

        // Validate optional parameters
        $validated = $request->validate([
            'risk_free_rate' => 'nullable|numeric|min:0|max:0.15',
            'num_points' => 'nullable|integer|min:10|max:100',
        ]);

        $riskFreeRate = $validated['risk_free_rate'] ?? 0.045; // UK Gilts ~4.5%
        $numPoints = $validated['num_points'] ?? 50;

        // Cache key for this calculation
        $cacheKey = "efficient_frontier_{$userId}_{$riskFreeRate}_{$numPoints}";

        try {
            // Cache for 1 hour (calculation is expensive)
            $result = Cache::remember($cacheKey, 3600, function () use ($userId, $riskFreeRate, $numPoints) {
                return $this->frontierCalculator->calculate(
                    $userId,
                    $riskFreeRate,
                    $numPoints
                );
            });

            if (! $result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Efficient frontier calculation failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate efficient frontier',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Optimize portfolio for minimum variance
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function optimizeMinimumVariance(Request $request): JsonResponse
    {
        $user = $request->user();

        // This will be enhanced when we add request validation class
        $validated = $request->validate([
            'min_weight' => 'nullable|numeric|min:0|max:1',
            'max_weight' => 'nullable|numeric|min:0|max:1',
        ]);

        try {
            // Get user's holdings data (simplified for now)
            // TODO: Extract holdings and returns properly
            $expectedReturns = [0.07, 0.08, 0.06, 0.09]; // Mock data
            $covarianceMatrix = $this->getMockCovarianceMatrix();

            $constraints = [
                'min_weight' => $validated['min_weight'] ?? 0.0,
                'max_weight' => $validated['max_weight'] ?? 1.0,
            ];

            $result = $this->optimizer->minimumVariance(
                $expectedReturns,
                $covarianceMatrix,
                $constraints
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Minimum variance optimization failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to optimize portfolio',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Optimize portfolio for maximum Sharpe ratio
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function optimizeMaximumSharpe(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'risk_free_rate' => 'nullable|numeric|min:0|max:0.15',
            'min_weight' => 'nullable|numeric|min:0|max:1',
            'max_weight' => 'nullable|numeric|min:0|max:1',
        ]);

        try {
            $expectedReturns = [0.07, 0.08, 0.06, 0.09]; // Mock data
            $covarianceMatrix = $this->getMockCovarianceMatrix();

            $riskFreeRate = $validated['risk_free_rate'] ?? 0.045;
            $constraints = [
                'min_weight' => $validated['min_weight'] ?? 0.0,
                'max_weight' => $validated['max_weight'] ?? 1.0,
            ];

            $result = $this->optimizer->maximumSharpe(
                $expectedReturns,
                $covarianceMatrix,
                $riskFreeRate,
                $constraints
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Maximum Sharpe optimization failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to optimize portfolio',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Optimize portfolio for target return
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function optimizeTargetReturn(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'target_return' => 'required|numeric|min:0|max:1',
            'min_weight' => 'nullable|numeric|min:0|max:1',
            'max_weight' => 'nullable|numeric|min:0|max:1',
        ]);

        try {
            $expectedReturns = [0.07, 0.08, 0.06, 0.09]; // Mock data
            $covarianceMatrix = $this->getMockCovarianceMatrix();

            $targetReturn = $validated['target_return'];
            $constraints = [
                'min_weight' => $validated['min_weight'] ?? 0.0,
                'max_weight' => $validated['max_weight'] ?? 1.0,
            ];

            $result = $this->optimizer->targetReturn(
                $expectedReturns,
                $covarianceMatrix,
                $targetReturn,
                $constraints
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Target return optimization failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to optimize portfolio',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Calculate risk parity portfolio
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function optimizeRiskParity(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            $volatilities = [0.15, 0.18, 0.12, 0.20]; // Mock data
            $expectedReturns = [0.07, 0.08, 0.06, 0.09]; // Mock data
            $covarianceMatrix = $this->getMockCovarianceMatrix();

            $result = $this->optimizer->riskParity(
                $volatilities,
                $expectedReturns,
                $covarianceMatrix
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Risk parity optimization failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate risk parity portfolio',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get current portfolio position on efficient frontier
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function getCurrentPosition(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            // Get efficient frontier data (cached)
            $frontierData = $this->frontierCalculator->calculate($user->id);

            if (! $frontierData['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $frontierData['message'],
                ], 400);
            }

            // Extract just current portfolio position
            $currentPosition = $frontierData['current_portfolio'];
            $improvements = $frontierData['improvement_opportunities'];

            return response()->json([
                'success' => true,
                'data' => [
                    'current_portfolio' => $currentPosition,
                    'improvement_opportunities' => $improvements,
                    'on_efficient_frontier' => $improvements['sharpe_improvement'] < 0.05,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Current position calculation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate current position',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Clear cached efficient frontier calculations
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function clearCache(Request $request): JsonResponse
    {
        $user = $request->user();
        $userId = $user->id;

        // Clear all efficient frontier cache keys for this user
        $pattern = "efficient_frontier_{$userId}_*";

        // Laravel doesn't support pattern-based cache clearing directly
        // So we'll clear common variations
        $riskFreeRates = [0.03, 0.035, 0.04, 0.045, 0.05];
        $numPointsOptions = [25, 50, 100];

        foreach ($riskFreeRates as $rate) {
            foreach ($numPointsOptions as $points) {
                Cache::forget("efficient_frontier_{$userId}_{$rate}_{$points}");
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Cache cleared successfully',
        ]);
    }

    /**
     * Mock covariance matrix for testing
     * TODO: Remove when real data integration complete
     *
     * @return array
     */
    private function getMockCovarianceMatrix(): array
    {
        return [
            [0.0225, 0.0120, 0.0090, 0.0150],
            [0.0120, 0.0324, 0.0108, 0.0180],
            [0.0090, 0.0108, 0.0144, 0.0120],
            [0.0150, 0.0180, 0.0120, 0.0400],
        ];
    }
}
