# November 10, 2025 - Bug Fixes and UI Improvements

## Overview

This document details all bug fixes and UI improvements completed on November 10, 2025, culminating in the merge of `feature/investment-financial-planning` into `main`.

**Commit Range**: 135b7f6 → f94fa73 (merge commit)
**Total Changes**: 11 distinct fixes across Estate, Investment, Protection, and Net Worth modules
**Status**: ✅ All fixes deployed to main branch

---

## 1. Estate Plan - API Field Naming Fix

**Commit**: 135b7f6

**Issue**: ComprehensiveEstatePlan.vue crashed with error: `Cannot read properties of undefined (reading 'strategy_name')`

**Root Cause**: Frontend using British spelling `plan.optimised_recommendation` but backend API returns American spelling `plan.optimized_recommendation`

**Files Modified**:
- `resources/js/views/Estate/ComprehensiveEstatePlan.vue` (lines 499-765)

**Changes**:
- Replaced all 6 occurrences of `plan.optimised_recommendation` with `plan.optimized_recommendation`
- Applied to strategy name, description, benefits, considerations, implementation steps, and priority

**Resolution**: Complete - Estate Plan document now displays correctly

---

## 2. Investment Plan - Polymorphic Relationship Fix

**Commit**: 476dad8

**Issue**: 500 error when generating Investment & Savings Plan: `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'investment_account_id'`

**Root Cause**: Holdings table uses polymorphic relationships (`holdable_type`, `holdable_id`), not direct foreign key `investment_account_id`

**Files Modified**:
- `app/Models/Investment/Holding.php` (lines 64-73) - Added accessor for backward compatibility
- `app/Services/Plans/InvestmentSavingsPlanService.php` (lines 36-42, 145-146) - Fixed queries
- `app/Http/Controllers/Api/Investment/InvestmentController.php` (lines 88-92, 111-115) - Fixed queries

**Changes**:
```php
// Added to Holding.php
public function getInvestmentAccountIdAttribute(): ?int
{
    return $this->holdable_type === InvestmentAccount::class
        ? $this->holdable_id
        : null;
}

// Fixed queries in InvestmentSavingsPlanService.php
$holdings = Holding::where('holdable_type', InvestmentAccount::class)
    ->whereIn('holdable_id', $accountIds)
    ->get();
```

**User Feedback**: "okay, thanks this is working now"

**Resolution**: Complete - Investment & Savings Plan generates successfully

---

## 3. Property Type Standardisation - ONE SOURCE OF TRUTH Enforcement

**Commit**: 70a2d4f

**Issue**: I inadvertently added `second_home` fallback code to PropertyCard.vue, violating the ONE SOURCE OF TRUTH principle

**User Feedback**: "WHY THE FUCK are you still using TWO?"

**Lesson Learned**: NO fallbacks or backward compatibility for canonical types. Database migration defines single source of truth.

**Files Modified**:
- `resources/js/components/NetWorth/PropertyCard.vue` (lines 145-153, 281-289)

**Changes**:
1. Removed `second_home` from propertyTypeLabel mapping
2. Removed `second_home` from CSS badge colour classes
3. Executed database cleanup: `Property::where('property_type', 'second_home')->update(['property_type' => 'secondary_residence'])`
4. Verified 0 properties remain with old `second_home` value

**Canonical Property Types** (ONLY THREE ALLOWED):
- `main_residence` → "Main Residence"
- `secondary_residence` → "Secondary Residence"
- `buy_to_let` → "Buy to Let"

**Resolution**: Complete - All properties now use canonical values, no fallbacks exist

---

## 4. Net Worth UI Improvements

**Commit**: 70a2d4f (same commit as above)

**Changes Made**:

### 4.1 Retirement Tab - "Recommendations" → "Strategies"
- **File**: `resources/js/views/NetWorth/RetirementView.vue` (line 95)
- **Change**: Updated tab label from "Recommendations" to "Strategies"

### 4.2 Investment Tab - Removed "What-If Scenarios" Tab
- **File**: `resources/js/views/Investment/InvestmentDashboard.vue`
- **Changes**:
  - Removed tab from tabs array (lines 161-162)
  - Removed component import (line 176)
  - Removed component registration (line 199)
  - Removed v-else-if block from template (line 233)

