<template>
  <div class="current-situation">
    <!-- No Protection Notice -->
    <div v-if="hasNoPolicies" class="bg-amber-50 border border-amber-200 rounded-lg p-6 mb-8">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-6 w-6 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3 flex-1">
          <h3 class="text-lg font-medium text-amber-800 mb-2">No Protection Coverage</h3>
          <p class="text-sm text-amber-700 mb-4">
            You currently have no protection policies recorded. Without adequate life insurance and protection coverage, your family may face financial difficulties if something unexpected happens.
          </p>
          <div class="bg-white rounded-lg p-4 border border-amber-300 mb-4">
            <h4 class="text-sm font-semibold text-gray-900 mb-2">Why Protection is Important:</h4>
            <ul class="text-sm text-gray-700 space-y-1 list-disc list-inside">
              <li>Replaces lost income if you're unable to work</li>
              <li>Covers outstanding debts and mortgages</li>
              <li>Provides financial security for dependents</li>
              <li>Protects your family's lifestyle and future plans</li>
            </ul>
          </div>
          <div class="space-y-4">
            <div class="flex gap-3">
              <button
                @click="$router.push('/protection')"
                class="px-4 py-2 bg-amber-600 text-white rounded-md hover:bg-amber-700 transition-colors font-medium text-sm"
              >
                View Gap Analysis →
              </button>
              <button
                @click="$emit('add-policy')"
                class="px-4 py-2 bg-white text-amber-600 border border-amber-600 rounded-md hover:bg-amber-50 transition-colors font-medium text-sm"
              >
                I Have Protection to Add
              </button>
            </div>

            <!-- I Don't Have Protection Checkbox -->
            <div class="flex items-start pt-2 border-t border-amber-200">
              <div class="flex items-center h-5">
                <input
                  id="has_no_policies"
                  v-model="hasNoPoliciesChecked"
                  type="checkbox"
                  class="h-4 w-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500"
                  @change="updateHasNoPoliciesFlag"
                />
              </div>
              <div class="ml-3 text-sm">
                <label for="has_no_policies" class="font-medium text-gray-700 cursor-pointer">
                  I currently have no protection policies
                </label>
                <p class="text-gray-600 text-xs mt-1">
                  Check this box if you don't have any life insurance or protection coverage. This will mark your protection profile as complete, but we strongly recommend considering protection for your family's financial security.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Coverage Summary Cards -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
      <div
        v-for="(coverage, key) in coverageSummary"
        :key="key"
        class="bg-gray-50 rounded-lg p-6 border border-gray-200"
      >
        <h3 class="text-sm font-medium text-gray-600 mb-2">{{ coverage.label }}</h3>
        <p class="text-2xl font-bold text-gray-900 mb-1">
          {{ formatCurrency(coverage.total) }}
        </p>
        <p class="text-xs text-gray-500">
          {{ coverage.policyCount }} {{ coverage.policyCount === 1 ? 'policy' : 'policies' }}
        </p>
      </div>
    </div>

    <!-- Charts Row -->
    <div v-if="!hasNoPolicies" class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <!-- Premium Breakdown Chart -->
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Premium Breakdown</h3>
        <PremiumBreakdownChart :premiums="premiumBreakdown" />
      </div>

      <!-- Coverage Timeline Chart -->
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Coverage Timeline</h3>
        <CoverageTimelineChart :policies="allPolicies" />
      </div>
    </div>

    <!-- Risk Exposure Metrics -->
    <div v-if="!hasNoPolicies" class="bg-white rounded-lg border border-gray-200 p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Risk Exposure</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="text-center">
          <div class="text-3xl font-bold text-blue-600 mb-1">
            {{ formatCurrency(humanCapital) }}
          </div>
          <div class="text-sm text-gray-600">Human Capital</div>
        </div>
        <div class="text-center">
          <div class="text-3xl font-bold text-purple-600 mb-1">
            {{ formatCurrency(totalDebt) }}
          </div>
          <div class="text-sm text-gray-600">Total Debt</div>
        </div>
        <div class="text-center">
          <div class="text-3xl font-bold text-green-600 mb-1">
            {{ formatCurrency(totalCoverage) }}
          </div>
          <div class="text-sm text-gray-600">Total Coverage</div>
        </div>
        <div class="text-center">
          <div
            class="text-3xl font-bold mb-1"
            :class="coverageRatioColor"
          >
            {{ coverageRatio }}%
          </div>
          <div class="text-sm text-gray-600">Coverage Ratio</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex';
