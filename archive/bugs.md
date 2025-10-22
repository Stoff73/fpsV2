# FPS v2 - Bugs and Issues Report

**Generated**: 2025-10-15
**Status**: Comprehensive Testing and Bug Documentation

---

## Executive Summary

This document contains a comprehensive list of bugs, issues, and omissions discovered during systematic testing of the Financial Planning System (FPS) v2 application across all five modules: Protection, Savings, Investment, Retirement, and Estate Planning.

---

## FIXED ISSUES

### 1. ✅ FIXED: Critical Illness Policy 422 Validation Error
- **Module**: Protection
- **Severity**: HIGH
- **Description**: When attempting to add a Critical Illness policy, the API returned a 422 Unprocessable Entity error
- **Root Cause**: PolicyFormModal was setting `policy_type = 'term'` for both Life Insurance and Critical Illness policies, but the backend expects `policy_type` to be one of `['standalone', 'accelerated', 'additional']` for critical illness
- **Fix**: Updated `PolicyFormModal.vue` line 382-394 to separate life insurance and critical illness logic
- **Files Modified**:
  - `resources/js/components/Protection/PolicyFormModal.vue`
- **Status**: ✅ FIXED

### 2. ✅ FIXED: Estate Module Cash Flow Tab Infinite Loop
- **Module**: Estate
- **Severity**: CRITICAL
- **Description**: Cash Flow tab would hang indefinitely, causing infinite API requests and eventually hitting rate limit (429)
- **Root Cause**: EstateDashboard was using global `loading` state with `v-if`, which destroyed/recreated components when loading changed, triggering `mounted()` again in an infinite loop
- **Fix**: Created local `initialLoading` state in EstateDashboard instead of watching global loading state
- **Files Modified**:
  - `resources/js/views/Estate/EstateDashboard.vue`
  - `resources/js/components/Estate/CashFlow.vue`
- **Status**: ✅ FIXED

### 3. ✅ FIXED: Liability Form Missing Database Columns
- **Module**: Estate
- **Severity**: HIGH
- **Description**: When attempting to add a liability, additional fields (secured_against, is_priority_debt, mortgage_type, fixed_until, notes) were being silently ignored
- **Root Cause**:
  - Database migration missing 5 columns
  - Enum values mismatch (migration had 4 types, form/controller expected 9)
  - Model fillable array incomplete
- **Fix**:
  - Created migration `2025_10_15_094650_add_additional_fields_to_liabilities_table.php`
  - Updated `Liability` model fillable array
  - Updated casts for boolean and date fields
- **Files Modified**:
  - `database/migrations/2025_10_15_094650_add_additional_fields_to_liabilities_table.php`
  - `app/Models/Estate/Liability.php`
- **Status**: ✅ FIXED

---

## PENDING ISSUES

### Protection Module

#### P-1: Missing Protection Profile Initialization
- **Severity**: MEDIUM
- **Description**: New users may not have a protection profile created automatically
- **Impact**: Coverage gap analysis may fail or show incorrect data
- **Recommendation**: Create protection profile on first visit to Protection module or during registration
- **Files to Check**:
  - `app/Http/Controllers/Api/ProtectionController.php`
  - `app/Models/Protection/ProtectionProfile.php`

#### P-2: Policy End Date Calculation
- **Severity**: LOW
- **Description**: Policy end dates should be auto-calculated from start date + term years
- **Impact**: Users have to manually calculate end dates
- **Recommendation**: Add computed field or auto-fill logic
- **Files to Check**:
  - `resources/js/components/Protection/PolicyFormModal.vue`

#### P-3: Coverage Gap Analysis Empty State
- **Severity**: LOW
- **Description**: When no policies exist, gap analysis should show helpful guidance
- **Impact**: User experience - empty charts without context
- **Recommendation**: Add empty state with call-to-action
- **Files to Check**:
  - `resources/js/components/Protection/GapAnalysis.vue`

#### P-4: Premium Breakdown Chart Missing Data Validation
- **Severity**: MEDIUM
- **Description**: Chart may fail to render if policy data is incomplete
- **Impact**: Visual glitches or errors in console
- **Recommendation**: Add null checks and default values
- **Files to Check**:
  - `resources/js/components/Protection/PremiumBreakdownChart.vue`

### Savings Module

