<template>
  <div class="readiness-gauge">
    <apexchart
      type="radialBar"
      :options="chartOptions"
      :series="series"
      height="350"
    ></apexchart>
  </div>
</template>

<script>
export default {
  name: 'ReadinessGauge',

  props: {
    score: {
      type: Number,
      required: true,
      validator: (value) => value >= 0 && value <= 100,
    },
  },

  computed: {
    series() {
      return [this.score];
    },

    gaugeColor() {
      if (this.score >= 90) return '#10b981'; // green
      if (this.score >= 70) return '#f59e0b'; // amber
      if (this.score >= 50) return '#f97316'; // orange
      return '#ef4444'; // red
    },

    statusLabel() {
      if (this.score >= 90) return 'Excellent';
      if (this.score >= 70) return 'Good';
      if (this.score >= 50) return 'Fair';
      return 'Poor';
    },

    chartOptions() {
      return {
        chart: {
          type: 'radialBar',
          offsetY: -20,
          sparkline: {
            enabled: true,
          },
        },
        plotOptions: {
          radialBar: {
            startAngle: -90,
            endAngle: 90,
            track: {
              background: '#e7e7e7',
              strokeWidth: '97%',
              margin: 5,
              dropShadow: {
                enabled: true,
                top: 2,
                left: 0,
                color: '#999',
                opacity: 1,
                blur: 2,
              },
            },
            dataLabels: {
              name: {
                show: true,
                fontSize: '16px',
                fontFamily: 'Inter, sans-serif',
                fontWeight: 600,
                color: '#6b7280',
                offsetY: 75,
              },
              value: {
                offsetY: 20,
                fontSize: '48px',
                fontFamily: 'Inter, sans-serif',
                fontWeight: 700,
                color: this.gaugeColor,
                formatter: (val) => {
                  return Math.round(val);
                },
              },
            },
          },
        },
        fill: {
          type: 'gradient',
          gradient: {
            shade: 'light',
            type: 'horizontal',
            shadeIntensity: 0.5,
            gradientToColors: [this.gaugeColor],
            inverseColors: false,
            opacityFrom: 1,
            opacityTo: 1,
            stops: [0, 100],
          },
        },
        colors: [this.gaugeColor],
        labels: [this.statusLabel],
        stroke: {
          lineCap: 'round',
        },
      };
    },
  },
};
</script>

<style scoped>
.readiness-gauge {
  display: flex;
  justify-content: center;
  align-items: center;
}
</style>
