<?php

declare(strict_types=1);

use App\Models\Investment\Holding;
use App\Models\Investment\InvestmentAccount;
use App\Models\Investment\RiskProfile;
use App\Services\Investment\PortfolioAnalyzer;

beforeEach(function () {
    $this->analyzer = new PortfolioAnalyzer;
});

describe('calculateTotalValue', function () {
    it('calculates total value across all accounts', function () {
        $accounts = collect([
            new InvestmentAccount(['current_value' => 50000]),
            new InvestmentAccount(['current_value' => 75000]),
            new InvestmentAccount(['current_value' => 25000]),
        ]);

        $total = $this->analyzer->calculateTotalValue($accounts);

        expect($total)->toBe(150000.0);
    });

    it('returns zero for empty accounts collection', function () {
        $accounts = collect([]);

        $total = $this->analyzer->calculateTotalValue($accounts);

        expect($total)->toBe(0.0);
    });

    it('handles decimal values correctly', function () {
        $accounts = collect([
            new InvestmentAccount(['current_value' => 12345.67]),
            new InvestmentAccount(['current_value' => 23456.78]),
        ]);

        $total = $this->analyzer->calculateTotalValue($accounts);

        expect($total)->toBe(35802.45);
    });
});

describe('calculateReturns', function () {
    it('calculates gains and returns for holdings', function () {
        $holdings = collect([
            new Holding([
                'security_name' => 'Stock A',
                'cost_basis' => 10000,
                'current_value' => 12000,
            ]),
            new Holding([
                'security_name' => 'Stock B',
                'cost_basis' => 5000,
                'current_value' => 4500,
            ]),
        ]);

        $returns = $this->analyzer->calculateReturns($holdings);

        expect($returns['total_cost_basis'])->toBe(15000.0)
            ->and($returns['total_current_value'])->toBe(16500.0)
            ->and($returns['total_gain'])->toBe(1500.0)
            ->and($returns['total_return_percent'])->toBe(10.0);
    });

    it('returns zero returns for empty holdings', function () {
        $holdings = collect([]);

        $returns = $this->analyzer->calculateReturns($holdings);

        expect($returns['total_return_percent'])->toBe(0.0)
            ->and($returns['total_gain'])->toBe(0.0);
    });

    it('handles negative returns correctly', function () {
        $holdings = collect([
            new Holding([
                'security_name' => 'Stock C',
                'cost_basis' => 10000,
                'current_value' => 8000,
            ]),
        ]);

        $returns = $this->analyzer->calculateReturns($holdings);

        expect($returns['total_gain'])->toBe(-2000.0)
            ->and($returns['total_return_percent'])->toBe(-20.0);
    });
});

describe('calculateAssetAllocation', function () {
    it('calculates asset allocation by type', function () {
        $holdings = collect([
            new Holding(['asset_type' => 'equity', 'current_value' => 60000]),
            new Holding(['asset_type' => 'bond', 'current_value' => 30000]),
            new Holding(['asset_type' => 'equity', 'current_value' => 10000]),
        ]);

        $allocation = $this->analyzer->calculateAssetAllocation($holdings);

        expect($allocation)->toHaveCount(2)
            ->and($allocation[0]['asset_type'])->toBe('equity')
            ->and($allocation[0]['value'])->toBe(70000.0)
            ->and($allocation[0]['percentage'])->toBe(70.0)
            ->and($allocation[1]['asset_type'])->toBe('bond')
            ->and($allocation[1]['value'])->toBe(30000.0)
            ->and($allocation[1]['percentage'])->toBe(30.0);
    });

    it('returns empty array for empty holdings', function () {
        $holdings = collect([]);

        $allocation = $this->analyzer->calculateAssetAllocation($holdings);

        expect($allocation)->toBe([]);
    });

    it('sorts allocation by value descending', function () {
        $holdings = collect([
            new Holding(['asset_type' => 'cash', 'current_value' => 5000]),
            new Holding(['asset_type' => 'equity', 'current_value' => 50000]),
            new Holding(['asset_type' => 'bond', 'current_value' => 20000]),
        ]);

        $allocation = $this->analyzer->calculateAssetAllocation($holdings);

        expect($allocation[0]['asset_type'])->toBe('equity')
            ->and($allocation[1]['asset_type'])->toBe('bond')
            ->and($allocation[2]['asset_type'])->toBe('cash');
    });
});

