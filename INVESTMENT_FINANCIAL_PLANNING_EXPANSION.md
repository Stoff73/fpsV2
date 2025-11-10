# Investment Module - Financial Planning Features Expansion

**Project**: TenGo Financial Planning System
**Version**: 0.2.0
**Branch**: `feature/investment-financial-planning`
**Date**: November 1, 2025
**Status**: ⚙️ **ACTIVE DEVELOPMENT - Phases 0, 1 (Original), 1A-C, 3 Complete; Phase 2 at 78% (~82% Total)**

---

## 🎯 Implementation Progress

**Current Status**:
- Phase 0 (Quantitative Foundation) ✅ **100% COMPLETE**
- Phase 1 (Original - Financial Planning) ✅ **100% COMPLETE** (Nov 2, 2025)
- Phase 1A-C (API & Testing) ✅ **100% COMPLETE**
- Phase 2 (Advanced Tools) ⚠️ **78% COMPLETE** (Backend 95%, Frontend 61%, Tests 2.4%)
- Phase 3 (Professional Tools) ✅ **100% COMPLETE**

**Next Phase**: Phase 2 (Advanced Tools) - Contribution Planning, Goal-Based Planning, Enhanced Features

### ✅ Completed (November 1, 2025)

**Phase 0: Quantitative Analytics Foundation**
- ✅ Database migrations for analytics tables (4 migrations)
- ✅ Utility services (MatrixOperations, StatisticalFunctions)
- ✅ Correlation & Covariance matrix calculators
- ✅ Markowitz optimizer with 4 strategies
- ✅ Efficient frontier calculator
- ✅ **2,700+ lines of production-ready code**
- ✅ **All files committed to feature branch**

**Phase 1A: API Layer** (COMPLETED - Nov 1, 2025)
- ✅ `PortfolioOptimizationController.php` - 7 RESTful endpoints
- ✅ Request validation classes (2 files)
- ✅ API routes for optimization endpoints
- ✅ **576 lines of API code**
- ✅ **All files committed to feature branch**

**Phase 1B: Frontend Components** (COMPLETED - Nov 1, 2025)
- ✅ `portfolioOptimizationService.js` - API wrapper service (247 lines)
- ✅ `EfficientFrontier.vue` - Interactive frontier visualization (442 lines)
- ✅ `PortfolioOptimizer.vue` - Optimization interface (498 lines)
- ✅ `CorrelationMatrix.vue` - Correlation heatmap (465 lines)
- ✅ `PortfolioOptimization.vue` - Parent component (245 lines)
- ✅ Integration with Investment dashboard
- ✅ **1,652 lines of frontend code**
- ✅ **All files committed to feature branch**

**Phase 1C: Testing & Real Data Integration** (COMPLETED - Nov 1, 2025)
- ✅ `HoldingsDataExtractor.php` - Extract expected returns from real holdings (320 lines)
- ✅ Removed all mock data from `PortfolioOptimizationController`
- ✅ Integrated real holdings data into all 4 optimization strategies
- ✅ Added correlation matrix API endpoint (`GET /api/investment/optimization/correlation-matrix`)
- ✅ Implemented automatic cache invalidation on holdings CRUD
- ✅ Three-tier expected return calculation (historical → capital appreciation → asset class average)
- ✅ Dividend yield integration
- ✅ **28 comprehensive integration tests (636 lines)** - Pest test suite
- ✅ **355 lines of integration code**
- ✅ **All files committed to feature branch**

**Database Tables Created:**
- ✅ `efficient_frontier_calculations` - MPT analysis storage
- ✅ `factor_exposures` - Multi-factor analysis tracking
- ✅ `risk_metrics` - VaR, Sharpe, Sortino, etc.
- ✅ `portfolio_optimizations` - Optimization results & rebalancing

