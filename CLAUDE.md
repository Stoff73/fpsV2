# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

---

## ⚠️ CRITICAL INSTRUCTIONS

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
- **Admin Account**: `admin@fps.com` / `admin123456` (ID: 1016)

**Commands to AVOID Without Backup:**
- ❌ `php artisan migrate:fresh`, `migrate:refresh`, `db:wipe`
- ❌ `php artisan migrate:rollback` (except single recent migration)

**Safe Commands:**
- ✅ `php artisan migrate` (forward only)
- ✅ Admin panel backup/restore system

### 3. USE AVAILABLE SKILLS

**BEFORE STARTING ANY TASK, CHECK IF THERE IS A RELEVANT SKILL AVAILABLE.**

Available Skills:
1. **systematic-debugging** - For ALL bug reports, unexpected behavior, troubleshooting
2. **fps-module-builder** - For creating new modules (full-stack)
3. **fps-feature-builder** - For adding/extending features in existing modules
4. **fps-component-builder** - For creating Vue 3 components

**FAILURE TO USE AVAILABLE SKILLS IS UNACCEPTABLE.**

### 4. UNIFIED FORM COMPONENTS - CRITICAL RULE

**⚠️ THE APPLICATION USES ONE FORM FOR ALL INPUTS ACROSS ALL AREAS**

**CRITICAL**: All data input forms MUST be reusable across the entire application:
- ✅ The SAME form component is used whether adding data during onboarding, from the module dashboard, or editing existing data
- ✅ Forms are located in module-specific component folders (e.g., `components/Protection/PolicyFormModal.vue`, `components/Estate/LiabilityForm.vue`)
- ✅ Forms accept a data prop (e.g., `:policy`, `:property`, `:account`) and a mode/isEditing prop
- ✅ Forms emit `@save` and `@close` events (NEVER use `@submit` which causes double submission)

**Examples of Unified Forms:**
- `Protection/PolicyFormModal.vue` - Used in onboarding, protection dashboard, and policy editing
- `NetWorth/Property/PropertyForm.vue` - Used in onboarding, net worth module, and estate planning
- `Investment/AccountForm.vue` - Used in onboarding and investment dashboard
- `Savings/SaveAccountModal.vue` - Used in onboarding and savings dashboard
- `Estate/LiabilityForm.vue` - Used in onboarding and estate planning

**Pattern to Follow:**
```vue
<!-- Parent Component (Onboarding Step or Dashboard) -->
<template>
  <button @click="showForm = true">Add Item</button>

  <ItemForm
    v-if="showForm"
    :item="editingItem"
    :is-editing="!!editingItem"
    @save="handleItemSaved"
    @close="closeForm"
  />
</template>

<script>
async function handleItemSaved(data) {
  if (editingItem.value) {
    await service.updateItem(editingItem.value.id, data);
  } else {
    await service.createItem(data);
  }
  closeForm();
  await loadItems(); // Refresh list
}
</script>
```

**DO NOT:**
- ❌ Create separate forms for onboarding vs. dashboard
- ❌ Duplicate form logic across components
- ❌ Use `@submit` event name (causes double submission bug)
- ❌ Create inline forms without extracting to reusable components

**VIOLATION OF THIS RULE REQUIRES IMMEDIATE REFACTORING.**

### 5. ENVIRONMENT VARIABLE CONTAMINATION - CRITICAL WARNING

**⚠️ THE #1 CAUSE OF DEVELOPMENT ENVIRONMENT FAILURES IS ENVIRONMENT VARIABLE POLLUTION**

**NEVER** export production environment variables in development sessions:
- ❌ **NEVER** run: `export $(cat .env.production | xargs)`
- ❌ **NEVER** run: `source .env.production`
- ❌ **NEVER** manually export production values like `export APP_URL=https://csjones.co/tengo`

