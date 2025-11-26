<template>
  <div class="pension-analysis-panel relative">
    <!-- Coming Soon Watermark -->
    <div class="absolute inset-0 flex items-center justify-center z-10 pointer-events-none">
      <div class="bg-amber-100 border-2 border-amber-400 rounded-lg px-8 py-4 transform -rotate-12 shadow-lg">
        <p class="text-2xl font-bold text-amber-700">Coming Soon</p>
      </div>
    </div>

    <!-- DC Pension Analysis (with opacity) -->
    <div v-if="pensionType === 'dc'" class="analysis-container opacity-50">
      <!-- Risk Assessment -->
      <div class="analysis-section">
        <h3 class="section-title">Risk Assessment</h3>
        <div class="risk-meter">
          <div class="meter-labels">
            <span>Low Risk</span>
            <span>High Risk</span>
          </div>
          <div class="meter-bar">
            <div class="meter-fill" :style="{ width: riskLevel + '%' }"></div>
            <div class="meter-indicator" :style="{ left: riskLevel + '%' }"></div>
          </div>
          <p class="risk-description">{{ riskDescription }}</p>
        </div>
      </div>

      <!-- Fee Analysis -->
      <div class="analysis-section">
        <h3 class="section-title">Fee Analysis</h3>
        <div class="fee-grid">
          <div class="fee-item">
            <span class="fee-label">Annual Management Charge</span>
            <span class="fee-value">{{ pension.amc ? (pension.amc * 100).toFixed(2) + '%' : 'N/A' }}</span>
          </div>
          <div class="fee-item">
            <span class="fee-label">Platform Fee</span>
            <span class="fee-value">{{ pension.platform_fee ? (pension.platform_fee * 100).toFixed(2) + '%' : 'N/A' }}</span>
          </div>
          <div class="fee-item">
            <span class="fee-label">Total Expense Ratio</span>
            <span class="fee-value">{{ totalExpenseRatio }}%</span>
          </div>
          <div class="fee-item highlight">
            <span class="fee-label">20-Year Fee Impact</span>
            <span class="fee-value">{{ formatCurrency(feeImpact20Years) }}</span>
          </div>
        </div>
        <div class="fee-comparison">
          <p class="comparison-note">{{ feeComparison }}</p>
        </div>
      </div>

      <!-- Diversification Score -->
      <div class="analysis-section">
        <h3 class="section-title">Diversification Score</h3>
        <div class="diversification-score">
          <div class="score-circle">
            <span class="score-value">{{ diversificationScore }}</span>
            <span class="score-label">/100</span>
          </div>
          <div class="score-breakdown">
            <div class="breakdown-item">
              <span class="breakdown-label">Asset Classes</span>
              <div class="breakdown-bar">
                <div class="breakdown-fill" :style="{ width: assetClassScore + '%' }"></div>
              </div>
            </div>
            <div class="breakdown-item">
              <span class="breakdown-label">Geographic Spread</span>
              <div class="breakdown-bar">
                <div class="breakdown-fill" :style="{ width: geographicScore + '%' }"></div>
              </div>
            </div>
            <div class="breakdown-item">
              <span class="breakdown-label">Sector Exposure</span>
              <div class="breakdown-bar">
                <div class="breakdown-fill" :style="{ width: sectorScore + '%' }"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Optimisation Suggestions -->
      <div class="analysis-section">
        <h3 class="section-title">Optimisation Suggestions</h3>
        <div class="suggestions-list">
          <div class="suggestion-item" v-for="(suggestion, index) in optimisationSuggestions" :key="index">
            <div class="suggestion-icon" :class="suggestion.priority">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
              </svg>
            </div>
            <div class="suggestion-content">
              <h4>{{ suggestion.title }}</h4>
              <p>{{ suggestion.description }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- DB Pension Analysis (with opacity) -->
    <div v-else-if="pensionType === 'db'" class="analysis-container opacity-50">
      <!-- Scheme Security -->
      <div class="analysis-section">
        <h3 class="section-title">Scheme Security Assessment</h3>
        <div class="security-assessment">
          <div class="security-item">
            <span class="security-label">Scheme Type</span>
            <span class="security-value">{{ formatDBPensionType(pension.scheme_type) }}</span>
          </div>
          <div class="security-item">
            <span class="security-label">PPF Protection</span>
            <span class="security-value">Yes (UK DB Schemes)</span>
          </div>
          <div class="security-item">
            <span class="security-label">Benefit Security</span>
            <span class="security-badge high">High</span>
          </div>
        </div>
        <p class="security-note">Your DB pension is protected by the Pension Protection Fund (PPF). If your employer becomes insolvent, the PPF will pay compensation at a level close to your full benefits.</p>
      </div>

      <!-- Transfer Value Analysis -->
      <div class="analysis-section">
        <h3 class="section-title">Transfer Value Analysis</h3>
        <div class="transfer-warning">
          <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
          </svg>
          <p>Transferring a DB pension to a DC arrangement requires careful consideration. Financial advice is required for transfers over £30,000 CETV.</p>
        </div>
        <div class="transfer-grid">
          <div class="transfer-item">
            <span class="transfer-label">CETV</span>
            <span class="transfer-value">{{ formatCurrency(pension.cash_equivalent_transfer_value || 0) }}</span>
          </div>
          <div class="transfer-item">
            <span class="transfer-label">CETV/Income Ratio</span>
            <span class="transfer-value">{{ cetvRatio }}x</span>
          </div>
          <div class="transfer-item">
            <span class="transfer-label">Breakeven Return</span>
            <span class="transfer-value">{{ breakevenReturn }}%</span>
          </div>
        </div>
      </div>

      <!-- Comparison with DC -->
      <div class="analysis-section">
        <h3 class="section-title">DB vs DC Comparison</h3>
        <div class="comparison-table">
          <div class="comparison-row header">
            <span>Feature</span>
            <span>Your DB Pension</span>
            <span>Typical DC</span>
          </div>
          <div class="comparison-row">
            <span>Income Security</span>
            <span class="positive">Guaranteed</span>
            <span class="neutral">Variable</span>
          </div>
          <div class="comparison-row">
            <span>Investment Risk</span>
            <span class="positive">None</span>
            <span class="neutral">You bear risk</span>
          </div>
          <div class="comparison-row">
            <span>Flexibility</span>
            <span class="neutral">Limited</span>
            <span class="positive">High</span>
          </div>
          <div class="comparison-row">
            <span>Death Benefits</span>
            <span class="neutral">Spouse pension</span>
            <span class="positive">Full fund</span>
          </div>
        </div>
      </div>
    </div>

    <!-- State Pension Analysis (with opacity) -->
    <div v-else-if="pensionType === 'state'" class="analysis-container opacity-50">
      <!-- Value for Money Analysis -->
      <div class="analysis-section">
        <h3 class="section-title">Voluntary NI Contributions - Value Analysis</h3>
        <div v-if="yearsNeeded > 0" class="vfm-analysis">
          <div class="vfm-grid">
            <div class="vfm-item">
              <span class="vfm-label">Cost per NI Year</span>
              <span class="vfm-value">{{ formatCurrency(824) }}</span>
            </div>
            <div class="vfm-item">
              <span class="vfm-label">Pension Increase per Year</span>
              <span class="vfm-value">{{ formatCurrency(342) }}/year</span>
            </div>
            <div class="vfm-item highlight">
              <span class="vfm-label">Return on Investment</span>
              <span class="vfm-value">{{ voluntaryNiReturn }}%</span>
            </div>
            <div class="vfm-item">
              <span class="vfm-label">Payback Period</span>
              <span class="vfm-value">{{ paybackPeriod }} years</span>
            </div>
          </div>
          <div class="vfm-recommendation">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p>Voluntary NI contributions typically offer excellent value for money, with returns significantly exceeding most other investments.</p>
          </div>
        </div>
        <div v-else class="full-record">
          <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <p>Congratulations! You have a full NI record and are entitled to the maximum State Pension.</p>
        </div>
      </div>

      <!-- Deferral Analysis -->
      <div class="analysis-section">
        <h3 class="section-title">Deferral Analysis</h3>
        <div class="deferral-analysis">
          <p class="deferral-intro">Deferring your State Pension can increase your income, but the decision depends on your circumstances.</p>
          <div class="deferral-scenarios">
            <div class="scenario">
              <h4>Deferral May Be Beneficial If:</h4>
              <ul>
                <li>You're in good health with family longevity</li>
                <li>You have other income sources</li>
                <li>You want to reduce early retirement income tax</li>
                <li>You expect to live well past your State Pension age</li>
              </ul>
            </div>
            <div class="scenario">
              <h4>Deferral May Not Be Worth It If:</h4>
              <ul>
                <li>You need the income immediately</li>
                <li>Health concerns about longevity</li>
                <li>You'd invest the pension if received</li>
                <li>Breakeven point is too far away</li>
              </ul>
            </div>
          </div>
          <div class="breakeven-info">
            <span class="breakeven-label">Breakeven Point:</span>
            <span class="breakeven-value">Approximately 17 years after State Pension age</span>
          </div>
        </div>
      </div>

      <!-- Integration with Private Pensions -->
      <div class="analysis-section">
        <h3 class="section-title">Integration with Private Pensions</h3>
        <div class="integration-tips">
          <div class="tip-item">
            <div class="tip-icon">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
              </svg>
            </div>
            <div class="tip-content">
              <h4>Bridge the Gap</h4>
              <p>Use DC pension drawdown to bridge the gap before State Pension starts at age {{ pension.state_pension_age || 67 }}.</p>
            </div>
          </div>
          <div class="tip-item">
            <div class="tip-icon">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
              </svg>
            </div>
            <div class="tip-content">
              <h4>Tax Planning</h4>
              <p>Consider your combined income from State Pension and private pensions for tax efficiency.</p>
            </div>
          </div>
          <div class="tip-item">
            <div class="tip-icon">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div class="tip-content">
              <h4>Inflation Protection</h4>
              <p>State Pension provides triple-lock protection against inflation, unlike many private pensions.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PensionAnalysisPanel',

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
    // DC Pension analysis
    riskLevel() {
      // Simplified risk calculation based on available data
      return 50; // Placeholder
    },

    riskDescription() {
      if (this.riskLevel < 30) return 'Your pension is invested conservatively with lower growth potential but more stability.';
      if (this.riskLevel < 70) return 'Your pension has a balanced risk profile, suitable for medium-term growth.';
      return 'Your pension is invested aggressively, with higher growth potential but more volatility.';
    },

    totalExpenseRatio() {
      const amc = this.pension.amc || 0;
      const platformFee = this.pension.platform_fee || 0;
      return ((amc + platformFee) * 100).toFixed(2);
    },

    feeImpact20Years() {
      // Simplified calculation
      const fundValue = this.pension.current_fund_value || 0;
      const ter = (this.pension.amc || 0) + (this.pension.platform_fee || 0);
      return fundValue * ter * 20;
    },

    feeComparison() {
      const ter = parseFloat(this.totalExpenseRatio);
      if (ter < 0.5) return 'Your fees are excellent - well below the industry average.';
      if (ter < 1.0) return 'Your fees are reasonable and competitive.';
      return 'Your fees are above average. Consider reviewing your investment options.';
    },

    diversificationScore() {
      return 65; // Placeholder
    },

    assetClassScore() {
      return 70;
    },

    geographicScore() {
      return 60;
    },

    sectorScore() {
      return 65;
    },

    optimisationSuggestions() {
      return [
        {
          priority: 'high',
          title: 'Review Investment Allocation',
          description: 'Consider adjusting your fund selection to match your risk tolerance and time to retirement.',
        },
        {
          priority: 'medium',
          title: 'Maximise Employer Match',
          description: 'Ensure you\'re contributing enough to receive the full employer match - this is effectively free money.',
        },
        {
          priority: 'low',
          title: 'Consolidate Pension Pots',
          description: 'If you have multiple small pension pots, consider consolidating for easier management and potentially lower fees.',
        },
      ];
    },

    // DB Pension analysis
    cetvRatio() {
      const cetv = this.pension.cash_equivalent_transfer_value || 0;
      const income = this.pension.annual_income || 1;
      return (cetv / income).toFixed(1);
    },

    breakevenReturn() {
      // Simplified breakeven calculation
      const ratio = parseFloat(this.cetvRatio);
      return (100 / ratio).toFixed(1);
    },

    // State Pension analysis
    yearsNeeded() {
      return Math.max(0, 35 - (this.pension.ni_years_completed || 0));
    },

    voluntaryNiReturn() {
      // Return on voluntary NI: £342 per year for £824 investment
      return ((342 / 824) * 100).toFixed(1);
    },

    paybackPeriod() {
      return Math.ceil(824 / 342);
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
.pension-analysis-panel {
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

.analysis-container {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.analysis-section {
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

/* Risk Meter */
.risk-meter {
  padding: 20px;
  background: #f9fafb;
  border-radius: 8px;
}

.meter-labels {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  color: #6b7280;
  margin-bottom: 8px;
}

.meter-bar {
  position: relative;
  height: 12px;
  background: linear-gradient(90deg, #10b981 0%, #fbbf24 50%, #ef4444 100%);
  border-radius: 6px;
}

.meter-indicator {
  position: absolute;
  top: -4px;
  width: 20px;
  height: 20px;
  background: white;
  border: 3px solid #111827;
  border-radius: 50%;
  transform: translateX(-50%);
}

.risk-description {
  font-size: 14px;
  color: #4b5563;
  margin: 16px 0 0 0;
}

/* Fee Analysis */
.fee-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 16px;
}

.fee-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: 16px;
  background: #f9fafb;
  border-radius: 8px;
}

.fee-item.highlight {
  background: #fef3c7;
}

.fee-label {
  font-size: 14px;
  color: #6b7280;
}

.fee-value {
  font-size: 20px;
  font-weight: 600;
  color: #111827;
}

.comparison-note {
  font-size: 14px;
  color: #4b5563;
  font-style: italic;
  margin: 0;
}

/* Diversification Score */
.diversification-score {
  display: flex;
  gap: 32px;
  align-items: center;
}

.score-circle {
  flex-shrink: 0;
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: white;
}

.score-value {
  font-size: 36px;
  font-weight: 700;
  line-height: 1;
}

.score-label {
  font-size: 14px;
  opacity: 0.8;
}

.score-breakdown {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.breakdown-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.breakdown-label {
  font-size: 14px;
  color: #6b7280;
}

.breakdown-bar {
  height: 8px;
  background: #e5e7eb;
  border-radius: 4px;
  overflow: hidden;
}

.breakdown-fill {
  height: 100%;
  background: #3b82f6;
  border-radius: 4px;
}

/* Suggestions */
.suggestions-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.suggestion-item {
  display: flex;
  gap: 16px;
  padding: 16px;
  background: #f9fafb;
  border-radius: 8px;
}

.suggestion-icon {
  flex-shrink: 0;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.suggestion-icon.high {
  background: #fee2e2;
  color: #dc2626;
}

.suggestion-icon.medium {
  background: #fef3c7;
  color: #d97706;
}

.suggestion-icon.low {
  background: #d1fae5;
  color: #059669;
}

.suggestion-content h4 {
  font-size: 16px;
  font-weight: 600;
  color: #111827;
  margin: 0 0 4px 0;
}

.suggestion-content p {
  font-size: 14px;
  color: #6b7280;
  margin: 0;
}

/* DB Pension Styles */
.security-assessment {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 16px;
}

.security-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.security-label {
  font-size: 14px;
  color: #6b7280;
}

.security-value {
  font-size: 16px;
  font-weight: 600;
  color: #111827;
}

.security-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 600;
}

.security-badge.high {
  background: #d1fae5;
  color: #065f46;
}

.security-note {
  font-size: 14px;
  color: #4b5563;
  margin: 0;
  line-height: 1.5;
}

.transfer-warning {
  display: flex;
  gap: 12px;
  padding: 16px;
  background: #fef3c7;
  border-radius: 8px;
  margin-bottom: 16px;
}

.transfer-warning p {
  font-size: 14px;
  color: #92400e;
  margin: 0;
}

.transfer-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.transfer-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: 16px;
  background: #f9fafb;
  border-radius: 8px;
}

.transfer-label {
  font-size: 14px;
  color: #6b7280;
}

.transfer-value {
  font-size: 20px;
  font-weight: 600;
  color: #111827;
}

.comparison-table {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  overflow: hidden;
}

.comparison-row {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 16px;
  padding: 12px 16px;
  border-bottom: 1px solid #e5e7eb;
}

.comparison-row:last-child {
  border-bottom: none;
}

.comparison-row.header {
  background: #f9fafb;
  font-weight: 600;
}

.comparison-row .positive {
  color: #059669;
}

.comparison-row .neutral {
  color: #6b7280;
}

/* State Pension Styles */
.vfm-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 16px;
  margin-bottom: 16px;
}

.vfm-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: 16px;
  background: #f9fafb;
  border-radius: 8px;
}

.vfm-item.highlight {
  background: #d1fae5;
}

.vfm-label {
  font-size: 14px;
  color: #6b7280;
}

.vfm-value {
  font-size: 20px;
  font-weight: 600;
  color: #111827;
}

.vfm-recommendation {
  display: flex;
  gap: 12px;
  padding: 16px;
  background: #d1fae5;
  border-radius: 8px;
}

.vfm-recommendation p {
  font-size: 14px;
  color: #065f46;
  margin: 0;
}

.full-record {
  text-align: center;
  padding: 32px;
}

.full-record p {
  font-size: 16px;
  color: #065f46;
  margin-top: 16px;
}

.deferral-intro {
  font-size: 14px;
  color: #4b5563;
  margin-bottom: 20px;
}

.deferral-scenarios {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
  margin-bottom: 20px;
}

.scenario {
  padding: 16px;
  background: #f9fafb;
  border-radius: 8px;
}

.scenario h4 {
  font-size: 14px;
  font-weight: 600;
  color: #111827;
  margin: 0 0 12px 0;
}

.scenario ul {
  margin: 0;
  padding-left: 20px;
}

.scenario li {
  font-size: 14px;
  color: #4b5563;
  margin-bottom: 4px;
}

.breakeven-info {
  display: flex;
  justify-content: space-between;
  padding: 16px;
  background: #eff6ff;
  border-radius: 8px;
}

.breakeven-label {
  font-size: 14px;
  color: #1e40af;
}

.breakeven-value {
  font-size: 14px;
  font-weight: 600;
  color: #1e40af;
}

.integration-tips {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.tip-item {
  display: flex;
  gap: 16px;
  padding: 16px;
  background: #f9fafb;
  border-radius: 8px;
}

.tip-icon {
  flex-shrink: 0;
  width: 40px;
  height: 40px;
  background: #dbeafe;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #2563eb;
}

.tip-content h4 {
  font-size: 16px;
  font-weight: 600;
  color: #111827;
  margin: 0 0 4px 0;
}

.tip-content p {
  font-size: 14px;
  color: #6b7280;
  margin: 0;
}

@media (max-width: 768px) {
  .diversification-score {
    flex-direction: column;
  }

  .comparison-row {
    grid-template-columns: 1fr;
    text-align: center;
  }
}
</style>
