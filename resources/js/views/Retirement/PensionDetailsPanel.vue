<template>
  <div class="pension-details-panel">
    <!-- DC Pension Details -->
    <div v-if="pensionType === 'dc'" class="details-container">
      <!-- Basic Information -->
      <div class="details-section">
        <h3 class="section-title">Basic Information</h3>
        <div class="details-grid">
          <div class="detail-item">
            <span class="detail-label">Scheme Name</span>
            <span class="detail-value">{{ pension.scheme_name || 'Not specified' }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Provider</span>
            <span class="detail-value">{{ pension.provider || 'Not specified' }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Policy Number</span>
            <span class="detail-value">{{ pension.policy_number || 'Not specified' }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Pension Type</span>
            <span class="detail-value">{{ formatDCPensionType(pension.pension_type) }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Ownership</span>
            <span class="detail-value">{{ formatOwnershipType(pension.ownership_type) }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Date Started</span>
            <span class="detail-value">{{ formatDate(pension.start_date) }}</span>
          </div>
        </div>
      </div>

      <!-- Fund Information -->
      <div class="details-section">
        <h3 class="section-title">Fund Information</h3>
        <div class="details-grid">
          <div class="detail-item">
            <span class="detail-label">Current Fund Value</span>
            <span class="detail-value highlight">{{ formatCurrency(pension.current_fund_value) }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Valuation Date</span>
            <span class="detail-value">{{ formatDate(pension.valuation_date) }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Retirement Age</span>
            <span class="detail-value">{{ pension.retirement_age || 67 }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Expected Growth Rate</span>
            <span class="detail-value">{{ pension.expected_growth_rate ? (pension.expected_growth_rate * 100).toFixed(1) + '%' : 'N/A' }}</span>
          </div>
        </div>
      </div>

      <!-- Contribution Details -->
      <div class="details-section">
        <h3 class="section-title">Contribution Details</h3>
        <div class="details-grid">
          <div class="detail-item">
            <span class="detail-label">Monthly Contribution</span>
            <span class="detail-value">{{ formatCurrency(pension.monthly_contribution_amount || 0) }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Employer Contribution</span>
            <span class="detail-value">{{ formatCurrency(pension.employer_contribution_amount || 0) }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Lump Sum Contributions</span>
            <span class="detail-value">{{ formatCurrency(pension.lump_sum_contribution || 0) }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Salary Sacrifice</span>
            <span class="detail-value">{{ pension.salary_sacrifice ? 'Yes' : 'No' }}</span>
          </div>
        </div>
      </div>

      <!-- Holdings (if available) -->
      <div v-if="pension.holdings && pension.holdings.length > 0" class="details-section">
        <h3 class="section-title">Fund Holdings</h3>
        <div class="holdings-table">
          <table class="w-full">
            <thead>
              <tr>
                <th class="text-left">Fund Name</th>
                <th class="text-right">Value</th>
                <th class="text-right">Allocation</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="holding in pension.holdings" :key="holding.id">
                <td>{{ holding.fund_name || holding.name }}</td>
                <td class="text-right">{{ formatCurrency(holding.value || holding.current_value) }}</td>
                <td class="text-right">{{ holding.allocation ? (holding.allocation * 100).toFixed(1) + '%' : 'N/A' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Notes -->
      <div v-if="pension.notes" class="details-section">
        <h3 class="section-title">Notes</h3>
        <p class="notes-text">{{ pension.notes }}</p>
      </div>
    </div>

    <!-- DB Pension Details -->
    <div v-else-if="pensionType === 'db'" class="details-container">
      <!-- Basic Information -->
      <div class="details-section">
        <h3 class="section-title">Basic Information</h3>
        <div class="details-grid">
          <div class="detail-item">
            <span class="detail-label">Scheme Name</span>
            <span class="detail-value">{{ pension.scheme_name || 'Not specified' }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Employer</span>
            <span class="detail-value">{{ pension.employer || pension.provider || 'Not specified' }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Scheme Type</span>
            <span class="detail-value">{{ formatDBPensionType(pension.scheme_type) }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Ownership</span>
            <span class="detail-value">{{ formatOwnershipType(pension.ownership_type) }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Date Joined</span>
            <span class="detail-value">{{ formatDate(pension.date_joined) }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Date Left</span>
            <span class="detail-value">{{ formatDate(pension.date_left) || 'Currently Active' }}</span>
          </div>
        </div>
      </div>

      <!-- Benefit Details -->
      <div class="details-section">
        <h3 class="section-title">Benefit Details</h3>
        <div class="details-grid">
          <div class="detail-item">
            <span class="detail-label">Annual Income</span>
            <span class="detail-value highlight">{{ formatCurrency(pension.annual_income) }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Payment Start Age</span>
            <span class="detail-value">{{ pension.payment_start_age || 67 }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Lump Sum Entitlement</span>
            <span class="detail-value">{{ formatCurrency(pension.lump_sum_entitlement || 0) }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Revaluation Rate</span>
            <span class="detail-value">{{ pension.revaluation_rate ? (pension.revaluation_rate * 100).toFixed(1) + '%' : 'N/A' }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Escalation Rate</span>
            <span class="detail-value">{{ pension.escalation_rate ? (pension.escalation_rate * 100).toFixed(1) + '%' : 'N/A' }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">CETV</span>
            <span class="detail-value">{{ formatCurrency(pension.cash_equivalent_transfer_value || 0) }}</span>
          </div>
        </div>
      </div>

      <!-- Spouse Benefits -->
      <div class="details-section">
        <h3 class="section-title">Spouse Benefits</h3>
        <div class="details-grid">
          <div class="detail-item">
            <span class="detail-label">Spouse Pension</span>
            <span class="detail-value">{{ pension.spouse_pension_percentage ? pension.spouse_pension_percentage + '%' : 'N/A' }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Death in Service</span>
            <span class="detail-value">{{ pension.death_in_service_benefit ? 'Yes' : 'No' }}</span>
          </div>
        </div>
      </div>

      <!-- Notes -->
      <div v-if="pension.notes" class="details-section">
        <h3 class="section-title">Notes</h3>
        <p class="notes-text">{{ pension.notes }}</p>
      </div>
    </div>

    <!-- State Pension Details -->
    <div v-else-if="pensionType === 'state'" class="details-container">
      <!-- Basic Information -->
      <div class="details-section">
        <h3 class="section-title">Basic Information</h3>
        <div class="details-grid">
          <div class="detail-item">
            <span class="detail-label">State Pension Age</span>
            <span class="detail-value">{{ pension.state_pension_age || 67 }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Annual Forecast</span>
            <span class="detail-value highlight">{{ formatCurrency(pension.state_pension_forecast_annual) }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Weekly Forecast</span>
            <span class="detail-value">{{ formatCurrency((pension.state_pension_forecast_annual || 0) / 52) }}</span>
          </div>
        </div>
      </div>

      <!-- National Insurance Record -->
      <div class="details-section">
        <h3 class="section-title">National Insurance Record</h3>
        <div class="details-grid">
          <div class="detail-item">
            <span class="detail-label">NI Years Completed</span>
            <span class="detail-value">{{ pension.ni_years_completed || 0 }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Years Required</span>
            <span class="detail-value">35 (for full pension)</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Years Remaining</span>
            <span class="detail-value">{{ Math.max(0, 35 - (pension.ni_years_completed || 0)) }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">NI Record Status</span>
            <span class="detail-value">{{ niRecordStatus }}</span>
          </div>
        </div>
      </div>

      <!-- Full State Pension Info -->
      <div class="details-section">
        <h3 class="section-title">Full State Pension (2025/26)</h3>
        <div class="details-grid">
          <div class="detail-item">
            <span class="detail-label">Full Annual Amount</span>
            <span class="detail-value">{{ formatCurrency(11973) }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Full Weekly Amount</span>
            <span class="detail-value">{{ formatCurrency(230.25) }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Your Percentage</span>
            <span class="detail-value">{{ percentageOfFull }}%</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Shortfall</span>
            <span class="detail-value">{{ formatCurrency(Math.max(0, 11973 - (pension.state_pension_forecast_annual || 0))) }}</span>
          </div>
        </div>
      </div>

      <!-- Notes -->
      <div v-if="pension.notes" class="details-section">
        <h3 class="section-title">Notes</h3>
        <p class="notes-text">{{ pension.notes }}</p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PensionDetailsPanel',

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
    niRecordStatus() {
      const years = this.pension.ni_years_completed || 0;
      if (years >= 35) return 'Full pension achieved';
      if (years >= 30) return 'Near full pension';
      if (years >= 20) return 'Building record';
      if (years >= 10) return 'Early stages';
      return 'Just started';
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

    formatDate(date) {
      if (!date) return 'Not specified';
      return new Date(date).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
      });
    },

    formatOwnershipType(type) {
      const types = {
        individual: 'Individual',
        joint: 'Joint',
        trust: 'Trust',
      };
      return types[type] || 'Individual';
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
.pension-details-panel {
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

.details-container {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.details-section {
  background: white;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  padding: 24px;
}

.section-title {
  font-size: 18px;
  font-weight: 600;
  color: #111827;
  margin-bottom: 20px;
  padding-bottom: 12px;
  border-bottom: 1px solid #e5e7eb;
}

.details-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 20px;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.detail-label {
  font-size: 14px;
  font-weight: 500;
  color: #6b7280;
}

.detail-value {
  font-size: 16px;
  font-weight: 600;
  color: #111827;
}

.detail-value.highlight {
  font-size: 20px;
  color: #059669;
}

.holdings-table {
  overflow-x: auto;
}

.holdings-table table {
  min-width: 100%;
  border-collapse: collapse;
}

.holdings-table th {
  font-size: 14px;
  font-weight: 600;
  color: #6b7280;
  padding: 12px 8px;
  border-bottom: 2px solid #e5e7eb;
}

.holdings-table td {
  font-size: 14px;
  color: #111827;
  padding: 12px 8px;
  border-bottom: 1px solid #f3f4f6;
}

.holdings-table tr:hover td {
  background: #f9fafb;
}

.notes-text {
  font-size: 14px;
  color: #4b5563;
  line-height: 1.6;
  white-space: pre-wrap;
}

@media (max-width: 768px) {
  .details-grid {
    grid-template-columns: 1fr;
  }
}
</style>
