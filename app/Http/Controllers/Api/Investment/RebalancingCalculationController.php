<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Investment;

use App\Http\Controllers\Controller;
use App\Models\Investment\InvestmentAccount;
use App\Services\Investment\Rebalancing\DriftAnalyzer;
use App\Services\Investment\Rebalancing\RebalancingCalculator;
use App\Services\Investment\Rebalancing\TaxAwareRebalancer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Portfolio rebalancing calculation controller
 * Handles rebalancing calculations, CGT optimization, and drift analysis
 */
class RebalancingCalculationController extends Controller
{
    public function __construct(
        private RebalancingCalculator $rebalancingCalculator,
        private TaxAwareRebalancer $taxAwareRebalancer,
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
}
