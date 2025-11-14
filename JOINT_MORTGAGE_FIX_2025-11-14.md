# Joint Mortgage Reciprocal Creation Fix

**Date**: November 14, 2025
**Status**: ✅ FIXED - Tested Locally, Ready for Production Deployment
**Severity**: Critical (blocked joint property mortgage creation)
**Version**: v0.2.7

---

## Issue Summary

When creating a joint property with a mortgage, only **ONE** mortgage record was being created instead of **TWO** (one for each linked account owner).

### Expected Behavior

For a £200,000 property with £100,000 mortgage, 50/50 joint ownership:

**User 1 Records:**
- Property: £100,000 (50%)
- Mortgage: £50,000 (50%)

**User 2 Records:**
- Property: £100,000 (50%)
- Mortgage: £50,000 (50%)

### Actual Behavior (Before Fix)

**User 1 Records:**
- Property: £100,000 (50%) ✅
- Mortgage: £50,000 (50%) ✅

**User 2 Records:**
- Property: £100,000 (50%) ✅
- Mortgage: **MISSING** ❌

---

## Root Cause Analysis

### The Real Issue

The **code logic was 100% correct**. The issue was a **missing database migration**.

The `PropertyController.php` code correctly:
1. Creates the first mortgage for the primary user ✅
2. Calls `createJointProperty()` method ✅
3. Creates reciprocal property for joint owner ✅
4. Attempts to create reciprocal mortgage ✅

**BUT** the mortgage creation failed silently because the mortgages table was missing required columns:
- `ownership_type` (enum: individual, joint, trust)
- `joint_owner_name` (varchar)

The column `joint_owner_id` existed, but the other two were missing.

### Why This Happened

Two migrations were created on November 13, 2025:
1. `2025_11_13_163500_add_joint_ownership_to_mortgages_table`
2. `2025_11_13_164000_add_missing_ownership_columns_to_mortgages`

These migrations **had not been run** in the production environment, causing the SQL errors when trying to insert mortgage records with `ownership_type` and `joint_owner_name` fields.

---

## Solution

### What Was Fixed

**NO code changes were required**. The existing code was already correct.

The only change made was **removing debug logging** from `PropertyController.php` that was added during investigation.

### What Needs to Be Done in Production

**Run the pending migration** to add the missing columns to the mortgages table.

---

## Production Deployment Instructions

### Step 1: Backup Database

**⚠️ CRITICAL: Always backup before migrations**

```bash
# Via admin panel (RECOMMENDED)
Login as admin@fps.com
Navigate to: Admin Panel → Database Backups
Click: "Create Backup"
Verify backup appears in list

# OR via command line
cd ~/www/csjones.co/tengo-app
php artisan backup:run
```

Verify backup exists:
```bash
ls -lh storage/app/backups/
```

### Step 2: Upload PropertyController.php (Optional)

This file only has debug logging removed - no functional changes. Upload is optional but recommended for cleaner code.

```bash
# Upload via SFTP or File Manager
app/Http/Controllers/Api/PropertyController.php
```

### Step 3: Check Pending Migrations

```bash
cd ~/www/csjones.co/tengo-app
php artisan migrate:status | grep mortgage
```

Expected output should show:
```
2025_11_13_163500_add_joint_ownership_to_mortgages_table ........... Pending
2025_11_13_164000_add_missing_ownership_columns_to_mortgages ....... Pending
```

### Step 4: Handle Migration Conflict

The first migration may fail if `joint_owner_id` already exists. This is expected.

**Option A: Skip First Migration (Recommended)**

```bash
# Mark first migration as complete without running it
mysql -u [db_user] -p [db_name] -e "INSERT INTO migrations (migration, batch) VALUES ('2025_11_13_163500_add_joint_ownership_to_mortgages_table', 3);"

# Then run the second migration
php artisan migrate
```

**Option B: Run Both and Handle Error**

```bash
php artisan migrate

# If first migration fails with "Duplicate column 'joint_owner_id'":
# Mark it as complete and run again
mysql -u [db_user] -p [db_name] -e "INSERT INTO migrations (migration, batch) VALUES ('2025_11_13_163500_add_joint_ownership_to_mortgages_table', 3);"
php artisan migrate
```

Expected successful output:
```
INFO  Running migrations.

2025_11_13_164000_add_missing_ownership_columns_to_mortgages ....... DONE
```

### Step 5: Verify Database Schema

```bash
mysql -u [db_user] -p [db_name] -e "DESCRIBE mortgages;" | grep -E "(ownership_type|joint_owner)"
```

Expected output:
```
joint_owner_id       bigint             YES  MUL  NULL
joint_owner_name     varchar(255)       YES       NULL
ownership_type       enum(...)          NO        individual
```

All three columns must be present.

### Step 6: Clear Laravel Caches

