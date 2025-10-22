<template>
  <OnboardingStep
    title="Income Information"
    description="Your income sources help us understand your financial position"
    :can-go-back="true"
    :can-skip="false"
    :loading="loading"
    :error="error"
    @next="handleNext"
    @back="handleBack"
  >
    <div class="space-y-6">
      <p class="text-body-sm text-gray-600 bg-gray-50 p-3 rounded-lg">
        This information helps us calculate your estate's Inheritance Tax liability and determine if your family would be financially secure.
      </p>

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
            placeholder="0"
          >
        </div>
        <p class="mt-1 text-body-sm text-gray-500">
          Salary, bonuses, and other employment income (before tax)
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
          Income from your own business or freelancing
        </p>
      </div>

      <!-- Rental Income -->
      <div>
        <label for="annual_rental_income" class="label">
          Annual Rental Income
        </label>
        <div class="relative">
          <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
          <input
            id="annual_rental_income"
            v-model.number="formData.annual_rental_income"
            type="number"
            min="0"
            step="1000"
            class="input-field pl-8"
            placeholder="0"
          >
        </div>
        <p class="mt-1 text-body-sm text-gray-500">
          Income from rental properties (gross, before expenses)
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
          Dividends from shares and investments
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
          Pensions, state benefits, or other income sources
        </p>
      </div>

      <!-- Total Income Summary -->
      <div v-if="totalIncome > 0" class="bg-primary-50 p-4 rounded-lg border border-primary-200">
        <div class="flex items-center justify-between">
          <span class="text-body font-medium text-primary-900">Total Annual Income:</span>
          <span class="text-h4 font-display text-primary-900">£{{ totalIncome.toLocaleString() }}</span>
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
      annual_employment_income: 0,
      annual_self_employment_income: 0,
      annual_rental_income: 0,
      annual_dividend_income: 0,
      annual_other_income: 0,
    });

    const loading = ref(false);
    const error = ref(null);

    const totalIncome = computed(() => {
      return (
        (formData.value.annual_employment_income || 0) +
        (formData.value.annual_self_employment_income || 0) +
        (formData.value.annual_rental_income || 0) +
        (formData.value.annual_dividend_income || 0) +
        (formData.value.annual_other_income || 0)
      );
    });

    const handleNext = async () => {
      loading.value = true;
      error.value = null;

      try {
        await store.dispatch('onboarding/saveStepData', {
          stepName: 'income',
          data: formData.value,
        });

        emit('next');
      } catch (err) {
        error.value = err.message || 'Failed to save. Please try again.';
      } finally {
        loading.value = false;
      }
    };

    const handleBack = () => {
      emit('back');
    };

    onMounted(async () => {
      // Load existing data if available
      const existingData = await store.dispatch('onboarding/fetchStepData', 'income');
      if (existingData) {
        Object.assign(formData.value, existingData);
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
