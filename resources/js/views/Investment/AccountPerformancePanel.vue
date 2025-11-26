<template>
  <div class="account-performance-panel relative">
    <!-- Coming Soon Watermark -->
    <div class="absolute inset-0 flex items-center justify-center z-10 pointer-events-none">
      <div class="bg-amber-100 border-2 border-amber-400 rounded-lg px-8 py-4 transform -rotate-12 shadow-lg">
        <p class="text-2xl font-bold text-amber-700">Coming Soon</p>
      </div>
    </div>

    <!-- Placeholder Content -->
    <div class="opacity-50">
      <!-- YTD Return Card -->
      <div class="performance-summary">
        <div class="summary-card">
          <div class="card-icon bg-green-100">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="icon text-green-600">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
            </svg>
          </div>
          <div class="card-content">
            <span class="card-label">YTD Return</span>
            <span class="card-value" :class="returnColorClass">{{ formatReturn(account.ytd_return) }}</span>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon bg-blue-100">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="icon text-blue-600">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
            </svg>
          </div>
          <div class="card-content">
            <span class="card-label">1 Year Return</span>
            <span class="card-value text-gray-400">--</span>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon bg-purple-100">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="icon text-purple-600">
              <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z" />
            </svg>
          </div>
          <div class="card-content">
            <span class="card-label">Since Inception</span>
            <span class="card-value text-gray-400">--</span>
          </div>
        </div>
      </div>

      <!-- Performance Chart Placeholder -->
      <div class="chart-section">
        <h4 class="section-title">Performance History</h4>
        <div class="chart-placeholder">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="placeholder-icon">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" />
          </svg>
          <p class="placeholder-text">Historical performance chart will be available here</p>
        </div>
      </div>

      <!-- Benchmark Comparison Placeholder -->
      <div class="benchmark-section">
        <h4 class="section-title">Benchmark Comparison</h4>
        <div class="benchmark-placeholder">
          <div class="benchmark-row">
            <span class="benchmark-label">Your Account</span>
            <span class="benchmark-value text-gray-400">--</span>
          </div>
          <div class="benchmark-row">
            <span class="benchmark-label">FTSE All-Share</span>
            <span class="benchmark-value text-gray-400">--</span>
          </div>
          <div class="benchmark-row">
            <span class="benchmark-label">S&P 500</span>
            <span class="benchmark-value text-gray-400">--</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AccountPerformancePanel',

  props: {
    account: {
      type: Object,
      required: true,
    },
  },

  computed: {
    returnColorClass() {
      if (this.account.ytd_return === null || this.account.ytd_return === undefined) {
        return 'text-gray-400';
      }
      return this.account.ytd_return >= 0 ? 'text-green-600' : 'text-red-600';
    },
  },

  methods: {
    formatReturn(value) {
      if (value === null || value === undefined) return '--';
      const sign = value >= 0 ? '+' : '';
      return `${sign}${parseFloat(value).toFixed(2)}%`;
    },
  },
};
</script>

<style scoped>
.account-performance-panel {
  min-height: 400px;
}

.performance-summary {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.summary-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
}

.card-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  border-radius: 12px;
  flex-shrink: 0;
}

.icon {
  width: 24px;
  height: 24px;
}

.card-content {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.card-label {
  font-size: 12px;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.card-value {
  font-size: 24px;
  font-weight: 700;
}

.section-title {
  font-size: 16px;
  font-weight: 600;
  color: #111827;
  margin: 0 0 16px 0;
}

.chart-section,
.benchmark-section {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 24px;
}

.chart-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  background: #f9fafb;
  border-radius: 8px;
}

.placeholder-icon {
  width: 48px;
  height: 48px;
  color: #d1d5db;
  margin-bottom: 12px;
}

.placeholder-text {
  font-size: 14px;
  color: #9ca3af;
  margin: 0;
}

.benchmark-placeholder {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.benchmark-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  background: #f9fafb;
  border-radius: 8px;
}

.benchmark-label {
  font-size: 14px;
  color: #374151;
}

.benchmark-value {
  font-size: 16px;
  font-weight: 600;
}

@media (max-width: 768px) {
  .performance-summary {
    grid-template-columns: 1fr;
  }
}
</style>
