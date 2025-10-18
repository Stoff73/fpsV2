<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Household;
use App\Models\PersonalAccount;
use App\Models\Property;
use App\Models\Investment\InvestmentAccount;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a household
    $this->household = Household::factory()->create();

    // Create a test user with income data
    $this->user = User::factory()->create([
        'household_id' => $this->household->id,
        'annual_employment_income' => 75000.00,
        'annual_self_employment_income' => 0.00,
        'annual_rental_income' => 12000.00,
        'annual_dividend_income' => 3000.00,
        'annual_other_income' => 0.00,
    ]);

    // Create a property for testing
    $this->property = Property::factory()->create([
        'user_id' => $this->user->id,
        'current_value' => 500000.00,
        'ownership_percentage' => 100.00,
    ]);

    // Create an investment account
    $this->investment = InvestmentAccount::factory()->create([
        'user_id' => $this->user->id,
        'current_value' => 50000.00,
        'ownership_percentage' => 100.00,
    ]);

    // Authenticate as this user
    $this->actingAs($this->user, 'sanctum');
});

describe('GET /api/user/personal-accounts', function () {
    test('returns personal accounts data for authenticated user', function () {
        $response = $this->getJson('/api/user/personal-accounts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'accounts',
                ],
            ]);
    });

    test('requires authentication', function () {
        // Create a new test instance without authentication
        $this->app = $this->createApplication();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->getJson('/api/user/personal-accounts');

        $response->assertStatus(401);
    });
});

describe('POST /api/user/personal-accounts/calculate', function () {
    test('calculates profit and loss correctly', function () {
        $requestData = [
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ];

        $response = $this->postJson('/api/user/personal-accounts/calculate', $requestData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'profit_and_loss',
                    'cashflow',
                    'balance_sheet',
                    'period' => [
                        'start_date',
                        'end_date',
                        'as_of_date',
                    ],
                ],
            ]);

        // Verify profit and loss calculations
        $data = $response->json('data.profit_and_loss');
        expect($data['total_income'])->toBeGreaterThan(0);
        expect($data)->toHaveKeys(['period', 'income', 'total_income', 'expenses', 'total_expenses', 'net_profit_loss']);
    });

    test('calculates cashflow correctly', function () {
        $requestData = [
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ];

        $response = $this->postJson('/api/user/personal-accounts/calculate', $requestData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'profit_and_loss',
                    'cashflow',
                    'balance_sheet',
                    'period',
                ],
            ]);

        $data = $response->json('data.cashflow');
        expect($data)->toHaveKeys(['period', 'inflows', 'total_inflows', 'outflows', 'total_outflows', 'net_cashflow']);
    });

    test('calculates balance sheet correctly', function () {
        $requestData = [
            'as_of_date' => '2024-12-31',
        ];

        $response = $this->postJson('/api/user/personal-accounts/calculate', $requestData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'profit_and_loss',
                    'cashflow',
                    'balance_sheet',
                    'period',
                ],
            ]);

        $data = $response->json('data.balance_sheet');
        expect($data['total_assets'])->toBeGreaterThan(0);
        expect($data)->toHaveKeys(['as_of_date', 'assets', 'total_assets', 'liabilities', 'total_liabilities', 'equity', 'total_equity']);
    });

    test('requires authentication', function () {
        // Create a new test instance without authentication
        $this->app = $this->createApplication();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->postJson('/api/user/personal-accounts/calculate', [
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);

        $response->assertStatus(401);
    });
});

