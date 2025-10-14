<?php

declare(strict_types=1);

use App\Models\Investment\Holding;
use App\Models\Investment\InvestmentAccount;
use App\Models\Investment\InvestmentGoal;
use App\Models\Investment\RiskProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'name' => 'Test Investor',
        'email' => 'investor@example.com',
    ]);

    Sanctum::actingAs($this->user);
});

describe('Investment Account Management', function () {
    it('can create an investment account', function () {
        $response = $this->postJson('/api/investment/accounts', [
            'account_type' => 'isa',
            'provider' => 'Vanguard',
            'account_number' => 'ISA123456',
            'platform' => 'Vanguard Investor',
            'current_value' => 50000.00,
            'contributions_ytd' => 15000.00,
            'tax_year' => '2024/25',
            'platform_fee_percent' => 0.15,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'account_type',
                    'provider',
                    'current_value',
                    'contributions_ytd',
                    'tax_year',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'account_type' => 'isa',
                    'provider' => 'Vanguard',
                    'current_value' => 50000.0,
                ],
            ]);

        $this->assertDatabaseHas('investment_accounts', [
            'user_id' => $this->user->id,
            'account_type' => 'isa',
            'provider' => 'Vanguard',
        ]);
    });

    it('can update an investment account', function () {
        $account = InvestmentAccount::factory()->create([
            'user_id' => $this->user->id,
            'account_type' => 'gia',
            'provider' => 'Hargreaves Lansdown',
            'current_value' => 75000,
        ]);

        $response = $this->putJson("/api/investment/accounts/{$account->id}", [
            'current_value' => 80000,
            'provider' => 'Updated Provider',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'current_value' => 80000.0,
                    'provider' => 'Updated Provider',
                ],
            ]);
    });

    it('can delete an investment account', function () {
        $account = InvestmentAccount::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->deleteJson("/api/investment/accounts/{$account->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Account deleted successfully',
            ]);

        $this->assertDatabaseMissing('investment_accounts', [
            'id' => $account->id,
        ]);
    });

    it('validates account type on creation', function () {
        $response = $this->postJson('/api/investment/accounts', [
            'account_type' => 'invalid_type',
            'provider' => 'Test Provider',
            'current_value' => 50000,
            'tax_year' => '2024/25',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['account_type']);
    });
});

describe('Holdings Management', function () {
    it('can create a holding', function () {
        $account = InvestmentAccount::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->postJson('/api/investment/holdings', [
            'investment_account_id' => $account->id,
            'asset_type' => 'equity',
            'security_name' => 'Vanguard S&P 500 ETF',
            'ticker' => 'VOO',
            'isin' => 'US9229083632',
            'quantity' => 100,
            'purchase_price' => 350.00,
            'purchase_date' => '2023-01-15',
            'current_price' => 420.00,
            'current_value' => 42000.00,
            'cost_basis' => 35000.00,
            'dividend_yield' => 1.50,
            'ocf_percent' => 0.03,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'security_name',
                    'ticker',
                    'asset_type',
                    'current_value',
                ],
            ]);

        $this->assertDatabaseHas('holdings', [
            'investment_account_id' => $account->id,
            'security_name' => 'Vanguard S&P 500 ETF',
            'ticker' => 'VOO',
        ]);
    });

    it('can update a holding', function () {
        $account = InvestmentAccount::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $holding = Holding::factory()->create([
            'investment_account_id' => $account->id,
            'current_price' => 400,
            'current_value' => 40000,
        ]);

        $response = $this->putJson("/api/investment/holdings/{$holding->id}", [
            'current_price' => 450,
            'current_value' => 45000,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'current_price' => 450.0,
                    'current_value' => 45000.0,
                ],
            ]);
    });

    it('can delete a holding', function () {
        $account = InvestmentAccount::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $holding = Holding::factory()->create([
            'investment_account_id' => $account->id,
        ]);

        $response = $this->deleteJson("/api/investment/holdings/{$holding->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('holdings', [
            'id' => $holding->id,
        ]);
    });

    it('prevents accessing holdings from other users', function () {
        $otherUser = User::factory()->create();
        $otherAccount = InvestmentAccount::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $holding = Holding::factory()->create([
            'investment_account_id' => $otherAccount->id,
        ]);

        $response = $this->putJson("/api/investment/holdings/{$holding->id}", [
            'current_value' => 99999,
        ]);

        $response->assertStatus(404);
    });
});

describe('Investment Goals Management', function () {
    it('can create an investment goal', function () {
        $response = $this->postJson('/api/investment/goals', [
            'goal_name' => 'Retirement Fund',
            'goal_type' => 'retirement',
            'target_amount' => 1000000,
            'target_date' => '2045-12-31',
            'priority' => 'high',
            'is_essential' => true,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'goal_name' => 'Retirement Fund',
                    'goal_type' => 'retirement',
                    'target_amount' => 1000000.0,
                ],
            ]);
    });

    it('can update an investment goal', function () {
        $goal = InvestmentGoal::factory()->create([
            'user_id' => $this->user->id,
            'target_amount' => 500000,
        ]);

        $response = $this->putJson("/api/investment/goals/{$goal->id}", [
            'target_amount' => 750000,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'target_amount' => 750000.0,
                ],
            ]);
    });

    it('can delete an investment goal', function () {
        $goal = InvestmentGoal::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->deleteJson("/api/investment/goals/{$goal->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('investment_goals', [
            'id' => $goal->id,
        ]);
    });
});

