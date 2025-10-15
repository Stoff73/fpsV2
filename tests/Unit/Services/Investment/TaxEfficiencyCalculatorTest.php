<?php

declare(strict_types=1);

use App\Models\Investment\Holding;
use App\Models\Investment\InvestmentAccount;
use App\Services\Investment\TaxEfficiencyCalculator;

beforeEach(function () {
    $this->taxCalculator = new TaxEfficiencyCalculator;

    // Mock UK tax config
    config([
        'uk_tax_config.dividend_tax' => [
            'dividend_allowance' => 500,
            'basic_rate' => 0.0875,
            'higher_rate' => 0.3375,
            'additional_rate' => 0.3935,
        ],
        'uk_tax_config.capital_gains_tax' => [
            'annual_exempt_amount' => 3000,
            'basic_rate' => 0.10,
            'higher_rate' => 0.20,
        ],
        'uk_tax_config.income_tax.bands' => [
            'personal_allowance' => 12570,
        ],
    ]);
});

describe('calculateUnrealizedGains', function () {
    it('calculates unrealized gains for holdings', function () {
        $holdings = collect([
            new Holding([
                'security_name' => 'Stock A',
                'cost_basis' => 10000,
                'current_value' => 15000,
            ]),
            new Holding([
                'security_name' => 'Stock B',
                'cost_basis' => 5000,
                'current_value' => 7000,
            ]),
        ]);

        $result = $this->taxCalculator->calculateUnrealizedGains($holdings);

        expect($result['total_unrealized_gains'])->toBe(7000.0)
            ->and($result['count'])->toBe(2)
            ->and($result['holdings_with_gains'][0]['security_name'])->toBe('Stock A')
            ->and($result['holdings_with_gains'][0]['unrealized_gain'])->toBe(5000.0)
            ->and($result['holdings_with_gains'][0]['gain_percent'])->toBe(50.0);
    });

    it('filters out holdings with losses', function () {
        $holdings = collect([
            new Holding([
                'security_name' => 'Winner',
                'cost_basis' => 10000,
                'current_value' => 12000,
            ]),
            new Holding([
                'security_name' => 'Loser',
                'cost_basis' => 5000,
                'current_value' => 4000,
            ]),
        ]);

        $result = $this->taxCalculator->calculateUnrealizedGains($holdings);

        expect($result['count'])->toBe(1)
            ->and($result['holdings_with_gains'][0]['security_name'])->toBe('Winner');
    });

    it('returns zero for empty holdings', function () {
        $holdings = collect([]);

        $result = $this->taxCalculator->calculateUnrealizedGains($holdings);

        expect($result['total_unrealized_gains'])->toBe(0.0)
            ->and($result['count'])->toBe(0);
    });
});

describe('calculateDividendTax', function () {
    it('applies dividend allowance before tax', function () {
        $dividendIncome = 2000;
        $totalIncome = 30000;

        $tax = $this->taxCalculator->calculateDividendTax($dividendIncome, $totalIncome);

        // Taxable dividends: 2000 - 500 = 1500
        // Basic rate taxpayer: 1500 * 0.0875 = 131.25
        expect($tax)->toBe(131.25);
    });

    it('returns zero tax when dividends are below allowance', function () {
        $dividendIncome = 300;
        $totalIncome = 30000;

        $tax = $this->taxCalculator->calculateDividendTax($dividendIncome, $totalIncome);

        expect($tax)->toBe(0.0);
    });

    it('applies higher rate for high earners', function () {
        $dividendIncome = 10000;
        $totalIncome = 100000; // Higher rate taxpayer

        $tax = $this->taxCalculator->calculateDividendTax($dividendIncome, $totalIncome);

        // Taxable: 10000 - 500 = 9500
        // Higher rate: 9500 * 0.3375 = 3206.25
        expect($tax)->toBe(3206.25);
    });

    it('applies additional rate for top earners', function () {
        $dividendIncome = 50000;
        $totalIncome = 200000; // Additional rate taxpayer

        $tax = $this->taxCalculator->calculateDividendTax($dividendIncome, $totalIncome);

        // Taxable: 50000 - 500 = 49500
        // Additional rate: 49500 * 0.3935 = 19478.25
        expect($tax)->toBe(19478.25);
    });
});

describe('calculateCGTLiability', function () {
    it('applies annual exemption before tax', function () {
        $realizedGains = 10000;
        $totalIncome = 30000;

        $cgt = $this->taxCalculator->calculateCGTLiability($realizedGains, $totalIncome);

        // Taxable gains: 10000 - 3000 = 7000
        // Basic rate: 7000 * 0.10 = 700
        expect($cgt)->toBe(700.0);
    });

    it('returns zero tax when gains are below exemption', function () {
        $realizedGains = 2000;
        $totalIncome = 30000;

        $cgt = $this->taxCalculator->calculateCGTLiability($realizedGains, $totalIncome);

        expect($cgt)->toBe(0.0);
    });

    it('applies higher rate for high earners', function () {
        $realizedGains = 20000;
        $totalIncome = 100000; // Higher rate taxpayer

        $cgt = $this->taxCalculator->calculateCGTLiability($realizedGains, $totalIncome);

        // Taxable: 20000 - 3000 = 17000
        // Higher rate: 17000 * 0.20 = 3400
        expect($cgt)->toBe(3400.0);
    });

    it('handles zero income correctly', function () {
        $realizedGains = 10000;
        $totalIncome = 0;

        $cgt = $this->taxCalculator->calculateCGTLiability($realizedGains, $totalIncome);

        // Should apply basic rate
        expect($cgt)->toBe(700.0);
    });
});

