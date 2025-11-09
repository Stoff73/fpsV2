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
        Protection policies provide financial security for you and your family. Adding your existing coverage helps us analyse any gaps in your protection.
      </p>

      <!-- I have no policies checkbox -->
      <div class="border border-gray-200 rounded-lg p-4 bg-blue-50">
        <label class="flex items-start gap-3 cursor-pointer">
          <input
            v-model="hasNoPolicies"
            type="checkbox"
            class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            @change="handleNoPoliciesChange"
          >
          <div>
            <span class="text-body font-medium text-gray-900">
              I have no protection policies in place
            </span>
            <p class="text-body-sm text-gray-600 mt-1">
              Check this if you don't currently have any life insurance, critical illness, income protection, or other protection policies. We'll help you understand what coverage you might need in the Protection module.
            </p>
          </div>
        </label>
      </div>

      <!-- Added Policies List -->
      <div v-if="policies.length > 0" class="space-y-3">
        <h4 class="text-body font-medium text-gray-900">
          Policies ({{ policies.length }})
        </h4>

        <div
          v-for="policy in policies"
          :key="policy.id"
          class="border border-gray-200 rounded-lg p-4 bg-gray-50"
        >
          <div class="flex justify-between items-start">
            <div class="flex-1">
              <div class="flex items-centre gap-2 mb-2">
                <h5 class="text-body font-medium text-gray-900">
                  {{ getPolicyTypeLabel(policy.policyType || policy.policy_type) }}
                </h5>
                <span class="text-body-sm px-2 py-0.5 bg-blue-100 text-blue-700 rounded">
                  {{ policy.provider }}
                </span>
              </div>
              <div class="mt-2 grid grid-cols-2 md:grid-cols-3 gap-2">
                <div>
                  <p class="text-body-sm text-gray-500">Sum Assured</p>
                  <p class="text-body font-medium text-gray-900">£{{ policy.coverage_amount?.toLocaleString() || policy.sum_assured?.toLocaleString() }}</p>
                </div>
                <div>
                  <p class="text-body-sm text-gray-500">Premium</p>
                  <p class="text-body font-medium text-gray-900">
                    £{{ policy.premium_amount?.toLocaleString() }} {{ policy.premium_frequency === 'monthly' ? 'pm' : 'pa' }}
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
                @click="editPolicy(policy)"
              >
                Edit
              </button>
              <button
                type="button"
                class="text-red-600 hover:text-red-700 text-body-sm"
                @click="deletePolicy(policy)"
              >
                Remove
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Add Policy Button -->
      <button
        v-if="!hasNoPolicies"
        type="button"
        class="btn-secondary w-full md:w-auto"
        @click="showForm = true"
      >
        + Add Protection Policy
      </button>

      <p v-if="policies.length === 0 && !hasNoPolicies" class="text-body-sm text-gray-500 italic">
        You can skip this step and add protection policies later from your dashboard.
      </p>

      <p v-if="hasNoPolicies" class="text-body-sm text-green-700 bg-green-50 p-3 rounded-lg">
        You've indicated you have no protection policies. The Protection module will help you understand your protection needs and recommend suitable coverage.
      </p>
    </div>

    <!-- Policy Form Modal -->
    <PolicyFormModal
      v-if="showForm"
      :policy="editingPolicy"
      :is-editing="!!editingPolicy"
      @close="closeForm"
      @save="handlePolicySaved"
    />
  </OnboardingStep>
</template>

<script>
import { ref, onMounted } from 'vue';
import OnboardingStep from '../OnboardingStep.vue';
import PolicyFormModal from '@/components/Protection/PolicyFormModal.vue';
import protectionService from '@/services/protectionService';

