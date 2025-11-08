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

      <!-- Added Liabilities List -->
      <div v-if="liabilities.length > 0" class="space-y-3">
        <h4 class="text-body font-medium text-gray-900">
          Liabilities ({{ liabilities.length }})
        </h4>

        <div
          v-for="liability in liabilities"
          :key="liability.id"
          class="border border-gray-200 rounded-lg p-4 bg-gray-50"
        >
          <div class="flex justify-between items-start">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <h5 class="text-body font-medium text-gray-900 capitalize">
                  {{ liability.liability_type?.replace(/_/g, ' ') }}
                </h5>
              </div>
              <p class="text-body-sm text-gray-600">{{ liability.liability_name }}</p>
              <div class="mt-2">
                <p class="text-body-sm text-gray-500">Balance</p>
                <p class="text-body font-medium text-gray-900">£{{ liability.current_balance?.toLocaleString() }}</p>
              </div>
            </div>
            <div class="flex gap-2 ml-4">
              <button
                type="button"
                class="text-primary-600 hover:text-primary-700 text-body-sm"
                @click="editLiability(liability)"
              >
                Edit
              </button>
              <button
                type="button"
                class="text-red-600 hover:text-red-700 text-body-sm"
                @click="deleteLiability(liability.id)"
              >
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Liability Form (Inline) -->
      <div v-if="showForm" class="border border-gray-200 rounded-lg p-4 bg-white">
        <h4 class="text-body font-medium text-gray-900 mb-4">
          {{ editingLiability !== null ? 'Edit Liability' : 'Add Liability' }}
        </h4>

        <div class="grid grid-cols-1 gap-4">
          <div>
            <label for="liability_type" class="label">
              Liability Type <span class="text-red-500">*</span>
            </label>
            <select
              id="liability_type"
              v-model="currentLiability.liability_type"
              class="input-field"
              required
            >
              <option value="">Select type</option>
              <option value="personal_loan">Personal Loan</option>
              <option value="hire_purchase">Car Finance / Hire Purchase</option>
              <option value="credit_card">Credit Card</option>
              <option value="student_loan">Student Loan</option>
              <option value="secured_loan">Secured Loan</option>
              <option value="overdraft">Bank Overdraft</option>
              <option value="other">Other</option>
            </select>
          </div>

          <div>
            <label for="liability_name" class="label">
              Liability Name / Description <span class="text-red-500">*</span>
            </label>
            <input
              id="liability_name"
              v-model="currentLiability.liability_name"
              type="text"
              class="input-field"
              placeholder="e.g., Car loan, Credit card"
              required
            >
          </div>

          <div>
            <label for="current_balance" class="label">
              Outstanding Balance <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
              <input
                id="current_balance"
                v-model.number="currentLiability.current_balance"
                type="number"
                min="0"
                step="0.01"
                class="input-field pl-8"
                placeholder="0"
                required
              >
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
                  step="0.01"
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
                  step="0.01"
                  class="input-field pr-8"
                  placeholder="0.0"
                >
                <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500">%</span>
              </div>
            </div>
          </div>

          <div>
            <label for="notes" class="label">
              Notes
            </label>
            <textarea
              id="notes"
              v-model="currentLiability.notes"
              rows="2"
              class="input-field"
              placeholder="Any additional information..."
            ></textarea>
          </div>
        </div>

        <div class="flex gap-3 mt-4">
          <button
            type="button"
            class="btn-primary"
            @click="saveLiability"
          >
            {{ editingLiability !== null ? 'Update Liability' : 'Add Liability' }}
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
import OnboardingStep from '../OnboardingStep.vue';
import estateService from '@/services/estateService';

export default {
  name: 'LiabilitiesStep',

  components: {
    OnboardingStep,
  },

  emits: ['next', 'back', 'skip'],

  setup(props, { emit }) {
    const liabilities = ref([]);
    const showForm = ref(false);
    const editingLiability = ref(null);
    const currentLiability = ref({
      liability_type: '',
      liability_name: '',
      current_balance: 0,
      monthly_payment: 0,
      interest_rate: 0,
      notes: '',
    });

    const loading = ref(false);
    const error = ref(null);

    onMounted(async () => {
      await loadLiabilities();
    });

    async function loadLiabilities() {
      try {
        const response = await estateService.getEstateData();
        liabilities.value = response.data?.liabilities || [];
      } catch (err) {
        console.error('Failed to load liabilities', err);
      }
    }

    const showAddForm = () => {
      showForm.value = true;
      editingLiability.value = null;
      resetCurrentLiability();
    };

    const resetCurrentLiability = () => {
      currentLiability.value = {
        liability_type: '',
        liability_name: '',
        current_balance: 0,
        monthly_payment: 0,
        interest_rate: 0,
        notes: '',
      };
    };

    const saveLiability = async () => {
      // Validation
      if (
        !currentLiability.value.liability_type ||
        !currentLiability.value.liability_name ||
        !currentLiability.value.current_balance
      ) {
        error.value = 'Please fill in all required fields';
        return;
      }

      error.value = null;

      try {
        if (editingLiability.value !== null) {
          // Update existing liability
          await estateService.updateLiability(editingLiability.value.id, currentLiability.value);
        } else {
          // Create new liability
          await estateService.createLiability(currentLiability.value);
        }

        showForm.value = false;
        resetCurrentLiability();
        await loadLiabilities();
      } catch (err) {
        error.value = 'Failed to save liability';
      }
    };

    const editLiability = (liability) => {
      editingLiability.value = liability;
      currentLiability.value = { ...liability };
      showForm.value = true;
    };

    const deleteLiability = async (id) => {
      if (confirm('Are you sure you want to remove this liability?')) {
        try {
          await estateService.deleteLiability(id);
          await loadLiabilities();
        } catch (err) {
          error.value = 'Failed to delete liability';
        }
      }
    };

    const cancelForm = () => {
      showForm.value = false;
      editingLiability.value = null;
      resetCurrentLiability();
      error.value = null;
    };

    const handleNext = () => {
      emit('next');
    };

    const handleBack = () => {
      emit('back');
    };

    const handleSkip = () => {
      emit('skip', 'liabilities');
    };

    return {
      liabilities,
      showForm,
      editingLiability,
      currentLiability,
      loading,
      error,
      showAddForm,
      saveLiability,
      editLiability,
      deleteLiability,
      cancelForm,
      handleNext,
      handleBack,
      handleSkip,
    };
  },
};
</script>
