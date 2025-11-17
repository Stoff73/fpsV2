# CLAUDE.md

Development guidelines for Claude Code when working with the TenGo financial planning application.

---

## ⚠️ CRITICAL RULES

### 1. MAINTAIN APPLICATION FUNCTIONALITY

**THE APPLICATION MUST REMAIN FULLY FUNCTIONAL AT ALL TIMES.**

- **DO NOT REMOVE** existing features, functionality, pages, views, components, or working code
- **ONLY WORK** on the specific section explicitly being modified
- **IF OTHER AREAS ARE AFFECTED**: STOP, explain impact, ASK PERMISSION, wait for approval before proceeding
- **BEFORE MAKING CHANGES**: Understand full scope, identify all affected files, ensure backward compatibility

### 2. DATABASE BACKUP PROTOCOL

**ALWAYS CHECK FOR AND MAINTAIN DATABASE BACKUPS BEFORE ANY DESTRUCTIVE OPERATIONS.**

- **BEFORE Database Wipe**: Create backup via admin panel, verify it exists in `storage/app/backups/`
- **NEVER** run `migrate:fresh` or `migrate:refresh` without explicit user approval
- **Admin Account**: `admin@fps.com` / `admin123` (ID: 1016)

**Commands to AVOID Without Backup:**
- ❌ `php artisan migrate:fresh`, `migrate:refresh`, `db:wipe`
- ❌ `php artisan migrate:rollback` (except single recent migration)

**Safe Commands:**
- ✅ `php artisan migrate` (forward only)
- ✅ Admin panel backup/restore system

### 3. NEVER HARDCODE USER INPUT VALUES

**⚠️ CRITICAL: ALWAYS USE DATABASE AND USER-PROVIDED VALUES - NEVER OVERRIDE WITH HARDCODED DEFAULTS**

- **NEVER** hardcode values from user inputs unless explicitly instructed to do so
- **ALWAYS** use the actual data provided by users in forms
- **ALWAYS** store and retrieve values exactly as entered by the user
- **NEVER** manipulate, override, or replace user input with default values like 'To be completed', 'Unknown', etc.

**Examples:**

❌ **WRONG - Hardcoding values:**
```php
$mortgageData = [
    'lender_name' => 'To be completed',  // NEVER DO THIS
    'mortgage_type' => 'repayment',      // NEVER DO THIS
];
```

✅ **CORRECT - Using actual user input:**
```php
$mortgageData = [
    'lender_name' => $validated['lender_name'] ?? 'To be completed',  // Use input, default only if not provided
    'mortgage_type' => $validated['mortgage_type'] ?? 'repayment',    // Use input, default only if not provided
];
```

**The only acceptable time to use default values is when the user has NOT provided any input at all.**

### 4. USE AVAILABLE SKILLS

**BEFORE STARTING ANY TASK, CHECK IF THERE IS A RELEVANT SKILL AVAILABLE.**

Available Skills:
1. **systematic-debugging** - For ALL bug reports, unexpected behavior, troubleshooting
2. **fps-module-builder** - For creating new modules (full-stack)
3. **fps-feature-builder** - For adding/extending features in existing modules
4. **fps-component-builder** - For creating Vue 3 components

Available Agents:
1. **laravel-stack-deployer** - For Laravel + MySQL + Vue.js + Vite deployment to production/staging/development environments

**FAILURE TO USE AVAILABLE SKILLS AND AGENTS IS UNACCEPTABLE.**

### 5. UNIFIED FORM COMPONENTS

**⚠️ THE APPLICATION USES ONE FORM FOR ALL INPUTS ACROSS ALL AREAS**

**CRITICAL**: All data input forms MUST be reusable across the entire application:
- ✅ The SAME form component is used whether adding data during onboarding, from the module dashboard, or editing existing data
- ✅ Forms are located in module-specific component folders (e.g., `components/Protection/PolicyFormModal.vue`)
- ✅ Forms accept a data prop and a mode/isEditing prop
- ✅ Forms emit `@save` and `@close` events (NEVER use `@submit` which causes double submission)

**Pattern to Follow:**
```vue
<!-- Parent Component -->
<ItemForm
  v-if="showForm"
  :item="editingItem"
  :is-editing="!!editingItem"
  @save="handleItemSaved"
  @close="closeForm"
/>
```

**DO NOT:**
- ❌ Create separate forms for onboarding vs. dashboard
- ❌ Duplicate form logic across components
- ❌ Use `@submit` event name (causes double submission bug)