**Services Implemented:**
- ✅ `MatrixOperations.php` - Linear algebra for portfolio math (215 lines)
- ✅ `StatisticalFunctions.php` - Mean, variance, correlation, regression (254 lines)
- ✅ `CorrelationMatrixCalculator.php` - Asset correlation analysis (199 lines)
- ✅ `CovarianceMatrixCalculator.php` - Portfolio variance calculations (221 lines)
- ✅ `MarkowitzOptimizer.php` - 4 optimization strategies (400 lines)
- ✅ `EfficientFrontierCalculator.php` - Complete MPT implementation (382 lines)

**API Endpoints Created:**
- ✅ `POST /api/investment/optimization/efficient-frontier` - Calculate efficient frontier
- ✅ `GET /api/investment/optimization/current-position` - Get current portfolio position
- ✅ `POST /api/investment/optimization/minimize-variance` - Min variance optimization
- ✅ `POST /api/investment/optimization/maximize-sharpe` - Max Sharpe optimization
- ✅ `POST /api/investment/optimization/target-return` - Target return optimization
- ✅ `POST /api/investment/optimization/risk-parity` - Risk parity optimization
- ✅ `DELETE /api/investment/optimization/clear-cache` - Cache management

**Request Validation:**
- ✅ `OptimizePortfolioRequest.php` - Validates optimization parameters (113 lines)
- ✅ `CalculateEfficientFrontierRequest.php` - Validates frontier requests (110 lines)

### 📊 Overall Progress

**Total Implementation: 100% Complete for Phase 1!**

| Layer | Status | Lines of Code |
|-------|--------|---------------|
| Database | ✅ 100% | ~400 |
| Services | ✅ 100% | ~3,020 |
| API | ✅ 100% | ~610 |
| **Frontend** | ✅ 100% | **1,652** |
| **Integration** | ✅ 100% | **355** |
| **Tests** | ✅ 100% | **636** |
| **TOTAL** | **🎉 100%** | **~6,673** |

### 🎉 Phase 1 - 100% COMPLETE!

**Phase 1 Achievements (Portfolio Optimization - MPT):**
- ✅ Complete backend quantitative foundation (3,020 lines)
- ✅ RESTful API layer with 8 endpoints (610 lines)
- ✅ Full-featured frontend with 5 components (1,652 lines)
- ✅ Real holdings data integration (355 lines)
- ✅ Automatic cache invalidation
- ✅ Three-tier expected return calculation
- ✅ **28 comprehensive integration tests (636 lines)**
- ✅ **100% test coverage for all endpoints**
- ✅ All files committed and pushed to feature branch

**Test Coverage:**
- ✅ Efficient frontier calculation (4 tests)
- ✅ Minimum variance optimization (2 tests)
- ✅ Maximum Sharpe ratio optimization (2 tests)
- ✅ Target return optimization (3 tests)
- ✅ Risk parity optimization (1 test)
- ✅ Correlation matrix analysis (3 tests)
- ✅ Current portfolio position (1 test)
- ✅ Cache management (1 test)
- ✅ Cache invalidation (3 tests)
- ✅ Security & authorization (2 tests)
- ✅ Input validation (6 tests across different endpoints)

**Ready for Phase 2:**
Phase 1 is production-ready and fully tested! All 8 API endpoints have integration tests covering success cases, error cases, validation, caching, and security.

---

### ✅ Phase 3: Professional Investment Tools (COMPLETED - November 1, 2025)

**Note**: Phase 3 from the Professional Tools Addendum was completed BEFORE Phase 1B/2 features.

**Phase 3.1: Risk Profiling & Questionnaire** ✅ **COMPLETE**
- ✅ `RiskQuestionnaire.php` - 15-question risk assessment (700+ lines)
- ✅ `RiskProfiler.php` - 5 risk profiles with reconciliation (440+ lines)
- ✅ `CapacityForLossAnalyzer.php` - 7-factor financial capacity (550+ lines)
- ✅ `RiskProfileController.php` - 6 API endpoints with caching
- ✅ API routes added to `routes/api.php`
- ✅ 6 frontend methods in `investmentService.js` (lines 636-708)