**WHY THIS BREAKS EVERYTHING:**
- Environment variables **override** `.env` file values in Laravel/Vite
- Production URLs get compiled into JavaScript by Vite
- Production database credentials cause "Access denied" errors
- Frontend calls production API instead of localhost (CORS errors)
- Cache driver mismatches cause "This cache store does not support tagging" errors

**DIAGNOSIS - Check for contamination FIRST when debugging:**
```bash
# Check for production environment variables
printenv | grep -E "^APP_|^DB_|^VITE_|^CACHE_"

# If you see production values (csjones.co, production DB names), you have contamination
```

**SOLUTION - Always start development servers with correct environment:**

**Recommended: Use the startup script:**
```bash
./dev.sh
```
This script automatically:
- Kills existing server processes
- Exports correct local environment variables
- Clears Laravel and Vite caches
- Verifies MySQL connection and database existence
- Starts both Laravel and Vite in correct sequence
- Displays process IDs and helpful information

**Manual alternative** (if needed):
```bash
export APP_ENV=local && \
export APP_URL=http://localhost:8000 && \
export VITE_API_BASE_URL=http://localhost:8000 && \
export DB_CONNECTION=mysql && \
export DB_HOST=localhost && \
export DB_DATABASE=laravel && \
export DB_USERNAME=root && \
export DB_PASSWORD="" && \
export CACHE_DRIVER=array && \
php artisan serve &
sleep 2
npm run dev
```

**CRITICAL**: Both Laravel AND Vite MUST start in the SAME bash session with these exports.

**COMMON SYMPTOMS:**
1. CORS errors: `Access to XMLHttpRequest at 'https://csjones.co/tengo/api/...' from origin 'http://localhost:8000' has been blocked`
2. Database errors: `Access denied for user 'uixybijdvk3yv'@'localhost'` (production DB user)
3. Cache errors: `This cache store does not support tagging`
4. Vite output shows wrong URL: `APP_URL: https://csjones.co/tengo` (should be `http://localhost:8000`)
5. 500 errors on API calls with database or cache issues in Laravel logs

**REFERENCE:**
- See `DEV_ENVIRONMENT_TROUBLESHOOTING.md` for comprehensive debugging guide
- See `.env` for local development configuration
- See `.env.production.example` for production template (NEVER rename until deployment)

**PREVENTION:**
- Keep production config in `.env.production.example` only
- Never work in same terminal session used for deployment preparation
- Always use `./dev.sh` script for local development
- Clear terminal session before deployment: open fresh terminal, don't reuse dev terminal

---

## Project Overview

**TenGo** - UK-focused comprehensive financial planning application covering five integrated modules: Protection, Savings, Investment, Retirement, and Estate Planning.

**Current Version**: v0.2.1
**Tech Stack**: Laravel 10.x (PHP 8.2+) + Vue.js 3 + MySQL 8.0+ + Memcached
**Status**: Active Development - Core modules complete with advanced portfolio optimization

---

## Architecture Overview

### Agent-Based System

Each module has an **Agent** that orchestrates analysis:
- **BaseAgent**: Abstract class with common utilities (analyze, generateRecommendations, buildScenarios)
- **ProtectionAgent**: Life/CI/IP coverage analysis
- **SavingsAgent**: Emergency fund & ISA tracking
- **InvestmentAgent**: Portfolio analysis & Monte Carlo simulations
- **RetirementAgent**: Pension projections & readiness scoring
- **EstateAgent**: IHT calculations & net worth tracking
- **CoordinatingAgent**: Cross-module holistic planning

### Three-Tier Architecture

```
┌─────────────────────────────────────┐
│ Vue.js 3 Frontend (150+ components) │
│ Vuex stores + ApexCharts + Tailwind │
└────────────────┬────────────────────┘
                 │ REST API (Sanctum auth)
                 ↓
┌─────────────────────────────────────┐
│ Laravel 10.x Application Layer      │
│ 7 Agents + 50+ Services + 39 Models │
└────────────────┬────────────────────┘
                 │ Eloquent ORM
                 ↓
┌─────────────────────────────────────┐
│ MySQL 8.0+ (40+ tables)             │
│ Memcached (expensive calculations)  │
└─────────────────────────────────────┘
```

