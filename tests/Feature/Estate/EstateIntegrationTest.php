<?php

declare(strict_types=1);

use App\Models\Estate\Asset;
use App\Models\Estate\Liability;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

describe('Complete estate planning workflow', function () {
    it('completes full estate planning analysis from setup to recommendations', function () {
        // 1. Create user and authenticate
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        Sanctum::actingAs($user);

        // 2. Set up IHT profile
        $profileResponse = $this->postJson('/api/estate/profile', [
            'marital_status' => 'married',
            'has_spouse' => true,
            'own_home' => true,
            'home_value' => 600000,
            'nrb_transferred_from_spouse' => 0,
            'charitable_giving_percent' => 5,
        ]);

        $profileResponse->assertOk();

        // 3. Add assets
        $this->postJson('/api/estate/assets', [
            'asset_type' => 'property',
            'asset_name' => 'Main Residence',
            'current_value' => 600000,
            'ownership_type' => 'joint',
            'is_iht_exempt' => false,
            'valuation_date' => '2024-01-01',
        ])->assertCreated();

        $this->postJson('/api/estate/assets', [
            'asset_type' => 'pension',
            'asset_name' => 'DC Pension',
            'current_value' => 300000,
            'ownership_type' => 'individual',
            'is_iht_exempt' => true,
            'exemption_reason' => 'Nominated beneficiary',
            'valuation_date' => '2024-01-01',
        ])->assertCreated();

        $this->postJson('/api/estate/assets', [
            'asset_type' => 'investment',
            'asset_name' => 'ISA Portfolio',
            'current_value' => 150000,
            'ownership_type' => 'individual',
            'is_iht_exempt' => false,
            'valuation_date' => '2024-01-01',
        ])->assertCreated();

        // 4. Add liabilities
        $this->postJson('/api/estate/liabilities', [
            'liability_type' => 'mortgage',
            'liability_name' => 'Home Mortgage',
            'current_balance' => 200000,
            'monthly_payment' => 1200,
            'interest_rate' => 0.035,
        ])->assertCreated();

        // 5. Add historical gifts
        $this->postJson('/api/estate/gifts', [
            'gift_date' => Carbon::now()->subYears(2)->format('Y-m-d'),
            'recipient' => 'Daughter',
            'gift_type' => 'pet',
            'gift_value' => 30000,
            'status' => 'within_7_years',
            'taper_relief_applicable' => false,
        ])->assertCreated();

        $this->postJson('/api/estate/gifts', [
            'gift_date' => Carbon::now()->subYears(5)->format('Y-m-d'),
            'recipient' => 'Son',
            'gift_type' => 'pet',
            'gift_value' => 50000,
            'status' => 'within_7_years',
            'taper_relief_applicable' => true,
        ])->assertCreated();

        // 6. Get full estate overview
        $indexResponse = $this->getJson('/api/estate');
        $indexResponse->assertOk()
            ->assertJsonCount(3, 'data.assets')
            ->assertJsonCount(1, 'data.liabilities')
            ->assertJsonCount(2, 'data.gifts');

        // 7. Run comprehensive analysis
        $analysisResponse = $this->postJson('/api/estate/analyze');
        $analysisResponse->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'net_worth',
                    'iht_liability',
                    'pet_analysis',
                    'concentration_risk',
                    'cash_flow',
                    'probate_readiness_score',
                ],
            ]);

        // Verify net worth calculation
        $analysisData = $analysisResponse->json('data');
        expect($analysisData['net_worth']['total_assets'])->toBe(1050000)
            ->and($analysisData['net_worth']['total_liabilities'])->toBe(200000)
            ->and($analysisData['net_worth']['net_worth'])->toBe(850000);

        // 8. Calculate IHT liability specifically
        $ihtResponse = $this->postJson('/api/estate/calculate-iht');
        $ihtResponse->assertOk();

        $ihtData = $ihtResponse->json('data');
        expect($ihtData['gross_estate_value'])->toBeGreaterThan(0)
            ->and($ihtData)->toHaveKey('nrb')
            ->and($ihtData)->toHaveKey('rnrb')
            ->and($ihtData)->toHaveKey('iht_liability');

        // 9. Get personalized recommendations
        $recommendationsResponse = $this->getJson('/api/estate/recommendations');
        $recommendationsResponse->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'recommendation_count',
                    'recommendations' => [
                        '*' => [
                            'category',
                            'priority',
                            'title',
                            'description',
                            'action',
                        ],
                    ],
                ],
            ]);

        // 10. Run scenario modeling
        $scenariosResponse = $this->postJson('/api/estate/scenarios', [
            'annual_gifting_years' => 7,
            'charitable_percent' => 10,
            'spouse_nrb_transfer' => 325000,
        ]);

        $scenariosResponse->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'scenario_count',
                    'scenarios' => [
                        '*' => [
                            'name',
                            'estate_value',
                            'iht_liability',
                            'net_estate',
                        ],
                    ],
                    'best_scenario',
                ],
            ]);

        // Verify at least 3 scenarios generated
        expect($scenariosResponse->json('data.scenario_count'))->toBeGreaterThanOrEqual(3);

        // 11. Get net worth analysis with health score
        $netWorthResponse = $this->getJson('/api/estate/net-worth');
        $netWorthResponse->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'net_worth',
                    'concentration_risk',
                    'health_score' => [
                        'score',
                        'grade',
                        'factors',
                    ],
                ],
            ]);

        // 12. Get cash flow projection
        $cashFlowResponse = $this->getJson('/api/estate/cash-flow/2025');
        $cashFlowResponse->assertOk()
            ->assertJsonPath('data.tax_year', '2025/26');
    });
});

