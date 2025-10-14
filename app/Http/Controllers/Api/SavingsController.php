<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Agents\SavingsAgent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Savings\SavingsAnalysisRequest;
use App\Http\Requests\Savings\ScenarioRequest;
use App\Http\Requests\Savings\StoreSavingsAccountRequest;
use App\Http\Requests\Savings\StoreSavingsGoalRequest;
use App\Http\Requests\Savings\UpdateSavingsAccountRequest;
use App\Http\Requests\Savings\UpdateSavingsGoalRequest;
use App\Models\ExpenditureProfile;
use App\Models\SavingsAccount;
use App\Models\SavingsGoal;
use App\Services\Savings\ISATracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SavingsController extends Controller
{
    public function __construct(
        private SavingsAgent $savingsAgent,
        private ISATracker $isaTracker
    ) {
    }

    /**
     * Get all savings data for authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $accounts = SavingsAccount::where('user_id', $user->id)->get();
        $goals = SavingsGoal::where('user_id', $user->id)->get();
        $expenditureProfile = ExpenditureProfile::where('user_id', $user->id)->first();

        return response()->json([
            'success' => true,
            'data' => [
                'accounts' => $accounts,
                'goals' => $goals,
                'expenditure_profile' => $expenditureProfile,
            ],
        ]);
    }

    /**
     * Run comprehensive savings analysis
     */
    public function analyze(SavingsAnalysisRequest $request): JsonResponse
    {
        $user = $request->user();

        try {
            $analysis = $this->savingsAgent->analyze($user->id);

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
            $analysis = $this->savingsAgent->analyze($user->id);
            $recommendations = $this->savingsAgent->generateRecommendations($analysis);

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
    public function scenarios(ScenarioRequest $request): JsonResponse
    {
        $user = $request->user();

        try {
            $scenarios = $this->savingsAgent->buildScenarios($user->id, $request->validated());

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
     * Get ISA allowance status for a tax year
     */
    public function isaAllowance(Request $request, string $taxYear): JsonResponse
    {
        $user = $request->user();

        try {
            $allowanceStatus = $this->isaTracker->getISAAllowanceStatus($user->id, $taxYear);

            return response()->json([
                'success' => true,
                'data' => $allowanceStatus,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get ISA allowance: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a new savings account
     */
    public function storeAccount(StoreSavingsAccountRequest $request): JsonResponse
    {
        $user = $request->user();

        try {
            $data = $request->validated();
            $data['user_id'] = $user->id;

            $account = SavingsAccount::create($data);

            // Invalidate cache
            Cache::forget("savings_analysis_{$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Savings account created successfully',
                'data' => $account,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create account: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a savings account
     */
    public function updateAccount(UpdateSavingsAccountRequest $request, int $id): JsonResponse
    {
        $user = $request->user();

        try {
            $account = SavingsAccount::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $account->update($request->validated());

            // Invalidate cache
            Cache::forget("savings_analysis_{$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Savings account updated successfully',
                'data' => $account->fresh(),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Account not found or unauthorized',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update account: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a savings account
     */
    public function destroyAccount(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        try {
            $account = SavingsAccount::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $account->delete();

            // Invalidate cache
            Cache::forget("savings_analysis_{$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Savings account deleted successfully',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Account not found or unauthorized',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all goals for authenticated user
     */
    public function indexGoals(Request $request): JsonResponse
    {
        $user = $request->user();
        $goals = SavingsGoal::where('user_id', $user->id)->with('linkedAccount')->get();

        return response()->json([
            'success' => true,
            'data' => $goals,
        ]);
    }

    /**
     * Store a new savings goal
     */
    public function storeGoal(StoreSavingsGoalRequest $request): JsonResponse
    {
        $user = $request->user();

        try {
            $data = $request->validated();
            $data['user_id'] = $user->id;
            $data['current_saved'] = $data['current_saved'] ?? 0.00;

            $goal = SavingsGoal::create($data);

            // Invalidate cache
            Cache::forget("savings_analysis_{$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Savings goal created successfully',
                'data' => $goal->load('linkedAccount'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create goal: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a savings goal
     */
    public function updateGoal(UpdateSavingsGoalRequest $request, int $id): JsonResponse
    {
        $user = $request->user();

        try {
            $goal = SavingsGoal::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $goal->update($request->validated());

            // Invalidate cache
            Cache::forget("savings_analysis_{$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Savings goal updated successfully',
                'data' => $goal->fresh()->load('linkedAccount'),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Goal not found or unauthorized',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update goal: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a savings goal
     */
    public function destroyGoal(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        try {
            $goal = SavingsGoal::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $goal->delete();

            // Invalidate cache
            Cache::forget("savings_analysis_{$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Savings goal deleted successfully',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Goal not found or unauthorized',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete goal: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update progress for a savings goal
     */
    public function updateGoalProgress(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        try {
            $goal = SavingsGoal::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $goal->current_saved = $request->input('amount');
            $goal->save();

            // Invalidate cache
            Cache::forget("savings_analysis_{$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Goal progress updated successfully',
                'data' => $goal->fresh()->load('linkedAccount'),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Goal not found or unauthorized',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update goal progress: ' . $e->getMessage(),
            ], 500);
        }
    }
}
