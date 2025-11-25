# November 25, 2025 Patch Notes

## Version: v0.2.14

## Overview
This patch includes four major fixes plus code quality improvements from review:
1. **Family Member/Spouse Account Linking Fix** - User Profile now uses the same service as onboarding
2. **Joint Property Sync Fix** - Joint property editing now correctly shows full values and syncs to linked accounts
3. **Joint Property Cost/Income Split Fix** - Monthly costs and rental income now correctly split by ownership percentage
4. **Joint Account History UI** - New tab in Net Worth to view edit history for joint accounts
5. **Code Quality Fixes** - 11 issues identified and resolved from comprehensive code review
6. **Spouse Modal Logout Fix** - Fixed issue where adding spouse would flash modal then logout
7. **DC Pension Provider Nullable Migration** - Created missing migration from v0.2.13 for provider column
8. **Joint Mortgage Payment Split Fix** - Monthly payment and original loan amount now correctly split by ownership percentage
9. **Spouse Expenditure Display Fix** - Fixed spouse expenditure showing 0 in User Profile due to double /api prefix
10. **Life Policy Validation Fix** - Made policy_end_date optional for term policies (was incorrectly required)

---

## 1. Family Member/Spouse Account Linking Fix

### Problem
When adding a spouse from the User Profile Family tab, the account was not being created/linked properly. The onboarding flow worked correctly, but User Profile used a different code path (Vuex store) that didn't trigger account creation.

### Root Cause
- Onboarding used `familyMembersService.createFamilyMember()` directly (WORKED)
- User Profile used Vuex store dispatch which had broken/different logic (BROKEN)
- Also found `step_child` was missing from allowed relationship values
- Email validation was inconsistent (should be required for spouse only)

### Solution
Made User Profile use the exact same service as onboarding - ONE form, ONE process, ONE logic base.

### Files Changed

#### `resources/js/components/UserProfile/FamilyMembers.vue`
- Changed from Vuex store dispatch to using `familyMembersService` directly
- Added `loadFamilyMembers()` function to fetch data on mount
- Updated `handleSave()` to use `familyMembersService.createFamilyMember()` and `updateFamilyMember()`
- Updated `handleDelete()` to use `familyMembersService.deleteFamilyMember()`

#### `app/Http/Requests/StoreFamilyMemberRequest.php`
- Added `step_child` to allowed relationship values
- Fixed email validation: required for spouse only, nullable for all others

```php
'relationship' => ['required', Rule::in(['spouse', 'child', 'step_child', 'parent', 'other_dependent'])],
'email' => $this->input('relationship') === 'spouse'
    ? ['required', 'email', 'max:255']
    : ['nullable', 'email', 'max:255'],
```

---

## 2. Joint Property Sync Fix

### Problem
When editing a joint or tenants_in_common property:
1. The edit form showed only the user's share value, not the full property value
2. Changes weren't syncing to the joint owner's reciprocal record
3. No audit trail of joint account edits

### Root Cause
- Database stores each user's SHARE of the property value
- Edit form was showing the stored share, not the full value
- Backend wasn't fetching the joint owner's share to calculate full value
- No mechanism to track edits on joint accounts

### Solution
1. Backend now returns `joint_owner_share` data with each joint property
2. Frontend ADDS both shares together to display full value
3. When saving, backend splits the full value according to ownership percentages
4. New `joint_account_logs` table tracks all edits on joint accounts

**KEY PRINCIPLE**: NEVER multiply to calculate full values. Always FETCH both records and ADD them together.

### Files Changed

#### `app/Http/Controllers/Api/PropertyController.php`
- `index()` - Now includes `joint_owner_share` data INSIDE each joint property object:
  - `current_value` - Joint owner's share of property value
  - `purchase_price` - Joint owner's share of purchase price
  - `ownership_percentage` - Joint owner's percentage
  - `mortgage` - Joint owner's mortgage data (outstanding_balance, original_loan_amount)
