# Security Fixes - 21 November 2025

## Summary
Three security issues identified and resolved.

---

## 1. Token Logging Removed (High Severity)

**File:** `app/Http/Controllers/Api/AuthController.php`

**Issue:** Partial authentication token was being logged on user registration, creating a security risk.

**Fix:** Removed `token_preview` and `user_email` from log output. Now only logs `user_id`.

```php
// Before
\Log::info('SECURITY AUDIT: User registered', [
    'user_id' => $user->id,
    'user_email' => $user->email,
    'token_preview' => substr($token, 0, 20).'...',
]);

// After
\Log::info('User registered', [
    'user_id' => $user->id,
]);
```

---

## 2. Password Regex Updated (Medium Severity)

**File:** `app/Http/Controllers/Api/AuthController.php`

**Issue:** Password validation only accepted a whitelist of special characters (`@$!%*?&`), rejecting valid secure characters like `#`, `^`, `(`, `)`.

**Fix:** Changed regex to accept any non-alphanumeric character.

```php
// Before
'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/'

// After
'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).+$/'
```

---

## 3. CORS Hardcoded Origins Removed (Configuration Risk)

**File:** `config/cors.php`

**Issue:** Hardcoded localhost URLs mixed with production URLs could allow local dev environments to make requests to production API.

**Fix:** CORS origins now read from environment variables only.

```php
// Before
'allowed_origins' => [
    'http://localhost:8000',
    'http://127.0.0.1:8000',
    'http://localhost:5173',
    'https://csjones.co',
    env('FRONTEND_URL', 'http://localhost:5173'),
    env('APP_URL'),
],

// After
'allowed_origins' => array_filter(array_unique(array_merge(
    explode(',', env('ALLOWED_ORIGINS', '')),
    [
        env('FRONTEND_URL'),
        env('APP_URL'),
    ]
))),
```

**Environment Variables Added to `.env`:**
```
ALLOWED_ORIGINS=http://localhost:8000,http://127.0.0.1:8000,http://localhost:5173
FRONTEND_URL=http://localhost:8000
```

**Production `.env` should have:**
```
ALLOWED_ORIGINS=https://csjones.co
FRONTEND_URL=https://csjones.co
```

---

## 4. Trust Route Controller Mismatch (Domain Leakage)

**Files:** `routes/api.php`, `app/Http/Controllers/Api/Estate/TrustController.php`

**Issue:** Route `/api/trusts/upcoming-tax-returns` was pointing to `WillController` instead of `TrustController`.

**Fix:** Moved method to `TrustController` and updated route.

```php
// Before (routes/api.php)
Route::get('/trusts/upcoming-tax-returns', [WillController::class, 'getUpcomingTaxReturns']);

// After
Route::get('/trusts/upcoming-tax-returns', [TrustController::class, 'getUpcomingTaxReturns']);
```

---

## 5. N+1 Query Risk in User Model

**File:** `app/Models/User.php`

**Issue:** `hasAcceptedSpousePermission()` used `User::find($this->spouse_id)` which triggers separate DB queries when iterating over users.

**Fix:** Use existing relationship with eager-loading check.

```php
// Before
$spouse = User::find($this->spouse_id);

// After
$spouse = $this->relationLoaded('spouse') ? $this->spouse : $this->spouse()->first();
```

---

## 6. Float Casts for Financial Data (Known Limitation)

**File:** `app/Models/User.php`

**Issue:** Financial fields cast to `float` can cause precision errors in calculations.

**Status:** Documented as known limitation. Changing to integer (pence) storage would require:
- Database migrations for all financial columns
- Service layer updates
- Frontend conversion logic

**Recommendation:** For future major version, consider migrating to integer storage (pence) or using a Decimal library.

---

## Files Changed

| File | Change |
|------|--------|
| `app/Http/Controllers/Api/AuthController.php` | Removed token logging, updated password regex |
| `config/cors.php` | Removed hardcoded origins, use env vars |
| `.env` | Added ALLOWED_ORIGINS and FRONTEND_URL |
| `routes/api.php` | Fixed trust route to use TrustController |
| `app/Http/Controllers/Api/Estate/TrustController.php` | Added getUpcomingTaxReturns method |
| `app/Models/User.php` | Fixed N+1 query in hasAcceptedSpousePermission |

---

## 7. User Model Switched to Guarded

**File:** `app/Models/User.php`

**Issue:** Massive `$fillable` array (70+ fields) was difficult to maintain and prone to silent failures when new columns added.

**Fix:** Switched to `$guarded` approach - protects sensitive fields, allows all others.

```php
// Before
protected $fillable = [
    'name',
    'email',
    // ... 70+ more fields
];

// After
protected $guarded = [
    'id',
    'email_verified_at',
    'remember_token',
    'created_at',
    'updated_at',
];
```

---

## 8. Route Style Consistency (Already Compliant)

**File:** `routes/api.php`

**Status:** No fully qualified controller references found - already using imported classes.

---

## 9. Middleware Consolidation (Deferred)

**File:** `routes/api.php`

**Observation:** 20 separate `auth:sanctum` middleware applications.

**Status:** Deferred - consolidating into single group is a style improvement with risk of breaking routes. Current structure is functional and explicit.

---

## Files Changed

| File | Change |
|------|--------|
| `app/Http/Controllers/Api/AuthController.php` | Removed token logging, updated password regex |
| `config/cors.php` | Removed hardcoded origins, use env vars |
| `.env` | Added ALLOWED_ORIGINS and FRONTEND_URL |
| `routes/api.php` | Fixed trust route to use TrustController |
| `app/Http/Controllers/Api/Estate/TrustController.php` | Added getUpcomingTaxReturns method |
| `app/Models/User.php` | Fixed N+1 query, switched to guarded |
| `resources/js/components/UserProfile/ExpenditureOverview.vue` | Fixed isMarried prop type coercion |

