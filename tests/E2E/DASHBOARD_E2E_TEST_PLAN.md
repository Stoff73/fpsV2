# Dashboard E2E Test Plan

**Module**: Dashboard Integration
**Test Type**: End-to-End (Manual)
**Date**: October 14, 2025
**Status**: Ready for Execution

---

## Overview

This document provides a comprehensive manual E2E test plan for the Dashboard Integration feature. These tests should be performed in a browser to verify the complete user workflow.

---

## Prerequisites

### Test Environment Setup

1. **Backend**: Laravel development server running (`php artisan serve`)
2. **Frontend**: Vite development server running (`npm run dev`)
3. **Database**: MySQL database with test data
4. **Browser**: Latest Chrome, Firefox, or Safari
5. **Test User**: Authenticated user account with module data

### Test Data Requirements

- At least 1 protection policy
- At least 1 savings account
- At least 1 investment holding
- At least 1 pension
- At least 1 estate asset

---

## Test Cases

### TC-01: Load Main Dashboard

**Objective**: Verify the dashboard loads correctly with all module cards

**Steps**:
1. Navigate to the application root URL (`http://localhost:8000`)
2. Log in with test credentials
3. Verify dashboard loads

**Expected Results**:
- ✅ Page displays "Welcome to FPS" heading
- ✅ All 5 module cards are visible:
  - Protection Overview Card
  - Savings Overview Card
  - Investment Overview Card
  - Retirement Overview Card
  - Estate Overview Card
- ✅ Refresh button is visible in the top-right corner
- ✅ All cards display loading states initially (skeleton loaders)
- ✅ All cards load data within 3 seconds

**Pass/Fail**: _______

---

### TC-02: Verify All 5 Module Cards Display

**Objective**: Confirm each module card displays correct data

**Steps**:
1. From the dashboard, observe each module card
2. Verify data is displayed for each module

**Expected Results**:

**Protection Card**:
- ✅ Coverage adequacy score (0-100)
- ✅ Total coverage amount (£)
- ✅ Total premium (£)
- ✅ Number of critical gaps

**Savings Card**:
- ✅ Emergency fund runway (months)
- ✅ Total savings (£)
- ✅ ISA usage percentage (%)
- ✅ Goals status (on track / total)

**Investment Card**:
- ✅ Portfolio value (£)
- ✅ YTD return (%)
- ✅ Number of holdings
- ✅ Rebalancing status

**Retirement Card**:
- ✅ Readiness score (0-100)
- ✅ Projected income (£)
- ✅ Target income (£)
- ✅ Years to retirement
- ✅ Total pension wealth (£)

**Estate Card**:
- ✅ Net worth (£)
- ✅ IHT liability (£)
- ✅ Probate readiness score (0-100)

**Pass/Fail**: _______

---

### TC-03: Click Each Card to Navigate to Module

**Objective**: Verify navigation from dashboard cards to individual modules

**Steps**:
1. Click on Protection Overview Card
2. Verify navigation to `/protection`
3. Use browser back button or "Back to Dashboard" link
4. Repeat for remaining 4 modules:
   - Savings (`/savings`)
   - Investment (`/investment`)
   - Retirement (`/retirement`)
   - Estate (`/estate`)

**Expected Results**:
- ✅ Clicking each card navigates to the correct module
- ✅ Module page loads correctly
- ✅ "Back to Dashboard" link is visible on module page
- ✅ Clicking "Back to Dashboard" returns to main dashboard
- ✅ Dashboard state is preserved (no re-loading needed)

**Pass/Fail**: _______

---

### TC-04: View Financial Health Score Breakdown

**Objective**: Verify Financial Health Score component displays and calculates correctly

**Steps**:
1. Locate Financial Health Score component on dashboard
2. Observe the composite score (0-100)
3. Click "View Details" or expand button
4. Review breakdown details

**Expected Results**:
- ✅ Composite score is displayed (0-100)
- ✅ Score has appropriate label:
  - "Excellent" (80-100) - Green
  - "Good" (60-79) - Amber
  - "Fair" (40-59) - Orange
  - "Needs Improvement" (0-39) - Red
- ✅ SVG radial gauge visualization is displayed
- ✅ Breakdown panel expands/collapses correctly
- ✅ Breakdown shows all 5 module scores:
  - Protection (20% weight)
  - Emergency Fund (15% weight)
  - Retirement (25% weight)
  - Investment (20% weight)
  - Estate (20% weight)
- ✅ Each module shows score, weight %, and contribution points
- ✅ Progress bars for each module are color-coded
- ✅ Recommendation text is contextual to the score

**Pass/Fail**: _______

---

### TC-05: Check ISA Allowance Summary

**Objective**: Verify cross-module ISA allowance tracking

**Steps**:
1. Locate ISA Allowance Summary component
2. Review ISA usage breakdown
3. Verify calculations

