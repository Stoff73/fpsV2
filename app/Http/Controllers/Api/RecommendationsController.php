<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RecommendationTracking;
use App\Services\Coordination\RecommendationsAggregatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RecommendationsController extends Controller
{
    public function __construct(
        private RecommendationsAggregatorService $aggregatorService
    ) {}

    /**
     * Get all recommendations for the authenticated user.
     *
     * GET /api/recommendations
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        // Validate query parameters
        $validator = Validator::make($request->all(), [
            'module' => 'sometimes|string|in:protection,savings,investment,retirement,estate,property',
            'priority' => 'sometimes|string|in:high,medium,low',
            'timeline' => 'sometimes|string|in:immediate,short_term,medium_term,long_term',
            'status' => 'sometimes|string|in:pending,in_progress,completed,dismissed',
            'limit' => 'sometimes|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $recommendations = $this->aggregatorService->aggregateRecommendations($userId);

            // Apply filters
            if ($request->has('module')) {
                $recommendations = array_filter($recommendations, fn($rec) => $rec['module'] === $request->module);
            }

            if ($request->has('priority')) {
                $recommendations = array_filter($recommendations, fn($rec) => $rec['impact'] === $request->priority);
            }

            if ($request->has('timeline')) {
                $recommendations = array_filter($recommendations, fn($rec) => $rec['timeline'] === $request->timeline);
            }

            if ($request->has('status')) {
                $recommendations = array_filter($recommendations, fn($rec) => $rec['status'] === $request->status);
            }

            // Apply limit
            if ($request->has('limit')) {
                $recommendations = array_slice($recommendations, 0, (int) $request->limit);
            }

            // Re-index array after filtering
            $recommendations = array_values($recommendations);

            return response()->json([
                'success' => true,
                'data' => $recommendations,
                'count' => count($recommendations),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recommendations: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get recommendations summary with counts.
     *
     * GET /api/recommendations/summary
     */
    public function summary(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        try {
            $summary = $this->aggregatorService->getSummary($userId);

            return response()->json([
                'success' => true,
                'data' => $summary,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch summary: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get top N recommendations.
     *
     * GET /api/recommendations/top
     */
    public function top(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $validator = Validator::make($request->all(), [
            'limit' => 'sometimes|integer|min:1|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $limit = (int) $request->input('limit', 5);
            $recommendations = $this->aggregatorService->getTopRecommendations($userId, $limit);

            return response()->json([
                'success' => true,
                'data' => $recommendations,
                'count' => count($recommendations),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch top recommendations: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark recommendation as done.
     *
     * POST /api/recommendations/{id}/mark-done
     */
    public function markDone(Request $request, string $recommendationId): JsonResponse
    {
        $userId = $request->user()->id;

        try {
            $tracking = RecommendationTracking::where('user_id', $userId)
                ->where('recommendation_id', $recommendationId)
                ->first();

            if (!$tracking) {
                // Create new tracking record
                $tracking = RecommendationTracking::create([
                    'user_id' => $userId,
                    'recommendation_id' => $recommendationId,
                    'module' => $request->input('module', 'general'),
                    'recommendation_text' => $request->input('recommendation_text', ''),
                    'priority_score' => $request->input('priority_score', 50.0),
                    'timeline' => $request->input('timeline', 'medium_term'),
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
            } else {
                $tracking->markAsCompleted();
            }

            return response()->json([
                'success' => true,
                'message' => 'Recommendation marked as completed',
                'data' => $tracking,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark recommendation as done: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark recommendation as in progress.
     *
     * POST /api/recommendations/{id}/in-progress
     */
    public function markInProgress(Request $request, string $recommendationId): JsonResponse
    {
        $userId = $request->user()->id;

        try {
            $tracking = RecommendationTracking::where('user_id', $userId)
                ->where('recommendation_id', $recommendationId)
                ->first();

            if (!$tracking) {
                // Create new tracking record
                $tracking = RecommendationTracking::create([
                    'user_id' => $userId,
                    'recommendation_id' => $recommendationId,
                    'module' => $request->input('module', 'general'),
                    'recommendation_text' => $request->input('recommendation_text', ''),
                    'priority_score' => $request->input('priority_score', 50.0),
                    'timeline' => $request->input('timeline', 'medium_term'),
                    'status' => 'in_progress',
                ]);
            } else {
                $tracking->markAsInProgress();
            }

            return response()->json([
                'success' => true,
                'message' => 'Recommendation marked as in progress',
                'data' => $tracking,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark recommendation as in progress: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Dismiss recommendation.
     *
     * POST /api/recommendations/{id}/dismiss
     */
    public function dismiss(Request $request, string $recommendationId): JsonResponse
    {
        $userId = $request->user()->id;

        try {
            $tracking = RecommendationTracking::where('user_id', $userId)
                ->where('recommendation_id', $recommendationId)
                ->first();

            if (!$tracking) {
                // Create new tracking record
                $tracking = RecommendationTracking::create([
                    'user_id' => $userId,
                    'recommendation_id' => $recommendationId,
                    'module' => $request->input('module', 'general'),
                    'recommendation_text' => $request->input('recommendation_text', ''),
                    'priority_score' => $request->input('priority_score', 50.0),
                    'timeline' => $request->input('timeline', 'medium_term'),
                    'status' => 'dismissed',
                ]);
            } else {
                $tracking->dismiss();
            }

            return response()->json([
                'success' => true,
                'message' => 'Recommendation dismissed',
                'data' => $tracking,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to dismiss recommendation: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update recommendation notes.
     *
     * PATCH /api/recommendations/{id}/notes
     */
    public function updateNotes(Request $request, string $recommendationId): JsonResponse
    {
        $userId = $request->user()->id;

        $validator = Validator::make($request->all(), [
            'notes' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $tracking = RecommendationTracking::where('user_id', $userId)
                ->where('recommendation_id', $recommendationId)
                ->firstOrFail();

            $tracking->update([
                'notes' => $request->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notes updated successfully',
                'data' => $tracking,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update notes: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get completed recommendations.
     *
     * GET /api/recommendations/completed
     */
    public function completed(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        try {
            $completed = RecommendationTracking::where('user_id', $userId)
                ->completed()
                ->orderBy('completed_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $completed,
                'count' => $completed->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch completed recommendations: ' . $e->getMessage(),
            ], 500);
        }
    }
}
