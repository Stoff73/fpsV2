<template>
  <div class="gap-analysis">
    <!-- Empty State -->
    <div v-if="hasNoPolicies" class="bg-white rounded-lg border border-gray-200 p-12 text-center">
      <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
      </svg>
      <h3 class="text-lg font-semibold text-gray-900 mb-2">
        No Protection Policies Added
      </h3>
      <p class="text-gray-600 mb-6 max-w-md mx-auto">
        You haven't added any protection policies yet. Add your life insurance, critical illness, or income protection policies to see your coverage gap analysis.
      </p>
      <button
        @click="$emit('add-policy')"
        class="px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors"
      >
        Add Your First Policy
      </button>
      <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4 text-left max-w-2xl mx-auto">
        <div class="p-4 bg-gray-50 rounded-lg">
          <h4 class="font-medium text-gray-900 mb-2">Life Insurance</h4>
          <p class="text-sm text-gray-600">Protects your family's financial future</p>
        </div>
        <div class="p-4 bg-gray-50 rounded-lg">
          <h4 class="font-medium text-gray-900 mb-2">Critical Illness</h4>
          <p class="text-sm text-gray-600">Provides lump sum if diagnosed with serious illness</p>
        </div>
        <div class="p-4 bg-gray-50 rounded-lg">
          <h4 class="font-medium text-gray-900 mb-2">Income Protection</h4>
          <p class="text-sm text-gray-600">Replaces income if unable to work</p>
        </div>
      </div>
    </div>

    <!-- Content (only show when policies exist) -->
    <div v-else>
      <!-- Coverage Adequacy Gauge -->
      <div class="mb-8">
        <div class="bg-white rounded-lg border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4 text-center">
            Overall Coverage Adequacy
          </h3>
          <CoverageAdequacyGauge :score="adequacyScore" />
          <p class="text-center text-sm text-gray-600 mt-4">
            {{ adequacyMessage }}
          </p>
        </div>
      </div>

      <!-- Coverage Gap by Category -->
      <div class="mb-8">
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Coverage Gaps by Category</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
          <div
            v-for="gap in gapCategories"
            :key="gap.category"
            class="p-4 rounded-lg"
            :class="getGapCardClass(gap.severity)"
          >
            <div class="flex justify-between items-start mb-2">
              <h4 class="font-medium text-gray-900">{{ gap.label }}</h4>
              <span
                class="px-2 py-1 text-xs font-semibold rounded"
                :class="getSeverityBadgeClass(gap.severity)"
              >
                {{ gap.severity }}
              </span>
            </div>
            <div class="text-2xl font-bold text-gray-900 mb-1">
              {{ formatCurrency(gap.amount) }}
            </div>
            <div class="text-xs text-gray-600">
              {{ gap.description }}
            </div>
          </div>
        </div>

        <!-- Heatmap Chart -->
        <CoverageGapChart :gaps="gapData" />
      </div>
    </div>

      <!-- Affordability Assessment -->
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Affordability Assessment</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <p class="text-sm text-gray-600 mb-1">Monthly Income</p>
          <p class="text-xl font-bold text-gray-900">
            {{ formatCurrency(monthlyIncome) }}
          </p>
        </div>
        <div>
          <p class="text-sm text-gray-600 mb-1">Current Premium Spend</p>
          <p class="text-xl font-bold text-gray-900">
            {{ formatCurrency(totalPremium) }}
          </p>
        </div>
        <div>
          <p class="text-sm text-gray-600 mb-1">% of Income</p>
          <p
            class="text-xl font-bold"
            :class="premiumPercentageColor"
          >
            {{ premiumPercentage }}%
          </p>
          <p class="text-xs text-gray-500 mt-1">
            Recommended: 5-10% of gross income
          </p>
        </div>
      </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex';
import CoverageAdequacyGauge from './CoverageAdequacyGauge.vue';
import CoverageGapChart from './CoverageGapChart.vue';

