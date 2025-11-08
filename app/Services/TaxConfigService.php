<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TaxConfiguration;
use Illuminate\Support\Arr;
use RuntimeException;

/**
 * Tax Configuration Service
 *
 * Provides centralized access to the active UK tax configuration.
 * Request-scoped singleton - loads active config once per request and caches in memory.
 *
 * Usage:
 *   $taxConfig = app(TaxConfigService::class);
 *   $personalAllowance = $taxConfig->get('income_tax.personal_allowance');
 *   $incomeTax = $taxConfig->getIncomeTax();
 */
class TaxConfigService
{
    /**
     * Cached active tax configuration (request-scoped)
     */
    private ?array $config = null;

    /**
     * Active tax configuration model
     */
    private ?TaxConfiguration $taxConfigModel = null;

    /**
     * Get the full active tax configuration
     *
     * @return array
     * @throws RuntimeException if no active tax year found
     */
    public function getAll(): array
    {
        return $this->loadActiveConfig();
    }

    /**
     * Get a specific tax configuration value using dot notation
     *
     * @param string $key Dot notation key (e.g., 'income_tax.personal_allowance')
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     * @throws RuntimeException if no active tax year found
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $config = $this->loadActiveConfig();

        return Arr::get($config, $key, $default);
    }

    /**
     * Check if a configuration key exists
     *
     * @param string $key Dot notation key
     * @return bool
     */
    public function has(string $key): bool
    {
        $config = $this->loadActiveConfig();

        return Arr::has($config, $key);
    }

    /**
     * Get the active tax year string
     *
     * @return string e.g., '2025/26'
     * @throws RuntimeException if no active tax year found
     */
    public function getTaxYear(): string
    {
        return $this->get('tax_year', '');
    }

    /**
     * Get the effective from date
     *
     * @return string e.g., '2025-04-06'
     * @throws RuntimeException if no active tax year found
     */
    public function getEffectiveFrom(): string
    {
        return $this->get('effective_from', '');
    }

    /**
     * Get the effective to date
     *
     * @return string e.g., '2026-04-05'
     * @throws RuntimeException if no active tax year found
     */
    public function getEffectiveTo(): string
    {
        return $this->get('effective_to', '');
    }

    /**
     * Check if a date falls within the current tax year
     *
     * @param \Carbon\Carbon|string $date
     * @return bool
     */
    public function isInCurrentTaxYear($date): bool
    {
        $effectiveFrom = $this->getEffectiveFrom();
        $effectiveTo = $this->getEffectiveTo();

        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        return $date->isBetween($effectiveFrom, $effectiveTo, true);
    }

    // =========================================================================
    // Module-Specific Helper Methods
    // =========================================================================

    /**
     * Get Income Tax configuration
     *
     * @return array Contains personal_allowance, bands, etc.
     */
    public function getIncomeTax(): array
    {
        return $this->get('income_tax', []);
    }

    /**
     * Get National Insurance configuration
     *
     * @return array Contains class_1, class_2, class_4 rates
     */
    public function getNationalInsurance(): array
    {
        return $this->get('national_insurance', []);
    }

    /**
     * Get ISA allowances configuration
     *
     * @return array Contains annual_allowance, lifetime_isa, junior_isa
     */
    public function getISAAllowances(): array
    {
        return $this->get('isa', []);
    }

    /**
     * Get Pension allowances configuration
     *
     * @return array Contains annual_allowance, MPAA, tapered_allowance, state_pension
     */
    public function getPensionAllowances(): array
    {
        return $this->get('pension', []);
    }

    /**
     * Get Inheritance Tax configuration
     *
     * @return array Contains NRB, RNRB, rates, PETs, CLTs
     */
    public function getInheritanceTax(): array
    {
        return $this->get('inheritance_tax', []);
    }

    /**
     * Get Capital Gains Tax configuration
     *
     * @return array Contains annual_exempt_amount, rates
     */
    public function getCapitalGainsTax(): array
    {
        return $this->get('capital_gains_tax', []);
    }

    /**
     * Get Dividend Tax configuration
     *
     * @return array Contains allowance, rates
     */
    public function getDividendTax(): array
    {
        return $this->get('dividend_tax', []);
    }

    /**
     * Get Stamp Duty Land Tax configuration
     *
     * @return array Contains residential and non_residential bands
     */
    public function getStampDuty(): array
    {
        return $this->get('stamp_duty', []);
    }

    /**
     * Get Gifting Exemptions configuration
     *
     * @return array Contains annual_exemption, small_gifts, wedding_gifts, etc.
     */
    public function getGiftingExemptions(): array
    {
        return $this->get('gifting_exemptions', []);
    }

    /**
     * Get Trusts configuration
     *
     * @return array Contains entry_charge, exit_charge, periodic_charge
     */
    public function getTrusts(): array
    {
        return $this->get('trusts', []);
    }

    /**
     * Get Investment/Financial Planning Assumptions
     *
     * @return array Contains investment_growth, inflation, salary_growth
     */
    public function getAssumptions(): array
    {
        return $this->get('assumptions', []);
    }

    /**
     * Get Domicile rules
     *
     * @return array Contains uk_domiciled, non_uk_domiciled rules
     */
    public function getDomicile(): array
    {
        return $this->get('domicile', []);
    }

    // =========================================================================
    // Private Methods
    // =========================================================================

    /**
     * Load active tax configuration (with request-scoped caching)
     *
     * @return array
     * @throws RuntimeException if no active tax year found
     */
    private function loadActiveConfig(): array
    {
        // Return cached config if already loaded
        if ($this->config !== null) {
            return $this->config;
        }

        // Load active tax configuration from database
        $this->taxConfigModel = TaxConfiguration::where('is_active', true)->first();

        if (! $this->taxConfigModel) {
            throw new RuntimeException(
                'No active tax configuration found. Please run TaxConfigurationSeeder or activate a tax year.'
            );
        }

        // Cache the config_data array for this request
        $this->config = $this->taxConfigModel->config_data;

        // Log which tax year is being used (helpful for debugging)
        logger()->debug('Tax Configuration Service loaded', [
            'tax_year' => $this->config['tax_year'] ?? 'unknown',
            'effective_from' => $this->config['effective_from'] ?? 'unknown',
        ]);

        return $this->config;
    }

    /**
     * Clear cached configuration (mainly for testing)
     *
     * @return void
     */
    public function clearCache(): void
    {
        $this->config = null;
        $this->taxConfigModel = null;
    }

    /**
     * Get the underlying TaxConfiguration model (if needed for relationships)
     *
     * @return TaxConfiguration|null
     */
    public function getModel(): ?TaxConfiguration
    {
        if ($this->taxConfigModel === null) {
            $this->loadActiveConfig();
        }

        return $this->taxConfigModel;
    }
}
