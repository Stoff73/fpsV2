<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Mortgage;
use App\Models\Property;
use App\Models\User;
use App\Services\Property\PropertyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyServiceTest extends TestCase
{
    use RefreshDatabase;

    private PropertyService $propertyService;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->propertyService = new PropertyService;
        $this->user = User::factory()->create();
    }

    public function test_calculate_equity_with_no_mortgage(): void
    {
        $property = Property::factory()->create([
            'user_id' => $this->user->id,
            'ownership_type' => 'individual',
            'current_value' => 500000,
            'ownership_percentage' => 100,
        ]);

        $equity = $this->propertyService->calculateEquity($property);

        expect($equity)->toBe(500000.0);
    }

    public function test_calculate_equity_with_mortgage(): void
    {
        $property = Property::factory()->create([
            'user_id' => $this->user->id,
            'ownership_type' => 'individual',
            'current_value' => 500000,
            'ownership_percentage' => 100,
        ]);

        Mortgage::factory()->create([
            'property_id' => $property->id,
            'user_id' => $this->user->id,
            'outstanding_balance' => 200000,
        ]);

        $equity = $this->propertyService->calculateEquity($property);

        expect($equity)->toBe(300000.0);
    }

    public function test_calculate_equity_with_joint_ownership(): void
    {
        $property = Property::factory()->create([
            'user_id' => $this->user->id,
            'ownership_type' => 'joint',
            'current_value' => 600000,
            'ownership_percentage' => 50,
        ]);

        Mortgage::factory()->create([
            'property_id' => $property->id,
            'user_id' => $this->user->id,
            'outstanding_balance' => 200000,
        ]);

        $equity = $this->propertyService->calculateEquity($property);

        // 50% of £600k = £300k value
        // 50% of £200k mortgage = £100k liability
        // Equity = £300k - £100k = £200k
        expect($equity)->toBe(200000.0);
    }

    public function test_calculate_total_annual_costs(): void
    {
        $property = Property::factory()->create([
            'user_id' => $this->user->id,
            'annual_service_charge' => 1200,
            'annual_ground_rent' => 300,
            'annual_insurance' => 500,
            'annual_maintenance_reserve' => 1000,
            'other_annual_costs' => 500,
        ]);

        $totalCosts = $this->propertyService->calculateTotalAnnualCosts($property);

        expect($totalCosts)->toBe(3500.0);
    }

    public function test_calculate_total_annual_costs_with_null_values(): void
    {
        $property = Property::factory()->create([
            'user_id' => $this->user->id,
            'annual_service_charge' => 1200,
            'annual_ground_rent' => null,
            'annual_insurance' => null,
            'annual_maintenance_reserve' => null,
            'other_annual_costs' => null,
        ]);

        $totalCosts = $this->propertyService->calculateTotalAnnualCosts($property);

        expect($totalCosts)->toBe(1200.0);
    }

    public function test_calculate_net_rental_yield_for_btl(): void
    {
        $property = Property::factory()->create([
            'user_id' => $this->user->id,
            'ownership_type' => 'individual',
            'property_type' => 'buy_to_let',
            'current_value' => 200000,
            'annual_rental_income' => 12000,
            'occupancy_rate_percent' => 100,
            'annual_service_charge' => 600,
            'annual_ground_rent' => null,
            'annual_insurance' => 400,
            'annual_maintenance_reserve' => null,
            'other_annual_costs' => null,
        ]);

        $netYield = $this->propertyService->calculateNetRentalYield($property);

        // Income: £12,000
        // Costs: £600 + £400 = £1,000
        // Net: £11,000
        // Yield: (£11,000 / £200,000) * 100 = 5.5%
        expect($netYield)->toBe(5.5);
    }

    public function test_calculate_net_rental_yield_with_vacancy(): void
    {
        $property = Property::factory()->create([
            'user_id' => $this->user->id,
            'ownership_type' => 'individual',
            'property_type' => 'buy_to_let',
            'current_value' => 200000,
            'annual_rental_income' => 12000,
            'occupancy_rate_percent' => 90,
            'annual_service_charge' => 600,
            'annual_ground_rent' => null,
            'annual_insurance' => 400,
            'annual_maintenance_reserve' => null,
            'other_annual_costs' => null,
        ]);

        $netYield = $this->propertyService->calculateNetRentalYield($property);

        // Income: £12,000 * 0.9 = £10,800
        // Costs: £600 + £400 = £1,000
        // Net: £9,800
        // Yield: (£9,800 / £200,000) * 100 = 4.9%
        expect($netYield)->toBe(4.9);
    }

    public function test_calculate_net_rental_yield_returns_zero_for_zero_value(): void
    {
        $property = Property::factory()->create([
            'user_id' => $this->user->id,
            'property_type' => 'buy_to_let',
            'current_value' => 0,
            'annual_rental_income' => 12000,
        ]);

        $netYield = $this->propertyService->calculateNetRentalYield($property);

        expect($netYield)->toBe(0.0);
    }

    public function test_get_property_summary(): void
    {
        $property = Property::factory()->create([
            'user_id' => $this->user->id,
            'current_value' => 400000,
            'ownership_percentage' => 100,
        ]);

        Mortgage::factory()->create([
            'property_id' => $property->id,
            'user_id' => $this->user->id,
            'outstanding_balance' => 150000,
        ]);

        $summary = $this->propertyService->getPropertySummary($property);

        expect($summary)->toHaveKeys([
            'id',
            'property_type',
            'address',
            'current_value',
            'equity',
            'mortgage_balance',
            'ownership_percentage',
        ]);

        expect($summary['current_value'])->toBe(400000.0);
        expect($summary['equity'])->toBe(250000.0);
        expect($summary['mortgage_balance'])->toBe(150000.0);
    }

    public function test_calculate_equity_never_goes_negative(): void
    {
        $property = Property::factory()->create([
            'user_id' => $this->user->id,
            'current_value' => 100000,
            'ownership_percentage' => 100,
        ]);

        Mortgage::factory()->create([
            'property_id' => $property->id,
            'user_id' => $this->user->id,
            'outstanding_balance' => 150000, // Negative equity scenario
        ]);

        $equity = $this->propertyService->calculateEquity($property);

        // Even with negative equity, should return 0 not negative
        expect($equity)->toBeGreaterThanOrEqual(0.0);
    }
}
