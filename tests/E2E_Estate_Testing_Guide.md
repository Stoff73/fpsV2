# Estate Planning Module - E2E Manual Testing Guide

**Version**: 1.0
**Date**: October 2025
**Module**: Estate Planning

---

## Overview

This document provides comprehensive end-to-end (E2E) testing procedures for the Estate Planning module of the FPS (Financial Planning System). These tests should be performed manually to verify the complete user journey through the Estate Planning features.

---

## Prerequisites

### System Setup
- [ ] Development server running (`php artisan serve`)
- [ ] Frontend build running (`npm run dev`)
- [ ] Database migrated and seeded
- [ ] Test user account created and authenticated
- [ ] Browser DevTools open (Console + Network tabs)

### Test Data Preparation
- [ ] Clear browser cache and localStorage
- [ ] Ensure test user has no existing estate data
- [ ] Have sample values ready for data entry

---

## Test Suite 1: Navigation and Dashboard Access

### Test 1.1: Navigate to Estate Dashboard
**Objective**: Verify user can access Estate Planning module from main dashboard

**Steps**:
1. Log in with test credentials
2. Navigate to main dashboard at `/dashboard`
3. Locate Estate Planning card/tile
4. Click on Estate Planning card
5. Verify URL changes to `/estate`
6. Verify Estate Dashboard loads successfully

**Expected Results**:
- ✅ Estate Planning card is visible on main dashboard
- ✅ Card displays placeholder net worth (£0 or "Not set")
- ✅ Clicking card navigates to `/estate`
- ✅ Estate Dashboard loads within 2 seconds
- ✅ Tab navigation is visible (Net Worth, IHT Planning, Gifting Strategy, Cash Flow, Assets & Liabilities, Recommendations, What-If Scenarios)
- ✅ No console errors
- ✅ Breadcrumb navigation shows "Home > Estate Planning"

**Pass/Fail**: ___________

---

### Test 1.2: Tab Navigation
**Objective**: Verify all tabs are accessible and functional

**Steps**:
1. On Estate Dashboard, click each tab in sequence:
   - Net Worth
   - IHT Planning
   - Gifting Strategy
   - Cash Flow
   - Assets & Liabilities
   - Recommendations
   - What-If Scenarios
2. Verify each tab displays its content
3. Check browser URL updates (if using query parameters)

**Expected Results**:
- ✅ All 7 tabs are visible and clickable
- ✅ Active tab is highlighted
- ✅ Each tab displays appropriate content (or empty state if no data)
- ✅ Tab transitions are smooth (no flickering)
- ✅ No console errors on tab switches

**Pass/Fail**: ___________

---

## Test Suite 2: Asset Management

### Test 2.1: Add New Asset (Property)
**Objective**: Verify user can add a property asset with all fields

**Steps**:
1. Navigate to "Assets & Liabilities" tab
2. Click "Add Asset" button
3. Select Asset Type: "Property / Real Estate"
4. Fill in form:
   - Asset Name: "Main Residence"
   - Current Value: £500,000
   - Ownership: "Joint Tenants (with spouse/partner)"
   - Property Address: "123 Test Street, London, SW1A 1AA"
   - Outstanding Mortgage: £200,000
   - ✓ Main Residence (eligible for RNRB)
   - IHT Exemption: Unchecked
5. Click "Add Asset" button
6. Verify asset appears in Assets table

**Expected Results**:
- ✅ Modal/form opens with all fields visible
- ✅ Property-specific fields appear when "Property" selected
- ✅ Form validation works (e.g., current value must be >£0)
- ✅ Asset is added successfully (success message displayed)
- ✅ Asset appears in Assets table with correct values
- ✅ Net worth updates automatically
- ✅ Modal closes after successful submission

**Pass/Fail**: ___________

---

### Test 2.2: Add New Asset (Pension)
**Objective**: Verify IHT-exempt asset handling

**Steps**:
1. Click "Add Asset" button
2. Select Asset Type: "Pension"
3. Fill in form:
   - Asset Name: "DC Pension"
   - Current Value: £300,000
   - Ownership: "Sole Ownership"
   - Beneficiary Designation: "Spouse"
   - ✓ This asset is IHT-exempt
