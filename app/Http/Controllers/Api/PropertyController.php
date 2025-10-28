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
            $validated['annual_rental_income'] = $validated['rental_income'] * 12;
        }

        // For joint ownership, default to 50/50 split if not specified
        if ($validated['ownership_type'] === 'joint' && $validated['ownership_percentage'] == 100.00) {
            $validated['ownership_percentage'] = 50.00;
        }

        $property = Property::create($validated);

        // If outstanding_mortgage provided, auto-create a basic mortgage record
        if (isset($validated['outstanding_mortgage']) && $validated['outstanding_mortgage'] > 0) {
            \App\Models\Mortgage::create([
                'property_id' => $property->id,
                'user_id' => $user->id,
                'lender_name' => 'To be completed',
                'mortgage_type' => 'repayment',
                'original_loan_amount' => $validated['outstanding_mortgage'],
                'outstanding_balance' => $validated['outstanding_mortgage'],
                'interest_rate' => 0.0000,
                'rate_type' => 'fixed',
                'monthly_payment' => 0.00,
                'start_date' => now(),
                'maturity_date' => now()->addYears(25),
                'remaining_term_months' => 300,
            ]);
        }

        // If joint ownership, create reciprocal property for joint owner
        if ($validated['ownership_type'] === 'joint' && isset($validated['joint_owner_id'])) {
            $this->createJointProperty($property, $validated['joint_owner_id'], $validated['ownership_percentage']);
        }

        // Sync rental income to user table
        $this->syncUserRentalIncome($user);

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
            'property_type' => 'required|in:main_residence,second_home,buy_to_let',
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
    private function createJointProperty(Property $originalProperty, int $jointOwnerId, float $ownershipPercentage): void
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

        $jointProperty = Property::create($jointPropertyData);

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
