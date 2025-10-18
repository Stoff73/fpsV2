<template>
  <div
    class="net-worth-overview-card"
    @click="navigateToDetail"
    role="button"
    tabindex="0"
    @keypress.enter="navigateToDetail"
  >
    <div class="card-header">
      <h3 class="card-title">Net Worth</h3>
      <span class="card-icon">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
          <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
        </svg>
      </span>
    </div>

    <div v-if="loading" class="loading-skeleton">
      <div class="skeleton-text skeleton-large"></div>
      <div class="skeleton-text skeleton-small"></div>
      <div class="skeleton-text skeleton-small"></div>
      <div class="skeleton-text skeleton-small"></div>
      <div class="skeleton-text skeleton-small"></div>
    </div>

    <div v-else-if="error" class="error-state">
      <p class="error-message">{{ error }}</p>
      <button @click.stop="retry" class="retry-button">Retry</button>
    </div>

    <div v-else class="card-content">
      <div class="net-worth-value">
        <span class="value-label">Total Net Worth</span>
        <span class="value-amount" :class="netWorthClass">{{ formattedNetWorth }}</span>
      </div>

      <div class="asset-breakdown">
        <div class="breakdown-item" v-if="hasProperty">
          <span class="breakdown-label">Property</span>
          <span class="breakdown-value">{{ formatCurrency(breakdown.property) }}</span>
        </div>
        <div class="breakdown-item" v-if="hasInvestments">
          <span class="breakdown-label">Investments</span>
          <span class="breakdown-value">{{ formatCurrency(breakdown.investments) }}</span>
        </div>
        <div class="breakdown-item" v-if="hasCash">
          <span class="breakdown-label">Cash</span>
          <span class="breakdown-value">{{ formatCurrency(breakdown.cash) }}</span>
        </div>
        <div class="breakdown-item" v-if="hasBusiness">
          <span class="breakdown-label">Business</span>
          <span class="breakdown-value">{{ formatCurrency(breakdown.business) }}</span>
        </div>
        <div class="breakdown-item" v-if="hasChattels">
          <span class="breakdown-label">Chattels</span>
          <span class="breakdown-value">{{ formatCurrency(breakdown.chattels) }}</span>
        </div>
      </div>

      <div class="card-footer">
        <span class="view-details">View Details →</span>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapGetters, mapActions } from 'vuex';

export default {
  name: 'NetWorthOverviewCard',

  computed: {
    ...mapState('netWorth', ['loading', 'error', 'overview']),
    ...mapGetters('netWorth', ['formattedNetWorth', 'netWorth']),

    breakdown() {
      return this.overview.breakdown || {};
    },

    hasProperty() {
      return this.breakdown.property > 0;
    },

    hasInvestments() {
      return this.breakdown.investments > 0;
    },

    hasCash() {
      return this.breakdown.cash > 0;
    },

    hasBusiness() {
      return this.breakdown.business > 0;
    },

    hasChattels() {
      return this.breakdown.chattels > 0;
    },

    netWorthClass() {
      if (this.netWorth < 0) {
        return 'negative';
      } else if (this.netWorth > 0) {
        return 'positive';
      }
      return '';
    },
  },

  methods: {
    ...mapActions('netWorth', ['fetchOverview']),

    navigateToDetail() {
      this.$router.push('/net-worth/overview');
    },

    formatCurrency(value) {
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(value || 0);
    },

    async retry() {
      await this.fetchOverview();
    },
  },

  async mounted() {
    try {
      await this.fetchOverview();
    } catch (error) {
      console.error('Failed to load net worth overview:', error);
    }
  },
};
</script>

<style scoped>
.net-worth-overview-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  cursor: pointer;
  transition: all 0.2s ease;
  border: 1px solid #e5e7eb;
}

.net-worth-overview-card:hover {
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
  border-color: #3b82f6;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.card-title {
  font-size: 18px;
  font-weight: 600;
  color: #111827;
  margin: 0;
}

.card-icon {
  color: #3b82f6;
}

.card-icon svg {
  width: 24px;
  height: 24px;
}

.card-content {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.net-worth-value {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding-bottom: 16px;
  border-bottom: 1px solid #e5e7eb;
}

.value-label {
  font-size: 14px;
  color: #6b7280;
  font-weight: 500;
}

.value-amount {
  font-size: 32px;
  font-weight: 700;
  color: #111827;
}

.value-amount.positive {
  color: #10b981;
}

.value-amount.negative {
  color: #ef4444;
}

.asset-breakdown {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.breakdown-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 14px;
}

.breakdown-label {
  color: #6b7280;
  font-weight: 500;
}

.breakdown-value {
  color: #111827;
  font-weight: 600;
}

.card-footer {
  padding-top: 16px;
  border-top: 1px solid #e5e7eb;
}

.view-details {
  color: #3b82f6;
  font-size: 14px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.loading-skeleton {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.skeleton-text {
  background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
  background-size: 200% 100%;
  animation: loading 1.5s ease-in-out infinite;
  border-radius: 4px;
  height: 20px;
}

.skeleton-large {
  height: 40px;
  width: 60%;
}

.skeleton-small {
  height: 16px;
  width: 80%;
}

@keyframes loading {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

.error-state {
  padding: 20px;
  text-align: center;
}

.error-message {
  color: #ef4444;
  font-size: 14px;
  margin-bottom: 12px;
}

.retry-button {
  background: #3b82f6;
  color: white;
  padding: 8px 16px;
  border-radius: 6px;
  border: none;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: background 0.2s;
}

.retry-button:hover {
  background: #2563eb;
}

/* Mobile responsive */
@media (max-width: 768px) {
  .net-worth-overview-card {
    padding: 16px;
  }

  .card-title {
    font-size: 16px;
  }

  .value-amount {
    font-size: 24px;
  }

  .breakdown-item {
    font-size: 13px;
  }
}
</style>
