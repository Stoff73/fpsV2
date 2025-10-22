# Comprehensive FPS Restructuring Plan

**Project:** Financial Planning System (FPS) Major Restructuring
**Date Created:** October 17, 2025
**Status:** Phase 1 In Progress
**Estimated Timeline:** 8-10 weeks full-time development

---

## Executive Summary

This document outlines a complete restructuring of the FPS application to implement:

- **Multi-user spouse functionality** with joint asset ownership
- **Expanded user profile** with comprehensive financial data entry
- **Net Worth dashboard** as primary focus (replacing Estate for asset tracking)
- **Property module** with full UK tax implications (SDLT, CGT, rental income tax)
- **Actions/Recommendations aggregation** card with actionability
- **Trusts dashboard** with cross-module integration
- **Admin role-based access control**
- **Reordered dashboard** with updated card displays

---

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Phase 1: Database Schema & Authentication](#phase-1-database-schema--authentication)
3. [Phase 2: User Profile Restructuring](#phase-2-user-profile-restructuring)
4. [Phase 3: Net Worth Dashboard](#phase-3-net-worth-dashboard)
5. [Phase 4: Property Module](#phase-4-property-module)
6. [Phase 5: Actions/Recommendations Card](#phase-5-actionsrecommendations-card)
7. [Phase 6: Trusts Dashboard](#phase-6-trusts-dashboard)
8. [Phase 7: Dashboard Reordering](#phase-7-dashboard-reordering)
9. [Phase 8: Admin Roles & RBAC](#phase-8-admin-roles--rbac)
10. [Phase 9: Data Migration](#phase-9-data-migration)
11. [Phase 10: Testing & Documentation](#phase-10-testing--documentation)
12. [Implementation Timeline](#implementation-timeline)
13. [Success Criteria](#success-criteria)

---

## Architecture Overview

### Key Architectural Changes

#### Multi-User Household Model
```
Household (hub)
    ├── User 1 (Primary)
    │   ├── Individual Assets
    │   └── Individual Plans
    ├── User 2 (Spouse)
    │   ├── Individual Assets
    │   └── Individual Plans
    └── Joint Assets (shared between spouses)
```

#### Asset Ownership Structure
Every asset now has:
- `ownership_type`: individual | joint
- `ownership_percentage`: 0-100%
- `household_id`: Links joint assets to household
- `trust_id`: Links assets held in trusts

#### Dashboard Restructuring
**New Order:**
1. Net Worth (new - aggregates property, investments, cash, business, chattels)
2. Retirement Planning (updated display)
3. Estate Planning (refocused on IHT/gifting)
4. Protection (unchanged)
5. Actions/Recommendations (new - aggregated from all modules)
6. Trusts (new - comprehensive trust tracking)
7. UK Taxes & Allowances (admin-only)

#### User Profile Expansion
Settings page becomes comprehensive Profile with tabs:
- Personal Information (address, NI number, contact)
- Family (spouse, children, dependents)
- Income & Occupation (all income sources)
- Assets (moved from Estate module)
- Liabilities (moved from Estate module)
- Personal Accounts (P&L, Cashflow, Balance Sheet)

---

## Phase 1: Database Schema & Authentication

### Objectives
- Implement multi-user spouse linking
- Create household management structure
- Add comprehensive asset tables with ownership tracking
- Implement role-based access control foundation

### Tasks

#### 1.1 User Table Extensions ✅ COMPLETED

**Migration:** `2025_10_17_142646_add_spouse_linking_and_role_to_users_table.php`

**Added Fields:**
- `spouse_id` (nullable, foreign key to users)
- `household_id` (nullable, foreign key to households)
- `is_primary_account` (boolean, default true)
- `role` (enum: user/admin, default user)
- `national_insurance_number` (varchar 13)
- `address_line_1`, `address_line_2`, `city`, `county`, `postcode`, `phone`
- `occupation`, `employer`, `industry`, `employment_status`
- `annual_employment_income`, `annual_self_employment_income`
- `annual_rental_income`, `annual_dividend_income`, `annual_other_income`

**Indexes:** spouse_id, household_id, role

---

#### 1.2 Households Table ✅ COMPLETED

**Migration:** `2025_10_17_142728_create_households_table.php`

**Schema:**
```sql
CREATE TABLE households (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    household_name VARCHAR(255) NULLABLE,
    notes TEXT NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Model:** `app/Models/Household.php` ✅ COMPLETED

**Relationships:**
- `hasMany(User)` - Users in household
- `hasMany(FamilyMember)` - Family members
- `hasMany(Property)` - Joint properties
- `hasMany(BusinessInterest)` - Joint businesses
- `hasMany(Chattel)` - Joint chattels
- `hasMany(CashAccount)` - Joint cash accounts
- `hasMany(InvestmentAccount)` - Joint investment accounts
- `hasMany(Trust)` - Household trusts

---

#### 1.3 Foreign Key Constraints ✅ COMPLETED

**Migration:** `2025_10_17_142742_add_foreign_keys_to_users_table.php`

Adds foreign key constraints for spouse_id and household_id on users table.

---

#### 1.4 Family Members Table ✅ COMPLETED

**Migration:** `2025_10_17_142756_create_family_members_table.php`

**Schema:**
```sql
CREATE TABLE family_members (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    household_id BIGINT UNSIGNED NULLABLE,
    relationship ENUM('spouse', 'child', 'parent', 'other_dependent'),
    name VARCHAR(255) NOT NULL,
    date_of_birth DATE NULLABLE,
    gender ENUM('male', 'female', 'other', 'prefer_not_to_say') NULLABLE,
    national_insurance_number VARCHAR(13) NULLABLE,
    annual_income DECIMAL(15,2) NULLABLE,
    is_dependent BOOLEAN DEFAULT FALSE,
    education_status ENUM(...) NULLABLE,
    notes TEXT NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (household_id) REFERENCES households(id) ON DELETE CASCADE,
    INDEX (user_id, household_id, relationship)
);
```

**Model:** `app/Models/FamilyMember.php` ✅ COMPLETED

**Relationships:**
- `belongsTo(User)`
- `belongsTo(Household)`

**Note:** Spouses are stored as User records, not family_members. Only children and dependents are stored here.

---

#### 1.5 Properties Table ✅ COMPLETED

**Migration:** `2025_10_17_142814_create_properties_table.php`

**Schema:**
```sql
CREATE TABLE properties (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    household_id BIGINT UNSIGNED NULLABLE,
    trust_id BIGINT UNSIGNED NULLABLE,

    -- Property type and ownership
    property_type ENUM('main_residence', 'secondary_residence', 'buy_to_let'),
    ownership_type ENUM('individual', 'joint') DEFAULT 'individual',
    ownership_percentage DECIMAL(5,2) DEFAULT 100.00,

    -- Address
    address_line_1 VARCHAR(255) NOT NULL,
    address_line_2 VARCHAR(255) NULLABLE,
    city VARCHAR(255) NOT NULL,
    county VARCHAR(255) NULLABLE,
    postcode VARCHAR(10) NOT NULL,

    -- Financial details
    purchase_date DATE NOT NULL,
    purchase_price DECIMAL(15,2) NOT NULL,
    current_value DECIMAL(15,2) NOT NULL,
    valuation_date DATE NOT NULL,
    sdlt_paid DECIMAL(15,2) NULLABLE COMMENT 'Stamp Duty Land Tax paid',

    -- BTL specific
    monthly_rental_income DECIMAL(10,2) NULLABLE,
    annual_rental_income DECIMAL(15,2) NULLABLE,
    occupancy_rate_percent INT NULLABLE,
    tenant_name VARCHAR(255) NULLABLE,
    lease_start_date DATE NULLABLE,
    lease_end_date DATE NULLABLE,

    -- Costs
    annual_service_charge DECIMAL(10,2) NULLABLE,
    annual_ground_rent DECIMAL(10,2) NULLABLE,
    annual_insurance DECIMAL(10,2) NULLABLE,
    annual_maintenance_reserve DECIMAL(10,2) NULLABLE,
    other_annual_costs DECIMAL(10,2) NULLABLE,

    notes TEXT NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (household_id) REFERENCES households(id) ON DELETE SET NULL,
    FOREIGN KEY (trust_id) REFERENCES trusts(id) ON DELETE SET NULL,
    INDEX (user_id, household_id, trust_id, property_type, ownership_type)
);
```

**Model:** `app/Models/Property.php` - ⏳ IN PROGRESS

---

#### 1.6 Mortgages Table ✅ COMPLETED

**Migration:** `2025_10_17_142836_create_mortgages_table.php`

**Schema:**
```sql
CREATE TABLE mortgages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,

    -- Lender information
    lender_name VARCHAR(255) NOT NULL,
    mortgage_account_number VARCHAR(255) NULLABLE,

    -- Mortgage terms
    mortgage_type ENUM('repayment', 'interest_only', 'part_and_part'),
    original_loan_amount DECIMAL(15,2) NOT NULL,
    outstanding_balance DECIMAL(15,2) NOT NULL,
    interest_rate DECIMAL(5,4) NOT NULL COMMENT 'Annual rate, e.g., 3.5% = 3.5000',
    rate_type ENUM('fixed', 'variable', 'tracker', 'discount'),
    rate_fix_end_date DATE NULLABLE,

    -- Payment details
    monthly_payment DECIMAL(10,2) NOT NULL,
    start_date DATE NOT NULL,
    maturity_date DATE NOT NULL,
    remaining_term_months INT NOT NULL,

    notes TEXT NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (property_id, user_id, mortgage_type)
);
```

**Model:** `app/Models/Mortgage.php` - ⏳ IN PROGRESS

---

#### 1.7 Business Interests Table ✅ COMPLETED

**Migration:** `2025_10_17_142854_create_business_interests_table.php`

**Schema:**
```sql
CREATE TABLE business_interests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    household_id BIGINT UNSIGNED NULLABLE,
    trust_id BIGINT UNSIGNED NULLABLE,

    -- Business details
    business_name VARCHAR(255) NOT NULL,
    company_number VARCHAR(255) NULLABLE COMMENT 'Companies House number',
    business_type ENUM('sole_trader', 'partnership', 'limited_company', 'llp', 'other'),
    ownership_type ENUM('individual', 'joint') DEFAULT 'individual',
    ownership_percentage DECIMAL(5,2) DEFAULT 100.00,

    -- Valuation
    current_valuation DECIMAL(15,2) NOT NULL,
    valuation_date DATE NOT NULL,
    valuation_method VARCHAR(255) NULLABLE,

    -- Financial metrics
    annual_revenue DECIMAL(15,2) NULLABLE,
    annual_profit DECIMAL(15,2) NULLABLE,
    annual_dividend_income DECIMAL(15,2) NULLABLE,

    description TEXT NULLABLE,
    notes TEXT NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (user_id, household_id, trust_id, business_type)
);
```

**Model:** `app/Models/BusinessInterest.php` - ⏳ IN PROGRESS

---

#### 1.8 Chattels Table ✅ COMPLETED

**Migration:** `2025_10_17_142854_create_chattels_table.php`

**Schema:**
```sql
CREATE TABLE chattels (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    household_id BIGINT UNSIGNED NULLABLE,
    trust_id BIGINT UNSIGNED NULLABLE,

    -- Chattel details
    chattel_type ENUM('vehicle', 'art', 'antique', 'jewelry', 'collectible', 'other'),
    name VARCHAR(255) NOT NULL,
    description TEXT NULLABLE,

    -- Ownership
    ownership_type ENUM('individual', 'joint') DEFAULT 'individual',
    ownership_percentage DECIMAL(5,2) DEFAULT 100.00,

    -- Valuation
    purchase_price DECIMAL(15,2) NULLABLE,
    purchase_date DATE NULLABLE,
    current_value DECIMAL(15,2) NOT NULL,
    valuation_date DATE NOT NULL,

    -- Vehicle-specific
    make VARCHAR(255) NULLABLE,
    model VARCHAR(255) NULLABLE,
    year YEAR NULLABLE,
    registration_number VARCHAR(255) NULLABLE,

    notes TEXT NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (user_id, household_id, trust_id, chattel_type)
);
```

**Model:** `app/Models/Chattel.php` - ⏳ IN PROGRESS

---

#### 1.9 Cash Accounts Table ✅ COMPLETED

**Migration:** `2025_10_17_142855_create_cash_accounts_table.php`

**Purpose:** Replaces `savings_accounts` table with enhanced ownership tracking and ISA management.

**Schema:**
```sql
CREATE TABLE cash_accounts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    household_id BIGINT UNSIGNED NULLABLE,
    trust_id BIGINT UNSIGNED NULLABLE,

    -- Account details
    account_name VARCHAR(255) NOT NULL,
    institution_name VARCHAR(255) NOT NULL,
    account_number VARCHAR(255) NULLABLE,
    sort_code VARCHAR(10) NULLABLE,

    -- Account type and purpose
    account_type ENUM('current_account', 'savings_account', 'cash_isa', 'fixed_term_deposit', 'ns_and_i', 'other'),
    purpose ENUM('emergency_fund', 'savings_goal', 'operating_cash', 'other') NULLABLE,

    -- Ownership
    ownership_type ENUM('individual', 'joint') DEFAULT 'individual',
    ownership_percentage DECIMAL(5,2) DEFAULT 100.00,

    -- Balance and interest
    current_balance DECIMAL(15,2) DEFAULT 0.00,
    interest_rate DECIMAL(5,4) NULLABLE,
    rate_valid_until DATE NULLABLE,

    -- ISA tracking
    is_isa BOOLEAN DEFAULT FALSE,
    isa_subscription_current_year DECIMAL(10,2) DEFAULT 0.00,
    tax_year VARCHAR(7) NULLABLE COMMENT 'e.g., 2024/25',

    notes TEXT NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (user_id, household_id, trust_id, account_type, is_isa)
);
```

**Model:** `app/Models/CashAccount.php` - ⏳ IN PROGRESS

**Data Migration Note:** Existing `savings_accounts` data will be migrated to this table in Phase 9.

---

#### 1.10 Personal Accounts Table ✅ COMPLETED

**Migration:** `2025_10_17_142855_create_personal_accounts_table.php`

**Purpose:** Track user's Profit & Loss, Cashflow, and Balance Sheet line items.

**Schema:**
```sql
CREATE TABLE personal_accounts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,

    -- Account type and period
    account_type ENUM('profit_and_loss', 'cashflow', 'balance_sheet'),
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,

    -- Line item details
    line_item VARCHAR(255) NOT NULL COMMENT 'e.g., Employment Income, Mortgage Payment',
    category ENUM('income', 'expense', 'asset', 'liability', 'equity', 'cash_inflow', 'cash_outflow') NULLABLE,
    amount DECIMAL(15,2) NOT NULL,

    notes TEXT NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (user_id, account_type, period_start, period_end)
);
```

**Model:** `app/Models/PersonalAccount.php` - ⏳ IN PROGRESS

---

#### 1.11 Investment Accounts Ownership Fields ✅ COMPLETED

**Migration:** `2025_10_17_142957_add_ownership_fields_to_investment_accounts_table.php`

**Added Fields:**
- `household_id` (nullable, foreign key to households)
- `trust_id` (nullable, foreign key to trusts)
- `ownership_type` (enum: individual/joint, default individual)
- `ownership_percentage` (decimal 5,2, default 100.00)

**Indexes:** household_id, trust_id

---

#### 1.12 Trusts Table Enhancements ✅ COMPLETED

**Migration:** `2025_10_17_143014_add_additional_fields_to_trusts_table.php`

**Added Fields:**
- `household_id` (nullable, foreign key to households)
- `last_valuation_date` (date)
- `next_tax_return_due` (date)
- `total_asset_value` (decimal 15,2) - Aggregated from all assets held in trust

**Index:** household_id

---

#### 1.13 Eloquent Models

**Status Summary:**
- ✅ `Household` - Complete with all relationships
- ✅ `FamilyMember` - Complete
- ⏳ `Property` - Generated, needs completion
- ⏳ `Mortgage` - Generated, needs completion
- ⏳ `BusinessInterest` - Generated, needs completion
- ⏳ `Chattel` - Generated, needs completion
- ⏳ `CashAccount` - Generated, needs completion
- ⏳ `PersonalAccount` - Generated, needs completion
- ⏳ `User` - Needs update with new relationships

**Remaining Work:**
1. Complete Property model with relationships (belongsTo User/Household/Trust, hasMany Mortgage)
2. Complete Mortgage model with relationships (belongsTo Property/User)
3. Complete BusinessInterest model with relationships (belongsTo User/Household/Trust)
4. Complete Chattel model with relationships (belongsTo User/Household/Trust)
5. Complete CashAccount model with relationships (belongsTo User/Household/Trust)
6. Complete PersonalAccount model with relationships (belongsTo User)
7. Update User model to add all new relationships (household, spouse, familyMembers, properties, mortgages, businessInterests, chattels, cashAccounts, personalAccounts)
8. Update existing InvestmentAccount model to add household/trust relationships
9. Update existing Trust model to add household relationship

---

#### 1.14 Run Migrations

**Command:**
```bash
php artisan migrate
```

**Verification:**
```bash
php artisan migrate:status
```

**Rollback (if needed):**
```bash
php artisan migrate:rollback --step=10
```

---

### Phase 1 Success Criteria

✅ All database tables created
✅ All foreign key constraints in place
✅ All Eloquent models defined with relationships
✅ Migrations run successfully without errors
⏳ Basic seeder created for testing (household with 2 users)
⏳ Architecture tests pass (models follow conventions)

---

## Phase 2: User Profile Restructuring

### Objectives
- Transform Settings page into comprehensive User Profile
- Create multi-tab interface for all user data
- Implement CRUD for personal information, family, income, assets, liabilities
- Auto-calculate Personal Accounts (P&L, Cashflow, Balance Sheet)

### Tasks

#### 2.1 Backend - User Profile API

**New Controller:** `app/Http/Controllers/Api/UserProfileController.php`

**Endpoints:**
```php
GET    /api/user/profile                    // Get all profile data
PUT    /api/user/profile/personal           // Update personal info
PUT    /api/user/profile/income-occupation  // Update income/occupation

GET    /api/user/family-members             // List family members
POST   /api/user/family-members             // Add family member
PUT    /api/user/family-members/{id}        // Update family member
DELETE /api/user/family-members/{id}        // Delete family member

GET    /api/user/personal-accounts          // Get P&L, Cashflow, Balance Sheet
POST   /api/user/personal-accounts/calculate // Auto-calculate from user data
POST   /api/user/personal-accounts/line-item // Add manual line item
PUT    /api/user/personal-accounts/line-item/{id} // Update line item
DELETE /api/user/personal-accounts/line-item/{id} // Delete line item
```

**Form Requests:**
- `UpdatePersonalInfoRequest` - Validation for personal fields
- `UpdateIncomeOccupationRequest` - Validation for income/employment fields
- `StoreFamilyMemberRequest` - Validation for family member creation
- `UpdateFamilyMemberRequest` - Validation for family member updates

**Services:**
- `app/Services/UserProfile/UserProfileService.php` - Aggregate user data
- `app/Services/UserProfile/PersonalAccountsService.php` - Auto-calculate P&L/Cashflow/BS

**PersonalAccountsService Methods:**
```php
public function calculateProfitAndLoss(User $user, Carbon $startDate, Carbon $endDate): array
public function calculateCashflow(User $user, Carbon $startDate, Carbon $endDate): array
public function calculateBalanceSheet(User $user, Carbon $asOfDate): array
```

**Auto-Calculation Logic:**
- **P&L Income:** Employment income + self-employment + rental + dividend + other income
- **P&L Expenses:** Mortgage payments + property costs + living expenses (from expenditure_profiles)
- **Cashflow Inflows:** All income sources + asset sales
- **Cashflow Outflows:** All expenses + mortgage principal payments + investments
- **Balance Sheet Assets:** Cash + investments + properties + business interests + chattels + pensions
- **Balance Sheet Liabilities:** Mortgages + other liabilities
- **Balance Sheet Equity:** Assets - Liabilities

---

#### 2.2 Frontend - Profile Vue Components

**Restructure:** `resources/js/views/Settings.vue` → `resources/js/views/UserProfile.vue`

**Tab Structure:**
1. Personal Information
2. Family
3. Income & Occupation
4. Assets (overview with links to Net Worth)
5. Liabilities (overview)
6. Personal Accounts (P&L, Cashflow, Balance Sheet)

**New Components:**

```
resources/js/components/UserProfile/
├── PersonalInformation.vue
├── FamilyMembers.vue
│   └── FamilyMemberForm.vue (modal)
├── IncomeOccupation.vue
├── AssetsOverview.vue
├── LiabilitiesOverview.vue
└── PersonalAccounts.vue
    ├── ProfitAndLossView.vue
    ├── CashflowView.vue
    └── BalanceSheetView.vue
```

**PersonalInformation.vue:**
- Editable fields: name, email, DOB, gender, marital status
- Address fields (line 1, line 2, city, county, postcode)
- Contact: phone, NI number
- Save button (PUT /api/user/profile/personal)

**FamilyMembers.vue:**
- List of family members (spouse read-only, children/dependents editable)
- Add Family Member button → opens FamilyMemberForm modal
- Edit/Delete actions
- Fields: relationship, name, DOB, gender, NI number, annual income, dependent?, education status

**IncomeOccupation.vue:**
- Employment: occupation, employer, industry, employment status
- Income breakdown:
  - Annual employment income
  - Annual self-employment income
  - Annual rental income
  - Annual dividend income
  - Annual other income
- Total annual income (calculated)
- Save button

**AssetsOverview.vue:**
- Cards showing total value of each asset type:
  - Properties (click → Net Worth → Property tab)
  - Investments (click → Net Worth → Investments tab)
  - Cash (click → Net Worth → Cash tab)
  - Business Interests (click → Net Worth → Business tab)
  - Chattels (click → Net Worth → Chattels tab)
- Total Assets value
- Link to Net Worth Dashboard

**LiabilitiesOverview.vue:**
- List of liabilities (mortgages pulled from properties, other liabilities)
- Total liabilities value
- Net Worth calculation (Total Assets - Total Liabilities)

**PersonalAccounts.vue:**
- Tab selector: P&L | Cashflow | Balance Sheet
- Date range picker
- "Calculate" button (auto-calculates from user data)
- "Add Line Item" button (manual entries)
- Chart visualization (bar chart for P&L, line chart for cashflow, bar chart for BS)
- Table view with line items and amounts
- Export to CSV button

---

#### 2.3 Vuex Store

**New Store:** `resources/js/store/modules/userProfile.js`

**State:**
```javascript
{
  personalInfo: {},
  familyMembers: [],
  incomeOccupation: {},
  personalAccounts: {
    profitAndLoss: [],
    cashflow: [],
    balanceSheet: []
  },
  loading: false,
  error: null
}
```

**Actions:**
- `fetchProfile()` - Load all profile data
- `updatePersonalInfo(data)`
- `updateIncomeOccupation(data)`
- `fetchFamilyMembers()`
- `addFamilyMember(data)`
- `updateFamilyMember({ id, data })`
- `deleteFamilyMember(id)`
- `fetchPersonalAccounts({ startDate, endDate })`
- `calculatePersonalAccounts({ startDate, endDate })`
- `addLineItem(data)`
- `updateLineItem({ id, data })`
- `deleteLineItem(id)`

---

#### 2.4 Router Updates

**Update:** `resources/js/router/index.js`

```javascript
{
  path: '/profile',
  name: 'UserProfile',
  component: () => import('../views/UserProfile.vue'),
  meta: { requiresAuth: true, breadcrumb: 'Profile' }
}
```

**Update Navigation:** When user clicks name in top-right, navigate to `/profile` instead of `/settings`

---

### Phase 2 Success Criteria

⬜ User Profile page accessible via clicking username
⬜ All 6 tabs functional with data loading
⬜ Personal info editable and saves successfully
⬜ Family members CRUD operations work
⬜ Income/occupation editable and saves
⬜ Assets/Liabilities overview shows correct totals
⬜ Personal Accounts auto-calculate correctly
⬜ Charts display data properly
⬜ API endpoints return correct data
⬜ Tests pass for UserProfileController

---

## Phase 3: Net Worth Dashboard

### Objectives
- Create primary Net Worth dashboard as main financial overview
- Aggregate assets from all modules (Property, Investment, Cash, Business, Chattels)
- Display net worth card on main dashboard (position 1)
- Implement tabbed Net Worth detail view with asset breakdowns

### Tasks

#### 3.1 Backend - Net Worth API

**New Controller:** `app/Http/Controllers/Api/NetWorthController.php`

**Endpoints:**
```php
GET /api/net-worth/overview         // Aggregated net worth with breakdown
GET /api/net-worth/breakdown        // Asset group breakdown with percentages
GET /api/net-worth/trend            // Historical net worth (monthly snapshots)
GET /api/net-worth/assets-summary   // Summary of all asset types
```

**New Service:** `app/Services/NetWorth/NetWorthService.php`

**Methods:**
```php
public function calculateNetWorth(User $user, ?Carbon $asOfDate = null): array
{
    // Aggregate:
    // - Properties (current_value * ownership_percentage)
    // - Investments (total portfolio value * ownership_percentage)
    // - Cash (current_balance * ownership_percentage)
    // - Business Interests (current_valuation * ownership_percentage)
    // - Chattels (current_value * ownership_percentage)
    // - Subtract: Mortgages + other liabilities

    return [
        'total_assets' => $totalAssets,
        'total_liabilities' => $totalLiabilities,
        'net_worth' => $totalAssets - $totalLiabilities,
        'breakdown' => [
            'property' => $propertyValue,
            'investments' => $investmentsValue,
            'cash' => $cashValue,
            'business' => $businessValue,
            'chattels' => $chattelsValue,
        ],
        'as_of_date' => $asOfDate
    ];
}

public function getNetWorthTrend(User $user, int $months = 12): array
{
    // Return monthly net worth values for trend chart
}
```

**Caching:**
- Cache key: `net_worth:user_{userId}:date_{date}`
- TTL: 30 minutes
- Invalidate on asset/liability changes

---

#### 3.2 Frontend - Net Worth Dashboard

**New Main Dashboard Card:** `resources/js/components/Dashboard/NetWorthOverviewCard.vue`

**Design:**
```vue
<template>
  <div class="net-worth-overview-card" @click="navigateToNetWorth">
    <h3>Net Worth</h3>
    <div class="net-worth-value">£{{ formatCurrency(netWorth) }}</div>
    <div class="asset-breakdown">
      <div class="breakdown-item">
        <span class="label">Property:</span>
        <span class="value">£{{ formatCurrency(breakdown.property) }}</span>
      </div>
      <div class="breakdown-item">
        <span class="label">Investments:</span>
        <span class="value">£{{ formatCurrency(breakdown.investments) }}</span>
      </div>
      <div class="breakdown-item">
        <span class="label">Cash:</span>
        <span class="value">£{{ formatCurrency(breakdown.cash) }}</span>
      </div>
      <div class="breakdown-item">
        <span class="label">Business:</span>
        <span class="value">£{{ formatCurrency(breakdown.business) }}</span>
      </div>
      <div class="breakdown-item">
        <span class="label">Chattels:</span>
        <span class="value">£{{ formatCurrency(breakdown.chattels) }}</span>
      </div>
    </div>
  </div>
</template>
```

**Mobile Design:** Stack breakdown items vertically

---

**New View:** `resources/js/views/NetWorth/NetWorthDashboard.vue`

**Tab Structure:**
1. Overview (charts and summary)
2. Property (list/grid of properties)
3. Investments (embedded InvestmentDashboard)
4. Cash (embedded SavingsDashboard, renamed "Cash")
5. Business Interests (list/grid)
6. Chattels (list/grid)

**New Components:**

```
resources/js/components/NetWorth/
├── NetWorthOverview.vue
│   ├── NetWorthSummaryCard.vue
│   ├── AssetAllocationDonut.vue
│   ├── NetWorthTrendChart.vue
│   └── AssetBreakdownBar.vue
├── PropertyList.vue
│   └── PropertyCard.vue
├── BusinessInterestsList.vue
│   └── BusinessInterestCard.vue
├── ChattelsList.vue
│   └── ChattelCard.vue
└── // Reuse existing Investment and Savings components
```

**NetWorthOverview.vue:**
- Summary cards: Total Assets, Total Liabilities, Net Worth
- Asset allocation donut chart
- Net worth trend line chart (12 months)
- Asset breakdown bar chart

**PropertyList.vue:**
- Grid of PropertyCard components
- Filter: All | Main Residence | Secondary | BTL
- Sort: Value (high to low), Purchase Date
- Add Property button → PropertyForm modal

**PropertyCard.vue:**
- Property address
- Property type badge
- Current value
- Ownership % (if joint)
- Mortgage outstanding (if applicable)
- Click → PropertyDetail view

**BusinessInterestsList.vue / ChattelsList.vue:**
- Similar structure to PropertyList

---

#### 3.3 Vuex Store

**New Store:** `resources/js/store/modules/netWorth.js`

**State:**
```javascript
{
  overview: {
    totalAssets: 0,
    totalLiabilities: 0,
    netWorth: 0,
    breakdown: {},
    asOfDate: null
  },
  trend: [],
  loading: false,
  error: null
}
```

**Actions:**
- `fetchOverview()` - Load net worth overview
- `fetchTrend(months)` - Load historical trend data
- `refreshNetWorth()` - Force refresh (bypass cache)

**Getters:**
- `netWorth` - Total net worth
- `totalAssets` - Sum of all assets
- `totalLiabilities` - Sum of all liabilities
- `assetBreakdown` - Breakdown with percentages
- `trendData` - Formatted for chart

---

#### 3.4 Router Updates

**Add Route:**
```javascript
{
  path: '/net-worth',
  name: 'NetWorth',
  component: () => import('../views/NetWorth/NetWorthDashboard.vue'),
  meta: {
    requiresAuth: true,
    breadcrumb: 'Net Worth'
  },
  children: [
    { path: '', redirect: 'overview' },
    { path: 'overview', name: 'NetWorthOverview', component: NetWorthOverview },
    { path: 'property', name: 'NetWorthProperty', component: PropertyList },
    { path: 'investments', name: 'NetWorthInvestments', component: InvestmentDashboard },
    { path: 'cash', name: 'NetWorthCash', component: SavingsDashboard },
    { path: 'business', name: 'NetWorthBusiness', component: BusinessInterestsList },
    { path: 'chattels', name: 'NetWorthChattels', component: ChattelsList }
  ]
}
```

---

### Phase 3 Success Criteria

⬜ Net Worth card appears first on main dashboard
⬜ Net Worth value calculated correctly (includes ownership %)
⬜ Asset breakdown displayed on card (mobile responsive)
⬜ Clicking card navigates to Net Worth Dashboard
⬜ All 6 tabs functional and load data
⬜ Overview charts render correctly
⬜ Trend chart shows 12 months of data
⬜ Property/Business/Chattels lists display correctly
⬜ Investments and Cash tabs show existing module content
⬜ Net worth updates when assets/liabilities change
⬜ Caching works (30 min TTL)
⬜ Tests pass for NetWorthController and NetWorthService

---

## Phase 4: Property Module

### Objectives
- Complete property management with CRUD operations
- Implement UK tax calculators (SDLT, CGT, rental income tax)
- Support all property types (main residence, secondary, BTL)
- Track mortgages and property costs
- Integrate with Net Worth and Estate modules

### Tasks

#### 4.1 Backend - Property API

**New Controller:** `app/Http/Controllers/Api/PropertyController.php`

**Endpoints:**
```php
GET    /api/properties                   // List all properties
POST   /api/properties                   // Create property
GET    /api/properties/{id}              // Get property details
PUT    /api/properties/{id}              // Update property
DELETE /api/properties/{id}              // Delete property

POST   /api/properties/{id}/calculate-sdlt      // Calculate SDLT
POST   /api/properties/{id}/calculate-cgt       // Calculate CGT
POST   /api/properties/{id}/rental-income-tax   // Calculate rental income tax
```

**New Controller:** `app/Http/Controllers/Api/MortgageController.php`

**Endpoints:**
```php
GET    /api/properties/{propertyId}/mortgages        // List mortgages for property
POST   /api/properties/{propertyId}/mortgages        // Add mortgage
GET    /api/mortgages/{id}                           // Get mortgage details
PUT    /api/mortgages/{id}                           // Update mortgage
DELETE /api/mortgages/{id}                           // Delete mortgage
POST   /api/mortgages/{id}/amortization-schedule    // Generate amortization
```

**Form Requests:**
- `StorePropertyRequest` - Validation for property creation
- `UpdatePropertyRequest` - Validation for property updates
- `StoreMortgageRequest` - Validation for mortgage creation
- `UpdateMortgageRequest` - Validation for mortgage updates

**Services:**

**`app/Services/Property/PropertyService.php`:**
```php
public function calculateEquity(Property $property): float
{
    // current_value - mortgage outstanding balance
}

public function calculateTotalAnnualCosts(Property $property): float
{
    // service charge + ground rent + insurance + maintenance + other costs
}

public function calculateNetRentalYield(Property $property): float
{
    // (annual rental income - annual costs - mortgage interest) / current_value * 100
}
```

**`app/Services/Property/PropertyTaxService.php`:**
```php
public function calculateSDLT(float $purchasePrice, string $propertyType, bool $isFirstHome): array
{
    // Stamp Duty Land Tax calculation
    // Main residence rates: 0% up to £250k (£425k first-time), 5% £250k-£925k, 10% £925k-£1.5m, 12% above £1.5m
    // Additional property: +3% surcharge on all bands

    return [
        'total_sdlt' => $sdlt,
        'effective_rate' => $effectiveRate,
        'breakdown' => $bandBreakdown
    ];
}

public function calculateCGT(Property $property, float $disposalPrice, float $disposalCosts): array
{
    // Capital Gains Tax calculation
    // Gain = disposal price - purchase price - costs - improvement costs
    // Annual exempt amount: £3,000 (2024/25)
    // CGT rates: 18% basic rate, 24% higher rate (for residential property)

    return [
        'gain' => $gain,
        'taxable_gain' => $taxableGain,
        'cgt_liability' => $cgtLiability,
        'effective_rate' => $effectiveRate
    ];
}

public function calculateRentalIncomeTax(Property $property, User $user): array
{
    // Rental income tax calculation
    // Income = annual rental income
    // Expenses = property costs + mortgage interest (20% tax relief)
    // Taxable profit = income - expenses
    // Tax = at user's marginal rate

    return [
        'rental_income' => $income,
        'allowable_expenses' => $expenses,
        'taxable_profit' => $profit,
        'tax_liability' => $tax
    ];
}
```

**`app/Services/Property/MortgageService.php`:**
```php
public function calculateMonthlyPayment(
    float $loanAmount,
    float $interestRate,
    int $termMonths,
    string $mortgageType
): float
{
    // Calculate monthly payment based on mortgage type
}

public function generateAmortizationSchedule(Mortgage $mortgage): array
{
    // Generate full amortization schedule
    // Each month: opening balance, payment, interest, principal, closing balance
}

public function calculateRemainingTerm(Mortgage $mortgage): int
{
    // Calculate months remaining from today to maturity
}
```

---

#### 4.2 Frontend - Property Components

**New Components:**

```
resources/js/components/NetWorth/Property/
├── PropertyCard.vue
├── PropertyDetail.vue
│   └── Tabs: Overview | Mortgage | Financials | Taxes
├── PropertyForm.vue (multi-step modal)
│   ├── Step 1: Basic Info (address, type, purchase details)
│   ├── Step 2: Ownership (individual/joint, percentage, trust)
│   ├── Step 3: Mortgage (add mortgage details)
│   ├── Step 4: Costs (service charge, ground rent, insurance, etc.)
│   └── Step 5: BTL Details (rental income, tenant, lease)
├── MortgageForm.vue
├── PropertyTaxCalculator.vue
│   └── Tabs: SDLT | CGT | Rental Income Tax
└── PropertyFinancials.vue
```

**PropertyCard.vue:**
```vue
<template>
  <div class="property-card" @click="viewDetails">
    <div class="property-header">
      <h4>{{ property.address_line_1 }}</h4>
      <span class="property-type-badge">{{ formatPropertyType(property.property_type) }}</span>
    </div>
    <div class="property-value">
      <span class="label">Current Value:</span>
      <span class="value">£{{ formatCurrency(property.current_value) }}</span>
    </div>
    <div class="property-mortgage" v-if="property.mortgage">
      <span class="label">Mortgage Outstanding:</span>
      <span class="value">£{{ formatCurrency(property.mortgage.outstanding_balance) }}</span>
    </div>
    <div class="property-equity">
      <span class="label">Equity:</span>
      <span class="value">£{{ formatCurrency(equity) }}</span>
    </div>
    <div class="property-ownership" v-if="property.ownership_type === 'joint'">
      <span class="ownership-badge">Joint ({{ property.ownership_percentage }}%)</span>
    </div>
  </div>
</template>
```

**PropertyForm.vue:**
- Multi-step wizard (5 steps)
- Step 1: Address fields, property type, purchase date/price, current value
- Step 2: Ownership type, percentage, household selection, trust selection
- Step 3: Add mortgage (optional) → MortgageForm embedded
- Step 4: Annual costs (service charge, ground rent, insurance, maintenance, other)
- Step 5: BTL-specific (if property_type = buy_to_let): rental income, occupancy rate, tenant, lease dates
- Progress indicator
- Previous/Next/Save buttons
- Validation on each step

**PropertyDetail.vue:**
- Full page view with breadcrumbs (Net Worth > Property > {address})
- Tab navigation:
  - **Overview Tab:** All property details, editable fields, valuation history
  - **Mortgage Tab:** Mortgage details, amortization schedule, payment history
  - **Financials Tab:** Annual costs breakdown, rental income (BTL), net yield calculation
  - **Taxes Tab:** Embedded PropertyTaxCalculator

**PropertyTaxCalculator.vue:**
- Tab navigation: SDLT | CGT | Rental Income Tax
- **SDLT Tab:**
  - Input: Purchase price (pre-filled), property type, first-time buyer?
  - Calculate button
  - Result: Total SDLT, effective rate, band breakdown table
- **CGT Tab:**
  - Input: Disposal price, disposal costs, improvement costs
  - Calculate button
  - Result: Gain, taxable gain, CGT liability, effective rate
  - Warning if main residence (no CGT)
- **Rental Income Tax Tab:**
  - Input: Annual rental income (pre-filled), annual costs (pre-filled)
  - Calculate button
  - Result: Taxable profit, tax liability (at user's marginal rate)

---

#### 4.3 Vuex Store Updates

**Update:** `resources/js/store/modules/netWorth.js`

Add property-specific state:
```javascript
{
  properties: [],
  selectedProperty: null,
  mortgages: []
}
```

**Actions:**
- `fetchProperties()`
- `fetchProperty(id)`
- `createProperty(data)`
- `updateProperty({ id, data })`
- `deleteProperty(id)`
- `calculateSDLT({ propertyId, data })`
- `calculateCGT({ propertyId, data })`
- `calculateRentalIncomeTax(propertyId)`
- `fetchMortgages(propertyId)`
- `createMortgage({ propertyId, data })`
- `updateMortgage({ id, data })`
- `deleteMortgage(id)`
- `fetchAmortizationSchedule(mortgageId)`

---

#### 4.4 Router Updates

**Add Routes:**
```javascript
{
  path: '/net-worth/property/:id',
  name: 'PropertyDetail',
  component: () => import('../components/NetWorth/Property/PropertyDetail.vue'),
  meta: {
    requiresAuth: true,
    breadcrumb: 'Property Details'
  }
}
```

---

### Phase 4 Success Criteria

⬜ Property CRUD operations functional
⬜ PropertyForm wizard works (5 steps)
⬜ Mortgage CRUD operations functional
⬜ SDLT calculator accurate (matches HMRC rates)
⬜ CGT calculator accurate (18%/24% rates, £3k exemption)
⬜ Rental income tax calculator accurate
⬜ Property list displays all properties
⬜ Property detail view shows all tabs
⬜ Mortgage amortization schedule generates correctly
⬜ Property values reflected in Net Worth dashboard
⬜ Joint ownership shows on both spouses' dashboards
⬜ Tests pass for PropertyController, MortgageController, PropertyTaxService

---

## Phase 5: Actions/Recommendations Card

### Objectives
- Aggregate recommendations from all modules
- Prioritize by impact (High/Medium/Low)
- Categorize by type (Tax Optimization, Risk Mitigation, Goal Achievement)
- Enable user actions: "Get Advice" or "Do It Myself"
- Track recommendation status (pending, actioned, dismissed)

### Tasks

#### 5.1 Backend - Recommendations Aggregation

**New Migration:** `create_recommendation_actions_table`

**Schema:**
```sql
CREATE TABLE recommendation_actions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    module ENUM('protection', 'savings', 'investment', 'retirement', 'estate', 'holistic', 'property'),
    recommendation_type VARCHAR(255) NOT NULL COMMENT 'e.g., increase_pension_contribution',
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    priority ENUM('high', 'medium', 'low') DEFAULT 'medium',
    category ENUM('tax_optimization', 'risk_mitigation', 'goal_achievement', 'compliance', 'other') DEFAULT 'other',
    estimated_impact_amount DECIMAL(15,2) NULLABLE COMMENT 'Estimated £ savings or value',
    estimated_impact_description TEXT NULLABLE COMMENT 'e.g., "Save £2,000 in tax"',
    action_taken ENUM('pending', 'get_advice', 'do_it_myself', 'dismissed') DEFAULT 'pending',
    action_date TIMESTAMP NULLABLE,
    notes TEXT NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (user_id, priority, category, action_taken)
);
```

**New Controller:** `app/Http/Controllers/Api/RecommendationsController.php`

**Endpoints:**
```php
GET    /api/recommendations/all                  // Get all recommendations
GET    /api/recommendations/pending              // Get pending only
POST   /api/recommendations/{id}/action          // Update action (get_advice/do_it_myself)
PUT    /api/recommendations/{id}/status          // Update status (dismissed/completed)
DELETE /api/recommendations/{id}                 // Delete recommendation
```

**New Service:** `app/Services/Recommendations/RecommendationsAggregatorService.php`

**Methods:**
```php
public function aggregateRecommendations(User $user): array
{
    // Pull recommendations from all module agents:
    // - ProtectionAgent->getRecommendations()
    // - SavingsAgent->getRecommendations()
    // - InvestmentAgent->getRecommendations()
    // - RetirementAgent->getRecommendations()
    // - EstateAgent->getRecommendations()
    // - PropertyTaxService->getOptimizations()

    // Store in recommendation_actions table
    // Return aggregated list with priority/category
}

public function prioritizeRecommendations(array $recommendations): array
{
    // Sort by:
    // 1. Priority (high > medium > low)
    // 2. Estimated impact amount (descending)
    // 3. Created date (newest first)
}

public function categorizeRecommendations(array $recommendations): array
{
    // Group by category for filtering
}
```

**Example Recommendations:**

**Protection Module:**
- Priority: High, Category: Risk Mitigation
- "Critical illness coverage gap of £250,000 - consider increasing coverage"
- Impact: "Protect against income loss from serious illness"

**Retirement Module:**
- Priority: High, Category: Tax Optimization
- "Unused pension allowance of £15,000 - contribute to save £6,000 in tax"
- Impact: "£6,000 tax saving + £3,000 NI saving"

**Estate Module:**
- Priority: Medium, Category: Tax Optimization
- "IHT liability of £120,000 - consider gifting strategy"
- Impact: "Reduce IHT by £48,000 over 7 years"

**Investment Module:**
- Priority: Low, Category: Tax Optimization
- "Unused ISA allowance of £8,000 - shelter investments from tax"
- Impact: "Save estimated £1,600 in future capital gains tax"

**Property Module:**
- Priority: Medium, Category: Tax Optimization
- "Mortgage rate expires in 6 months - consider remortgaging to save £150/month"
- Impact: "£1,800 annual savings"

---

#### 5.2 Frontend - Actions Card

**New Main Dashboard Card:** `resources/js/components/Dashboard/ActionsOverviewCard.vue`

**Design:**
```vue
<template>
  <div class="actions-overview-card" @click="navigateToActions">
    <h3>Actions & Recommendations</h3>
    <div class="pending-count">
      <span class="count">{{ pendingCount }}</span>
      <span class="label">Pending Actions</span>
    </div>
    <div class="top-recommendations">
      <div class="recommendation-item" v-for="rec in topThree" :key="rec.id">
        <span :class="`priority-badge priority-${rec.priority}`">{{ rec.priority }}</span>
        <span class="recommendation-title">{{ rec.title }}</span>
      </div>
    </div>
    <div class="estimated-impact" v-if="totalImpact > 0">
      <span class="label">Potential Savings:</span>
      <span class="value">£{{ formatCurrency(totalImpact) }}</span>
    </div>
  </div>
</template>
```

---

**New View:** `resources/js/views/Actions/ActionsDashboard.vue`

**Layout:**
- Filter bar (Priority: All|High|Medium|Low, Category: All|Tax|Risk|Goal|Compliance)
- Sort dropdown (Priority, Impact, Date)
- List of RecommendationCard components
- Empty state when no recommendations

**New Components:**

```
resources/js/components/Actions/
├── RecommendationCard.vue
├── RecommendationFilters.vue
├── InitialDisclosureModal.vue
└── SelfExecutionMandateModal.vue
```

**RecommendationCard.vue:**
```vue
<template>
  <div class="recommendation-card">
    <div class="recommendation-header">
      <span :class="`priority-badge priority-${recommendation.priority}`">
        {{ formatPriority(recommendation.priority) }}
      </span>
      <span class="module-badge">{{ recommendation.module }}</span>
      <span class="category-badge">{{ recommendation.category }}</span>
    </div>
    <h4>{{ recommendation.title }}</h4>
    <p>{{ recommendation.description }}</p>
    <div class="estimated-impact" v-if="recommendation.estimated_impact_description">
      <strong>Impact:</strong> {{ recommendation.estimated_impact_description }}
      <span class="impact-amount" v-if="recommendation.estimated_impact_amount">
        (£{{ formatCurrency(recommendation.estimated_impact_amount) }})
      </span>
    </div>
    <div class="action-buttons" v-if="recommendation.action_taken === 'pending'">
      <button @click="getAdvice" class="btn btn-primary">Get Advice</button>
      <button @click="doItMyself" class="btn btn-secondary">Do It Myself</button>
      <button @click="dismiss" class="btn btn-text">Dismiss</button>
    </div>
    <div class="action-status" v-else>
      <span class="status-badge">{{ formatActionStatus(recommendation.action_taken) }}</span>
      <span class="action-date">{{ formatDate(recommendation.action_date) }}</span>
    </div>
  </div>
</template>
```

**InitialDisclosureModal.vue:**
- Displayed when user clicks "Get Advice"
- Content: Initial Disclosure Document (IDD)
- Explains:
  - FPS is a demonstration tool, not regulated advice
  - Recommendation requires professional financial adviser
  - Link to find FCA-regulated adviser
  - Disclaimer and risk warnings
- Action buttons: "Find an Adviser" (external link), "Close"

**SelfExecutionMandateModal.vue:**
- Displayed when user clicks "Do It Myself"
- Content: Self Execution Mandate
- Explains:
  - User is proceeding without professional advice
  - FPS not responsible for outcomes
  - Recommendation based on user-provided data
  - User confirms understanding of risks
- Checkbox: "I understand and accept the risks"
- Action buttons: "Proceed" (enabled only if checkbox checked), "Cancel"
- On Proceed: Mark recommendation as "do_it_myself", navigate to relevant module

---

#### 5.3 Vuex Store

**New Store:** `resources/js/store/modules/recommendations.js`

**State:**
```javascript
{
  recommendations: [],
  filters: {
    priority: 'all',
    category: 'all',
    status: 'pending'
  },
  sort: 'priority',
  loading: false,
  error: null
}
```

**Actions:**
- `fetchRecommendations()`
- `updateRecommendationAction({ id, action })` - 'get_advice' or 'do_it_myself'
- `dismissRecommendation(id)`
- `filterRecommendations(filters)`
- `sortRecommendations(sortBy)`

**Getters:**
- `pendingRecommendations` - Filter by action_taken = 'pending'
- `highPriorityRecommendations` - Filter by priority = 'high'
- `totalEstimatedImpact` - Sum of all estimated_impact_amount
- `recommendationsByCategory` - Group by category
- `topThreeRecommendations` - Top 3 by priority and impact

---

#### 5.4 Router Updates

**Add Route:**
```javascript
{
  path: '/actions',
  name: 'Actions',
  component: () => import('../views/Actions/ActionsDashboard.vue'),
  meta: {
    requiresAuth: true,
    breadcrumb: 'Actions & Recommendations'
  }
}
```

---

### Phase 5 Success Criteria

⬜ Recommendations aggregated from all modules
⬜ Actions card shows pending count and top 3
⬜ Actions dashboard displays all recommendations
⬜ Filtering works (priority, category, status)
⬜ Sorting works (priority, impact, date)
⬜ "Get Advice" opens Initial Disclosure modal
⬜ "Do It Myself" opens Self Execution Mandate modal
⬜ Action status tracked correctly
⬜ Estimated impact calculations accurate
⬜ Total potential savings displayed
⬜ Tests pass for RecommendationsController and Aggregator Service

---

## Phase 6: Trusts Dashboard

### Objectives
- Create dedicated Trusts dashboard with comprehensive trust tracking
- Aggregate assets held in trusts from all modules
- Display trust types, beneficiaries, trustees, tax implications
- Calculate IHT impact of trusts (integration with Estate module)
- Show upcoming tax return due dates

### Tasks

#### 6.1 Backend - Trusts API

**Update Controller:** `app/Http/Controllers/Api/TrustsController.php` (already exists in Estate module)

**New/Updated Endpoints:**
```php
GET    /api/trusts                           // List all trusts
POST   /api/trusts                           // Create trust
GET    /api/trusts/{id}                      // Get trust details
PUT    /api/trusts/{id}                      // Update trust
DELETE /api/trusts/{id}                      // Delete trust

GET    /api/trusts/{id}/assets               // Get all assets held in trust
POST   /api/trusts/{id}/calculate-iht-impact // Calculate IHT implications
GET    /api/trusts/{id}/tax-returns          // Get tax return history
```

**New Service:** `app/Services/Trusts/TrustsService.php`

**Methods:**
```php
public function aggregateTrustAssets(Trust $trust): array
{
    // Aggregate assets where trust_id = trust.id:
    // - Properties
    // - Investment accounts
    // - Cash accounts
    // - Business interests
    // - Chattels

    return [
        'properties' => $properties,
        'investments' => $investments,
        'cash' => $cash,
        'business' => $business,
        'chattels' => $chattels,
        'total_value' => $totalValue
    ];
}

public function calculateIHTImpact(Trust $trust): array
{
    // For relevant property trusts:
    // - 10-year periodic charge (6% of trust value)
    // - Exit charges when assets leave trust
    // - Integration with Estate IHT calculation

    return [
        'is_relevant_property_trust' => $isRPT,
        'next_periodic_charge_date' => $nextChargeDate,
        'estimated_periodic_charge' => $estimatedCharge,
        'trust_impact_on_estate' => $impactOnEstate
    ];
}

public function getUpcomingTaxReturns(User $user): array
{
    // Get all trusts with next_tax_return_due in next 90 days
    // Return sorted by due date
}
```

---

#### 6.2 Frontend - Trusts Dashboard

**New Main Dashboard Card:** `resources/js/components/Dashboard/TrustsOverviewCard.vue`

**Design:**
```vue
<template>
  <div class="trusts-overview-card" @click="navigateToTrusts">
    <h3>Trusts</h3>
    <div class="trust-count">
      <span class="count">{{ trustCount }}</span>
      <span class="label">Active Trusts</span>
    </div>
    <div class="total-trust-value">
      <span class="label">Total Trust Assets:</span>
      <span class="value">£{{ formatCurrency(totalTrustValue) }}</span>
    </div>
    <div class="upcoming-returns" v-if="upcomingReturns.length > 0">
      <span class="label">Tax Returns Due:</span>
      <span class="value">{{ upcomingReturns.length }}</span>
    </div>
  </div>
</template>
```

---

**New View:** `resources/js/views/Trusts/TrustsDashboard.vue`

**Layout:**
- Grid of TrustCard components
- Add Trust button
- Filter: All | Relevant Property | Non-RPT | Inactive

**New Components:**

```
resources/js/components/Trusts/
├── TrustCard.vue
├── TrustDetail.vue
│   └── Tabs: Overview | Assets | Beneficiaries | Tax | Documents
├── TrustForm.vue
├── TrustAssetAllocation.vue (donut chart)
└── TrustTaxSummary.vue
```

**TrustCard.vue:**
```vue
<template>
  <div class="trust-card" @click="viewDetails">
    <div class="trust-header">
      <h4>{{ trust.trust_name }}</h4>
      <span class="trust-type-badge">{{ formatTrustType(trust.trust_type) }}</span>
    </div>
    <div class="trust-value">
      <span class="label">Total Assets:</span>
      <span class="value">£{{ formatCurrency(trust.total_asset_value) }}</span>
    </div>
    <div class="trust-created">
      <span class="label">Established:</span>
      <span class="value">{{ formatDate(trust.trust_creation_date) }}</span>
    </div>
    <div class="tax-return-due" v-if="trust.next_tax_return_due">
      <span class="label">Tax Return Due:</span>
      <span class="value" :class="{ 'overdue': isOverdue(trust.next_tax_return_due) }">
        {{ formatDate(trust.next_tax_return_due) }}
      </span>
    </div>
    <div class="rpt-badge" v-if="trust.is_relevant_property_trust">
      <span>Relevant Property Trust</span>
    </div>
  </div>
</template>
```

**TrustDetail.vue:**
- Full page view with breadcrumbs (Trusts > {trust_name})
- Tab navigation:
  - **Overview Tab:** Trust name, type, creation date, trustees, purpose, notes
  - **Assets Tab:** List of all assets held in trust (properties, investments, cash, etc.), Asset allocation donut chart
  - **Beneficiaries Tab:** List of beneficiaries, their relationship, shares/entitlements
  - **Tax Tab:** IHT implications, periodic charge calculations, exit charge estimates, integration with Estate planning
  - **Documents Tab:** (Future) Upload trust deed, tax returns, correspondence

**TrustForm.vue:**
- Multi-step form:
  - Step 1: Basic Info (name, type, creation date, initial value)
  - Step 2: Trustees (names, contact info)
  - Step 3: Beneficiaries (names, relationship, entitlements)
  - Step 4: Purpose and Notes
  - Step 5: Tax Details (is RPT?, last periodic charge date, next tax return due)
- Validation on each step

**TrustAssetAllocation.vue:**
- Donut chart showing breakdown of trust assets by type
- Legend with values and percentages

**TrustTaxSummary.vue:**
- Summary card showing:
  - Relevant Property Trust status
  - Next periodic charge date (if RPT)
  - Estimated periodic charge amount
  - Impact on estate IHT calculation
  - Link to Estate Planning module

---

#### 6.3 Integration with Other Modules

**Property Module:**
- PropertyForm: Add "Held in Trust?" checkbox → trust_id dropdown

**Investment Module:**
- Update InvestmentAccountForm: Add trust_id field

**Cash Module:**
- Update CashAccountForm: Add trust_id field

**Business Module:**
- Update BusinessInterestForm: Add trust_id field

**Estate Module:**
- Update IHT calculation to include trust assets
- Show trust impact in IHT breakdown

---

#### 6.4 Vuex Store

**Update Store:** `resources/js/store/modules/estate.js` (trusts already there)

Add trust-specific actions:
```javascript
actions: {
  fetchTrusts() {},
  fetchTrust(id) {},
  createTrust(data) {},
  updateTrust({ id, data }) {},
  deleteTrust(id) {},
  fetchTrustAssets(trustId) {},
  calculateTrustIHTImpact(trustId) {},
  fetchUpcomingTaxReturns() {}
}
```

---

#### 6.5 Router Updates

**Add Routes:**
```javascript
{
  path: '/trusts',
  name: 'Trusts',
  component: () => import('../views/Trusts/TrustsDashboard.vue'),
  meta: {
    requiresAuth: true,
    breadcrumb: 'Trusts'
  }
},
{
  path: '/trusts/:id',
  name: 'TrustDetail',
  component: () => import('../components/Trusts/TrustDetail.vue'),
  meta: {
    requiresAuth: true,
    breadcrumb: 'Trust Details'
  }
}
```

---

### Phase 6 Success Criteria

⬜ Trusts dashboard accessible from main dashboard
⬜ Trust CRUD operations functional
⬜ Trust assets aggregated from all modules
⬜ Asset allocation chart displays correctly
⬜ IHT impact calculated for relevant property trusts
⬜ Periodic charge calculations accurate (6% every 10 years)
⬜ Tax return due dates tracked
⬜ Trust card shows upcoming returns
⬜ All asset forms include "held in trust" option
⬜ Estate IHT calculation includes trust assets
⬜ Tests pass for TrustsController and TrustsService

---

## Phase 7: Dashboard Reordering & Card Updates

### Objectives
- Reorder main dashboard cards to new sequence
- Update Retirement card display (remove readiness score, show total pension value)
- Update Estate card display (refocus on IHT/gifting)
- Ensure Protection card unchanged
- Make UK Taxes card admin-only

### Tasks

#### 7.1 Main Dashboard Restructure

**Update:** `resources/js/views/Dashboard.vue`

**New Card Order:**
```vue
<template>
  <div class="dashboard-grid">
    <!-- 1. Net Worth (new) -->
    <NetWorthOverviewCard />

    <!-- 2. Retirement Planning (updated) -->
    <RetirementOverviewCard />

    <!-- 3. Estate Planning (updated) -->
    <EstateOverviewCard />

    <!-- 4. Protection (unchanged) -->
    <ProtectionOverviewCard />

    <!-- 5. Actions & Recommendations (new) -->
    <ActionsOverviewCard />

    <!-- 6. Trusts (new) -->
    <TrustsOverviewCard />

    <!-- 7. UK Taxes & Allowances (admin-only) -->
    <UKTaxesOverviewCard v-if="isAdmin" />
  </div>
</template>

<script>
computed: {
  isAdmin() {
    return this.$store.getters['auth/currentUser']?.role === 'admin';
  }
}
</script>
```

**Grid Layout:**
- Desktop: 3 columns (cards 1-6 visible, card 7 if admin)
- Tablet: 2 columns
- Mobile: 1 column (stacked vertically)

---

#### 7.2 Retirement Card Update

**Update:** `resources/js/components/Retirement/RetirementOverviewCard.vue`

**Remove:**
- Readiness Score gauge

**New Display:**
```vue
<template>
  <div class="retirement-overview-card" @click="navigateToRetirement">
    <h3>Retirement Planning</h3>

    <div class="metric-row">
      <div class="metric">
        <span class="label">Total Pension Value</span>
        <span class="value">£{{ formatCurrency(totalPensionValue) }}</span>
      </div>
    </div>

    <div class="metric-row">
      <div class="metric">
        <span class="label">Years to Retirement</span>
        <span class="value">{{ yearsToRetirement }} years</span>
      </div>
    </div>

    <div class="metric-row">
      <div class="metric">
        <span class="label">Projected Retirement Income</span>
        <span class="value">£{{ formatCurrency(projectedIncome) }}/year</span>
      </div>
    </div>
  </div>
</template>

<script>
computed: {
  totalPensionValue() {
    // Sum of all DC pensions current_fund_value + DB pensions CETV
    return this.$store.getters['retirement/totalPensionValue'];
  },
  yearsToRetirement() {
    // Calculate from user's age and retirement age (default 67)
    const currentAge = this.calculateAge(this.currentUser.date_of_birth);
    const retirementAge = this.$store.getters['retirement/retirementAge'] || 67;
    return Math.max(0, retirementAge - currentAge);
  },
  projectedIncome() {
    // From retirement analysis
    return this.$store.getters['retirement/projectedIncome'];
  }
}
</script>
```

**Update Vuex Getter:** `resources/js/store/modules/retirement.js`
```javascript
getters: {
  totalPensionValue(state) {
    const dcTotal = state.dcPensions.reduce((sum, pension) => sum + parseFloat(pension.current_fund_value || 0), 0);
    const dbTotal = state.dbPensions.reduce((sum, pension) => sum + parseFloat(pension.cetv || 0), 0);
    return dcTotal + dbTotal;
  },
  projectedIncome(state) {
    return state.analysis?.projected_income || 0;
  },
  retirementAge(state) {
    return state.retirementProfile?.target_retirement_age || 67;
  }
}
```

---

#### 7.3 Estate Card Refocus

**Update:** `resources/js/components/Estate/EstateOverviewCard.vue`

**Updated Display:**
```vue
<template>
  <div class="estate-overview-card" @click="navigateToEstate">
    <h3>Estate Planning</h3>

    <div class="metric-row">
      <div class="metric">
        <span class="label">Net Worth</span>
        <span class="value">£{{ formatCurrency(netWorth) }}</span>
      </div>
    </div>

    <div class="metric-row">
      <div class="metric">
        <span class="label">IHT Liability</span>
        <span class="value" :class="ihtLiabilityClass">£{{ formatCurrency(ihtLiability) }}</span>
      </div>
    </div>

    <div class="metric-row">
      <div class="metric">
        <span class="label">Probate Readiness</span>
        <span class="value">{{ probateReadiness }}%</span>
      </div>
    </div>
  </div>
</template>

<script>
computed: {
  netWorth() {
    // Pull from Net Worth service (not stored in Estate anymore)
    return this.$store.getters['netWorth/netWorth'];
  },
  ihtLiability() {
    return this.$store.getters['estate/ihtLiability'];
  },
  ihtLiabilityClass() {
    // Red if > £50k, amber if > £10k, green if £0
    if (this.ihtLiability > 50000) return 'liability-high';
    if (this.ihtLiability > 10000) return 'liability-medium';
    return 'liability-low';
  },
  probateReadiness() {
    // Score based on: Will in place, assets documented, gifts tracked
    return this.$store.getters['estate/probateReadinessScore'];
  }
}
</script>
```

---

#### 7.4 Update Estate Dashboard Focus

**Update:** `resources/js/views/Estate/EstateDashboard.vue`

**Remove Tabs:**
- Assets (moved to User Profile)
- Liabilities (moved to User Profile)
- Net Worth Statement (moved to Net Worth Dashboard)

**Keep/Update Tabs:**
1. **IHT Planning** - IHT calculation, NRB/RNRB tracker, IHT waterfall chart
2. **Gifting Strategy** - PETs, CLTs, gifting timeline chart
3. **Probate Planning** - Probate readiness checklist, will tracking
4. **Will Planning** - Will details, beneficiaries, executors

**Remove Components:**
- AssetForm.vue (moved to Net Worth/User Profile)
- LiabilityForm.vue (moved to User Profile)
- NetWorthStatement.vue (moved to Net Worth)

---

#### 7.5 Protection Card

**No changes required** - Keep current display:
- Adequacy Score (%)
- Total Coverage (£)
- Monthly Premium (£)

---

#### 7.6 Update Dashboard Data Loading

**Update:** `resources/js/store/modules/dashboard.js`

```javascript
actions: {
  async loadDashboard({ commit, dispatch }) {
    commit('SET_LOADING', true);

    try {
      // Load all module data in parallel
      await Promise.allSettled([
        dispatch('netWorth/fetchOverview', null, { root: true }),
        dispatch('retirement/fetchAnalysis', null, { root: true }),
        dispatch('estate/fetchIHTAnalysis', null, { root: true }),
        dispatch('protection/fetchAnalysis', null, { root: true }),
        dispatch('recommendations/fetchRecommendations', null, { root: true }),
        dispatch('estate/fetchTrusts', null, { root: true }) // trusts in estate store
      ]);

      commit('SET_LOADING', false);
    } catch (error) {
      commit('SET_ERROR', error.message);
      commit('SET_LOADING', false);
    }
  }
}
```

---

#### 7.7 Update Breadcrumbs

**Update:** `resources/js/router/index.js`

Ensure breadcrumbs reflect new navigation hierarchy:
- Dashboard > Net Worth > Property > Property Details
- Dashboard > Actions & Recommendations
- Dashboard > Trusts > Trust Details
- Dashboard > Estate Planning (refocused)

---

### Phase 7 Success Criteria

⬜ Dashboard cards in new order (1-7)
⬜ Net Worth card appears first
⬜ Retirement card shows pension value, years to retirement, projected income
⬜ Estate card shows net worth (from NetWorth service), IHT liability, probate readiness
⬜ Protection card unchanged
⬜ Actions card functional
⬜ Trusts card functional
⬜ UK Taxes card visible only to admins
⬜ Mobile layout stacks cards vertically
⬜ All cards clickable and navigate correctly
⬜ Estate Dashboard tabs refocused (no assets/liabilities entry)
⬜ Dashboard loads all data correctly

---

## Phase 8: Admin Roles & RBAC

### Objectives
- Implement basic role-based access control (RBAC)
- Restrict UK Taxes & Allowances card to admin users only
- Add admin role field to User model (already done in Phase 1)
- Create middleware to protect admin-only routes

### Tasks

#### 8.1 Backend - Authorization

**New Middleware:** `app/Http/Middleware/CheckAdminRole.php`

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        return $next($request);
    }
}
```

**Register Middleware:** `app/Http/Kernel.php`

```php
protected $middlewareAliases = [
    // ... existing middleware
    'admin' => \App\Http\Middleware\CheckAdminRole::class,
];
```

---

#### 8.2 Route Protection

**Update:** `routes/api.php`

```php
// Admin-only routes
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/uk-taxes', [UKTaxesController::class, 'index']);
    Route::get('/uk-taxes/allowances', [UKTaxesController::class, 'getAllowances']);
    Route::get('/uk-taxes/rates', [UKTaxesController::class, 'getRates']);
});
```

---

#### 8.3 Frontend - Conditional Rendering

**Update:** `resources/js/views/Dashboard.vue`

```vue
<template>
  <div class="dashboard-grid">
    <!-- Cards 1-6 -->
    <NetWorthOverviewCard />
    <RetirementOverviewCard />
    <EstateOverviewCard />
    <ProtectionOverviewCard />
    <ActionsOverviewCard />
    <TrustsOverviewCard />

    <!-- UK Taxes card - admin only -->
    <UKTaxesOverviewCard v-if="isAdmin" />
  </div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
  computed: {
    ...mapGetters('auth', ['currentUser']),
    isAdmin() {
      return this.currentUser?.role === 'admin';
    }
  }
};
</script>
```

---

#### 8.4 Update Auth Store

**Update:** `resources/js/store/modules/auth.js`

Ensure `role` field included in user state:

```javascript
state: {
  user: null, // Will include role field
  token: null,
  isAuthenticated: false
},

getters: {
  currentUser(state) {
    return state.user;
  },
  isAdmin(state) {
    return state.user?.role === 'admin';
  }
}
```

---

#### 8.5 Update User API Response

**Update:** `app/Http/Controllers/Api/AuthController.php`

Ensure `role` field returned in user endpoint:

```php
public function user(Request $request)
{
    $user = $request->user();

    return response()->json([
        'success' => true,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'date_of_birth' => $user->date_of_birth,
            'gender' => $user->gender,
            'marital_status' => $user->marital_status,
            'role' => $user->role, // IMPORTANT: Include role
            'created_at' => $user->created_at,
        ]
    ]);
}
```

---

#### 8.6 Create Admin User Seeder

**New Seeder:** `database/seeders/AdminUserSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user if not exists
        User::firstOrCreate(
            ['email' => 'admin@fps.test'],
            [
                'name' => 'FPS Administrator',
                'password' => Hash::make('password'),
                'date_of_birth' => '1980-01-01',
                'gender' => 'prefer_not_to_say',
                'marital_status' => 'single',
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
```

**Run Seeder:**
```bash
php artisan db:seed --class=AdminUserSeeder
```

---

#### 8.7 Future RBAC Expansion (Out of Scope)

This basic RBAC implementation can be expanded in future to include:
- Multiple roles (user, admin, adviser, super_admin)
- Granular permissions (view_all_users, edit_tax_config, etc.)
- Role assignment UI for admins
- Audit logging for admin actions

---

### Phase 8 Success Criteria

⬜ CheckAdminRole middleware created and registered
⬜ UK Taxes routes protected with admin middleware
⬜ UK Taxes card only visible to admin users
⬜ Non-admin users get 403 error when accessing admin routes
⬜ Admin user seeder created and run successfully
⬜ User model includes role field in API responses
⬜ isAdmin getter works in Vuex auth store
⬜ Tests pass for admin middleware

---

## Phase 9: Data Migration

### Objectives
- Migrate existing Estate module asset/liability data to new structure
- Migrate existing savings_accounts to cash_accounts table
- Preserve user_id associations
- Ensure data integrity throughout migration
- Create rollback mechanism in case of errors

### Tasks

#### 9.1 Pre-Migration Backup

**Create Backup:**
```bash
# Create database backup before migration
php artisan db:backup

# Or manual backup
mysqldump -u root -p fps_database > fps_backup_pre_migration_$(date +%Y%m%d).sql
```

---

#### 9.2 Migration: Estate Assets to New Asset Tables

**New Artisan Command:** `app/Console/Commands/MigrateEstateToNetWorth.php`

```php
<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\Property;
use App\Models\BusinessInterest;
use App\Models\Chattel;
use App\Models\CashAccount;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateEstateToNetWorth extends Command
{
    protected $signature = 'migrate:estate-to-networth';
    protected $description = 'Migrate estate assets to new asset-specific tables';

    public function handle()
    {
        $this->info('Starting estate asset migration...');

        DB::beginTransaction();

        try {
            $assets = Asset::all();
            $migratedCount = 0;
            $errorCount = 0;

            foreach ($assets as $asset) {
                try {
                    switch ($asset->asset_type) {
                        case 'property':
                            $this->migrateProperty($asset);
                            break;
                        case 'business':
                            $this->migrateBusiness($asset);
                            break;
                        case 'other':
                            $this->migrateOther($asset);
                            break;
                        // 'investment' and 'pension' already have dedicated tables
                    }
                    $migratedCount++;
                } catch (\Exception $e) {
                    $this->error("Error migrating asset {$asset->id}: {$e->getMessage()}");
                    $errorCount++;
                }
            }

            DB::commit();

            $this->info("Migration complete!");
            $this->info("Migrated: {$migratedCount} assets");
            $this->info("Errors: {$errorCount}");

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Migration failed: {$e->getMessage()}");
            return 1;
        }
    }

    private function migrateProperty(Asset $asset)
    {
        // Parse asset_name for address (assumption: stored as address)
        Property::create([
            'user_id' => $asset->user_id,
            'household_id' => null, // Will be set manually by user
            'trust_id' => $asset->ownership_type === 'trust' ? null : null, // Needs manual mapping
            'property_type' => 'main_residence', // Default, user can update
            'ownership_type' => $asset->ownership_type === 'joint' ? 'joint' : 'individual',
            'ownership_percentage' => 100.00, // Default
            'address_line_1' => $asset->asset_name,
            'address_line_2' => null,
            'city' => 'Unknown',
            'county' => null,
            'postcode' => 'UNKNOWN',
            'purchase_date' => $asset->created_at,
            'purchase_price' => $asset->current_value, // Assumption
            'current_value' => $asset->current_value,
            'valuation_date' => $asset->valuation_date,
            'sdlt_paid' => null,
        ]);
    }

    private function migrateBusiness(Asset $asset)
    {
        BusinessInterest::create([
            'user_id' => $asset->user_id,
            'household_id' => null,
            'trust_id' => null,
            'business_name' => $asset->asset_name,
            'company_number' => null,
            'business_type' => 'other', // Default
            'ownership_type' => $asset->ownership_type === 'joint' ? 'joint' : 'individual',
            'ownership_percentage' => 100.00,
            'current_valuation' => $asset->current_value,
            'valuation_date' => $asset->valuation_date,
            'valuation_method' => null,
        ]);
    }

    private function migrateOther(Asset $asset)
    {
        // Assume 'other' assets are chattels
        Chattel::create([
            'user_id' => $asset->user_id,
            'household_id' => null,
            'trust_id' => null,
            'chattel_type' => 'other',
            'name' => $asset->asset_name,
            'description' => null,
            'ownership_type' => $asset->ownership_type === 'joint' ? 'joint' : 'individual',
            'ownership_percentage' => 100.00,
            'purchase_price' => null,
            'purchase_date' => null,
            'current_value' => $asset->current_value,
            'valuation_date' => $asset->valuation_date,
        ]);
    }
}
```

**Run Migration:**
```bash
php artisan migrate:estate-to-networth
```

---

#### 9.3 Migration: Savings Accounts to Cash Accounts

**New Artisan Command:** `app/Console/Commands/MigrateSavingsToCash.php`

```php
<?php

namespace App\Console\Commands;

use App\Models\SavingsAccount; // Old model
use App\Models\CashAccount; // New model
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateSavingsToCash extends Command
{
    protected $signature = 'migrate:savings-to-cash';
    protected $description = 'Migrate savings_accounts to cash_accounts table';

    public function handle()
    {
        $this->info('Starting savings account migration...');

        DB::beginTransaction();

        try {
            $savingsAccounts = SavingsAccount::all();
            $migratedCount = 0;

            foreach ($savingsAccounts as $account) {
                CashAccount::create([
                    'user_id' => $account->user_id,
                    'household_id' => null, // Will be set manually
                    'trust_id' => null,
                    'account_name' => $account->account_name,
                    'institution_name' => $account->institution_name,
                    'account_number' => $account->account_number ?? null,
                    'sort_code' => $account->sort_code ?? null,
                    'account_type' => $this->mapAccountType($account->account_type),
                    'purpose' => $this->mapPurpose($account->purpose),
                    'ownership_type' => 'individual', // Default
                    'ownership_percentage' => 100.00,
                    'current_balance' => $account->current_balance,
                    'interest_rate' => $account->interest_rate,
                    'rate_valid_until' => null,
                    'is_isa' => $account->is_isa ?? false,
                    'isa_subscription_current_year' => $account->isa_subscription_current_year ?? 0,
                    'tax_year' => $account->tax_year ?? '2024/25',
                    'notes' => null,
                ]);
                $migratedCount++;
            }

            DB::commit();

            $this->info("Migration complete! Migrated {$migratedCount} accounts");
            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Migration failed: {$e->getMessage()}");
            return 1;
        }
    }

    private function mapAccountType(string $oldType): string
    {
        // Map old account types to new enum values
        $mapping = [
            'savings' => 'savings_account',
            'current' => 'current_account',
            'isa' => 'cash_isa',
            'fixed_deposit' => 'fixed_term_deposit',
        ];
        return $mapping[$oldType] ?? 'other';
    }

    private function mapPurpose(?string $oldPurpose): ?string
    {
        if (!$oldPurpose) return null;

        $mapping = [
            'emergency' => 'emergency_fund',
            'goal' => 'savings_goal',
            'operating' => 'operating_cash',
        ];
        return $mapping[$oldPurpose] ?? 'other';
    }
}
```

**Run Migration:**
```bash
php artisan migrate:savings-to-cash
```

---

#### 9.4 Data Verification

**New Artisan Command:** `app/Console/Commands/VerifyDataMigration.php`

```php
<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\SavingsAccount;
use App\Models\Property;
use App\Models\BusinessInterest;
use App\Models\Chattel;
use App\Models\CashAccount;
use Illuminate\Console\Command;

class VerifyDataMigration extends Command
{
    protected $signature = 'verify:migration';
    protected $description = 'Verify data migration integrity';

    public function handle()
    {
        $this->info('Verifying data migration...');

        // Count old records
        $oldAssetsCount = Asset::count();
        $oldSavingsCount = SavingsAccount::count();

        // Count new records
        $newPropertiesCount = Property::count();
        $newBusinessCount = BusinessInterest::count();
        $newChattelsCount = Chattel::count();
        $newCashCount = CashAccount::count();

        $this->info("Old Assets: {$oldAssetsCount}");
        $this->info("New Properties: {$newPropertiesCount}");
        $this->info("New Business Interests: {$newBusinessCount}");
        $this->info("New Chattels: {$newChattelsCount}");

        $this->info("Old Savings Accounts: {$oldSavingsCount}");
        $this->info("New Cash Accounts: {$newCashCount}");

        // Check for data integrity
        $missingUsers = Property::whereDoesntHave('user')->count();
        if ($missingUsers > 0) {
            $this->error("{$missingUsers} properties have invalid user_id");
        } else {
            $this->info("All properties have valid user associations");
        }

        return 0;
    }
}
```

**Run Verification:**
```bash
php artisan verify:migration
```

---

#### 9.5 Post-Migration Cleanup (Optional)

**After successful migration and verification:**

**Option 1: Rename old tables** (safer, allows rollback)
```sql
RENAME TABLE assets TO assets_old;
RENAME TABLE savings_accounts TO savings_accounts_old;
```

**Option 2: Drop old tables** (permanent, not recommended until thoroughly tested)
```sql
DROP TABLE assets;
DROP TABLE savings_accounts;
```

**Recommendation:** Keep old tables for at least 30 days, then archive and remove.

---

### Phase 9 Success Criteria

⬜ Database backup created before migration
⬜ Estate assets migrated to appropriate tables
⬜ Savings accounts migrated to cash_accounts
⬜ All user_id associations preserved
⬜ No data loss during migration
⬜ Verification command confirms data integrity
⬜ Migration rollback mechanism tested
⬜ Old tables renamed (not dropped)
⬜ Migration report generated

---

## Phase 10: Testing & Documentation

### Objectives
- Write comprehensive tests for all new functionality
- Update API documentation (Postman collections)
- Create user documentation for new features
- Perform integration testing
- Conduct UAT (User Acceptance Testing)

### Tasks

#### 10.1 Backend Tests (Pest)

**New Test Files:**

```
tests/
├── Unit/
│   ├── Services/
│   │   ├── NetWorthServiceTest.php
│   │   ├── PropertyServiceTest.php
│   │   ├── PropertyTaxServiceTest.php
│   │   ├── MortgageServiceTest.php
│   │   ├── RecommendationsAggregatorServiceTest.php
│   │   ├── TrustsServiceTest.php
│   │   └── PersonalAccountsServiceTest.php
│   └── Models/
│       ├── HouseholdTest.php
│       ├── PropertyTest.php
│       ├── MortgageTest.php
│       ├── FamilyMemberTest.php
│       ├── BusinessInterestTest.php
│       ├── ChattelTest.php
│       ├── CashAccountTest.php
│       └── PersonalAccountTest.php
├── Feature/
│   ├── Api/
│   │   ├── NetWorthControllerTest.php
│   │   ├── PropertyControllerTest.php
│   │   ├── MortgageControllerTest.php
│   │   ├── UserProfileControllerTest.php
│   │   ├── RecommendationsControllerTest.php
│   │   └── TrustsControllerTest.php
│   ├── Auth/
│   │   └── SpouseAccessTest.php
│   ├── MultiUser/
│   │   ├── HouseholdTest.php
│   │   └── JointAssetVisibilityTest.php
│   └── Integration/
│       ├── NetWorthCalculationTest.php
│       ├── PropertyTaxCalculationTest.php
│       └── RecommendationsAggregationTest.php
└── Architecture/
    └── MultiUserArchitectureTest.php
```

**Example Test:** `tests/Unit/Services/NetWorthServiceTest.php`

```php
<?php

use App\Models\User;
use App\Models\Property;
use App\Models\Mortgage;
use App\Models\CashAccount;
use App\Services\NetWorth\NetWorthService;

test('calculates net worth correctly with individual assets', function () {
    $user = User::factory()->create();

    // Create assets
    Property::factory()->create([
        'user_id' => $user->id,
        'current_value' => 400000,
        'ownership_percentage' => 100.00,
    ]);

    CashAccount::factory()->create([
        'user_id' => $user->id,
        'current_balance' => 50000,
        'ownership_percentage' => 100.00,
    ]);

    // Create liability
    Mortgage::factory()->create([
        'user_id' => $user->id,
        'outstanding_balance' => 250000,
    ]);

    $service = new NetWorthService();
    $result = $service->calculateNetWorth($user);

    expect($result['total_assets'])->toBe(450000.00);
    expect($result['total_liabilities'])->toBe(250000.00);
    expect($result['net_worth'])->toBe(200000.00);
});

test('calculates net worth correctly with joint assets', function () {
    $household = Household::factory()->create();
    $user = User::factory()->create(['household_id' => $household->id]);

    Property::factory()->create([
        'user_id' => $user->id,
        'household_id' => $household->id,
        'current_value' => 400000,
        'ownership_type' => 'joint',
        'ownership_percentage' => 50.00, // User owns 50%
    ]);

    $service = new NetWorthService();
    $result = $service->calculateNetWorth($user);

    // Should only count user's 50% share
    expect($result['breakdown']['property'])->toBe(200000.00);
});
```

**Example Test:** `tests/Unit/Services/PropertyTaxServiceTest.php`

```php
<?php

use App\Services\Property\PropertyTaxService;

test('calculates SDLT correctly for main residence under £250k', function () {
    $service = new PropertyTaxService();
    $result = $service->calculateSDLT(200000, 'main_residence', false);

    expect($result['total_sdlt'])->toBe(0.00);
    expect($result['effective_rate'])->toBe(0.00);
});

test('calculates SDLT correctly for main residence £250k-£925k', function () {
    $service = new PropertyTaxService();
    $result = $service->calculateSDLT(500000, 'main_residence', false);

    // £250k @ 0% = £0
    // £250k @ 5% = £12,500
    // Total: £12,500
    expect($result['total_sdlt'])->toBe(12500.00);
    expect($result['effective_rate'])->toBe(2.5); // 12500/500000 * 100
});

test('calculates SDLT correctly for additional property with 3% surcharge', function () {
    $service = new PropertyTaxService();
    $result = $service->calculateSDLT(300000, 'buy_to_let', false);

    // Standard SDLT: £2,500 (£250k @ 0%, £50k @ 5%)
    // Plus 3% surcharge on full amount: £9,000
    // Total: £11,500
    expect($result['total_sdlt'])->toBe(11500.00);
});

test('calculates CGT correctly for secondary property', function () {
    $service = new PropertyTaxService();
    $property = Property::factory()->create([
        'property_type' => 'secondary_residence',
        'purchase_price' => 200000,
        'current_value' => 350000,
    ]);

    $user = User::factory()->create(); // Assume higher rate taxpayer

    $result = $service->calculateCGT($property, 350000, 5000, $user);

    // Gain: £350k - £200k - £5k costs = £145k
    // Less annual exempt amount: £145k - £3k = £142k taxable
    // CGT @ 24%: £34,080
    expect($result['gain'])->toBe(145000.00);
    expect($result['taxable_gain'])->toBe(142000.00);
    expect($result['cgt_liability'])->toBe(34080.00);
});
```

**Example Test:** `tests/Feature/Auth/SpouseAccessTest.php`

```php
<?php

use App\Models\User;
use App\Models\Household;
use App\Models\Property;

test('spouse can view joint assets', function () {
    $household = Household::factory()->create();

    $user1 = User::factory()->create(['household_id' => $household->id]);
    $user2 = User::factory()->create([
        'household_id' => $household->id,
        'spouse_id' => $user1->id,
    ]);
    $user1->update(['spouse_id' => $user2->id]);

    // Create joint property
    $property = Property::factory()->create([
        'user_id' => $user1->id,
        'household_id' => $household->id,
        'ownership_type' => 'joint',
        'ownership_percentage' => 50.00,
    ]);

    // User 2 should see this property in their net worth
    $this->actingAs($user2)
        ->getJson('/api/net-worth/overview')
        ->assertOk()
        ->assertJsonPath('data.breakdown.property', 200000.00); // 50% of £400k
});

test('spouse cannot view individual assets of partner', function () {
    $household = Household::factory()->create();

    $user1 = User::factory()->create(['household_id' => $household->id]);
    $user2 = User::factory()->create([
        'household_id' => $household->id,
        'spouse_id' => $user1->id,
    ]);

    // Create individual property (not joint)
    $property = Property::factory()->create([
        'user_id' => $user1->id,
        'ownership_type' => 'individual',
        'ownership_percentage' => 100.00,
    ]);

    // User 2 should NOT see this property
    $this->actingAs($user2)
        ->getJson('/api/properties')
        ->assertOk()
        ->assertJsonMissing(['id' => $property->id]);
});
```

**Run Tests:**
```bash
# Run all tests
./vendor/bin/pest

# Run specific test suite
./vendor/bin/pest --testsuite=Unit
./vendor/bin/pest --testsuite=Feature

# Run with coverage
./vendor/bin/pest --coverage --min=80

# Run in parallel
./vendor/bin/pest --parallel
```

---

#### 10.2 Frontend Tests

**New Test Files:**

```
resources/js/components/__tests__/
├── Dashboard/
│   ├── NetWorthOverviewCard.spec.js
│   ├── ActionsOverviewCard.spec.js
│   └── TrustsOverviewCard.spec.js
├── NetWorth/
│   ├── NetWorthOverview.spec.js
│   ├── PropertyCard.spec.js
│   └── PropertyForm.spec.js
├── UserProfile/
│   ├── PersonalInformation.spec.js
│   ├── FamilyMembers.spec.js
│   └── PersonalAccounts.spec.js
├── Actions/
│   ├── RecommendationCard.spec.js
│   └── RecommendationFilters.spec.js
└── Trusts/
    ├── TrustCard.spec.js
    └── TrustDetail.spec.js
```

**Example Test:** `NetWorthOverviewCard.spec.js`

```javascript
import { mount } from '@vue/test-utils';
import { createStore } from 'vuex';
import NetWorthOverviewCard from '@/components/Dashboard/NetWorthOverviewCard.vue';

describe('NetWorthOverviewCard', () => {
  let store;

  beforeEach(() => {
    store = createStore({
      modules: {
        netWorth: {
          namespaced: true,
          state: {
            overview: {
              netWorth: 500000,
              breakdown: {
                property: 400000,
                investments: 50000,
                cash: 30000,
                business: 10000,
                chattels: 10000
              }
            }
          },
          getters: {
            netWorth: (state) => state.overview.netWorth,
            breakdown: (state) => state.overview.breakdown
          }
        }
      }
    });
  });

  it('renders net worth value correctly', () => {
    const wrapper = mount(NetWorthOverviewCard, {
      global: {
        plugins: [store]
      }
    });

    expect(wrapper.find('.net-worth-value').text()).toContain('500,000');
  });

  it('renders asset breakdown', () => {
    const wrapper = mount(NetWorthOverviewCard, {
      global: {
        plugins: [store]
      }
    });

    expect(wrapper.text()).toContain('Property: £400,000');
    expect(wrapper.text()).toContain('Investments: £50,000');
    expect(wrapper.text()).toContain('Cash: £30,000');
  });

  it('navigates to net worth dashboard on click', async () => {
    const mockRouter = {
      push: jest.fn()
    };

    const wrapper = mount(NetWorthOverviewCard, {
      global: {
        plugins: [store],
        mocks: {
          $router: mockRouter
        }
      }
    });

    await wrapper.trigger('click');

    expect(mockRouter.push).toHaveBeenCalledWith('/net-worth');
  });
});
```

**Run Tests:**
```bash
npm run test
```

---

#### 10.3 API Documentation

**Update Postman Collections:**

Create new collection: `FPS - Multi-User & Net Worth`

**Folders:**
1. **User Profile**
   - GET /api/user/profile
   - PUT /api/user/profile/personal
   - PUT /api/user/profile/income-occupation
   - CRUD /api/user/family-members

2. **Net Worth**
   - GET /api/net-worth/overview
   - GET /api/net-worth/breakdown
   - GET /api/net-worth/trend

3. **Properties**
   - CRUD /api/properties
   - POST /api/properties/{id}/calculate-sdlt
   - POST /api/properties/{id}/calculate-cgt
   - POST /api/properties/{id}/rental-income-tax

4. **Mortgages**
   - CRUD /api/properties/{propertyId}/mortgages
   - POST /api/mortgages/{id}/amortization-schedule

5. **Recommendations**
   - GET /api/recommendations/all
   - POST /api/recommendations/{id}/action
   - PUT /api/recommendations/{id}/status

6. **Trusts**
   - CRUD /api/trusts
   - GET /api/trusts/{id}/assets
   - POST /api/trusts/{id}/calculate-iht-impact

**Export Collection:**
```bash
# Export from Postman and save to:
docs/postman/FPS_MultiUser_NetWorth.postman_collection.json
```

---

#### 10.4 User Documentation

**Create Documentation Files:**

```
docs/
├── user-guide/
│   ├── 01-getting-started.md
│   ├── 02-user-profile.md
│   ├── 03-net-worth-dashboard.md
│   ├── 04-property-management.md
│   ├── 05-actions-recommendations.md
│   ├── 06-trusts-tracking.md
│   ├── 07-spouse-joint-assets.md
│   └── 08-tax-calculators.md
├── technical/
│   ├── architecture.md
│   ├── multi-user-design.md
│   ├── api-reference.md
│   └── data-migration-guide.md
└── images/
    └── screenshots/
```

**Example:** `docs/user-guide/03-net-worth-dashboard.md`

```markdown
# Net Worth Dashboard

The Net Worth Dashboard provides a comprehensive view of your financial position by aggregating all your assets and liabilities.

## Overview

Your net worth is calculated as:

**Net Worth = Total Assets - Total Liabilities**

### Asset Categories

- **Property:** Main residence, secondary properties, buy-to-let investments
- **Investments:** Stocks, bonds, funds held in investment accounts
- **Cash:** Savings accounts, current accounts, Cash ISAs
- **Business Interests:** Ownership stakes in businesses
- **Chattels:** Vehicles, art, antiques, collectibles

### Ownership Percentages

If you own an asset jointly (e.g., with your spouse), only your ownership percentage is included in your net worth calculation.

Example: A £400,000 property owned 50/50 with your spouse contributes £200,000 to your net worth.

## Accessing Net Worth Dashboard

1. From the main dashboard, click the **Net Worth** card
2. This will take you to the detailed Net Worth Dashboard

## Net Worth Overview Tab

The Overview tab displays:

- **Total Assets:** Sum of all your assets
- **Total Liabilities:** Sum of all your liabilities (mortgages, loans)
- **Net Worth:** Your total net worth
- **Asset Allocation Chart:** Visual breakdown of assets by category
- **Net Worth Trend:** Historical net worth over the past 12 months

## Managing Assets

### Properties

Click the **Property** tab to:
- View all your properties
- Add a new property (main residence, secondary, or buy-to-let)
- Edit property details
- Add mortgage information
- Calculate SDLT, CGT, and rental income tax

[See Property Management guide for details](04-property-management.md)

### Investments

Click the **Investments** tab to view your investment portfolio.

### Cash

Click the **Cash** tab to view your savings and current accounts.

### Business Interests

Click the **Business** tab to track your business ownership.

### Chattels

Click the **Chattels** tab to record valuable possessions (vehicles, art, etc.)

## Joint Assets with Spouse

If you are married/partnered and your spouse also has an FPS account:

1. Link your accounts via User Profile > Family
2. Set assets as "joint" when creating/editing them
3. Specify ownership percentage (e.g., 50% each)
4. Both spouses will see the asset, but only their percentage contributes to individual net worth

## Updating Valuations

To keep your net worth accurate:

- Update property valuations annually (or when significant changes occur)
- Update investment account values monthly (or sync with your platform)
- Update cash balances regularly

## Troubleshooting

**Q: Why is my net worth different from last month?**
A: Net worth changes when asset values or liabilities change. Check the trend chart to see which asset categories changed.

**Q: My spouse's assets are showing in my net worth**
A: Only joint assets (where you're listed as co-owner) should appear. Check the ownership type and percentage on each asset.
```

---

#### 10.5 Integration Testing

**Test Scenarios:**

1. **End-to-End User Journey:**
   - User registers
   - Completes profile (personal info, income, family)
   - Adds properties with mortgages
   - Adds cash accounts
   - Views Net Worth dashboard
   - Sees recommendations on Actions card
   - Creates trust and moves property to trust
   - Verifies IHT calculation includes trust

2. **Multi-User Journey:**
   - User 1 registers
   - User 2 registers (spouse)
   - Users link accounts via household
   - User 1 creates joint property (50/50)
   - User 2 logs in and sees joint property
   - Both users' net worth reflects 50% share
   - User 1 adds individual asset (not joint)
   - User 2 does NOT see individual asset

3. **Tax Calculation Journey:**
   - User adds property with purchase price £400k
   - Calculates SDLT (should show £7,500)
   - Updates property value to £600k
   - Calculates CGT on disposal (should calculate gain)
   - User adds rental income to BTL property
   - Calculates rental income tax

---

#### 10.6 UAT (User Acceptance Testing)

**UAT Checklist:**

- [ ] User can complete profile with all fields
- [ ] User can add family members
- [ ] User can add properties (all 3 types)
- [ ] User can add mortgages to properties
- [ ] User can add business interests and chattels
- [ ] Net Worth dashboard calculates correctly
- [ ] SDLT calculator matches HMRC calculator
- [ ] CGT calculator accurate
- [ ] Recommendations appear on Actions card
- [ ] User can action recommendations ("Get Advice"/"Do It Myself")
- [ ] Trusts dashboard tracks all trusts
- [ ] Trust assets aggregated correctly
- [ ] Spouse can see joint assets only
- [ ] Spouse cannot see individual assets
- [ ] Dashboard cards in correct order
- [ ] UK Taxes card only visible to admin
- [ ] Retirement card shows pension value
- [ ] Estate card shows IHT liability
- [ ] All navigation works (breadcrumbs, links)
- [ ] Mobile responsive design works

---

### Phase 10 Success Criteria

⬜ 60+ backend tests written and passing
⬜ 20+ frontend tests written and passing
⬜ Code coverage > 80%
⬜ All architecture tests pass
⬜ Postman collections updated
⬜ User guide documentation complete
⬜ Technical documentation complete
⬜ Integration tests pass
⬜ UAT checklist 100% complete
⬜ No critical bugs identified
⬜ Performance benchmarks met (page load < 2s)

---

## Implementation Timeline

### Week-by-Week Breakdown

| Week | Phase | Key Deliverables | Estimated Hours |
|------|-------|------------------|-----------------|
| **1** | Phase 1.1-1.7 | Database migrations, User/Household/Family models | 40 |
| **2** | Phase 1.8-1.14 | Property/Mortgage/Business/Chattel/Cash models, Run migrations | 40 |
| **3** | Phase 2.1 | User Profile API (backend) | 40 |
| **4** | Phase 2.2 | User Profile UI (frontend) | 40 |
| **5** | Phase 3.1 | Net Worth API (backend) | 40 |
| **6** | Phase 3.2 | Net Worth Dashboard (frontend) | 40 |
| **7** | Phase 4.1 | Property API + Tax Services | 40 |
| **8** | Phase 4.2 | Property UI + Tax Calculators | 40 |
| **9** | Phase 5 | Actions/Recommendations (full stack) | 40 |
| **10** | Phase 6 | Trusts Dashboard (full stack) | 40 |
| **11** | Phase 7 + 8 | Dashboard reordering + Admin RBAC | 40 |
| **12** | Phase 9 | Data Migration | 40 |
| **13** | Phase 10 | Testing + Documentation | 40 |

**Total:** 13 weeks, 520 hours (full-time equivalent)

### Milestones

- **End of Week 2:** Phase 1 Complete - Database foundation ready
- **End of Week 4:** Phase 2 Complete - User Profile functional
- **End of Week 6:** Phase 3 Complete - Net Worth Dashboard live
- **End of Week 8:** Phase 4 Complete - Property module with tax calculators
- **End of Week 10:** Phases 5-6 Complete - Actions/Recommendations + Trusts
- **End of Week 11:** Phases 7-8 Complete - Dashboard finalized
- **End of Week 13:** Phases 9-10 Complete - Migration + Testing done

---

## Success Criteria

### Functional Requirements

✅ **Multi-User Functionality**
- Spouses can create separate accounts linked via household
- Joint assets visible to both spouses with correct ownership %
- Individual assets not visible to spouse
- Each spouse has independent dashboard

✅ **Net Worth Dashboard**
- Aggregates assets from 5 categories (Property, Investments, Cash, Business, Chattels)
- Displays net worth prominently on main dashboard card
- Detail view with 6 tabs (Overview, Property, Investments, Cash, Business, Chattels)
- Asset allocation chart
- Net worth trend (12 months)

✅ **Property Module**
- CRUD for properties (main/secondary/BTL)
- CRUD for mortgages linked to properties
- SDLT calculator (accurate to HMRC rates)
- CGT calculator (18%/24% rates, £3k exemption)
- Rental income tax calculator

✅ **Actions/Recommendations**
- Aggregates recommendations from all 7 modules
- Prioritizes by impact (High/Medium/Low)
- Categorizes by type (Tax Optimization, Risk Mitigation, Goal Achievement, Compliance)
- "Get Advice" opens Initial Disclosure
- "Do It Myself" opens Self Execution Mandate
- Tracks action status

✅ **Trusts Dashboard**
- CRUD for trusts
- Aggregates assets held in trusts
- Calculates IHT implications (periodic charges for RPTs)
- Shows tax return due dates
- Integrates with Estate IHT calculation

✅ **User Profile Expansion**
- 6 tabs: Personal Info, Family, Income/Occupation, Assets, Liabilities, Personal Accounts
- Auto-calculates P&L, Cashflow, Balance Sheet
- Charts for financial statements

✅ **Dashboard Reordering**
- Cards in order: Net Worth, Retirement, Estate, Protection, Actions, Trusts, UK Taxes (admin)
- Retirement card shows: Total Pension Value, Years to Retirement, Projected Income
- Estate card shows: Net Worth (from NetWorth service), IHT Liability, Probate Readiness
- UK Taxes card visible only to admin

✅ **Admin RBAC**
- Basic role-based access control implemented
- Admin middleware protects admin-only routes
- UK Taxes card/routes restricted to admin role

✅ **Data Migration**
- Estate assets migrated to new tables
- Savings accounts migrated to cash_accounts
- All user_id associations preserved
- No data loss

---

### Technical Requirements

✅ **Database**
- All 15 migrations created and run successfully
- All foreign key constraints in place
- All indexes created for performance

✅ **Backend**
- All API endpoints implemented (40+ endpoints)
- All controllers follow RESTful conventions
- All services implemented (NetWorth, Property, PropertyTax, Mortgage, RecommendationsAggregator, Trusts, PersonalAccounts)
- Authorization middleware functional
- Caching implemented (30 min TTL for net worth)

✅ **Frontend**
- All 30+ Vue components created
- All Vuex stores implemented (netWorth, userProfile, recommendations)
- All routes added to router
- Breadcrumbs functional
- Mobile responsive (320px - 2560px)

✅ **Testing**
- 60+ backend tests (Unit, Feature, Architecture)
- 20+ frontend tests
- Code coverage > 80%
- All tests pass

✅ **Documentation**
- API documentation updated (Postman collections)
- User guide complete (8 chapters)
- Technical documentation complete
- Migration guide written

---

### Performance Requirements

✅ **Page Load Times**
- Main dashboard: < 2 seconds
- Net Worth dashboard: < 3 seconds
- Property detail page: < 2 seconds

✅ **API Response Times**
- Net worth calculation: < 500ms (cached)
- Property tax calculations: < 200ms
- Recommendations aggregation: < 1 second

✅ **Database Queries**
- All queries optimized with indexes
- N+1 query problems resolved
- Eager loading used where appropriate

---

## Risk Assessment & Mitigation

### High-Risk Items

1. **Data Migration Integrity**
   - Risk: Data loss or corruption during migration
   - Mitigation: Full database backup, transaction-wrapped migration, verification script, rollback plan

2. **Multi-User Authorization**
   - Risk: Spouse seeing individual (non-joint) assets
   - Mitigation: Comprehensive authorization tests, middleware checks on every query, UAT with real user scenarios

3. **Net Worth Calculation Accuracy**
   - Risk: Incorrect ownership % calculations
   - Mitigation: Unit tests for all calculation scenarios, manual verification against known datasets

4. **Tax Calculator Accuracy**
   - Risk: SDLT/CGT calculations incorrect
   - Mitigation: Test against HMRC calculators, cross-reference with UK tax tables, user disclaimer

### Medium-Risk Items

1. **Performance with Large Datasets**
   - Risk: Slow page loads with 100+ assets
   - Mitigation: Database indexing, query optimization, caching, pagination

2. **Frontend Complexity**
   - Risk: Difficult to maintain with 30+ new components
   - Mitigation: Component reusability, consistent patterns, documentation, code reviews

3. **Timeline Overrun**
   - Risk: 13-week estimate may be optimistic
   - Mitigation: Weekly progress reviews, prioritize MVPs, defer non-critical features

---

## Assumptions

1. All development is done by a single full-time developer (or equivalent team)
2. Backend uses Laravel 10.x, frontend uses Vue.js 3
3. Database is MySQL 8.0+
4. No external API integrations required (all data manually entered)
5. UK tax rules are current as of 2024/25 tax year
6. No mobile native apps (web responsive only)
7. Admin functionality limited to hiding UK Taxes card (no full admin panel)
8. Trust tracking is basic (no full trust accounting)
9. Property tax calculators are informational only (not regulated advice)

---

## Constraints

1. **UK Only:** System designed exclusively for UK tax rules and products
2. **Manual Data Entry:** No bank/platform integrations
3. **Demonstration System:** Not a production financial advisory tool
4. **No Real-Time Market Data:** Investment prices manually updated
5. **Browser Support:** Modern browsers only (Chrome, Firefox, Safari, Edge)
6. **Database Size:** Tested with up to 10,000 users, 50,000 assets

---

## Future Enhancements (Out of Scope)

The following features are NOT included in this restructuring but could be added later:

1. **Bank/Platform Integrations:** Open Banking API for automated data import
2. **Real-Time Market Data:** Live investment prices and valuations
3. **Multi-Currency Support:** Track assets in multiple currencies
4. **Adviser Portal:** Multi-user access for financial advisers
5. **Mobile Native Apps:** iOS and Android native applications
6. **AI Recommendations:** Machine learning for personalized recommendations
7. **Document Storage:** Upload and store documents (deeds, wills, policies)
8. **Advanced Reporting:** Custom report builder with PDF export
9. **International Tax:** Support for non-UK tax jurisdictions
10. **Goal Tracking:** Visual goal progress with milestones

---

## Contact & Support

For questions or issues during implementation:

- **Technical Lead:** [Name]
- **Project Manager:** [Name]
- **Documentation:** See `/docs` directory
- **Issue Tracker:** GitHub Issues

---

## Document History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | 2025-10-17 | Claude Code Assistant | Initial plan created |

---

**End of Comprehensive FPS Restructuring Plan**
