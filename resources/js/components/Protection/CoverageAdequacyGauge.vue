<template>
  <div class="coverage-adequacy-gauge">
    <apexchart
      type="radialBar"
      :options="chartOptions"
      :series="[score]"
      height="300"
    />
  </div>
</template>

<script>
export default {
  name: 'CoverageAdequacyGauge',

  props: {
    score: {
      type: Number,
      required: true,
      validator: (value) => value >= 0 && value <= 100,
      default: 0,
    },
  },

  computed: {
    scoreColor() {
      if (this.score >= 80) return '#10B981'; // green
      if (this.score >= 60) return '#F59E0B'; // amber
      return '#EF4444'; // red
    },

    chartOptions() {
      return {
        chart: {
          type: 'radialBar',
          fontFamily: 'Inter, sans-serif',
        },
        plotOptions: {
          radialBar: {
            startAngle: -135,
            endAngle: 135,
            hollow: {
              margin: 0,
              size: '70%',
              background: '#fff',
              position: 'front',
              dropShadow: {
                enabled: true,
                top: 3,
                left: 0,
                blur: 4,
                opacity: 0.24,
              },
            },
            track: {
              background: '#E5E7EB',
              strokeWidth: '100%',
              margin: 0,
            },
            dataLabels: {
              show: true,
              name: {
                offsetY: -10,
                show: true,
                color: '#6B7280',
                fontSize: '14px',
              },
              value: {
                formatter: (val) => {
                  return parseInt(val) + '%';
                },
                color: '#111827',
                fontSize: '36px',
                fontWeight: 700,
                show: true,
                offsetY: 10,
              },
            },
          },
        },
        fill: {
          type: 'solid',
          colors: [this.scoreColor],
        },
        stroke: {
          lineCap: 'round',
        },
        labels: ['Adequacy Score'],
      };
    },
  },
};
</script>

<style scoped>
.coverage-adequacy-gauge {
  width: 100%;
  max-width: 400px;
  margin: 0 auto;
}
</style>