4. Verify auto-suggestion for IHT exemption
5. Add asset

**Expected Results**:
- ✅ IHT exemption checkbox auto-checks for pension
- ✅ Helper text explains pension IHT exemption
- ✅ Asset added with is_iht_exempt = true
- ✅ Asset appears in table with IHT-exempt badge/indicator

**Pass/Fail**: ___________

---

### Test 2.3: Add Multiple Assets
**Objective**: Verify multiple assets can be added

**Test Data**:
1. Investment: ISA Portfolio - £150,000
2. Savings: Current Account - £50,000
3. Personal: Classic Car - £25,000

**Steps**:
1. Add each asset listed above
2. Verify each appears in Assets table
3. Check total assets value updates

**Expected Results**:
- ✅ All 5 assets appear in table (including previous 2)
- ✅ Total assets = £1,025,000
- ✅ Assets are sortable by type, name, value
- ✅ Filter by asset type works correctly

**Pass/Fail**: ___________

---

### Test 2.4: Edit Existing Asset
**Objective**: Verify asset can be updated

**Steps**:
1. Click "Edit" button on "Main Residence" asset
2. Update Current Value to £550,000
3. Update Outstanding Mortgage to £180,000
4. Click "Update Asset"
5. Verify changes reflected in table
6. Verify net worth recalculates

**Expected Results**:
- ✅ Edit modal pre-fills with existing values
- ✅ Changes save successfully
- ✅ Updated values appear in table
- ✅ Net worth increases by £30,000 (£50k value increase - £20k mortgage decrease)

**Pass/Fail**: ___________

---

### Test 2.5: Delete Asset
**Objective**: Verify asset deletion with confirmation

**Steps**:
1. Click "Delete" button on "Classic Car" asset
2. Verify confirmation dialog appears
3. Click "Cancel" first
4. Asset should remain
5. Click "Delete" again
6. Confirm deletion
7. Verify asset removed

**Expected Results**:
- ✅ Confirmation dialog appears with asset name
- ✅ Cancel preserves asset
- ✅ Confirm deletes asset
- ✅ Asset removed from table
- ✅ Total assets decreases by £25,000
- ✅ Net worth recalculates

**Pass/Fail**: ___________

---

## Test Suite 3: Liability Management

### Test 3.1: Add New Liability (Mortgage)
**Objective**: Verify mortgage liability with repayment projection

**Steps**:
1. In Assets & Liabilities tab, click "Add Liability"
2. Select Liability Type: "Mortgage"
3. Fill in form:
   - Liability Name: "Main Residence Mortgage"
   - Current Balance: £180,000
   - Monthly Payment: £1,200
   - Interest Rate: 3.5%
   - Maturity Date: 2040-12-31
   - Secured Against: "Main Residence"
   - ✓ Priority Debt
   - Mortgage Type: "Repayment"
4. Verify repayment projection appears
5. Add liability

**Expected Results**:
- ✅ Mortgage-specific fields appear
- ✅ Repayment projection calculates correctly
- ✅ Shows estimated time to repay, total interest, total payable
- ✅ Priority debt auto-checked for mortgage
- ✅ Liability added successfully
- ✅ Net worth decreases by £180,000

**Pass/Fail**: ___________

---

### Test 3.2: Add Multiple Liabilities
**Objective**: Add various liability types

**Test Data**:
1. Personal Loan: £30,000, 6.5% interest, £500/month
2. Credit Card: £5,000, 19.9% interest, £150/month

**Steps**:
1. Add each liability
2. Verify appears in Liabilities table
3. Check total liabilities

**Expected Results**:
- ✅ All liabilities listed in table
- ✅ Total liabilities = £215,000
- ✅ Net worth = Total Assets - Total Liabilities

**Pass/Fail**: ___________

---

### Test 3.3: Repayment Projection Edge Cases
**Objective**: Test projection calculator

**Steps**:
1. Edit Personal Loan
2. Set monthly payment to £50 (too low to cover interest)
3. Verify warning/message about payment being too low
4. Increase to £600
5. Verify projection shows reasonable timeframe

