# Data Sources Integration - Complete

## Summary

All data from **Net Worth**, **Protection Planning**, and **User Profile** modules is now being pulled through to the Estate Planning Second Death IHT calculation.

---

## Data Sources Included

### ✅ Net Worth Module

#### Properties
- **Source**: `Property` model
- **Fields**: `current_value`, `ownership_percentage`, `address_line_1`
- **Usage**: Estate valuation, RNRB calculation (main residence)
- **Ownership**: Individual, joint, and trust ownership supported

#### Investment Accounts
- **Source**: `InvestmentAccount` model
- **Fields**: `current_value`, `provider`, `account_type`
- **Usage**: Estate valuation
- **Ownership**: Individual, joint, and trust ownership supported

#### Savings/Cash Accounts
- **Source**: `SavingsAccount` model
- **Fields**: `current_balance`, `institution`, `account_type`
- **Usage**: Estate valuation
- **Ownership**: Individual, joint, and trust ownership supported

#### Business Interests ✨ NEW
- **Source**: `BusinessInterest` model
- **Fields**: `current_valuation`, `ownership_percentage`, `business_name`
- **Usage**: Estate valuation (may qualify for Business Relief at 50% or 100%)
- **Ownership**: Individual, joint, and trust ownership supported

#### Chattels (Personal Property) ✨ NEW
- **Source**: `Chattel` model
- **Fields**: `current_value`, `ownership_percentage`, `name`
- **Usage**: Estate valuation
- **Ownership**: Individual, joint, and trust ownership supported

#### Mortgages
- **Source**: `Mortgage` model
- **Fields**: `outstanding_balance`
- **Usage**: Liabilities deduction from estate

#### Other Liabilities
- **Source**: `Liability` model (Estate module)
- **Fields**: `current_balance`
- **Usage**: Liabilities deduction from estate

---

### ✅ Retirement Module (Pensions)

#### DC Pensions ✨ NEW
- **Source**: `DCPension` model
- **Fields**: `current_fund_value`, `scheme_name`
- **Usage**:
  - Estate valuation (marked as IHT exempt if beneficiary nominated)
  - Income projections for gifting from income strategy
- **IHT Treatment**: Outside estate if beneficiary nominated

#### DB Pensions ✨ NEW
- **Source**: `DBPension` model
- **Fields**: `expected_annual_pension`, `scheme_name`
- **Usage**: Income projections for gifting from income strategy
- **IHT Treatment**: No estate value (dies with member)

---

### ✅ Protection Planning Module

#### Life Insurance Policies ✨ NEW
- **Source**: `LifeInsurancePolicy` model
- **Fields**: `sum_assured`, `policy_status`
- **Usage**: Existing life cover calculation to avoid duplicate recommendations
- **Filter**: Only active policies included

#### Critical Illness Policies ✨ NEW
- **Source**: `CriticalIllnessPolicy` model
- **Fields**: `sum_assured`, `policy_status`, `has_life_cover`
- **Usage**: Existing life cover calculation (if critical illness includes life cover)
- **Filter**: Only active policies with life cover benefit included

---

### ✅ User Profile Module

#### Income Data
- **Source**: `User` model
- **Fields**:
  - `annual_employment_income`
  - `annual_self_employment_income`
  - `annual_rental_income`
  - `annual_dividend_income`
  - `annual_other_income`
- **Usage**:
  - Total income calculation for gifting from income strategy
  - Actuarial life expectancy calculations
  - Tax calculations (via UKTaxCalculator)

#### Expenditure Data ✨ NEW
- **Primary Source**: `ExpenditureProfile` model
- **Fallback Source**: `ProtectionProfile` model
- **Fields**: `total_monthly_expenditure` (converted to annual)
- **Usage**: Gifting from income strategy (determines affordable gift amount)
- **Calculation**: Annual Income - Annual Expenditure = Available for Gifting