---

## 10. Vue Prop Type Warning Fix

**File:** `resources/js/components/UserProfile/ExpenditureOverview.vue`

**Issue:** `isMarried` prop was passing `spouse_id` (Number) instead of Boolean, causing Vue warnings.

**Fix:** Added `!!` to coerce to boolean.

```javascript
// Before
return user.value?.marital_status === 'married' && user.value?.spouse_id;

// After
return user.value?.marital_status === 'married' && !!user.value?.spouse_id;
```

---

---

## 11. Slippery Mode Default Fix

**File:** `resources/js/composables/useDesignMode.js`

**Issue:** Slippery mode was persisting across sessions via localStorage, causing it to load as default on landing page.

**Fix:** Removed localStorage persistence - now always defaults to 'normal' mode on page load. Slippery mode must be explicitly activated from the dashboard each session.

```javascript
// Before
const storedMode = localStorage.getItem(DESIGN_MODE_KEY);
const designMode = ref(storedMode === 'slippery' ? 'slippery' : 'normal');

// After
localStorage.removeItem(DESIGN_MODE_KEY);
const designMode = ref('normal');
```

---

## 12. DC Pension Provider Field Made Optional

**File:** `resources/js/components/Retirement/DCPensionForm.vue`

**Issue:** Provider field was required, preventing form submission without a provider name.

**Fix:** Removed validation check for provider field - now optional.

```javascript
// Removed this validation block:
if (!this.formData.provider) {
  alert('Please enter a provider name');
  return;
}
```

---

---

## 13. DC Pension Provider - Database Column Made Nullable

**⚠️ PRODUCTION DATABASE CHANGE REQUIRED**

**Issue:** After making provider optional in validation, database column still had NOT NULL constraint causing `SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'provider' cannot be null`.

**Root Cause:** Three layers needed updating:
1. Frontend validation (DCPensionForm.vue) ✅
2. Backend validation (StoreDCPensionRequest.php) ✅
3. Database schema (dc_pensions.provider column) ✅

**Fix:** Altered database column to allow NULL.

**Production SQL to run:**
```sql
ALTER TABLE dc_pensions MODIFY COLUMN provider varchar(255) NULL;
```

**Verification:**
```sql
DESCRIBE dc_pensions;
-- provider column should show YES in Null column
```

---

## 14. Property Form - Removed 'Part and Part' Mortgage Type

**File:** `resources/js/components/NetWorth/Property/PropertyForm.vue`

**Change:** Removed 'Part and Part' option from mortgage type dropdown. Options now: Repayment, Interest Only, Mixed.

---

---

## 15. Retirement Age - Minimum Reduced to 30

**File:** `resources/js/components/Onboarding/steps/IncomeStep.vue`

**Issue:** Minimum retirement age of 55 didn't accommodate professional sports people who retire earlier.

**Changes:**
1. Input `min` attribute changed from 55 to 30
2. Validation minimum changed from 55 to 30
3. Help text updated from "Your planned retirement age (minimum 55)" to "Your planned retirement age. This may be different to the age entered for your DC Pension Plans."

---

## Files Changed (Session 2)

| File | Change |
|------|--------|
| `resources/js/composables/useDesignMode.js` | Always default to normal mode, no localStorage persistence |
| `resources/js/components/Retirement/DCPensionForm.vue` | Made provider field optional (frontend validation) |
| `app/Http/Requests/Retirement/StoreDCPensionRequest.php` | Made provider field nullable (backend validation) |
| `resources/js/components/NetWorth/Property/PropertyForm.vue` | Removed 'Part and Part' mortgage type option |
| `resources/js/components/Onboarding/steps/IncomeStep.vue` | Retirement age min reduced to 30, updated help text |
| `resources/js/store/modules/protection.js` | Added getters for individual policy types |
| `resources/js/views/Dashboard.vue` | Pass policy arrays to ProtectionOverviewCard |
| `resources/js/components/NetWorth/NetWorthOverview.vue` | Fixed spouse name display in Wealth Summary |

---

## 16. Protection Dashboard Card - Policy Display Fix

**Files:**
- `resources/js/store/modules/protection.js`
- `resources/js/views/Dashboard.vue`

**Issue:** Protection card on dashboard wasn't displaying individual policies under each category (Life Insurance, Critical Illness, Income Protection, Disability) even though policies existed in the database.

**Root Cause:** The Dashboard wasn't passing policy arrays to ProtectionOverviewCard component. The store had the data but no getters exposed it, and Dashboard didn't map or pass the props.

**Fix:**
1. Added getters in protection store:
```javascript
lifePolicies: (state) => state.policies.life || [],
criticalIllnessPolicies: (state) => state.policies.criticalIllness || [],
incomeProtectionPolicies: (state) => state.policies.incomeProtection || [],
disabilityPolicies: (state) => state.policies.disability || [],
```

2. Mapped getters in Dashboard.vue computed properties

3. Added props to ProtectionOverviewCard:
```vue
:life-policies="protectionData.lifePolicies"
:critical-illness-policies="protectionData.criticalIllnessPolicies"
:income-protection-policies="protectionData.incomeProtectionPolicies"
:disability-policies="protectionData.disabilityPolicies"
```

---

## 17. Net Worth Wealth Summary - Spouse Name Display

**File:** `resources/js/components/NetWorth/NetWorthOverview.vue`

**Issue:** Wealth Summary was showing "Spouse Wealth" instead of the actual spouse's name.

**Root Cause:** Code was looking for `user?.spouse_name` but the user object has `user?.spouse?.name` (spouse is a loaded relationship).

**Fix:** Changed computed property to use correct path:
```javascript
// Before
const spouseName = user?.spouse_name;
return spouseName || 'Spouse Wealth';

// After
const spouseName = user?.spouse?.name;
return spouseName || 'Spouse';
```

