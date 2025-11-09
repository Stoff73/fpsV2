<template>
  <div
    class="card hover:shadow-lg transition-shadow cursor-pointer"
    @click="navigateToTrusts"
  >
    <div class="flex items-start justify-between mb-4">
      <div>
        <h3 class="text-h3 text-gray-900">Trusts</h3>
        <p class="text-sm text-gray-600 mt-1">Asset protection & IHT planning</p>
      </div>
      <div class="p-3 bg-purple-100 rounded-lg">
        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColour" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
      </div>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-2 gap-4 mb-4">
      <div>
        <p class="text-sm text-gray-600">Active Trusts</p>
        <p class="text-2xl font-bold text-gray-900">{{ activeTrustsCount }}</p>
      </div>
      <div>
        <p class="text-sm text-gray-600">Total Value</p>
        <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(totalTrustValue) }}</p>
      </div>
    </div>

    <!-- Assets Held -->
    <div class="mb-4">
      <p class="text-sm text-gray-600 mb-2">Assets in Trusts</p>
      <div class="flex items-centre space-x-2">
        <div class="flex-1 bg-gray-200 rounded-full h-2">
          <div
            class="bg-purple-600 h-2 rounded-full transition-all"
            :style="{ width: `${Math.min(100, (totalTrustValue / (totalTrustValue + 1000)) * 100)}%` }"
          ></div>
        </div>
        <span class="text-sm font-medium text-gray-700">{{ totalAssetCount }}</span>
      </div>
    </div>

    <!-- Upcoming Charges -->
    <div v-if="upcomingCharges > 0" class="bg-amber-50 border border-amber-200 rounded-lg p-3">
      <div class="flex items-centre">
        <svg class="w-5 h-5 text-amber-600 mr-2" fill="none" stroke="currentColour" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="text-sm text-amber-900">
          <strong>{{ upcomingCharges }}</strong> upcoming periodic charge{{ upcomingCharges > 1 ? 's' : '' }}
        </span>
      </div>
    </div>

    <!-- No Trusts State -->
    <div v-if="activeTrustsCount === 0" class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-centre">
      <p class="text-sm text-gray-600">No trusts set up</p>
      <button class="mt-2 text-sm text-purple-600 hover:text-purple-700 font-medium">
        Create your first trust →
      </button>
    </div>

    <!-- Quick Actions -->
    <div class="mt-4 pt-4 border-t border-gray-200">
      <button class="text-sm text-purple-600 hover:text-purple-700 font-medium flex items-centre">
        View all trusts
        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColour" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TrustsOverviewCard',

  props: {
    activeTrustsCount: {
      type: Number,
      default: 0,
    },
    totalTrustValue: {
      type: Number,
      default: 0,
    },
    totalAssetCount: {
      type: Number,
      default: 0,
    },
    upcomingCharges: {
      type: Number,
      default: 0,
    },
  },

  methods: {
    formatCurrency(value) {
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(value);
    },

    navigateToTrusts() {
      this.$router.push('/trusts');
    },
  },
};
</script>
