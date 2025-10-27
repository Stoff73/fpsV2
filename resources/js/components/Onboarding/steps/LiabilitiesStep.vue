<template>
  <OnboardingStep
    title="Other Liabilities"
    description="Add details about loans, credit cards, and other debts"
    :can-go-back="true"
    :can-skip="true"
    :loading="loading"
    :error="error"
    @next="handleNext"
    @back="handleBack"
    @skip="handleSkip"
  >
    <div class="space-y-6">
      <p class="text-body-sm text-gray-600 bg-gray-50 p-3 rounded-lg">
        Liabilities reduce your taxable estate. We've already captured mortgages with your properties - here you can add other debts like personal loans, car finance, or credit cards.
      </p>

      <!-- Liability Form -->
      <div v-if="showForm" class="border border-gray-200 rounded-lg p-4 bg-white">
        <h4 class="text-body font-medium text-gray-900 mb-4">
          {{ editingIndex !== null ? 'Edit Liability' : 'Add Liability' }}
        </h4>

        <div class="grid grid-cols-1 gap-4">
          <div>
            <label for="liability_type" class="label">
              Liability Type <span class="text-red-500">*</span>
            </label>
            <select
              id="liability_type"
              v-model="currentLiability.type"
              class="input-field"
              required
            >
              <option value="">Select type</option>
              <option value="personal_loan">Personal Loan</option>
              <option value="hire_purchase">Car Finance / Hire Purchase</option>
              <option value="credit_card">Credit Card</option>
              <option value="student_loan">Student Loan</option>
              <option value="other">Other</option>
            </select>
          </div>

          <!-- Country Selector -->
          <div>
            <CountrySelector
              v-model="currentLiability.country"
              label="Country"
              :required="true"
              default-country="United Kingdom"
            />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="lender" class="label">
                Lender / Provider <span class="text-red-500">*</span>
              </label>
              <input
                id="lender"
                v-model="currentLiability.lender"
                type="text"
                class="input-field"
                placeholder="e.g., Barclays, HSBC"
                required
              >
            </div>

            <div>
              <label for="outstanding_balance" class="label">
                Outstanding Balance <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
                <input
                  id="outstanding_balance"
                  v-model.number="currentLiability.outstanding_balance"
                  type="number"
                  min="0"
                  step="100"
                  class="input-field pl-8"
                  placeholder="0"
                  required
                >
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="monthly_payment" class="label">
                Monthly Payment
              </label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
                <input
                  id="monthly_payment"
                  v-model.number="currentLiability.monthly_payment"
                  type="number"
                  min="0"
                  step="10"
                  class="input-field pl-8"
                  placeholder="0"
                >
              </div>
            </div>

            <div>
              <label for="interest_rate" class="label">
                Interest Rate (%)
              </label>
              <div class="relative">
                <input
                  id="interest_rate"
                  v-model.number="currentLiability.interest_rate"
                  type="number"
                  min="0"
                  max="100"
                  step="0.1"
                  class="input-field pr-8"
                  placeholder="0.0"
                >
                <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500">%</span>
              </div>
            </div>
          </div>

          <div>
            <label for="purpose" class="label">
              Purpose / Notes
            </label>
            <textarea
              id="purpose"
              v-model="currentLiability.purpose"
              rows="2"
              class="input-field"
              placeholder="e.g., Car purchase, home improvements"
            ></textarea>
          </div>
        </div>

        <div class="flex gap-3 mt-4">
          <button
            type="button"
            class="btn-primary"
            @click="saveLiability"
          >
            {{ editingIndex !== null ? 'Update Liability' : 'Add Liability' }}
          </button>
          <button
            type="button"
            class="btn-secondary"
            @click="cancelForm"
          >
            Cancel
          </button>
        </div>
      </div>

      <!-- Added Liabilities List -->
      <div v-if="liabilities.length > 0" class="space-y-3">
        <h4 class="text-body font-medium text-gray-900">
          Liabilities ({{ liabilities.length }})
        </h4>

        <div
          v-for="(liability, index) in liabilities"
          :key="index"
          class="border border-gray-200 rounded-lg p-4 bg-gray-50"
        >
          <div class="flex justify-between items-start">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <h5 class="text-body font-medium text-gray-900 capitalize">
                  {{ liability.type.replace(/_/g, ' ') }}
                </h5>
              </div>
              <p class="text-body-sm text-gray-600">{{ liability.lender }}</p>
              <div class="mt-2 grid grid-cols-2 md:grid-cols-3 gap-2">
                <div>
                  <p class="text-body-sm text-gray-500">Balance</p>
                  <p class="text-body font-medium text-gray-900">£{{ liability.outstanding_balance.toLocaleString() }}</p>
                </div>
                <div v-if="liability.monthly_payment > 0">
                  <p class="text-body-sm text-gray-500">Monthly Payment</p>
                  <p class="text-body font-medium text-gray-900">£{{ liability.monthly_payment.toLocaleString() }}</p>
                </div>
                <div v-if="liability.interest_rate > 0">
                  <p class="text-body-sm text-gray-500">Interest Rate</p>
                  <p class="text-body font-medium text-gray-900">{{ liability.interest_rate }}%</p>
                </div>
              </div>
              <p v-if="liability.purpose" class="mt-2 text-body-sm text-gray-600 italic">{{ liability.purpose }}</p>
            </div>
            <div class="flex gap-2 ml-4">
              <button
                type="button"
                class="text-primary-600 hover:text-primary-700 text-body-sm"
                @click="editLiability(index)"
              >
                Edit
              </button>
              <button
                type="button"
                class="text-red-600 hover:text-red-700 text-body-sm"
                @click="removeLiability(index)"
              >
                Remove
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Add Liability Button -->
      <div v-if="!showForm">
        <button
          type="button"
          class="btn-secondary w-full md:w-auto"
          @click="showAddForm"
        >
          + Add Liability
        </button>
      </div>

      <p v-if="liabilities.length === 0" class="text-body-sm text-gray-500 italic">
        You can skip this step if you don't have any loans or credit card debt.
      </p>
    </div>
  </OnboardingStep>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useStore } from 'vuex';
