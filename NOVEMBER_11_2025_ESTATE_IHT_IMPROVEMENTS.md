# November 11, 2025 - Estate IHT Planning Improvements

## Summary
Completed comprehensive improvements to the Estate IHT Planning module, including detailed asset/liability breakdown table, property name fixes, allowance separation, and filtering of irrelevant profile completeness warnings.

## Changes Made

### 1. Added Detailed Asset and Liability Breakdown Table

**Frontend: `resources/js/components/Estate/IHTPlanning.vue`**
- Created comprehensive breakdown table showing individual line items for all assets and liabilities
- Displays both user and spouse sections (for married couples)
- Shows current values and projected values at estimated age of death
- Organized structure:
  1. User Assets (property, investment, cash, business, chattel) + subtotal
  2. Spouse Assets (all asset types) + subtotal
  3. **Total Gross Assets**
  4. User Liabilities (mortgages, other liabilities) + subtotal
  5. Spouse Liabilities (mortgages, other liabilities) + subtotal
  6. **Total Liabilities**
  7. Net Estate
  8. NRB and RNRB allowances (separated and itemized by person)
  9. Taxable Estate
  10. IHT Liability (40%)

### 2. Fixed Mortgage Property Names

**Backend: `app/Http/Controllers/Api/Estate/IHTController.php`**
- **Issue**: Mortgages displaying "Unknown Property" instead of property addresses
- **Root Cause**: Code was accessing non-existent `property_name` field; properties table uses `address_line_1`
- **Fix**:
  - Added eager loading: `Mortgage::where(...)->with('property')->get()` (lines 213, 254)
  - Changed property name access from `property_name` to `address_line_1` (lines 223, 265)
  - Now displays: "15 Amherst Place", "10 Amherst Place" etc.

### 3. Separated NRB and RNRB Allowances

**Frontend: `resources/js/components/Estate/IHTPlanning.vue` (lines 741-777)**
- Removed combined "Total Allowances (NRB + RNRB)" display
- **For Married Couples**: Shows individual NRB and RNRB for each spouse
  - Chris Slater-Jones's NRB: £325,000
  - Angela Slater-Jones's NRB: £325,000
  - RNRB lines (only if RNRB > 0): Split equally between spouses
- **For Single People**: Shows combined NRB and RNRB amounts
- RNRB conditionally displayed only when `rnrb_available > 0`

### 4. Changed Top Card to Display Taxable Estate

**Frontend: `resources/js/components/Estate/IHTPlanning.vue` (lines 96-109)**
- Changed first summary card from "Gross Estate" to "Taxable Estate"
- Now displays:
  - **Now**: Current taxable estate (after NRB/RNRB deductions)
  - **At age X**: Projected taxable estate
- Shows the actual amount subject to 40% IHT rate

### 5. Filtered Profile Completeness Warnings

**Frontend: `resources/js/views/Estate/EstateDashboard.vue` (lines 196-229)**
- **Issue**: "Profile Partially Complete" showing warnings about protection policies and dependants
- **Fix**: Filter out protection and dependants from missing_fields
- Recalculate completeness score excluding irrelevant fields
- Estate module now only shows warnings relevant to IHT planning (spouse details, will info, etc.)

### 6. Filtered Missing Data Alert

**Frontend: `resources/js/components/Estate/IHTPlanning.vue` (lines 35-40)**
- Changed MissingDataAlert to only show when `spouse_account` is missing
- No longer displays alerts for protection policies or dependants
- Alert specifically targets married users without linked spouse accounts

## Technical Details

### Database Structure Verified
- `iht_calculations` table: Contains all projected values (projected_gross_assets, projected_liabilities, etc.)
- `properties` table: Uses `address_line_1` not `property_name`
- `mortgages` table: Has `property_id` foreign key linking to properties

### API Response Structure
All data comes from `/api/estate/iht/calculate` endpoint returning:
```javascript
{
  calculation: {
    total_gross_assets: number,
    projected_gross_assets: number,
    total_liabilities: number,
    projected_liabilities: number,
    nrb_available: number,
    rnrb_available: number,
  },
  assets_breakdown: {
    user: { name, assets: {...}, total },
    spouse: { name, assets: {...}, total }
  },
  liabilities_breakdown: {
    user: { name, mortgages: [...], liabilities: [...], total },
    spouse: { name, mortgages: [...], liabilities: [...], total }
  },
  iht_summary: {
    current: { net_estate, taxable_estate, iht_liability, ... },
    projected: { net_estate, taxable_estate, iht_liability, ... }
  }
}
```

## Files Modified

### Backend (2 files)
1. `app/Http/Controllers/Api/Estate/IHTController.php`
   - Added `with('property')` to mortgage queries
   - Changed property name access from `property_name` to `address_line_1`

### Frontend (2 files)
1. `resources/js/components/Estate/IHTPlanning.vue`
   - Added detailed breakdown table (lines 513-782)
   - Added spouse assets and liabilities sections
   - Separated NRB/RNRB allowances (lines 741-777)
   - Changed top card to Taxable Estate (lines 96-109)
   - Filtered missing data alert (lines 35-40)

2. `resources/js/views/Estate/EstateDashboard.vue`
   - Filtered profile completeness for protection/dependants (lines 196-229)

## Testing Performed
- Verified detailed breakdown shows all user and spouse assets/liabilities
- Confirmed mortgage property names display correctly ("15 Amherst Place", etc.)
- Verified NRB shows as separate lines (£325k + £325k = £650k)
- Confirmed RNRB doesn't display when value is £0
- Verified taxable estate displays in top card
- Confirmed profile completeness no longer warns about protection/dependants

## User Impact
- ✅ Much clearer view of estate composition with individual line items
- ✅ Property addresses now display correctly in mortgage liabilities
- ✅ Transparent breakdown of allowances (NRB user + NRB spouse)
- ✅ Focus on taxable amount rather than gross estate
- ✅ Cleaner UI without irrelevant warnings about protection/dependants

## Status
✅ Complete - All changes tested and working correctly

---

**Version**: v0.2.5
**Date**: November 11, 2025
**Developer**: Built with Claude Code
