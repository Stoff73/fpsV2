<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Models\Property;
use App\Services\Property\PropertyService;
use App\Services\Property\PropertyTaxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function __construct(
        private PropertyService $propertyService,
        private PropertyTaxService $propertyTaxService
    ) {}

    /**
     * Get all properties for the authenticated user
     *
     * GET /api/properties
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $properties = Property::where('user_id', $user->id)
            ->with('mortgages')  // Load mortgages relationship for edit functionality
            ->orderBy('property_type')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($properties);
    }

    /**
     * Store a new property
     *
     * POST /api/properties
     */
    public function store(StorePropertyRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Set defaults for optional fields
        $validated['user_id'] = $user->id;
        $validated['household_id'] = $user->household_id;
        $validated['ownership_type'] = $validated['ownership_type'] ?? 'individual';
        $validated['ownership_percentage'] = $validated['ownership_percentage'] ?? 100.00;
        $validated['valuation_date'] = $validated['valuation_date'] ?? now();

        // Handle address field - populate address_line_1 from address if needed
        if (isset($validated['address']) && ! isset($validated['address_line_1'])) {
            $validated['address_line_1'] = $validated['address'];
        }

        // Ensure postcode is never null (database requires NOT NULL)
        if (! isset($validated['postcode']) || $validated['postcode'] === null) {
            $validated['postcode'] = '';
        }

        // Convert rental_income to monthly if provided
        if (isset($validated['rental_income']) && ! isset($validated['monthly_rental_income'])) {
            $validated['monthly_rental_income'] = $validated['rental_income'];
        }

        // For joint ownership, default to 50/50 split if not specified
        if ($validated['ownership_type'] === 'joint' && $validated['ownership_percentage'] == 100.00) {
            $validated['ownership_percentage'] = 50.00;
        }

        // Split the current_value based on ownership percentage
        $totalValue = $validated['current_value'];
        $userOwnershipPercentage = $validated['ownership_percentage'];
        $validated['current_value'] = $totalValue * ($userOwnershipPercentage / 100);

        $property = Property::create($validated);

        // If outstanding_mortgage provided, auto-create a basic mortgage record
        if (isset($validated['outstanding_mortgage']) && $validated['outstanding_mortgage'] > 0) {
            // Split outstanding balance based on ownership percentage
            $userMortgageBalance = $validated['outstanding_mortgage'] * ($userOwnershipPercentage / 100);

            // Split monthly payment based on ownership percentage if provided
            $userMonthlyPayment = isset($validated['mortgage_monthly_payment'])
                ? $validated['mortgage_monthly_payment'] * ($userOwnershipPercentage / 100)
                : 0.00;

            $mortgageData = [
                'property_id' => $property->id,
                'user_id' => $user->id,
                'lender_name' => $validated['mortgage_lender_name'] ?? 'To be completed',
                'mortgage_type' => $validated['mortgage_type'] ?? 'repayment',
                'original_loan_amount' => 0.00,  // Not provided in property form
                'outstanding_balance' => $userMortgageBalance,  // User's share
                'interest_rate' => $validated['mortgage_interest_rate'] ?? 0.0000,
                'rate_type' => $validated['mortgage_rate_type'] ?? 'fixed',
                'monthly_payment' => $userMonthlyPayment,
                'start_date' => $validated['mortgage_start_date'] ?? now(),
                'maturity_date' => $validated['mortgage_maturity_date'] ?? now()->addYears(25),
                'remaining_term_months' => 300,
                'ownership_type' => $validated['ownership_type'],
            ];

            // Add joint ownership fields if applicable
            if ($validated['ownership_type'] === 'joint' && isset($validated['joint_owner_id'])) {
                $jointOwner = \App\Models\User::find($validated['joint_owner_id']);
                $mortgageData['joint_owner_id'] = $validated['joint_owner_id'];
                $mortgageData['joint_owner_name'] = $jointOwner ? $jointOwner->name : null;
            }

            \App\Models\Mortgage::create($mortgageData);
        }

        // If joint ownership, create reciprocal property for joint owner
        if ($validated['ownership_type'] === 'joint' && isset($validated['joint_owner_id'])) {
            $totalMortgage = $validated['outstanding_mortgage'] ?? 0;
            $this->createJointProperty($property, $validated['joint_owner_id'], $validated['ownership_percentage'], $totalValue, $totalMortgage, $validated);
        }

        // Sync rental income to user table
        $this->syncUserRentalIncome($user);

        // Load mortgages relationship before returning
        $property->load('mortgages');

        return response()->json($property, 201);
    }

    /**
     * Get a single property
     *
     * GET /api/properties/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $property = Property::where('id', $id)
            ->where('user_id', $user->id)
            ->with(['mortgages', 'household', 'trust'])
            ->firstOrFail();

        $summary = $this->propertyService->getPropertySummary($property);

        return response()->json([
            'success' => true,
            'data' => [
                'property' => $summary,
            ],
        ]);
    }

    /**
     * Update a property
     *
     * PUT /api/properties/{id}
     */
    public function update(UpdatePropertyRequest $request, int $id): JsonResponse
    {
        $user = $request->user();

        $property = Property::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $property->update($request->validated());

        // If this is a joint property, also update the reciprocal property
        if ($property->joint_owner_id) {
            $reciprocalProperty = Property::where('user_id', $property->joint_owner_id)
                ->where('joint_owner_id', $user->id)
                ->where('address_line_1', $property->address_line_1)
                ->where('postcode', $property->postcode)
                ->first();

            if ($reciprocalProperty) {
                $reciprocalProperty->update($request->validated());
            }
        }

        $property->load(['mortgages', 'household', 'trust']);

        // Sync rental income to user table
        $this->syncUserRentalIncome($user);

        $summary = $this->propertyService->getPropertySummary($property);

        return response()->json([
            'success' => true,
            'message' => 'Property updated successfully',
            'data' => [
                'property' => $summary,
            ],
        ]);
    }

    /**
     * Delete a property
     *
     * DELETE /api/properties/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $property = Property::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // If this is a joint property, also delete the reciprocal property
        if ($property->joint_owner_id) {
            $reciprocalProperty = Property::where('user_id', $property->joint_owner_id)
                ->where('joint_owner_id', $user->id)
                ->where('address_line_1', $property->address_line_1)
                ->where('postcode', $property->postcode)
                ->first();

            if ($reciprocalProperty) {
                $reciprocalProperty->delete();
            }
        }

        $property->delete();

        return response()->json([
            'success' => true,
            'message' => 'Property deleted successfully',
        ]);
    }

    /**
     * Calculate SDLT for a property purchase
     *
     * POST /api/properties/calculate-sdlt
     */
    public function calculateSDLT(Request $request): JsonResponse
    {
        $request->validate([
            'purchase_price' => 'required|numeric|min:0',
            'property_type' => 'required|in:main_residence,secondary_residence,buy_to_let',
            'is_first_home' => 'sometimes|boolean',
        ]);

        $sdlt = $this->propertyTaxService->calculateSDLT(
            $request->input('purchase_price'),
            $request->input('property_type'),
            $request->input('is_first_home', false)
        );

        return response()->json([
            'success' => true,
            'data' => $sdlt,
        ]);
    }

    /**
     * Calculate CGT for a property disposal
     *
     * POST /api/properties/{id}/calculate-cgt
     */
    public function calculateCGT(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'disposal_price' => 'required|numeric|min:0',
            'disposal_costs' => 'sometimes|numeric|min:0',
        ]);

        $property = Property::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $cgt = $this->propertyTaxService->calculateCGT(
            $property,
            $request->input('disposal_price'),
            $request->input('disposal_costs', 0),
            $user
        );

        return response()->json([
            'success' => true,
            'data' => $cgt,
        ]);
    }

    /**
     * Calculate rental income tax for a property
     *
     * POST /api/properties/{id}/rental-income-tax
     */
    public function calculateRentalIncomeTax(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $property = Property::where('id', $id)
            ->where('user_id', $user->id)
            ->with('mortgages')
            ->firstOrFail();

        $rentalTax = $this->propertyTaxService->calculateRentalIncomeTax($property, $user);

        return response()->json([
            'success' => true,
            'data' => $rentalTax,
        ]);
    }

    /**
     * Create a reciprocal property record for joint owner
     */
    private function createJointProperty(Property $originalProperty, int $jointOwnerId, float $ownershipPercentage, float $totalValue, float $totalMortgage = 0, array $validated = []): void
    {
        // Calculate the reciprocal ownership percentage
        $reciprocalPercentage = 100.00 - $ownershipPercentage;

        // Get joint owner's household_id
        $jointOwner = \App\Models\User::findOrFail($jointOwnerId);

        // Create the reciprocal property
        $jointPropertyData = $originalProperty->toArray();

        // Remove auto-generated fields
        unset($jointPropertyData['id'], $jointPropertyData['created_at'], $jointPropertyData['updated_at']);

        // Update fields for joint owner
        $jointPropertyData['user_id'] = $jointOwnerId;
        $jointPropertyData['household_id'] = $jointOwner->household_id;
        $jointPropertyData['ownership_percentage'] = $reciprocalPercentage;
        $jointPropertyData['joint_owner_id'] = $originalProperty->user_id;

        // Calculate joint owner's share of the total value
        $jointPropertyData['current_value'] = $totalValue * ($reciprocalPercentage / 100);

        $jointProperty = Property::create($jointPropertyData);

        // If there's a mortgage, create joint owner's share
        if ($totalMortgage > 0) {
            $jointMortgageBalance = $totalMortgage * ($reciprocalPercentage / 100);

            // Split monthly payment based on reciprocal ownership percentage if provided
            $jointMonthlyPayment = isset($validated['mortgage_monthly_payment'])
                ? $validated['mortgage_monthly_payment'] * ($reciprocalPercentage / 100)
                : 0.00;

            \App\Models\Mortgage::create([
                'property_id' => $jointProperty->id,
                'user_id' => $jointOwnerId,
                'lender_name' => $validated['mortgage_lender_name'] ?? 'To be completed',
                'mortgage_type' => $validated['mortgage_type'] ?? 'repayment',
                'original_loan_amount' => 0.00,  // Not provided in property form
                'outstanding_balance' => $jointMortgageBalance,  // Joint owner's share
                'interest_rate' => $validated['mortgage_interest_rate'] ?? 0.0000,
                'rate_type' => $validated['mortgage_rate_type'] ?? 'fixed',
                'monthly_payment' => $jointMonthlyPayment,
                'start_date' => $validated['mortgage_start_date'] ?? now(),
                'maturity_date' => $validated['mortgage_maturity_date'] ?? now()->addYears(25),
                'remaining_term_months' => 300,
                'ownership_type' => 'joint',
                'joint_owner_id' => $originalProperty->user_id,
                'joint_owner_name' => \App\Models\User::find($originalProperty->user_id)->name ?? null,
            ]);
        }

        // Update original property with joint_owner_id pointing to the reciprocal record
        $originalProperty->update(['joint_owner_id' => $jointOwnerId]);

        // Sync rental income for joint owner as well
        $this->syncUserRentalIncome($jointOwner);
    }

    /**
     * Sync rental income from properties to user table
     */
    private function syncUserRentalIncome(\App\Models\User $user): void
    {
        $annualRentalIncome = $user->properties->sum(function ($property) {
            $monthlyRental = $property->monthly_rental_income ?? 0;
            $ownershipPercentage = $property->ownership_percentage ?? 100;

            return ($monthlyRental * 12) * ($ownershipPercentage / 100);
        });

        $user->update(['annual_rental_income' => $annualRentalIncome]);
    }
}
