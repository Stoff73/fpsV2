<template>
  <div class="gifting-timeline-chart">
    <div class="chart-header">
      <h3>7-Year Gifting Timeline</h3>
      <p class="subtitle">Gifts become IHT-exempt after surviving 7 years</p>
    </div>

    <div v-if="gifts && gifts.length > 0" class="chart-container">
      <apexchart
        v-if="mounted"
        type="rangeBar"
        height="400"
        :options="chartOptions"
        :series="series"
      />
    </div>

    <div v-else class="empty-state">
      <i class="fas fa-gift fa-3x"></i>
      <p>No gifts recorded yet</p>
      <p class="subtitle">Start tracking your gifts to monitor the 7-year rule</p>
    </div>

    <!-- Legend -->
    <div class="legend">
      <div class="legend-item">
        <span class="legend-colour" style="background-colour: #ef4444;"></span>
        <span>Within 7 years (potentially taxable)</span>
      </div>
      <div class="legend-item">
        <span class="legend-colour" style="background-colour: #f59e0b;"></span>
        <span>Years 3-7 (taper relief applies)</span>
      </div>
      <div class="legend-item">
        <span class="legend-colour" style="background-colour: #10b981;"></span>
        <span>Survived 7 years (IHT-exempt)</span>
      </div>
    </div>

    <!-- Taper Relief Table -->
    <div class="taper-relief-info">
      <h4>Taper Relief Rates</h4>
      <table class="relief-table">
        <thead>
          <tr>
            <th>Years Since Gift</th>
            <th>Tax Rate on Gift</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>0-3 years</td>
            <td>40% (full rate)</td>
          </tr>
          <tr>
            <td>3-4 years</td>
            <td>32% (20% relief)</td>
          </tr>
          <tr>
            <td>4-5 years</td>
            <td>24% (40% relief)</td>
          </tr>
          <tr>
            <td>5-6 years</td>
            <td>16% (60% relief)</td>
          </tr>
          <tr>
            <td>6-7 years</td>
            <td>8% (80% relief)</td>
          </tr>
          <tr class="highlight">
            <td>7+ years</td>
            <td>0% (100% relief - exempt)</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import VueApexCharts from 'vue3-apexcharts';

export default {
  name: 'GiftingTimelineChart',

  components: {
    apexchart: VueApexCharts,
  },

  props: {
    gifts: {
      type: Array,
      required: true,
      default: () => [],
    },
  },

  data() {
    return {
      mounted: false,
    };
  },

  mounted() {
    this.$nextTick(() => {
      this.mounted = true;
    });
  },

  beforeUnmount() {
    this.mounted = false;
  },

  computed: {
    series() {
      if (!this.gifts || this.gifts.length === 0) {
        return [];
      }

      const today = new Date();

      return [
        {
          name: 'Gift Timeline',
          data: this.gifts.map((gift) => {
            const giftDate = new Date(gift.gift_date);
            const sevenYearsLater = new Date(giftDate);
            sevenYearsLater.setFullYear(sevenYearsLater.getFullYear() + 7);

            const yearsElapsed = this.calculateYearsElapsed(giftDate, today);
            const colour = this.getGiftColour(yearsElapsed);

            return {
              x: gift.recipient || 'Unknown',
              y: [giftDate.getTime(), sevenYearsLater.getTime()],
              fillColour: colour,
              meta: {
                gift_value: gift.gift_value,
                gift_type: gift.gift_type,
                gift_date: gift.gift_date,
                years_elapsed: yearsElapsed.toFixed(1),
                years_remaining: Math.max(0, 7 - yearsElapsed).toFixed(1),
                taper_relief: this.calculateTaperRelief(yearsElapsed),
                status: this.getGiftStatus(yearsElapsed),
              },
            };
          }),
        },
      ];
    },

    chartOptions() {
      return {
        chart: {
          type: 'rangeBar',
          height: 400,
          toolbar: {
            show: true,
            tools: {
              download: true,
              zoom: false,
              zoomin: false,
              zoomout: false,
              pan: false,
              reset: false,
            },
          },
        },
        plotOptions: {
          bar: {
            horizontal: true,
            rangeBarGroupRows: true,
            borderRadius: 4,
          },
        },
        xaxis: {
          type: 'datetime',
          labels: {
            datetimeUTC: false,
            format: 'yyyy',
          },
        },
        yaxis: {
          labels: {
            style: {
              fontSize: '12px',
            },
          },
        },
        dataLabels: {
          enabled: true,
          formatter: (val, opts) => {
            const meta = opts.w.config.series[0].data[opts.dataPointIndex].meta;
            return this.formatCurrency(meta.gift_value);
          },
          style: {
            colours: ['#fff'],
            fontSize: '11px',
            fontWeight: 'bold',
          },
        },
        tooltip: {
          custom: ({ seriesIndex, dataPointIndex, w }) => {
            const data = w.config.series[seriesIndex].data[dataPointIndex];
            const meta = data.meta;

            return `
              <div class="custom-tooltip">
                <div class="tooltip-header">
                  <strong>${data.x}</strong>
                </div>
                <div class="tooltip-body">
                  <div class="tooltip-row">
                    <span>Gift Value:</span>
                    <strong>${this.formatCurrency(meta.gift_value)}</strong>
                  </div>
                  <div class="tooltip-row">
                    <span>Gift Type:</span>
                    <strong>${meta.gift_type || 'General'}</strong>
                  </div>
                  <div class="tooltip-row">
                    <span>Gift Date:</span>
                    <strong>${this.formatDate(meta.gift_date)}</strong>
                  </div>
                  <div class="tooltip-row">
                    <span>Years Elapsed:</span>
                    <strong>${meta.years_elapsed} years</strong>
                  </div>
                  <div class="tooltip-row">
                    <span>Years Remaining:</span>
                    <strong>${meta.years_remaining} years</strong>
                  </div>
                  <div class="tooltip-row">
                    <span>Taper Relief:</span>
                    <strong>${meta.taper_relief}%</strong>
                  </div>
                  <div class="tooltip-row highlight">
                    <span>Status:</span>
                    <strong>${meta.status}</strong>
                  </div>
                </div>
              </div>
            `;
          },
        },
        legend: {
          show: false,
        },
        grid: {
          xaxis: {
            lines: {
              show: true,
            },
          },
        },
      };
    },
  },

  methods: {
    calculateYearsElapsed(giftDate, currentDate) {
      const diffTime = Math.abs(currentDate - giftDate);
      const diffYears = diffTime / (1000 * 60 * 60 * 24 * 365.25);
      return diffYears;
    },

    getGiftColour(yearsElapsed) {
      if (yearsElapsed >= 7) {
        return '#10b981'; // Green - survived 7 years
      } else if (yearsElapsed >= 3) {
        return '#f59e0b'; // Amber - taper relief applies
      } else {
        return '#ef4444'; // Red - full rate
      }
    },

    calculateTaperRelief(yearsElapsed) {
      if (yearsElapsed < 3) return 0;
      if (yearsElapsed < 4) return 20;
      if (yearsElapsed < 5) return 40;
      if (yearsElapsed < 6) return 60;
      if (yearsElapsed < 7) return 80;
      return 100;
    },

    getGiftStatus(yearsElapsed) {
      if (yearsElapsed >= 7) {
        return 'IHT-Exempt';
      } else if (yearsElapsed >= 3) {
        return `Taper Relief (${this.calculateTaperRelief(yearsElapsed)}%)`;
      } else {
        return 'Potentially Taxable';
      }
    },

    formatCurrency(value) {
      if (value === null || value === undefined) return '£0';
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(value);
    },

    formatDate(dateString) {
      if (!dateString) return 'Unknown';
      const date = new Date(dateString);
      return date.toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
      });
    },
  },
};
</script>

