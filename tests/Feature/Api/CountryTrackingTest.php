<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Property;
use App\Models\SavingsAccount;
use App\Models\Mortgage;

describe('Country Tracking API', function () {
    describe('Property Country Tracking', function () {
        it('saves property with specified country', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)->postJson('/api/properties', [
                'property_type' => 'residential',
                'ownership_type' => 'individual',
                'current_value' => 250000,
                'purchase_price' => 200000,
                'purchase_date' => '2020-01-01',
                'address_line_1' => '123 Main St',
                'city' => 'Paris',
                'postcode' => '75001',
                'country' => 'France',
            ]);

            $response->assertStatus(201);
            expect($response->json('data.country'))->toBe('France');

            $this->assertDatabaseHas('properties', [
                'user_id' => $user->id,
                'country' => 'France',
            ]);
        });

        it('defaults to United Kingdom when country not provided', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)->postJson('/api/properties', [
                'property_type' => 'residential',
                'ownership_type' => 'individual',
                'current_value' => 250000,
                'purchase_price' => 200000,
                'purchase_date' => '2020-01-01',
                'address_line_1' => '123 Main St',
                'city' => 'London',
                'postcode' => 'SW1A 1AA',
            ]);

            $response->assertStatus(201);
            expect($response->json('data.country'))->toBe('United Kingdom');

            $this->assertDatabaseHas('properties', [
                'user_id' => $user->id,
                'country' => 'United Kingdom',
            ]);
        });

        it('updates property country', function () {
            $user = User::factory()->create();
            $property = Property::factory()->create([
                'user_id' => $user->id,
                'country' => 'United Kingdom',
            ]);

            $response = $this->actingAs($user)->putJson("/api/properties/{$property->id}", [
                'property_type' => $property->property_type,
                'ownership_type' => $property->ownership_type,
                'current_value' => $property->current_value,
                'purchase_price' => $property->purchase_price,
                'purchase_date' => $property->purchase_date->format('Y-m-d'),
                'address_line_1' => $property->address_line_1,
                'city' => $property->city,
                'postcode' => $property->postcode,
                'country' => 'Spain',
            ]);

            $response->assertStatus(200);
            expect($response->json('data.country'))->toBe('Spain');

            $this->assertDatabaseHas('properties', [
                'id' => $property->id,
                'country' => 'Spain',
            ]);
        });
    });

    describe('Savings Account Country Tracking', function () {
        it('saves non-ISA account with specified country', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)->postJson('/api/savings-accounts', [
                'account_type' => 'cash',
                'institution' => 'Foreign Bank',
                'current_balance' => 10000,
                'is_isa' => false,
                'country' => 'Germany',
            ]);

            $response->assertStatus(201);
            expect($response->json('data.country'))->toBe('Germany');

            $this->assertDatabaseHas('savings_accounts', [
                'user_id' => $user->id,
                'country' => 'Germany',
            ]);
        });

        it('forces ISA accounts to United Kingdom', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)->postJson('/api/savings-accounts', [
                'account_type' => 'cash',
                'institution' => 'UK Bank',
                'current_balance' => 15000,
                'is_isa' => true,
                'isa_type' => 'cash',
                'isa_subscription_year' => '2025/26',
                'isa_subscription_amount' => 15000,
                'country' => 'France', // Should be overridden
            ]);

            $response->assertStatus(201);
            expect($response->json('data.country'))->toBe('United Kingdom');

            $this->assertDatabaseHas('savings_accounts', [
                'user_id' => $user->id,
                'is_isa' => true,
                'country' => 'United Kingdom',
            ]);
        });

        it('defaults non-ISA to United Kingdom when country not provided', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)->postJson('/api/savings-accounts', [
                'account_type' => 'cash',
                'institution' => 'UK Bank',
                'current_balance' => 5000,
                'is_isa' => false,
            ]);

            $response->assertStatus(201);
            expect($response->json('data.country'))->toBe('United Kingdom');

            $this->assertDatabaseHas('savings_accounts', [
                'user_id' => $user->id,
                'country' => 'United Kingdom',
            ]);
        });

        it('updates ISA account country to United Kingdom even if different provided', function () {
            $user = User::factory()->create();
            $account = SavingsAccount::factory()->create([
                'user_id' => $user->id,
                'is_isa' => true,
                'country' => 'United Kingdom',
            ]);

            $response = $this->actingAs($user)->putJson("/api/savings-accounts/{$account->id}", [
                'account_type' => $account->account_type,
                'institution' => $account->institution,
                'current_balance' => 20000,
                'is_isa' => true,
                'isa_type' => 'cash',
                'isa_subscription_year' => '2025/26',
                'isa_subscription_amount' => 20000,
                'country' => 'Spain', // Should be overridden
            ]);

            $response->assertStatus(200);
            expect($response->json('data.country'))->toBe('United Kingdom');

            $this->assertDatabaseHas('savings_accounts', [
                'id' => $account->id,
                'country' => 'United Kingdom',
            ]);
        });
    });

    describe('Mortgage Country Tracking', function () {
        it('saves mortgage with specified country', function () {
            $user = User::factory()->create();
            $property = Property::factory()->create([
                'user_id' => $user->id,
                'country' => 'France',
            ]);

            $response = $this->actingAs($user)->postJson('/api/mortgages', [
                'property_id' => $property->id,
                'lender' => 'French Bank',
                'mortgage_type' => 'repayment',
                'original_amount' => 200000,
                'outstanding_balance' => 180000,
                'interest_rate' => 3.5,
                'term_years' => 25,
                'start_date' => '2020-01-01',
                'country' => 'France',
            ]);

            $response->assertStatus(201);
            expect($response->json('data.country'))->toBe('France');

            $this->assertDatabaseHas('mortgages', [
                'user_id' => $user->id,
                'country' => 'France',
            ]);
        });

        it('defaults to United Kingdom when country not provided', function () {
            $user = User::factory()->create();
            $property = Property::factory()->create([
                'user_id' => $user->id,
            ]);

            $response = $this->actingAs($user)->postJson('/api/mortgages', [
                'property_id' => $property->id,
                'lender' => 'UK Bank',
                'mortgage_type' => 'repayment',
                'original_amount' => 150000,
                'outstanding_balance' => 140000,
                'interest_rate' => 4.0,
                'term_years' => 20,
                'start_date' => '2021-01-01',
            ]);

            $response->assertStatus(201);
            expect($response->json('data.country'))->toBe('United Kingdom');

            $this->assertDatabaseHas('mortgages', [
                'user_id' => $user->id,
                'country' => 'United Kingdom',
            ]);
        });

        it('updates mortgage country', function () {
            $user = User::factory()->create();
            $property = Property::factory()->create(['user_id' => $user->id]);
            $mortgage = Mortgage::factory()->create([
                'user_id' => $user->id,
                'property_id' => $property->id,
                'country' => 'United Kingdom',
            ]);

            $response = $this->actingAs($user)->putJson("/api/mortgages/{$mortgage->id}", [
                'property_id' => $mortgage->property_id,
                'lender' => $mortgage->lender,
                'mortgage_type' => $mortgage->mortgage_type,
                'original_amount' => $mortgage->original_amount,
                'outstanding_balance' => 130000,
                'interest_rate' => $mortgage->interest_rate,
                'term_years' => $mortgage->term_years,
                'start_date' => $mortgage->start_date->format('Y-m-d'),
                'country' => 'Portugal',
            ]);

            $response->assertStatus(200);
            expect($response->json('data.country'))->toBe('Portugal');

            $this->assertDatabaseHas('mortgages', [
                'id' => $mortgage->id,
                'country' => 'Portugal',
            ]);
        });
    });

    describe('Country Field Validation', function () {
        it('accepts valid country names', function () {
            $user = User::factory()->create();

            $countries = [
                'United Kingdom',
                'United States',
                'France',
                'Germany',
                'Spain',
                'Australia',
                'Canada',
            ];

            foreach ($countries as $country) {
                $response = $this->actingAs($user)->postJson('/api/properties', [
                    'property_type' => 'residential',
                    'ownership_type' => 'individual',
                    'current_value' => 250000,
                    'purchase_price' => 200000,
                    'purchase_date' => '2020-01-01',
                    'address_line_1' => '123 Main St',
                    'city' => 'Test City',
                    'postcode' => '12345',
                    'country' => $country,
                ]);

                $response->assertStatus(201);
                expect($response->json('data.country'))->toBe($country);
            }
        });

        it('accepts null country and applies default', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)->postJson('/api/properties', [
                'property_type' => 'residential',
                'ownership_type' => 'individual',
                'current_value' => 250000,
                'purchase_price' => 200000,
                'purchase_date' => '2020-01-01',
                'address_line_1' => '123 Main St',
                'city' => 'London',
                'postcode' => 'SW1A 1AA',
                'country' => null,
            ]);

            $response->assertStatus(201);
            expect($response->json('data.country'))->toBe('United Kingdom');
        });
    });

    describe('Authorization', function () {
        it('prevents users from accessing other users properties country data', function () {
            $user1 = User::factory()->create();
            $user2 = User::factory()->create();

            $property = Property::factory()->create([
                'user_id' => $user2->id,
                'country' => 'France',
            ]);

            $response = $this->actingAs($user1)->getJson("/api/properties/{$property->id}");

            // Should either be 403 Forbidden or 404 Not Found depending on authorization strategy
            expect($response->status())->toBeIn([403, 404]);
        });
    });
});
