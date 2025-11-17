# TenGo Application - Full Redeployment Guide (v0.2.7 → v0.2.9)

**Target Environment**: SiteGround Shared Hosting
**Application**: TenGo Financial Planning System
**Deployment Type**: Full Production Redeployment
**Estimated Total Time**: 25-35 minutes

---

## Table of Contents

1. [Pre-Deployment Phase](#1-pre-deployment-phase) (10-15 minutes)
2. [Deployment Execution](#2-deployment-execution) (10-15 minutes)
3. [Post-Deployment Verification](#3-post-deployment-verification) (5-10 minutes)
4. [Rollback Procedure](#4-rollback-procedure) (Emergency Only)
5. [Troubleshooting Guide](#5-troubleshooting-guide)

---

## Why Full Redeployment?

This guide uses **full redeployment** instead of incremental patching because:

✅ **Guaranteed Completeness** - Pulls entire v0.2.9 codebase, no risk of missing changes
✅ **Simpler Process** - No need to track 50+ individual file changes
✅ **Better Testing** - Test entire application, not just deltas
✅ **Cleaner State** - Fresh build of all assets and dependencies
✅ **More Confidence** - Know exactly what you're deploying

Since production is on v0.2.7 and you're deploying v0.2.9 (skipping v0.2.8), this is the safest approach.

---

## 1. Pre-Deployment Phase

**Estimated Time**: 10-15 minutes

### Step 1.1: Connect to Production Server

```bash
ssh -p 18765 u163-ptanegf9edny@csjones.co
```

**Expected Output**:
```
Welcome to SiteGround...
```

### Step 1.2: Navigate to Application Directory

```bash
cd ~/www/csjones.co/tengo-app
```

**Verify Current Location**:
```bash
pwd
```

**Expected Output**:
```
/home/u163-ptanegf9edny/www/csjones.co/tengo-app
```

### Step 1.3: Verify Current Version

```bash
git log --oneline -1
```

**Expected Output**:
```
982ef1a docs: Add comprehensive deployment patch documentation for v0.2.8
```

**Success Criteria**: Commit hash should be `982ef1a` (v0.2.7)

---

### Step 1.4: Database Backup (CRITICAL - DO NOT SKIP)

> **⚠️ CRITICAL WARNING**
> This step is MANDATORY. Never proceed without a verified database backup.
> You are running 20 database migrations that modify table structures.

#### Option A: Admin Panel Backup (Recommended)

1. Open browser: https://csjones.co/tengo/
2. Log in with: `admin@fps.com` / `admin123`
3. Navigate to: **Admin Panel** → **Database Backups**
4. Click: **"Create New Backup"**
5. Wait for success message
6. **VERIFY**: Download the backup file to confirm it exists

**Success Criteria**: Backup file appears in list with current timestamp

#### Option B: Command Line Backup (Alternative)

```bash
# Navigate to app directory
cd ~/www/csjones.co/tengo-app

# Create backup using mysqldump
mysqldump -u u163-ptanegf9edny_tengo -p u163-ptanegf9edny_fps_tengo > backup_v027_$(date +%Y%m%d_%H%M%S).sql
```

**When prompted, enter database password**

**Verify Backup Created**:
```bash
ls -lh backup_v027_*.sql
```

**Expected Output**:
```
-rw-r--r-- 1 user group 2.5M Nov 15 10:30 backup_v027_20251115_103045.sql
```

**Success Criteria**: Backup file size should be 1-5MB (depending on data volume)

> **📋 CHECKPOINT**: Do not proceed until you have confirmed a valid backup exists.

---

### Step 1.5: Environment Configuration Verification

```bash
cat .env | grep -E "^APP_ENV=|^APP_DEBUG=|^APP_URL=|^DB_DATABASE="
```

**Expected Output**:
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://csjones.co/tengo
DB_DATABASE=u163-ptanegf9edny_fps_tengo
```

**Success Criteria**:
- `APP_ENV=production` (NOT local/development)
- `APP_DEBUG=false` (NOT true)
- `APP_URL` matches production URL
- `DB_DATABASE` matches production database

> **⚠️ WARNING**: If `APP_DEBUG=true`, change to `false` immediately:
> ```bash
> sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env
> ```

---

### Step 1.6: Git Repository Status Check

```bash
git status
```

**Expected Output**:
```
On branch main
Your branch is up to date with 'origin/main'.

nothing to commit, working tree clean
```

**If You See Uncommitted Changes**:

```bash
# View what changed
git diff

# Stash changes for safety
git stash save "Pre-deployment-v029-$(date +%Y%m%d_%H%M%S)"

# Verify clean state
git status
```

**Success Criteria**: Working tree must be clean before proceeding

---

### Step 1.7: Pre-Deployment Checklist

**Complete this checklist before proceeding**:

- [ ] SSH connection successful
- [ ] Current directory: `~/www/csjones.co/tengo-app`
- [ ] Current version confirmed: `982ef1a` (v0.2.7)
- [ ] Database backup created and verified (admin panel OR command line)
- [ ] Backup file downloaded to local machine (safety copy)
- [ ] `.env` file verified: `APP_ENV=production`, `APP_DEBUG=false`
- [ ] Git working tree is clean (no uncommitted changes)
- [ ] Admin credentials tested: admin@fps.com works
- [ ] Application currently accessible at https://csjones.co/tengo/

> **🛑 STOP**: Do not proceed to deployment unless ALL items above are checked.

---

## 2. Deployment Execution

**Estimated Time**: 10-15 minutes

### Step 2.1: Enable Maintenance Mode

```bash
cd ~/www/csjones.co/tengo-app
php artisan down --message="Upgrading to v0.2.9 - Back online in 15 minutes" --retry=60
```

**Expected Output**:
```
Application is now in maintenance mode.
```

**What This Does**: Displays a user-friendly maintenance page while you deploy

---

### Step 2.2: Fetch Latest Code from Repository

```bash
git fetch origin main
```

**Expected Output**:
```
remote: Enumerating objects: 150, done.
remote: Counting objects: 100% (150/150), done.
...
From https://github.com/Stoff73/fpsV2
 * branch            main       -> FETCH_HEAD
```

**Success Criteria**: Fetch completes without errors

---

### Step 2.3: View Incoming Changes (Optional Verification)

```bash
git log --oneline 982ef1a..origin/main | head -20
```

**Expected Output**: Should show commits from Nov 12-15, 2025 including:
- Mixed mortgage functionality
- Managing agents
- Expenditure modes
- Joint mortgage fix
- Family name granularity
- Pension/liability type expansions

**This is informational only** - proceed regardless of output

---

### Step 2.4: Pull Latest Code (FULL REDEPLOYMENT)

```bash
git pull origin main
```

**Expected Output**:
```
Updating 982ef1a..2a58f31
Fast-forward
 app/Http/Controllers/Api/Property/PropertyController.php | 45 ++++++--
 app/Models/Property/Mortgage.php                         | 12 +++
 database/migrations/2025_11_12_*.php                     | 6 files changed
 database/migrations/2025_11_13_*.php                     | 2 files changed
 database/migrations/2025_11_14_*.php                     | 4 files changed
 database/migrations/2025_11_15_*.php                     | 8 files changed
 resources/js/components/Property/MortgageForm.vue        | 120 +++++++++++++--------
 ...
 50 files changed, 4500 insertions(+), 1600 deletions(-)
```

**Verify New Version**:
```bash
git log --oneline -1
```

**Expected Output**:
```
2a58f31 (HEAD -> main, origin/main) [Latest commit message from Nov 15]
```

**Success Criteria**:
- Code pulled successfully
- Current commit is `2a58f31` or later
- No merge conflicts reported

---

### Step 2.5: Install Backend Dependencies (Production Mode)

```bash
composer install --no-dev --optimize-autoloader --no-interaction
```

**Expected Output**:
```
Loading composer repositories with package information
Installing dependencies from lock file
Verifying lock file contents can be installed on current platform.
Package operations: 0 installs, 2 updates, 0 removals
  - Downloading laravel/framework (v10.x)
  - Installing laravel/framework (v10.x): Extracting archive
...
Generating optimized autoload files
> @php artisan package:discover --ansi
Discovered Package: ...
```

**Flags Explained**:
- `--no-dev`: Exclude development dependencies (phpunit, etc.)
- `--optimize-autoloader`: Generate optimized class maps
- `--no-interaction`: Don't prompt for input

**Success Criteria**:
- Installation completes without errors
- "Generating optimized autoload files" appears
- No warnings about missing extensions

**Estimated Time**: 2-3 minutes

---

### Step 2.6: Install Frontend Dependencies

```bash
npm ci --silent
```

**Expected Output**:
```
added 250 packages in 15s
```

**Why `npm ci` Instead of `npm install`**:
- Uses `package-lock.json` for exact versions
- Faster and more reliable for deployments
- Deletes `node_modules` first (clean install)

**Success Criteria**:
- Installation completes without errors
- No vulnerabilities reported (or only low severity)

**Estimated Time**: 1-2 minutes

---

### Step 2.7: Build Production Frontend Assets

```bash
NODE_ENV=production npm run build
```

**Expected Output**:
```
> build
> vite build

vite v5.x.x building for production...
✓ 1250 modules transformed.
rendering chunks...
computing gzip size...
dist/assets/app-[hash].js      450.23 kB │ gzip: 145.67 kB
dist/assets/app-[hash].css      85.45 kB │ gzip:  18.32 kB
dist/assets/vendor-[hash].js  1250.89 kB │ gzip: 380.45 kB
✓ built in 45.23s
```

**Verify Build Artifacts**:
```bash
ls -lh public/build/
```

**Expected Output**:
```
-rw-r--r-- 1 user group  450K Nov 15 10:45 assets/app-[hash].js
-rw-r--r-- 1 user group   85K Nov 15 10:45 assets/app-[hash].css
-rw-r--r-- 1 user group 1.2M Nov 15 10:45 assets/vendor-[hash].js
-rw-r--r-- 1 user group  12K Nov 15 10:45 manifest.json
```

**Success Criteria**:
- Build completes without errors
- `public/build/manifest.json` exists
- `public/build/assets/` contains JS and CSS files
- File sizes are reasonable (not 0 bytes)

**Estimated Time**: 1-2 minutes

---

### Step 2.8: Run Database Migrations (ALL 20 PENDING)

> **⚠️ CRITICAL STEP**
> This will run 20 migrations that modify database structure.
> Ensure database backup is confirmed before proceeding.

```bash
php artisan migrate --force
```

**Expected Output**:
```
Running migrations.

2025_11_12_075601_add_charitable_bequest_to_users_table .......... DONE
2025_11_12_083427_add_decreasing_policy_fields_to_life ............ DONE
2025_11_12_094404_add_lump_sum_contribution_to_dc_pensions ........ DONE
2025_11_12_101030_add_annual_interest_income_to_users_table ....... DONE
2025_11_12_193748_add_tenants_in_common_and_trust_to_properties .. DONE
2025_11_12_194237_make_properties_purchase_fields_nullable ........ DONE
2025_11_13_163500_add_joint_ownership_to_mortgages_table .......... DONE
2025_11_13_164000_add_missing_ownership_columns_to_mortgages ...... DONE
2025_11_14_095112_remove_redundant_rental_fields_from_properties .. DONE
2025_11_14_103319_add_name_fields_to_family_members_table ......... DONE
2025_11_14_120204_add_end_date_and_make_fields_optional_on_life .. DONE
2025_11_14_123750_add_pension_type_to_dc_pensions_table ........... DONE
2025_11_15_093603_add_other_account_type_to_investment_accounts ... DONE
2025_11_15_095207_add_mixed_mortgage_fields_to_mortgages_table .... DONE
2025_11_15_100406_add_managing_agent_fields_to_properties_table ... DONE
2025_11_15_111744_add_part_time_to_employment_status_enum ......... DONE
2025_11_15_115911_add_expenditure_modes_and_education_fields ...... DONE
2025_11_15_125142_add_is_mortgage_protection_to_life_insurance .... DONE
2025_11_15_162349_remove_part_and_part_from_mortgage_type_enum .... DONE
2025_11_15_170630_update_liability_type_enum_to_support_all_types . DONE
```

**Why `--force` Flag**: Required in production (when `APP_ENV=production`)

**Verify All Migrations Ran**:
```bash
php artisan migrate:status | tail -25
```

**Expected Output**: All 20 new migrations should show "Ran" status

**Success Criteria**:
- All 20 migrations complete with "DONE"
- No errors reported
- `migrate:status` shows all migrations as "Ran"

**Estimated Time**: 30-60 seconds

> **📋 CHECKPOINT**: If ANY migration fails, STOP immediately and proceed to Rollback section.

---

### Step 2.9: Clear All Application Caches

```bash
php artisan optimize:clear
```

**Expected Output**:
```
Compiled views cleared successfully.
Application cache cleared successfully.
Route cache cleared successfully.
Configuration cache cleared successfully.
Compiled services and packages files removed successfully.
Caches cleared successfully.
```

**What This Clears**:
- Compiled views (Blade templates)
- Application cache
- Route cache
- Configuration cache
- Cached services/packages

**Success Criteria**: All cache types cleared successfully

---

### Step 2.10: Rebuild Production Caches

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Expected Output**:
```
Configuration cache cleared successfully.
Configuration cached successfully.

Route cache cleared successfully.
Routes cached successfully.

Blade templates cached successfully.
```

**Why Cache in Production**:
- Improves performance by 30-50%
- Reduces file system reads
- Standard Laravel production practice

**Success Criteria**: All three cache operations succeed

---

### Step 2.11: Optimize Composer Autoloader

```bash
composer dump-autoload --optimize
```

**Expected Output**:
```
Generating optimized autoload files
Generated optimized autoload files containing 5000 classes
```

**Success Criteria**: Autoloader regenerated successfully

---

### Step 2.12: Set File Permissions (If Needed)

**Check Current Permissions**:
```bash
ls -ld storage bootstrap/cache
```

**If Permissions Are Wrong** (not writable):
```bash
chmod -R 775 storage bootstrap/cache
```

**For SiteGround Shared Hosting** (if above doesn't work):
```bash
find storage -type d -exec chmod 755 {} \;
find storage -type f -exec chmod 644 {} \;
find bootstrap/cache -type d -exec chmod 755 {} \;
find bootstrap/cache -type f -exec chmod 644 {} \;
```

**Success Criteria**:
- `storage/` is writable
- `bootstrap/cache/` is writable
- No permission errors in logs

---

### Step 2.13: Disable Maintenance Mode (Bring Site Online)

```bash
php artisan up
```

**Expected Output**:
```
Application is now live.
```

**Verify Site is Live**:
```bash
curl -I https://csjones.co/tengo/ | head -5
```

**Expected Output**:
```
HTTP/2 200
server: nginx
content-type: text/html; charset=UTF-8
```

**Success Criteria**:
- Maintenance mode disabled
- HTTP 200 response (not 503)
- Site accessible in browser

---

### Step 2.14: Deployment Completion Summary

```bash
echo "==================================="
echo "DEPLOYMENT COMPLETED SUCCESSFULLY"
echo "==================================="
echo "Version: v0.2.9"
echo "Deployed: $(date)"
echo "Commit: $(git log --oneline -1)"
echo "Migrations Run: 20"
echo "==================================="
```

**Take Screenshot or Copy Output** for your records

---

## 3. Post-Deployment Verification

**Estimated Time**: 5-10 minutes

### Step 3.1: Database Migration Verification

```bash
php artisan migrate:status | grep -E "2025_11_1[2345]" | wc -l
```

**Expected Output**: `20` (confirming all 20 migrations ran)

**Detailed View**:
```bash
php artisan migrate:status | grep -E "2025_11_1[2345]"
```

**Success Criteria**: All 20 migrations show "Ran" status

---

### Step 3.2: Application Health Check

**Check Laravel Logs**:
```bash
tail -50 storage/logs/laravel.log
```

**What to Look For**:
- ✅ No PHP errors
- ✅ No SQL errors
- ✅ No "Class not found" errors

**If You See Errors**: Note them and proceed to Troubleshooting section

---

### Step 3.3: Critical Feature Tests (Browser Testing)

Open browser and navigate to: https://csjones.co/tengo/

#### Test 1: Application Loads
- [ ] Homepage loads without errors
- [ ] No JavaScript console errors (F12 → Console)
- [ ] CSS styles applied correctly
- [ ] Navigation menu works

#### Test 2: User Authentication
- [ ] Log in as demo user: `demo@fps.com` / `password`
- [ ] Dashboard loads successfully
- [ ] All 5 modules visible: Protection, Savings, Investment, Retirement, Estate

#### Test 3: Joint Mortgage Creation (CRITICAL BUG FIX)

**This is THE most critical test - validates the joint mortgage reciprocal creation fix**

1. Navigate to **Net Worth** → **Properties** → **Add Property**
2. Fill in property details:
   - Property Type: Main Residence
   - Ownership: **Joint**
   - Select spouse from dropdown
   - Current Value: £300,000
   - Ownership %: 50%
3. Add mortgage:
   - Has Mortgage: Yes
   - Lender: Test Bank
   - Outstanding Balance: £200,000
   - Monthly Payment: £1,000
4. Save property

**Verification Steps**:

**As Primary User**:
- [ ] Property appears in list
- [ ] Shows full value (£300,000) with "Your 50% share: £150,000"
- [ ] Mortgage appears
- [ ] Shows full balance (£200,000) with "Your 50% share: £100,000"

**Switch to Spouse Account**:
- [ ] Log out, log in as spouse
- [ ] Navigate to Net Worth → Properties
- [ ] Property appears in spouse's list
- [ ] **CRITICAL**: Mortgage appears in spouse's list
- [ ] Both show correct shares (50% each)

**Success Criteria**:
- ✅ 2 property records created (one for each owner)
- ✅ **2 mortgage records created (one for each owner)** ← THIS WAS THE BUG
- ✅ Both owners see the joint property and mortgage
- ✅ Shares calculated correctly (50% each)

> **🔴 CRITICAL FAILURE**: If spouse does NOT see the mortgage record, the deployment has failed. Proceed to rollback immediately.

---

#### Test 4: Mixed Mortgage Creation

1. **Net Worth** → **Properties** → **Add Property**
2. Fill in details, add mortgage
3. In mortgage form:
   - Mortgage Type: **Mixed**
   - Repayment Percentage: 70%
   - Interest-Only Percentage: 30%
   - Rate Type: **Mixed**
   - Fixed Rate %: 60%
   - Variable Rate %: 40%
4. Save

**Verification**:
- [ ] Mortgage saves successfully
- [ ] Percentages must add to 100% (validation)
- [ ] Display shows split correctly
- [ ] Property detail shows both types

---

#### Test 5: Managing Agent Fields (BTL Only)

1. **Net Worth** → **Properties** → **Add Property**
2. Property Type: **Buy to Let**
3. Verify "Managing Agent" section appears
4. Fill in:
   - Agent Name: Test Agency
   - Company: Test Ltd
   - Phone: 01234567890
   - Email: test@test.com
   - Fee: £150
5. Save

**Verification**:
- [ ] All 5 fields save correctly
- [ ] Display shows agent details
- [ ] **For Main/Secondary Residence**: Fields should NOT appear

---

#### Test 6: Expenditure Modes (Married Couples)

1. **User Profile** → **Expenditure** tab
2. Look for mode selector
3. **Joint Entry Mode**:
   - [ ] Single form with combined values
   - [ ] Saves as 50/50 split
4. **Separate Entry Mode**:
   - [ ] Two columns (You / Spouse)
   - [ ] Can enter different values
   - [ ] Saves individual allocations

---

#### Test 7: Expanded Liability Types

1. **Estate Planning** → **Liabilities**
2. Click "Add Liability"
3. Check "Type" dropdown includes ALL 9 types:
   - [ ] Loan (generic)
   - [ ] Secured Loan
   - [ ] Unsecured Loan
   - [ ] Personal Loan
   - [ ] Car Loan
   - [ ] Credit Card
   - [ ] Hire Purchase
   - [ ] Overdraft
   - [ ] Other

**Success Criteria**: All 9 types present (was 4 in v0.2.7)

---

#### Test 8: Life Insurance Enhancements

1. **Protection** → **Add Policy**
2. Check for:
   - [ ] **End Date** field (required)
   - [ ] **Mortgage Protection** checkbox
   - [ ] Start Date optional (not required)
3. Save test policy

---

#### Test 9: State Pension Entry

1. **Retirement** → **Add Pension**
2. Check for **State Pension** option
3. Save test state pension

---

### Step 3.4: Regression Testing (Existing Features)

**Test Existing Data Still Works**:

- [ ] Existing properties display correctly
- [ ] Existing mortgages display correctly
- [ ] Existing policies display correctly
- [ ] Existing pensions display correctly
- [ ] All module dashboards load
- [ ] No data corruption

---

### Step 3.5: Post-Deployment Checklist

**Complete this checklist to confirm successful deployment**:

- [ ] All 20 migrations ran successfully
- [ ] Application loads without errors
- [ ] User authentication works
- [ ] **CRITICAL**: Joint mortgages create 2 records (bug fix verified)
- [ ] Mixed mortgages save and display correctly
- [ ] Managing agent fields work on BTL properties
- [ ] Expenditure modes work for married couples
- [ ] Expanded liability types (9 total) available
- [ ] Life insurance enhancements work
- [ ] State pension entry works
- [ ] All existing data displays correctly (no corruption)
- [ ] All 5 module dashboards load successfully
- [ ] No critical errors in logs

> **🎉 SUCCESS**: If all items checked, deployment is complete and verified!

---

## 4. Rollback Procedure

**Use this section ONLY if deployment fails critically**

### When to Rollback

Rollback immediately if:
- 🔴 Any migration fails
- 🔴 Application shows critical errors
- 🔴 Data corruption detected
- 🔴 Joint mortgage fix didn't work (spouse doesn't see mortgage)
- 🔴 More than 30 minutes of downtime

---

### Rollback Steps

#### 1. Enable Maintenance Mode
```bash
php artisan down --message="Emergency rollback in progress"
```

#### 2. Restore Database from Backup

**Via Admin Panel**:
1. Admin Panel → Database Backups
2. Find backup from Step 1.4 (timestamp before deployment)
3. Click "Restore"

**Via Command Line**:
```bash
mysql -u u163-ptanegf9edny_tengo -p u163-ptanegf9edny_fps_tengo < backup_v027_[TIMESTAMP].sql
```

#### 3. Rollback Code to v0.2.7
```bash
git checkout 982ef1a
```

#### 4. Reinstall Dependencies
```bash
composer install --no-dev --optimize-autoloader
npm ci
NODE_ENV=production npm run build
```

#### 5. Clear Caches
```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
```

#### 6. Bring Site Back Online
```bash
php artisan up
```

#### 7. Verify Rollback
```bash
git log --oneline -1  # Should show 982ef1a
curl -I https://csjones.co/tengo/  # Should return 200
```

---

## 5. Troubleshooting Guide

### Issue: Migration Fails

**Check error**:
```bash
tail -50 storage/logs/laravel.log
```

**If column already exists**: Safe to skip (already ran)
**If uncertain**: ROLLBACK IMMEDIATELY

---

### Issue: White Screen

**Diagnosis**:
```bash
tail -50 storage/logs/laravel.log
```

**Resolution**:
```bash
php artisan config:clear
php artisan cache:clear
composer dump-autoload --optimize
chmod -R 775 storage bootstrap/cache
```

---

### Issue: Assets Not Loading (404)

**Resolution**:
```bash
NODE_ENV=production npm run build
php artisan view:clear
# Hard refresh browser: Ctrl+Shift+R
```

---

### Issue: Joint Mortgage Not Creating 2 Records

**This is critical failure - ROLLBACK IMMEDIATELY**

Verify with:
```bash
php artisan tinker
```
```php
$property = App\Models\Property::where('ownership_type', 'joint')->latest()->first();
$property->mortgage;  // Primary owner's mortgage

// Check spouse mortgage exists
$spouseMortgage = App\Models\Mortgage::where('joint_owner_id', $property->user_id)
    ->where('user_id', $property->joint_owner_id)
    ->first();

dd($spouseMortgage);  // Should NOT be null
```

If null → Deployment FAILED → Rollback

---

### Quick Health Check Script

```bash
#!/bin/bash
echo "======================================"
echo "TenGo Application Health Check"
echo "======================================"
echo ""
echo "1. Version:" && git log --oneline -1
echo "2. Migrations:" && php artisan migrate:status | tail -5
echo "3. Build:" && ls public/build/manifest.json && echo "✓ OK" || echo "✗ Missing"
echo "4. Errors:" && tail -10 storage/logs/laravel.log | grep -i error || echo "None"
echo "======================================"
```

---

## Summary

### Deployment Timeline

| Phase | Time |
|-------|------|
| Pre-Deployment | 10-15 min |
| Deployment | 10-15 min |
| Verification | 5-10 min |
| **Total** | **25-40 min** |

### Success Metrics

- ✅ All 20 migrations completed
- ✅ Joint mortgage creates 2 records
- ✅ Zero data corruption
- ✅ No critical errors
- ✅ All features work

### Next Steps After Success

1. Tag the release:
```bash
git tag -a v0.2.9 -m "Production deployment v0.2.9"
git push origin v0.2.9
```

2. Monitor logs for 24 hours
3. Document any issues encountered
4. Plan for v0.3.0

---

**Deployment Guide Version**: 1.0
**Created**: November 15, 2025
**Application**: TenGo Financial Planning System
**Target Version**: v0.2.9

🤖 **Generated by laravel-stack-deployer agent** via Claude Code
