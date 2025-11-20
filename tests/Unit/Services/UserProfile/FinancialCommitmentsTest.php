<?php

declare(strict_types=1);

use App\Models\CriticalIllnessPolicy;
use App\Models\DCPension;
use App\Models\IncomeProtectionPolicy;
use App\Models\Liability;
use App\Models\LifeInsurancePolicy;
use App\Models\Property;
use App\Models\User;
use App\Services\Estate\AssetAggregatorService;
use App\Services\NetWorth\NetWorthTaxCalculator;
use App\Services\UserProfile\UserProfileService;

beforeEach(function () {
    $this->assetAggregator = new AssetAggregatorService();
    $this->taxCalculator = new NetWorthTaxCalculator();
    $this->service = new UserProfileService($this->assetAggregator, $this->taxCalculator);
    $this->user = User::factory()->create();
});

// =============================================================================
// BASIC FUNCTIONALITY TESTS
// =============================================================================

test('returns empty commitments for user with no assets', function () {
    $result = $this->service->getFinancialCommitments($this->user);

    expect($result['commitments'])->toBeArray()
        ->and($result['commitments']['retirement'])->toBeEmpty()
        ->and($result['commitments']['properties'])->toBeEmpty()
        ->and($result['commitments']['protection'])->toBeEmpty()
        ->and($result['commitments']['liabilities'])->toBeEmpty()
        ->and($result['totals']['total'])->toBe(0.0);
});

test('includes structure with all commitment types', function () {
    $result = $this->service->getFinancialCommitments($this->user);

    expect($result)->toHaveKeys(['commitments', 'totals'])
        ->and($result['commitments'])->toHaveKeys(['retirement', 'properties', 'protection', 'liabilities'])
        ->and($result['totals'])->toHaveKeys(['retirement', 'properties', 'protection', 'liabilities', 'total']);
});

// =============================================================================
// DC PENSION CONTRIBUTION TESTS
// =============================================================================

test('calculates individual DC pension contribution correctly', function () {
    DCPension::factory()->create([
        'user_id' => $this->user->id,
        'pension_name' => 'Workplace Pension',
        'monthly_contribution_amount' => 300.00,
        'ownership_type' => 'individual',
    ]);

    $result = $this->service->getFinancialCommitments($this->user);

    expect($result['commitments']['retirement'])->toHaveCount(1)
        ->and($result['commitments']['retirement'][0])->toMatchArray([
            'name' => 'Workplace Pension',
            'monthly_amount' => 300.00,
            'is_joint' => false,
        ])
        ->and($result['totals']['retirement'])->toBe(300.00)
        ->and($result['totals']['total'])->toBe(300.00);
});

test('splits joint DC pension contribution 50/50', function () {
    $spouse = User::factory()->create();
    $this->user->update(['spouse_id' => $spouse->id]);

    DCPension::factory()->create([
        'user_id' => $this->user->id,
        'pension_name' => 'Joint Pension',
        'monthly_contribution_amount' => 600.00,
        'ownership_type' => 'joint',
        'joint_owner_id' => $spouse->id,
    ]);

    $result = $this->service->getFinancialCommitments($this->user);

    expect($result['commitments']['retirement'])->toHaveCount(1)
        ->and($result['commitments']['retirement'][0])->toMatchArray([
            'name' => 'Joint Pension',
            'monthly_amount' => 300.00, // 50% of 600
            'is_joint' => true,
        ])
        ->and($result['totals']['retirement'])->toBe(300.00);
});

test('excludes DC pensions with zero contributions', function () {
    DCPension::factory()->create([
        'user_id' => $this->user->id,
        'pension_name' => 'Inactive Pension',
        'monthly_contribution_amount' => 0.00,
    ]);

    $result = $this->service->getFinancialCommitments($this->user);

    expect($result['commitments']['retirement'])->toBeEmpty()
        ->and($result['totals']['retirement'])->toBe(0.0);
});

test('handles multiple DC pensions correctly', function () {
    DCPension::factory()->create([
        'user_id' => $this->user->id,
        'pension_name' => 'Workplace Pension',
        'monthly_contribution_amount' => 300.00,
        'ownership_type' => 'individual',
    ]);

    DCPension::factory()->create([
        'user_id' => $this->user->id,
        'pension_name' => 'SIPP',
        'monthly_contribution_amount' => 500.00,
        'ownership_type' => 'individual',
    ]);

    $result = $this->service->getFinancialCommitments($this->user);

    expect($result['commitments']['retirement'])->toHaveCount(2)
        ->and($result['totals']['retirement'])->toBe(800.00);
});

