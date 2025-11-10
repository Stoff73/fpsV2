# November 10, 2025 - Session 2: Property Costs & Pension Fixes

## Summary
Fixed critical bugs in pension edit forms, property cost displays, and BTL rental income calculations. Converted all property costs from annual to monthly throughout the entire stack.

---

## Fixes Completed

### 1. Pension Edit Forms - Blank Data Bug ✅
**Issue**: When clicking edit on a pension, the form appeared blank instead of populating with pension data.

**Root Cause**: Components using `v-if` with `mounted()` hook - props weren't reliably available when component mounted.

**Solution**: Added `watch` with `immediate: true` to reactively populate form data when pension prop changes.

**Files Modified**:
- `resources/js/components/Retirement/DCPensionForm.vue` (lines 336-355)
- `resources/js/components/Retirement/DBPensionForm.vue` (lines 276-295)
- `resources/js/components/Retirement/StatePensionForm.vue` (lines 216-233)

**Pattern Applied**:
```javascript
watch: {
  pension: {
    immediate: true,
    handler(newPension) {
      if (newPension) {
        this.formData = { ...newPension };
      } else {
        // Populate defaults from user profile
      }
    },
  },
},
```

---

### 2. Trust Ownership Validation Bug ✅
**Issue**: Trust ownership validation failed when selecting "Trust" with 0% ownership (correct for trusts).

**Root Cause**: Validation used `!this.form.ownership_percentage` which evaluated to `true` for 0, causing false positive.

**Solution**: Changed to explicit null/undefined check: `ownership_percentage === null || ownership_percentage === undefined`

**Files Modified**:
- `resources/js/components/NetWorth/Property/PropertyForm.vue` (line 1501)

---

### 3. DC Pension Card Display Fix ✅
**Issue**: DC pension cards always showed "Employee/Employer Contribution" labels regardless of pension type.

**Root Cause**: PensionCard.vue ignored `scheme_type` field and always displayed workplace pension structure.

**Solution**:
- Added computed properties: `isWorkplacePension()`, `isPersonalPension()`
- Updated template with conditional rendering
- Workplace pensions show employee% and employer%
- SIPP/Personal pensions show monthly contribution amount

**Files Modified**:
- `resources/js/components/Retirement/PensionCard.vue` (lines 44-70, 154-166)

---

### 4. Property Costs - Annual to Monthly Conversion ✅
**Issue**:
- Property costs showing £0 even when entered
- System expected `annual_*` fields but database has `monthly_*` fields
- Mixing of monthly and annual values in calculations

**Root Cause**: Complete field name mismatch between database schema, API, and frontend.

**Solution - Full Stack Fix**:

#### A. Database Schema (Confirmed)
Migration `2025_11_08_100336_add_monthly_costs_to_properties_table.php` added:
- `monthly_council_tax`
- `monthly_gas`
- `monthly_electricity`
- `monthly_water`
- `monthly_building_insurance`
- `monthly_contents_insurance`
- `monthly_service_charge`
- `monthly_maintenance_reserve`
- `other_monthly_costs`

#### B. Backend - PropertyService.php
**Changes**:
1. Renamed method: `calculateTotalAnnualCosts()` → `calculateTotalMonthlyCosts()`
2. Updated method to sum `monthly_*` fields instead of non-existent `annual_*` fields
3. Updated `calculateNetRentalYield()` to use monthly values
4. Updated `getPropertySummary()` to return `monthly_*` fields
5. Cast ALL monetary values to `float` to prevent string/number type issues

**Files Modified**:
- `app/Services/Property/PropertyService.php` (lines 34-59, 61-90, 95-226)

#### C. Frontend - PropertyFinancials.vue
**Complete Rewrite**:

1. **Computed Properties** - All changed to monthly:
   - `monthlyMortgagePayments()` - Sum of mortgage monthly payments
   - `totalMonthlyCosts()` - Sum of all monthly costs
   - `actualMonthlyIncome()` - Monthly rental × occupancy rate
   - `netMonthlyIncome()` - Monthly income - monthly costs
   - `netAnnualIncome()` - Monthly net × 12 (for yield calculation only)

2. **Display Template** - Changed to 2-column grid matching PropertyForm:
   - Shows each cost line item (Council Tax, Gas, Electricity, Water, etc.)
   - All values displayed as monthly amounts
   - Total Monthly Costs summary at bottom

