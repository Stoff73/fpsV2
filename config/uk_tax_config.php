<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | UK Tax Configuration - 2024/25 Tax Year
    |--------------------------------------------------------------------------
    |
    | This configuration file contains all UK tax rules, allowances, and rates
    | for the 2024/25 tax year (April 6, 2024 - April 5, 2025).
    |
    | Update this file annually after the UK Budget announcement.
    |
    */

    'tax_year' => '2024/25',
    'effective_from' => '2024-04-06',
    'effective_to' => '2025-04-05',

    /*
    |--------------------------------------------------------------------------
    | Income Tax
    |--------------------------------------------------------------------------
    */

    'income_tax' => [
        'personal_allowance' => 12570,
        'personal_allowance_taper_threshold' => 100000,
        'personal_allowance_taper_rate' => 0.5, // £1 reduction for every £2 over threshold

        'bands' => [
            [
                'name' => 'Basic Rate',
                'min' => 0,
                'max' => 37700,
                'rate' => 0.20,
            ],
            [
                'name' => 'Higher Rate',
                'min' => 37700,
                'max' => 125140,
                'rate' => 0.40,
            ],
            [
                'name' => 'Additional Rate',
                'min' => 125140,
                'max' => null, // No upper limit
                'rate' => 0.45,
            ],
        ],

        // Scotland has different income tax bands (if needed in future)
        'scotland' => [
            'enabled' => false,
            'bands' => [],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | National Insurance
    |--------------------------------------------------------------------------
    */

    'national_insurance' => [
        'class_1' => [
            'employee' => [
                'primary_threshold' => 12570,
                'upper_earnings_limit' => 50270,
                'main_rate' => 0.12,
                'additional_rate' => 0.02,
            ],
            'employer' => [
                'secondary_threshold' => 9100,
                'rate' => 0.138,
            ],
        ],
        'class_2' => [
            'small_profits_threshold' => 6725,
            'weekly_rate' => 3.45,
        ],
        'class_4' => [
            'lower_profits_limit' => 12570,
            'upper_profits_limit' => 50270,
            'main_rate' => 0.09,
            'additional_rate' => 0.02,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Capital Gains Tax
    |--------------------------------------------------------------------------
    */

    'capital_gains_tax' => [
        'annual_exempt_amount' => 3000,
        'rates' => [
            'basic_rate_taxpayer' => 0.10,
            'higher_rate_taxpayer' => 0.20,
            'residential_property_basic' => 0.18,
            'residential_property_higher' => 0.24,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dividend Tax
    |--------------------------------------------------------------------------
    */

    'dividend_tax' => [
        'allowance' => 500,
        'rates' => [
            'basic_rate' => 0.0875,
            'higher_rate' => 0.3375,
            'additional_rate' => 0.3935,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | ISA Allowances
    |--------------------------------------------------------------------------
    */

    'isa' => [
        'annual_allowance' => 20000,
        'lifetime_isa' => [
            'annual_allowance' => 4000,
            'max_age_to_open' => 39,
            'government_bonus_rate' => 0.25,
            'withdrawal_penalty' => 0.25, // If withdrawn before 60 (except first home)
        ],
        'junior_isa' => [
            'annual_allowance' => 9000,
            'max_age' => 17,
        ],
        'help_to_buy_isa' => [
            'closed_to_new_accounts' => true,
            'max_monthly_deposit' => 200,
            'first_month_bonus' => 1000,
            'government_bonus_rate' => 0.25,
            'max_bonus' => 3000,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pension Allowances
    |--------------------------------------------------------------------------
    */

    'pension' => [
        'annual_allowance' => 60000,
        'money_purchase_annual_allowance' => 10000, // After flexi-access
        'lifetime_allowance_abolished' => true, // Abolished April 2024
        'carry_forward_years' => 3,

        'tapered_annual_allowance' => [
            'threshold_income' => 200000,
            'adjusted_income' => 260000,
            'minimum_allowance' => 10000,
            'taper_rate' => 0.5, // £1 reduction for every £2 over adjusted income
        ],

        'tax_relief' => [
            'basic_rate' => 0.20,
            'higher_rate' => 0.40,
            'additional_rate' => 0.45,
        ],

        'state_pension' => [
            'full_new_state_pension' => 11502.40, // Annual
            'qualifying_years' => 35,
            'minimum_qualifying_years' => 10,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Inheritance Tax (IHT)
    |--------------------------------------------------------------------------
    */

    'inheritance_tax' => [
        'nil_rate_band' => 325000,
        'residence_nil_rate_band' => 175000,
        'rnrb_taper_threshold' => 2000000, // Estate value at which RNRB starts to taper
        'rnrb_taper_rate' => 0.5, // £1 reduction for every £2 over threshold
        'standard_rate' => 0.40,
        'reduced_rate_charity' => 0.36, // If 10%+ left to charity

        'spouse_exemption' => true,
        'transferable_nil_rate_band' => true,

        'potentially_exempt_transfers' => [
            'years_to_exemption' => 7,
            'taper_relief' => [
                ['years' => 3, 'rate' => 0.40],
                ['years' => 4, 'rate' => 0.32],
                ['years' => 5, 'rate' => 0.24],
                ['years' => 6, 'rate' => 0.16],
                ['years' => 7, 'rate' => 0.08],
            ],
        ],

        'chargeable_lifetime_transfers' => [
            'lookback_period' => 14, // Years for cumulative total
            'rate' => 0.20, // If exceeds NRB during lifetime
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IHT Gifting Exemptions
    |--------------------------------------------------------------------------
    */

    'gifting_exemptions' => [
        'annual_exemption' => 3000,
        'annual_exemption_can_carry_forward' => true,
        'carry_forward_years' => 1,

        'small_gifts' => [
            'amount' => 250,
            'per_person' => true,
        ],

        'wedding_gifts' => [
            'child' => 5000,
            'grandchild_great_grandchild' => 2500,
            'other' => 1000,
        ],

        'normal_expenditure_out_of_income' => [
            'unlimited' => true,
            'conditions' => [
                'Regular pattern',
                'Made out of income (not capital)',
                'Does not reduce standard of living',
            ],
        ],

        'maintenance_gifts' => [
            'spouse_civil_partner' => true,
            'dependent_children' => true,
            'dependent_relatives' => true,
        ],

        'charity_gifts' => [
            'unlimited' => true,
            'exempt' => true,
        ],

        'political_party_gifts' => [
            'unlimited' => true,
            'exempt' => true,
            'conditions' => 'Must meet qualifying conditions',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Other Allowances & Rates
    |--------------------------------------------------------------------------
    */

    'other' => [
        'marriage_allowance' => [
            'transferable_amount' => 1260,
            'max_income_transferee' => 12570,
            'min_income_transferor' => 0,
        ],

        'savings_allowance' => [
            'basic_rate_taxpayer' => 1000,
            'higher_rate_taxpayer' => 500,
            'additional_rate_taxpayer' => 0,
        ],

        'starting_rate_for_savings' => [
            'band' => 5000,
            'rate' => 0.00,
            'reduces_with_income' => true,
        ],

        'blind_persons_allowance' => 3070,

        'child_benefit' => [
            'eldest_child' => 25.60, // Weekly
            'additional_child' => 16.95, // Weekly
            'high_income_charge_threshold' => 60000,
            'high_income_charge_rate' => 0.01, // Per £100 over threshold
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Investment & Savings Rates (Reference Only)
    |--------------------------------------------------------------------------
    |
    | These are typical market rates for planning purposes.
    | Actual rates vary by provider and market conditions.
    |
    */

    'assumptions' => [
        'inflation_rate' => 0.02, // 2% assumed
        'investment_growth_rate' => 0.05, // 5% assumed for balanced portfolio
        'cash_savings_rate' => 0.04, // 4% assumed
        'mortgage_rate' => 0.055, // 5.5% assumed
        'state_pension_increase_rate' => 0.025, // Triple lock assumption
    ],
];
