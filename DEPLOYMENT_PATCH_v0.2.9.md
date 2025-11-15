# Deployment Patch v0.2.9

**Date**: November 15, 2025
**Type**: Bug Fix & Feature Enhancement Patch
**Status**: Ready for Deployment

---

## Overview

This patch addresses two critical issues:
1. **Estate Plan (Plans Module)** - Spouse assets and liabilities not displaying
2. **IHT Planning Module** - Non-mortgage liabilities not showing in breakdown

---

## Changes Summary

### 1. Estate Plan - Spouse Assets Integration (Plans Module)

**Issue**: The Comprehensive Estate Plan (accessed via Plans → Estate Plan) was only showing the user's assets and liabilities, not spouse data, even when data sharing was enabled.

**Impact**: Married couples could not see combined estate planning data.

**Files Modified**:
- `app/Services/Estate/ComprehensiveEstatePlanService.php`

**Changes**:
1. Added spouse asset gathering when data sharing is enabled (lines 65-71)
2. Updated `buildBalanceSheet()` method to include user, spouse, and combined sections
3. Updated `buildEstateOverview()` method to show breakdown for both spouses
4. Updated `buildEstateBreakdown()` method to display separate and combined estate data

**Result**: Estate Plan now shows:
- User's balance sheet, overview, and breakdown
- Spouse's balance sheet, overview, and breakdown (when data sharing enabled)
- Combined totals for complete estate view

---

### 2. IHT Planning - Liability Display Fix

**Issue**: Non-mortgage liabilities (credit cards, loans, hire purchase, etc.) were not displaying in the IHT Planning tab, even though mortgages were showing correctly.

**Root Cause**: The `formatLiabilitiesBreakdown()` method was using incorrect field names:
- Using `$liability->amount` instead of `$liability->current_balance`
- Using `$liability->description` instead of `$liability->liability_name`

**Impact**: Users could not see their complete liability picture in IHT calculations.

**Files Modified**:
- `app/Http/Controllers/Api/Estate/IHTController.php`

**Changes**:
1. Line 302: Changed `if ($liability->amount > 0)` to `if ($liability->current_balance > 0)`
2. Line 306: Changed `$liability->description` to `$liability->liability_name`
3. Lines 307-312: Changed all references from `amount` to `current_balance`
4. Lines 368-379: Applied same fixes to spouse liability formatting

**Result**: All liabilities now display correctly in IHT Planning breakdown.

---

## Files Changed

### Backend Services
```
app/Services/Estate/ComprehensiveEstatePlanService.php
  - Added spouse asset gathering
  - Enhanced buildBalanceSheet() for spouse data
  - Enhanced buildEstateOverview() for spouse data
  - Enhanced buildEstateBreakdown() for spouse data
```

### Backend Controllers
```
app/Http/Controllers/Api/Estate/IHTController.php
  - Fixed liability field names in formatLiabilitiesBreakdown()
  - User liabilities: lines 302-313
  - Spouse liabilities: lines 368-379
```

---

## Testing Performed

### Manual Testing

**Test User**: Chris Jones (ID: 1160)
**Test Spouse**: Ang Jones (ID: 1161)

#### Estate Plan (Plans Module)
- ✅ User assets displaying correctly
- ✅ Spouse assets displaying correctly (3 properties, 1 investment)
- ✅ Combined totals calculating accurately
- ✅ Balance sheet showing separate and combined sections
- ✅ Estate overview showing both spouses' breakdowns

#### IHT Planning Module
- ✅ Mortgages displaying correctly for both spouses
- ✅ Credit card liability (Lloyds - £11,000) now displaying
- ✅ Hire purchase liability (Merc - £2,395) now displaying
- ✅ Liability totals calculating correctly
- ✅ Projected liabilities accounting for all debt types

---

## Database Changes

**None** - No migrations or schema changes required.

---

## Deployment Steps

### 1. Backup
```bash
# Via Admin Panel (admin@fps.com / admin123456)
# Navigate to: Admin → System → Backup Database
# Download backup file to local machine
```

### 2. Pull Latest Code
```bash
cd /path/to/tengo
git pull origin main
```

### 3. Verify No Dependencies
```bash
# This patch requires no new dependencies
composer install --no-dev
npm install
```

### 4. Build Frontend Assets
```bash
npm run build
```

### 5. Clear Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 6. Restart Services
```bash
# Restart PHP-FPM
sudo systemctl restart php8.2-fpm

# Restart Nginx
sudo systemctl restart nginx

# Restart Queue Worker (if running)
sudo systemctl restart laravel-worker
```

---

## Rollback Plan

If issues occur, rollback to previous version:

```bash
# 1. Restore from backup (via Admin Panel)

# 2. Checkout previous version
git checkout ad7fc9a

# 3. Rebuild assets
npm run build

# 4. Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 5. Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

---

## Post-Deployment Verification

### 1. Estate Plan (Plans Module)
1. Log in as Chris Jones
2. Navigate to Dashboard → Plans Card → Estate Plan
3. Verify:
   - User balance sheet displays
   - Spouse balance sheet displays
   - Combined totals are accurate
   - All asset types visible for both spouses

### 2. IHT Planning Module
1. Navigate to Estate Planning → IHT Planning
2. Verify:
   - Mortgages display for both spouses
   - Credit card liability (£11,000) displays
   - Hire purchase liability (£2,395) displays
   - Total liabilities match sum of all items

---

## Known Issues

**None** - All critical issues resolved.

---

## Version History

- **v0.2.8** - Retirement form improvements and property management enhancements
- **v0.2.9** - Estate Plan spouse integration and IHT liability display fix

---

## Support

If issues arise post-deployment:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Check browser console for JavaScript errors
3. Verify cache was cleared properly
4. Confirm assets were built for production (`NODE_ENV=production`)
5. Test with different user accounts

---

**Generated**: November 15, 2025
**Author**: Claude Code
**Status**: ✅ Ready for Production Deployment
