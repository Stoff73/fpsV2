<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Estate;

use App\Http\Controllers\Controller;
use App\Models\Estate\Liability;
use App\Models\LifeInsurancePolicy;
use App\Models\Mortgage;
use App\Models\User;
use App\Services\Estate\EstateAssetAggregatorService;
use App\Services\Estate\IHTCalculationService;
use App\Services\TaxConfigService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IHTController extends Controller
{
    public function __construct(
        private IHTCalculationService $ihtCalculationService,
        private EstateAssetAggregatorService $assetAggregator,
        private TaxConfigService $taxConfig
    ) {}

    /**
     * UNIFIED IHT Calculation - Handles all scenarios:
     * - Single users
     * - Married users without linked spouse
     * - Married users with linked spouse (second death scenario)
     */
    public function calculateIHT(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            // Determine user scenario
            $hasLinkedSpouse = $user->spouse_id !== null;
            $spouse = $hasLinkedSpouse ? User::find($user->spouse_id) : null;
            $dataSharingEnabled = $hasLinkedSpouse && $user->hasAcceptedSpousePermission();

            // Calculate IHT using the simplified service
            $calculation = $this->ihtCalculationService->calculate($user, $spouse, $dataSharingEnabled);

            // Format assets and liabilities breakdown
            $userAssets = $this->assetAggregator->gatherUserAssets($user);
            $spouseAssets = ($spouse && $dataSharingEnabled)
                ? $this->assetAggregator->gatherUserAssets($spouse)
                : collect();

            $assetsBreakdown = $this->formatAssetsBreakdown(
                $userAssets,
                $spouseAssets,
                $dataSharingEnabled,
                $user,
                $spouse
            );

            $liabilitiesBreakdown = $this->formatLiabilitiesBreakdown(
                $user,
                $spouse,
                $dataSharingEnabled
            );

            // Format response for frontend compatibility
            $response = [
                'success' => true,
                'calculation' => $calculation,
                'assets_breakdown' => $assetsBreakdown,
                'liabilities_breakdown' => $liabilitiesBreakdown,
            ];

            // Add formatted data for easy frontend consumption
            $response['iht_summary'] = [
                'current' => [
                    'net_estate' => $calculation['total_net_estate'],
                    'nrb_available' => $calculation['nrb_available'],
                    'nrb_message' => $calculation['nrb_message'],
                    'rnrb_available' => $calculation['rnrb_available'],
                    'rnrb_status' => $calculation['rnrb_status'],
                    'rnrb_message' => $calculation['rnrb_message'],
                    'total_allowances' => $calculation['total_allowances'],
                    'taxable_estate' => $calculation['taxable_estate'],
                    'iht_liability' => $calculation['iht_liability'],
                    'effective_rate' => $calculation['effective_rate'],
                ],
                'projected' => [
                    'net_estate' => $calculation['projected_net_estate'],
                    'taxable_estate' => $calculation['projected_taxable_estate'],
                    'iht_liability' => $calculation['projected_iht_liability'],
                    'years_to_death' => $calculation['years_to_death'],
                    'estimated_age_at_death' => $calculation['estimated_age_at_death'],
                ],
                'is_married' => $calculation['is_married'],
                'data_sharing_enabled' => $calculation['data_sharing_enabled'],
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            \Log::error('IHT Calculation Error:', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred calculating IHT: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Format assets breakdown for response
     */
    private function formatAssetsBreakdown($userAssets, $spouseAssets = null, bool $includeSpouse = false, ?User $user = null, ?User $spouse = null): array
    {
        $estateGrowthRate = 0.045;
        $yearsToProject = 20; // Default projection

        $userAssetsForIHT = [
            'investment' => [],
            'property' => [],
            'cash' => [],
            'business' => [],
            'chattel' => [],
        ];
        $userAssetsTotal = 0;

        // Process user assets
        foreach ($userAssets as $asset) {
            if ($asset->is_iht_exempt || $asset->current_value <= 0) {
                continue;
            }

            if (in_array($asset->asset_type, ['investment', 'property', 'cash', 'business', 'chattel'])) {
                $isJoint = ($asset->ownership_type ?? 'individual') === 'joint';
                // Database already stores the user's share - do NOT divide by 2
                $displayValue = $asset->current_value;
                $projectedValue = $displayValue * pow(1 + $estateGrowthRate, $yearsToProject);

                $userAssetsForIHT[$asset->asset_type][] = [
                    'name' => $asset->asset_name,
                    'value' => $displayValue,
                    'projected_value' => $projectedValue,
                    'is_joint' => $isJoint,
                    'ownership_type' => $asset->ownership_type,
                ];
                $userAssetsTotal += $displayValue;
            }
        }

        $userName = $user ? (trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: $user->name) : 'User';

        $breakdown = [
            'user' => [
                'name' => $userName,
                'assets' => $userAssetsForIHT,
                'total' => $userAssetsTotal,
            ],
            'spouse' => null,
        ];

        // Add spouse assets if applicable
        if ($includeSpouse && $spouseAssets && $spouseAssets->isNotEmpty()) {
            $spouseAssetsForIHT = [
                'investment' => [],
                'property' => [],
                'cash' => [],
                'business' => [],
                'chattel' => [],
            ];
            $spouseAssetsTotal = 0;

            foreach ($spouseAssets as $asset) {
                if ($asset->is_iht_exempt || $asset->current_value <= 0) {
                    continue;
                }

                if (in_array($asset->asset_type, ['investment', 'property', 'cash', 'business', 'chattel'])) {
                    $isJoint = ($asset->ownership_type ?? 'individual') === 'joint';
                    // Database already stores the spouse's share - do NOT divide by 2
                    $displayValue = $asset->current_value;
                    $projectedValue = $displayValue * pow(1 + $estateGrowthRate, $yearsToProject);

                    $spouseAssetsForIHT[$asset->asset_type][] = [
                        'name' => $asset->asset_name,
                        'value' => $displayValue,
                        'projected_value' => $projectedValue,
                        'is_joint' => $isJoint,
                        'ownership_type' => $asset->ownership_type,
                    ];
                    $spouseAssetsTotal += $displayValue;
                }
            }

            $spouseName = $spouse ? (trim(($spouse->first_name ?? '').' '.($spouse->last_name ?? '')) ?: $spouse->name) : 'Spouse';
            $breakdown['spouse'] = [
                'name' => $spouseName,
                'assets' => $spouseAssetsForIHT,
                'total' => $spouseAssetsTotal,
            ];
        }

        return $breakdown;
    }

    /**
     * Format liabilities breakdown for response
     */
    private function formatLiabilitiesBreakdown(User $user, ?User $spouse = null, bool $includeSpouse = false): array
    {
        $userMortgages = Mortgage::where('user_id', $user->id)->with('property')->get();
        $userLiabilities = Liability::where('user_id', $user->id)->get();

        $userMortgagesFormatted = [];
        $userLiabilitiesFormatted = [];
        $userMortgagesTotal = 0;
        $userLiabilitiesTotal = 0;

        foreach ($userMortgages as $mortgage) {
            if ($mortgage->outstanding_balance > 0) {
                $propertyName = $mortgage->property ? $mortgage->property->address_line_1 : 'Unknown Property';
                $userMortgagesFormatted[] = [
                    'property' => $propertyName,
                    'balance' => $mortgage->outstanding_balance,
                ];
                $userMortgagesTotal += $mortgage->outstanding_balance;
            }
        }

        foreach ($userLiabilities as $liability) {
            if ($liability->amount > 0) {
                $userLiabilitiesFormatted[] = [
                    'type' => ucwords(str_replace('_', ' ', $liability->liability_type)),
                    'amount' => $liability->amount,
                ];
                $userLiabilitiesTotal += $liability->amount;
            }
        }

        $breakdown = [
            'user' => [
                'name' => trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: $user->name,
                'mortgages' => $userMortgagesFormatted,
                'mortgages_total' => $userMortgagesTotal,
                'liabilities' => $userLiabilitiesFormatted,
                'liabilities_total' => $userLiabilitiesTotal,
                'total' => $userMortgagesTotal + $userLiabilitiesTotal,
            ],
            'spouse' => null,
        ];

        if ($includeSpouse && $spouse) {
            $spouseMortgages = Mortgage::where('user_id', $spouse->id)->with('property')->get();
            $spouseLiabilities = Liability::where('user_id', $spouse->id)->get();

            $spouseMortgagesFormatted = [];
            $spouseLiabilitiesFormatted = [];
            $spouseMortgagesTotal = 0;
            $spouseLiabilitiesTotal = 0;

            foreach ($spouseMortgages as $mortgage) {
                if ($mortgage->outstanding_balance > 0) {
                    $propertyName = $mortgage->property ? $mortgage->property->address_line_1 : 'Unknown Property';
                    $spouseMortgagesFormatted[] = [
                        'property' => $propertyName,
                        'balance' => $mortgage->outstanding_balance,
                    ];
                    $spouseMortgagesTotal += $mortgage->outstanding_balance;
                }
            }

            foreach ($spouseLiabilities as $liability) {
                if ($liability->amount > 0) {
                    $spouseLiabilitiesFormatted[] = [
                        'type' => ucwords(str_replace('_', ' ', $liability->liability_type)),
                        'amount' => $liability->amount,
                    ];
                    $spouseLiabilitiesTotal += $liability->amount;
                }
            }

            $breakdown['spouse'] = [
                'name' => trim(($spouse->first_name ?? '').' '.($spouse->last_name ?? '')) ?: $spouse->name,
                'mortgages' => $spouseMortgagesFormatted,
                'mortgages_total' => $spouseMortgagesTotal,
                'liabilities' => $spouseLiabilitiesFormatted,
                'liabilities_total' => $spouseLiabilitiesTotal,
                'total' => $spouseMortgagesTotal + $spouseLiabilitiesTotal,
            ];
        }

        return $breakdown;
    }

    /**
     * Get existing life cover for both spouses
     */
    private function getExistingLifeCover(User $user, ?User $spouse): array
    {
        $userLifeCover = LifeInsurancePolicy::where('user_id', $user->id)
            ->where('in_trust', true)
            ->sum('sum_assured');

        $spouseLifeCover = 0;
        if ($spouse) {
            $spouseLifeCover = LifeInsurancePolicy::where('user_id', $spouse->id)
                ->where('in_trust', true)
                ->sum('sum_assured');
        }

        return [
            'user' => $userLifeCover,
            'spouse' => $spouseLifeCover,
            'total' => $userLifeCover + $spouseLifeCover,
        ];
    }

    /**
     * Invalidate IHT calculation cache
     */
    public function invalidateCache(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->ihtCalculationService->invalidateCache($user);

        return response()->json([
            'success' => true,
            'message' => 'IHT calculation cache cleared',
        ]);
    }

    /**
     * DEPRECATED: Backward compatibility alias
     * This method now just calls the unified calculateIHT()
     *
     * @deprecated Use calculateIHT() instead
     */
    public function calculateSecondDeathIHTPlanning(Request $request): JsonResponse
    {
        return $this->calculateIHT($request);
    }
}
