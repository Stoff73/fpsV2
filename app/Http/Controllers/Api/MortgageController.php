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

        // Set sensible defaults for optional fields
        $validated['ownership_type'] = $validated['ownership_type'] ?? 'individual';
        $validated['lender_name'] = $validated['lender_name'] ?? 'To be completed';
        $validated['mortgage_type'] = $validated['mortgage_type'] ?? 'repayment';
        $validated['interest_rate'] = $validated['interest_rate'] ?? 0.0000;
        $validated['rate_type'] = $validated['rate_type'] ?? 'fixed';
        $validated['start_date'] = $validated['start_date'] ?? now();
        // Note: original_loan_amount is optional and should NOT be auto-filled if not provided

        // Calculate maturity date if not provided (assume 25 year term)
        if (! isset($validated['maturity_date'])) {
            $validated['maturity_date'] = now()->addYears(25);
        }

        // Calculate remaining_term_months if not provided but dates are available
        if (! isset($validated['remaining_term_months']) &&
            isset($validated['start_date']) && $validated['start_date'] &&
            isset($validated['maturity_date']) && $validated['maturity_date']) {
            // Convert to Carbon if not already
            $startDate = $validated['start_date'] instanceof \Carbon\Carbon
                ? $validated['start_date']
                : \Carbon\Carbon::parse($validated['start_date']);
            $maturityDate = $validated['maturity_date'] instanceof \Carbon\Carbon
                ? $validated['maturity_date']
                : \Carbon\Carbon::parse($validated['maturity_date']);

            $validated['remaining_term_months'] = $startDate->diffInMonths($maturityDate);
        } else {
            $validated['remaining_term_months'] = $validated['remaining_term_months'] ?? 300; // 25 years default
        }

        // For joint or tenants_in_common ownership, split financial values by ownership percentage
        if (isset($validated['ownership_type']) && in_array($validated['ownership_type'], ['joint', 'tenants_in_common'])) {
            // Get ownership percentage from property, default to 50% for joint
            $ownershipPercentage = $property->ownership_percentage ?? 50;
            $validated['outstanding_balance'] = $validated['outstanding_balance'] * ($ownershipPercentage / 100);

            // Also split monthly_payment and original_loan_amount
            if (isset($validated['monthly_payment'])) {
                $validated['monthly_payment'] = $validated['monthly_payment'] * ($ownershipPercentage / 100);
            }
            if (isset($validated['original_loan_amount'])) {
                $validated['original_loan_amount'] = $validated['original_loan_amount'] * ($ownershipPercentage / 100);
            }
        }

        $mortgage = Mortgage::create([
            'property_id' => $propertyId,
            'user_id' => $user->id,
            ...$validated,
        ]);

        // If joint or tenants_in_common ownership, create reciprocal mortgage for joint owner
        if (isset($validated['ownership_type']) && in_array($validated['ownership_type'], ['joint', 'tenants_in_common']) && isset($validated['joint_owner_id'])) {
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
     * PUT /api/properties/{propertyId}/mortgages/{mortgageId}
     */
    public function update(UpdateMortgageRequest $request, ?int $propertyId = null, ?int $mortgageId = null): JsonResponse
    {
        $user = $request->user();

        // Handle both route patterns
        $id = $mortgageId ?? $propertyId;

        $mortgage = Mortgage::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $validated = $request->validated();

        // Calculate remaining_term_months if not provided but dates are
        if (! isset($validated['remaining_term_months']) && isset($validated['start_date']) && isset($validated['maturity_date'])) {
            $startDate = new \DateTime($validated['start_date']);
            $maturityDate = new \DateTime($validated['maturity_date']);
            $interval = $startDate->diff($maturityDate);
            $validated['remaining_term_months'] = ($interval->y * 12) + $interval->m;
        }

        // Get the property to check ownership type
        $property = $mortgage->property;

        // Check if this is a joint mortgage - check BOTH mortgage AND property ownership type
        // Some mortgages might have ownership_type='individual' but belong to a joint property
        $isJointMortgage = (
            (in_array($mortgage->ownership_type, ['joint', 'tenants_in_common']) && $mortgage->joint_owner_id) ||
            (in_array($property->ownership_type, ['joint', 'tenants_in_common']) && $property->joint_owner_id)
        );

        if ($isJointMortgage) {
            // Use property's joint_owner_id if mortgage doesn't have one
            $jointOwnerId = $mortgage->joint_owner_id ?? $property->joint_owner_id;

            // Find the reciprocal property
            $reciprocalProperty = Property::where('user_id', $jointOwnerId)
                ->where('joint_owner_id', $user->id)
                ->where('address_line_1', $property->address_line_1)
                ->where('postcode', $property->postcode)
                ->first();

            $reciprocalMortgage = null;
            if ($reciprocalProperty) {
                $reciprocalMortgage = Mortgage::where('property_id', $reciprocalProperty->id)
                    ->where('user_id', $jointOwnerId)
                    ->first();
            }

            if ($reciprocalMortgage) {
                // Capture before values for logging
                $beforeValues = [
                    'outstanding_balance' => [
                        'user_share' => $mortgage->outstanding_balance,
                        'joint_owner_share' => $reciprocalMortgage->outstanding_balance,
                        'full_balance' => $mortgage->outstanding_balance + $reciprocalMortgage->outstanding_balance,
                    ],
                ];

                // Get ownership percentages from the properties
                $userPercentage = $property->ownership_percentage;
                $jointOwnerPercentage = $reciprocalProperty->ownership_percentage;

                // Prepare reciprocal update data
                $reciprocalData = $validated;
                $fieldsChanged = [];

                // If outstanding_balance is provided, it's the FULL balance - split it
                if (isset($validated['outstanding_balance'])) {
                    $fullBalance = $validated['outstanding_balance'];
                    $validated['outstanding_balance'] = $fullBalance * ($userPercentage / 100);
                    $reciprocalData['outstanding_balance'] = $fullBalance * ($jointOwnerPercentage / 100);
                    $fieldsChanged[] = 'outstanding_balance';
                }

                // If monthly_payment is provided, it's the FULL payment - split it
                if (isset($validated['monthly_payment'])) {
                    $fullPayment = $validated['monthly_payment'];
                    $validated['monthly_payment'] = $fullPayment * ($userPercentage / 100);
                    $reciprocalData['monthly_payment'] = $fullPayment * ($jointOwnerPercentage / 100);
                    $fieldsChanged[] = 'monthly_payment';
                }

                // If original_loan_amount is provided, it's the FULL amount - split it
                if (isset($validated['original_loan_amount'])) {
                    $fullLoan = $validated['original_loan_amount'];
                    $validated['original_loan_amount'] = $fullLoan * ($userPercentage / 100);
                    $reciprocalData['original_loan_amount'] = $fullLoan * ($jointOwnerPercentage / 100);
                    $fieldsChanged[] = 'original_loan_amount';
                }

                // Update reciprocal mortgage
                $reciprocalMortgage->update($reciprocalData);

                // Log the joint account edit if any financial fields changed
                if (! empty($fieldsChanged)) {
                    // Capture after values for logging
                    $afterValues = [
                        'outstanding_balance' => [
                            'user_share' => $validated['outstanding_balance'] ?? $mortgage->outstanding_balance,
                            'joint_owner_share' => $reciprocalData['outstanding_balance'] ?? $reciprocalMortgage->outstanding_balance,
                            'full_balance' => ($validated['outstanding_balance'] ?? $mortgage->outstanding_balance) + ($reciprocalData['outstanding_balance'] ?? $reciprocalMortgage->outstanding_balance),
                        ],
                    ];

                    \App\Models\JointAccountLog::logEdit(
                        $user->id,
                        $jointOwnerId,
                        $mortgage,
                        [
                            'before' => $beforeValues,
                            'after' => $afterValues,
                            'fields_changed' => $fieldsChanged,
                        ],
                        'update'
                    );
                }
            }
        }

        // Update the user's mortgage
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
     * DELETE /api/properties/{propertyId}/mortgages/{mortgageId}
     */
    public function destroy(Request $request, ?int $propertyId = null, ?int $mortgageId = null): JsonResponse
    {
        // Handle both route patterns
        $id = $mortgageId ?? $propertyId;

        $user = $request->user();

        $mortgage = Mortgage::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // If this is a joint mortgage, also delete the reciprocal mortgage
        if ($mortgage->joint_owner_id) {
            // Find the reciprocal mortgage - match on user IDs AND lender + balance to ensure correct mortgage
            $reciprocalMortgage = Mortgage::where('user_id', $mortgage->joint_owner_id)
                ->where('joint_owner_id', $user->id)
                ->where('lender_name', $mortgage->lender_name)
                ->where('outstanding_balance', $mortgage->outstanding_balance)
                ->first();

            if ($reciprocalMortgage) {
                $reciprocalMortgage->delete();
            }
        }

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
            'mortgage_type' => 'required|in:repayment,interest_only,mixed',
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
        if (! $jointProperty) {
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

        // Calculate joint owner's share based on ownership percentages
        // Original mortgage already has user's share (split in store())
        // Joint owner's share = user's share * (joint_owner% / user%)
        $userPercentage = $property->ownership_percentage ?? 50;
        $jointOwnerPercentage = $jointProperty->ownership_percentage ?? 50;

        // Prevent division by zero
        if ($userPercentage > 0) {
            $ratio = $jointOwnerPercentage / $userPercentage;

            // Recalculate joint owner's share of financial values
            $jointMortgageData['outstanding_balance'] = $originalMortgage->outstanding_balance * $ratio;
            if ($originalMortgage->monthly_payment) {
                $jointMortgageData['monthly_payment'] = $originalMortgage->monthly_payment * $ratio;
            }
            if ($originalMortgage->original_loan_amount) {
                $jointMortgageData['original_loan_amount'] = $originalMortgage->original_loan_amount * $ratio;
            }
        }

        $jointMortgage = Mortgage::create($jointMortgageData);

        // Update original mortgage with joint_owner_id
        $originalMortgage->update(['joint_owner_id' => $jointOwnerId]);
    }
}