describe('Risk Profile Management', function () {
    it('can create or update risk profile', function () {
        $response = $this->postJson('/api/investment/risk-profile', [
            'risk_tolerance' => 'balanced',
            'capacity_for_loss_percent' => 25,
            'time_horizon_years' => 20,
            'knowledge_level' => 'intermediate',
            'attitude_to_volatility' => 'Comfortable with moderate fluctuations',
            'esg_preference' => true,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'risk_tolerance' => 'balanced',
                    'capacity_for_loss_percent' => 25.0,
                    'knowledge_level' => 'intermediate',
                ],
            ]);

        $this->assertDatabaseHas('risk_profiles', [
            'user_id' => $this->user->id,
            'risk_tolerance' => 'balanced',
        ]);
    });

    it('updates existing risk profile instead of creating duplicate', function () {
        RiskProfile::factory()->create([
            'user_id' => $this->user->id,
            'risk_tolerance' => 'cautious',
        ]);

        $response = $this->postJson('/api/investment/risk-profile', [
            'risk_tolerance' => 'adventurous',
            'capacity_for_loss_percent' => 40,
            'time_horizon_years' => 25,
            'knowledge_level' => 'experienced',
        ]);

        $response->assertStatus(200);

        // Should have only one risk profile
        expect(RiskProfile::where('user_id', $this->user->id)->count())->toBe(1);

        $this->assertDatabaseHas('risk_profiles', [
            'user_id' => $this->user->id,
            'risk_tolerance' => 'adventurous',
        ]);
    });
});

describe('Portfolio Analysis', function () {
    it('returns investment overview', function () {
        InvestmentAccount::factory()->count(2)->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson('/api/investment');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'accounts',
                    'goals',
                    'risk_profile',
                ],
            ]);
    });

    it('runs comprehensive portfolio analysis', function () {
        $account = InvestmentAccount::factory()->create([
            'user_id' => $this->user->id,
            'current_value' => 100000,
        ]);

        Holding::factory()->count(3)->create([
            'investment_account_id' => $account->id,
        ]);

        RiskProfile::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->postJson('/api/investment/analyze');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'analysis' => [
                        'portfolio_summary',
                        'returns',
                        'asset_allocation',
                        'diversification_score',
                        'risk_metrics',
                        'fee_analysis',
                        'tax_efficiency',
                    ],
                    'recommendations',
                ],
            ]);
    });

    it('returns message when no accounts exist', function () {
        $response = $this->postJson('/api/investment/analyze');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'message' => 'No investment accounts found',
                ],
            ]);
    });

    it('can fetch recommendations', function () {
        $account = InvestmentAccount::factory()->create([
            'user_id' => $this->user->id,
        ]);

        Holding::factory()->count(2)->create([
            'investment_account_id' => $account->id,
        ]);

        $response = $this->getJson('/api/investment/recommendations');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'recommendation_count',
                    'recommendations',
                ],
            ]);
    });
});

describe('What-If Scenarios', function () {
    it('builds investment scenarios', function () {
        InvestmentAccount::factory()->create([
            'user_id' => $this->user->id,
            'current_value' => 50000,
        ]);

        $response = $this->postJson('/api/investment/scenarios', [
            'monthly_contribution' => 1000,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'scenario_count',
                    'scenarios',
                    'note',
                ],
            ])
            ->assertJsonPath('data.scenario_count', fn ($count) => $count >= 3);
    });
});

describe('Monte Carlo Simulation', function () {
    it('can start Monte Carlo simulation', function () {
        $response = $this->postJson('/api/investment/monte-carlo', [
            'start_value' => 100000,
            'monthly_contribution' => 1000,
            'expected_return' => 0.07,
            'volatility' => 0.12,
            'years' => 20,
            'iterations' => 1000,
            'goal_amount' => 500000,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'job_id',
                    'status',
                    'message',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'queued',
                ],
            ]);
    });

    it('validates Monte Carlo parameters', function () {
        $response = $this->postJson('/api/investment/monte-carlo', [
            'start_value' => -1000, // Invalid negative value
            'monthly_contribution' => 500,
            'expected_return' => 0.07,
            'volatility' => 0.12,
            'years' => 10,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_value']);
    });

    it('validates volatility is between 0 and 1', function () {
        $response = $this->postJson('/api/investment/monte-carlo', [
            'start_value' => 100000,
            'monthly_contribution' => 500,
            'expected_return' => 0.07,
            'volatility' => 1.5, // Invalid > 1
            'years' => 10,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['volatility']);
    });

    it('can retrieve Monte Carlo results', function () {
        $jobId = 'test-job-id-12345';

        // Simulate completed job
        Cache::put("monte_carlo_status_{$jobId}", 'completed', 3600);
        Cache::put("monte_carlo_results_{$jobId}", [
            'summary' => ['iterations' => 1000],
            'year_by_year' => [],
        ], 3600);

        $response = $this->getJson("/api/investment/monte-carlo/{$jobId}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'job_id' => $jobId,
                    'status' => 'completed',
                ],
            ]);
    });

    it('returns 404 for non-existent Monte Carlo job', function () {
        $response = $this->getJson('/api/investment/monte-carlo/non-existent-job');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Job not found',
            ]);
    });
});

describe('Authorization', function () {
    it('requires authentication for all endpoints', function () {
        Sanctum::actingAs(User::factory()->create(['id' => 999]));

        $this->getJson('/api/investment')->assertStatus(200);
        $this->postJson('/api/investment/analyze')->assertStatus(200);
    });

    it('prevents accessing other users data', function () {
        $otherUser = User::factory()->create();
        $account = InvestmentAccount::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->deleteJson("/api/investment/accounts/{$account->id}");

        $response->assertStatus(404);
    });
});
