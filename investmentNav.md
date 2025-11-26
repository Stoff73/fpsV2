# Investment Detail Navigation - Component State Approach

## Overview

Implement investment account detail navigation using component state management within InvestmentDashboard.vue. This approach keeps the URL constant at `/net-worth/investments` while managing account selection and tab navigation through Vue component state - identical to the Retirement module pattern.

## Current State

**Current Tabs (11):**
1. Portfolio Overview
2. Accounts
3. Holdings
4. Performance
5. Contributions
6. Portfolio Optimisation
7. Rebalancing
8. Goals
9. Tax Efficiency
10. Fees
11. Strategy

**Current Behaviour:** Clicking an account card navigates to Accounts tab (not a detail view).

## Target State

### Overview Mode Tabs (9 tabs):
1. **Portfolio Overview** - Summary with account cards (clicking card enters detail mode)
2. **Holdings** - All holdings across all accounts
3. **Performance** - Coming Soon watermark
4. **Portfolio Optimisation** - Coming Soon watermark
5. **Rebalancing** - Coming Soon watermark
6. **Goals** - Coming Soon watermark
7. **Tax Efficiency** - Coming Soon watermark
8. **Fees** - Coming Soon watermark
9. **Strategy** - Coming Soon watermark

**Removed from Overview:**
- Accounts tab (merged into Portfolio Overview - cards are clickable)
- Contributions tab (removed entirely)

### Detail Mode Tabs (5 tabs):
1. **Portfolio Overview** - Returns to overview mode (like "All Pensions")
2. **Overview** - Selected account summary
3. **Holdings** - Holdings for this specific account
4. **Performance** - Coming Soon watermark
5. **Fees** - Fee breakdown for this account - Coming Soon watermark

---

## Implementation Plan

### Phase 1: Modify InvestmentDashboard.vue State Management

**File:** `resources/js/views/Investment/InvestmentDashboard.vue`

**Changes:**
1. Add new data properties:
   - `selectedAccount: null` - The selected account object
   - `detailTab: 'overview'` - Current detail view tab

2. Create two tab configurations:
   - `overviewTabs` - Simplified tabs for overview mode
   - `detailTabs` - Tabs for account detail view:
     - "Portfolio Overview" (returns to overview)
     - "Overview" (account summary)
     - "Holdings" (account-specific holdings)
     - "Performance" (Coming Soon)
     - "Fees" (Coming Soon)

3. Computed property `currentTabs` switches between tab sets based on `selectedAccount`

4. Add methods:
   - `selectAccount(account)` - Called when card clicked
   - `clearSelection()` - Returns to overview
   - `handleTabClick(tabId)` - Handles tab navigation

5. Remove breadcrumb navigation (as per earlier changes)

6. Remove these tabs entirely:
   - Accounts (cards are in Portfolio Overview)
   - Contributions

### Phase 2: Modify PortfolioOverview.vue

**File:** `resources/js/components/Investment/PortfolioOverview.vue`

**Changes:**
1. Add `emits: ['select-account']` to component
2. Change `viewAccount()` method to emit: `$emit('select-account', account)`
3. Keep all existing functionality (summary cards, accounts grid, risk metrics)

### Phase 3: Create Account Detail Components

**New Components in `resources/js/views/Investment/`:**

1. **AccountDetailView.vue** - Container component for account detail mode
   - Props: `account`, `activeTab`
   - Renders appropriate panel based on activeTab
   - Shows account header with type badge, provider, value

2. **AccountSummaryPanel.vue** - Summary view of selected account
   - Current value (with joint owner display if applicable)
   - Account type and provider
   - ISA contributions if ISA account
   - YTD return
   - Holdings count
   - Platform fees
   - Primary asset allocation

3. **AccountHoldingsPanel.vue** - Holdings for this specific account
   - Holdings table (reuse existing HoldingsTable logic)
   - Add holding button
   - Asset allocation breakdown for this account
   - Filter by asset type

4. **AccountPerformancePanel.vue** - Performance for this account
   - Coming Soon watermark
   - Placeholder for future: YTD return, historical performance chart

5. **AccountFeesPanel.vue** - Fee breakdown for this account
   - Coming Soon watermark
   - Placeholder for future: Platform fees, fund fees, total cost

### Phase 4: Add Coming Soon Watermarks

**Files to modify:**

1. `Performance.vue` - Add Coming Soon watermark
2. `PortfolioOptimization.vue` - Add Coming Soon watermark
3. `RebalancingCalculator.vue` - Add Coming Soon watermark
4. `Goals.vue` - Add Coming Soon watermark
5. `AssetLocationOptimizer.vue` / `WrapperOptimizer.vue` (Tax Efficiency) - Add Coming Soon watermark
6. `FeeBreakdown.vue` / Fees tab - Add Coming Soon watermark
7. `Recommendations.vue` (Strategy) - Add Coming Soon watermark

### Phase 5: Wire Up Components

**In InvestmentDashboard.vue:**
1. Import all new components
2. Update template to conditionally render:
   - Overview mode components when no account selected
   - AccountDetailView when account selected
3. Pass selectedAccount and activeTab to AccountDetailView
4. Handle tab bar to show appropriate tabs based on mode

---