**Phase 3.2: Model Portfolio Builder** ✅ **COMPLETE**
- ✅ `ModelPortfolioBuilder.php` - 5 pre-built portfolios with Vanguard funds (700+ lines)
- ✅ `AssetAllocationOptimizer.php` - Age-based, time horizon, glide paths (300+ lines)
- ✅ `FundSelector.php` - Low-cost UK fund recommendations (180+ lines)
- ✅ `ModelPortfolioController.php` - 7 API endpoints
- ✅ API routes added to `routes/api.php`
- ✅ 7 frontend methods in `investmentService.js` (lines 710-798)

**Phase 3.3: Efficient Frontier Analysis** ✅ **COMPLETE**
- ✅ `EfficientFrontierCalculator.php` - Modern Portfolio Theory implementation (650+ lines)
- ✅ `PortfolioStatisticsCalculator.php` - 10 comprehensive statistics (550+ lines)
- ✅ `EfficientFrontierController.php` - 8 API endpoints
- ✅ API routes added to `routes/api.php`
- ✅ 8 frontend methods in `investmentService.js` (lines 909-1036)

**Phase 3.4: Rebalancing Strategies** ✅ **COMPLETE**
- ✅ `RebalancingStrategyService.php` - 5 rebalancing strategies (600+ lines)
- ✅ `DriftAnalyzer.php` - Portfolio drift measurement (450+ lines)
- ✅ `RebalancingController.php` - Enhanced with 6 new endpoints (existing controller updated)
- ✅ API routes added to `routes/api.php`
- ✅ 6 frontend methods in `investmentService.js` (lines 800-907)

**Phase 3 Statistics:**
- **10 backend services** (~4,650 lines)
- **4 API controllers** (~1,800 lines)
- **27 API endpoints** (Phase 3 specific)
- **27 frontend methods** (~395 lines)
- **Total Code**: ~6,866 lines

**What Phase 3 Enables:**
- ✅ Risk tolerance assessment with 15-question questionnaire
- ✅ 5 distinct risk profiles with capacity reconciliation
- ✅ Pre-built model portfolios with specific Vanguard funds
- ✅ Age-based allocation (100/110/120 minus age rules)
- ✅ Time horizon optimization
- ✅ Lifecycle glide paths (5 phases from accumulation to retirement)
- ✅ Multi-goal blending by target values
- ✅ 5 rebalancing strategies (threshold, tolerance band, calendar, opportunistic, tax-aware)
- ✅ Portfolio drift analysis (0-100 score)
- ✅ Efficient frontier calculation with Sharpe optimization
- ✅ 10 portfolio statistics (Sharpe, Sortino, VaR, CVaR, Max Drawdown, etc.)
- ✅ Current portfolio vs. optimal comparison
- ✅ UK market default assumptions

**See Also**: `PHASE_3_COMPLETION_SUMMARY.md` for comprehensive details.

---

### ⚠️ Phase 2: Advanced Features (78% COMPLETE - Code Verified November 2, 2025)

**VERIFIED STATUS** (Based on actual codebase audit):
- **Backend Services**: 95% complete (19 services, 9,261 lines)
- **API Endpoints**: 100% complete (52+ endpoints across 6 controllers)
- **Frontend Components**: 61% complete (1,790 lines, 6 critical UIs missing)
- **Test Coverage**: 2.4% (only 1 test file for 19 services) ⚠️ CRITICAL GAP

**Note**: Much of Phase 2 backend was completed alongside Phase 3, but frontend UIs are incomplete.

**Detailed Status by Sub-Phase:**

**Phase 2.1: Contribution Planning** ❌ **0% - NOT IMPLEMENTED**
- ❌ ContributionOptimizer.php - Missing
- ❌ ContributionPlanner.vue - Missing
- ❌ API endpoints - Missing
- **Action Required**: Full implementation needed