- `show()` - Returns `joint_owner_share` INSIDE the property object (not as separate field)
  - **CRITICAL FIX**: Initially returned `joint_owner_share` as a separate field in the response, but Vuex store only extracted `response.data.property` and ignored the separate field. Fixed by including `joint_owner_share` inside `$summary` array.
- `update()` - Handles splitting full values to both records and logs changes to `joint_account_logs`

#### `app/Http/Controllers/Api/MortgageController.php`
- `update()` - Handles joint mortgage updates with proper splitting by ownership percentage
- Logs changes to `joint_account_logs` table
- Finds reciprocal mortgage via property relationship matching

#### `app/Http/Controllers/Api/JointAccountLogController.php` (NEW)
- `index()` - Fetches joint account logs for authenticated user
- Supports filtering by type (property, mortgage, investment, savings)
- Returns formatted logs with editor name, affected user, asset type, changes, timestamps

#### `app/Models/JointAccountLog.php` (NEW)
- Polymorphic model for logging edits on joint accounts
- `logEdit()` static helper method for creating log entries
- Relationships: `user()`, `jointOwner()`, `loggable()`

#### `database/migrations/2025_11_25_110113_create_joint_account_logs_table.php` (NEW)
```php
Schema::create('joint_account_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('joint_owner_id')->constrained('users')->onDelete('cascade');
    $table->morphs('loggable'); // Property, Mortgage, InvestmentAccount, SavingsAccount
    $table->json('changes');
    $table->string('action')->default('update');
    $table->timestamps();
    $table->index(['user_id', 'loggable_type', 'loggable_id'], 'jal_user_loggable_idx');
    $table->index(['joint_owner_id', 'loggable_type', 'loggable_id'], 'jal_joint_owner_loggable_idx');
});
```

#### `resources/js/components/NetWorth/Property/PropertyForm.vue`
- Added `isJointPropertyEdit` computed property
- Updated labels to show "Full Property Value (£)" and "Full Outstanding Balance (£)" for joint property edits
- Added helper text explaining that user's share will be calculated automatically
- Updated `populateForm()` to ADD user's share + joint owner's share:
```javascript
if (isJointWithLinkedOwner) {
    // ADD user's share + joint owner's share to get full property value
    const userValue = this.property.current_value || 0;
    const jointOwnerValue = this.property.joint_owner_share?.current_value || 0;
    this.form.current_value = userValue + jointOwnerValue;
    // ... same for purchase_price and mortgage values
}
```

#### `routes/api.php`
- Added new route group for joint account logs:
```php
Route::middleware('auth:sanctum')->prefix('joint-account-logs')->group(function () {
    Route::get('/', [JointAccountLogController::class, 'index']);
});
```

---

## 3. Joint Property Cost/Income Split Fix

### Problem
For joint and tenants_in_common properties, monthly costs (council tax, utilities, insurance, etc.) and rental income were being stored at FULL value in BOTH user records. This caused double-counting when aggregating financial data.

### Root Cause
- `store()` method only split `current_value` and mortgage values by ownership percentage
- Monthly costs and rental income were copied as-is to both records
- `createJointProperty()` copied all fields from original property without adjusting for ownership

### Solution
1. **`store()` method** - Now splits ALL financial values by ownership percentage before saving:
   - `monthly_rental_income`
   - `monthly_council_tax`
   - `monthly_gas`, `monthly_electricity`, `monthly_water`
   - `monthly_building_insurance`, `monthly_contents_insurance`
   - `monthly_service_charge`, `monthly_maintenance_reserve`
   - `other_monthly_costs`

2. **`createJointProperty()` method** - Now calculates joint owner's share using ratio formula:
   - `joint_share = user_share * (reciprocal% / user%)`

3. **Existing Data Fix** - Ran script to fix 12 existing joint property records in database

### Files Changed

