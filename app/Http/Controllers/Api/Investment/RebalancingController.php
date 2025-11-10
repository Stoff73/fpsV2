<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Investment;

use App\Http\Controllers\Controller;
use App\Models\Investment\InvestmentAccount;
use App\Models\Investment\RebalancingAction;
use App\Services\Investment\Rebalancing\DriftAnalyzer;
use App\Services\Investment\Rebalancing\RebalancingCalculator;
use App\Services\Investment\Rebalancing\RebalancingStrategyService;
use App\Services\Investment\Rebalancing\TaxAwareRebalancer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Portfolio rebalancing controller
 * Calculates and manages rebalancing recommendations with CGT optimization
 */
class RebalancingController extends Controller
{
    public function __construct(
        private RebalancingCalculator $rebalancingCalculator,
        private TaxAwareRebalancer $taxAwareRebalancer,
        private RebalancingStrategyService $strategyService,
        private DriftAnalyzer $driftAnalyzer
    ) {}

    /**
     * Calculate rebalancing actions from target weights
     *
     * POST /api/investment/rebalancing/calculate
     */
    public function calculateRebalancing(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'target_weights' => 'required|array|min:2',
            'target_weights.*' => 'required|numeric|min:0|max:1',
            'account_ids' => 'nullable|array',
            'account_ids.*' => 'integer|exists:investment_accounts,id',
            'min_trade_size' => 'nullable|numeric|min:0',
            'optimize_for_cgt' => 'nullable|boolean',
            'cgt_allowance' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:1',
            'loss_carryforward' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $user = $request->user();

        try {
            // Get user's holdings
            $query = InvestmentAccount::where('user_id', $user->id)->with('holdings');

            if (isset($validated['account_ids'])) {
                $query->whereIn('id', $validated['account_ids']);
            }

            $accounts = $query->get();

            if ($accounts->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No investment accounts found',
                ], 404);
            }

            $holdings = $accounts->flatMap->holdings;

