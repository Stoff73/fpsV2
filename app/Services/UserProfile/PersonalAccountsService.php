<?php

declare(strict_types=1);

namespace App\Services\UserProfile;

use App\Models\User;
use Carbon\Carbon;

class PersonalAccountsService
{
    /**
     * Calculate Profit and Loss statement for the user
     *
     * P&L = Total Income - Total Expenses
     */
    public function calculateProfitAndLoss(User $user, Carbon $startDate, Carbon $endDate): array
    {
        // Load relationships
        $user->load(['properties', 'mortgages']);

        // Calculate income line items
        $income = [
            [
                'line_item' => 'Employment Income',
                'category' => 'income',
                'amount' => $user->annual_employment_income ?? 0,
            ],
            [
                'line_item' => 'Self-Employment Income',
                'category' => 'income',
                'amount' => $user->annual_self_employment_income ?? 0,
            ],
            [
                'line_item' => 'Rental Income',
                'category' => 'income',
                'amount' => $user->annual_rental_income ?? 0,
            ],
            [
                'line_item' => 'Dividend Income',
                'category' => 'income',
                'amount' => $user->annual_dividend_income ?? 0,
            ],
            [
                'line_item' => 'Other Income',
                'category' => 'income',
                'amount' => $user->annual_other_income ?? 0,
            ],
        ];

        $totalIncome = collect($income)->sum('amount');

        // Calculate expense line items
        $mortgagePayments = $user->mortgages->sum(function ($mortgage) {
            return ($mortgage->monthly_payment ?? 0) * 12;
        });

        $propertyExpenses = $user->properties->sum(function ($property) {
            return ($property->annual_service_charge ?? 0) +
                   ($property->annual_ground_rent ?? 0) +
                   ($property->annual_insurance ?? 0) +
                   ($property->annual_maintenance_reserve ?? 0) +
                   ($property->other_annual_costs ?? 0);
        });

        $expenses = [
            [
                'line_item' => 'Mortgage Payments',
                'category' => 'expense',
                'amount' => $mortgagePayments,
            ],
            [
                'line_item' => 'Property Expenses',
                'category' => 'expense',
                'amount' => $propertyExpenses,
            ],
        ];

        $totalExpenses = collect($expenses)->sum('amount');

        $netProfitLoss = $totalIncome - $totalExpenses;

        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'income' => $income,
            'total_income' => $totalIncome,
            'expenses' => $expenses,
            'total_expenses' => $totalExpenses,
            'net_profit_loss' => $netProfitLoss,
        ];
    }

    /**
     * Calculate Cashflow statement for the user
     *
     * Cashflow = Cash Inflows - Cash Outflows
     */
    public function calculateCashflow(User $user, Carbon $startDate, Carbon $endDate): array
    {
        // Load relationships
        $user->load(['properties', 'mortgages', 'dcPensions']);

        // Cash inflows
        $inflows = [
            [
                'line_item' => 'Employment Income',
                'category' => 'cash_inflow',
                'amount' => $user->annual_employment_income ?? 0,
            ],
            [
                'line_item' => 'Self-Employment Income',
                'category' => 'cash_inflow',
                'amount' => $user->annual_self_employment_income ?? 0,
            ],
            [
                'line_item' => 'Rental Income',
                'category' => 'cash_inflow',
                'amount' => $user->annual_rental_income ?? 0,
            ],
            [
                'line_item' => 'Dividend Income',
                'category' => 'cash_inflow',
                'amount' => $user->annual_dividend_income ?? 0,
            ],
            [
                'line_item' => 'Other Income',
                'category' => 'cash_inflow',
                'amount' => $user->annual_other_income ?? 0,
            ],
        ];

        $totalInflows = collect($inflows)->sum('amount');

        // Cash outflows
        $mortgagePayments = $user->mortgages->sum(function ($mortgage) {
            return ($mortgage->monthly_payment ?? 0) * 12;
        });

        $propertyExpenses = $user->properties->sum(function ($property) {
            return ($property->annual_service_charge ?? 0) +
                   ($property->annual_ground_rent ?? 0) +
                   ($property->annual_insurance ?? 0) +
                   ($property->annual_maintenance_reserve ?? 0) +
                   ($property->other_annual_costs ?? 0);
        });

        // Pension contributions (assuming from DC pensions)
        $pensionContributions = $user->dcPensions->sum(function ($pension) use ($user) {
            $annualSalary = $pension->annual_salary ?? $user->annual_employment_income ?? 0;
            $employeePercent = $pension->employee_contribution_percent ?? 0;
            return $annualSalary * ($employeePercent / 100);
        });

        $outflows = [
            [
                'line_item' => 'Mortgage Payments',
                'category' => 'cash_outflow',
                'amount' => $mortgagePayments,
            ],
            [
                'line_item' => 'Property Expenses',
                'category' => 'cash_outflow',
                'amount' => $propertyExpenses,
            ],
            [
                'line_item' => 'Pension Contributions',
                'category' => 'cash_outflow',
                'amount' => $pensionContributions,
            ],
        ];

        $totalOutflows = collect($outflows)->sum('amount');

        $netCashflow = $totalInflows - $totalOutflows;

        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'inflows' => $inflows,
            'total_inflows' => $totalInflows,
            'outflows' => $outflows,
            'total_outflows' => $totalOutflows,
            'net_cashflow' => $netCashflow,
        ];
    }

    /**
     * Calculate Balance Sheet for the user
     *
     * Balance Sheet: Assets - Liabilities = Equity
     */
    public function calculateBalanceSheet(User $user, Carbon $asOfDate): array
    {
        // Load all asset and liability relationships
        $user->load([
            'cashAccounts',
            'investmentAccounts',
            'properties',
            'businessInterests',
            'chattels',
            'dcPensions',
            'mortgages',
        ]);

        // Calculate assets
        $cashTotal = $user->cashAccounts->sum(function ($account) {
            return $account->current_balance * ($account->ownership_percentage / 100);
        });

        $investmentsTotal = $user->investmentAccounts->sum(function ($account) {
            return $account->current_value * ($account->ownership_percentage / 100);
        });

        $propertiesTotal = $user->properties->sum(function ($property) {
            return $property->current_value * ($property->ownership_percentage / 100);
        });

        $businessTotal = $user->businessInterests->sum(function ($business) {
            return $business->current_valuation * ($business->ownership_percentage / 100);
        });

        $chattelsTotal = $user->chattels->sum(function ($chattel) {
            return $chattel->current_value * ($chattel->ownership_percentage / 100);
        });

        $pensionsTotal = $user->dcPensions->sum('current_fund_value');

        $assets = [
            [
                'line_item' => 'Cash & Cash Equivalents',
                'category' => 'asset',
                'amount' => $cashTotal,
            ],
            [
                'line_item' => 'Investments',
                'category' => 'asset',
                'amount' => $investmentsTotal,
            ],
            [
                'line_item' => 'Properties',
                'category' => 'asset',
                'amount' => $propertiesTotal,
            ],
            [
                'line_item' => 'Business Interests',
                'category' => 'asset',
                'amount' => $businessTotal,
            ],
            [
                'line_item' => 'Chattels',
                'category' => 'asset',
                'amount' => $chattelsTotal,
            ],
            [
                'line_item' => 'Pension Funds',
                'category' => 'asset',
                'amount' => $pensionsTotal,
            ],
        ];

        $totalAssets = collect($assets)->sum('amount');

        // Calculate liabilities
        $mortgagesTotal = $user->mortgages->sum('outstanding_balance');

        $liabilities = [
            [
                'line_item' => 'Mortgages',
                'category' => 'liability',
                'amount' => $mortgagesTotal,
            ],
        ];

        $totalLiabilities = collect($liabilities)->sum('amount');

        // Calculate equity
        $equity = $totalAssets - $totalLiabilities;

        return [
            'as_of_date' => $asOfDate->format('Y-m-d'),
            'assets' => $assets,
            'total_assets' => $totalAssets,
            'liabilities' => $liabilities,
            'total_liabilities' => $totalLiabilities,
            'equity' => [
                [
                    'line_item' => 'Net Worth (Equity)',
                    'category' => 'equity',
                    'amount' => $equity,
                ],
            ],
            'total_equity' => $equity,
        ];
    }
}