### 4.3 Cash Tab - "Recommendations" → "Strategy"
- **File**: `resources/js/views/Savings/SavingsDashboard.vue` (line 148)
- **Change**: Updated tab label from "Recommendations" to "Strategy"

**Resolution**: Complete - All UI improvements applied

---

## 5. Estate Life Policy Strategy - Missing Response Structure

**Commit**: 74b2325

**Issue**: 500 error when accessing Life Policy Strategy tab: "Second death analysis not available"

**Root Cause**: `IHTController.calculateSecondDeathIHTPlanning()` returned inconsistent response structure when spouse data unavailable - missing `second_death_analysis` key

**Files Modified**:
- `app/Http/Controllers/Api/Estate/IHTController.php` (lines 481-550)

**Changes**:
Added complete `second_death_analysis` structure when spouse not linked or missing DOB/gender:
```php
$secondDeathAnalysis = [
    'success' => true,
    'second_death' => [
        'years_until_death' => $yearsUntilDeath,
        'estimated_age_at_death' => $estimatedAgeAtDeath,
        'is_user' => true,
        'name' => $user->first_name . ' ' . $user->last_name,
        'projected_combined_estate_at_second_death' => $projectedNetEstate,
    ],
    'first_death' => ['name' => 'Spouse'],
    'current_combined_totals' => [...],
    'current_iht_calculation' => [...],
    'iht_calculation' => [...],
];
```

**User Feedback**: "okay, this now works"

**Resolution**: Complete - Life Policy Strategy tab now loads with consistent data structure

---

## 6. Estate Trust Strategy - Missing Method Parameters

**Commit**: 59125a2

**Issue**: 500 error when accessing Trust Planning tab: `Undefined array key 'types'`

**Root Cause**: `TrustController.getTrustRecommendations()` called `calculateIHTLiability()` with only 3 parameters (assets, profile, gifts) but method signature requires 7

**Files Modified**:
- `app/Http/Controllers/Api/Estate/TrustController.php` (lines 168-204)

**Method Signature**:
```php
public function calculateIHTLiability(
    Collection $assets,
    IHTProfile $ihtProfile,
    Collection $gifts,
    Collection $trusts,
    float $totalLiabilities,
    ?Will $will,
    User $user
): array
```

**Changes**:
Added missing data loading and passed all 7 required parameters:
```php
$trusts = Trust::where('user_id', $user->id)->where('is_active', true)->get();
$will = Will::where('user_id', $user->id)->first();
$totalLiabilities = $liabilities->sum('amount');

$ihtCalculation = $this->ihtCalculator->calculateIHTLiability(
    $assets,
    $ihtProfile,
    $gifts,
    $trusts,
    $totalLiabilities,
    $will,
    $user
);
```

**Resolution**: Complete - Trust Planning tab now loads successfully

---

## 7. Estate Overview Tab - Data Not Loading

**Commit**: 7082d84

**Issue**: After fixing trust strategy, Estate Overview tab displayed no data

**Root Cause**: Dashboard was calling `estate/calculateIHT` for ALL users, but married users need `estate/calculateSecondDeathIHTPlanning` to populate `secondDeathPlanning` state

**Files Modified**:
- `resources/js/views/Dashboard.vue` (lines 194-301)

**Changes**:
Added marital status check in three methods:

1. **loadAllData()** (lines 194-246)
2. **retryLoadModule()** (lines 248-274)
3. **refreshDashboard()** (lines 276-301)

```javascript
const user = this.$store.state.auth.user;
const isMarried = user && user.marital_status === 'married';
const estateCalculationAction = isMarried
  ? 'estate/calculateSecondDeathIHTPlanning'
  : 'estate/calculateIHT';
```

**Resolution**: Complete - Dashboard now calls appropriate IHT calculation based on marital status

---

## 8. Estate IHT Calculation - Incorrect Math

**Commit**: 4007bc9

**Issue**: User reported: "how is £7,853,398 minus £1,000,000 = £0?"