**Expected Results**:
- ✅ Calculator detects insufficient payment
- ✅ Shows "Never (payment too low)" or similar warning
- ✅ Valid payments show months/years to repay
- ✅ Total interest calculation is reasonable

**Pass/Fail**: ___________

---

## Test Suite 4: Net Worth Visualization

### Test 4.1: View Net Worth Waterfall Chart
**Objective**: Verify waterfall chart displays correctly

**Steps**:
1. Navigate to "Net Worth" tab
2. Observe waterfall chart
3. Hover over each bar
4. Click legend items to toggle series

**Expected Results**:
- ✅ Chart loads within 2 seconds
- ✅ Shows Total Assets as starting point
- ✅ Each liability category shown as negative bar
- ✅ Ends with Net Worth
- ✅ Colors: Green for assets, Red for liabilities
- ✅ Tooltips show values on hover
- ✅ Values match Assets & Liabilities totals
- ✅ Net Worth = £785,000 (£1,000,000 assets - £215,000 liabilities)

**Pass/Fail**: ___________

---

### Test 4.2: Net Worth Summary Cards
**Objective**: Verify summary statistics

**Steps**:
1. Check Net Worth tab for summary cards
2. Verify values match table totals

**Expected Results**:
- ✅ Total Assets card shows £1,000,000
- ✅ Total Liabilities card shows £215,000
- ✅ Net Worth card shows £785,000
- ✅ All values formatted as currency (£X,XXX)

**Pass/Fail**: ___________

---

## Test Suite 5: IHT Planning

### Test 5.1: Set Up IHT Profile
**Objective**: Configure IHT profile for calculations

**Steps**:
1. Navigate to "IHT Planning" tab
2. Click "Set Up IHT Profile" or edit existing
3. Fill in:
   - Marital Status: Married
   - ✓ Has Spouse
   - ✓ Own Home
   - NRB Transferred from Spouse: £0
   - Charitable Giving: 5%
4. Save profile

**Expected Results**:
- ✅ Profile saves successfully
- ✅ IHT calculation triggers automatically
- ✅ NRB and RNRB values display

**Pass/Fail**: ___________

---

### Test 5.2: View IHT Liability Gauge
**Objective**: Verify IHT gauge displays correctly

**Steps**:
1. Observe IHTLiabilityGauge component
2. Check percentage and color coding
3. Verify central label shows £ amount

**Expected Results**:
- ✅ Gauge displays IHT as % of estate
- ✅ Color coding:
  - Green if <10%
  - Amber if 10-20%
  - Red if >20%
- ✅ Central label shows IHT liability amount
- ✅ Status text indicates severity (Good/Warning/Critical)

**Pass/Fail**: ___________

---

### Test 5.3: NRB and RNRB Tracker
**Objective**: Verify allowance tracking

**Steps**:
1. Observe NRB progress bar
2. Check RNRB progress bar
3. Verify spouse transfer status
4. Note combined allowance

**Expected Results**:
- ✅ NRB shows £325,000 (or £650,000 if spouse transfer)
- ✅ RNRB shows £175,000 (if eligible - main residence + direct descendants)
- ✅ Progress bars show usage percentage
- ✅ Taxable estate above allowances calculated correctly
- ✅ Helper text explains each allowance

**Pass/Fail**: ___________

---

### Test 5.4: RNRB Tapering for Large Estates
**Objective**: Test tapering for estates >£2m

**Steps**:
1. Edit assets to increase estate value to £2,200,000
2. Verify RNRB tapers
3. Check calculation: Reduction = (£2,200,000 - £2,000,000) / 2 = £100,000
4. RNRB should be £175,000 - £100,000 = £75,000

**Expected Results**:
- ✅ RNRB displays £75,000
- ✅ Taper warning/message displayed
- ✅ Calculation correct

**Pass/Fail**: ___________

---

## Test Suite 6: Gifting Strategy

### Test 6.1: Record New Gift (PET)
**Objective**: Add potentially exempt transfer

**Steps**:
1. Navigate to "Gifting Strategy" tab
2. Click "Record New Gift"
3. Fill in form:
   - Gift Date: 2 years ago (e.g., 2023-01-15)
   - Recipient: "Daughter"
   - Gift Value: £50,000
   - Gift Type: "Potentially Exempt Transfer (PET)"
