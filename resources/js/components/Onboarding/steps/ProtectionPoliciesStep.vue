<template>
  <OnboardingStep
    title="Protection Policies"
    description="Add details about your life insurance and protection coverage"
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
        Protection policies provide financial security for you and your family. Adding your existing coverage helps us analyze any gaps in your protection.
      </p>

      <!-- Policy Form -->
      <div v-if="showForm" class="border border-gray-200 rounded-lg p-4 bg-white">
        <h4 class="text-body font-medium text-gray-900 mb-4">
          {{ editingIndex !== null ? 'Edit Policy' : 'Add Protection Policy' }}
        </h4>

        <div class="grid grid-cols-1 gap-4">
          <div>
            <label for="policy_type" class="label">
              Policy Type <span class="text-red-500">*</span>
            </label>
            <select
              id="policy_type"
              v-model="currentPolicy.policyType"
              class="input-field"
              required
            >
              <option value="">Select policy type</option>
              <option value="life">Life Insurance</option>
              <option value="criticalIllness">Critical Illness</option>
              <option value="incomeProtection">Income Protection</option>
              <option value="disability">Disability</option>
              <option value="sicknessIllness">Sickness/Illness</option>
            </select>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="provider" class="label">
                Provider <span class="text-red-500">*</span>
              </label>
              <input
                id="provider"
                v-model="currentPolicy.provider"
                type="text"
                class="input-field"
                placeholder="e.g., Aviva, Legal & General"
                required
              >
            </div>

            <div>
              <label for="policy_number" class="label">
                Policy Number
              </label>
              <input
                id="policy_number"
                v-model="currentPolicy.policy_number"
                type="text"
                class="input-field"
                placeholder="Policy reference"
              >
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="coverage_amount" class="label">
                Sum Assured / Cover Amount <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
                <input
                  id="coverage_amount"
                  v-model.number="currentPolicy.coverage_amount"
                  type="number"
                  min="0"
                  step="1000"
                  class="input-field pl-8"
                  placeholder="0"
                  required
                >
              </div>
            </div>

            <div>
              <label for="premium_amount" class="label">
                Premium Amount <span class="text-red-500">*</span>
              </label>
              <div class="flex gap-2">
                <div class="relative flex-1">
                  <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
                  <input
                    id="premium_amount"
                    v-model.number="currentPolicy.premium_amount"
                    type="number"
                    min="0"
                    step="0.01"
                    class="input-field pl-8"
                    placeholder="0.00"
                    required
                  >
                </div>
                <select
                  v-model="currentPolicy.premium_frequency"
                  class="input-field"
                  style="max-width: 120px"
                >
                  <option value="monthly">Monthly</option>
                  <option value="annual">Annual</option>
                </select>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="start_date" class="label">
                Start Date
              </label>
              <input
                id="start_date"
                v-model="currentPolicy.start_date"
                type="date"
                class="input-field"
              >
            </div>

            <div>
              <label for="end_date" class="label">
                End Date / Expiry
              </label>
              <input
                id="end_date"
                v-model="currentPolicy.end_date"
                type="date"
                class="input-field"
              >
            </div>
          </div>

          <div v-if="currentPolicy.policyType === 'incomeProtection' || currentPolicy.policyType === 'disability'">
            <label for="waiting_period" class="label">
              Waiting Period (weeks)
            </label>
            <input
              id="waiting_period"
              v-model.number="currentPolicy.waiting_period_weeks"
              type="number"
              min="0"
              class="input-field"
              placeholder="e.g., 4, 13, 26"
            >
          </div>

          <div v-if="currentPolicy.policyType === 'incomeProtection'">
            <label for="benefit_period" class="label">
              Benefit Period (months)
            </label>
            <input
              id="benefit_period"
              v-model.number="currentPolicy.benefit_period_months"
              type="number"
              min="0"
              class="input-field"
              placeholder="e.g., 12, 24, until retirement"
            >
          </div>
        </div>

        <div class="flex gap-3 mt-4">
          <button
            type="button"
            class="btn-primary"
            @click="savePolicy"
          >
            {{ editingIndex !== null ? 'Update Policy' : 'Add Policy' }}
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

      <!-- Added Policies List -->
      <div v-if="policies.length > 0" class="space-y-3">
        <h4 class="text-body font-medium text-gray-900">
          Policies ({{ policies.length }})
        </h4>

        <div
          v-for="(policy, index) in policies"
          :key="index"
          class="border border-gray-200 rounded-lg p-4 bg-gray-50"
        >
          <div class="flex justify-between items-start">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <h5 class="text-body font-medium text-gray-900">
                  {{ getPolicyTypeLabel(policy.policyType) }}
                </h5>
                <span class="text-body-sm px-2 py-0.5 bg-blue-100 text-blue-700 rounded">
                  {{ policy.provider }}
                </span>
              </div>
              <div class="mt-2 grid grid-cols-2 md:grid-cols-3 gap-2">
                <div>
                  <p class="text-body-sm text-gray-500">Sum Assured</p>
                  <p class="text-body font-medium text-gray-900">£{{ policy.coverage_amount.toLocaleString() }}</p>
                </div>
                <div>
                  <p class="text-body-sm text-gray-500">Premium</p>
                  <p class="text-body font-medium text-gray-900">
                    £{{ policy.premium_amount.toLocaleString() }} {{ policy.premium_frequency === 'monthly' ? 'pm' : 'pa' }}
                  </p>
                </div>
                <div v-if="policy.policy_number">
                  <p class="text-body-sm text-gray-500">Policy Number</p>
                  <p class="text-body text-gray-900">{{ policy.policy_number }}</p>
                </div>
              </div>
            </div>
            <div class="flex gap-2 ml-4">
              <button
                type="button"
                class="text-primary-600 hover:text-primary-700 text-body-sm"
                @click="editPolicy(index)"
              >
                Edit
              </button>
              <button
                type="button"
                class="text-red-600 hover:text-red-700 text-body-sm"
                @click="removePolicy(index)"
              >
                Remove
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Add Policy Button -->
      <div v-if="!showForm">
        <button
          type="button"
          class="btn-secondary w-full md:w-auto"
          @click="showAddForm"
        >
          + Add Protection Policy
        </button>
      </div>

      <p v-if="policies.length === 0" class="text-body-sm text-gray-500 italic">
        You can skip this step and add protection policies later from your dashboard.
      </p>
    </div>
  </OnboardingStep>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useStore } from 'vuex';
