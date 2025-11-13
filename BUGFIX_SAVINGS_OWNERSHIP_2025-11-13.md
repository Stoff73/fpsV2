# Bug Fix Report: Savings Account Ownership Fields

**Date**: November 13, 2025
**Status**: ✅ Fixed and Deployed
**Severity**: Critical (blocked onboarding)
**Affected Version**: v0.2.7 (production)

---

## Issue Summary

Users experienced form freeze and 500/422 errors when trying to add cash savings accounts during onboarding. The form would not submit and console showed validation errors.

---

## User Report

**Location**: Onboarding → Assets & Wealth → Cash tab
**Action**: Clicked "Add Account" after filling form
**Result**: Form froze, did not close

**Console Errors**:
```javascript
Failed to load resource: the server responded with a status of 500 ()
// After first fix:
Failed to load resource: the server responded with a status of 422 ()
[API] 422 Validation: The given data was invalid.
```

---

## Root Cause Analysis (Systematic Debugging)

### Phase 1: Investigation

**Error Type**: Initially HTTP 500, then HTTP 422 after partial fix

**Database Investigation**:
- ✅ Migration `2025_10_21_100607_add_joint_ownership_to_assets_tables.php` added `joint_owner_id` to `savings_accounts` table (line 27-30)
- ✅ Migration `2025_10_21_085212_add_ownership_fields_to_savings_accounts_table.php` added `ownership_type` and `ownership_percentage` fields
- ✅ Database schema was correct

**Code Investigation**:
- ❌ `app/Models/SavingsAccount.php` - `$fillable` array **MISSING** ownership fields
- ❌ `app/Http/Requests/Savings/StoreSavingsAccountRequest.php` - validation made `ownership_percentage` **required** but frontend doesn't send it
- ✅ `app/Http/Controllers/Api/SavingsController.php` - controller expected these fields

### Phase 2: Pattern Analysis

**Working Example (Properties)**:
- ✅ Property model includes all ownership fields in `$fillable`
- ✅ PropertyController uses ownership fields correctly
- ✅ Ownership percentage has database default of 100.00

**Broken Example (Savings)**:
- ❌ SavingsAccount model missing ownership fields from `$fillable`
- ❌ Validation required a field the frontend doesn't send
- ✅ Controller logic was correct but couldn't execute

### Root Cause

**Two Issues**:

1. **Laravel Mass Assignment Protection**: The `SavingsAccount` model's `$fillable` array did not include ownership fields, causing Laravel to silently ignore them during `SavingsAccount::create($data)`

2. **Validation Mismatch**: Made `ownership_percentage` required in validation, but the frontend form doesn't send this field (expected controller to set defaults)

---

## Fix Applied

### Issue #1: Missing Fillable Fields

**File**: `app/Models/SavingsAccount.php`

**Change**:
```php
protected $fillable = [
    'user_id',
    'account_type',
    'institution',
    'account_number',
    'current_balance',
    'interest_rate',
    'access_type',
    'notice_period_days',
    'maturity_date',
    'is_emergency_fund',
    'is_isa',
    'country',
    'isa_type',
    'isa_subscription_year',
    'isa_subscription_amount',
    // ADD: Ownership fields
    'ownership_type',
    'ownership_percentage',
    'joint_owner_id',
    'trust_id',
];
```

### Issue #2: Validation & Default Logic

**File**: `app/Http/Requests/Savings/StoreSavingsAccountRequest.php`

**Change**:
```php
// Ownership
'ownership_type' => ['required', Rule::in(['individual', 'joint', 'trust'])],
'ownership_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'], // Changed from 'required'
'joint_owner_id' => ['nullable', 'required_if:ownership_type,joint', 'exists:users,id'],
'trust_id' => ['nullable', 'required_if:ownership_type,trust', 'exists:trusts,id'],
```

**File**: `app/Http/Controllers/Api/SavingsController.php`