## Component Hierarchy (After Implementation)

```
NetWorthDashboard.vue
  └── [Top Nav: Overview | Retirement | Property | Investments | ...]
      └── InvestmentDashboard.vue
          ├── [Tab Nav - Overview Mode]: Portfolio Overview | Holdings | Performance* | Optimisation* | Rebalancing* | Goals* | Tax Efficiency* | Fees* | Strategy*
          │   └── PortfolioOverview.vue (shows account cards - clickable)
          │   └── Holdings.vue (all holdings)
          │   └── Performance.vue* (Coming Soon)
          │   └── etc.
          │
          └── [Tab Nav - Detail Mode]: Portfolio Overview | Overview | Holdings | Performance* | Fees*
              └── AccountDetailView.vue
                  ├── AccountSummaryPanel.vue
                  ├── AccountHoldingsPanel.vue
                  ├── AccountPerformancePanel.vue* (Coming Soon)
                  └── AccountFeesPanel.vue* (Coming Soon)

* = Coming Soon watermark
```

---

## Benefits of This Approach

1. **Consistency** - Matches Retirement module navigation pattern exactly
2. **URL Stability** - URL stays at `/net-worth/investments`, no route changes
3. **Instant Transitions** - No page reloads, smooth Vue transitions
4. **Preserved Context** - Top-level navigation always visible
5. **Simple Implementation** - No router configuration changes needed
6. **Easy Back Navigation** - "Portfolio Overview" tab returns to overview instantly
7. **Maintainable** - All logic contained within Investment module

---

## Files to Create

1. `resources/js/views/Investment/AccountDetailView.vue`
2. `resources/js/views/Investment/AccountSummaryPanel.vue`
3. `resources/js/views/Investment/AccountHoldingsPanel.vue`
4. `resources/js/views/Investment/AccountPerformancePanel.vue`
5. `resources/js/views/Investment/AccountFeesPanel.vue`

## Files to Modify

1. `resources/js/views/Investment/InvestmentDashboard.vue` - State management, tab switching, remove breadcrumb
2. `resources/js/components/Investment/PortfolioOverview.vue` - Emit select-account event
3. `resources/js/components/Investment/Performance.vue` - Add Coming Soon watermark
4. `resources/js/components/Investment/PortfolioOptimization.vue` - Add Coming Soon watermark
5. `resources/js/components/Investment/RebalancingCalculator.vue` - Add Coming Soon watermark
6. `resources/js/components/Investment/Goals.vue` - Add Coming Soon watermark
7. `resources/js/components/Investment/AssetLocationOptimizer.vue` - Add Coming Soon watermark
8. `resources/js/components/Investment/WrapperOptimizer.vue` - Add Coming Soon watermark
9. `resources/js/components/Investment/FeeBreakdown.vue` - Add Coming Soon watermark
10. `resources/js/components/Investment/Recommendations.vue` - Add Coming Soon watermark

---

## Tab Configuration Reference

### Overview Mode Tabs
```javascript
overviewTabs: [
  { id: 'overview', label: 'Portfolio Overview' },
  { id: 'holdings', label: 'Holdings' },
  { id: 'performance', label: 'Performance' },
  { id: 'optimization', label: 'Portfolio Optimisation' },
  { id: 'rebalancing', label: 'Rebalancing' },
  { id: 'goals', label: 'Goals' },
  { id: 'taxefficiency', label: 'Tax Efficiency' },
  { id: 'fees', label: 'Fees' },
  { id: 'recommendations', label: 'Strategy' },
]
```

### Detail Mode Tabs
```javascript
detailTabs: [
  { id: 'portfolio-overview', label: 'Portfolio Overview' },
  { id: 'overview', label: 'Overview' },
  { id: 'holdings', label: 'Holdings' },
  { id: 'performance', label: 'Performance' },
  { id: 'fees', label: 'Fees' },
]
```

---

## Implementation Order

1. Modify InvestmentDashboard.vue with state management and dual tab system
2. Modify PortfolioOverview.vue to emit events instead of changing tabs
3. Create AccountDetailView.vue container
4. Create AccountSummaryPanel.vue (initial landing view)
5. Create AccountHoldingsPanel.vue
6. Create AccountPerformancePanel.vue (Coming Soon)
7. Create AccountFeesPanel.vue (Coming Soon)
8. Add Coming Soon watermarks to overview tabs (Performance, Optimisation, Rebalancing, Goals, Fees, Strategy)
9. Test all account types (ISA, GIA, SIPP, etc.)
10. Update Nov26Patch.md with changes

---

## Coming Soon Watermark Pattern

Use the same pattern as PensionAnalysisPanel.vue:

```vue
<div class="relative">
  <!-- Coming Soon Watermark -->
  <div class="absolute inset-0 flex items-center justify-center z-10 pointer-events-none">
    <div class="text-6xl font-bold text-gray-300 opacity-50 transform -rotate-12">
      Coming Soon
    </div>
  </div>

  <!-- Actual content with reduced opacity -->
  <div class="opacity-50">
    <!-- Component content here -->
  </div>
</div>
```

---

## Estimated Scope

- 5 new Vue components
- 10 modified Vue components
- No backend changes required
- No router configuration changes