### 6. ENVIRONMENT VARIABLE CONTAMINATION

**⚠️ THE #1 CAUSE OF DEVELOPMENT ENVIRONMENT FAILURES IS ENVIRONMENT VARIABLE POLLUTION**

**NEVER** export production environment variables in development sessions:
- ❌ **NEVER** run: `export $(cat .env.production | xargs)`
- ❌ **NEVER** run: `source .env.production`

**SOLUTION - Always use the startup script:**
```bash
./dev.sh
```

**DIAGNOSIS - Check for contamination:**
```bash
printenv | grep -E "^APP_|^DB_|^VITE_|^CACHE_"
```

**COMMON SYMPTOMS:**
1. CORS errors from production URL
2. Database errors with production DB user
3. Cache errors: "This cache store does not support tagging"
4. Vite shows wrong URL in output

### 7. CANONICAL DATA TYPES - ONE SOURCE OF TRUTH

**⚠️ ALL ASSET/LIABILITY/PROPERTY TYPES MUST USE CANONICAL VALUES DEFINED BY DATABASE MIGRATIONS**

#### Property Types (ONLY THREE ALLOWED)

```php
'main_residence'         → "Main Residence"
'secondary_residence'    → "Secondary Residence"
'buy_to_let'            → "Buy to Let"
```

**❌ FORBIDDEN**: `second_home`, `commercial`, `land`

#### Ownership Types (ALL MODULES)

```php
'individual'       → "Individual"
'joint'           → "Joint"
'trust'           → "Trust"
```

**❌ FORBIDDEN**: Never use `sole` - always use `individual`

#### Other Canonical Types

**Investment Account Types**: `isa`, `gia`, `nsi`, `onshore_bond`, `offshore_bond`, `vct`, `eis`

**Liability Types**: `mortgage`, `loan`, `credit_card`, `other`

**Life Insurance Types**: `term`, `whole_of_life`, `decreasing_term`, `family_income_benefit`, `level_term`

**Critical Illness Types**: `standalone`, `accelerated`, `additional`

**Savings Access Types**: `immediate`, `notice`, `fixed`

**DC Pension Types**: `occupational`, `sipp`, `personal`, `stakeholder`

**ENFORCEMENT RULES:**
1. **Database is Source of Truth**: Always check migration files first
2. **No Variations**: If migration says `secondary_residence`, NEVER use `second_home`
3. **Backend Validation**: All Form Requests must validate against canonical values only
4. **Frontend Forms**: All `<select>` options must use canonical values exactly

---

## Project Overview

**TenGo** - UK-focused comprehensive financial planning application covering five integrated modules: Protection, Savings, Investment, Retirement, and Estate Planning.

**Current Version**: v0.2.7 (Beta - Production Ready)
**Tech Stack**: Laravel 10.x (PHP 8.2+) + Vue.js 3 + MySQL 8.0+ + Memcached
**Status**: All core modules complete, 95% advanced features complete

---

## Architecture Overview

### Agent-Based System

Each module has an **Agent** that orchestrates analysis:
- **ProtectionAgent**: Life/CI/IP coverage analysis
- **SavingsAgent**: Emergency fund & ISA tracking
- **InvestmentAgent**: Portfolio analysis & Monte Carlo simulations
- **RetirementAgent**: Pension projections & readiness scoring
- **CoordinatingAgent**: Cross-module holistic planning

**Note**: Estate module uses direct service architecture (EstateAgent deprecated in favor of IHTCalculationService).

### Three-Tier Architecture

```
Vue.js 3 Frontend (150+ components)
        ↓ REST API (80+ endpoints)
Laravel 10.x Application Layer (6 Agents + 40+ Services)
        ↓ Eloquent ORM
MySQL 8.0+ (45+ tables) + Memcached
```

---

## Essential Development Commands

### Running Development Servers

**⚠️ CRITICAL**: You must run **BOTH** servers simultaneously.

**Recommended:**
```bash
./dev.sh
```

**Manual:**
```bash
# Terminal 1 - Laravel Backend (REQUIRED)
php artisan serve

# Terminal 2 - Vite Frontend (REQUIRED)
npm run dev

# Terminal 3 - Queue Worker (Optional, for Monte Carlo)
php artisan queue:work database
```

### Testing

```bash
# Run all tests
./vendor/bin/pest

# Run specific test suite
./vendor/bin/pest --testsuite=Unit
```

### Code Formatting

```bash
# Run Laravel Pint (PSR-12 formatter)
./vendor/bin/pint
```