4. Add gift

**Expected Results**:
- ✅ Form validates gift date (not in future)
- ✅ Gift added successfully
- ✅ GiftCard displays with red status (within 3 years)
- ✅ Progress bar shows ~28% complete (2/7 years)
- ✅ Years remaining: ~5 years
- ✅ No taper relief shown (too early)

**Pass/Fail**: ___________

---

### Test 6.2: Record Gift with Taper Relief
**Objective**: Add gift 5 years old (taper relief applies)

**Steps**:
1. Click "Record New Gift"
2. Gift Date: 5 years ago
3. Recipient: "Son"
4. Gift Value: £75,000
5. Gift Type: PET
6. Add gift

**Expected Results**:
- ✅ GiftCard displays with amber status (3-7 years)
- ✅ Progress bar shows ~71% complete
- ✅ Years remaining: ~2 years
- ✅ Taper Relief: 60% badge displayed
- ✅ Effective IHT rate: 16% (40% * (100% - 60%))

**Pass/Fail**: ___________

---

### Test 6.3: Record Gift Survived 7+ Years
**Objective**: Add gift that survived 7-year rule

**Steps**:
1. Record gift dated 8 years ago
2. Recipient: "Grandchild"
3. Value: £100,000

**Expected Results**:
- ✅ GiftCard displays green status (Exempt)
- ✅ Progress bar at 100%
- ✅ Status: "IHT-Exempt (Survived 7 Years)"
- ✅ No IHT liability for this gift

**Pass/Fail**: ___________

---

### Test 6.4: View Gifting Timeline Chart
**Objective**: Verify 7-year timeline visualization

**Steps**:
1. Observe GiftingTimelineChart component
2. Check all 3 gifts appear on timeline
3. Hover over each gift bar
4. Verify color coding

**Expected Results**:
- ✅ Chart displays all gifts
- ✅ X-axis: Years (from gift date to 7 years later)
- ✅ Color coding:
  - Red: 0-3 years
  - Amber: 3-7 years
  - Green: 7+ years
- ✅ Tooltips show gift details, taper relief %, years remaining
- ✅ Taper relief reference table displayed

**Pass/Fail**: ___________

---

### Test 6.5: Small Gift Exemption
**Objective**: Test £250 small gift limit

**Steps**:
1. Record gift of £250
2. Select "Small Gift Exemption (£250 limit)"
3. Verify accepted
4. Try to record £251 small gift
5. Verify validation error

**Expected Results**:
- ✅ £250 gift accepted with small gift exemption
- ✅ £251 gift rejected with error: "Small gifts must be £250 or less per person"
- ✅ Automatic exemption checker shows gift qualifies

**Pass/Fail**: ___________

---

### Test 6.6: Annual Exemption
**Objective**: Test £3,000 annual exemption limit

**Steps**:
1. Record gift of £3,000
2. Select "Annual Exemption (£3,000)"
3. Verify accepted
4. Try £3,001
5. Verify validation error

**Expected Results**:
- ✅ £3,000 accepted
- ✅ £3,001 rejected with error: "Annual exemption is limited to £3,000 per tax year"
- ✅ Helper text explains annual exemption

**Pass/Fail**: ___________

---

### Test 6.7: Edit Gift
**Objective**: Update existing gift

**Steps**:
1. Click "Edit" on one gift card
2. Change gift value
3. Update

**Expected Results**:
- ✅ Edit form pre-fills with existing values
- ✅ Changes save successfully
- ✅ Timeline chart updates

**Pass/Fail**: ___________

---

### Test 6.8: Delete Gift
**Objective**: Remove gift with confirmation

**Steps**:
1. Click "Delete" on gift card
2. Confirm deletion

**Expected Results**:
- ✅ Confirmation dialog shows recipient name
- ✅ Gift removed from list
- ✅ Timeline chart updates

**Pass/Fail**: ___________

---

## Test Suite 7: Cash Flow Projection

### Test 7.1: View Personal P&L Statement
**Objective**: Verify cash flow tab displays income/expenses

