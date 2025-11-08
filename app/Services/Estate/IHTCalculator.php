<?php

declare(strict_types=1);

namespace App\Services\Estate;

use App\Models\Estate\Gift;
use App\Models\Estate\IHTProfile;
use App\Models\Estate\Trust;
use App\Models\Estate\Will;
use App\Models\User;
use App\Services\TaxConfigService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class IHTCalculator
{
    /**
     * Tax configuration service
     */
    private TaxConfigService $taxConfig;

    /**
     * Constructor
     */
    public function __construct(TaxConfigService $taxConfig)
    {
        $this->taxConfig = $taxConfig;
    }
    /**
     * Calculate IHT liability on an estate
     *
     * @param  Collection  $assets  Estate assets
     * @param  IHTProfile  $profile  IHT profile
     * @param  Collection|null  $gifts  Gifts (optional, for comprehensive calculation)
     * @param  Collection|null  $trusts  Trusts (optional, for trust IHT value calculation)
     * @param  float  $liabilities  Total liabilities (mortgages, loans, etc.)
     * @param  Will|null  $will  Will (optional, for spouse exemption and bequest calculations)
     * @param  User|null  $user  User (optional, for checking marital status and spouse)
     */
    public function calculateIHTLiability(Collection $assets, IHTProfile $profile, ?Collection $gifts = null, ?Collection $trusts = null, float $liabilities = 0, ?Will $will = null, ?User $user = null): array
    {
        // Calculate gross estate value from assets (before deducting liabilities)
        $grossEstateValue = $assets->sum('current_value');

        // Add trust values that count in estate (e.g., discounted gift trust retained value, loan trust loan balance)
        $trustIHTValue = 0;
        $trustDetails = [];
        if ($trusts && $trusts->isNotEmpty()) {
            foreach ($trusts as $trust) {
                $trustIHTValue += $trust->getIHTValue();
                $trustDetails[] = [
                    'trust_id' => $trust->id,
                    'trust_name' => $trust->trust_name,
                    'trust_type' => $trust->trust_type,
                    'current_value' => $trust->current_value,
                    'iht_value' => $trust->getIHTValue(),
                ];
            }
        }

        // Total estate value including trust IHT values
        $grossEstateValue += $trustIHTValue;

        // Deduct liabilities (mortgages, loans, etc.) to get net estate value
        $netEstateValue = max(0, $grossEstateValue - $liabilities);

        // Check for spouse exemption
        $spouseExemption = 0;
        $spouseExemptionApplies = false;
        $deathScenario = 'user_only';

        if ($will && $user) {
            $deathScenario = $will->death_scenario;

            // Check if user is married and has a spouse
            $isMarried = in_array($user->marital_status, ['married']);
            $hasSpouse = $user->spouse_id !== null;

            // Spouse exemption only applies if:
            // 1. User is married and has a linked spouse
            // 2. Death scenario is 'user_only' (not both dying)
            // 3. Spouse is primary beneficiary
            if ($isMarried && $hasSpouse && $will->death_scenario === 'user_only' && $will->spouse_primary_beneficiary) {
                $spouseExemptionPercentage = $will->spouse_bequest_percentage / 100;
                $spouseExemption = $netEstateValue * $spouseExemptionPercentage;
                $spouseExemptionApplies = true;
            }
        }

        // Calculate taxable estate after spouse exemption
        $taxableNetEstate = max(0, $netEstateValue - $spouseExemption);

        // Get tax config
        $config = $this->taxConfig->getInheritanceTax();

        // Apply NRB (Nil Rate Band)
        $nrb = $config['nil_rate_band'];

        // Add spouse NRB transfer if applicable
        $totalNRB = $nrb + $profile->nrb_transferred_from_spouse;

        // Check RNRB eligibility (only applies to estate, not gifts)
        // Use taxableNetEstate for RNRB taper calculation (after spouse exemption)
        $rnrb = $this->checkRNRBEligibility($profile, $assets)
            ? $this->calculateRNRB($taxableNetEstate, $config, $user)
            : 0;

        // Calculate gift liabilities if gifts provided
        // IMPORTANT: Gifts use the NRB FIRST (chronologically oldest first)
        $giftLiability = 0;
        $giftingDetails = null;
        $nrbUsedByGifts = 0;

        if ($gifts && $gifts->isNotEmpty()) {
            $giftingDetails = $this->calculateGiftingLiabilityWithNRB($gifts, $totalNRB);
            $giftLiability = $giftingDetails['total_gifting_liability'];
            $nrbUsedByGifts = $giftingDetails['nrb_used_by_gifts'];
        }

        // Remaining NRB for estate (after gifts have used their share)
        $remainingNRB = max(0, $totalNRB - $nrbUsedByGifts);

        // Calculate total tax-free allowance for estate (remaining NRB + RNRB)
        $totalAllowance = $remainingNRB + $rnrb;

        // Calculate taxable estate (taxable net estate minus allowances)
        $taxableEstate = max(0, $taxableNetEstate - $totalAllowance);

        // Determine IHT rate (standard 40% or reduced 36% for charity)
        $ihtRate = $this->calculateCharitableReduction($grossEstateValue, $profile->charitable_giving_percent);

        // Calculate IHT liability on estate
        $estateLiability = $taxableEstate * $ihtRate;

        // Total IHT liability (estate + gifts)
        $totalIHTLiability = $estateLiability + $giftLiability;

        // Calculate individual RNRB components for breakdown display
        $individualRNRB = $config['residence_nil_rate_band'];
        $spouseRNRB = 0;
        if ($user && in_array($user->marital_status, ['married'])) {
            $spouseRNRB = $individualRNRB; // Spouse's RNRB
        }

        return [
            'gross_estate_value' => round($grossEstateValue, 2),
            'liabilities' => round($liabilities, 2),
            'net_estate_value' => round($netEstateValue, 2),
            'spouse_exemption' => round($spouseExemption, 2),
            'spouse_exemption_applies' => $spouseExemptionApplies,
            'death_scenario' => $deathScenario,
            'taxable_net_estate' => round($taxableNetEstate, 2),
            'trust_iht_value' => round($trustIHTValue, 2),
            'trust_details' => $trustDetails,
            'nrb' => round($nrb, 2),
            'nrb_from_spouse' => round($profile->nrb_transferred_from_spouse, 2),
            'total_nrb' => round($totalNRB, 2),
            'nrb_used_by_gifts' => round($nrbUsedByGifts, 2),
            'nrb_available_for_estate' => round($remainingNRB, 2),
            'rnrb' => round($rnrb, 2),
            'rnrb_individual' => round($individualRNRB, 2),
            'rnrb_from_spouse' => round($spouseRNRB, 2),
            'rnrb_eligible' => $rnrb > 0,
            'total_allowance' => round($totalAllowance, 2),
            'taxable_estate' => round($taxableEstate, 2),
            'iht_rate' => $ihtRate,
            'estate_iht_liability' => round($estateLiability, 2),
            'gift_iht_liability' => round($giftLiability, 2),
            'iht_liability' => round($totalIHTLiability, 2),
            'effective_rate' => $netEstateValue > 0
                ? round(($totalIHTLiability / $netEstateValue) * 100, 2)
                : 0,
            'gifting_details' => $giftingDetails,
        ];
    }

    /**
     * Check if estate qualifies for Residence Nil Rate Band (RNRB)
     */
    public function checkRNRBEligibility(IHTProfile $profile, Collection $assets): bool
    {
        // Check if there's a property asset in the assets list
        $hasPropertyAsset = $assets->contains(function ($asset) {
            return isset($asset->asset_type) &&
                   in_array(strtolower($asset->asset_type), ['property', 'residential_property', 'main_residence', 'home']);
        });

        // If there's a property asset, RNRB is available
        if ($hasPropertyAsset) {
            return true;
        }

        // Fallback: Check IHT profile settings
        if ($profile->own_home && $profile->home_value > 0) {
            return true;
        }

        // Must have direct descendants (simplified check - in real system would check will)
        // For now, we assume if they own a home, they qualify
        // In production, you'd check beneficiary designation for direct descendants

        return false;
    }

    /**
     * Calculate RNRB amount including taper and spouse transfer
     *
     * @param  float  $estateValue  Estate value for taper calculation
     * @param  array  $config  UK tax config
     * @param  User|null  $user  User (optional, for checking marital status)
     * @return float RNRB amount
     */
    private function calculateRNRB(float $estateValue, array $config, ?User $user = null): float
    {
        $rnrb = $config['residence_nil_rate_band'];

        // For married users, include full spouse RNRB by default (£175,000 + £175,000 = £350,000)
        // This will be verified once spouse accounts are linked
        if ($user && in_array($user->marital_status, ['married'])) {
            $rnrb = $rnrb * 2; // Double RNRB for married users
        }

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
        $config = $this->taxConfig->getInheritanceTax();

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
        $ihtConfig = $this->taxConfig->getInheritanceTax();
        $config = $ihtConfig['potentially_exempt_transfers'];
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
        $config = $this->taxConfig->getInheritanceTax();
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
     * @param  Collection  $gifts  All gifts (includes CLTs to trusts, etc.)
     * @return array CLT calculation details
     */
    public function calculateCLTLiability(Collection $gifts): array
    {
        $config = $this->taxConfig->getInheritanceTax();
        $nrb = $config['nil_rate_band'];
        $ihtRate = $config['chargeable_lifetime_transfers']['rate'];

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
     * @param  Collection  $gifts  All gifts
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

    /**
     * Calculate gifting liability WITH proper NRB allocation
     * Gifts use NRB first (chronologically), then estate gets remainder
     *
     * @param  Collection  $gifts  All gifts
     * @param  float  $totalNRB  Total NRB available (including spouse transfer)
     * @return array Complete gifting liability breakdown with NRB tracking
     */
    public function calculateGiftingLiabilityWithNRB(Collection $gifts, float $totalNRB): array
    {
        $config = $this->taxConfig->getInheritanceTax();

        // Get all gifts within 7 years, sorted by date (oldest first)
        $recentGifts = $gifts->filter(function ($gift) {
            $yearsAgo = Carbon::now()->diffInYears($gift->gift_date);

            return $yearsAgo < 7 && $gift->gift_type === 'pet';
        })->sortBy('gift_date');

        $totalGiftValue = $recentGifts->sum('gift_value');
        $cumulativeGiftValue = 0;
        $totalLiability = 0;
        $nrbRemaining = $totalNRB;
        $giftDetails = [];

        foreach ($recentGifts as $gift) {
            $cumulativeGiftValue += $gift->gift_value;

            // How much of this gift is covered by NRB?
            $previouslyUsed = max(0, $cumulativeGiftValue - $gift->gift_value);
            $nrbBeforeThisGift = max(0, $totalNRB - $previouslyUsed);
            $nrbCoveringThisGift = min($gift->gift_value, $nrbBeforeThisGift);
            $taxablePortionOfGift = max(0, $gift->gift_value - $nrbCoveringThisGift);

            // Apply taper relief only to the taxable portion
            $yearsAgo = Carbon::now()->diffInYears($gift->gift_date);
            $taperRate = $this->getTaperRate($yearsAgo);

            $giftTax = $taxablePortionOfGift * $taperRate;
            $totalLiability += $giftTax;

            $giftDetails[] = [
                'gift_id' => $gift->id,
                'gift_date' => $gift->gift_date->format('Y-m-d'),
                'recipient' => $gift->recipient,
                'gift_value' => round($gift->gift_value, 2),
                'years_ago' => $yearsAgo,
                'nrb_covered' => round($nrbCoveringThisGift, 2),
                'taxable_amount' => round($taxablePortionOfGift, 2),
                'taper_rate' => $taperRate,
                'tax_liability' => round($giftTax, 2),
            ];
        }

        // How much NRB did gifts use?
        $nrbUsedByGifts = min($totalNRB, $cumulativeGiftValue);

        return [
            'pet_liability' => [
                'total_gift_value' => round($totalGiftValue, 2),
                'total_pet_liability' => round($totalLiability, 2),
                'gift_count' => $recentGifts->count(),
                'gifts' => $giftDetails,
            ],
            'clt_liability' => [
                'total_clt_value' => 0,
                'total_clt_liability' => 0,
                'clt_count' => 0,
                'clts' => [],
            ],
            'total_gifting_liability' => round($totalLiability, 2),
            'nrb_used_by_gifts' => round($nrbUsedByGifts, 2),
        ];
    }

    /**
     * Get taper relief rate based on years since gift
     */
    private function getTaperRate(int $yearsAgo): float
    {
        if ($yearsAgo < 3) {
            return 0.40;
        }
        if ($yearsAgo < 4) {
            return 0.32;
        }
        if ($yearsAgo < 5) {
            return 0.24;
        }
        if ($yearsAgo < 6) {
            return 0.16;
        }
        if ($yearsAgo < 7) {
            return 0.08;
        }

        return 0;
    }

    /**
     * Calculate IHT liability for surviving spouse scenario
     *
     * This projects the estate value to the expected death date and calculates
     * IHT with transferred NRB from deceased spouse.
     *
     * @param  User  $survivor  The surviving spouse (user)
     * @param  User  $deceased  The deceased spouse
     * @param  Collection  $assets  Survivor's current assets
     * @param  IHTProfile  $survivorProfile  Survivor's IHT profile
     * @param  Collection|null  $gifts  Survivor's gifts
     * @param  Collection|null  $trusts  Survivor's trusts
     * @param  float  $liabilities  Survivor's liabilities
     * @param  Will|null  $will  Survivor's will
     * @param  ActuarialLifeTableService  $actuarialService  Actuarial service
     * @param  SpouseNRBTrackerService  $nrbTracker  NRB tracker service
     * @param  FutureValueCalculator  $fvCalculator  Future value calculator
     * @param  array|null  $customGrowthRates  Optional custom growth rates by asset type
     * @return array IHT calculation for surviving spouse
     */
    public function calculateSurvivingSpouseIHT(
        User $survivor,
        User $deceased,
        Collection $assets,
        IHTProfile $survivorProfile,
        ?Collection $gifts,
        ?Collection $trusts,
        float $liabilities,
        ?Will $will,
        ActuarialLifeTableService $actuarialService,
        SpouseNRBTrackerService $nrbTracker,
        FutureValueCalculator $fvCalculator,
        ?array $customGrowthRates = null
    ): array {
        // 1. Get actuarial life expectancy for survivor
        if (! $survivor->date_of_birth || ! $survivor->gender) {
            return [
                'success' => false,
                'error' => 'Survivor must have date_of_birth and gender to calculate life expectancy',
            ];
        }

        $yearsUntilDeath = $actuarialService->getYearsUntilExpectedDeath(
            \Carbon\Carbon::parse($survivor->date_of_birth),
            $survivor->gender
        );

        $estimatedDeathDate = $actuarialService->getEstimatedDateOfDeath(
            \Carbon\Carbon::parse($survivor->date_of_birth),
            $survivor->gender
        );

        // 2. Project assets to expected death date
        $growthRates = $customGrowthRates ?? $fvCalculator->getDefaultGrowthRates();
        $futureEstateProjection = $fvCalculator->projectEstateAtDeath($assets, $yearsUntilDeath, $growthRates);

        // Convert projected assets back to collection format for IHT calculation
        $projectedAssets = collect($futureEstateProjection['asset_projections'])->map(function ($projection) {
            return (object) [
                'asset_type' => $projection['asset_type'],
                'asset_name' => $projection['asset_name'],
                'current_value' => $projection['future_value'], // Use projected future value
                'is_iht_exempt' => false,
            ];
        });

        // 3. Get transferred NRB from deceased spouse
        $nrbTransferDetails = $nrbTracker->calculateSurvivorTotalNRB($survivor, $deceased);

        // 4. Update survivor's IHT profile with transferred NRB
        $adjustedProfile = clone $survivorProfile;
        $adjustedProfile->nrb_transferred_from_spouse = $nrbTransferDetails['transferred_nrb_from_deceased'];

        // 5. Project liabilities (assuming they'll be paid down over time, conservative approach: no reduction)
        $projectedLiabilities = $liabilities;

        // 6. Calculate IHT on projected estate
        $ihtCalculation = $this->calculateIHTLiability(
            $projectedAssets,
            $adjustedProfile,
            $gifts,
            $trusts,
            $projectedLiabilities,
            $will,
            $survivor
        );

        // 7. Return comprehensive surviving spouse IHT analysis
        return [
            'success' => true,
            'scenario' => 'surviving_spouse',
            'survivor_name' => $survivor->name,
            'deceased_spouse_name' => $deceased->name,
            'current_date' => now()->format('Y-m-d'),
            'estimated_death_date' => $estimatedDeathDate->format('Y-m-d'),
            'years_until_death' => $yearsUntilDeath,
            'survivor_current_age' => \Carbon\Carbon::parse($survivor->date_of_birth)->age,
            'survivor_estimated_age_at_death' => \Carbon\Carbon::parse($survivor->date_of_birth)->age + $yearsUntilDeath,
            'current_estate_value' => $futureEstateProjection['current_estate_value'],
            'projected_estate_value' => $futureEstateProjection['projected_estate_value_at_death'],
            'projected_growth' => $futureEstateProjection['projected_growth'],
            'growth_rates_used' => $growthRates,
            'nrb_transfer_details' => $nrbTransferDetails,
            'total_nrb_available' => $ihtCalculation['total_nrb'],
            'iht_calculation' => $ihtCalculation,
            'asset_projections' => $futureEstateProjection['asset_projections'],
        ];
    }
}
