<template>
  <div class="performance">
    <h2 class="text-2xl font-bold text-gray-900 mb-4">Performance Analysis</h2>
    <p class="text-gray-600 mb-6">Track portfolio performance over time with benchmarks</p>

    <!-- Performance Metrics Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-white border border-gray-200 rounded-lg p-4">
        <h4 class="text-sm font-medium text-gray-600 mb-1">Total Return</h4>
        <p class="text-2xl font-bold" :class="totalReturn >= 0 ? 'text-green-600' : 'text-red-600'">
          {{ formatReturn(totalReturn) }}
        </p>
        <p class="text-xs text-gray-500 mt-1">Since inception</p>
      </div>
      <div class="bg-white border border-gray-200 rounded-lg p-4">
        <h4 class="text-sm font-medium text-gray-600 mb-1">YTD Return</h4>
        <p class="text-2xl font-bold" :class="ytdReturn >= 0 ? 'text-green-600' : 'text-red-600'">
          {{ formatReturn(ytdReturn) }}
        </p>
        <p class="text-xs text-gray-500 mt-1">This calendar year</p>
      </div>
      <div class="bg-white border border-gray-200 rounded-lg p-4">
        <h4 class="text-sm font-medium text-gray-600 mb-1">Best Month</h4>
        <p class="text-2xl font-bold text-green-600">
          {{ formatReturn(bestMonth) }}
        </p>
        <p class="text-xs text-gray-500 mt-1">Highest monthly return</p>
      </div>
      <div class="bg-white border border-gray-200 rounded-lg p-4">
        <h4 class="text-sm font-medium text-gray-600 mb-1">Worst Month</h4>
        <p class="text-2xl font-bold text-red-600">
          {{ formatReturn(worstMonth) }}
        </p>
        <p class="text-xs text-gray-500 mt-1">Lowest monthly return</p>
      </div>
    </div>

    <!-- Performance Chart -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
      <PerformanceLineChart
        :performance-data="performanceData"
        :benchmarks="benchmarks"
        :loading="loading"
      />
    </div>

    <!-- Benchmark Comparison Table -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Benchmark Comparison</h3>
      <div v-if="benchmarkComparison && benchmarkComparison.length > 0" class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Period
              </th>
              <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Portfolio
              </th>
              <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                FTSE All-Share
              </th>
              <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                S&P 500
              </th>
              <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Outperformance
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="row in benchmarkComparison" :key="row.period" class="hover:bg-gray-50">
              <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ row.period }}</td>
              <td class="px-4 py-3 text-sm text-right font-medium" :class="getReturnClass(row.portfolio)">
                {{ formatReturn(row.portfolio) }}
              </td>
              <td class="px-4 py-3 text-sm text-right" :class="getReturnClass(row.ftse_all_share)">
                {{ formatReturn(row.ftse_all_share) }}
              </td>
              <td class="px-4 py-3 text-sm text-right" :class="getReturnClass(row.sp_500)">
                {{ formatReturn(row.sp_500) }}
              </td>
              <td class="px-4 py-3 text-sm text-right font-medium" :class="getReturnClass(row.outperformance)">
                {{ formatReturn(row.outperformance) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <p v-else class="text-gray-500 text-center py-8">No benchmark comparison data available</p>
    </div>
  </div>
</template>

<script>
import PerformanceLineChart from './PerformanceLineChart.vue';

export default {
  name: 'Performance',

  components: {
    PerformanceLineChart,
  },

  computed: {
    performanceData() {
      // Get historical performance data from Vuex store
      return this.$store.state.investment.analysis?.performance_data || [];
    },

    benchmarks() {
      return this.$store.state.investment.analysis?.benchmarks || {
        ftse_all_share: [],
        sp_500: [],
      };
    },

    benchmarkComparison() {
      return this.$store.state.investment.analysis?.benchmark_comparison || [];
    },

    loading() {
      return this.$store.state.investment.loading;
    },

    totalReturn() {
      return this.$store.getters['investment/ytdReturn'] || 0;
    },

    ytdReturn() {
      return this.$store.getters['investment/ytdReturn'] || 0;
    },

    bestMonth() {
      // Calculate from performance data
      if (!this.performanceData || this.performanceData.length === 0) return 0;
      // This would need monthly return calculations
      return 0;
    },

    worstMonth() {
      // Calculate from performance data
      if (!this.performanceData || this.performanceData.length === 0) return 0;
      // This would need monthly return calculations
      return 0;
    },
  },

  methods: {
    formatReturn(value) {
      const sign = value >= 0 ? '+' : '';
      return `${sign}${(value || 0).toFixed(2)}%`;
    },

    getReturnClass(returnPercent) {
      if (returnPercent > 0) return 'text-green-600';
      if (returnPercent < 0) return 'text-red-600';
      return 'text-gray-600';
    },
  },
};
</script>
