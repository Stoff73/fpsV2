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

    <!-- Asset Allocation Chart -->
    <div v-if="!loading" class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
      <AssetAllocationChart
        :key="`allocation-${accountsCount}`"
        :allocation="allocationForChart"
        :loading="false"
      />
    </div>

    <!-- Geographic Allocation Map -->
    <div v-if="!loading" class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
      <GeographicAllocationMap
        :key="`geographic-${accountsCount}`"
        :allocation="geographicAllocationForChart"
        :loading="false"
      />
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
            <span class="text-sm text-gray-600">ISA Usage:</span>
            <span class="text-sm font-medium text-gray-900">{{ isaPercentage }}%</span>
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
      'isaPercentage',
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
  },
};
</script>