**Root Cause**: Using spouse exemption calculation (which results in £0 IHT for married couples on first death) instead of second death scenario calculation

**Files Modified**:
- `app/Http/Controllers/Api/Estate/IHTController.php` (lines 374-480)

**Changes**:

### Second Death Scenario Calculation Rules:
1. **Combined NRB**: £325,000 × 2 = £650,000 (both NRB allowances available)
2. **Combined RNRB**: £175,000 × 2 = £350,000 (if main residence owned and left to descendants)
3. **No Spouse Exemption**: Both spouses deceased, estate passes to beneficiaries
4. **Current IHT**: Calculate based on TODAY's estate value
5. **Projected IHT**: Calculate based on future estate value at second death

### Code Implementation:
```php
// For married couples, assume full NRB transferability
$totalNRB = $ihtConfig['nil_rate_band'] * 2; // £650,000

// RNRB calculation (if home owned and left to descendants)
$rnrb = 0;
$rnrbEligible = false;
if ($ihtCalculation['rnrb_eligible'] ?? false) {
    $rnrb = ($ihtConfig['rnrb'] ?? 175000) * 2; // £350,000
    $rnrbEligible = true;
}

// Current IHT calculation (second death - no spouse exemption)
$currentTaxableEstate = max(0, $currentNetEstate - $totalNRB - $rnrb);
$currentIHTLiability = $currentTaxableEstate * 0.40;

// Projected IHT calculation (second death - no spouse exemption)
$projectedTaxableEstate = max(0, $projectedNetEstate - $totalNRB - $rnrb);
$projectedIHTLiability = $projectedTaxableEstate * 0.40;
```

**Resolution**: Partial - Math corrected but dashboard still showing £0 (see next fix)

---

## 9. Dashboard Estate Values - Store Getter Path Issue

**Commit**: adbe699

**Issue**: After fixing IHT math, dashboard still displayed £0 for estate values

**Root Cause**: Store getters (`ihtLiability`, `taxableEstate`) were pulling from projected values in `second_death_analysis.iht_calculation` instead of current values in `second_death_analysis.current_iht_calculation`

**Files Modified**:
- `resources/js/store/modules/estate.js` (lines 46-57, 136-151)

**Changes**:

### Before (WRONG - using projected values):
```javascript
ihtLiability: (state) => {
    if (state.secondDeathPlanning?.second_death_analysis?.iht_calculation?.iht_liability !== undefined) {
        return state.secondDeathPlanning.second_death_analysis.iht_calculation.iht_liability;
    }
    // ...
}
```

### After (CORRECT - using current values):
```javascript
ihtLiability: (state) => {
    // For married users with second death analysis, use CURRENT (now) IHT liability
    if (state.secondDeathPlanning?.second_death_analysis?.current_iht_calculation?.iht_liability !== undefined) {
        return state.secondDeathPlanning.second_death_analysis.current_iht_calculation.iht_liability;
    }
    // For married users without linked spouse, use user_iht_calculation
    if (state.secondDeathPlanning?.user_iht_calculation?.iht_liability !== undefined) {
        return state.secondDeathPlanning.user_iht_calculation.iht_liability;
    }
    // Otherwise use standard analysis
    return state.analysis?.iht_liability || 0;
}
```

**User Feedback**: "perfect, well done"

**Resolution**: Complete - Dashboard now displays correct current IHT values

---

## 10. Protection Card Icon Update

**Commit**: ba30cd2

**Issue**: User requested better icon representation for Protection module card

**User Request**: "the icon for the Protection module card in the top right of the card, can we change this to a shield"

**Files Modified**:
- `resources/js/components/Protection/ProtectionOverviewCard.vue` (lines 8-23)

**Changes**:
Replaced document icon with shield-check icon:

### Before (document icon):
```vue
<path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
```

### After (shield-check icon):
```vue
<path
  stroke-linecap="round"
  stroke-linejoin="round"
  stroke-width="2"
  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
/>
```

**User Feedback**: "thanks, this works"

**Resolution**: Complete - Protection card now displays shield icon

---

## 11. Protection Policy "In Trust" Checkbox

**Commit**: 0027256

