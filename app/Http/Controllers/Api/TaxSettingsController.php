<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TaxConfiguration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaxSettingsController extends Controller
{
    /**
     * Get current active tax configuration
     */
    public function getCurrent(): JsonResponse
    {
        try {
            $config = TaxConfiguration::where('is_active', true)->first();

            if (! $config) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active tax configuration found',
                ], 404);
            }

            // Flatten config_data into top-level response
            $response = [
                'id' => $config->id,
                'tax_year' => $config->tax_year,
                'effective_from' => $config->effective_from,
                'effective_to' => $config->effective_to,
                'is_active' => $config->is_active,
            ];

            // Merge config_data fields into response
            if ($config->config_data && is_array($config->config_data)) {
                $response = array_merge($response, $config->config_data);
            }

            return response()->json([
                'success' => true,
                'data' => $response,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tax configuration: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all tax configurations (including historical)
     */
    public function getAll(): JsonResponse
    {
        try {
            $configs = TaxConfiguration::orderBy('effective_from', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $configs,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tax configurations: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update tax configuration
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $config = TaxConfiguration::find($id);

        if (! $config) {
            return response()->json([
                'success' => false,
                'message' => 'Tax configuration not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'tax_year' => 'sometimes|string',
            'effective_from' => 'sometimes|date',
            'effective_to' => 'sometimes|date',
            'config_data' => 'sometimes|array',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            if ($request->has('tax_year')) {
                $config->tax_year = $request->tax_year;
            }
            if ($request->has('effective_from')) {
                $config->effective_from = $request->effective_from;
            }
            if ($request->has('effective_to')) {
                $config->effective_to = $request->effective_to;
            }
            if ($request->has('config_data')) {
                $config->config_data = $request->config_data;
            }
            if ($request->has('is_active') && $request->is_active) {
                // Deactivate all others first
                TaxConfiguration::where('is_active', true)->update(['is_active' => false]);
                $config->is_active = true;
            }

            $config->save();

            return response()->json([
                'success' => true,
                'message' => 'Tax configuration updated successfully',
                'data' => $config,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tax configuration: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create new tax configuration
     */
    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tax_year' => 'required|string|unique:tax_configurations,tax_year',
            'effective_from' => 'required|date',
            'effective_to' => 'required|date',
            'config_data' => 'required|array',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // If setting as active, deactivate all others
            if ($request->is_active) {
                TaxConfiguration::where('is_active', true)->update(['is_active' => false]);
            }

            $config = TaxConfiguration::create([
                'tax_year' => $request->tax_year,
                'effective_from' => $request->effective_from,
                'effective_to' => $request->effective_to,
                'config_data' => $request->config_data,
                'is_active' => $request->is_active ?? false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tax configuration created successfully',
                'data' => $config,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create tax configuration: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Set a tax configuration as active
     */
    public function setActive(int $id): JsonResponse
    {
        $config = TaxConfiguration::find($id);

        if (! $config) {
            return response()->json([
                'success' => false,
                'message' => 'Tax configuration not found',
            ], 404);
        }

        try {
            // Deactivate all others
            TaxConfiguration::where('is_active', true)->update(['is_active' => false]);

            // Activate this one
            $config->is_active = true;
            $config->save();

            return response()->json([
                'success' => true,
                'message' => 'Tax configuration activated successfully',
                'data' => $config,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate tax configuration: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get tax calculation formulas and explanations
     */
    public function getCalculations(): JsonResponse
    {
        try {
            $calculations = [
                'income_tax' => [
                    'name' => 'Income Tax',
                    'description' => 'UK Income Tax on earned income',
                    'formula' => 'Taxable Income × Tax Rate (after Personal Allowance)',
                    'bands' => [
                        'personal_allowance' => '£0 - £12,570 (0%)',
                        'basic_rate' => '£12,571 - £50,270 (20%)',
                        'higher_rate' => '£50,271 - £125,140 (40%)',
                        'additional_rate' => 'Over £125,140 (45%)',
                    ],
                    'notes' => 'Personal allowance reduces by £1 for every £2 earned over £100,000',
                ],
                'national_insurance' => [
                    'name' => 'National Insurance',
                    'description' => 'UK National Insurance contributions',
                    'class_1_employee' => [
                        'primary_threshold' => '£12,570 per year',
                        'upper_earnings_limit' => '£50,270 per year',
                        'main_rate' => '12% (between thresholds)',
                        'additional_rate' => '2% (above upper limit)',
                    ],
                    'class_1_employer' => [
                        'secondary_threshold' => '£9,100 per year',
                        'rate' => '13.8% (above threshold)',
                    ],
                    'class_4_self_employed' => [
                        'lower_profits_limit' => '£12,570 per year',
                        'upper_profits_limit' => '£50,270 per year',
                        'main_rate' => '9% (between limits)',
                        'additional_rate' => '2% (above upper limit)',
                    ],
                ],
                'inheritance_tax' => [
                    'name' => 'Inheritance Tax (IHT)',
                    'description' => 'Tax on estate value above nil rate bands',
                    'formula' => '(Estate Value - NRB - RNRB) × 40%',
                    'nil_rate_band' => '£325,000 (transferable between spouses)',
                    'residence_nil_rate_band' => '£175,000 (for main residence, transferable)',
                    'standard_rate' => '40%',
                    'reduced_rate' => '36% (if 10%+ to charity)',
                    'pets' => 'Potentially Exempt Transfers - 7 year rule with taper relief',
                    'taper_relief' => 'Years 3-7: 20% per year reduction in IHT',
                ],
                'capital_gains_tax' => [
                    'name' => 'Capital Gains Tax (CGT)',
                    'description' => 'Tax on profits from selling assets',
                    'formula' => '(Gain - Annual Exemption) × CGT Rate',
                    'annual_exemption' => '£3,000 per tax year',
                    'rates' => [
                        'basic_rate_taxpayer' => '10% (18% for property)',
                        'higher_rate_taxpayer' => '20% (28% for property)',
                    ],
                ],
                'pension_allowances' => [
                    'name' => 'Pension Allowances',
                    'annual_allowance' => '£60,000 per tax year',
                    'tapered_allowance' => 'Reduces for high earners (threshold income >£200k, adjusted income >£260k)',
                    'minimum_allowance' => '£10,000',
                    'money_purchase_annual_allowance' => '£10,000 (after flexibly accessing pension)',
                    'carry_forward' => 'Can carry forward unused allowance from previous 3 years',
                    'lifetime_allowance' => 'Abolished from April 2024',
                ],
                'isa_allowances' => [
                    'name' => 'ISA Allowances',
                    'total_allowance' => '£20,000 per tax year',
                    'cash_isa' => 'Part of total allowance',
                    'stocks_shares_isa' => 'Part of total allowance',
                    'lifetime_isa' => '£4,000 (counts towards total allowance)',
                    'innovative_finance_isa' => 'Part of total allowance',
                    'note' => 'Can split £20,000 across different ISA types',
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $calculations,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch calculations: '.$e->getMessage(),
            ], 500);
        }
    }
}
