<template>
  <div class="wealth-summary">
    <h3 class="chart-title">Wealth Summary</h3>

    <div v-if="hasData" class="summary-content">
      <!-- Single User or User Column -->
      <div :class="hasSpouse ? 'user-column' : 'single-user'">
        <h4 class="user-heading">{{ userName }}</h4>

        <!-- Assets Section -->
        <div class="section-block assets-section">
          <div class="section-header">
            <svg class="section-icon text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
            </svg>
            <h5 class="section-title">Assets</h5>
          </div>
          <div class="breakdown-items">
            <div v-if="userBreakdown.property > 0" class="breakdown-item">
              <span class="item-label">Property</span>
              <span class="item-value">{{ formatCurrency(userBreakdown.property) }}</span>
            </div>
            <div v-if="userBreakdown.investments > 0" class="breakdown-item">
              <span class="item-label">Investments</span>
              <span class="item-value">{{ formatCurrency(userBreakdown.investments) }}</span>
            </div>
            <div v-if="userBreakdown.cash > 0" class="breakdown-item">
              <span class="item-label">Cash & Savings</span>
              <span class="item-value">{{ formatCurrency(userBreakdown.cash) }}</span>
            </div>
            <div v-if="userBreakdown.pensions > 0" class="breakdown-item">
              <span class="item-label">Pensions</span>
              <span class="item-value">{{ formatCurrency(userBreakdown.pensions) }}</span>
            </div>
            <div v-if="userBreakdown.business > 0" class="breakdown-item">
              <span class="item-label">Business</span>
              <span class="item-value">{{ formatCurrency(userBreakdown.business) }}</span>
            </div>
            <div v-if="userBreakdown.chattels > 0" class="breakdown-item">
              <span class="item-label">Chattels</span>
              <span class="item-value">{{ formatCurrency(userBreakdown.chattels) }}</span>
            </div>
          </div>
          <div class="section-total assets-total">
            <span class="total-label">Total Assets</span>
            <span class="total-value">{{ formatCurrency(userTotalAssets) }}</span>
          </div>
        </div>

        <!-- Liabilities Section -->
        <div class="section-block liabilities-section">
          <div class="section-header">
            <svg class="section-icon text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181" />
            </svg>
            <h5 class="section-title">Liabilities</h5>
          </div>
          <div class="breakdown-items">
            <div v-if="userLiabilitiesBreakdown.mortgages > 0" class="breakdown-item">
              <span class="item-label">Mortgages</span>
              <span class="item-value">{{ formatCurrency(userLiabilitiesBreakdown.mortgages) }}</span>
            </div>
            <div v-if="userLiabilitiesBreakdown.loans > 0" class="breakdown-item">
              <span class="item-label">Loans</span>
              <span class="item-value">{{ formatCurrency(userLiabilitiesBreakdown.loans) }}</span>
            </div>
            <div v-if="userLiabilitiesBreakdown.credit_cards > 0" class="breakdown-item">
              <span class="item-label">Credit Cards</span>
              <span class="item-value">{{ formatCurrency(userLiabilitiesBreakdown.credit_cards) }}</span>
            </div>
            <div v-if="userLiabilitiesBreakdown.other > 0" class="breakdown-item">
              <span class="item-label">Other</span>
              <span class="item-value">{{ formatCurrency(userLiabilitiesBreakdown.other) }}</span>
            </div>
          </div>
          <div class="section-total liabilities-total">
            <span class="total-label">Total Liabilities</span>
            <span class="total-value">{{ formatCurrency(userTotalLiabilities) }}</span>
          </div>
        </div>

        <!-- Net Worth -->
        <div class="section-block net-worth-section">
          <div class="net-worth-total">
            <span class="net-worth-label">Net Worth</span>
            <span class="net-worth-value" :class="userNetWorthClass">{{ formatCurrency(userNetWorth) }}</span>
          </div>
        </div>
      </div>

      <!-- Spouse Column (if linked account exists) -->
      <div v-if="hasSpouse" class="user-column">
        <h4 class="user-heading">{{ spouseName }}</h4>

        <!-- Assets Section -->
        <div class="section-block assets-section">
          <div class="section-header">
            <svg class="section-icon text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
            </svg>
            <h5 class="section-title">Assets</h5>
          </div>
          <div class="breakdown-items">
            <div v-if="spouseBreakdown.property > 0" class="breakdown-item">
              <span class="item-label">Property</span>
              <span class="item-value">{{ formatCurrency(spouseBreakdown.property) }}</span>
            </div>
            <div v-if="spouseBreakdown.investments > 0" class="breakdown-item">
              <span class="item-label">Investments</span>
              <span class="item-value">{{ formatCurrency(spouseBreakdown.investments) }}</span>
            </div>
            <div v-if="spouseBreakdown.cash > 0" class="breakdown-item">
              <span class="item-label">Cash & Savings</span>
              <span class="item-value">{{ formatCurrency(spouseBreakdown.cash) }}</span>
            </div>
            <div v-if="spouseBreakdown.pensions > 0" class="breakdown-item">
              <span class="item-label">Pensions</span>
              <span class="item-value">{{ formatCurrency(spouseBreakdown.pensions) }}</span>
            </div>
            <div v-if="spouseBreakdown.business > 0" class="breakdown-item">
              <span class="item-label">Business</span>
              <span class="item-value">{{ formatCurrency(spouseBreakdown.business) }}</span>
            </div>
            <div v-if="spouseBreakdown.chattels > 0" class="breakdown-item">
              <span class="item-label">Chattels</span>
              <span class="item-value">{{ formatCurrency(spouseBreakdown.chattels) }}</span>
            </div>
          </div>
          <div class="section-total assets-total">
            <span class="total-label">Total Assets</span>
            <span class="total-value">{{ formatCurrency(spouseTotalAssets) }}</span>
          </div>
        </div>

        <!-- Liabilities Section -->
        <div class="section-block liabilities-section">
          <div class="section-header">
            <svg class="section-icon text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181" />
            </svg>
            <h5 class="section-title">Liabilities</h5>
          </div>
          <div class="breakdown-items">
            <div v-if="spouseLiabilitiesBreakdown.mortgages > 0" class="breakdown-item">
              <span class="item-label">Mortgages</span>
              <span class="item-value">{{ formatCurrency(spouseLiabilitiesBreakdown.mortgages) }}</span>
            </div>
            <div v-if="spouseLiabilitiesBreakdown.loans > 0" class="breakdown-item">
              <span class="item-label">Loans</span>
              <span class="item-value">{{ formatCurrency(spouseLiabilitiesBreakdown.loans) }}</span>
            </div>
            <div v-if="spouseLiabilitiesBreakdown.credit_cards > 0" class="breakdown-item">
              <span class="item-label">Credit Cards</span>
              <span class="item-value">{{ formatCurrency(spouseLiabilitiesBreakdown.credit_cards) }}</span>
            </div>
            <div v-if="spouseLiabilitiesBreakdown.other > 0" class="breakdown-item">
              <span class="item-label">Other</span>
              <span class="item-value">{{ formatCurrency(spouseLiabilitiesBreakdown.other) }}</span>
            </div>
          </div>
          <div class="section-total liabilities-total">
            <span class="total-label">Total Liabilities</span>
            <span class="total-value">{{ formatCurrency(spouseTotalLiabilities) }}</span>
          </div>
        </div>

        <!-- Net Worth -->
        <div class="section-block net-worth-section">
          <div class="net-worth-total">
            <span class="net-worth-label">Net Worth</span>
            <span class="net-worth-value" :class="spouseNetWorthClass">{{ formatCurrency(spouseNetWorth) }}</span>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="no-data">
      <p>No wealth data available</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'WealthSummary',

  props: {
    breakdown: {
      type: Object,
      required: true,
      default: () => ({}),
    },
    liabilitiesBreakdown: {
      type: Object,
      default: () => ({}),
    },
    totalAssets: {
      type: Number,
      default: 0,
    },
    totalLiabilities: {
      type: Number,
      default: 0,
    },
    spouseData: {
      type: Object,
      default: null,
    },
    userName: {
      type: String,
      default: 'Your Wealth',
    },
    spouseName: {
      type: String,
      default: 'Spouse Wealth',
    },
  },

  computed: {
    hasData() {
      return this.totalAssets > 0 || this.totalLiabilities > 0 || (this.spouseData && (this.spouseData.totalAssets > 0 || this.spouseData.totalLiabilities > 0));
    },

    hasSpouse() {
      return this.spouseData !== null && this.spouseData !== undefined;
    },

    userBreakdown() {
      return {
        property: this.breakdown.property || 0,
        investments: this.breakdown.investments || 0,
        cash: this.breakdown.cash || 0,
        pensions: this.breakdown.pensions || 0,
        business: this.breakdown.business || 0,
        chattels: this.breakdown.chattels || 0,
      };
    },

    userLiabilitiesBreakdown() {
      return {
        mortgages: this.liabilitiesBreakdown.mortgages || 0,
        loans: this.liabilitiesBreakdown.loans || 0,
        credit_cards: this.liabilitiesBreakdown.credit_cards || 0,
        other: this.liabilitiesBreakdown.other || 0,
      };
    },

    userTotalAssets() {
      return this.totalAssets;
    },

    userTotalLiabilities() {
      return this.totalLiabilities;
    },

    userNetWorth() {
      return this.userTotalAssets - this.userTotalLiabilities;
    },

    userNetWorthClass() {
      if (this.userNetWorth < 0) {
        return 'negative';
      } else if (this.userNetWorth > 0) {
        return 'positive';
      }
      return '';
    },

    spouseBreakdown() {
      if (!this.spouseData) return {};
      return {
        property: this.spouseData.breakdown?.property || 0,
        investments: this.spouseData.breakdown?.investments || 0,
        cash: this.spouseData.breakdown?.cash || 0,
        pensions: this.spouseData.breakdown?.pensions || 0,
        business: this.spouseData.breakdown?.business || 0,
        chattels: this.spouseData.breakdown?.chattels || 0,
      };
    },

    spouseLiabilitiesBreakdown() {
      if (!this.spouseData) return {};
      return {
        mortgages: this.spouseData.liabilitiesBreakdown?.mortgages || 0,
        loans: this.spouseData.liabilitiesBreakdown?.loans || 0,
        credit_cards: this.spouseData.liabilitiesBreakdown?.credit_cards || 0,
        other: this.spouseData.liabilitiesBreakdown?.other || 0,
      };
    },

    spouseTotalAssets() {
      return this.spouseData?.totalAssets || 0;
    },

    spouseTotalLiabilities() {
      return this.spouseData?.totalLiabilities || 0;
    },

    spouseNetWorth() {
      return this.spouseTotalAssets - this.spouseTotalLiabilities;
    },

    spouseNetWorthClass() {
      if (this.spouseNetWorth < 0) {
        return 'negative';
      } else if (this.spouseNetWorth > 0) {
        return 'positive';
      }
      return '';
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
.wealth-summary {
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
  margin: 0 0 24px 0;
}

.summary-content {
  display: grid;
  grid-template-columns: 1fr;
  gap: 32px;
}

.summary-content:has(.user-column) {
  grid-template-columns: 1fr 1fr;
  gap: 48px;
}

.single-user {
  max-width: 600px;
  margin: 0 auto;
}

.user-column {
  min-width: 0;
}

.user-heading {
  font-size: 16px;
  font-weight: 600;
  color: #111827;
  margin: 0 0 16px 0;
  padding-bottom: 8px;
  border-bottom: 2px solid #e5e7eb;
}

.section-block {
  margin-bottom: 20px;
}

.section-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 12px;
}

.section-icon {
  width: 20px;
  height: 20px;
}

.section-title {
  font-size: 14px;
  font-weight: 600;
  color: #6b7280;
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.breakdown-items {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 12px;
}

.breakdown-item {
  display: flex;
  justify-content: space-between;
  padding: 8px 12px;
  background: #f9fafb;
  border-radius: 6px;
}

.item-label {
  font-size: 14px;
  color: #6b7280;
  font-weight: 500;
}

.item-value {
  font-size: 14px;
  color: #111827;
  font-weight: 600;
}

.section-total {
  display: flex;
  justify-content: space-between;
  padding: 12px 12px;
  border-radius: 8px;
  margin-top: 8px;
}

.assets-total {
  background: #d1fae5;
  border: 1px solid #10b981;
}

.liabilities-total {
  background: #fee2e2;
  border: 1px solid #ef4444;
}

.total-label {
  font-size: 14px;
  font-weight: 600;
  color: #111827;
}

.total-value {
  font-size: 16px;
  font-weight: 700;
  color: #111827;
}

.net-worth-section {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 2px solid #e5e7eb;
}

.net-worth-total {
  display: flex;
  justify-content: space-between;
  padding: 16px;
  background: linear-gradient(135deg, #f0f9ff 0%, #ffffff 100%);
  border: 2px solid #3b82f6;
  border-radius: 8px;
}

.net-worth-label {
  font-size: 16px;
  font-weight: 700;
  color: #111827;
}

.net-worth-value {
  font-size: 20px;
  font-weight: 700;
  color: #111827;
}

.net-worth-value.positive {
  color: #10b981;
}

.net-worth-value.negative {
  color: #ef4444;
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

/* Mobile responsive */
@media (max-width: 1024px) {
  .summary-content:has(.user-column) {
    grid-template-columns: 1fr;
    gap: 32px;
  }
}

@media (max-width: 768px) {
  .wealth-summary {
    padding: 16px;
  }

  .chart-title {
    font-size: 16px;
    margin-bottom: 16px;
  }

  .user-heading {
    font-size: 14px;
  }

  .section-header {
    margin-bottom: 8px;
  }

  .section-icon {
    width: 16px;
    height: 16px;
  }

  .section-title {
    font-size: 12px;
  }

  .breakdown-item {
    padding: 6px 10px;
  }

  .item-label,
  .item-value {
    font-size: 13px;
  }

  .section-total {
    padding: 10px;
  }

  .total-label {
    font-size: 13px;
  }

  .total-value {
    font-size: 14px;
  }

  .net-worth-total {
    padding: 12px;
  }

  .net-worth-label {
    font-size: 14px;
  }

  .net-worth-value {
    font-size: 18px;
  }
}
</style>
