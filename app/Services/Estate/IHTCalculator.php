<?php

declare(strict_types=1);

namespace App\Services\Estate;

use App\Models\Estate\Asset;
use App\Models\Estate\Gift;
use App\Models\Estate\IHTProfile;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class IHTCalculator
{
    /**
     * Calculate IHT liability on an estate
     */
    public function calculateIHTLiability(Collection $assets, IHTProfile $profile): array
    {
        // Calculate gross estate value
        $grossEstateValue = $assets->sum('current_value');

        // Get tax config
        $config = config('uk_tax_config.inheritance_tax');

        // Apply NRB (Nil Rate Band)
        $nrb = $config['nil_rate_band'];

        // Add spouse NRB transfer if applicable
        $totalNRB = $nrb + $profile->nrb_transferred_from_spouse;

        // Check RNRB eligibility
        $rnrb = $this->checkRNRBEligibility($profile, $assets)
            ? $this->calculateRNRB($grossEstateValue, $config)
            : 0;

        // Calculate total tax-free allowance
        $totalAllowance = $totalNRB + $rnrb;

        // Calculate taxable estate
        $taxableEstate = max(0, $grossEstateValue - $totalAllowance);

        // Determine IHT rate (standard 40% or reduced 36% for charity)
        $ihtRate = $this->calculateCharitableReduction($grossEstateValue, $profile->charitable_giving_percent);

        // Calculate IHT liability
        $ihtLiability = $taxableEstate * $ihtRate;

        return [
            'gross_estate_value' => round($grossEstateValue, 2),
            'nrb' => round($nrb, 2),
            'nrb_from_spouse' => round($profile->nrb_transferred_from_spouse, 2),
            'total_nrb' => round($totalNRB, 2),
            'rnrb' => round($rnrb, 2),
            'rnrb_eligible' => $rnrb > 0,
            'total_allowance' => round($totalAllowance, 2),
            'taxable_estate' => round($taxableEstate, 2),
            'iht_rate' => $ihtRate,
            'iht_liability' => round($ihtLiability, 2),
            'effective_rate' => $grossEstateValue > 0
                ? round(($ihtLiability / $grossEstateValue) * 100, 2)
                : 0,
        ];
    }

    /**
     * Check if estate qualifies for Residence Nil Rate Band (RNRB)
     */
    public function checkRNRBEligibility(IHTProfile $profile, Collection $assets): bool
    {
        // Must own a home
        if (!$profile->own_home) {
            return false;
        }

        // Home value must be > 0
        if ($profile->home_value <= 0) {
            return false;
        }

        // Must have direct descendants (simplified check - in real system would check will)
        // For now, we assume if they own a home, they qualify
        // In production, you'd check beneficiary designation for direct descendants

        return true;
    }

    /**
     * Calculate RNRB amount including taper
     */
    private function calculateRNRB(float $estateValue, array $config): float
    {
        $rnrb = $config['residence_nil_rate_band'];
        $taperThreshold = $config['rnrb_taper_threshold'];
        $taperRate = $config['rnrb_taper_rate'];

        // If estate value exceeds taper threshold, reduce RNRB
        if ($estateValue > $taperThreshold) {
            $excess = $estateValue - $taperThreshold;
            $reduction = $excess * $taperRate;
            $rnrb = max(0, $rnrb - $reduction);
        }

        return $rnrb;
    }

    /**
     * Calculate IHT rate considering charitable giving
     * Returns 0.36 if 10%+ to charity, otherwise 0.40
     */
    public function calculateCharitableReduction(float $estate, float $charitablePercent): float
    {
        $config = config('uk_tax_config.inheritance_tax');

        if ($charitablePercent >= 10) {
            return $config['reduced_rate_charity'];
        }

        return $config['standard_rate'];
    }

    /**
     * Apply taper relief to a potentially exempt transfer (PET)
     * Based on years since gift was made
     */
    public function applyTaperRelief(Gift $gift): float
    {
        $config = config('uk_tax_config.inheritance_tax.potentially_exempt_transfers');
        $yearsAgo = Carbon::now()->diffInYears($gift->gift_date);

        // If gift is more than 7 years old, it's fully exempt
        if ($yearsAgo >= $config['years_to_exemption']) {
            return 0;
        }

        // Get taper relief schedule
        $taperSchedule = $config['taper_relief'];

        // Find applicable taper rate
        $applicableRate = 0.40; // Default full rate for years 0-3

        foreach ($taperSchedule as $tier) {
            if ($yearsAgo < $tier['years']) {
                $applicableRate = $tier['rate'];
                break;
            }
        }

        // Calculate tax on gift value
        // (Assumes gift exceeds annual exemptions)
        return $gift->gift_value * $applicableRate;
    }

    /**
     * Calculate total PET liability considering all gifts within 7 years
     */
    public function calculatePETLiability(Collection $gifts): array
    {
        $config = config('uk_tax_config.inheritance_tax');
        $nrb = $config['nil_rate_band'];

        // Filter gifts within 7 years
        $recentGifts = $gifts->filter(function ($gift) {
            $yearsAgo = Carbon::now()->diffInYears($gift->gift_date);
            return $yearsAgo < 7 && $gift->gift_type === 'pet';
        })->sortBy('gift_date');

        $totalGiftValue = $recentGifts->sum('gift_value');
        $totalLiability = 0;
        $runningTotal = 0;

        $giftDetails = [];

        foreach ($recentGifts as $gift) {
            $runningTotal += $gift->gift_value;

            // Calculate taxable amount (amount over NRB)
            $taxableAmount = max(0, $runningTotal - $nrb);

            if ($taxableAmount > 0) {
                $giftTax = $this->applyTaperRelief($gift);
                $totalLiability += $giftTax;

                $giftDetails[] = [
                    'gift_id' => $gift->id,
                    'gift_date' => $gift->gift_date->format('Y-m-d'),
                    'recipient' => $gift->recipient,
                    'gift_value' => $gift->gift_value,
                    'years_ago' => Carbon::now()->diffInYears($gift->gift_date),
                    'tax_liability' => round($giftTax, 2),
                ];
            }
        }

        return [
            'total_gift_value' => round($totalGiftValue, 2),
            'total_pet_liability' => round($totalLiability, 2),
            'gift_count' => $recentGifts->count(),
            'gifts' => $giftDetails,
        ];
    }

    /**
     * Calculate CLT (Chargeable Lifetime Transfer) liability
     * CLTs require 14-year lookback for cumulative calculation
     *
     * @param Collection $gifts All gifts (includes CLTs to trusts, etc.)
     * @return array CLT calculation details
     */
    public function calculateCLTLiability(Collection $gifts): array
    {
        $config = config('uk_tax_config.inheritance_tax');
        $nrb = $config['nil_rate_band'];
        $ihtRate = $config['rate'];

        // Filter CLT gifts within 14 years (lookback period for cumulation)
        $cltGifts = $gifts->filter(function ($gift) {
            $yearsAgo = Carbon::now()->diffInYears($gift->gift_date);
            return $yearsAgo < 14 && $gift->gift_type === 'clt';
        })->sortBy('gift_date');

        $totalCLTValue = $cltGifts->sum('gift_value');
        $totalLiability = 0;
        $cumulativeTotal = 0;

        $cltDetails = [];

        foreach ($cltGifts as $gift) {
            // Add to cumulative total (14-year lookback)
            $cumulativeTotal += $gift->gift_value;

            // Calculate taxable amount (amount over NRB)
            // CLTs use lifetime rate (20%) when made, or 40% on death within 7 years
            $taxableAmount = max(0, $cumulativeTotal - $nrb);

            if ($taxableAmount > 0) {
                $yearsAgo = Carbon::now()->diffInYears($gift->gift_date);

                // Lifetime CLT rate is 20% (half the death rate)
                $lifetimeRate = 0.20;

                // If donor dies within 7 years, additional tax may be due
                // (difference between 40% death rate and 20% lifetime rate already paid)
                $giftTax = $gift->gift_value * $lifetimeRate;

                $totalLiability += $giftTax;

                $cltDetails[] = [
                    'gift_id' => $gift->id,
                    'gift_date' => $gift->gift_date->format('Y-m-d'),
                    'recipient' => $gift->recipient,
                    'gift_value' => $gift->gift_value,
                    'years_ago' => $yearsAgo,
                    'cumulative_total' => round($cumulativeTotal, 2),
                    'tax_liability' => round($giftTax, 2),
                    'lifetime_rate_applied' => $lifetimeRate,
                ];
            }
        }

        return [
            'total_clt_value' => round($totalCLTValue, 2),
            'total_clt_liability' => round($totalLiability, 2),
            'clt_count' => $cltGifts->count(),
            'clts' => $cltDetails,
            'lookback_period' => '14 years',
            'note' => 'CLTs are cumulated over a 14-year period. Lifetime rate is 20%. If donor dies within 7 years, additional tax may be due.',
        ];
    }

    /**
     * Calculate total gifting liability (PETs + CLTs)
     *
     * @param Collection $gifts All gifts
     * @return array Complete gifting liability breakdown
     */
    public function calculateGiftingLiability(Collection $gifts): array
    {
        $petLiability = $this->calculatePETLiability($gifts);
        $cltLiability = $this->calculateCLTLiability($gifts);

        return [
            'pet_liability' => $petLiability,
            'clt_liability' => $cltLiability,
            'total_gifting_liability' => round(
                $petLiability['total_pet_liability'] + $cltLiability['total_clt_liability'],
                2
            ),
        ];
    }
}
