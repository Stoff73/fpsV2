<template>
  <div class="retirement-readiness">
    <!-- Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
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
          <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
          </svg>
        </div>
        <p class="text-xl font-semibold text-gray-500">Coming Soon</p>
        <p class="text-sm text-gray-400 mt-1">Not available in this version</p>
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

export default {
  name: 'RetirementReadiness',

  computed: {
    ...mapState('retirement', ['dcPensions', 'dbPensions', 'statePension', 'profile']),
    ...mapGetters('retirement', ['yearsToRetirement']),

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
