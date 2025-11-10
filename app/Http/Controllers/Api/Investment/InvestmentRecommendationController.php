<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Investment;

use App\Http\Controllers\Controller;
use App\Models\Investment\InvestmentRecommendation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class InvestmentRecommendationController extends Controller
{
    /**
     * Get all recommendations for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = InvestmentRecommendation::where('user_id', $user->id);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->has('priority_level')) {
            $level = $request->input('priority_level');
            if ($level === 'high') {
                $query->where('priority', '<=', 3);
            } elseif ($level === 'medium') {
                $query->whereBetween('priority', [4, 7]);
            } elseif ($level === 'low') {
                $query->where('priority', '>', 7);
            }
        }

        // Order by priority and created date
        $recommendations = $query->orderBy('priority')->orderBy('created_at', 'desc')->get();

        // Calculate statistics
        $stats = [
            'total' => $recommendations->count(),
            'pending' => $recommendations->where('status', 'pending')->count(),
            'in_progress' => $recommendations->where('status', 'in_progress')->count(),
            'completed' => $recommendations->where('status', 'completed')->count(),
            'dismissed' => $recommendations->where('status', 'dismissed')->count(),
            'high_priority' => $recommendations->where('priority', '<=', 3)->count(),
            'total_potential_saving' => $recommendations->where('status', '!=', 'dismissed')->sum('potential_saving'),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'recommendations' => $recommendations,
                'stats' => $stats,
            ],
        ]);
    }

    /**
     * Get a single recommendation by ID
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $recommendation = InvestmentRecommendation::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$recommendation) {
            return response()->json([
                'success' => false,
                'message' => 'Recommendation not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $recommendation,
        ]);
    }

    /**
     * Create a new recommendation
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'category' => 'required|string|in:rebalancing,tax,fees,risk,goal,contribution',
            'priority' => 'required|integer|min:1',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'action_required' => 'required|string',
            'impact_level' => 'nullable|string|in:low,medium,high',
            'potential_saving' => 'nullable|numeric|min:0',
            'estimated_effort' => 'nullable|string|in:quick,moderate,significant',
            'investment_plan_id' => 'nullable|exists:investment_plans,id',
            'due_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['user_id'] = $user->id;
        $data['status'] = 'pending';

        $recommendation = InvestmentRecommendation::create($data);

        // Clear cache
        Cache::forget("investment_recommendations_{$user->id}");

        return response()->json([
            'success' => true,
            'data' => $recommendation,
            'message' => 'Recommendation created successfully',
        ], 201);
    }

    /**
     * Update recommendation status
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $recommendation = InvestmentRecommendation::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$recommendation) {
            return response()->json([
                'success' => false,
                'message' => 'Recommendation not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,in_progress,completed,dismissed',
            'dismissal_reason' => 'required_if:status,dismissed|string|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $status = $request->input('status');
        $recommendation->status = $status;

        if ($status === 'completed') {
            $recommendation->completed_at = now();
        } elseif ($status === 'dismissed') {
            $recommendation->dismissed_at = now();
            $recommendation->dismissal_reason = $request->input('dismissal_reason');
        }

        $recommendation->save();

        // Clear cache
        Cache::forget("investment_recommendations_{$user->id}");

        return response()->json([
            'success' => true,
            'data' => $recommendation,
            'message' => 'Recommendation status updated successfully',
        ]);
    }

    /**
     * Update a recommendation
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $recommendation = InvestmentRecommendation::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$recommendation) {
            return response()->json([
                'success' => false,
                'message' => 'Recommendation not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'category' => 'sometimes|string|in:rebalancing,tax,fees,risk,goal,contribution',
            'priority' => 'sometimes|integer|min:1',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'action_required' => 'sometimes|string',
            'impact_level' => 'nullable|string|in:low,medium,high',
            'potential_saving' => 'nullable|numeric|min:0',
            'estimated_effort' => 'nullable|string|in:quick,moderate,significant',
            'due_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $recommendation->update($validator->validated());

        // Clear cache
        Cache::forget("investment_recommendations_{$user->id}");

        return response()->json([
            'success' => true,
            'data' => $recommendation,
            'message' => 'Recommendation updated successfully',
        ]);
    }

    /**
     * Delete a recommendation
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $recommendation = InvestmentRecommendation::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$recommendation) {
            return response()->json([
                'success' => false,
                'message' => 'Recommendation not found',
            ], 404);
        }

        $recommendation->delete();

        // Clear cache
        Cache::forget("investment_recommendations_{$user->id}");

        return response()->json([
            'success' => true,
            'message' => 'Recommendation deleted successfully',
        ]);
    }

    /**
     * Get recommendations dashboard/summary
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        $recommendations = InvestmentRecommendation::where('user_id', $user->id)
            ->orderBy('priority')
            ->get();

        $stats = [
            'total' => $recommendations->count(),
            'by_status' => [
                'pending' => $recommendations->where('status', 'pending')->count(),
                'in_progress' => $recommendations->where('status', 'in_progress')->count(),
                'completed' => $recommendations->where('status', 'completed')->count(),
                'dismissed' => $recommendations->where('status', 'dismissed')->count(),
            ],
            'by_category' => [
                'rebalancing' => $recommendations->where('category', 'rebalancing')->count(),
                'tax' => $recommendations->where('category', 'tax')->count(),
                'fees' => $recommendations->where('category', 'fees')->count(),
                'risk' => $recommendations->where('category', 'risk')->count(),
                'goal' => $recommendations->where('category', 'goal')->count(),
                'contribution' => $recommendations->where('category', 'contribution')->count(),
            ],
            'by_priority' => [
                'high' => $recommendations->where('priority', '<=', 3)->count(),
                'medium' => $recommendations->whereBetween('priority', [4, 7])->count(),
                'low' => $recommendations->where('priority', '>', 7)->count(),
            ],
            'potential_savings' => [
                'active' => $recommendations->whereIn('status', ['pending', 'in_progress'])->sum('potential_saving'),
                'realized' => $recommendations->where('status', 'completed')->sum('potential_saving'),
                'total' => $recommendations->where('status', '!=', 'dismissed')->sum('potential_saving'),
            ],
        ];

        $highPriority = $recommendations->where('status', '!=', 'completed')
            ->where('status', '!=', 'dismissed')
            ->where('priority', '<=', 3)
            ->take(5)
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'high_priority_recommendations' => $highPriority,
                'recent_completed' => $recommendations->where('status', 'completed')
                    ->sortByDesc('completed_at')
                    ->take(5)
                    ->values(),
            ],
        ]);
    }

    /**
     * Bulk update status for multiple recommendations
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'recommendation_ids' => 'required|array',
            'recommendation_ids.*' => 'integer|exists:investment_recommendations,id',
            'status' => 'required|string|in:pending,in_progress,completed,dismissed',
            'dismissal_reason' => 'required_if:status,dismissed|string|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $ids = $request->input('recommendation_ids');
        $status = $request->input('status');

        $updateData = ['status' => $status];

        if ($status === 'completed') {
            $updateData['completed_at'] = now();
        } elseif ($status === 'dismissed') {
            $updateData['dismissed_at'] = now();
            $updateData['dismissal_reason'] = $request->input('dismissal_reason');
        }

        $updated = InvestmentRecommendation::whereIn('id', $ids)
            ->where('user_id', $user->id)
            ->update($updateData);

        // Clear cache
        Cache::forget("investment_recommendations_{$user->id}");

        return response()->json([
            'success' => true,
            'data' => [
                'updated_count' => $updated,
            ],
            'message' => "{$updated} recommendation(s) updated successfully",
        ]);
    }
}
