<template>
  <div class="policy-card bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
    <!-- Card Header (Always Visible) -->
    <div
      class="p-4 cursor-pointer"
      @click="isExpanded = !isExpanded"
    >
      <div class="flex items-start justify-between">
        <div class="flex-1">
          <div class="flex items-center gap-3 mb-2">
            <span
              class="px-3 py-1 text-xs font-semibold rounded-full"
              :class="policyTypeBadgeClass"
            >
              {{ policyTypeLabel }}
            </span>
            <span
              v-if="isActive"
              class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded"
            >
              Active
            </span>
            <span
              v-else
              class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded"
            >
              Inactive
            </span>
          </div>

          <h4 class="text-lg font-semibold text-gray-900 mb-1">
            {{ policy.provider || 'Unknown Provider' }}
          </h4>

          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <span class="text-gray-600">{{ coverageLabel }}:</span>
              <span class="font-semibold text-gray-900 ml-1">
                {{ formatCurrency(coverageAmount) }}
              </span>
            </div>
            <div>
              <span class="text-gray-600">Premium:</span>
              <span class="font-semibold text-gray-900 ml-1">
                {{ formatCurrency(policy.premium_amount) }}/{{ policy.premium_frequency || 'month' }}
              </span>
            </div>
          </div>
        </div>

        <button
          class="ml-4 text-gray-400 hover:text-gray-600 transition-colors flex-shrink-0"
          @click.stop="isExpanded = !isExpanded"
        >
          <svg
            class="w-5 h-5 transition-transform duration-200"
            :class="{ 'transform rotate-180': isExpanded }"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M19 9l-7 7-7-7"
            />
          </svg>
        </button>
      </div>
    </div>

    <!-- Expanded Details -->
    <div
      v-if="isExpanded"
      class="px-4 pb-4 border-t border-gray-100"
    >
      <div class="mt-4 space-y-4">
        <!-- Policy Details Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
          <div>
            <span class="text-gray-600">Policy Number:</span>
            <span class="font-medium text-gray-900 ml-1">
              {{ policy.policy_number || 'N/A' }}
            </span>
          </div>

          <div v-if="policy.policy_start_date || policy.start_date">
            <span class="text-gray-600">Start Date:</span>
            <span class="font-medium text-gray-900 ml-1">
              {{ formatDate(policy.policy_start_date || policy.start_date) }}
            </span>
          </div>

          <div v-if="policy.policy_term_years || policy.term_years">
            <span class="text-gray-600">Term:</span>
            <span class="font-medium text-gray-900 ml-1">
              {{ policy.policy_term_years || policy.term_years }} years
            </span>
          </div>

          <div v-if="policy.benefit_period_months">
            <span class="text-gray-600">Benefit Period:</span>
            <span class="font-medium text-gray-900 ml-1">
              {{ policy.benefit_period_months }} months
            </span>
          </div>

          <div v-if="policy.deferred_period_weeks">
            <span class="text-gray-600">Deferred Period:</span>
            <span class="font-medium text-gray-900 ml-1">
              {{ policy.deferred_period_weeks }} weeks
            </span>
          </div>

          <div v-if="policy.coverage_type">
            <span class="text-gray-600">Coverage Type:</span>
            <span class="font-medium text-gray-900 ml-1">
              {{ formatCoverageType(policy.coverage_type) }}
            </span>
          </div>

          <div v-if="policy.benefit_frequency">
            <span class="text-gray-600">Benefit Frequency:</span>
            <span class="font-medium text-gray-900 ml-1">
              {{ formatBenefitFrequency(policy.benefit_frequency) }}
            </span>
          </div>
        </div>

        <!-- Additional Details -->
        <div v-if="policy.notes" class="p-3 bg-gray-50 rounded-lg">
          <p class="text-sm text-gray-700">
            <span class="font-medium">Notes:</span> {{ policy.notes }}
          </p>
        </div>

        <!-- Conditions Covered (for Sickness/Illness) -->
        <div v-if="policy.conditions_covered" class="p-3 bg-blue-50 rounded-lg">
          <p class="text-sm font-medium text-gray-700 mb-1">Conditions Covered:</p>
          <div class="flex flex-wrap gap-2">
            <span
              v-for="(condition, index) in parseConditions(policy.conditions_covered)"
              :key="index"
              class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded"
            >
              {{ condition }}
            </span>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3 pt-2">
          <button
            @click="handleEdit"
            class="flex-1 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-2"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-4 w-4"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
              />
            </svg>
            Edit
          </button>
          <button
            @click="handleDelete"
            class="px-4 py-2 bg-red-50 text-red-600 text-sm font-medium rounded-lg hover:bg-red-100 transition-colors flex items-center justify-center gap-2"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-4 w-4"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
              />
            </svg>
            Delete
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PolicyCard',

  props: {
    policy: {
      type: Object,
      required: true,
    },
  },

  data() {
    return {
      isExpanded: false,
    };
  },

  computed: {
    policyTypeLabel() {
      const labels = {
        life: 'Life Insurance',
        criticalIllness: 'Critical Illness',
        incomeProtection: 'Income Protection',
        disability: 'Disability',
        sicknessIllness: 'Sickness/Illness',
      };
      return labels[this.policy.policy_type] || 'Policy';
    },

    policyTypeBadgeClass() {
      const classes = {
        life: 'bg-blue-100 text-blue-800',
        criticalIllness: 'bg-purple-100 text-purple-800',
        incomeProtection: 'bg-green-100 text-green-800',
        disability: 'bg-amber-100 text-amber-800',
        sicknessIllness: 'bg-red-100 text-red-800',
      };
      return classes[this.policy.policy_type] || 'bg-gray-100 text-gray-800';
    },

    coverageLabel() {
      const type = this.policy.policy_type;
      if (type === 'life' || type === 'criticalIllness') {
        return 'Sum Assured';
      }
      return 'Benefit Amount';
    },

    coverageAmount() {
      return this.policy.sum_assured || this.policy.benefit_amount || 0;
    },

    isActive() {
      // Policy is active if it has a start date and hasn't expired
      const startDateField = this.policy.policy_start_date || this.policy.start_date;
      if (!startDateField) return false;

      const startDate = new Date(startDateField);
      const now = new Date();

      if (startDate > now) return false; // Not started yet

      const termYears = this.policy.policy_term_years || this.policy.term_years;
      if (termYears) {
        const endDate = new Date(startDate);
        endDate.setFullYear(endDate.getFullYear() + termYears);
        return endDate > now;
      }

      if (this.policy.benefit_period_months) {
        const endDate = new Date(startDate);
        endDate.setMonth(endDate.getMonth() + this.policy.benefit_period_months);
        return endDate > now;
      }

      return true; // No end date specified, assume active
    },
  },

  methods: {
    handleEdit() {
      this.$emit('edit', this.policy);
    },

    handleDelete() {
      this.$emit('delete', this.policy);
    },

    formatCurrency(value) {
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }).format(value || 0);
    },

    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('en-GB');
    },

    formatCoverageType(type) {
      return type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    },

    formatBenefitFrequency(frequency) {
      const map = {
        monthly: 'Monthly',
        weekly: 'Weekly',
        lump_sum: 'Lump Sum',
      };
      return map[frequency] || frequency;
    },

    parseConditions(conditions) {
      if (Array.isArray(conditions)) {
        return conditions;
      }
      try {
        return JSON.parse(conditions);
      } catch {
        return [];
      }
    },
  },
};
</script>

<style scoped>
.policy-card {
  transition: all 0.2s ease;
}

@media (max-width: 640px) {
  .policy-card .flex.gap-3 {
    flex-direction: column;
  }

  .policy-card .flex-1 {
    width: 100%;
  }
}
</style>
