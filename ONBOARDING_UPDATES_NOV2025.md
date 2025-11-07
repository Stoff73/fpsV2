# Onboarding Updates - November 2025

## Overview
Significant updates to the onboarding flow to streamline data collection and improve user experience.

---

## 1. Health Status Field Enhancement

**Location**: `PersonalInfoStep.vue`

**Change**: Added "Yes, previous health conditions" option to health status dropdown.

**Database**:
- Migration: `2025_11_07_155504_add_yes_previous_to_health_status_enum.php`
- Enum values: `'yes'`, `'yes_previous'`, `'no_previous'`, `'no_existing'`, `'no_both'`

**Impact**: Provides more granular health status tracking for protection insurance premium calculations.

---

## 2. Employment & Income Step - Rental Income Removal

**Location**: `IncomeStep.vue`

**Changes**:
- ✅ Removed `annual_rental_income` field from income form
- ✅ Added informational message: "Rental income is entered through the Property section"
- ✅ Removed all expenditure fields from this step (moved to dedicated step)

**Backend Updates**:
- `OnboardingService::processIncomeInfo()` - removed `annual_rental_income` processing
- `EstateOnboardingFlow.php` - removed rental income and expenditure fields from income step definition

**Rationale**: Rental income is more appropriately captured in the Property section where users enter property details, rental amounts, and expenses together.

---

## 3. New Dedicated Expenditure Step

**Location**: `ExpenditureStep.vue` (NEW FILE)

**Features**:

### Entry Mode Toggle
Users can choose between two modes:
1. **Simple Total** - Single monthly expenditure field
2. **Detailed Breakdown** - 15 detailed expense categories

### Detailed Categories (Monthly)

**Essential Living Expenses:**
- Food & Groceries
- Transport & Fuel
- Healthcare & Medical
- Insurance (non-property)

**Communication & Technology:**
- Mobile Phones
- Internet & TV
- Subscriptions & Memberships

**Personal & Lifestyle:**
- Clothing & Personal Care
- Entertainment & Dining Out
- Holidays & Travel
- Pets

**Children & Education:**
- Childcare
- School Fees
- Children's Activities

**Other Expenses:**
- Other Expenditure

### Auto-Calculation
- Automatically calculates monthly and annual totals from detailed breakdown
- Simple mode: user enters one total, system sets detail fields to 0
- Detailed mode: system sums all categories

### Database
- Migration: `2025_11_07_160346_add_detailed_expenditure_fields_to_users_table.php`
- Added 17 new `DECIMAL(10,2)` fields to `users` table
- Fields: `food_groceries`, `transport_fuel`, `healthcare_medical`, `insurance`, `mobile_phones`, `internet_tv`, `subscriptions`, `clothing_personal_care`, `entertainment_dining`, `holidays_travel`, `pets`, `childcare`, `school_fees`, `children_activities`, `other_expenditure`
- Retained: `monthly_expenditure`, `annual_expenditure` (aggregate fields)

### Backend
- `OnboardingService::processExpenditureInfo()` - new method to process expenditure data
- `EstateOnboardingFlow.php` - added `expenditure` step (order 3) between income and domicile
- All subsequent step orders incremented (domicile now 4, assets now 5, etc.)

### Informational Messages
- Blue info box: Explains why expenditure tracking matters
- Amber note: "Household expenditure such as Council Tax, utilities, and maintenance are entered in the Properties tab. Loans, credit cards, and hire purchase are entered in the Liabilities section."

**Rationale**: Provides detailed expenditure tracking for accurate emergency fund calculations, protection needs assessment, and discretionary income analysis. Toggle allows flexibility for users who prefer simplicity vs. detailed tracking.

---

## 4. Domicile Information Streamlining

**Location**: `DomicileInformationStep.vue`

**Changes**:
- ✅ Removed manual "Domicile Status" dropdown
- ✅ Changed question to: "Where were you born?"
- ✅ Defaults to "United Kingdom"
- ✅ Auto-determines domicile status based on country of birth and UK arrival date

### Auto-Determination Logic

**UK Born:**
- No additional fields required
- Domicile status: "UK Domiciled"

**Non-UK Born:**
- Shows: "Date Moved to UK" field (required)
- Auto-calculates years UK resident
- Displays domicile status with explanation