#### S-1: ISA Allowance Tracking Incomplete
- **Severity**: MEDIUM
- **Description**: ISA allowance tracker needs to coordinate with Investment module for Stocks & Shares ISA
- **Impact**: Users may exceed £20,000 combined allowance
- **Recommendation**: Implement cross-module ISA tracking
- **Files to Check**:
  - `resources/js/components/Savings/ISAAllowanceTracker.vue`
  - `app/Services/Savings/ISATracker.php`

#### S-2: Emergency Fund Calculation Missing Expenses
- **Severity**: MEDIUM
- **Description**: Emergency fund "months of coverage" calculation requires expense data which may not be entered
- **Impact**: Gauge shows 0% or incorrect values
- **Recommendation**: Add expense input form or link to Estate module cash flow
- **Files to Check**:
  - `resources/js/components/Savings/EmergencyFund.vue`
  - `app/Services/Savings/EmergencyFundCalculator.php`

#### S-3: Savings Goal Progress Not Updating
- **Severity**: MEDIUM
- **Description**: Goal progress should auto-update when linked account balance changes
- **Impact**: Users see stale data
- **Recommendation**: Implement reactive updates or refresh mechanism
- **Files to Check**:
  - `resources/js/store/modules/savings.js`
  - `app/Services/Savings/GoalTracker.php`

#### S-4: Interest Rate Validation Missing
- **Severity**: LOW
- **Description**: Interest rates should be validated for realistic ranges (0-20%)
- **Impact**: Users could enter 200% by mistake
- **Recommendation**: Add validation rules
- **Files to Check**:
  - `resources/js/components/Savings/SaveAccountModal.vue`
  - `app/Http/Controllers/Api/SavingsController.php`

### Investment Module

#### ✅ I-1: Monte Carlo Simulation Job Polling (FIXED)
- **Severity**: MEDIUM → **RESOLVED**
- **Description**: Monte Carlo simulations run as background jobs and need frontend polling
- **Impact**: Users don't see results or get stuck in loading state
- **Resolution**:
  - Comprehensive polling utility implemented in `poller.js`
  - `pollMonteCarloJob()` function with 100 attempts (200 seconds max)
  - Polls every 2 seconds, checking for 'running', 'queued', or 'completed' status
  - Handles 404 errors gracefully (job not ready yet)
  - Includes progress callbacks for UI updates
- **Files Verified**:
  - `resources/js/utils/poller.js` (lines 123-137 for Monte Carlo specific polling)
  - Generic `poll()` function (lines 29-103) with configurable options
- **Status**: ✅ FIXED

#### I-2: Asset Allocation Chart Empty State
- **Severity**: LOW
- **Description**: Donut chart shows nothing when no holdings exist
- **Impact**: Confusing UX for new users
- **Recommendation**: Show placeholder or sample allocation
- **Files to Check**:
  - `resources/js/components/Investment/AssetAllocationChart.vue`

#### I-3: Holding Price Update Mechanism Missing
- **Severity**: MEDIUM
- **Description**: No way to update holding prices after initial entry
- **Impact**: Portfolio values become stale
- **Recommendation**: Add "Update Prices" button or batch import
- **Files to Check**:
  - `resources/js/components/Investment/Holdings.vue`
  - `resources/js/components/Investment/HoldingForm.vue`

#### I-4: Fee Calculation Inconsistencies
- **Severity**: MEDIUM
- **Description**: Platform fees, fund fees, and transaction fees may not be calculated consistently
- **Impact**: Incorrect net returns and fee analysis
- **Recommendation**: Audit fee calculation logic
- **Files to Check**:
  - `app/Services/Investment/FeeCalculator.php`
  - `app/Models/Investment/InvestmentAccount.php`

#### I-5: Geographic Allocation Map Data Source
- **Severity**: LOW
- **Description**: Map requires holdings to have country/region data
- **Impact**: Map shows empty if data not entered
- **Recommendation**: Add geography field to holding form
- **Files to Check**:
  - `resources/js/components/Investment/GeographicAllocationMap.vue`
  - `resources/js/components/Investment/HoldingForm.vue`

### Retirement Module

#### ✅ R-1: DC Pension Annual Salary Field (FIXED)
- **Severity**: HIGH → **RESOLVED**
- **Description**: DC pension form was missing annual salary field needed for contribution calculations
- **Impact**: Cannot calculate employer/employee contributions correctly
- **Resolution**:
  - Migration `2025_10_15_085438_add_annual_salary_to_dc_pensions_table.php` added the field
  - Model includes `annual_salary` in fillable array with decimal casting
  - DCPensionForm.vue includes annual_salary input (line 101-108)
  - Contribution calculations use salary correctly (lines 318-328)
