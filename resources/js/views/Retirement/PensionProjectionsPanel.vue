<template>
  <div class="pension-projections-panel">
    <!-- DC Pension Projections -->
    <div v-if="pensionType === 'dc'" class="projections-container">
      <!-- Projection Summary Cards -->
      <div class="projection-cards">
        <div class="projection-card low">
          <div class="card-header">
            <span class="scenario-label">Conservative</span>
            <span class="growth-rate">3% growth</span>
          </div>
          <p class="projected-value">{{ formatCurrency(projectedValueLow) }}</p>
          <p class="monthly-income">{{ formatCurrency(monthlyIncomeLow) }}/month</p>
        </div>

        <div class="projection-card medium featured">
          <div class="card-header">
            <span class="scenario-label">Moderate</span>
            <span class="growth-rate">5% growth</span>
          </div>
          <p class="projected-value">{{ formatCurrency(projectedValueMedium) }}</p>
          <p class="monthly-income">{{ formatCurrency(monthlyIncomeMedium) }}/month</p>
          <span class="featured-badge">Most Likely</span>
        </div>

        <div class="projection-card high">
          <div class="card-header">
            <span class="scenario-label">Optimistic</span>
            <span class="growth-rate">7% growth</span>
          </div>
          <p class="projected-value">{{ formatCurrency(projectedValueHigh) }}</p>
          <p class="monthly-income">{{ formatCurrency(monthlyIncomeHigh) }}/month</p>
        </div>
      </div>

      <!-- Projection Assumptions -->
      <div class="assumptions-section">
        <h3 class="section-title">Projection Assumptions</h3>
        <div class="assumptions-grid">
          <div class="assumption-item">
            <span class="assumption-label">Current Fund Value</span>
            <span class="assumption-value">{{ formatCurrency(pension.current_fund_value) }}</span>
          </div>
          <div class="assumption-item">
            <span class="assumption-label">Monthly Contribution</span>
            <span class="assumption-value">{{ formatCurrency(totalMonthlyContribution) }}</span>
          </div>
          <div class="assumption-item">
            <span class="assumption-label">Years to Retirement</span>
            <span class="assumption-value">{{ yearsToRetirement }} years</span>
          </div>
          <div class="assumption-item">
            <span class="assumption-label">Retirement Age</span>
            <span class="assumption-value">{{ pension.retirement_age || 67 }}</span>
          </div>
        </div>
      </div>

      <!-- Drawdown vs Annuity -->
      <div class="comparison-section">
        <h3 class="section-title">Retirement Income Options</h3>
        <div class="comparison-cards">
          <div class="comparison-card">
            <div class="card-icon drawdown">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
              </svg>
            </div>
            <h4>Flexible Drawdown</h4>
            <p class="comparison-value">{{ formatCurrency(monthlyDrawdown) }}/month</p>
            <p class="comparison-detail">Based on 4% sustainable withdrawal rate</p>
            <ul class="comparison-features">
              <li>Flexible income</li>
              <li>Investment growth potential</li>
              <li>Pass on remaining fund</li>
              <li>Income may vary</li>
            </ul>
          </div>

          <div class="comparison-card">
            <div class="card-icon annuity">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
              </svg>
            </div>
            <h4>Annuity Purchase</h4>
            <p class="comparison-value">{{ formatCurrency(monthlyAnnuity) }}/month</p>
            <p class="comparison-detail">Based on current annuity rates (~5%)</p>
            <ul class="comparison-features">
              <li>Guaranteed for life</li>
              <li>No investment risk</li>
              <li>Fixed income</li>
              <li>No inheritance value</li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Tax-Free Lump Sum -->
      <div class="lump-sum-section">
        <h3 class="section-title">Tax-Free Lump Sum (PCLS)</h3>
        <div class="lump-sum-grid">
          <div class="lump-sum-item">
            <span class="lump-sum-label">Maximum Tax-Free (25%)</span>
            <span class="lump-sum-value">{{ formatCurrency(taxFreeLumpSum) }}</span>
          </div>
          <div class="lump-sum-item">
            <span class="lump-sum-label">Remaining for Income</span>
            <span class="lump-sum-value">{{ formatCurrency(remainingAfterLumpSum) }}</span>
          </div>
          <div class="lump-sum-item">
            <span class="lump-sum-label">Monthly Income (after PCLS)</span>
            <span class="lump-sum-value">{{ formatCurrency(monthlyIncomeAfterPCLS) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- DB Pension Projections -->
    <div v-else-if="pensionType === 'db'" class="projections-container">
      <!-- Income Projections -->
      <div class="db-projection-cards">
        <div class="projection-card featured">
          <div class="card-header">
            <span class="scenario-label">Projected Annual Income</span>
            <span class="growth-rate">At Normal Pension Age</span>
          </div>
          <p class="projected-value">{{ formatCurrency(pension.annual_income) }}</p>
          <p class="monthly-income">{{ formatCurrency((pension.annual_income || 0) / 12) }}/month</p>
        </div>
      </div>

      <!-- Early Retirement Options -->
      <div class="early-retirement-section">
        <h3 class="section-title">Early Retirement Options</h3>
        <div class="early-retirement-grid">
          <div class="early-option" v-for="option in earlyRetirementOptions" :key="option.age">
            <div class="option-age">Age {{ option.age }}</div>
            <div class="option-reduction">{{ option.reduction }}% reduction</div>
            <div class="option-income">{{ formatCurrency(option.income) }}/year</div>
            <div class="option-monthly">{{ formatCurrency(option.income / 12) }}/month</div>
          </div>
        </div>
        <p class="reduction-note">Early retirement reductions are typically 3-6% per year before Normal Pension Age</p>
      </div>

      <!-- Commutation Options -->
      <div class="commutation-section">
        <h3 class="section-title">Commutation Options</h3>
        <div class="commutation-grid">
          <div class="commutation-item">
            <span class="commutation-label">Maximum Lump Sum</span>
            <span class="commutation-value">{{ formatCurrency(maxLumpSum) }}</span>
          </div>
          <div class="commutation-item">
            <span class="commutation-label">Reduced Annual Income</span>
            <span class="commutation-value">{{ formatCurrency(reducedAnnualIncome) }}</span>
          </div>
          <div class="commutation-item">
            <span class="commutation-label">Commutation Factor</span>
            <span class="commutation-value">12:1 (typical)</span>
          </div>
        </div>
        <p class="commutation-note">Taking the maximum lump sum reduces your annual income but provides tax-free cash</p>
      </div>

      <!-- Inflation Impact -->
      <div class="inflation-section">
        <h3 class="section-title">Inflation Impact ({{ pension.escalation_rate ? (pension.escalation_rate * 100).toFixed(1) + '% escalation' : 'No escalation' }})</h3>
        <div class="inflation-grid">
          <div class="inflation-item">
            <span class="inflation-label">Year 1</span>
            <span class="inflation-value">{{ formatCurrency(pension.annual_income) }}</span>
          </div>
          <div class="inflation-item">
            <span class="inflation-label">Year 10</span>
            <span class="inflation-value">{{ formatCurrency(incomeYear10) }}</span>
          </div>
          <div class="inflation-item">
            <span class="inflation-label">Year 20</span>
            <span class="inflation-value">{{ formatCurrency(incomeYear20) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- State Pension Projections -->
    <div v-else-if="pensionType === 'state'" class="projections-container">
      <!-- Current Projection -->
      <div class="state-projection-cards">
        <div class="projection-card featured">
          <div class="card-header">
            <span class="scenario-label">Current Projection</span>
            <span class="growth-rate">Based on NI record</span>
          </div>
          <p class="projected-value">{{ formatCurrency(pension.state_pension_forecast_annual) }}</p>
          <p class="monthly-income">{{ formatCurrency((pension.state_pension_forecast_annual || 0) / 52) }}/week</p>
        </div>

        <div class="projection-card">
          <div class="card-header">
            <span class="scenario-label">Full State Pension</span>
            <span class="growth-rate">35 NI years</span>
          </div>
          <p class="projected-value">{{ formatCurrency(11973) }}</p>
          <p class="monthly-income">{{ formatCurrency(230.25) }}/week</p>
        </div>
      </div>

      <!-- If NI Gaps Filled -->
      <div v-if="yearsNeeded > 0" class="gap-filling-section">
        <h3 class="section-title">If You Fill Your NI Gaps</h3>
        <div class="gap-filling-grid">
          <div class="gap-item">
            <span class="gap-label">Years to Fill</span>
            <span class="gap-value">{{ yearsNeeded }}</span>
          </div>
          <div class="gap-item">
            <span class="gap-label">Potential Annual Increase</span>
            <span class="gap-value">{{ formatCurrency(potentialIncrease) }}</span>
          </div>
          <div class="gap-item">
            <span class="gap-label">Cost to Fill Gaps</span>
            <span class="gap-value">{{ formatCurrency(costToFillGaps) }}</span>
          </div>
          <div class="gap-item">
            <span class="gap-label">Payback Period</span>
            <span class="gap-value">{{ paybackYears }} years</span>
          </div>
        </div>
      </div>

      <!-- Deferral Options -->
      <div class="deferral-section">
        <h3 class="section-title">Deferral Options</h3>
        <p class="deferral-intro">Delaying your State Pension increases it by 5.8% for each year you defer (approximately 1% every 9 weeks).</p>
        <div class="deferral-grid">
          <div class="deferral-item" v-for="option in deferralOptions" :key="option.years">
            <div class="deferral-years">Defer {{ option.years }} year{{ option.years > 1 ? 's' : '' }}</div>
            <div class="deferral-increase">+{{ option.increase }}%</div>
            <div class="deferral-annual">{{ formatCurrency(option.annual) }}/year</div>
            <div class="deferral-weekly">{{ formatCurrency(option.annual / 52) }}/week</div>
          </div>
        </div>
      </div>

      <!-- Triple Lock Info -->
      <div class="triple-lock-section">
        <h3 class="section-title">Triple Lock Protection</h3>
        <div class="triple-lock-info">
          <p>The State Pension is protected by the Triple Lock, meaning it increases each April by the highest of:</p>
          <ul>
            <li>Earnings growth (average wage increase)</li>
            <li>Price inflation (CPI)</li>
            <li>2.5%</li>
          </ul>
          <p class="triple-lock-note">This provides protection against inflation and ensures your State Pension maintains its purchasing power over time.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';

export default {
  name: 'PensionProjectionsPanel',

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
    ...mapState('retirement', ['profile']),

    // Years to retirement
    yearsToRetirement() {
      const currentAge = this.profile?.current_age || 40;
      const retirementAge = this.pension.retirement_age || this.pension.payment_start_age || this.pension.state_pension_age || 67;
      return Math.max(0, retirementAge - currentAge);
    },

    totalMonthlyContribution() {
      return (this.pension.monthly_contribution_amount || 0) + (this.pension.employer_contribution_amount || 0);
    },

    // DC Pension projections
    projectedValueLow() {
      return this.calculateProjectedValue(0.03);
    },

    projectedValueMedium() {
      return this.calculateProjectedValue(0.05);
    },

    projectedValueHigh() {
      return this.calculateProjectedValue(0.07);
    },

    monthlyIncomeLow() {
      return (this.projectedValueLow * 0.04) / 12;
    },

    monthlyIncomeMedium() {
      return (this.projectedValueMedium * 0.04) / 12;
    },

    monthlyIncomeHigh() {
      return (this.projectedValueHigh * 0.04) / 12;
    },

    monthlyDrawdown() {
      return (this.projectedValueMedium * 0.04) / 12;
    },

    monthlyAnnuity() {
      return (this.projectedValueMedium * 0.05) / 12;
    },

    taxFreeLumpSum() {
      return this.projectedValueMedium * 0.25;
    },

    remainingAfterLumpSum() {
      return this.projectedValueMedium * 0.75;
    },

    monthlyIncomeAfterPCLS() {
      return (this.remainingAfterLumpSum * 0.04) / 12;
    },

    // DB Pension projections
    earlyRetirementOptions() {
      const npa = this.pension.payment_start_age || 67;
      const annualIncome = this.pension.annual_income || 0;
      const options = [];

      for (let i = 5; i >= 1; i--) {
        const age = npa - i;
        if (age >= 55) {
          const reduction = i * 4; // Assume 4% reduction per year
          options.push({
            age,
            reduction,
            income: annualIncome * (1 - reduction / 100),
          });
        }
      }

      return options;
    },

    maxLumpSum() {
      // Typical commutation: exchange £1 of pension for £12 lump sum
      // Max tax-free is 25% of CETV or 25% of pension value
      const cetv = this.pension.cash_equivalent_transfer_value || 0;
      if (cetv > 0) {
        return cetv * 0.25;
      }
      // Rough estimate based on income
      return (this.pension.annual_income || 0) * 3;
    },

    reducedAnnualIncome() {
      // If taking max lump sum, reduce pension
      const annualIncome = this.pension.annual_income || 0;
      const reduction = this.maxLumpSum / 12; // Assume 12:1 commutation
      return annualIncome - reduction;
    },

    incomeYear10() {
      const escalation = this.pension.escalation_rate || 0;
      return (this.pension.annual_income || 0) * Math.pow(1 + escalation, 10);
    },

    incomeYear20() {
      const escalation = this.pension.escalation_rate || 0;
      return (this.pension.annual_income || 0) * Math.pow(1 + escalation, 20);
    },

    // State Pension projections
    yearsNeeded() {
      return Math.max(0, 35 - (this.pension.ni_years_completed || 0));
    },

    potentialIncrease() {
      const currentForecast = this.pension.state_pension_forecast_annual || 0;
      return 11973 - currentForecast;
    },

    costToFillGaps() {
      return this.yearsNeeded * 824; // Approximate cost per year
    },

    paybackYears() {
      if (this.potentialIncrease <= 0) return 0;
      return Math.ceil(this.costToFillGaps / this.potentialIncrease);
    },

    deferralOptions() {
      const currentForecast = this.pension.state_pension_forecast_annual || 0;
      return [
        { years: 1, increase: 5.8, annual: currentForecast * 1.058 },
        { years: 2, increase: 11.6, annual: currentForecast * 1.116 },
        { years: 3, increase: 17.4, annual: currentForecast * 1.174 },
        { years: 5, increase: 29, annual: currentForecast * 1.29 },
      ];
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

    calculateProjectedValue(growthRate) {
      const currentValue = this.pension.current_fund_value || 0;
      const monthlyContribution = this.totalMonthlyContribution;
      const years = this.yearsToRetirement;
      const monthlyRate = growthRate / 12;
      const months = years * 12;

      // Future value of current amount
      const fvCurrent = currentValue * Math.pow(1 + growthRate, years);

      // Future value of monthly contributions (annuity)
      const fvContributions = monthlyContribution * ((Math.pow(1 + monthlyRate, months) - 1) / monthlyRate);

      return fvCurrent + fvContributions;
    },
  },
};
</script>

<style scoped>
.pension-projections-panel {
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

.projections-container {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.projection-cards,
.db-projection-cards,
.state-projection-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
}

.projection-card {
  position: relative;
  background: white;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  padding: 24px;
  text-align: center;
}

.projection-card.featured {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  color: white;
  border: none;
}

.projection-card.low {
  border-left: 4px solid #f59e0b;
}

.projection-card.high {
  border-left: 4px solid #10b981;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.scenario-label {
  font-size: 14px;
  font-weight: 600;
}

.growth-rate {
  font-size: 12px;
  opacity: 0.8;
}

.projected-value {
  font-size: 32px;
  font-weight: 700;
  margin: 0 0 8px 0;
}

.monthly-income {
  font-size: 16px;
  margin: 0;
  opacity: 0.8;
}

.featured-badge {
  position: absolute;
  top: -10px;
  right: 20px;
  background: #fbbf24;
  color: #92400e;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.assumptions-section,
.comparison-section,
.lump-sum-section,
.early-retirement-section,
.commutation-section,
.inflation-section,
.gap-filling-section,
.deferral-section,
.triple-lock-section {
  background: white;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  padding: 24px;
}

.section-title {
  font-size: 18px;
  font-weight: 600;
  color: #111827;
  margin: 0 0 20px 0;
}

.assumptions-grid,
.lump-sum-grid,
.commutation-grid,
.inflation-grid,
.gap-filling-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 20px;
}

.assumption-item,
.lump-sum-item,
.commutation-item,
.inflation-item,
.gap-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.assumption-label,
.lump-sum-label,
.commutation-label,
.inflation-label,
.gap-label {
  font-size: 14px;
  color: #6b7280;
}

.assumption-value,
.lump-sum-value,
.commutation-value,
.inflation-value,
.gap-value {
  font-size: 18px;
  font-weight: 600;
  color: #111827;
}

.comparison-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
}

.comparison-card {
  background: #f9fafb;
  border-radius: 12px;
  padding: 24px;
  text-align: center;
}

.card-icon {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 16px;
}

.card-icon.drawdown {
  background: #dbeafe;
  color: #2563eb;
}

.card-icon.annuity {
  background: #d1fae5;
  color: #059669;
}

.comparison-card h4 {
  font-size: 18px;
  font-weight: 600;
  color: #111827;
  margin: 0 0 12px 0;
}

.comparison-value {
  font-size: 28px;
  font-weight: 700;
  color: #111827;
  margin: 0 0 4px 0;
}

.comparison-detail {
  font-size: 14px;
  color: #6b7280;
  margin: 0 0 16px 0;
}

.comparison-features {
  list-style: none;
  padding: 0;
  margin: 0;
  text-align: left;
}

.comparison-features li {
  font-size: 14px;
  color: #4b5563;
  padding: 8px 0;
  border-bottom: 1px solid #e5e7eb;
}

.comparison-features li:last-child {
  border-bottom: none;
}

/* Early Retirement Options */
.early-retirement-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 16px;
  margin-bottom: 16px;
}

.early-option {
  background: #f9fafb;
  border-radius: 8px;
  padding: 16px;
  text-align: center;
}

.option-age {
  font-size: 18px;
  font-weight: 700;
  color: #111827;
  margin-bottom: 4px;
}

.option-reduction {
  font-size: 12px;
  color: #dc2626;
  margin-bottom: 8px;
}

.option-income {
  font-size: 16px;
  font-weight: 600;
  color: #111827;
}

.option-monthly {
  font-size: 13px;
  color: #6b7280;
}

.reduction-note,
.commutation-note {
  font-size: 14px;
  color: #6b7280;
  font-style: italic;
  margin: 0;
}

/* Deferral Options */
.deferral-intro {
  font-size: 14px;
  color: #4b5563;
  margin-bottom: 20px;
}

.deferral-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 16px;
}

.deferral-item {
  background: #d1fae5;
  border-radius: 8px;
  padding: 16px;
  text-align: center;
}

.deferral-years {
  font-size: 14px;
  font-weight: 600;
  color: #065f46;
  margin-bottom: 4px;
}

.deferral-increase {
  font-size: 20px;
  font-weight: 700;
  color: #059669;
  margin-bottom: 8px;
}

.deferral-annual {
  font-size: 16px;
  font-weight: 600;
  color: #111827;
}

.deferral-weekly {
  font-size: 13px;
  color: #6b7280;
}

/* Triple Lock */
.triple-lock-info {
  background: #eff6ff;
  border-radius: 8px;
  padding: 20px;
}

.triple-lock-info p {
  font-size: 14px;
  color: #1e40af;
  margin: 0 0 12px 0;
}

.triple-lock-info ul {
  margin: 0 0 12px 0;
  padding-left: 20px;
}

.triple-lock-info li {
  font-size: 14px;
  color: #1e40af;
  margin-bottom: 4px;
}

.triple-lock-note {
  font-style: italic;
  border-top: 1px solid #bfdbfe;
  padding-top: 12px;
  margin-bottom: 0 !important;
}

@media (max-width: 768px) {
  .projection-cards,
  .comparison-cards {
    grid-template-columns: 1fr;
  }

  .early-retirement-grid,
  .deferral-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>
