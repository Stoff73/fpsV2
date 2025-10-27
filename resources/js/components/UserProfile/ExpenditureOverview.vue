<template>
  <div>
    <div class="mb-6">
      <h2 class="text-h4 font-semibold text-gray-900">Expenditure & Surplus Income</h2>
      <p class="mt-1 text-body-sm text-gray-600">
        Manage your monthly and annual expenditure to calculate surplus income
      </p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      <!-- Net Income Card -->
      <div class="card p-6 bg-gradient-to-r from-success-50 to-success-100">
        <p class="text-body-sm font-medium text-success-700">Net Annual Income</p>
        <p class="text-h3 font-display font-bold text-success-900 mt-2">
          {{ formatCurrency(netAnnualIncome) }}
        </p>
        <p class="text-body-sm text-success-700 mt-1">
          {{ formatCurrency(netAnnualIncome / 12) }}/month (after tax & NI)
        </p>
      </div>

      <!-- Total Expenditure Card -->
      <div class="card p-6 bg-gradient-to-r from-error-50 to-error-100">
        <p class="text-body-sm font-medium text-error-700">Annual Expenditure</p>
        <p class="text-h3 font-display font-bold text-error-900 mt-2">
          {{ formatCurrency(annualExpenditure) }}
        </p>
        <p class="text-body-sm text-error-700 mt-1">
          {{ formatCurrency(monthlyExpenditure) }}/month
        </p>
      </div>

      <!-- Surplus Income Card -->
      <div class="card p-6" :class="surplusIncomeClass">
        <p class="text-body-sm font-medium" :class="surplusIncomeTextClass">Surplus Income</p>
        <p class="text-h3 font-display font-bold mt-2" :class="surplusIncomeTextClass">
          {{ formatCurrency(surplusIncome) }}
        </p>
        <p class="text-body-sm mt-1" :class="surplusIncomeTextClass">
          {{ formatCurrency(surplusIncome / 12) }}/month
        </p>
      </div>
    </div>

    <!-- Expenditure Form -->
    <div class="card p-6">
      <h3 class="text-h5 font-semibold text-gray-900 mb-4">Update Expenditure</h3>

      <form @submit.prevent="handleSubmit">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Monthly Expenditure -->
          <div>
            <label for="monthly_expenditure" class="label">
              Monthly Expenditure
            </label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">£</span>
              <input
                id="monthly_expenditure"
                v-model.number="formData.monthly_expenditure"
                type="number"
                step="0.01"
                min="0"
                class="input-field pl-7"
                placeholder="0.00"
                @input="calculateAnnualExpenditure"
              />
            </div>
            <p class="mt-1 text-body-sm text-gray-500">
              Your total monthly living expenses
            </p>
          </div>

          <!-- Annual Expenditure -->
          <div>
            <label for="annual_expenditure" class="label">
              Annual Expenditure
            </label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">£</span>
              <input
                id="annual_expenditure"
                v-model.number="formData.annual_expenditure"
                type="number"
                step="0.01"
                min="0"
                class="input-field pl-7"
                placeholder="0.00"
                @input="calculateMonthlyExpenditure"
              />
            </div>
            <p class="mt-1 text-body-sm text-gray-500">
              Your total yearly living expenses
            </p>
          </div>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="mt-4 rounded-md bg-error-50 p-4">
          <p class="text-body-sm text-error-800">{{ error }}</p>
        </div>

        <!-- Success Message -->
        <div v-if="successMessage" class="mt-4 rounded-md bg-success-50 p-4">
          <p class="text-body-sm text-success-800">{{ successMessage }}</p>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 flex justify-end space-x-3">
          <button
            type="button"
            @click="resetForm"
            class="btn-secondary"
            :disabled="saving"
          >
            Reset
          </button>
          <button
            type="submit"
            class="btn-primary"
            :disabled="saving"
          >
            <span v-if="saving">Saving...</span>
            <span v-else>Save Changes</span>
          </button>
        </div>
      </form>
    </div>

    <!-- Info Box -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
      <h4 class="text-body font-medium text-blue-900 mb-2">About Surplus Income</h4>
      <p class="text-body-sm text-blue-800">
        Surplus income is the difference between your net annual income (after tax and National Insurance)
        and your annual expenditure. This figure is important for calculating your affordability for
        protection policies, investments, and retirement savings. A positive surplus income indicates
        you have disposable income available for savings and investments.
      </p>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch, onMounted } from 'vue';
