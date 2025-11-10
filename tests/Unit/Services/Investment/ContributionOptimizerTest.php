<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\Investment\ContributionOptimizer;

beforeEach(function () {
    $this->user = User::factory()->create([
        'marital_status' => 'single',
    ]);
    $this->optimizer = new ContributionOptimizer();
});

describe('ContributionOptimizer', function () {
    it('calculates wrapper allocation correctly for basic rate taxpayer', function () {
        $inputs = [
            'monthly_investable_income' => 1000,
            'lump_sum_amount' => 0,
            'time_horizon' => 20,
            'risk_tolerance' => 'balanced',
            'income_tax_band' => 'basic',
            'expected_return' => 0.06,
        ];

        $result = $this->optimizer->optimizeContributions($this->user->id, $inputs);

        expect($result)->toHaveKeys([
            'wrapper_allocation',
            'lump_sum_analysis',
            'projections',
            'tax_efficiency_score',
            'recommendations',
        ]);

        // Verify wrapper allocation structure
        expect($result['wrapper_allocation'])->toHaveKeys([
            'isa',
            'pension',
            'gia',
        ]);

        // Verify total allocation equals input
        $totalMonthly = $result['wrapper_allocation']['isa']['monthly_contribution']
            + $result['wrapper_allocation']['pension']['monthly_contribution']
            + $result['wrapper_allocation']['gia']['monthly_contribution'];

        expect($totalMonthly)->toBe(1000.0);
    });

    it('prioritizes ISA allocation up to allowance', function () {
        $inputs = [
            'monthly_investable_income' => 500, // £6k annually, within ISA allowance
            'lump_sum_amount' => 0,
            'time_horizon' => 10,
            'risk_tolerance' => 'balanced',
            'income_tax_band' => 'basic',
            'expected_return' => 0.06,
        ];

        $result = $this->optimizer->optimizeContributions($this->user->id, $inputs);

        // Should allocate most/all to ISA when within allowance
        $isaMonthly = $result['wrapper_allocation']['isa']['monthly_contribution'];
        expect($isaMonthly)->toBeGreaterThan(0);
        expect($isaMonthly)->toBeLessThanOrEqual(500);
    });

    it('includes pension for higher rate taxpayers', function () {
        $inputs = [
            'monthly_investable_income' => 2000,
            'lump_sum_amount' => 0,
            'time_horizon' => 25,
            'risk_tolerance' => 'balanced',
            'income_tax_band' => 'higher',
            'expected_return' => 0.06,
        ];

        $result = $this->optimizer->optimizeContributions($this->user->id, $inputs);

        // Higher rate taxpayers should have pension allocation due to tax relief
        $pensionMonthly = $result['wrapper_allocation']['pension']['monthly_contribution'];
        expect($pensionMonthly)->toBeGreaterThan(0);

        // Verify tax relief calculation
        $pensionTaxRelief = $result['wrapper_allocation']['pension']['tax_relief_annual'];
        expect($pensionTaxRelief)->toBeGreaterThan(0);
    });

    it('analyzes lump sum vs DCA correctly', function () {
        $inputs = [
            'monthly_investable_income' => 500,
            'lump_sum_amount' => 10000,
            'time_horizon' => 5,
            'risk_tolerance' => 'balanced',
            'income_tax_band' => 'basic',
            'expected_return' => 0.07,
        ];

        $result = $this->optimizer->optimizeContributions($this->user->id, $inputs);

        expect($result['lump_sum_analysis'])->toHaveKeys([
            'lump_sum_immediate',
            'dca_monthly',
            'recommendation',
            'timing_risk_score',
        ]);

        // Verify recommendation is either 'lump_sum' or 'dca'
        expect($result['lump_sum_analysis']['recommendation'])
            ->toBeIn(['lump_sum', 'dca', 'hybrid']);
    });

    it('calculates tax efficiency score between 0-100', function () {
        $inputs = [
            'monthly_investable_income' => 1500,
            'lump_sum_amount' => 0,
            'time_horizon' => 15,
            'risk_tolerance' => 'balanced',
            'income_tax_band' => 'higher',
            'expected_return' => 0.06,
        ];

        $result = $this->optimizer->optimizeContributions($this->user->id, $inputs);

        expect($result['tax_efficiency_score'])
            ->toBeGreaterThanOrEqual(0)
            ->toBeLessThanOrEqual(100);
    });

    it('generates three projection scenarios', function () {
        $inputs = [
            'monthly_investable_income' => 1000,
            'lump_sum_amount' => 0,
            'time_horizon' => 20,
            'risk_tolerance' => 'balanced',
            'income_tax_band' => 'basic',
            'expected_return' => 0.06,
        ];

        $result = $this->optimizer->optimizeContributions($this->user->id, $inputs);

        expect($result['projections'])->toHaveKeys([
            'conservative',
            'expected',
            'optimistic',
        ]);

        // Verify each scenario has final value
        expect($result['projections']['conservative']['final_value'])->toBeGreaterThan(0);
        expect($result['projections']['expected']['final_value'])->toBeGreaterThan(0);
        expect($result['projections']['optimistic']['final_value'])->toBeGreaterThan(0);

        // Verify scenarios are ordered correctly
        expect($result['projections']['optimistic']['final_value'])
            ->toBeGreaterThan($result['projections']['expected']['final_value']);
        expect($result['projections']['expected']['final_value'])
            ->toBeGreaterThan($result['projections']['conservative']['final_value']);
    });

    it('provides actionable recommendations', function () {
        $inputs = [
            'monthly_investable_income' => 1000,
            'lump_sum_amount' => 0,
            'time_horizon' => 20,
            'risk_tolerance' => 'balanced',
            'income_tax_band' => 'basic',
            'expected_return' => 0.06,
        ];

        $result = $this->optimizer->optimizeContributions($this->user->id, $inputs);

        expect($result['recommendations'])->toBeArray();
        expect($result['recommendations'])->not->toBeEmpty();

        // Verify each recommendation has required fields
        foreach ($result['recommendations'] as $recommendation) {
            expect($recommendation)->toHaveKeys(['priority', 'title', 'description']);
        }
    });

    it('respects ISA allowance limits', function () {
        $inputs = [
            'monthly_investable_income' => 3000, // £36k annually, exceeds ISA allowance
            'lump_sum_amount' => 0,
            'time_horizon' => 10,
            'risk_tolerance' => 'balanced',
            'income_tax_band' => 'higher',
            'expected_return' => 0.06,
        ];

        $result = $this->optimizer->optimizeContributions($this->user->id, $inputs);

        // ISA allocation should not exceed £20k annual allowance (£1,667 monthly)
        $isaMonthly = $result['wrapper_allocation']['isa']['monthly_contribution'];
        $isaAnnual = $isaMonthly * 12;
        expect($isaAnnual)->toBeLessThanOrEqual(20000);

        // Overflow should go to Pension or GIA
        $pensionMonthly = $result['wrapper_allocation']['pension']['monthly_contribution'];
        $giaMonthly = $result['wrapper_allocation']['gia']['monthly_contribution'];
        expect($pensionMonthly + $giaMonthly)->toBeGreaterThan(0);
    });

    it('handles zero investable income gracefully', function () {
        $inputs = [
            'monthly_investable_income' => 0,
            'lump_sum_amount' => 0,
            'time_horizon' => 10,
            'risk_tolerance' => 'balanced',
            'income_tax_band' => 'basic',
            'expected_return' => 0.06,
        ];

        $result = $this->optimizer->optimizeContributions($this->user->id, $inputs);

        // Should still return valid structure
        expect($result)->toHaveKeys([
            'wrapper_allocation',
            'lump_sum_analysis',
            'projections',
            'tax_efficiency_score',
            'recommendations',
        ]);

        // All allocations should be zero
        expect($result['wrapper_allocation']['isa']['monthly_contribution'])->toBe(0.0);
        expect($result['wrapper_allocation']['pension']['monthly_contribution'])->toBe(0.0);
        expect($result['wrapper_allocation']['gia']['monthly_contribution'])->toBe(0.0);
    });

    it('calculates pension tax relief correctly for higher rate', function () {
        $inputs = [
            'monthly_investable_income' => 1000,
            'lump_sum_amount' => 0,
            'time_horizon' => 20,
            'risk_tolerance' => 'balanced',
            'income_tax_band' => 'higher',
            'expected_return' => 0.06,
        ];

        $result = $this->optimizer->optimizeContributions($this->user->id, $inputs);

        if ($result['wrapper_allocation']['pension']['monthly_contribution'] > 0) {
            $pensionContribution = $result['wrapper_allocation']['pension']['monthly_contribution'];
            $taxRelief = $result['wrapper_allocation']['pension']['tax_relief_annual'];

            // Higher rate: 40% tax relief
            $expectedRelief = ($pensionContribution * 12) * 0.40;

            // Allow 1% tolerance for rounding
            expect($taxRelief)->toBeGreaterThanOrEqual($expectedRelief * 0.99);
            expect($taxRelief)->toBeLessThanOrEqual($expectedRelief * 1.01);
        }
    });

    it('adjusts allocation based on risk tolerance', function () {
        $baseInputs = [
            'monthly_investable_income' => 1000,
            'lump_sum_amount' => 0,
            'time_horizon' => 20,
            'income_tax_band' => 'basic',
            'expected_return' => 0.06,
        ];

        $cautiousResult = $this->optimizer->optimizeContributions($this->user->id, array_merge($baseInputs, ['risk_tolerance' => 'cautious']));
        $adventurousResult = $this->optimizer->optimizeContributions($this->user->id, array_merge($baseInputs, ['risk_tolerance' => 'adventurous']));

        // Risk tolerance should affect projections
        expect($cautiousResult['projections'])->toBeDifferentFrom($adventurousResult['projections']);
    });
});