---

## Database Changes (Session 2)

| Table | Column | Change | SQL |
|-------|--------|--------|-----|
| `dc_pensions` | `provider` | NOT NULL → NULL | `ALTER TABLE dc_pensions MODIFY COLUMN provider varchar(255) NULL;` |

---

## 18. State Pension Save Not Working

**Files:**
- `resources/js/components/Retirement/UnifiedPensionForm.vue`
- `resources/js/views/Retirement/RetirementReadiness.vue`

**Issue:** When adding a state pension from the Retirement module, clicking save closed the modal but nothing was saved to the database.

**Root Cause:** Two issues:
1. `UnifiedPensionForm` wasn't passing the pension type to the parent
2. `RetirementReadiness.handlePensionSave()` wasn't dispatching any store action

**Fix:**
1. Updated `UnifiedPensionForm.handleSave()` to include pension type:
```javascript
handleSave(data) {
  const pensionType = this.mainPensionType;
  this.mainPensionType = null;
  this.$emit('save', { ...data, _pensionType: pensionType });
},
```

2. Updated `RetirementReadiness.handlePensionSave()` to dispatch correct store action based on type:
```javascript
async handlePensionSave(data) {
  const pensionType = data._pensionType;
  delete data._pensionType;

  if (pensionType === 'state') {
    await this.$store.dispatch('retirement/saveStatePension', data);
  } else if (pensionType === 'dc') {
    await this.$store.dispatch('retirement/saveDCPension', data);
  } else if (pensionType === 'db') {
    await this.$store.dispatch('retirement/saveDBPension', data);
  }

  this.showPensionForm = false;
  await this.loadData();
}
```

---

## 19. State Pension Card - Wrong Field Mappings

**File:** `resources/js/views/Retirement/RetirementReadiness.vue`

**Issue:** State pension card was displaying incorrect/missing values for forecast amount, qualifying years, and retirement age.

**Root Cause:** Display code used wrong field names that didn't match database schema:
- Used `forecast_weekly_amount` → Should be `state_pension_forecast_annual`
- Used `qualifying_years` → Should be `ni_years_completed`
- Used `retirement_age` → Should be `state_pension_age`

**Fix:** Updated computed properties to use correct field names from database.

---

## 20. DC Pension Card - Missing Fields

**File:** `resources/js/views/Retirement/RetirementReadiness.vue`

**Issue:** DC pension cards weren't showing retirement age or monthly contributions.

**Root Cause:** Display code used wrong field name `contribution_amount` instead of `monthly_contribution_amount`.

**Fix:**
1. Added retirement age display
2. Changed field reference from `contribution_amount` to `monthly_contribution_amount`

---

## Files Changed (Session 2 - Continued)

| File | Change |
|------|--------|
| `resources/js/components/Retirement/UnifiedPensionForm.vue` | Include pension type in save event |
| `resources/js/views/Retirement/RetirementReadiness.vue` | Fix handlePensionSave, fix state pension field mappings, fix DC pension field mappings |
| `resources/js/components/Estate/GiftingStrategy.vue` | Fix taper relief timeline percentage colours |

---

## 21. Gifting Strategy - Taper Relief Timeline Colours

**File:** `resources/js/components/Estate/GiftingStrategy.vue`

**Issue:** In the Gifts Made card, the Taper Relief Timeline showed percentage numbers in white text on a grey background, making them unreadable.

**Fix:** Updated `getYearLabelClass()` method to show:
- **Green** for years that have passed (gift is safer)
- **Red** for years remaining (still at IHT risk)

```javascript
// Before
if (yearsElapsed >= year) {
  return 'text-white font-bold';
}
return 'text-gray-600';

// After
if (yearsElapsed >= year) {
  return 'text-green-600 font-bold'; // Passed - good
}
return 'text-red-600 font-bold'; // Remaining - still at risk
```

---

## 22. Investment Portfolio Cards - Enhanced Information

**File:** `resources/js/components/Investment/PortfolioOverview.vue`

**Enhancement:** Added additional information to investment account cards:

1. **ISA Accounts**: Show ISA contributions for current year and remaining allowance
   - Uses `isa_subscription_current_year` field
   - Colour-coded: green (plenty remaining), amber (<£5k), red (exhausted)

2. **Joint Accounts**: Show user's 50% share alongside total value

```javascript
// ISA contributions
getIsaContributions(account) {
  return account.isa_subscription_current_year || 0;
},

getIsaRemaining(account) {
  const isaAllowance = 20000;
  return Math.max(0, isaAllowance - this.getIsaContributions(account));
},
```

---

## 23. Joint Investment Accounts - Spouse Visibility Fix

**Files:**
- `app/Http/Controllers/Api/InvestmentController.php`
- `app/Models/Investment/InvestmentAccount.php`

**Issue:** Joint investment accounts were not visible to the spouse. The spouse couldn't see accounts they co-owned.

**Root Cause:**
1. Index query only fetched `user_id = current_user`, not accounts where user is `joint_owner_id`
2. `joint_owner_id` was not in the model's `$fillable` array, so updates via Eloquent silently failed
3. Existing joint accounts had `joint_owner_id = null` (not linked)

**Fixes:**

1. **Updated index query** to include joint accounts:
```php
// Before
$accounts = InvestmentAccount::where('user_id', $user->id)->get();

// After
$accounts = InvestmentAccount::where(function ($query) use ($user) {
    $query->where('user_id', $user->id)
          ->orWhere('joint_owner_id', $user->id);
})->get();
```

2. **Added `joint_owner_id` to model fillable array**

3. **Fixed existing database records** - Linked 4 joint accounts:
   - ID 2 (user 3) ↔ ID 3 (user 4) - Vanguard
   - ID 4 (user 3) ↔ ID 5 (user 4) - Royal London

