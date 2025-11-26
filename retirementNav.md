# Pension Detail Navigation - Component State Approach

## Overview

Implement pension detail navigation using component state management within RetirementDashboard.vue. This approach keeps the URL constant at `/net-worth/retirement` while managing pension selection and tab navigation through Vue component state.

## Current Problem

When clicking a pension card in RetirementReadiness (Overview tab), navigation goes to `/pension/:type/:id` which is a top-level route. This causes the NetWorth module's top-level navigation (Overview, Retirement, Property, Investments, etc.) to disappear.

## Solution: Component State Navigation

Instead of URL-based routing, we'll manage pension selection and tab navigation entirely within RetirementDashboard.vue using component state.

---

## Implementation Plan

### Phase 1: Modify RetirementDashboard.vue State Management

**File:** `resources/js/views/Retirement/RetirementDashboard.vue`

**Changes:**
1. Add new data properties:
   - `selectedPension: null` - The selected pension object
   - `selectedPensionType: null` - 'dc', 'db', or 'state'
   - `detailTab: 'summary'` - Current detail view tab

2. Create two tab configurations:
   - `overviewTabs` - Current tabs (Overview, Contributions, Projections, etc.)
   - `detailTabs` - New tabs for pension detail view:
     - "All Pensions" (returns to overview)
     - "Summary" (pension summary panel)
     - "Details" (full pension details)
     - "Contributions" (pension-specific contributions)
     - "Projections" (pension-specific projections)
     - "Analysis" (pension-specific analysis)

3. Computed property `currentTabs` switches between tab sets based on `selectedPension`

4. Add methods:
   - `selectPension(pension, type)` - Called when card clicked
   - `clearSelection()` - Returns to overview
   - `handleDetailTabChange(tabId)` - Handles detail tab navigation

### Phase 2: Create Pension Detail Components

**New Components in `resources/js/views/Retirement/`:**

1. **PensionDetailView.vue** - Container component that shows detail tabs
   - Props: `pension`, `pensionType`, `activeTab`
   - Renders appropriate panel based on activeTab

2. **PensionSummaryPanel.vue** - Summary view of selected pension
   - Shows key metrics: current value, retirement age, monthly income projection
   - Quick stats cards

3. **PensionDetailsPanel.vue** - Full pension details
   - All pension fields in editable/view mode
   - Holdings breakdown (for DC pensions with investments)
   - Pension-specific information

4. **PensionContributionsPanel.vue** - Contribution tracking
   - Employee/employer contributions (DC)
   - Accrual rates (DB)
   - Contribution history
   - NI credits (State)

5. **PensionProjectionsPanel.vue** - Projections for this pension
   - Growth projections at different rates
   - Retirement value estimates
   - Annuity/drawdown comparisons

6. **PensionAnalysisPanel.vue** - Analysis for this pension
   - Risk assessment
   - Fee analysis
   - Optimisation suggestions

### Phase 3: Modify RetirementReadiness.vue

**File:** `resources/js/views/Retirement/RetirementReadiness.vue`

**Changes:**
1. Change card click handlers from `router.push()` to `$emit('select-pension', pension, type)`
2. Parent (RetirementDashboard) handles the event and updates state

### Phase 4: Wire Up Components

**In RetirementDashboard.vue:**
1. Import all new components
2. Update template to conditionally render:
   - RetirementReadiness when no pension selected (overview mode)
   - PensionDetailView when pension selected (detail mode)
3. Pass selectedPension and detailTab to PensionDetailView
4. Handle tab bar to show appropriate tabs based on mode

---

## Component Hierarchy (After Implementation)

```
NetWorthDashboard.vue
  └── [Top Nav: Overview | Retirement | Property | Investments | ...]
      └── RetirementDashboard.vue
          ├── [Tab Nav - Overview Mode]: Overview | Contributions | Projections | Portfolio | Strategies | Decumulation
          │   └── RetirementReadiness.vue (shows pension cards)
          │
          └── [Tab Nav - Detail Mode]: All Pensions | Summary | Details | Contributions | Projections | Analysis
              └── PensionDetailView.vue
                  ├── PensionSummaryPanel.vue
                  ├── PensionDetailsPanel.vue
                  ├── PensionContributionsPanel.vue
                  ├── PensionProjectionsPanel.vue
                  └── PensionAnalysisPanel.vue
```

---

## Benefits of This Approach

1. **URL Stability** - URL stays at `/net-worth/retirement`, no route changes
2. **Instant Transitions** - No page reloads, smooth Vue transitions
3. **Preserved Context** - Top-level navigation always visible
4. **Simple Implementation** - No router configuration changes needed
5. **Easy Back Navigation** - "All Pensions" tab returns to overview instantly
6. **Maintainable** - All logic contained within Retirement module

---

## Files to Create

1. `resources/js/views/Retirement/PensionDetailView.vue`
2. `resources/js/views/Retirement/PensionSummaryPanel.vue`
3. `resources/js/views/Retirement/PensionDetailsPanel.vue`
4. `resources/js/views/Retirement/PensionContributionsPanel.vue`
5. `resources/js/views/Retirement/PensionProjectionsPanel.vue`
6. `resources/js/views/Retirement/PensionAnalysisPanel.vue`

## Files to Modify

1. `resources/js/views/Retirement/RetirementDashboard.vue`
2. `resources/js/views/Retirement/RetirementReadiness.vue`

---

## Implementation Order

1. Modify RetirementDashboard.vue with state management and dual tab system
2. Modify RetirementReadiness.vue to emit events instead of routing
3. Create PensionDetailView.vue container
4. Create PensionSummaryPanel.vue (initial landing view)
5. Create PensionDetailsPanel.vue
6. Create PensionContributionsPanel.vue
7. Create PensionProjectionsPanel.vue
8. Create PensionAnalysisPanel.vue
9. Test all pension types (DC, DB, State)
10. Update Nov26Patch.md with changes

---

## Estimated Scope

- 6 new Vue components
- 2 modified Vue components
- No backend changes required
- No router configuration changes
