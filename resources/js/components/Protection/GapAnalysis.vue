<template>
  <div class="gap-analysis">
    <!-- No Policies Alert Banner -->
    <div v-if="hasNoPolicies" class="mb-6 bg-amber-50 border-l-4 border-amber-400 p-4">
      <div class="flex items-start">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3 flex-1">
          <h3 class="text-sm font-medium text-amber-800">No Protection Policies Added</h3>
          <div class="mt-2 text-sm text-amber-700">
            <p class="mb-2">
              You haven't added any protection policies yet. The analysis below shows your protection needs based on your current situation.
            </p>
            <button
              @click="$emit('add-policy')"
              class="inline-flex items-center px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-md hover:bg-amber-700 transition-colors"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Add Your First Policy
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Spouse Income Not Included Warning -->
    <div v-if="spousePermissionDenied" class="mb-6 bg-blue-50 border-l-4 border-blue-400 p-4">
      <div class="flex items-start">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3 flex-1">
          <h3 class="text-sm font-medium text-blue-800">Spouse Income Not Included</h3>
          <div class="mt-2 text-sm text-blue-700">
            <p>
              Your spouse's income has not been included in this protection analysis because data sharing permissions have not been granted.
              To get a more accurate household protection assessment, please enable data sharing with your spouse in User Profile settings.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Content (always show) -->
    <div>
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

      <!-- Protection Needs Breakdown -->
      <div class="mb-8">
        <div class="bg-white rounded-lg border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Protection Needs Calculation</h3>
          <p class="text-sm text-gray-600 mb-6">
            Understanding how your protection needs are calculated based on your current situation.
          </p>

          <!-- Human Capital Breakdown -->
          <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-start justify-between mb-3">
              <div>
                <h4 class="font-semibold text-gray-900 mb-1">Human Capital</h4>
                <p class="text-sm text-gray-600">
                  The value of your future earning potential
                </p>
              </div>
              <div class="text-right">
                <p class="text-2xl font-bold text-blue-600">
                  {{ formatCurrency(humanCapital) }}
                </p>
              </div>
            </div>
            <div class="space-y-2 text-sm">
              <div class="flex justify-between items-center">
                <span class="text-gray-600">Your Gross Annual Income</span>
                <span class="font-medium text-gray-900">{{ formatCurrency(grossAnnualIncome) }}</span>
              </div>
              <div class="flex justify-between items-center text-xs pl-4">
                <span class="text-gray-500">Less: Income Tax</span>
                <span class="text-red-600">-{{ formatCurrency(incomeTax) }}</span>
              </div>
              <div class="flex justify-between items-center text-xs pl-4">
                <span class="text-gray-500">Less: National Insurance</span>
                <span class="text-red-600">-{{ formatCurrency(nationalInsurance) }}</span>
              </div>
              <div class="flex justify-between items-center pt-1 border-t border-blue-200">
                <span class="text-gray-700 font-semibold">Your Net Earned Income</span>
                <span class="font-semibold text-gray-900">{{ formatCurrency(totalAnnualIncome) }}</span>
              </div>

              <!-- Your Continuing Income (Rental + Dividend) -->
              <div v-if="continuingIncome > 0" class="flex justify-between items-center pt-1 text-green-700">
                <div class="flex items-center">
                  <span class="font-medium">Add: Your Rental/Dividend Income</span>
                  <div class="ml-2 group relative">
                    <svg class="w-4 h-4 text-green-400 cursor-help" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                    </svg>
                    <div class="hidden group-hover:block absolute z-10 w-64 p-2 mt-2 text-xs text-white bg-gray-900 rounded-lg shadow-lg -left-32">
                      This income continues after death, reducing protection need
                    </div>
                  </div>
                </div>
                <span class="font-medium">+{{ formatCurrency(continuingIncome) }}</span>
              </div>

              <!-- Spouse Income (if included) -->
              <div v-if="spouseIncluded && spouseNetIncome > 0" class="flex justify-between items-center pt-1 text-green-700">
                <span class="font-medium">Add: Spouse's Net Earned Income</span>
                <span class="font-medium">+{{ formatCurrency(spouseNetIncome) }}</span>
              </div>

              <!-- Spouse Continuing Income (if included) -->
              <div v-if="spouseIncluded && spouseContinuingIncome > 0" class="flex justify-between items-center text-green-700">
                <span class="font-medium">Add: Spouse's Rental/Dividend Income</span>
                <span class="font-medium">+{{ formatCurrency(spouseContinuingIncome) }}</span>
              </div>

              <!-- Income Summary -->
              <div class="pt-2 border-t border-blue-300 space-y-1">
                <div class="flex justify-between items-center text-sm">
                  <span class="text-gray-600">Income that STOPS on death</span>
                  <span class="font-medium text-red-600">{{ formatCurrency(incomeThatStops) }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                  <span class="text-gray-600">Income that CONTINUES after death</span>
                  <span class="font-medium text-green-600">{{ formatCurrency(incomeThatContinues) }}</span>
                </div>
              </div>

              <!-- Income Difference -->
              <div class="flex justify-between items-center pt-2 border-t border-blue-300 bg-blue-100 -mx-2 px-2 py-1 rounded">
                <div class="flex items-center">
                  <span class="font-semibold text-gray-900">Net Income Replacement Need</span>
                  <div class="ml-2 group relative">
                    <svg class="w-4 h-4 text-gray-400 cursor-help" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                    </svg>
                    <div class="hidden group-hover:block absolute z-10 w-64 p-2 mt-2 text-xs text-white bg-gray-900 rounded-lg shadow-lg -left-32">
                      This is the actual income the family would lose if you die (what stops minus what continues)
                    </div>
                  </div>
                </div>
                <span class="font-semibold text-gray-900">{{ formatCurrency(netIncomeDifference) }}</span>
              </div>

              <div class="flex justify-between items-center pt-2">
                <span class="text-gray-600">Years to Retirement (Age {{ currentAge }} → {{ retirementAge }})</span>
                <span class="font-medium text-gray-900">{{ yearsToRetirement }} years</span>
              </div>
              <div class="flex justify-between items-center">
                <div class="flex items-center">
                  <span class="text-gray-600">Effective Years (capped at 10)</span>
                  <div class="ml-2 group relative">
                    <svg class="w-4 h-4 text-gray-400 cursor-help" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                    </svg>
                    <div class="hidden group-hover:block absolute z-10 w-64 p-2 mt-2 text-xs text-white bg-gray-900 rounded-lg shadow-lg -left-32">
                      Capped at 10 years max because distant future earnings have reduced present value in financial planning
                    </div>
                  </div>
                </div>
                <span class="font-medium text-gray-900">{{ effectiveYears }} years</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-gray-600">Multiplier (standard rule of thumb)</span>
                <span class="font-medium text-gray-900">10×</span>
              </div>
              <div class="pt-2 border-t border-blue-300">
                <div class="flex justify-between items-center">
                  <span class="font-semibold text-gray-900">Calculation</span>
                  <span class="font-mono text-sm text-gray-600">
                    {{ formatCurrency(netIncomeDifference || totalAnnualIncome) }} × 10 × {{ effectiveYears }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Total Debt Breakdown -->
          <div class="p-4 bg-purple-50 border border-purple-200 rounded-lg">
            <div class="flex items-start justify-between mb-3">
              <div>
                <h4 class="font-semibold text-gray-900 mb-1">Total Debt Protection Need</h4>
                <p class="text-sm text-gray-600">
                  Outstanding debts that would need to be cleared
                </p>
              </div>
              <div class="text-right">
                <p class="text-2xl font-bold text-purple-600">
                  {{ formatCurrency(totalDebt) }}
                </p>
              </div>
            </div>
            <div class="space-y-2 text-sm">
              <div class="flex justify-between items-center">
                <span class="text-gray-600">Total Mortgage Debt</span>
                <span class="font-medium text-gray-900">{{ formatCurrency(mortgageDebt) }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-gray-600">Other Liabilities</span>
                <span class="font-medium text-gray-900">{{ formatCurrency(otherDebt) }}</span>
              </div>
              <div class="pt-2 border-t border-purple-300">
                <div class="flex justify-between items-center">
                  <span class="font-semibold text-gray-900">Total Debt</span>
                  <span class="font-semibold text-gray-900">{{ formatCurrency(totalDebt) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Total Protection Need Summary -->
          <div class="mt-6 p-4 bg-gray-100 border border-gray-300 rounded-lg">
            <div class="flex justify-between items-center">
              <div>
                <h4 class="font-semibold text-gray-900 mb-1">Total Protection Need</h4>
                <p class="text-xs text-gray-600">
                  Human Capital + Debt + Education + Final Expenses
                </p>
              </div>
              <div class="text-right">
                <p class="text-3xl font-bold text-gray-900">
                  {{ formatCurrency(totalProtectionNeed) }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Coverage Gap by Category -->
      <div class="mb-8">
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Coverage Gaps by Category</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="gap in gapCategories"
            :key="gap.category"
            class="p-4 rounded-lg"
            :class="gap.isPlaceholder ? 'bg-gray-50 border border-gray-200' : getGapCardClass(gap.severity)"
          >
            <div class="flex justify-between items-start mb-2">
              <h4 class="font-medium" :class="gap.isPlaceholder ? 'text-gray-500' : 'text-gray-900'">{{ gap.label }}</h4>
              <span
                v-if="!gap.isPlaceholder"
                class="px-2 py-1 text-xs font-semibold rounded"
                :class="getSeverityBadgeClass(gap.severity)"
              >
                {{ gap.severity }}
              </span>
              <span
                v-else
                class="px-2 py-1 text-xs font-semibold rounded bg-gray-200 text-gray-600"
              >
                pending
              </span>
            </div>
            <div v-if="!gap.isPlaceholder" class="text-2xl font-bold text-gray-900 mb-1">
              {{ formatCurrency(gap.amount) }}
            </div>
            <div v-else class="text-2xl font-bold text-gray-400 mb-1">
              —
            </div>
            <div class="text-xs" :class="gap.isPlaceholder ? 'text-gray-500 italic' : 'text-gray-600'">
              {{ gap.description }}
            </div>
          </div>
        </div>
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
    ...mapGetters('protection', ['coverageGaps', 'totalPremium']),

    // Override adequacyScore to extract numeric value from object
    adequacyScore() {
      const score = this.$store.getters['protection/adequacyScore'];
      // Backend may return either a number or an object {score: X, category: Y, ...}
      if (typeof score === 'object' && score !== null) {
        return score.score ?? 0;
      }
      return score || 0;
    },

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
      // Get gaps from backend - structure is analysis.data.gaps.gaps_by_category
      const gaps = this.analysis?.data?.gaps?.gaps_by_category ||
                   this.analysis?.gaps?.gaps_by_category || {};

      return [
        {
          category: 'human_capital',
          label: 'Human Capital',
          amount: gaps.human_capital_gap || 0,
          severity: this.calculateSeverity(gaps.human_capital_gap || 0),
          description: 'Lost income potential from death or disability',
          isPlaceholder: false,
        },
        {
          category: 'debt',
          label: 'Outstanding Debt',
          amount: gaps.debt_protection_gap || 0,
          severity: this.calculateSeverity(gaps.debt_protection_gap || 0),
          description: 'Mortgage and other debts requiring coverage',
          isPlaceholder: false,
        },
        {
          category: 'education',
          label: 'Education Costs',
          amount: 0,
          severity: 'placeholder',
          description: 'Coming in next phase',
          isPlaceholder: true,
        },
        {
          category: 'income_protection',
          label: 'Income Protection',
          amount: 0,
          severity: 'placeholder',
          description: 'Coming in next phase',
          isPlaceholder: true,
        },
        {
          category: 'disability',
          label: 'Disability Coverage',
          amount: 0,
          severity: 'placeholder',
          description: 'Coming in next phase',
          isPlaceholder: true,
        },
        {
          category: 'sickness',
          label: 'Sickness/Illness',
          amount: 0,
          severity: 'placeholder',
          description: 'Coming in next phase',
          isPlaceholder: true,
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

    // Human Capital breakdown values
    grossAnnualIncome() {
      // Get gross income from analysis needs
      return this.analysis?.data?.needs?.gross_income ||
             this.analysis?.needs?.gross_income || 0;
    },

    totalAnnualIncome() {
      // Get NET income from analysis needs (this is what's used for human capital)
      return this.analysis?.data?.needs?.net_income ||
             this.analysis?.needs?.net_income || 0;
    },

    incomeTax() {
      return this.analysis?.data?.needs?.income_tax ||
             this.analysis?.needs?.income_tax || 0;
    },

    nationalInsurance() {
      return this.analysis?.data?.needs?.national_insurance ||
             this.analysis?.needs?.national_insurance || 0;
    },

    currentAge() {
      // Get from analysis.data.profile
      return this.analysis?.data?.profile?.current_age ||
             this.analysis?.profile?.current_age || 0;
    },

    retirementAge() {
      // Get from analysis.data.profile or protection profile
      return this.analysis?.data?.profile?.retirement_age ||
             this.analysis?.profile?.retirement_age ||
             this.profile?.retirement_age || 67;
    },

    yearsToRetirement() {
      const years = Math.max(0, this.retirementAge - this.currentAge);
      return years;
    },

    effectiveYears() {
      // Effective years is capped at 10 because distant future earnings have less present value
      // This is a standard financial planning convention
      return Math.min(this.yearsToRetirement, 10);
    },

    humanCapital() {
      return this.analysis?.data?.needs?.human_capital ||
             this.analysis?.needs?.human_capital || 0;
    },

    // Debt breakdown values
    totalDebt() {
      return this.analysis?.data?.needs?.debt_protection ||
             this.analysis?.needs?.debt_protection || 0;
    },

    mortgageDebt() {
      return this.analysis?.data?.debt_breakdown?.mortgage ||
             this.analysis?.debt_breakdown?.mortgage || 0;
    },

    otherDebt() {
      return this.analysis?.data?.debt_breakdown?.other ||
             this.analysis?.debt_breakdown?.other || 0;
    },

    totalProtectionNeed() {
      return this.analysis?.data?.needs?.total_need ||
             this.analysis?.needs?.total_need || 0;
    },

    // Spouse income tracking
    spousePermissionDenied() {
      return this.analysis?.data?.needs?.spouse_permission_denied ||
             this.analysis?.needs?.spouse_permission_denied || false;
    },

    spouseIncluded() {
      return this.analysis?.data?.needs?.spouse_included ||
             this.analysis?.needs?.spouse_included || false;
    },

    spouseHumanCapital() {
      return this.analysis?.data?.needs?.spouse_human_capital ||
             this.analysis?.needs?.spouse_human_capital || 0;
    },

    spouseNetIncome() {
      return this.analysis?.data?.needs?.spouse_net_income ||
             this.analysis?.needs?.spouse_net_income || 0;
    },

    netIncomeDifference() {
      return this.analysis?.data?.needs?.net_income_difference ||
             this.analysis?.needs?.net_income_difference || 0;
    },

    // Continuing income (rental + dividend)
    continuingIncome() {
      return this.analysis?.data?.needs?.continuing_income ||
             this.analysis?.needs?.continuing_income || 0;
    },

    incomeThatStops() {
      return this.analysis?.data?.needs?.income_that_stops ||
             this.analysis?.needs?.income_that_stops || 0;
    },

    incomeThatContinues() {
      return this.analysis?.data?.needs?.income_that_continues ||
             this.analysis?.needs?.income_that_continues || 0;
    },

    spouseContinuingIncome() {
      return this.analysis?.data?.needs?.spouse_continuing_income ||
             this.analysis?.needs?.spouse_continuing_income || 0;
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
