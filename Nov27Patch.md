# November 27, 2025 Patch Notes

## Summary
- Fixed Income Protection policy display issues in the Protection module
- Added scaffold warning to Investment & Savings Plan
- Fixed emergency fund data not pulling through in Plans module
- Disabled edit buttons for Property, Investment Accounts, and Savings Accounts (demo mode)
- Made Date of Birth a required field in Family Member form (onboarding)

---

## Issues Fixed

### 1. Income Protection Benefit Amount Not Displaying (Dashboard Card)
**Problem**: The Protection Overview Card on the main dashboard showed "Benefit: £0/mo" for Income Protection policies instead of the actual benefit amount.

**Root Cause**: The `ProtectionOverviewCard.vue` component was using incorrect field names:
- Used `policy.monthly_benefit` instead of `policy.benefit_amount`
- Used `policy.waiting_period_weeks` instead of `policy.deferred_period_weeks`

**Solution**: Updated the component to use the correct field names that match the API response.

### 2. Missing Helper Message for Income Protection Form
**Problem**: When adding an Income Protection policy, there was no guidance about what the benefit amount represents or what the minimum value should be.

**Solution**: Added a helper message below the benefit amount input field when Income Protection is selected: "This is the monthly amount paid out if you are unable to work. Minimum £1,000 per month."

### 3. Backend Validation Update
**Problem**: Backend allowed benefit amounts as low as £100, but business requirement is minimum £1,000.

**Solution**: Updated validation rules for Income Protection policies to require minimum £1,000 benefit amount.

---

## Files Modified

### 1. `resources/js/components/Protection/ProtectionOverviewCard.vue`
**Line 150** - Fixed field names for Income Protection display:
```vue
<!-- Before -->
<p class="policy-details">Benefit: {{ formatCurrency(policy.monthly_benefit) }}/mo • {{ policy.waiting_period_weeks }} weeks waiting</p>

<!-- After -->
<p class="policy-details">Benefit: {{ formatCurrency(policy.benefit_amount) }}/mo • {{ policy.deferred_period_weeks || 0 }} weeks waiting</p>
```

### 2. `resources/js/components/Protection/PolicyFormModal.vue`
**Lines 115-124** - Added dynamic step/min values and helper message:
```vue
<input
  v-model.number="formData.coverage_amount"
  type="number"
  :step="isIncomeProtection ? 100 : 1000"
  required
  :min="isIncomeProtection ? 1000 : 0"
  ...
/>
<p v-if="isIncomeProtection" class="text-xs text-gray-500 mt-1">
  This is the monthly amount paid out if you are unable to work. Minimum £1,000 per month.
</p>
```

**Lines 567-570** - Added new computed property:
```javascript
isIncomeProtection() {
  const type = this.formData.policyType || this.policy?.policy_type;
  return type === 'incomeProtection';
},
```

### 3. `app/Http/Controllers/Api/ProtectionController.php`
**Line 443** - Updated create validation:
```php
// Before
'benefit_amount' => 'required|numeric|min:100',

// After
'benefit_amount' => 'required|numeric|min:1000',
```

**Line 486** - Updated update validation:
```php
// Before
'benefit_amount' => 'sometimes|numeric|min:100',

// After
'benefit_amount' => 'sometimes|numeric|min:1000',
```

### 4. Investment & Savings Plan - Scaffold Warning
**Problem**: Users viewing the Investment & Savings Plan were not aware that it is not fully implemented.

**Solution**: Added a prominent amber warning banner at the top of the plan view stating: "This Investment & Savings Plan is currently a scaffold and not fully implemented. Some data may be incomplete or calculations may not reflect your full financial picture. Full functionality is coming soon."

### 5. Emergency Fund Data Not Displaying
**Problem**: The Investment & Savings Plan showed emergency fund runway as 0 months even when the user had savings and expenditure data.

**Root Cause**: The `SavingsAgent` was only looking for expenditure data in the `ExpenditureProfile` table, but the application now stores expenditure data directly on the `User` model (`monthly_expenditure` and `annual_expenditure` fields).

**Solution**: Updated `SavingsAgent.php` to fall back to User model expenditure data when `ExpenditureProfile` doesn't exist or has no data.

