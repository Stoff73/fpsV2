<?php

declare(strict_types=1);

namespace App\Services\Estate;

use App\Models\Estate\Gift;
use App\Models\Estate\IHTProfile;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class GiftingStrategy
{
    /**
     * Analyze potentially exempt transfers (PETs)
     */
    public function analyzePETs(Collection $gifts): array
    {
        $config = config('uk_tax_config.inheritance_tax.potentially_exempt_transfers');
        $yearsToExemption = $config['years_to_exemption'];

        // Filter PETs within 7 years
        $activePETs = $gifts->filter(function ($gift) use ($yearsToExemption) {
            $yearsAgo = Carbon::now()->diffInYears($gift->gift_date);
            return $gift->gift_type === 'pet' && $yearsAgo < $yearsToExemption;
        })->sortBy('gift_date');

        $totalValue = $activePETs->sum('gift_value');
        $petDetails = [];

        foreach ($activePETs as $pet) {
            $yearsAgo = Carbon::now()->diffInYears($pet->gift_date);
            $yearsRemaining = max(0, $yearsToExemption - $yearsAgo);

            // Determine taper relief percentage
            $taperReliefPercent = $this->getTaperReliefPercent($yearsAgo);

            $petDetails[] = [
                'id' => $pet->id,
                'date' => $pet->gift_date->format('Y-m-d'),
                'recipient' => $pet->recipient,
                'value' => $pet->gift_value,
                'years_ago' => $yearsAgo,
                'years_remaining' => $yearsRemaining,
                'taper_relief_percent' => $taperReliefPercent,
                'fully_exempt_date' => $pet->gift_date->copy()->addYears($yearsToExemption)->format('Y-m-d'),
            ];
        }

        return [
            'active_pets_count' => $activePETs->count(),
            'total_pet_value' => round($totalValue, 2),
            'pets' => $petDetails,
        ];
    }

    /**
     * Get taper relief percentage based on years since gift
     */
    private function getTaperReliefPercent(int $yearsAgo): int
    {
        $config = config('uk_tax_config.inheritance_tax.potentially_exempt_transfers.taper_relief');

        foreach ($config as $tier) {
            if ($yearsAgo >= $tier['years'] - 1) {
                // Calculate relief percentage (100% - effective rate)
                $effectiveRate = $tier['rate'];
                return (int) ((1 - ($effectiveRate / 0.40)) * 100);
            }
        }

        return 0; // No relief in first 3 years
    }

    /**
     * Calculate available annual exemption
     * £3,000 per year, can carry forward 1 year unused
     */
    public function calculateAnnualExemption(int $userId, string $taxYear): float
    {
        $config = config('uk_tax_config.gifting_exemptions');
        $annualExemption = $config['annual_exemption'];
        $canCarryForward = $config['annual_exemption_can_carry_forward'];
        $carryForwardYears = $config['carry_forward_years'];

        // Get tax year dates
        $taxYearStart = Carbon::createFromFormat('Y-m-d', $taxYear . '-04-06');
        $taxYearEnd = $taxYearStart->copy()->addYear()->subDay();

        // Get gifts made in current tax year
        $currentYearGifts = Gift::where('user_id', $userId)
            ->whereBetween('gift_date', [$taxYearStart, $taxYearEnd])
            ->whereIn('gift_type', ['pet', 'exempt_transfer'])
            ->sum('gift_value');

        // Calculate used exemption for current year
        $usedExemption = min($annualExemption, $currentYearGifts);
        $remainingExemption = $annualExemption - $usedExemption;

        // Check previous year for carry forward
        $previousYearStart = $taxYearStart->copy()->subYear();
        $previousYearEnd = $previousYearStart->copy()->addYear()->subDay();

        $previousYearGifts = Gift::where('user_id', $userId)
            ->whereBetween('gift_date', [$previousYearStart, $previousYearEnd])
            ->whereIn('gift_type', ['pet', 'exempt_transfer'])
            ->sum('gift_value');

        $previousYearUsed = min($annualExemption, $previousYearGifts);
        $previousYearRemaining = $annualExemption - $previousYearUsed;

        // Total available exemption
        $totalAvailable = $remainingExemption + ($canCarryForward ? $previousYearRemaining : 0);

        return round($totalAvailable, 2);
    }

    /**
     * Identify small gifts (£250 per person per year)
     */
    public function identifySmallGifts(Collection $gifts): array
    {
        $config = config('uk_tax_config.gifting_exemptions.small_gifts');
        $smallGiftLimit = $config['amount'];

        $smallGifts = $gifts->filter(function ($gift) use ($smallGiftLimit) {
            return $gift->gift_type === 'small_gift' && $gift->gift_value <= $smallGiftLimit;
        });

        // Group by recipient and tax year
        $byRecipient = $smallGifts->groupBy(function ($gift) {
            $taxYearStart = $gift->gift_date->month >= 4 ? $gift->gift_date->year : $gift->gift_date->year - 1;
            return $gift->recipient . '_' . $taxYearStart;
        });

        $summary = [];
        foreach ($byRecipient as $key => $recipientGifts) {
            $parts = explode('_', $key);
            $recipient = $parts[0];
            $taxYear = $parts[1];

            $totalValue = $recipientGifts->sum('gift_value');
            $isValid = $totalValue <= $smallGiftLimit;

            $summary[] = [
                'recipient' => $recipient,
                'tax_year' => $taxYear . '/' . substr((string)($taxYear + 1), -2),
                'total_value' => round($totalValue, 2),
                'is_valid' => $isValid,
                'warning' => !$isValid ? "Exceeds £{$smallGiftLimit} limit" : null,
            ];
        }

        return [
            'small_gifts_count' => $smallGifts->count(),
            'total_value' => round($smallGifts->sum('gift_value'), 2),
            'by_recipient' => $summary,
        ];
    }

    /**
     * Calculate wedding/marriage gift allowances
     */
    public function calculateMarriageGifts(string $relationship): float
    {
        $config = config('uk_tax_config.gifting_exemptions.wedding_gifts');

        return match ($relationship) {
            'child' => $config['child'],
            'grandchild', 'great_grandchild' => $config['grandchild_great_grandchild'],
            default => $config['other'],
        };
    }

    /**
     * Recommend optimal gifting strategy
     */
    public function recommendOptimalGiftingStrategy(float $estate, IHTProfile $profile): array
    {
        $config = config('uk_tax_config');
        $ihtConfig = $config['inheritance_tax'];
        $giftingConfig = $config['gifting_exemptions'];

        $nrb = $ihtConfig['nil_rate_band'];
        $annualExemption = $giftingConfig['annual_exemption'];

        // Calculate current IHT exposure
        $totalAllowance = $nrb + $profile->nrb_transferred_from_spouse;
        $ihtLiability = max(0, $estate - $totalAllowance) * $ihtConfig['standard_rate'];

        $recommendations = [];

        // Recommendation 1: Use annual exemption
        if ($ihtLiability > 0) {
            $recommendations[] = [
                'strategy' => 'Annual Exemption',
                'description' => "Gift £{$annualExemption} per year (can carry forward unused from previous year)",
                'benefit' => 'Immediate IHT saving',
                'potential_saving' => round($annualExemption * $ihtConfig['standard_rate'] * 7, 2), // 7 years of gifts
                'risk' => 'Low - exempt immediately',
            ];
        }

        // Recommendation 2: Small gifts
        $recommendations[] = [
            'strategy' => 'Small Gifts',
            'description' => 'Gift £250 per person per year to multiple recipients',
            'benefit' => 'Exempt immediately, no limit on number of recipients',
            'potential_saving' => 'Variable - depends on number of recipients',
            'risk' => 'Low - exempt immediately',
        ];

        // Recommendation 3: PETs for larger amounts
        if ($ihtLiability > $annualExemption * $ihtConfig['standard_rate']) {
            $recommendations[] = [
                'strategy' => 'Potentially Exempt Transfers (PETs)',
                'description' => 'Gift larger amounts - becomes fully exempt after 7 years',
                'benefit' => 'Can reduce estate significantly if you survive 7 years',
                'potential_saving' => round($ihtLiability, 2),
                'risk' => 'Medium - requires survival for 7 years, taper relief applies from year 3',
            ];
        }

        // Recommendation 4: Charitable giving
        if ($profile->charitable_giving_percent < 10) {
            $charitableAmount = $estate * 0.10;
            $rateDifference = $ihtConfig['standard_rate'] - $ihtConfig['reduced_rate_charity'];
            $saving = ($estate - $totalAllowance) * $rateDifference;

            $recommendations[] = [
                'strategy' => 'Charitable Giving (10%+ of estate)',
                'description' => "Leave {$charitableAmount} (10% of estate) to charity",
                'benefit' => 'Reduces IHT rate from 40% to 36%',
                'potential_saving' => round($saving, 2),
                'risk' => 'Low - benefits charity and reduces tax',
            ];
        }

        // Recommendation 5: Regular gifts from income
        $recommendations[] = [
            'strategy' => 'Normal Expenditure Out of Income',
            'description' => 'Make regular gifts from income (not capital) that don\'t affect your standard of living',
            'benefit' => 'Unlimited and immediately exempt',
            'potential_saving' => 'Variable - depends on surplus income',
            'risk' => 'Low - must demonstrate regularity and not affect living standards',
        ];

        return [
            'current_iht_liability' => round($ihtLiability, 2),
            'estate_value' => round($estate, 2),
            'recommendations' => $recommendations,
            'priority' => $this->prioritizeRecommendations($recommendations),
        ];
    }

    /**
     * Prioritize recommendations based on effectiveness
     */
    private function prioritizeRecommendations(array $recommendations): array
    {
        $priority = [];

        // Priority order: Annual Exemption -> Small Gifts -> Charitable -> Income -> PETs
        $order = [
            'Annual Exemption' => 1,
            'Small Gifts' => 2,
            'Charitable Giving (10%+ of estate)' => 3,
            'Normal Expenditure Out of Income' => 4,
            'Potentially Exempt Transfers (PETs)' => 5,
        ];

        foreach ($recommendations as $rec) {
            $priority[] = [
                'strategy' => $rec['strategy'],
                'priority' => $order[$rec['strategy']] ?? 99,
                'rationale' => $this->getPriorityRationale($rec['strategy']),
            ];
        }

        usort($priority, fn ($a, $b) => $a['priority'] <=> $b['priority']);

        return $priority;
    }

    /**
     * Get rationale for priority ranking
     */
    private function getPriorityRationale(string $strategy): string
    {
        return match ($strategy) {
            'Annual Exemption' => 'Use first - immediately exempt and risk-free',
            'Small Gifts' => 'Use second - immediately exempt, multiple recipients',
            'Charitable Giving (10%+ of estate)' => 'Consider if charitable intent - reduces overall rate',
            'Normal Expenditure Out of Income' => 'Excellent if sufficient income - unlimited potential',
            'Potentially Exempt Transfers (PETs)' => 'For larger amounts - requires 7-year survival',
            default => 'Consider based on circumstances',
        };
    }
}
