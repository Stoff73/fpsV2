<?php

declare(strict_types=1);

use App\Models\ProtectionProfile;
use App\Models\User;
use App\Services\Protection\RecommendationEngine;

beforeEach(function () {
    $this->engine = new RecommendationEngine;
});

describe('generateRecommendations', function () {
    it('generates life insurance recommendation for large human capital gap', function () {
        $user = User::factory()->create(['date_of_birth' => now()->subYears(35)]);
        $profile = ProtectionProfile::factory()->create([
            'user_id' => $user->id,
            'annual_income' => 50000,
            'number_of_dependents' => 2,
            'mortgage_balance' => 0,
            'other_debts' => 0,
        ]);

        $gaps = [
            'gaps_by_category' => [
                'human_capital_gap' => 500000,
                'debt_protection_gap' => 0,
                'education_funding_gap' => 0,
                'income_protection_gap' => 0,
            ],
        ];

        $result = $this->engine->generateRecommendations($gaps, $profile);

        expect($result)->toBeArray();
        expect($result)->not->toBeEmpty();
        expect($result[0])->toHaveKeys(['priority', 'category', 'action', 'rationale', 'impact', 'estimated_cost']);
        expect($result[0]['category'])->toBe('Life Insurance');
        expect($result[0]['action'])->toContain('Increase life insurance');
    });

    it('generates debt protection recommendation', function () {
        $user = User::factory()->create();
        $profile = ProtectionProfile::factory()->create([
            'user_id' => $user->id,
            'annual_income' => 50000,
            'mortgage_balance' => 250000,
            'other_debts' => 25000,
        ]);

        $gaps = [
            'gaps_by_category' => [
                'human_capital_gap' => 0,
                'debt_protection_gap' => 275000,
                'education_funding_gap' => 0,
                'income_protection_gap' => 0,
            ],
        ];

        $result = $this->engine->generateRecommendations($gaps, $profile);

        expect($result)->toBeArray();
        $debtRec = collect($result)->first(fn ($r) => str_contains($r['action'], 'debt'));
        expect($debtRec)->not->toBeNull();
        expect($debtRec['category'])->toBe('Life Insurance');
    });

    it('generates income protection recommendation', function () {
        $user = User::factory()->create();
        $profile = ProtectionProfile::factory()->create([
            'user_id' => $user->id,
            'annual_income' => 50000,
        ]);

        $gaps = [
            'gaps_by_category' => [
                'human_capital_gap' => 0,
                'debt_protection_gap' => 0,
                'education_funding_gap' => 0,
                'income_protection_gap' => 30000,
            ],
        ];

        $result = $this->engine->generateRecommendations($gaps, $profile);

        expect($result)->toBeArray();
        $incomeRec = collect($result)->first(fn ($r) => $r['category'] === 'Income Protection');
        expect($incomeRec)->not->toBeNull();
        expect($incomeRec['action'])->toContain('income protection');
    });

    it('returns empty array when no gaps exist', function () {
        $user = User::factory()->create();
        $profile = ProtectionProfile::factory()->create([
            'user_id' => $user->id,
            'annual_income' => 50000,
            'mortgage_balance' => 0,
            'other_debts' => 0,
        ]);

        $gaps = [
            'gaps_by_category' => [
                'human_capital_gap' => 0,
                'debt_protection_gap' => 0,
                'education_funding_gap' => 0,
                'income_protection_gap' => 0,
            ],
        ];

        $result = $this->engine->generateRecommendations($gaps, $profile);

        expect($result)->toBeArray();
        expect($result)->toBeEmpty();
    });

    it('sorts recommendations by priority', function () {
        $user = User::factory()->create();
        $profile = ProtectionProfile::factory()->create([
            'user_id' => $user->id,
            'annual_income' => 50000,
            'number_of_dependents' => 2,
            'mortgage_balance' => 100000,
            'other_debts' => 0,
        ]);

        $gaps = [
            'gaps_by_category' => [
                'human_capital_gap' => 500000,
                'debt_protection_gap' => 100000,
                'education_funding_gap' => 50000,
                'income_protection_gap' => 30000,
            ],
        ];

        $result = $this->engine->generateRecommendations($gaps, $profile);

        expect($result)->toBeArray();
        expect($result)->not->toBeEmpty();

        // Verify priorities are ascending
        $priorities = array_column($result, 'priority');
        $sortedPriorities = $priorities;
        sort($sortedPriorities);
        expect($priorities)->toEqual($sortedPriorities);
    });

    it('includes estimated cost in recommendations', function () {
        $user = User::factory()->create(['date_of_birth' => now()->subYears(35)]);
        $profile = ProtectionProfile::factory()->create([
            'user_id' => $user->id,
            'annual_income' => 50000,
            'smoker_status' => false,
        ]);

        $gaps = [
            'gaps_by_category' => [
                'human_capital_gap' => 500000,
                'debt_protection_gap' => 0,
                'education_funding_gap' => 0,
                'income_protection_gap' => 0,
            ],
        ];

        $result = $this->engine->generateRecommendations($gaps, $profile);

        expect($result)->not->toBeEmpty();
        expect($result[0]['estimated_cost'])->toBeGreaterThan(0);
    });
});
