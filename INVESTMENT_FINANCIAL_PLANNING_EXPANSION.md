# Investment Module - Financial Planning Features Expansion

**Project**: TenGo Financial Planning System
**Version**: 0.2.0
**Branch**: `feature/investment-financial-planning`
**Date**: November 1, 2025
**Status**: Planning Phase

---

## Executive Summary

This document outlines the expansion of the Investment module to include comprehensive financial planning features, transforming it from a portfolio tracking tool into a complete investment planning and advisory system.

### Current State (v0.1.2.13)

**Existing Features:**
- Portfolio tracking with multiple account types (ISA, GIA, bonds, VCT, EIS)
- Basic holdings management
- Asset allocation visualization
- Monte Carlo simulations (infrastructure exists)
- Basic performance tracking
- Investment goals tracking
- Fee analysis infrastructure
- Tax efficiency calculations

**Gaps Identified:**
- ✗ Recommendations tab (placeholder only)
- ✗ What-If Scenarios tab (placeholder only)
- ✗ No comprehensive investment plan generation
- ✗ Limited goal-based planning functionality
- ✗ No withdrawal/decumulation planning
- ✗ No rebalancing calculator
- ✗ No ISA allowance optimization
- ✗ Limited tax planning features
- ✗ No contribution planning tools
- ✗ No education funding calculators

### Target State (v0.2.0)