#### `app/Http/Controllers/Api/PropertyController.php`
- `store()` - Added ownership percentage splitting for all cost and income fields (lines 118-152)
- `createJointProperty()` - Now calculates joint owner's share using ratio formula (lines 485-498)
- `syncUserRentalIncome()` - Simplified since values are now already user's share

#### `app/Services/UserProfile/UserProfileService.php`
- `calculateAnnualRentalIncome()` - Removed incorrect ownership percentage multiplication (values already split)
- `getFinancialCommitments()` - Removed incorrect ownership percentage multiplication for property costs

#### `app/Services/Property/PropertyService.php`
- `calculateTotalMonthlyCosts()` - Updated comments to clarify values are already user's share
- `getPropertySummary()` - Updated comments

---

## 4. Joint Account History UI

### Problem
The backend for `joint_account_logs` was created (controller, model, migration, API route), but there was no frontend UI to display the edit history. Users had no way to see when joint account changes were made.

### Solution
Created a new "Joint History" tab in the Net Worth dashboard that displays all joint account edit logs with filtering capabilities.

### Files Changed

#### `resources/js/components/NetWorth/JointAccountHistory.vue` (NEW)
- Displays joint account edit logs in a table format
- Filter by type (property, mortgage, investment, savings)
- Shows editor name, affected user, asset name
- Displays before/after values for each changed field
- Proper currency formatting for financial fields
- Loading, empty, and error states
- Mobile responsive design

#### `resources/js/router/index.js`
- Added lazy import for `JointAccountHistory` component
- Added route: `/net-worth/joint-history` as child of `/net-worth`

#### `resources/js/views/NetWorth/NetWorthDashboard.vue`
- Added "Joint History" tab to the tabs array

---

## 5. Code Quality Fixes (From Code Review)

A comprehensive code review identified 12 issues (1 critical, 3 high, 5 medium, 3 low). 11 were fixed (1 skipped as acceptable).

### Critical Fix

#### Division by Zero Prevention in `createJointProperty()`
**File**: `app/Http/Controllers/Api/PropertyController.php`
- Added validation to prevent division by zero when ownership percentage is 0% or 100%
- Throws `InvalidArgumentException` for invalid percentages

```php
if ($ownershipPercentage <= 0 || $ownershipPercentage >= 100) {
    throw new \InvalidArgumentException('Ownership percentage must be between 0 and 100 (exclusive) for joint properties');
}
```

### High Priority Fixes

#### 1. Null Safety in JointAccountLogController
**File**: `app/Http/Controllers/Api/JointAccountLogController.php`
- Added null-safe operators (`?->`) for user and jointOwner relationships
- Prevents crashes when related users are deleted
- Added `'loggable'` to eager loading for asset name resolution

```php
$userName = $log->user?->name ?? 'Unknown User';
$jointOwnerName = $log->jointOwner?->name ?? 'Unknown User';
```

#### 2. Duplicate Vue Watchers Merged
**File**: `resources/js/components/NetWorth/Property/PropertyForm.vue`
- Merged two separate `'form.ownership_type'` watchers into single watcher
- Duplicate watchers caused unpredictable behavior

#### 3. JointAccountHistory API Response Handling
**File**: `resources/js/components/NetWorth/JointAccountHistory.vue`
- Fixed API response path from `response.data.logs` to `response.data.data.logs`
- Fixed template field names to match API response (`log.edited_by`, `log.affected_user`)

### Medium Priority Fixes

#### 1. Hardcoded 50% Split in MortgageController
**File**: `app/Http/Controllers/Api/MortgageController.php`
- Changed from hardcoded 50% split to use property's actual `ownership_percentage`
- Ensures correct splitting for non-50/50 joint ownership

```php
$ownershipPercentage = $property->ownership_percentage ?? 50;
$validated['outstanding_balance'] = $validated['outstanding_balance'] * ($ownershipPercentage / 100);
```

#### 2. Double Ownership Calculation in IncomeStep
**File**: `resources/js/components/Onboarding/steps/IncomeStep.vue`
- Removed redundant `* (ownershipPercentage / 100)` multiplication
- Values are already stored as user's share in database