**Steps**:
1. Navigate to "Cash Flow" tab
2. Observe Personal P&L Statement
3. Check income and expense sections
4. Verify net surplus/deficit calculation

**Expected Results**:
- ✅ P&L statement displays current tax year
- ✅ Income sections: Salary, Dividends, Interest, Rental, Other
- ✅ Expense sections: Essential, Lifestyle, Debt Servicing
- ✅ Net surplus/deficit calculated correctly
- ✅ Formatted as accounting statement

**Pass/Fail**: ___________

---

### Test 7.2: Cash Flow Projection Chart
**Objective**: Verify multi-year cash flow projection

**Steps**:
1. Observe CashFlowProjectionChart
2. Default projection: 10 years
3. Check bars for each year
4. Verify cumulative line overlay

**Expected Results**:
- ✅ Chart displays 10 years by default
- ✅ Green bars for surplus, red bars for deficit
- ✅ Cumulative line shows running total
- ✅ Tooltips show values on hover
- ✅ Summary cards display:
  - Total Projected Income
  - Total Projected Expenses
  - Cumulative Surplus/Deficit

**Pass/Fail**: ___________

---

### Test 7.3: Change Projection Period
**Objective**: Adjust projection timeframe

**Steps**:
1. Change "Projection Period" dropdown to 20 years
2. Verify chart updates to 20 years
3. Try 5 years
4. Try 15 years

**Expected Results**:
- ✅ Chart updates instantly when dropdown changes
- ✅ X-axis adjusts to show all years
- ✅ Summary cards recalculate for selected period
- ✅ No console errors

**Pass/Fail**: ___________

---

### Test 7.4: Change Growth Rate
**Objective**: Adjust annual growth rate assumption

**Steps**:
1. Change "Annual Growth Rate" to 0% (Flat)
2. Observe projection - should be flat lines
3. Change to 5%
4. Observe steeper growth

**Expected Results**:
- ✅ 0% growth = flat projection
- ✅ Higher growth rates = steeper curves
- ✅ Both income and expenses grow at same rate
- ✅ Cumulative line reflects growth

**Pass/Fail**: ___________

---

## Test Suite 8: Recommendations

### Test 8.1: View Recommendations
**Objective**: Verify personalized recommendations display

**Steps**:
1. Navigate to "Recommendations" tab
2. Review list of recommendations
3. Check categorization (IHT, Gifting, Trust, Will, LPA)

**Expected Results**:
- ✅ Recommendations load successfully
- ✅ Each recommendation has:
  - Title
  - Description
  - Action button/link
  - Priority indicator (High/Medium/Low)
- ✅ Recommendations sorted by priority
- ✅ Categories displayed (e.g., "IHT Mitigation", "Gifting Opportunity")

**Pass/Fail**: ___________

---

### Test 8.2: Recommendation Content
**Objective**: Verify relevant recommendations based on user data

**Expected Recommendations** (based on test data):
- IHT liability reduction strategies (estate >£500k)
- Annual gifting opportunities (use £3k exemption)
- Will review reminder
- Lasting Power of Attorney recommendation
- RNRB eligibility confirmation

**Expected Results**:
- ✅ Recommendations relevant to user's estate
- ✅ Specific numbers/values referenced (e.g., "Your IHT liability is £X")
- ✅ Actionable advice provided

**Pass/Fail**: ___________

---

## Test Suite 9: What-If Scenarios

### Test 9.1: Run Annual Gifting Scenario
**Objective**: Model IHT reduction through annual gifting

**Steps**:
1. Navigate to "What-If Scenarios" tab
2. Select scenario: "Annual Gifting Strategy"
3. Set: "Gift £3,000 annually for 7 years"
4. Click "Run Scenario"
5. Review results

**Expected Results**:
- ✅ Scenario calculates successfully
- ✅ Shows:
  - Estate value after gifting
  - IHT liability after gifting
  - Net estate (after IHT)
  - Saving vs. baseline
- ✅ Comparison chart shows before/after IHT
- ✅ Numeric savings displayed (e.g., "Save £8,400 in IHT")

**Pass/Fail**: ___________

---

### Test 9.2: Run Charitable Giving Scenario
**Objective**: Model 10% charitable giving for reduced IHT rate

