<template>
  <div class="emergency-fund-gauge">
    <apexchart
      type="radialBar"
      :options="chartOptions"
      :series="[runwayPercentage]"
      height="300"
    />
  </div>
</template>

<script>
export default {
  name: 'EmergencyFundGauge',

  props: {
    runwayMonths: {
      type: Number,
      required: true,
      default: 0,
    },
    targetMonths: {
      type: Number,
      default: 6,
    },
  },

  computed: {
    runwayPercentage() {
      return Math.min((this.runwayMonths / this.targetMonths) * 100, 100);
    },

    runwayColour() {
      if (this.runwayMonths >= 6) return '#10B981'; // green
      if (this.runwayMonths >= 3) return '#F59E0B'; // amber
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
                formatter: () => {
                  return this.runwayMonths.toFixed(1);
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
          colours: [this.runwayColour],
        },
        stroke: {
          lineCap: 'round',
        },
        labels: ['Months Runway'],
      };
    },
  },
};
</script>

<style scoped>
.emergency-fund-gauge {
  width: 100%;
  max-width: 400px;
  margin: 0 auto;
}
</style>
