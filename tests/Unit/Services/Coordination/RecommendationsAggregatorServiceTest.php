<?php

use App\Models\User;
use App\Services\Coordination\RecommendationsAggregatorService;
use App\Services\Estate\EstateAnalyzer;
use App\Services\Investment\PortfolioAnalyzer;
use App\Services\Protection\ProtectionAgent;
use App\Services\Retirement\RetirementProjector;
use App\Services\Savings\EmergencyFundAnalyzer;

beforeEach(function () {
    $this->user = User::factory()->create();

    // Mock all the services
    $this->protectionAgent = Mockery::mock(ProtectionAgent::class);
    $this->savingsAnalyzer = Mockery::mock(EmergencyFundAnalyzer::class);
    $this->investmentAnalyzer = Mockery::mock(PortfolioAnalyzer::class);
    $this->retirementProjector = Mockery::mock(RetirementProjector::class);
    $this->estateAnalyzer = Mockery::mock(EstateAnalyzer::class);

    $this->service = new RecommendationsAggregatorService(
        $this->protectionAgent,
        $this->savingsAnalyzer,
        $this->investmentAnalyzer,
        $this->retirementProjector,
        $this->estateAnalyzer
    );
});

test('aggregateRecommendations returns recommendations from all modules', function () {
    // Setup mock responses
    $this->protectionAgent->shouldReceive('analyze')->andReturn([]);
    $this->protectionAgent->shouldReceive('generateRecommendations')->andReturn([
        [
            'recommendation_id' => 'prot_1',
            'recommendation_text' => 'Increase life insurance coverage',
            'priority_score' => 85.0,
            'category' => 'risk_mitigation',
        ],
    ]);

    $this->savingsAnalyzer->shouldReceive('analyze')->andReturn([
        'recommendations' => [
            [
                'recommendation_id' => 'sav_1',
                'recommendation' => 'Build 6-month emergency fund',
                'priority' => 90.0,
            ],
        ],
    ]);

    // Mock user's investmentAccounts relationship
    $this->user->setRelation('investmentAccounts', collect([]));

    $this->retirementProjector->shouldReceive('analyze')->andReturn([
        'recommendations' => [],
    ]);

    $this->estateAnalyzer->shouldReceive('analyze')->andReturn([
        'recommendations' => [],
    ]);

    $recommendations = $this->service->aggregateRecommendations($this->user->id);

    expect($recommendations)->toHaveCount(2);
    expect($recommendations[0]['module'])->toBe('savings'); // Higher priority (90)
    expect($recommendations[1]['module'])->toBe('protection'); // Lower priority (85)
});

test('aggregateRecommendations sorts by priority score descending', function () {
    $this->protectionAgent->shouldReceive('analyze')->andReturn([]);
    $this->protectionAgent->shouldReceive('generateRecommendations')->andReturn([
        ['recommendation_id' => 'prot_1', 'recommendation_text' => 'Test 1', 'priority_score' => 50.0],
    ]);

    $this->savingsAnalyzer->shouldReceive('analyze')->andReturn([
        'recommendations' => [
            ['recommendation_id' => 'sav_1', 'recommendation' => 'Test 2', 'priority' => 90.0],
        ],
    ]);

    // Mock user's investmentAccounts relationship
    $this->user->setRelation('investmentAccounts', collect([]));
    $this->retirementProjector->shouldReceive('analyze')->andReturn([
        'recommendations' => [
            ['recommendation_id' => 'ret_1', 'recommendation' => 'Test 3', 'priority' => 70.0],
        ],
    ]);

    $this->estateAnalyzer->shouldReceive('analyze')->andReturn([
        'recommendations' => [],
    ]);

    $recommendations = $this->service->aggregateRecommendations($this->user->id);

    expect($recommendations)->toHaveCount(3);
    expect($recommendations[0]['priority_score'])->toBe(90.0);
    expect($recommendations[1]['priority_score'])->toBe(70.0);
    expect($recommendations[2]['priority_score'])->toBe(50.0);
});

