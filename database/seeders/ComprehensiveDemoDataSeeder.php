<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\FamilyMember;
use App\Models\Estate\Asset;
use App\Models\Estate\Liability;
use App\Models\Estate\Gift;
use App\Models\Estate\IHTProfile;
use App\Models\Estate\Will;
use App\Models\Property;
use App\Models\Mortgage;
use App\Models\SavingsAccount;
use App\Models\SavingsGoal;
use App\Models\InvestmentAccount;
use App\Models\Holding;
use App\Models\InvestmentGoal;
use App\Models\RiskProfile;
use App\Models\DCPension;
use App\Models\DBPension;
use App\Models\StatePension;
use App\Models\RetirementProfile;
use App\Models\LifeInsurancePolicy;
use App\Models\ExpenditureProfile;
use Illuminate\Database\Seeder;

class ComprehensiveDemoDataSeeder extends Seeder
{
    private User $demoUser;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the demo user
        $this->demoUser = User::where('email', 'demo@fps.com')->first();

        if (!$this->demoUser) {
            $this->command->error('Demo user not found. Please run DemoUserSeeder first.');
            return;
        }

        // Update user profile with realistic data
        $this->demoUser->update([
            'date_of_birth' => '1980-05-15',
            'gender' => 'male',
            'marital_status' => 'married',
            'employment_status' => 'employed',
            'annual_employment_income' => 75000,
            'occupation' => 'Financial Analyst',
            'employer' => 'ABC Financial Services Ltd',
            'industry' => 'Financial Services',
        ]);

        $this->command->info('Seeding comprehensive demo data for: ' . $this->demoUser->email);

        // Seed all modules
        $this->seedFamilyMembers();
        $this->seedExpenditureProfile();
        $this->seedProtectionModule();
        $this->seedSavingsModule();
        $this->seedInvestmentModule();
        $this->seedRetirementModule();
        $this->seedPropertyModule();
        $this->seedEstateModule();