---

## Key Implementation Patterns

### 1. Centralized Tax Configuration (DATABASE-DRIVEN)

**CRITICAL**: All UK tax values are stored in `tax_configurations` table, NOT hardcoded.

**Usage Pattern**:
```php
use App\Services\TaxConfigService;

class MyService
{
    public function __construct(private TaxConfigService $taxConfig) {}

    public function calculate()
    {
        $personalAllowance = $this->taxConfig->getIncomeTax()['personal_allowance'];
        $isaAllowance = $this->taxConfig->getISAAllowances()['annual_allowance'];
    }
}
```

**Available Methods**:
- `getIncomeTax()` - Income tax bands and personal allowance
- `getNationalInsurance()` - NI thresholds and rates
- `getCapitalGainsTax()` - CGT rates and exemptions
- `getDividendTax()` - Dividend tax allowances and rates
- `getISAAllowances()` - ISA annual limits
- `getPensionAllowances()` - Pension annual allowance, MPAA, taper rules
- `getInheritanceTax()` - IHT rates, NRB, RNRB, gifting rules
- `getStampDuty()` - SDLT bands and rates
- `get(string $key)` - Get any nested value using dot notation

**DO NOT**:
- ❌ Use `config('uk_tax_config')` - DEPRECATED
- ❌ Hardcode tax values in services

**DO**:
- ✅ Inject TaxConfigService via constructor
- ✅ Mock TaxConfigService in unit tests

### 2. ISA Allowance Tracking (Cross-Module)

**Total Allowance**: £20,000 per tax year (April 6 - April 5)

**Implementation**: `app/Services/Savings/ISATracker.php`
- Aggregates Cash ISAs from Savings module
- Aggregates S&S ISAs from Investment module
- Warns when approaching or exceeding limit

### 3. Asset Ownership Patterns

**Ownership Types**: Individual, Joint, Trust

**Database Pattern**:
```php
$table->enum('ownership_type', ['individual', 'joint', 'trust'])->default('individual');
$table->unsignedBigInteger('joint_owner_id')->nullable();
$table->foreignId('trust_id')->nullable();
```

**CRITICAL**:
- Use **'individual'** NOT 'sole' in forms
- ISAs MUST be 'individual' only (UK tax rule)
- Joint ownership creates reciprocal records automatically

### 4. Spouse Account Management

- **Auto-Creation**: New email → creates account with random password, sends welcome email
- **Account Linking**: Existing email → links accounts bidirectionally, sets `marital_status = 'married'`
- **Permissions**: Granular view/edit permissions via `spouse_permissions` table

### 5. Polymorphic Holdings System

**Pattern**: Holdings can belong to either InvestmentAccount OR DCPension

**Database**:
```php
$table->morphs('holdable'); // Creates holdable_type and holdable_id
```

**Usage**:
```php
// Investment Account holdings
$holdings = $investmentAccount->holdings;

// DC Pension holdings
$holdings = $dcPension->holdings;
```

This enables shared portfolio optimization services across Investment and Retirement modules.

---

## Critical Vue.js Patterns

### 1. Form Modal Event Naming Bug

**❌ WRONG - Causes double submission:**
```vue
<FormModal @submit="handleSubmit" />
```

**✅ CORRECT - Use 'save' event:**
```vue
<FormModal @save="handleSubmit" @close="closeModal" />
```

### 2. Date Field Formatting

**⚠️ HTML5 date inputs REQUIRE yyyy-MM-DD format**

**ALWAYS add a formatDateForInput() helper to components with date fields:**

```javascript
formatDateForInput(date) {
  if (!date) return '';
  if (typeof date === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(date)) {
    return date;
  }
  const dateObj = new Date(date);
  if (isNaN(dateObj.getTime())) return '';
  const year = dateObj.getFullYear();
  const month = String(dateObj.getMonth() + 1).padStart(2, '0');
  const day = String(dateObj.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}
```

### 3. Currency Formatting

**ALWAYS** use the `formatCurrency()` method:

```javascript
formatCurrency(value) {
  if (value === null || value === undefined) return '£0';
  return new Intl.NumberFormat('en-GB', {
    style: 'currency',
    currency: 'GBP',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value);
}
```

**Usage**:
```vue
<!-- CORRECT -->
<p>Total: {{ formatCurrency(totalValue) }}</p>

<!-- WRONG -->
<p>Total: £{{ totalValue.toLocaleString() }}</p>
```

### 4. Unified Form Pattern for Multiple Entity Types

