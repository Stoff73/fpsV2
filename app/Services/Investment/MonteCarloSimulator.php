<?php

declare(strict_types=1);

namespace App\Services\Investment;

class MonteCarloSimulator
{
    /**
     * Run Monte Carlo simulation
     *
     * @param  float  $startValue  Initial portfolio value
     * @param  float  $monthlyContribution  Monthly contribution amount
     * @param  float  $expectedReturn  Expected annual return (e.g., 0.07 for 7%)
     * @param  float  $volatility  Annual volatility/std deviation (e.g., 0.15 for 15%)
     * @param  int  $years  Number of years to simulate
     * @param  int  $iterations  Number of simulation runs (default 1000)
     * @return array Simulation results with percentiles
     */
    public function simulate(
        float $startValue,
        float $monthlyContribution,
        float $expectedReturn,
        float $volatility,
        int $years,
        int $iterations = 1000
    ): array {
        $results = [];
        $monthlyReturn = $expectedReturn / 12;
        $monthlyVolatility = $volatility / sqrt(12);
        $totalMonths = $years * 12;

        // Run simulations
        for ($i = 0; $i < $iterations; $i++) {
            $portfolioValue = $startValue;
            $yearlyValues = [];

            for ($month = 1; $month <= $totalMonths; $month++) {
                // Generate random return using normal distribution
                $randomReturn = $this->generateNormalDistribution($monthlyReturn, $monthlyVolatility);

                // Apply return and add contribution
                $portfolioValue = $portfolioValue * (1 + $randomReturn) + $monthlyContribution;

                // Store value at end of each year
                if ($month % 12 == 0) {
                    $yearlyValues[] = $portfolioValue;
                }
            }

            $results[] = [
                'final_value' => $portfolioValue,
                'yearly_values' => $yearlyValues,
            ];
        }

        // Calculate statistics
        $finalValues = array_column($results, 'final_value');
        sort($finalValues);

        $percentiles = $this->calculatePercentiles($finalValues);

        // Calculate year-by-year percentiles
        $yearByYearPercentiles = [];
        for ($year = 1; $year <= $years; $year++) {
            $yearIndex = $year - 1;
            $yearValues = array_map(fn ($r) => $r['yearly_values'][$yearIndex], $results);
            sort($yearValues);

            $yearByYearPercentiles[] = [
                'year' => $year,
                'percentiles' => $this->calculatePercentiles($yearValues),
            ];
        }

        $medianValue = $percentiles[2]['value'] ?? 0; // 50th percentile is index 2
        $totalContributions = $startValue + ($monthlyContribution * $totalMonths);

        return [
            'summary' => [
                'start_value' => round($startValue, 2),
                'monthly_contribution' => round($monthlyContribution, 2),
                'expected_return' => $expectedReturn,
                'volatility' => $volatility,
                'years' => $years,
                'iterations' => $iterations,
            ],
            'year_by_year' => $yearByYearPercentiles,
            'iterations' => $iterations,
            'final_percentiles' => $percentiles,
            'total_contributions' => round($totalContributions, 2),
            'median_gain' => round($medianValue - $totalContributions, 2),
        ];
    }

    /**
     * Generate random number from normal distribution using Box-Muller transform
     *
     * @param  float  $mean  Mean of the distribution
     * @param  float  $stdDev  Standard deviation
     * @return float Random value from normal distribution
     */
    public function generateNormalDistribution(float $mean, float $stdDev): float
    {
        // Box-Muller transform
        // Generate two independent uniform random numbers between 0 and 1
        $u1 = mt_rand() / mt_getrandmax();
        $u2 = mt_rand() / mt_getrandmax();

        // Ensure u1 is not zero to avoid log(0)
        $u1 = max($u1, 1e-10);

        // Apply Box-Muller transform
        $z0 = sqrt(-2.0 * log($u1)) * cos(2.0 * M_PI * $u2);

        // Transform to desired mean and standard deviation
        return $mean + ($z0 * $stdDev);
    }

    /**
     * Calculate percentiles from sorted array of values
     *
     * @param  array  $sortedValues  Array of values (must be sorted)
     * @return array Percentiles (10th, 25th, 50th, 75th, 90th)
     */
    public function calculatePercentiles(array $sortedValues): array
    {
        $count = count($sortedValues);

        if ($count == 0) {
            return [
                ['percentile' => '10th', 'value' => 0.0, 'final_value' => 0.0],
                ['percentile' => '25th', 'value' => 0.0, 'final_value' => 0.0],
                ['percentile' => '50th', 'value' => 0.0, 'final_value' => 0.0],
                ['percentile' => '75th', 'value' => 0.0, 'final_value' => 0.0],
                ['percentile' => '90th', 'value' => 0.0, 'final_value' => 0.0],
            ];
        }

        $percentiles = [];
        foreach ([10, 25, 50, 75, 90] as $p) {
            $index = (int) ceil(($p / 100) * $count) - 1;
            $index = max(0, min($index, $count - 1));
            $value = round($sortedValues[$index], 2);
            $percentiles[] = [
                'percentile' => "{$p}th",
                'value' => $value,
                'final_value' => $value, // For compatibility
            ];
        }

        return $percentiles;
    }

    /**
     * Calculate probability of reaching a goal
     *
     * @param  array  $finalValues  Array of final portfolio values from simulation
     * @param  float  $goalAmount  Target amount
     * @return float Probability as percentage (0-100)
     */
    public function calculateGoalProbability(array $finalValues, float $goalAmount): float
    {
        if (empty($finalValues)) {
            return 0.0;
        }

        $successCount = count(array_filter($finalValues, fn ($v) => $v >= $goalAmount));

        return round(($successCount / count($finalValues)) * 100, 2);
    }
}
