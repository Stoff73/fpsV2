<template>
  <div class="asset-allocation-donut">
    <h3 class="chart-title">Asset Allocation</h3>
    <div v-if="hasData" class="chart-container">
      <apexchart
        type="donut"
        :options="chartOptions"
        :series="series"
        height="350"
      ></apexchart>
    </div>
    <div v-else class="no-data">
      <p>No asset data available</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AssetAllocationDonut',

  props: {
    breakdown: {
      type: Object,
      required: true,
      default: () => ({}),
    },
  },

  computed: {
    hasData() {
      return this.series.some(value => value > 0);
    },

    series() {
      return [
        this.breakdown.property || 0,
        this.breakdown.investments || 0,
        this.breakdown.cash || 0,
        this.breakdown.business || 0,
        this.breakdown.chattels || 0,
      ];
    },

    chartOptions() {
      return {
        chart: {
          type: 'donut',
          fontFamily: 'Inter, system-ui, sans-serif',
        },
        labels: ['Property', 'Investments', 'Cash', 'Business', 'Chattels'],
        colors: ['#10B981', '#3B82F6', '#F59E0B', '#8B5CF6', '#EC4899'],
        legend: {
          position: 'bottom',
          fontSize: '14px',
        },
        dataLabels: {
          enabled: true,
          formatter: (val) => {
            return val.toFixed(1) + '%';
          },
        },
        plotOptions: {
          pie: {
            donut: {
              size: '65%',
              labels: {
                show: true,
                name: {
                  show: true,
                  fontSize: '16px',
                  fontWeight: 600,
                },
                value: {
                  show: true,
                  fontSize: '24px',
                  fontWeight: 700,
                  formatter: (val) => {
                    return this.formatCurrency(val);
                  },
                },
                total: {
                  show: true,
                  label: 'Total Assets',
                  fontSize: '14px',
                  fontWeight: 600,
                  color: '#6b7280',
                  formatter: () => {
                    const total = this.series.reduce((sum, val) => sum + val, 0);
                    return this.formatCurrency(total);
                  },
                },
              },
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
        responsive: [
          {
            breakpoint: 768,
            options: {
              chart: {
                height: 300,
              },
              legend: {
                position: 'bottom',
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
.asset-allocation-donut {
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
  .asset-allocation-donut {
    padding: 16px;
  }

  .chart-title {
    font-size: 16px;
  }
}
</style>