describe('identifyHarvestingOpportunities', function () {
    it('identifies holdings with significant losses', function () {
        $holdings = collect([
            new Holding([
                'security_name' => 'Loss Stock A',
                'cost_basis' => 10000,
                'current_value' => 8500, // -£1,500 loss
            ]),
            new Holding([
                'security_name' => 'Profit Stock',
                'cost_basis' => 5000,
                'current_value' => 6000, // £1,000 gain
            ]),
            new Holding([
                'security_name' => 'Small Loss',
                'cost_basis' => 1000,
                'current_value' => 950, // -£50 loss (below threshold)
            ]),
        ]);

        $result = $this->taxCalculator->identifyHarvestingOpportunities($holdings);

        expect($result['opportunities_count'])->toBe(1)
            ->and($result['holdings'][0]['security_name'])->toBe('Loss Stock A')
            ->and($result['holdings'][0]['unrealized_loss'])->toBe(-1500.0)
            ->and($result['total_harvestable_losses'])->toBe(1500.0);
    });

    it('calculates potential tax saving', function () {
        $holdings = collect([
            new Holding([
                'security_name' => 'Loss Position',
                'cost_basis' => 10000,
                'current_value' => 7000, // -£3,000 loss
            ]),
        ]);

        $result = $this->taxCalculator->identifyHarvestingOpportunities($holdings);

        // Potential tax saving: 3000 * 0.20 = 600 (assuming 20% CGT rate)
        expect($result['potential_tax_saving'])->toBe(600.0);
    });

    it('returns empty result when no harvestable losses', function () {
        $holdings = collect([
            new Holding([
                'security_name' => 'Winner',
                'cost_basis' => 5000,
                'current_value' => 6000,
            ]),
        ]);

        $result = $this->taxCalculator->identifyHarvestingOpportunities($holdings);

        expect($result['opportunities_count'])->toBe(0)
            ->and($result['holdings'])->toBeEmpty();
    });

    it('only includes losses greater than £100', function () {
        $holdings = collect([
            new Holding([
                'security_name' => 'Tiny Loss',
                'cost_basis' => 1000,
                'current_value' => 950, // -£50 (too small)
            ]),
            new Holding([
                'security_name' => 'Significant Loss',
                'cost_basis' => 5000,
                'current_value' => 4500, // -£500 (significant)
            ]),
        ]);

        $result = $this->taxCalculator->identifyHarvestingOpportunities($holdings);

        expect($result['opportunities_count'])->toBe(1)
            ->and($result['holdings'][0]['security_name'])->toBe('Significant Loss');
    });
});

describe('calculateTaxEfficiencyScore', function () {
    it('rewards high ISA usage', function () {
        $accounts = collect([
            new InvestmentAccount([
                'account_type' => 'isa',
                'current_value' => 60000,
            ]),
            new InvestmentAccount([
                'account_type' => 'gia',
                'current_value' => 40000,
            ]),
        ]);

        $holdings = collect([]);

        $score = $this->taxCalculator->calculateTaxEfficiencyScore($accounts, $holdings);

        // 60% in ISA = good usage, should score above 100 (capped at 100)
        expect($score)->toBeGreaterThanOrEqual(90);
    });

    it('penalizes low ISA usage', function () {
        $accounts = collect([
            new InvestmentAccount([
                'account_type' => 'gia',
                'current_value' => 80000,
            ]),
            new InvestmentAccount([
                'account_type' => 'isa',
                'current_value' => 20000,
            ]),
        ]);

        $holdings = collect([]);

        $score = $this->taxCalculator->calculateTaxEfficiencyScore($accounts, $holdings);

        // Only 20% in ISA = poor usage, should be penalized
        expect($score)->toBeLessThan(90);
    });

    it('penalizes many holdings with large unrealized gains', function () {
        $accounts = collect([
            new InvestmentAccount([
                'account_type' => 'isa',
                'current_value' => 50000,
            ]),
        ]);

        $holdings = collect([
            new Holding(['cost_basis' => 10000, 'current_value' => 20000]), // 100% gain
            new Holding(['cost_basis' => 10000, 'current_value' => 18000]), // 80% gain
            new Holding(['cost_basis' => 10000, 'current_value' => 16000]), // 60% gain
            new Holding(['cost_basis' => 10000, 'current_value' => 17000]), // 70% gain
        ]);

        $score = $this->taxCalculator->calculateTaxEfficiencyScore($accounts, $holdings);

        // Many holdings with large gains (>50%) should reduce score
        expect($score)->toBeLessThan(100);
    });

    it('returns score capped between 0 and 100', function () {
        $accounts = collect([
            new InvestmentAccount([
                'account_type' => 'isa',
                'current_value' => 100000,
            ]),
        ]);

        $holdings = collect([]);

        $score = $this->taxCalculator->calculateTaxEfficiencyScore($accounts, $holdings);

        expect($score)->toBeGreaterThanOrEqual(0)
            ->and($score)->toBeLessThanOrEqual(100);
    });
});
