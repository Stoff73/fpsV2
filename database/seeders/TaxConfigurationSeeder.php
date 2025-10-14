<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\TaxConfiguration;
use Illuminate\Database\Seeder;

class TaxConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Load the UK tax configuration
        $config = config('uk_tax_config');

        // Create or update the 2024/25 tax year configuration
        $taxConfig = TaxConfiguration::updateOrCreate(
            ['tax_year' => $config['tax_year']],
            [
                'effective_from' => $config['effective_from'],
                'effective_to' => $config['effective_to'],
                'config_data' => $config,
                'is_active' => true,
                'notes' => 'UK Tax Year 2024/25 - Initial configuration seeded from config file',
            ]
        );

        $this->command->info("Tax configuration for {$config['tax_year']} seeded successfully.");

        // Ensure this is the only active configuration
        TaxConfiguration::where('id', '!=', $taxConfig->id)
            ->update(['is_active' => false]);
    }
}