import { useStore } from 'vuex';

export default {
  name: 'ExpenditureOverview',

  setup() {
    const store = useStore();

    const formData = ref({
      monthly_expenditure: 0,
      annual_expenditure: 0,
    });

    const saving = ref(false);
    const error = ref(null);
    const successMessage = ref(null);

    const profile = computed(() => store.getters['userProfile/profile']);
    const expenditure = computed(() => profile.value?.expenditure || {});
    const incomeOccupation = computed(() => profile.value?.income_occupation || {});

    const netAnnualIncome = computed(() => incomeOccupation.value?.net_income || 0);
    const monthlyExpenditure = computed(() => formData.value.monthly_expenditure || 0);
    const annualExpenditure = computed(() => formData.value.annual_expenditure || 0);

    const surplusIncome = computed(() => netAnnualIncome.value - annualExpenditure.value);

    const surplusIncomeClass = computed(() => {
      if (surplusIncome.value > 0) {
        return 'bg-gradient-to-r from-success-50 to-success-100';
      } else if (surplusIncome.value < 0) {
        return 'bg-gradient-to-r from-error-50 to-error-100';
      }
      return 'bg-gradient-to-r from-gray-50 to-gray-100';
    });

    const surplusIncomeTextClass = computed(() => {
      if (surplusIncome.value > 0) {
        return 'text-success-700';
      } else if (surplusIncome.value < 0) {
        return 'text-error-700';
      }
      return 'text-gray-700';
    });

    const calculateAnnualExpenditure = () => {
      if (formData.value.monthly_expenditure) {
        formData.value.annual_expenditure = Math.round(formData.value.monthly_expenditure * 12 * 100) / 100;
      }
    };

    const calculateMonthlyExpenditure = () => {
      if (formData.value.annual_expenditure) {
        formData.value.monthly_expenditure = Math.round((formData.value.annual_expenditure / 12) * 100) / 100;
      }
    };

    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(amount || 0);
    };

    const handleSubmit = async () => {
      saving.value = true;
      error.value = null;
      successMessage.value = null;

      try {
        await store.dispatch('userProfile/updateExpenditure', {
          monthly_expenditure: formData.value.monthly_expenditure,
          annual_expenditure: formData.value.annual_expenditure,
        });

        successMessage.value = 'Expenditure updated successfully';

        // Clear success message after 3 seconds
        setTimeout(() => {
          successMessage.value = null;
        }, 3000);
      } catch (err) {
        error.value = err.response?.data?.message || 'Failed to update expenditure. Please try again.';
      } finally {
        saving.value = false;
      }
    };

    const resetForm = () => {
      formData.value = {
        monthly_expenditure: expenditure.value?.monthly_expenditure || 0,
        annual_expenditure: expenditure.value?.annual_expenditure || 0,
      };
      error.value = null;
      successMessage.value = null;
    };

    // Load initial data
    watch(expenditure, (newExpenditure) => {
      if (newExpenditure) {
        formData.value = {
          monthly_expenditure: newExpenditure.monthly_expenditure || 0,
          annual_expenditure: newExpenditure.annual_expenditure || 0,
        };
      }
    }, { immediate: true });

    onMounted(() => {
      // Load profile if not already loaded
      if (!profile.value) {
        store.dispatch('userProfile/fetchProfile');
      }
    });

    return {
      formData,
      saving,
      error,
      successMessage,
      netAnnualIncome,
      monthlyExpenditure,
      annualExpenditure,
      surplusIncome,
      surplusIncomeClass,
      surplusIncomeTextClass,
      calculateAnnualExpenditure,
      calculateMonthlyExpenditure,
      formatCurrency,
      handleSubmit,
      resetForm,
    };
  },
};
</script>
