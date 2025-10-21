<?php

declare(strict_types=1);

namespace App\Services\Estate;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ActuarialLifeTableService
{
    /**
     * Get life expectancy for a given age and gender
     *
     * @param  int  $currentAge  Current age of the individual
     * @param  string  $gender  Gender (male/female)
     * @param  string  $tableVersion  Life table version (default: ONS_2020_2022)
     * @return float Life expectancy in years
     */
    public function getLifeExpectancy(int $currentAge, string $gender, string $tableVersion = 'ONS_2020_2022'): float
    {
        // Get exact age match first
        $exactMatch = DB::table('uk_life_expectancy_tables')
            ->where('age', $currentAge)
            ->where('gender', $gender)
            ->where('table_version', $tableVersion)
            ->value('life_expectancy_years');

        if ($exactMatch !== null) {
            return (float) $exactMatch;
        }

        // If no exact match, interpolate between nearest ages
        return $this->interpolateLifeExpectancy($currentAge, $gender, $tableVersion);
    }

    /**
     * Calculate estimated age at death based on current age and gender
     *
     * @param  int  $currentAge  Current age
     * @param  string  $gender  Gender (male/female)
     * @param  string  $tableVersion  Life table version
     * @return int Estimated age at death (rounded)
     */
    public function getEstimatedAgeAtDeath(int $currentAge, string $gender, string $tableVersion = 'ONS_2020_2022'): int
    {
        $lifeExpectancy = $this->getLifeExpectancy($currentAge, $gender, $tableVersion);

        return (int) round($currentAge + $lifeExpectancy);
    }

    /**
     * Calculate years until expected death
     *
     * @param  Carbon  $dateOfBirth  Date of birth
     * @param  string  $gender  Gender (male/female)
     * @return int Years until expected death
     */
    public function getYearsUntilExpectedDeath(Carbon $dateOfBirth, string $gender): int
    {
        $currentAge = $dateOfBirth->age;
        $lifeExpectancy = $this->getLifeExpectancy($currentAge, $gender);

        return (int) ceil($lifeExpectancy);
    }

    /**
     * Calculate estimated date of death
     *
     * @param  Carbon  $dateOfBirth  Date of birth
     * @param  string  $gender  Gender (male/female)
     * @return Carbon Estimated date of death
     */
    public function getEstimatedDateOfDeath(Carbon $dateOfBirth, string $gender): Carbon
    {
        $yearsUntilDeath = $this->getYearsUntilExpectedDeath($dateOfBirth, $gender);

        return Carbon::now()->addYears($yearsUntilDeath);
    }

    /**
     * Get life expectancy for spouse scenario (surviving spouse calculation)
     *
     * This assumes the first spouse has died and calculates life expectancy
     * for the surviving spouse, who will inherit the deceased spouse's NRB.
     *
     * @param  Carbon  $userDateOfBirth  User's date of birth
     * @param  string  $userGender  User's gender
     * @param  Carbon  $spouseDateOfBirth  Spouse's date of birth
     * @param  string  $spouseGender  Spouse's gender
     * @return array Survival analysis with expected death dates
     */
    public function getSurvivingSpouseAnalysis(
        Carbon $userDateOfBirth,
        string $userGender,
        Carbon $spouseDateOfBirth,
        string $spouseGender
    ): array {
        $userAge = $userDateOfBirth->age;
        $spouseAge = $spouseDateOfBirth->age;

        $userLifeExpectancy = $this->getLifeExpectancy($userAge, $userGender);
        $spouseLifeExpectancy = $this->getLifeExpectancy($spouseAge, $spouseGender);

        $userEstimatedAgeAtDeath = $this->getEstimatedAgeAtDeath($userAge, $userGender);
        $spouseEstimatedAgeAtDeath = $this->getEstimatedAgeAtDeath($spouseAge, $spouseGender);

        $userEstimatedDateOfDeath = $this->getEstimatedDateOfDeath($userDateOfBirth, $userGender);
        $spouseEstimatedDateOfDeath = $this->getEstimatedDateOfDeath($spouseDateOfBirth, $spouseGender);

        // Determine who is likely to die first (for surviving spouse scenario)
        $userDiesFirst = $userEstimatedDateOfDeath->lessThan($spouseEstimatedDateOfDeath);

        $firstToDecease = $userDiesFirst ? 'user' : 'spouse';
        $survivingPerson = $userDiesFirst ? 'spouse' : 'user';

        $firstDeathDate = $userDiesFirst ? $userEstimatedDateOfDeath : $spouseEstimatedDateOfDeath;
        $secondDeathDate = $userDiesFirst ? $spouseEstimatedDateOfDeath : $userEstimatedDateOfDeath;

        $yearsAsWidowWidower = $secondDeathDate->diffInYears($firstDeathDate);

        return [
            'user_current_age' => $userAge,
            'user_life_expectancy_years' => round($userLifeExpectancy, 2),
            'user_estimated_age_at_death' => $userEstimatedAgeAtDeath,
            'user_estimated_date_of_death' => $userEstimatedDateOfDeath->format('Y-m-d'),
            'spouse_current_age' => $spouseAge,
            'spouse_life_expectancy_years' => round($spouseLifeExpectancy, 2),
            'spouse_estimated_age_at_death' => $spouseEstimatedAgeAtDeath,
            'spouse_estimated_date_of_death' => $spouseEstimatedDateOfDeath->format('Y-m-d'),
            'first_to_decease' => $firstToDecease,
            'surviving_person' => $survivingPerson,
            'first_death_date' => $firstDeathDate->format('Y-m-d'),
            'second_death_date' => $secondDeathDate->format('Y-m-d'),
            'years_as_widow_widower' => $yearsAsWidowWidower,
            'years_until_user_death' => Carbon::now()->diffInYears($userEstimatedDateOfDeath),
            'years_until_spouse_death' => Carbon::now()->diffInYears($spouseEstimatedDateOfDeath),
        ];
    }

    /**
     * Interpolate life expectancy between two ages
     *
     * @param  int  $age  Age to interpolate
     * @param  string  $gender  Gender
     * @param  string  $tableVersion  Table version
     * @return float Interpolated life expectancy
     */
    private function interpolateLifeExpectancy(int $age, string $gender, string $tableVersion): float
    {
        // Get nearest lower and upper ages
        $lowerAge = DB::table('uk_life_expectancy_tables')
            ->where('age', '<=', $age)
            ->where('gender', $gender)
            ->where('table_version', $tableVersion)
            ->orderBy('age', 'desc')
            ->first();

        $upperAge = DB::table('uk_life_expectancy_tables')
            ->where('age', '>=', $age)
            ->where('gender', $gender)
            ->where('table_version', $tableVersion)
            ->orderBy('age', 'asc')
            ->first();

        // If only one bound exists, use it
        if (! $lowerAge && $upperAge) {
            return (float) $upperAge->life_expectancy_years;
        }
        if ($lowerAge && ! $upperAge) {
            return (float) $lowerAge->life_expectancy_years;
        }

        // If no data at all, return default conservative estimate
        if (! $lowerAge && ! $upperAge) {
            return max(1.0, 90.0 - $age); // Conservative estimate
        }

        // Linear interpolation
        $ageDiff = $upperAge->age - $lowerAge->age;
        if ($ageDiff == 0) {
            return (float) $lowerAge->life_expectancy_years;
        }

        $leDiff = $upperAge->life_expectancy_years - $lowerAge->life_expectancy_years;
        $fraction = ($age - $lowerAge->age) / $ageDiff;

        return (float) ($lowerAge->life_expectancy_years + ($leDiff * $fraction));
    }
}
