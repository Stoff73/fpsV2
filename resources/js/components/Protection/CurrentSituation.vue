<template>
  <div class="current-situation">
    <!-- Coverage Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
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
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
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
    <div class="bg-white rounded-lg border border-gray-200 p-6">
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

export default {
  name: 'CurrentSituation',

  components: {
    PremiumBreakdownChart,
    CoverageTimelineChart,
  },

  computed: {
    ...mapState('protection', ['policies', 'profile', 'analysis']),
    ...mapGetters('protection', [
      'totalCoverage',
      'premiumBreakdown',
      'allPolicies',
    ]),

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
      return this.analysis?.human_capital || 0;
    },

    totalDebt() {
      return this.analysis?.total_debt || 0;
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