**Pattern**: When a single action can create multiple types of entities (e.g., DC/DB/State pensions), use a unified form with type selection.

**Implementation**: `UnifiedPensionForm.vue`

```vue
<template>
  <div>
    <!-- Type Selection Modal (shows first) -->
    <div v-if="!selectedType">
      <button @click="selectedType = 'type_a'">Type A</button>
      <button @click="selectedType = 'type_b'">Type B</button>
    </div>

    <!-- Specific Form Components (shown after selection) -->
    <TypeAForm v-if="selectedType === 'type_a'" @close="handleClose" @save="handleSave" />
    <TypeBForm v-if="selectedType === 'type_b'" @close="handleClose" @save="handleSave" />
  </div>
</template>

<script>
export default {
  data() {
    return {
      selectedType: null,
    };
  },
  methods: {
    handleClose() {
      this.selectedType = null;
      this.$emit('close');
    },
    handleSave(data) {
      this.selectedType = null;
      this.$emit('save', data);
    },
  },
};
</script>
```

**Benefits**:
- Single entry point for multiple entity types
- Clear visual selection for users
- Reuses existing individual form components
- Maintains separation of concerns

### 5. British vs. American Spelling

**CRITICAL RULE**: British English for users, American English for code

#### User-Facing Text (Use British Spelling)

- ✅ Headings: `<h1>Portfolio Optimisation</h1>`
- ✅ Button labels: `<button>Customise</button>`
- ✅ Form placeholders: `placeholder="Analyse your data"`

#### Code Syntax (Use American Spelling - DO NOT CHANGE)

- ❌ CSS classes: `class="items-center"` (Tailwind convention)
- ❌ API routes: `/api/retirement/analyze` (Laravel convention)
- ❌ Variable names: `optimizationResult`, `colorScheme`
- ❌ Method names: `analyzePortfolio()`, `optimizeAllocation()`

### 6. Syncing Related Form Data (CRITICAL)

**⚠️ CRITICAL**: When forms have related parent-child data (e.g., property + mortgage), ALWAYS sync the child form data with the parent using watchers.

**Problem Example**: PropertyForm has `form.ownership_type` and `mortgageForm.ownership_type`. User changes property to joint, but mortgage stays individual.

**✅ CORRECT - Use watchers to sync:**

```javascript
watch: {
  // Sync mortgage ownership with property ownership
  'form.ownership_type'(newVal) {
    this.mortgageForm.ownership_type = newVal;
  },

  'form.joint_owner_id'(newVal) {
    this.mortgageForm.joint_owner_id = newVal;
  },

  'form.joint_owner_name'(newVal) {
    this.mortgageForm.joint_owner_name = newVal;
  },
}
```

**Also initialize in populateForm():**

```javascript
// If no existing mortgage, sync ownership from property
if (!this.property.mortgages?.length) {
  this.mortgageForm.ownership_type = this.form.ownership_type || 'individual';
  this.mortgageForm.joint_owner_id = this.form.joint_owner_id || null;
  this.mortgageForm.joint_owner_name = this.form.joint_owner_name || '';
}
```

**Why This Matters**:
- Backend logic (e.g., reciprocal record creation) depends on correct ownership_type
- Prevents silent data inconsistency bugs that are hard to debug
- Ensures frontend and backend state remain synchronized

**Real-World Impact**: This pattern fixed the critical joint mortgage bug where only one mortgage was created instead of two.

---

## Coding Standards

### PHP (PSR-12 Compliant)

**Naming**:
- Classes: `PascalCase` (e.g., `ProtectionAgent`)
- Methods/Properties: `camelCase` (e.g., `calculateGap()`)
- Database: `snake_case` (e.g., `user_id`, `sum_assured`)

**Key Rules**:
- Always use `declare(strict_types=1);`
- 4 spaces indentation (no tabs)
- Type hints for parameters and return types
- Visibility declared for all properties/methods

### MySQL Standards

**Naming**:
- Tables: `snake_case` (e.g., `life_insurance_policies`)
- Columns: `snake_case` (e.g., `user_id`, `sum_assured`)
- Primary keys: `id` (BIGINT UNSIGNED AUTO_INCREMENT)
- Foreign keys: `{table}_id` (e.g., `user_id`)

**Data Types**:
- IDs: `BIGINT UNSIGNED`
- Currency: `DECIMAL(15,2)` or `DOUBLE` for computed values
- Percentages/rates: `DECIMAL(5,4)`
- Dates: `DATE`, `TIMESTAMP` for timestamps