            if ($holdings->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No holdings found',
                ], 404);
            }

            // Validate target weights count matches holdings count
            if (count($validated['target_weights']) !== $holdings->count()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Number of target weights must match number of holdings',
                ], 422);
            }

            // Validate weights sum to 1.0
            $weightSum = array_sum($validated['target_weights']);
            if (abs($weightSum - 1.0) > 0.01) {
                return response()->json([
                    'success' => false,
                    'message' => 'Target weights must sum to 1.0 (100%)',
                ], 422);
            }

            // Calculate total cash
            $accountCash = $accounts->sum('cash_balance');

            // Calculate rebalancing
            $options = [
                'min_trade_size' => $validated['min_trade_size'] ?? 100,
                'account_cash' => $accountCash,
            ];

            $result = $this->rebalancingCalculator->calculateRebalancing(
                $holdings,
                $validated['target_weights'],
                $options
            );

            if (! $result['success']) {
                return response()->json($result, 400);
            }

            // Apply CGT optimization if requested
            if ($validated['optimize_for_cgt'] ?? false) {
                $cgtOptions = [
                    'cgt_allowance' => $validated['cgt_allowance'] ?? 12300,
                    'tax_rate' => $validated['tax_rate'] ?? 0.20,
                    'loss_carryforward' => $validated['loss_carryforward'] ?? 0,
                ];

                $cgtResult = $this->taxAwareRebalancer->optimizeForCGT(
                    $result['actions'],
                    $holdings,
                    $cgtOptions
                );

                // Merge CGT analysis into result
                $result['actions'] = $cgtResult['optimized_actions'];
                $result['cgt_analysis'] = $cgtResult['cgt_analysis'];
                $result['tax_loss_opportunities'] = $cgtResult['tax_loss_opportunities'];
                $result['cgt_summary'] = $cgtResult['summary'];
            }

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Rebalancing calculation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate rebalancing',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate rebalancing from optimization result
     *
     * POST /api/investment/rebalancing/from-optimization
     */
    public function calculateFromOptimization(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'weights' => 'required|array|min:2',
            'weights.*' => 'required|numeric|min:0|max:1',
            'labels' => 'nullable|array',
            'account_ids' => 'nullable|array',
            'account_ids.*' => 'integer|exists:investment_accounts,id',
            'min_trade_size' => 'nullable|numeric|min:0',
            'optimize_for_cgt' => 'nullable|boolean',
            'cgt_allowance' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $user = $request->user();

        // Forward to calculateRebalancing with target_weights
        $request->merge(['target_weights' => $validated['weights']]);

        return $this->calculateRebalancing($request);
    }

    /**
     * Save rebalancing actions to database
     *
     * POST /api/investment/rebalancing/save
     */
    public function saveRebalancingActions(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'actions' => 'required|array|min:1',
            'actions.*.holding_id' => 'required|integer|exists:holdings,id',
            'actions.*.investment_account_id' => 'nullable|integer|exists:investment_accounts,id',
            'actions.*.action_type' => 'required|in:buy,sell',
            'actions.*.security_name' => 'required|string',
            'actions.*.ticker' => 'nullable|string',
            'actions.*.isin' => 'nullable|string',
            'actions.*.shares_to_trade' => 'required|numeric|min:0',
            'actions.*.trade_value' => 'required|numeric|min:0',
            'actions.*.current_price' => 'required|numeric|min:0',
            'actions.*.current_holding' => 'nullable|numeric|min:0',
            'actions.*.target_value' => 'required|numeric|min:0',
            'actions.*.target_weight' => 'required|numeric|min:0|max:1',
            'actions.*.priority' => 'nullable|integer|min:1|max:5',
            'actions.*.rationale' => 'nullable|string',
            'actions.*.cgt_cost_basis' => 'nullable|numeric',
            'actions.*.cgt_gain_or_loss' => 'nullable|numeric',
            'actions.*.cgt_liability' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $user = $request->user();

        try {
            $savedActions = [];

            foreach ($validated['actions'] as $actionData) {
                $actionData['user_id'] = $user->id;
                $actionData['status'] = 'pending';

                $action = RebalancingAction::create($actionData);
                $savedActions[] = $action;
            }

            return response()->json([
                'success' => true,
                'data' => $savedActions,
                'message' => sprintf('%d rebalancing action(s) saved', count($savedActions)),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to save rebalancing actions', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save rebalancing actions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's pending rebalancing actions
     *
     * GET /api/investment/rebalancing/actions
     */
    public function getRebalancingActions(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'status' => 'nullable|in:pending,executed,cancelled,expired',
            'action_type' => 'nullable|in:buy,sell',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $query = RebalancingAction::where('user_id', $user->id)
            ->with(['holding', 'investmentAccount'])
            ->orderBy('priority', 'asc')
            ->orderBy('created_at', 'desc');

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (isset($validated['action_type'])) {
            $query->where('action_type', $validated['action_type']);
        }

        $actions = $query->get();

        return response()->json([
            'success' => true,
            'data' => $actions,
            'count' => $actions->count(),
        ]);
    }

    /**
     * Update rebalancing action status
     *
     * PUT /api/investment/rebalancing/actions/{id}
     */
    public function updateRebalancingAction(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $action = RebalancingAction::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (! $action) {
            return response()->json([
                'success' => false,
                'message' => 'Rebalancing action not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,executed,cancelled,expired',
            'executed_at' => 'nullable|date',
            'executed_price' => 'nullable|numeric|min:0',
            'executed_shares' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
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
            $action->update($validated);

            return response()->json([
                'success' => true,
                'data' => $action->fresh(),
                'message' => 'Rebalancing action updated',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update rebalancing action', [
                'user_id' => $user->id,
                'action_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update rebalancing action',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete rebalancing action
     *
     * DELETE /api/investment/rebalancing/actions/{id}
     */
    public function deleteRebalancingAction(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $action = RebalancingAction::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (! $action) {
            return response()->json([
                'success' => false,
                'message' => 'Rebalancing action not found',
            ], 404);
        }

        try {
            $action->delete();

            return response()->json([
                'success' => true,
                'message' => 'Rebalancing action deleted',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete rebalancing action', [
                'user_id' => $user->id,
                'action_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete rebalancing action',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Compare CGT between different rebalancing strategies
     *
     * POST /api/investment/rebalancing/compare-cgt
     */
    public function compareCGTStrategies(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'strategy_1_weights' => 'required|array|min:2',
            'strategy_1_weights.*' => 'required|numeric|min:0|max:1',
            'strategy_2_weights' => 'required|array|min:2',
            'strategy_2_weights.*' => 'required|numeric|min:0|max:1',
            'account_ids' => 'nullable|array',
            'cgt_allowance' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $user = $request->user();

        try {
            // Get holdings
            $query = InvestmentAccount::where('user_id', $user->id)->with('holdings');

            if (isset($validated['account_ids'])) {
                $query->whereIn('id', $validated['account_ids']);
            }

            $accounts = $query->get();
            $holdings = $accounts->flatMap->holdings;

            if ($holdings->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No holdings found',
                ], 404);
            }

            // Calculate actions for both strategies
            $options = ['min_trade_size' => 100];

            $strategy1Actions = $this->rebalancingCalculator->calculateRebalancing(
                $holdings,
                $validated['strategy_1_weights'],
                $options
            )['actions'];

            $strategy2Actions = $this->rebalancingCalculator->calculateRebalancing(
                $holdings,
                $validated['strategy_2_weights'],
                $options
            )['actions'];

            // Compare CGT
            $cgtOptions = [
                'cgt_allowance' => $validated['cgt_allowance'] ?? 12300,
                'tax_rate' => $validated['tax_rate'] ?? 0.20,
            ];

            $comparison = $this->taxAwareRebalancer->compareStrategies(
                $holdings,
                $strategy1Actions,
                $strategy2Actions,
                $cgtOptions
            );

            return response()->json([
                'success' => true,
                'data' => $comparison,
            ]);
        } catch (\Exception $e) {
            Log::error('CGT strategy comparison failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to compare CGT strategies',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate rebalancing within CGT allowance
     *
     * POST /api/investment/rebalancing/within-cgt-allowance
     */
    public function rebalanceWithinCGTAllowance(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'target_weights' => 'required|array|min:2',
            'target_weights.*' => 'required|numeric|min:0|max:1',
            'account_ids' => 'nullable|array',
            'cgt_allowance' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $user = $request->user();

        try {
            // Get holdings
            $query = InvestmentAccount::where('user_id', $user->id)->with('holdings');

            if (isset($validated['account_ids'])) {
                $query->whereIn('id', $validated['account_ids']);
            }

            $accounts = $query->get();
            $holdings = $accounts->flatMap->holdings;

            if ($holdings->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No holdings found',
                ], 404);
            }

            // Calculate initial actions
            $options = ['min_trade_size' => 100];

            $actions = $this->rebalancingCalculator->calculateRebalancing(
                $holdings,
                $validated['target_weights'],
                $options
            )['actions'];

            // Constrain to CGT allowance
            $cgtOptions = [
                'cgt_allowance' => $validated['cgt_allowance'] ?? 12300,
                'tax_rate' => $validated['tax_rate'] ?? 0.20,
            ];

            $result = $this->taxAwareRebalancer->rebalanceWithinCGTAllowance(
                $actions,
                $holdings,
                $cgtOptions
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('CGT-constrained rebalancing failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate CGT-constrained rebalancing',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Analyze portfolio drift from target allocation
     *
     * POST /api/investment/rebalancing/analyze-drift
     */
    public function analyzeDrift(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'target_allocation' => 'required|array|min:2',
            'target_allocation.equities' => 'required|numeric|min:0|max:100',
            'target_allocation.bonds' => 'required|numeric|min:0|max:100',
            'target_allocation.cash' => 'required|numeric|min:0|max:100',
            'target_allocation.alternatives' => 'required|numeric|min:0|max:100',
            'account_ids' => 'nullable|array',
            'account_ids.*' => 'integer|exists:investment_accounts,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $user = $request->user();

        try {
            // Get holdings
            $query = InvestmentAccount::where('user_id', $user->id)->with('holdings');

            if (isset($validated['account_ids'])) {
                $query->whereIn('id', $validated['account_ids']);
            }

            $accounts = $query->get();
            $holdings = $accounts->flatMap->holdings;

            if ($holdings->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No holdings found',
                ], 404);
            }

            $result = $this->driftAnalyzer->analyzeDrift($holdings, $validated['target_allocation']);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Drift analysis failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to analyze drift',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Evaluate rebalancing strategies
     *
     * POST /api/investment/rebalancing/evaluate-strategies
     */
    public function evaluateStrategies(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'target_allocation' => 'required|array|min:2',
            'target_allocation.equities' => 'required|numeric|min:0|max:100',
            'target_allocation.bonds' => 'required|numeric|min:0|max:100',
            'target_allocation.cash' => 'required|numeric|min:0|max:100',
            'target_allocation.alternatives' => 'required|numeric|min:0|max:100',
            'threshold_percent' => 'nullable|numeric|min:1|max:50',
            'tolerance_band_percent' => 'nullable|numeric|min:1|max:50',
            'last_rebalance_date' => 'nullable|date',
            'frequency' => 'nullable|in:quarterly,semi_annual,annual,biennial',
            'account_ids' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $user = $request->user();

        try {
            // Get current allocation
            $query = InvestmentAccount::where('user_id', $user->id)->with('holdings');

            if (isset($validated['account_ids'])) {
                $query->whereIn('id', $validated['account_ids']);
            }

            $accounts = $query->get();
            $holdings = $accounts->flatMap->holdings;

            if ($holdings->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No holdings found',
                ], 404);
            }

            // Calculate current allocation
            $totalValue = $holdings->sum('current_value');
            $currentAllocation = [];

            foreach ($holdings as $holding) {
                $assetClass = $this->normalizeAssetClass($holding->asset_type);
                if (! isset($currentAllocation[$assetClass])) {
                    $currentAllocation[$assetClass] = 0.0;
                }
                $currentAllocation[$assetClass] += ($holding->current_value / $totalValue) * 100;
            }

            // Evaluate strategies
            $options = [
                'threshold_percent' => $validated['threshold_percent'] ?? 5.0,
                'tolerance_band_percent' => $validated['tolerance_band_percent'] ?? 5.0,
                'last_rebalance_date' => $validated['last_rebalance_date'] ?? date('Y-m-d', strtotime('-6 months')),
                'frequency' => $validated['frequency'] ?? 'annual',
            ];

            $result = $this->strategyService->compareStrategies(
                $currentAllocation,
                $validated['target_allocation'],
                $options
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Strategy evaluation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to evaluate strategies',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Recommend optimal rebalancing frequency
     *
     * POST /api/investment/rebalancing/recommend-frequency
     */
    public function recommendFrequency(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'portfolio_value' => 'required|numeric|min:0',
            'risk_level' => 'required|integer|min:1|max:5',
            'expected_volatility' => 'required|numeric|min:0|max:100',
            'is_taxable' => 'nullable|boolean',
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
            $result = $this->strategyService->recommendRebalancingFrequency(
                $validated['portfolio_value'],
                $validated['risk_level'],
                $validated['expected_volatility'],
                $validated['is_taxable'] ?? true
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Frequency recommendation failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to recommend frequency',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Evaluate threshold-based rebalancing strategy
     *
     * POST /api/investment/rebalancing/threshold-strategy
     */
    public function evaluateThresholdStrategy(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'target_allocation' => 'required|array|min:2',
            'threshold_percent' => 'required|numeric|min:1|max:50',
            'account_ids' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $user = $request->user();

        try {
            // Get current allocation
            $query = InvestmentAccount::where('user_id', $user->id)->with('holdings');

            if (isset($validated['account_ids'])) {
                $query->whereIn('id', $validated['account_ids']);
            }

            $holdings = $query->get()->flatMap->holdings;

            if ($holdings->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No holdings found',
                ], 404);
            }

            $totalValue = $holdings->sum('current_value');
            $currentAllocation = [];

            foreach ($holdings as $holding) {
                $assetClass = $this->normalizeAssetClass($holding->asset_type);
                if (! isset($currentAllocation[$assetClass])) {
                    $currentAllocation[$assetClass] = 0.0;
                }
                $currentAllocation[$assetClass] += ($holding->current_value / $totalValue) * 100;
            }

            $result = $this->strategyService->evaluateThresholdStrategy(
                $currentAllocation,
                $validated['target_allocation'],
                $validated['threshold_percent']
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Threshold strategy evaluation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to evaluate threshold strategy',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Evaluate calendar-based rebalancing strategy
     *
     * POST /api/investment/rebalancing/calendar-strategy
     */
    public function evaluateCalendarStrategy(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'last_rebalance_date' => 'required|date',
            'frequency' => 'required|in:quarterly,semi_annual,annual,biennial',
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
            $result = $this->strategyService->evaluateCalendarStrategy(
                $validated['last_rebalance_date'],
                $validated['frequency']
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Calendar strategy evaluation failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to evaluate calendar strategy',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Evaluate opportunistic rebalancing with cash flow
     *
     * POST /api/investment/rebalancing/opportunistic-strategy
     */
    public function evaluateOpportunisticStrategy(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'target_allocation' => 'required|array|min:2',
            'new_cash_flow' => 'required|numeric',
            'account_ids' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $user = $request->user();

        try {
            // Get current allocation
            $query = InvestmentAccount::where('user_id', $user->id)->with('holdings');

            if (isset($validated['account_ids'])) {
                $query->whereIn('id', $validated['account_ids']);
            }

            $holdings = $query->get()->flatMap->holdings;

            if ($holdings->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No holdings found',
                ], 404);
            }

            $totalValue = $holdings->sum('current_value');
            $currentAllocation = [];

            foreach ($holdings as $holding) {
                $assetClass = $this->normalizeAssetClass($holding->asset_type);
                if (! isset($currentAllocation[$assetClass])) {
                    $currentAllocation[$assetClass] = 0.0;
                }
                $currentAllocation[$assetClass] += ($holding->current_value / $totalValue) * 100;
            }

            $result = $this->strategyService->evaluateOpportunisticStrategy(
                $currentAllocation,
                $validated['target_allocation'],
                $validated['new_cash_flow'],
                $totalValue
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Opportunistic strategy evaluation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to evaluate opportunistic strategy',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Normalize asset class names
     */
    private function normalizeAssetClass(string $assetType): string
    {
        return match (strtolower($assetType)) {
            'uk_equity', 'global_equity', 'emerging_markets', 'equity', 'stock' => 'equities',
            'uk_bonds', 'global_bonds', 'government_bonds', 'corporate_bonds', 'bond' => 'bonds',
            'cash', 'money_market' => 'cash',
            'property', 'real_estate', 'commodities', 'alternative' => 'alternatives',
            default => $assetType,
        };
    }
}
