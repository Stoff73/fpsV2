<template>
  <div class="business-interest-card" @click="viewDetails">
    <div class="card-header">
      <span class="business-type-badge" :class="typeClass">
        {{ businessTypeLabel }}
      </span>
      <span v-if="isJoint" class="ownership-badge">
        {{ business.ownership_percentage }}%
      </span>
    </div>

    <div class="card-content">
      <h3 class="business-name">{{ business.business_name }}</h3>

      <div class="business-details">
        <div class="detail-row highlighted">
          <span class="detail-label">Valuation</span>
          <span class="detail-value">{{ formatCurrency(business.current_valuation) }}</span>
        </div>

        <div v-if="hasRevenue" class="detail-row">
          <span class="detail-label">Annual Revenue</span>
          <span class="detail-value">{{ formatCurrency(business.annual_revenue) }}</span>
        </div>

        <div v-if="hasProfit" class="detail-row">
          <span class="detail-label">Annual Profit</span>
          <span class="detail-value">{{ formatCurrency(business.annual_profit) }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'BusinessInterestCard',

  props: {
    business: {
      type: Object,
      required: true,
    },
  },

  computed: {
    businessTypeLabel() {
      const labels = {
        sole_trader: 'Sole Trader',
        partnership: 'Partnership',
        limited_company: 'Limited Company',
        llp: 'LLP',
      };
      return labels[this.business.business_type] || this.business.business_type;
    },

    typeClass() {
      return `type-${this.business.business_type}`;
    },

    isJoint() {
      return this.business.ownership_percentage < 100;
    },

    hasRevenue() {
      return this.business.annual_revenue && this.business.annual_revenue > 0;
    },

    hasProfit() {
      return this.business.annual_profit && this.business.annual_profit > 0;
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

    viewDetails() {
      // Navigate to business detail (Phase 4)
      // this.$router.push(`/net-worth/business/${this.business.id}`);
    },
  },
};
</script>

<style scoped>
.business-interest-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
  cursor: pointer;
  transition: all 0.2s;
}

.business-interest-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  border-color: #8b5cf6;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
  flex-wrap: wrap;
  gap: 8px;
}

.business-type-badge {
  padding: 4px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
}

.type-sole_trader {
  background: #dbeafe;
  color: #1e40af;
}

.type-partnership {
  background: #fef3c7;
  color: #92400e;
}

.type-limited_company {
  background: #d1fae5;
  color: #065f46;
}

.type-llp {
  background: #f3e8ff;
  color: #6b21a8;
}

.ownership-badge {
  padding: 4px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  background: #e0e7ff;
  color: #3730a3;
}

.card-content {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.business-name {
  font-size: 18px;
  font-weight: 600;
  color: #111827;
  margin: 0;
}

.business-details {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding-top: 12px;
  border-top: 1px solid #e5e7eb;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 14px;
}

.detail-row.highlighted {
  font-weight: 600;
  padding-bottom: 8px;
  border-bottom: 1px solid #e5e7eb;
}

.detail-label {
  color: #6b7280;
}

.detail-value {
  color: #111827;
  font-weight: 600;
}

@media (max-width: 768px) {
  .business-interest-card {
    padding: 16px;
  }

  .business-name {
    font-size: 16px;
  }
}
</style>