---

## Files Modified

### 4. `resources/js/components/Plans/InvestmentSavingsPlanView.vue`
**Lines 26-41** - Added scaffold warning banner:
```vue
<!-- Scaffold Warning Banner -->
<div class="bg-amber-50 border-2 border-amber-400 rounded-lg p-4">
  <div class="flex items-start">
    <svg class="h-6 w-6 text-amber-600 mr-3 flex-shrink-0 mt-0.5" ...>
      <!-- Warning icon -->
    </svg>
    <div>
      <h3 class="text-lg font-semibold text-amber-800">Work in Progress</h3>
      <p class="text-sm text-amber-700 mt-1">
        This Investment & Savings Plan is currently a scaffold and not fully implemented...
      </p>
    </div>
  </div>
</div>
```

### 5. `app/Agents/SavingsAgent.php`
**Lines 38-52** - Added User model import and fallback logic for expenditure:
```php
use App\Models\User;

// In analyze() method:
$user = User::find($userId);

// Get monthly expenditure - try ExpenditureProfile first, then fall back to User model
$monthlyExpenditure = 0.0;
if ($expenditureProfile && $expenditureProfile->total_monthly_expenditure > 0) {
    $monthlyExpenditure = (float) $expenditureProfile->total_monthly_expenditure;
} elseif ($user && $user->monthly_expenditure > 0) {
    $monthlyExpenditure = (float) $user->monthly_expenditure;
} elseif ($user && $user->annual_expenditure > 0) {
    // Fall back to annual expenditure divided by 12
    $monthlyExpenditure = (float) ($user->annual_expenditure / 12);
}
```

---

## Testing Checklist

### Protection Module
- [ ] Create new Income Protection policy - verify benefit amount displays correctly on dashboard
- [ ] Edit existing Income Protection policy - verify form shows correct value
- [ ] Verify helper message appears when selecting Income Protection in the form
- [ ] Verify validation error if benefit amount < £1,000
- [ ] Verify deferred period weeks displays correctly (shows 0 if not set)

### Investment & Savings Plan
- [ ] Verify scaffold warning banner appears at top of Investment & Savings Plan
- [ ] Verify emergency fund runway shows correct value (not 0) when user has expenditure data
- [ ] Verify emergency fund status displays correctly (Excellent/Adequate/Critical)

### Demo Mode - Disabled Edit Buttons
- [ ] Verify Property edit button is greyed out with tooltip "This functionality is not available for this demo"
- [ ] Verify Investment Account edit button is greyed out with tooltip
- [ ] Verify Savings Account edit button (list view) is greyed out with tooltip
- [ ] Verify Savings Account edit button (detail view) is greyed out with tooltip

### Onboarding - Family Member Form
- [ ] Verify Date of Birth field shows red asterisk for all relationship types
- [ ] Verify Date of Birth is required when submitting the form

---

## Additional Files Modified

### 6. `resources/js/components/NetWorth/Property/PropertyDetailInline.vue`
**Lines 41-47** - Disabled edit button:
```vue
<button
  disabled
  class="px-4 py-2 bg-gray-300 text-gray-500 rounded-md cursor-not-allowed"
  title="This functionality is not available for this demo"
>
  Edit
</button>
```

### 7. `resources/js/components/Investment/AccountCard.vue`
**Lines 18-26** - Disabled edit icon button:
```vue
<button
  disabled
  class="text-gray-400 cursor-not-allowed"
  title="This functionality is not available for this demo"
>
  <!-- Edit icon SVG -->
</button>
```

### 8. `resources/js/components/Savings/AccountDetails.vue`
**Lines 79-85** - Disabled edit button:
```vue
<button
  disabled
  class="px-4 py-2 bg-gray-300 text-gray-500 text-sm font-medium rounded-lg cursor-not-allowed"
  title="This functionality is not available for this demo"
>
  Edit
</button>
```

### 9. `resources/js/views/Savings/SavingsAccountDetailInline.vue`
**Lines 49-55** - Disabled edit button in savings account detail view:
```vue
<button
  disabled
  class="px-4 py-2 bg-gray-300 text-gray-500 rounded-md cursor-not-allowed"
  title="This functionality is not available for this demo"
>
  Edit
</button>
```

