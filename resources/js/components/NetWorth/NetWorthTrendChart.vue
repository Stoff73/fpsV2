<template>
  <div class="net-worth-trend-chart">
    <h3 class="chart-title">Net Worth Trend</h3>
    <div v-if="hasData" class="chart-container">
      <apexchart
        type="area"
        :options="chartOptions"
        :series="series"
        height="350"
      ></apexchart>
    </div>
    <div v-else class="no-data">
      <p>No trend data available</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'NetWorthTrendChart',

  props: {
    trend: {
      type: Array,
      required: true,
      default: () => [],
    },
  },

  computed: {
    hasData() {
      return this.trend && this.trend.length > 0;
    },

    series() {
      return [
        {
          name: 'Net Worth',
          data: this.trend.map(item => item.net_worth || 0),
        },
      ];
    },

    categories() {
      return this.trend.map(item => item.month || '');
    },

    chartOptions() {
      return {
        chart: {
          type: 'area',
          fontFamily: 'Inter, system-ui, sans-serif',
          toolbar: {
            show: true,
            tools: {
              download: true,
              selection: false,
              zoom: false,
              zoomin: false,
              zoomout: false,
              pan: false,
              reset: false,
            },
          },
        },
        dataLabels: {
          enabled: false,
        },
        stroke: {
          curve: 'smooth',
          width: 3,
        },
        colours: ['#3B82F6'],
        fill: {
          type: 'gradient',
          gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.7,
            opacityTo: 0.2,
            stops: [0, 90, 100],
          },
        },
        xaxis: {
          categories: this.categories,
          labels: {
            style: {
              fontSize: '12px',
              colours: '#6b7280',
            },
          },
        },
        yaxis: {
          labels: {
            style: {
              fontSize: '12px',
              colours: '#6b7280',
            },
            formatter: (val) => {
              return this.formatCurrency(val);
            },
          },
        },
        tooltip: {
          y: {
            formatter: (val) => {
              return this.formatCurrency(val);
            },
          },
        },
        grid: {
          borderColour: '#e5e7eb',
          strokeDashArray: 4,
        },
        responsive: [
          {
            breakpoint: 768,
            options: {
              chart: {
                height: 300,
              },
              xaxis: {
                labels: {
                  rotate: -45,
                },
              },
            },
          },
        ],
      };
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
  },
};
</script>

<style scoped>
.net-worth-trend-chart {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
}

.chart-title {
  font-size: 18px;
  font-weight: 600;
  color: #111827;
  margin: 0 0 20px 0;
}

.chart-container {
  width: 100%;
}

.no-data {
  text-align: center;
  padding: 60px 20px;
  color: #9ca3af;
}

.no-data p {
  margin: 0;
  font-size: 14px;
}

@media (max-width: 768px) {
  .net-worth-trend-chart {
    padding: 16px;
  }

  .chart-title {
    font-size: 16px;
  }
}
</style>