// =============================================================================
// PROPERTY EXPENSE TESTS
// =============================================================================

test('calculates individual property expenses correctly', function () {
    Property::factory()->create([
        'user_id' => $this->user->id,
        'address_line_1' => '15 Amherst Place',
        'ownership_type' => 'individual',
        'monthly_council_tax' => 200.00,
        'monthly_gas' => 80.00,
        'monthly_electricity' => 95.00,
        'monthly_water' => 40.00,
        'monthly_building_insurance' => 50.00,
        'monthly_contents_insurance' => 30.00,
        'monthly_service_charge' => 0.00,
        'monthly_maintenance_reserve' => 0.00,
        'other_monthly_costs' => 0.00,
    ]);

    // Create mortgage for the property
    \App\Models\Mortgage::factory()->create([
        'property_id' => Property::first()->id,
        'user_id' => $this->user->id,
        'monthly_payment' => 450.00,
    ]);

    $result = $this->service->getFinancialCommitments($this->user);

    expect($result['commitments']['properties'])->toHaveCount(1)
        ->and($result['commitments']['properties'][0])->toMatchArray([
            'name' => '15 Amherst Place',
            'monthly_amount' => 945.00, // 450 + 200 + 80 + 95 + 40 + 50 + 30
            'is_joint' => false,
        ])
        ->and($result['commitments']['properties'][0]['breakdown'])->toMatchArray([
            'mortgage' => 450.00,
            'council_tax' => 200.00,
            'utilities' => 215.00, // gas + electric + water
            'insurance' => 80.00, // building + contents
            'service_charge' => 0.00,
            'maintenance' => 0.00,
            'other' => 0.00,
        ])
        ->and($result['totals']['properties'])->toBe(945.00);
});

test('splits joint property expenses 50/50', function () {
    $spouse = User::factory()->create();
    $this->user->update(['spouse_id' => $spouse->id]);

    Property::factory()->create([
        'user_id' => $this->user->id,
        'address_line_1' => 'Joint Property',
        'ownership_type' => 'joint',
        'joint_owner_id' => $spouse->id,
        'monthly_council_tax' => 400.00,
        'monthly_gas' => 0.00,
        'monthly_electricity' => 0.00,
        'monthly_water' => 0.00,
        'monthly_building_insurance' => 0.00,
        'monthly_contents_insurance' => 0.00,
    ]);

    \App\Models\Mortgage::factory()->create([
        'property_id' => Property::first()->id,
        'user_id' => $this->user->id,
        'monthly_payment' => 900.00,
        'ownership_type' => 'joint',
    ]);

    $result = $this->service->getFinancialCommitments($this->user);

    // Total property costs: 900 (mortgage) + 400 (council tax) = 1300
    // User's 50% share: 650
    expect($result['commitments']['properties'])->toHaveCount(1)
        ->and($result['commitments']['properties'][0])->toMatchArray([
            'monthly_amount' => 650.00, // 50% of 1300
            'is_joint' => true,
        ])
        ->and($result['totals']['properties'])->toBe(650.00);
});

test('handles property without mortgage', function () {
    Property::factory()->create([
        'user_id' => $this->user->id,
        'address_line_1' => 'Unmortgaged Property',
        'ownership_type' => 'individual',
        'monthly_council_tax' => 150.00,
        'monthly_gas' => 60.00,
        'monthly_electricity' => 70.00,
        'monthly_water' => 30.00,
    ]);

    $result = $this->service->getFinancialCommitments($this->user);

    expect($result['commitments']['properties'])->toHaveCount(1)
        ->and($result['commitments']['properties'][0])->toMatchArray([
            'monthly_amount' => 310.00, // Just utilities and council tax
        ])
        ->and($result['commitments']['properties'][0]['breakdown']['mortgage'])->toBe(0.00);
});

