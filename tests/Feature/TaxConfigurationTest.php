<?php

declare(strict_types=1);

use App\Models\TaxConfiguration;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed the tax configuration
    $this->seed(\Database\Seeders\TaxConfigurationSeeder::class);
});

test('tax configuration exists for 2024/25 tax year', function () {
    $config = TaxConfiguration::where('tax_year', '2024/25')->first();

    expect($config)->not()->toBeNull();
});

test('tax configuration has required fields', function () {
    $config = TaxConfiguration::where('tax_year', '2024/25')->first();

    expect($config)->not()->toBeNull();
    expect($config->tax_year)->toBe('2024/25');
    expect($config->config_data)->not()->toBeNull();
    expect($config->config_data)->toBeArray();
    expect($config->is_active)->toBeTrue();
});

test('personal allowance configuration exists', function () {
    $config = TaxConfiguration::where('tax_year', '2024/25')->first();

    expect($config)->not()->toBeNull();
    expect($config->config_data)->toHaveKey('income_tax');
    expect($config->config_data['income_tax'])->toHaveKey('personal_allowance');
    expect($config->config_data['income_tax']['personal_allowance'])->toBe(12570);
});

test('isa annual allowance configuration exists', function () {
    $config = TaxConfiguration::where('tax_year', '2024/25')->first();

    expect($config)->not()->toBeNull();
    expect($config->config_data)->toHaveKey('isa');
    expect($config->config_data['isa'])->toHaveKey('annual_allowance');
    expect($config->config_data['isa']['annual_allowance'])->toBe(20000);
});

test('pension annual allowance configuration exists', function () {
    $config = TaxConfiguration::where('tax_year', '2024/25')->first();

    expect($config)->not()->toBeNull();
    expect($config->config_data)->toHaveKey('pension');
    expect($config->config_data['pension'])->toHaveKey('annual_allowance');
    expect($config->config_data['pension']['annual_allowance'])->toBe(60000);
});

test('inheritance tax nil rate band configuration exists', function () {
    $config = TaxConfiguration::where('tax_year', '2024/25')->first();

    expect($config)->not()->toBeNull();
    expect($config->config_data)->toHaveKey('inheritance_tax');
    expect($config->config_data['inheritance_tax'])->toHaveKey('nil_rate_band');
    expect($config->config_data['inheritance_tax']['nil_rate_band'])->toBe(325000);
});

test('inheritance tax residence nil rate band configuration exists', function () {
    $config = TaxConfiguration::where('tax_year', '2024/25')->first();

    expect($config)->not()->toBeNull();
    expect($config->config_data['inheritance_tax'])->toHaveKey('residence_nil_rate_band');
    expect($config->config_data['inheritance_tax']['residence_nil_rate_band'])->toBe(175000);
});

test('inheritance tax standard rate configuration exists', function () {
    $config = TaxConfiguration::where('tax_year', '2024/25')->first();

    expect($config)->not()->toBeNull();
    expect($config->config_data['inheritance_tax'])->toHaveKey('standard_rate');
    expect($config->config_data['inheritance_tax']['standard_rate'])->toBe(0.40);
});

test('can retrieve complete tax configuration for a tax year', function () {
    $config = TaxConfiguration::where('tax_year', '2024/25')->first();

    expect($config->config_data)->toHaveKeys([
        'income_tax',
        'isa',
        'pension',
        'inheritance_tax',
        'national_insurance',
        'capital_gains_tax',
        'dividend_tax',
        'gifting_exemptions',
    ]);
});
