# Pension Detail Navigation - Task List

## Project Status Tracker

**Start Date:** November 26, 2025
**Completion Date:** November 26, 2025
**Status:** COMPLETE
**Total Tasks:** 42
**Completed:** 42

---

## Phase 1: RetirementDashboard.vue State Management

### 1.1 Add State Properties
- [ ] **Task 1.1.1:** Add `selectedPension: null` data property
- [ ] **Task 1.1.2:** Add `selectedPensionType: null` data property ('dc', 'db', or 'state')
- [ ] **Task 1.1.3:** Add `detailTab: 'summary'` data property for detail view tab tracking

### 1.2 Create Tab Configurations
- [ ] **Task 1.2.1:** Rename existing `tabs` array to `overviewTabs`
- [ ] **Task 1.2.2:** Create `detailTabs` array with entries:
  - `{ id: 'all-pensions', name: 'All Pensions' }`
  - `{ id: 'summary', name: 'Summary' }`
  - `{ id: 'details', name: 'Details' }`
  - `{ id: 'contributions', name: 'Contributions' }`
  - `{ id: 'projections', name: 'Projections' }`
  - `{ id: 'analysis', name: 'Analysis' }`

### 1.3 Add Computed Properties
- [ ] **Task 1.3.1:** Create `currentTabs` computed property that returns `detailTabs` when `selectedPension` exists, otherwise `overviewTabs`
- [ ] **Task 1.3.2:** Create `isDetailMode` computed property (`return !!this.selectedPension`)
- [ ] **Task 1.3.3:** Update `currentTabComponent` to handle detail mode components

### 1.4 Add Methods
- [ ] **Task 1.4.1:** Create `selectPension(pension, type)` method
  - Sets `selectedPension` to pension object
  - Sets `selectedPensionType` to type
  - Sets `detailTab` to 'summary'
  - Sets `activeTab` to 'summary'
- [ ] **Task 1.4.2:** Create `clearSelection()` method
  - Resets `selectedPension` to null
  - Resets `selectedPensionType` to null
  - Resets `activeTab` to 'readiness'
- [ ] **Task 1.4.3:** Update tab click handler to call `clearSelection()` when 'all-pensions' tab clicked

### 1.5 Update Template
- [ ] **Task 1.5.1:** Update tab v-for to use `currentTabs` instead of `tabs`
- [ ] **Task 1.5.2:** Add conditional rendering for overview vs detail mode in content area
- [ ] **Task 1.5.3:** Pass `@select-pension="selectPension"` to RetirementReadiness component

---

## Phase 2: Modify RetirementReadiness.vue

### 2.1 Remove Router Navigation
- [ ] **Task 2.1.1:** Remove `router.push()` calls from pension card click handlers
- [ ] **Task 2.1.2:** Remove router import if no longer needed

### 2.2 Add Event Emission
- [ ] **Task 2.2.1:** Add `emits: ['select-pension']` to component definition
- [ ] **Task 2.2.2:** Update DC pension card click to emit: `$emit('select-pension', pension, 'dc')`
- [ ] **Task 2.2.3:** Update DB pension card click to emit: `$emit('select-pension', pension, 'db')`
- [ ] **Task 2.2.4:** Update State pension card click to emit: `$emit('select-pension', pension, 'state')`

### 2.3 Update Card Styling
- [ ] **Task 2.3.1:** Add hover cursor styling to indicate cards are clickable
- [ ] **Task 2.3.2:** Add hover effect (shadow increase) for better UX

---

## Phase 3: Create PensionDetailView.vue Container

### 3.1 Component Setup
- [ ] **Task 3.1.1:** Create `resources/js/views/Retirement/PensionDetailView.vue`
- [ ] **Task 3.1.2:** Define props: `pension` (Object), `pensionType` (String), `activeTab` (String)
- [ ] **Task 3.1.3:** Import all panel components

### 3.2 Template Structure
- [ ] **Task 3.2.1:** Create container div with appropriate styling
- [ ] **Task 3.2.2:** Add pension header section showing pension name/type
- [ ] **Task 3.2.3:** Add conditional rendering for each panel based on `activeTab`
- [ ] **Task 3.2.4:** Add Vue transitions for smooth panel switching

---

## Phase 4: Create PensionSummaryPanel.vue

### 4.1 Component Setup
- [ ] **Task 4.1.1:** Create `resources/js/views/Retirement/PensionSummaryPanel.vue`
- [ ] **Task 4.1.2:** Define props: `pension` (Object), `pensionType` (String)

### 4.2 DC Pension Summary
- [ ] **Task 4.2.1:** Display current fund value
- [ ] **Task 4.2.2:** Display monthly contributions (employee + employer)
- [ ] **Task 4.2.3:** Display retirement age
- [ ] **Task 4.2.4:** Display projected retirement value (basic calculation)
- [ ] **Task 4.2.5:** Display provider and scheme type

