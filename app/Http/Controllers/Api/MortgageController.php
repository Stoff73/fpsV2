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

        $validated = $request->validated();

        // Set default ownership type if not provided
        $validated['ownership_type'] = $validated['ownership_type'] ?? 'individual';

        // Calculate remaining_term_months if not provided
        if (!isset($validated['remaining_term_months'])) {
            $startDate = new \DateTime($validated['start_date']);
            $maturityDate = new \DateTime($validated['maturity_date']);
            $interval = $startDate->diff($maturityDate);
            $validated['remaining_term_months'] = ($interval->y * 12) + $interval->m;
        }

        $mortgage = Mortgage::create([
            'property_id' => $propertyId,
            'user_id' => $user->id,
            ...$validated,
        ]);

        // If joint ownership, create reciprocal mortgage for joint owner
        if (isset($validated['ownership_type']) && $validated['ownership_type'] === 'joint' && isset($validated['joint_owner_id'])) {
            $this->createJointMortgage($mortgage, $validated['joint_owner_id'], $property);
        }

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

        $validated = $request->validated();

        // Calculate remaining_term_months if not provided but dates are
        if (!isset($validated['remaining_term_months']) && isset($validated['start_date']) && isset($validated['maturity_date'])) {
            $startDate = new \DateTime($validated['start_date']);
            $maturityDate = new \DateTime($validated['maturity_date']);
            $interval = $startDate->diff($maturityDate);
            $validated['remaining_term_months'] = ($interval->y * 12) + $interval->m;
        }

        $mortgage->update($validated);

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

    /**
     * Create a reciprocal mortgage record for joint owner
     */
    private function createJointMortgage(Mortgage $originalMortgage, int $jointOwnerId, Property $property): void
    {
        // Get joint owner
        $jointOwner = \App\Models\User::findOrFail($jointOwnerId);

        // Find the joint owner's corresponding property record
        // (This should exist if the property is joint-owned)
        $jointProperty = Property::where('user_id', $jointOwnerId)
            ->where('joint_owner_id', $property->user_id)
            ->where('address_line_1', $property->address_line_1)
            ->where('postcode', $property->postcode)
            ->first();

        // If no joint property found, we can't create the mortgage
        if (!$jointProperty) {
            \Log::warning("Joint property not found for user {$jointOwnerId}. Mortgage will not be duplicated.");
            return;
        }

        // Create the reciprocal mortgage
        $jointMortgageData = $originalMortgage->toArray();

        // Remove auto-generated fields
        unset($jointMortgageData['id'], $jointMortgageData['created_at'], $jointMortgageData['updated_at']);

        // Update fields for joint owner
        $jointMortgageData['user_id'] = $jointOwnerId;
        $jointMortgageData['property_id'] = $jointProperty->id;
        $jointMortgageData['joint_owner_id'] = $originalMortgage->user_id;

        $jointMortgage = Mortgage::create($jointMortgageData);

        // Update original mortgage with joint_owner_id
        $originalMortgage->update(['joint_owner_id' => $jointOwnerId]);
    }
}
