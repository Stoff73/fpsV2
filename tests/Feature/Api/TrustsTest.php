<?php

use App\Models\BusinessInterest;
use App\Models\CashAccount;
use App\Models\Estate\Trust;
use App\Models\Household;
use App\Models\Property;
use App\Models\User;
use Carbon\Carbon;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->household = Household::factory()->create();
    Sanctum::actingAs($this->user);
});

test('GET /api/estate/trusts/{id}/assets returns trust assets', function () {
    $trust = Trust::factory()->create([
        'user_id' => $this->user->id,
        'household_id' => $this->household->id,
    ]);

    Property::factory()->create([
        'user_id' => $this->user->id,
        'trust_id' => $trust->id,
        'current_value' => 500000,
        'ownership_percentage' => 100,
    ]);

    CashAccount::factory()->create([
        'user_id' => $this->user->id,
        'trust_id' => $trust->id,
        'current_balance' => 100000,
        'ownership_percentage' => 100,
    ]);

    $response = $this->getJson("/api/estate/trusts/{$trust->id}/assets");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'assets',
                'total_value',
                'asset_count',
                'breakdown',
            ],
        ]);

    $data = $response->json('data');
    expect($data['asset_count'])->toBe(2);
    expect((float) $data['total_value'])->toBe(600000.0);
});

test('POST /api/estate/trusts/{id}/calculate-iht-impact calculates IHT', function () {
    $trust = Trust::factory()->create([
        'user_id' => $this->user->id,
        'household_id' => $this->household->id,
        'trust_type' => 'discretionary',
        'is_relevant_property_trust' => true,
        'trust_creation_date' => Carbon::now()->subYears(10),
        'current_value' => 500000,
    ]);

    Property::factory()->create([
        'user_id' => $this->user->id,
        'trust_id' => $trust->id,
        'current_value' => 500000,
        'ownership_percentage' => 100,
    ]);

    $response = $this->postJson("/api/estate/trusts/{$trust->id}/calculate-iht-impact");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'periodic_charge',
                'trust',
                'total_asset_value',
            ],
        ]);

    expect($response->json('data.periodic_charge'))->toBeArray();
});

test('GET /api/estate/trusts/upcoming-tax-returns returns data', function () {
    Trust::factory()->create([
        'user_id' => $this->user->id,
        'household_id' => $this->household->id,
        'trust_type' => 'discretionary',
        'is_relevant_property_trust' => true,
        'is_active' => true,
        'trust_creation_date' => Carbon::now()->subYears(9)->subMonths(6),
        'current_value' => 600000,
        'total_asset_value' => 600000,
    ]);

    $response = $this->getJson('/api/estate/trusts/upcoming-tax-returns');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'upcoming_periodic_charges',
                'tax_returns',
            ],
        ]);
});

test('users cannot access other users trust assets', function () {
    $otherUser = User::factory()->create();
    $otherTrust = Trust::factory()->create([
        'user_id' => $otherUser->id,
        'household_id' => $this->household->id,
    ]);

    $response = $this->getJson("/api/estate/trusts/{$otherTrust->id}/assets");

    // Should fail with 404 (not found) because findOrFail filters by user_id
    $response->assertStatus(404);
});

test('trust assets endpoint handles empty trust', function () {
    $trust = Trust::factory()->create([
        'user_id' => $this->user->id,
        'household_id' => $this->household->id,
    ]);

    $response = $this->getJson("/api/estate/trusts/{$trust->id}/assets");

    $response->assertStatus(200);

    $data = $response->json('data');
    expect($data['asset_count'])->toBe(0);
    expect((float) $data['total_value'])->toBe(0.0);
});

test('trust assets endpoint returns correct breakdown', function () {
    $trust = Trust::factory()->create([
        'user_id' => $this->user->id,
        'household_id' => $this->household->id,
    ]);

    Property::factory()->create([
        'user_id' => $this->user->id,
        'trust_id' => $trust->id,
        'current_value' => 400000,
        'ownership_percentage' => 100,
    ]);

    CashAccount::factory()->create([
        'user_id' => $this->user->id,
        'trust_id' => $trust->id,
        'current_balance' => 100000,
        'ownership_percentage' => 100,
    ]);

    $response = $this->getJson("/api/estate/trusts/{$trust->id}/assets");

    $response->assertStatus(200);

    $breakdown = $response->json('data.breakdown');
    expect($breakdown)->toHaveKeys(['properties', 'investment_accounts', 'cash_accounts', 'business_interests', 'chattels']);
    expect((float) $breakdown['properties']['value'])->toBe(400000.0);
    expect($breakdown['properties']['count'])->toBe(1);
});

test('upcoming tax returns handles user with no RPT trusts', function () {
    Trust::factory()->create([
        'user_id' => $this->user->id,
        'household_id' => $this->household->id,
        'trust_type' => 'bare', // Non-RPT
        'is_active' => true,
    ]);

    $response = $this->getJson('/api/estate/trusts/upcoming-tax-returns');

    $response->assertStatus(200);

    $data = $response->json('data');
    expect($data)->toHaveKey('upcoming_periodic_charges');
    expect($data)->toHaveKey('tax_returns');
});

test('trust assets include correct metadata', function () {
    $trust = Trust::factory()->create([
        'user_id' => $this->user->id,
        'household_id' => $this->household->id,
    ]);

    Property::factory()->create([
        'user_id' => $this->user->id,
        'trust_id' => $trust->id,
        'address_line_1' => '123 Test Street',
        'city' => 'London',
        'current_value' => 750000,
        'ownership_percentage' => 100,
    ]);

    $response = $this->getJson("/api/estate/trusts/{$trust->id}/assets");

    $response->assertStatus(200);

    $properties = $response->json('data.assets.properties');
    expect($properties)->toHaveCount(1);
    expect($properties[0]['type'])->toBe('property');
    expect($properties[0]['name'])->toContain('123 Test Street');
});

test('trust assets handle partial ownership', function () {
    $trust = Trust::factory()->create([
        'user_id' => $this->user->id,
        'household_id' => $this->household->id,
    ]);

    Property::factory()->create([
        'user_id' => $this->user->id,
        'trust_id' => $trust->id,
        'current_value' => 600000,
        'ownership_percentage' => 50,
    ]);

    BusinessInterest::factory()->create([
        'user_id' => $this->user->id,
        'trust_id' => $trust->id,
        'current_valuation' => 400000,
        'ownership_percentage' => 75,
    ]);

    $response = $this->getJson("/api/estate/trusts/{$trust->id}/assets");

    $response->assertStatus(200);

    $data = $response->json('data');
    // (600000 * 0.5) + (400000 * 0.75) = 300000 + 300000 = 600000
    expect((float) $data['total_value'])->toBe(600000.0);

    $properties = $response->json('data.assets.properties');
    expect((float) $properties[0]['value'])->toBe(300000.0);
    expect((float) $properties[0]['full_value'])->toBe(600000.0);
});
