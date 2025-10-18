<template>
  <div>
    <div class="mb-6">
      <h2 class="text-h4 font-semibold text-gray-900">Liabilities Overview</h2>
      <p class="mt-1 text-body-sm text-gray-600">
        Summary of all your liabilities and net worth calculation
      </p>
    </div>

    <!-- Net Worth Summary Card -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      <!-- Total Assets -->
      <div class="card p-6 bg-gradient-to-r from-success-50 to-success-100">
        <p class="text-body-sm font-medium text-success-700">Total Assets</p>
        <p class="text-h3 font-display font-bold text-success-900 mt-2">
          {{ formatCurrency(assetsSummary?.total || 0) }}
        </p>
      </div>

      <!-- Total Liabilities -->
      <div class="card p-6 bg-gradient-to-r from-error-50 to-error-100">
        <p class="text-body-sm font-medium text-error-700">Total Liabilities</p>
        <p class="text-h3 font-display font-bold text-error-900 mt-2">
          {{ formatCurrency(liabilitiesSummary?.total || 0) }}
        </p>
      </div>

      <!-- Net Worth -->
      <div class="card p-6 bg-gradient-to-r from-primary-50 to-primary-100">
        <p class="text-body-sm font-medium text-primary-700">Net Worth</p>
        <p
          class="text-h3 font-display font-bold mt-2"
          :class="netWorth >= 0 ? 'text-primary-900' : 'text-error-900'"
        >
          {{ formatCurrency(netWorth) }}
        </p>
      </div>
    </div>

    <!-- Liabilities List -->
    <div class="space-y-4">
      <!-- Mortgages Section -->
      <div class="card p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-h5 font-semibold text-gray-900">Mortgages</h3>
          <span class="text-body-sm text-gray-500">
            {{ liabilitiesSummary?.mortgages?.count || 0 }} {{ liabilitiesSummary?.mortgages?.count === 1 ? 'mortgage' : 'mortgages' }}
          </span>
        </div>

        <div v-if="liabilitiesSummary?.mortgages?.count > 0" class="space-y-3">
          <div
            v-for="mortgage in mortgages"
            :key="mortgage.id"
            class="flex justify-between items-center py-3 border-b border-gray-200 last:border-0"
          >
            <div>
              <p class="text-body-base font-medium text-gray-900">{{ mortgage.property_address }}</p>
              <p class="text-body-sm text-gray-600">
                Lender: {{ mortgage.lender || 'N/A' }}
              </p>
            </div>
            <div class="text-right">
              <p class="text-body-base font-semibold text-gray-900">
                {{ formatCurrency(mortgage.outstanding_balance) }}
              </p>
              <p class="text-body-sm text-gray-600">
                {{ mortgage.monthly_payment ? formatCurrency(mortgage.monthly_payment) + '/mo' : 'N/A' }}
              </p>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-4 text-gray-500">
          <p class="text-body-sm">No mortgages recorded</p>
        </div>

        <div class="mt-4 pt-4 border-t border-gray-200 flex justify-between items-center">
          <p class="text-body-base font-semibold text-gray-900">Total Mortgage Debt</p>
          <p class="text-h5 font-semibold text-gray-900">
            {{ formatCurrency(liabilitiesSummary?.mortgages?.total || 0) }}
          </p>
        </div>
      </div>

      <!-- Other Liabilities Section -->
      <div class="card p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-h5 font-semibold text-gray-900">Other Liabilities</h3>
          <span class="text-body-sm text-gray-500">
            {{ liabilitiesSummary?.other?.count || 0 }} {{ liabilitiesSummary?.other?.count === 1 ? 'liability' : 'liabilities' }}
          </span>
        </div>

        <div v-if="liabilitiesSummary?.other?.count > 0" class="space-y-3">
          <div
            v-for="liability in otherLiabilities"
            :key="liability.id"
            class="flex justify-between items-center py-3 border-b border-gray-200 last:border-0"
          >
            <div>
              <p class="text-body-base font-medium text-gray-900">{{ liability.description }}</p>
              <p class="text-body-sm text-gray-600">
                Type: {{ formatLiabilityType(liability.liability_type) }}
              </p>
            </div>
            <div class="text-right">
              <p class="text-body-base font-semibold text-gray-900">
                {{ formatCurrency(liability.amount) }}
              </p>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-4 text-gray-500">
          <p class="text-body-sm">No other liabilities recorded</p>
        </div>

        <div class="mt-4 pt-4 border-t border-gray-200 flex justify-between items-center">
          <p class="text-body-base font-semibold text-gray-900">Total Other Liabilities</p>
          <p class="text-h5 font-semibold text-gray-900">
            {{ formatCurrency(liabilitiesSummary?.other?.total || 0) }}
          </p>
        </div>
      </div>
    </div>

    <!-- Net Worth Indicator -->
    <div class="mt-6 card p-6">
      <div class="flex justify-between items-center">
        <div>
          <h3 class="text-h5 font-semibold text-gray-900">Your Net Worth</h3>
          <p class="text-body-sm text-gray-600 mt-1">Total Assets minus Total Liabilities</p>
        </div>
        <div class="text-right">
          <p
            class="text-h2 font-display font-bold"
            :class="netWorth >= 0 ? 'text-success-600' : 'text-error-600'"
          >
            {{ formatCurrency(netWorth) }}
          </p>
          <div class="mt-2 flex items-center justify-end">
            <span
              class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
              :class="netWorth >= 0 ? 'bg-success-100 text-success-800' : 'bg-error-100 text-error-800'"
            >
              <svg
                v-if="netWorth >= 0"
                class="w-4 h-4 mr-1"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <svg
                v-else
                class="w-4 h-4 mr-1"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
              {{ netWorth >= 0 ? 'Positive' : 'Negative' }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue';
import { useStore } from 'vuex';

export default {
  name: 'LiabilitiesOverview',

  setup() {
    const store = useStore();

    const profile = computed(() => store.getters['userProfile/profile']);
    const assetsSummary = computed(() => profile.value?.assets_summary);
    const liabilitiesSummary = computed(() => profile.value?.liabilities_summary);

    const netWorth = computed(() => {
      const assets = assetsSummary.value?.total || 0;
      const liabilities = liabilitiesSummary.value?.total || 0;
      return assets - liabilities;
    });

    // Mock data - in real implementation, these would come from the profile
    const mortgages = computed(() => {
      // This would be fetched from the profile/store
      return [];
    });

    const otherLiabilities = computed(() => {
      // This would be fetched from the profile/store
      return [];
    });

    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(amount || 0);
    };

    const formatLiabilityType = (type) => {
      if (!type) return 'N/A';
      return type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
    };

    return {
      assetsSummary,
      liabilitiesSummary,
      netWorth,
      mortgages,
      otherLiabilities,
      formatCurrency,
      formatLiabilityType,
    };
  },
};
</script>
