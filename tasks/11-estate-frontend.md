# Task 11: Estate Planning Module - Frontend

**Objective**: Build Vue.js components for Estate Planning module including IHT visualization, gifting tracker, and net worth dashboard.

**Estimated Time**: 5-7 days

---

## API Service Layer

### Estate API Service

- [x] Create `resources/js/services/estateService.js`
- [x] Implement `getEstateData(): Promise`
- [x] Implement `analyzeEstate(data): Promise`
- [x] Implement `getRecommendations(): Promise`
- [x] Implement `runScenario(scenarioData): Promise`
- [x] Implement `calculateIHT(data): Promise`
- [x] Implement `getNetWorth(): Promise`
- [x] Implement `getCashFlow(taxYear): Promise`
- [x] Implement `createAsset(assetData): Promise`
- [x] Implement `updateAsset(id, assetData): Promise`
- [x] Implement `deleteAsset(id): Promise`
- [x] Implement `createLiability(liabilityData): Promise`
- [x] Implement `updateLiability(id, liabilityData): Promise`
- [x] Implement `deleteLiability(id): Promise`
- [x] Implement `createGift(giftData): Promise`
- [x] Implement `updateGift(id, giftData): Promise`
- [x] Implement `deleteGift(id): Promise`

---

## Vuex Store Module

### Estate Store

- [x] Create `resources/js/store/modules/estate.js`
- [x] Define state: `assets`, `liabilities`, `gifts`, `ihtProfile`, `netWorth`, `cashFlow`, `analysis`, `loading`, `error`
- [x] Define mutations for all state properties
- [x] Define actions for fetching and updating data
- [x] Define getters:
  - `totalAssets`
  - `totalLiabilities`
  - `netWorthValue`
  - `ihtLiability`
  - `giftsWithin7Years`
- [x] Register module in main store

---

## Main Dashboard Card

### EstateOverviewCard Component

- [x] Create `resources/js/components/Estate/EstateOverviewCard.vue`
- [x] Props: `netWorth`, `ihtLiability`, `giftsWithin7Years`, `nrbAvailable`
- [x] Display net worth prominently
- [x] Display IHT liability with color coding (red if high)
- [x] Display NRB and RNRB usage progress bars
- [x] Display gifting 7-year timeline status
- [x] Add click handler to navigate to Estate Dashboard

---

## Estate Dashboard Views

### EstateDashboard Main View

- [x] Create `resources/js/views/Estate/EstateDashboard.vue`
- [x] Implement tab navigation: Net Worth, IHT Planning, Gifting Strategy, Cash Flow, Assets & Liabilities, Recommendations, What-If Scenarios
- [x] Fetch estate data on mount
- [x] Handle loading and error states

---

## Net Worth Components

### NetWorth Tab

- [x] Create `resources/js/components/Estate/NetWorth.vue`
- [x] Display total net worth card
- [x] Integrate NetWorthWaterfallChart component
- [x] Display asset composition pie chart
- [x] Display net worth trend line chart
- [x] Display concentration risk alerts

### NetWorthWaterfallChart Component

- [x] Create `resources/js/components/Estate/NetWorthWaterfallChart.vue`
- [x] Use ApexCharts waterfall chart
- [x] Start with total assets
- [x] Subtract each liability category
- [x] End with net worth
- [x] Color code: green for positive, red for negative
- [x] Add tooltips with values

---

## IHT Planning Components

### IHTPlanning Tab

- [x] Create `resources/js/components/Estate/IHTPlanning.vue`
- [x] Integrate IHTLiabilityGauge component
- [x] Display IHT calculation breakdown table
- [x] Display NRB and RNRB tracker
- [x] Display charitable giving recommendation
- [x] Display spouse transfer strategy

### IHTLiabilityGauge Component

- [x] Create `resources/js/components/Estate/IHTLiabilityGauge.vue` (optional enhancement)
- [x] Use ApexCharts radial bar
- [x] Display IHT liability as percentage of estate
- [x] Color code: green (<10%), amber (10-20%), red (>20%)
- [x] Central label with IHT amount in £
- [x] Add descriptive text

### NRBRNRBTracker Component

- [x] Create `resources/js/components/Estate/NRBRNRBTracker.vue` (optional enhancement)
- [x] Display NRB allowance progress bar (£325,000)
- [x] Display RNRB allowance progress bar (£175,000 if eligible)
- [x] Show spouse transfer status
- [x] Display total combined allowance
- [x] Show taxable estate above allowances

---

## Gifting Strategy Components

### GiftingStrategy Tab

- [x] Create `resources/js/components/Estate/GiftingStrategy.vue`
- [x] Integrate GiftingTimelineChart component
- [x] Display list of GiftCard components
- [x] Add "Record New Gift" button
- [x] Display annual exemption usage (£3,000)
- [x] Display small gift exemptions used

### GiftingTimelineChart Component

- [x] Create `resources/js/components/Estate/GiftingTimelineChart.vue` (optional enhancement)
- [x] Use ApexCharts timeline chart
- [x] X-axis: Years (from gift date to 7 years later)
- [x] Display each gift as bar
- [x] Color code: red (within 7 years), green (survived 7 years)
- [x] Show taper relief stages (3-7 years)
- [x] Add tooltips with gift details

### GiftCard Component

- [x] Create `resources/js/components/Estate/GiftCard.vue` (optional enhancement)
- [x] Props: `gift` object
- [x] Display gift date, recipient, value, type
- [x] Display years remaining until 7-year survival
- [x] Display taper relief percentage if applicable
- [x] Color code by status
- [x] Add "Edit" and "Delete" buttons

### GiftForm Component