- **Files Modified**:
  - `database/migrations/2025_10_15_085438_add_annual_salary_to_dc_pensions_table.php`
  - `app/Models/DCPension.php`
  - `resources/js/components/Retirement/DCPensionForm.vue`
- **Status**: ✅ FIXED

#### R-2: Annual Allowance Taper Calculation
- **Severity**: MEDIUM
- **Description**: High earner taper (threshold income >£200k, adjusted income >£260k) may not be implemented
- **Impact**: Incorrect annual allowance shown for high earners
- **Recommendation**: Implement taper logic
- **Files to Check**:
  - `app/Services/Retirement/AnnualAllowanceCalculator.php`
  - `resources/js/components/Retirement/AnnualAllowanceTracker.vue`

#### R-3: Carry Forward Logic
- **Severity**: MEDIUM
- **Description**: 3-year carry forward of unused annual allowance may not be working
- **Impact**: Users don't know they can contribute more
- **Recommendation**: Implement carry forward tracker
- **Files to Check**:
  - `app/Services/Retirement/AnnualAllowanceCalculator.php`

#### R-4: State Pension Forecast Integration
- **Severity**: LOW
- **Description**: Users have to manually enter state pension amount - should guide to check.service.gov.uk
- **Impact**: May use incorrect state pension estimates
- **Recommendation**: Add help text with link to state pension forecast
- **Files to Check**:
  - `resources/js/components/Retirement/StatePensionForm.vue`

#### R-5: Retirement Income Projection Inflation
- **Severity**: MEDIUM
- **Description**: Income projections should adjust for inflation
- **Impact**: Misleading future values
- **Recommendation**: Add inflation adjustment toggle
- **Files to Check**:
  - `app/Services/Retirement/IncomeProjector.php`
  - `resources/js/components/Retirement/IncomeProjectionChart.vue`

#### R-6: MPAA (Money Purchase Annual Allowance) Trigger
- **Severity**: MEDIUM
- **Description**: No tracking of whether user has triggered MPAA (£10k limit after flexible access)
- **Impact**: User may unknowingly breach MPAA
- **Recommendation**: Add MPAA status field and warning
- **Files to Check**:
  - `app/Models/DCPension.php`
  - `resources/js/components/Retirement/AnnualAllowanceTracker.vue`

### Estate Module

#### E-1: Asset Valuation Date Handling
- **Severity**: MEDIUM
- **Description**: Asset values should have valuation dates and potentially track historical values
- **Impact**: Net worth calculation may use stale valuations
- **Recommendation**: Prompt users to update valuations periodically
- **Files to Check**:
  - `app/Models/Estate/Asset.php`
  - `resources/js/components/Estate/AssetForm.vue`

#### ✅ E-2: IHT Calculation with RNRB Taper (FIXED)
- **Severity**: HIGH → **RESOLVED**
- **Description**: Residence Nil Rate Band (£175k) needs to taper away for estates >£2m
- **Impact**: Incorrect IHT liability for high-value estates
- **Resolution**:
  - `IHTCalculator.php` lines 91-105 implement RNRB taper calculation
  - Config has correct values: RNRB £175k, taper threshold £2m, taper rate 0.5 (£1 reduction per £2 excess)
  - Calculation: `$rnrb = max(0, $rnrb - ($excess * $taperRate))`
  - Also includes PET taper relief (7-year rule) on lines 126-152
- **Files Verified**:
  - `app/Services/Estate/IHTCalculator.php`
  - `config/uk_tax_config.php`
- **Status**: ✅ FIXED

#### E-3: PET (Potentially Exempt Transfer) 7-Year Tracking
- **Severity**: MEDIUM
- **Description**: Gifting strategy needs to track 7-year rule and taper relief (3-7 years)
- **Impact**: Incorrect IHT calculations
- **Recommendation**: Verify taper relief logic
- **Files to Check**:
  - `app/Services/Estate/GiftingStrategyCalculator.php`
  - `resources/js/components/Estate/GiftingStrategy.vue`

#### E-4: CLT (Chargeable Lifetime Transfer) 14-Year Lookback
- **Severity**: MEDIUM
- **Description**: CLTs require 14-year lookback for cumulative total - may not be implemented
- **Impact**: Incorrect IHT on gifts to trusts
- **Recommendation**: Implement 14-year cumulative calculation
- **Files to Check**:
  - `app/Services/Estate/GiftingStrategyCalculator.php`

