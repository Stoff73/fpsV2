<template>
  <div class="retirement-readiness">
    <!-- Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- Readiness Score -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-2">
          <h3 class="text-sm font-medium text-gray-600">Readiness Score</h3>
          <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <p class="text-3xl font-bold" :class="scoreColor">{{ readinessScore }}</p>
        <p class="text-sm text-gray-500 mt-1">{{ statusText }}</p>
      </div>

      <!-- Years to Retirement -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-2">
          <h3 class="text-sm font-medium text-gray-600">Years to Retirement</h3>
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <p class="text-3xl font-bold text-gray-900">{{ yearsToRetirement }}</p>
        <p class="text-sm text-gray-500 mt-1">Target age: {{ targetRetirementAge }}</p>
      </div>

      <!-- Projected Income -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-2">
          <h3 class="text-sm font-medium text-gray-600">Projected Income</h3>
          <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <p class="text-3xl font-bold text-gray-900">£{{ projectedIncome.toLocaleString() }}</p>
        <p class="text-sm text-gray-500 mt-1">Per year</p>
      </div>

      <!-- Income Gap/Surplus -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-2">
          <h3 class="text-sm font-medium text-gray-600">{{ incomeGapLabel }}</h3>
          <svg class="w-5 h-5" :class="incomeGap > 0 ? 'text-red-600' : 'text-green-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
          </svg>
        </div>
        <p class="text-3xl font-bold" :class="incomeGap > 0 ? 'text-red-600' : 'text-green-600'">
          £{{ Math.abs(incomeGap).toLocaleString() }}
        </p>
        <p class="text-sm text-gray-500 mt-1">vs target £{{ targetIncome.toLocaleString() }}/year</p>
      </div>
    </div>

    <!-- Readiness Gauge -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Retirement Readiness</h3>
        <ReadinessGauge :score="readinessScore" />
      </div>

      <!-- Readiness Breakdown -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Readiness Breakdown</h3>
        <div class="space-y-4" v-if="analysis">
          <div v-for="factor in readinessFactors" :key="factor.name" class="flex items-center justify-between">
            <div class="flex items-center flex-1">
              <span class="text-sm text-gray-700 mr-4 w-32">{{ factor.name }}</span>
              <div class="flex-1 bg-gray-200 rounded-full h-2 mr-4">
                <div
                  class="h-2 rounded-full transition-all duration-500"
                  :style="{ width: factor.score + '%', backgroundColor: getFactorColor(factor.score) }"
                ></div>
              </div>
            </div>
            <span class="text-sm font-semibold text-gray-900 w-12 text-right">{{ factor.score }}</span>
          </div>
        </div>
        <div v-else class="text-center text-gray-500 py-8">
          No analysis data available
        </div>
      </div>
    </div>

    <!-- Pension Wealth Summary -->
    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-6">Pension Wealth Summary</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- DC Pensions -->
        <div class="border-l-4 border-blue-500 pl-4">
          <p class="text-sm text-gray-600 mb-1">DC Pensions</p>
          <p class="text-2xl font-bold text-gray-900">£{{ dcPensionValue.toLocaleString() }}</p>
          <p class="text-sm text-gray-500 mt-1">{{ dcPensionCount }} pension{{ dcPensionCount !== 1 ? 's' : '' }}</p>
        </div>

        <!-- DB Pensions -->
        <div class="border-l-4 border-purple-500 pl-4">
          <p class="text-sm text-gray-600 mb-1">DB Pensions</p>
          <p class="text-2xl font-bold text-gray-900">£{{ dbPensionIncome.toLocaleString() }}<span class="text-sm text-gray-500">/year</span></p>
          <p class="text-sm text-gray-500 mt-1">{{ dbPensionCount }} scheme{{ dbPensionCount !== 1 ? 's' : '' }}</p>
        </div>

        <!-- State Pension -->
        <div class="border-l-4 border-green-500 pl-4">
          <p class="text-sm text-gray-600 mb-1">State Pension</p>
          <p class="text-2xl font-bold text-gray-900">£{{ statePensionForecast.toLocaleString() }}<span class="text-sm text-gray-500">/year</span></p>
          <p class="text-sm text-gray-500 mt-1">{{ niYears }} NI years</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex';
import ReadinessGauge from '../../components/Retirement/ReadinessGauge.vue';

export default {
  name: 'RetirementReadiness',

  components: {
    ReadinessGauge,
  },

  computed: {
    ...mapState('retirement', ['dcPensions', 'dbPensions', 'statePension', 'profile', 'analysis']),
    ...mapGetters('retirement', [
      'totalPensionWealth',
      'retirementReadinessScore',
      'projectedIncome',
      'targetIncome',
      'incomeGap',
      'yearsToRetirement',
    ]),

    readinessScore() {
      return this.retirementReadinessScore;
    },

    scoreColor() {
      if (this.readinessScore >= 90) return 'text-green-600';
      if (this.readinessScore >= 70) return 'text-amber-600';
      if (this.readinessScore >= 50) return 'text-orange-600';
      return 'text-red-600';
    },

    statusText() {
      if (this.readinessScore >= 90) return 'On Track';
      if (this.readinessScore >= 70) return 'Good Progress';
      if (this.readinessScore >= 50) return 'Needs Attention';
      return 'Action Required';
    },

    incomeGapLabel() {
      return this.incomeGap > 0 ? 'Income Gap' : 'Income Surplus';
    },

    targetRetirementAge() {
      return this.profile?.target_retirement_age || 67;
    },

    dcPensionValue() {
      return this.dcPensions.reduce((sum, p) => sum + parseFloat(p.current_fund_value || 0), 0);
    },

    dcPensionCount() {
      return this.dcPensions.length;
    },

    dbPensionIncome() {
      return this.dbPensions.reduce((sum, p) => sum + parseFloat(p.annual_income || 0), 0);
    },

    dbPensionCount() {
      return this.dbPensions.length;
    },

    statePensionForecast() {
      return parseFloat(this.statePension?.forecast_weekly_amount || 0) * 52;
    },

    niYears() {
      return this.statePension?.qualifying_years || 0;
    },

    readinessFactors() {
      if (!this.analysis?.readiness_factors) {
        return [];
      }
      return [
        { name: 'Savings Rate', score: this.analysis.readiness_factors.savings_rate || 0 },
        { name: 'Time Horizon', score: this.analysis.readiness_factors.time_horizon || 0 },
        { name: 'Income Coverage', score: this.analysis.readiness_factors.income_coverage || 0 },
        { name: 'Diversification', score: this.analysis.readiness_factors.diversification || 0 },
      ];
    },
  },

  methods: {
    getFactorColor(score) {
      if (score >= 80) return '#10b981'; // green
      if (score >= 60) return '#f59e0b'; // amber
      if (score >= 40) return '#f97316'; // orange
      return '#ef4444'; // red
    },
  },
};
</script>

<style scoped>
/* Animations */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.retirement-readiness > div {
  animation: fadeIn 0.5s ease-out;
}
</style>
