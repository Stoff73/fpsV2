<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Property;
use App\Models\User;
use App\Services\Property\PropertyTaxService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyTaxServiceTest extends TestCase
{
    use RefreshDatabase;

    private PropertyTaxService $taxService;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->taxService = new PropertyTaxService();
        // Create user with higher rate income for CGT tests
        $this->user = User::factory()->create([
            'annual_employment_income' => 60000,
            'annual_self_employment_income' => 0,
            'annual_rental_income' => 0,
            'annual_dividend_income' => 0,
            'annual_other_income' => 0,
        ]);
    }

    // SDLT Tests

    public function test_sdlt_main_residence_under_250k(): void
    {
        $result = $this->taxService->calculateSDLT(200000, 'main_residence', false);

        expect($result['total_sdlt'])->toBe(0.0);
        expect($result['effective_rate'])->toBe(0.0);
    }

    public function test_sdlt_main_residence_500k(): void
    {
        $result = $this->taxService->calculateSDLT(500000, 'main_residence', false);

        // Band 1: £250k at 0% = £0
        // Band 2: £250k at 5% = £12,500
        // Total: £12,500
        expect($result['total_sdlt'])->toBe(12500.0);
        expect($result['effective_rate'])->toBe(2.5);
    }

    public function test_sdlt_first_time_buyer_400k(): void
    {
        $result = $this->taxService->calculateSDLT(400000, 'main_residence', true);

        // First-time buyer relief up to £425k
        // £400k at 0% = £0
        expect($result['total_sdlt'])->toBe(0.0);
        expect($result['effective_rate'])->toBe(0.0);
    }

    public function test_sdlt_first_time_buyer_above_625k_gets_no_relief(): void
    {
        $result = $this->taxService->calculateSDLT(700000, 'main_residence', true);

        // Above £625k, no first-time buyer relief
        // Should calculate as standard main residence
        // Band 1: £250k at 0% = £0
        // Band 2: £450k at 5% = £22,500
        // Total: £22,500
        expect($result['total_sdlt'])->toBeGreaterThan(0.0);
    }

    public function test_sdlt_additional_property_with_3_percent_surcharge(): void
    {
        $result = $this->taxService->calculateSDLT(300000, 'secondary_residence', false);

        // Band 1: £250k at 3% (surcharge only) = £7,500
        // Band 2: £50k at 8% (5% + 3%) = £4,000
        // Total: £11,500
        expect($result['total_sdlt'])->toBe(11500.0);
    }

    public function test_sdlt_buy_to_let_applies_surcharge(): void
    {
        $result = $this->taxService->calculateSDLT(250000, 'buy_to_let', false);

        // All buy-to-let properties get 3% surcharge
        // £250k at 3% = £7,500
        expect($result['total_sdlt'])->toBe(7500.0);
    }

    public function test_sdlt_above_1_5_million(): void
    {
        $result = $this->taxService->calculateSDLT(2000000, 'main_residence', false);

        // Band 1: £250k at 0% = £0
        // Band 2: £675k at 5% = £33,750
        // Band 3: £575k at 10% = £57,500
        // Band 4: £500k at 12% = £60,000
        // Total: £151,250
        expect($result['total_sdlt'])->toBe(151250.0);
        expect($result['effective_rate'])->toBe(7.56);
    }

    // CGT Tests

    public function test_cgt_calculation_basic_rate_taxpayer(): void
    {
        $basicRateUser = User::factory()->create();

        $property = Property::factory()->create([
            'user_id' => $basicRateUser->id,
            'property_type' => 'secondary_residence',
            'purchase_price' => 200000,
            'current_value' => 300000,
            'sdlt_paid' => 0,  // Set to 0 for predictable test
        ]);

        $result = $this->taxService->calculateCGT(
            $property,
            300000, // disposal price
            5000,   // disposal costs (legal, estate agent)
            $basicRateUser
        );

        // Gain: £300k - (£200k + £0 SDLT) - £5k = £95k
        // Less annual exempt: £95k - £3k = £92k taxable
        // CGT at 18% (basic rate): £92k * 0.18 = £16,560
        expect($result['gain'])->toBe(95000.0);
        expect($result['taxable_gain'])->toBe(92000.0);
        expect($result['cgt_rate'])->toBe(18.0);
        expect($result['cgt_liability'])->toBe(16560.0);
    }

    public function test_cgt_calculation_higher_rate_taxpayer(): void
    {
        $property = Property::factory()->create([
            'user_id' => $this->user->id,
            'property_type' => 'secondary_residence',
            'purchase_price' => 200000,
            'current_value' => 300000,
            'sdlt_paid' => 0,
        ]);

        $result = $this->taxService->calculateCGT(
            $property,
            300000,
            5000,
            $this->user
        );

        // Gain: £300k - £200k - £5k = £95k
        // Less annual exempt: £95k - £3k = £92k taxable
        // CGT at 24% (higher rate): £92k * 0.24 = £22,080
        expect($result['cgt_rate'])->toBe(24.0);
        expect($result['cgt_liability'])->toBe(22080.0);
    }


    public function test_cgt_annual_exempt_amount_2024_25(): void
    {
        $property = Property::factory()->create([
            'user_id' => $this->user->id,
            'property_type' => 'secondary_residence',
            'purchase_price' => 200000,
            'current_value' => 205000,
            'sdlt_paid' => 0,
        ]);

        $result = $this->taxService->calculateCGT(
            $property,
            205000,
            1000,
            $this->user
        );

        // Gain: £205k - £200k - £1k = £4k
        // Less annual exempt: £4k - £3k = £1k taxable
        expect($result['taxable_gain'])->toBe(1000.0);
    }

    public function test_cgt_no_liability_when_gain_below_exempt_amount(): void
    {
        $property = Property::factory()->create([
            'user_id' => $this->user->id,
            'property_type' => 'secondary_residence',
            'purchase_price' => 200000,
            'current_value' => 202000,
            'sdlt_paid' => 0,
        ]);

        $result = $this->taxService->calculateCGT(
            $property,
            202000,
            0,
            $this->user
        );

        // Gain: £2k
        // Less annual exempt: £2k - £3k = £0 (can't go negative)
        expect($result['taxable_gain'])->toBe(0.0);
        expect($result['cgt_liability'])->toBe(0.0);
    }

    // Rental Income Tax Tests

    public function test_rental_income_tax_basic_calculation(): void
    {
        $property = Property::factory()->create([
            'user_id' => $this->user->id,
            'property_type' => 'buy_to_let',
            'annual_rental_income' => 15000,
            'annual_service_charge' => 1200,
            'annual_insurance' => 500,
            'annual_ground_rent' => null,
            'annual_maintenance_reserve' => 1000,
            'other_annual_costs' => null,
        ]);

        $result = $this->taxService->calculateRentalIncomeTax($property, $this->user);

        // Income: £15,000
        // Allowable expenses: £2,700
        // Taxable profit: £12,300
        // Tax at 40% (higher rate): £4,920
        expect($result['gross_income'])->toBe(15000.0);
        expect($result['allowable_expenses'])->toBe(2700.0);
        expect($result['taxable_profit'])->toBe(12300.0);
        expect($result['tax_liability'])->toBeGreaterThan(0.0);
    }

    public function test_rental_income_tax_with_mortgage_interest_relief(): void
    {
        $property = Property::factory()->create([
            'user_id' => $this->user->id,
            'property_type' => 'buy_to_let',
            'annual_rental_income' => 15000,
            'annual_service_charge' => 1000,
        ]);

        // Create mortgage with interest
        \App\Models\Mortgage::factory()->create([
            'property_id' => $property->id,
            'user_id' => $this->user->id,
            'outstanding_balance' => 150000,
            'interest_rate' => 4.0,
        ]);

        $result = $this->taxService->calculateRentalIncomeTax($property, $this->user);

        // Mortgage interest: ~£6,000 per year
        // 20% tax credit applied
        expect($result)->toHaveKey('mortgage_interest_relief');
        expect($result['mortgage_interest_relief'])->toBeGreaterThan(0.0);
    }

    public function test_rental_income_tax_with_occupancy_rate(): void
    {
        $property = Property::factory()->create([
            'user_id' => $this->user->id,
            'property_type' => 'buy_to_let',
            'annual_rental_income' => 12000,
            'occupancy_rate_percent' => 90,
            'annual_service_charge' => 1000,
        ]);

        $result = $this->taxService->calculateRentalIncomeTax($property, $this->user);

        // Income: £12,000 * 0.9 = £10,800
        expect($result['gross_income'])->toBe(10800.0);
    }

    public function test_rental_income_tax_basic_rate_taxpayer(): void
    {
        $basicRateUser = User::factory()->create();

        $property = Property::factory()->create([
            'user_id' => $basicRateUser->id,
            'property_type' => 'buy_to_let',
            'annual_rental_income' => 10000,
            'annual_service_charge' => 1000,
        ]);

        $result = $this->taxService->calculateRentalIncomeTax($property, $basicRateUser);

        // Tax at 20% (basic rate) should apply
        expect($result['tax_liability'])->toBeLessThan(2000.0);
    }
}
