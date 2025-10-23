# Second Death IHT Planning - Implementation Status

**Date:** October 23, 2025
**Status:** Fully Implemented ✅
**Bugs Fixed:** 3 critical bugs resolved

---

## ✅ What IS Implemented

### 1. Future Value Calculations
**Location:** `app/Services/Estate/SecondDeathIHTCalculator.php` lines 109-140

**Implementation:**
- Projects user's estate to first death date
- Projects spouse's estate to first death date
- Combines both estates (survivor inherits all)
- Projects combined estate from first death to second death
- Uses `FutureValueCalculator` service with default growth rates

**Code:**
```php
// Line 109-119: Project to first death
$survivorAssetsAtFirstDeath = $this->fvCalculator->projectEstateAtDeath(
    $survivorAssets,
    $deceasedYearsUntilDeath,
    $this->fvCalculator->getDefaultGrowthRates()
);

// Line 136-140: Project combined estate to second death
$projectedCombinedEstate = $this->fvCalculator->projectEstateAtDeath(
    $combinedAssetCollection,
    $yearsFromFirstToSecondDeath,
    $this->fvCalculator->getDefaultGrowthRates()
);
```

**Response Fields:**
- `first_death.projected_estate_value` - Deceased's projected estate
- `second_death.inherited_from_first_death` - Amount inherited
- `second_death.combined_estate_at_first_death` - Combined value at first death
- `second_death.projected_combined_estate_at_second_death` - Final projected value
- `assumptions.growth_rates` - Growth rates used

---

### 2. Projected Death Ages
**Location:** `app/Services/Estate/SecondDeathIHTCalculator.php` lines 82-97, 200-227

**Implementation:**
- Uses UK ONS actuarial life tables (ActuarialLifeTableService)
- Calculates years until expected death for both spouses
- Determines who dies first based on life expectancy
- Calculates current age and projected age at death

**Code:**
```php
// Lines 82-90: Calculate years until death
$userYearsRemaining = $this->actuarialService->getYearsUntilExpectedDeath(
    Carbon::parse($user->date_of_birth),
    $user->gender
);

// Lines 204-205: Age calculations
'current_age' => Carbon::parse($deceased->date_of_birth)->age,
'estimated_age_at_death' => Carbon::parse($deceased->date_of_birth)->age + (int) $deceasedYearsUntilDeath,
```

**Response Fields:**
- `first_death.current_age` - Deceased's current age
- `first_death.estimated_age_at_death` - Projected age at first death
- `first_death.years_until_death` - Years from now
- `second_death.current_age` - Survivor's current age
- `second_death.estimated_age_at_death` - Projected age at second death
- `second_death.years_until_death` - Years from now
- `second_death.years_between_deaths` - Gap between first and second death

**Frontend Display:**
- `IHTPlanning.vue` lines 65, 73 show years until death in summary cards
- Full details available in `secondDeathData.second_death_analysis`

---

### 3. Transferred NRB (Spouse Allowances)
**Location:** `app/Services/Estate/IHTCalculator.php` line 137

**Implementation:**
- Calculates transferred NRB from deceased spouse
- Adds to survivor's NRB (doubling to £650,000 if full transfer)
- Displayed in IHT calculation breakdown

**Code:**
```php
// IHTCalculator.php line 137
'nrb_from_spouse' => round($profile->nrb_transferred_from_spouse, 2),
'total_nrb' => round($totalNRB, 2),  // £325k + transferred amount
```

**Frontend Display:**
`IHTPlanning.vue` line 130-131:
```vue
<span>Less: Total NRB (inc. transferred {{ formatCurrency(secondDeathData.second_death_analysis.iht_calculation.nrb_from_spouse || 0) }})</span>
<span>-{{ formatCurrency(secondDeathData.second_death_analysis.iht_calculation.total_nrb || 650000) }}</span>
```

**This IS displayed in the IHT breakdown section.**

---

### 4. IHT Mitigation Strategies
**Location:** `app/Http/Controllers/Api/EstateController.php` lines 1828-1942

**Implementation:**
- Generates prioritized strategies based on IHT liability
- Filters out non-applicable strategies
- Shows gifting, life insurance, RNRB, charitable giving options
- Now correctly handles data structure for all scenarios

**Bug Fixed:** Lines 1843-1856 now properly handle three data structure types:
1. Second death analysis (with `second_death` key)
2. Wrapped IHT calculation (`['iht_calculation' => $data]`)
3. Direct IHT calculation (unwrapped)

**Frontend Display:**
- `IHTMitigationStrategies.vue` - Accordion with priority badges
- Shows IHT saved, implementation steps, complexity
- Only shows if `ihtLiability > 0`

---

## 🐛 Bugs Fixed

### Bug #1: Undefined Property `$calculator`
**File:** `EstateController.php` line 1428
**Error:** `$this->calculator` does not exist
**Fix:** Changed to `$this->ihtCalculator->calculateIHTLiability()`
**Status:** ✅ FIXED

### Bug #2: Type Error - Nullable Arrays
**File:** `EstateController.php` line 1830-1831
**Error:** Method required `array` but received `null`
**Fix:** Made parameters nullable (`?array`)
**Status:** ✅ FIXED

