<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePersonalInfoRequest;
use App\Http\Requests\UpdateIncomeOccupationRequest;
use App\Services\UserProfile\UserProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function __construct(
        private UserProfileService $userProfileService
    ) {}

    /**
     * Get the authenticated user's complete profile
     *
     * GET /api/user/profile
     */
    public function getProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $profile = $this->userProfileService->getCompleteProfile($user);

        return response()->json([
            'success' => true,
            'data' => $profile,
        ]);
    }

    /**
     * Update personal information
     *
     * PUT /api/user/profile/personal
     */
    public function updatePersonalInfo(UpdatePersonalInfoRequest $request): JsonResponse
    {
        $user = $request->user();

        $updatedUser = $this->userProfileService->updatePersonalInfo(
            $user,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Personal information updated successfully',
            'data' => [
                'user' => $updatedUser,
            ],
        ]);
    }

    /**
     * Update income and occupation information
     *
     * PUT /api/user/profile/income-occupation
     */
    public function updateIncomeOccupation(UpdateIncomeOccupationRequest $request): JsonResponse
    {
        $user = $request->user();

        $updatedUser = $this->userProfileService->updateIncomeOccupation(
            $user,
            $request->validated()
        );

        // Clear protection analysis cache when income changes
        // This ensures protection needs recalculate with new income
        \Cache::tags(['protection', 'user_' . $user->id])->flush();

        // If user has spouse, also clear their protection cache
        // (spouse's protection calculation depends on this user's income)
        if ($user->spouse_id) {
            \Cache::tags(['protection', 'user_' . $user->spouse_id])->flush();
        }

        return response()->json([
            'success' => true,
            'message' => 'Income and occupation information updated successfully',
            'data' => [
                'user' => $updatedUser,
            ],
        ]);
    }
}