**Steps**:
1. Select scenario: "Charitable Giving"
2. Set: "10% to charity"
3. Run scenario
4. Verify IHT rate reduces to 36% (from 40%)

**Expected Results**:
- ✅ Scenario shows IHT rate: 36%
- ✅ Estate value after charity deduction
- ✅ Total IHT saving calculated
- ✅ Comparison chart displays

**Pass/Fail**: ___________

---

### Test 9.3: Run Spouse NRB Transfer Scenario
**Objective**: Model spouse's unused NRB transfer

**Steps**:
1. Select scenario: "Spouse NRB Transfer"
2. Set: Transfer £325,000 NRB from deceased spouse
3. Run scenario

**Expected Results**:
- ✅ NRB doubles to £650,000
- ✅ Taxable estate reduces
- ✅ IHT liability recalculates
- ✅ Saving displayed

**Pass/Fail**: ___________

---

### Test 9.4: Compare Multiple Scenarios
**Objective**: View comparison of all scenarios

**Steps**:
1. Run all 3 scenarios above
2. View "Best Scenario" recommendation
3. Check comparison table/chart

**Expected Results**:
- ✅ All scenarios listed
- ✅ "Best Scenario" identified (lowest IHT)
- ✅ Comparison table shows:
  - Scenario Name
  - Estate Value
  - IHT Liability
  - Net Estate
  - Saving vs. Baseline
- ✅ Visual chart compares all scenarios

**Pass/Fail**: ___________

---

## Test Suite 10: Responsive Design

### Test 10.1: Mobile View (320px)
**Objective**: Test on smallest mobile screen

**Steps**:
1. Resize browser to 320px width
2. Navigate through all Estate tabs
3. Add asset, liability, gift on mobile

**Expected Results**:
- ✅ All content readable without horizontal scroll
- ✅ Tab navigation stacks or scrolls horizontally
- ✅ Forms are usable
- ✅ Buttons large enough to tap (min 44px)
- ✅ Charts resize appropriately
- ✅ Tables become horizontally scrollable or stack

**Pass/Fail**: ___________

---

### Test 10.2: Tablet View (768px)
**Objective**: Test tablet layout

**Steps**:
1. Resize to 768px width
2. Navigate all tabs
3. Verify 2-column layouts where appropriate

**Expected Results**:
- ✅ Content adapts to tablet width
- ✅ Charts display at appropriate size
- ✅ Forms use 2-column layout where sensible
- ✅ No layout breaking

**Pass/Fail**: ___________

---

### Test 10.3: Desktop View (1920px)
**Objective**: Test large desktop screens

**Steps**:
1. Resize to 1920px width
2. Verify content doesn't stretch excessively
3. Check max-width constraints

**Expected Results**:
- ✅ Content centered or constrained
- ✅ No ultra-wide text lines (>120ch)
- ✅ Charts scale appropriately
- ✅ Proper use of whitespace

**Pass/Fail**: ___________

---

## Test Suite 11: Error Handling and Edge Cases

### Test 11.1: Form Validation Errors
**Objective**: Verify all form validation works

**Test Cases**:
1. Asset form:
   - Empty asset name → Error
   - Value £0 → Error
   - Value negative → Error
   - No asset type selected → Error
2. Liability form:
   - Interest rate >100% → Error
   - Interest rate <0% → Error
   - Maturity date in past → Error/Warning
3. Gift form:
   - Future gift date → Error
   - Gift value £0 → Error
   - Small gift >£250 → Error
   - Annual exemption >£3,000 → Error

**Expected Results**:
- ✅ All validation rules enforced
- ✅ Error messages clear and specific
- ✅ Error styling (red borders, red text)
- ✅ Focus moves to first error field

**Pass/Fail**: ___________

---

### Test 11.2: Empty States
**Objective**: Verify empty states display when no data

**Steps**:
1. Create fresh user with no estate data
2. Navigate to each tab
3. Check empty state messages

**Expected Results**:
- ✅ Net Worth tab: "No assets or liabilities added yet"
- ✅ Gifting Strategy tab: "No gifts recorded"
- ✅ Cash Flow tab: "No cash flow data available"
- ✅ Empty states have icon + helpful message
- ✅ Call-to-action buttons ("Add Asset", etc.)