### 10. `resources/js/components/UserProfile/FamilyMemberFormModal.vue`
**Lines 107-116** - Made Date of Birth a required field with red asterisk:
```vue
<label for="date_of_birth" class="block text-body-sm font-medium text-gray-700 mb-1">
  Date of Birth <span class="text-error-600">*</span>
</label>
<input
  id="date_of_birth"
  v-model="form.date_of_birth"
  type="date"
  required
  class="input-field"
/>
```

---

## Onboarding UI Updates

### 11. Property Cards in Onboarding
**Problem**: Property cards in the Assets & Wealth onboarding step didn't match the style used in Net Worth property tab.

**Solution**: Replaced inline property card display with the reusable `PropertyCard` component from the Net Worth module. Updated card alignment to ensure equity values line up across all property cards.

### 12. Retirement Cards in Onboarding
**Problem**: Pension cards in the Assets & Wealth onboarding step used an old card style that didn't match the Net Worth retirement tab.

**Solution**: Updated the Retirement tab in AssetsStep.vue to use the same card styling as RetirementReadiness.vue:
- Added styled pension cards for DC, DB, and State Pensions
- Cards display type badge, scheme name, provider, and key metrics
- Clickable cards open the edit form
- Color-coded badges (blue for DC, purple for DB, green for State)

---

## Files Modified - UI Updates

### 11. `resources/js/components/Onboarding/steps/AssetsStep.vue`
**Multiple Changes**:

a) **Property Tab** - Now uses PropertyCard component:
```vue
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
  <PropertyCard
    v-for="property in properties"
    :key="property.id"
    :property="property"
    @select-property="editProperty"
  />
</div>
```

b) **Retirement Tab** - New styled pension cards matching RetirementReadiness:
```vue
<div v-if="pensions.dc.length > 0 || pensions.db.length > 0 || pensions.state" class="pensions-grid">
  <!-- DC Pension Cards -->
  <div v-for="pension in pensions.dc" :key="'dc-' + pension.id" class="pension-card" @click="openPensionForm('dc', pension)">
    <div class="card-header">
      <span class="badge badge-dc">{{ formatDCPensionType(pension.pension_type || pension.scheme_type) }}</span>
    </div>
    <!-- Card content with current value, retirement age, monthly contribution -->
  </div>

  <!-- DB Pension Cards with badge-db -->
  <!-- State Pension Card with badge-state -->
</div>
```

c) **Added Format Methods**:
```javascript
const formatDCPensionType = (type) => {
  const types = {
    occupational: 'Occupational',
    sipp: 'SIPP',
    personal: 'Personal',
    stakeholder: 'Stakeholder',
    workplace: 'Workplace',
  };
  return types[type] || 'DC Pension';
};

const formatDBPensionType = (type) => {
  const types = {
    final_salary: 'Final Salary',
    career_average: 'Career Average',
    public_sector: 'Public Sector',
  };
  return types[type] || 'DB Pension';
};
```

d) **Added CSS Styles** for pension cards (scoped):
- `.pensions-grid` - Grid layout for pension cards
- `.pension-card` - Card styling with hover effects
- `.badge-dc`, `.badge-db`, `.badge-state` - Color-coded type badges
- `.pension-scheme`, `.pension-provider-text` - Text styling
- `.pension-details`, `.detail-row`, `.detail-label`, `.detail-value` - Details section styling

e) **Removed unused import**: `PensionCard` component (replaced with inline styled cards)

f) **Investment Tab** - New styled investment account cards matching PortfolioOverview:
```vue
<div v-if="investments.length > 0" class="accounts-grid">
  <div v-for="investment in investments" :key="investment.id" class="account-card" @click="editInvestment(investment)">
    <div class="card-header">
      <span :class="getOwnershipBadgeClass(investment.ownership_type)" class="ownership-badge">
        {{ formatOwnershipType(investment.ownership_type) }}
      </span>
      <span class="badge" :class="getInvestmentTypeBadgeClass(investment.account_type)">
        {{ formatInvestmentAccountType(investment.account_type) }}
      </span>
    </div>
    <!-- Card content with value, joint share info, holdings count -->
  </div>
</div>
```