test('aggregates multiple mortgages on same property', function () {
    $property = Property::factory()->create([
        'user_id' => $this->user->id,
        'address_line_1' => 'Property with Two Mortgages',
        'monthly_council_tax' => 200.00,
    ]);

    // First mortgage
    \App\Models\Mortgage::factory()->create([
        'property_id' => $property->id,
        'user_id' => $this->user->id,
        'monthly_payment' => 800.00,
    ]);

    // Second mortgage (e.g., secured loan)
    \App\Models\Mortgage::factory()->create([
        'property_id' => $property->id,
        'user_id' => $this->user->id,
        'monthly_payment' => 200.00,
    ]);

    $result = $this->service->getFinancialCommitments($this->user);

    expect($result['commitments']['properties'][0]['breakdown']['mortgage'])->toBe(1000.00)
        ->and($result['commitments']['properties'][0]['monthly_amount'])->toBe(1200.00); // 1000 + 200 council tax
});

// =============================================================================
// LIFE INSURANCE PREMIUM TESTS
// =============================================================================

test('converts monthly life insurance premium correctly', function () {
    LifeInsurancePolicy::factory()->create([
        'user_id' => $this->user->id,
        'policy_name' => 'Term Life',
        'premium_amount' => 150.00,
        'premium_frequency' => 'monthly',
        'ownership_type' => 'individual',
    ]);

    $result = $this->service->getFinancialCommitments($this->user);

    expect($result['commitments']['protection'])->toHaveCount(1)
        ->and($result['commitments']['protection'][0])->toMatchArray([
            'name' => 'Term Life',
            'type' => 'Life Insurance',
            'monthly_amount' => 150.00,
            'is_joint' => false,
        ])
        ->and($result['totals']['protection'])->toBe(150.00);
});

test('converts quarterly premium to monthly', function () {
    LifeInsurancePolicy::factory()->create([
        'user_id' => $this->user->id,
        'policy_name' => 'Quarterly Life',
        'premium_amount' => 450.00,
        'premium_frequency' => 'quarterly',
        'ownership_type' => 'individual',
    ]);

    $result = $this->service->getFinancialCommitments($this->user);

    expect($result['commitments']['protection'][0]['monthly_amount'])->toBe(150.00); // 450 / 3
});

test('converts annual premium to monthly', function () {
    LifeInsurancePolicy::factory()->create([
        'user_id' => $this->user->id,
        'policy_name' => 'Annual Life',
        'premium_amount' => 1800.00,
        'premium_frequency' => 'annually',
        'ownership_type' => 'individual',
    ]);

    $result = $this->service->getFinancialCommitments($this->user);

    expect($result['commitments']['protection'][0]['monthly_amount'])->toBe(150.00); // 1800 / 12
});

test('splits joint life insurance premium 50/50', function () {
    $spouse = User::factory()->create();
    $this->user->update(['spouse_id' => $spouse->id]);

    LifeInsurancePolicy::factory()->create([
        'user_id' => $this->user->id,
        'policy_name' => 'Joint Life',
        'premium_amount' => 300.00,
        'premium_frequency' => 'monthly',
        'ownership_type' => 'joint',
        'joint_owner_id' => $spouse->id,
    ]);

    $result = $this->service->getFinancialCommitments($this->user);

    expect($result['commitments']['protection'][0])->toMatchArray([
        'monthly_amount' => 150.00, // 50% of 300
        'is_joint' => true,
    ]);
});

// =============================================================================
// CRITICAL ILLNESS PREMIUM TESTS
// =============================================================================

test('calculates critical illness premium correctly', function () {
    CriticalIllnessPolicy::factory()->create([
        'user_id' => $this->user->id,
        'policy_name' => 'CI Policy',
        'premium_amount' => 80.00,
        'premium_frequency' => 'monthly',
        'ownership_type' => 'individual',
    ]);

    $result = $this->service->getFinancialCommitments($this->user);

    expect($result['commitments']['protection'])->toHaveCount(1)
        ->and($result['commitments']['protection'][0])->toMatchArray([
            'name' => 'CI Policy',
            'type' => 'Critical Illness',
            'monthly_amount' => 80.00,
        ]);
});

// =============================================================================
// INCOME PROTECTION PREMIUM TESTS
// =============================================================================

test('calculates income protection premium correctly', function () {
    IncomeProtectionPolicy::factory()->create([
        'user_id' => $this->user->id,
        'policy_name' => 'IP Policy',
        'premium_amount' => 120.00,
        'premium_frequency' => 'monthly',
        'ownership_type' => 'individual',
    ]);

    $result = $this->service->getFinancialCommitments($this->user);

    expect($result['commitments']['protection'])->toHaveCount(1)
        ->and($result['commitments']['protection'][0])->toMatchArray([
            'name' => 'IP Policy',
            'type' => 'Income Protection',
            'monthly_amount' => 120.00,
        ]);
});