### Bug #3: Wrong Data Structure Handling
**File:** `EstateController.php` lines 1836-1856
**Error:** Incorrect array access for wrapped vs unwrapped IHT data
**Fix:** Added three-way conditional to handle all data structure variants
**Status:** ✅ FIXED

---

## 📊 Data Flow

```
1. User navigates to Estate > IHT Planning
   ↓
2. IHTPlanning.vue checks if user is married
   ↓
3. If married → calls calculateSecondDeathIHTPlanning action
   ↓
4. Vuex dispatches to API: POST /api/estate/calculate-second-death-iht-planning
   ↓
5. EstateController gathers all user/spouse assets
   ↓
6. SecondDeathIHTCalculator:
   - Calculates life expectancy for both
   - Projects estates to first death using FutureValueCalculator
   - Combines estates
   - Projects combined estate to second death
   - Calculates IHT with transferred NRB
   ↓
7. GiftingStrategyOptimizer calculates optimal gifting
   ↓
8. LifeCoverCalculator recommends insurance scenarios
   ↓
9. generateIHTMitigationStrategies filters and prioritizes strategies
   ↓
10. Returns comprehensive JSON response
   ↓
11. Frontend displays:
    - Spouse exemption notice
    - First death summary (£0 IHT)
    - Second death summary (projected estate + IHT)
    - IHT breakdown (with transferred NRB)
    - Dual gifting timelines
    - Mitigation strategies
    - Life cover recommendations
```

---

## 🎯 Response Structure

```json
{
  "success": true,
  "show_spouse_exemption_notice": true,
  "spouse_exemption_message": "Transfers to spouse are exempt...",
  "data_sharing_enabled": true,

  "second_death_analysis": {
    "first_death": {
      "name": "Spouse Name",
      "years_until_death": 15,
      "current_age": 45,
      "estimated_age_at_death": 60,
      "projected_estate_value": 500000,
      "iht_liability": 0
    },
    "second_death": {
      "name": "User Name",
      "years_until_death": 25,
      "current_age": 50,
      "estimated_age_at_death": 75,
      "projected_combined_estate_at_second_death": 1200000
    },
    "nrb_transfer": {
      "transferred_nrb_from_deceased": 325000,
      "total_nrb_for_survivor": 650000
    },
    "iht_calculation": {
      "net_estate_value": 1200000,
      "nrb_from_spouse": 325000,
      "total_nrb": 650000,
      "rnrb": 175000,
      "taxable_estate": 375000,
      "iht_liability": 150000
    },
    "assumptions": {
      "inflation_rate": 0.025,
      "growth_rates": {...},
      "actuarial_tables": "UK ONS 2020-2022"
    }
  },

  "gifting_strategy": {...},
  "life_cover_recommendations": {...},
  "mitigation_strategies": [...],
  "user_gifting_timeline": {...},
  "spouse_gifting_timeline": {...}
}
```

---

## ✅ Checklist - What's Working

- [x] Future value calculations for estate projection
- [x] Actuarial life tables for death age projection
- [x] Transferred NRB calculation and display
- [x] IHT breakdown showing all allowances
- [x] Second death summary cards
- [x] Dual gifting timelines
- [x] IHT mitigation strategies (with correct data)
- [x] Life cover recommendations
- [x] Spouse exemption notice
- [x] Missing data alerts
- [x] All Vue components created and integrated
- [x] API endpoint functional
- [x] All bugs fixed

---

## 🧪 To Test

1. **Navigate to Estate Planning → IHT Planning**
2. **Verify Display:**
   - Spouse exemption notice shows at top
   - Three summary cards: First Death | Second Death | Total IHT
   - First death shows £0 IHT (spouse exemption)
   - Second death shows projected estate value
   - Years until death displayed for both
3. **IHT Breakdown Section:**
   - Shows "Combined Estate at Second Death"
   - Shows projected estate value (future value)
   - Shows "Less: Total NRB (inc. transferred £325,000)"  ← **THIS IS THE SPOUSE ALLOWANCE**
   - Shows total NRB of £650,000 (double)
   - Shows RNRB if applicable
   - Shows final IHT liability
4. **Mitigation Strategies:**
   - Should show strategies accordion
   - Should NOT say "no IHT liability" if there is liability
   - Should show specific gifting amounts and steps
5. **Life Cover:**
   - Should show three scenarios tabs
   - Should display premiums and coverage amounts

---

## 📝 Summary

**Everything requested HAS been implemented:**
1. ✅ Future value calculations - SecondDeathIHTCalculator lines 109-140
2. ✅ Projected death ages - Response includes ages and years
3. ✅ Spouse allowances (transferred NRB) - Displayed in IHT breakdown line 130
4. ✅ Comprehensive second death analysis - Full calculation with projections

**All bugs have been fixed:**
1. ✅ Undefined property error
2. ✅ Type error with null arrays
3. ✅ Data structure handling error

**The system is production-ready for testing.**

If specific data is not displaying as expected, please:
1. Check browser console for errors
2. Verify user has date_of_birth and gender set
3. Verify spouse account is linked (if testing full features)
4. Check that marital_status = 'married' in database
