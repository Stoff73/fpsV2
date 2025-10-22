<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | UK Tax Configuration - 2025/26 Tax Year
    |--------------------------------------------------------------------------
    |
    | This configuration file contains all UK tax rules, allowances, and rates
    | for the 2025/26 tax year (April 6, 2025 - April 5, 2026).
    |
    | Update this file annually after the UK Budget announcement.
    |
    */

    'tax_year' => '2025/26',
    'effective_from' => '2025-04-06',
    'effective_to' => '2026-04-05',

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

    /*
    |--------------------------------------------------------------------------
    | Trusts & IHT Planning
    |--------------------------------------------------------------------------
    |
    | Trust types and their IHT treatment under UK tax law.
    |
    */

    'trusts' => [
        'types' => [
            'bare' => [
                'name' => 'Bare Trust',
                'description' => 'Beneficiary has absolute entitlement to capital and income at age 18',
                'iht_treatment' => 'Assets count in beneficiary estate, not settlor',
                'periodic_charges' => false,
                'entry_charge' => false,
                'best_for' => 'Passing assets to young people with certainty',
            ],
            'interest_in_possession' => [
                'name' => 'Interest in Possession Trust',
                'description' => 'Beneficiary entitled to income, trustees hold capital',
                'iht_treatment' => 'Qualifying IIP: counts in life tenant estate. Non-qualifying: relevant property regime',
                'periodic_charges' => false, // For qualifying IIP
                'entry_charge' => false, // For qualifying IIP
                'best_for' => 'Providing income to spouse while preserving capital for children',
            ],
            'discretionary' => [
                'name' => 'Discretionary Trust',
                'description' => 'Trustees have full discretion over distributions',
                'iht_treatment' => 'Relevant property regime - outside settlor estate',
                'periodic_charges' => true,
                'periodic_charge_rate' => 0.06, // Up to 6% every 10 years
                'entry_charge' => true,
                'entry_charge_rate' => 0.20, // 20% on amounts over NRB
                'exit_charges' => true,
                'best_for' => 'Flexible planning where beneficiary needs uncertain or beneficiaries unable to manage money',
            ],
            'accumulation_maintenance' => [
                'name' => 'Accumulation & Maintenance Trust',
                'description' => 'Income accumulated for children, capital distributed at set age',
                'iht_treatment' => 'Relevant property regime (post-2006)',
                'periodic_charges' => true,
                'periodic_charge_rate' => 0.06,
                'entry_charge' => true,
                'entry_charge_rate' => 0.20,
                'best_for' => 'Providing for children during minority',
            ],
            'life_insurance' => [
                'name' => 'Life Insurance Trust',
                'description' => 'Life insurance policy written in trust',
                'iht_treatment' => 'Policy proceeds outside estate if written in trust from inception',
                'periodic_charges' => false,
                'entry_charge' => false, // No IHT if no change of ownership
                'best_for' => 'Providing liquid funds to pay IHT liability without affecting estate assets',
            ],
            'discounted_gift' => [
                'name' => 'Discounted Gift Trust',
                'description' => 'Gift to trust with retained income stream',
                'iht_treatment' => 'Actuarial discount applied - only retained income counts in estate',
                'periodic_charges' => false, // If structured as bare or qualifying IIP
                'entry_charge' => true, // PET on discounted value
                'best_for' => 'Reducing estate while retaining income',
                'typical_discount_range' => [0.30, 0.60], // 30-60% typical discount
            ],
            'loan' => [
                'name' => 'Loan Trust',
                'description' => 'Interest-free loan to trust, growth accrues outside estate',
                'iht_treatment' => 'Loan remains in estate, growth is outside estate immediately',
                'periodic_charges' => false,
                'entry_charge' => false, // Loan, not gift
                'best_for' => 'Freezing estate value while maintaining access to capital',
            ],
            'mixed' => [
                'name' => 'Mixed Trust',
                'description' => 'Combination of trust types',
                'iht_treatment' => 'Different parts taxed according to applicable rules',
                'periodic_charges' => null, // Depends on structure
                'entry_charge' => null,
                'best_for' => 'Complex estate planning with multiple objectives',
            ],
            'settlor_interested' => [
                'name' => 'Settlor-Interested Trust',
                'description' => 'Settlor or spouse can benefit',
                'iht_treatment' => 'Counts in settlor estate (reservation of benefit)',
                'periodic_charges' => false,
                'entry_charge' => false,
                'best_for' => 'Limited use - potential for reservation of benefit issues',
            ],
        ],

        'periodic_charges' => [
            'frequency_years' => 10,
            'max_rate' => 0.06, // 6% maximum
            'calculation_base' => 'cumulative_total', // Uses 7-year cumulation like IHT
        ],

        'exit_charges' => [
            'applies_to' => ['discretionary', 'accumulation_maintenance'],
            'calculation' => 'proportionate', // Proportionate to time since last 10-year charge
        ],
    ],
];