describe('calculateDiversificationScore', function () {
    it('returns high score for well-diversified portfolio', function () {
        $allocation = [
            ['asset_type' => 'equity', 'percentage' => 40],
            ['asset_type' => 'bond', 'percentage' => 35],
            ['asset_type' => 'cash', 'percentage' => 15],
            ['asset_type' => 'alternative', 'percentage' => 10],
        ];

        $score = $this->analyzer->calculateDiversificationScore($allocation);

        expect($score)->toBeGreaterThanOrEqual(70);
    });

    it('returns low score for concentrated portfolio', function () {
        $allocation = [
            ['asset_type' => 'equity', 'percentage' => 95],
            ['asset_type' => 'cash', 'percentage' => 5],
        ];

        $score = $this->analyzer->calculateDiversificationScore($allocation);

        expect($score)->toBeLessThan(40);
    });

    it('returns zero for empty allocation', function () {
        $allocation = [];

        $score = $this->analyzer->calculateDiversificationScore($allocation);

        expect($score)->toBe(0);
    });

    it('returns moderate score for moderately diversified portfolio', function () {
        $allocation = [
            ['asset_type' => 'equity', 'percentage' => 60],
            ['asset_type' => 'bond', 'percentage' => 25],
            ['asset_type' => 'cash', 'percentage' => 15],
        ];

        $score = $this->analyzer->calculateDiversificationScore($allocation);

        expect($score)->toBeGreaterThan(40)
            ->and($score)->toBeLessThan(70);
    });
});

describe('calculatePortfolioRisk', function () {
    it('estimates portfolio volatility based on asset mix', function () {
        $holdings = collect([
            new Holding(['asset_type' => 'equity', 'current_value' => 70000]),
            new Holding(['asset_type' => 'bond', 'current_value' => 30000]),
        ]);

        $riskProfile = new RiskProfile([
            'risk_tolerance' => 'balanced',
            'capacity_for_loss_percent' => 20,
        ]);

        $risk = $this->analyzer->calculatePortfolioRisk($holdings, $riskProfile);

        expect($risk['estimated_volatility'])->toBeGreaterThan(0)
            ->and($risk['risk_level'])->toBeIn(['low', 'medium', 'high']);
    });

    it('returns medium risk for empty holdings', function () {
        $holdings = collect([]);
        $riskProfile = new RiskProfile(['risk_tolerance' => 'balanced']);

        $risk = $this->analyzer->calculatePortfolioRisk($holdings, $riskProfile);

        expect($risk['risk_level'])->toBe('medium');
    });

    it('calculates higher volatility for equity-heavy portfolios', function () {
        $equityHeavy = collect([
            new Holding(['asset_type' => 'equity', 'current_value' => 90000]),
            new Holding(['asset_type' => 'cash', 'current_value' => 10000]),
        ]);

        $balanced = collect([
            new Holding(['asset_type' => 'equity', 'current_value' => 50000]),
            new Holding(['asset_type' => 'bond', 'current_value' => 50000]),
        ]);

        $riskProfile = new RiskProfile(['risk_tolerance' => 'balanced']);

        $equityRisk = $this->analyzer->calculatePortfolioRisk($equityHeavy, $riskProfile);
        $balancedRisk = $this->analyzer->calculatePortfolioRisk($balanced, $riskProfile);

        expect($equityRisk['estimated_volatility'])->toBeGreaterThan($balancedRisk['estimated_volatility']);
    });
});
