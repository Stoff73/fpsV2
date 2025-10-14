<?php

declare(strict_types=1);

namespace App\Services\Retirement;

/**
 * Readiness Scorer Service
 *
 * Calculates retirement readiness scores based on projected vs target income.
 */
class ReadinessScorer
{
    /**
     * Calculate retirement readiness score.
     *
     * @param float $projectedIncome Projected annual retirement income
     * @param float $targetIncome Target annual retirement income
     * @return int Score from 0 to 100
     */
    public function calculateReadinessScore(float $projectedIncome, float $targetIncome): int
    {
        if ($targetIncome <= 0) {
            return 0;
        }

        $ratio = ($projectedIncome / $targetIncome) * 100;
        return (int) min(100, round($ratio));
    }

    /**
     * Categorize readiness based on score.
     *
     * @param int $score Readiness score (0-100)
     * @return string Category label
     */
    public function categorizeReadiness(int $score): string
    {
        return match (true) {
            $score >= 90 => 'Excellent',
            $score >= 70 => 'Good',
            $score >= 50 => 'Fair',
            default => 'Critical',
        };
    }

    /**
     * Calculate income gap between target and projected.
     *
     * @param float $projected Projected annual income
     * @param float $target Target annual income
     * @return float Gap amount (positive if shortfall, negative if surplus)
     */
    public function calculateIncomeGap(float $projected, float $target): float
    {
        return round($target - $projected, 2);
    }

    /**
     * Get color code for readiness score (for UI display).
     *
     * @param int $score
     * @return string Color: 'green', 'amber', or 'red'
     */
    public function getReadinessColor(int $score): string
    {
        return match (true) {
            $score >= 70 => 'green',
            $score >= 50 => 'amber',
            default => 'red',
        };
    }

    /**
     * Generate readiness analysis with score, category, gap, and recommendations.
     *
     * @param float $projectedIncome
     * @param float $targetIncome
     * @return array
     */
    public function analyzeReadiness(float $projectedIncome, float $targetIncome): array
    {
        $score = $this->calculateReadinessScore($projectedIncome, $targetIncome);
        $category = $this->categorizeReadiness($score);
        $gap = $this->calculateIncomeGap($projectedIncome, $targetIncome);
        $color = $this->getReadinessColor($score);

        $messages = $this->generateMessages($score, $gap);

        return [
            'score' => $score,
            'category' => $category,
            'color' => $color,
            'projected_income' => round($projectedIncome, 2),
            'target_income' => round($targetIncome, 2),
            'income_gap' => $gap,
            'message' => $messages['primary'],
            'recommendation' => $messages['recommendation'],
        ];
    }

    /**
     * Generate contextual messages based on score and gap.
     *
     * @param int $score
     * @param float $gap
     * @return array
     */
    private function generateMessages(int $score, float $gap): array
    {
        $primary = '';
        $recommendation = '';

        if ($score >= 90) {
            $primary = 'You are on track for a comfortable retirement.';
            $recommendation = 'Continue your current pension contributions and review annually.';
        } elseif ($score >= 70) {
            $primary = 'You are in good shape for retirement, with minor adjustments needed.';
            $recommendation = 'Consider increasing contributions slightly or adjusting retirement age to enhance your retirement income.';
        } elseif ($score >= 50) {
            $primary = sprintf('You have a retirement income gap of £%s per year.', number_format(abs($gap), 2));
            $recommendation = 'Increase pension contributions, consider working longer, or reduce target retirement spending.';
        } else {
            $primary = sprintf('Significant retirement shortfall detected: £%s per year.', number_format(abs($gap), 2));
            $recommendation = 'Urgent action required: Maximize pension contributions, review retirement age, and seek professional financial advice.';
        }

        return [
            'primary' => $primary,
            'recommendation' => $recommendation,
        ];
    }
}
