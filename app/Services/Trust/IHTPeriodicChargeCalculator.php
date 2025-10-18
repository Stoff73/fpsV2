<?php

declare(strict_types=1);

namespace App\Services\Trust;

use App\Models\Estate\Trust;
use Carbon\Carbon;

class IHTPeriodicChargeCalculator
{
    // UK IHT rates and thresholds
    private const NRB = 325000; // Nil Rate Band
    private const IHT_RATE = 0.40; // 40% IHT rate
    private const PERIODIC_CHARGE_RATE = 0.06; // 6% charge every 10 years
    private const ENTRY_CHARGE_MAX = 0.20; // 20% max entry charge
    private const EXIT_CHARGE_MAX = 0.06; // 6% max exit charge

    /**
     * Calculate the 10-year periodic charge for relevant property trusts
     */
    public function calculatePeriodicCharge(Trust $trust, ?Carbon $chargeDate = null): array
    {
        // Only relevant property trusts are subject to periodic charges
        if (!$trust->isRelevantPropertyTrust()) {
            return [
                'charge_applicable' => false,
                'reason' => 'Not a relevant property trust',
                'charge_amount' => 0,
            ];
        }

        // Calculate years since trust creation
        $chargeDate = $chargeDate ?? Carbon::now();
        $trustCreationDate = Carbon::parse($trust->trust_creation_date);
        $yearsSinceCreation = $trustCreationDate->diffInYears($chargeDate);

        // Periodic charges apply every 10 years
        if ($yearsSinceCreation < 10 || $yearsSinceCreation % 10 !== 0) {
            $nextChargeDate = $trustCreationDate->copy()->addYears((int)(($yearsSinceCreation / 10) + 1) * 10);

            return [
                'charge_applicable' => false,
                'reason' => 'Next periodic charge due on ' . $nextChargeDate->format('Y-m-d'),
                'charge_amount' => 0,
                'next_charge_date' => $nextChargeDate,
                'years_until_next_charge' => $chargeDate->diffInYears($nextChargeDate, false),
            ];
        }

        // Get trust value for calculation
        $trustValue = $trust->total_asset_value ?? $trust->current_value ?? 0;

        // Calculate the chargeable value (value above NRB)
        $chargeableValue = max(0, $trustValue - self::NRB);

        // Effective rate is 30% of IHT rate (40% * 30% = 12%), applied over 10 years = 6%
        // But it's simplified to 6% of chargeable value every 10 years
        $periodicCharge = $chargeableValue * self::PERIODIC_CHARGE_RATE;

        return [
            'charge_applicable' => true,
            'trust_value' => $trustValue,
            'nil_rate_band' => self::NRB,
            'chargeable_value' => $chargeableValue,
            'periodic_charge_rate' => self::PERIODIC_CHARGE_RATE,
            'charge_amount' => $periodicCharge,
            'charge_date' => $chargeDate,
            'years_since_creation' => $yearsSinceCreation,
            'next_charge_date' => $chargeDate->copy()->addYears(10),
        ];
    }

    /**
     * Calculate exit charge when assets leave the trust
     */
    public function calculateExitCharge(Trust $trust, float $assetValue, Carbon $exitDate): array
    {
        // Only relevant property trusts are subject to exit charges
        if (!$trust->isRelevantPropertyTrust()) {
            return [
                'charge_applicable' => false,
                'reason' => 'Not a relevant property trust',
                'charge_amount' => 0,
            ];
        }

        $trustCreationDate = Carbon::parse($trust->trust_creation_date);
        $quartersSinceLastCharge = $this->calculateQuartersSinceLastCharge($trust, $exitDate);
        $trustValue = $trust->total_asset_value ?? $trust->current_value ?? 0;

        // Calculate chargeable value
        $chargeableValue = max(0, $trustValue - self::NRB);

        // Exit charge is proportionate to time since last periodic charge
        // Rate = (Periodic charge rate) * (quarters since last charge / 40 quarters)
        $effectiveRate = self::PERIODIC_CHARGE_RATE * ($quartersSinceLastCharge / 40);
        $exitCharge = ($assetValue / $trustValue) * $chargeableValue * $effectiveRate;

        // Cap at 6%
        $exitCharge = min($exitCharge, $assetValue * self::EXIT_CHARGE_MAX);

        return [
            'charge_applicable' => true,
            'asset_value' => $assetValue,
            'trust_value' => $trustValue,
            'chargeable_value' => $chargeableValue,
            'quarters_since_last_charge' => $quartersSinceLastCharge,
            'effective_rate' => $effectiveRate,
            'charge_amount' => $exitCharge,
            'exit_date' => $exitDate,
        ];
    }

