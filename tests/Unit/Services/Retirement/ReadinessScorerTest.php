<?php

use App\Services\Retirement\ReadinessScorer;

beforeEach(function () {
    $this->scorer = new ReadinessScorer();
});

test('calculates readiness score correctly', function () {
    $projectedIncome = 40000;
    $targetIncome = 50000;

    $score = $this->scorer->calculateReadinessScore($projectedIncome, $targetIncome);

    expect($score)->toBe(80);
});

test('caps readiness score at 100', function () {
    $projectedIncome = 60000;
    $targetIncome = 50000;

    $score = $this->scorer->calculateReadinessScore($projectedIncome, $targetIncome);

    expect($score)->toBe(100);
});

test('handles zero target income', function () {
    $projectedIncome = 40000;
    $targetIncome = 0;

    $score = $this->scorer->calculateReadinessScore($projectedIncome, $targetIncome);

    expect($score)->toBe(0);
});

test('categorizes readiness as excellent', function () {
    $category = $this->scorer->categorizeReadiness(95);

    expect($category)->toBe('Excellent');
});

test('categorizes readiness as good', function () {
    $category = $this->scorer->categorizeReadiness(75);

    expect($category)->toBe('Good');
});

test('categorizes readiness as fair', function () {
    $category = $this->scorer->categorizeReadiness(60);

    expect($category)->toBe('Fair');
});

test('categorizes readiness as critical', function () {
    $category = $this->scorer->categorizeReadiness(40);

    expect($category)->toBe('Critical');
});

test('calculates income gap correctly with shortfall', function () {
    $projected = 30000;
    $target = 40000;

    $gap = $this->scorer->calculateIncomeGap($projected, $target);

    expect($gap)->toBe(10000.0);
});

test('calculates income gap correctly with surplus', function () {
    $projected = 50000;
    $target = 40000;

    $gap = $this->scorer->calculateIncomeGap($projected, $target);

    expect($gap)->toBe(-10000.0);
});

test('returns green color for high readiness', function () {
    $color = $this->scorer->getReadinessColor(85);

    expect($color)->toBe('green');
});

test('returns amber color for medium readiness', function () {
    $color = $this->scorer->getReadinessColor(60);

    expect($color)->toBe('amber');
});

test('returns red color for low readiness', function () {
    $color = $this->scorer->getReadinessColor(40);

    expect($color)->toBe('red');
});

test('analyzes readiness comprehensively', function () {
    $projectedIncome = 35000;
    $targetIncome = 50000;

    $analysis = $this->scorer->analyzeReadiness($projectedIncome, $targetIncome);

    expect($analysis)->toHaveKeys([
        'score',
        'category',
        'color',
        'projected_income',
        'target_income',
        'income_gap',
        'message',
        'recommendation',
    ])
        ->and($analysis['score'])->toBe(70)
        ->and($analysis['category'])->toBe('Good')
        ->and($analysis['color'])->toBe('green')
        ->and($analysis['income_gap'])->toBe(15000.0);
});
