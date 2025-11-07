<template>
  <OnboardingStep
    title="Employment & Income"
    description="Your income and employment details help us understand your financial position"
    :can-go-back="true"
    :can-skip="false"
    :loading="loading"
    :error="error"
    @next="handleNext"
    @back="handleBack"
  >
    <div class="space-y-6">
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <p class="text-body-sm text-blue-800">
          <strong>Why this matters:</strong> Income information is essential for calculating your estate's Inheritance Tax liability and understanding your protection needs.
        </p>
      </div>

      <!-- Employment Details Section -->
      <div class="border-t pt-4">
        <h4 class="text-body font-medium text-gray-900 mb-4">
          Employment Details
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="occupation" class="label">
              Occupation
            </label>
            <input
              id="occupation"
              v-model="formData.occupation"
              type="text"
              class="input-field"
              placeholder="Software Developer"
            >
          </div>

          <div>
            <label for="employer" class="label">
              Employer
            </label>
            <input
              id="employer"
              v-model="formData.employer"
              type="text"
              class="input-field"
              placeholder="Tech Company Ltd"
            >
          </div>

          <div>
            <label for="industry" class="label">
              Industry
            </label>
            <input
              id="industry"
              v-model="formData.industry"
              type="text"
              class="input-field"
              placeholder="Technology"
            >
          </div>

          <div>
            <label for="employment_status" class="label">
              Employment Status <span class="text-red-500">*</span>
            </label>
            <select
              id="employment_status"
              v-model="formData.employment_status"
              class="input-field"
              required
            >
              <option value="">Select status</option>
              <option value="employed">Employed</option>
              <option value="self_employed">Self-Employed</option>
              <option value="unemployed">Unemployed</option>
              <option value="retired">Retired</option>
              <option value="other">Other</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Income Section -->
      <div class="border-t pt-4">
        <h4 class="text-body font-medium text-gray-900 mb-4">
          Income Sources
        </h4>

        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-4">
          <p class="text-body-sm text-amber-800">
            <strong>Note:</strong> Rental income is entered through the Property section where you can track property values, rental income, and expenses.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Employment Income -->
          <div>
            <label for="annual_employment_income" class="label">
              Annual Employment Income
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
              <input
                id="annual_employment_income"
                v-model.number="formData.annual_employment_income"
                type="number"
                min="0"
                step="1000"
                class="input-field pl-8"
                placeholder="50000"
              >
            </div>
            <p class="mt-1 text-body-sm text-gray-500">
              Salary, bonuses, employment income (before tax)
            </p>
          </div>

          <!-- Self-Employment Income -->
          <div>
            <label for="annual_self_employment_income" class="label">
              Annual Self-Employment Income
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
              <input
                id="annual_self_employment_income"
                v-model.number="formData.annual_self_employment_income"
                type="number"
                min="0"
                step="1000"
                class="input-field pl-8"
                placeholder="0"
              >
            </div>
            <p class="mt-1 text-body-sm text-gray-500">
              Income from business or freelancing
            </p>
          </div>

          <!-- Dividend Income -->
          <div>
            <label for="annual_dividend_income" class="label">
              Annual Dividend Income
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
              <input
                id="annual_dividend_income"
                v-model.number="formData.annual_dividend_income"
                type="number"
                min="0"
                step="1000"
                class="input-field pl-8"
                placeholder="0"
              >
            </div>
            <p class="mt-1 text-body-sm text-gray-500">
              Income from dividends and distributions
            </p>
          </div>

          <!-- Other Income -->
          <div>
            <label for="annual_other_income" class="label">
              Annual Other Income
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
              <input
                id="annual_other_income"
                v-model.number="formData.annual_other_income"
                type="number"
                min="0"
                step="1000"
                class="input-field pl-8"
                placeholder="0"
              >
            </div>
            <p class="mt-1 text-body-sm text-gray-500">
              Any other income sources
            </p>
          </div>

          <!-- Total Income (calculated) -->
          <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-body-sm text-gray-600">Total Annual Income</p>
            <p class="text-h3 font-display text-gray-900">
              £{{ totalIncome.toLocaleString() }}
            </p>
          </div>
        </div>
      </div>

    </div>
  </OnboardingStep>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useStore } from 'vuex';
import OnboardingStep from '../OnboardingStep.vue';

export default {
  name: 'IncomeStep',

  components: {
    OnboardingStep,
  },

  emits: ['next', 'back', 'skip'],

  setup(props, { emit }) {
    const store = useStore();

    const formData = ref({
      occupation: '',
      employer: '',
      industry: '',
      employment_status: '',
      annual_employment_income: 0,
      annual_self_employment_income: 0,
      annual_dividend_income: 0,
      annual_other_income: 0,
    });

    const loading = ref(false);
    const error = ref(null);

    const totalIncome = computed(() => {
      return (
        (formData.value.annual_employment_income || 0) +
        (formData.value.annual_self_employment_income || 0) +
        (formData.value.annual_dividend_income || 0) +
        (formData.value.annual_other_income || 0)
      );
    });

    const validateForm = () => {
      if (!formData.value.employment_status) {
        error.value = 'Please select your employment status';
        return false;
      }

      return true;
    };

    const handleNext = async () => {
      if (!validateForm()) {
        return;
      }

      loading.value = true;
      error.value = null;

      try {
        await store.dispatch('onboarding/saveStepData', {
          stepName: 'income',
          data: formData.value,
        });

        emit('next');
      } catch (err) {
        error.value = err.message || 'Failed to save income information. Please try again.';
      } finally {
        loading.value = false;
      }
    };

    const handleBack = () => {
      emit('back');
    };

    onMounted(async () => {
      // Load existing user data if available
      const currentUser = store.getters['auth/currentUser'];
      if (currentUser) {
        formData.value = {
          occupation: currentUser.occupation || '',
          employer: currentUser.employer || '',
          industry: currentUser.industry || '',
          employment_status: currentUser.employment_status || '',
          annual_employment_income: currentUser.annual_employment_income || 0,
          annual_self_employment_income: currentUser.annual_self_employment_income || 0,
          annual_dividend_income: currentUser.annual_dividend_income || 0,
          annual_other_income: currentUser.annual_other_income || 0,
        };
      }

      // Load existing step data if available
      try {
        const stepData = await store.dispatch('onboarding/fetchStepData', 'income');
        if (stepData && Object.keys(stepData).length > 0) {
          formData.value = { ...formData.value, ...stepData };
        }
      } catch (err) {
        // No existing data, start fresh
      }
    });

    return {
      formData,
      loading,
      error,
      totalIncome,
      handleNext,
      handleBack,
    };
  },
};
</script>