### Vue.js 3 Standards

**Component Naming**:
- Multi-word names (e.g., `AssetForm.vue`, `IHTPlanning.vue`)
- PascalCase in SFC, kebab-case in templates

**Key Rules**:
- Always use `:key` with `v-for`
- Never use `v-if` with `v-for` on same element
- Use shorthand syntax (`:` for `v-bind`, `@` for `v-on`)

---

## UK Tax Configuration

**Active Tax Year**: 2025/26 (configurable via admin panel)
**Historical Years Available**: 2021/22, 2022/23, 2023/24, 2024/25

**Key Values (2025/26)**:
- **IHT Rate**: 40% on estate above nil rate band
- **NRB**: £325,000 (transferable to spouse)
- **RNRB**: £175,000 (residence nil rate band, transferable)
- **Pension Annual Allowance**: £60,000 (tapered for high earners)
- **ISA Annual Allowance**: £20,000 (April 6 - April 5)
- **Tax Year Period**: April 6 to April 5

---

## Caching Strategy

**Cache TTLs** based on data volatility:
- Tax config: 1 hour
- Monte Carlo results: 24 hours
- Dashboard data: 30 minutes
- Agent analysis: 1 hour

**Pattern**:
```php
Cache::remember("estate_analysis_{$userId}", 3600, function() {
    return $this->service->analyze($userId);
});
```

**Invalidation**: After writes
```php
Cache::forget("estate_analysis_{$userId}");
```

---

## Testing Pattern

### Unit Test (Pest)

```php
use App\Services\Estate\IHTCalculationService;

test('calculates IHT liability correctly for single person', function () {
    $user = User::factory()->create();
    $profile = IHTProfile::factory()->create([
        'user_id' => $user->id,
        'available_nrb' => 325000,
    ]);

    $assets = collect([(object)['current_value' => 500000]]);

    $service = new IHTCalculationService($taxConfig);
    $result = $service->calculateIHTLiability($assets, collect(), $profile);

    // Estate: £500k - NRB: £325k = £175k taxable
    // IHT: £175k × 40% = £70k
    expect($result['iht_liability'])->toBe(70000.0);
});
```

### Mocking TaxConfigService

```php
use App\Services\TaxConfigService;
use Mockery;

test('calculates tax correctly', function () {
    $mockTaxConfig = Mockery::mock(TaxConfigService::class);
    $mockTaxConfig->shouldReceive('getIncomeTax')
        ->andReturn([
            'personal_allowance' => 12570,
            'bands' => [
                ['name' => 'Basic Rate', 'threshold' => 0, 'rate' => 0.20],
            ],
        ]);

    $service = new MyCalculatorService($mockTaxConfig);
    $result = $service->calculateTax(50000);

    expect($result['personal_allowance'])->toBe(12570.0);
});
```

---

## Module Structure

Each module follows consistent pattern:

**Backend**:
```
app/
├── Agents/[Module]Agent.php         # Business logic orchestrator
├── Services/[Module]/               # Domain-specific calculations
├── Http/Controllers/Api/[Module]Controller.php
├── Http/Requests/[Module]/          # Form validation
└── Models/[Module]/                 # Eloquent models
```

**Frontend**:
```
resources/js/
├── views/[Module]/[Module]Dashboard.vue
├── components/[Module]/             # Module-specific components
├── services/[module]Service.js      # API wrapper
└── store/modules/[module].js        # Vuex store
```

---

## Key Development Principles

1. **UK-Specific**: Follow UK tax rules, tax year April 6 - April 5
2. **Agent Pattern**: Encapsulate module logic in Agent classes (except Estate)
3. **Centralized Config**: Never hardcode tax rates; use TaxConfigService
4. **Data Isolation**: Users only access their own data; always filter by `user_id`
5. **Caching**: Cache expensive calculations with appropriate TTLs
6. **Testing**: Write Pest tests for all financial calculations

---

## Previously Known Issues - NOW RESOLVED

### ✅ RESOLVED - Joint Mortgage Reciprocal Creation (Fixed November 15, 2025)

**Issue**: When creating a joint property with a mortgage, only ONE mortgage record was being created instead of TWO.

**Root Cause**: PropertyForm's `mortgageForm` kept default `ownership_type: 'individual'` even when property was joint. MortgageController checked ownership_type to trigger reciprocal creation but it was always 'individual'.

