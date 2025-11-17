# TenGo v0.2.9 Patch Deployment Guide (November 17 Updates)

**Deployment Date**: November 17, 2025
**Deployment Type**: Patch Update via FTP + SSH
**Version**: v0.2.7 → v0.2.9
**Environment**: Production (SiteGround or similar hosting)
**Status**: Ready for Deployment
**Risk Level**: LOW (Bug fixes, UI improvements, no schema-breaking changes)

---

## Overview

This patch deployment addresses **18 bugs and enhancements** (17 bug fixes + 1 UI enhancement) identified during the November 17, 2025 debugging session. All changes have been tested in development and are production-ready.

### Key Changes Summary

**Critical Bug Fixes**:
1. Middle name handling errors (4 locations fixed)
2. DC Pension expected return field not saving
3. State Pension age field reactivity issue
4. Property value double-percentage calculation bug
5. Estate Gifting/Trust Strategy total estate value mismatch
6. User Profile balance sheet double-percentage bug

**UI/UX Improvements**:
7. Retirement age validation (minimum 55 with user-friendly error messages)
8. Pension cards now clickable to detail views
9. Property detail view shows both full value and user's share
10. Dashboard cleanup (removed "Welcome to TenGo" heading)
11. Retirement tab renamed "Strategies" from "Recommendations"

**Technical Improvements**:
- Virtual record anti-pattern eliminated
- Reciprocal spouse deletion fixed
- State Pension removed from Net Worth calculation (not accessible as capital)
- Unified pension form architecture validated

---

## Pre-Deployment Checklist

### 1. Local Environment Verification

#### 1.1 Run Code Formatting
```bash
cd /Users/Chris/Desktop/fpsApp/tengo
./vendor/bin/pint
```
**Expected Output**: "Files formatted successfully" or "No files require formatting"

#### 1.2 Build Frontend Assets
```bash
npm run build
```
**Expected Output**:
- Vite build completes without errors
- `public/build/manifest.json` created
- `public/build/assets/` contains compiled JS/CSS files

**Verify Build Success**:
```bash
ls -lh public/build/manifest.json
ls public/build/assets/ | head -5
```

#### 1.3 Verify Version Numbers Updated
```bash
# Check Footer component
grep "v0.2.9" resources/js/components/Footer.vue

# Check Version view
grep "v0.2.9" resources/js/views/Version.vue
```

### 2. Production Database Backup

**⚠️ CRITICAL - NEVER SKIP THIS STEP**

#### Option A: Via Admin Panel (Recommended)
1. Login to production: `admin@fps.com` / `admin123`
2. Navigate to: **Admin → System → Backup Database**
3. Click "Create Backup"
4. Download backup file to local machine
5. Verify download: File should be `backup_YYYY_MM_DD_HHMMSS.sql`
6. Store safely with clear naming: `tengo_prod_backup_before_v0.2.9_Nov17.sql`

#### Option B: Via SSH (Manual)
```bash
# Connect to production server
ssh username@your-production-server.com

# Navigate to project root
cd /path/to/tengo

# Create backup
mysqldump -u [DB_USERNAME] -p [DB_NAME] > ~/backups/tengo_backup_$(date +%Y%m%d_%H%M%S).sql

# Verify backup created
ls -lh ~/backups/
```

**Backup Verification Checklist**:
- [ ] Backup file exists
- [ ] File size > 100KB (indicates data was exported)
- [ ] Downloaded to local machine (for admin panel backups)
- [ ] Filename clearly identifies date and purpose

### 3. Create Deployment Archive

This creates a single archive containing all files to upload:

```bash
cd /Users/Chris/Desktop/fpsApp/tengo

# Create deployment directory
mkdir -p deployment_nov17

# Copy backend files
mkdir -p deployment_nov17/app/Http/Controllers/Api
mkdir -p deployment_nov17/app/Http/Controllers/Api/Estate
mkdir -p deployment_nov17/app/Models
mkdir -p deployment_nov17/app/Services/NetWorth
mkdir -p deployment_nov17/app/Services/Shared
mkdir -p deployment_nov17/app/Services/UserProfile
mkdir -p deployment_nov17/database/migrations

# Copy controllers
cp app/Http/Controllers/Api/FamilyMembersController.php deployment_nov17/app/Http/Controllers/Api/
cp app/Http/Controllers/Api/Estate/GiftingController.php deployment_nov17/app/Http/Controllers/Api/Estate/

# Copy models
cp app/Models/DCPension.php deployment_nov17/app/Models/

# Copy services
cp app/Services/NetWorth/NetWorthService.php deployment_nov17/app/Services/NetWorth/
cp app/Services/Shared/CrossModuleAssetAggregator.php deployment_nov17/app/Services/Shared/
cp app/Services/UserProfile/PersonalAccountsService.php deployment_nov17/app/Services/UserProfile/

# Copy migration
cp database/migrations/2025_11_17_074642_add_expected_return_percent_to_dc_pensions_table.php deployment_nov17/database/migrations/

# Copy frontend files
mkdir -p deployment_nov17/resources/js/components/NetWorth/Property
mkdir -p deployment_nov17/resources/js/components/NetWorth
mkdir -p deployment_nov17/resources/js/components/Retirement
mkdir -p deployment_nov17/resources/js/components
mkdir -p deployment_nov17/resources/js/views/Retirement
mkdir -p deployment_nov17/resources/js/views
mkdir -p deployment_nov17/resources/js/router

# Copy Vue components
cp resources/js/components/NetWorth/Property/PropertyDetail.vue deployment_nov17/resources/js/components/NetWorth/Property/
cp resources/js/components/NetWorth/PropertyCard.vue deployment_nov17/resources/js/components/NetWorth/
cp resources/js/views/Retirement/RetirementDashboard.vue deployment_nov17/resources/js/views/Retirement/
cp resources/js/components/Retirement/PensionCard.vue deployment_nov17/resources/js/components/Retirement/
cp resources/js/views/Retirement/RetirementReadiness.vue deployment_nov17/resources/js/views/Retirement/
cp resources/js/views/Retirement/PensionInventory.vue deployment_nov17/resources/js/views/Retirement/
cp resources/js/components/Retirement/DCPensionForm.vue deployment_nov17/resources/js/components/Retirement/
cp resources/js/components/Retirement/StatePensionForm.vue deployment_nov17/resources/js/components/Retirement/
cp resources/js/components/Footer.vue deployment_nov17/resources/js/components/
cp resources/js/views/Version.vue deployment_nov17/resources/js/views/
cp resources/js/views/Dashboard.vue deployment_nov17/resources/js/views/
cp resources/js/views/Retirement/PensionDetail.vue deployment_nov17/resources/js/views/Retirement/
cp resources/js/router/index.js deployment_nov17/resources/js/router/

# Copy built assets
mkdir -p deployment_nov17/public/build
cp -r public/build/* deployment_nov17/public/build/

# Create archive
tar -czf tengo-v0.2.9-nov17-patch.tar.gz deployment_nov17/

# Verify archive
tar -tzf tengo-v0.2.9-nov17-patch.tar.gz | head -20

echo "✅ Deployment archive created: tengo-v0.2.9-nov17-patch.tar.gz"
```