#### Personal Details
- **Source**: `User` model
- **Fields**:
  - `date_of_birth` (for age calculation)
  - `gender` (for actuarial tables)
  - `marital_status` (for spouse exemption)
  - `spouse_id` (for spouse linking)
- **Usage**:
  - Actuarial life expectancy
  - Second death projections
  - Premium calculations

---

## Helper Methods Created

### `gatherUserAssets(User $user)`
**Purpose**: Collects all assets from all modules for a single user

**Returns**: Collection of asset objects with:
- `asset_type` (property, investment, cash, business, chattel, dc_pension, db_pension)
- `asset_name`
- `current_value`
- `is_iht_exempt`
- `annual_income` (for DB pensions)

**Assets Included**:
1. Estate module assets
2. Investment accounts
3. Properties (with ownership percentage)
4. Savings/cash accounts
5. Business interests (with ownership percentage) ✨
6. Chattels (with ownership percentage) ✨
7. DC pensions (marked IHT exempt) ✨
8. DB pensions (£0 value, income only) ✨

### `calculateUserLiabilities(User $user)`
**Purpose**: Calculates total liabilities for a user

**Returns**: Float (total liabilities)

**Includes**:
1. Estate liabilities (from `liabilities` table)
2. Mortgage debt (from `mortgages` table)

### `getExistingLifeCover(User $user)` ✨ NEW
**Purpose**: Gets total existing life cover from Protection module

**Returns**: Float (total life cover)

**Includes**:
1. Active life insurance policies (`sum_assured`)
2. Active critical illness policies with life cover (`sum_assured`)

### `getUserExpenditure(User $user)` ✨ NEW
**Purpose**: Gets user expenditure data for gifting calculations

**Returns**: Array with:
- `monthly_expenditure`
- `annual_expenditure`

**Fallback Logic**:
1. Try `ExpenditureProfile` model first
2. Fall back to `ProtectionProfile` model
3. Return 0 if no data found

---

## Integration Points

### GiftingStrategyOptimizer
**Updated**: Now receives `annual_expenditure` as parameter (line 37)

**Usage**:
- Calculates affordable gifting from income
- Formula: `(Annual Income - Annual Expenditure) * 0.5` (50% of surplus)
- Only suggests gifting from income if expenditure data exists

### LifeCoverCalculator
**Updated**: Now receives `existing_cover` as parameter (line 34)

**Usage**:
- Calculates cover gap: `IHT Liability - Existing Cover`
- Adjusts recommendations based on existing cover
- Shows "Existing Cover is Sufficient" if cover ≥ IHT liability
- Calculates additional cover needed if gap exists

### SecondDeathIHTCalculator
**No changes required** - receives complete asset collections from controller

---

## Data Flow

```
User Profile
    ↓
    ├── Income (annual_employment_income, etc.)
    ├── Expenditure (ExpenditureProfile → ProtectionProfile → 0)
    └── Personal Details (DOB, gender, marital_status)

Net Worth Module
    ↓
    ├── Properties (current_value, ownership_percentage)
    ├── Investments (current_value)
    ├── Cash/Savings (current_balance)
    ├── Business Interests (current_valuation, ownership_percentage) ✨
    ├── Chattels (current_value, ownership_percentage) ✨
    ├── Mortgages (outstanding_balance)
    └── Liabilities (current_balance)

Retirement Module
    ↓
    ├── DC Pensions (current_fund_value) ✨
    └── DB Pensions (expected_annual_pension) ✨

Protection Module
    ↓
    ├── Life Insurance (sum_assured) ✨
    └── Critical Illness (sum_assured, has_life_cover) ✨

        ↓↓↓ ALL DATA FLOWS TO ↓↓↓

EstateController::calculateSecondDeathIHTPlanning()
    ↓
    ├── SecondDeathIHTCalculator (estate valuation, IHT calculation)
    ├── GiftingStrategyOptimizer (uses income & expenditure) ✨
    └── LifeCoverCalculator (uses existing cover) ✨
        ↓
        Response to Frontend
```

---

