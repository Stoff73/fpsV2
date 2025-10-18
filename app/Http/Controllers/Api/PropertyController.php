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
            ->with(['mortgages', 'household', 'trust'])
            ->orderBy('property_type')
            ->orderBy('purchase_date', 'desc')
            ->get();

        $propertySummaries = $properties->map(function ($property) {
            return $this->propertyService->getPropertySummary($property);
        });

        return response()->json([
            'success' => true,
            'data' => [
                'properties' => $propertySummaries,
                'count' => $properties->count(),
            ],
        ]);
    }

    /**
     * Store a new property
     *
     * POST /api/properties
     */
    public function store(StorePropertyRequest $request): JsonResponse
    {
        $user = $request->user();

        $property = Property::create([
            'user_id' => $user->id,
            'household_id' => $user->household_id,
            ...$request->validated(),
        ]);

        $property->load(['mortgages', 'household', 'trust']);
        $summary = $this->propertyService->getPropertySummary($property);

        return response()->json([
            'success' => true,
            'message' => 'Property added successfully',
            'data' => [
                'property' => $summary,
            ],
        ], 201);
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
}
