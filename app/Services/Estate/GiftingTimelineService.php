<?php

declare(strict_types=1);

namespace App\Services\Estate;

use Illuminate\Support\Collection;

/**
 * Service for building and managing gifting timelines
 *
 * Extracted from EstateController to improve reusability
 */
class GiftingTimelineService
{
    /**
     * Build gifting timeline for display
     *
     * @param  Collection|null  $gifts  Collection of Gift models
     * @param  string  $name  Name of the gift giver
     * @return array Timeline data structure
     */
    public function buildGiftingTimeline(?Collection $gifts, string $name): array
    {
        if (! $gifts || $gifts->isEmpty()) {
            return [
                'name' => $name,
                'total_gifts' => 0,
                'gift_count' => 0,
                'gifts_within_7_years' => [],
                'timeline_events' => [],
            ];
        }

        // Filter gifts within 7 years
        $recentGifts = $gifts->filter(function ($gift) {
            $yearsAgo = \Carbon\Carbon::now()->diffInYears($gift->gift_date);

            return $yearsAgo < 7;
        })->sortBy('gift_date');

        $timelineEvents = $recentGifts->map(function ($gift) {
            $yearsAgo = \Carbon\Carbon::now()->diffInYears($gift->gift_date);
            $yearsRemaining = max(0, 7 - $yearsAgo);
            $becomeExemptDate = \Carbon\Carbon::parse($gift->gift_date)->addYears(7);

            return [
                'gift_id' => $gift->id,
                'date' => $gift->gift_date->format('Y-m-d'),
                'recipient' => $gift->recipient,
                'value' => $gift->gift_value,
                'type' => $gift->gift_type,
                'years_ago' => $yearsAgo,
                'years_remaining_until_exempt' => $yearsRemaining,
                'becomes_exempt_on' => $becomeExemptDate->format('Y-m-d'),
                'status' => $yearsRemaining === 0 ? 'Exempt' : 'Within 7 years',
            ];
        })->values();

        return [
            'name' => $name,
            'total_gifts' => $recentGifts->sum('gift_value'),
            'gift_count' => $recentGifts->count(),
            'gifts_within_7_years' => $timelineEvents,
        ];
    }
}