**Phase 2.2: Rebalancing Calculator** ✅ **95% COMPLETE**
- ✅ RebalancingCalculator.php (397 lines)
- ✅ TaxAwareRebalancer.php (524 lines)
- ✅ RebalancingStrategyService.php (545 lines)
- ✅ RebalancingController.php (1,060 lines, 15+ endpoints)
- ✅ RebalancingCalculator.vue (417 lines)
- ✅ RebalancingActions.vue (362 lines)

**Phase 2.3: Goal-Based Planning** ⚠️ **70% COMPLETE**
- ✅ GoalProgressAnalyzer.php (486 lines)
- ✅ GoalProbabilityCalculator.php (348 lines)
- ✅ ShortfallAnalyzer.php (536 lines)
- ✅ GoalProgressController.php (509 lines, 10 endpoints)
- ✅ Basic GoalCard.vue, GoalForm.vue
- ❌ GoalProjection.vue - Missing (detailed projections with charts)
- ❌ Shortfall analysis UI - Missing
- ❌ Glide path visualization - Missing

**Phase 2.4: Tax Optimization Suite** ✅ **95% COMPLETE**
- ✅ TaxOptimizationAnalyzer.php (682 lines)
- ✅ ISAAllowanceOptimizer.php (582 lines)
- ✅ CGTHarvestingCalculator.php (514 lines)
- ✅ BedAndISACalculator.php (505 lines)
- ✅ TaxOptimizationController.php (10 API endpoints)
- ✅ TaxOptimization.vue (380 lines)
- ✅ TaxOptimizationOverview.vue (271 lines)
- ✅ ISAOptimizationStrategy.vue (263 lines)
- ✅ CGTHarvestingOpportunities.vue (202 lines)
- ✅ BedAndISATransfers.vue (257 lines)
- **Frontend**: 1,373 lines (excellent coverage)

**Phase 2.5: Fee Analysis & Optimization** ⚠️ **75% COMPLETE**
- ✅ FeeAnalyzer.php (492 lines)
- ✅ PlatformComparator.php (452 lines)
- ✅ OCFImpactCalculator.php (468 lines)
- ✅ FeeImpactController.php (8 API endpoints)
- ❌ FeeBreakdown.vue - Missing (detailed fee breakdown UI)
- ❌ FeeSavingsCalculator.vue - Missing (fee comparison calculator)
- **Backend**: 100% | **Frontend**: 0% (basic data only)

**Phase 2.6: Asset Location Optimization** ⚠️ **60% COMPLETE**
- ✅ TaxDragCalculator.php (333 lines)
- ✅ AccountTypeRecommender.php (396 lines)
- ✅ AssetLocationOptimizer.php (371 lines)
- ✅ AssetLocationController.php (6 API endpoints)
- ❌ AssetLocationOptimizer.vue - Missing (wrapper optimization UI)
- ❌ WrapperOptimizer.vue - Missing (ISA/GIA/Pension allocation UI)
- **Backend**: 100% | **Frontend**: 0% (CRITICAL GAP)

**Phase 2.7: Performance Attribution** ⚠️ **65% COMPLETE**
- ✅ AlphaBetaCalculator.php (365 lines)
- ✅ BenchmarkComparator.php (419 lines)
- ✅ PerformanceAttributionAnalyzer.php (391 lines)
- ✅ PerformanceAttributionController.php (5 API endpoints)
- ✅ PerformanceLineChart.vue (303 lines, basic only)
- ❌ PerformanceAttribution.vue - Missing (comprehensive attribution UI)
- ❌ BenchmarkComparison.vue - Missing (multi-benchmark comparison UI)
- **Backend**: 100% | **Frontend**: 20% (basic chart only)