### Request Flow Pattern

```
Vue Component → JS Service → API Call → Controller → Agent → Services → Models → Database
                                                        ↓
Response ← Store Mutation ← Component ← JSON ← Controller ← Calculation Results
```

---

## Essential Development Commands

### First-Time Setup

```bash
composer install && npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=TaxConfigurationSeeder
```

### Running Development Servers

**⚠️ CRITICAL**: You must run **BOTH** servers simultaneously.

**Recommended:**
```bash
./dev.sh
```

**Manual (3 separate terminals):**
```bash
# Terminal 1 - Laravel Backend (REQUIRED)
php artisan serve

# Terminal 2 - Vite Frontend (REQUIRED)
npm run dev

# Terminal 3 - Queue Worker (Optional, for Monte Carlo)
php artisan queue:work database
```

**Why both servers?**
- Laravel (port 8000): Serves backend API and pages
- Vite (port 5173): Serves frontend assets with HMR
- Without Laravel: "unable to reach" errors
- Without Vite: Frontend assets won't load

### Testing

```bash
# Run all tests
./vendor/bin/pest

# Run specific test suite
./vendor/bin/pest --testsuite=Unit
./vendor/bin/pest --testsuite=Feature

# Run specific test file
./vendor/bin/pest tests/Unit/Services/Protection/AdequacyScorerTest.php

# Run in parallel (faster)
./vendor/bin/pest --parallel
```

### Code Formatting

```bash
# Run Laravel Pint (PSR-12 formatter)
./vendor/bin/pint

# Check without fixing
./vendor/bin/pint --test
```

---

## Key Implementation Patterns

### 1. Centralized Tax Configuration (DATABASE-DRIVEN)

**CRITICAL**: All UK tax values are stored in `tax_configurations` table, NOT hardcoded.

**Location**: `app/Services/TaxConfigService.php`

