<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NetWorth\NetWorthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NetWorthController extends Controller
{
    public function __construct(
        private NetWorthService $netWorthService
    ) {
    }

    /**
     * Get net worth overview
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getOverview(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                ], 401);
            }

            $netWorth = $this->netWorthService->getCachedNetWorth($user);

            return response()->json([
                'success' => true,
                'data' => $netWorth,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate net worth',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get asset breakdown with percentages
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getBreakdown(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                ], 401);
            }

            $breakdown = $this->netWorthService->getAssetBreakdown($user);

            return response()->json([
                'success' => true,
                'data' => $breakdown,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get asset breakdown',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get net worth trend
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getTrend(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                ], 401);
            }

            $months = (int) $request->query('months', 12);

            if ($months < 1 || $months > 36) {
                return response()->json([
                    'success' => false,
                    'message' => 'Months must be between 1 and 36',
                ], 422);
            }

            $trend = $this->netWorthService->getNetWorthTrend($user, $months);

            return response()->json([
                'success' => true,
                'data' => $trend,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get net worth trend',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get assets summary
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAssetsSummary(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                ], 401);
            }

            $summary = $this->netWorthService->getAssetsSummary($user);

            return response()->json([
                'success' => true,
                'data' => $summary,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get assets summary',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get joint assets
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getJointAssets(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                ], 401);
            }

            $jointAssets = $this->netWorthService->getJointAssets($user);

            return response()->json([
                'success' => true,
                'data' => $jointAssets,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get joint assets',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Refresh net worth (bypass cache)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                ], 401);
            }

            // Invalidate cache
            $this->netWorthService->invalidateCache($user->id);

            // Recalculate
            $netWorth = $this->netWorthService->calculateNetWorth($user);

            return response()->json([
                'success' => true,
                'data' => $netWorth,
                'message' => 'Net worth refreshed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh net worth',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