**Domicile Status Rules:**
- UK born → "UK Domiciled"
- Non-UK born + < 15 years resident → "Non-UK Domiciled"
- Non-UK born + 15+ years resident → "Deemed UK Domiciled"

### Enhanced Status Display
When UK arrival date is entered, system shows:
- Years UK Resident (auto-calculated)
- Domicile Status label
- **Deemed domiciled**: Amber warning about IHT on worldwide assets
- **Not yet deemed**: Blue info showing years remaining to reach deemed status

### Backend
- `EstateOnboardingFlow.php` - updated field definitions
- `domicile_status` changed from user-input to auto-determined
- Field order updated: `country_of_birth` (required), `uk_arrival_date` (conditional), `domicile_status` (auto)

**Rationale**: Simplifies form by removing technical jargon. Users answer simple questions (where born, when moved to UK) and system automatically determines correct domicile status using existing backend logic in User model.

---

## Updated Onboarding Flow Order

1. Personal Information (order 1)
2. Employment & Income (order 2) - Rental income removed, expenditure removed
3. **Household Expenditure (order 3)** - NEW STEP
4. Domicile Information (order 4) - Streamlined
5. Assets & Wealth (order 5)
6. Liabilities & Debts (order 6)
7. Protection Policies (order 7)
8. Family & Beneficiaries (order 8)
9. Will Information (order 9)
10. Trust Information (order 10)
11. Setup Complete (order 11)

---

## User Model Updates

**New Fillable Fields**:
```php
'food_groceries', 'transport_fuel', 'healthcare_medical', 'insurance',
'mobile_phones', 'internet_tv', 'subscriptions', 'clothing_personal_care',
'entertainment_dining', 'holidays_travel', 'pets', 'childcare',
'school_fees', 'children_activities', 'other_expenditure'
```

**New Casts**:
All new expenditure fields cast as `'decimal:2'`

**Health Status Enum**:
Updated to include `'yes_previous'` option

---

## Files Modified

### Frontend Components
- `resources/js/components/Onboarding/OnboardingWizard.vue`
- `resources/js/components/Onboarding/steps/PersonalInfoStep.vue`
- `resources/js/components/Onboarding/steps/IncomeStep.vue`
- `resources/js/components/Onboarding/steps/ExpenditureStep.vue` (NEW)
- `resources/js/components/Onboarding/steps/DomicileInformationStep.vue`

### Backend Services
- `app/Services/Onboarding/OnboardingService.php`
- `app/Services/Onboarding/EstateOnboardingFlow.php`

### Models
- `app/Models/User.php`

### Migrations
- `database/migrations/2025_11_07_155504_add_yes_previous_to_health_status_enum.php`
- `database/migrations/2025_11_07_160346_add_detailed_expenditure_fields_to_users_table.php`

---

## Testing Recommendations

1. **Health Status**: Test all 5 health status options save correctly
2. **Income Step**: Verify rental income message displays, form saves without rental income field
3. **Expenditure Toggle**:
   - Test switching between simple and detailed modes
   - Verify calculations in both modes
   - Test data persistence when returning to step
4. **Domicile Auto-Determination**:
   - Test UK born scenario (no additional fields)
   - Test non-UK born with < 15 years (non-UK domiciled)
   - Test non-UK born with 15+ years (deemed domiciled)
   - Verify domicile status saves correctly to user profile

---

## Data Migration Notes

**No data migration required** - all new fields have default values of 0, and existing functionality is preserved.

Users who previously entered:
- Rental income: Will be retained in `annual_rental_income` field
- Expenditure: Will be retained in `monthly_expenditure` and `annual_expenditure` fields
- Domicile status: Will be retained and can be viewed/edited

---

## Benefits

1. **Improved UX**: Simpler, more intuitive forms with less technical jargon
2. **Flexibility**: Expenditure toggle allows quick entry or detailed tracking
3. **Data Quality**: Auto-determination reduces user error in domicile status
4. **Better Analysis**: Detailed expenditure breakdown enables more accurate financial planning
5. **Logical Organization**: Rental income with properties, expenditure in dedicated section

---

**Version**: v0.2.1+
**Date**: November 7, 2025
**Branch**: feature/investment-financial-planning