### 4.3 DB Pension Summary
- [ ] **Task 4.3.1:** Display current accrued value
- [ ] **Task 4.3.2:** Display projected annual pension
- [ ] **Task 4.3.3:** Display lump sum entitlement
- [ ] **Task 4.3.4:** Display retirement age
- [ ] **Task 4.3.5:** Display scheme type (Final Salary, Career Average, etc.)

### 4.4 State Pension Summary
- [ ] **Task 4.4.1:** Display current weekly amount
- [ ] **Task 4.4.2:** Display qualifying years
- [ ] **Task 4.4.3:** Display state pension age
- [ ] **Task 4.4.4:** Display projected annual state pension

### 4.5 Styling
- [ ] **Task 4.5.1:** Create summary cards with consistent FPS styling
- [ ] **Task 4.5.2:** Add formatCurrency helper for monetary values
- [ ] **Task 4.5.3:** Ensure responsive layout

---

## Phase 5: Create PensionDetailsPanel.vue

### 5.1 Component Setup
- [ ] **Task 5.1.1:** Create `resources/js/views/Retirement/PensionDetailsPanel.vue`
- [ ] **Task 5.1.2:** Define props: `pension` (Object), `pensionType` (String)

### 5.2 DC Pension Details
- [ ] **Task 5.2.1:** Display all pension fields (provider, scheme name, policy number, etc.)
- [ ] **Task 5.2.2:** Display holdings breakdown if available
- [ ] **Task 5.2.3:** Display contribution details (employee %, employer %, salary sacrifice)
- [ ] **Task 5.2.4:** Add edit button to open pension form modal

### 5.3 DB Pension Details
- [ ] **Task 5.3.1:** Display all pension fields (scheme name, employer, accrual rate, etc.)
- [ ] **Task 5.3.2:** Display benefit details (NPA, commutation options)
- [ ] **Task 5.3.3:** Display spouse benefits if applicable
- [ ] **Task 5.3.4:** Add edit button to open pension form modal

### 5.4 State Pension Details
- [ ] **Task 5.4.1:** Display NI record details
- [ ] **Task 5.4.2:** Display forecast details
- [ ] **Task 5.4.3:** Display deferral options
- [ ] **Task 5.4.4:** Add edit button to open pension form modal

---

## Phase 6: Create PensionContributionsPanel.vue

### 6.1 Component Setup
- [ ] **Task 6.1.1:** Create `resources/js/views/Retirement/PensionContributionsPanel.vue`
- [ ] **Task 6.1.2:** Define props: `pension` (Object), `pensionType` (String)

### 6.2 DC Pension Contributions
- [ ] **Task 6.2.1:** Display employee contribution amount and percentage
- [ ] **Task 6.2.2:** Display employer contribution amount and percentage
- [ ] **Task 6.2.3:** Display total annual contribution
- [ ] **Task 6.2.4:** Display contribution vs annual allowance (£60,000)
- [ ] **Task 6.2.5:** Show lump sum contribution if any

### 6.3 DB Pension Contributions
- [ ] **Task 6.3.1:** Display employee contribution rate
- [ ] **Task 6.3.2:** Display years of service
- [ ] **Task 6.3.3:** Display accrual rate
- [ ] **Task 6.3.4:** Display pensionable salary

### 6.4 State Pension Contributions
- [ ] **Task 6.4.1:** Display qualifying years
- [ ] **Task 6.4.2:** Display years needed for full pension
- [ ] **Task 6.4.3:** Display NI credits status
- [ ] **Task 6.4.4:** Display gaps in record if any

---

## Phase 7: Create PensionProjectionsPanel.vue

### 7.1 Component Setup
- [ ] **Task 7.1.1:** Create `resources/js/views/Retirement/PensionProjectionsPanel.vue`
- [ ] **Task 7.1.2:** Define props: `pension` (Object), `pensionType` (String)

### 7.2 DC Pension Projections
- [ ] **Task 7.2.1:** Calculate projected fund value at retirement (low/medium/high growth)
- [ ] **Task 7.2.2:** Display projected monthly income from drawdown
- [ ] **Task 7.2.3:** Display projected annuity purchase comparison
- [ ] **Task 7.2.4:** Add chart showing growth trajectory

### 7.3 DB Pension Projections
- [ ] **Task 7.3.1:** Display projected annual pension at NPA
- [ ] **Task 7.3.2:** Display early retirement reduction scenarios
- [ ] **Task 7.3.3:** Display commutation options (lump sum vs income trade-off)
- [ ] **Task 7.3.4:** Display inflation-adjusted values

### 7.4 State Pension Projections
- [ ] **Task 7.4.1:** Display projected weekly/annual state pension
- [ ] **Task 7.4.2:** Display deferral increase scenarios
- [ ] **Task 7.4.3:** Display impact of filling NI gaps