    /**
     * Calculate entry charge when assets are added to the trust
     */
    public function calculateEntryCharge(float $assetValue): array
    {
        // Entry charge applies to transfers into discretionary trusts
        // Calculated as 20% of IHT that would be due on the transfer
        $chargeableValue = max(0, $assetValue - self::NRB);
        $entryCharge = $chargeableValue * self::IHT_RATE * (self::ENTRY_CHARGE_MAX / self::IHT_RATE);

        return [
            'charge_applicable' => $chargeableValue > 0,
            'asset_value' => $assetValue,
            'nil_rate_band' => self::NRB,
            'chargeable_value' => $chargeableValue,
            'entry_charge_rate' => self::ENTRY_CHARGE_MAX,
            'charge_amount' => $entryCharge,
        ];
    }

    /**
     * Calculate quarters since last periodic charge
     */
    private function calculateQuartersSinceLastCharge(Trust $trust, Carbon $exitDate): int
    {
        $trustCreationDate = Carbon::parse($trust->trust_creation_date);
        $lastChargeDate = $trust->last_periodic_charge_date
            ? Carbon::parse($trust->last_periodic_charge_date)
            : $trustCreationDate;

        // Calculate complete quarters
        $quarters = (int)floor($lastChargeDate->diffInMonths($exitDate) / 3);

        // Cap at 40 quarters (10 years)
        return min($quarters, 40);
    }

    /**
     * Get upcoming periodic charges for a user's trusts
     */
    public function getUpcomingCharges(int $userId, int $monthsAhead = 12): array
    {
        $trusts = Trust::where('user_id', $userId)
            ->where('is_active', true)
            ->get()
            ->filter(fn ($trust) => $trust->isRelevantPropertyTrust());

        $upcomingCharges = [];

        foreach ($trusts as $trust) {
            $trustCreationDate = Carbon::parse($trust->trust_creation_date);
            $now = Carbon::now();

            // Calculate next charge date
            $yearsSinceCreation = $trustCreationDate->diffInYears($now);
            $nextChargeYears = (int)((floor($yearsSinceCreation / 10) + 1) * 10);
            $nextChargeDate = $trustCreationDate->copy()->addYears($nextChargeYears);

            // Check if within the specified months ahead
            if ($nextChargeDate->lte($now->copy()->addMonths($monthsAhead))) {
                $chargeCalculation = $this->calculatePeriodicCharge($trust, $nextChargeDate);

                $upcomingCharges[] = [
                    'trust_id' => $trust->id,
                    'trust_name' => $trust->trust_name,
                    'charge_date' => $nextChargeDate,
                    'months_until_charge' => $now->diffInMonths($nextChargeDate, false),
                    'estimated_charge' => $chargeCalculation['charge_amount'] ?? 0,
                    'trust_value' => $trust->total_asset_value ?? $trust->current_value,
                ];
            }
        }

        // Sort by charge date
        usort($upcomingCharges, fn ($a, $b) => $a['charge_date'] <=> $b['charge_date']);

        return $upcomingCharges;
    }

    /**
     * Calculate tax return due dates for trusts
     */
    public function calculateTaxReturnDueDates(Trust $trust): array
    {
        $trustCreationDate = Carbon::parse($trust->trust_creation_date);
        $taxYearEnd = Carbon::create(null, 4, 5); // UK tax year ends April 5

        // Adjust to current or next tax year
        if ($taxYearEnd->lt(Carbon::now())) {
            $taxYearEnd->addYear();
        }

        // Trust tax returns typically due by January 31 following tax year end
        $returnDueDate = $taxYearEnd->copy()->addMonths(9)->day(31);

        return [
            'tax_year_end' => $taxYearEnd,
            'return_due_date' => $returnDueDate,
            'days_until_due' => Carbon::now()->diffInDays($returnDueDate, false),
            'is_overdue' => $returnDueDate->lt(Carbon::now()),
        ];
    }
}
