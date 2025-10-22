<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Onboarding\OnboardingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OnboardingController extends Controller
{
    public function __construct(
        private OnboardingService $onboardingService
    ) {}

    /**
     * Get onboarding status for the authenticated user
     */
    public function getOnboardingStatus(Request $request): JsonResponse
    {
        try {
            $status = $this->onboardingService->getOnboardingStatus($request->user()->id);

            return response()->json([
                'success' => true,
                'data' => $status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve onboarding status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Set the focus area for onboarding
     */
    public function setFocusArea(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'focus_area' => 'required|in:estate,protection,retirement,investment,tax_optimisation',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = $this->onboardingService->setFocusArea(
                $request->user()->id,
                $request->input('focus_area')
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'focus_area' => $user->onboarding_focus_area,
                    'current_step' => $user->onboarding_current_step,
                    'started_at' => $user->onboarding_started_at?->toISOString(),
                ],
                'message' => 'Focus area set successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to set focus area',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save progress for a step
     */
    public function saveStepProgress(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'step_name' => 'required|string',
            'data' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $progress = $this->onboardingService->saveStepProgress(
                $request->user()->id,
                $request->input('step_name'),
                $request->input('data')
            );

            // Get updated progress percentage
            $status = $this->onboardingService->getOnboardingStatus($request->user()->id);

            return response()->json([
                'success' => true,
                'data' => [
                    'progress' => $progress,
                    'progress_percentage' => $status['progress_percentage'],
                    'next_step' => $this->onboardingService->getNextStep(
                        $request->user()->id,
                        $request->input('step_name')
                    ),
                ],
                'message' => 'Step progress saved successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save step progress',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Skip a step
     */
    public function skipStep(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'step_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $progress = $this->onboardingService->skipStep(
                $request->user()->id,
                $request->input('step_name')
            );

            // Get updated progress percentage
            $status = $this->onboardingService->getOnboardingStatus($request->user()->id);

            return response()->json([
                'success' => true,
                'data' => [
                    'progress' => $progress,
                    'progress_percentage' => $status['progress_percentage'],
                    'next_step' => $this->onboardingService->getNextStep(
                        $request->user()->id,
                        $request->input('step_name')
                    ),
                ],
                'message' => 'Step skipped successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to skip step',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Complete the onboarding process
     */
    public function completeOnboarding(Request $request): JsonResponse
    {
        try {
            $user = $this->onboardingService->completeOnboarding($request->user()->id);

            return response()->json([
                'success' => true,
                'data' => [
                    'onboarding_completed' => $user->onboarding_completed,
                    'completed_at' => $user->onboarding_completed_at?->toISOString(),
                ],
                'message' => 'Onboarding completed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete onboarding',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Restart the onboarding process
     */
    public function restartOnboarding(Request $request): JsonResponse
    {
        try {
            $user = $this->onboardingService->restartOnboarding($request->user()->id);

            return response()->json([
                'success' => true,
                'data' => [
                    'onboarding_completed' => $user->onboarding_completed,
                    'focus_area' => $user->onboarding_focus_area,
                ],
                'message' => 'Onboarding restarted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restart onboarding',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get data for a specific step
     */
    public function getStepData(Request $request, string $step): JsonResponse
    {
        try {
            $stepData = $this->onboardingService->getStepData($request->user()->id, $step);

            return response()->json([
                'success' => true,
                'data' => $stepData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve step data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all steps for the user's focus area
     */
    public function getSteps(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user->onboarding_focus_area) {
                return response()->json([
                    'success' => false,
                    'message' => 'Focus area not set',
                ], 400);
            }

            $steps = $this->onboardingService->getOnboardingSteps(
                $user->onboarding_focus_area,
                $user->id
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'steps' => array_values($steps),
                    'total' => count($steps),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve steps',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get skip reason for a step
     */
    public function getSkipReason(Request $request, string $step): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user->onboarding_focus_area) {
                return response()->json([
                    'success' => false,
                    'message' => 'Focus area not set',
                ], 400);
            }

            $reason = $this->onboardingService->getSkipReasonText(
                $user->onboarding_focus_area,
                $step
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'skip_reason' => $reason,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve skip reason',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