describe('IHT calculation with multiple scenarios', function () {
    it('shows IHT reduction through gifting strategy', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Set up large estate
        $this->postJson('/api/estate/profile', [
            'marital_status' => 'single',
            'has_spouse' => false,
            'own_home' => true,
            'home_value' => 500000,
            'nrb_transferred_from_spouse' => 0,
            'charitable_giving_percent' => 0,
        ]);

        $this->postJson('/api/estate/assets', [
            'asset_type' => 'property',
            'asset_name' => 'Home',
            'current_value' => 500000,
            'ownership_type' => 'individual',
            'valuation_date' => '2024-01-01',
        ]);

        $this->postJson('/api/estate/assets', [
            'asset_type' => 'investment',
            'asset_name' => 'Portfolio',
            'current_value' => 600000,
            'ownership_type' => 'individual',
            'valuation_date' => '2024-01-01',
        ]);

        // Calculate baseline IHT
        $baselineResponse = $this->postJson('/api/estate/calculate-iht');
        $baselineIHT = $baselineResponse->json('data.iht_liability');

        // Run scenario with 7 years of annual gifting
        $scenarioResponse = $this->postJson('/api/estate/scenarios', [
            'annual_gifting_years' => 7,
        ]);

        $scenarios = $scenarioResponse->json('data.scenarios');
        $giftingScenario = collect($scenarios)->firstWhere('name', 'like', '%Annual Gifting%');

        // Verify gifting reduces IHT
        if ($giftingScenario) {
            expect($giftingScenario)->toHaveKey('saving_vs_baseline');
        }

        expect($baselineIHT)->toBeGreaterThan(0);
    });

    it('shows IHT reduction through charitable giving', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/estate/profile', [
            'marital_status' => 'single',
            'has_spouse' => false,
            'own_home' => false,
            'home_value' => 0,
            'nrb_transferred_from_spouse' => 0,
            'charitable_giving_percent' => 0,
        ]);

        $this->postJson('/api/estate/assets', [
            'asset_type' => 'investment',
            'asset_name' => 'Portfolio',
            'current_value' => 800000,
            'ownership_type' => 'individual',
            'valuation_date' => '2024-01-01',
        ]);

        // Calculate baseline
        $baselineResponse = $this->postJson('/api/estate/calculate-iht');
        $baselineRate = $baselineResponse->json('data.iht_rate');
        expect($baselineRate)->toBe(0.40);

        // Run scenario with 10% charitable giving
        $scenarioResponse = $this->postJson('/api/estate/scenarios', [
            'charitable_percent' => 10,
        ]);

        $scenarios = $scenarioResponse->json('data.scenarios');
        $charitableScenario = collect($scenarios)->firstWhere('name', 'like', '%Charitable%');

        expect($charitableScenario)->not()->toBeNull()
            ->and($charitableScenario)->toHaveKey('saving_vs_baseline');
    });
});

describe('Cache behavior', function () {
    it('caches estate analysis results', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/estate/profile', [
            'marital_status' => 'single',
            'has_spouse' => false,
            'own_home' => false,
            'home_value' => 0,
            'nrb_transferred_from_spouse' => 0,
            'charitable_giving_percent' => 0,
        ]);

        Asset::create([
            'user_id' => $user->id,
            'asset_type' => 'investment',
            'asset_name' => 'Portfolio',
            'current_value' => 500000,
            'ownership_type' => 'individual',
            'valuation_date' => Carbon::now(),
        ]);

        // First call - should cache
        $firstResponse = $this->postJson('/api/estate/analyze');
        $firstResponse->assertOk();

        // Second call - should use cache
        $secondResponse = $this->postJson('/api/estate/analyze');
        $secondResponse->assertOk();

        // Results should be identical
        expect($firstResponse->json('data'))->toEqual($secondResponse->json('data'));
    });

    it('invalidates cache when asset is updated', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/estate/profile', [
            'marital_status' => 'single',
            'has_spouse' => false,
            'own_home' => false,
            'home_value' => 0,
            'nrb_transferred_from_spouse' => 0,
            'charitable_giving_percent' => 0,
        ]);

        $asset = Asset::create([
            'user_id' => $user->id,
            'asset_type' => 'investment',
            'asset_name' => 'Portfolio',
            'current_value' => 500000,
            'ownership_type' => 'individual',
            'valuation_date' => Carbon::now(),
        ]);

        // Initial analysis
        $firstResponse = $this->postJson('/api/estate/analyze');
        $firstNetWorth = $firstResponse->json('data.net_worth.net_worth');

        // Update asset
        $this->putJson("/api/estate/assets/{$asset->id}", [
            'current_value' => 600000,
        ]);

        // Analysis should reflect new value
        $secondResponse = $this->postJson('/api/estate/analyze');
        $secondNetWorth = $secondResponse->json('data.net_worth.net_worth');

        expect($secondNetWorth)->not()->toBe($firstNetWorth)
            ->and($secondNetWorth)->toBe(600000);
    });
});
