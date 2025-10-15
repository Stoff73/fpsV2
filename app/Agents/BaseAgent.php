<?php

declare(strict_types=1);

namespace App\Agents;

use Illuminate\Support\Facades\Cache;

abstract class BaseAgent
{
    /**
     * Cache time-to-live in seconds.
     */
    protected int $cacheTtl = 3600; // 1 hour default

    /**
     * Analyze user data and generate insights.
     */
    abstract public function analyze(int $userId): array;

    /**
     * Generate personalized recommendations based on analysis.
     */
    abstract public function generateRecommendations(array $analysisData): array;

    /**
     * Build what-if scenarios for user planning.
     */
    abstract public function buildScenarios(int $userId, array $parameters): array;

    /**
     * Get cached data or execute callback and cache result.
     */
    protected function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $ttl = $ttl ?? $this->cacheTtl;

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Format currency value to GBP.
     */
    protected function formatCurrency(float $amount, int $decimals = 2): string
    {
        return '£'.number_format($amount, $decimals);
    }

    /**
     * Format percentage value.
     */
    protected function formatPercentage(float $value, int $decimals = 2): string
    {
        return number_format($value, $decimals).'%';
    }

    /**
     * Calculate percentage change between two values.
     */
    protected function calculatePercentageChange(float $oldValue, float $newValue): float
    {
        if ($oldValue == 0) {
            return 0;
        }

        return (($newValue - $oldValue) / $oldValue) * 100;
    }

    /**
     * Calculate compound growth.
     *
     * @param  float  $rate  Annual growth rate (as decimal, e.g., 0.05 for 5%)
     */
    protected function calculateCompoundGrowth(float $principal, float $rate, int $years): float
    {
        return $principal * pow(1 + $rate, $years);
    }

    /**
     * Calculate present value.
     */
    protected function calculatePresentValue(float $futureValue, float $discountRate, int $years): float
    {
        return $futureValue / pow(1 + $discountRate, $years);
    }

    /**
     * Generate a standardized response format.
     */
    protected function response(bool $success, string $message, array $data = []): array
    {
        return [
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Get current UK tax year.
     *
     * @return string Format: "2024/25"
     */
    protected function getCurrentTaxYear(): string
    {
        $now = now();
        $year = $now->year;
        $month = $now->month;

        // UK tax year runs from April 6 to April 5
        if ($month >= 4 && $now->day >= 6) {
            return $year.'/'.substr((string) ($year + 1), 2);
        }

        return ($year - 1).'/'.substr((string) $year, 2);
    }

    /**
     * Validate required parameters.
     *
     * @throws \InvalidArgumentException
     */
    protected function validateRequired(array $data, array $required): void
    {
        foreach ($required as $field) {
            if (! isset($data[$field])) {
                throw new \InvalidArgumentException("Missing required parameter: {$field}");
            }
        }
    }

    /**
     * Calculate age from date of birth.
     */
    protected function calculateAge(\DateTimeInterface|string $dateOfBirth): int
    {
        if (is_string($dateOfBirth)) {
            $dateOfBirth = new \DateTime($dateOfBirth);
        }

        return (int) $dateOfBirth->diff(new \DateTime)->y;
    }

    /**
     * Round to nearest penny (2 decimal places).
     */
    protected function roundToPenny(float $value): float
    {
        return round($value, 2);
    }
}
