<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMortgageRequest;
use App\Http\Requests\UpdateMortgageRequest;
use App\Models\Mortgage;
use App\Models\Property;
use App\Services\Property\MortgageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MortgageController extends Controller
{
    public function __construct(
        private MortgageService $mortgageService
    ) {}

    /**
     * Get all mortgages for a property
     *
     * GET /api/properties/{propertyId}/mortgages
     */
    public function index(Request $request, int $propertyId): JsonResponse
    {
        $user = $request->user();

        // Verify property ownership
        $property = Property::where('id', $propertyId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $mortgages = Mortgage::where('property_id', $propertyId)
            ->orderBy('start_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'mortgages' => $mortgages,
                'count' => $mortgages->count(),
            ],
        ]);
    }

    /**
     * Store a new mortgage for a property
     *
     * POST /api/properties/{propertyId}/mortgages
     */
    public function store(StoreMortgageRequest $request, int $propertyId): JsonResponse
    {
        $user = $request->user();

        // Verify property ownership
        $property = Property::where('id', $propertyId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $mortgage = Mortgage::create([
            'property_id' => $propertyId,
            'user_id' => $user->id,
            ...$request->validated(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mortgage added successfully',
            'data' => [
                'mortgage' => $mortgage,
            ],
        ], 201);
    }

    /**
     * Get a single mortgage
     *
     * GET /api/mortgages/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $mortgage = Mortgage::where('id', $id)
            ->where('user_id', $user->id)
            ->with('property')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'mortgage' => $mortgage,
            ],
        ]);
    }

    /**
     * Update a mortgage
     *
     * PUT /api/mortgages/{id}
     */
    public function update(UpdateMortgageRequest $request, int $id): JsonResponse
    {
        $user = $request->user();

        $mortgage = Mortgage::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $mortgage->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Mortgage updated successfully',
            'data' => [
                'mortgage' => $mortgage,
            ],
        ]);
    }

    /**
     * Delete a mortgage
     *
     * DELETE /api/mortgages/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $mortgage = Mortgage::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $mortgage->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mortgage deleted successfully',
        ]);
    }

    /**
     * Generate amortization schedule for a mortgage
     *
     * GET /api/mortgages/{id}/amortization-schedule
     */
    public function amortizationSchedule(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $mortgage = Mortgage::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $schedule = $this->mortgageService->generateAmortizationSchedule($mortgage);

        return response()->json([
            'success' => true,
            'data' => $schedule,
        ]);
    }

    /**
     * Calculate monthly payment for mortgage parameters
     *
     * POST /api/mortgages/calculate-payment
     */
    public function calculatePayment(Request $request): JsonResponse
    {
        $request->validate([
            'loan_amount' => 'required|numeric|min:0',
            'annual_interest_rate' => 'required|numeric|min:0|max:100',
            'term_months' => 'required|integer|min:1',
            'mortgage_type' => 'required|in:repayment,interest_only',
        ]);

        $monthlyPayment = $this->mortgageService->calculateMonthlyPayment(
            $request->input('loan_amount'),
            $request->input('annual_interest_rate'),
            $request->input('term_months'),
            $request->input('mortgage_type')
        );

        return response()->json([
            'success' => true,
            'data' => [
                'loan_amount' => $request->input('loan_amount'),
                'annual_interest_rate' => $request->input('annual_interest_rate'),
                'term_months' => $request->input('term_months'),
                'term_years' => round($request->input('term_months') / 12, 1),
                'mortgage_type' => $request->input('mortgage_type'),
                'monthly_payment' => $monthlyPayment,
                'annual_payment' => $monthlyPayment * 12,
                'total_repayment' => $monthlyPayment * $request->input('term_months'),
            ],
        ]);
    }
}
