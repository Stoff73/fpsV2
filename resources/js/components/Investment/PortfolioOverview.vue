<template>
  <div class="portfolio-overview">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
      <!-- Total Value Card -->
      <div class="bg-blue-50 rounded-lg p-6">
        <h3 class="text-sm font-medium text-blue-900 mb-2">Total Portfolio Value</h3>
        <p class="text-3xl font-bold text-blue-600">{{ formattedTotalValue }}</p>
        <p class="text-sm text-blue-700 mt-2">{{ accountsCount }} accounts</p>
      </div>

      <!-- YTD Return Card -->
      <div class="bg-green-50 rounded-lg p-6">
        <h3 class="text-sm font-medium text-green-900 mb-2">YTD Return</h3>
        <p class="text-3xl font-bold" :class="ytdReturn >= 0 ? 'text-green-600' : 'text-red-600'">
          {{ formattedYtdReturn }}
        </p>
        <p class="text-sm text-green-700 mt-2">{{ holdingsCount }} holdings</p>
      </div>

      <!-- Diversification Score Card -->
      <div class="bg-purple-50 rounded-lg p-6">
        <h3 class="text-sm font-medium text-purple-900 mb-2">Diversification Score</h3>
        <p class="text-3xl font-bold text-purple-600">{{ diversificationScore }}/100</p>
        <p class="text-sm text-purple-700 mt-2">{{ diversificationLabel }}</p>
      </div>
    </div>

    <!-- Investment Accounts List -->
    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
          <h2 class="text-xl font-semibold text-gray-900">Investment Accounts</h2>
          <button
            @click="addAccount"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
          >
            <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Account
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!loading && accounts.length === 0" class="px-6 py-12">
        <div class="text-center">
          <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
          <h3 class="mt-4 text-lg font-medium text-gray-900">No investment accounts yet</h3>
          <p class="mt-2 text-sm text-gray-600 max-w-md mx-auto">
            Get started by adding your first investment account. Track your portfolio performance, analyze holdings, and monitor your investment strategy.
          </p>
          <div class="mt-6">
            <button
              @click="addAccount"
              class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
              <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Add Your First Account
            </button>
          </div>
        </div>
      </div>

      <!-- Accounts List -->
      <div v-else-if="!loading && accounts.length > 0" class="divide-y divide-gray-200">
        <div
          v-for="account in accounts"
          :key="account.id"
          class="px-6 py-4 hover:bg-gray-50 transition-colors cursor-pointer"
          @click="viewAccount(account.id)"
        >
          <div class="flex items-center justify-between">
            <div class="flex-1">
              <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                  <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                  </div>
                </div>
                <div>
                  <h3 class="text-base font-medium text-gray-900">{{ account.account_name }}</h3>
                  <p class="text-sm text-gray-600">{{ account.provider }} • {{ formatAccountType(account.account_type) }}</p>
                </div>
              </div>
            </div>
            <div class="text-right">
              <p class="text-lg font-semibold text-gray-900">{{ formatCurrency(account.current_value) }}</p>
              <p class="text-sm" :class="getReturnColorClass(account.ytd_return)">
                {{ formatReturn(account.ytd_return) }} YTD
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-else class="px-6 py-12">
        <div class="flex justify-center items-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <span class="ml-3 text-gray-600">Loading accounts...</span>
        </div>
      </div>
    </div>

    <!-- Risk Metrics Placeholder -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Risk Profile</h2>
        <div v-if="riskMetrics" class="space-y-3">
          <div class="flex justify-between">
            <span class="text-sm text-gray-600">Risk Level:</span>
            <span class="text-sm font-medium text-gray-900 capitalize">{{ riskMetrics.risk_level }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-sm text-gray-600">Equity %:</span>
            <span class="text-sm font-medium text-gray-900">{{ riskMetrics.equity_percentage }}%</span>
          </div>
          <div class="flex justify-between">
            <span class="text-sm text-gray-600">Est. Volatility:</span>
            <span class="text-sm font-medium text-gray-900">{{ riskMetrics.estimated_volatility }}%</span>
          </div>
        </div>
        <p v-else class="text-gray-500 text-center py-4">No risk metrics available</p>
      </div>

      <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Tax Efficiency</h2>
        <div v-if="taxEfficiency" class="space-y-3">
          <div class="flex justify-between">
            <span class="text-sm text-gray-600">Efficiency Score:</span>
            <span class="text-sm font-medium text-gray-900">{{ taxEfficiencyScore }}/100</span>
          </div>
          <div class="flex justify-between">
            <span class="text-sm text-gray-600">Unrealized Gains:</span>
            <span class="text-sm font-medium text-gray-900">{{ formatCurrency(unrealizedGains) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-sm text-gray-600">ISA Allowance Used:</span>
            <span class="text-sm font-medium text-gray-900">{{ isaAllowancePercentage.toFixed(1) }}%</span>
          </div>
        </div>
        <p v-else class="text-gray-500 text-center py-4">No tax efficiency data available</p>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters } from 'vuex';
import AssetAllocationChart from './AssetAllocationChart.vue';
import GeographicAllocationMap from './GeographicAllocationMap.vue';

export default {
  name: 'PortfolioOverview',

  components: {
    AssetAllocationChart,
    GeographicAllocationMap,
  },

  computed: {
    ...mapGetters('investment', [
      'totalPortfolioValue',
      'ytdReturn',
      'assetAllocation',
      'diversificationScore',
      'holdingsCount',
      'accountsCount',
      'unrealizedGains',
      'taxEfficiencyScore',
      'isaAllowancePercentage',
      'accounts',
    ]),

    formattedTotalValue() {
      return this.formatCurrency(this.totalPortfolioValue);
    },

    formattedYtdReturn() {
      const sign = this.ytdReturn >= 0 ? '+' : '';
      return `${sign}${this.ytdReturn.toFixed(2)}%`;
    },

    diversificationLabel() {
      if (this.diversificationScore >= 80) return 'Excellent';
      if (this.diversificationScore >= 60) return 'Good';
      if (this.diversificationScore >= 40) return 'Fair';
      return 'Poor';
    },

    riskMetrics() {
      return this.$store.state.investment.analysis?.risk_metrics;
    },

    taxEfficiency() {
      return this.$store.state.investment.analysis?.tax_efficiency;
    },

    allocationForChart() {
      // Convert array of allocation objects to key-value object for chart
      if (!this.assetAllocation || this.assetAllocation.length === 0) {
        return {};
      }

      return this.assetAllocation.reduce((acc, asset) => {
        acc[asset.asset_type] = asset.percentage;
        return acc;
      }, {});
    },

    geographicAllocationForChart() {
      // Get geographic allocation from analysis
      const analysis = this.$store.state.investment.analysis;
      const geographicAllocation = analysis?.geographic_allocation;

      if (!geographicAllocation || Object.keys(geographicAllocation).length === 0) {
        return {};
      }

      return geographicAllocation;
    },

    loading() {
      return this.$store.state.investment.loading;
    },
  },

  methods: {
    formatCurrency(value) {
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(value || 0);
    },

    formatReturn(value) {
      if (!value && value !== 0) return 'N/A';
      const sign = value >= 0 ? '+' : '';
      return `${sign}${value.toFixed(2)}%`;
    },

    formatAccountType(type) {
      const types = {
        'isa': 'Stocks & Shares ISA',
        'sipp': 'SIPP',
        'gia': 'General Investment Account',
        'pension': 'Workplace Pension',
        'other': 'Other',
      };
      return types[type] || type;
    },

    getReturnColorClass(value) {
      if (!value && value !== 0) return 'text-gray-600';
      return value >= 0 ? 'text-green-600' : 'text-red-600';
    },

    addAccount() {
      // Emit event to parent to switch tab and open modal
      this.$emit('open-add-account-modal');
    },

    viewAccount(accountId) {
      // Navigate to Accounts tab with selected account
      this.$parent.activeTab = 'accounts';
      // Optionally emit event to pre-select the account
      this.$emit('account-selected', accountId);
    },
  },
};
</script>