g) **Cash Tab** - New styled savings account cards matching CurrentSituation:
```vue
<div v-if="savingsAccounts.length > 0" class="accounts-grid">
  <div v-for="savings in savingsAccounts" :key="savings.id" class="account-card" @click="editSavings(savings)">
    <div class="card-header">
      <span :class="getOwnershipBadgeClass(savings.ownership_type)" class="ownership-badge">
        {{ formatOwnershipType(savings.ownership_type) }}
      </span>
      <div class="badge-group">
        <span v-if="savings.is_emergency_fund" class="badge badge-emergency">Emergency Fund</span>
        <span v-if="savings.is_isa" class="badge badge-isa">ISA</span>
      </div>
    </div>
    <!-- Card content with balance, joint share info, interest rate -->
  </div>
</div>
```

h) **Added Helper Methods** for Investment and Savings:
```javascript
// Investment helpers
formatInvestmentAccountType(type)  // ISA, GIA, SIPP, etc.
getInvestmentTypeBadgeClass(type)  // Badge colors by type

// Savings helpers
formatSavingsAccountType(type)     // Savings Account, Current Account, etc.
getFullSavingsBalance(account)     // Calculate full balance for joint accounts
formatInterestRate(rate)           // Convert decimal to percentage

// Common helpers
formatOwnershipType(type)          // Individual, Joint, Trust
getOwnershipBadgeClass(type)       // Badge colors by ownership
```

i) **Added CSS Styles** for account cards:
- `.accounts-grid` - Grid layout for account cards
- `.account-card` - Card styling with hover effects
- `.ownership-badge` - Ownership type badge styling
- `.badge-group` - Container for multiple badges
- `.badge-emergency`, `.badge-isa` - Special badge colors
- `.account-institution`, `.account-type` - Text styling
- `.account-details` - Details section styling
- `.detail-value.interest` - Green color for interest rates

### 12. `resources/js/components/NetWorth/PropertyCard.vue`
**CSS Update** - Fixed card alignment for consistent equity value positioning:
```css
.value-rows {
  display: flex;
  flex-direction: column;
  gap: 8px;
  min-height: 78px; /* Space for 3 rows */
}

.detail-row.equity {
  margin-top: auto;
  padding-top: 8px;
  border-top: 1px solid #e5e7eb;
  font-weight: 600;
}
```

---

## Testing Checklist - UI Updates

### Onboarding Property Cards
- [ ] Property cards in onboarding match Net Worth property tab style
- [ ] Property type badges display correctly (Main Residence, Secondary Residence, Buy to Let)
- [ ] Joint/Tenants in Common badges display with ownership percentage
- [ ] Full property value shown for shared ownership
- [ ] Mortgage amount displays correctly
- [ ] Equity values align at bottom across all property cards
- [ ] Clicking card opens property edit form

### Onboarding Retirement Cards
- [ ] DC Pension cards show: Type badge, Scheme name, Provider, Current Value, Retirement Age, Monthly Contribution
- [ ] DB Pension cards show: Type badge, Scheme name, Provider, Annual Income, Payment Start Age, Lump Sum
- [ ] State Pension card shows: Type badge, Forecast, NI Years, Payment Age
- [ ] Badge colors: DC = Blue, DB = Purple, State = Green
- [ ] Clicking card opens the appropriate edit form
- [ ] Cards are responsive on mobile (single column)

### Onboarding Investment Cards
- [ ] Investment cards show: Ownership badge, Account type badge, Provider, Account name/platform, Current Value
- [ ] Joint accounts show Full Value and Your Share (50%)
- [ ] Holdings count displayed
- [ ] Badge colors match Net Worth: ISA = Green, GIA = Blue, SIPP = Purple, etc.
- [ ] Clicking card opens the edit form
- [ ] Cards are responsive on mobile (single column)

### Onboarding Cash/Savings Cards
- [ ] Savings cards show: Ownership badge, Emergency Fund badge (if applicable), ISA badge (if applicable)
- [ ] Institution name, Account type displayed
- [ ] Joint accounts show Full Balance and Your Share with percentage
- [ ] Interest rate displayed (green color) when > 0
- [ ] Clicking card opens the edit form
- [ ] Cards are responsive on mobile (single column)

