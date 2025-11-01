<?php

declare(strict_types=1);

namespace App\Services\Investment\EfficientFrontier;

/**
 * Efficient Frontier Calculator
 * Implements Modern Portfolio Theory (MPT) calculations
 *
 * Generates efficient frontier curve showing optimal risk/return trade-offs
 * for different portfolio allocations.
 *
 * Key Concepts:
 * - Efficient Frontier: Set of optimal portfolios offering highest return for given risk
 * - Sharpe Ratio: Risk-adjusted return measure (Return - Risk-free rate) / Volatility
 * - Maximum Sharpe Portfolio: Optimal risk/return trade-off
 * - Minimum Variance Portfolio: Lowest risk portfolio
 */
class EfficientFrontierCalculator
{
    /**
     * Calculate efficient frontier for given asset classes
     *
     * @param  array  $assetClasses  Asset class data with returns, volatility, correlations
     * @param  int  $numPortfolios  Number of portfolios to generate (default 100)
     * @param  float  $riskFreeRate  Risk-free rate (default 0.04 = 4%)
     * @return array Efficient frontier data
     */
    public function calculateEfficientFrontier(
        array $assetClasses,
        int $numPortfolios = 100,
        float $riskFreeRate = 0.04
    ): array {
        // Validate inputs
        if (count($assetClasses) < 2) {
            return [
                'success' => false,
                'message' => 'At least 2 asset classes required',
            ];
        }

        // Generate random portfolio allocations
        $portfolios = $this->generateRandomPortfolios($assetClasses, $numPortfolios);

        // Calculate risk/return for each portfolio
        $portfolioStats = [];
        foreach ($portfolios as $portfolio) {
            $stats = $this->calculatePortfolioStatistics($portfolio, $assetClasses);
            $stats['sharpe_ratio'] = $this->calculateSharpeRatio(
                $stats['expected_return'],
                $stats['volatility'],
                $riskFreeRate
            );
            $stats['allocation'] = $portfolio['weights'];
            $portfolioStats[] = $stats;
        }

        // Find key portfolios
        $maxSharpePortfolio = $this->findMaxSharpePortfolio($portfolioStats);
        $minVariancePortfolio = $this->findMinVariancePortfolio($portfolioStats);

        // Calculate efficient frontier curve (pareto optimal portfolios)
        $efficientPortfolios = $this->extractEfficientPortfolios($portfolioStats);

        return [
            'success' => true,
            'efficient_frontier' => $efficientPortfolios,
            'all_portfolios' => $portfolioStats,
            'max_sharpe_portfolio' => $maxSharpePortfolio,
            'min_variance_portfolio' => $minVariancePortfolio,
            'risk_free_rate' => $riskFreeRate,
            'num_portfolios' => count($portfolioStats),
            'asset_classes' => array_keys($assetClasses),
        ];
    }

    /**
     * Calculate optimal portfolio for target return
     *
     * @param  array  $assetClasses  Asset class data
     * @param  float  $targetReturn  Target annual return
     * @param  float  $riskFreeRate  Risk-free rate
     * @return array Optimal portfolio allocation
     */
    public function calculateOptimalPortfolio(
        array $assetClasses,
        float $targetReturn,
        float $riskFreeRate = 0.04
    ): array {
        // Generate efficient frontier
        $frontier = $this->calculateEfficientFrontier($assetClasses, 500, $riskFreeRate);

        if (! $frontier['success']) {
            return $frontier;
        }

        // Find portfolio closest to target return on efficient frontier
        $optimalPortfolio = null;
        $minDifference = PHP_FLOAT_MAX;

        foreach ($frontier['efficient_frontier'] as $portfolio) {
            $difference = abs($portfolio['expected_return'] - $targetReturn);
            if ($difference < $minDifference) {
                $minDifference = $difference;
                $optimalPortfolio = $portfolio;
            }
        }

        if (! $optimalPortfolio) {
            return [
                'success' => false,
                'message' => 'Could not find optimal portfolio for target return',
            ];
        }

        return [
            'success' => true,
            'optimal_portfolio' => $optimalPortfolio,
            'target_return' => $targetReturn,
            'achieved_return' => $optimalPortfolio['expected_return'],
            'volatility' => $optimalPortfolio['volatility'],
            'sharpe_ratio' => $optimalPortfolio['sharpe_ratio'],
            'allocation' => $optimalPortfolio['allocation'],
        ];
    }