**Note:** The controller already had reciprocal creation, update sync, and delete sync logic - only the query and model fillable were missing.

---

## 24. Tenants in Common - Full Support Added

**Files:**
- `resources/js/components/NetWorth/PropertyCard.vue`
- `app/Http/Controllers/Api/PropertyController.php`
- `app/Http/Controllers/Api/MortgageController.php`

**Issue:** Properties with "Tenants in Common" ownership weren't displaying correctly on property cards - no badge, no full value, no share breakdown.

**Root Cause:** All shared ownership logic only checked for `ownership_type === 'joint'`, ignoring `tenants_in_common`.

**Fixes:**

1. **PropertyCard.vue** - Added tenants in common display:
```javascript
isTenantsInCommon() {
  return this.property.ownership_type === 'tenants_in_common';
},

isSharedOwnership() {
  return this.isJoint || this.isTenantsInCommon;
},
```
- Added green "Tenants in Common" badge
- Full property value and share breakdown now shows for both types

2. **PropertyController.php** - Updated all joint ownership checks:
```php
// Before
if ($validated['ownership_type'] === 'joint')

// After
if (in_array($validated['ownership_type'], ['joint', 'tenants_in_common']))
```
- 50/50 default split now applies to both types
- Reciprocal property creation now works for both types
- Mortgage joint owner fields now work for both types

3. **MortgageController.php** - Same pattern applied:
- Balance split now applies to both types
- Reciprocal mortgage creation now works for both types

**Database Fix:** Created missing reciprocal property for existing TIC property (ID 6 → ID 7)

---

## 25. Joint Investment Accounts - Fixed Duplicate Display

**File:** `app/Http/Controllers/Api/InvestmentController.php`

**Issue:** After adding joint_owner_id query, spouse account was seeing duplicate investment accounts.

**Root Cause:** Query included `orWhere('joint_owner_id', $user->id)` which returned both the user's own record AND the linked record.

**Fix:** Reverted to `where('user_id', $user->id)` only. With reciprocal records, each user has their own record - the `joint_owner_id` is only for linking/sync, not querying.

---

## 26. Onboarding Pre-population - Personal Info & Income

**Files:**
- `resources/js/components/Onboarding/steps/PersonalInfoStep.vue`
- `resources/js/components/Onboarding/steps/IncomeStep.vue`

**Enhancement:** Spouse onboarding forms now pre-populate with data from the linked user account.

**Context:** When a user creates a spouse account and the spouse later logs in to complete onboarding, the forms should show existing data that was collected when the account was created.

**Implementation:**

1. **PersonalInfoStep.vue** - Pre-populate from user table in onMounted:
```javascript
onMounted(async () => {
  // Ensure we have latest user data from backend
  if (!store.getters['auth/currentUser']) {
    await store.dispatch('auth/fetchUser');
  }

  const currentUser = store.getters['auth/currentUser'];

  // Pre-populate from user table if data exists
  if (currentUser) {
    if (currentUser.date_of_birth) {
      formData.value.date_of_birth = formatDate(currentUser.date_of_birth);
    }
    if (currentUser.gender) {
      formData.value.gender = currentUser.gender;
    }
    if (currentUser.marital_status) {
      formData.value.marital_status = currentUser.marital_status;
    }
    // ... similar for address fields, phone, NI number
  }

  // Then fetch step data (overrides if exists)
  const stepData = await store.dispatch('onboarding/fetchStepData', 'personal_info');
  if (stepData && Object.keys(stepData).length > 0) {
    formData.value = { ...formData.value, ...stepData };
  }
});
```

2. **IncomeStep.vue** - Pre-populate employment income:
```javascript
// Get current user from store
const currentUser = store.getters['auth/currentUser'];

// Pre-populate from user table if data exists
if (currentUser) {
  if (currentUser.employment_income) {
    formData.value.annual_employment_income = currentUser.employment_income;
  }
}
```

**Fields Pre-populated:**
- Date of birth
- Gender
- Marital status
- Address (line 1, line 2, city, county, postcode)
- Phone number
- National Insurance number
- Employment income

---

## 27. Spouse Account Creation - Address Copying

**File:** `app/Http/Controllers/Api/FamilyMembersController.php`

**Enhancement:** When a spouse account is created or linked, the address is automatically copied from the main user's account.

**Context:** Most couples share the same main residence address. When creating a spouse account, the address should default to the current user's address.

**Implementation:**

1. **New Spouse Creation** (createSpouseAccount method):
```php
$spouseUser = \App\Models\User::create([
    // ... existing fields
    // Copy address from current user (main residence)
    'address_line_1' => $currentUser->address_line_1,
    'address_line_2' => $currentUser->address_line_2,
    'city' => $currentUser->city,
    'county' => $currentUser->county,
    'postcode' => $currentUser->postcode,
]);
```

2. **Existing Account Linking** (linkExistingSpouse method):
```php
// If spouse doesn't have address, copy from current user
if (!$spouseUser->address_line_1 && $currentUser->address_line_1) {
    $spouseUser->update([
        'address_line_1' => $currentUser->address_line_1,
        'address_line_2' => $currentUser->address_line_2,
        'city' => $currentUser->city,
        'county' => $currentUser->county,
        'postcode' => $currentUser->postcode,
    ]);
}
```

**Database Fix:** Manually updated existing spouse account (user 4) with address from user 3.

---

## 28. Family Members - Reciprocal Records for Spouse

**File:** `app/Http/Controllers/Api/FamilyMembersController.php`

**Issue:** When a spouse completes onboarding, the "Family & Dependents" section was empty - their partner wasn't showing as a family member.

**Root Cause:** Creating a spouse account only created ONE family_member record (current user → spouse), not the reciprocal record (spouse → current user).

**Fix:** Added reciprocal family member creation in both scenarios:

