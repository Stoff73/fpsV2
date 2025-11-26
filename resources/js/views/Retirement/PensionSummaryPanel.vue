<template>
  <div class="pension-summary-panel">
    <!-- DC Pension Summary -->
    <div v-if="pensionType === 'dc'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <!-- Current Fund Value -->
      <div class="summary-card">
        <div class="card-icon bg-blue-100">
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <div class="card-content">
          <p class="card-label">Current Fund Value</p>
          <p class="card-value">{{ formatCurrency(pension.current_fund_value) }}</p>
        </div>
      </div>

      <!-- Monthly Contribution -->
      <div class="summary-card">
        <div class="card-icon bg-green-100">
          <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
          </svg>
        </div>
        <div class="card-content">
          <p class="card-label">Monthly Contribution</p>
          <p class="card-value">{{ formatCurrency(pension.monthly_contribution_amount || 0) }}</p>
          <p class="card-sublabel">{{ formatCurrency(annualContribution) }}/year</p>
        </div>
      </div>

      <!-- Retirement Age -->
      <div class="summary-card">
        <div class="card-icon bg-purple-100">
          <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
          </svg>
        </div>
        <div class="card-content">
          <p class="card-label">Retirement Age</p>
          <p class="card-value">{{ pension.retirement_age || 67 }}</p>
          <p class="card-sublabel">years old</p>
        </div>
      </div>

      <!-- Scheme Type -->
      <div class="summary-card">
        <div class="card-icon bg-amber-100">
          <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
          </svg>
        </div>
        <div class="card-content">
          <p class="card-label">Scheme Type</p>
          <p class="card-value">{{ formatDCPensionType(pension.pension_type) }}</p>
        </div>
      </div>

      <!-- Provider -->
      <div class="summary-card">
        <div class="card-icon bg-indigo-100">
          <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
          </svg>
        </div>
        <div class="card-content">
          <p class="card-label">Provider</p>
          <p class="card-value text-base">{{ pension.provider || 'Not specified' }}</p>
        </div>
      </div>

      <!-- Lump Sum Contributions -->
      <div class="summary-card">
        <div class="card-icon bg-teal-100">
          <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
          </svg>
        </div>
        <div class="card-content">
          <p class="card-label">Lump Sum Contribution</p>
          <p class="card-value">{{ formatCurrency(pension.lump_sum_contribution || 0) }}</p>
        </div>
      </div>
    </div>

    <!-- DB Pension Summary -->
    <div v-else-if="pensionType === 'db'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <!-- Annual Income -->
      <div class="summary-card">
        <div class="card-icon bg-purple-100">
          <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <div class="card-content">
          <p class="card-label">Annual Income</p>
          <p class="card-value">{{ formatCurrency(pension.annual_income) }}</p>
          <p class="card-sublabel">{{ formatCurrency((pension.annual_income || 0) / 12) }}/month</p>
        </div>
      </div>

      <!-- Payment Start Age -->
      <div class="summary-card">
        <div class="card-icon bg-blue-100">
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
          </svg>
        </div>
        <div class="card-content">
          <p class="card-label">Payment Start Age</p>
          <p class="card-value">{{ pension.payment_start_age || 67 }}</p>
          <p class="card-sublabel">years old</p>
        </div>
      </div>

      <!-- Lump Sum Entitlement -->
      <div class="summary-card">
        <div class="card-icon bg-green-100">
          <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
          </svg>
        </div>
        <div class="card-content">
          <p class="card-label">Lump Sum Entitlement</p>
          <p class="card-value">{{ formatCurrency(pension.lump_sum_entitlement || 0) }}</p>
        </div>
      </div>

      <!-- Scheme Type -->
      <div class="summary-card">
        <div class="card-icon bg-amber-100">
          <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
          </svg>
        </div>
        <div class="card-content">
          <p class="card-label">Scheme Type</p>
          <p class="card-value">{{ formatDBPensionType(pension.scheme_type) }}</p>
        </div>
      </div>

      <!-- Revaluation Rate -->
      <div class="summary-card">
        <div class="card-icon bg-indigo-100">
          <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
          </svg>
        </div>
        <div class="card-content">
          <p class="card-label">Revaluation Rate</p>
          <p class="card-value">{{ pension.revaluation_rate ? (pension.revaluation_rate * 100).toFixed(1) + '%' : 'N/A' }}</p>
        </div>
      </div>

      <!-- Employer -->
      <div class="summary-card">
        <div class="card-icon bg-teal-100">
          <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
          </svg>
        </div>
        <div class="card-content">
          <p class="card-label">Employer</p>
          <p class="card-value text-base">{{ pension.employer || pension.provider || 'Not specified' }}</p>
        </div>
      </div>
    </div>

    <!-- State Pension Summary -->
    <div v-else-if="pensionType === 'state'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <!-- Annual Forecast -->
      <div class="summary-card">
        <div class="card-icon bg-green-100">
          <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <div class="card-content">
          <p class="card-label">Annual Forecast</p>
          <p class="card-value">{{ formatCurrency(pension.state_pension_forecast_annual) }}</p>
          <p class="card-sublabel">{{ formatCurrency((pension.state_pension_forecast_annual || 0) / 52) }}/week</p>
        </div>
      </div>

      <!-- NI Years -->
      <div class="summary-card">
        <div class="card-icon bg-blue-100">
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <div class="card-content">
          <p class="card-label">NI Qualifying Years</p>
          <p class="card-value">{{ pension.ni_years_completed || 0 }} / 35</p>
          <p class="card-sublabel">{{ yearsRemaining }} more needed for full pension</p>
        </div>
      </div>

      <!-- State Pension Age -->
      <div class="summary-card">
        <div class="card-icon bg-purple-100">
          <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
          </svg>
        </div>
        <div class="card-content">
          <p class="card-label">State Pension Age</p>
          <p class="card-value">{{ pension.state_pension_age || 67 }}</p>
          <p class="card-sublabel">years old</p>
        </div>
      </div>

      <!-- Full State Pension Rate -->
      <div class="summary-card">
        <div class="card-icon bg-amber-100">
          <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
          </svg>
        </div>
        <div class="card-content">
          <p class="card-label">Full State Pension (2025/26)</p>
          <p class="card-value">{{ formatCurrency(11973) }}</p>
          <p class="card-sublabel">{{ formatCurrency(230.25) }}/week</p>
        </div>
      </div>

      <!-- Percentage of Full Pension -->
      <div class="summary-card">
        <div class="card-icon bg-indigo-100">
          <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
          </svg>
        </div>
        <div class="card-content">
          <p class="card-label">Percentage of Full Pension</p>
          <p class="card-value">{{ percentageOfFull }}%</p>
          <p class="card-sublabel">based on NI record</p>
        </div>
      </div>

      <!-- Deferral -->
      <div class="summary-card">
        <div class="card-icon bg-teal-100">
          <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <div class="card-content">
          <p class="card-label">Deferral Option</p>
          <p class="card-value text-base">+5.8% per year</p>
          <p class="card-sublabel">if delayed beyond SPA</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PensionSummaryPanel',

  props: {
    pension: {
      type: Object,
      required: true,
    },
    pensionType: {
      type: String,
      required: true,
    },
  },

  computed: {
    annualContribution() {
      return (this.pension.monthly_contribution_amount || 0) * 12;
    },

    yearsRemaining() {
      const completed = this.pension.ni_years_completed || 0;
      return Math.max(0, 35 - completed);
    },

    percentageOfFull() {
      const completed = this.pension.ni_years_completed || 0;
      return Math.min(100, Math.round((completed / 35) * 100));
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
.pension-summary-panel {
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.summary-card {
  background: white;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  padding: 20px;
  display: flex;
  align-items: flex-start;
  gap: 16px;
  transition: all 0.2s ease;
}

.summary-card:hover {
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.card-icon {
  flex-shrink: 0;
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.card-content {
  flex: 1;
  min-width: 0;
}

.card-label {
  font-size: 14px;
  font-weight: 500;
  color: #6b7280;
  margin-bottom: 4px;
}

.card-value {
  font-size: 24px;
  font-weight: 700;
  color: #111827;
  line-height: 1.2;
}

.card-sublabel {
  font-size: 13px;
  color: #9ca3af;
  margin-top: 4px;
}
</style>
