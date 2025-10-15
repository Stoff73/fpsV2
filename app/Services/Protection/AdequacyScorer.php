<?php

declare(strict_types=1);

namespace App\Services\Protection;

class AdequacyScorer
{
    /**
     * Calculate adequacy score based on coverage gaps.
     */
    public function calculateAdequacyScore(array $gaps, array $needs): int
    {
        $totalNeed = $needs['total_need'];
        $totalGap = $gaps['total_gap'];

        if ($totalNeed <= 0) {
            return 100;
        }

        $coverageRatio = ($totalNeed - $totalGap) / $totalNeed;
        $score = (int) round($coverageRatio * 100);

        return max(0, min(100, $score));
    }

    /**
     * Categorize adequacy score.
     */
    public function categorizeScore(int $score): string
    {
        return match (true) {
            $score >= 80 => 'Excellent',
            $score >= 60 => 'Good',
            $score >= 40 => 'Fair',
            default => 'Critical',
        };
    }

    /**
     * Get score color for UI display.
     */
    public function getScoreColor(int $score): string
    {
        return match (true) {
            $score >= 80 => 'green',
            $score >= 60 => 'blue',
            $score >= 40 => 'amber',
            default => 'red',
        };
    }

    /**
     * Generate score insights.
     */
    public function generateScoreInsights(int $score, array $gaps): array
    {
        $category = $this->categorizeScore($score);
        $color = $this->getScoreColor($score);

        $insights = [];

        if ($score >= 80) {
            $insights[] = 'Your protection coverage is excellent. You have comprehensive protection in place.';
        } elseif ($score >= 60) {
            $insights[] = 'Your protection coverage is good, but there are some areas for improvement.';
        } elseif ($score >= 40) {
            $insights[] = 'Your protection coverage is fair. Consider increasing coverage to better protect your family.';
        } else {
            $insights[] = 'Your protection coverage is critical. Immediate action is recommended to protect your family.';
        }

        // Add specific gap insights
        if ($gaps['gaps_by_category']['human_capital_gap'] > 0) {
            $insights[] = 'There is a significant gap in life insurance coverage.';
        }

        if ($gaps['gaps_by_category']['income_protection_gap'] > 0) {
            $insights[] = 'Consider adding income protection to cover loss of earnings.';
        }

        return [
            'score' => $score,
            'category' => $category,
            'color' => $color,
            'insights' => $insights,
        ];
    }
}