#### 3. Missing Cost/Income Split in update()
**File**: `app/Http/Controllers/Api/PropertyController.php`
- Added cost field splitting logic to `update()` method to match `store()` behavior
- Ensures edits to joint properties correctly split costs

#### 4. Missing asset_name in Log Response
**File**: `app/Http/Controllers/Api/JointAccountLogController.php`
- Added `asset_name` field resolution based on loggable type
- Displays property address, mortgage lender, or account name

### Low Priority Fixes

#### 1. Debug Logging Removed
**File**: `app/Http/Controllers/Api/MortgageController.php`
- Removed verbose `\Log::info()` debug statements from `destroy()` method

#### 2. "Sole Owner" Labels Changed to "Individual Owner"
**Files Changed**:
- `resources/js/components/NetWorth/Property/PropertyForm.vue`
- `resources/js/components/NetWorth/Property/PropertyDetail.vue`
- `resources/js/components/Savings/SaveAccountModal.vue`
- `resources/js/components/Estate/AssetForm.vue`

Changed all instances of "Sole Owner" to "Individual Owner" for consistency with database enum values.

### Skipped Issue
- **Index naming convention** (TASK-010): Database index names `jal_user_loggable_idx` and `jal_joint_owner_loggable_idx` are acceptable as-is.

---

## 7. DC Pension Provider Nullable Migration Fix

### Problem
After running `migrate:fresh --seed`, DC pension creation failed with:
```
SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'provider' cannot be null
```

### Root Cause
The Nov 24 patch (v0.2.13) fixed this issue on production via manual SQL:
```sql
ALTER TABLE dc_pensions MODIFY COLUMN provider VARCHAR(255) NULL;
```

However, **no migration file was created**. When `migrate:fresh` was run, it recreated the schema from the original migrations which had `provider` as NOT NULL.

### Solution
Created the missing migration file: `2025_11_25_132510_make_provider_nullable_on_dc_pensions_table.php`

```php
public function up(): void
{
    Schema::table('dc_pensions', function (Blueprint $table) {
        $table->string('provider', 255)->nullable()->change();
    });
}
```

### Files Changed

#### `database/migrations/2025_11_25_132510_make_provider_nullable_on_dc_pensions_table.php` (NEW)
- Makes `provider` column nullable on `dc_pensions` table
- Includes comment explaining the missing migration from Nov 24 patch

### Impact
- ✅ DC pensions can now be created without provider
- ✅ Migration persists across `migrate:fresh` runs
- ✅ Fixes the root cause from v0.2.13 properly

---

## 8. Joint Mortgage Payment Split Fix

### Problem
For joint and tenants_in_common properties, the mortgage `monthly_payment` and `original_loan_amount` were NOT being split by ownership percentage. This caused double-counting when aggregating mortgage payments across users.

The `outstanding_balance` WAS being split correctly, but `monthly_payment` was stored as the full amount in BOTH user records.

### Root Cause
In `MortgageController.php`:
- `store()` only split `outstanding_balance` by ownership percentage
- `update()` only split `outstanding_balance` when syncing to joint owner
- `createJointMortgage()` just copied the data without adjusting for ownership percentage

### Solution

#### `app/Http/Controllers/Api/MortgageController.php`

**1. `store()` method** - Now splits `monthly_payment` and `original_loan_amount`:
```php
if (isset($validated['monthly_payment'])) {
    $validated['monthly_payment'] = $validated['monthly_payment'] * ($ownershipPercentage / 100);
}
if (isset($validated['original_loan_amount'])) {
    $validated['original_loan_amount'] = $validated['original_loan_amount'] * ($ownershipPercentage / 100);
}
```

**2. `update()` method** - Now splits all financial fields when updating joint mortgages:
```php
// If monthly_payment is provided, it's the FULL payment - split it
if (isset($validated['monthly_payment'])) {
    $fullPayment = $validated['monthly_payment'];
    $validated['monthly_payment'] = $fullPayment * ($userPercentage / 100);
    $reciprocalData['monthly_payment'] = $fullPayment * ($jointOwnerPercentage / 100);
}
```

