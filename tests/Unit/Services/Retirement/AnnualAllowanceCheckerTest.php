<?php

use App\Services\Retirement\AnnualAllowanceChecker;

beforeEach(function () {
    $this->checker = new AnnualAllowanceChecker;
});

test('calculates tapering for high earners correctly', function () {
    $thresholdIncome = 250000;
    $adjustedIncome = 300000;

    $taperedAllowance = $this->checker->calculateTapering($thresholdIncome, $adjustedIncome);

    // Adjusted income exceeds threshold by £40,000
    // Reduction: £40,000 / 2 = £20,000
    // Tapered allowance: £60,000 - £20,000 = £40,000
    expect($taperedAllowance)->toBe(40000.0);
});

test('applies minimum tapered allowance of £10,000', function () {
    $thresholdIncome = 250000;
    $adjustedIncome = 400000; // Very high income

    $taperedAllowance = $this->checker->calculateTapering($thresholdIncome, $adjustedIncome);

    // Reduction would be £70,000 (£140,000 / 2)
    // But minimum allowance is £10,000
    expect($taperedAllowance)->toBe(10000.0);
});

test('returns standard allowance when no tapering applies', function () {
    $thresholdIncome = 150000; // Below £200,000 threshold
    $adjustedIncome = 160000;

    $taperedAllowance = $this->checker->calculateTapering($thresholdIncome, $adjustedIncome);

    expect($taperedAllowance)->toBe(60000.0);
});

test('returns standard allowance when adjusted income below threshold', function () {
    $thresholdIncome = 210000;
    $adjustedIncome = 250000; // Below £260,000 adjusted income threshold

    $taperedAllowance = $this->checker->calculateTapering($thresholdIncome, $adjustedIncome);

    expect($taperedAllowance)->toBe(60000.0);
});

test('calculates carry forward allowance', function () {
    $carryForward = $this->checker->getCarryForward(1, '2024/25');

    // Simplified implementation returns £60,000
    expect($carryForward)->toBe(60000.0);
});

test('checks MPAA status when not triggered', function () {
    $mpaaStatus = $this->checker->checkMPAA(1);

    expect($mpaaStatus)->toHaveKeys(['is_triggered', 'mpaa_amount', 'message'])
        ->and($mpaaStatus['is_triggered'])->toBeFalse()
        ->and($mpaaStatus['mpaa_amount'])->toBe(10000);
});
