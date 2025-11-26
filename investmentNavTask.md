# Investment Detail Navigation - Task List

## Project Status Tracker

**Start Date:** November 26, 2025
**Completion Date:** November 26, 2025
**Status:** COMPLETE
**Total Tasks:** 89
**Completed:** 89

---

## Phase 1: InvestmentDashboard.vue State Management

### 1.1 Add State Properties
- [ ] **Task 1.1.1:** Add `selectedAccount: null` data property
- [ ] **Task 1.1.2:** Add `detailTab: 'overview'` data property for detail view tab tracking

### 1.2 Create Tab Configurations
- [ ] **Task 1.2.1:** Rename existing `tabs` array to `overviewTabs`
- [ ] **Task 1.2.2:** Update `overviewTabs` to remove Accounts and Contributions tabs:
  - `{ id: 'overview', label: 'Portfolio Overview' }`
  - `{ id: 'holdings', label: 'Holdings' }`
  - `{ id: 'performance', label: 'Performance' }`
  - `{ id: 'optimization', label: 'Portfolio Optimisation' }`
  - `{ id: 'rebalancing', label: 'Rebalancing' }`
  - `{ id: 'goals', label: 'Goals' }`
  - `{ id: 'taxefficiency', label: 'Tax Efficiency' }`
  - `{ id: 'fees', label: 'Fees' }`
  - `{ id: 'recommendations', label: 'Strategy' }`
- [ ] **Task 1.2.3:** Create `detailTabs` array with entries:
  - `{ id: 'portfolio-overview', label: 'Portfolio Overview' }`
  - `{ id: 'overview', label: 'Overview' }`
  - `{ id: 'holdings', label: 'Holdings' }`
  - `{ id: 'performance', label: 'Performance' }`
  - `{ id: 'fees', label: 'Fees' }`

### 1.3 Add Computed Properties
- [ ] **Task 1.3.1:** Create `currentTabs` computed property that returns `detailTabs` when `selectedAccount` exists, otherwise `overviewTabs`
- [ ] **Task 1.3.2:** Create `isDetailMode` computed property (`return !!this.selectedAccount`)

### 1.4 Add Methods
- [ ] **Task 1.4.1:** Create `selectAccount(account)` method
  - Sets `selectedAccount` to account object
  - Sets `activeTab` to 'overview'
- [ ] **Task 1.4.2:** Create `clearSelection()` method
  - Resets `selectedAccount` to null
  - Resets `activeTab` to 'overview'
- [ ] **Task 1.4.3:** Update tab click handler to call `clearSelection()` when 'portfolio-overview' tab clicked in detail mode

### 1.5 Update Template
- [ ] **Task 1.5.1:** Remove breadcrumb navigation section
- [ ] **Task 1.5.2:** Update tab v-for to use `currentTabs` instead of `tabs`
- [ ] **Task 1.5.3:** Add conditional rendering for overview vs detail mode in content area
- [ ] **Task 1.5.4:** Pass `@select-account="selectAccount"` to PortfolioOverview component
- [ ] **Task 1.5.5:** Remove Accounts tab component rendering
- [ ] **Task 1.5.6:** Remove Contributions tab component rendering
- [ ] **Task 1.5.7:** Keep Tax Efficiency tab component rendering (will have Coming Soon watermark)

---

## Phase 2: Modify PortfolioOverview.vue

### 2.1 Add Event Emission
- [ ] **Task 2.1.1:** Add `emits: ['select-account']` to component definition
- [ ] **Task 2.1.2:** Update `viewAccount()` method to emit: `$emit('select-account', account)`
  - Find account object by ID from accounts array
  - Emit the full account object, not just ID

### 2.2 Update Card Click Handler
- [ ] **Task 2.2.1:** Ensure card click calls `viewAccount(account)` with full account object
- [ ] **Task 2.2.2:** Remove any direct `$parent.activeTab` manipulation

---

## Phase 3: Create AccountDetailView.vue Container

### 3.1 Component Setup
- [ ] **Task 3.1.1:** Create `resources/js/views/Investment/AccountDetailView.vue`
- [ ] **Task 3.1.2:** Define props: `account` (Object), `activeTab` (String)
- [ ] **Task 3.1.3:** Import all panel components

### 3.2 Template Structure
- [ ] **Task 3.2.1:** Create container div with appropriate styling
- [ ] **Task 3.2.2:** Add account header section showing:
  - Ownership badge (Individual/Joint/Trust)
  - Account type badge (ISA/GIA/SIPP etc.)
  - Provider name
  - Account name
  - Current value
- [ ] **Task 3.2.3:** Add conditional rendering for each panel based on `activeTab`
- [ ] **Task 3.2.4:** Add Vue transitions for smooth panel switching

---

## Phase 4: Create AccountSummaryPanel.vue

### 4.1 Component Setup
- [ ] **Task 4.1.1:** Create `resources/js/views/Investment/AccountSummaryPanel.vue`
- [ ] **Task 4.1.2:** Define props: `account` (Object)