**Verification**:
```bash
ls -lh tengo-v0.2.9-nov17-patch.tar.gz
# Should be 2-5 MB depending on compiled assets
```

### 4. Pre-Deployment Verification Checklist

- [ ] All tests passing locally (`./vendor/bin/pest`)
- [ ] Laravel Pint formatting applied (`./vendor/bin/pint`)
- [ ] Frontend build successful (`npm run build`)
- [ ] Production database backup created and downloaded
- [ ] Deployment archive created
- [ ] All 20 files + 1 migration confirmed in archive
- [ ] Current production version confirmed as v0.2.7 or v0.2.8

---

## File Upload Section (FTP)

### Critical Files to Upload (21 Total)

#### Backend PHP Files (7 files)

**Controllers (2 files)**:

1. **`app/Http/Controllers/Api/FamilyMembersController.php`**
   - **Path**: `app/Http/Controllers/Api/FamilyMembersController.php`
   - **Purpose**: Fixed middle_name handling in 4 locations
   - **Lines Changed**: 148, 236, 276, 304
   - **Backup Recommended**: Yes

2. **`app/Http/Controllers/Api/Estate/GiftingController.php`**
   - **Path**: `app/Http/Controllers/Api/Estate/GiftingController.php`
   - **Purpose**: Fixed total estate value in Gifting and Trust Strategy tabs
   - **Lines Changed**: 267, 296-297, 364, 388-390
   - **Backup Recommended**: Yes

**Models (1 file)**:

3. **`app/Models/DCPension.php`**
   - **Path**: `app/Models/DCPension.php`
   - **Purpose**: Added expected_return_percent to $fillable and $casts
   - **Backup Recommended**: Yes

**Services (3 files)**:

4. **`app/Services/NetWorth/NetWorthService.php`**
   - **Path**: `app/Services/NetWorth/NetWorthService.php`
   - **Purpose**: Removed State Pension from Net Worth pension calculation
   - **Backup Recommended**: Yes

5. **`app/Services/Shared/CrossModuleAssetAggregator.php`**
   - **Path**: `app/Services/Shared/CrossModuleAssetAggregator.php`
   - **Purpose**: Fixed property double-percentage calculation bug
   - **Backup Recommended**: Yes

6. **`app/Services/UserProfile/PersonalAccountsService.php`**
   - **Path**: `app/Services/UserProfile/PersonalAccountsService.php`
   - **Purpose**: Fixed balance sheet double-percentage bug for all asset types
   - **Lines Changed**: 224-233, 235-249, 251-260, 262-271
   - **Backup Recommended**: Yes

**Migrations (1 file)**:

7. **`database/migrations/2025_11_17_074642_add_expected_return_percent_to_dc_pensions_table.php`**
   - **Path**: `database/migrations/2025_11_17_074642_add_expected_return_percent_to_dc_pensions_table.php`
   - **Purpose**: Adds expected_return_percent column to dc_pensions table
   - **Critical**: Must be uploaded before running migrations

#### Frontend Vue Files (13 files)

**Net Worth Module (2 files)**:

8. **`resources/js/components/NetWorth/Property/PropertyDetail.vue`**
   - **Path**: `resources/js/components/NetWorth/Property/PropertyDetail.vue`
   - **Purpose**: Shows both full property value and user's share

9. **`resources/js/components/NetWorth/PropertyCard.vue`**
   - **Path**: `resources/js/components/NetWorth/PropertyCard.vue`
   - **Purpose**: Enhanced property card display for joint properties

**Retirement Module (7 files)**:

10. **`resources/js/views/Retirement/RetirementDashboard.vue`**
    - **Path**: `resources/js/views/Retirement/RetirementDashboard.vue`
    - **Purpose**: Changed "Recommendations" to "Strategies" tab label

11. **`resources/js/components/Retirement/PensionCard.vue`**
    - **Path**: `resources/js/components/Retirement/PensionCard.vue`
    - **Purpose**: Made pension cards clickable to detail views

12. **`resources/js/views/Retirement/RetirementReadiness.vue`**
    - **Path**: `resources/js/views/Retirement/RetirementReadiness.vue`
    - **Purpose**: Fixed pension navigation to go to detail view

13. **`resources/js/views/Retirement/PensionInventory.vue`**
    - **Path**: `resources/js/views/Retirement/PensionInventory.vue`
    - **Purpose**: Unified pension form usage

14. **`resources/js/components/Retirement/DCPensionForm.vue`**
    - **Path**: `resources/js/components/Retirement/DCPensionForm.vue`
    - **Purpose**: Added retirement age validation (minimum 55)

15. **`resources/js/components/Retirement/StatePensionForm.vue`**
    - **Path**: `resources/js/components/Retirement/StatePensionForm.vue`
    - **Purpose**: Fixed state pension age field reactivity

16. **`resources/js/views/Retirement/PensionDetail.vue`** ⭐ **NEW FILE**
    - **Path**: `resources/js/views/Retirement/PensionDetail.vue`
    - **Purpose**: Comprehensive pension detail view component
    - **Note**: This is a NEW file, not replacing existing file

**Global Components (3 files)**:

17. **`resources/js/components/Footer.vue`**
    - **Path**: `resources/js/components/Footer.vue`
    - **Purpose**: Updated version from v0.2.7 to v0.2.9

18. **`resources/js/views/Version.vue`**
    - **Path**: `resources/js/views/Version.vue`
    - **Purpose**: Updated to v0.2.9 with comprehensive changelog

19. **`resources/js/views/Dashboard.vue`**
    - **Path**: `resources/js/views/Dashboard.vue`
    - **Purpose**: Removed "Welcome to TenGo" heading

**Router (1 file)**:

20. **`resources/js/router/index.js`**
    - **Path**: `resources/js/router/index.js`
    - **Purpose**: Added pension detail route `/pension/:type/:id`

#### Compiled Assets (1 directory)

21. **`public/build/`** (entire directory)
    - **Path**: `public/build/*`
    - **Purpose**: Compiled frontend assets (JS/CSS bundles)
    - **Action**: Upload entire directory, replacing existing
    - **Critical Files**:
      - `manifest.json`
      - `assets/*.js`
      - `assets/*.css`

### FTP Upload Instructions

#### Step 1: Connect to Production Server

**Using FileZilla or similar FTP client**:
- Host: `ftp.yourserver.com`
- Username: `your-ftp-username`
- Password: `your-ftp-password`
- Port: `21` (or `22` for SFTP)

#### Step 2: Navigate to Project Root

