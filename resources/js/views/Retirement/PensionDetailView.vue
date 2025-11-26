<template>
  <div class="pension-detail-view">
    <!-- Pension Header -->
    <div class="pension-header bg-white rounded-lg shadow p-6 mb-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <div :class="['pension-type-badge', pensionTypeBadgeClass]">
            {{ pensionTypeLabel }}
          </div>
          <div class="ml-4">
            <h2 class="text-2xl font-bold text-gray-900">{{ pensionName }}</h2>
            <p class="text-gray-600">{{ pensionProvider }}</p>
          </div>
        </div>
        <div class="text-right">
          <p class="text-sm text-gray-600">{{ valueLabel }}</p>
          <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(pensionValue) }}</p>
        </div>
      </div>
    </div>

    <!-- Panel Content -->
    <transition name="fade" mode="out-in">
      <PensionSummaryPanel
        v-if="activeTab === 'summary'"
        :pension="pension"
        :pension-type="pensionType"
      />
      <PensionDetailsPanel
        v-else-if="activeTab === 'details'"
        :pension="pension"
        :pension-type="pensionType"
      />
      <PensionContributionsPanel
        v-else-if="activeTab === 'contributions'"
        :pension="pension"
        :pension-type="pensionType"
      />
      <PensionProjectionsPanel
        v-else-if="activeTab === 'projections'"
        :pension="pension"
        :pension-type="pensionType"
      />
      <PensionAnalysisPanel
        v-else-if="activeTab === 'analysis'"
        :pension="pension"
        :pension-type="pensionType"
      />
    </transition>
  </div>
</template>

<script>
import PensionSummaryPanel from './PensionSummaryPanel.vue';
import PensionDetailsPanel from './PensionDetailsPanel.vue';
import PensionContributionsPanel from './PensionContributionsPanel.vue';
import PensionProjectionsPanel from './PensionProjectionsPanel.vue';
import PensionAnalysisPanel from './PensionAnalysisPanel.vue';

export default {
  name: 'PensionDetailView',

  components: {
    PensionSummaryPanel,
    PensionDetailsPanel,
    PensionContributionsPanel,
    PensionProjectionsPanel,
    PensionAnalysisPanel,
  },

  props: {
    pension: {
      type: Object,
      required: true,
    },
    pensionType: {
      type: String,
      required: true,
      validator: value => ['dc', 'db', 'state'].includes(value),
    },
    activeTab: {
      type: String,
      default: 'summary',
    },
  },

  computed: {
    pensionTypeBadgeClass() {
      const classes = {
        dc: 'badge-dc',
        db: 'badge-db',
        state: 'badge-state',
      };
      return classes[this.pensionType] || 'badge-dc';
    },

    pensionTypeLabel() {
      if (this.pensionType === 'dc') {
        return this.formatDCPensionType(this.pension.pension_type);
      } else if (this.pensionType === 'db') {
        return this.formatDBPensionType(this.pension.scheme_type);
      }
      return 'State Pension';
    },

    pensionName() {
      if (this.pensionType === 'state') {
        return 'UK State Pension';
      }
      return this.pension.scheme_name || (this.pensionType === 'dc' ? 'Defined Contribution' : 'Defined Benefit');
    },

    pensionProvider() {
      if (this.pensionType === 'state') {
        return 'State Retirement Pension';
      }
      return this.pension.provider || '';
    },

    valueLabel() {
      if (this.pensionType === 'dc') {
        return 'Current Value';
      } else if (this.pensionType === 'db') {
        return 'Annual Income';
      }
      return 'Annual Forecast';
    },

    pensionValue() {
      if (this.pensionType === 'dc') {
        return this.pension.current_fund_value || 0;
      } else if (this.pensionType === 'db') {
        return this.pension.annual_income || 0;
      }
      return this.pension.state_pension_forecast_annual || 0;
    },
  },

  methods: {
    formatCurrency(value) {
      if (value === null || value === undefined) return '£0';
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(value);
    },

    formatDCPensionType(type) {
      const types = {
        occupational: 'Occupational',
        sipp: 'SIPP',
        personal: 'Personal',
        stakeholder: 'Stakeholder',
        workplace: 'Workplace',
      };
      return types[type] || 'DC Pension';
    },

    formatDBPensionType(type) {
      const types = {
        final_salary: 'Final Salary',
        career_average: 'Career Average',
        public_sector: 'Public Sector',
      };
      return types[type] || 'DB Pension';
    },
  },
};
</script>

<style scoped>
.pension-detail-view {
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.pension-header {
  border-left: 4px solid #3b82f6;
}

.pension-type-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 8px 16px;
  font-size: 14px;
  font-weight: 600;
  border-radius: 8px;
}

.badge-dc {
  background: #dbeafe;
  color: #1e40af;
}

.badge-db {
  background: #e9d5ff;
  color: #6b21a8;
}

.badge-state {
  background: #d1fae5;
  color: #065f46;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