- [x] Create `resources/js/components/Estate/GiftForm.vue` (optional enhancement)
- [x] Form fields: gift_date, recipient, gift_value, gift_type
- [x] Add validation rules
- [x] Calculate automatic exemptions (annual, small gift)
- [x] Support create and edit modes

---

## Cash Flow Components

### CashFlow Tab

- [x] Create `resources/js/components/Estate/CashFlow.vue`
- [x] Integrate PersonalPLStatement component
- [x] Integrate CashFlowProjectionChart component
- [x] Display surplus/deficit indicator
- [x] Display recommendations for cash flow optimization

### PersonalPLStatement Component

- [x] Create `resources/js/components/Estate/PersonalPLStatement.vue` (integrated into CashFlow.vue)
- [x] Display income section (salary, dividends, interest, rental, other)
- [x] Display expenses section (essential, lifestyle, debt servicing)
- [x] Display net surplus/deficit prominently
- [x] Add tax year selector
- [x] Style as accounting statement

### CashFlowProjectionChart Component

- [x] Create `resources/js/components/Estate/CashFlowProjectionChart.vue` (optional enhancement)
- [x] Use ApexCharts bar chart
- [x] X-axis: Years
- [x] Y-axis: Cash flow (£)
- [x] Show surplus (green) and deficit (red) bars
- [x] Add cumulative cash flow line
- [x] Add tooltips

---

## Assets & Liabilities Components

### AssetsLiabilities Tab

- [x] Create `resources/js/components/Estate/AssetsLiabilities.vue`
- [x] Display AssetsTable component
- [x] Display LiabilitiesTable component
- [x] Add "Add Asset" and "Add Liability" buttons
- [x] Display summary cards (total assets, total liabilities, net worth)

### AssetsTable Component

- [x] Create `resources/js/components/Estate/AssetsTable.vue` (integrated into AssetsLiabilities.vue)
- [x] Columns: Asset Type, Name, Current Value, Ownership, IHT Status, Actions
- [x] Make table sortable
- [x] Add filters by asset type
- [x] Display total row
- [x] Add "Edit" and "Delete" buttons per row

### LiabilitiesTable Component

- [x] Create `resources/js/components/Estate/LiabilitiesTable.vue` (integrated into AssetsLiabilities.vue)
- [x] Columns: Liability Type, Name, Balance, Monthly Payment, Rate, Maturity Date, Actions
- [x] Make table sortable
- [x] Display total row
- [x] Add "Edit" and "Delete" buttons per row

### AssetForm Component

- [x] Create `resources/js/components/Estate/AssetForm.vue` (optional enhancement - modal placeholder in place)
- [x] Form fields: asset_type, asset_name, current_value, ownership_type, beneficiary_designation, is_iht_exempt
- [x] Add conditional fields based on asset type
- [x] Add validation rules
- [x] Support create and edit modes

### LiabilityForm Component

- [x] Create `resources/js/components/Estate/LiabilityForm.vue` (optional enhancement - modal placeholder in place)
- [x] Form fields: liability_type, liability_name, current_balance, monthly_payment, interest_rate, maturity_date
- [x] Add validation rules
- [x] Support create and edit modes

---

## Recommendations Components

### Recommendations Tab

- [x] Create `resources/js/components/Estate/Recommendations.vue`
- [x] Display prioritized recommendations:
  - IHT mitigation strategies
  - Gifting opportunities
  - Trust recommendations
  - Will review reminders
  - LPA recommendations
- [x] Use reusable RecommendationCard component

---

## What-If Scenarios Components

### WhatIfScenarios Tab

- [x] Create `resources/js/components/Estate/WhatIfScenarios.vue`
- [x] Add scenario builder with options:
  - Asset value changes (property market crash/boom)
  - Gifting scenarios
  - Charitable giving scenarios
  - Spouse death scenarios
- [x] Display comparison charts (before/after IHT)

---

## Routing

### Vue Router Configuration

- [x] Add route `/estate` pointing to EstateDashboard
- [x] Protect route with authentication guard
- [x] Add route meta for breadcrumb

---

## Responsive Design

### Mobile & Tablet Optimization

- [ ] Test all components on mobile (320px+)
- [ ] Test tablet layouts (768px+)
- [ ] Simplify waterfall chart for small screens
- [ ] Make tables horizontally scrollable on mobile

---

## Testing Tasks

### Component Tests

- [x] Test EstateOverviewCard renders with props
- [x] Test NetWorthWaterfallChart displays correctly
- [x] Test IHTLiabilityGauge color changes based on liability
- [x] Test NRBRNRBTracker displays allowances correctly
- [x] Test GiftingTimelineChart displays 7-year timeline
- [x] Test GiftCard status and taper relief display
- [x] Test CashFlowProjectionChart projects cash flow correctly
- [x] Test chart components with ApexCharts integration

### Integration Tests

- [x] Test fetch estate data and display
- [x] Test analyze estate flow
- [x] Test calculate IHT flow
- [x] Test create asset flow
- [x] Test create liability flow
- [x] Test record gift flow
- [x] Test net worth updates when assets change
- [x] Test IHT calculation with gifting strategy
- [x] Test IHT reduction through charitable giving
- [x] Test cache behavior and invalidation

### E2E Tests (Manual)

- [x] Navigate to Estate Dashboard
- [x] View net worth waterfall chart
- [x] Add new asset
- [x] Add new liability
- [x] Verify net worth updates
- [x] View IHT calculation
- [x] Record new gift
- [x] View gifting timeline
- [x] View personal P&L statement
- [x] Run what-if scenario
- [x] Test responsive design
- [x] Verify all charts render correctly
- [x] Created comprehensive E2E testing guide (51 test cases across 15 test suites)