**Issue**: User wanted checkbox for "Is this policy in Trust" for life insurance policies

**Requirements**:
- Must work in BOTH onboarding and protection module (unified form principle)
- Only show for life insurance policies
- Include helpful text about IHT benefits

**Database Schema**: Column `in_trust` already existed in `life_insurance_policies` table
**Model**: Already in `$fillable` array
**Validation**: Already in `StoreLifeInsurancePolicyRequest`

**Files Modified**:
- `resources/js/components/Protection/PolicyFormModal.vue`

**Changes**:

### 1. Added Checkbox to Template (lines 184-200):
```vue
<!-- In Trust (for Life Insurance) -->
<div v-if="formData.policyType === 'life'">
  <div class="flex items-center">
    <input
      id="in_trust"
      v-model="formData.in_trust"
      type="checkbox"
      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
    />
    <label for="in_trust" class="ml-2 block text-sm font-medium text-gray-700">
      Is this policy in Trust?
    </label>
  </div>
  <p class="text-xs text-gray-500 mt-1 ml-6">
    Policies held in trust can help reduce inheritance tax liability
  </p>
</div>
```

### 2. Updated formData Initialisation (line 314-329):
```javascript
formData: {
  policyType: '',
  provider: '',
  policy_number: '',
  coverage_amount: 0,
  premium_amount: 0,
  premium_frequency: 'monthly',
  start_date: '',
  term_years: null,
  in_trust: false,  // Added
  // ... other fields
}
```

### 3. Updated loadPolicyData() for Editing (lines 419-436):
```javascript
loadPolicyData() {
  this.formData = {
    policyType: this.policy.policy_type,
    provider: this.policy.provider || '',
    policy_number: this.policy.policy_number || '',
    coverage_amount: this.policy.sum_assured || this.policy.benefit_amount || 0,
    premium_amount: this.policy.premium_amount || 0,
    premium_frequency: this.policy.premium_frequency || 'monthly',
    start_date: this.formatDateForInput(this.policy.start_date),
    term_years: this.policy.term_years || null,
    in_trust: this.policy.in_trust || false,  // Added
    // ... other fields
  };
}
```

### 4. Updated prepareData() to Use Actual Value (lines 462-468):
```javascript
if (type === 'life') {
  data.policy_type = 'term';
  data.sum_assured = this.formData.coverage_amount;
  data.policy_term_years = this.formData.term_years || 20;
  data.policy_start_date = this.formData.start_date || new Date().toISOString().split('T')[0];
  data.in_trust = this.formData.in_trust || false;  // Changed from hardcoded false
  data.beneficiaries = this.formData.notes || null;
}
```

**User Feedback**: "thanks, this works"

**Resolution**: Complete - Life insurance policies can now be marked as "in trust" in both onboarding and protection module

---

## Git Workflow

**Branch Merge**: feature/investment-financial-planning → main
**Merge Commit**: f94fa73
**Merge Date**: November 10, 2025

### Merge Process:
1. Verified all changes on feature branch (11 fixes above)
2. User confirmed: "yes, this looks correct, let's proceed"
3. Switched to main branch: `git checkout main`
4. Merged with no-ff strategy: `git merge feature/investment-financial-planning --no-ff`
5. Pushed to remote: `git push origin main`

### Merge Summary:
- **Files Changed**: 333 files
- **Commits Included**: 52 commits ahead of origin/main
- **Features Added**:
  - Estate Planning IHT calculations with second death scenario
  - Dashboard estate card shows current IHT values
  - Protection card shield icon
  - Life insurance 'in trust' checkbox
  - Fixed property type standardisation (secondary_residence)
  - Fixed IHT calculation math for married couples
  - Multiple bug fixes for estate and investment modules

**Status**: ✅ Successfully merged and pushed to production

---

## Key Lessons Learned

### 1. API Field Naming Convention
- **Rule**: All API endpoint names, methods, request/response field names use American spelling
- **User-Facing Text**: British spelling in UI
- **Code**: American spelling in variables, routes, API fields
- **Example**: API returns `optimized_recommendation`, UI displays "Optimised Recommendation"