        $this->command->info('✅ Comprehensive demo data seeded successfully!');
        $this->command->info('Demo user now has:');
        $this->command->info('- Family members (spouse + 2 children)');
        $this->command->info('- Protection policies');
        $this->command->info('- Savings accounts and goals');
        $this->command->info('- Investment accounts and holdings');
        $this->command->info('- Pension accounts (DC, DB, State)');
        $this->command->info('- Properties with mortgages');
        $this->command->info('- Estate assets and liabilities');
        $this->command->info('- IHT profile with gifts and will');
    }

    private function seedFamilyMembers(): void
    {
        // Spouse
        FamilyMember::create([
            'user_id' => $this->demoUser->id,
            'relationship' => 'spouse',
            'name' => 'Sarah Demo',
            'date_of_birth' => '1982-08-20',
            'gender' => 'female',
            'is_dependent' => false,
            'annual_income' => 45000,
        ]);

        // Child 1
        FamilyMember::create([
            'user_id' => $this->demoUser->id,
            'relationship' => 'child',
            'name' => 'Emily Demo',
            'date_of_birth' => '2010-03-12',
            'gender' => 'female',
            'is_dependent' => true,
            'education_status' => 'secondary',
        ]);

        // Child 2
        FamilyMember::create([
            'user_id' => $this->demoUser->id,
            'relationship' => 'child',
            'name' => 'Jack Demo',
            'date_of_birth' => '2013-11-08',
            'gender' => 'male',
            'is_dependent' => true,
            'education_status' => 'primary',
        ]);

        $this->command->info('✓ Family members seeded');
    }

    private function seedExpenditureProfile(): void
    {
        ExpenditureProfile::create([
            'user_id' => $this->demoUser->id,
            'monthly_housing' => 1800,
            'monthly_utilities' => 300,
            'monthly_food' => 600,
            'monthly_transport' => 400,
            'monthly_insurance' => 250,
            'monthly_loans' => 500,
            'monthly_discretionary' => 650,
            'total_monthly_expenditure' => 4500,
        ]);

        $this->command->info('✓ Expenditure profile seeded');
    }

    private function seedProtectionModule(): void
    {
        // Life Insurance - Term Life
        LifeInsurancePolicy::create([
            'user_id' => $this->demoUser->id,
            'provider' => 'Aviva',
            'policy_type' => 'level_term',
            'sum_assured' => 250000,
            'premium_amount' => 35.50,
            'premium_frequency' => 'monthly',
            'policy_start_date' => '2020-01-15',
            'policy_term_years' => 25,
            'in_trust' => true,
            'beneficiaries' => json_encode(['Sarah Demo', 'Emily Demo', 'Jack Demo']),
        ]);

        // Life Insurance - Whole of Life
        LifeInsurancePolicy::create([
            'user_id' => $this->demoUser->id,
            'provider' => 'Legal & General',
            'policy_type' => 'whole_of_life',
            'sum_assured' => 100000,
            'premium_amount' => 85.00,
            'premium_frequency' => 'monthly',
            'policy_start_date' => '2022-06-01',
            'policy_term_years' => 50,
            'in_trust' => true,
            'beneficiaries' => json_encode(['Sarah Demo']),
        ]);

        $this->command->info('✓ Protection module seeded (2 life policies)');
    }

    private function seedSavingsModule(): void
    {
        // Emergency Fund - Cash ISA
        SavingsAccount::create([
            'user_id' => $this->demoUser->id,
            'institution' => 'Nationwide',
            'account_name' => 'Emergency Fund ISA',
            'account_type' => 'cash_isa',
            'current_balance' => 15000,
            'interest_rate' => 4.5,
            'ownership_type' => 'individual',
        ]);

        // Regular Saver
        SavingsAccount::create([
            'user_id' => $this->demoUser->id,
            'institution' => 'First Direct',
            'account_name' => 'Regular Saver',
            'account_type' => 'regular_saver',
            'current_balance' => 8500,
            'interest_rate' => 7.0,
            'ownership_type' => 'individual',
        ]);

        // Joint Savings
        SavingsAccount::create([
            'user_id' => $this->demoUser->id,
            'institution' => 'Santander',
            'account_name' => 'Joint Savings',
            'account_type' => 'instant_access',
            'current_balance' => 12000,
            'interest_rate' => 3.5,
            'ownership_type' => 'joint',
        ]);

        // Savings Goals
        SavingsGoal::create([
            'user_id' => $this->demoUser->id,
            'goal_name' => 'Holiday Fund 2026',
            'target_amount' => 5000,
            'current_amount' => 2800,
            'target_date' => '2026-07-01',
            'monthly_contribution' => 200,
        ]);

        SavingsGoal::create([
            'user_id' => $this->demoUser->id,
            'goal_name' => 'New Car',
            'target_amount' => 25000,
            'current_amount' => 8500,
            'target_date' => '2027-01-01',
            'monthly_contribution' => 500,
        ]);

        $this->command->info('✓ Savings module seeded (3 accounts, 2 goals)');
    }

    private function seedInvestmentModule(): void
    {
        // Risk Profile
        RiskProfile::create([
            'user_id' => $this->demoUser->id,
            'risk_score' => 65,
            'risk_category' => 'balanced',
            'time_horizon_years' => 20,
            'capacity_for_loss' => 'moderate',
        ]);

        // S&S ISA
        $isa = InvestmentAccount::create([
            'user_id' => $this->demoUser->id,
            'provider' => 'Vanguard',
            'account_name' => 'Stocks & Shares ISA',
            'account_type' => 'stocks_shares_isa',
            'current_value' => 45000,
            'ownership_type' => 'individual',
        ]);

        // Holdings for ISA
        Holding::create([
            'investment_account_id' => $isa->id,
            'security_name' => 'Vanguard FTSE Global All Cap',
            'ticker' => 'VWRL',
            'asset_class' => 'equity',
            'quantity' => 4200,
            'current_price' => 10.71,
            'current_value' => 45000,
        ]);

        // General Investment Account
        $gia = InvestmentAccount::create([
            'user_id' => $this->demoUser->id,
            'provider' => 'Hargreaves Lansdown',
            'account_name' => 'General Investment Account',
            'account_type' => 'general_investment',
            'current_value' => 28000,
            'ownership_type' => 'individual',
        ]);

        // Holdings for GIA
        Holding::create([
            'investment_account_id' => $gia->id,
            'security_name' => 'iShares Core FTSE 100',
            'ticker' => 'ISF',
            'asset_class' => 'equity',
            'quantity' => 3500,
            'current_price' => 8.00,
            'current_value' => 28000,
        ]);

        // Investment Goal
        InvestmentGoal::create([
            'user_id' => $this->demoUser->id,
            'goal_name' => 'Retirement Nest Egg',
            'target_amount' => 500000,
            'current_amount' => 73000,
            'target_date' => '2045-05-15',
            'monthly_contribution' => 800,
            'expected_return_rate' => 7.0,
        ]);

        $this->command->info('✓ Investment module seeded (2 accounts, 2 holdings, 1 goal)');
    }

    private function seedRetirementModule(): void
    {
        // Retirement Profile
        RetirementProfile::create([
            'user_id' => $this->demoUser->id,
            'target_retirement_age' => 65,
            'desired_retirement_income' => 35000,
            'state_pension_age' => 67,
        ]);

        // DC Pension 1 - Current Employer
        DCPension::create([
            'user_id' => $this->demoUser->id,
            'scheme_name' => 'ABC Ltd Pension Scheme',
            'provider' => 'Scottish Widows',
            'current_value' => 85000,
            'employee_contribution_rate' => 0.05,
            'employer_contribution_rate' => 0.08,
            'projected_growth_rate' => 0.055,
            'retirement_age' => 65,
        ]);

        // DC Pension 2 - Previous Employer
        DCPension::create([
            'user_id' => $this->demoUser->id,
            'scheme_name' => 'Old Company Pension',
            'provider' => 'Aviva',
            'current_value' => 42000,
            'employee_contribution_rate' => 0.00,
            'employer_contribution_rate' => 0.00,
            'projected_growth_rate' => 0.05,
            'retirement_age' => 65,
        ]);

        // DB Pension - Previous Public Sector Role
        DBPension::create([
            'user_id' => $this->demoUser->id,
            'scheme_name' => 'NHS Pension Scheme',
            'accrual_rate' => 0.0139,
            'years_of_service' => 8,
            'final_salary' => 38000,
            'revaluation_rate' => 0.015,
            'pension_age' => 65,
        ]);

        // State Pension
        StatePension::create([
            'user_id' => $this->demoUser->id,
            'qualifying_years' => 18,
            'projected_annual_amount' => 11502,
            'state_pension_age' => 67,
        ]);

        $this->command->info('✓ Retirement module seeded (2 DC, 1 DB, State Pension)');
    }

    private function seedPropertyModule(): void
    {
        // Main Residence
        $mainHome = Property::create([
            'user_id' => $this->demoUser->id,
            'address_line_1' => '42 Oak Avenue',
            'city' => 'Manchester',
            'postcode' => 'M20 4QR',
            'property_type' => 'detached',
            'ownership_type' => 'joint',
            'usage_type' => 'primary_residence',
            'purchase_date' => '2015-06-01',
            'purchase_price' => 280000,
            'current_value' => 425000,
            'last_valuation_date' => now()->subMonths(3),
        ]);

        // Mortgage on main residence
        Mortgage::create([
            'property_id' => $mainHome->id,
            'lender' => 'Nationwide',
            'mortgage_type' => 'repayment',
            'original_amount' => 224000,
            'outstanding_balance' => 165000,
            'interest_rate' => 3.89,
            'monthly_payment' => 1250,
            'start_date' => '2015-06-01',
            'end_date' => '2040-06-01',
            'remaining_term_months' => 180,
        ]);

        // Buy-to-Let Property
        $btl = Property::create([
            'user_id' => $this->demoUser->id,
            'address_line_1' => '15 River Street',
            'city' => 'Leeds',
            'postcode' => 'LS1 3BG',
            'property_type' => 'apartment',
            'ownership_type' => 'individual',
            'usage_type' => 'buy_to_let',
            'purchase_date' => '2018-09-15',
            'purchase_price' => 145000,
            'current_value' => 185000,
            'rental_income_monthly' => 950,
            'last_valuation_date' => now()->subMonths(6),
        ]);

        // Mortgage on BTL
        Mortgage::create([
            'property_id' => $btl->id,
            'lender' => 'Paragon Bank',
            'mortgage_type' => 'interest_only',
            'original_amount' => 108750,
            'outstanding_balance' => 108750,
            'interest_rate' => 4.25,
            'monthly_payment' => 385,
            'start_date' => '2018-09-15',
            'end_date' => '2043-09-15',
            'remaining_term_months' => 225,
        ]);

        $this->command->info('✓ Property module seeded (2 properties with mortgages)');
    }

    private function seedEstateModule(): void
    {
        // IHT Profile
        IHTProfile::create([
            'user_id' => $this->demoUser->id,
            'available_nrb' => 325000,
            'available_rnrb' => 175000,
            'spouse_nrb_transferred' => 0,
            'spouse_rnrb_transferred' => 0,
            'iht_planning_preference' => 'minimize_tax',
        ]);

        // Estate Assets (in addition to properties/investments already seeded)

        // Cash Accounts
        Asset::create([
            'user_id' => $this->demoUser->id,
            'asset_type' => 'cash',
            'asset_name' => 'Current Account',
            'current_value' => 8500,
            'ownership_type' => 'individual',
        ]);

        // Art & Collectibles
        Asset::create([
            'user_id' => $this->demoUser->id,
            'asset_type' => 'chattels',
            'asset_name' => 'Art Collection',
            'current_value' => 15000,
            'ownership_type' => 'individual',
        ]);

        // Vehicle
        Asset::create([
            'user_id' => $this->demoUser->id,
            'asset_type' => 'chattels',
            'asset_name' => 'BMW 5 Series',
            'current_value' => 22000,
            'ownership_type' => 'individual',
        ]);

        // Jewelry
        Asset::create([
            'user_id' => $this->demoUser->id,
            'asset_type' => 'chattels',
            'asset_name' => 'Jewelry & Watches',
            'current_value' => 12000,
            'ownership_type' => 'joint',
        ]);

        // Liabilities (in addition to mortgages)

        // Personal Loan
        Liability::create([
            'user_id' => $this->demoUser->id,
            'liability_type' => 'personal_loan',
            'description' => 'Car Loan',
            'outstanding_balance' => 8500,
            'monthly_payment' => 350,
            'interest_rate' => 5.9,
        ]);

        // Credit Card
        Liability::create([
            'user_id' => $this->demoUser->id,
            'liability_type' => 'credit_card',
            'description' => 'Credit Card Balance',
            'outstanding_balance' => 2500,
            'monthly_payment' => 150,
            'interest_rate' => 19.9,
        ]);

        // Gifts (PETs to demonstrate 7-year rule)
        Gift::create([
            'user_id' => $this->demoUser->id,
            'recipient_name' => 'Emily Demo',
            'gift_type' => 'PET',
            'amount' => 15000,
            'gift_date' => now()->subYears(2)->format('Y-m-d'),
            'description' => 'University fund contribution',
        ]);

        Gift::create([
            'user_id' => $this->demoUser->id,
            'recipient_name' => 'Jack Demo',
            'gift_type' => 'PET',
            'amount' => 10000,
            'gift_date' => now()->subYears(4)->format('Y-m-d'),
            'description' => 'Education trust fund',
        ]);

        Gift::create([
            'user_id' => $this->demoUser->id,
            'recipient_name' => 'Parents',
            'gift_type' => 'PET',
            'amount' => 25000,
            'gift_date' => now()->subYears(6)->format('Y-m-d'),
            'description' => 'Help with care home costs',
        ]);

        // Will
        Will::create([
            'user_id' => $this->demoUser->id,
            'will_type' => 'mirror_will',
            'created_date' => '2020-03-15',
            'last_updated' => '2023-01-10',
            'executor_names' => json_encode(['Sarah Demo', 'John Smith (Solicitor)']),
            'has_trust_provisions' => true,
            'main_beneficiaries' => json_encode([
                'Spouse' => 'Sarah Demo',
                'Children' => ['Emily Demo', 'Jack Demo']
            ]),
        ]);

        $this->command->info('✓ Estate module seeded (IHT profile, 4 assets, 2 liabilities, 3 gifts, will)');
    }
}