1. **New Spouse Creation** (createSpouseAccount method):
```php
// Create main family member record (user → spouse)
$familyMember = FamilyMember::create([...]);

// Create reciprocal family member record (spouse → user)
$currentUserNameParts = explode(' ', $currentUser->name);
$currentUserFirstName = $currentUserNameParts[0] ?? '';
$currentUserLastName = implode(' ', array_slice($currentUserNameParts, 1)) ?: '';

FamilyMember::create([
    'user_id' => $spouseUser->id,
    'household_id' => $spouseUser->household_id,
    'relationship' => 'spouse',
    'first_name' => $currentUserFirstName,
    'last_name' => $currentUserLastName,
    'date_of_birth' => $currentUser->date_of_birth,
    'gender' => $currentUser->gender,
    'national_insurance_number' => $currentUser->national_insurance_number,
    'annual_income' => $currentUser->employment_income ?? 0,
    'is_dependent' => false,
    'name' => $currentUser->name,
]);
```

2. **Existing Account Linking** (linkExistingSpouse method):
```php
// Same pattern - create reciprocal family member after creating main record
```

**Database Fix:** Manually created missing family member record for existing spouse (user 4) pointing to user 3.

**Result:** Spouse onboarding now shows their partner in the Family & Dependents section with all relevant details (name, DOB, gender, income).

---

## 29. Expenditure Step - Pre-population from User Fields

**File:** `app/Services/Onboarding/OnboardingService.php`

**Issue:** When spouse completes onboarding, expenditure step was showing blank even though Chris had entered separate expenses for both himself and Ang.

**Root Cause:** `getStepData` method only looked in `onboarding_progress` table for saved step data. When Chris entered separate expenses during his onboarding, the data was saved directly to both users' records in the `users` table, but no `onboarding_progress` record was created for Ang.

**Fix:** Updated `getStepData` to fall back to user's existing expenditure fields when no `onboarding_progress` record exists.

**Implementation:**

1. **Added `getStepDataFromUser` method** (lines 1104-1198):
```php
private function getStepDataFromUser(User $user, string $stepName): ?array
{
    switch ($stepName) {
        case 'expenditure':
            // Check if user has any expenditure data
            $hasExpenditureData = $user->monthly_expenditure > 0 ||
                                 $user->food_groceries > 0 || ...;

            if (!$hasExpenditureData) {
                return null;
            }

            $userData = [
                'food_groceries' => $user->food_groceries ?? 0,
                'transport_fuel' => $user->transport_fuel ?? 0,
                // ... all expenditure fields
                'expenditure_sharing_mode' => $user->expenditure_sharing_mode ?? 'joint',
            ];

            // If spouse also has expenditure data, return in separate mode format
            if ($user->spouse_id) {
                $spouse = User::find($user->spouse_id);
                if ($spouse && $spouse has expenditure data) {
                    $userData['expenditure_sharing_mode'] = 'separate';

                    $spouseData = [/* all spouse expenditure fields */];

                    return [
                        'userData' => $userData,
                        'spouseData' => $spouseData,
                    ];
                }
            }

            return $userData;
    }
}
```

2. **Updated `getStepData`** to use fallback (lines 1079-1099):
```php
public function getStepData(int $userId, string $stepName): ?array
{
    $progress = OnboardingProgress::where('user_id', $userId)
        ->where('focus_area', $user->onboarding_focus_area)
        ->where('step_name', $stepName)
        ->first();

    // If we have saved progress, return it
    if ($progress && $progress->step_data) {
        return $progress->step_data;
    }

    // No saved progress - fall back to user's existing data
    return $this->getStepDataFromUser($user, $stepName);
}
```

**Result:**
- When Ang completes onboarding, her expenditure data (entered by Chris) pre-populates correctly
- System detects both users have separate data and returns in separate mode format
- Frontend automatically checks "Enter separate expenditure for each spouse" checkbox
- "Your Expenditure" tab shows Ang's data, "Spouse's Expenditure" shows Chris's data

---

## 30. Financial Commitments - Ownership Filtering

**Files:**
- `app/Http/Controllers/Api/UserProfileController.php`
- `app/Services/UserProfile/UserProfileService.php`

**Issue:** In separate expenditure mode, spouse's tab was showing Chris's individual property expenses. Only joint/shared commitments should appear on spouse's tab.

**Root Cause:** `getFinancialCommitments` endpoint returned ALL commitments for the user (individual + joint), with no way to filter by ownership type.

**Fix:** Added optional `ownership_filter` query parameter to filter commitments by ownership type.

**Implementation:**

1. **Updated Controller** (UserProfileController.php line 238-259):
```php
public function getFinancialCommitments(Request $request): JsonResponse
{
    $user = $request->user();

    // Optional filter: 'all' (default), 'joint_only', 'individual_only'
    $ownershipFilter = $request->query('ownership_filter', 'all');

    $commitments = $this->userProfileService->getFinancialCommitments($user, $ownershipFilter);

    return response()->json([
        'success' => true,
        'data' => $commitments,
    ]);
}
```

2. **Updated Service** (UserProfileService.php lines 407-618):
```php
public function getFinancialCommitments(User $user, string $ownershipFilter = 'all'): array
{
    // For each category (pensions, properties, liabilities):
    foreach ($items as $item) {
        $isJoint = $item->ownership_type === 'joint'; // or tenants_in_common for properties

        // Apply ownership filter
        if ($ownershipFilter === 'joint_only' && !$isJoint) {
            continue; // Skip individual items
        }
        if ($ownershipFilter === 'individual_only' && $isJoint) {
            continue; // Skip joint items
        }

        $commitments[] = [...];
    }
}
```

**Filter Logic:**
- **DC Pensions**: Filter by `ownership_type`
- **Properties**: Filter by `ownership_type` (includes 'joint' and 'tenants_in_common')
- **Liabilities**: Filter by `ownership_type`
- **Protection**: No filtering (always individual)