test('formatRecommendations normalizes different recommendation formats', function () {
    $this->protectionAgent->shouldReceive('analyze')->andReturn([]);
    $this->protectionAgent->shouldReceive('generateRecommendations')->andReturn([
        [
            'recommendation_id' => 'prot_1',
            'recommendation_text' => 'Test recommendation',
            'priority_score' => 75.0,
        ],
    ]);

    $this->savingsAnalyzer->shouldReceive('analyze')->andReturn([
        'recommendations' => [
            [
                'id' => 'sav_1',
                'recommendation' => 'Different format',
                'priority' => 80.0,
            ],
        ],
    ]);

    // Mock user's investmentAccounts relationship
    $this->user->setRelation('investmentAccounts', collect([]));
    $this->retirementProjector->shouldReceive('analyze')->andReturn(['recommendations' => []]);
    $this->estateAnalyzer->shouldReceive('analyze')->andReturn(['recommendations' => []]);

    $recommendations = $this->service->aggregateRecommendations($this->user->id);

    expect($recommendations)->toHaveCount(2);
    expect($recommendations[0])->toHaveKey('recommendation_id');
    expect($recommendations[0])->toHaveKey('recommendation_text');
    expect($recommendations[0])->toHaveKey('priority_score');
    expect($recommendations[0])->toHaveKey('module');
    expect($recommendations[0])->toHaveKey('timeline');
    expect($recommendations[0])->toHaveKey('category');
});

test('determineTimeline assigns correct timeline based on priority score', function () {
    $this->protectionAgent->shouldReceive('analyze')->andReturn([]);
    $this->protectionAgent->shouldReceive('generateRecommendations')->andReturn([
        ['recommendation_id' => 'p1', 'recommendation_text' => 'Immediate', 'priority_score' => 85.0],
        ['recommendation_id' => 'p2', 'recommendation_text' => 'Short term', 'priority_score' => 65.0],
        ['recommendation_id' => 'p3', 'recommendation_text' => 'Medium term', 'priority_score' => 45.0],
        ['recommendation_id' => 'p4', 'recommendation_text' => 'Long term', 'priority_score' => 25.0],
    ]);

    $this->savingsAnalyzer->shouldReceive('analyze')->andReturn(['recommendations' => []]);
    // Mock user's investmentAccounts relationship
    $this->user->setRelation('investmentAccounts', collect([]));
    $this->retirementProjector->shouldReceive('analyze')->andReturn(['recommendations' => []]);
    $this->estateAnalyzer->shouldReceive('analyze')->andReturn(['recommendations' => []]);

    $recommendations = $this->service->aggregateRecommendations($this->user->id);

    expect($recommendations[0]['timeline'])->toBe('immediate');
    expect($recommendations[1]['timeline'])->toBe('short_term');
    expect($recommendations[2]['timeline'])->toBe('medium_term');
    expect($recommendations[3]['timeline'])->toBe('long_term');
});

test('determineImpact assigns correct impact based on priority score', function () {
    $this->protectionAgent->shouldReceive('analyze')->andReturn([]);
    $this->protectionAgent->shouldReceive('generateRecommendations')->andReturn([
        ['recommendation_id' => 'p1', 'recommendation_text' => 'High', 'priority_score' => 75.0],
        ['recommendation_id' => 'p2', 'recommendation_text' => 'Medium', 'priority_score' => 50.0],
        ['recommendation_id' => 'p3', 'recommendation_text' => 'Low', 'priority_score' => 30.0],
    ]);

    $this->savingsAnalyzer->shouldReceive('analyze')->andReturn(['recommendations' => []]);
    // Mock user's investmentAccounts relationship
    $this->user->setRelation('investmentAccounts', collect([]));
    $this->retirementProjector->shouldReceive('analyze')->andReturn(['recommendations' => []]);
    $this->estateAnalyzer->shouldReceive('analyze')->andReturn(['recommendations' => []]);

    $recommendations = $this->service->aggregateRecommendations($this->user->id);

    expect($recommendations[0]['impact'])->toBe('high');
    expect($recommendations[1]['impact'])->toBe('medium');
    expect($recommendations[2]['impact'])->toBe('low');
});