### 4.2 Summary Cards
- [ ] **Task 4.2.1:** Display current value (with full value for joint accounts)
- [ ] **Task 4.2.2:** Display provider and platform
- [ ] **Task 4.2.3:** Display account type with badge
- [ ] **Task 4.2.4:** Display YTD return if available
- [ ] **Task 4.2.5:** Display holdings count
- [ ] **Task 4.2.6:** Display platform fee percentage

### 4.3 ISA-Specific Information
- [ ] **Task 4.3.1:** If ISA account, display ISA contributions YTD
- [ ] **Task 4.3.2:** Display ISA allowance remaining with colour coding
- [ ] **Task 4.3.3:** Display tax year

### 4.4 Joint Account Information
- [ ] **Task 4.4.1:** If joint account, display full value (×2)
- [ ] **Task 4.4.2:** Display user's share (50%)
- [ ] **Task 4.4.3:** Display joint owner name if available

### 4.5 Asset Allocation
- [ ] **Task 4.5.1:** Display primary asset class with percentage
- [ ] **Task 4.5.2:** Display asset allocation breakdown for this account

### 4.6 Styling
- [ ] **Task 4.6.1:** Create summary cards with consistent FPS styling
- [ ] **Task 4.6.2:** Add formatCurrency helper for monetary values
- [ ] **Task 4.6.3:** Ensure responsive layout

---

## Phase 5: Create AccountHoldingsPanel.vue

### 5.1 Component Setup
- [ ] **Task 5.1.1:** Create `resources/js/views/Investment/AccountHoldingsPanel.vue`
- [ ] **Task 5.1.2:** Define props: `account` (Object)

### 5.2 Holdings Display
- [ ] **Task 5.2.1:** Display holdings filtered to this account only
- [ ] **Task 5.2.2:** Show holding name, ticker, ISIN
- [ ] **Task 5.2.3:** Show units and unit cost
- [ ] **Task 5.2.4:** Show current value
- [ ] **Task 5.2.5:** Show asset type with badge
- [ ] **Task 5.2.6:** Calculate and display allocation percentage

### 5.3 Add Holding Functionality
- [ ] **Task 5.3.1:** Add "Add Holding" button
- [ ] **Task 5.3.2:** Open HoldingForm modal with account pre-selected
- [ ] **Task 5.3.3:** Handle holding save/close events

### 5.4 Empty State
- [ ] **Task 5.4.1:** Show empty state message when no holdings
- [ ] **Task 5.4.2:** Include "Add First Holding" button

---

## Phase 6: Create AccountPerformancePanel.vue

### 6.1 Component Setup
- [ ] **Task 6.1.1:** Create `resources/js/views/Investment/AccountPerformancePanel.vue`
- [ ] **Task 6.1.2:** Define props: `account` (Object)

### 6.2 Coming Soon Watermark
- [ ] **Task 6.2.1:** Add Coming Soon watermark with opacity-50
- [ ] **Task 6.2.2:** Add placeholder content showing:
  - YTD Return card
  - Performance chart placeholder
  - Benchmark comparison placeholder

---

## Phase 7: Create AccountFeesPanel.vue

### 7.1 Component Setup
- [ ] **Task 7.1.1:** Create `resources/js/views/Investment/AccountFeesPanel.vue`
- [ ] **Task 7.1.2:** Define props: `account` (Object)

### 7.2 Coming Soon Watermark
- [ ] **Task 7.2.1:** Add Coming Soon watermark with opacity-50
- [ ] **Task 7.2.2:** Add placeholder content showing:
  - Platform fee display
  - Fund fees placeholder
  - Total cost calculation placeholder

---

## Phase 8: Add Coming Soon Watermarks to Overview Tabs

### 8.1 Performance.vue
- [ ] **Task 8.1.1:** Add Coming Soon watermark to Performance component
- [ ] **Task 8.1.2:** Reduce content opacity to 50%

### 8.2 PortfolioOptimization.vue
- [ ] **Task 8.2.1:** Add Coming Soon watermark to Portfolio Optimisation component
- [ ] **Task 8.2.2:** Reduce content opacity to 50%

### 8.3 RebalancingCalculator.vue
- [ ] **Task 8.3.1:** Add Coming Soon watermark to Rebalancing component
- [ ] **Task 8.3.2:** Reduce content opacity to 50%

### 8.4 Goals.vue
- [ ] **Task 8.4.1:** Add Coming Soon watermark to Goals component
- [ ] **Task 8.4.2:** Reduce content opacity to 50%

### 8.5 Tax Efficiency (AssetLocationOptimizer.vue & WrapperOptimizer.vue)
- [ ] **Task 8.5.1:** Add Coming Soon watermark to AssetLocationOptimizer component
- [ ] **Task 8.5.2:** Add Coming Soon watermark to WrapperOptimizer component
- [ ] **Task 8.5.3:** Reduce content opacity to 50% for both components

### 8.6 FeeBreakdown.vue
- [ ] **Task 8.6.1:** Add Coming Soon watermark to Fees tab
- [ ] **Task 8.6.2:** Reduce content opacity to 50%

