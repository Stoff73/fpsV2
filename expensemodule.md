# Expenditure Module Documentation

**Component:** `resources/js/components/UserProfile/ExpenditureForm.vue`
**Purpose:** Comprehensive household expenditure tracking with spouse support and automatic financial commitments integration
**Last Updated:** November 20, 2025
**Version:** v0.2.10

---

## Table of Contents

1. [Overview](#overview)
2. [Three Operating Modes](#three-operating-modes)
3. [Financial Commitments Integration](#financial-commitments-integration)
4. [Double-Counting Prevention](#double-counting-prevention)
5. [Component Architecture](#component-architecture)
6. [Key Computed Properties](#key-computed-properties)
7. [Critical Business Logic](#critical-business-logic)
8. [Common Pitfalls](#common-pitfalls)
9. [Testing Scenarios](#testing-scenarios)

---

## Overview

The ExpenditureForm component is a sophisticated multi-mode form that handles household expenditure tracking for single users, married couples with joint finances, and married couples with separate finances. It automatically integrates financial commitments from other modules (pensions, properties, protection policies, liabilities) to provide a complete picture of household outgoings.

### Key Features

- **Three operating modes** for different household scenarios
- **Automatic financial commitments** pulled from assets/liabilities
- **Double-counting prevention** for joint ownership items
- **Spouse data integration** with linked accounts
- **Tab-based interface** for separate expenditure mode
- **Simple vs Detailed entry** modes for flexibility

---

## Three Operating Modes

### Mode 1: Simple Entry (Single User or No Spouse)

**When Active:**
- User is not married, OR
- User is married but hasn't enabled separate expenditure
- User chooses "Simple Total" entry method

**Behavior:**
- Single form with one monthly expenditure field
- No tabs displayed
- No spouse data required
- Financial commitments added on top of simple value

**Calculation:**
```javascript
totalMonthlyExpenditure = simpleMonthlyExpenditure
totalMonthlyWithCommitments = totalMonthlyExpenditure + allCommitments
```

**Example:**
- User enters: £3,000/month
- DC Pension contribution: £300/month
- Property expenses: £1,650/month
- **Total with commitments:** £4,950/month

---

### Mode 2: Joint Household (50/50 Split)

**When Active:**
- User is married, AND
- `useSeparateExpenditure = false` (checkbox NOT ticked), AND
- User chooses "Detailed Breakdown" entry method

**Behavior:**
- User enters ONE set of expenditure values representing HOUSEHOLD total
- System automatically assumes 50/50 split with spouse
- No separate spouse entries required
- All financial commitments counted once (household level)

**Calculation:**
```javascript
// User enters HOUSEHOLD expenses (not individual)
totalMonthlyExpenditure = sum of all user form fields

// Household total IS the user's total (no duplication)
householdTotalMonthlyExpenditure = totalMonthlyExpenditure

// Commitments added once at household level
householdTotalMonthlyWithCommitments = totalMonthlyExpenditure + allCommitments
```

**Example:**
- User enters Food: £800 (household total, not individual)
- User enters Entertainment: £300 (household total)
- Joint property expenses: £1,650/month
- **Household total with commitments:** £2,750/month (manual + commitments counted once)

**Important Note:**
In this mode, the user is entering HOUSEHOLD expenses, not their individual portion. The 50/50 split is IMPLIED but not calculated separately because both spouses are using the same household values.

---

### Mode 3: Separate Expenditure (Each Spouse Enters Own)

**When Active:**
- User is married, AND
- `useSeparateExpenditure = true` (checkbox IS ticked), AND
- User chooses "Detailed Breakdown" entry method

**Behavior:**
- THREE TABS: "Your Expenditure" / "Spouse's Expenditure" / "Household Total"
- Each spouse enters their OWN individual expenses (via their linked account)
- User tab shows ALL commitments (individual + 50% joint)
- Spouse tab shows ONLY joint commitments (50% joint, preventing double-counting)
- Household tab shows combined total with NO double-counting

**Calculation - User Tab:**
```javascript
// User's manual expenditure
totalMonthlyExpenditure = sum of formData fields

// User's commitments include ALL (individual + 50% of joint items)
allCommitments = {
  individualDCPension: £300,           // 100% (user's only)
  jointProperty: £1,650 * 0.5 = £825,  // 50% of joint
  individualLiability: £200            // 100% (user's only)
}
totalCommitments = £1,325

// User's total with commitments
totalMonthlyWithCommitments = totalMonthlyExpenditure + totalCommitments
// Example: £500 (manual) + £1,325 (commitments) = £1,825
```

**Calculation - Spouse Tab:**
```javascript
// Spouse's manual expenditure (entered via their account)
spouseTotalMonthlyExpenditure = sum of spouseFormData fields

// Spouse's commitments include ONLY joint items (50% of joint, preventing double-counting)
jointCommitmentsOnly = {
  jointProperty: £1,650 * 0.5 = £825,  // 50% of joint (same property as user)
  // NO individual items from user shown here
}
jointCommitmentsTotal = £825

// Spouse's total with commitments
spouseTotalMonthlyWithCommitments = spouseTotalMonthlyExpenditure + jointCommitmentsTotal
// Example: £400 (spouse manual) + £825 (joint commitments only) = £1,225
```

**Calculation - Household Tab:**
```javascript
// Household total with NO double-counting
householdTotalMonthlyWithCommitmentsCorrect =
  totalMonthlyWithCommitments +           // User: £1,825 (manual + all commitments)
  spouseTotalMonthlyWithCommitments       // Spouse: £1,225 (manual + joint only)

// = £1,825 + £1,225 = £3,050

// Breakdown verification:
// User manual:         £500
// Spouse manual:       £400
// User individual:     £500 (£300 pension + £200 liability)
// Joint property:      £1,650 (£825 user + £825 spouse = no double-count)
// TOTAL:              £3,050 ✓
```

**Why This Works (No Double-Counting):**

1. **Individual commitments** (user's DC pension, user's individual liability):
   - Counted ONCE in user's total
   - NOT shown on spouse tab

2. **Joint commitments** (joint property, joint liabilities):
   - User gets 50% (£825 of £1,650 property)
   - Spouse gets 50% (£825 of £1,650 property)
   - Total = 100% (£1,650) ✓ NO DUPLICATION

3. **Spouse's individual commitments** (if any):
   - Would be shown on SPOUSE'S account when they log in
   - NOT shown on this user's view
   - When spouse logs in, their view shows their individual items + 50% joint

---

## Financial Commitments Integration

### What Are Financial Commitments?

Financial commitments are regular monthly expenses automatically pulled from assets and liabilities entered elsewhere in the system:

| Source Module | Commitment Type | Example |
|---------------|-----------------|---------|
| Retirement (DC Pensions) | Monthly contributions | £300/month employee contribution |
| Net Worth (Properties) | Property expenses | £1,650/month (mortgage + council tax + utilities) |
| Protection (Life/CI/IP) | Insurance premiums | £150/month life insurance premium |
| Net Worth (Liabilities) | Loan/credit repayments | £200/month personal loan repayment |
| Investment (ISAs/GIAs) | Regular contributions | £500/month ISA contribution |

### How Commitments Are Calculated

**Backend:** `app/Services/UserProfile/UserProfileService.php` - `getFinancialCommitments()`

**Property Expenses Breakdown:**
```php
// For each property, aggregate monthly costs:
monthly_amount =
  mortgage_monthly_payment +
  monthly_council_tax +
  monthly_gas +
  monthly_electricity +
  monthly_water +
  monthly_building_insurance +
  monthly_contents_insurance +
  monthly_service_charge +
  monthly_maintenance_reserve +
  other_monthly_costs
```

**Joint Ownership Adjustment:**
```php
// If property is joint ownership, show 50% on each spouse's tab
if ($property->ownership_type === 'joint') {
    $displayAmount = $monthlyAmount / 2;
    $isJoint = true;
}
```

**Premium Frequency Conversion:**
```php
// Life/CI/IP insurance premiums converted to monthly
if ($policy->premium_frequency === 'quarterly') {
    $monthlyPremium = $policy->premium_amount / 3;
} elseif ($policy->premium_frequency === 'annually') {
    $monthlyPremium = $policy->premium_amount / 12;
}
```

### API Response Structure

```json
{
  "success": true,
  "data": {
    "commitments": {
      "retirement": [
        {
          "id": 1,
          "name": "Workplace Pension",
          "monthly_amount": 300.00,
          "is_joint": false,
          "breakdown": "Employee contribution"
        }
      ],
      "properties": [
        {
          "id": 5,
          "name": "15 Amherst Place, Sevenoaks",
          "monthly_amount": 825.00,
          "is_joint": true,
          "breakdown": {
            "mortgage": 450.00,
            "council_tax": 200.00,
            "utilities": 175.00
          }
        }
      ],
      "protection": [...],
      "liabilities": [...],
      "investments": [...]
    },
    "totals": {
      "retirement": 300.00,
      "properties": 825.00,
      "protection": 150.00,
      "liabilities": 200.00,
      "investments": 500.00,
      "total": 1975.00
    }
  }
}
```

---

## Double-Counting Prevention

### The Problem

When both spouses have linked accounts and view the same household:
- Joint property expenses appear in BOTH accounts
- Without proper filtering, household total would count joint items TWICE
- Example: £1,650 joint property → would show as £3,300 if counted on both tabs

### The Solution: Filtered Commitments

**User Tab - Shows ALL Commitments:**
```javascript
// Uses unfiltered financialCommitments.value.commitments
const allCommitments = financialCommitments.value.commitments;

// Includes:
// - User's individual DC pension: £300
// - Joint property (50%): £825
// - User's individual liability: £200
// Total: £1,325
```

**Spouse Tab - Shows ONLY Joint Commitments:**
```javascript
// Filters commitments by is_joint === true
const jointPropertyCommitments = computed(() => {
  return financialCommitments.value?.commitments?.properties?.filter(item => item.is_joint) || [];
});

const jointRetirementCommitments = computed(() => {
  return financialCommitments.value?.commitments?.retirement?.filter(item => item.is_joint) || [];
});

// etc. for protection, liabilities

// Joint total includes ONLY items where is_joint === true
const jointCommitmentsTotal = computed(() => {
  const retirement = jointRetirementCommitments.value.reduce((sum, item) => sum + item.monthly_amount, 0);
  const properties = jointPropertyCommitments.value.reduce((sum, item) => sum + item.monthly_amount, 0);
  const protection = jointProtectionCommitments.value.reduce((sum, item) => sum + item.monthly_amount, 0);
  const liabilities = jointLiabilityCommitments.value.reduce((sum, item) => sum + item.monthly_amount, 0);
  return retirement + properties + protection + liabilities;
});

// Example result: £825 (only the joint property, not the individual pension)
```

**Household Total - Combines Without Double-Counting:**
```javascript
const householdTotalMonthlyWithCommitmentsCorrect = computed(() => {
  if (!props.isMarried || !useSeparateExpenditure.value) {
    // Not using separate mode, just user total with all commitments
    return totalMonthlyWithCommitments.value;
  }

  // Separate mode: user (manual + all commitments) + spouse (manual + joint commitments only)
  return totalMonthlyWithCommitments.value + spouseTotalMonthlyWithCommitments.value;
});

// Calculation:
// User total:   £500 manual + £1,325 all commitments = £1,825
// Spouse total: £400 manual + £825 joint only = £1,225
// Household:    £1,825 + £1,225 = £3,050
//
// Verification:
// Manual expenditure: £500 + £400 = £900 ✓
// Individual commitments: £300 + £200 = £500 ✓
// Joint commitments: £825 + £825 = £1,650 ✓ (not £3,300)
// TOTAL: £3,050 ✓
```

---

## Component Architecture

### Component Structure (2,148 lines)

```
ExpenditureForm.vue
├── Template (lines 1-1461)
│   ├── Notes section (why expenditure matters)
│   ├── Separate expenditure checkbox (married users only)
│   ├── Entry mode toggle (Simple vs Detailed)
│   ├── Simple Entry Mode section
│   ├── Detailed Entry Mode section
│   │   ├── Tabs (User / Spouse / Household)
│   │   ├── User Tab
│   │   │   ├── Manual expenditure form fields (19 categories)
│   │   │   └── Financial Commitments (all - individual + joint)
│   │   ├── Spouse Tab
│   │   │   ├── Spouse manual expenditure form fields (19 categories)
│   │   │   └── Financial Commitments (joint only - filtered)
│   │   └── Household Tab
│   │       └── Summary (user + spouse + household totals)
│   └── Action buttons (Save / Cancel)
├── Script Setup (lines 1462-2148)
│   ├── Props (initialData, spouseData, isMarried, spouseName)
│   ├── Reactive state (27 reactive refs)
│   ├── Computed properties (40+ computed properties)
│   │   ├── Manual expenditure totals (user, spouse, household)
│   │   ├── Totals with commitments (user, spouse, household)
│   │   ├── Joint commitments filtering (4 filters)
│   │   ├── Commitment presence checks (10 booleans)
│   │   └── Display helpers (formatCurrency, tabs)
│   ├── Methods (4 core methods)
│   │   ├── fetchFinancialCommitments()
│   │   ├── loadInitialData()
│   │   ├── loadSpouseData()
│   │   ├── handleSave()
│   │   └── handleCancel()
│   └── Lifecycle hooks (onMounted, watchers)
└── No styles (uses Tailwind CSS utility classes)
```

### Props

```javascript
const props = defineProps({
  // Initial user expenditure data
  initialData: {
    type: Object,
    default: () => ({}),
  },

  // Spouse's expenditure data (if married and data sharing enabled)
  spouseData: {
    type: Object,
    default: () => ({}),
  },

  // Whether user is married (enables spouse features)
  isMarried: {
    type: Boolean,
    default: false,
  },

  // Spouse's name for display
  spouseName: {
    type: String,
    default: 'Spouse',
  },
});
```

### Events Emitted

```javascript
// Emitted when form is saved
emit('save', dataToSave);

// Emitted when user cancels/closes form
emit('close');
```

### Key Reactive State

```javascript
// Entry modes
const useSimpleEntry = ref(false);                    // Simple total vs detailed breakdown
const useSeparateExpenditure = ref(false);            // Joint household vs separate entries

// Tab management
const activeTab = ref('user');                         // 'user' | 'spouse' | 'household'
const showTabs = computed(() =>
  props.isMarried && useSeparateExpenditure.value
);

// Form data
const simpleMonthlyExpenditure = ref(0);              // Simple mode value
const formData = ref({ ... });                        // User's 19 expenditure fields
const spouseFormData = ref({ ... });                  // Spouse's 19 expenditure fields
const spouseSimpleMonthlyExpenditure = ref(0);        // Spouse simple mode value

// Financial commitments
const financialCommitments = ref(null);               // API response with all commitments
const loadingCommitments = ref(false);                // Loading state
```

---

## Key Computed Properties

### Manual Expenditure Totals (Without Commitments)

```javascript
// User's manual monthly total (sum of 19 fields)
const totalMonthlyExpenditure = computed(() => {
  if (useSimpleEntry.value) {
    return simpleMonthlyExpenditure.value || 0;
  }
  return (
    (formData.value.food_groceries || 0) +
    (formData.value.transport_fuel || 0) +
    // ... 17 more fields
    (formData.value.other_expenditure || 0)
  );
});

// Spouse's manual monthly total (sum of 19 fields)
const spouseTotalMonthlyExpenditure = computed(() => {
  if (useSimpleEntry.value) {
    return spouseSimpleMonthlyExpenditure.value || 0;
  }
  return (
    (spouseFormData.value.food_groceries || 0) +
    // ... same 19 fields as user
  );
});

// Household manual total (BEFORE commitments)
const householdTotalMonthlyExpenditure = computed(() => {
  if (!props.isMarried || !useSeparateExpenditure.value) {
    // Joint mode: user's entries ARE the household total
    return totalMonthlyExpenditure.value;
  }
  // Separate mode: sum user + spouse manual entries
  return totalMonthlyExpenditure.value + spouseTotalMonthlyExpenditure.value;
});
```

### Totals WITH Financial Commitments

```javascript
// User's total including ALL commitments (individual + 50% joint)
const totalMonthlyWithCommitments = computed(() => {
  const commitmentsTotal = financialCommitments.value?.totals?.total || 0;
  return totalMonthlyExpenditure.value + commitmentsTotal;
});

// Spouse's total including ONLY joint commitments (prevents double-counting)
const spouseTotalMonthlyWithCommitments = computed(() => {
  return spouseTotalMonthlyExpenditure.value + jointCommitmentsTotal.value;
});

// Household total with commitments (CORRECT - no double-counting)
const householdTotalMonthlyWithCommitmentsCorrect = computed(() => {
  if (!props.isMarried || !useSeparateExpenditure.value) {
    // Not using separate mode, just user total with all commitments
    return totalMonthlyWithCommitments.value;
  }
  // Separate mode: user (manual + all) + spouse (manual + joint only)
  return totalMonthlyWithCommitments.value + spouseTotalMonthlyWithCommitments.value;
});
```

### Joint Commitments Filtering (For Spouse Tab)

```javascript
// Filter retirement commitments to joint-only
const jointRetirementCommitments = computed(() => {
  return financialCommitments.value?.commitments?.retirement?.filter(item => item.is_joint) || [];
});

// Filter property commitments to joint-only
const jointPropertyCommitments = computed(() => {
  return financialCommitments.value?.commitments?.properties?.filter(item => item.is_joint) || [];
});

// Filter protection commitments to joint-only
const jointProtectionCommitments = computed(() => {
  return financialCommitments.value?.commitments?.protection?.filter(item => item.is_joint) || [];
});

// Filter liability commitments to joint-only
const jointLiabilityCommitments = computed(() => {
  return financialCommitments.value?.commitments?.liabilities?.filter(item => item.is_joint) || [];
});

// Calculate total of joint commitments only (for spouse tab)
const jointCommitmentsTotal = computed(() => {
  const retirement = jointRetirementCommitments.value.reduce((sum, item) => sum + item.monthly_amount, 0);
  const properties = jointPropertyCommitments.value.reduce((sum, item) => sum + item.monthly_amount, 0);
  const protection = jointProtectionCommitments.value.reduce((sum, item) => sum + item.monthly_amount, 0);
  const liabilities = jointLiabilityCommitments.value.reduce((sum, item) => sum + item.monthly_amount, 0);
  return retirement + properties + protection + liabilities;
});
```

---

## Critical Business Logic

### 1. Preventing Double-Counting in Separate Mode

**Problem:** Joint items appear in both user and spouse accounts

**Solution:** Different commitment display rules per tab

| Tab | Commitments Shown | Logic |
|-----|-------------------|-------|
| User Tab | ALL (individual + 50% joint) | `financialCommitments.value.commitments` (unfiltered) |
| Spouse Tab | ONLY joint (50% joint) | Filtered: `item.is_joint === true` |
| Household Tab | Combined (no duplication) | User all + Spouse joint-only |

**Example:**
```
User has:
- Individual DC pension: £300/month (is_joint: false)
- Joint property: £1,650/month → shows £825 (is_joint: true, 50% split)

User tab shows: £300 + £825 = £1,125
Spouse tab shows: £825 (joint property only, NOT the pension)
Household total: £1,125 (user) + £825 (spouse) = £1,950

Property counted once: £825 + £825 = £1,650 ✓ (not £3,300)
```

### 2. The "Correct" Suffix Explained

**Why two household total computed properties exist:**

```javascript
// OLD (line 1756) - INCORRECT, causes double-counting
const householdTotalMonthlyWithCommitments = computed(() => {
  const commitmentsTotal = financialCommitments.value?.totals?.total || 0;
  if (!props.isMarried || !useSeparateExpenditure.value) {
    return totalMonthlyExpenditure.value + commitmentsTotal;
  }
  // BUG: Adds ALL commitments to both user and spouse
  return totalMonthlyExpenditure.value + spouseTotalMonthlyExpenditure.value + commitmentsTotal;
});

// NEW (line 1774) - CORRECT, prevents double-counting
const householdTotalMonthlyWithCommitmentsCorrect = computed(() => {
  if (!props.isMarried || !useSeparateExpenditure.value) {
    return totalMonthlyWithCommitments.value;
  }
  // FIXED: User gets all commitments, spouse gets joint-only
  return totalMonthlyWithCommitments.value + spouseTotalMonthlyWithCommitments.value;
});
```

**Why both exist:** The old version is kept for backward compatibility in templates that haven't been updated. Eventually, the old one should be removed and the "Correct" one renamed.

### 3. Joint Ownership 50/50 Split

**Backend calculation** (`UserProfileService.php`):

```php
// When fetching property commitments
if ($property->ownership_type === 'joint') {
    // Split the total monthly costs 50/50
    $displayAmount = $monthlyAmount / 2;
    $isJoint = true;
} else {
    // Individual ownership - full amount
    $displayAmount = $monthlyAmount;
    $isJoint = false;
}

return [
    'monthly_amount' => $displayAmount,  // Already halved for joint
    'is_joint' => $isJoint,
];
```

**Frontend display** (ExpenditureForm.vue):

```vue
<!-- User tab: shows the 50% amount directly -->
<div class="flex justify-between">
  <span>{{ property.name }}</span>
  <span>{{ formatCurrency(property.monthly_amount) }}</span>
  <!-- £825 displayed (already 50% from backend) -->
</div>

<!-- Spouse tab: shows same 50% amount -->
<div class="flex justify-between">
  <span>{{ property.name }}</span>
  <span>{{ formatCurrency(property.monthly_amount) }}</span>
  <!-- £825 displayed (same property, same amount) -->
</div>

<!-- Total: £825 + £825 = £1,650 (the full property cost) ✓ -->
```

---

## Common Pitfalls

### ❌ PITFALL 1: Merging User and Spouse Form Data

**Wrong Assumption:** "These are duplicated, let's consolidate them"

**Why This Fails:**
- User and spouse enter DIFFERENT values
- `formData` represents USER's individual expenditure
- `spouseFormData` represents SPOUSE's individual expenditure
- Merging would prevent spouse from entering their own values

**Correct Approach:**
Keep them separate. They represent two different people's data.

---

### ❌ PITFALL 2: Removing the "Joint-Only" Filter on Spouse Tab

**Wrong Assumption:** "Why show commitments twice? Let's just show them once"

**Why This Fails:**
- User tab needs ALL commitments (individual + joint) for accurate user total
- Spouse tab needs ONLY joint to prevent double-counting household total
- Removing the filter causes joint items to be counted twice in household total

**Correct Approach:**
Keep the filtering logic. It's essential for preventing double-counting.

---

### ❌ PITFALL 3: Consolidating the Two Household Total Computed Properties

**Wrong Assumption:** "There are two household total properties, that's a bug"

**Why This is Partially True:**
- The OLD version (`householdTotalMonthlyWithCommitments`) DOES have a bug
- The NEW version (`householdTotalMonthlyWithCommitmentsCorrect`) is the fix
- Both exist during transition period

**Correct Approach:**
1. Remove the OLD version
2. Rename NEW version to remove "Correct" suffix
3. Update all template references

---

### ❌ PITFALL 4: Assuming Household Mode = 50/50 Split

**Wrong Assumption:** "In joint household mode, user enters their half, system doubles it"

**Why This Fails:**
- In joint household mode (`useSeparateExpenditure = false`), user enters HOUSEHOLD total, not individual
- The 50/50 split is implied/assumed, not calculated
- System doesn't double the values

**Correct Understanding:**
- Joint mode: User enters £800 food → household total IS £800 (not £1,600)
- Separate mode: User enters £400 food, spouse enters £400 food → household total IS £800

---

### ❌ PITFALL 5: Extracting Commitment Display to Shared Component

**Wrong Assumption:** "User tab and spouse tab show similar commitment UI, let's make it a component"

**Why This is Tricky:**
- User tab shows ALL commitments (unfiltered)
- Spouse tab shows JOINT-ONLY commitments (filtered)
- Extraction is possible BUT requires careful prop design

**Correct Approach (if extracting):**
```vue
<!-- FinancialCommitmentsDisplay.vue -->
<template>
  <div class="card p-6 bg-blue-50">
    <div v-if="hasPropertyCommitments">
      <div v-for="property in propertyCommitments" :key="property.id">
        <!-- Display logic -->
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    commitments: Object,        // Full commitments object
    filterJointOnly: Boolean,   // true = spouse tab, false = user tab
  },
  computed: {
    propertyCommitments() {
      const properties = this.commitments?.properties || [];
      return this.filterJointOnly
        ? properties.filter(p => p.is_joint)
        : properties;
    }
  }
}
</script>
```

Usage:
```vue
<!-- User tab -->
<FinancialCommitmentsDisplay
  :commitments="financialCommitments"
  :filter-joint-only="false"
/>

<!-- Spouse tab -->
<FinancialCommitmentsDisplay
  :commitments="financialCommitments"
  :filter-joint-only="true"
/>
```

---

## Testing Scenarios

### Scenario 1: Single User, No Spouse

**Setup:**
- User is not married
- No spouse account

**Expected Behavior:**
- No separate expenditure checkbox shown
- No tabs (single form)
- All commitments added to user total
- Household total = user total

**Test Data:**
```
Manual expenditure: £3,000/month
DC Pension contribution: £300/month
Property expenses: £1,200/month
Expected total: £4,500/month
```

**Test Steps:**
1. Enter expenditure values
2. Verify commitments appear in read-only section
3. Verify total = £4,500
4. Save and verify database stores correct values

---

### Scenario 2: Married Couple, Joint Household (50/50)

**Setup:**
- User is married
- `useSeparateExpenditure` checkbox NOT ticked
- Using detailed breakdown mode

**Expected Behavior:**
- User enters HOUSEHOLD expenditure (not individual)
- No separate tabs shown
- All commitments counted once
- Total represents full household

**Test Data:**
```
Household manual expenditure: £2,000/month (entered by user)
Joint property expenses: £1,650/month
Expected household total: £3,650/month
```

**Test Steps:**
1. Ensure separate expenditure checkbox is NOT ticked
2. Enter £2,000 in expenditure fields (household amounts)
3. Verify joint property shows as £825 (50% of £1,650)
4. Verify total = £2,000 + £825 = £2,825
5. Spouse logs in and sees same values (implied 50/50)

**Note:** In this mode, commitments still show as 50% (£825) but the household total is correct because user is entering household values, not individual.

---

### Scenario 3: Married Couple, Separate Expenditure

**Setup:**
- User is married
- `useSeparateExpenditure` checkbox IS ticked
- Using detailed breakdown mode
- Linked spouse account exists

**Expected Behavior:**
- Three tabs: User / Spouse / Household
- User tab shows all commitments (individual + 50% joint)
- Spouse tab shows only joint commitments (50% joint)
- Household tab shows combined total with NO double-counting

**Test Data:**
```
User manual expenditure: £500/month
Spouse manual expenditure: £400/month

User's individual DC pension: £300/month (is_joint: false)
Joint property expenses: £1,650/month (is_joint: true)
User's individual liability: £200/month (is_joint: false)
```

**Expected Calculations:**

| Tab | Manual | Commitments | Total |
|-----|--------|-------------|-------|
| User | £500 | £1,325 (£300 pension + £825 property + £200 liability) | £1,825 |
| Spouse | £400 | £825 (£825 property only) | £1,225 |
| Household | £900 | £2,150 (no duplication) | £3,050 |

**Test Steps:**
1. Tick "Enter separate expenditure" checkbox
2. Verify three tabs appear
3. Enter £500 in user expenditure fields
4. Switch to spouse tab, enter £400 in spouse expenditure fields
5. Verify user tab shows:
   - Manual: £500
   - Commitments: DC Pension £300, Joint Property £825, Liability £200
   - Total: £1,825
6. Verify spouse tab shows:
   - Manual: £400
   - Commitments: ONLY Joint Property £825 (no pension, no liability)
   - Total: £1,225
7. Switch to household tab
8. Verify household total: £3,050
9. Verify breakdown:
   - User: £1,825
   - Spouse: £1,225
   - Property counted once: £825 + £825 = £1,650 ✓

---

### Scenario 4: Joint Property Edge Case (Both Spouses View Same Property)

**Setup:**
- Married couple with separate expenditure enabled
- Joint property entered (50/50 ownership)
- Both spouses log in to their respective accounts

**Expected Behavior:**
- User sees property as £825 (50%) on their user tab
- User sees property as £825 (50%) on spouse tab (when viewing spouse's data)
- Spouse (in their account) sees property as £825 (50%) on their user tab
- Household total counts property only once (£1,650 total)

**Test Data:**
```
Property: 15 Amherst Place
Full monthly costs: £1,650
  - Mortgage: £900
  - Council tax: £400
  - Utilities: £350
Ownership: Joint (50/50)
```

**Test Steps (User Account):**
1. Navigate to User Profile → Expenditure
2. Enable separate expenditure
3. User tab: Verify property shows £825 with breakdown
4. Spouse tab: Verify same property shows £825
5. Household tab: Verify property contributes £1,650 to total (not £3,300)

**Test Steps (Spouse Account):**
1. Spouse logs in to their account
2. Navigate to User Profile → Expenditure
3. User tab (spouse's view): Verify property shows £825
4. Spouse tab (viewing user's data): Verify property shows £825
5. Household tab: Verify same £1,650 total (consistent across both accounts)

**Verification:**
- Property appears in both accounts: ✓
- Each account shows 50% (£825): ✓
- Household total is £1,650 (not £3,300): ✓
- No double-counting: ✓

---

### Scenario 5: Simple Entry Mode with Commitments

**Setup:**
- User toggles to "Simple Total" entry mode
- Has existing financial commitments

**Expected Behavior:**
- Single input field for monthly expenditure
- Commitments still appear in read-only section
- Total = simple value + commitments

**Test Data:**
```
Simple monthly expenditure: £2,500
Property expenses: £1,200
DC Pension: £300
Expected total: £4,000
```

**Test Steps:**
1. Click "Simple Total" button
2. Enter £2,500 in monthly expenditure field
3. Verify commitments section shows £1,500 total
4. Verify total = £4,000
5. Save and reload
6. Verify simple mode persists
7. Switch back to detailed breakdown
8. Verify all individual fields are zero (simple mode cleared them)

---

## 19 Expenditure Categories

The detailed breakdown mode captures 19 categories of monthly expenditure:

### Essential Living Expenses (6 categories)
1. **Food & Groceries** - `food_groceries`
2. **Transport & Fuel** - `transport_fuel`
3. **Healthcare & Medical** - `healthcare_medical`
4. **Insurance** - `insurance` (personal insurance, not property/vehicle)
5. **Mobile Phones** - `mobile_phones`
6. **Internet & TV** - `internet_tv`

### Lifestyle & Discretionary (6 categories)
7. **Subscriptions** - `subscriptions` (streaming, magazines, memberships)
8. **Clothing & Personal Care** - `clothing_personal_care`
9. **Entertainment & Dining Out** - `entertainment_dining`
10. **Holidays & Travel** - `holidays_travel`
11. **Pets** - `pets`
12. **Gifts & Charity** - `gifts_charity`

### Children & Education (5 categories)
13. **Childcare** - `childcare` (nursery, nanny, after-school care)
14. **School Fees** - `school_fees`
15. **School Lunches** - `school_lunches`
16. **School Extras** - `school_extras` (uniform, books, trips)
17. **Children's Activities** - `children_activities` (clubs, sports, lessons)

### Education (1 category - university level)
18. **University Fees** - `university_fees`

### Other (2 categories)
19. **Regular Savings** - `regular_savings`
20. **Other Expenditure** - `other_expenditure`

**Note:** The following are NOT included in manual entry (they're in financial commitments):
- Housing costs (mortgage, rent, council tax, utilities) → from Properties
- Car loans/finance → from Liabilities
- Credit cards → from Liabilities
- Personal loans → from Liabilities
- Pension contributions → from Retirement
- Investment contributions → from Investments
- Life/CI/IP insurance → from Protection

---

## Maintenance Notes

### Safe Changes

✅ **Can be changed without breaking logic:**
- Currency formatting (extract to utility)
- Field labels and help text
- CSS styling and layout
- Order of expenditure categories (with backend sync)
- Premium frequency conversion (extract to helper)

### Dangerous Changes

❌ **DO NOT change without careful consideration:**
- The three operating modes (simple/joint/separate)
- Form data structure (formData vs spouseFormData)
- Computed property calculation logic
- Joint commitments filtering logic
- Household total calculation with commitments
- The is_joint flag behavior

### Future Improvements (Safe)

1. **Extract formatCurrency to utility:**
   ```javascript
   // utils/currency.js
   export const formatCurrency = (amount) => { ... }
   ```

2. **Remove old household total computed property:**
   ```javascript
   // Delete householdTotalMonthlyWithCommitments
   // Rename householdTotalMonthlyWithCommitmentsCorrect → householdTotalMonthlyWithCommitments
   ```

3. **Extract premium frequency helper (backend):**
   ```php
   private function calculateMonthlyPremium(float $amount, string $frequency): float
   {
       return match($frequency) {
           'monthly' => $amount,
           'quarterly' => $amount / 3,
           'annually' => $amount / 12,
           default => $amount,
       };
   }
   ```

4. **Add unit tests for financial commitments:**
   ```php
   test('calculates joint property expenses correctly', function () { ... });
   test('prevents double-counting of joint commitments', function () { ... });
   ```

---

## Related Files

### Backend
- `app/Http/Controllers/Api/UserProfileController.php` - API endpoints
  - `updateExpenditure()` - Updates user expenditure
  - `updateSpouseExpenditure()` - Updates spouse expenditure
  - `getFinancialCommitments()` - Returns commitments data

- `app/Services/UserProfile/UserProfileService.php` - Business logic
  - `getFinancialCommitments()` - Aggregates commitments from all modules

### Frontend
- `resources/js/components/UserProfile/ExpenditureForm.vue` - Main component
- `resources/js/components/Onboarding/steps/ExpenditureStep.vue` - Onboarding wrapper
- `resources/js/services/userProfileService.js` - API client
- `resources/js/store/modules/userProfile.js` - Vuex store

### Database
- `users` table - Stores manual expenditure fields (19 columns)
- `expenditure_profiles` table - Legacy (deprecated in favor of users table)

---

## Version History

### v0.2.10 (November 20, 2025)
- ✅ Added financial commitments integration
- ✅ Added spouse expenditure tab
- ✅ Implemented joint-only filtering to prevent double-counting
- ✅ Added `householdTotalMonthlyWithCommitmentsCorrect` (bug fix)
- ✅ Added property expense breakdown display
- ✅ Fixed expense totals to include commitments

### v0.2.9 (November 15, 2025)
- ✅ Added separate expenditure mode for married couples
- ✅ Implemented three-tab interface (user/spouse/household)
- ✅ Added spouse form data support

### v0.2.7 (Previous)
- ✅ Initial expenditure form with simple/detailed modes
- ✅ Basic married couple support (joint household mode)

---

## Summary

The ExpenditureForm component is a sophisticated financial tracking tool that handles three distinct scenarios:

1. **Single users** - Simple or detailed expenditure entry
2. **Married couples (joint)** - Household expenditure with implied 50/50 split
3. **Married couples (separate)** - Individual expenditure with proper commitment allocation

The key innovation is the **double-counting prevention system** which ensures joint financial commitments (properties, liabilities, etc.) are counted once in household totals, not duplicated across both spouses' accounts.

**Critical principle:** User sees ALL commitments (individual + joint), Spouse sees ONLY joint commitments, Household total combines them correctly.

This documentation should be read in conjunction with `CLAUDE.md` and `bomaPath.md` for complete understanding of the FPS application architecture.

---

**Document Version:** 1.0
**Last Updated:** November 20, 2025
**Maintained By:** Development Team
**Related Documentation:** CLAUDE.md, bomaPath.md, deployment guides