export default {
  name: 'GapAnalysis',

  components: {
    CoverageAdequacyGauge,
    CoverageGapChart,
  },

  computed: {
    ...mapState('protection', ['profile', 'analysis', 'policies']),
    ...mapGetters('protection', ['adequacyScore', 'coverageGaps', 'totalPremium']),

    hasNoPolicies() {
      if (!this.policies) return true;

      const allPolicies = [
        ...(this.policies.life_insurance || []),
        ...(this.policies.critical_illness || []),
        ...(this.policies.income_protection || []),
        ...(this.policies.disability || []),
        ...(this.policies.sickness_illness || []),
      ];

      return allPolicies.length === 0;
    },

    gapCategories() {
      const gaps = this.analysis?.gap_breakdown || {};

      return [
        {
          category: 'human_capital',
          label: 'Human Capital',
          amount: gaps.human_capital || 0,
          severity: this.calculateSeverity(gaps.human_capital || 0),
          description: 'Lost income potential from death or disability',
        },
        {
          category: 'debt',
          label: 'Outstanding Debt',
          amount: gaps.debt || 0,
          severity: this.calculateSeverity(gaps.debt || 0),
          description: 'Mortgage and other debts requiring coverage',
        },
        {
          category: 'mortgage',
          label: 'Mortgage Protection',
          amount: gaps.mortgage || 0,
          severity: this.calculateSeverity(gaps.mortgage || 0),
          description: 'Additional coverage needed for mortgage',
        },
        {
          category: 'education',
          label: 'Education Costs',
          amount: gaps.education || 0,
          severity: this.calculateSeverity(gaps.education || 0),
          description: 'Future education expenses for dependents',
        },
        {
          category: 'final_expenses',
          label: 'Final Expenses',
          amount: gaps.final_expenses || 0,
          severity: this.calculateSeverity(gaps.final_expenses || 0),
          description: 'Funeral and estate settlement costs',
        },
        {
          category: 'income_replacement',
          label: 'Income Replacement',
          amount: gaps.income_replacement || 0,
          severity: this.calculateSeverity(gaps.income_replacement || 0),
          description: 'Coverage for income loss from illness or injury',
        },
      ];
    },

    gapData() {
      // Prepare data for heatmap chart
      return this.analysis?.gap_heatmap || [];
    },

    monthlyIncome() {
      return this.profile?.monthly_gross_income || 0;
    },

    premiumPercentage() {
      if (this.monthlyIncome === 0) return 0;
      return ((this.totalPremium / this.monthlyIncome) * 100).toFixed(1);
    },

    premiumPercentageColor() {
      const percentage = parseFloat(this.premiumPercentage);
      if (percentage <= 10) return 'text-green-600';
      if (percentage <= 15) return 'text-amber-600';
      return 'text-red-600';
    },

    adequacyMessage() {
      const score = this.adequacyScore;
      if (score >= 80) {
        return 'Your protection coverage is adequate for your current needs.';
      } else if (score >= 60) {
        return 'Your protection coverage is moderate. Consider addressing identified gaps.';
      } else {
        return 'Your protection coverage has significant gaps. Immediate attention recommended.';
      }
    },
  },

  methods: {
    calculateSeverity(amount) {
      if (amount === 0) return 'none';
      if (amount < 50000) return 'low';
      if (amount < 150000) return 'medium';
      return 'high';
    },

    getGapCardClass(severity) {
      const classes = {
        none: 'bg-green-50 border border-green-200',
        low: 'bg-blue-50 border border-blue-200',
        medium: 'bg-amber-50 border border-amber-200',
        high: 'bg-red-50 border border-red-200',
      };
      return classes[severity] || 'bg-gray-50 border border-gray-200';
    },

    getSeverityBadgeClass(severity) {
      const classes = {
        none: 'bg-green-100 text-green-800',
        low: 'bg-blue-100 text-blue-800',
        medium: 'bg-amber-100 text-amber-800',
        high: 'bg-red-100 text-red-800',
      };
      return classes[severity] || 'bg-gray-100 text-gray-800';
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
  .gap-analysis .grid {
    gap: 1rem;
  }
}
</style>