### 8.7 Recommendations.vue (Strategy)
- [ ] **Task 8.7.1:** Add Coming Soon watermark to Strategy component
- [ ] **Task 8.7.2:** Reduce content opacity to 50%

---

## Phase 9: Integration and Testing

### 9.1 Import Components
- [ ] **Task 9.1.1:** Import AccountDetailView in InvestmentDashboard.vue
- [ ] **Task 9.1.2:** Register all new components
- [ ] **Task 9.1.3:** Add component map entries for detail tabs

### 9.2 Testing - Account Types
- [ ] **Task 9.2.1:** Test clicking ISA account card from overview
- [ ] **Task 9.2.2:** Test clicking GIA account card
- [ ] **Task 9.2.3:** Test clicking SIPP account card
- [ ] **Task 9.2.4:** Test clicking joint account (verify full value display)
- [ ] **Task 9.2.5:** Test all detail tabs render correctly

### 9.3 Testing - Navigation
- [ ] **Task 9.3.1:** Test "Portfolio Overview" tab returns to overview mode
- [ ] **Task 9.3.2:** Test tab switching in detail mode
- [ ] **Task 9.3.3:** Test top-level Net Worth navigation remains visible

### 9.4 Testing - Holdings
- [ ] **Task 9.4.1:** Test holdings display for account with holdings
- [ ] **Task 9.4.2:** Test empty state for account without holdings
- [ ] **Task 9.4.3:** Test add holding functionality

### 9.5 Testing - Coming Soon
- [ ] **Task 9.5.1:** Verify Coming Soon watermark displays on Performance tab
- [ ] **Task 9.5.2:** Verify Coming Soon watermark displays on Optimisation tab
- [ ] **Task 9.5.3:** Verify Coming Soon watermark displays on Rebalancing tab
- [ ] **Task 9.5.4:** Verify Coming Soon watermark displays on Goals tab
- [ ] **Task 9.5.5:** Verify Coming Soon watermark displays on Tax Efficiency tab
- [ ] **Task 9.5.6:** Verify Coming Soon watermark displays on Fees tab
- [ ] **Task 9.5.7:** Verify Coming Soon watermark displays on Strategy tab

---

## Phase 10: Documentation and Cleanup

### 10.1 Update Documentation
- [ ] **Task 10.1.1:** Update Nov26Patch.md with all changes
- [ ] **Task 10.1.2:** Document new component structure
- [ ] **Task 10.1.3:** Add usage notes for future development

### 10.2 Code Cleanup
- [ ] **Task 10.2.1:** Remove unused Accounts.vue import if not used elsewhere
- [ ] **Task 10.2.2:** Remove unused ContributionPlanner.vue import if not used elsewhere
- [ ] **Task 10.2.3:** Verify no console errors or warnings

### 10.3 Final Review
- [ ] **Task 10.3.1:** Review all new components follow FPS coding standards
- [ ] **Task 10.3.2:** Verify British spelling in user-facing text (Optimisation, not Optimization)
- [ ] **Task 10.3.3:** Ensure consistent formatCurrency usage
- [ ] **Task 10.3.4:** Mark investmentNavTask.md as complete

---

## Progress Summary

| Phase | Description | Tasks | Completed |
|-------|-------------|-------|-----------|
| 1 | InvestmentDashboard State Management | 17 | 0 |
| 2 | Modify PortfolioOverview | 4 | 0 |
| 3 | AccountDetailView Container | 4 | 0 |
| 4 | AccountSummaryPanel | 14 | 0 |
| 5 | AccountHoldingsPanel | 9 | 0 |
| 6 | AccountPerformancePanel | 3 | 0 |
| 7 | AccountFeesPanel | 3 | 0 |
| 8 | Coming Soon Watermarks | 15 | 0 |
| 9 | Integration and Testing | 16 | 0 |
| 10 | Documentation and Cleanup | 7 | 0 |
| **TOTAL** | | **89** | **0** |

---

## Notes

- All monetary values must use `formatCurrency()` method per CLAUDE.md standards
- Use British spelling for user-facing text (e.g., "Optimisation" not "Optimization")
- Maintain consistent card styling with existing FPS components
- Coming Soon watermarks use opacity-50 pattern from PensionAnalysisPanel.vue
- No backend changes required - all frontend component state management
- Keep Holdings.vue as a separate overview tab (shows all holdings across all accounts)
- Account detail Holdings panel shows only holdings for the selected account

---

## Account Types Reference

```javascript
const accountTypes = {
  'isa': 'ISA',
  'sipp': 'SIPP',
  'gia': 'GIA',
  'pension': 'Pension',
  'nsi': 'NS&I',
  'onshore_bond': 'Onshore Bond',
  'offshore_bond': 'Offshore Bond',
  'vct': 'VCT',
  'eis': 'EIS',
  'other': 'Other',
};
```

## Ownership Types Reference

```javascript
const ownershipTypes = {
  'individual': 'Individual',
  'joint': 'Joint',
  'trust': 'Trust',
};
```