test('getRecommendationsByModule filters correctly', function () {
    $this->protectionAgent->shouldReceive('analyze')->andReturn([]);
    $this->protectionAgent->shouldReceive('generateRecommendations')->andReturn([
        ['recommendation_id' => 'p1', 'recommendation_text' => 'Protection rec', 'priority_score' => 75.0],
    ]);

    $this->savingsAnalyzer->shouldReceive('analyze')->andReturn([
        'recommendations' => [
            ['recommendation_id' => 's1', 'recommendation' => 'Savings rec', 'priority' => 80.0],
        ],
    ]);

    // Mock user's investmentAccounts relationship
    $this->user->setRelation('investmentAccounts', collect([]));
    $this->retirementProjector->shouldReceive('analyze')->andReturn(['recommendations' => []]);
    $this->estateAnalyzer->shouldReceive('analyze')->andReturn(['recommendations' => []]);

    $protectionRecs = $this->service->getRecommendationsByModule($this->user->id, 'protection');
    $savingsRecs = $this->service->getRecommendationsByModule($this->user->id, 'savings');

    expect($protectionRecs)->toHaveCount(1);
    expect($savingsRecs)->toHaveCount(1);
    expect($protectionRecs[1]['module'])->toBe('protection');
    expect($savingsRecs[0]['module'])->toBe('savings');
});

test('getRecommendationsByPriority filters correctly', function () {
    $this->protectionAgent->shouldReceive('analyze')->andReturn([]);
    $this->protectionAgent->shouldReceive('generateRecommendations')->andReturn([
        ['recommendation_id' => 'p1', 'recommendation_text' => 'High priority', 'priority_score' => 75.0],
        ['recommendation_id' => 'p2', 'recommendation_text' => 'Low priority', 'priority_score' => 30.0],
    ]);

    $this->savingsAnalyzer->shouldReceive('analyze')->andReturn(['recommendations' => []]);
    // Mock user's investmentAccounts relationship
    $this->user->setRelation('investmentAccounts', collect([]));
    $this->retirementProjector->shouldReceive('analyze')->andReturn(['recommendations' => []]);
    $this->estateAnalyzer->shouldReceive('analyze')->andReturn(['recommendations' => []]);

    $highPriorityRecs = $this->service->getRecommendationsByPriority($this->user->id, 'high');
    $lowPriorityRecs = $this->service->getRecommendationsByPriority($this->user->id, 'low');

    expect($highPriorityRecs)->toHaveCount(1);
    expect($lowPriorityRecs)->toHaveCount(1);
});

test('getTopRecommendations returns limited results', function () {
    $this->protectionAgent->shouldReceive('analyze')->andReturn([]);
    $this->protectionAgent->shouldReceive('generateRecommendations')->andReturn([
        ['recommendation_id' => 'p1', 'recommendation_text' => 'Rec 1', 'priority_score' => 90.0],
        ['recommendation_id' => 'p2', 'recommendation_text' => 'Rec 2', 'priority_score' => 80.0],
        ['recommendation_id' => 'p3', 'recommendation_text' => 'Rec 3', 'priority_score' => 70.0],
        ['recommendation_id' => 'p4', 'recommendation_text' => 'Rec 4', 'priority_score' => 60.0],
        ['recommendation_id' => 'p5', 'recommendation_text' => 'Rec 5', 'priority_score' => 50.0],
    ]);

    $this->savingsAnalyzer->shouldReceive('analyze')->andReturn(['recommendations' => []]);
    // Mock user's investmentAccounts relationship
    $this->user->setRelation('investmentAccounts', collect([]));
    $this->retirementProjector->shouldReceive('analyze')->andReturn(['recommendations' => []]);
    $this->estateAnalyzer->shouldReceive('analyze')->andReturn(['recommendations' => []]);

    $topRecs = $this->service->getTopRecommendations($this->user->id, 3);

    expect($topRecs)->toHaveCount(3);
    expect($topRecs[0]['priority_score'])->toBe(90.0);
    expect($topRecs[2]['priority_score'])->toBe(70.0);
});

