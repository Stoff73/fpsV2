# Outstanding Mortgage Issue - Joint Ownership

**Date:** November 13-14, 2025
**Priority:** CRITICAL
**Status:** ✅ RESOLVED - November 14, 2025

---

## Problem Summary

When creating a joint property with a mortgage, only **ONE** mortgage record is being created in the database instead of **TWO** (one for each spouse).

### Current Behavior

- ✅ **Properties:** TWO property records created correctly (one per spouse)
- ✅ **Property Joint Ownership Fields:** Correctly populated with `joint_owner_id`, `ownership_type = 'joint'`
- ❌ **Mortgages:** Only ONE mortgage record created (for the user who created the property)
- ❌ **Reciprocal Mortgage:** NOT being created for the joint owner

### Expected Behavior

For a £200,000 mortgage on a joint property with 50/50 split:

**User 1 (Chris) - Mortgage Record:**
- `property_id` → Chris's property record ID
- `user_id` → Chris's user ID
- `original_loan_amount` → 0.00 (not provided in property form)
- `outstanding_balance` → £100,000 (50% share)
- `ownership_type` → 'joint'
- `joint_owner_id` → Angela's user ID
- `joint_owner_name` → 'Angela Slater-Jones'

**User 2 (Angela) - Reciprocal Mortgage Record:**
- `property_id` → Angela's property record ID
- `user_id` → Angela's user ID
- `original_loan_amount` → 0.00 (not provided in property form)
- `outstanding_balance` → £100,000 (50% share)
- `ownership_type` → 'joint'
- `joint_owner_id` → Chris's user ID
- `joint_owner_name` → 'Chris Slater-Jones'

---

## Code Investigation

### PropertyController.php - store() method (Lines 86-115)

The first mortgage IS being created correctly:

```php
// If outstanding_mortgage provided, auto-create a basic mortgage record
if (isset($validated['outstanding_mortgage']) && $validated['outstanding_mortgage'] > 0) {
    $userMortgageBalance = $validated['outstanding_mortgage'] * ($userOwnershipPercentage / 100);

    $mortgageData = [
        'property_id' => $property->id,
        'user_id' => $user->id,
        'original_loan_amount' => 0.00,
        'outstanding_balance' => $userMortgageBalance,
        'ownership_type' => $validated['ownership_type'],
        'joint_owner_id' => $validated['joint_owner_id'],
        'joint_owner_name' => $jointOwner->name,
        // ... other fields
    ];

    \App\Models\Mortgage::create($mortgageData);
}
```

✅ This creates the first mortgage successfully.

### PropertyController.php - createJointProperty() method (Lines 337-358)

The reciprocal mortgage SHOULD be created here:

```php
private function createJointProperty(Property $originalProperty, int $jointOwnerId, float $ownershipPercentage, float $totalValue, float $totalMortgage = 0): void
{
    // ... property creation code (WORKS) ...

    // If there's a mortgage, create joint owner's share
    if ($totalMortgage > 0) {
        $jointMortgageBalance = $totalMortgage * ($reciprocalPercentage / 100);

        \App\Models\Mortgage::create([
            'property_id' => $jointProperty->id,
            'user_id' => $jointOwnerId,
            'original_loan_amount' => 0.00,
            'outstanding_balance' => $jointMortgageBalance,
            'ownership_type' => 'joint',
            'joint_owner_id' => $originalProperty->user_id,
            'joint_owner_name' => \App\Models\User::find($originalProperty->user_id)->name ?? null,
            // ... other fields
        ]);
    }
}
```

❌ This code is NOT executing or SILENTLY FAILING.

### Verification Done

✅ Code is uploaded to server (verified via grep)
✅ Method signature is correct (line 312)
✅ `createJointProperty()` IS being called (line 120 in store method)
✅ Two property records ARE being created
✅ Mortgage model has all fields in $fillable array

---

## Diagnostic Steps Needed

1. **Add Debug Logging** to `createJointProperty()` method around line 338:

```php
// If there's a mortgage, create joint owner's share
if ($totalMortgage > 0) {
    \Log::info('Creating reciprocal mortgage', [
        'totalMortgage' => $totalMortgage,
        'reciprocalPercentage' => $reciprocalPercentage,
        'jointOwnerId' => $jointOwnerId,
        'jointProperty_id' => $jointProperty->id
    ]);

    $jointMortgageBalance = $totalMortgage * ($reciprocalPercentage / 100);

    try {
        $mortgage = \App\Models\Mortgage::create([...]);
        \Log::info('Reciprocal mortgage created', ['mortgage_id' => $mortgage->id]);
    } catch (\Exception $e) {
        \Log::error('Failed to create reciprocal mortgage', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        throw $e;
    }
}
```