    /**
     * Calculate optimal portfolio for target risk level
     *
     * @param  array  $assetClasses  Asset class data
     * @param  float  $targetVolatility  Target volatility (standard deviation)
     * @param  float  $riskFreeRate  Risk-free rate
     * @return array Optimal portfolio allocation
     */
    public function calculateOptimalPortfolioByRisk(
        array $assetClasses,
        float $targetVolatility,
        float $riskFreeRate = 0.04
    ): array {
        // Generate efficient frontier
        $frontier = $this->calculateEfficientFrontier($assetClasses, 500, $riskFreeRate);

        if (! $frontier['success']) {
            return $frontier;
        }

        // Find portfolio closest to target volatility on efficient frontier
        $optimalPortfolio = null;
        $minDifference = PHP_FLOAT_MAX;

        foreach ($frontier['efficient_frontier'] as $portfolio) {
            $difference = abs($portfolio['volatility'] - $targetVolatility);
            if ($difference < $minDifference) {
                $minDifference = $difference;
                $optimalPortfolio = $portfolio;
            }
        }

        if (! $optimalPortfolio) {
            return [
                'success' => false,
                'message' => 'Could not find optimal portfolio for target risk',
            ];
        }

        return [
            'success' => true,
            'optimal_portfolio' => $optimalPortfolio,
            'target_volatility' => $targetVolatility,
            'achieved_volatility' => $optimalPortfolio['volatility'],
            'expected_return' => $optimalPortfolio['expected_return'],
            'sharpe_ratio' => $optimalPortfolio['sharpe_ratio'],
            'allocation' => $optimalPortfolio['allocation'],
        ];
    }

    /**
     * Generate random portfolio allocations
     *
     * @param  array  $assetClasses  Asset classes
     * @param  int  $numPortfolios  Number of portfolios to generate
     * @return array Random portfolio allocations
     */
    private function generateRandomPortfolios(array $assetClasses, int $numPortfolios): array
    {
        $portfolios = [];
        $assetNames = array_keys($assetClasses);
        $numAssets = count($assetNames);

        for ($i = 0; $i < $numPortfolios; $i++) {
            // Generate random weights that sum to 1.0
            $weights = [];
            $sum = 0;

            for ($j = 0; $j < $numAssets; $j++) {
                $weight = mt_rand(0, 100) / 100;
                $weights[$assetNames[$j]] = $weight;
                $sum += $weight;
            }

            // Normalize weights to sum to 1.0
            foreach ($weights as $asset => $weight) {
                $weights[$asset] = $weight / $sum;
            }

            $portfolios[] = ['weights' => $weights];
        }

        return $portfolios;
    }

    /**
     * Calculate portfolio statistics (return, volatility)
     *
     * @param  array  $portfolio  Portfolio with weights
     * @param  array  $assetClasses  Asset class data
     * @return array Portfolio statistics
     */
    private function calculatePortfolioStatistics(array $portfolio, array $assetClasses): array
    {
        $weights = $portfolio['weights'];

        // Calculate expected return (weighted average)
        $expectedReturn = 0.0;
        foreach ($weights as $asset => $weight) {
            $expectedReturn += $weight * $assetClasses[$asset]['expected_return'];
        }

        // Calculate portfolio variance (considers correlations)
        $variance = 0.0;

        foreach ($weights as $asset1 => $weight1) {
            foreach ($weights as $asset2 => $weight2) {
                $volatility1 = $assetClasses[$asset1]['volatility'];
                $volatility2 = $assetClasses[$asset2]['volatility'];

                // Get correlation (defaults to 0 if not specified)
                $correlation = $this->getCorrelation($asset1, $asset2, $assetClasses);

                $variance += $weight1 * $weight2 * $volatility1 * $volatility2 * $correlation;
            }
        }

        $volatility = sqrt($variance);

        return [
            'expected_return' => $expectedReturn * 100, // Convert to percentage
            'volatility' => $volatility * 100, // Convert to percentage
        ];
    }