test('aggregates multiple protection policies', function () {
    LifeInsurancePolicy::factory()->create([
        'user_id' => $this->user->id,
        'premium_amount' => 150.00,
        'premium_frequency' => 'monthly',
    ]);

    CriticalIllnessPolicy::factory()->create([
        'user_id' => $this->user->id,
        'premium_amount' => 80.00,
        'premium_frequency' => 'monthly',
    ]);

    IncomeProtectionPolicy::factory()->create([
        'user_id' => $this->user->id,
        'premium_amount' => 120.00,
        'premium_frequency' => 'monthly',
    ]);

    $result = $this->service->getFinancialCommitments($this->user);

    expect($result['commitments']['protection'])->toHaveCount(3)
        ->and($result['totals']['protection'])->toBe(350.00);
});

// =============================================================================
// LIABILITY REPAYMENT TESTS
// =============================================================================

test('calculates individual liability repayment correctly', function () {
    Liability::factory()->create([
        'user_id' => $this->user->id,
        'liability_name' => 'Personal Loan',
        'liability_type' => 'personal_loan',
        'current_balance' => 10000.00,
        'monthly_payment' => 250.00,
        'ownership_type' => 'individual',
    ]);

    $result = $this->service->getFinancialCommitments($this->user);

    expect($result['commitments']['liabilities'])->toHaveCount(1)
        ->and($result['commitments']['liabilities'][0])->toMatchArray([
            'name' => 'Personal Loan',
            'monthly_amount' => 250.00,
            'is_joint' => false,
        ])
        ->and($result['totals']['liabilities'])->toBe(250.00);
});

test('splits joint liability repayment 50/50', function () {
    $spouse = User::factory()->create();
    $this->user->update(['spouse_id' => $spouse->id]);

    Liability::factory()->create([
        'user_id' => $this->user->id,
        'liability_name' => 'Joint Car Loan',
        'liability_type' => 'car_loan',
        'current_balance' => 15000.00,
        'monthly_payment' => 400.00,
        'ownership_type' => 'joint',
        'joint_owner_id' => $spouse->id,
    ]);

    $result = $this->service->getFinancialCommitments($this->user);

    expect($result['commitments']['liabilities'][0])->toMatchArray([
        'monthly_amount' => 200.00, // 50% of 400
        'is_joint' => true,
    ]);
});

// =============================================================================
// COMPREHENSIVE INTEGRATION TESTS
// =============================================================================

test('calculates total commitments across all categories', function () {
    // DC Pension
    DCPension::factory()->create([
        'user_id' => $this->user->id,
        'monthly_contribution_amount' => 300.00,
    ]);

    // Property
    $property = Property::factory()->create([
        'user_id' => $this->user->id,
        'monthly_council_tax' => 200.00,
    ]);
    \App\Models\Mortgage::factory()->create([
        'property_id' => $property->id,
        'user_id' => $this->user->id,
        'monthly_payment' => 800.00,
    ]);

    // Life Insurance
    LifeInsurancePolicy::factory()->create([
        'user_id' => $this->user->id,
        'premium_amount' => 150.00,
        'premium_frequency' => 'monthly',
    ]);

    // Liability
    Liability::factory()->create([
        'user_id' => $this->user->id,
        'monthly_payment' => 250.00,
    ]);

    $result = $this->service->getFinancialCommitments($this->user);

    expect($result['totals']['retirement'])->toBe(300.00)
        ->and($result['totals']['properties'])->toBe(1000.00)
        ->and($result['totals']['protection'])->toBe(150.00)
        ->and($result['totals']['liabilities'])->toBe(250.00)
        ->and($result['totals']['total'])->toBe(1700.00);
});