**Goal**: Create a comprehensive investment planning system that:
1. Generates professional investment plans (like Protection module's comprehensive plan)
2. Provides actionable recommendations with priority scoring
3. Models complex what-if scenarios with Monte Carlo integration
4. Optimizes tax efficiency across ISA, GIA, and pension wrappers
5. Plans for specific goals (retirement, education, wealth building, home purchase)
6. Provides contribution optimization strategies
7. Includes rebalancing tools and alerts
8. Integrates with other modules (especially Retirement and Estate)

---

## Feature Breakdown

### Phase 1: Core Financial Planning Features

#### 1.1 Comprehensive Investment Plan
**Status**: Not Started
**Priority**: HIGH
**Estimated Effort**: 3-4 days

Similar to `ComprehensiveProtectionPlan.vue`, create a professional investment plan document.

**Components to Build:**
- `ComprehensiveInvestmentPlan.vue` (main view)
- Backend: `InvestmentPlanGenerator.php` service
- Backend: `InvestmentPlanController.php`

**Plan Structure:**
1. **Executive Summary**
   - Overall portfolio health score (0-100)
   - Total portfolio value and composition
   - Key metrics: diversification, risk alignment, tax efficiency
   - Top 3 priorities for action

2. **Current Situation Analysis**
   - Portfolio snapshot (all accounts and holdings)
   - Asset allocation breakdown (actual vs. target)
   - Risk profile assessment
   - Fee analysis summary
   - Tax efficiency score

3. **Goal Progress Review**
   - Each goal with progress tracking
   - Probability of success (Monte Carlo results)
   - On-track / Off-track status
   - Required actions to stay on track

4. **Risk Analysis**
   - Portfolio volatility and expected returns
   - Stress testing results (market crash scenarios)
   - Concentration risk assessment
   - Currency exposure analysis

5. **Tax Optimization Strategy**
   - ISA allowance utilization (current year)
   - Tax loss harvesting opportunities
   - Wrapper optimization (ISA vs. GIA vs. Pension)
   - Capital gains tax planning

6. **Fee Analysis**
   - Current annual fees breakdown
   - Comparison to low-cost alternatives
   - Potential annual savings
   - Compound impact over 10/20/30 years

7. **Recommendations** (prioritized)
   - Rebalancing actions
   - Tax optimization moves
   - Fee reduction opportunities
   - Contribution strategies
   - Risk alignment adjustments

8. **Action Plan**
   - Immediate actions (next 30 days)
   - Short-term actions (3-6 months)
   - Long-term actions (12+ months)

**Database Requirements:**
- Table: `investment_plans` (store generated plans)
- Table: `investment_recommendations` (track recommendation status)

**API Endpoints:**
```
POST   /api/investment/generate-plan
GET    /api/investment/plan
GET    /api/investment/plan/download-pdf
PUT    /api/investment/recommendations/:id/status
```

---

#### 1.2 Recommendations System
**Status**: Not Started (Placeholder exists)
**Priority**: HIGH
**Estimated Effort**: 2-3 days

Transform the placeholder into a fully functional recommendation engine.

**Components to Build:**
- `Recommendations.vue` (replace placeholder)
- `RecommendationCard.vue`
- Backend: Enhanced `InvestmentAgent::generateRecommendations()`
- Backend: `RecommendationPrioritizer.php` service
- Backend: `RecommendationTracker.php` service

**Recommendation Categories:**
1. **Portfolio Rebalancing**
   - Detect allocation drift > 5% from target
   - Calculate specific buy/sell actions
   - Estimate tax implications
   - Priority: Based on drift magnitude

2. **Tax Optimization**
   - ISA allowance remaining (warn at £5k, critical at £2k)
   - Tax loss harvesting (unrealized losses > £500)
   - Wrapper migration opportunities
   - Pension contribution opportunities
   - Priority: Based on potential tax saving

3. **Fee Reduction**
   - High-cost funds (OCF > 1%)
   - Platform fee optimization
   - Low-cost alternative suggestions
   - Priority: Based on annual saving amount

4. **Risk Alignment**
   - Portfolio too aggressive/conservative for risk profile
   - Concentration risk (single holding > 10%)
   - Geographic concentration
   - Priority: Based on risk mismatch severity

5. **Goal Alignment**
   - Contributions insufficient for goal
   - Asset allocation inappropriate for time horizon
   - Liquidity issues for short-term goals
   - Priority: Based on goal importance and shortfall

6. **Contribution Strategy**
   - Increase contributions (affordability analysis)
   - Lump sum vs. DCA (dollar-cost averaging)
   - Emergency fund first (coordination with Savings module)
   - Priority: Based on goal urgency

**Recommendation Tracking:**
- Status: Pending / In Progress / Completed / Dismissed
- Due date suggestions
- Progress tracking
- Impact measurement (actual vs. projected)

**API Endpoints:**
```
GET    /api/investment/recommendations
POST   /api/investment/recommendations/:id/status
POST   /api/investment/recommendations/:id/dismiss
POST   /api/investment/recommendations/refresh
```

---

#### 1.3 What-If Scenarios System
**Status**: Not Started (Placeholder exists)
**Priority**: HIGH
**Estimated Effort**: 3-4 days

Advanced scenario modeling with Monte Carlo integration.

**Components to Build:**
- `WhatIfScenarios.vue` (replace placeholder)
- `ScenarioBuilder.vue` (form to create custom scenarios)
- `ScenarioComparison.vue` (side-by-side comparison)
- `ScenarioResults.vue` (detailed results with charts)
- Backend: Enhanced `InvestmentAgent::buildScenarios()`
- Backend: `ScenarioAnalyzer.php` service
- Backend: Integration with `MonteCarloSimulator.php`

**Pre-Built Scenarios:**

1. **Market Conditions**
   - Bull Market (12% return, 15% volatility)
   - Normal Market (7% return, 12% volatility)
   - Bear Market (2% return, 20% volatility)
   - Market Crash (-20% in year 1, recovery)
   - Stagflation (3% return, 8% volatility, high inflation)

2. **Contribution Strategies**
   - Current contributions (baseline)
   - Increase by 10% / 25% / 50%
   - Maximize ISA allowance
   - One-time lump sum investment
   - Inheritance windfall scenario

3. **Asset Allocation Changes**
   - More aggressive (90/10 equity/bonds)
   - Balanced (60/40)
   - Conservative (40/60)
   - Lifecycle glide path (automatic de-risking)

4. **Fee Optimization**
   - Current fees vs. 0.5% reduction
   - Platform change savings
   - Switch to passive funds

5. **Withdrawal Scenarios** (Decumulation)
   - 3% / 4% / 5% withdrawal rates
   - Bucket strategy simulation
   - Sequence of returns risk

6. **Goal-Specific Scenarios**
   - Early retirement (retire 5 years earlier)
   - Education funding (£30k needed in 10 years)
   - House deposit (£50k in 5 years)

**Custom Scenario Builder:**
Users can create custom scenarios by adjusting:
- Expected return (slider: 0% - 15%)
- Volatility (slider: 5% - 30%)
- Monthly contribution (£ amount)
- One-time contribution (£ amount, specify year)
- Asset allocation shifts
- Fee changes
- Withdrawal amount and timing

**Scenario Comparison:**
- Side-by-side comparison of up to 4 scenarios
- Probability distributions (Monte Carlo results)
- Best/worst/median outcomes
- Goal success probabilities
- 20-year projections (line chart)

**API Endpoints:**
```
POST   /api/investment/scenarios/analyze
GET    /api/investment/scenarios/prebuilt
POST   /api/investment/scenarios/custom
POST   /api/investment/scenarios/compare
POST   /api/investment/monte-carlo/run    (existing, enhance)
GET    /api/investment/monte-carlo/results/:id    (existing)
```

---

### Phase 2: Advanced Planning Tools

#### 2.1 Contribution Planning & Optimization
**Status**: Not Started
**Priority**: MEDIUM
**Estimated Effort**: 2 days

Help users determine optimal contribution strategy.

**Components to Build:**
- `ContributionPlanner.vue`
- Backend: `ContributionOptimizer.php` service

**Features:**
- Affordability analysis (integrate with cash flow from Estate module)
- ISA vs. GIA contribution priority
- Pension vs. ISA optimization (coordinate with Retirement module)
- Lump sum vs. DCA calculator
- Automatic increase plan (e.g., increase 2% per year)
- Tax relief calculations for pensions

**Inputs:**
- Current portfolio value
- Monthly investable income
- One-time investment amount (optional)
- Investment time horizon
- Tax situation (income tax band)
- Risk tolerance

**Outputs:**
- Recommended monthly contribution
- Wrapper allocation (£X to ISA, £Y to GIA, £Z to pension)
- Lump sum vs. DCA recommendation with rationale
- Projected outcomes (Monte Carlo)
- Tax efficiency score

---

#### 2.2 Rebalancing Calculator
**Status**: Not Started
**Priority**: MEDIUM
**Estimated Effort**: 2 days

Automated rebalancing with tax optimization.

**Components to Build:**
- `RebalancingCalculator.vue`
- `RebalancingActions.vue` (specific buy/sell recommendations)
- Backend: `RebalancingCalculator.php` service
- Backend: `TaxAwareRebalancer.php` service

**Features:**
- Target allocation entry (or use risk profile)
- Current allocation analysis
- Drift calculation
- Generate specific trades (sell X, buy Y)
- Tax-aware optimization:
  - Prefer in-specie transfers to ISA
  - Harvest losses before realizing gains
  - Use new contributions for rebalancing
  - Consider threshold rebalancing (only if drift > X%)
- Estimate transaction costs
- Estimate tax implications (CGT)

**Rebalancing Strategies:**
- Calendar-based (quarterly, annually)
- Threshold-based (drift > 5%)
- Opportunistic (during market extremes)

---

#### 2.3 Goal-Based Investment Planning
**Status**: Partially exists (basic goal tracking)
**Priority**: MEDIUM
**Estimated Effort**: 2-3 days

Transform basic goal tracking into comprehensive goal-based planning.

**Components to Build:**
- Enhanced `GoalCard.vue`
- `GoalPlanner.vue`
- `GoalProjection.vue` (detailed projections with Monte Carlo)
- Backend: `GoalPlanner.php` service
- Backend: Enhanced goal tracking

**Goal Types & Templates:**

1. **Retirement Savings**
   - Target: £500k by age 65
   - Integrated with Retirement module
   - Pension wrapper preference
   - Lifestyling/de-risking strategy

2. **Education Funding**
   - Target: £30k per child for university
   - Time horizon: Birth to age 18
   - Junior ISA consideration
   - Stepped withdrawals (£10k/year for 3 years)

3. **House Deposit**
   - Target: £50k in 5 years
   - LISA consideration (25% government bonus)
   - Lower risk allocation (short time horizon)

4. **Wealth Accumulation**
   - No specific target date
   - Growth-focused
   - Higher risk tolerance

5. **Income Generation**
   - Target income: £2k/month
   - Dividend-focused portfolio
   - Capital preservation

**Enhanced Goal Features:**
- Dedicated account assignment
- Asset allocation per goal (time-based glide path)
- Monte Carlo probability of success
- Required contribution calculator
- "What if I'm late?" catch-up scenarios
- Milestone tracking
- Goal prioritization (essential vs. nice-to-have)

**Database Changes:**
Enhance `investment_goals` table:
```sql
ALTER TABLE investment_goals ADD COLUMN:
- monthly_contribution DECIMAL(10,2)
- expected_return DECIMAL(5,4)
- success_probability DECIMAL(5,2)  -- from Monte Carlo
- last_projected_at TIMESTAMP
- asset_allocation_strategy VARCHAR(50)  -- aggressive/balanced/conservative/custom
- custom_allocation JSON  -- if custom
- withdrawal_strategy VARCHAR(50)  -- lump_sum/periodic/stepped
```

---

#### 2.4 Tax Optimization Suite
**Status**: Basic infrastructure exists
**Priority**: MEDIUM-HIGH
**Estimated Effort**: 2-3 days

Comprehensive tax planning tools for investors.

**Components to Build:**
- `TaxOptimization.vue` (new tab or part of Recommendations)
- `ISAAllowanceTracker.vue` (enhanced)
- `TaxLossHarvesting.vue`
- `WrapperOptimizer.vue`
- Backend: Enhanced `TaxEfficiencyCalculator.php`
- Backend: `WrapperOptimizer.php` service

**Features:**

1. **ISA Allowance Optimization**
   - Current year usage across all accounts
   - Cross-module tracking (Savings + Investment)
   - Recommendation: Which holdings to move to ISA
   - Bed & ISA calculator
   - Historical tracking (prevent over-subscription)

2. **Tax Loss Harvesting**
   - Identify holdings with unrealized losses
   - Calculate potential CGT savings
   - Suggest replacement investments (avoid 30-day rule)
   - Track wash sale rule compliance
   - Estimate annual tax benefit

3. **Wrapper Optimization**
   - ISA vs. GIA analysis:
     - Income-generating assets → ISA (tax-free dividends)
     - Growth assets → depends on tax situation
   - Pension vs. ISA for retirement savings
   - Offshore bond optimization (high-income earners)
   - VCT/EIS tax relief tracking

4. **Capital Gains Tax Planning**
   - Annual CGT allowance tracking (£6,000 for 2024/25)
   - Realized gains YTD
   - Projected gains from planned sales
   - Spouse transfer optimization (utilize both allowances)
   - Warning when approaching limit

5. **Dividend Tax Planning**
   - Dividend allowance tracking (£1,000 for basic rate)
   - Dividend income forecast
   - ISA migration for high-dividend stocks

**Integration:**
- Link with user's tax situation (from User Profile)
- Link with Savings module (Cash ISA usage)
- Link with Retirement module (Pension contributions)

---

#### 2.5 Fee Analysis & Optimization
**Status**: Basic infrastructure exists
**Priority**: MEDIUM
**Estimated Effort**: 1-2 days

Enhanced fee analysis with actionable recommendations.

**Components to Build:**
- Enhanced `TaxFees.vue` (expand beyond placeholder)
- `FeeBreakdown.vue`
- `FeeSavingsCalculator.vue`
- Backend: Enhanced `FeeAnalyzer.php`

**Features:**
- Detailed fee breakdown:
  - Platform fees (% and fixed)
  - Fund OCF (ongoing charges)
  - Trading costs
  - FX fees
  - Custody fees
- Annual cost calculation
- Low-cost alternatives suggestion
- 10/20/30 year compound impact calculation
- Platform comparison
- "What if I switched" calculator

**Example Calculation:**
```
Current fees: 0.45% platform + 0.75% OCF = 1.20% total
Low-cost alternative: 0.25% platform + 0.15% OCF = 0.40% total
Annual saving on £100k: £800
30-year impact: £183,000 (assuming 7% growth)
```

---

### Phase 3: Integration & Advanced Features

#### 3.1 Cross-Module Integration
**Status**: Not Started
**Priority**: HIGH
**Estimated Effort**: 2 days

Integrate Investment module with other modules for holistic planning.

**Integrations:**

1. **Retirement Module**
   - Include investment accounts in retirement projections
   - Pension vs. ISA optimization
   - Decumulation planning (coordinate drawdown strategies)
   - Retirement readiness impact

2. **Estate Module**
   - Include investments in IHT calculations
   - Asset ownership (joint/individual/trust)
   - Lifetime gifting of investments (7-year rule)
   - Beneficiary designation tracking

3. **Savings Module**
   - ISA allowance coordination (£20k total)
   - Emergency fund sufficiency check
   - Cash allocation recommendations
   - Move excess cash to investments

4. **Protection Module**
   - Ensure adequate protection before aggressive investing
   - Investment risk vs. life insurance coverage
   - Critical illness coverage sufficiency

5. **Coordinating Agent**
   - Holistic cash flow analysis
   - Prioritize: Emergency fund → Protection → Pensions → Investments
   - Conflict resolution (e.g., ISA allowance allocation)

**Backend Changes:**
- Enhance `CoordinatingAgent.php` with investment integration
- Create `InvestmentCoordinationService.php`
- Update holistic plan generation

---

#### 3.2 Performance Attribution & Reporting
**Status**: Basic performance tracking exists
**Priority**: MEDIUM
**Estimated Effort**: 2 days

Advanced performance analysis and reporting.

**Components to Build:**
- Enhanced `Performance.vue`
- `PerformanceAttribution.vue`
- `BenchmarkComparison.vue`
- Backend: `PerformanceAnalyzer.php` service

**Features:**
- Time-weighted return (TWR) calculation
- Money-weighted return (MWR/IRR) calculation
- Performance attribution:
  - Asset allocation effect
  - Security selection effect
  - Timing effect
- Benchmark comparison:
  - FTSE All-Share
  - S&P 500
  - 60/40 portfolio
  - Custom benchmark
- Risk-adjusted returns:
  - Sharpe ratio
  - Sortino ratio
  - Maximum drawdown
- Rolling returns (1yr, 3yr, 5yr, 10yr)

---

#### 3.3 Educational Content & Guidance
**Status**: Not Started
**Priority**: LOW
**Estimated Effort**: 1-2 days

Help users understand investment concepts.

**Components to Build:**
- `InvestmentEducation.vue` (new tab)
- Educational modals/tooltips throughout
- Glossary component

**Topics:**
- Asset allocation principles
- Risk vs. return
- Compound interest
- Tax wrappers (ISA, SIPP, GIA)
- Diversification
- Pound-cost averaging
- Rebalancing
- Tax loss harvesting
- Fee impact
- Common behavioral biases

**Implementation:**
- Contextual help icons (?)
- "Learn More" links in recommendations
- Interactive calculators (e.g., compound interest)
- Video tutorials (optional)

---

## Database Schema Changes

### New Tables

```sql
-- Store generated investment plans
CREATE TABLE investment_plans (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    plan_version VARCHAR(20) NOT NULL,  -- e.g., "1.0", "1.1"
    plan_data JSON NOT NULL,  -- Complete plan structure
    portfolio_health_score INT NOT NULL,  -- 0-100
    is_complete BOOLEAN DEFAULT FALSE,  -- Profile completeness
    completeness_score INT,  -- 0-100
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_generated (user_id, generated_at)
);

-- Track investment recommendations
CREATE TABLE investment_recommendations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    investment_plan_id BIGINT UNSIGNED,  -- Optional link to plan
    category VARCHAR(50) NOT NULL,  -- rebalancing, tax, fees, risk, goal, contribution
    priority INT NOT NULL,  -- 1 (highest) to N
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    action_required TEXT NOT NULL,
    impact_level VARCHAR(20),  -- low, medium, high
    potential_saving DECIMAL(10,2),  -- Annual saving in £
    estimated_effort VARCHAR(20),  -- quick, moderate, significant
    status VARCHAR(20) DEFAULT 'pending',  -- pending, in_progress, completed, dismissed
    due_date DATE,
    completed_at TIMESTAMP NULL,
    dismissed_at TIMESTAMP NULL,
    dismissal_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (investment_plan_id) REFERENCES investment_plans(id) ON DELETE SET NULL,
    INDEX idx_user_status (user_id, status),
    INDEX idx_priority (user_id, priority)
);

-- Store what-if scenarios and results
CREATE TABLE investment_scenarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    scenario_name VARCHAR(255) NOT NULL,
    scenario_type VARCHAR(50) NOT NULL,  -- prebuilt, custom
    parameters JSON NOT NULL,  -- Scenario inputs
    monte_carlo_job_id VARCHAR(100),  -- Link to background job
    results JSON,  -- Monte Carlo results
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_created (user_id, created_at)
);

-- Track rebalancing actions
CREATE TABLE rebalancing_actions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    rebalancing_date DATE NOT NULL,
    target_allocation JSON NOT NULL,
    actions JSON NOT NULL,  -- Specific buy/sell instructions
    estimated_tax_impact DECIMAL(10,2),
    status VARCHAR(20) DEFAULT 'pending',  -- pending, in_progress, completed
    completed_at TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, rebalancing_date)
);
```

### Enhanced Tables

```sql
-- Enhance investment_goals table
ALTER TABLE investment_goals ADD COLUMN (
    monthly_contribution DECIMAL(10,2) DEFAULT 0.00,
    expected_return DECIMAL(5,4) DEFAULT 0.0700,  -- 7% default
    success_probability DECIMAL(5,2),  -- From Monte Carlo
    last_projected_at TIMESTAMP NULL,
    asset_allocation_strategy VARCHAR(50) DEFAULT 'balanced',
    custom_allocation JSON,  -- If strategy is 'custom'
    withdrawal_strategy VARCHAR(50) DEFAULT 'lump_sum',
    withdrawal_schedule JSON  -- For periodic withdrawals
);

-- Add risk profile tracking
ALTER TABLE risk_profiles ADD COLUMN (
    last_assessed_at TIMESTAMP NULL,
    assessment_responses JSON,  -- Store questionnaire responses
    target_allocation JSON  -- Store target allocation
);
```

---

## API Endpoints Summary

### New Endpoints

**Investment Plans:**
```
POST   /api/investment/generate-plan              Generate comprehensive plan
GET    /api/investment/plan                        Get latest plan
GET    /api/investment/plan/:id                    Get specific plan version
GET    /api/investment/plan/download-pdf           Download plan as PDF
```

**Recommendations:**
```
GET    /api/investment/recommendations             Get all recommendations
POST   /api/investment/recommendations/refresh     Regenerate recommendations
PUT    /api/investment/recommendations/:id/status  Update recommendation status
POST   /api/investment/recommendations/:id/dismiss Dismiss recommendation
GET    /api/investment/recommendations/history     Historical recommendations
```

**Scenarios:**
```
GET    /api/investment/scenarios                   Get saved scenarios
POST   /api/investment/scenarios/analyze           Analyze new scenario
GET    /api/investment/scenarios/prebuilt          Get prebuilt scenario templates
POST   /api/investment/scenarios/custom            Create custom scenario
POST   /api/investment/scenarios/compare           Compare multiple scenarios
DELETE /api/investment/scenarios/:id               Delete scenario
```

**Contribution Planning:**
```
POST   /api/investment/contribution/optimize       Get optimal contribution strategy
POST   /api/investment/contribution/affordability  Affordability analysis
POST   /api/investment/contribution/lumpsum-vs-dca Compare strategies
```

**Rebalancing:**
```
POST   /api/investment/rebalancing/analyze         Analyze current vs target allocation
POST   /api/investment/rebalancing/actions         Generate rebalancing actions
POST   /api/investment/rebalancing/execute         Mark rebalancing as executed
GET    /api/investment/rebalancing/history         Historical rebalancing
```

**Tax Optimization:**
```
GET    /api/investment/tax/isa-allowance           ISA allowance status (cross-module)
GET    /api/investment/tax/loss-harvesting         Tax loss harvesting opportunities
POST   /api/investment/tax/wrapper-optimize        Optimize wrapper allocation
GET    /api/investment/tax/cgt-forecast            CGT forecast for current year
```

**Goals:**
```
POST   /api/investment/goals/:id/project           Project goal with Monte Carlo
POST   /api/investment/goals/:id/optimize          Optimize strategy for goal
GET    /api/investment/goals/:id/milestones        Get goal milestones
```

---

## Services Architecture

### New Services

```
app/Services/Investment/
├── InvestmentPlanGenerator.php          # Generate comprehensive plans
├── RecommendationPrioritizer.php        # Prioritize recommendations
├── RecommendationTracker.php            # Track recommendation status
├── ScenarioAnalyzer.php                 # Analyze what-if scenarios
├── ContributionOptimizer.php            # Optimize contributions
├── RebalancingCalculator.php            # Calculate rebalancing actions
├── TaxAwareRebalancer.php               # Tax-optimized rebalancing
├── GoalPlanner.php                      # Goal-based planning
├── WrapperOptimizer.php                 # ISA/GIA/Pension optimization
├── PerformanceAnalyzer.php              # Performance attribution
└── InvestmentCoordinationService.php    # Cross-module coordination
```

### Enhanced Services

```
app/Services/Investment/
├── TaxEfficiencyCalculator.php          # Enhanced tax features
├── MonteCarloSimulator.php              # Enhanced scenario support
└── PortfolioAnalyzer.php                # Additional analysis features
```

---

## Frontend Components

### New Components

```
resources/js/views/Investment/
└── ComprehensiveInvestmentPlan.vue      # Main plan view

resources/js/components/Investment/
├── PlanGeneration/
│   ├── PlanExecutiveSummary.vue
│   ├── PlanCurrentSituation.vue
│   ├── PlanGoalProgress.vue
│   ├── PlanRiskAnalysis.vue
│   ├── PlanTaxStrategy.vue
│   ├── PlanFeeAnalysis.vue
│   └── PlanActionItems.vue
│
├── Recommendations/
│   ├── RecommendationCard.vue           # Individual recommendation
│   ├── RecommendationFilter.vue         # Filter by category
│   └── RecommendationHistory.vue        # Past recommendations
│
├── Scenarios/
│   ├── ScenarioBuilder.vue              # Create custom scenarios
│   ├── ScenarioCard.vue                 # Individual scenario
│   ├── ScenarioComparison.vue           # Compare scenarios
│   ├── ScenarioResults.vue              # Detailed results
│   └── PrebuiltScenarios.vue            # Prebuilt templates
│
├── ContributionPlanning/
│   ├── ContributionPlanner.vue          # Main planner
│   ├── AffordabilityAnalysis.vue
│   └── LumpSumVsDCA.vue
│
├── Rebalancing/
│   ├── RebalancingCalculator.vue
│   ├── RebalancingActions.vue
│   └── RebalancingHistory.vue
│
├── GoalPlanning/
│   ├── GoalPlanner.vue                  # Enhanced goal planning
│   ├── GoalProjection.vue               # Detailed projections
│   └── GoalMilestones.vue
│
├── TaxOptimization/
│   ├── TaxOptimization.vue              # Main tax view
│   ├── ISAAllowanceTracker.vue
│   ├── TaxLossHarvesting.vue
│   ├── WrapperOptimizer.vue
│   └── CGTTracker.vue
│
└── Performance/
    ├── PerformanceAttribution.vue
    ├── BenchmarkComparison.vue
    └── RiskAdjustedReturns.vue
```

### Enhanced Components

```
resources/js/components/Investment/
├── Recommendations.vue                  # Replace placeholder
├── WhatIfScenarios.vue                  # Replace placeholder
├── TaxFees.vue                          # Expand functionality
├── GoalCard.vue                         # Enhanced features
└── Performance.vue                      # Additional metrics
```

---

## Testing Strategy

### Unit Tests (Pest)

```
tests/Unit/Services/Investment/
├── InvestmentPlanGeneratorTest.php
├── RecommendationPrioritizerTest.php
├── ScenarioAnalyzerTest.php
├── ContributionOptimizerTest.php
├── RebalancingCalculatorTest.php
├── TaxAwareRebalancerTest.php
├── GoalPlannerTest.php
├── WrapperOptimizerTest.php
└── PerformanceAnalyzerTest.php
```

**Key Tests:**
- Plan generation with various portfolios
- Recommendation prioritization algorithm
- Scenario comparison accuracy
- Tax optimization calculations
- Rebalancing accuracy (target allocation)
- Goal probability calculations
- ISA allowance tracking (cross-module)
- Fee impact calculations

### Feature Tests

```
tests/Feature/Investment/
├── InvestmentPlanGenerationTest.php
├── RecommendationWorkflowTest.php
├── ScenarioAnalysisTest.php
├── ContributionPlanningTest.php
├── RebalancingWorkflowTest.php
├── GoalPlanningTest.php
└── TaxOptimizationTest.php
```

**Key Tests:**
- Complete plan generation workflow
- Recommendation CRUD operations
- Scenario creation and comparison
- Cross-module ISA allowance coordination
- PDF generation
- Recommendation status tracking

### Integration Tests

```
tests/Integration/
└── InvestmentModuleIntegrationTest.php
```

**Key Tests:**
- Investment + Retirement integration
- Investment + Savings (ISA allowance)
- Investment + Estate (IHT calculation)
- Coordinating Agent holistic analysis

---

## Implementation Timeline

### Phase 1: Core Features (Weeks 1-3)
**Priority**: HIGH
**Target**: v0.2.0 release

**Week 1: Comprehensive Investment Plan**
- [ ] Day 1-2: Backend - `InvestmentPlanGenerator` service
- [ ] Day 3: Backend - `InvestmentPlanController` + routes
- [ ] Day 4-5: Frontend - `ComprehensiveInvestmentPlan.vue` + sub-components
- [ ] Day 5: PDF generation integration
- [ ] Testing & refinement

**Week 2: Recommendations System**
- [ ] Day 1: Backend - Enhanced `InvestmentAgent::generateRecommendations()`
- [ ] Day 2: Backend - `RecommendationPrioritizer` + `RecommendationTracker`
- [ ] Day 3: Database - Create `investment_recommendations` table + migration
- [ ] Day 4-5: Frontend - `Recommendations.vue` + sub-components
- [ ] Testing & refinement

**Week 3: What-If Scenarios**
- [ ] Day 1-2: Backend - Enhanced `InvestmentAgent::buildScenarios()` + `ScenarioAnalyzer`
- [ ] Day 3: Database - Create `investment_scenarios` table + migration
- [ ] Day 4-5: Frontend - `WhatIfScenarios.vue` + sub-components
- [ ] Integration with Monte Carlo simulator
- [ ] Testing & refinement

### Phase 2: Advanced Tools (Weeks 4-5)
**Priority**: MEDIUM
**Target**: v0.2.1 release

**Week 4: Contribution Planning & Rebalancing**
- [ ] Day 1-2: Backend - `ContributionOptimizer` service
- [ ] Day 2: Frontend - `ContributionPlanner.vue`
- [ ] Day 3-4: Backend - `RebalancingCalculator` + `TaxAwareRebalancer`
- [ ] Day 4: Database - Create `rebalancing_actions` table
- [ ] Day 5: Frontend - `RebalancingCalculator.vue`
- [ ] Testing

**Week 5: Goal Planning & Tax Optimization**
- [ ] Day 1-2: Backend - `GoalPlanner` service
- [ ] Day 2: Database - Enhance `investment_goals` table
- [ ] Day 3: Frontend - Enhanced goal components
- [ ] Day 4: Backend - `WrapperOptimizer` service
- [ ] Day 5: Frontend - `TaxOptimization.vue` + sub-components
- [ ] Testing

### Phase 3: Integration & Polish (Week 6)
**Priority**: HIGH
**Target**: v0.2.2 release

**Week 6: Cross-Module Integration & Testing**
- [ ] Day 1: Backend - `InvestmentCoordinationService`
- [ ] Day 2: Integration with Retirement module
- [ ] Day 3: Integration with Savings & Estate modules
- [ ] Day 4: Enhanced Coordinating Agent
- [ ] Day 5: Comprehensive testing, bug fixes, polish

---

## Success Metrics

### Feature Adoption
- [ ] 80%+ of users generate comprehensive investment plan within 30 days
- [ ] 60%+ of users view recommendations tab
- [ ] 40%+ of users run at least one what-if scenario
- [ ] 30%+ of users use contribution planner

### User Engagement
- [ ] Average time spent in Investment module increases by 50%
- [ ] Investment plan generation triggers action on 3+ recommendations
- [ ] Scenario comparison leads to contribution changes for 20% of users

### Technical Quality
- [ ] All unit tests passing (target: 50+ tests)
- [ ] Feature tests covering main workflows (target: 20+ tests)
- [ ] Code quality: PSR-12 compliant (Laravel Pint)
- [ ] No performance regressions (page load < 2s)

### User Satisfaction
- [ ] Qualitative feedback: "Comprehensive," "Actionable," "Easy to understand"
- [ ] Feature requests for additional scenarios/optimizations

---

## Risk Mitigation

### Technical Risks

**Risk**: Monte Carlo simulations are slow
**Mitigation**:
- Use Laravel Queue for background processing
- Show progress indicator
- Cache results for 24 hours
- Optimize simulation algorithm

**Risk**: Plan generation is complex and brittle
**Mitigation**:
- Modular service design
- Comprehensive unit tests
- Graceful degradation (show partial plan if some data missing)
- Clear error messages

**Risk**: Cross-module integration breaks existing functionality
**Mitigation**:
- Extensive integration testing
- Feature flags for gradual rollout
- Maintain backward compatibility
- Clear API contracts

### User Experience Risks

**Risk**: Users overwhelmed by complexity
**Mitigation**:
- Progressive disclosure (show summary first, details on demand)
- Contextual help and tooltips
- Guided workflows
- Simplified language (avoid jargon)

**Risk**: Recommendations not actionable
**Mitigation**:
- Specific action steps (not vague advice)
- Priority ranking
- "Quick wins" highlighted
- Integration with real actions (e.g., rebalancing calculator)

---

## Future Enhancements (v0.3+)

### Advanced Features
- Portfolio backtesting
- ESG/Sustainable investing tracking
- Factor exposure analysis
- Currency hedging analysis
- Leverage/borrowing optimization
- Tax-aware asset location optimizer
- Robo-advisor automation (auto-rebalancing)

### Integrations
- Open Banking integration (live portfolio import)
- Broker API integrations (execute trades)
- HMRC integration (auto-populate tax data)
- External portfolio tracking services

### AI/ML Features
- Predictive analytics (market forecasting)
- Behavioral coaching (prevent panic selling)
- Natural language plan generation
- Chatbot for investment questions

---

## Appendix

### Key Formulas & Calculations

**Portfolio Health Score (0-100):**
```
Score = (
    DiversificationScore * 0.25 +
    RiskAlignmentScore * 0.20 +
    TaxEfficiencyScore * 0.20 +
    FeeScore * 0.15 +
    GoalProgressScore * 0.20
)
```

**Recommendation Priority Score:**
```
Priority = (
    PotentialImpact * 0.40 +
    Urgency * 0.30 +
    EaseOfImplementation * 0.20 +
    TaxSaving * 0.10
)
```

**Rebalancing Threshold:**
```
Trigger rebalancing if:
  ABS(CurrentAllocation - TargetAllocation) > Threshold
  Default threshold: 5%
```

**Tax Loss Harvesting Opportunity:**
```
If:
  UnrealizedLoss > £500
  AND RealizedGains > 0
  AND NoWashSaleViolation
Then: Recommend harvesting
```

---

## Conclusion

This expansion transforms the Investment module from a basic portfolio tracker into a comprehensive financial planning tool that rivals professional adviser software. The phased approach ensures:

1. **Quick wins**: Core features (plan, recommendations, scenarios) in first 3 weeks
2. **Solid foundation**: Advanced tools build on core infrastructure
3. **Integration**: Holistic planning across all modules
4. **User value**: Actionable advice, not just data

**Next Steps:**
1. Review and approve this plan
2. Set up project tracking (GitHub issues/projects)
3. Begin Phase 1, Week 1 implementation
4. Weekly progress reviews and adjustments

---

**Document Version**: 1.0
**Last Updated**: November 1, 2025
**Author**: Claude (TenGo Development Team)
