<template>
  <div class="retirement-readiness">
    <!-- Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
      <!-- Years to Retirement -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-2">
          <h3 class="text-sm font-medium text-gray-600">Years to Retirement</h3>
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <p class="text-3xl font-bold text-gray-900">{{ yearsToRetirement }}</p>
        <p class="text-sm text-gray-500 mt-1">Target age: {{ targetRetirementAge }}</p>
      </div>

      <!-- Projected Income -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-2">
          <h3 class="text-sm font-medium text-gray-600">Projected Income</h3>
          <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
          </svg>
        </div>
        <p class="text-xl font-semibold text-gray-500">Coming Soon</p>
        <p class="text-sm text-gray-400 mt-1">Not available in this version</p>
      </div>
    </div>

    <!-- Pensions Overview -->
    <div class="pension-overview">
      <div class="section-header-row">
        <h3 class="section-title">Your Pensions</h3>
        <button @click="showPensionForm = true" class="add-pension-btn">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="btn-icon">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Add Pension
        </button>
      </div>

      <!-- Empty State -->
      <div v-if="allPensions.length === 0" class="empty-state">
        <p class="empty-message">No pensions added yet.</p>
        <button @click="showPensionForm = true" class="add-pension-button">
          Add Your First Pension
        </button>
      </div>

      <!-- Pensions Grid -->
      <div v-else class="pensions-grid">
        <!-- DC Pensions -->
        <div
          v-for="pension in dcPensions"
          :key="'dc-' + pension.id"
          @click="viewPension('dc', pension.id)"
          class="pension-card"
        >
          <div class="card-header">
            <span
              :class="getOwnershipBadgeClass(pension.ownership_type)"
              class="ownership-badge"
            >
              {{ formatOwnershipType(pension.ownership_type) }}
            </span>
            <span class="badge badge-dc">
              DC Pension
            </span>
          </div>

          <div class="card-content">
            <h4 class="pension-provider">{{ pension.provider }}</h4>
            <p class="pension-type">{{ pension.scheme_name || 'Defined Contribution' }}</p>

            <div class="pension-details">
              <div class="detail-row">
                <span class="detail-label">Current Value</span>
                <span class="detail-value">{{ formatCurrency(pension.current_fund_value) }}</span>
              </div>

              <div v-if="pension.contribution_amount" class="detail-row">
                <span class="detail-label">Monthly Contribution</span>
                <span class="detail-value">{{ formatCurrency(pension.contribution_amount) }}</span>
              </div>

              <div v-if="pension.projected_retirement_value" class="detail-row">
                <span class="detail-label">Projected at {{ targetRetirementAge }}</span>
                <span class="detail-value">{{ formatCurrency(pension.projected_retirement_value) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- DB Pensions -->
        <div
          v-for="pension in dbPensions"
          :key="'db-' + pension.id"
          @click="viewPension('db', pension.id)"
          class="pension-card"
        >
          <div class="card-header">
            <span
              :class="getOwnershipBadgeClass(pension.ownership_type)"
              class="ownership-badge"
            >
              {{ formatOwnershipType(pension.ownership_type) }}
            </span>
            <span class="badge badge-db">
              DB Pension
            </span>
          </div>

          <div class="card-content">
            <h4 class="pension-provider">{{ pension.provider }}</h4>
            <p class="pension-type">{{ pension.scheme_name || 'Defined Benefit' }}</p>

            <div class="pension-details">
              <div class="detail-row">
                <span class="detail-label">Annual Income</span>
                <span class="detail-value">{{ formatCurrency(pension.annual_income) }}<span class="text-xs">/year</span></span>
              </div>

              <div v-if="pension.payment_start_age" class="detail-row">
                <span class="detail-label">Payment Start</span>
                <span class="detail-value">Age {{ pension.payment_start_age }}</span>
              </div>

              <div v-if="pension.revaluation_rate" class="detail-row">
                <span class="detail-label">Revaluation</span>
                <span class="detail-value">{{ (pension.revaluation_rate * 100).toFixed(1) }}%</span>
              </div>
            </div>
          </div>
        </div>

        <!-- State Pension -->
        <div
          v-if="statePension"
          @click="viewPension('state', 'state')"
          class="pension-card"
        >
          <div class="card-header">
            <span class="ownership-badge bg-gray-100 text-gray-800">
              Individual
            </span>
            <span class="badge badge-state">
              State Pension
            </span>
          </div>

          <div class="card-content">
            <h4 class="pension-provider">UK State Pension</h4>
            <p class="pension-type">State Retirement Pension</p>

            <div class="pension-details">
              <div class="detail-row">
                <span class="detail-label">Forecast</span>
                <span class="detail-value">{{ formatCurrency(statePensionForecast) }}<span class="text-xs">/year</span></span>
              </div>

              <div class="detail-row">
                <span class="detail-label">NI Years</span>
                <span class="detail-value">{{ niYears }} / 35</span>
              </div>

              <div class="detail-row">
                <span class="detail-label">Payment Age</span>
                <span class="detail-value">{{ statePension.payment_start_age || 67 }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Pension Wealth Summary -->
    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-6">Pension Wealth Summary</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- DC Pensions -->
        <div class="border-l-4 border-blue-500 pl-4">
          <p class="text-sm text-gray-600 mb-1">DC Pensions</p>
          <p class="text-2xl font-bold text-gray-900">£{{ dcPensionValue.toLocaleString() }}</p>
          <p class="text-sm text-gray-500 mt-1">{{ dcPensionCount }} pension{{ dcPensionCount !== 1 ? 's' : '' }}</p>
        </div>

        <!-- DB Pensions -->
        <div class="border-l-4 border-purple-500 pl-4">
          <p class="text-sm text-gray-600 mb-1">DB Pensions</p>
          <p class="text-2xl font-bold text-gray-900">£{{ dbPensionIncome.toLocaleString() }}<span class="text-sm text-gray-500">/year</span></p>
          <p class="text-sm text-gray-500 mt-1">{{ dbPensionCount }} scheme{{ dbPensionCount !== 1 ? 's' : '' }}</p>
        </div>

        <!-- State Pension -->
        <div class="border-l-4 border-green-500 pl-4">
          <p class="text-sm text-gray-600 mb-1">State Pension</p>
          <p class="text-2xl font-bold text-gray-900">£{{ statePensionForecast.toLocaleString() }}<span class="text-sm text-gray-500">/year</span></p>
          <p class="text-sm text-gray-500 mt-1">{{ niYears }} NI years</p>
        </div>
      </div>
    </div>

    <!-- Unified Pension Form Modal -->
    <UnifiedPensionForm
      v-if="showPensionForm"
      :pension="selectedPension"
      :state-pension="statePension"
      :is-edit="isEditMode"
      @close="closePensionForm"
      @save="handlePensionSave"
    />
  </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex';
import UnifiedPensionForm from '../../components/Retirement/UnifiedPensionForm.vue';

export default {
  name: 'RetirementReadiness',

  components: {
    UnifiedPensionForm,
  },

  data() {
    return {
      showPensionForm: false,
      selectedPension: null,
      isEditMode: false,
    };
  },

  computed: {
    ...mapState('retirement', ['dcPensions', 'dbPensions', 'statePension', 'profile']),
    ...mapGetters('retirement', ['yearsToRetirement']),

    targetRetirementAge() {
      return this.profile?.target_retirement_age || 67;
    },

    dcPensionValue() {
      return this.dcPensions.reduce((sum, p) => sum + parseFloat(p.current_fund_value || 0), 0);
    },

    dcPensionCount() {
      return this.dcPensions.length;
    },

    dbPensionIncome() {
      return this.dbPensions.reduce((sum, p) => sum + parseFloat(p.annual_income || 0), 0);
    },

    dbPensionCount() {
      return this.dbPensions.length;
    },

    statePensionForecast() {
      return parseFloat(this.statePension?.forecast_weekly_amount || 0) * 52;
    },

    niYears() {
      return this.statePension?.qualifying_years || 0;
    },

    allPensions() {
      const all = [...this.dcPensions, ...this.dbPensions];
      if (this.statePension) {
        all.push(this.statePension);
      }
      return all;
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

    formatOwnershipType(type) {
      const types = {
        individual: 'Individual',
        joint: 'Joint',
        trust: 'Trust',
      };
      return types[type] || 'Individual';
    },

    getOwnershipBadgeClass(type) {
      const classes = {
        individual: 'bg-gray-100 text-gray-800',
        joint: 'bg-purple-100 text-purple-800',
        trust: 'bg-amber-100 text-amber-800',
      };
      return classes[type] || 'bg-gray-100 text-gray-800';
    },

    closePensionForm() {
      this.showPensionForm = false;
      this.selectedPension = null;
      this.isEditMode = false;
    },

    handlePensionSave(data) {
      // The UnifiedPensionForm's child components handle saving
      // This just closes the form
      this.closePensionForm();
    },

    viewPension(type, id) {
      // Emit event to parent to change tab
      this.$emit('change-tab', 'inventory');
    },
  },
};
</script>

<style scoped>
/* Animations */
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

.retirement-readiness > div {
  animation: fadeIn 0.5s ease-out;
}

/* Pension Cards Styling */
.pension-overview {
  margin-bottom: 24px;
}

.section-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 16px;
}

.section-title {
  font-size: 20px;
  font-weight: 600;
  color: #111827;
  margin: 0;
}

.add-pension-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.add-pension-btn:hover {
  background: #2563eb;
}

.btn-icon {
  width: 20px;
  height: 20px;
}

.pensions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 20px;
}

.pension-card {
  background: white;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  padding: 20px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.pension-card:hover {
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
  border-color: #3b82f6;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
  flex-wrap: wrap;
  gap: 8px;
}

.ownership-badge {
  display: inline-block;
  padding: 4px 12px;
  font-size: 12px;
  font-weight: 600;
  border-radius: 6px;
}

.badge {
  display: inline-block;
  padding: 4px 10px;
  font-size: 11px;
  font-weight: 600;
  border-radius: 6px;
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

.card-content {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.pension-provider {
  font-size: 18px;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.pension-type {
  font-size: 14px;
  color: #6b7280;
  margin: 0;
}

.pension-details {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-top: 4px;
  padding-top: 12px;
  border-top: 1px solid #e5e7eb;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.detail-label {
  font-size: 14px;
  color: #6b7280;
  font-weight: 500;
}

.detail-value {
  font-size: 16px;
  color: #111827;
  font-weight: 700;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  background: white;
  border-radius: 12px;
  border: 2px dashed #d1d5db;
}

.empty-message {
  color: #6b7280;
  font-size: 16px;
  margin-bottom: 20px;
}

.add-pension-button {
  padding: 12px 24px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.add-pension-button:hover {
  background: #2563eb;
}

@media (max-width: 768px) {
  .section-header-row {
    flex-direction: column;
    align-items: flex-start;
  }

  .add-pension-btn {
    width: 100%;
    justify-content: center;
  }

  .pensions-grid {
    grid-template-columns: 1fr;
  }
}
</style>