**Pass/Fail**: ___________

---

### Test 11.3: Network Error Handling
**Objective**: Test behavior when API calls fail

**Steps**:
1. Open DevTools → Network tab
2. Throttle to "Offline"
3. Try to add an asset
4. Observe error handling

**Expected Results**:
- ✅ User-friendly error message displayed
- ✅ "Unable to connect to server" or similar
- ✅ No cryptic technical errors shown to user
- ✅ Retry option provided
- ✅ Form data preserved (not lost)

**Pass/Fail**: ___________

---

### Test 11.4: Loading States
**Objective**: Verify loading indicators display

**Steps**:
1. Throttle network to "Slow 3G"
2. Navigate to Estate Dashboard
3. Observe loading states
4. Add asset and observe submission spinner

**Expected Results**:
- ✅ Loading spinners/skeletons display during data fetch
- ✅ "Saving..." indicator on form submission
- ✅ Submit button disabled during save
- ✅ Spinners don't persist indefinitely

**Pass/Fail**: ___________

---

### Test 11.5: Negative Net Worth
**Objective**: Handle liabilities exceeding assets

**Steps**:
1. Add assets totaling £300,000
2. Add liabilities totaling £400,000
3. Verify negative net worth displayed correctly

**Expected Results**:
- ✅ Net worth displays as -£100,000 (or red with parentheses)
- ✅ Charts handle negative values
- ✅ IHT calculation shows £0 (no IHT on negative estates)
- ✅ Warning message about insolvency risk

**Pass/Fail**: ___________

---

## Test Suite 12: Chart Rendering

### Test 12.1: All Charts Render Without Errors
**Objective**: Verify ApexCharts render correctly

**Charts to Check**:
1. IHT Liability Gauge (radial bar)
2. Net Worth Waterfall Chart (bar)
3. Gifting Timeline Chart (rangeBar)
4. Cash Flow Projection Chart (bar + line)

**Steps**:
1. Navigate to each chart
2. Check browser console for errors
3. Verify chart displays within 2 seconds
4. Check tooltips on hover

**Expected Results**:
- ✅ All charts render successfully
- ✅ No ApexCharts errors in console
- ✅ Charts responsive to window resize
- ✅ Tooltips work on hover
- ✅ Chart legends functional

**Pass/Fail**: ___________

---

### Test 12.2: Chart Export Functionality
**Objective**: Test chart download/export

**Steps**:
1. Hover over chart toolbar (if available)
2. Click export/download icon
3. Select PNG or SVG format

**Expected Results**:
- ✅ Export menu appears
- ✅ Chart downloads as image file
- ✅ Filename is descriptive
- ✅ Image quality is good

**Pass/Fail**: ___________

---

## Test Suite 13: Data Persistence

### Test 13.1: Data Persists After Refresh
**Objective**: Verify data saves to database

**Steps**:
1. Add several assets, liabilities, gifts
2. Refresh browser (F5)
3. Navigate back to Estate Dashboard
4. Verify all data still present

**Expected Results**:
- ✅ All assets still listed
- ✅ All liabilities still listed
- ✅ All gifts still listed
- ✅ Net worth same as before refresh

**Pass/Fail**: ___________

---

### Test 13.2: Data Isolated by User
**Objective**: Verify users can't see other users' data

**Steps**:
1. Log in as User A
2. Add estate data
3. Log out
4. Log in as User B
5. Navigate to Estate Dashboard
6. Verify User B sees no data (or only their own)

**Expected Results**:
- ✅ User B cannot see User A's estate data
- ✅ User B sees empty state or only their own data
- ✅ API returns 401/403 if trying to access another user's data

**Pass/Fail**: ___________

---

## Test Suite 14: Performance

### Test 14.1: Page Load Performance
**Objective**: Verify acceptable load times

**Steps**:
1. Clear browser cache
2. Navigate to Estate Dashboard
3. Measure time to interactive (DevTools → Performance tab)

**Expected Results**:
- ✅ Initial load <3 seconds (on good connection)
- ✅ Charts render within 2 seconds
- ✅ No layout shifts (CLS <0.1)
- ✅ Lighthouse score >80

