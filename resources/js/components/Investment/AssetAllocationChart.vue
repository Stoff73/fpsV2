<template>
  <div class="asset-allocation-chart">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold text-gray-900">Asset Allocation</h3>
      <button
        v-if="showViewDetails"
        class="text-sm text-blue-600 hover:text-blue-800"
        @click="$emit('view-details')"
      >
        View Details
      </button>
    </div>

    <div v-if="loading" class="flex items-center justify-center h-64">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <div v-else-if="hasData" class="chart-container">
      <apexchart
        type="donut"
        :options="chartOptions"
        :series="series"
        height="350"
      />
    </div>

    <div v-else class="flex items-center justify-center h-64 text-gray-500">
      <div class="text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
        </svg>
        <p>No allocation data available</p>
        <p class="text-sm mt-1">Add holdings to see your asset allocation</p>
      </div>
    </div>
  </div>
</template>

<script>
import VueApexCharts from 'vue3-apexcharts';

export default {
  name: 'AssetAllocationChart',

  components: {
    apexchart: VueApexCharts,
  },

  props: {
    allocation: {
      type: Object,
      required: true,
      default: () => ({}),
    },
    loading: {
      type: Boolean,
      default: false,
    },
    showViewDetails: {
      type: Boolean,
      default: false,
    },
  },

  computed: {
    hasData() {
      return this.allocation && Object.keys(this.allocation).length > 0;
    },

    series() {
      if (!this.hasData) return [];

      return Object.values(this.allocation).map(item =>
        typeof item === 'object' ? item.percentage : item
      );
    },

    chartOptions() {
      const labels = Object.keys(this.allocation).map(key => {
        // Convert snake_case to Title Case
        return key.split('_')
          .map(word => word.charAt(0).toUpperCase() + word.slice(1))
          .join(' ');
      });

      return {
        chart: {
          type: 'donut',
          fontFamily: 'Inter, system-ui, sans-serif',
          toolbar: {
            show: false,
          },
        },
        labels: labels,
        colors: [
          '#3b82f6', // UK Equities - blue
          '#8b5cf6', // US Equities - violet
          '#ec4899', // International Equities - pink
          '#10b981', // Bonds - green
          '#f59e0b', // Cash - amber
          '#6366f1', // Alternatives - indigo
          '#14b8a6', // Property - teal
        ],
        plotOptions: {
          pie: {
            donut: {
              size: '65%',
              labels: {
                show: true,
                name: {
                  show: true,
                  fontSize: '14px',
                  fontWeight: 600,
                  color: '#1f2937',
                },
                value: {
                  show: true,
                  fontSize: '24px',
                  fontWeight: 700,
                  color: '#111827',
                  formatter: (val) => `${val.toFixed(1)}%`,
                },
                total: {
                  show: true,
                  label: 'Total Value',
                  fontSize: '14px',
                  fontWeight: 500,
                  color: '#6b7280',
                  formatter: () => {
                    const total = this.series.reduce((sum, val) => sum + val, 0);
                    return `${total.toFixed(1)}%`;
                  },
                },
              },
            },
          },
        },
        dataLabels: {
          enabled: false,
        },
        legend: {
          position: 'bottom',
          fontSize: '14px',
          fontWeight: 500,
          labels: {
            colors: '#374151',
          },
          markers: {
            width: 12,
            height: 12,
            radius: 3,
          },
          itemMargin: {
            horizontal: 10,
            vertical: 5,
          },
          formatter: (seriesName, opts) => {
            const value = opts.w.globals.series[opts.seriesIndex];
            return `${seriesName}: ${value.toFixed(1)}%`;
          },
        },
        tooltip: {
          enabled: true,
          y: {
            formatter: (val) => `${val.toFixed(2)}%`,
          },
        },
        responsive: [
          {
            breakpoint: 768,
            options: {
              chart: {
                height: 300,
              },
              legend: {
                position: 'bottom',
                fontSize: '12px',
              },
            },
          },
        ],
      };
    },
  },
};
</script>

<style scoped>
.chart-container {
  width: 100%;
}
</style>
