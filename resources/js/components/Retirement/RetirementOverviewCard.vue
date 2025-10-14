<template>
  <div class="bg-white shadow-lg rounded-lg p-6 cursor-pointer hover:shadow-xl transition-shadow duration-300" @click="navigateToDashboard">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-xl font-semibold text-gray-800">Retirement Planning</h2>
      <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
      </svg>
    </div>

    <!-- Readiness Score Gauge -->
    <div class="flex items-center justify-center mb-6">
      <div class="relative">
        <svg class="w-32 h-32 transform -rotate-90">
          <circle cx="64" cy="64" r="56" stroke="#e5e7eb" stroke-width="8" fill="none"></circle>
          <circle
            cx="64"
            cy="64"
            r="56"
            :stroke="gaugeColor"
            stroke-width="8"
            fill="none"
            :stroke-dasharray="circumference"
            :stroke-dashoffset="dashOffset"
            class="transition-all duration-1000 ease-out"
          ></circle>
        </svg>
        <div class="absolute inset-0 flex flex-col items-center justify-center">
          <span class="text-3xl font-bold" :class="scoreTextColor">{{ readinessScore }}</span>
          <span class="text-xs text-gray-500">Readiness</span>
        </div>
      </div>
    </div>

    <!-- Key Metrics -->
    <div class="space-y-3">
      <!-- Years to Retirement -->
      <div class="flex items-center justify-between">
        <span class="text-sm text-gray-600">Years to Retirement</span>
        <span class="text-sm font-semibold text-gray-900">{{ yearsToRetirement }} years</span>
      </div>

      <!-- Projected Income -->
      <div class="flex items-center justify-between">
        <span class="text-sm text-gray-600">Projected Income</span>
        <span class="text-sm font-semibold text-gray-900">£{{ projectedIncome.toLocaleString() }}/year</span>
      </div>

      <!-- Income Gap/Surplus -->
      <div class="flex items-center justify-between">
        <span class="text-sm text-gray-600">{{ incomeGapLabel }}</span>
        <span class="text-sm font-semibold" :class="incomeGapColor">
          £{{ Math.abs(incomeGap).toLocaleString() }}/year
        </span>
      </div>

      <!-- Total Pension Wealth -->
      <div class="flex items-center justify-between pt-3 border-t border-gray-200">
        <span class="text-sm text-gray-600">Total Pension Wealth</span>
        <span class="text-sm font-bold text-indigo-600">£{{ totalWealth.toLocaleString() }}</span>
      </div>
    </div>

    <!-- Status Badge -->
    <div class="mt-4 pt-4 border-t border-gray-200">
      <span
        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
        :class="statusBadgeClass"
      >
        {{ statusText }}
      </span>
    </div>
  </div>
</template>

<script>
export default {
  name: 'RetirementOverviewCard',
  props: {
    readinessScore: {
      type: Number,
      default: 0,
    },
    projectedIncome: {
      type: Number,
      default: 0,
    },
    targetIncome: {
      type: Number,
      default: 0,
    },
    yearsToRetirement: {
      type: Number,
      default: 0,
    },
    totalWealth: {
      type: Number,
      default: 0,
    },
  },
  computed: {
    circumference() {
      return 2 * Math.PI * 56;
    },
    dashOffset() {
      return this.circumference - (this.readinessScore / 100) * this.circumference;
    },
    incomeGap() {
      return this.targetIncome - this.projectedIncome;
    },
    incomeGapLabel() {
      return this.incomeGap > 0 ? 'Income Gap' : 'Income Surplus';
    },
    incomeGapColor() {
      return this.incomeGap > 0 ? 'text-red-600' : 'text-green-600';
    },
    gaugeColor() {
      if (this.readinessScore >= 90) return '#10b981'; // green
      if (this.readinessScore >= 70) return '#f59e0b'; // amber
      if (this.readinessScore >= 50) return '#f97316'; // orange
      return '#ef4444'; // red
    },
    scoreTextColor() {
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
    statusBadgeClass() {
      if (this.readinessScore >= 90) return 'bg-green-100 text-green-800';
      if (this.readinessScore >= 70) return 'bg-amber-100 text-amber-800';
      if (this.readinessScore >= 50) return 'bg-orange-100 text-orange-800';
      return 'bg-red-100 text-red-800';
    },
  },
  methods: {
    navigateToDashboard() {
      this.$router.push('/retirement');
    },
  },
};
</script>

<style scoped>
/* Additional styles can be added here if needed */
</style>
