<?php

declare(strict_types=1);

use App\Models\Estate\Asset;
use App\Models\Estate\Gift;
use App\Models\Estate\IHTProfile;
use App\Models\Estate\Liability;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
});

describe('GET /api/estate', function () {
    it('returns all estate data for authenticated user', function () {
        Asset::create([
            'user_id' => $this->user->id,
            'asset_type' => 'property',
            'asset_name' => 'Home',
            'current_value' => 500000,
            'ownership_type' => 'sole',
            'valuation_date' => Carbon::now(),
        ]);

        Liability::create([
            'user_id' => $this->user->id,
            'liability_type' => 'mortgage',
            'liability_name' => 'Home Mortgage',
            'current_balance' => 200000,
        ]);

        $response = $this->getJson('/api/estate');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'assets',
                    'liabilities',
                    'gifts',
                    'iht_profile',
                ],
            ]);
    });

    it('requires authentication', function () {
        Sanctum::actingAs(null);

        $response = $this->getJson('/api/estate');

        $response->assertUnauthorized();
    });
});

describe('POST /api/estate/analyze', function () {
    it('performs comprehensive estate analysis', function () {
        Asset::create([
            'user_id' => $this->user->id,
            'asset_type' => 'property',
            'asset_name' => 'Home',
            'current_value' => 600000,
            'ownership_type' => 'sole',
            'valuation_date' => Carbon::now(),
        ]);

        IHTProfile::create([
            'user_id' => $this->user->id,
            'marital_status' => 'single',
            'has_spouse' => false,
            'own_home' => true,
            'home_value' => 600000,
            'nrb_transferred_from_spouse' => 0,
            'charitable_giving_percent' => 0,
        ]);

        $response = $this->postJson('/api/estate/analyze');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'net_worth',
                    'iht_liability',
                    'pet_analysis',
                    'concentration_risk',
                    'probate_readiness_score',
                ],
            ]);
    });
});

describe('GET /api/estate/recommendations', function () {
    it('returns personalized recommendations', function () {
        Asset::create([
            'user_id' => $this->user->id,
            'asset_type' => 'property',
            'asset_name' => 'Home',
            'current_value' => 600000,
            'ownership_type' => 'sole',
            'valuation_date' => Carbon::now(),
        ]);

        IHTProfile::create([
            'user_id' => $this->user->id,
            'marital_status' => 'single',
            'has_spouse' => false,
            'own_home' => true,
            'home_value' => 600000,
            'nrb_transferred_from_spouse' => 0,
            'charitable_giving_percent' => 0,
        ]);

        $response = $this->getJson('/api/estate/recommendations');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'recommendation_count',
                    'recommendations',
                ],
            ]);
    });
});

describe('POST /api/estate/scenarios', function () {
    it('builds what-if scenarios', function () {
        Asset::create([
            'user_id' => $this->user->id,
            'asset_type' => 'investment',
            'asset_name' => 'Portfolio',
            'current_value' => 500000,
            'ownership_type' => 'sole',
            'valuation_date' => Carbon::now(),
        ]);

        IHTProfile::create([
            'user_id' => $this->user->id,
            'marital_status' => 'single',
            'has_spouse' => false,
            'own_home' => false,
            'home_value' => 0,
            'nrb_transferred_from_spouse' => 0,
            'charitable_giving_percent' => 0,
        ]);

        $response = $this->postJson('/api/estate/scenarios', [
            'annual_gifting_years' => 5,
            'charitable_percent' => 10,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'scenario_count',
                    'scenarios',
                    'best_scenario',
                ],
            ]);
    });
});

describe('POST /api/estate/calculate-iht', function () {
    it('calculates IHT liability', function () {
        Asset::create([
            'user_id' => $this->user->id,
            'asset_type' => 'property',
            'asset_name' => 'Home',
            'current_value' => 600000,
            'ownership_type' => 'sole',
            'valuation_date' => Carbon::now(),
        ]);

        IHTProfile::create([
            'user_id' => $this->user->id,
            'marital_status' => 'single',
            'has_spouse' => false,
            'own_home' => true,
            'home_value' => 600000,
            'nrb_transferred_from_spouse' => 0,
            'charitable_giving_percent' => 0,
        ]);

        $response = $this->postJson('/api/estate/calculate-iht');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'gross_estate_value',
                    'nrb',
                    'rnrb',
                    'total_allowance',
                    'taxable_estate',
                    'iht_liability',
                ],
            ]);
    });

    it('returns 404 when IHT profile not found', function () {
        $response = $this->postJson('/api/estate/calculate-iht');

        $response->assertNotFound()
            ->assertJsonFragment(['success' => false]);
    });
});

describe('GET /api/estate/net-worth', function () {
    it('returns net worth analysis', function () {
        Asset::create([
            'user_id' => $this->user->id,
            'asset_type' => 'property',
            'asset_name' => 'Home',
            'current_value' => 500000,
            'ownership_type' => 'sole',
            'valuation_date' => Carbon::now(),
        ]);

        $response = $this->getJson('/api/estate/net-worth');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'net_worth',
                    'concentration_risk',
                    'trend',
                    'health_score',
                ],
            ]);
    });
});

describe('GET /api/estate/cash-flow/{taxYear}', function () {
    it('returns cash flow for specified tax year', function () {
        $response = $this->getJson('/api/estate/cash-flow/2024');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'tax_year',
                    'income',
                    'expenditure',
                    'net_surplus_deficit',
                ],
            ]);
    });
});