**Solution**: Added watchers in PropertyForm.vue to sync mortgage ownership with property ownership:
- Watch `form.ownership_type` → Update `mortgageForm.ownership_type`
- Watch `form.joint_owner_id` → Update `mortgageForm.joint_owner_id`
- Watch `form.joint_owner_name` → Update `mortgageForm.joint_owner_name`

**Status**: ✅ **FIXED** - Both property and mortgage reciprocal records now created correctly

**Documentation**: See `DEPLOYMENT_PATCH_v0.2.8.md` Section 14.10

**Key Lesson**: When forms have related data (parent-child), always sync the data using watchers to prevent inconsistent submissions.

### ✅ RESOLVED - Estate Plan Spouse Data & IHT Liability Display (Fixed November 15, 2025)

**Issues**:
1. Comprehensive Estate Plan (Plans module) not showing spouse assets/liabilities even when data sharing enabled
2. IHT Planning tab not displaying non-mortgage liabilities (credit cards, loans, etc.)

**Root Causes**:
1. `ComprehensiveEstatePlanService` only gathered user assets, didn't pass spouse data to build methods
2. `IHTController.formatLiabilitiesBreakdown()` used wrong field names (`amount` instead of `current_balance`, `description` instead of `liability_name`)

**Solutions**:
1. Updated `ComprehensiveEstatePlanService`:
   - Added spouse asset gathering when data sharing enabled
   - Enhanced `buildBalanceSheet()`, `buildEstateOverview()`, `buildEstateBreakdown()` to accept and display spouse data
   - Returns structured data with `user`, `spouse`, and `combined` sections

2. Updated `IHTController.formatLiabilitiesBreakdown()`:
   - Changed `$liability->amount` to `$liability->current_balance`
   - Changed `$liability->description` to `$liability->liability_name`
   - Applied fixes to both user and spouse liability sections

**Status**: ✅ **FIXED** - Estate Plan shows combined spouse data, all liabilities display correctly

**Documentation**: See `DEPLOYMENT_PATCH_v0.2.9.md`

**Key Lesson**: Always verify model field names match database schema. The Liability model uses `current_balance` and `liability_name`, not `amount` and `description`.

### ✅ RESOLVED - All Liability Display Issues (Confirmed November 17, 2025)

**Comprehensive Resolution Summary**:

All liability-related display and categorization issues have been fully resolved across all modules:

1. **Net Worth Card Liability Display** ✅
   - Fixed: Only mortgages showing, missing other liability types
   - Solution: Replaced deprecated `PersonalAccount` model with `Liability` model
   - Result: Complete liability breakdown with all 9 types visible

2. **IHT Planning Liability Display** ✅
   - Fixed: Non-mortgage liabilities not displaying
   - Solution: Corrected field names in `formatLiabilitiesBreakdown()`
   - Result: All liabilities (mortgages, credit cards, loans, hire purchase, etc.) display correctly

3. **Expanded Liability Types** ✅
   - Enhancement: Expanded from 4 types to 9 types
   - New types: Secured loan, unsecured loan, personal loan, car loan, hire purchase, overdraft
   - Migration: `2025_11_15_170630_update_liability_type_enum_to_support_all_types.php`
   - Result: More accurate debt categorization and reporting

4. **User Profile Liabilities** ✅
   - Fixed: Interest rates displaying as 2700.00% instead of 27.00%
   - Fixed: Balance sheet showing categories instead of individual line items
   - Result: Accurate display with correct formatting

**Current Status**:
- ✅ All 9 liability types fully supported
- ✅ Correct display across all modules (Net Worth, Estate Planning, User Profile)
- ✅ Proper categorization and reporting
- ✅ No known liability-related issues remaining

**Files Involved**:
- `app/Services/NetWorth/NetWorthService.php`
- `app/Http/Controllers/Api/Estate/IHTController.php`
- `app/Services/UserProfile/PersonalAccountsService.php`
- `database/migrations/2025_11_15_170630_update_liability_type_enum_to_support_all_types.php`

**Documentation**: See `DEPLOYMENT_PATCH_v0.2.9.md` for complete details

---

For any bugs encountered, please use the `systematic-debugging` skill to investigate before implementing fixes.

---

## Demo Credentials

- **User**: `demo@fps.com` / `password`
- **Admin**: `admin@fps.com` / `admin123`

---

**Current Version**: v0.2.9 (Beta - Production Ready)
**Last Updated**: November 17, 2025
**Status**: 🚀 Active Development - All Core Features Complete

---

🤖 **Built with [Claude Code](https://claude.com/claude-code)**