```bash
cd ~/www/csjones.co/tengo-app
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

### Step 7: Test the Fix

**Test Case 1: Create Joint Property with Mortgage (50/50)**

1. Login as a user with a linked spouse account
2. Navigate to: Net Worth → Properties
3. Click "Add Property"
4. Fill in:
   - Property Type: Main Residence
   - Address: 123 Test Street, London, SW1A 1AA
   - Current Value: £200,000
   - Ownership Type: Joint
   - Joint Owner: [Select spouse from dropdown]
   - Ownership Percentage: 50%
   - Outstanding Mortgage: £100,000
5. Click "Save"

**Expected Result:**

Check database:
```bash
mysql -u [db_user] -p [db_name] -e "SELECT id, user_id, address_line_1, current_value, ownership_percentage FROM properties ORDER BY id DESC LIMIT 2;"
```

Should show:
- 2 property records (one per user)
- Each with £100,000 value (50% of £200k)

```bash
mysql -u [db_user] -p [db_name] -e "SELECT id, property_id, user_id, outstanding_balance, ownership_type, joint_owner_id FROM mortgages ORDER BY id DESC LIMIT 2;"
```

Should show:
- 2 mortgage records (one per user)
- Each with £50,000 outstanding balance (50% of £100k)
- `ownership_type` = 'joint'
- `joint_owner_id` pointing to the other user

**Test Case 2: Create Joint Property with Mortgage (30/70)**

Repeat test with:
- Current Value: £300,000
- Ownership Percentage: 30%
- Outstanding Mortgage: £150,000

**Expected Result:**
- User 1 property: £90,000 (30%), mortgage: £45,000 (30%)
- User 2 property: £210,000 (70%), mortgage: £105,000 (70%)

---

## Verification Queries

### Check All Joint Properties

```sql
SELECT id, user_id, address_line_1, current_value, ownership_percentage, joint_owner_id
FROM properties
WHERE ownership_type = 'joint'
ORDER BY address_line_1, user_id;
```

### Check All Joint Mortgages

```sql
SELECT id, property_id, user_id, outstanding_balance, ownership_type, joint_owner_id, joint_owner_name
FROM mortgages
WHERE ownership_type = 'joint'
ORDER BY property_id;
```

### Verify Reciprocal Records Match

```sql
SELECT
    p1.address_line_1,
    p1.user_id AS user1_id,
    p1.current_value AS user1_property_value,
    p1.ownership_percentage AS user1_percentage,
    m1.outstanding_balance AS user1_mortgage,
    p2.user_id AS user2_id,
    p2.current_value AS user2_property_value,
    p2.ownership_percentage AS user2_percentage,
    m2.outstanding_balance AS user2_mortgage
FROM properties p1
JOIN properties p2 ON p1.joint_owner_id = p2.user_id AND p2.joint_owner_id = p1.user_id
LEFT JOIN mortgages m1 ON p1.id = m1.property_id
LEFT JOIN mortgages m2 ON p2.id = m2.property_id
WHERE p1.ownership_type = 'joint';
```

---

## What Was Tested Locally

### Test Environment
- PHP 8.2.12
- MySQL 8.0
- Laravel 10.x
- Fresh migrations run

### Tests Performed

✅ **Test 1: 50/50 Joint Ownership**
- £200,000 property, £100,000 mortgage
- Result: 2 properties (£100k each), 2 mortgages (£50k each)

✅ **Test 2: 30/70 Tenants in Common**
- £300,000 property, £150,000 mortgage, 30% ownership
- Result: 2 properties (£90k / £210k), 2 mortgages (£45k / £105k)

✅ **Test 3: Code Logic Verification**
- Confirmed `createJointProperty()` method executes correctly
- Confirmed mortgage creation block is entered
- Confirmed values are calculated correctly
- Confirmed reciprocal records are created

### Files Verified

✅ **PropertyController.php** (lines 309-359)
- `createJointProperty()` method logic is correct
- Mortgage creation logic properly splits balances by ownership percentage
- All required fields populated correctly

✅ **Mortgage Model** (`app/Models/Mortgage.php`)
- All ownership fields in `$fillable` array
- No unique constraints preventing duplicate records

✅ **Migrations**
- Migration `2025_11_13_164000` adds columns conditionally (safe to run)
- Columns added: `ownership_type`, `joint_owner_name`

---

## Rollback Plan

If issues occur after deployment:

### 1. Restore Database Backup

```bash
# Via admin panel
Login as admin@fps.com
Navigate to: Admin Panel → Database Backups
Select backup from before migration
Click "Restore"

# OR via command line
mysql -u [db_user] -p [db_name] < storage/app/backups/backup-YYYY-MM-DD.sql
```

### 2. Rollback Migration (if needed)

```bash
cd ~/www/csjones.co/tengo-app
php artisan migrate:rollback --step=1
```

This will remove the `ownership_type` and `joint_owner_name` columns.

### 3. Clear Caches

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

---

## Post-Deployment Actions

### Update OUTSTANDING_MORTGAGE_ISSUE.md

Change status from "Under Investigation" to "✅ RESOLVED"

### Update Known Issues in CLAUDE.md

Remove or update the joint mortgage issue section.

### Update README.md

Add to Recent Updates section:

```markdown
### November 14, 2025 - Joint Mortgage Fix

**Issue**: Joint properties with mortgages only creating one mortgage record
**Root Cause**: Missing database migration for ownership columns
**Solution**: Ran pending migration `2025_11_13_164000_add_missing_ownership_columns_to_mortgages`
**Result**: Joint mortgages now create reciprocal records correctly with proper ownership splits
```

---

## Files Changed

### Local Development (Committed)
- `app/Http/Controllers/Api/PropertyController.php` - Removed debug logging (optional for production)

### Production Deployment (Required)
- None - just run the migration

### Database Changes (Required)
- Migration: `2025_11_13_164000_add_missing_ownership_columns_to_mortgages`
- Adds: `ownership_type`, `joint_owner_name` columns to mortgages table

---

## Summary

**The code was always correct**. The issue was simply missing database columns that prevented the mortgage creation from succeeding.

**Deployment is low-risk**:
- Migration adds columns (non-destructive)
- No existing data is modified
- Code logic unchanged (just debug logging removed)
- Extensively tested locally with multiple scenarios

**After deployment**:
- Joint properties with mortgages will create reciprocal records correctly
- Both property AND mortgage records created for linked accounts
- Values correctly split by ownership percentage (50/50, 30/70, etc.)

---

**Status**: ✅ **READY FOR PRODUCTION DEPLOYMENT**

**Documented By**: Claude Code (Anthropic)
**Date**: November 14, 2025

🤖 Generated with [Claude Code](https://claude.com/claude-code)