import PremiumBreakdownChart from './PremiumBreakdownChart.vue';
import CoverageTimelineChart from './CoverageTimelineChart.vue';
import protectionService from '@/services/protectionService';

export default {
  name: 'CurrentSituation',

  components: {
    PremiumBreakdownChart,
    CoverageTimelineChart,
  },

  data() {
    return {
      hasNoPoliciesChecked: false,
    };
  },

  mounted() {
    // Initialization is now handled by the watcher
  },

  computed: {
    ...mapState('protection', ['policies', 'profile', 'analysis']),
    ...mapGetters('protection', [
      'totalCoverage',
      'premiumBreakdown',
      'allPolicies',
    ]),

    hasNoPolicies() {
      // Check if all policy types have zero policies
      const totalPolicies =
        (this.policies.life?.length || 0) +
        (this.policies.criticalIllness?.length || 0) +
        (this.policies.incomeProtection?.length || 0) +
        (this.policies.disability?.length || 0) +
        (this.policies.sicknessIllness?.length || 0);
      return totalPolicies === 0;
    },

    coverageSummary() {
      return {
        life: {
          label: 'Life Insurance',
          total: this.calculateCoverageByType('life'),
          policyCount: this.policies.life?.length || 0,
        },
        criticalIllness: {
          label: 'Critical Illness',
          total: this.calculateCoverageByType('criticalIllness'),
          policyCount: this.policies.criticalIllness?.length || 0,
        },
        incomeProtection: {
          label: 'Income Protection',
          total: this.calculateCoverageByType('incomeProtection'),
          policyCount: this.policies.incomeProtection?.length || 0,
        },
        disability: {
          label: 'Disability',
          total: this.calculateCoverageByType('disability'),
          policyCount: this.policies.disability?.length || 0,
        },
        sicknessIllness: {
          label: 'Sickness/Illness',
          total: this.calculateCoverageByType('sicknessIllness'),
          policyCount: this.policies.sicknessIllness?.length || 0,
        },
      };
    },

    humanCapital() {
      // Analysis returns needs.human_capital
      return this.analysis?.needs?.human_capital || 0;
    },

    totalDebt() {
      // Analysis returns needs.debt_protection (mortgage + other debts)
      return this.analysis?.needs?.debt_protection || 0;
    },

    coverageRatio() {
      const target = this.humanCapital + this.totalDebt;
      if (target === 0) return 0;
      return Math.round((this.totalCoverage / target) * 100);
    },

    coverageRatioColor() {
      if (this.coverageRatio >= 100) return 'text-green-600';
      if (this.coverageRatio >= 75) return 'text-amber-600';
      return 'text-red-600';
    },
  },

  watch: {
    profile: {
      handler(newProfile) {
        // Sync checkbox state with profile data when it loads or changes
        if (newProfile && typeof newProfile.has_no_policies !== 'undefined') {
          this.hasNoPoliciesChecked = newProfile.has_no_policies;
        }
      },
      immediate: true,
    },
  },

  methods: {
    calculateCoverageByType(type) {
      const policies = this.policies[type] || [];
      return policies.reduce((sum, policy) => {
        // For life and critical illness, use sum_assured
        if (type === 'life' || type === 'criticalIllness') {
          return sum + parseFloat(policy.sum_assured || 0);
        }
        // For income protection, disability, and sickness/illness, use benefit_amount (annualized)
        const benefitAmount = parseFloat(policy.benefit_amount || 0);
        const frequency = policy.benefit_frequency || 'monthly';

        if (frequency === 'monthly') {
          return sum + (benefitAmount * 12);
        } else if (frequency === 'weekly') {
          return sum + (benefitAmount * 52);
        }
        // For lump_sum, just add the benefit amount
        return sum + benefitAmount;
      }, 0);
    },

    async updateHasNoPoliciesFlag() {
      try {

        // Call the API directly using protectionService and WAIT for it to complete
        const response = await protectionService.updateHasNoPolicies(this.hasNoPoliciesChecked);


        // Wait a moment to ensure the database transaction is committed
        await new Promise(resolve => setTimeout(resolve, 500));

        // Force page reload to refresh all completeness calculations
        window.location.reload();
      } catch (error) {
        console.error('Failed to update has_no_policies flag:', error);
        alert('Failed to update profile. Please try again.');
        // Revert checkbox on error
        this.hasNoPoliciesChecked = !this.hasNoPoliciesChecked;
      }
    },

    formatCurrency(value) {
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(value);
    },
  },
};
</script>

<style scoped>
/* Responsive adjustments */
@media (max-width: 640px) {
  .current-situation .grid {
    gap: 1rem;
  }
}
</style>