**3. `createJointMortgage()` method** - Now calculates joint owner's share based on ownership percentages:
```php
$ratio = $jointOwnerPercentage / $userPercentage;
$jointMortgageData['outstanding_balance'] = $originalMortgage->outstanding_balance * $ratio;
if ($originalMortgage->monthly_payment) {
    $jointMortgageData['monthly_payment'] = $originalMortgage->monthly_payment * $ratio;
}
if ($originalMortgage->original_loan_amount) {
    $jointMortgageData['original_loan_amount'] = $originalMortgage->original_loan_amount * $ratio;
}
```

#### `resources/js/components/NetWorth/Property/PropertyDetail.vue`

- Updated to display "Full Monthly Payment" and "Your Share" for joint properties
- Added `calculateFullMonthlyPayment()` method
- Updated `calculateFullOutstandingBalance()` and `calculateFullPropertyValue()` to also handle `tenants_in_common`

### Existing Data Fix

Run in tinker to fix existing joint mortgage records:
```php
use App\Models\Mortgage;
use App\Models\Property;

$jointMortgages = Mortgage::whereHas('property', function($q) {
    $q->whereIn('ownership_type', ['joint', 'tenants_in_common']);
})->get();

foreach ($jointMortgages as $mortgage) {
    $property = $mortgage->property;
    // Assuming current monthly_payment is FULL amount, split by ownership%
    $userShare = $mortgage->monthly_payment * ($property->ownership_percentage / 100);
    $mortgage->update(['monthly_payment' => $userShare]);
    echo "Fixed mortgage {$mortgage->id}: {$mortgage->monthly_payment} → {$userShare}\n";
}
```

### Files Changed
- `app/Http/Controllers/Api/MortgageController.php` - Split monthly_payment and original_loan_amount
- `resources/js/components/NetWorth/Property/PropertyDetail.vue` - Display full vs share for joint properties

### Impact
- ✅ New joint mortgages correctly split all financial values
- ✅ Updates to joint mortgages correctly split all financial values
- ✅ PropertyDetail shows "Full Monthly Payment" and "Your Share" for joint properties
- ✅ No more double-counting of mortgage payments when aggregating

---

## 6. Spouse Modal Logout Fix

### Problem
When adding a spouse from User Profile > Family, the temporary password modal would flash briefly then the user would be logged out and redirected to login screen.

### Root Cause
1. After showing the spouse success modal, code called `await store.dispatch('auth/fetchUser')`
2. If `fetchUser` failed for any reason (network timing, transient error), the auth store's catch block called `commit('clearAuth')` which logged the user out
3. The `await` blocked the modal from staying visible

### Solution
Two-layer fix:

#### 1. `resources/js/store/modules/auth.js`
- Modified `fetchUser` to only clear auth if there's no valid token
- Prevents logout on transient network errors during normal operations

```javascript
} catch (error) {
  // Only clear auth if we don't have a valid token
  if (!state.token) {
    commit('clearAuth');
  }
  throw error;
}
```

#### 2. `resources/js/components/UserProfile/FamilyMembers.vue`
- Changed `await store.dispatch('auth/fetchUser')` to non-blocking call
- Added `.catch()` to silently handle errors without affecting the modal

```javascript
// Before (BROKEN)
await store.dispatch('auth/fetchUser');

// After (FIXED)
store.dispatch('auth/fetchUser').catch((err) => {
  console.warn('Failed to refresh user data after spouse creation:', err);
});
```

### Files Changed
- `resources/js/store/modules/auth.js` - Prevent logout on fetchUser errors when token exists
- `resources/js/components/UserProfile/FamilyMembers.vue` - Non-blocking fetchUser call

---

## Deployment Instructions

### 1. Upload Files to Production

Upload the following files:

