<?php

declare(strict_types=1);

namespace App\Services\Estate;

use App\Models\Estate\Gift;
use App\Models\Estate\IHTProfile;
use App\Models\Estate\Trust;
use App\Models\Estate\Will;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SecondDeathIHTCalculator
{
    public function __construct(
        private IHTCalculator $ihtCalculator,
        private ActuarialLifeTableService $actuarialService,
        private FutureValueCalculator $fvCalculator,
        private SpouseNRBTrackerService $nrbTracker
    ) {}

    /**
     * Calculate IHT for second death scenario (when both spouses are alive)
     *
     * This calculates IHT as if the user dies first, then projects the surviving
     * spouse's estate to their expected death date and calculates the full IHT.
     *
     * @param User $user Current user (married)
     * @param User $spouse Spouse of current user
     * @param Collection $userAssets User's current assets
     * @param Collection $spouseAssets Spouse's current assets
     * @param IHTProfile $userProfile User's IHT profile
     * @param IHTProfile|null $spouseProfile Spouse's IHT profile (if data sharing enabled)
     * @param Collection|null $userGifts User's gifts
     * @param Collection|null $spouseGifts Spouse's gifts (if data sharing enabled)
     * @param Collection|null $userTrusts User's trusts
     * @param Collection|null $spouseTrusts Spouse's trusts (if data sharing enabled)
     * @param float $userLiabilities User's liabilities
     * @param float $spouseLiabilities Spouse's liabilities (if data sharing enabled)
     * @param Will|null $userWill User's will
     * @param Will|null $spouseWill Spouse's will (if data sharing enabled)
     * @param bool $dataSharingEnabled Whether spouse data sharing is enabled
     * @return array Second death IHT calculation
     */
    public function calculateSecondDeathIHT(
        User $user,
        User $spouse,
        Collection $userAssets,
        Collection $spouseAssets,
        IHTProfile $userProfile,
        ?IHTProfile $spouseProfile,
        ?Collection $userGifts = null,
        ?Collection $spouseGifts = null,
        ?Collection $userTrusts = null,
        ?Collection $spouseTrusts = null,
        float $userLiabilities = 0,
        float $spouseLiabilities = 0,
        ?Will $userWill = null,
        ?Will $spouseWill = null,
        bool $dataSharingEnabled = false
    ): array {
        // Validate both users have required data for actuarial calculation
        if (!$user->date_of_birth || !$user->gender) {
            return [
                'success' => false,
                'error' => 'User must have date_of_birth and gender to calculate life expectancy',
                'missing_data' => ['user' => ['date_of_birth', 'gender']],
            ];
        }

        if (!$spouse->date_of_birth || !$spouse->gender) {
            return [
                'success' => false,
                'error' => 'Spouse must have date_of_birth and gender to calculate life expectancy',
                'missing_data' => ['spouse' => ['date_of_birth', 'gender']],
            ];
        }

        // 1. Determine who dies first and who survives based on actuarial tables
        $userYearsRemaining = $this->actuarialService->getYearsUntilExpectedDeath(
            Carbon::parse($user->date_of_birth),
            $user->gender
        );

        $spouseYearsRemaining = $this->actuarialService->getYearsUntilExpectedDeath(
            Carbon::parse($spouse->date_of_birth),
            $spouse->gender
        );

        // Assume whoever has longer life expectancy survives
        $survivor = $userYearsRemaining > $spouseYearsRemaining ? $user : $spouse;
        $deceased = $survivor->id === $user->id ? $spouse : $user;

        $survivorYearsUntilDeath = max($userYearsRemaining, $spouseYearsRemaining);
        $deceasedYearsUntilDeath = min($userYearsRemaining, $spouseYearsRemaining);

        // 2. Calculate first death (deceased spouse) - all goes to survivor (spouse exemption)
        // No IHT on first death due to spouse exemption
        $firstDeathYear = Carbon::now()->addYears((int) $deceasedYearsUntilDeath);

        // 3. Project survivor's estate to second death
        // Combine both estates (survivor inherits from deceased)
        $survivorAssets = $survivor->id === $user->id ? $userAssets : $spouseAssets;
        $deceasedAssets = $deceased->id === $user->id ? $userAssets : $spouseAssets;

        // Project both estates to first death date
        $survivorAssetsAtFirstDeath = $this->fvCalculator->projectEstateAtDeath(
            $survivorAssets,
            $deceasedYearsUntilDeath,
            $this->fvCalculator->getDefaultGrowthRates()
        );

        $deceasedAssetsAtFirstDeath = $this->fvCalculator->projectEstateAtDeath(
            $deceasedAssets,
            $deceasedYearsUntilDeath,
            $this->fvCalculator->getDefaultGrowthRates()
        );

        // Combined estate at first death (survivor inherits all)
        $combinedValueAtFirstDeath = $survivorAssetsAtFirstDeath['projected_estate_value_at_death'] +
                                      $deceasedAssetsAtFirstDeath['projected_estate_value_at_death'];

        // Project combined estate to second death
        $yearsFromFirstToSecondDeath = $survivorYearsUntilDeath - $deceasedYearsUntilDeath;

        // Create a single combined asset for projection
        $combinedAssetCollection = collect([(object) [
            'asset_type' => 'combined_estate',
            'asset_name' => 'Combined Estate Value',
            'current_value' => $combinedValueAtFirstDeath,
            'is_iht_exempt' => false,
        ]]);

        $projectedCombinedEstate = $this->fvCalculator->projectEstateAtDeath(
            $combinedAssetCollection,
            $yearsFromFirstToSecondDeath,
            $this->fvCalculator->getDefaultGrowthRates()
        );

        // Convert projected estate back to collection for IHT calculation
        $projectedAssets = collect([(object) [
            'asset_type' => 'combined_estate',
            'asset_name' => 'Combined Estate at Second Death',
            'current_value' => $projectedCombinedEstate['projected_estate_value_at_death'],
            'is_iht_exempt' => false,
        ]]);

        // 4. Get transferred NRB from deceased spouse
        $nrbTransferDetails = $this->nrbTracker->calculateSurvivorTotalNRB($survivor, $deceased);

        // 5. Update survivor's IHT profile with transferred NRB
        $survivorProfile = $survivor->id === $user->id ? $userProfile : ($spouseProfile ?? new IHTProfile([
            'user_id' => $survivor->id,
            'marital_status' => 'widowed',
            'own_home' => false,
            'home_value' => 0,
            'nrb_transferred_from_spouse' => 0,
            'charitable_giving_percent' => 0,
        ]));

        $adjustedProfile = clone $survivorProfile;
        $adjustedProfile->nrb_transferred_from_spouse = $nrbTransferDetails['transferred_nrb_from_deceased'];

        // 6. Get survivor's gifts (both user and spouse gifts need to be considered)
        $survivorGifts = $survivor->id === $user->id ? $userGifts : $spouseGifts;
        $deceasedGifts = $deceased->id === $user->id ? $userGifts : $spouseGifts;

        // 7. Get survivor's trusts
        $survivorTrusts = $survivor->id === $user->id ? $userTrusts : $spouseTrusts;

        // 8. Project liabilities (conservative: assume they remain)
        $survivorLiabilitiesProjected = $survivor->id === $user->id ? $userLiabilities : $spouseLiabilities;

        // 9. Get survivor's will
        $survivorWill = $survivor->id === $user->id ? $userWill : $spouseWill;

        // 10a. Calculate IHT on CURRENT combined estate (for comparison)
        $currentCombinedEstate = $userAssets->sum('current_value') + $spouseAssets->sum('current_value');
        $currentCombinedLiabilities = $userLiabilities + $spouseLiabilities;

        $currentCombinedAssets = collect([(object) [
            'asset_type' => 'combined_estate',
            'asset_name' => 'Current Combined Estate',
            'current_value' => $currentCombinedEstate,
            'is_iht_exempt' => false,
        ]]);

        $currentIHTCalculation = $this->ihtCalculator->calculateIHTLiability(
            $currentCombinedAssets,
            $adjustedProfile,
            $survivorGifts,
            $survivorTrusts,
            $currentCombinedLiabilities,
            $survivorWill,
            $survivor
        );

        // 10b. Calculate IHT on projected estate at second death
        $secondDeathIHT = $this->ihtCalculator->calculateIHTLiability(
            $projectedAssets,
            $adjustedProfile,
            $survivorGifts,
            $survivorTrusts,
            $survivorLiabilitiesProjected,
            $survivorWill,
            $survivor
        );

        // 11. Return comprehensive second death analysis
        $secondDeathDate = Carbon::now()->addYears((int) $survivorYearsUntilDeath);

        return [
            'success' => true,
            'scenario' => 'second_death',
            'data_sharing_enabled' => $dataSharingEnabled,
            'current_date' => now()->format('Y-m-d'),

            // First death details
            'first_death' => [
                'name' => $deceased->name,
                'estimated_death_date' => $firstDeathYear->format('Y-m-d'),
                'years_until_death' => (int) $deceasedYearsUntilDeath,
                'current_age' => Carbon::parse($deceased->date_of_birth)->age,
                'estimated_age_at_death' => Carbon::parse($deceased->date_of_birth)->age + (int) $deceasedYearsUntilDeath,
                'current_estate_value' => $deceased->id === $user->id ?
                    $userAssets->sum('current_value') :
                    $spouseAssets->sum('current_value'),
                'projected_estate_value' => $deceasedAssetsAtFirstDeath['projected_estate_value_at_death'],
                'iht_liability' => 0, // Spouse exemption applies
                'note' => 'All assets pass to surviving spouse - no IHT due to spouse exemption',
            ],

            // Second death details (survivor)
            'second_death' => [
                'name' => $survivor->name,
                'estimated_death_date' => $secondDeathDate->format('Y-m-d'),
                'years_until_death' => (int) $survivorYearsUntilDeath,
                'current_age' => Carbon::parse($survivor->date_of_birth)->age,
                'estimated_age_at_death' => Carbon::parse($survivor->date_of_birth)->age + (int) $survivorYearsUntilDeath,
                'current_estate_value' => $survivor->id === $user->id ?
                    $userAssets->sum('current_value') :
                    $spouseAssets->sum('current_value'),
                'inherited_from_first_death' => $deceasedAssetsAtFirstDeath['projected_estate_value_at_death'],
                'combined_estate_at_first_death' => $combinedValueAtFirstDeath,
                'projected_combined_estate_at_second_death' => $projectedCombinedEstate['projected_estate_value_at_death'],
                'years_between_deaths' => max(0, $yearsFromFirstToSecondDeath),
            ],

            // NRB transfer details
            'nrb_transfer' => $nrbTransferDetails,

            // IHT calculation at second death (PROJECTED)
            'iht_calculation' => $secondDeathIHT,

            // IHT calculation if death occurred NOW (for comparison)
            'current_iht_calculation' => $currentIHTCalculation,

            // Summary
            'total_iht_payable' => [
                'first_death' => 0,
                'second_death' => $secondDeathIHT['iht_liability'],
                'total' => $secondDeathIHT['iht_liability'],
            ],

            // Inflation and growth assumptions
            'assumptions' => [
                'inflation_rate' => 0.025, // 2.5%
                'growth_rates' => $this->fvCalculator->getDefaultGrowthRates(),
                'actuarial_tables' => 'UK Office for National Statistics 2020-2022',
            ],
        ];
    }
}
