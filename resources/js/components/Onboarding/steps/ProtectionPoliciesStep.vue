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
              <div class="flex items-center gap-2 mb-2">
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
        type="button"
        class="btn-secondary w-full md:w-auto"
        @click="showForm = true"
      >
        + Add Protection Policy
      </button>

      <p v-if="policies.length === 0" class="text-body-sm text-gray-500 italic">
        You can skip this step and add protection policies later from your dashboard.
      </p>
    </div>

    <!-- Policy Form Modal -->
    <PolicyFormModal
      v-if="showForm"
      :policy="editingPolicy"
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
        // Combine all policy types into single array
        const allPolicies = [];

        if (response.data?.life_policies) {
          allPolicies.push(...response.data.life_policies.map(p => ({ ...p, policyType: 'life' })));
        }
        if (response.data?.critical_illness_policies) {
          allPolicies.push(...response.data.critical_illness_policies.map(p => ({ ...p, policyType: 'criticalIllness' })));
        }
        if (response.data?.income_protection_policies) {
          allPolicies.push(...response.data.income_protection_policies.map(p => ({ ...p, policyType: 'incomeProtection' })));
        }
        if (response.data?.disability_policies) {
          allPolicies.push(...response.data.disability_policies.map(p => ({ ...p, policyType: 'disability' })));
        }
        if (response.data?.sickness_illness_policies) {
          allPolicies.push(...response.data.sickness_illness_policies.map(p => ({ ...p, policyType: 'sicknessIllness' })));
        }

        policies.value = allPolicies;
      } catch (err) {
        console.error('Failed to load policies', err);
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

    async function handlePolicySaved() {
      closeForm();
      await loadPolicies();
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
      getPolicyTypeLabel,
      editPolicy,
      deletePolicy,
      closeForm,
      handlePolicySaved,
      handleNext,
      handleBack,
      handleSkip,
    };
  },
};
</script>