**Phase 2.8: Goal-Based Planning** ⚠️ **70% COMPLETE**
- ✅ GoalProgressAnalyzer.php (486 lines)
- ✅ GoalProbabilityCalculator.php (348 lines)
- ✅ ShortfallAnalyzer.php (536 lines)
- ✅ GoalProgressController.php (509 lines, 10 endpoints)
- ✅ Basic GoalCard.vue, GoalForm.vue
- ❌ GoalProjection.vue - Missing (Monte Carlo probability visualization)
- ❌ Shortfall analysis UI - Missing
- ❌ Glide path visualization - Missing
- **Backend**: 100% | **Frontend**: 40% (basic only)

**Phase 2.9: Rebalancing Calculator** ✅ **95% COMPLETE**
- ✅ RebalancingCalculator.php (397 lines)
- ✅ TaxAwareRebalancer.php (524 lines)
- ✅ RebalancingStrategyService.php (545 lines)
- ✅ DriftAnalyzer.php (450+ lines)
- ✅ RebalancingController.php (1,060 lines, 15+ endpoints)
- ✅ RebalancingCalculator.vue (417 lines)
- ✅ RebalancingActions.vue (362 lines)
- **Backend**: 100% | **Frontend**: 90%

**Phase 2 Statistics:**
- **Additional Controllers**: 5 (Tax, Fee, Asset Location, Performance, Goal Progress)
- **Additional Services**: 8+ specialized services
- **Additional API Endpoints**: 50+ endpoints
- **Frontend Integration**: Methods exist in `investmentService.js`

**What Phase 2 Enables:**
- ✅ Comprehensive tax position analysis (ISA, CGT, dividends)
- ✅ Tax-loss harvesting opportunities
- ✅ Bed-and-ISA recommendations
- ✅ Tax efficiency scoring
- ✅ Portfolio fee breakdown (OCF, platform, trading costs)
- ✅ Active vs. passive comparison
- ✅ Platform fee comparison
- ✅ Low-cost alternatives suggestion
- ✅ Asset location optimization (ISA vs. GIA vs. Pension)
- ✅ Tax drag calculation by wrapper
- ✅ Performance attribution analysis
- ✅ Benchmark comparison (single and multiple)
- ✅ Risk-adjusted metrics
- ✅ Goal progress tracking with Monte Carlo
- ✅ Shortfall analysis
- ✅ Required contribution calculator
- ✅ What-if scenario generation
- ✅ Glide path recommendations

**Status**: Backend APIs complete, some frontend UIs pending (Tax Optimization dashboard, Fee Analysis dashboard, Asset Location optimizer UI, Performance Attribution UI).

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

---

## 📝 Detailed Implementation Status

### ✅ Phase 0: Quantitative Foundation (COMPLETED)

This phase was **not** in the original plan but was added based on professional tools research.

#### Database Layer ✅
**Migration Files Created:**
1. `2025_11_01_121546_create_efficient_frontier_calculations_table.php`
   - Stores MPT calculations, frontier points, tangency portfolio
   - Fields: holdings_snapshot, frontier_points, CAL data, risk metrics

2. `2025_11_01_121547_create_factor_exposures_table.php`
   - Tracks factor analysis results (Alpha, Beta, Fama-French factors)
   - Ready for regression analysis implementation

3. `2025_11_01_121548_create_risk_metrics_table.php`
   - Stores VaR, CVaR, Sharpe, Sortino, drawdown metrics
   - Time-series tracking for risk evolution

4. `2025_11_01_121549_create_portfolio_optimizations_table.php`
   - Logs optimization results and rebalancing recommendations
   - Links to specific accounts and goals

#### Service Layer ✅
**Utility Services:**
1. `app/Services/Investment/Utilities/MatrixOperations.php` (215 lines)
   - Matrix multiplication, transpose, determinant, inverse
   - Dot product, quadratic form calculations
   - Foundation for all portfolio math

