<template>
  <div class="premium-breakdown-chart">
    <apexchart
      v-if="hasData && isReady"
      type="pie"
      :options="chartOptions"
      :series="series"
      height="300"
    />
    <div v-if="!hasData" class="flex items-center justify-center h-64 text-gray-400">
      <p>No premium data available</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PremiumBreakdownChart',

  props: {
    premiums: {
      type: Object,
      required: true,
      default: () => ({
        life: 0,
        criticalIllness: 0,
        incomeProtection: 0,
        disability: 0,
        sicknessIllness: 0,
      }),
    },
  },

  data() {
    return {
      isReady: false,
    };
  },

  mounted() {
    this.$nextTick(() => {
      this.isReady = true;
    });
  },

  computed: {
    series() {
      return [
        this.premiums.life || 0,
        this.premiums.criticalIllness || 0,
        this.premiums.incomeProtection || 0,
        this.premiums.disability || 0,
        this.premiums.sicknessIllness || 0,
      ];
    },

    hasData() {
      return this.series.some(value => value > 0);
    },

    chartOptions() {
      return {
        chart: {
          type: 'pie',
          fontFamily: 'Inter, sans-serif',
        },
        labels: [
          'Life Insurance',
          'Critical Illness',
          'Income Protection',
          'Disability',
          'Sickness/Illness',
        ],
        colors: ['#3B82F6', '#8B5CF6', '#10B981', '#F59E0B', '#EF4444'],
        dataLabels: {
          enabled: true,
          formatter: (val, opts) => {
            const value = opts.w.config.series[opts.seriesIndex];
            return `£${value.toFixed(2)}`;
          },
        },
        legend: {
          position: 'bottom',
          horizontalAlign: 'center',
          fontSize: '14px',
          markers: {
            width: 12,
            height: 12,
            radius: 6,
          },
          itemMargin: {
            horizontal: 8,
            vertical: 4,
          },
        },
        tooltip: {
          y: {
            formatter: (value) => {
              return new Intl.NumberFormat('en-GB', {
                style: 'currency',
                currency: 'GBP',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
              }).format(value) + ' per month';
            },
          },
        },
        responsive: [
          {
            breakpoint: 480,
            options: {
              chart: {
                width: '100%',
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
};
</script>

<style scoped>
.premium-breakdown-chart {
  width: 100%;
  min-height: 300px;
}
</style>
