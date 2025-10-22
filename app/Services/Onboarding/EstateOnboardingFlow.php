<?php

declare(strict_types=1);

namespace App\Services\Onboarding;

class EstateOnboardingFlow
{
    /**
     * Get the estate planning onboarding steps with configuration
     */
    public function getSteps(): array
    {
        return [
            'personal_info' => [
                'name' => 'personal_info',
                'title' => 'Personal Information',
                'description' => 'Tell us about yourself to help us tailor your estate plan',
                'order' => 1,
                'required' => true,
                'skip_reason' => 'Personal information helps us calculate your estate value and available tax reliefs. Without this, we cannot provide personalized estate planning advice.',
                'fields' => [
                    'marital_status' => ['required' => true],
                    'number_of_dependents' => ['required' => false],
                    'has_will' => ['required' => false],
                ],
            ],
            'income' => [
                'name' => 'income',
                'title' => 'Income Information',
                'description' => 'Your income sources help us understand your financial position',
                'order' => 2,
                'required' => true,
                'skip_reason' => 'Income information is essential for calculating your estate\'s Inheritance Tax liability and understanding your protection needs. Without this, we cannot provide accurate IHT projections or determine if your family would be financially secure.',
                'fields' => [
                    'annual_employment_income' => ['required' => false],
                    'annual_self_employment_income' => ['required' => false],
                    'annual_rental_income' => ['required' => false],
                    'annual_dividend_income' => ['required' => false],
                    'annual_other_income' => ['required' => false],
                ],
            ],
            'protection_policies' => [
                'name' => 'protection_policies',
                'title' => 'Protection Policies',
                'description' => 'Tell us about your existing life insurance and protection coverage',
                'order' => 3,
                'required' => false,
                'skip_reason' => 'Protection policies can provide liquidity for your estate to pay IHT bills. Knowing about these helps us ensure your beneficiaries have enough funds to settle tax liabilities.',
                'fields' => [
                    'has_life_insurance' => ['required' => false],
                    'life_insurance_policies' => ['required' => false],
                ],
            ],
            'assets' => [
                'name' => 'assets',
                'title' => 'Assets & Wealth',
                'description' => 'Tell us about your properties, investments, and other assets',
                'order' => 4,
                'required' => true,
                'skip_reason' => 'Your assets form the basis of your taxable estate. Without this information, we cannot calculate your potential IHT liability, which is the primary purpose of estate planning.',
                'fields' => [
                    'has_properties' => ['required' => false],
                    'has_investments' => ['required' => false],
                    'has_savings' => ['required' => false],
                    'has_business_interests' => ['required' => false],
                    'has_chattels' => ['required' => false],
                ],
            ],
            'liabilities' => [
                'name' => 'liabilities',
                'title' => 'Liabilities & Debts',
                'description' => 'Tell us about mortgages, loans, and other debts',
                'order' => 5,
                'required' => false,
                'skip_reason' => 'Liabilities reduce your taxable estate for IHT purposes. Skipping this may result in overestimating your IHT bill and missing potential tax savings.',
                'fields' => [
                    'has_mortgages' => ['required' => false],
                    'has_loans' => ['required' => false],
                    'has_credit_cards' => ['required' => false],
                ],
            ],
            'family_info' => [
                'name' => 'family_info',
                'title' => 'Family & Beneficiaries',
                'description' => 'Tell us about your family members and who you want to benefit from your estate',
                'order' => 6,
                'required' => false,
                'skip_reason' => 'Beneficiary information helps us calculate available reliefs (like spouse exemption and RNRB) and model different bequest scenarios to minimize IHT.',
                'fields' => [
                    'spouse_info' => ['required' => false],
                    'children_info' => ['required' => false],
                    'other_beneficiaries' => ['required' => false],
                ],
            ],
            'will_info' => [
                'name' => 'will_info',
                'title' => 'Will Information',
                'description' => 'Tell us about your will and estate planning documents',
                'order' => 7,
                'required' => false,
                'skip_reason' => 'Will status is crucial for probate readiness scoring and understanding how your estate would be distributed. This helps identify gaps in your estate plan.',
                'fields' => [
                    'has_will' => ['required' => false],
                    'will_last_updated' => ['required' => false],
                    'executor_details' => ['required' => false],
                ],
            ],
            'trust_info' => [
                'name' => 'trust_info',
                'title' => 'Trust Information',
                'description' => 'Tell us about any trusts you have created or benefit from',
                'order' => 8,
                'required' => false,
                'conditional' => true, // Only show if certain conditions are met
                'skip_reason' => 'Existing trusts can affect your IHT calculation due to Potentially Exempt Transfers (PETs) and Chargeable Lifetime Transfers (CLTs). Skipping this may lead to inaccurate tax projections.',
                'fields' => [
                    'has_trusts' => ['required' => false],
                    'trust_details' => ['required' => false],
                ],
            ],
            'completion' => [
                'name' => 'completion',
                'title' => 'Setup Complete',
                'description' => 'You\'re all set! Here\'s what happens next',
                'order' => 9,
                'required' => true,
                'skip_reason' => null, // Cannot skip completion
                'fields' => [],
            ],
        ];
    }