    /**
     * Get correlation between two assets
     *
     * @param  string  $asset1  First asset
     * @param  string  $asset2  Second asset
     * @param  array  $assetClasses  Asset class data
     * @return float Correlation coefficient
     */
    private function getCorrelation(string $asset1, string $asset2, array $assetClasses): float
    {
        // Diagonal: perfect correlation with self
        if ($asset1 === $asset2) {
            return 1.0;
        }

        // Check if correlation matrix exists
        if (isset($assetClasses[$asset1]['correlations'][$asset2])) {
            return $assetClasses[$asset1]['correlations'][$asset2];
        }

        // Default correlations if not specified
        return $this->getDefaultCorrelation($asset1, $asset2);
    }

    /**
     * Get default correlation between asset classes
     * Based on historical UK market data
     *
     * @param  string  $asset1  First asset
     * @param  string  $asset2  Second asset
     * @return float Default correlation
     */
    private function getDefaultCorrelation(string $asset1, string $asset2): float
    {
        // UK market typical correlations
        $defaultCorrelations = [
            'equities' => [
                'bonds' => 0.20,
                'cash' => 0.05,
                'alternatives' => 0.40,
            ],
            'bonds' => [
                'equities' => 0.20,
                'cash' => 0.30,
                'alternatives' => 0.15,
            ],
            'cash' => [
                'equities' => 0.05,
                'bonds' => 0.30,
                'alternatives' => 0.10,
            ],
            'alternatives' => [
                'equities' => 0.40,
                'bonds' => 0.15,
                'cash' => 0.10,
            ],
        ];

        return $defaultCorrelations[$asset1][$asset2] ?? 0.30; // Default moderate correlation
    }

    /**
     * Calculate Sharpe ratio
     *
     * @param  float  $return  Expected return
     * @param  float  $volatility  Portfolio volatility
     * @param  float  $riskFreeRate  Risk-free rate
     * @return float Sharpe ratio
     */
    private function calculateSharpeRatio(float $return, float $volatility, float $riskFreeRate): float
    {
        if ($volatility <= 0) {
            return 0.0;
        }

        return ($return - ($riskFreeRate * 100)) / $volatility;
    }

    /**
     * Find portfolio with maximum Sharpe ratio
     *
     * @param  array  $portfolios  Array of portfolio statistics
     * @return array Portfolio with maximum Sharpe ratio
     */
    private function findMaxSharpePortfolio(array $portfolios): array
    {
        $maxSharpe = -PHP_FLOAT_MAX;
        $maxSharpePortfolio = null;

        foreach ($portfolios as $portfolio) {
            if ($portfolio['sharpe_ratio'] > $maxSharpe) {
                $maxSharpe = $portfolio['sharpe_ratio'];
                $maxSharpePortfolio = $portfolio;
            }
        }

        return $maxSharpePortfolio;
    }

    /**
     * Find portfolio with minimum variance
     *
     * @param  array  $portfolios  Array of portfolio statistics
     * @return array Portfolio with minimum variance
     */
    private function findMinVariancePortfolio(array $portfolios): array
    {
        $minVolatility = PHP_FLOAT_MAX;
        $minVariancePortfolio = null;

        foreach ($portfolios as $portfolio) {
            if ($portfolio['volatility'] < $minVolatility) {
                $minVolatility = $portfolio['volatility'];
                $minVariancePortfolio = $portfolio;
            }
        }

        return $minVariancePortfolio;
    }

    /**
     * Extract efficient frontier (pareto optimal portfolios)
     * Portfolios that offer highest return for given risk level
     *
     * @param  array  $portfolios  All portfolio statistics
     * @return array Efficient frontier portfolios
     */
    private function extractEfficientPortfolios(array $portfolios): array
    {
        // Sort by volatility
        usort($portfolios, fn ($a, $b) => $a['volatility'] <=> $b['volatility']);

        $efficientPortfolios = [];
        $maxReturnSoFar = -PHP_FLOAT_MAX;

        foreach ($portfolios as $portfolio) {
            // Portfolio is efficient if it has higher return than all lower-risk portfolios
            if ($portfolio['expected_return'] > $maxReturnSoFar) {
                $efficientPortfolios[] = $portfolio;
                $maxReturnSoFar = $portfolio['expected_return'];
            }
        }

        return $efficientPortfolios;
    }