export default {
  name: 'ProtectionPoliciesStep',

  components: {
    OnboardingStep,
    PolicyFormModal,
  },

  emits: ['next', 'back', 'skip'],

  setup(props, { emit }) {
    const policies = ref([]);
    const showForm = ref(false);
    const editingPolicy = ref(null);
    const loading = ref(false);
    const error = ref(null);
    const hasNoPolicies = ref(false);

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

    onMounted(async () => {
      await loadPolicies();
    });

    async function loadPolicies() {
      try {
        const response = await protectionService.getProtectionData();
        console.log('Protection data response:', response);

        // Combine all policy types into single array
        const allPolicies = [];

        // Response structure: response.data.policies contains the policies
        const data = response.data || response;
        const policyData = data.policies || {};

        console.log('Policy data:', policyData);

        // API returns snake_case keys: life_insurance, critical_illness, etc.
        if (policyData?.life_insurance && Array.isArray(policyData.life_insurance)) {
          console.log('Adding life policies:', policyData.life_insurance.length);
          allPolicies.push(...policyData.life_insurance.map(p => ({ ...p, policyType: 'life', policy_type: 'life' })));
        }
        if (policyData?.critical_illness && Array.isArray(policyData.critical_illness)) {
          console.log('Adding CI policies:', policyData.critical_illness.length);
          allPolicies.push(...policyData.critical_illness.map(p => ({ ...p, policyType: 'criticalIllness', policy_type: 'criticalIllness' })));
        }
        if (policyData?.income_protection && Array.isArray(policyData.income_protection)) {
          console.log('Adding IP policies:', policyData.income_protection.length);
          allPolicies.push(...policyData.income_protection.map(p => ({ ...p, policyType: 'incomeProtection', policy_type: 'incomeProtection' })));
        }
        if (policyData?.disability && Array.isArray(policyData.disability)) {
          console.log('Adding disability policies:', policyData.disability.length);
          allPolicies.push(...policyData.disability.map(p => ({ ...p, policyType: 'disability', policy_type: 'disability' })));
        }
        if (policyData?.sickness_illness && Array.isArray(policyData.sickness_illness)) {
          console.log('Adding sickness policies:', policyData.sickness_illness.length);
          allPolicies.push(...policyData.sickness_illness.map(p => ({ ...p, policyType: 'sicknessIllness', policy_type: 'sicknessIllness' })));
        }

        policies.value = allPolicies;
        console.log('Loaded policies:', policies.value);
        console.log('Total policies loaded:', allPolicies.length);

        // Load has_no_policies flag from protection profile
        if (data?.profile) {
          hasNoPolicies.value = data.profile.has_no_policies || false;
        }
      } catch (err) {
        console.error('Failed to load policies', err);
        error.value = 'Failed to load policies';
      }
    }

    async function handleNoPoliciesChange() {
      try {
        loading.value = true;
        await protectionService.updateHasNoPolicies(hasNoPolicies.value);

        // If user checks "no policies", disable adding policies
        if (hasNoPolicies.value) {
          showForm.value = false;
        }
      } catch (err) {
        error.value = 'Failed to update protection preferences';
        console.error('Failed to update has_no_policies:', err);
        // Revert checkbox on error
        hasNoPolicies.value = !hasNoPolicies.value;
      } finally {
        loading.value = false;
      }
    }

    function editPolicy(policy) {
      editingPolicy.value = policy;
      showForm.value = true;
    }

    async function deletePolicy(policy) {
      if (!confirm('Are you sure you want to remove this policy?')) {
        return;
      }

      try {
        const policyType = policy.policyType || policy.policy_type;

        switch (policyType) {
          case 'life':
            await protectionService.deleteLifePolicy(policy.id);
            break;
          case 'criticalIllness':
            await protectionService.deleteCriticalIllnessPolicy(policy.id);
            break;
          case 'incomeProtection':
            await protectionService.deleteIncomeProtectionPolicy(policy.id);
            break;
          case 'disability':
            await protectionService.deleteDisabilityPolicy(policy.id);
            break;
          case 'sicknessIllness':
            await protectionService.deleteSicknessIllnessPolicy(policy.id);
            break;
        }

        await loadPolicies();
      } catch (err) {
        error.value = 'Failed to delete policy';
      }
    }

    function closeForm() {
      showForm.value = false;
      editingPolicy.value = null;
    }

    async function handlePolicySaved(policyData) {
      try {
        console.log('handlePolicySaved called with:', policyData);
        error.value = null;

        const { policyType, ...actualPolicyData } = policyData;
        console.log('Policy type:', policyType);
        console.log('Actual policy data:', actualPolicyData);

        // Call the appropriate API endpoint based on policy type
        switch (policyType) {
          case 'life':
            if (editingPolicy.value) {
              await protectionService.updateLifePolicy(editingPolicy.value.id, actualPolicyData);
            } else {
              await protectionService.createLifePolicy(actualPolicyData);
            }
            break;
          case 'criticalIllness':
            if (editingPolicy.value) {
              await protectionService.updateCriticalIllnessPolicy(editingPolicy.value.id, actualPolicyData);
            } else {
              await protectionService.createCriticalIllnessPolicy(actualPolicyData);
            }
            break;
          case 'incomeProtection':
            if (editingPolicy.value) {
              await protectionService.updateIncomeProtectionPolicy(editingPolicy.value.id, actualPolicyData);
            } else {
              await protectionService.createIncomeProtectionPolicy(actualPolicyData);
            }
            break;
          case 'disability':
            if (editingPolicy.value) {
              await protectionService.updateDisabilityPolicy(editingPolicy.value.id, actualPolicyData);
            } else {
              await protectionService.createDisabilityPolicy(actualPolicyData);
            }
            break;
          case 'sicknessIllness':
            if (editingPolicy.value) {
              await protectionService.updateSicknessIllnessPolicy(editingPolicy.value.id, actualPolicyData);
            } else {
              await protectionService.createSicknessIllnessPolicy(actualPolicyData);
            }
            break;
        }

        console.log('Policy saved successfully, closing form and reloading...');

        // If user adds a policy, automatically uncheck "has_no_policies"
        if (!editingPolicy.value && hasNoPolicies.value) {
          hasNoPolicies.value = false;
          await protectionService.updateHasNoPolicies(false);
        }

        closeForm();
        await loadPolicies();
        console.log('Policies reloaded, should now be visible');
      } catch (err) {
        error.value = 'Failed to save policy';
        console.error('Failed to save policy:', err);
        console.error('Validation errors:', err.response?.data?.errors);
        console.error('Full error:', err.response?.data);
        console.error('Sent data:', policyData);
      }
    }

    const handleNext = () => {
      emit('next');
    };

    const handleBack = () => {
      emit('back');
    };

    const handleSkip = () => {
      emit('skip', 'protection_policies');
    };

    return {
      policies,
      showForm,
      editingPolicy,
      loading,
      error,
      hasNoPolicies,
      getPolicyTypeLabel,
      editPolicy,
      deletePolicy,
      closeForm,
      handlePolicySaved,
      handleNoPoliciesChange,
      handleNext,
      handleBack,
      handleSkip,
    };
  },
};
</script>