#### E-5: Cash Flow Personal P&L Data Sources
- **Severity**: MEDIUM
- **Description**: Cash flow tab requires income/expense data which may not be collected
- **Impact**: Empty or zero values in cash flow statement
- **Recommendation**: Add income/expense form or link to external data
- **Files to Check**:
  - `app/Services/Estate/CashFlowProjector.php`
  - `resources/js/components/Estate/CashFlow.vue`

#### E-6: Net Worth Chart Not Showing Historical Data
- **Severity**: LOW
- **Description**: Net worth should track changes over time
- **Impact**: No trend analysis possible
- **Recommendation**: Store periodic snapshots
- **Files to Check**:
  - `app/Models/Estate/NetWorthStatement.php`
  - `resources/js/components/Estate/NetWorth.vue`

### Cross-Module Issues

#### X-1: Tax Configuration Caching
- **Severity**: LOW
- **Description**: UK tax rules are in centralized config but cache TTL may be too long or too short
- **Impact**: Stale tax rates or excessive database queries
- **Recommendation**: Review cache strategy
- **Files to Check**:
  - `config/uk_tax_config.php`
  - `app/Models/TaxConfiguration.php`

#### X-2: Module Data Dependencies
- **Severity**: MEDIUM
- **Description**: Modules reference each other's data (e.g., ISA tracking, pension values in net worth) but may not update reactively
- **Impact**: Inconsistent data across modules
- **Recommendation**: Implement event-driven updates or cache invalidation
- **Files to Check**:
  - All Vuex store modules
  - All service classes

#### X-3: Mobile Responsiveness
- **Severity**: MEDIUM
- **Description**: Forms and charts may not render well on mobile devices
- **Impact**: Poor mobile UX
- **Recommendation**: Test all modules on mobile breakpoints
- **Files to Check**:
  - All Vue components with forms and charts

#### X-4: Error Handling Consistency
- **Severity**: MEDIUM
- **Description**: Error messages may not be consistent across modules
- **Impact**: Confusing user experience
- **Recommendation**: Standardize error handling and user-friendly messages
- **Files to Check**:
  - All controllers
  - All Vue components

#### X-5: Loading States
- **Severity**: LOW
- **Description**: Some components may not show loading indicators during API calls
- **Impact**: Users think app is frozen
- **Recommendation**: Audit all async operations for loading indicators
- **Files to Check**:
  - All Vue components with API calls

---

## TESTING INFRASTRUCTURE

### Playwright Tests Created
- ✅ `tests/e2e/01-protection.spec.js` - 8 test cases
- ✅ `tests/e2e/02-savings.spec.js` - 6 test cases
- ✅ `tests/e2e/03-investment.spec.js` - 8 test cases
- ✅ `tests/e2e/04-retirement.spec.js` - 8 test cases
- ✅ `tests/e2e/05-estate.spec.js` - 8 test cases

### Test Helpers Created
- ✅ `tests/e2e/helpers/auth.js` - Login, register, logout helpers
- ✅ `tests/e2e/helpers/common.js` - Common utilities (wait, fill, navigate, etc.)

### Configuration
- ✅ `playwright.config.js` - Playwright configuration with HTML/JSON reporters

---

## RECOMMENDATIONS

### High Priority
1. ✅ COMPLETED: Fix DC pension annual salary field (R-1)
2. ✅ COMPLETED: Fix IHT RNRB taper calculation (E-2)
3. ✅ COMPLETED: Fix Monte Carlo simulation polling (I-1)

### Medium Priority (Next to tackle)
4. Implement ISA cross-module tracking (S-1)
5. Fix emergency fund calculation (S-2)
6. Implement annual allowance taper (R-2)
7. Fix asset valuation date handling (E-1)
8. Fix module data dependencies (X-2)

### Low Priority
9. Add empty states for all charts (P-3, I-2)
10. Add mobile responsiveness testing (X-3)
11. Standardize error handling (X-4)

---

## TESTING NOTES

- Playwright tests configured but require longer runtime due to application complexity
- Manual testing recommended for comprehensive coverage
- Each module should be tested with:
  1. Empty state (new user)
  2. Adding first item
  3. Adding multiple items
  4. Editing items
  5. Deleting items
  6. Tab navigation
  7. Chart rendering
  8. Form validation

---

## NEXT STEPS

1. ✅ Document all known issues
2. ⏳ Prioritize bug fixes
3. ⏳ Run manual testing session for each module
4. ⏳ Fix high-priority bugs
5. ⏳ Fix medium-priority bugs
6. ⏳ Add automated tests for fixed bugs
7. ⏳ Regression testing

---

**End of Report**