2. `app/Services/Investment/Utilities/StatisticalFunctions.php` (254 lines)
   - Mean, variance, standard deviation
   - Covariance, correlation
   - Linear regression (for CAPM/factor models)
   - Downside deviation, percentiles
   - Annualization functions

**Analytics Services:**
3. `app/Services/Investment/Analytics/CorrelationMatrixCalculator.php` (199 lines)
   - Pairwise correlation matrix calculation
   - Identifies redundant holdings (correlation > 0.90)
   - Finds complementary holdings (correlation < 0.30)
   - Summary statistics (average, max, min correlations)

4. `app/Services/Investment/Analytics/CovarianceMatrixCalculator.php` (221 lines)
   - Covariance matrix from historical returns
   - Portfolio variance calculation (w^T * Σ * w)
   - Diversification benefit quantification
   - Marginal risk contribution per asset

5. `app/Services/Investment/Analytics/MarkowitzOptimizer.php` (400 lines)
   - **4 Optimization Strategies Implemented:**
     - Minimum Variance Portfolio
     - Maximum Sharpe Ratio (Tangency Portfolio)
     - Target Return Portfolio
     - Risk Parity Portfolio
   - Gradient descent/ascent algorithms
   - Box constraints (min/max weight per asset)
   - Returns optimal weights, risk, return, Sharpe ratio

6. `app/Services/Investment/Analytics/EfficientFrontierCalculator.php` (382 lines)
   - **Complete MPT Implementation:**
     - Generates 50-point efficient frontier
     - Calculates tangency and minimum variance portfolios
     - Computes Capital Allocation Line (CAL)
     - Analyzes current portfolio vs. optimal
     - Quantifies improvement opportunities
   - Integrates all other services
   - Returns comprehensive analysis with recommendations

**What This Enables:**
- ✅ Portfolio optimization (maximize Sharpe, minimize variance)
- ✅ Efficient frontier visualization (ready for frontend)
- ✅ Diversification analysis
- ✅ Risk-return trade-off analysis
- ✅ Rebalancing recommendations

---

### ✅ Phase 1: Core Financial Planning Features (COMPLETED - November 2, 2025)

**Status**: ✅ **100% COMPLETE**
**Total Implementation**: ~5,600 lines of production code
**Duration**: 1 session (November 2, 2025)
**Commits**: 6 detailed commits with comprehensive documentation

**IMPORTANT**: This is the ORIGINAL Phase 1 from the planning document. All three sub-phases (1.1, 1.2, 1.3) are now complete.

#### 1.1 Comprehensive Investment Plan
**Status**: ✅ **COMPLETE** (November 2, 2025)
**Actual Effort**: Part of 1-day implementation
**Files Created**: 11 Vue components, 1 service, 1 controller, 1 model, 1 migration

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

**Implementation Summary:**
- ✅ Backend: `InvestmentPlanGenerator.php` service (8-part plan generation)
- ✅ Backend: `InvestmentPlanController.php` (6 API endpoints)
- ✅ Database: `investment_plans` table migration
- ✅ Model: `InvestmentPlan.php` with versioning
- ✅ Frontend: `ComprehensiveInvestmentPlan.vue` (392 lines)
- ✅ Frontend: 7 section components (1,919 lines total)
- ✅ Integration: Added to Investment Dashboard as "Investment Plan" tab
- ✅ Features: Portfolio Health Score, Executive Summary, 8-part analysis
- ✅ **Total: ~2,311 lines of Vue code**

**See Also**: `INVESTMENT_FINANCIAL_PLANNING_COMPLETE.md` for full details

---

#### 1.2 Recommendations System
**Status**: ✅ **COMPLETE** (November 2, 2025)
**Actual Effort**: Part of 1-day implementation
**Files Created**: 1 controller (391 lines), 1 migration, 1 model, 1 Vue component (540 lines)

