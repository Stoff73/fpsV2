<?php

use App\Services\Estate\ActuarialLifeTableService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed life expectancy data for testing
    $this->artisan('db:seed', ['--class' => 'UKLifeExpectancySeeder']);
    $this->service = new ActuarialLifeTableService();
});

test('it can get life expectancy for a given age and gender', function () {
    $lifeExpectancy = $this->service->getLifeExpectancy(65, 'male');

    expect($lifeExpectancy)->toBeFloat()
        ->and($lifeExpectancy)->toBeGreaterThan(0)
        ->and($lifeExpectancy)->toBeLessThan(50); // Reasonable upper bound
});

test('it can get estimated age at death', function () {
    $ageAtDeath = $this->service->getEstimatedAgeAtDeath(50, 'female');

    expect($ageAtDeath)->toBeInt()
        ->and($ageAtDeath)->toBeGreaterThan(50)
        ->and($ageAtDeath)->toBeLessThan(120);
});

test('it can calculate years until expected death from date of birth', function () {
    $dateOfBirth = Carbon::now()->subYears(40); // 40 years old

    $yearsUntilDeath = $this->service->getYearsUntilExpectedDeath($dateOfBirth, 'male');

    expect($yearsUntilDeath)->toBeInt()
        ->and($yearsUntilDeath)->toBeGreaterThan(0);
});

test('it can get estimated date of death', function () {
    $dateOfBirth = Carbon::now()->subYears(50);

    $estimatedDateOfDeath = $this->service->getEstimatedDateOfDeath($dateOfBirth, 'female');

    expect($estimatedDateOfDeath)->toBeInstanceOf(Carbon::class)
        ->and($estimatedDateOfDeath->isFuture())->toBeTrue();
});

test('it can perform surviving spouse analysis', function () {
    $userDOB = Carbon::now()->subYears(60);
    $spouseDOB = Carbon::now()->subYears(58);

    $analysis = $this->service->getSurvivingSpouseAnalysis(
        $userDOB,
        'male',
        $spouseDOB,
        'female'
    );

    expect($analysis)->toBeArray()
        ->and($analysis)->toHaveKeys([
            'user_current_age',
            'user_life_expectancy_years',
            'user_estimated_age_at_death',
            'spouse_current_age',
            'spouse_life_expectancy_years',
            'spouse_estimated_age_at_death',
            'first_to_decease',
            'surviving_person',
            'years_as_widow_widower',
        ])
        ->and($analysis['user_current_age'])->toBe(60)
        ->and($analysis['spouse_current_age'])->toBe(58);
});

test('female life expectancy is typically higher than male', function () {
    $maleLE = $this->service->getLifeExpectancy(65, 'male');
    $femaleLE = $this->service->getLifeExpectancy(65, 'female');

    expect($femaleLE)->toBeGreaterThan($maleLE);
});

test('it interpolates life expectancy for ages not in the table', function () {
    // Age 63 is likely not in our seeded data (we have 60 and 65)
    $lifeExpectancy = $this->service->getLifeExpectancy(63, 'male');

    expect($lifeExpectancy)->toBeFloat()
        ->and($lifeExpectancy)->toBeGreaterThan(0);
});