import OnboardingStep from '../OnboardingStep.vue';
import CountrySelector from '@/components/Shared/CountrySelector.vue';

export default {
  name: 'LiabilitiesStep',

  components: {
    OnboardingStep,
    CountrySelector,
  },

  emits: ['next', 'back', 'skip'],

  setup(props, { emit }) {
    const store = useStore();

    const liabilities = ref([]);
    const showForm = ref(false);
    const editingIndex = ref(null);
    const currentLiability = ref({
      type: '',
      lender: '',
      country: 'United Kingdom',
      outstanding_balance: 0,
      monthly_payment: 0,
      interest_rate: 0,
      purpose: '',
    });

    const loading = ref(false);
    const error = ref(null);

    const showAddForm = () => {
      showForm.value = true;
      editingIndex.value = null;
      resetCurrentLiability();
    };

    const resetCurrentLiability = () => {
      currentLiability.value = {
        type: '',
        lender: '',
        country: 'United Kingdom',
        outstanding_balance: 0,
        monthly_payment: 0,
        interest_rate: 0,
        purpose: '',
      };
    };

    const saveLiability = () => {
      // Validation
      if (
        !currentLiability.value.type ||
        !currentLiability.value.lender ||
        !currentLiability.value.outstanding_balance
      ) {
        error.value = 'Please fill in all required fields';
        return;
      }

      error.value = null;

      if (editingIndex.value !== null) {
        // Update existing liability
        liabilities.value[editingIndex.value] = { ...currentLiability.value };
      } else {
        // Add new liability
        liabilities.value.push({ ...currentLiability.value });
      }

      showForm.value = false;
      resetCurrentLiability();
    };

    const editLiability = (index) => {
      editingIndex.value = index;
      currentLiability.value = { ...liabilities.value[index] };
      showForm.value = true;
    };

    const removeLiability = (index) => {
      if (confirm('Are you sure you want to remove this liability?')) {
        liabilities.value.splice(index, 1);
      }
    };

    const cancelForm = () => {
      showForm.value = false;
      editingIndex.value = null;
      resetCurrentLiability();
      error.value = null;
    };

    const handleNext = async () => {
      loading.value = true;
      error.value = null;

      try {
        await store.dispatch('onboarding/saveStepData', {
          stepName: 'liabilities',
          data: {
            liabilities: liabilities.value,
          },
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

    const handleSkip = () => {
      emit('skip', 'liabilities');
    };

    onMounted(async () => {
      const existingData = await store.dispatch('onboarding/fetchStepData', 'liabilities');
      if (existingData && existingData.liabilities && Array.isArray(existingData.liabilities)) {
        liabilities.value = existingData.liabilities;
      }
    });

    return {
      liabilities,
      showForm,
      editingIndex,
      currentLiability,
      loading,
      error,
      showAddForm,
      saveLiability,
      editLiability,
      removeLiability,
      cancelForm,
      handleNext,
      handleBack,
      handleSkip,
    };
  },
};
</script>