test('getSummary calculates correct statistics', function () {
    $this->protectionAgent->shouldReceive('analyze')->andReturn([]);
    $this->protectionAgent->shouldReceive('generateRecommendations')->andReturn([
        [
            'recommendation_id' => 'p1',
            'recommendation_text' => 'High priority protection',
            'priority_score' => 85.0,
            'estimated_cost' => 1000.0,
            'potential_benefit' => 50000.0,
        ],
    ]);

    $this->savingsAnalyzer->shouldReceive('analyze')->andReturn([
        'recommendations' => [
            [
                'recommendation_id' => 's1',
                'recommendation' => 'Medium priority savings',
                'priority' => 50.0,
                'cost' => 500.0,
                'benefit' => 10000.0,
            ],
        ],
    ]);

    // Mock user's investmentAccounts relationship
    $this->user->setRelation('investmentAccounts', collect([]));
    $this->retirementProjector->shouldReceive('analyze')->andReturn(['recommendations' => []]);
    $this->estateAnalyzer->shouldReceive('analyze')->andReturn(['recommendations' => []]);

    $summary = $this->service->getSummary($this->user->id);

    expect($summary['total_count'])->toBe(2);
    expect($summary['by_priority']['high'])->toBe(1);
    expect($summary['by_priority']['medium'])->toBe(1);
    expect($summary['by_module']['protection'])->toBe(1);
    expect($summary['by_module']['savings'])->toBe(1);
    expect($summary['total_estimated_cost'])->toBe(1500.0);
    expect($summary['total_potential_benefit'])->toBe(60000.0);
});

test('aggregateRecommendations handles service exceptions gracefully', function () {
    $this->protectionAgent->shouldReceive('analyze')->andThrow(new \Exception('Protection service error'));

    $this->savingsAnalyzer->shouldReceive('analyze')->andReturn([
        'recommendations' => [
            ['recommendation_id' => 's1', 'recommendation' => 'Savings rec', 'priority' => 80.0],
        ],
    ]);

    // Mock user's investmentAccounts relationship
    $this->user->setRelation('investmentAccounts', collect([]));
    $this->retirementProjector->shouldReceive('analyze')->andReturn(['recommendations' => []]);
    $this->estateAnalyzer->shouldReceive('analyze')->andReturn(['recommendations' => []]);

    $recommendations = $this->service->aggregateRecommendations($this->user->id);

    // Should still return savings recommendation despite protection error
    expect($recommendations)->toHaveCount(1);
    expect($recommendations[0]['module'])->toBe('savings');
});

test('determineCategory assigns correct category based on module', function () {
    $this->protectionAgent->shouldReceive('analyze')->andReturn([]);
    $this->protectionAgent->shouldReceive('generateRecommendations')->andReturn([
        ['recommendation_id' => 'p1', 'recommendation_text' => 'Protection', 'priority_score' => 75.0],
    ]);

    $this->savingsAnalyzer->shouldReceive('analyze')->andReturn([
        'recommendations' => [
            ['recommendation_id' => 's1', 'recommendation' => 'Savings', 'priority' => 75.0],
        ],
    ]);

    // Mock user's investmentAccounts relationship
    $this->user->setRelation('investmentAccounts', collect([]));
    $this->retirementProjector->shouldReceive('analyze')->andReturn([
        'recommendations' => [
            ['recommendation_id' => 'r1', 'recommendation' => 'Retirement', 'priority' => 75.0],
        ],
    ]);

    $this->estateAnalyzer->shouldReceive('analyze')->andReturn([
        'recommendations' => [
            ['recommendation_id' => 'e1', 'recommendation' => 'Estate', 'priority' => 75.0],
        ],
    ]);

    $recommendations = $this->service->aggregateRecommendations($this->user->id);

    $protectionRec = array_values(array_filter($recommendations, fn($r) => $r['module'] === 'protection'))[0];
    $savingsRec = array_values(array_filter($recommendations, fn($r) => $r['module'] === 'savings'))[0];
    $retirementRec = array_values(array_filter($recommendations, fn($r) => $r['module'] === 'retirement'))[0];
    $estateRec = array_values(array_filter($recommendations, fn($r) => $r['module'] === 'estate'))[0];

    expect($protectionRec['category'])->toBe('risk_mitigation');
    expect($savingsRec['category'])->toBe('liquidity_management');
    expect($retirementRec['category'])->toBe('retirement_planning');
    expect($estateRec['category'])->toBe('tax_optimization');
});