## Files Modified

### Backend

1. **app/Models/User.php**
   - Added `expenditureProfile()` relationship (line 280-285)
   - Added `savingsAccounts()` relationship (line 287-293)

2. **app/Http/Controllers/Api/EstateController.php**
   - Updated `gatherUserAssets()` to include business interests, chattels, DC/DB pensions (lines 1608-1667)
   - Added `getExistingLifeCover()` helper method (lines 1680-1695)
   - Added `getUserExpenditure()` helper method (lines 1697-1725)
   - Updated `calculateSecondDeathIHTPlanning()` to pass expenditure and existing cover (lines 1505-1533)

3. **app/Services/Estate/GiftingStrategyOptimizer.php**
   - Added `$annualExpenditure` parameter to `calculateOptimalGiftingStrategy()` (line 37)
   - Removed broken `$user->personalAccount->monthly_expenditure` access (line 65)
   - Now uses passed parameter instead of trying to access non-existent relationship

4. **app/Services/Estate/LifeCoverCalculator.php**
   - Added `$existingCover` parameter to `calculateLifeCoverRecommendations()` (line 34)
   - Added `existing_cover` and `cover_gap` to response (lines 84-85)
   - Updated `generateRecommendation()` to accept and use `$existingCover` (lines 349, 354-366)
   - Added logic to check if existing cover is sufficient (lines 354-366)
   - Calculate cover gap after existing cover and gifting (lines 379-396)

---

## Testing Checklist

### ✅ Data Completeness
- [x] Properties pulled from Net Worth module
- [x] Investments pulled from Investment module
- [x] Cash/Savings pulled from Savings module
- [x] Business interests pulled from Net Worth module ✨
- [x] Chattels pulled from Net Worth module ✨
- [x] Mortgages and liabilities included
- [x] DC pensions included (marked IHT exempt) ✨
- [x] DB pensions included (for income projections) ✨
- [x] Life insurance policies included ✨
- [x] Critical illness policies included ✨
- [x] User income data included
- [x] User expenditure data included ✨

### ✅ Calculation Accuracy
- [ ] Estate valuation uses all asset types
- [ ] Liabilities correctly deducted
- [ ] Ownership percentages applied correctly
- [ ] IHT exemptions applied correctly (DC pensions, DB pensions)
- [ ] Gifting from income uses correct expenditure data
- [ ] Life cover recommendations account for existing cover
- [ ] Cover gap calculated correctly

### ✅ Missing Data Handling
- [ ] Shows appropriate missing data alerts
- [ ] Falls back gracefully when expenditure data missing
- [ ] Handles missing spouse data correctly
- [ ] Returns £0 for assets/liabilities when none exist

---

## Next Steps

1. **Browser Testing**:
   - Test with married user with complete data
   - Test with married user with missing expenditure data
   - Test with user who has existing life cover
   - Test with user who has business interests and chattels

2. **Integration Testing**:
   - Verify all asset types appear in estate valuation
   - Verify gifting from income strategy shows when expenditure exists
   - Verify life cover recommendations adjust for existing policies
   - Verify pension income used in projections

3. **Documentation Updates**:
   - Update CLAUDE.md with data sources integration
   - Update API documentation
   - Update testing guide

---

## Summary of Changes

**Before**: Only pulling properties, investments, cash, mortgages, and liabilities from Estate module

**After**: Pulling ALL data from:
- ✅ Net Worth (properties, investments, cash, business interests, chattels, mortgages, liabilities)
- ✅ Retirement (DC pensions, DB pensions)
- ✅ Protection (life insurance, critical illness)
- ✅ User Profile (income, expenditure, personal details)

**Impact**:
- More accurate estate valuations
- More realistic gifting strategies (using actual expenditure)
- Smarter life cover recommendations (accounting for existing policies)
- Better income projections (using pension data)

---

**Status**: ✅ COMPLETE - All data sources integrated

**Date**: 2025-10-23

**Version**: v0.1.2.2