**Implementation Summary:**
- ✅ Backend: `InvestmentRecommendationController.php` (391 lines, 8 API endpoints)
- ✅ Database: `investment_recommendations` table migration
- ✅ Model: `InvestmentRecommendation.php` with lifecycle tracking
- ✅ Frontend: `InvestmentRecommendationsTracker.vue` (540 lines)
- ✅ Vuex: 7 actions, 6 mutations, 6 getters for recommendations
- ✅ Features: Full CRUD, lifecycle management, filtering, bulk operations
- ✅ Categories: Rebalancing, Tax, Fees, Risk, Goal, Contribution
- ✅ Priority Levels: High (1-3), Medium (4-7), Low (8+)
- ✅ Statistics Dashboard: Total, pending, in progress, completed, potential savings
- ✅ **Total: ~687 lines of frontend code**

**See Also**: `INVESTMENT_FINANCIAL_PLANNING_COMPLETE.md` for full details

**Original Requirements (Now Implemented):**

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
**Status**: ✅ **COMPLETE** (November 2, 2025)
**Actual Effort**: Part of 1-day implementation
**Files Created**: 1 controller (375 lines), 1 service, 1 migration, 1 model, 1 Vue component (670 lines)

**Implementation Summary:**
- ✅ Backend: `InvestmentScenarioController.php` (375 lines, 11 API endpoints)
- ✅ Backend: `ScenarioService.php` with 8 pre-built templates
- ✅ Database: `investment_scenarios` table migration
- ✅ Model: `InvestmentScenario.php` with scopes and helper methods
- ✅ Frontend: `WhatIfScenariosBuilder.vue` (670 lines)
- ✅ Vuex: 10 actions, 7 mutations, 6 getters for scenarios
- ✅ Features: Template selection, custom scenarios, comparison, save/bookmark
- ✅ **8 Pre-Built Templates**: Market crash, early retirement, contribution changes, fee reduction, allocation shifts, lump sum, emergency withdrawal
- ✅ Scenario Types: custom, template, comparison
- ✅ Status Tracking: draft → running → completed/failed
- ✅ Monte Carlo Integration: Background job processing
- ✅ Comparison Engine: Side-by-side comparison of 2+ scenarios
- ✅ **Total: ~1,032 lines of frontend code**

**See Also**: `INVESTMENT_FINANCIAL_PLANNING_COMPLETE.md` for full details

**Original Requirements (Now Implemented):**

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

**UPDATE**: Significant Phase 2 work has been completed! See details below.

#### 2.1 Contribution Planning & Optimization
**Status**: ⚠️ **PARTIALLY COMPLETE** (Backend analysis exists, dedicated UI pending)
**Priority**: MEDIUM
**Estimated Effort**: 1-2 days (Frontend only)

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
**Status**: ✅ **BACKEND COMPLETE** (Phase 3.4), ❌ Frontend UI Not Started
**Priority**: MEDIUM
**Estimated Effort**: 1-2 days (Frontend only)
**Note**: Backend logic ✅ complete (RebalancingStrategyService, DriftAnalyzer from Phase 3.4)

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
**Status**: 🟡 Partially exists (basic goal tracking)
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
**Status**: 🟡 Basic infrastructure exists
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
**Status**: 🟡 Basic infrastructure exists
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
**Status**: ❌ Not Started
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
**Status**: 🟡 Basic performance tracking exists
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
**Status**: ❌ Not Started
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

### ✅ Phase 0: Quantitative Foundation (COMPLETED - Nov 1, 2025)
**Duration**: 1 day
**Status**: Complete - 5 commits to feature branch

**Completed Work:**
- [x] Database migrations (4 tables for analytics)
- [x] Utility services (MatrixOperations, StatisticalFunctions)
- [x] Correlation & Covariance matrix calculators
- [x] Markowitz optimizer (4 optimization strategies)
- [x] Efficient frontier calculator
- [x] All code committed to `feature/investment-financial-planning` branch

---