test('prevents double-counting joint items', function () {
    $spouse = User::factory()->create();
    $this->user->update(['spouse_id' => $spouse->id]);

    // Joint property (should be 50% each, totaling 100% once)
    $property = Property::factory()->create([
        'user_id' => $this->user->id,
        'ownership_type' => 'joint',
        'joint_owner_id' => $spouse->id,
        'monthly_council_tax' => 400.00,
    ]);
    \App\Models\Mortgage::factory()->create([
        'property_id' => $property->id,
        'user_id' => $this->user->id,
        'ownership_type' => 'joint',
        'monthly_payment' => 1600.00,
    ]);

    // Get commitments for user (should be 50% of joint items)
    $userCommitments = $this->service->getFinancialCommitments($this->user);

    // Joint property total: 1600 + 400 = 2000
    // User's 50% share: 1000
    expect($userCommitments['commitments']['properties'][0])->toMatchArray([
        'monthly_amount' => 1000.00,
        'is_joint' => true,
    ])
        ->and($userCommitments['totals']['total'])->toBe(1000.00);

    // If we were to get spouse's commitments (from their account), they'd also get 1000
    // Combined household: 1000 + 1000 = 2000 (not 4000) ✓
});

test('handles mixed individual and joint commitments correctly', function () {
    $spouse = User::factory()->create();
    $this->user->update(['spouse_id' => $spouse->id]);

    // User's individual DC pension
    DCPension::factory()->create([
        'user_id' => $this->user->id,
        'monthly_contribution_amount' => 300.00,
        'ownership_type' => 'individual',
    ]);

    // Joint property
    $property = Property::factory()->create([
        'user_id' => $this->user->id,
        'ownership_type' => 'joint',
        'joint_owner_id' => $spouse->id,
        'monthly_council_tax' => 400.00,
    ]);
    \App\Models\Mortgage::factory()->create([
        'property_id' => $property->id,
        'user_id' => $this->user->id,
        'ownership_type' => 'joint',
        'monthly_payment' => 1600.00,
    ]);

    // User's individual liability
    Liability::factory()->create([
        'user_id' => $this->user->id,
        'monthly_payment' => 200.00,
        'ownership_type' => 'individual',
    ]);

    $result = $this->service->getFinancialCommitments($this->user);

    // Breakdown:
    // - Individual pension: 300 (full amount)
    // - Joint property: 1000 (50% of 2000)
    // - Individual liability: 200 (full amount)
    // Total: 1500

    expect($result['totals']['retirement'])->toBe(300.00)
        ->and($result['totals']['properties'])->toBe(1000.00)
        ->and($result['totals']['liabilities'])->toBe(200.00)
        ->and($result['totals']['total'])->toBe(1500.00);

    // Verify is_joint flags
    expect($result['commitments']['retirement'][0]['is_joint'])->toBeFalse()
        ->and($result['commitments']['properties'][0]['is_joint'])->toBeTrue()
        ->and($result['commitments']['liabilities'][0]['is_joint'])->toBeFalse();
});

// =============================================================================
// EDGE CASE TESTS
// =============================================================================

test('handles null monthly payment values gracefully', function () {
    Liability::factory()->create([
        'user_id' => $this->user->id,
        'liability_name' => 'Credit Card',
        'monthly_payment' => null, // No fixed payment
    ]);

    $result = $this->service->getFinancialCommitments($this->user);

    // Should not include liabilities without monthly payment
    expect($result['commitments']['liabilities'])->toBeEmpty()
        ->and($result['totals']['liabilities'])->toBe(0.0);
});

test('handles zero monthly payment values', function () {
    DCPension::factory()->create([
        'user_id' => $this->user->id,
        'monthly_contribution_amount' => 0.00,
    ]);

    $result = $this->service->getFinancialCommitments($this->user);

    // Should not include commitments with zero amount
    expect($result['commitments']['retirement'])->toBeEmpty();
});

test('excludes properties with zero total costs', function () {
    Property::factory()->create([
        'user_id' => $this->user->id,
        'monthly_council_tax' => 0.00,
        'monthly_gas' => 0.00,
        'monthly_electricity' => 0.00,
        'monthly_water' => 0.00,
    ]);
    // No mortgage

    $result = $this->service->getFinancialCommitments($this->user);

    expect($result['commitments']['properties'])->toBeEmpty()
        ->and($result['totals']['properties'])->toBe(0.0);
});

test('rounds monetary values correctly', function () {
    LifeInsurancePolicy::factory()->create([
        'user_id' => $this->user->id,
        'premium_amount' => 123.456, // Should round
        'premium_frequency' => 'monthly',
    ]);

    $result = $this->service->getFinancialCommitments($this->user);

    // PHP floats should handle this, but verify no precision errors
    expect($result['commitments']['protection'][0]['monthly_amount'])->toBeFloat();
});
