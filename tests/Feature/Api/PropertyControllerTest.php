<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_can_list_user_properties(): void
    {
        Property::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        // Create properties for another user (should not be returned)
        $otherUser = User::factory()->create();
        Property::factory()->count(2)->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/properties');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'properties' => [
                        '*' => [
                            'id',
                            'property_type',
                            'address',
                            'current_value',
                            'equity',
                        ],
                    ],
                ],
            ]);

        // Should only return 3 properties (user's own)
        expect($response->json('data.properties'))->toHaveCount(3);
    }

    public function test_can_create_property(): void
    {
        $propertyData = [
            'property_type' => 'main_residence',
            'ownership_type' => 'individual',
            'ownership_percentage' => 100,
            'address_line_1' => '123 Test Street',
            'city' => 'London',
            'postcode' => 'SW1A 1AA',
            'purchase_date' => '2020-01-01',
            'purchase_price' => 300000,
            'current_value' => 350000,
        ];

        $response = $this->withToken($this->token)
            ->postJson('/api/properties', $propertyData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'property_type',
                    'current_value',
                ],
            ]);

        $this->assertDatabaseHas('properties', [
            'user_id' => $this->user->id,
            'property_type' => 'main_residence',
            'postcode' => 'SW1A 1AA',
        ]);
    }

    public function test_can_show_property(): void
    {
        $property = Property::factory()->create([
            'user_id' => $this->user->id,
            'property_type' => 'main_residence',
            'current_value' => 400000,
        ]);

        $response = $this->withToken($this->token)
            ->getJson("/api/properties/{$property->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $property->id,
                    'property_type' => 'main_residence',
                    'current_value' => 400000.0,
                ],
            ]);
    }

    public function test_cannot_view_other_user_property(): void
    {
        $otherUser = User::factory()->create();
        $property = Property::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->withToken($this->token)
            ->getJson("/api/properties/{$property->id}");

        $response->assertStatus(404);
    }

    public function test_can_update_property(): void
    {
        $property = Property::factory()->create([
            'user_id' => $this->user->id,
            'current_value' => 300000,
        ]);

        $updateData = [
            'current_value' => 350000,
            'valuation_date' => now()->format('Y-m-d'),
        ];

        $response = $this->withToken($this->token)
            ->putJson("/api/properties/{$property->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $property->id,
                    'current_value' => 350000.0,
                ],
            ]);

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'current_value' => 350000,
        ]);
    }

    public function test_cannot_update_other_user_property(): void
    {
        $otherUser = User::factory()->create();
        $property = Property::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->withToken($this->token)
            ->putJson("/api/properties/{$property->id}", [
                'current_value' => 999999,
            ]);

        $response->assertStatus(404);

        // Ensure property was not updated
        $this->assertDatabaseMissing('properties', [
            'id' => $property->id,
            'current_value' => 999999,
        ]);
    }

    public function test_can_delete_property(): void
    {
        $property = Property::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->withToken($this->token)
            ->deleteJson("/api/properties/{$property->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('properties', [
            'id' => $property->id,
        ]);
    }

    public function test_cannot_delete_other_user_property(): void
    {
        $otherUser = User::factory()->create();
        $property = Property::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->withToken($this->token)
            ->deleteJson("/api/properties/{$property->id}");

        $response->assertStatus(404);

        // Ensure property was not deleted
        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
        ]);
    }

    public function test_validation_fails_for_invalid_data(): void
    {
        $invalidData = [
            'property_type' => 'invalid_type',
            'ownership_percentage' => 150, // Over 100
            'postcode' => 'INVALID',
        ];

        $response = $this->withToken($this->token)
            ->postJson('/api/properties', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['property_type', 'ownership_percentage', 'postcode']);
    }

    public function test_can_calculate_sdlt(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/properties/calculate-sdlt', [
                'purchase_price' => 500000,
                'property_type' => 'main_residence',
                'is_first_home' => false,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_sdlt',
                    'effective_rate',
                    'bands',
                ],
            ]);

        // £500k main residence should be £12,500 SDLT
        expect($response->json('data.total_sdlt'))->toBe(12500.0);
    }

    public function test_can_calculate_cgt_for_property(): void
    {
        $property = Property::factory()->create([
            'user_id' => $this->user->id,
            'property_type' => 'second_home',
            'purchase_price' => 200000,
        ]);

        $response = $this->withToken($this->token)
            ->postJson("/api/properties/{$property->id}/calculate-cgt", [
                'disposal_price' => 300000,
                'disposal_costs' => 5000,
                'improvement_costs' => 0,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'gain',
                    'taxable_gain',
                    'cgt_rate',
                    'cgt_liability',
                ],
            ]);
    }

    public function test_can_calculate_rental_income_tax(): void
    {
        $property = Property::factory()->create([
            'user_id' => $this->user->id,
            'property_type' => 'buy_to_let',
            'annual_rental_income' => 15000,
            'annual_service_charge' => 1000,
        ]);

        $response = $this->withToken($this->token)
            ->postJson("/api/properties/{$property->id}/rental-income-tax");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'gross_income',
                    'allowable_expenses',
                    'taxable_profit',
                    'tax_liability',
                ],
            ]);
    }

    public function test_requires_authentication(): void
    {
        $response = $this->getJson('/api/properties');

        $response->assertStatus(401);
    }

    public function test_create_property_with_btl_fields(): void
    {
        $propertyData = [
            'property_type' => 'buy_to_let',
            'ownership_type' => 'individual',
            'ownership_percentage' => 100,
            'address_line_1' => '456 Rental Street',
            'city' => 'Manchester',
            'postcode' => 'M1 1AA',
            'purchase_date' => '2019-01-01',
            'purchase_price' => 200000,
            'current_value' => 220000,
            'monthly_rental_income' => 1200,
            'annual_rental_income' => 14400,
            'occupancy_rate_percent' => 95,
        ];

        $response = $this->withToken($this->token)
            ->postJson('/api/properties', $propertyData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('properties', [
            'user_id' => $this->user->id,
            'property_type' => 'buy_to_let',
            'monthly_rental_income' => 1200,
        ]);
    }
}