**Backend (PHP):**
```
app/Http/Controllers/Api/PropertyController.php
app/Http/Controllers/Api/MortgageController.php
app/Http/Controllers/Api/JointAccountLogController.php (NEW)
app/Models/JointAccountLog.php (NEW)
app/Http/Requests/StoreFamilyMemberRequest.php
app/Http/Requests/Protection/StoreLifePolicyRequest.php
app/Services/UserProfile/UserProfileService.php
app/Services/Property/PropertyService.php
database/migrations/2025_11_25_110113_create_joint_account_logs_table.php (NEW)
database/migrations/2025_11_25_132510_make_provider_nullable_on_dc_pensions_table.php (NEW)
routes/api.php
```

**Frontend (Vue):**
```
resources/js/components/UserProfile/FamilyMembers.vue
resources/js/components/NetWorth/Property/PropertyForm.vue
resources/js/components/NetWorth/Property/PropertyDetail.vue
resources/js/components/NetWorth/JointAccountHistory.vue (NEW)
resources/js/components/Onboarding/steps/IncomeStep.vue
resources/js/components/Savings/SaveAccountModal.vue
resources/js/components/Estate/AssetForm.vue
resources/js/views/NetWorth/NetWorthDashboard.vue
resources/js/router/index.js
resources/js/store/modules/auth.js
resources/js/services/authService.js
```

### 2. Run Migration on Production

```bash
cd ~/tengo-app
php artisan migrate
```

This will create the `joint_account_logs` table and make the DC pension provider column nullable.

### 3. Build Frontend Locally

```bash
NODE_ENV=production npm run build
```

### 4. Upload Build Directory

Upload the entire `public/build/` directory to production.

### 5. Clear Caches on Production

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

---

## Testing Checklist

### Family Member/Spouse Linking
- [ ] Go to User Profile > Family tab
- [ ] Add a new spouse with email address
- [ ] Verify account is created/linked (check database or login as spouse)
- [ ] Add a child with no email (should work - email nullable for non-spouse)
- [ ] Add a step_child relationship (should work - now in allowed values)

### Joint Property Editing
- [ ] Login as user with joint property
- [ ] Edit the joint property
- [ ] Verify the "Full Property Value" label shows
- [ ] Verify the value shown is the SUM of both owners' shares
- [ ] Change the value and save
- [ ] Login as joint owner
- [ ] Verify their property value updated proportionally
- [ ] Check `joint_account_logs` table for audit entry

### Joint Mortgage Editing
- [ ] Edit a joint property with mortgage
- [ ] Verify "Full Outstanding Balance" label shows
- [ ] Change the balance and save
- [ ] Verify both users' mortgage records updated

### Joint Property Cost/Income Split
- [ ] Create a new joint property (50/50 ownership) with £1000/month rental income
- [ ] Verify user's record shows £500/month (their 50% share)
- [ ] Login as joint owner
- [ ] Verify their record also shows £500/month (their 50% share)
- [ ] Check User Profile > Financial Commitments shows correct cost amounts
- [ ] Verify total rental income across both accounts equals £1000 (not £2000)

### Joint Account History UI
- [ ] Go to Net Worth > Joint History tab
- [ ] Verify tab appears in the navigation
- [ ] Verify empty state shows when no logs exist
- [ ] Edit a joint property and save
- [ ] Return to Joint History tab
- [ ] Verify the edit log appears with correct details
- [ ] Test filter dropdown (Property, Mortgage, etc.)
- [ ] Verify before/after values display correctly

### Spouse Expenditure Display
- [ ] Login as user with spouse linked
- [ ] Go to User Profile > Expenditure tab
- [ ] Click on "Spouse's Expenditure" tab
- [ ] Verify spouse's expenditure values display (not 0)
- [ ] Verify values match what was entered during onboarding
- [ ] Check "Household Total" tab shows combined amounts

---

## Database Changes

