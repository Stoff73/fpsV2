<template>
  <div
    class="protection-overview-card bg-white rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition-shadow duration-200"
    @click="navigateToProtection"
  >
    <div class="flex justify-between items-start mb-4">
      <h3 class="text-xl font-semibold text-gray-800">Protection</h3>
      <div class="text-sm text-gray-500">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
          />
        </svg>
      </div>
    </div>

    <!-- Adequacy Score -->
    <div class="mb-6">
      <div class="flex items-baseline mb-2">
        <span
          class="text-4xl font-bold"
          :class="adequacyScoreColor"
        >
          {{ adequacyScore }}%
        </span>
        <span class="ml-2 text-sm text-gray-600">Coverage Adequacy</span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-2">
        <div
          class="h-2 rounded-full transition-all duration-300"
          :class="adequacyScoreBarColor"
          :style="{ width: adequacyScore + '%' }"
        ></div>
      </div>
    </div>

    <!-- Total Coverage -->
    <div class="grid grid-cols-2 gap-4 mb-4">
      <div>
        <p class="text-sm text-gray-600 mb-1">Total Coverage</p>
        <p class="text-lg font-semibold text-gray-800">
          {{ formattedTotalCoverage }}
        </p>
      </div>
      <div>
        <p class="text-sm text-gray-600 mb-1">Monthly Premium</p>
        <p class="text-lg font-semibold text-gray-800">
          {{ formattedPremiumTotal }}
        </p>
      </div>
    </div>

    <!-- Critical Gaps -->
    <div
      v-if="criticalGaps > 0"
      class="flex items-center p-3 bg-red-50 rounded-md"
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        class="h-5 w-5 text-red-600 mr-2"
        viewBox="0 0 20 20"
        fill="currentColor"
      >
        <path
          fill-rule="evenodd"
          d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
          clip-rule="evenodd"
        />
      </svg>
      <span class="text-sm font-medium text-red-800">
        {{ criticalGaps }} critical {{ criticalGaps === 1 ? 'gap' : 'gaps' }} identified
      </span>
    </div>

    <div
      v-else
      class="flex items-center p-3 bg-green-50 rounded-md"
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        class="h-5 w-5 text-green-600 mr-2"
        viewBox="0 0 20 20"
        fill="currentColor"
      >
        <path
          fill-rule="evenodd"
          d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
          clip-rule="evenodd"
        />
      </svg>
      <span class="text-sm font-medium text-green-800">
        No critical gaps identified
      </span>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ProtectionOverviewCard',

  props: {
    adequacyScore: {
      type: Number,
      required: true,
      validator: (value) => value >= 0 && value <= 100,
    },
    totalCoverage: {
      type: Number,
      required: true,
      default: 0,
    },
    premiumTotal: {
      type: Number,
      required: true,
      default: 0,
    },
    criticalGaps: {
      type: Number,
      required: true,
      default: 0,
    },
  },

  computed: {
    adequacyScoreColor() {
      if (this.adequacyScore >= 80) {
        return 'text-green-600';
      } else if (this.adequacyScore >= 60) {
        return 'text-amber-600';
      } else {
        return 'text-red-600';
      }
    },

    adequacyScoreBarColor() {
      if (this.adequacyScore >= 80) {
        return 'bg-green-600';
      } else if (this.adequacyScore >= 60) {
        return 'bg-amber-600';
      } else {
        return 'bg-red-600';
      }
    },

    formattedTotalCoverage() {
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(this.totalCoverage);
    },

    formattedPremiumTotal() {
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }).format(this.premiumTotal);
    },
  },

  methods: {
    navigateToProtection() {
      this.$router.push('/protection');
    },
  },
};
</script>

<style scoped>
.protection-overview-card {
  min-width: 280px;
  max-width: 100%;
}

@media (min-width: 640px) {
  .protection-overview-card {
    min-width: 320px;
  }
}

@media (min-width: 1024px) {
  .protection-overview-card {
    min-width: 360px;
  }
}
</style>