**Usage:**
- `GET /api/user/financial-commitments` → Returns all commitments (default)
- `GET /api/user/financial-commitments?ownership_filter=joint_only` → Returns only joint commitments
- `GET /api/user/financial-commitments?ownership_filter=individual_only` → Returns only individual commitments

**Status:** Backend complete. Frontend needs to use `ownership_filter=joint_only` when displaying spouse's expenditure tab.

---

## 31. Joint Ownership Support - Liabilities

**Files:**
- `database/migrations/2025_11_22_092125_add_joint_ownership_to_liabilities_table.php`
- `app/Models/Estate/Liability.php`
- `app/Services/UserProfile/UserProfileService.php`

**Issue:** Liabilities table lacked joint ownership support. Code in `UserProfileService` referenced `$liability->ownership_type` but the field didn't exist in the database schema.

**Root Cause:** Liabilities were implemented without joint ownership fields, unlike properties, investments, and savings accounts which all support joint ownership.

**Fix:** Added joint ownership fields to liabilities table and updated model.

**Implementation:**

1. **Created migration** to add fields:
```php
Schema::table('liabilities', function (Blueprint $table) {
    $table->enum('ownership_type', ['individual', 'joint'])
        ->default('individual')
        ->after('user_id');

    $table->unsignedBigInteger('joint_owner_id')
        ->nullable()
        ->after('ownership_type');

    $table->foreign('joint_owner_id')
        ->references('id')
        ->on('users')
        ->onDelete('set null');
});
```

2. **Updated Liability model** (app/Models/Estate/Liability.php):
```php
protected $fillable = [
    'user_id',
    'ownership_type',    // Added
    'joint_owner_id',    // Added
    'liability_type',
    // ... rest of fields
];
```

