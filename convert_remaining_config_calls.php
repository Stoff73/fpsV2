#!/usr/bin/env php
<?php

/**
 * Batch Script to Convert Remaining config() Calls to TaxConfigService
 *
 * This script systematically updates files to use TaxConfigService instead of config()
 */

$filesToUpdate = [
    'app/Agents/EstateAgent.php',
    'app/Services/Estate/GiftingStrategy.php',
    'app/Services/Estate/GiftingStrategyOptimizer.php',
    'app/Services/Estate/IHTStrategyGeneratorService.php',
    'app/Services/Estate/PersonalizedGiftingStrategyService.php',
    'app/Services/Estate/PersonalizedTrustStrategyService.php',
    'app/Services/Estate/SpouseNRBTrackerService.php',
    'app/Services/Estate/TrustService.php',
    'app/Services/Estate/CashFlowProjector.php',
    'app/Services/Estate/ComprehensiveEstatePlanService.php',
    'app/Services/Estate/FutureValueCalculator.php',
    'app/Services/Investment/TaxEfficiencyCalculator.php',
    'app/Services/Retirement/ContributionOptimizer.php',
    'app/Services/Trust/IHTPeriodicChargeCalculator.php',
];

$pattern = '/config\([\'"]uk_tax_config\.([^\'"]+)[\'"]\)/';
$replacements = [
    'inheritance_tax' => '$this->taxConfig->getInheritanceTax()',
    'pension' => '$this->taxConfig->getPensionAllowances()',
    'isa' => '$this->taxConfig->getISAAllowances()',
    'income_tax' => '$this->taxConfig->getIncomeTax()',
    'capital_gains_tax' => '$this->taxConfig->getCapitalGainsTax()',
    'dividend_tax' => '$this->taxConfig->getDividendTax()',
    'gifting_exemptions' => '$this->taxConfig->getGiftingExemptions()',
    'trusts' => '$this->taxConfig->getTrusts()',
];

echo "Files to update: " . count($filesToUpdate) . "\n";
echo "Pattern: Direct config('uk_tax_config.*') -> TaxConfigService methods\n\n";

foreach ($filesToUpdate as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $count = preg_match_all($pattern, $content, $matches);

        if ($count > 0) {
            echo "✓ {$file}: {$count} config() calls found\n";
            foreach ($matches[1] as $configKey) {
                $baseKey = explode('.', $configKey)[0];
                if (isset($replacements[$baseKey])) {
                    echo "  - {$configKey} -> {$replacements[$baseKey]}\n";
                }
            }
        }
    }
}

echo "\n=== MANUAL STEPS REQUIRED ===\n";
echo "1. Add TaxConfigService dependency injection to each class constructor\n";
echo "2. Add 'use App\\Services\\TaxConfigService;' to imports\n";
echo "3. Replace config() calls with appropriate TaxConfigService methods\n";
echo "4. For nested paths, extract the full config array then access nested keys\n";
echo "\nExample:\n";
echo "  OLD: config('uk_tax_config.inheritance_tax.nil_rate_band')\n";
echo "  NEW: \$ihtConfig = \$this->taxConfig->getInheritanceTax();\n";
echo "       \$nrb = \$ihtConfig['nil_rate_band'];\n";