---

## Estate Planning Module Updates

### 13. Will Planning - Preview Mode Notice
**Problem**: Users visiting the Will Planning tab may not be aware that the feature is not fully implemented.

**Solution**: Added a user-friendly development notice at the top of the Will Planning component with:
- Gradient amber/orange background for visual appeal
- Warning icon
- Clear "Preview Mode" heading
- Explanation of what's currently available vs coming soon
- Important disclaimer about seeking professional legal advice

### Files Modified

### 13. `resources/js/components/Estate/WillPlanning.vue`
**Lines 3-21** - Added development notice banner:
```vue
<div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-5 mb-6 shadow-sm">
  <div class="flex items-start">
    <svg class="h-6 w-6 text-amber-500"><!-- Warning icon --></svg>
    <div class="ml-4">
      <h3 class="text-base font-semibold text-amber-800">Will Planning - Preview Mode</h3>
      <p class="mt-1 text-sm text-amber-700">
        This section is currently under development. You can explore the basic will configuration options,
        but advanced features like detailed bequest management, trust integration, and professional will
        review recommendations are coming soon.
      </p>
      <p class="mt-2 text-xs text-amber-600">
        <strong>Important:</strong> This tool is for planning purposes only and does not replace
        professional legal advice. Always consult a solicitor for creating or updating your will.
      </p>
    </div>
  </div>
</div>
```

---

## Net Worth Module Updates

### 14. Business Interests - Coming Soon Banner
**Problem**: The Business Interests tab had an inconsistent "Coming Soon" style compared to other modules.

**Solution**: Updated to match the Investment module's Coming Soon watermark pattern:
- Rotated amber banner overlay (`bg-amber-100 border-2 border-amber-400`)
- Content dimmed to 50% opacity
- Disabled filter and sort dropdowns
- Added feature list describing upcoming functionality

### 15. Chattels & Valuables - Coming Soon Banner
**Problem**: The Chattels tab had an inconsistent "Coming Soon" style compared to other modules.

**Solution**: Updated to match the Investment module's Coming Soon watermark pattern:
- Rotated amber banner overlay (`bg-amber-100 border-2 border-amber-400`)
- Content dimmed to 50% opacity
- Disabled filter and sort dropdowns
- Added feature list describing upcoming functionality

### Files Modified

### 14. `resources/js/components/NetWorth/BusinessInterestsList.vue`
**Template Changes**:
- Added Coming Soon watermark overlay
- Wrapped content in `opacity-50` div
- Added `disabled` attribute to filter and sort selects
- Added feature list with amber bullet points

**CSS Changes**:
- Added `.empty-title` styling
- Added `.feature-description` styling
- Added `.feature-list` and `.feature-list li` styling with amber bullets

### 15. `resources/js/components/NetWorth/ChattelsList.vue`
**Template Changes**:
- Added Coming Soon watermark overlay
- Wrapped content in `opacity-50` div
- Added `disabled` attribute to filter and sort selects
- Added feature list with amber bullet points

**CSS Changes**:
- Added `.empty-title` styling
- Added `.feature-description` styling
- Added `.feature-list` and `.feature-list li` styling with amber bullets

---

## Testing Checklist - Net Worth Coming Soon Banners

### Business Interests Tab
- [ ] Rotated amber "Coming Soon" banner displays prominently
- [ ] Underlying content is dimmed (50% opacity)
- [ ] Filter dropdown is disabled
- [ ] Sort dropdown is disabled
- [ ] Feature list displays with amber bullet points
- [ ] Banner uses consistent styling (amber-100 bg, amber-400 border, -rotate-12)

### Chattels & Valuables Tab
- [ ] Rotated amber "Coming Soon" banner displays prominently
- [ ] Underlying content is dimmed (50% opacity)
- [ ] Filter dropdown is disabled
- [ ] Sort dropdown is disabled
- [ ] Feature list displays with amber bullet points
- [ ] Banner uses consistent styling (amber-100 bg, amber-400 border, -rotate-12)

---

## Version
**Patch Date**: November 27, 2025
**Applies to**: v0.2.15+