import OnboardingStep from '../OnboardingStep.vue';

export default {
  name: 'ProtectionPoliciesStep',

  components: {
    OnboardingStep,
  },

  emits: ['next', 'back', 'skip'],

  setup(props, { emit }) {
    const store = useStore();

    const policies = ref([]);
    const showForm = ref(false);
    const editingIndex = ref(null);
    const currentPolicy = ref({
      policyType: '',
      provider: '',
      policy_number: '',
      coverage_amount: 0,
      premium_amount: 0,
      premium_frequency: 'monthly',
      start_date: '',
      end_date: '',
      waiting_period_weeks: null,
      benefit_period_months: null,
    });

    const loading = ref(false);
    const error = ref(null);

    const getPolicyTypeLabel = (type) => {
      const labels = {
        life: 'Life Insurance',
        criticalIllness: 'Critical Illness',
        incomeProtection: 'Income Protection',
        disability: 'Disability',
        sicknessIllness: 'Sickness/Illness',
      };
      return labels[type] || type;
    };

    const showAddForm = () => {
      showForm.value = true;
      editingIndex.value = null;
      resetCurrentPolicy();
    };

    const resetCurrentPolicy = () => {
      currentPolicy.value = {
        policyType: '',
        provider: '',
        policy_number: '',
        coverage_amount: 0,
        premium_amount: 0,
        premium_frequency: 'monthly',
        start_date: '',
        end_date: '',
        waiting_period_weeks: null,
        benefit_period_months: null,
      };
    };

    const savePolicy = () => {
      // Validation
      if (
        !currentPolicy.value.policyType ||
        !currentPolicy.value.provider ||
        !currentPolicy.value.coverage_amount ||
        !currentPolicy.value.premium_amount
      ) {
        error.value = 'Please fill in all required fields';
        return;
      }

      error.value = null;

      if (editingIndex.value !== null) {
        // Update existing policy
        policies.value[editingIndex.value] = { ...currentPolicy.value };
      } else {
        // Add new policy
        policies.value.push({ ...currentPolicy.value });
      }

      showForm.value = false;
      resetCurrentPolicy();
    };

    const editPolicy = (index) => {
      editingIndex.value = index;
      currentPolicy.value = { ...policies.value[index] };
      showForm.value = true;
    };

    const removePolicy = (index) => {
      if (confirm('Are you sure you want to remove this policy?')) {
        policies.value.splice(index, 1);
      }
    };

    const cancelForm = () => {
      showForm.value = false;
      editingIndex.value = null;
      resetCurrentPolicy();
      error.value = null;
    };

    const handleNext = async () => {
      loading.value = true;
      error.value = null;

      try {
        await store.dispatch('onboarding/saveStepData', {
          stepName: 'protection_policies',
          data: {
            policies: policies.value,
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
      emit('skip', 'protection_policies');
    };

    onMounted(async () => {
      const existingData = await store.dispatch('onboarding/fetchStepData', 'protection_policies');
      if (existingData && existingData.policies && Array.isArray(existingData.policies)) {
        policies.value = existingData.policies;
      }
    });

    return {
      policies,
      showForm,
      editingIndex,
      currentPolicy,
      loading,
      error,
      getPolicyTypeLabel,
      showAddForm,
      savePolicy,
      editPolicy,
      removePolicy,
      cancelForm,
      handleNext,
      handleBack,
      handleSkip,
    };
  },
};
</script>