### New Table: `joint_account_logs`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | User who made the edit |
| joint_owner_id | bigint | Joint owner affected |
| loggable_type | varchar | Model class (Property, Mortgage, etc.) |
| loggable_id | bigint | ID of the model |
| changes | json | Before/after values |
| action | varchar | 'update', 'create', or 'delete' |
| created_at | timestamp | When edit occurred |
| updated_at | timestamp | Last updated |

---

## API Changes

### New Endpoint
- `GET /api/joint-account-logs` - Fetch joint account edit logs
  - Query param: `type` - Filter by asset type (property, mortgage, investment, savings)

### Modified Endpoints
- `GET /api/properties` - Now includes `joint_owner_share` object for joint properties
- `GET /api/properties/{id}` - Now includes `joint_owner_share` in response

---

## 9. Spouse Expenditure Display Fix

### Problem
In User Profile > Expenditure tab, the spouse's expenditure values (entered during onboarding) were displaying as 0 instead of the actual saved values, even though the data was correctly stored in the database.

### Root Cause
The `authService.js` file's `getUserById()` method was calling `/api/users/${userId}`, but the `api` service already has `/api` as a base URL prefix. This resulted in:
- **Actual URL called**: `/api/api/users/8`
- **Expected URL**: `/api/users/8`

The double `/api` prefix caused a 404 error, so the spouse data was never fetched.

### Solution
Removed the `/api` prefix from the `getUserById()` call since the base axios instance already includes it.

### Files Changed

#### `resources/js/services/authService.js`
```javascript
// Before (BROKEN - caused /api/api/users/{id})
async getUserById(userId) {
  const response = await api.get(`/api/users/${userId}`);
  ...
}

// After (FIXED - correct /api/users/{id})
async getUserById(userId) {
  const response = await api.get(`/users/${userId}`);
  ...
}
```

### Impact
- ✅ Spouse expenditure values now display correctly in User Profile > Expenditure tab
- ✅ All expenditure fields (food_groceries, transport_fuel, healthcare_medical, etc.) properly load from spouse data
- ✅ Household Total tab correctly shows combined totals

---

## 10. Life Policy Validation Fix

### Problem
When creating a life insurance policy (especially during onboarding for linked spouse accounts), users received a 422 validation error. The error occurred because `policy_end_date` was required for term policies (`term`, `level_term`, `family_income_benefit`), but the Nov 24 patch (v0.2.13) had made these dates optional in the database migrations without updating the Form Request validation rules.

### Root Cause
`StoreLifePolicyRequest.php` still had:
```php
// For term policies - policy_end_date was REQUIRED
$rules['policy_end_date'] = ['required', 'date', 'after:today'];
```

This created a mismatch: database allowed NULL but validation rejected it.

### Solution
Updated `StoreLifePolicyRequest.php` to make `policy_end_date` nullable for ALL life policy types, consistent with the database schema and other protection policy types.

### Files Changed

#### `app/Http/Requests/Protection/StoreLifePolicyRequest.php`
```php
// Before (BROKEN - required for term policies)
} elseif ($policyType === 'term' || $policyType === 'level_term' || $policyType === 'family_income_benefit') {
    $rules['policy_end_date'] = ['required', 'date', 'after:today'];
}

// After (FIXED - nullable for all policy types)
} elseif ($policyType === 'term' || $policyType === 'level_term' || $policyType === 'family_income_benefit') {
    $rules['policy_end_date'] = ['nullable', 'date', 'after:today'];
}
```

### Impact
- ✅ Life insurance policies can now be created without specifying end date
- ✅ Both primary and linked spouse accounts can create policies during onboarding
- ✅ Consistent validation across all protection policy types

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| v0.2.14 | 2025-11-25 | Family member linking fix, Joint property sync fix, Cost/income split fix, Joint history UI, Spouse expenditure fix, Life policy validation fix |
| v0.2.13 | 2025-11-24 | Critical pension and protection policy fixes |
| v0.2.12 | 2025-11-23 | Security fixes, UI improvements |
