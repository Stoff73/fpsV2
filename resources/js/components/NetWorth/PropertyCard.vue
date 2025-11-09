<template>
  <div class="property-card" @click="viewDetails">
    <div class="card-header">
      <span class="property-type-badge" :class="typeClass">
        {{ propertyTypeLabel }}
      </span>
      <span v-if="isJoint" class="ownership-badge">
        Joint ({{ property.ownership_percentage }}%)
      </span>
    </div>

    <div class="card-content">
      <h3 class="property-address">{{ property.address_line_1 }}</h3>
      <p v-if="property.address_line_2" class="property-address-2">
        {{ property.address_line_2 }}
      </p>
      <p class="property-location">
        {{ property.city }}, {{ property.postcode }}
      </p>

      <div class="property-details">
        <div class="detail-row">
          <span class="detail-label">Current Value</span>
          <span class="detail-value">{{ formatCurrency(property.current_value) }}</span>
        </div>

        <div v-if="hasMortgage" class="detail-row">
          <span class="detail-label">Mortgage Outstanding</span>
          <span class="detail-value mortgage">{{ formatCurrency(mortgageAmount) }}</span>
        </div>

        <div class="detail-row equity">
          <span class="detail-label">Equity</span>
          <span class="detail-value">{{ formatCurrency(equity) }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PropertyCard',

  props: {
    property: {
      type: Object,
      required: true,
    },
  },

  computed: {
    propertyTypeLabel() {
      const labels = {
        main_residence: 'Main Residence',
        secondary_residence: 'Secondary',
        buy_to_let: 'Buy to Let',
      };
      return labels[this.property.property_type] || this.property.property_type;
    },

    typeClass() {
      return `type-${this.property.property_type}`;
    },

    isJoint() {
      return this.property.ownership_type === 'joint';
    },

    hasMortgage() {
      return this.property.outstanding_mortgage > 0;
    },

    mortgageAmount() {
      return this.property.outstanding_mortgage || 0;
    },

    equity() {
      const value = this.property.current_value || 0;
      const mortgage = this.mortgageAmount;
      const ownershipPercent = this.property.ownership_percentage || 100;
      return (value - mortgage) * (ownershipPercent / 100);
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
      this.$router.push(`/property/${this.property.id}`);
    },
  },
};
</script>

<style scoped>
.property-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
  cursor: pointer;
  transition: all 0.2s;
}

.property-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  border-colour: #3b82f6;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: centre;
  margin-bottom: 16px;
  flex-wrap: wrap;
  gap: 8px;
}

.property-type-badge {
  padding: 4px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
}

.type-main_residence {
  background: #dbeafe;
  colour: #1e40af;
}

.type-secondary_residence {
  background: #fef3c7;
  colour: #92400e;
}

.type-buy_to_let {
  background: #d1fae5;
  colour: #065f46;
}

.ownership-badge {
  padding: 4px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  background: #f3e8ff;
  colour: #6b21a8;
}

.card-content {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.property-address {
  font-size: 18px;
  font-weight: 600;
  colour: #111827;
  margin: 0;
}

.property-address-2 {
  font-size: 14px;
  colour: #6b7280;
  margin: 0;
}

.property-location {
  font-size: 14px;
  colour: #6b7280;
  margin: 0;
}

.property-details {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding-top: 12px;
  border-top: 1px solid #e5e7eb;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: centre;
  font-size: 14px;
}

.detail-row.equity {
  padding-top: 8px;
  border-top: 1px solid #e5e7eb;
  font-weight: 600;
}

.detail-label {
  colour: #6b7280;
}

.detail-value {
  colour: #111827;
  font-weight: 600;
}

.detail-value.mortgage {
  colour: #ef4444;
}

@media (max-width: 768px) {
  .property-card {
    padding: 16px;
  }

  .property-address {
    font-size: 16px;
  }
}
</style>
