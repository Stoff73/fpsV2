<?php

declare(strict_types=1);

namespace App\Services\Property;

use App\Models\Mortgage;
use Carbon\Carbon;

class MortgageService
{
    /**
     * Calculate monthly mortgage payment
     *
     * @param  float  $annualInterestRate  (e.g., 5.5 for 5.5%)
     * @param  string  $mortgageType  ('repayment' or 'interest_only')
     */
    public function calculateMonthlyPayment(
        float $loanAmount,
        float $annualInterestRate,
        int $termMonths,
        string $mortgageType = 'repayment'
    ): float {
        if ($loanAmount <= 0 || $termMonths <= 0) {
            return 0;
        }

        $monthlyRate = ($annualInterestRate / 100) / 12;

        if ($mortgageType === 'interest_only') {
            // Interest-only: monthly payment = loan amount × monthly interest rate
            return $loanAmount * $monthlyRate;
        }

        // Repayment mortgage formula: M = P[r(1+r)^n]/[(1+r)^n-1]
        // Where: M = monthly payment, P = principal, r = monthly rate, n = number of payments

        if ($monthlyRate == 0) {
            // No interest
            return $loanAmount / $termMonths;
        }

        $monthlyPayment = $loanAmount * (
            ($monthlyRate * pow(1 + $monthlyRate, $termMonths)) /
            (pow(1 + $monthlyRate, $termMonths) - 1)
        );

        return round($monthlyPayment, 2);
    }

    /**
     * Generate amortization schedule for a mortgage
     */
    public function generateAmortizationSchedule(Mortgage $mortgage): array
    {
        $schedule = [];

        $balance = (float) ($mortgage->outstanding_balance ?? 0);
        $monthlyPayment = (float) ($mortgage->monthly_payment ?? 0);
        $annualRate = (float) ($mortgage->interest_rate ?? 0);
        $monthlyRate = ($annualRate / 100) / 12;
        $remainingMonths = $mortgage->remaining_term_months ?? 0;
        $startDate = $mortgage->start_date ? Carbon::parse($mortgage->start_date) : Carbon::now();
        $mortgageType = $mortgage->mortgage_type ?? 'repayment';

        $currentDate = Carbon::now();
        $monthsPassed = $startDate->diffInMonths($currentDate);
        $currentMonth = $monthsPassed;

        // Generate schedule for remaining term
        for ($month = $currentMonth; $month < $currentMonth + $remainingMonths; $month++) {
            $paymentDate = $startDate->copy()->addMonths($month);
            $openingBalance = $balance;
            $interestPayment = $balance * $monthlyRate;

            // For interest-only mortgages, principal payment is 0
            if ($mortgageType === 'interest_only') {
                $principalPayment = 0;
            } else {
                $principalPayment = $monthlyPayment - $interestPayment;

                // Ensure we don't overpay on the last payment
                if ($principalPayment > $balance) {
                    $principalPayment = $balance;
                    $monthlyPayment = $principalPayment + $interestPayment;
                }
            }

            $balance -= $principalPayment;
            $closingBalance = max(0, $balance);

            $schedule[] = [
                'month' => $month + 1,
                'payment_date' => $paymentDate->format('Y-m-d'),
                'opening_balance' => round($openingBalance, 2),
                'payment' => round($monthlyPayment, 2),
                'principal' => round($principalPayment, 2),
                'interest' => round($interestPayment, 2),
                'closing_balance' => round($closingBalance, 2),
            ];

            if ($balance <= 0) {
                break;
            }
        }

        return [
            'mortgage_id' => $mortgage->id,
            'lender' => $mortgage->lender_name,
            'original_loan' => $mortgage->original_loan_amount,
            'outstanding_balance' => $mortgage->outstanding_balance,
            'interest_rate' => $mortgage->interest_rate,
            'monthly_payment' => $mortgage->monthly_payment,
            'remaining_months' => $remainingMonths,
            'schedule' => $schedule,
            'total_payments' => count($schedule),
            'total_interest' => array_sum(array_column($schedule, 'interest')),
            'total_principal' => array_sum(array_column($schedule, 'principal')),
        ];
    }

    /**
     * Calculate remaining term in months
     */
    public function calculateRemainingTerm(Mortgage $mortgage): int
    {
        if (! $mortgage->maturity_date) {
            return $mortgage->remaining_term_months ?? 0;
        }

        $today = Carbon::now();
        $maturityDate = Carbon::parse($mortgage->maturity_date);

        if ($maturityDate->isPast()) {
            return 0;
        }

        return $today->diffInMonths($maturityDate);
    }

    /**
     * Calculate total interest to be paid over remaining term
     */
    public function calculateTotalInterest(Mortgage $mortgage): float
    {
        $schedule = $this->generateAmortizationSchedule($mortgage);

        return $schedule['total_interest'] ?? 0;
    }

    /**
     * Calculate equity being built per year
     */
    public function calculateAnnualEquityBuild(Mortgage $mortgage): array
    {
        $schedule = $this->generateAmortizationSchedule($mortgage);
        $annualData = [];

        $currentYear = null;
        $yearlyPrincipal = 0;
        $yearlyInterest = 0;

        foreach ($schedule['schedule'] as $payment) {
            $year = Carbon::parse($payment['payment_date'])->year;

            if ($currentYear !== null && $year !== $currentYear) {
                $annualData[] = [
                    'year' => $currentYear,
                    'principal_paid' => round($yearlyPrincipal, 2),
                    'interest_paid' => round($yearlyInterest, 2),
                    'total_paid' => round($yearlyPrincipal + $yearlyInterest, 2),
                ];
                $yearlyPrincipal = 0;
                $yearlyInterest = 0;
            }

            $currentYear = $year;
            $yearlyPrincipal += $payment['principal'];
            $yearlyInterest += $payment['interest'];
        }

        // Add the last year
        if ($currentYear !== null) {
            $annualData[] = [
                'year' => $currentYear,
                'principal_paid' => round($yearlyPrincipal, 2),
                'interest_paid' => round($yearlyInterest, 2),
                'total_paid' => round($yearlyPrincipal + $yearlyInterest, 2),
            ];
        }

        return [
            'mortgage_id' => $mortgage->id,
            'annual_breakdown' => $annualData,
        ];
    }
}