describe('Asset CRUD operations', function () {
    it('creates a new asset', function () {
        $response = $this->postJson('/api/estate/assets', [
            'asset_type' => 'property',
            'asset_name' => 'Investment Property',
            'current_value' => 350000,
            'ownership_type' => 'sole',
            'is_iht_exempt' => false,
            'valuation_date' => '2024-01-01',
        ]);

        $response->assertCreated()
            ->assertJsonFragment(['success' => true])
            ->assertJsonPath('data.asset_name', 'Investment Property');
    });

    it('validates required fields when creating asset', function () {
        $response = $this->postJson('/api/estate/assets', [
            'asset_type' => 'property',
            // Missing required fields
        ]);

        $response->assertUnprocessable();
    });

    it('updates an asset', function () {
        $asset = Asset::create([
            'user_id' => $this->user->id,
            'asset_type' => 'property',
            'asset_name' => 'Home',
            'current_value' => 500000,
            'ownership_type' => 'sole',
            'valuation_date' => Carbon::now(),
        ]);

        $response = $this->putJson("/api/estate/assets/{$asset->id}", [
            'current_value' => 550000,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.current_value', '550000.00');
    });

    it('prevents updating another users asset', function () {
        $otherUser = User::factory()->create();
        $asset = Asset::create([
            'user_id' => $otherUser->id,
            'asset_type' => 'property',
            'asset_name' => 'Home',
            'current_value' => 500000,
            'ownership_type' => 'sole',
            'valuation_date' => Carbon::now(),
        ]);

        $response = $this->putJson("/api/estate/assets/{$asset->id}", [
            'current_value' => 550000,
        ]);

        $response->assertNotFound();
    });

    it('deletes an asset', function () {
        $asset = Asset::create([
            'user_id' => $this->user->id,
            'asset_type' => 'property',
            'asset_name' => 'Home',
            'current_value' => 500000,
            'ownership_type' => 'sole',
            'valuation_date' => Carbon::now(),
        ]);

        $response = $this->deleteJson("/api/estate/assets/{$asset->id}");

        $response->assertOk()
            ->assertJsonFragment(['success' => true]);

        $this->assertDatabaseMissing('assets', ['id' => $asset->id]);
    });
});

describe('Liability CRUD operations', function () {
    it('creates a new liability', function () {
        $response = $this->postJson('/api/estate/liabilities', [
            'liability_type' => 'mortgage',
            'liability_name' => 'Home Mortgage',
            'current_balance' => 200000,
            'monthly_payment' => 1000,
            'interest_rate' => 0.035,
        ]);

        $response->assertCreated()
            ->assertJsonFragment(['success' => true])
            ->assertJsonPath('data.liability_name', 'Home Mortgage');
    });

    it('updates a liability', function () {
        $liability = Liability::create([
            'user_id' => $this->user->id,
            'liability_type' => 'mortgage',
            'liability_name' => 'Mortgage',
            'current_balance' => 200000,
        ]);

        $response = $this->putJson("/api/estate/liabilities/{$liability->id}", [
            'current_balance' => 190000,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.current_balance', '190000.00');
    });

    it('deletes a liability', function () {
        $liability = Liability::create([
            'user_id' => $this->user->id,
            'liability_type' => 'loan',
            'liability_name' => 'Car Loan',
            'current_balance' => 15000,
        ]);

        $response = $this->deleteJson("/api/estate/liabilities/{$liability->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('liabilities', ['id' => $liability->id]);
    });
});

describe('Gift CRUD operations', function () {
    it('creates a new gift', function () {
        $response = $this->postJson('/api/estate/gifts', [
            'gift_date' => '2024-01-15',
            'recipient' => 'Child',
            'gift_type' => 'pet',
            'gift_value' => 50000,
            'status' => 'within_7_years',
            'taper_relief_applicable' => false,
        ]);

        $response->assertCreated()
            ->assertJsonFragment(['success' => true])
            ->assertJsonPath('data.recipient', 'Child');
    });

    it('updates a gift', function () {
        $gift = Gift::create([
            'user_id' => $this->user->id,
            'gift_date' => Carbon::now()->subYears(3),
            'recipient' => 'Child',
            'gift_type' => 'pet',
            'gift_value' => 50000,
        ]);

        $response = $this->putJson("/api/estate/gifts/{$gift->id}", [
            'gift_value' => 55000,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.gift_value', '55000.00');
    });

    it('deletes a gift', function () {
        $gift = Gift::create([
            'user_id' => $this->user->id,
            'gift_date' => Carbon::now(),
            'recipient' => 'Friend',
            'gift_type' => 'small_gift',
            'gift_value' => 250,
        ]);

        $response = $this->deleteJson("/api/estate/gifts/{$gift->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('gifts', ['id' => $gift->id]);
    });
});

describe('POST /api/estate/profile', function () {
    it('creates IHT profile', function () {
        $response = $this->postJson('/api/estate/profile', [
            'marital_status' => 'married',
            'has_spouse' => true,
            'own_home' => true,
            'home_value' => 500000,
            'nrb_transferred_from_spouse' => 0,
            'charitable_giving_percent' => 10,
        ]);

        $response->assertOk()
            ->assertJsonFragment(['success' => true])
            ->assertJsonPath('data.marital_status', 'married');
    });

    it('updates existing IHT profile', function () {
        IHTProfile::create([
            'user_id' => $this->user->id,
            'marital_status' => 'single',
            'has_spouse' => false,
            'own_home' => false,
            'home_value' => 0,
            'nrb_transferred_from_spouse' => 0,
            'charitable_giving_percent' => 0,
        ]);

        $response = $this->postJson('/api/estate/profile', [
            'marital_status' => 'married',
            'has_spouse' => true,
            'own_home' => true,
            'home_value' => 600000,
            'nrb_transferred_from_spouse' => 325000,
            'charitable_giving_percent' => 15,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.marital_status', 'married')
            ->assertJsonPath('data.home_value', '600000.00');

        $this->assertDatabaseCount('iht_profiles', 1);
    });
});