### 2. ONE SOURCE OF TRUTH Principle
- **Rule**: Database migration defines canonical values - ZERO tolerance for fallbacks
- **Violation**: I added `second_home` fallback code
- **User Response**: "WHY THE FUCK are you still using TWO?"
- **Lesson**: NO backward compatibility for canonical types, ever
- **Action**: Immediately removed fallback, updated database, verified cleanup

### 3. Polymorphic Relationships
- **Pattern**: Holdings use `holdable_type` and `holdable_id` columns
- **Issue**: Cannot query by non-existent `investment_account_id` foreign key
- **Solution**: Query by polymorphic columns: `where('holdable_type', InvestmentAccount::class)->whereIn('holdable_id', $accountIds)`

### 4. Method Signature Consistency
- **Issue**: TrustController called calculateIHTLiability() with wrong number of parameters
- **Lesson**: Always check method signature before calling
- **Solution**: Load and pass all 7 required parameters (assets, profile, gifts, trusts, liabilities, will, user)

### 5. Response Structure Consistency
- **Issue**: IHTController returned different structures based on data availability
- **Lesson**: API endpoints must return consistent structures regardless of edge cases
- **Solution**: Always return complete structure with fallback values when data missing

### 6. Dashboard Data Flow for Married Users
- **Issue**: Dashboard called wrong IHT calculation for married users
- **Lesson**: Check marital status before determining which calculation to call
- **Solution**: `isMarried ? 'calculateSecondDeathIHTPlanning' : 'calculateIHT'`

### 7. Second Death IHT Calculation Rules
- **Combined NRB**: £325,000 × 2 = £650,000
- **Combined RNRB**: £175,000 × 2 = £350,000 (if eligible)
- **No Spouse Exemption**: Both deceased, estate passes to beneficiaries
- **Calculate Both**: Current IHT (now) and Projected IHT (future)

### 8. Vuex Store Getter Paths
- **Issue**: Getters pulling from wrong nested object
- **Lesson**: Dashboard displays "now" values, not projected future values
- **Solution**: Use `current_iht_calculation` instead of `iht_calculation` for dashboard display

### 9. Git Workflow Best Practices
- **Mistake**: I checked main branch first when changes were in feature branch
- **User Correction**: "why are you looking in main, there have been NO changes in main, the changes are all in the branch?"
- **Lesson**: Always verify which branch contains the work before merging
- **Process**: Review on feature branch → user approval → merge to main

---

## Testing Checklist

All features tested and verified working:

- ✅ Estate Plan document displays correctly
- ✅ Investment & Savings Plan generates successfully
- ✅ Property types use canonical values only
- ✅ Net Worth tab labels updated
- ✅ Investment What-If Scenarios tab removed
- ✅ Estate Life Policy Strategy tab loads
- ✅ Estate Trust Strategy tab loads
- ✅ Estate Overview tab displays data for married users
- ✅ Estate IHT calculation correct for second death scenario
- ✅ Dashboard estate card shows current (not projected) values
- ✅ Protection card displays shield icon
- ✅ Life insurance policies support "in trust" checkbox
- ✅ Feature branch successfully merged into main
- ✅ All commits pushed to remote repository

---

## Documentation Updates Required

### CLAUDE.md
- Section 6 "CANONICAL DATA TYPES" already documents property types correctly
- Section "Critical Vue.js Patterns" already covers form event naming
- Section "Recent Fixes (November 2025)" should be updated with today's work

### API Documentation
- Document `calculateSecondDeathIHTPlanning` endpoint response structure
- Document polymorphic Holdings relationship pattern
- Document marital status check requirement for IHT calculations

### Developer Guide
- Add section on second death IHT calculation rules
- Document store getter path conventions (current vs projected)
- Add git workflow best practices from today's lesson

---

## Version Information

**Version**: v0.2.1 (Beta)
**Date**: November 10, 2025
**Status**: 🚀 Active Development - Core Modules Complete

**Next Steps**:
1. Update CLAUDE.md "Recent Fixes" section with today's work
2. Monitor production for any edge cases with new IHT calculations
3. Consider adding unit tests for second death scenario calculations

---

🤖 **Built with [Claude Code](https://claude.com/claude-code)**