Navigate to: `/home/username/public_html/` (or wherever TenGo is installed)

**Verify you're in correct location**:
- You should see directories: `app/`, `resources/`, `public/`, `database/`, etc.
- Check for `.env` file presence

#### Step 3: Backup Existing Files (Recommended)

**Before uploading, backup these critical files on the server**:

Create a backup directory on the server:
- `/home/username/backups/tengo_v0.2.7_backup_nov17/`

**Copy existing files to backup** (if your FTP client supports it):
- `app/Http/Controllers/Api/FamilyMembersController.php`
- `app/Http/Controllers/Api/Estate/GiftingController.php`
- `app/Models/DCPension.php`
- `resources/js/components/Footer.vue`
- `public/build/` (entire directory)

**Note**: If FTP doesn't support copying, you can do this later via SSH.

#### Step 4: Upload Backend Files (7 files)

**Upload Order** (follow this sequence):

1. **Models First** (least risky):
   - Upload: `app/Models/DCPension.php`

2. **Services Next**:
   - Upload: `app/Services/NetWorth/NetWorthService.php`
   - Upload: `app/Services/Shared/CrossModuleAssetAggregator.php`
   - Upload: `app/Services/UserProfile/PersonalAccountsService.php`

3. **Controllers**:
   - Upload: `app/Http/Controllers/Api/FamilyMembersController.php`
   - Upload: `app/Http/Controllers/Api/Estate/GiftingController.php`

4. **Migration**:
   - Upload: `database/migrations/2025_11_17_074642_add_expected_return_percent_to_dc_pensions_table.php`

**File Permission Check**: Ensure uploaded files have `644` permissions (rw-r--r--)

#### Step 5: Upload Frontend Files (13 files)

**Upload Vue Components**:

1. **Net Worth Components**:
   - Upload: `resources/js/components/NetWorth/Property/PropertyDetail.vue`
   - Upload: `resources/js/components/NetWorth/PropertyCard.vue`

2. **Retirement Components**:
   - Upload: `resources/js/views/Retirement/RetirementDashboard.vue`
   - Upload: `resources/js/components/Retirement/PensionCard.vue`
   - Upload: `resources/js/views/Retirement/RetirementReadiness.vue`
   - Upload: `resources/js/views/Retirement/PensionInventory.vue`
   - Upload: `resources/js/components/Retirement/DCPensionForm.vue`
   - Upload: `resources/js/components/Retirement/StatePensionForm.vue`
   - Upload: `resources/js/views/Retirement/PensionDetail.vue` ⭐ **NEW**

3. **Global Components**:
   - Upload: `resources/js/components/Footer.vue`
   - Upload: `resources/js/views/Version.vue`
   - Upload: `resources/js/views/Dashboard.vue`

4. **Router**:
   - Upload: `resources/js/router/index.js`

#### Step 6: Upload Compiled Assets

**Critical**: Upload entire `public/build/` directory

1. **Delete existing `public/build/` on server** (or rename to `public/build_old/`)
2. **Upload fresh `public/build/` directory** from your local build
3. **Verify upload**:
   - `public/build/manifest.json` exists
   - `public/build/assets/*.js` files exist (multiple JS bundles)
   - `public/build/assets/*.css` files exist

**File Permissions**: Ensure `public/build/` has `755` permissions (rwxr-xr-x)

### Upload Verification Checklist

After upload completion, verify:

- [ ] All 7 backend PHP files uploaded successfully
- [ ] Migration file in `database/migrations/` directory
- [ ] All 13 frontend Vue files uploaded successfully
- [ ] `public/build/manifest.json` exists and is recent (today's date)
- [ ] `public/build/assets/` contains multiple JS/CSS files
- [ ] File permissions correct (PHP: 644, directories: 755)
- [ ] File sizes match local versions (spot-check 3-4 files)

---

## SSH Post-Deployment Commands

### Step 1: Connect to Production Server

```bash
ssh username@your-production-server.com
```

### Step 2: Navigate to Project Root

```bash
cd /home/username/public_html/  # Or wherever TenGo is installed
pwd  # Verify correct directory
ls -la  # Should see: app/, resources/, public/, .env, artisan
```

### Step 3: Verify Environment (Critical Safety Check)

**⚠️ ENSURE YOU'RE IN PRODUCTION ENVIRONMENT**

```bash
# Check environment variables
grep "APP_ENV" .env
grep "APP_DEBUG" .env
grep "APP_URL" .env

# Expected output:
# APP_ENV=production
# APP_DEBUG=false
# APP_URL=https://your-production-domain.com
```

**If APP_DEBUG=true or APP_ENV != production, STOP and fix before continuing.**

### Step 4: Run Database Migration

**⚠️ CRITICAL STEP - Adds expected_return_percent column**

```bash
php artisan migrate --force
```

**Expected Output**:
```
Running migrations.
2025_11_17_074642_add_expected_return_percent_to_dc_pensions_table ... DONE
```

**If migration fails**:
```bash
# Check what went wrong
php artisan migrate:status

# Check last 50 lines of Laravel log
tail -50 storage/logs/laravel.log

# If column already exists error:
# This is safe - it means the column was added before, continue
```

**Verify Migration Success**:
```bash
php artisan migrate:status | grep "2025_11_17"
# Should show: Ran    2025_11_17_074642_add_expected_return_percent_to_dc_pensions_table
```

### Step 5: Clear All Caches

**Clear application caches** (run ALL these commands):

```bash
# Clear configuration cache
php artisan config:clear

# Clear application cache
php artisan cache:clear

# Clear view cache
php artisan view:clear

# Clear route cache
php artisan route:clear

# Run optimize (regenerates optimized files)
php artisan optimize
```

**Expected Output**: Each command should return "Cache cleared successfully" or similar.

**If cache commands fail**:
```bash
# Check storage permissions
ls -la storage/
# Directories should be writable (drwxr-xr-x or drwxrwxr-x)

# Fix permissions if needed
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

### Step 6: Fix Permissions (if needed)

**Ensure Laravel can write to required directories**:

```bash
# Check current ownership
ls -la storage/ bootstrap/cache/

# Set correct permissions
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# If using a specific web server user (e.g., www-data, nginx):
# chown -R www-data:www-data storage/ bootstrap/cache/
# OR
# chown -R your-username:your-username storage/ bootstrap/cache/
```

**Verify**:
```bash
ls -la storage/logs/
# Should show: drwxrwxr-x (775 permissions)
```

### Step 7: Verify Database Column Added

**Check that expected_return_percent column exists**:

```bash
# Option A: Via Artisan Tinker
php artisan tinker
>>> Schema::hasColumn('dc_pensions', 'expected_return_percent');
>>> exit

# Expected output: true
```

```bash
# Option B: Via MySQL (if you have mysql command access)
mysql -u [DB_USER] -p [DB_NAME] -e "DESCRIBE dc_pensions;" | grep expected_return

# Expected output:
# expected_return_percent    decimal(5,2)    YES        NULL
```

### Step 8: Test Application Accessibility

**Quick smoke test** (before detailed verification):

```bash
# Test if application responds
curl -I https://your-production-domain.com

# Expected: HTTP/2 200 OK
```

**If you get 500 error**:
```bash
# Check Laravel logs immediately
tail -100 storage/logs/laravel.log

# Check web server logs
tail -100 /var/log/nginx/error.log
# OR
tail -100 /var/log/apache2/error.log
```

### Step 9: Restart PHP-FPM (if applicable)

**If your server uses PHP-FPM**:

```bash
# Check PHP version
php -v

# Restart PHP-FPM (adjust version number if needed)
sudo systemctl restart php8.2-fpm

# Verify service running
sudo systemctl status php8.2-fpm
```

**If not using PHP-FPM** (shared hosting):
- Skip this step
- Caches cleared in Step 5 should be sufficient

### Step 10: Final Verification Commands

```bash
# Verify migration status
php artisan migrate:status | tail -5

# Check application status
php artisan about

# Verify no configuration issues
php artisan config:cache
php artisan route:cache
```

**All commands should complete without errors.**

---

## Comprehensive Verification Checklist

### Phase 1: Basic Application Health

#### 1.1 Homepage & Authentication

- [ ] **Visit homepage**: `https://your-production-domain.com`
  - **Expected**: Homepage loads without errors
  - **Check**: No console errors in browser DevTools

- [ ] **Login as admin**: `admin@fps.com` / `admin123`
  - **Expected**: Login successful, redirects to dashboard
  - **Check**: No authentication errors

- [ ] **Check version in footer**:
  - **Expected**: Footer shows "v0.2.9"
  - **Location**: Bottom of any page

- [ ] **Visit Version page**: Navigate to About → Version
  - **Expected**: Version page shows v0.2.9 with November 17 changelog
  - **Check**: Changelog lists all 18 fixes

#### 1.2 Dashboard Check

- [ ] **Navigate to Dashboard**
  - **Expected**: No "Welcome to TenGo" heading visible
  - **Expected**: Module cards display correctly
  - **Expected**: Refresh button works

### Phase 2: Bug Fix Verification

#### 2.1 Family Member Creation (Bug Fix #1)

**Test: Create child without middle name**

1. Navigate to: **Family → Add Family Member**
2. Select relationship: **Child**
3. Enter:
   - First name: "TestChild"
   - Last name: "NoMiddleName"
   - **Leave middle name EMPTY**
   - Date of birth: Select any date
4. Click "Save"

**Expected Result**: ✅ Family member saves successfully, no "Undefined array key 'middle_name'" error

**Test: Create child with middle name**

1. Add another family member
2. Enter:
   - First name: "TestChild2"
   - Middle name: "Alexander"
   - Last name: "WithMiddle"
3. Click "Save"

**Expected Result**: ✅ Family member saves with middle name intact

#### 2.2 DC Pension Expected Return Field (Bug Fix #2)

**Test: Expected return percent saves and persists**

1. Navigate to: **Net Worth → Retirement Tab → Add Pension**
2. Select: **DC Pension**
3. Fill in form:
   - Pension name: "Test DC Pension"
   - Provider: "Test Provider"
   - Current fund value: £50,000
   - **Expected annual return: 5%** ← **KEY FIELD**
   - Retirement age: 67
4. Click "Save"
5. **Edit the same pension** (click edit icon)

**Expected Result**: ✅ Expected return field shows "5%" (not blank)

**Database Verification**:
```bash
# Via SSH
php artisan tinker
>>> App\Models\DCPension::latest()->first()->expected_return_percent;
>>> exit

# Expected output: 5.00
```

#### 2.3 State Pension Age Field (Bug Fix #3)

**Test: State pension age displays correctly**

1. Navigate to: **Retirement → Pension Inventory → Add Pension**
2. Select: **State Pension**
3. Enter:
   - Pension name: "State Pension"
   - **State pension age: 67** ← **KEY FIELD**
   - Estimated annual amount: £11,500
4. Click "Save"
5. **Edit the same pension**

**Expected Result**: ✅ State pension age field shows "67" (not blank, not overwritten)

#### 2.4 Retirement Age Validation (Bug Fix #4)

**Test: Minimum age validation works**

1. Navigate to: **Retirement → Add DC Pension**
2. Fill form with retirement age: **50** (below minimum)
3. Try to save

**Expected Result**:
- ✅ Red border around retirement age field
- ✅ Error message: "DC pensions can only be accessed from 55, so this is the youngest age you can enter"
- ✅ Form does NOT submit

**Test: Valid age accepted**

1. Change retirement age to: **55**
2. Click save

**Expected Result**: ✅ Form submits successfully, no validation error

#### 2.5 Property Value Display (Bug Fix #5)

**Test: Property detail view shows both values**

1. Navigate to: **Net Worth → Properties**
2. **Create joint property** or **select existing joint property**
3. Click property card to view details

**Expected Result**:
- ✅ "Full Property Value" displayed (e.g., £400,000)
- ✅ "Your Share (50%)" displayed separately (e.g., £200,000)
- ✅ Full value highlighted in blue
- ✅ Both values visible in Key Metrics and Valuation sections

**Test: Property card shows both values**

1. View property cards in Net Worth overview

**Expected Result** (for joint property):
- ✅ Card shows "Full Property Value: £400,000"
- ✅ Card shows "Your Share (50%): £200,000"

#### 2.6 Estate Gifting/Trust Strategy Total Estate Value (Bug Fix #6)

**Test: Total estate value matches IHT Planning tab**

1. Navigate to: **Estate Planning → IHT Planning**
2. Note the "Total Gross Estate" value (e.g., £750,000)
3. Navigate to: **Estate Planning → Gifting Strategy**
4. Check "Total Estate Value" at top

**Expected Result**: ✅ Total estate value in Gifting Strategy matches IHT Planning total

5. Navigate to: **Estate Planning → Trust Strategy**
6. Check "Total Estate Value"

**Expected Result**: ✅ Total estate value in Trust Strategy matches IHT Planning total

#### 2.7 User Profile Balance Sheet (Bug Fix #7)

**Test: Asset values correct (no double-percentage)**

**Login as test user with joint assets**:
- User: `demo@fps.com` / `password`

1. Navigate to: **User Profile → Balance Sheet**
2. Check asset values:
   - **Investment accounts**: Value should be user's share, NOT multiplied by percentage again
   - **Properties**: Value should be user's share (e.g., £200,000 for 50% of £400,000)
   - **Business interests**: Value should be user's share
   - **Chattels**: Value should be user's share

**How to verify correct calculation**:
- If you have a joint property worth £400,000 (50% ownership)
- Database stores: £200,000 (your share)
- **Correct display**: £200,000
- **Incorrect (old bug)**: £100,000 (£200,000 × 50% = double reduction)

**Expected Result**: ✅ All asset values display as stored in database (user's share), no additional percentage multiplication

#### 2.8 State Pension in Net Worth (Bug Fix #8)

**Test: State pension excluded from Net Worth calculation**

1. Navigate to: **Dashboard → Net Worth Card**
2. Check "Total Pensions" value
3. Note: This should **NOT include State Pension**

**Manual Verification**:
- Add up DC pensions: £X
- Add up DB pensions: £Y
- State pension: £Z
- **Expected Net Worth pension total**: £X + £Y (NOT including £Z)

**Rationale**: State Pension is not accessible as capital, so it shouldn't be in Net Worth

#### 2.9 Virtual Records Removed (Bug Fix #9)

**Test: No virtual family member records created**

1. Create a new spouse via User Profile
2. Enter email: `newspouse@test.com`
3. Accept data sharing

**Expected Result**:
- ✅ Spouse user account created
- ✅ Reciprocal family_member records created for BOTH users
- ✅ **NO extra "virtual" family_member records** created

**Database Verification**:
```bash
# Via SSH
php artisan tinker
>>> $user = App\Models\User::where('email', 'demo@fps.com')->first();
>>> $user->familyMembers()->count();
>>> exit

# Expected: Should be exact count of real family members, no duplicates
```

#### 2.10 Reciprocal Spouse Deletion (Bug Fix #10)

**Test: Unlinking spouse deletes both family_member records**

1. Navigate to: **User Profile → Edit**
2. **Unlink spouse** (remove spouse email)
3. Save changes

**Expected Result**:
- ✅ Both family_member records deleted (user's and spouse's)
- ✅ Spouse user account remains (not deleted)
- ✅ Both users' marital status updated

**Database Verification**:
```bash
php artisan tinker
>>> $user = App\Models\User::where('email', 'demo@fps.com')->first();
>>> $user->familyMembers()->where('relationship', 'spouse')->count();
>>> exit

# Expected: 0
```

### Phase 3: UI/UX Enhancement Verification

#### 3.1 Pension Card Navigation (Enhancement #1)

**Test: Clicking pension card navigates to detail view**

1. Navigate to: **Retirement → Pension Inventory**
2. View list of pensions (DC, DB, or State)
3. **Click on pension card body** (not buttons)

**Expected Result**:
- ✅ Navigates to pension detail view (`/pension/dc/123` or similar)
- ✅ Detail view displays comprehensive pension information
- ✅ Back button works

**Test: Action buttons still work**

1. Click **Edit button** on pension card

**Expected Result**: ✅ Opens edit modal (does NOT navigate to detail view)

2. Click **Delete button**

**Expected Result**: ✅ Shows delete confirmation (does NOT navigate)

3. Click **Expand button** (if visible)

**Expected Result**: ✅ Expands card to show more details inline

#### 3.2 Pension Cards from Retirement Readiness (Enhancement #2)

**Test: Pension cards on Retirement Readiness navigate correctly**

1. Navigate to: **Retirement → Retirement Readiness**
2. Scroll to pension list section
3. **Click on a pension card**

**Expected Result**:
- ✅ Navigates to detail view (`/pension/dc/123`)
- ✅ **Does NOT switch to Pension Inventory tab** (old bug)

#### 3.3 Retirement Tab Renamed (Enhancement #3)

**Test: Tab label changed**

1. Navigate to: **Retirement → Dashboard**
2. Check tab labels

**Expected Result**:
- ✅ Tab shows "Strategies" (not "Recommendations")
- ✅ Tab content unchanged (same strategy information)

#### 3.4 Dashboard Cleanup (Enhancement #4)

**Test: Welcome heading removed**

1. Navigate to: **Dashboard**

**Expected Result**:
- ✅ **NO "Welcome to TenGo" heading** visible
- ✅ Refresh button visible at top
- ✅ Module cards display correctly
- ✅ Cleaner, more professional layout

#### 3.5 Pension Detail View (New Component)

**Test: New PensionDetail.vue component works**

1. Navigate to: **Retirement → Pension Inventory**
2. Click on any pension card
3. Verify detail view displays:
   - **Key Metrics** section (fund value, contributions, etc.)
   - **Pension Information** section (provider, type, etc.)
   - **Edit button** works (opens UnifiedPensionForm)
   - **Back button** returns to previous view

**Test detail view for each type**:

**DC Pension**:
- Shows: Fund value, contributions, retirement age, **expected return percent** ← NEW
- Shows: Provider, pension type, account number

**DB Pension**:
- Shows: Pension name, scheme name, accrual rate
- Shows: Expected annual pension at retirement

**State Pension**:
- Shows: State pension age, estimated annual amount
- Shows: Years of contributions

**Expected Result**: ✅ All pension types display correctly in detail view

### Phase 4: Regression Testing

**Ensure existing functionality still works**

#### 4.1 Existing Pensions Display

- [ ] Navigate to: **Retirement → Pension Inventory**
- [ ] **Expected**: All existing DC, DB, and State pensions display
- [ ] **Expected**: Values, dates, and details unchanged

#### 4.2 Existing Properties Display

- [ ] Navigate to: **Net Worth → Properties**
- [ ] **Expected**: All existing properties display
- [ ] **Expected**: Valuations, mortgages unchanged
- [ ] **Expected**: Joint properties show correct ownership

#### 4.3 Existing Family Members Display

- [ ] Navigate to: **Family → Family Members**
- [ ] **Expected**: All existing family members display
- [ ] **Expected**: Names split correctly (first/last)
- [ ] **Expected**: Middle names preserved if they existed

#### 4.4 Estate Planning Still Works

- [ ] Navigate to: **Estate Planning → IHT Planning**
- [ ] **Expected**: Total estate value displays
- [ ] **Expected**: All assets and liabilities visible
- [ ] **Expected**: Calculations correct

#### 4.5 User Profile Still Works

- [ ] Navigate to: **User Profile → All Tabs**
- [ ] **Expected**: Personal info displays
- [ ] **Expected**: Income & occupation displays
- [ ] **Expected**: Expenditure displays
- [ ] **Expected**: Balance sheet displays

---

## Post-Verification Commands

**After all manual verification passes, run these final checks**:

### Check Application Logs (No Errors)

```bash
# SSH into server
ssh username@your-production-server.com
cd /path/to/tengo

# Check last 100 lines of Laravel log
tail -100 storage/logs/laravel.log

# Expected: No PHP errors, no "Undefined array key" errors, no 500 errors
```

**Look for**:
- ❌ `Undefined array key 'middle_name'` → Should NOT appear
- ❌ `Column not found: expected_return_percent` → Should NOT appear
- ❌ Any stack traces or exceptions

### Verify Migration Status

```bash
php artisan migrate:status | grep "2025_11_17"

# Expected output:
# Ran    2025_11_17_074642_add_expected_return_percent_to_dc_pensions_table
```

### Check Database Column Exists

```bash
php artisan tinker
>>> Schema::hasColumn('dc_pensions', 'expected_return_percent');
>>> exit

# Expected output: true
```

### Final Cache Optimization

```bash
# Cache configuration for production performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Verify caches created
ls -la bootstrap/cache/
# Should see: config.php, routes-v7.php, services.php
```

---

## Rollback Plan

**If critical issues occur after deployment, follow this emergency rollback procedure.**

### When to Rollback

Rollback immediately if:
- Application returns 500 errors consistently
- Database corruption detected
- Critical features completely broken
- Data loss suspected
- Errors in logs indicating major issues

**DO NOT rollback for**:
- Minor UI glitches (can be fixed with hotfix)
- Single isolated user reports (investigate first)
- Cache issues (clear cache instead)

### Rollback Procedure

#### Step 1: Restore Database from Backup

**Option A: Via Admin Panel** (if accessible)

1. Login: `admin@fps.com` / `admin123`
2. Navigate: **Admin → System → Restore Database**
3. Upload: `tengo_prod_backup_before_v0.2.9_Nov17.sql`
4. Click: "Restore Database"
5. Wait for confirmation

**Option B: Via SSH** (if admin panel inaccessible)

```bash
# SSH into server
ssh username@your-production-server.com
cd /path/to/tengo

# Restore database
mysql -u [DB_USER] -p [DB_NAME] < ~/backups/tengo_backup_20251117_*.sql

# Verify restore completed
mysql -u [DB_USER] -p [DB_NAME] -e "SELECT COUNT(*) FROM users;"
```

#### Step 2: Restore Files via FTP

**If you created backups on server before upload**:

1. Connect via FTP
2. Navigate to: `/home/username/backups/tengo_v0.2.7_backup_nov17/`
3. Copy all backed-up files back to their original locations

**If no backups on server**:

You'll need to:
1. **Git checkout previous version** (see Step 3)
2. **Re-upload v0.2.7 files** from your local repository

#### Step 3: Rollback Code to v0.2.7 (Git Method)

**On your LOCAL machine**:

```bash
cd /Users/Chris/Desktop/fpsApp/tengo

# Find last v0.2.7 commit
git log --oneline --all | grep "v0.2.7"

# Checkout that commit (example commit hash)
git checkout fb5a98f  # Replace with actual commit hash

# Rebuild assets for v0.2.7
npm run build

# Create rollback archive
tar -czf tengo-rollback-v0.2.7.tar.gz public/build/

# Upload public/build/ to production via FTP
```

#### Step 4: Reverse Migration (Only if Database Restore Failed)

**⚠️ ONLY USE IF DATABASE BACKUP RESTORE NOT AVAILABLE**

```bash
# SSH into server
ssh username@your-production-server.com
cd /path/to/tengo

# Rollback the single migration
php artisan migrate:rollback --step=1

# Verify migration rolled back
php artisan migrate:status | grep "2025_11_17"
# Expected: Should NOT show "Ran" status
```

**Note**: The migration's `down()` method drops the `expected_return_percent` column. Any data in that column will be lost.

#### Step 5: Clear All Caches

```bash
# Clear all Laravel caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

# Restart PHP-FPM (if applicable)
sudo systemctl restart php8.2-fpm
```

#### Step 6: Verify Rollback Success

```bash
# Test application
curl -I https://your-production-domain.com
# Expected: HTTP/2 200 OK

# Check logs
tail -50 storage/logs/laravel.log
# Expected: No errors

# Verify version
# Visit homepage and check footer
# Expected: v0.2.7 or v0.2.8 (previous version)
```

#### Step 7: Document Rollback Reason

**Create incident report**:

1. What went wrong?
2. When did it occur?
3. What error messages appeared?
4. What data was affected?
5. Was rollback successful?
6. What needs to be fixed before retry?

**Save logs**:
```bash
# Save error logs for analysis
cp storage/logs/laravel.log ~/rollback_logs_$(date +%Y%m%d_%H%M%S).log
```

### Rollback Time Estimate

- **Database restore**: 2-5 minutes
- **File restoration**: 5-10 minutes
- **Cache clearing**: 2 minutes
- **Verification**: 5 minutes
- **Total**: 15-25 minutes

---

## Troubleshooting

### Common Issues and Solutions

#### Issue 1: "Column not found: expected_return_percent"

**Symptom**: Error when viewing or editing DC pensions

**Cause**: Migration didn't run or failed

**Solution**:
```bash
# SSH into server
php artisan migrate:status | grep "2025_11_17"

# If not showing "Ran":
php artisan migrate --force

# If shows "Ran" but column missing (rare):
php artisan tinker
>>> Schema::hasColumn('dc_pensions', 'expected_return_percent');
>>> exit

# If returns false, manually add column:
mysql -u [DB_USER] -p [DB_NAME]
ALTER TABLE dc_pensions ADD COLUMN expected_return_percent DECIMAL(5,2) NULL AFTER pension_type;
EXIT;
```

#### Issue 2: "Undefined array key 'middle_name'"

**Symptom**: Error when creating family members

**Cause**: Old FamilyMembersController.php still in place, new one not uploaded

**Solution**:
```bash
# Verify file upload via FTP
# Re-upload: app/Http/Controllers/Api/FamilyMembersController.php

# Clear cache
php artisan cache:clear
php artisan config:clear

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

#### Issue 3: Assets Not Loading (CSS/JS 404 Errors)

**Symptom**: Website displays but no styling, browser console shows 404 errors

**Cause**: `public/build/` directory not uploaded or manifest.json missing

**Solution**:
```bash
# Via SSH, check if manifest exists
ls -la public/build/manifest.json

# If missing, re-upload entire public/build/ directory via FTP
# Ensure files uploaded to correct location

# Check file permissions
chmod -R 755 public/build/

# Clear Laravel cache
php artisan cache:clear
php artisan view:clear
```

#### Issue 4: Old Version Still Showing in Footer

**Symptom**: Footer shows v0.2.7 or v0.2.8 instead of v0.2.9

**Cause**: Vue component not uploaded or browser cache

**Solution**:
```bash
# Verify file uploaded
# Re-upload: resources/js/components/Footer.vue

# Rebuild assets (on local machine)
npm run build

# Re-upload: public/build/ (entire directory)

# On server, clear cache
php artisan cache:clear
php artisan view:clear

# In browser
# Hard refresh: Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)
# Or clear browser cache
```

#### Issue 5: Pension Cards Not Clickable

**Symptom**: Clicking pension card does nothing

**Cause**: PensionCard.vue or router not updated

**Solution**:
```bash
# Verify files uploaded via FTP:
# 1. resources/js/components/Retirement/PensionCard.vue
# 2. resources/js/views/Retirement/PensionDetail.vue (NEW file)
# 3. resources/js/router/index.js

# Rebuild assets (on local machine)
npm run build

# Re-upload: public/build/ directory

# Clear cache on server
php artisan cache:clear
php artisan view:clear
```

#### Issue 6: Property Values Still Doubled

**Symptom**: Joint property showing £100,000 instead of £200,000

**Cause**: Service files not uploaded

**Solution**:
```bash
# Verify files uploaded via FTP:
# 1. app/Services/Shared/CrossModuleAssetAggregator.php
# 2. app/Services/UserProfile/PersonalAccountsService.php

# Clear cache
php artisan cache:clear
php artisan config:clear

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm

# Test again after clearing cache
```

#### Issue 7: Estate Strategy Totals Still Wrong

**Symptom**: Gifting/Trust Strategy showing different total than IHT Planning

**Cause**: GiftingController.php not uploaded

**Solution**:
```bash
# Verify file uploaded:
# app/Http/Controllers/Api/Estate/GiftingController.php

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Test again
```

#### Issue 8: Permission Denied Errors

**Symptom**: "Permission denied" or "Unable to write" errors

**Cause**: Incorrect file permissions on storage/ or bootstrap/cache/

**Solution**:
```bash
# Fix permissions
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# If using specific web server user
chown -R www-data:www-data storage/
chown -R www-data:www-data bootstrap/cache/

# Verify permissions
ls -la storage/
# Should show: drwxrwxr-x
```

#### Issue 9: 500 Internal Server Error

**Symptom**: Application returns HTTP 500 error

**Cause**: Multiple possible causes

**Solution**:
```bash
# Step 1: Check Laravel logs
tail -100 storage/logs/laravel.log
# Look for PHP errors, stack traces

# Step 2: Check web server logs
tail -100 /var/log/nginx/error.log
# OR
tail -100 /var/log/apache2/error.log

# Step 3: Check .env file
cat .env | grep -E "APP_|DB_"
# Ensure APP_DEBUG=false, correct database credentials

# Step 4: Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Step 5: Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

#### Issue 10: State Pension Age Still Not Saving

**Symptom**: State pension age field blank after saving

**Cause**: StatePensionForm.vue not uploaded or cached

**Solution**:
```bash
# Verify file uploaded:
# resources/js/components/Retirement/StatePensionForm.vue

# Rebuild assets (local machine)
npm run build

# Re-upload: public/build/

# Clear browser cache completely
# Hard refresh page
```

---

## Performance Monitoring

### After Deployment, Monitor These Metrics

#### Application Performance

**Via Browser DevTools** (first 24 hours):
- Page load times (should be < 3 seconds)
- API response times (should be < 500ms)
- No console errors
- No memory leaks

**Via Server Monitoring**:
```bash
# Check CPU usage
top -bn1 | grep "Cpu(s)"

# Check memory usage
free -m

# Check PHP-FPM processes
ps aux | grep php-fpm | wc -l

# Check error rate in logs
tail -1000 storage/logs/laravel.log | grep -i error | wc -l
```

#### Database Performance

```bash
# Check active connections
mysql -u [DB_USER] -p -e "SHOW PROCESSLIST;"

# Check slow queries (if slow query log enabled)
mysql -u [DB_USER] -p -e "SELECT * FROM mysql.slow_log LIMIT 10;"
```

#### Expected Metrics (Post-Deployment)

- **Application Response Time**: < 500ms (no degradation)
- **Database Query Time**: < 100ms average (unchanged)
- **Error Rate**: < 0.1% (should be near zero)
- **Asset Load Time**: < 2 seconds (first load, < 100ms cached)

**If performance degrades**:
1. Check Laravel logs for slow queries
2. Clear OPcache: `sudo systemctl restart php8.2-fpm`
3. Check memcached/redis status
4. Review recent changes in code

---

## Support Contacts

### Deployment Team

**Deployment Lead**: Chris Jones
**Email**: chris@example.com
**Phone**: [Your contact]

### Test Accounts

**Admin Access**:
- Email: `admin@fps.com`
- Password: `admin123`
- User ID: 1016

**Demo User**:
- Email: `demo@fps.com`
- Password: `password`

**Test Users** (Development):
- Chris Jones (ID: 1160) - Primary user with full data
- Ang Jones (ID: 1161) - Spouse with data sharing

### Escalation Procedure

**If Critical Issue Arises**:

1. **Immediate** (0-5 minutes):
   - Check Laravel logs: `tail -100 storage/logs/laravel.log`
   - Check web server logs
   - Verify services running: `systemctl status php8.2-fpm nginx`

2. **Short-term** (5-15 minutes):
   - Document issue with screenshots
   - Save error logs
   - Attempt cache clearing
   - Test if issue affects all users or specific scenarios

3. **If Unresolvable** (15+ minutes):
   - **DECISION POINT**: Rollback or debug?
   - If affecting all users: **ROLLBACK IMMEDIATELY**
   - If affecting specific feature: Document and create hotfix

4. **Post-Incident**:
   - Document root cause
   - Create incident report
   - Plan hotfix or rollback retry

---

## Documentation Updates

**After successful deployment, update these documents**:

### Internal Documentation

1. **CLAUDE.md**:
   - Update "Current Version" to v0.2.9
   - Update "Last Updated" date
   - Mark resolved issues as ✅
   - Document any new learnings

2. **README.md**:
   - Update version badge
   - Add v0.2.9 to changelog
   - Update deployment date

3. **Version.vue**:
   - Already updated in this deployment
   - Verify it displays correctly

### Deployment History

**Record deployment in your internal tracking**:

```
Deployment: v0.2.9 (November 17 Patch)
Date: [Deployment Date]
Time: [Start Time] - [End Time]
Duration: [Minutes]
Method: FTP + SSH
Status: [Success/Partial/Rollback]
Issues: [None/List any issues]
Deployed By: [Your Name]
```

---

## Final Deployment Checklist

### Pre-Deployment ✅

- [ ] All tests passing locally
- [ ] Laravel Pint formatting applied
- [ ] Frontend build successful (`npm run build`)
- [ ] Production database backup created and downloaded
- [ ] Deployment archive created
- [ ] All 20 files + 1 migration confirmed in filesUpload17Nov.md
- [ ] Current production version verified

### During Deployment ✅

#### FTP Upload Phase
- [ ] Connected to production server via FTP
- [ ] Navigated to correct project root
- [ ] Backed up existing files (optional but recommended)
- [ ] Uploaded 7 backend PHP files
- [ ] Uploaded 1 migration file
- [ ] Uploaded 13 frontend Vue files
- [ ] Uploaded entire `public/build/` directory
- [ ] Verified file sizes match local versions
- [ ] Verified file permissions correct (644 for files, 755 for directories)

#### SSH Command Phase
- [ ] Connected to production server via SSH
- [ ] Navigated to project root
- [ ] Verified environment (APP_ENV=production, APP_DEBUG=false)
- [ ] Ran migration: `php artisan migrate --force`
- [ ] Migration successful (expected_return_percent column added)
- [ ] Cleared config cache: `php artisan config:clear`
- [ ] Cleared application cache: `php artisan cache:clear`
- [ ] Cleared view cache: `php artisan view:clear`
- [ ] Cleared route cache: `php artisan route:clear`
- [ ] Ran optimize: `php artisan optimize`
- [ ] Fixed permissions (if needed): `chmod -R 775 storage/`
- [ ] Restarted PHP-FPM (if applicable): `sudo systemctl restart php8.2-fpm`
- [ ] Verified services running

### Post-Deployment Verification ✅

#### Phase 1: Basic Health
- [ ] Homepage loads without errors
- [ ] Admin login successful
- [ ] Version footer shows v0.2.9
- [ ] Version page shows v0.2.9 changelog
- [ ] Dashboard displays correctly (no "Welcome to TenGo")

#### Phase 2: Bug Fixes
- [ ] Family member creation works (with/without middle_name)
- [ ] DC pension expected return field saves and persists
- [ ] State pension age field displays correctly
- [ ] Retirement age validation works (min 55, error message displays)
- [ ] Property detail view shows both full value and user's share
- [ ] Estate Gifting Strategy total matches IHT Planning total
- [ ] Estate Trust Strategy total matches IHT Planning total
- [ ] User Profile balance sheet shows correct values (no double-percentage)
- [ ] Net Worth excludes State Pension from pension total
- [ ] No virtual family member records created
- [ ] Reciprocal spouse deletion works correctly

#### Phase 3: UI Enhancements
- [ ] Pension cards clickable to detail views
- [ ] Pension cards from Retirement Readiness navigate correctly (not to inventory tab)
- [ ] Retirement tab shows "Strategies" label
- [ ] Dashboard has no "Welcome to TenGo" heading
- [ ] PensionDetail component displays for DC/DB/State pensions

#### Phase 4: Regression Tests
- [ ] Existing pensions display correctly
- [ ] Existing properties display correctly
- [ ] Existing family members display correctly
- [ ] Estate planning still works
- [ ] User profile still works
- [ ] All previous functionality unchanged

#### Phase 5: Technical Verification
- [ ] No errors in Laravel logs (last 100 lines)
- [ ] Migration status shows new migration "Ran"
- [ ] Database column `expected_return_percent` exists
- [ ] Configuration cached for production performance
- [ ] Routes cached for production performance
- [ ] Views cached for production performance

### Monitoring (First 24 Hours) ✅

- [ ] Check error logs hourly for first 4 hours
- [ ] Monitor application performance metrics
- [ ] Track user-reported issues (if any)
- [ ] Verify no database connection issues
- [ ] Confirm no unusual server load

---

## Success Criteria

**Deployment considered successful when**:

✅ **All 21 files uploaded** (20 files + 1 migration)
✅ **Migration ran successfully** (expected_return_percent column added)
✅ **All 10 bug fixes verified working**
✅ **All 5 UI enhancements verified working**
✅ **No regression issues** (existing features work)
✅ **No errors in logs** (first hour post-deployment)
✅ **Version footer shows v0.2.9**
✅ **All caches cleared and optimized**
✅ **Performance unchanged** (no degradation)
✅ **Test accounts functional** (admin and demo)

---

## Deployment Timeline Estimate

**Total Estimated Time: 45-60 minutes**

| Phase | Task | Duration |
|-------|------|----------|
| **Pre-Deployment** | Code formatting, build, backup, archive creation | 10 minutes |
| **FTP Upload** | Upload 21 files + built assets | 10 minutes |
| **SSH Commands** | Migration, cache clearing, permissions | 5 minutes |
| **Verification** | Comprehensive testing of all fixes | 20-30 minutes |
| **Documentation** | Update internal docs, record deployment | 5 minutes |

**Note**: Timeline assumes no issues. Add 15-30 minutes for troubleshooting if issues arise.

---

## Post-Deployment Actions

### Immediate (Within 1 Hour)

1. **Monitor error logs**:
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Test critical user flows**:
   - User login/logout
   - Family member creation
   - Pension creation/editing
   - Property viewing

3. **Verify no user-reported issues**

### Short-Term (Within 24 Hours)

1. **Review error logs** (any patterns?)
2. **Check performance metrics** (any degradation?)
3. **Verify all bug fixes working in production**
4. **Monitor user feedback**

### Medium-Term (Within 1 Week)

1. **Update documentation** (if any issues found)
2. **Plan hotfix** (if minor issues discovered)
3. **Gather user feedback** on improvements
4. **Review deployment process** (any improvements needed?)

---

## Related Documentation

**Reference these documents for additional context**:

1. **filesUpload17Nov.md** - Complete file list and changes
2. **Nov17Debug.md** - Detailed debugging session documentation
3. **DEPLOYMENT_PATCH_v0.2.9.md** - Full v0.2.9 deployment guide (major release)
4. **CLAUDE.md** - Development guidelines and resolved issues
5. **README.md** - Project overview and changelog

---

## Version History

| Version | Date | Description |
|---------|------|-------------|
| v0.2.7 | November 11, 2025 | Property and family improvements |
| v0.2.8 | November 13, 2025 | Retirement forms and joint mortgage fix |
| **v0.2.9** | **November 17, 2025** | **18 bug fixes and UI enhancements** ← **CURRENT** |

---

## Deployment Status

**Status**: ✅ **Ready for Production Deployment**
**Risk Level**: ⚠️ **LOW** (Bug fixes, no breaking changes)
**Rollback Plan**: ✅ **Available** (database restore + file restore)
**Testing**: ✅ **Complete** (all fixes verified in development)
**Documentation**: ✅ **Complete** (this guide + filesUpload17Nov.md)

---

## Approval

**Deployment Package Prepared By**: Claude Code
**Preparation Date**: November 17, 2025
**Review Status**: Ready for Production
**Approved By**: [Deployment Lead Name]
**Approval Date**: [Date]

---

## Notes

- This is a **patch deployment** (bug fixes and improvements)
- **No breaking changes** - existing data unaffected
- **One database migration** - adds single column, safe to run
- **Frontend rebuild required** - all Vue changes in compiled assets
- **Cache clearing required** - ensures new code loads
- **Rollback available** - database backup + file backup method

**Confidence Level**: **HIGH** - All changes tested in development, no schema-breaking modifications, clear rollback path available.

---

**Generated**: November 17, 2025
**Deployment Type**: Patch Update (v0.2.7 → v0.2.9)
**Deployment Method**: FTP + SSH
**Target Environment**: Production
**Estimated Downtime**: < 5 minutes (during migration and cache clearing)

---

🤖 **Built with [Claude Code](https://claude.com/claude-code)**

---

**END OF DEPLOYMENT GUIDE**