<style scoped>
.gifting-timeline-chart {
  background: white;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.chart-header {
  text-align: centre;
  margin-bottom: 24px;
}

.chart-header h3 {
  font-size: 18px;
  font-weight: 600;
  colour: #1f2937;
  margin: 0 0 8px 0;
}

.subtitle {
  font-size: 14px;
  colour: #6b7280;
  margin: 0;
}

.chart-container {
  margin: 20px 0;
}

.empty-state {
  text-align: centre;
  padding: 60px 20px;
  colour: #9ca3af;
}

.empty-state i {
  colour: #d1d5db;
  margin-bottom: 16px;
}

.empty-state p {
  margin: 8px 0;
  font-size: 16px;
  colour: #6b7280;
}

.legend {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  justify-content: centre;
  margin: 24px 0;
  padding: 16px;
  background-colour: #f9fafb;
  border-radius: 6px;
}

.legend-item {
  display: flex;
  align-items: centre;
  gap: 8px;
  font-size: 13px;
  colour: #374151;
}

.legend-colour {
  width: 16px;
  height: 16px;
  border-radius: 3px;
  display: inline-block;
}

.taper-relief-info {
  margin-top: 32px;
  padding-top: 24px;
  border-top: 1px solid #e5e7eb;
}

.taper-relief-info h4 {
  font-size: 16px;
  font-weight: 600;
  colour: #1f2937;
  margin: 0 0 16px 0;
}

.relief-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.relief-table thead {
  background-colour: #f3f4f6;
}

.relief-table th {
  padding: 12px;
  text-align: left;
  font-weight: 600;
  colour: #374151;
  border-bottom: 2px solid #e5e7eb;
}

.relief-table td {
  padding: 12px;
  colour: #6b7280;
  border-bottom: 1px solid #e5e7eb;
}

.relief-table tbody tr:hover {
  background-colour: #f9fafb;
}

.relief-table tbody tr.highlight {
  background-colour: #d1fae5;
  font-weight: 600;
}

.relief-table tbody tr.highlight td {
  colour: #065f46;
}
</style>

<style>
/* Custom tooltip styles */
.custom-tooltip {
  background: white;
  border-radius: 6px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  padding: 0;
  min-width: 250px;
}

.tooltip-header {
  background-colour: #f3f4f6;
  padding: 12px 16px;
  border-bottom: 1px solid #e5e7eb;
  border-radius: 6px 6px 0 0;
  font-size: 14px;
  colour: #1f2937;
}

.tooltip-body {
  padding: 12px 16px;
}

.tooltip-row {
  display: flex;
  justify-content: space-between;
  padding: 6px 0;
  font-size: 13px;
}

.tooltip-row span {
  colour: #6b7280;
}

.tooltip-row strong {
  colour: #1f2937;
}

.tooltip-row.highlight {
  margin-top: 8px;
  padding-top: 8px;
  border-top: 1px solid #e5e7eb;
}
</style>