---

## Phase 8: Create PensionAnalysisPanel.vue

### 8.1 Component Setup
- [ ] **Task 8.1.1:** Create `resources/js/views/Retirement/PensionAnalysisPanel.vue`
- [ ] **Task 8.1.2:** Define props: `pension` (Object), `pensionType` (String)

### 8.2 DC Pension Analysis
- [ ] **Task 8.2.1:** Display fund risk assessment
- [ ] **Task 8.2.2:** Display fee analysis (AMC, platform fees)
- [ ] **Task 8.2.3:** Display diversification score if holdings available
- [ ] **Task 8.2.4:** Display optimisation suggestions

### 8.3 DB Pension Analysis
- [ ] **Task 8.3.1:** Display scheme funding status if known
- [ ] **Task 8.3.2:** Display benefit security assessment
- [ ] **Task 8.3.3:** Display comparison with DC alternative
- [ ] **Task 8.3.4:** Display transfer value considerations (with warnings)

### 8.4 State Pension Analysis
- [ ] **Task 8.4.1:** Display value for money of voluntary NI contributions
- [ ] **Task 8.4.2:** Display deferral analysis
- [ ] **Task 8.4.3:** Display integration with private pensions

### 8.5 Coming Soon Watermark
- [ ] **Task 8.5.1:** Add Coming Soon watermark to analysis panel (feature in development)

---

## Phase 9: Integration and Testing

### 9.1 Import Components
- [ ] **Task 9.1.1:** Import PensionDetailView in RetirementDashboard.vue
- [ ] **Task 9.1.2:** Register all new components
- [ ] **Task 9.1.3:** Add component map entries for detail tabs

### 9.2 Testing - DC Pensions
- [ ] **Task 9.2.1:** Test clicking DC pension card from overview
- [ ] **Task 9.2.2:** Test all detail tabs render correctly
- [ ] **Task 9.2.3:** Test "All Pensions" returns to overview
- [ ] **Task 9.2.4:** Test different DC pension types (Occupational, SIPP, Personal, Stakeholder)

### 9.3 Testing - DB Pensions
- [ ] **Task 9.3.1:** Test clicking DB pension card from overview
- [ ] **Task 9.3.2:** Test all detail tabs render correctly
- [ ] **Task 9.3.3:** Test different DB pension types (Final Salary, Career Average, Public Sector)

### 9.4 Testing - State Pension
- [ ] **Task 9.4.1:** Test clicking State pension card from overview
- [ ] **Task 9.4.2:** Test all detail tabs render correctly
- [ ] **Task 9.4.3:** Test state pension specific fields display

### 9.5 Cross-Browser Testing
- [ ] **Task 9.5.1:** Test in Chrome
- [ ] **Task 9.5.2:** Test in Safari
- [ ] **Task 9.5.3:** Test responsive layout on mobile

---

## Phase 10: Documentation and Cleanup

### 10.1 Update Documentation
- [ ] **Task 10.1.1:** Update Nov26Patch.md with all changes
- [ ] **Task 10.1.2:** Document new component structure
- [ ] **Task 10.1.3:** Add usage notes for future development

### 10.2 Code Cleanup
- [ ] **Task 10.2.1:** Remove old pension detail route if exists
- [ ] **Task 10.2.2:** Run Laravel Pint for PHP formatting (if any backend changes)
- [ ] **Task 10.2.3:** Verify no console errors or warnings

### 10.3 Final Review
- [ ] **Task 10.3.1:** Review all new components follow FPS coding standards
- [ ] **Task 10.3.2:** Verify British spelling in user-facing text
- [ ] **Task 10.3.3:** Ensure consistent formatCurrency usage
- [ ] **Task 10.3.4:** Mark retirementNavTask.md as complete

---

## Progress Summary

| Phase | Description | Tasks | Completed |
|-------|-------------|-------|-----------|
| 1 | RetirementDashboard State Management | 15 | 0 |
| 2 | Modify RetirementReadiness | 7 | 0 |
| 3 | PensionDetailView Container | 4 | 0 |
| 4 | PensionSummaryPanel | 15 | 0 |
| 5 | PensionDetailsPanel | 12 | 0 |
| 6 | PensionContributionsPanel | 13 | 0 |
| 7 | PensionProjectionsPanel | 12 | 0 |
| 8 | PensionAnalysisPanel | 14 | 0 |
| 9 | Integration and Testing | 12 | 0 |
| 10 | Documentation and Cleanup | 10 | 0 |
| **TOTAL** | | **114** | **0** |

---

## Notes

- All monetary values must use `formatCurrency()` method per CLAUDE.md standards
- Use British spelling for user-facing text (e.g., "Optimisation" not "Optimization")
- Maintain consistent card styling with existing FPS components
- Analysis panel will have "Coming Soon" watermark initially
- No backend changes required - all frontend component state management