**Usage Pattern**:
```php
use App\Services\TaxConfigService;

class MyService
{
    public function __construct(private TaxConfigService $taxConfig) {}

    public function calculate()
    {
        // Get specific values
        $personalAllowance = $this->taxConfig->getIncomeTax()['personal_allowance'];
        $isaAllowance = $this->taxConfig->getISAAllowances()['annual_allowance'];

        // Get entire sections
        $incomeTax = $this->taxConfig->getIncomeTax();
        $iht = $this->taxConfig->getInheritanceTax();
        $pension = $this->taxConfig->getPensionAllowances();
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
- ❌ Access database directly for tax values

**DO**:
- ✅ Inject TaxConfigService via constructor
- ✅ Mock TaxConfigService in unit tests
- ✅ Use specific getter methods

**Admin Panel**: Tax Settings page allows switching between tax years (2021/22 - 2025/26)

### 2. ISA Allowance Tracking (Cross-Module)

**Total Allowance**: £20,000 per tax year (April 6 - April 5)

**Implementation**: `app/Services/Savings/ISATracker.php`
- Aggregates Cash ISAs from Savings module
- Aggregates S&S ISAs from Investment module
- Warns when approaching or exceeding limit

**Usage**:
```php
$isaUsage = $isaTracker->calculateAllowanceUsage($userId, '2025/26');
// Returns: ['used' => 15000, 'remaining' => 5000, 'limit' => 20000, 'warning' => false]
```

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

**Auto-Creation**: New email → creates account with random password, sends welcome email
**Account Linking**: Existing email → links accounts bidirectionally, sets `marital_status = 'married'`
**Permissions**: Granular view/edit permissions via `spouse_permissions` table

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

**Why**: The `@submit` event conflicts with native form submission, triggering twice. Always use `@save` for custom form modals.

### 2. Date Field Formatting - CRITICAL

**⚠️ HTML5 date inputs REQUIRE yyyy-MM-DD format, but Laravel returns ISO 8601 dates**

**ALWAYS add a formatDateForInput() helper to components with date fields:**

```javascript
formatDateForInput(date) {
  if (!date) return '';
  try {
    // If it's already in YYYY-MM-DD format, return it
    if (typeof date === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(date)) {
      return date;
    }
    // Parse and format the date
    const dateObj = new Date(date);
    if (isNaN(dateObj.getTime())) return '';
    const year = dateObj.getFullYear();
    const month = String(dateObj.getMonth() + 1).padStart(2, '0');
    const day = String(dateObj.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  } catch (e) {
    return '';
  }
}
```

**Apply when loading data:**
```javascript
// When populating form from database
this.formData.start_date = this.formatDateForInput(policy.start_date);
```

**Files requiring date formatting:**
- All forms with `type="date"` inputs
- Onboarding steps loading existing data
- Edit modals for policies, properties, assets, etc.

**Components already fixed:**
- PolicyFormModal.vue
- AssetForm.vue
- GiftForm.vue
- TrustForm.vue
- StatePensionForm.vue
- WillInfoStep.vue
- PersonalInfoStep.vue
- FamilyMemberFormModal.vue
- PropertyForm.vue
- DomicileInformationStep.vue

---

## Coding Standards

### PHP (PSR-12 Compliant)

**Naming**:
- Classes: `PascalCase` (e.g., `ProtectionAgent`, `IHTCalculator`)
- Methods/Properties: `camelCase` (e.g., `calculateGap()`, `$annualIncome`)
- Database: `snake_case` (e.g., `user_id`, `sum_assured`)

**Key Rules**:
- Always use `declare(strict_types=1);`
- 4 spaces indentation (no tabs)
- Type hints for parameters and return types
- Visibility declared for all properties/methods

### MySQL Standards

**Naming**:
- Tables: `snake_case` (e.g., `life_insurance_policies`, `dc_pensions`)
- Columns: `snake_case` (e.g., `user_id`, `sum_assured`)
- Primary keys: `id` (BIGINT UNSIGNED AUTO_INCREMENT)
- Foreign keys: `{table}_id` (e.g., `user_id`, `investment_account_id`)

**Data Types**:
- IDs: `BIGINT UNSIGNED`
- Currency: `DECIMAL(15,2)`
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

**Currency Formatting**:
- **ALWAYS** use the `formatCurrency()` method for displaying currency values
- **NEVER** use `.toLocaleString()` or manual string concatenation
- Standard implementation:

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

<!-- WRONG - DO NOT USE -->
<p>Total: £{{ totalValue.toLocaleString() }}</p>
```

**Benefits**:
- Consistent formatting across the entire application
- Proper handling of null/undefined values
- UK-specific currency format (£ symbol, comma separators)
- No decimal places for whole pound amounts

---

## UK Tax Configuration

**Active Tax Year**: 2025/26 (configurable via admin panel)
**Historical Years Available**: 2021/22, 2022/23, 2023/24, 2024/25

**Key Values (2025/26 Example)**:
- **IHT Rate**: 40% on estate above nil rate band
- **NRB**: £325,000 (transferable to spouse)
- **RNRB**: £175,000 (residence nil rate band, transferable)
- **Pension Annual Allowance**: £60,000 (tapered for high earners)
- **ISA Annual Allowance**: £20,000 (April 6 - April 5)
- **Tax Year Period**: April 6 to April 5

**Admin Panel Workflow** (Annual Tax Update):
1. Before April 6: Duplicate current year, update all tax values
2. On April 6: Activate new tax year via admin panel
3. System automatically deactivates previous year
4. All calculations now use new tax year values

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
    return $this->agent->analyze($userId);
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
use App\Services\Estate\IHTCalculator;

test('calculates IHT liability correctly for single person', function () {
    $user = User::factory()->create();
    $profile = IHTProfile::factory()->create([
        'user_id' => $user->id,
        'available_nrb' => 325000,
    ]);

    $assets = collect([(object)['current_value' => 500000]]);

    $calculator = new IHTCalculator();
    $result = $calculator->calculateIHTLiability($assets, $profile);

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
                ['name' => 'Higher Rate', 'threshold' => 37700, 'rate' => 0.40],
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

**Tab Structure** (Dashboard):
1. Current Situation - Data entry forms
2. Analysis - Charts, calculations, metrics
3. Recommendations - Generated advice from agent
4. What-If Scenarios - Scenario modeling
5. Details - Additional information

---

## Key Development Principles

1. **UK-Specific**: Follow UK tax rules, tax year April 6 - April 5
2. **Agent Pattern**: Encapsulate module logic in Agent classes
3. **Centralized Config**: Never hardcode tax rates; use TaxConfigService
4. **Data Isolation**: Users only access their own data; always filter by `user_id`
5. **Caching**: Cache expensive calculations with appropriate TTLs
6. **Testing**: Write Pest tests for all financial calculations

---

## Recent Fixes (November 2025)

### Expenditure Data Type Fix ✅

**Issue**: Expenditure totals displaying as "£NaN" due to incorrect data type handling

**Resolution**:
- Changed database columns from `DECIMAL(15,2)` to `DOUBLE` for all income and expenditure fields
- Updated Eloquent model casts from `'decimal:2'` to `'float'` in User model
- Migration: `2025_11_09_133324_change_expenditure_columns_to_double.php`
- **Impact**: All monetary values now serialize as numbers instead of strings, preventing NaN issues

### User Profile UI Improvements ✅

**Edit Button Repositioning**:
- Moved "Edit Information" button from bottom to top right in all User Profile tabs
- Affected components:
  - `PersonalInformation.vue`
  - `IncomeOccupation.vue`
  - `DomicileInformation.vue`
  - `HealthInformation.vue` (already had button at top)
- **Benefit**: Improved UX with consistent button placement

### British Spelling Conversion ✅

**Scope**: Converted all user-facing text from American to British English spelling

**Changes Applied**:
- `-ize` → `-ise` (organise, analyse, optimise, realise, recognise, customise)
- `-or` → `-our` (colour, behaviour, favour, neighbour)
- `-er` → `-re` (centre, fibre, theatre)
- Additional: travelling, cancelled, defence, offence
- **Files Modified**: 252 Vue and JavaScript files across entire frontend

**Technical Notes**:
- Component names and file paths preserved as American spelling (code convention)
- JavaScript API parameters unchanged (e.g., `behavior: 'smooth'`)
- CSS class names unchanged (e.g., `items-center`)

## Known Issues

### Protection Policies Onboarding (⚠️ IN PROGRESS)

**Issue**: Protection policy form in onboarding saves successfully to database but policies don't display in the list after save.

**Status**: Under investigation - API response structure mismatch identified

**Details**:
- Form submission works (console shows "Policy saved successfully")
- API call completes without errors
- Policy is created in database
- However, loadPolicies() returns empty array after save
- Root cause: Response structure from `protectionService.getProtectionData()` returns:
  ```javascript
  {
    data: {
      profile: {...},
      policies: {
        life: [...],
        criticalIllness: [...]
      }
    }
  }
  ```
- Code has been updated to parse this structure but requires testing

**Files Modified**:
- `resources/js/components/Onboarding/steps/ProtectionPoliciesStep.vue` - Updated data parsing logic
- `resources/js/components/Protection/PolicyFormModal.vue` - Added date formatting, fixed isEditing prop

**Next Steps**:
1. Verify the exact response structure from the API
2. Confirm policies array is populated after creation
3. Test display rendering after successful save

---

## Demo Credentials

- **User**: `demo@fps.com` / `password`
- **Admin**: `admin@fps.com` / `admin123456`

---

**Current Version**: v0.2.1 (Beta)
**Last Updated**: November 9, 2025
**Status**: 🚀 Active Development - Core Modules Complete

---

🤖 **Built with [Claude Code](https://claude.com/claude-code)**