3. **Edit Costs Modal** - Updated to monthly fields:
   - Modal title: "Edit Monthly Costs"
   - All input fields use `monthly_*` field names
   - Form data structure matches database schema

4. **Data Type Fixes**:
   - Added `parseFloat()` to ALL calculations
   - Updated `formatCurrency()` to handle NaN values
   - Fixed mortgage payments calculation to use property.mortgages fallback

**Files Modified**:
- `resources/js/components/NetWorth/Property/PropertyFinancials.vue` (complete rewrite)

---

### 5. BTL Rental Income Analysis Fix ✅
**Issue**: Rental income calculation mixed monthly rent with annual costs.

**Solution**: All calculations now monthly-based:
- Monthly Rental Income (entered value)
- Occupancy Rate (%)
- Actual Monthly Income (rental × occupancy)
- Total Monthly Costs (all costs + mortgage)
- Net Monthly Income (income - costs)
- Projected Annual Net Income (monthly × 12, for reference)
- Net Rental Yield % (annual net / property value)

**Display Cards**:
- Card 1: Monthly Rental Income
- Card 2: Net Monthly Income (after all costs)
- Card 3: Net Rental Yield % (annual percentage)

---

## Technical Details

### Field Name Mapping
**Database → Frontend**:
```
monthly_council_tax          → monthly_council_tax
monthly_gas                  → monthly_gas
monthly_electricity          → monthly_electricity
monthly_water                → monthly_water
monthly_building_insurance   → monthly_building_insurance
monthly_contents_insurance   → monthly_contents_insurance
monthly_service_charge       → monthly_service_charge
monthly_maintenance_reserve  → monthly_maintenance_reserve
other_monthly_costs          → other_monthly_costs
```

### Data Type Casting
**Backend (PropertyService.php)**:
```php
'monthly_council_tax' => (float) ($property->monthly_council_tax ?? 0),
```

**Frontend (PropertyFinancials.vue)**:
```javascript
(parseFloat(this.property.monthly_council_tax) || 0)
```

### Calculation Flow (BTL Example)
```
Monthly Rental Income:        £2,300
× Occupancy Rate:             100%
= Actual Monthly Income:      £2,300

Monthly Costs:
- Council Tax:                £150
- Gas:                        £80
- Electricity:                £100
- Water:                      £40
- Building Insurance:         £50
- Contents Insurance:         £30
- Service Charge:             £200
- Maintenance Reserve:        £100
- Other Costs:                £0
- Mortgage Payment:           £750
= Total Monthly Costs:        £1,500

Net Monthly Income:           £800
Projected Annual Net:         £9,600
```

---

## Files Changed

### Backend (PHP/Laravel)
1. `app/Services/Property/PropertyService.php` - Complete rewrite of cost calculations

### Frontend (Vue.js)
1. `resources/js/components/Retirement/DCPensionForm.vue` - Watcher pattern
2. `resources/js/components/Retirement/DBPensionForm.vue` - Watcher pattern
3. `resources/js/components/Retirement/StatePensionForm.vue` - Watcher pattern
4. `resources/js/components/Retirement/PensionCard.vue` - Conditional display
5. `resources/js/components/NetWorth/Property/PropertyForm.vue` - Validation fix
6. `resources/js/components/NetWorth/Property/PropertyFinancials.vue` - Complete rewrite

---

## Testing Checklist

- [x] Pension edit forms populate correctly
- [x] Trust ownership validation accepts 0%
- [x] DC pension cards show correct labels by type
- [x] Property costs display all line items
- [x] Property costs calculate correct total
- [x] BTL rental income calculation correct
- [x] No NaN values displayed
- [x] All monetary values display as numbers

---

## Breaking Changes

**None** - All changes are backward compatible. Existing properties with no costs entered will display £0 for each line item.

---

## Migration Notes

Properties created before migration `2025_11_08_100336` will have NULL values for monthly cost fields. These display as £0 and can be updated via "Edit Costs" button.

---

**Session Duration**: ~2 hours
**Commits**: 1 (this session)
**Status**: ✅ Complete - All fixes tested and working

---

🤖 **Built with [Claude Code](https://claude.com/claude-code)**
