<template>
  <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Background overlay -->
    <div
      class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
      @click="handleClose"
    ></div>

    <!-- Modal container -->
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

      <!-- Modal panel -->
      <div
        class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full"
      >
        <!-- Header -->
        <div class="bg-white px-6 pt-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-semibold text-gray-900">
              {{ isEditing ? 'Edit Policy' : 'Add New Policy' }}
            </h3>
            <button
              @click="handleClose"
              class="text-gray-400 hover:text-gray-500 transition-colors"
            >
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="px-6 pb-6">
          <div class="space-y-4 max-h-[calc(100vh-300px)] overflow-y-auto pr-2">
            <!-- Policy Type Selection (only for new policies) -->
            <div v-if="!isEditing">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Policy Type <span class="text-red-500">*</span>
              </label>
              <select
                v-model="formData.policyType"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">Select policy type...</option>
                <option value="life">Life Insurance</option>
                <option value="criticalIllness">Critical Illness</option>
                <option value="incomeProtection">Income Protection</option>
                <option value="disability">Disability</option>
                <option value="sicknessIllness">Sickness/Illness</option>
              </select>
            </div>

            <!-- Provider -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Provider <span class="text-red-500">*</span>
              </label>
              <input
                v-model="formData.provider"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="e.g., Aviva, Legal & General"
              />
            </div>

            <!-- Policy Number -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Policy Number
              </label>
              <input
                v-model="formData.policy_number"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Policy reference number"
              />
            </div>

            <!-- Sum Assured / Benefit Amount -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ coverageLabel }} <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <span class="absolute left-3 top-2.5 text-gray-500">£</span>
                <input
                  v-model.number="formData.coverage_amount"
                  type="number"
                  step="1000"
                  required
                  min="0"
                  class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="0"
                />
              </div>
            </div>

            <!-- Premium Amount -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Premium Amount <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <span class="absolute left-3 top-2.5 text-gray-500">£</span>
                  <input
                    v-model.number="formData.premium_amount"
                    type="number"
                    step="0.01"
                    required
                    min="0"
                    class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="0.00"
                  />
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Frequency <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="formData.premium_frequency"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="monthly">Monthly</option>
                  <option value="annual">Annual</option>
                </select>
              </div>
            </div>

            <!-- Start Date -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Start Date
              </label>
              <input
                v-model="formData.start_date"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            <!-- Term Years (for Life and Critical Illness) -->
            <div v-if="showTermYears">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Policy Term (years)
              </label>
              <input
                v-model.number="formData.term_years"
                type="number"
                min="1"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="e.g., 20"
              />
            </div>

            <!-- Benefit Frequency (for Income-based policies) -->
            <div v-if="showBenefitFrequency">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Benefit Frequency
              </label>
              <select
                v-model="formData.benefit_frequency"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="monthly">Monthly</option>
                <option value="weekly">Weekly</option>
                <option value="lump_sum">Lump Sum</option>
              </select>
            </div>

            <!-- Deferred Period (for Income Protection and Disability) -->
            <div v-if="showDeferredPeriod">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Deferred Period (weeks)
              </label>
              <input
                v-model.number="formData.deferred_period_weeks"
                type="number"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="e.g., 4"
              />
            </div>

            <!-- Benefit Period (for Income-based policies) -->
            <div v-if="showBenefitPeriod">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Benefit Period (months)
              </label>
              <input
                v-model.number="formData.benefit_period_months"
                type="number"
                min="1"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="e.g., 24"
              />
            </div>

            <!-- Coverage Type (for Disability) -->
            <div v-if="showCoverageType">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Coverage Type
              </label>
              <select
                v-model="formData.coverage_type"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="accident_only">Accident Only</option>
                <option value="accident_and_sickness">Accident and Sickness</option>
              </select>
            </div>

            <!-- Notes -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Additional Notes
              </label>
              <textarea
                v-model="formData.notes"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Any additional information about this policy..."
              ></textarea>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="mt-6 flex gap-3">
            <button
              type="submit"
              :disabled="submitting"
              class="flex-1 px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
            >
              {{ submitting ? 'Saving...' : (isEditing ? 'Update Policy' : 'Add Policy') }}
            </button>
            <button
              type="button"
              @click="handleClose"
              class="px-6 py-3 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PolicyFormModal',

  props: {
    policy: {
      type: Object,
      default: null,
    },
    isEditing: {
      type: Boolean,
      default: false,
    },
  },

  data() {
    return {
      submitting: false,
      formData: {
        policyType: '',
        provider: '',
        policy_number: '',
        coverage_amount: 0,
        premium_amount: 0,
        premium_frequency: 'monthly',
        start_date: '',
        term_years: null,
        benefit_frequency: 'monthly',
        deferred_period_weeks: null,
        benefit_period_months: null,
        coverage_type: 'accident_and_sickness',
        notes: '',
      },
    };
  },

  computed: {
    coverageLabel() {
      const type = this.formData.policyType || this.policy?.policy_type;
      if (type === 'life' || type === 'criticalIllness') {
        return 'Sum Assured';
      }
      return 'Benefit Amount';
    },

    showTermYears() {
      const type = this.formData.policyType || this.policy?.policy_type;
      return type === 'life' || type === 'criticalIllness';
    },

    showBenefitFrequency() {
      const type = this.formData.policyType || this.policy?.policy_type;
      return type === 'incomeProtection' || type === 'disability' || type === 'sicknessIllness';
    },

    showDeferredPeriod() {
      const type = this.formData.policyType || this.policy?.policy_type;
      return type === 'incomeProtection' || type === 'disability';
    },

    showBenefitPeriod() {
      const type = this.formData.policyType || this.policy?.policy_type;
      return type === 'incomeProtection' || type === 'disability' || type === 'sicknessIllness';
    },

    showCoverageType() {
      const type = this.formData.policyType || this.policy?.policy_type;
      return type === 'disability';
    },
  },

  mounted() {
    if (this.isEditing && this.policy) {
      this.loadPolicyData();
    }
  },

  methods: {
    loadPolicyData() {
      this.formData = {
        policyType: this.policy.policy_type,
        provider: this.policy.provider || '',
        policy_number: this.policy.policy_number || '',
        coverage_amount: this.policy.sum_assured || this.policy.benefit_amount || 0,
        premium_amount: this.policy.premium_amount || 0,
        premium_frequency: this.policy.premium_frequency || 'monthly',
        start_date: this.policy.start_date || '',
        term_years: this.policy.term_years || null,
        benefit_frequency: this.policy.benefit_frequency || 'monthly',
        deferred_period_weeks: this.policy.deferred_period_weeks || null,
        benefit_period_months: this.policy.benefit_period_months || null,
        coverage_type: this.policy.coverage_type || 'accident_and_sickness',
        notes: this.policy.notes || '',
      };
    },

    async handleSubmit() {
      this.submitting = true;

      try {
        const policyData = this.preparePolicyData();
        this.$emit('save', policyData);
      } catch (error) {
        console.error('Form submission error:', error);
      } finally {
        this.submitting = false;
      }
    },

    preparePolicyData() {
      const type = this.formData.policyType || this.policy?.policy_type;
      const data = {
        policyType: type,
        provider: this.formData.provider,
        policy_number: this.formData.policy_number,
        premium_amount: this.formData.premium_amount,
        premium_frequency: this.formData.premium_frequency === 'annual' ? 'annually' : this.formData.premium_frequency,
      };

      // Add coverage amount with correct field name
      if (type === 'life' || type === 'criticalIllness') {
        data.policy_type = 'term'; // Default to term life insurance
        data.sum_assured = this.formData.coverage_amount;
        data.policy_term_years = this.formData.term_years || 20; // Default to 20 years if not provided
        data.policy_start_date = this.formData.start_date || new Date().toISOString().split('T')[0];
        data.in_trust = false; // Default to false
        data.beneficiaries = this.formData.notes || null;
      } else {
        data.benefit_amount = this.formData.coverage_amount;
        data.benefit_frequency = this.formData.benefit_frequency;
        data.benefit_period_months = this.formData.benefit_period_months;
        data.policy_start_date = this.formData.start_date || new Date().toISOString().split('T')[0];
      }

      // Add deferred period for income protection and disability
      if (type === 'incomeProtection' || type === 'disability') {
        data.deferred_period_weeks = this.formData.deferred_period_weeks || 0;
      }

      // Add coverage type for disability
      if (type === 'disability') {
        data.coverage_type = this.formData.coverage_type;
      }

      return data;
    },

    handleClose() {
      this.$emit('close');
    },
  },
};
</script>

<style scoped>
/* Custom scrollbar for form content */
.overflow-y-auto {
  scrollbar-width: thin;
  scrollbar-color: #CBD5E0 #F7FAFC;
}

.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #F7FAFC;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #CBD5E0;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #A0AEC0;
}
</style>