describe('POST /api/user/personal-accounts/line-item', function () {
    test('creates a manual line item successfully', function () {
        $lineItemData = [
            'account_type' => 'profit_and_loss',
            'period_start' => '2024-01-01',
            'period_end' => '2024-12-31',
            'line_item' => 'Consulting Income',
            'category' => 'income',
            'amount' => 15000.00,
            'notes' => 'Side project consulting',
        ];

        $response = $this->postJson('/api/user/personal-accounts/line-item', $lineItemData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Line item added successfully',
            ]);

        // Verify database
        $this->assertDatabaseHas('personal_accounts', [
            'user_id' => $this->user->id,
            'account_type' => 'profit_and_loss',
            'line_item' => 'Consulting Income',
            'amount' => 15000.00,
        ]);
    });

    test('validates required fields', function () {
        $invalidData = [
            'account_type' => 'profit_and_loss',
            // Missing required fields
        ];

        $response = $this->postJson('/api/user/personal-accounts/line-item', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['line_item', 'category', 'amount']);
    });

    test('validates amount is numeric', function () {
        $invalidData = [
            'account_type' => 'profit_and_loss',
            'period_start' => '2024-01-01',
            'period_end' => '2024-12-31',
            'line_item' => 'Test',
            'category' => 'income',
            'amount' => 'not-a-number',
        ];

        $response = $this->postJson('/api/user/personal-accounts/line-item', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    });

    test('requires authentication', function () {
        // Create a new test instance without authentication
        $this->app = $this->createApplication();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->postJson('/api/user/personal-accounts/line-item', [
            'account_type' => 'profit_and_loss',
            'period_start' => '2024-01-01',
            'period_end' => '2024-12-31',
            'line_item' => 'Test',
            'category' => 'income',
            'amount' => 100,
        ]);

        $response->assertStatus(401);
    });
});

describe('PUT /api/user/personal-accounts/line-item/{id}', function () {
    test('updates a line item successfully', function () {
        // Create a line item first
        $lineItem = PersonalAccount::factory()->create([
            'user_id' => $this->user->id,
            'account_type' => 'profit_and_loss',
            'line_item' => 'Original Item',
            'amount' => 1000.00,
        ]);

        $updatedData = [
            'line_item' => 'Updated Item',
            'amount' => 2000.00,
            'notes' => 'Updated notes',
        ];

        $response = $this->putJson("/api/user/personal-accounts/line-item/{$lineItem->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Line item updated successfully',
            ]);

        // Verify database
        $this->assertDatabaseHas('personal_accounts', [
            'id' => $lineItem->id,
            'line_item' => 'Updated Item',
            'amount' => 2000.00,
        ]);
    });

    test('user cannot update another user line item', function () {
        // Create another user with a line item
        $otherUser = User::factory()->create([
            'household_id' => Household::factory()->create()->id,
        ]);
        $otherLineItem = PersonalAccount::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->putJson("/api/user/personal-accounts/line-item/{$otherLineItem->id}", [
            'line_item' => 'Unauthorized Update',
            'amount' => 999.99,
        ]);

        $response->assertStatus(404);
    });

    test('returns 404 for non-existent line item', function () {
        $response = $this->putJson('/api/user/personal-accounts/line-item/99999', [
            'line_item' => 'Test',
            'amount' => 100,
        ]);

        $response->assertStatus(404);
    });

    test('requires authentication', function () {
        $lineItem = PersonalAccount::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Create a new test instance without authentication
        $this->app = $this->createApplication();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->putJson("/api/user/personal-accounts/line-item/{$lineItem->id}", [
            'line_item' => 'Test',
        ]);

        $response->assertStatus(401);
    });
});

describe('DELETE /api/user/personal-accounts/line-item/{id}', function () {
    test('deletes a line item successfully', function () {
        $lineItem = PersonalAccount::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->deleteJson("/api/user/personal-accounts/line-item/{$lineItem->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Line item deleted successfully',
            ]);

        // Verify database
        $this->assertDatabaseMissing('personal_accounts', [
            'id' => $lineItem->id,
        ]);
    });

    test('user cannot delete another user line item', function () {
        $otherUser = User::factory()->create([
            'household_id' => Household::factory()->create()->id,
        ]);
        $otherLineItem = PersonalAccount::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->deleteJson("/api/user/personal-accounts/line-item/{$otherLineItem->id}");

        $response->assertStatus(404);

        // Verify database still has the record
        $this->assertDatabaseHas('personal_accounts', [
            'id' => $otherLineItem->id,
        ]);
    });

    test('returns 404 for non-existent line item', function () {
        $response = $this->deleteJson('/api/user/personal-accounts/line-item/99999');

        $response->assertStatus(404);
    });

    test('requires authentication', function () {
        $lineItem = PersonalAccount::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Create a new test instance without authentication
        $this->app = $this->createApplication();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->deleteJson("/api/user/personal-accounts/line-item/{$lineItem->id}");

        $response->assertStatus(401);
    });
});