    /**
     * Compare current portfolio with efficient frontier
     *
     * @param  array  $currentAllocation  Current portfolio allocation
     * @param  array  $assetClasses  Asset class data
     * @param  float  $riskFreeRate  Risk-free rate
     * @return array Comparison analysis
     */
    public function compareWithEfficientFrontier(
        array $currentAllocation,
        array $assetClasses,
        float $riskFreeRate = 0.04
    ): array {
        // Calculate current portfolio statistics
        $currentStats = $this->calculatePortfolioStatistics(
            ['weights' => $currentAllocation],
            $assetClasses
        );
        $currentStats['sharpe_ratio'] = $this->calculateSharpeRatio(
            $currentStats['expected_return'],
            $currentStats['volatility'],
            $riskFreeRate
        );

        // Generate efficient frontier
        $frontier = $this->calculateEfficientFrontier($assetClasses, 500, $riskFreeRate);

        if (! $frontier['success']) {
            return $frontier;
        }

        // Find nearest efficient portfolio with same risk
        $nearestEfficientPortfolio = null;
        $minDifference = PHP_FLOAT_MAX;

        foreach ($frontier['efficient_frontier'] as $portfolio) {
            $difference = abs($portfolio['volatility'] - $currentStats['volatility']);
            if ($difference < $minDifference) {
                $minDifference = $difference;
                $nearestEfficientPortfolio = $portfolio;
            }
        }

        // Calculate efficiency score (0-100)
        $efficiencyScore = $this->calculateEfficiencyScore(
            $currentStats,
            $nearestEfficientPortfolio,
            $frontier['max_sharpe_portfolio']
        );

        return [
            'success' => true,
            'current_portfolio' => [
                'expected_return' => $currentStats['expected_return'],
                'volatility' => $currentStats['volatility'],
                'sharpe_ratio' => $currentStats['sharpe_ratio'],
                'allocation' => $currentAllocation,
            ],
            'nearest_efficient_portfolio' => $nearestEfficientPortfolio,
            'max_sharpe_portfolio' => $frontier['max_sharpe_portfolio'],
            'efficiency_score' => $efficiencyScore,
            'improvement_potential' => [
                'return_increase' => $nearestEfficientPortfolio['expected_return'] - $currentStats['expected_return'],
                'risk_reduction' => $currentStats['volatility'] - $nearestEfficientPortfolio['volatility'],
                'sharpe_improvement' => $nearestEfficientPortfolio['sharpe_ratio'] - $currentStats['sharpe_ratio'],
            ],
            'recommendation' => $this->generateEfficiencyRecommendation($efficiencyScore),
        ];
    }

    /**
     * Calculate portfolio efficiency score
     *
     * @param  array  $currentStats  Current portfolio statistics
     * @param  array  $efficientPortfolio  Nearest efficient portfolio
     * @param  array  $maxSharpePortfolio  Maximum Sharpe portfolio
     * @return float Efficiency score (0-100)
     */
    private function calculateEfficiencyScore(
        array $currentStats,
        array $efficientPortfolio,
        array $maxSharpePortfolio
    ): float {
        // Score based on Sharpe ratio relative to max Sharpe
        if ($maxSharpePortfolio['sharpe_ratio'] <= 0) {
            return 50.0;
        }

        $sharpeScore = ($currentStats['sharpe_ratio'] / $maxSharpePortfolio['sharpe_ratio']) * 100;

        // Cap at 100
        return min(100.0, max(0.0, $sharpeScore));
    }

    /**
     * Generate efficiency recommendation
     *
     * @param  float  $efficiencyScore  Efficiency score
     * @return string Recommendation text
     */
    private function generateEfficiencyRecommendation(float $efficiencyScore): string
    {
        if ($efficiencyScore >= 90) {
            return 'Your portfolio is highly efficient and near-optimal on the efficient frontier.';
        }

        if ($efficiencyScore >= 75) {
            return 'Your portfolio is reasonably efficient but has room for improvement.';
        }

        if ($efficiencyScore >= 60) {
            return 'Your portfolio could be significantly improved by moving towards the efficient frontier.';
        }

        return 'Your portfolio is sub-optimal. Consider rebalancing to improve risk-adjusted returns.';
    }
}