    /**
     * Get steps filtered based on progressive disclosure rules
     */
    public function getFilteredSteps(array $userData): array
    {
        $allSteps = $this->getSteps();
        $filteredSteps = [];

        foreach ($allSteps as $stepKey => $step) {
            if ($this->shouldShowStep($stepKey, $userData)) {
                $filteredSteps[$stepKey] = $step;
            }
        }

        return $filteredSteps;
    }

    /**
     * Determine if a step should be shown based on progressive disclosure rules
     */
    public function shouldShowStep(string $stepName, array $userData): bool
    {
        $steps = $this->getSteps();

        if (!isset($steps[$stepName])) {
            return false;
        }

        $step = $steps[$stepName];

        // Always show non-conditional steps
        if (!isset($step['conditional']) || !$step['conditional']) {
            return true;
        }

        // Trust Info - show only if:
        // 1. User indicated trust ownership elsewhere
        // 2. Or estimated estate value > £2m (RNRB taper threshold)
        if ($stepName === 'trust_info') {
            $hasThrusts = $userData['has_trusts'] ?? false;
            $estateValue = $this->calculateEstimatedEstateValue($userData);

            return $hasThrusts || $estateValue > 2000000;
        }

        // Family Info - show spouse section only if married
        if ($stepName === 'family_info') {
            $maritalStatus = $userData['marital_status'] ?? null;
            // Always show family info, but content will be filtered inside the component
            return true;
        }

        return true;
    }

    /**
     * Calculate estimated estate value from user data
     */
    private function calculateEstimatedEstateValue(array $userData): float
    {
        $estimatedValue = 0.0;

        // Add property values (rough estimate)
        if (isset($userData['has_properties']) && $userData['has_properties']) {
            // Use average UK property price as rough estimate
            $estimatedValue += 300000;
        }

        // Add investment values
        if (isset($userData['has_investments']) && $userData['has_investments']) {
            $estimatedValue += 100000; // Conservative estimate
        }

        // Add savings
        if (isset($userData['has_savings']) && $userData['has_savings']) {
            $estimatedValue += 50000; // Conservative estimate
        }

        // Add business interests
        if (isset($userData['has_business_interests']) && $userData['has_business_interests']) {
            $estimatedValue += 200000; // Conservative estimate
        }

        return $estimatedValue;
    }

    /**
     * Get the skip reason text for a specific step
     */
    public function getSkipReason(string $stepName): ?string
    {
        $steps = $this->getSteps();
        return $steps[$stepName]['skip_reason'] ?? null;
    }

    /**
     * Get the next step after the current one
     */
    public function getNextStep(string $currentStep, array $userData): ?string
    {
        $steps = $this->getFilteredSteps($userData);
        $stepKeys = array_keys($steps);
        $currentIndex = array_search($currentStep, $stepKeys);

        if ($currentIndex === false || $currentIndex === count($stepKeys) - 1) {
            return null;
        }

        return $stepKeys[$currentIndex + 1];
    }

    /**
     * Get the previous step before the current one
     */
    public function getPreviousStep(string $currentStep, array $userData): ?string
    {
        $steps = $this->getFilteredSteps($userData);
        $stepKeys = array_keys($steps);
        $currentIndex = array_search($currentStep, $stepKeys);

        if ($currentIndex === false || $currentIndex === 0) {
            return null;
        }

        return $stepKeys[$currentIndex - 1];
    }
}