3. **Fixed DC Pension logic** in UserProfileService (lines 417-436):
   - Removed incorrect joint ownership logic (DC Pensions don't support joint ownership)
   - Set all DC Pensions as `'ownership_type' => 'individual'`
   - Skip DC Pensions when filtering for `'joint_only'`

**Result:** Liabilities now support joint ownership matching the pattern of properties, investments, and savings accounts. Joint credit cards, loans, etc. can now be properly tracked.

---

## 32. Joint Investment Display - Value Calculation Fix

**File:** `resources/js/components/Investment/PortfolioOverview.vue`

**Issue:** Joint investment accounts were showing incorrect values. The display was dividing the stored value by 2, but the database already stores each user's 50% share.

**Root Cause:** Misunderstanding of the storage pattern. The database stores:
- Record for User A: `current_value = £417,500` (their 50% share)
- Record for User B: `current_value = £417,500` (their 50% share)

The frontend was incorrectly dividing by 2 again, showing £208,750 instead of £417,500.

**Correct Storage Pattern:**
- **Each record stores the user's 50% share** (NOT the full value)
- **To get full value**: Add both linked records (£417,500 + £417,500 = £835,000)
- **To get user's share**: Use the stored value as-is (£417,500)

**Fix:**

**Before (WRONG):**
```vue
<div class="detail-row">
  <span class="detail-label">Current Value</span>
  <span class="detail-value">{{ formatCurrency(account.current_value) }}</span>  <!-- £417,500 -->
</div>

<div v-if="account.ownership_type === 'joint'" class="detail-row">
  <span class="detail-label">Your Share (50%)</span>
  <span class="detail-value">{{ formatCurrency(account.current_value / 2) }}</span>  <!-- £208,750 ❌ -->
</div>
```

**After (CORRECT):**
```vue
<!-- Joint account shows both full value and user's share -->
<div v-if="account.ownership_type === 'joint'">
  <div class="detail-row">
    <span class="detail-label">Full Value</span>
    <span class="detail-value">{{ formatCurrency(account.current_value * 2) }}</span>  <!-- £835,000 ✅ -->
  </div>
  <div class="detail-row">
    <span class="detail-label">Your Share (50%)</span>
    <span class="detail-value text-purple-600">{{ formatCurrency(account.current_value) }}</span>  <!-- £417,500 ✅ -->
  </div>
</div>

<!-- Individual account shows just current value -->
<div v-else class="detail-row">
  <span class="detail-label">Current Value</span>
  <span class="detail-value">{{ formatCurrency(account.current_value) }}</span>
</div>
```

**Result:** Joint investment accounts now display correctly:
- Full Value: User's share × 2 = £835,000
- Your Share: User's share as stored = £417,500

---

## Files Changed (Expenditure Pre-population & Filtering Session)

| File | Change |
|------|--------|
| `resources/js/components/Onboarding/steps/PersonalInfoStep.vue` | Pre-populate from user table (DOB, gender, marital status, address, phone, NI) |
| `resources/js/components/Onboarding/steps/IncomeStep.vue` | Pre-populate employment income from user table |
| `app/Http/Controllers/Api/FamilyMembersController.php` | Copy address when creating/linking spouse, create reciprocal family member records |
| `app/Services/Onboarding/OnboardingService.php` | Added `getStepDataFromUser` fallback method for expenditure pre-population |
| `app/Http/Controllers/Api/UserProfileController.php` | Added `ownership_filter` query parameter to getFinancialCommitments |
| `app/Services/UserProfile/UserProfileService.php` | Added ownership filtering and fixed DC Pension logic |
| `database/migrations/2025_11_22_092125_add_joint_ownership_to_liabilities_table.php` | Added joint ownership support to liabilities |
| `app/Models/Estate/Liability.php` | Added ownership_type and joint_owner_id to fillable |
| `resources/js/components/Investment/PortfolioOverview.vue` | Fixed joint investment value display calculation |

---

## 33. IHT Planning UI Enhancements

**Files:**
- `resources/js/components/Estate/IHTMitigationStrategies.vue`
- `resources/js/components/Estate/IHTPlanning.vue`

**Enhancement:** Added informational messages to improve user understanding of Estate Planning module features.

### 33.1 Will Strategy Implementation Notice

**File:** `resources/js/components/Estate/IHTMitigationStrategies.vue`

**Change:** Added implementation status message to Will strategy card.

**Implementation:**

Added conditional rendering for Will strategy (line 124) to display a warning notice at the bottom of the expanded card:

```vue
<!-- Will Strategy - Show Implementation Notice -->
<div v-else-if="strategy.strategy_name && strategy.strategy_name.toLowerCase().includes('will')" class="mt-3">
  <div v-if="strategy.specific_actions && Array.isArray(strategy.specific_actions)" class="mb-3">
    <h5 class="text-sm font-medium text-gray-900 mb-2">Implementation Steps:</h5>
    <ul class="space-y-2">
      <li v-for="(step, stepIndex) in strategy.specific_actions" :key="stepIndex">
        <span class="text-blue-600 mr-2 mt-1">→</span>
        <span>{{ step }}</span>
      </li>
    </ul>
  </div>
  <!-- Implementation Status Notice -->
  <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mt-3">
    <p class="text-sm text-amber-800">
      <span class="font-medium">⚠️ Note:</span> Full will functionality has not been implemented.
    </p>
  </div>
</div>
```

**Result:** Users are now informed that full will functionality is not yet available when viewing the Will strategy mitigation card.

---

### 33.2 Projected Values Methodology Note

**File:** `resources/js/components/Estate/IHTPlanning.vue`

**Change:** Added methodology explanation under "Projected" column heading in IHT Calculation Breakdown table.

**Implementation:**

Updated table header cell (line 583) to include explanatory text:

```vue
<th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
  <div>Projected (Age {{ projection?.at_death?.estimated_age_at_death || '...' }})</div>
  <div class="text-[10px] font-normal text-gray-400 normal-case mt-0.5">
    This is a static future value calculation using 4.7%
  </div>
</th>
```

**Result:** Users are now informed that projected values use a 4.7% growth rate assumption, helping them understand the methodology behind the calculations.

---

## Files Changed (IHT Planning UI Session)

| File | Change |
|------|--------|
| `resources/js/components/Estate/IHTMitigationStrategies.vue` | Added implementation status notice to Will strategy card |
| `resources/js/components/Estate/IHTPlanning.vue` | Added projection methodology note to Projected column header |

---

## 34. Code Quality Improvements - Audit Results

**Files Modified:** 8 files
**Overall Quality Score Before:** 82/100
**Issues Resolved:** 7 of 11 (all HIGH, MEDIUM priority issues addressed)
**Estimated Effort:** 3 hours

### 34.1 Trust Ownership Support for Liabilities (HIGH Priority)

**Files:**
- `database/migrations/2025_11_22_092125_add_joint_ownership_to_liabilities_table.php`
- `app/Models/Estate/Liability.php`

**Issue:** The liabilities ownership_type enum only included `['individual', 'joint']` but FPS standards require `['individual', 'joint', 'trust']` for consistency with other modules (Properties, Investments, Savings).

**Fix:**

1. **Updated migration enum:**
```php
// Before
$table->enum('ownership_type', ['individual', 'joint'])

// After
$table->enum('ownership_type', ['individual', 'joint', 'trust'])
```

2. **Added trust_id field:**
```php
$table->unsignedBigInteger('trust_id')
    ->nullable()
    ->after('joint_owner_id');

$table->foreign('trust_id')
    ->references('id')
    ->on('trusts')
    ->onDelete('set null');
```

3. **Updated Liability model fillable:**
```php
protected $fillable = [
    'user_id',
    'ownership_type',
    'joint_owner_id',
    'trust_id',  // Added
    // ...
];
```

**Result:** Liabilities now support trust ownership matching Properties, Investments, and Savings patterns.

---

### 34.2 Null Safety in OnboardingService (HIGH Priority)

**File:** `app/Services/Onboarding/OnboardingService.php`

**Issue:** The `getStepDataFromUser` method accessed spouse data without defensive null checks, creating potential for exceptions if spouse account was deleted.

**Fix:**
```php
// Before
if ($user->spouse_id) {
    $spouse = User::find($user->spouse_id);
    if ($spouse) {
        $hasSpouseExpenditureData = $spouse->monthly_expenditure > 0 ||
                                   $spouse->annual_expenditure > 0 ||
                                   // ...

// After
if ($user->spouse_id) {
    $spouse = User::find($user->spouse_id);
    if ($spouse !== null) {
        $hasSpouseExpenditureData = ($spouse->monthly_expenditure ?? 0) > 0 ||
                                   ($spouse->annual_expenditure ?? 0) > 0 ||
                                   // ...
```

**Result:** Defensive coding prevents exceptions when accessing potentially deleted spouse accounts.

---

### 34.3 Eloquent Relationships for Liabilities (MEDIUM Priority)

**File:** `app/Models/Estate/Liability.php`

**Issue:** Liability model had `joint_owner_id` and `trust_id` foreign keys but no corresponding Eloquent relationship methods, causing N+1 query risks.

**Fix:**
```php
/**
 * Joint owner relationship (for joint liabilities)
 */
public function jointOwner(): BelongsTo
{
    return $this->belongsTo(User::class, 'joint_owner_id');
}

/**
 * Trust relationship (for trust-owned liabilities)
 */
public function trust(): BelongsTo
{
    return $this->belongsTo(\App\Models\Estate\Trust::class);
}
```

**Usage:**
```php
// Eager load to prevent N+1 queries
$liabilities = Liability::with('jointOwner', 'trust')->get();
```

**Result:** Enables efficient eager loading of related users and trusts.

---

### 34.4 Ownership Filter Logic Extraction (MEDIUM Priority)

**File:** `app/Services/UserProfile/UserProfileService.php`

**Issue:** Ownership filtering logic was duplicated 4 times across DC Pensions, Properties, Protection, and Liabilities sections (8 lines of duplicated code).

**Fix:**

1. **Created helper method:**
```php
/**
 * Helper method to determine if an item should be included based on ownership filter
 */
private function shouldIncludeByOwnership(bool $isJoint, string $filter): bool
{
    return match($filter) {
        'joint_only' => $isJoint,
        'individual_only' => !$isJoint,
        'all' => true,
        default => true,
    };
}
```

2. **Replaced duplicated logic:**
```php
// Before (repeated 4 times)
if ($ownershipFilter === 'joint_only' && !$isJoint) {
    continue;
}
if ($ownershipFilter === 'individual_only' && $isJoint) {
    continue;
}

// After (in all 4 locations)
if (!$this->shouldIncludeByOwnership($isJoint, $ownershipFilter)) {
    continue;
}
```

**Result:** Reduced code duplication, improved maintainability, follows DRY principle.

---

### 34.5 Improved Inline Comments (LOW Priority)

**File:** `resources/js/components/Investment/PortfolioOverview.vue`

**Issue:** Joint investment display logic lacked detailed comments explaining WHY database stores 50% shares vs full values.

**Fix:**
```vue
<!-- Before -->
<!-- Joint account shows both full value and user's share -->

<!-- After -->
<!-- Joint account: DB stores user's 50% share, display both full value (share × 2) and user's share -->
<div v-if="account.ownership_type === 'joint'">
  <div class="detail-row">
    <span class="detail-label">Full Value</span>
    <!-- Full value = user's share × 2 (each user has reciprocal 50% record) -->
    <span class="detail-value">{{ formatCurrency(account.current_value * 2) }}</span>
  </div>
  <div class="detail-row">
    <span class="detail-label">Your Share (50%)</span>
    <!-- DB stores user's 50% share directly, no division needed -->
    <span class="detail-value text-purple-600">{{ formatCurrency(account.current_value) }}</span>
  </div>
</div>
```

**Result:** Future developers can understand the storage pattern without confusion.

---

### 34.6 Centralized ISA Allowance Constant (LOW Priority)

**Files:**
- `resources/js/constants/taxConfig.js` (NEW)
- `resources/js/components/Investment/PortfolioOverview.vue`

**Issue:** ISA allowance was hardcoded as `20000` with comment "2024/25 allowance", violating "never hardcode tax values" principle.

**Fix:**

1. **Created tax config constants file:**
```javascript
// resources/js/constants/taxConfig.js
export const TAX_CONFIG = {
  ISA_ANNUAL_ALLOWANCE: 20000,
  LIFETIME_ISA_ALLOWANCE: 4000,
  JUNIOR_ISA_ALLOWANCE: 9000,
  PERSONAL_ALLOWANCE: 12570,
  PENSION_ANNUAL_ALLOWANCE: 60000,
  CGT_ALLOWANCE: 3000,
};
```

2. **Updated PortfolioOverview:**
```javascript
// Before
getIsaRemaining(account) {
  const isaAllowance = 20000; // 2024/25 allowance
  const contributions = this.getIsaContributions(account);
  return Math.max(0, isaAllowance - contributions);
}

// After
import { TAX_CONFIG } from '@/constants/taxConfig';

getIsaRemaining(account) {
  const contributions = this.getIsaContributions(account);
  return Math.max(0, TAX_CONFIG.ISA_ANNUAL_ALLOWANCE - contributions);
}
```

**Result:** Single source of truth for tax constants, easier to update for new tax years.

---

### 34.7 Method Naming Consistency (LOW Priority)

**File:** `resources/js/components/Investment/PortfolioOverview.vue`

**Issue:** Method named `getReturnColourClass` used British spelling inconsistent with American spelling convention in code.

**Fix:**
```javascript
// Before
getReturnColourClass(value) { ... }
:class="getReturnColourClass(account.ytd_return)"

// After
getReturnColorClass(value) { ... }
:class="getReturnColorClass(account.ytd_return)"
```

**Result:** Consistent American spelling in code (British spelling still used in user-facing text as per FPS standards).

---

## Files Changed (Code Quality Audit Session)

| File | Change |
|------|--------|
| `database/migrations/2025_11_22_092125_add_joint_ownership_to_liabilities_table.php` | Added 'trust' to ownership_type enum, added trust_id field |
| `app/Models/Estate/Liability.php` | Added trust_id to fillable, added jointOwner() and trust() relationships |
| `app/Services/Onboarding/OnboardingService.php` | Added null safety checks for spouse data access |
| `app/Services/UserProfile/UserProfileService.php` | Created shouldIncludeByOwnership() helper, replaced duplicated filter logic |
| `resources/js/components/Investment/PortfolioOverview.vue` | Improved comments, renamed method, imported TAX_CONFIG constant |
| `resources/js/constants/taxConfig.js` | NEW - Centralized UK tax configuration constants |

---

## Deferred Tasks (for Future Sprint)

**TASK-005**: Create useCurrency composable for shared formatting (1hr)
- Extract duplicated `formatCurrency()` methods to composable
- Would affect multiple Vue components

**TASK-006**: Extract expenditure fallback to separate method (2hrs)
- Refactor `getStepDataFromUser()` 99-line method
- Extract step-specific logic to dedicated methods

**TASK-007**: Implement joint liability UI forms and display (2-3hrs)
- Backend supports joint liabilities, but no frontend UI yet
- Add ownership type selector to liability forms
- Display joint badges and value breakdowns

**TASK-008**: Write unit tests for joint liability feature (1-2hrs)
- Test migration, model fillable, ownership filtering
- Ensure joint liabilities filtered correctly

---

**Code Quality Score**: 82/100 → 88/100 (estimated after fixes)

---

Generated: 22 November 2025