2. **Check Laravel Logs** immediately after creating joint property:
   - `storage/logs/laravel-2025-11-13.log`
   - Look for log entries or exceptions

3. **Verify $totalMortgage Value** is being passed correctly:
   - Line 119: `$totalMortgage = $validated['outstanding_mortgage'] ?? 0;`
   - Check if `$validated['outstanding_mortgage']` exists after validation

4. **Database Constraints Check:**
   - Verify no unique constraints preventing second mortgage
   - Check for foreign key issues

---

## Files Modified

### Backend
- ✅ `app/Http/Controllers/Api/PropertyController.php` - Fixed mortgage creation logic
- ✅ `app/Http/Controllers/Api/MortgageController.php` - Fixed reciprocal update/delete
- ✅ `app/Http/Controllers/Api/SavingsController.php` - Fixed reciprocal update/delete
- ✅ `app/Http/Controllers/Api/InvestmentController.php` - Fixed reciprocal update/delete
- ✅ `app/Http/Controllers/Api/AuthController.php` - Load spouse relationship
- ✅ `app/Http/Controllers/Api/FamilyMembersController.php` - Fixed family display
- ✅ `app/Services/UserProfile/UserProfileService.php` - Fixed family display
- ✅ `database/migrations/2025_11_13_164000_add_missing_ownership_columns_to_mortgages.php` - Added missing columns

### Frontend
- ✅ `resources/js/components/NetWorth/Property/PropertyDetail.vue` - Fixed redirect

### Documentation
- ✅ `linkAccounts.md` - Complete joint ownership documentation
- ✅ `OUTSTANDING_MORTGAGE_ISSUE.md` - This file

---

## Next Session Actions

1. **Upload Latest PropertyController.php to server**
2. **Add debug logging to createJointProperty() method**
3. **Test creating joint property with mortgage**
4. **Check logs for execution flow**
5. **Fix based on findings**
6. **Clean up duplicate/incorrect mortgage records** after fix is confirmed

---

## Test Case

**Setup:**
- Logged in as Chris (User ID: 1004)
- Spouse: Angela (User ID: 1003)
- Linked accounts with spouse_id set

**Test:**
1. Navigate to Net Worth > Properties
2. Click "Add Property"
3. Fill in:
   - Property Type: Main Residence
   - Address: 123 Test Street, London, SW1A 1AA
   - Current Value: £500,000
   - Ownership Type: Joint
   - Joint Owner: Angela Slater-Jones (Spouse)
   - Ownership Percentage: 50%
   - Outstanding Mortgage: £200,000
4. Save

**Expected Result:**
- 2 property records created
- 2 mortgage records created

**Actual Result:**
- 2 property records created ✅
- 1 mortgage record created ❌

---

## ✅ RESOLUTION (November 14, 2025)

### Root Cause

The code logic was **100% CORRECT**. The issue was a **missing database migration**.

The mortgages table was missing two required columns:
- `ownership_type` (enum: individual, joint, trust)
- `joint_owner_name` (varchar)

When the code attempted to create the reciprocal mortgage record, it failed with SQL error:
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'ownership_type' in 'field list'
```

### Solution

Run the pending migration:
```bash
php artisan migrate
```

This runs migration: `2025_11_13_164000_add_missing_ownership_columns_to_mortgages`

### Testing Results

✅ **Test 1: 50/50 Joint Ownership**
- £200k property, £100k mortgage
- Result: 2 properties (£100k each), 2 mortgages (£50k each) ✅

✅ **Test 2: 30/70 Tenants in Common**
- £300k property, £150k mortgage, 30% ownership
- Result: 2 properties (£90k / £210k), 2 mortgages (£45k / £105k) ✅

### Files Changed

- `app/Http/Controllers/Api/PropertyController.php` - Removed debug logging (no functional changes)
- Database migration run (adds missing columns)

### Deployment Guide

See `JOINT_MORTGAGE_FIX_2025-11-14.md` for complete production deployment instructions.

---

**ISSUE RESOLVED** - Code was always correct, just needed database migration to be run.