**Expected Results**:
- ✅ Total ISA allowance displayed: £20,000
- ✅ Cash ISA usage from Savings module is displayed
- ✅ Stocks & Shares ISA usage from Investment module is displayed
- ✅ Total used = Cash ISA + S&S ISA
- ✅ Remaining allowance = £20,000 - Total used
- ✅ Progress bar color is correct:
  - Green (<50% used)
  - Amber (50-75% used)
  - Orange (75-95% used)
  - Red (>95% used)
- ✅ "Manage Savings" button navigates to `/savings`
- ✅ "Manage Investments" button navigates to `/investment`
- ✅ Warning message if over £20,000 limit

**Pass/Fail**: _______

---

### TC-06: View and Dismiss Alerts

**Objective**: Verify Alerts Panel functionality

**Steps**:
1. Locate Alerts Panel on dashboard
2. Review displayed alerts
3. Click "Dismiss" on an alert
4. Verify alert is removed

**Expected Results**:
- ✅ Alerts are displayed (up to 5)
- ✅ Alerts are sorted by severity (critical → important → info)
- ✅ Each alert shows:
  - Module badge (color-coded)
  - Severity badge (critical=red, important=amber, info=blue)
  - Title and message
  - Action link
- ✅ Clicking action link navigates to correct module
- ✅ Clicking "Dismiss" removes alert from list
- ✅ "View All" link appears if more than 5 alerts exist
- ✅ Empty state displayed when no alerts

**Pass/Fail**: _______

---

### TC-07: Use Quick Actions

**Objective**: Verify Quick Actions Panel buttons work

**Steps**:
1. Locate Quick Actions Panel
2. Click each of the 6 quick action buttons
3. Verify navigation

**Expected Results**:
- ✅ "Add Savings Goal" → navigates to `/savings`
- ✅ "Record Gift" → navigates to `/estate`
- ✅ "Update Pension Contribution" → navigates to `/retirement`
- ✅ "Add Investment Holding" → navigates to `/investment`
- ✅ "Check IHT Liability" → navigates to `/estate`
- ✅ "Update Protection Coverage" → navigates to `/protection`
- ✅ All buttons have appropriate icons
- ✅ Hover effects work on all buttons

**Pass/Fail**: _______

---

### TC-08: Test Responsive Layouts

**Objective**: Verify dashboard is responsive on different screen sizes

**Steps**:
1. Open dashboard on desktop (1920px width)
2. Resize browser to tablet (768px width)
3. Resize browser to mobile (375px width)
4. Test portrait and landscape orientations on tablet

**Expected Results**:

**Desktop (≥1024px)**:
- ✅ Module cards in 3-column grid
- ✅ All components visible
- ✅ Refresh button in header

**Tablet (768px - 1023px)**:
- ✅ Module cards in 2-column grid
- ✅ Components stack appropriately
- ✅ Touch targets are adequate (min 44x44px)

**Mobile (320px - 767px)**:
- ✅ Module cards in 1-column stack
- ✅ All text is readable
- ✅ No horizontal scrolling
- ✅ Touch targets are adequate
- ✅ Refresh button remains accessible

**Pass/Fail**: _______

---

### TC-09: Verify Loading States and Error Handling

**Objective**: Test loading states and error recovery

**Steps**:
1. Open browser DevTools Network tab
2. Throttle network to "Slow 3G"
3. Refresh dashboard
4. Observe loading states
5. Simulate API error (block API in DevTools)
6. Refresh dashboard
7. Click "Retry" button on failed card

**Expected Results**:

**Loading States**:
- ✅ Skeleton loaders appear immediately on page load
- ✅ Pulse animation on skeleton loaders
- ✅ Cards load progressively (don't wait for all)
- ✅ Refresh button shows spinning icon while refreshing

**Error Handling**:
- ✅ Failed cards show red border and error message
- ✅ Error message is user-friendly (not technical)
- ✅ "Retry" button appears on failed cards
- ✅ Clicking "Retry" attempts to reload that module
- ✅ Successfully loaded cards remain visible during errors
- ✅ No console errors visible to user

**Pass/Fail**: _______

---

### TC-10: Test Dashboard Refresh Functionality

**Objective**: Verify manual refresh works correctly

**Steps**:
1. Load dashboard and note current data
2. Change data in one module (e.g., add a savings goal)
3. Return to dashboard
4. Click "Refresh" button
5. Verify updated data appears

**Expected Results**:
- ✅ Refresh button is visible in top-right
- ✅ Clicking refresh shows "Refreshing..." text
- ✅ Spinning icon appears during refresh
- ✅ Button is disabled during refresh
- ✅ All module data is reloaded
- ✅ Updated data appears after refresh
- ✅ Refresh completes within 3 seconds
- ✅ No page reload required

**Pass/Fail**: _______

---

### TC-11: Test Net Worth Summary

**Objective**: Verify Net Worth Summary displays correct aggregated wealth

**Steps**:
1. Locate Net Worth Summary component
2. Review breakdown of assets
3. Verify calculations

**Expected Results**:
- ✅ Total net worth displayed (£)
- ✅ Breakdown shows:
  - Savings balance
  - Investment portfolio value
  - Pension values (DC + DB)
  - Other estate assets
  - Liabilities
- ✅ Net worth = Total assets - Liabilities
- ✅ All values formatted as GBP (£X,XXX.XX)
- ✅ Color coding: Green (positive), Red (negative if applicable)
- ✅ Trend indicator (placeholder for up/down)
- ✅ "View Details" button navigates to `/estate`

**Pass/Fail**: _______

---

### TC-12: Cross-Browser Compatibility

**Objective**: Verify dashboard works on different browsers

**Steps**:
1. Test on Chrome (latest version)
2. Test on Firefox (latest version)
3. Test on Safari (latest version)
4. Test on Edge (latest version)

**Expected Results**:
- ✅ Dashboard loads correctly on all browsers
- ✅ All components render identically
- ✅ No layout issues or broken elements
- ✅ Interactive features work (clicks, navigation)
- ✅ Loading states and animations work
- ✅ No console errors in any browser

**Pass/Fail**: _______

---

## Performance Testing

### PT-01: Page Load Performance

**Steps**:
1. Open browser DevTools
2. Go to Performance/Network tab
3. Hard refresh dashboard (Cmd+Shift+R or Ctrl+Shift+R)
4. Measure metrics

**Expected Results**:
- ✅ Initial page load: < 2 seconds
- ✅ All module data loaded: < 3 seconds
- ✅ Time to Interactive (TTI): < 3 seconds
- ✅ Largest Contentful Paint (LCP): < 2.5 seconds
- ✅ No memory leaks after 5 minutes of use

**Pass/Fail**: _______

---

### PT-02: Cache Performance

**Steps**:
1. Load dashboard (first visit)
2. Note load time
3. Refresh dashboard immediately
4. Note load time
5. Compare times

**Expected Results**:
- ✅ Second load is significantly faster (cache hit)
- ✅ API calls use cached data (check Network tab)
- ✅ Dashboard data cache TTL: 5 minutes
- ✅ Health score cache TTL: 1 hour
- ✅ Alerts cache TTL: 15 minutes

**Pass/Fail**: _______

---

## Accessibility Testing

### AT-01: Keyboard Navigation

**Steps**:
1. Navigate dashboard using only Tab key
2. Press Enter on focusable elements
3. Use Arrow keys where applicable

**Expected Results**:
- ✅ All interactive elements are keyboard accessible
- ✅ Tab order is logical
- ✅ Focus indicators are visible
- ✅ Enter key activates buttons and links
- ✅ No keyboard traps

**Pass/Fail**: _______

---

### AT-02: Screen Reader Compatibility

**Steps**:
1. Enable screen reader (VoiceOver on Mac, NVDA on Windows)
2. Navigate dashboard
3. Verify all content is announced

**Expected Results**:
- ✅ All headings are announced
- ✅ Card titles and data are readable
- ✅ Button purposes are clear
- ✅ Alt text on icons/images
- ✅ ARIA labels where appropriate

**Pass/Fail**: _______

---

## Security Testing

### ST-01: Authentication Required

**Steps**:
1. Log out of application
2. Try to access `/api/dashboard` directly
3. Try to access dashboard view

**Expected Results**:
- ✅ API endpoints return 401 Unauthorized
- ✅ Dashboard view redirects to login
- ✅ No data is exposed without authentication

**Pass/Fail**: _______

---

### ST-02: User Data Isolation

**Steps**:
1. Log in as User A
2. Note User A's dashboard data
3. Log out
4. Log in as User B
5. Verify User B's data is different

**Expected Results**:
- ✅ Each user sees only their own data
- ✅ Cache keys are user-specific
- ✅ No data leakage between users

**Pass/Fail**: _______

---

## Test Summary

**Total Test Cases**: 17
**Passed**: _______
**Failed**: _______
**Blocked**: _______

**Overall Status**: ⬜ Pass / ⬜ Fail

---

## Defects Found

| ID | Test Case | Description | Severity | Status |
|----|-----------|-------------|----------|--------|
| 1  |           |             |          |        |
| 2  |           |             |          |        |
| 3  |           |             |          |        |

---

## Notes

**Tested By**: _______________
**Date**: _______________
**Environment**: _______________
**Browser**: _______________
**OS**: _______________

---

## Sign-Off

**Tester**: _______________
**Date**: _______________

**Reviewer**: _______________
**Date**: _______________
