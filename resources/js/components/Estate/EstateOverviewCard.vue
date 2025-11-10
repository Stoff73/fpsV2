<template>
  <div
    class="estate-overview-card bg-white rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition-shadow duration-200"
    @click="navigateToEstate"
  >
    <div class="flex justify-between items-start mb-4">
      <h3 class="text-xl font-semibold text-gray-800">Estate Planning</h3>
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
            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
          />
        </svg>
      </div>
    </div>

    <!-- Taxable Estate -->
    <div class="mb-6">
      <div class="flex items-baseline mb-2">
        <span class="text-4xl font-bold text-blue-600">
          {{ formattedTaxableEstate }}
        </span>
      </div>
      <p class="text-sm text-gray-600">Taxable Estate</p>
    </div>

    <!-- IHT Liability & Probate Readiness -->
    <div class="grid grid-cols-2 gap-4 mb-4">
      <div>
        <p class="text-sm text-gray-600 mb-1">IHT Liability</p>
        <p
          class="text-lg font-semibold"
          :class="ihtLiabilityColour"
        >
          {{ formattedIHTLiability }}
        </p>
      </div>
      <div>
        <p class="text-sm text-gray-600 mb-1">Probate Readiness</p>
        <p class="text-lg font-semibold" :class="probateReadinessColour">
          {{ probateReadiness }}%
        </p>
      </div>
    </div>

    <!-- Status Banner -->
    <div
      v-if="ihtLiability > 0"
      class="flex items-center p-3 bg-amber-50 rounded-md"
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        class="h-5 w-5 text-amber-600 mr-2"
        viewBox="0 0 20 20"
        fill="currentColor"
      >
        <path
          fill-rule="evenodd"
          d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
          clip-rule="evenodd"
        />
      </svg>
      <span class="text-sm font-medium text-amber-800">
        IHT planning recommended
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
        No IHT liability forecast
      </span>
    </div>
  </div>
</template>

<script>
export default {
  name: 'EstateOverviewCard',

  props: {
    taxableEstate: {
      type: Number,
      required: true,
      default: 0,
    },
    ihtLiability: {
      type: Number,
      required: true,
      default: 0,
    },
    probateReadiness: {
      type: Number,
      required: true,
      default: 0,
    },
  },

  computed: {
    formattedTaxableEstate() {
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(this.taxableEstate);
    },

    formattedIHTLiability() {
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(this.ihtLiability);
    },

    ihtLiabilityColour() {
      if (this.ihtLiability === 0) {
        return 'text-green-600';
      } else if (this.ihtLiability < 100000) {
        return 'text-amber-600';
      } else {
        return 'text-red-600';
      }
    },

    probateReadinessColour() {
      if (this.probateReadiness >= 80) {
        return 'text-green-600';
      } else if (this.probateReadiness >= 50) {
        return 'text-amber-600';
      } else {
        return 'text-red-600';
      }
    },
  },

  methods: {
    navigateToEstate() {
      this.$router.push('/estate');
    },
  },
};
</script>

<style scoped>
.estate-overview-card {
  min-width: 280px;
  max-width: 100%;
}

@media (min-width: 640px) {
  .estate-overview-card {
    min-width: 320px;
  }
}

@media (min-width: 1024px) {
  .estate-overview-card {
    min-width: 360px;
  }
}
</style>
