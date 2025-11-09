<template>
  <div class="performance">
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-centre items-centre py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
      <div class="flex items-centre">
        <svg class="h-5 w-5 text-red-600 mr-2" fill="currentColour" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
        </svg>
        <span class="text-sm font-medium text-red-800">{{ error }}</span>
      </div>
    </div>

    <!-- Empty State - No Accounts -->
    <div v-else-if="!hasAccounts" class="flex flex-col items-centre justify-centre py-16 px-4">
      <div class="bg-white border-2 border-gray-200 rounded-lg p-8 max-w-md w-full text-centre shadow-sm">
        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColour">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">No Performance Data Yet</h2>
        <p class="text-gray-600 mb-6">
          Add investment accounts to start tracking your portfolio performance
        </p>
        <button
          @click="navigateToTab('accounts')"
          class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colours font-medium"
        >
          Add Investment Account
        </button>
      </div>
    </div>

    <!-- Main Content - Performance Data Exists -->
    <div v-else class="space-y-6">
      <!-- Performance Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Total Value -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <p class="text-sm text-gray-600 mb-2">Total Portfolio Value</p>
          <p class="text-3xl font-bold text-gray-800">
            £{{ formatNumber(totalPortfolioValue) }}
          </p>
          <p class="text-xs text-gray-500 mt-1">Across {{ accountCount }} account{{ accountCount !== 1 ? 's' : '' }}</p>
        </div>

        <!-- Holdings Count -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <p class="text-sm text-gray-600 mb-2">Total Holdings</p>
          <p class="text-3xl font-bold text-gray-800">
            {{ holdingsCount }}
          </p>
          <p class="text-xs text-gray-500 mt-1">Investment positions</p>
        </div>

        <!-- Average Return (if available) -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <p class="text-sm text-gray-600 mb-2">Portfolio Health</p>
          <p class="text-3xl font-bold" :class="portfolioHealthColour">
            {{ portfolioHealthScore }}/100
          </p>
          <p class="text-xs text-gray-500 mt-1">Based on analysis</p>
        </div>

        <!-- Asset Allocation Diversity -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <p class="text-sm text-gray-600 mb-2">Diversification</p>
          <p class="text-3xl font-bold" :class="diversificationColour">
            {{ diversificationScore }}%
          </p>
          <p class="text-xs text-gray-500 mt-1">Asset allocation spread</p>
        </div>
      </div>

      <!-- Performance Chart -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-centre justify-between mb-6">
          <h3 class="text-lg font-semibold text-gray-800">Portfolio Value Over Time</h3>
          <select
            v-model="selectedPeriod"
            @change="loadPerformanceData"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
          >
            <option value="1m">1 Month</option>
            <option value="3m">3 Months</option>
            <option value="6m">6 Months</option>
            <option value="1y">1 Year</option>
            <option value="3y">3 Years</option>
            <option value="5y">5 Years</option>
            <option value="all">All Time</option>
          </select>
        </div>

        <!-- Placeholder for future line chart -->
        <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg p-8 text-centre">
          <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColour">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
          </svg>
          <p class="text-sm text-gray-600">
            Performance tracking requires historical data
          </p>
          <p class="text-xs text-gray-500 mt-1">
            Update your holdings regularly to build performance history
          </p>
        </div>
      </div>

      <!-- Asset Allocation Overview -->
      <div v-if="analysis && analysis.allocation" class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-6">Current Asset Allocation</h3>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div v-for="(value, type) in analysis.allocation" :key="type" class="text-centre">
            <div class="text-2xl font-bold text-gray-800 mb-1">{{ value }}%</div>
            <div class="text-sm text-gray-600 capitalize">{{ formatAssetType(type) }}</div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Advanced Performance Analysis</h3>
        <p class="text-sm text-gray-600 mb-4">
          View detailed performance metrics, benchmark comparisons, and attribution analysis below
        </p>
        <div class="flex flex-wrap gap-3">
          <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colours text-sm font-medium">
            View Performance Attribution
          </button>
          <button class="px-4 py-2 bg-white border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 transition-colours text-sm font-medium">
            Compare to Benchmarks
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex';

export default {
  name: 'Performance',

  data() {
    return {
      selectedPeriod: '1y',
      localLoading: false,
      localError: null,
    };
  },

  computed: {
    ...mapState('investment', ['accounts', 'holdings', 'analysis', 'loading', 'error']),
    ...mapGetters('investment', ['totalPortfolioValue']),

    hasAccounts() {
      return this.accounts && this.accounts.length > 0;
    },

    hasHoldings() {
      return this.holdings && this.holdings.length > 0;
    },

    accountCount() {
      return this.accounts ? this.accounts.length : 0;
    },

    holdingsCount() {
      return this.holdings ? this.holdings.length : 0;
    },

    portfolioHealthScore() {
      if (this.analysis && this.analysis.portfolio_health_score !== undefined) {
        return this.analysis.portfolio_health_score;
      }
      // Default score based on basic diversification
      if (this.holdingsCount < 3) return 45;
      if (this.holdingsCount < 5) return 60;
      if (this.holdingsCount < 10) return 75;
      return 85;
    },

    portfolioHealthColour() {
      const score = this.portfolioHealthScore;
      if (score >= 80) return 'text-green-600';
      if (score >= 60) return 'text-yellow-600';
      return 'text-red-600';
    },

    diversificationScore() {
      if (this.analysis && this.analysis.diversification_score !== undefined) {
        return Math.round(this.analysis.diversification_score);
      }
      // Basic diversification based on holdings count
      const count = this.holdingsCount;
      if (count >= 10) return 90;
      if (count >= 7) return 75;
      if (count >= 5) return 60;
      if (count >= 3) return 45;
      return 25;
    },

    diversificationColour() {
      const score = this.diversificationScore;
      if (score >= 70) return 'text-green-600';
      if (score >= 50) return 'text-yellow-600';
      return 'text-red-600';
    },
  },

  methods: {
    formatNumber(value) {
      if (!value) return '0';
      return new Intl.NumberFormat('en-GB').format(value);
    },

    formatAssetType(type) {
      return type.replace(/_/g, ' ');
    },

    navigateToTab(tabId) {
      this.$emit('navigate-to-tab', tabId);
    },

    loadPerformanceData() {
      // This will be implemented when historical performance tracking is added
      console.log(`Loading performance data for period: ${this.selectedPeriod}`);
    },
  },
};
</script>

<style scoped>
/* Add any specific styles here */
</style>
