<?php

declare(strict_types=1);

namespace App\Services\Onboarding;

use App\Models\OnboardingProgress;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OnboardingService
{
    public function __construct(
        private EstateOnboardingFlow $estateFlow
    ) {}

    /**
     * Get the onboarding status for a user
     */
    public function getOnboardingStatus(int $userId): array
    {
        $user = User::findOrFail($userId);

        $status = [
            'onboarding_completed' => $user->onboarding_completed,
            'focus_area' => $user->onboarding_focus_area,
            'current_step' => $user->onboarding_current_step,
            'skipped_steps' => $user->onboarding_skipped_steps ?? [],
            'started_at' => $user->onboarding_started_at?->toISOString(),
            'completed_at' => $user->onboarding_completed_at?->toISOString(),
            'progress_percentage' => 0,
            'total_steps' => 0,
            'completed_steps' => 0,
        ];

        if ($user->onboarding_focus_area) {
            $progress = $this->calculateProgress($userId);
            $status['progress_percentage'] = $progress['percentage'];
            $status['total_steps'] = $progress['total'];
            $status['completed_steps'] = $progress['completed'];
        }

        return $status;
    }

    /**
     * Set the focus area for user's onboarding
     */
    public function setFocusArea(int $userId, string $focusArea): User
    {
        $user = User::findOrFail($userId);

        $user->update([
            'onboarding_focus_area' => $focusArea,
            'onboarding_started_at' => $user->onboarding_started_at ?? Carbon::now(),
            'onboarding_current_step' => $this->getFirstStep($focusArea),
        ]);

        return $user->fresh();
    }

    /**
     * Get the first step for a focus area
     */
    private function getFirstStep(string $focusArea): string
    {
        if ($focusArea === 'estate') {
            $steps = $this->estateFlow->getSteps();
            $firstStep = array_key_first($steps);
            return $firstStep;
        }

        // Future: Add other focus areas
        return 'personal_info';
    }

    /**
     * Save progress for a specific step
     */
    public function saveStepProgress(int $userId, string $stepName, array $data): OnboardingProgress
    {
        $user = User::findOrFail($userId);

        if (!$user->onboarding_focus_area) {
            throw new \Exception('Focus area not set');
        }

        // Process step-specific data to save to actual database tables
        $this->processStepData($userId, $stepName, $data);

        // Find or create progress record for this step
        $progress = OnboardingProgress::updateOrCreate(
            [
                'user_id' => $userId,
                'focus_area' => $user->onboarding_focus_area,
                'step_name' => $stepName,
            ],
            [
                'step_data' => $data,
                'completed' => true,
                'completed_at' => Carbon::now(),
            ]
        );

        // Update current step on user
        $nextStep = $this->getNextStep($userId, $stepName);
        $user->update([
            'onboarding_current_step' => $nextStep ?? $stepName,
        ]);

        return $progress;
    }

    /**
     * Process step-specific data and save to proper database tables
     */
    protected function processStepData(int $userId, string $stepName, array $data): void
    {
        switch ($stepName) {
            case 'family_info':
                $this->processFamilyInfo($userId, $data);
                break;

            case 'income':
                $this->processIncomeInfo($userId, $data);
                break;

            case 'assets':
                $this->processAssets($userId, $data);
                break;

            case 'liabilities':
                $this->processLiabilities($userId, $data);
                break;

            case 'protection_policies':
                $this->processProtectionPolicies($userId, $data);
                break;

            // Add more cases as we implement other steps
        }
    }

    /**
     * Process family information and save to family_members table
     */
    protected function processFamilyInfo(int $userId, array $data): void
    {
        if (!isset($data['family_members']) || !is_array($data['family_members'])) {
            return;
        }

        // Get existing family members added during onboarding
        $existingMembers = \App\Models\FamilyMember::where('user_id', $userId)
            ->whereNotNull('date_of_birth')
            ->get()
            ->keyBy('name');

        foreach ($data['family_members'] as $memberData) {
            // Check if this member already exists (by name)
            $existingMember = $existingMembers->get($memberData['name']);

            if ($existingMember) {
                // Update existing member
                $existingMember->update([
                    'relationship' => $memberData['relationship'],
                    'date_of_birth' => $memberData['date_of_birth'],
                    'is_dependent' => $memberData['is_dependent'] ?? false,
                ]);
            } else {
                // Create new family member
                \App\Models\FamilyMember::create([
                    'user_id' => $userId,
                    'name' => $memberData['name'],
                    'relationship' => $memberData['relationship'],
                    'date_of_birth' => $memberData['date_of_birth'],
                    'is_dependent' => $memberData['is_dependent'] ?? false,
                ]);
            }
        }
    }

    /**
     * Process income information and update user record
     */
    protected function processIncomeInfo(int $userId, array $data): void
    {
        $user = User::findOrFail($userId);

        // Update user income fields
        $user->update([
            'annual_employment_income' => $data['annual_employment_income'] ?? 0,
            'annual_self_employment_income' => $data['annual_self_employment_income'] ?? 0,
            'annual_rental_income' => $data['annual_rental_income'] ?? 0,
            'annual_dividend_income' => $data['annual_dividend_income'] ?? 0,
            'annual_other_income' => $data['annual_other_income'] ?? 0,
        ]);
    }

    /**
     * Process assets information and save to properties table
     */
    protected function processAssets(int $userId, array $data): void
    {
        if (!isset($data['properties']) || !is_array($data['properties'])) {
            return;
        }

        foreach ($data['properties'] as $propertyData) {
            // Create property record
            \App\Models\Property::create([
                'user_id' => $userId,
                'property_type' => $propertyData['property_type'],
                'ownership_type' => $propertyData['ownership_type'] ?? 'individual',
                'address_line_1' => $propertyData['address_line_1'],
                'address_line_2' => $propertyData['address_line_2'] ?? null,
                'city' => $propertyData['city'] ?? null,
                'postcode' => $propertyData['postcode'],
                'current_value' => $propertyData['current_value'],
                'outstanding_mortgage' => $propertyData['outstanding_mortgage'] ?? 0,
            ]);
        }
    }

    /**
     * Process liabilities information and save to liabilities table
     */
    protected function processLiabilities(int $userId, array $data): void
    {
        if (!isset($data['liabilities']) || !is_array($data['liabilities'])) {
            return;
        }

        foreach ($data['liabilities'] as $liabilityData) {
            // Create liability record
            \App\Models\Estate\Liability::create([
                'user_id' => $userId,
                'liability_type' => $liabilityData['type'],
                'liability_name' => $liabilityData['lender'],
                'current_balance' => $liabilityData['outstanding_balance'],
                'monthly_payment' => $liabilityData['monthly_payment'] ?? null,
                'interest_rate' => $liabilityData['interest_rate'] ?? null,
                'notes' => $liabilityData['purpose'] ?? null,
            ]);
        }
    }

    /**
     * Process protection policies and save to appropriate policy tables
     */
    protected function processProtectionPolicies(int $userId, array $data): void
    {
        if (!isset($data['policies']) || !is_array($data['policies'])) {
            return;
        }

        foreach ($data['policies'] as $policyData) {
            $policyType = $policyData['policyType'];

            switch ($policyType) {
                case 'life':
                    $this->createLifeInsurancePolicy($userId, $policyData);
                    break;

                case 'criticalIllness':
                    $this->createCriticalIllnessPolicy($userId, $policyData);
                    break;

                case 'incomeProtection':
                    $this->createIncomeProtectionPolicy($userId, $policyData);
                    break;

                // Note: disability and sicknessIllness might need their own tables
                // For now, we'll skip them or you can add tables for these
            }
        }
    }

    /**
     * Create life insurance policy record
     */
    protected function createLifeInsurancePolicy(int $userId, array $data): void
    {
        $startDate = !empty($data['start_date']) ? $data['start_date'] : now()->toDateString();
        $endDate = !empty($data['end_date']) ? $data['end_date'] : null;

        // Calculate term years if end date provided
        $termYears = 25; // Default
        if ($endDate) {
            $start = \Carbon\Carbon::parse($startDate);
            $end = \Carbon\Carbon::parse($endDate);
            $termYears = $start->diffInYears($end);
        }

        \App\Models\Protection\LifeInsurancePolicy::create([
            'user_id' => $userId,
            'policy_type' => 'term', // Default to term for onboarding
            'provider' => $data['provider'],
            'policy_number' => $data['policy_number'] ?? null,
            'sum_assured' => $data['coverage_amount'],
            'premium_amount' => $data['premium_amount'],
            'premium_frequency' => $data['premium_frequency'] === 'annual' ? 'annually' : 'monthly',
            'policy_start_date' => $startDate,
            'policy_term_years' => $termYears,
        ]);
    }

    /**
     * Create critical illness policy record
     */
    protected function createCriticalIllnessPolicy(int $userId, array $data): void
    {
        $startDate = !empty($data['start_date']) ? $data['start_date'] : now()->toDateString();
        $endDate = !empty($data['end_date']) ? $data['end_date'] : null;

        // Calculate term years if end date provided
        $termYears = 25; // Default
        if ($endDate) {
            $start = \Carbon\Carbon::parse($startDate);
            $end = \Carbon\Carbon::parse($endDate);
            $termYears = $start->diffInYears($end);
        }

        \App\Models\Protection\CriticalIllnessPolicy::create([
            'user_id' => $userId,
            'policy_type' => 'standalone', // Default
            'provider' => $data['provider'],
            'policy_number' => $data['policy_number'] ?? null,
            'sum_assured' => $data['coverage_amount'],
            'premium_amount' => $data['premium_amount'],
            'premium_frequency' => $data['premium_frequency'] === 'annual' ? 'annually' : 'monthly',
            'policy_start_date' => $startDate,
            'policy_term_years' => $termYears,
        ]);
    }

    /**
     * Create income protection policy record
     */
    protected function createIncomeProtectionPolicy(int $userId, array $data): void
    {
        $startDate = !empty($data['start_date']) ? $data['start_date'] : now()->toDateString();

        \App\Models\Protection\IncomeProtectionPolicy::create([
            'user_id' => $userId,
            'provider' => $data['provider'],
            'policy_number' => $data['policy_number'] ?? null,
            'benefit_amount' => $data['coverage_amount'],
            'benefit_frequency' => 'monthly',
            'deferred_period_weeks' => $data['waiting_period_weeks'] ?? 13, // Default 13 weeks
            'benefit_period_months' => $data['benefit_period_months'] ?? null,
            'premium_amount' => $data['premium_amount'],
            'policy_start_date' => $startDate,
        ]);
    }

    /**
     * Mark a step as skipped
     */
    public function skipStep(int $userId, string $stepName): OnboardingProgress
    {
        $user = User::findOrFail($userId);

        if (!$user->onboarding_focus_area) {
            throw new \Exception('Focus area not set');
        }

        // Create or update progress record
        $progress = OnboardingProgress::updateOrCreate(
            [
                'user_id' => $userId,
                'focus_area' => $user->onboarding_focus_area,
                'step_name' => $stepName,
            ],
            [
                'skipped' => true,
                'skip_reason_shown' => true,
            ]
        );

        // Add to skipped steps array in user record
        $skippedSteps = $user->onboarding_skipped_steps ?? [];
        if (!in_array($stepName, $skippedSteps)) {
            $skippedSteps[] = $stepName;
        }

        // Update current step to next step
        $nextStep = $this->getNextStep($userId, $stepName);
        $user->update([
            'onboarding_skipped_steps' => $skippedSteps,
            'onboarding_current_step' => $nextStep ?? $stepName,
        ]);

        return $progress;
    }

    /**
     * Complete the onboarding process
     */
    public function completeOnboarding(int $userId): User
    {
        $user = User::findOrFail($userId);

        $user->update([
            'onboarding_completed' => true,
            'onboarding_completed_at' => Carbon::now(),
        ]);

        return $user->fresh();
    }

    /**
     * Restart the onboarding process
     */
    public function restartOnboarding(int $userId): User
    {
        $user = User::findOrFail($userId);

        DB::transaction(function () use ($user) {
            // Delete all progress records
            OnboardingProgress::where('user_id', $user->id)->delete();

            // Reset user onboarding fields
            $user->update([
                'onboarding_completed' => false,
                'onboarding_focus_area' => null,
                'onboarding_current_step' => null,
                'onboarding_skipped_steps' => null,
                'onboarding_started_at' => null,
                'onboarding_completed_at' => null,
            ]);
        });

        return $user->fresh();
    }

    /**
     * Calculate progress percentage for a user
     */
    public function calculateProgress(int $userId): array
    {
        $user = User::findOrFail($userId);

        if (!$user->onboarding_focus_area) {
            return [
                'percentage' => 0,
                'total' => 0,
                'completed' => 0,
            ];
        }

        $steps = $this->getOnboardingSteps($user->onboarding_focus_area, $userId);
        $totalSteps = count($steps);

        $completedSteps = OnboardingProgress::where('user_id', $userId)
            ->where('focus_area', $user->onboarding_focus_area)
            ->where(function ($query) {
                $query->where('completed', true)
                      ->orWhere('skipped', true);
            })
            ->count();

        $percentage = $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100) : 0;

        return [
            'percentage' => $percentage,
            'total' => $totalSteps,
            'completed' => $completedSteps,
        ];
    }

    /**
     * Get onboarding steps for a focus area
     */
    public function getOnboardingSteps(string $focusArea, ?int $userId = null): array
    {
        if ($focusArea === 'estate') {
            if ($userId) {
                $user = User::find($userId);
                $userData = $this->getUserDataArray($user);
                return $this->estateFlow->getFilteredSteps($userData);
            }
            return $this->estateFlow->getSteps();
        }

        // Future: Add other focus areas
        return [];
    }

    /**
     * Get next step name
     */
    public function getNextStep(int $userId, string $currentStep): ?string
    {
        $user = User::findOrFail($userId);

        if (!$user->onboarding_focus_area) {
            return null;
        }

        if ($user->onboarding_focus_area === 'estate') {
            $userData = $this->getUserDataArray($user);
            return $this->estateFlow->getNextStep($currentStep, $userData);
        }

        return null;
    }

    /**
     * Get previous step name
     */
    public function getPreviousStep(int $userId, string $currentStep): ?string
    {
        $user = User::findOrFail($userId);

        if (!$user->onboarding_focus_area) {
            return null;
        }

        if ($user->onboarding_focus_area === 'estate') {
            $userData = $this->getUserDataArray($user);
            return $this->estateFlow->getPreviousStep($currentStep, $userData);
        }

        return null;
    }

    /**
     * Check if a step should be shown based on progressive disclosure
     */
    public function shouldShowStep(int $userId, string $stepName): bool
    {
        $user = User::findOrFail($userId);

        if (!$user->onboarding_focus_area) {
            return false;
        }

        if ($user->onboarding_focus_area === 'estate') {
            $userData = $this->getUserDataArray($user);
            return $this->estateFlow->shouldShowStep($stepName, $userData);
        }

        return false;
    }

    /**
     * Get skip reason text for a step
     */
    public function getSkipReasonText(string $focusArea, string $stepName): ?string
    {
        if ($focusArea === 'estate') {
            return $this->estateFlow->getSkipReason($stepName);
        }

        return null;
    }

    /**
     * Get step data for a user
     */
    public function getStepData(int $userId, string $stepName): ?array
    {
        $user = User::findOrFail($userId);

        if (!$user->onboarding_focus_area) {
            return null;
        }

        $progress = OnboardingProgress::where('user_id', $userId)
            ->where('focus_area', $user->onboarding_focus_area)
            ->where('step_name', $stepName)
            ->first();

        return $progress?->step_data;
    }

    /**
     * Convert User model to array for step logic
     */
    private function getUserDataArray(?User $user): array
    {
        if (!$user) {
            return [];
        }

        $userData = [
            'marital_status' => $user->marital_status,
            'annual_employment_income' => $user->annual_employment_income,
            'annual_self_employment_income' => $user->annual_self_employment_income,
            'annual_rental_income' => $user->annual_rental_income,
            'annual_dividend_income' => $user->annual_dividend_income,
            'annual_other_income' => $user->annual_other_income,
        ];

        // Add step data from onboarding progress
        $progressRecords = OnboardingProgress::where('user_id', $user->id)
            ->where('focus_area', $user->onboarding_focus_area)
            ->get();

        foreach ($progressRecords as $progress) {
            if ($progress->step_data) {
                $userData = array_merge($userData, $progress->step_data);
            }
        }

        return $userData;
    }
}