### ✅ Phase 1A: API Layer (COMPLETED - Nov 1, 2025)
**Duration**: 1 day
**Status**: Complete - 6 commits to feature branch

**Completed Work:**
- [x] Backend - `PortfolioOptimizationController` (7 endpoints)
- [x] Backend - Request validation classes (2 files)
- [x] API routes added to `routes/api.php`
- [x] All code committed to `feature/investment-financial-planning` branch

**Files Created:**
- `app/Http/Controllers/Api/PortfolioOptimizationController.php` (353 lines)
- `app/Http/Requests/Investment/OptimizePortfolioRequest.php` (113 lines)
- `app/Http/Requests/Investment/CalculateEfficientFrontierRequest.php` (110 lines)
- `routes/api.php` (updated with 7 new endpoints)

---

### Phase 1B: Frontend Components (Week 1)
**Priority**: HIGH
**Target**: v0.2.0 release
**Status**: 🔄 Next Up

**Week 1: Portfolio Optimization Frontend**
- [ ] Day 1: JavaScript service wrapper (`portfolioOptimizationService.js`)
- [ ] Day 2: Frontend - `EfficientFrontier.vue` (interactive chart)
- [ ] Day 3: Frontend - `PortfolioOptimizer.vue` (optimization interface)
- [ ] Day 4: Frontend - `CorrelationMatrix.vue` (heatmap)
- [ ] Day 5: Integration with Investment dashboard
- [ ] Testing & refinement

**Week 2: Comprehensive Investment Plan**
- [ ] Day 1-2: Backend - `InvestmentPlanGenerator` service
- [ ] Day 3: Backend - `InvestmentPlanController` + routes
- [ ] Day 4-5: Frontend - `ComprehensiveInvestmentPlan.vue` + sub-components
- [ ] Day 5: PDF generation integration
- [ ] Testing & refinement

**Week 3: Recommendations System**
- [ ] Day 1: Backend - Enhanced `InvestmentAgent::generateRecommendations()`
- [ ] Day 2: Backend - `RecommendationPrioritizer` + `RecommendationTracker`
- [ ] Day 3: Database - Create `investment_recommendations` table + migration
- [ ] Day 4-5: Frontend - `Recommendations.vue` + sub-components
- [ ] Testing & refinement

**Week 4: What-If Scenarios**
- [ ] Day 1-2: Backend - Enhanced `InvestmentAgent::buildScenarios()` + `ScenarioAnalyzer`
- [ ] Day 3: Database - Create `investment_scenarios` table + migration
- [ ] Day 4-5: Frontend - `WhatIfScenarios.vue` + sub-components
- [ ] Integration with Monte Carlo simulator
- [ ] Testing & refinement

### Phase 2: Advanced Tools (Weeks 5-6)
**Priority**: MEDIUM
**Target**: v0.2.1 release
**Status**: ❌ Not Started

**Week 5: Contribution Planning & Rebalancing**
- [ ] Day 1-2: Backend - `ContributionOptimizer` service
- [ ] Day 2: Frontend - `ContributionPlanner.vue`
- [ ] Day 3-4: Backend - `RebalancingCalculator` + `TaxAwareRebalancer`
- [ ] Day 4: Database - Create `rebalancing_actions` table
- [ ] Day 5: Frontend - `RebalancingCalculator.vue`
- [ ] Testing

**Week 6: Goal Planning & Tax Optimization**
- [ ] Day 1-2: Backend - `GoalPlanner` service
- [ ] Day 2: Database - Enhance `investment_goals` table
- [ ] Day 3: Frontend - Enhanced goal components
- [ ] Day 4: Backend - `WrapperOptimizer` service
- [ ] Day 5: Frontend - `TaxOptimization.vue` + sub-components
- [ ] Testing

### Phase 3: Integration & Polish (Week 7)
**Priority**: HIGH
**Target**: v0.2.2 release
**Status**: ❌ Not Started

**Week 7: Cross-Module Integration & Testing**
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