**Pass/Fail**: ___________

---

### Test 14.2: Large Dataset Performance
**Objective**: Test with many assets/liabilities/gifts

**Steps**:
1. Add 50 assets
2. Add 20 liabilities
3. Add 30 gifts
4. Navigate between tabs
5. Observe performance

**Expected Results**:
- ✅ Tables render without lag
- ✅ Charts still performant
- ✅ Scrolling smooth
- ✅ No browser freezing

**Pass/Fail**: ___________

---

## Test Suite 15: Accessibility

### Test 15.1: Keyboard Navigation
**Objective**: Verify full keyboard accessibility

**Steps**:
1. Navigate Estate Dashboard using only keyboard
2. Tab through all interactive elements
3. Use Enter/Space to activate buttons
4. Use arrow keys in dropdowns

**Expected Results**:
- ✅ All elements reachable via Tab
- ✅ Clear focus indicators
- ✅ Logical tab order
- ✅ Forms submittable via Enter
- ✅ Modals closable via Escape

**Pass/Fail**: ___________

---

### Test 15.2: Screen Reader Compatibility
**Objective**: Test with screen reader (if available)

**Steps**:
1. Enable screen reader (NVDA, JAWS, VoiceOver)
2. Navigate Estate Dashboard
3. Listen to announcements

**Expected Results**:
- ✅ All text read correctly
- ✅ Form labels announced
- ✅ Button purposes clear
- ✅ Chart data tables available (for chart accessibility)
- ✅ Error messages announced

**Pass/Fail**: ___________

---

### Test 15.3: Color Contrast
**Objective**: Verify sufficient color contrast

**Steps**:
1. Use DevTools → Lighthouse → Accessibility
2. Check for contrast issues
3. Manually verify text readability

**Expected Results**:
- ✅ All text meets WCAG AA contrast (4.5:1)
- ✅ Important text meets WCAG AAA (7:1)
- ✅ No color-only indicators (use icons too)

**Pass/Fail**: ___________

---

## Summary Report

### Overall Results

| Test Suite | Tests Passed | Tests Failed | Pass Rate |
|------------|--------------|--------------|-----------|
| 1. Navigation | __ / 2 | __ | __% |
| 2. Asset Management | __ / 5 | __ | __% |
| 3. Liability Management | __ / 3 | __ | __% |
| 4. Net Worth Visualization | __ / 2 | __ | __% |
| 5. IHT Planning | __ / 4 | __ | __% |
| 6. Gifting Strategy | __ / 8 | __ | __% |
| 7. Cash Flow Projection | __ / 4 | __ | __% |
| 8. Recommendations | __ / 2 | __ | __% |
| 9. What-If Scenarios | __ / 4 | __ | __% |
| 10. Responsive Design | __ / 3 | __ | __% |
| 11. Error Handling | __ / 5 | __ | __% |
| 12. Chart Rendering | __ / 2 | __ | __% |
| 13. Data Persistence | __ / 2 | __ | __% |
| 14. Performance | __ / 2 | __ | __% |
| 15. Accessibility | __ / 3 | __ | __% |
| **TOTAL** | **__ / 51** | **__** | **__%** |

---

### Critical Issues Found

| Issue # | Description | Severity | Test Suite | Status |
|---------|-------------|----------|------------|--------|
| 1 | | | | |
| 2 | | | | |
| 3 | | | | |

---

### Recommendations

1.
2.
3.

---

### Sign-Off

**Tester Name**: ___________________________
**Date**: ___________________________
**Signature**: ___________________________

**Approved By**: ___________________________
**Date**: ___________________________
**Signature**: ___________________________

---

## Notes

- This test plan assumes UK tax rules for 2024/25 tax year
- IHT rates: 40% standard, 36% with 10%+ charitable giving
- NRB: £325,000 (transferable between spouses)
- RNRB: £175,000 (tapers above £2m estate, transferable)
- 7-year rule for PETs with taper relief in years 3-7
- Small gift exemption: £250 per person per year
- Annual exemption: £3,000 per tax year

---

**END OF TEST PLAN**
