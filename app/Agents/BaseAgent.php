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
     *
     * @param int $userId
     * @return array
     */
    abstract public function analyze(int $userId): array;

    /**
     * Generate personalized recommendations based on analysis.
     *
     * @param array $analysisData
     * @return array
     */
    abstract public function generateRecommendations(array $analysisData): array;

    /**
     * Build what-if scenarios for user planning.
     *
     * @param int $userId
     * @param array $parameters
     * @return array
     */
    abstract public function buildScenarios(int $userId, array $parameters): array;

    /**
     * Get cached data or execute callback and cache result.
     *
     * @param string $key
     * @param callable $callback
     * @param int|null $ttl
     * @return mixed
     */
    protected function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $ttl = $ttl ?? $this->cacheTtl;

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Format currency value to GBP.
     *
     * @param float $amount
     * @param int $decimals
     * @return string
     */
    protected function formatCurrency(float $amount, int $decimals = 2): string
    {
        return '£' . number_format($amount, $decimals);
    }

    /**
     * Format percentage value.
     *
     * @param float $value
     * @param int $decimals
     * @return string
     */
    protected function formatPercentage(float $value, int $decimals = 2): string
    {
        return number_format($value, $decimals) . '%';
    }

    /**
     * Calculate percentage change between two values.
     *
     * @param float $oldValue
     * @param float $newValue
     * @return float
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
     * @param float $principal
     * @param float $rate Annual growth rate (as decimal, e.g., 0.05 for 5%)
     * @param int $years
     * @return float
     */
    protected function calculateCompoundGrowth(float $principal, float $rate, int $years): float
    {
        return $principal * pow(1 + $rate, $years);
    }

    /**
     * Calculate present value.
     *
     * @param float $futureValue
     * @param float $discountRate
     * @param int $years
     * @return float
     */
    protected function calculatePresentValue(float $futureValue, float $discountRate, int $years): float
    {
        return $futureValue / pow(1 + $discountRate, $years);
    }

    /**
     * Generate a standardized response format.
     *
     * @param bool $success
     * @param string $message
     * @param array $data
     * @return array
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
            return $year . '/' . substr((string)($year + 1), 2);
        }

        return ($year - 1) . '/' . substr((string)$year, 2);
    }

    /**
     * Validate required parameters.
     *
     * @param array $data
     * @param array $required
     * @throws \InvalidArgumentException
     * @return void
     */
    protected function validateRequired(array $data, array $required): void
    {
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new \InvalidArgumentException("Missing required parameter: {$field}");
            }
        }
    }

    /**
     * Calculate age from date of birth.
     *
     * @param \DateTimeInterface|string $dateOfBirth
     * @return int
     */
    protected function calculateAge(\DateTimeInterface|string $dateOfBirth): int
    {
        if (is_string($dateOfBirth)) {
            $dateOfBirth = new \DateTime($dateOfBirth);
        }

        return (int) $dateOfBirth->diff(new \DateTime())->y;
    }

    /**
     * Round to nearest penny (2 decimal places).
     *
     * @param float $value
     * @return float
     */
    protected function roundToPenny(float $value): float
    {
        return round($value, 2);
    }
}