**Change** (added default logic):
```php
// Set default ownership type if not provided
$data['ownership_type'] = $data['ownership_type'] ?? 'individual';

// Set default ownership percentage if not provided
if (! isset($data['ownership_percentage'])) {
    $data['ownership_percentage'] = 100.00;
}

// For joint ownership, default to 50/50 split if not specified or 100
if ($data['ownership_type'] === 'joint' && $data['ownership_percentage'] == 100.00) {
    $data['ownership_percentage'] = 50.00;
}

// Split the current_balance based on ownership percentage
$totalBalance = $data['current_balance'];
$userOwnershipPercentage = $data['ownership_percentage'];
$data['current_balance'] = $totalBalance * ($userOwnershipPercentage / 100);
```

---

## Files Changed

1. `app/Models/SavingsAccount.php` - Added 4 ownership fields to `$fillable`
2. `app/Http/Requests/Savings/StoreSavingsAccountRequest.php` - Changed `ownership_percentage` from required to nullable
3. `app/Http/Controllers/Api/SavingsController.php` - Added default percentage logic

**Git Commits**:
- `c6fb1ab` - fix: Add missing ownership fields to SavingsAccount model
- `f6e5b3a` - fix: Make ownership_percentage optional with proper defaults

---

## Deployment Process

### Method Used: SFTP Upload

Since SSH key authentication wasn't configured, used SFTP to upload fixed files:

```bash
# From local machine
sftp -P 18765 u163-ptanegf9edny@csjones.co

# Upload controller
cd www/csjones.co/tengo-app/app/Http/Controllers/Api
put app/Http/Controllers/Api/SavingsController.php

# Upload validation request
cd ../../Requests/Savings
put app/Http/Requests/Savings/StoreSavingsAccountRequest.php

# Upload model
cd ../../../Models
put app/Models/SavingsAccount.php

quit
```

**Cache Clearing** (via SSH):
```bash
cd ~/www/csjones.co/tengo-app
php artisan config:clear
php artisan route:clear
```

---

## Testing & Verification

**Test Steps**:
1. Navigate to https://csjones.co/tengo/
2. Go to Onboarding → Assets & Wealth → Cash tab
3. Fill in savings account form (any ownership type)
4. Click "Add Account"

**Expected Result**: ✅ Form saves successfully, closes, account appears in list

**Actual Result**: ✅ Works as expected - bug fixed

---

## Prevention for Future

### Code Review Checklist

When adding ownership fields to new models:

- [ ] Database migration includes: `ownership_type`, `ownership_percentage`, `joint_owner_id`, `trust_id`
- [ ] Model `$fillable` array includes ALL ownership fields
- [ ] Validation rules make `ownership_percentage` **nullable** (not required)
- [ ] Controller sets sensible defaults when field not provided
- [ ] Test with frontend form to ensure validation passes

### Pattern to Follow

Use the **Property model** as the reference implementation for ownership:
- See: `app/Models/Property.php` lines 7-40
- See: `app/Http/Controllers/Api/PropertyController.php`

---

## Related Issues

This pattern should be checked on other asset models:
- ✅ Properties - Working correctly
- ✅ Savings Accounts - Fixed
- ⚠️ Investment Accounts - Should verify
- ⚠️ Business Interests - Should verify
- ⚠️ Chattels - Should verify

**Action**: Review all models that support joint ownership to ensure `$fillable` includes ownership fields.

---

## Lessons Learned

1. **Mass Assignment Protection**: When adding new database columns, ALWAYS update model `$fillable` array
2. **Frontend/Backend Contract**: If frontend doesn't send a field, validation must be `nullable` with controller defaults
3. **Pattern Consistency**: When one module (Properties) works, use it as reference for others (Savings)
4. **Systematic Debugging**: Following the 4-phase process revealed root cause quickly vs. random fixes

---

## References

- Systematic Debugging Skill: `.claude/skills/systematic-debugging`
- Property Model Reference: `app/Models/Property.php`
- Migration: `database/migrations/2025_10_21_100607_add_joint_ownership_to_assets_tables.php`

---

**Status**: ✅ **RESOLVED** - Production deployed and verified working

**Documented By**: Claude Code (Anthropic)
**Date**: November 13, 2025

🤖 Generated with [Claude Code](https://claude.com/claude-code)
