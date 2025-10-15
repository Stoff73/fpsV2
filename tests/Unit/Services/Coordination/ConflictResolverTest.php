<?php

use App\Services\Coordination\ConflictResolver;

describe('ConflictResolver', function () {
    beforeEach(function () {
        $this->resolver = new ConflictResolver;
    });

    describe('identifyConflicts', function () {
        it('detects cashflow conflicts when demand exceeds surplus', function () {
            $recommendations = [
                'protection' => [
                    ['recommended_monthly_premium' => 200],
                ],
                'savings' => [
                    ['recommended_monthly_contribution' => 300],
                ],
                'retirement' => [
                    ['recommended_monthly_contribution' => 500],
                ],
                'available_surplus' => 800,
            ];

            $conflicts = $this->resolver->identifyConflicts($recommendations);

            expect($conflicts)->toHaveCount(1);
            expect($conflicts[0]['type'])->toBe('cashflow_conflict');
            expect($conflicts[0]['total_demand'])->toBe(1000);
            expect($conflicts[0]['shortfall'])->toBe(200);
        });

        it('detects ISA allowance conflicts when demands exceed £20,000', function () {
            $recommendations = [
                'savings' => [
                    ['recommended_cash_isa_contribution' => 12000],
                ],
                'investment' => [
                    ['recommended_isa_contribution' => 10000],
                ],
                'available_surplus' => 5000,
            ];

            $conflicts = $this->resolver->identifyConflicts($recommendations);

            $isaConflict = collect($conflicts)->firstWhere('type', 'isa_allowance_conflict');

            expect($isaConflict)->not->toBeNull();
            expect($isaConflict['total_allowance'])->toBe(20000);
            expect($isaConflict['total_demand'])->toBe(22000);
            expect($isaConflict['shortfall'])->toBe(2000);
        });

        it('returns empty array when no conflicts exist', function () {
            $recommendations = [
                'protection' => [
                    ['recommended_monthly_premium' => 100],
                ],
                'savings' => [
                    ['recommended_monthly_contribution' => 200],
                ],
                'available_surplus' => 500,
            ];

            $conflicts = $this->resolver->identifyConflicts($recommendations);

            expect($conflicts)->toBeEmpty();
        });
    });

    describe('resolveContributionConflicts', function () {
        it('allocates surplus in priority order', function () {
            $demands = [
                'emergency_fund' => ['amount' => 300, 'urgency' => 90],
                'protection' => ['amount' => 200, 'urgency' => 80],
                'pension' => ['amount' => 400, 'urgency' => 70],
                'investment' => ['amount' => 200, 'urgency' => 60],
            ];

            $result = $this->resolver->resolveContributionConflicts(800, $demands);

            // Emergency fund should be fully funded (priority 1)
            expect($result['allocation']['emergency_fund']['allocated'])->toBe(300);
            expect($result['allocation']['emergency_fund']['percent_funded'])->toBe(100);

            // Protection should be fully funded (priority 2)
            expect($result['allocation']['protection']['allocated'])->toBe(200);
            expect($result['allocation']['protection']['percent_funded'])->toBe(100);

            // Pension should be partially funded (priority 3)
            expect($result['allocation']['pension']['allocated'])->toBe(300); // Remaining surplus
            expect($result['allocation']['pension']['shortfall'])->toBe(100);

            // Investment should not be funded (priority 4)
            expect($result['allocation']['investment']['allocated'])->toBe(0);
            expect($result['allocation']['investment']['shortfall'])->toBe(200);

            expect($result['total_demand'])->toBe(1100);
            expect($result['total_shortfall'])->toBe(300);
        });

        it('prioritizes urgent recommendations regardless of category', function () {
            $demands = [
                'pension' => ['amount' => 200, 'urgency' => 95], // Critical
                'emergency_fund' => ['amount' => 300, 'urgency' => 50], // Normal
            ];

            $result = $this->resolver->resolveContributionConflicts(300, $demands);

            // Pension should be funded first due to high urgency
            expect($result['allocation']['pension']['allocated'])->toBe(200);
            expect($result['allocation']['emergency_fund']['allocated'])->toBe(100);
        });
    });

    describe('resolveISAAllocation', function () {
        it('prioritizes Cash ISA when emergency fund is critically low', function () {
            $demands = [
                'cash_isa' => 15000,
                'stocks_shares_isa' => 10000,
                'emergency_fund_adequacy' => 30, // Critical
                'risk_tolerance' => 'medium',
            ];

            $result = $this->resolver->resolveISAAllocation(20000, $demands);

            expect($result['allocation']['cash_isa'])->toBe(15000);
            expect($result['allocation']['stocks_shares_isa'])->toBe(5000);
            expect($result['reasoning'])->toContain('Emergency fund is critically low');
        });

        it('prioritizes Stocks & Shares ISA for high risk tolerance and growth goals', function () {
            $demands = [
                'cash_isa' => 8000,
                'stocks_shares_isa' => 15000,
                'emergency_fund_adequacy' => 100,
                'investment_goal_urgency' => 80,
                'risk_tolerance' => 'high',
            ];

            $result = $this->resolver->resolveISAAllocation(20000, $demands);

            expect($result['allocation']['stocks_shares_isa'])->toBe(15000);
            expect($result['allocation']['cash_isa'])->toBe(5000);
            expect($result['reasoning'])->toContain('Prioritize Stocks & Shares ISA');
        });

        it('splits proportionally when demands fit within allowance', function () {
            $demands = [
                'cash_isa' => 8000,
                'stocks_shares_isa' => 10000,
                'emergency_fund_adequacy' => 80,
                'risk_tolerance' => 'medium',
            ];

            $result = $this->resolver->resolveISAAllocation(20000, $demands);

            expect($result['allocation']['cash_isa'])->toBe(8000);
            expect($result['allocation']['stocks_shares_isa'])->toBe(10000);
            expect($result['unallocated'])->toBe(2000);
            expect($result['reasoning'])->toContain('Sufficient ISA allowance');
        });
    });

    describe('resolveProtectionVsSavings', function () {
        it('prioritizes protection when adequacy score is lower', function () {
            $recommendations = [
                'module_scores' => [
                    'protection' => ['adequacy_score' => 40],
                    'savings' => ['emergency_fund_adequacy' => 70],
                ],
            ];

            $result = $this->resolver->resolveProtectionVsSavings($recommendations);

            expect($result['resolution'])->toBe('protection_priority');
            expect($result['allocation']['protection'])->toBe(0.8);
            expect($result['allocation']['savings'])->toBe(0.2);
        });

        it('prioritizes savings when emergency fund is more critical', function () {
            $recommendations = [
                'module_scores' => [
                    'protection' => ['adequacy_score' => 75],
                    'savings' => ['emergency_fund_adequacy' => 35],
                ],
            ];

            $result = $this->resolver->resolveProtectionVsSavings($recommendations);

            expect($result['resolution'])->toBe('savings_priority');
            expect($result['allocation']['protection'])->toBe(0.2);
            expect($result['allocation']['savings'])->toBe(0.8);
        });

        it('splits evenly when both are critically low', function () {
            $recommendations = [
                'module_scores' => [
                    'protection' => ['adequacy_score' => 35],
                    'savings' => ['emergency_fund_adequacy' => 40],
                ],
            ];

            $result = $this->resolver->resolveProtectionVsSavings($recommendations);

            expect($result['resolution'])->toBe('split_priority');
            expect($result['allocation']['protection'])->toBe(0.6); // Slight priority to protection
            expect($result['allocation']['savings'])->toBe(0.4);
        });
    });
});
