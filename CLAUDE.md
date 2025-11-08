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
2. **fps-module-builder** - For creating new FPS modules (full-stack)
3. **fps-feature-builder** - For adding/extending features in existing modules
4. **fps-component-builder** - For creating Vue 3 components

**FAILURE TO USE AVAILABLE SKILLS IS UNACCEPTABLE.**

### 4. ENVIRONMENT VARIABLE CONTAMINATION - CRITICAL WARNING

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

**TenGo (Financial Planning System)** - UK-focused comprehensive financial planning application covering five integrated modules: Protection, Savings, Investment, Retirement, and Estate Planning.

**Current Version**: v0.2.1
**Tech Stack**: Laravel 10.x (PHP 8.2+) + Vue.js 3 + MySQL 8.0+ + Memcached

**Status**: Active Development - Core modules complete with advanced portfolio optimization

**Recent Updates (v0.2.1)**:
- Investment & Savings Plans module with consolidated goal tracking and risk metrics
- DC Pension Portfolio Optimization - full integration with Investment module tools
- Polymorphic Holdings System supporting both Investment Accounts and DC Pensions
- Advanced Risk Metrics: Alpha, Beta, Sharpe Ratio, Volatility, Max Drawdown, VaR (95%)
- Portfolio Analysis with asset allocation, diversification scoring, and fee optimization
- Holdings Management for DC pensions with full CRUD functionality
- Service Reuse Architecture - shared portfolio services across Investment and Retirement modules

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

## Development Commands

### Essential Setup

```bash
# First-time setup
composer install && npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=TaxConfigurationSeeder
```

### Development Servers

**⚠️ CRITICAL**: You must run **BOTH** servers simultaneously.

**Recommended Method:**
```bash
./dev.sh
```

**Manual Method (3 separate terminals):**
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
./vendor/bin/pest --testsuite=Architecture

# Run specific test file
./vendor/bin/pest tests/Unit/Services/Protection/AdequacyScorerTest.php

# Run with coverage
./vendor/bin/pest --coverage

# Run in parallel (faster)
./vendor/bin/pest --parallel
```

### Database Operations

```bash
# Safe forward migrations
php artisan migrate

# Seed database
php artisan db:seed --class=TaxConfigurationSeeder
php artisan db:seed --class=DemoUserSeeder

# DANGEROUS - Require backup approval
php artisan migrate:rollback      # Rollback last migration
php artisan migrate:fresh         # Drop all tables and re-migrate
```

### Cache Management

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Code Quality

```bash
# Run Laravel Pint (code formatter - PSR-12)
./vendor/bin/pint

# Check code style without fixing
./vendor/bin/pint --test
```

---

## Module Overview

### Protection Module
- **Purpose**: Life/critical illness/income protection analysis
- **Agent**: ProtectionAgent
- **Key Services**: CoverageGapAnalyzer, AdequacyScorer, RecommendationEngine
- **Models**: ProtectionProfile, LifeInsurancePolicy, CriticalIllnessPolicy, IncomeProtectionPolicy
- **Key Concept**: Uses NET income for human capital calculation; distinguishes earned income vs. continuing income

### Savings Module
- **Purpose**: Emergency fund tracking, savings goals, ISA allowance monitoring
- **Agent**: SavingsAgent
- **Key Services**: EmergencyFundCalculator, ISATracker, GoalProgressCalculator
- **Models**: SavingsAccount, SavingsGoal, ISAAllowanceTracking
- **Key Concept**: Runway = Emergency fund ÷ monthly expenditure; cross-module ISA tracking

### Investment Module
- **Purpose**: Portfolio analysis, asset allocation, Monte Carlo projections
- **Agent**: InvestmentAgent
- **Key Services**: PortfolioAnalyzer, MonteCarloSimulator, AssetAllocationOptimizer
- **Models**: InvestmentAccount, Holding, InvestmentGoal, RiskProfile
- **Key Concept**: Monte Carlo simulations run as background jobs (1,000 iterations)

### Retirement Module
- **Purpose**: Pension planning, contribution optimization, retirement readiness
- **Agent**: RetirementAgent
- **Key Services**: PensionProjector, AnnualAllowanceChecker, ContributionOptimizer
- **Models**: DCPension, DBPension, StatePension, RetirementProfile
- **Key Concept**: £60k annual allowance with 3-year carry forward rules

### Estate Planning Module
- **Purpose**: IHT calculation, net worth tracking, gifting strategy, second death planning
- **Agent**: EstateAgent
- **Key Services**: IHTCalculator, NetWorthAnalyzer, GiftingStrategy, SecondDeathIHTCalculator
- **Models**: Asset, Liability, Gift, Trust, IHTProfile, Will, Bequest
- **Key Concept**: Most complex module; aggregates assets from all modules; supports married couple planning

---

## Key Implementation Details

### Centralized Tax Configuration

**Database-Driven System**: All UK tax values are stored in the `tax_configurations` table and managed via the admin panel.

**TaxConfigService**: Centralized service for retrieving active tax configuration.

**Location**:
- **Service**: `app/Services/TaxConfigService.php`
- **Database**: `tax_configurations` table (seeded with 5 years: 2021/22 - 2025/26)
- **Admin Panel**: Tax Settings page (admin-only access)

**Active Tax Year**: 2025/26 (configurable via admin panel)

**Usage in Services**:
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

**CRITICAL**: Never hardcode tax rates - always use TaxConfigService

**Historical Tax Years**: Previous years (2021/22 - 2024/25) available for calculations. Switch active year via admin panel.

### ISA Allowance Tracking (Cross-Module)

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

### Asset Ownership Patterns

**Ownership Types**: Individual, Joint, Trust

**Database Pattern**:
```php
$table->enum('ownership_type', ['individual', 'joint', 'trust'])->default('individual');
$table->unsignedBigInteger('joint_owner_id')->nullable();
$table->foreignId('trust_id')->nullable();
```

**CRITICAL**:
- Use 'individual' NOT 'sole' in forms
- ISAs MUST be 'individual' only (UK tax rule)
- Joint ownership creates reciprocal records automatically

### Spouse Account Management

**Auto-Creation**: New email → creates account with random password, sends welcome email
**Account Linking**: Existing email → links accounts bidirectionally, sets `marital_status = 'married'`
**Permissions**: Granular view/edit permissions via `spouse_permissions` table

### Caching Strategy

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

### Admin Panel: Tax Configuration Management

**Access**: Admin users only (`is_admin = true`)

**Location**: Admin Panel → Tax Settings

**Features**:
1. **View All Tax Years**: See all configured tax years (2021/22 - 2025/26)
2. **Active Tax Year**: Only one tax year can be active at a time
3. **Switch Active Year**: Activate a different tax year for calculations
4. **Edit Tax Values**: Update tax rates, allowances, bands, thresholds
5. **Duplicate Tax Year**: Copy existing year as template for new year
6. **Historical Reference**: View previous years' tax configurations

**Annual Tax Update Workflow**:

1. **Before April 6** (start of new tax year):
   ```
   1. Login as admin (admin@fps.com / admin123456)
   2. Navigate to Tax Settings page
   3. Click "Duplicate" on current year (e.g., 2025/26)
   4. Enter new tax year (e.g., 2026/27)
   5. Update all tax values for new year:
      - Income tax bands and personal allowance
      - National Insurance thresholds
      - ISA allowances
      - Pension annual allowance
      - IHT: NRB, RNRB, rates
      - SDLT bands
      - CGT rates and exemptions
      - Dividend tax allowances
   6. Click "Save"
   ```

2. **On April 6** (activation day):
   ```
   1. Navigate to Tax Settings
   2. Find new tax year (2026/27)
   3. Click "Activate"
   4. Confirm activation
   5. System automatically deactivates previous year
   6. All calculations now use new tax year values
   ```

**Data Validation**:
- Tax year format: `YYYY/YY` (e.g., 2025/26)
- All required fields must be filled
- Income tax must have 3+ bands
- Rates must be 0-100%
- Thresholds must be in ascending order
- Cannot delete active tax year
- Only one tax year can be active

**Database Seeding**:
```bash
# Seed 5 years of UK tax configurations (2021/22 - 2025/26)
php artisan db:seed --class=TaxConfigurationSeeder
```

**API Endpoints** (Admin only):
- `GET /api/tax-settings/all` - List all tax years
- `GET /api/tax-settings/current` - Get active tax year
- `POST /api/tax-settings/create` - Create new tax year
- `PUT /api/tax-settings/{id}` - Update tax year
- `POST /api/tax-settings/{id}/activate` - Activate tax year
- `POST /api/tax-settings/{id}/duplicate` - Duplicate tax year
- `DELETE /api/tax-settings/{id}` - Delete inactive tax year

---

## File Structure & Key Locations

### Backend Structure

```
app/
├── Agents/                    # Module analysis orchestrators (7 files)
│   ├── BaseAgent.php
│   ├── ProtectionAgent.php, SavingsAgent.php, InvestmentAgent.php
│   ├── RetirementAgent.php, EstateAgent.php
│   └── CoordinatingAgent.php
│
├── Http/Controllers/Api/      # RESTful API controllers (25+ files)
│   ├── ProtectionController.php, SavingsController.php, etc.
│   └── Estate/                # Estate sub-controllers (IHT, Gifting, Trust, Will)
│
├── Services/                  # Business logic (50+ files)
│   ├── Estate/                # 20+ estate services
│   ├── Protection/            # 5 protection services
│   ├── Savings/               # 5 savings services (includes ISATracker)
│   ├── Investment/            # 5 investment services
│   ├── Retirement/            # 5 retirement services
│   ├── Coordination/          # Cross-module services
│   └── UKTaxCalculator.php    # Shared tax calculator
│
├── Models/                    # Eloquent models (39 files)
│   ├── User.php, FamilyMember.php
│   ├── Estate/                # Estate models (Asset, Liability, Gift, Trust, etc.)
│   └── ... (other models)
│
└── Http/Requests/             # Form validation (30+ files)

config/
└── uk_tax_config.php          # DEPRECATED: Tax rules now in tax_configurations table

database/
├── migrations/                # Database migrations
│   └── *_create_tax_configurations_table.php
└── seeders/
    └── TaxConfigurationSeeder.php  # Seeds 5 years of UK tax data (2021/22-2025/26)

routes/
└── api.php                    # All API routes (420+ lines)
```

### Frontend Structure

```
resources/js/
├── components/                # Vue components (150+ files)
│   ├── Estate/                # 45+ estate components
│   ├── Protection/            # 20+ protection components
│   ├── Savings/               # 15+ savings components
│   ├── Investment/            # 20+ investment components
│   ├── Retirement/            # 15+ retirement components
│   └── NetWorth/              # 12+ net worth components
│
├── views/                     # Page-level components (25 files)
│   ├── Dashboard.vue
│   └── [Module]/[Module]Dashboard.vue
│
├── store/modules/             # Vuex stores (16 files)
│   ├── auth.js, estate.js, protection.js, etc.
│
├── services/                  # API wrappers (17 files)
│   ├── api.js                 # Axios instance
│   └── [module]Service.js
│
└── router/index.js            # Vue Router config
```

---

## Coding Standards

### PHP (PSR-12 Compliant)

**Naming Conventions**:
- Classes: `PascalCase` (e.g., `ProtectionAgent`, `IHTCalculator`)
- Methods/Properties: `camelCase` (e.g., `calculateGap()`, `$annualIncome`)
- Database: `snake_case` (e.g., `user_id`, `sum_assured`)

**File Structure**:
```php
<?php

declare(strict_types=1);

namespace App\Agents;

use App\Services\TaxCalculator;

class ProtectionAgent extends BaseAgent
{
    public function calculateGap(float $income, int $age): array
    {
        $capital = $this->calculateHumanCapital($income, $age);

        return [
            'gap' => $capital - $existingCoverage,
            'adequacy_score' => min(100, ($existingCoverage / $capital) * 100),
        ];
    }
}
```

**Key Rules**:
- Always use `declare(strict_types=1);`
- 4 spaces indentation (no tabs)
- Opening braces on same line for methods
- Visibility must be declared for all properties/methods
- Use type hints for parameters and return types

### MySQL Standards

**Naming**:
- Tables: `snake_case` (e.g., `life_insurance_policies`, `dc_pensions`)
- Columns: `snake_case` (e.g., `user_id`, `sum_assured`, `premium_amount`)
- Primary keys: `id` (BIGINT UNSIGNED AUTO_INCREMENT)
- Foreign keys: `{table}_id` (e.g., `user_id`, `investment_account_id`)

**Data Types**:
- IDs: `BIGINT UNSIGNED`
- Currency: `DECIMAL(15,2)`
- Percentages/rates: `DECIMAL(5,4)`
- Dates: `DATE`, `TIMESTAMP` for created_at/updated_at

### Vue.js 3 Standards

**Component Naming**:
- Multi-word component names (e.g., `AssetForm.vue`, `IHTPlanning.vue`)
- PascalCase in SFC, kebab-case in templates

**CRITICAL - Form Modal Event Naming**:
```vue
<!-- ❌ WRONG - Causes double submission -->
<FormModal @submit="handleSubmit" />

<!-- ✅ CORRECT - Use 'save' event -->
<FormModal @save="handleSubmit" @close="closeModal" />
```

**Key Rules**:
- Always use `:key` with `v-for`
- Never use `v-if` with `v-for` on same element
- Use shorthand syntax (`:` for `v-bind`, `@` for `v-on`)
- Data must be a function returning object
- Detailed props with types and validators

---

## Important UK Tax Rules

**NOTE**: Values shown below are examples from the **2025/26 tax year**. The application uses database-driven tax configuration, so actual values are retrieved from the active tax year in the `tax_configurations` table.

**To view/update current values**: Use the admin panel → Tax Settings page.

**Historical tax years**: 2021/22, 2022/23, 2023/24, 2024/25 are available in the database for historical calculations.

### Inheritance Tax (IHT) - 2025/26 Example
- **Rate**: 40% on estate above nil rate band
- **NRB**: £325,000 (transferable to spouse)
- **RNRB**: £175,000 (residence nil rate band, transferable)
- **Spouse Exemption**: Unlimited transfers to spouse
- **PETs**: Potentially Exempt Transfers - 7-year rule with taper relief
- **CLTs**: Chargeable Lifetime Transfers - 14-year lookback

### Pensions - 2025/26 Example
- **Annual Allowance**: £60,000 (tapered for high earners)
- **Carry Forward**: 3-year lookback for unused allowance
- **MPAA**: £10,000 (Money Purchase Annual Allowance after accessing pension)
- **Lifetime Allowance**: Abolished from April 2024

### ISAs (Individual Savings Accounts) - 2025/26 Example
- **Annual Allowance**: £20,000 (tax year April 6 - April 5)
- **LISA**: £4,000 (counts towards total allowance)
- **Tax Treatment**: Tax-free growth and withdrawals
- **IHT Treatment**: ISAs are NOT IHT-exempt; included in estate

### Tax Year
- **Period**: April 6 to April 5
- **Current Active**: 2025/26 (configurable via admin panel)
- **Available Historical**: 2021/22, 2022/23, 2023/24, 2024/25

---

## Common Development Patterns

### Using TaxConfigService in New Services

**Pattern**: Always inject TaxConfigService via constructor dependency injection.

```php
<?php

namespace App\Services\MyModule;

use App\Services\TaxConfigService;

class MyCalculatorService
{
    public function __construct(
        private TaxConfigService $taxConfig
    ) {}

    public function calculateTax(float $amount): array
    {
        // Get tax configuration
        $incomeTax = $this->taxConfig->getIncomeTax();
        $personalAllowance = $incomeTax['personal_allowance'];
        $bands = $incomeTax['bands'];

        // Perform calculations using tax config values
        $taxableIncome = max(0, $amount - $personalAllowance);

        // Calculate tax across bands
        $tax = 0;
        foreach ($bands as $band) {
            // ... calculation logic
        }

        return [
            'gross_income' => $amount,
            'personal_allowance' => $personalAllowance,
            'taxable_income' => $taxableIncome,
            'tax' => $tax,
        ];
    }
}
```

**Testing Pattern**: Mock TaxConfigService in unit tests:

```php
<?php

use App\Services\MyModule\MyCalculatorService;
use App\Services\TaxConfigService;
use Mockery;

test('calculates tax correctly', function () {
    // Mock TaxConfigService
    $mockTaxConfig = Mockery::mock(TaxConfigService::class);
    $mockTaxConfig->shouldReceive('getIncomeTax')
        ->andReturn([
            'personal_allowance' => 12570,
            'bands' => [
                ['name' => 'Basic Rate', 'threshold' => 0, 'rate' => 0.20],
                ['name' => 'Higher Rate', 'threshold' => 37700, 'rate' => 0.40],
                ['name' => 'Additional Rate', 'threshold' => 125140, 'rate' => 0.45],
            ],
        ]);

    // Create service with mocked dependency
    $service = new MyCalculatorService($mockTaxConfig);

    // Test calculation
    $result = $service->calculateTax(50000);

    expect($result['personal_allowance'])->toBe(12570.0);
    expect($result['tax'])->toBeGreaterThan(0);
});
```

**DO NOT**:
- ❌ Use `config('uk_tax_config')` - this is deprecated
- ❌ Hardcode tax values in services
- ❌ Access database directly for tax values

**DO**:
- ✅ Inject TaxConfigService via constructor
- ✅ Mock TaxConfigService in unit tests
- ✅ Use specific getter methods (`getIncomeTax()`, `getPensionAllowances()`, etc.)

---

### Adding a New Feature to a Module

**Step-by-Step Guide**:

1. **Create Migration**
   ```bash
   php artisan make:migration create_new_feature_table
   ```

2. **Create Model** (`app/Models/NewFeature.php`)
   ```php
   class NewFeature extends Model
   {
       protected $fillable = ['user_id', 'name', 'value'];
       protected $casts = ['value' => 'float'];

       public function user()
       {
           return $this->belongsTo(User::class);
       }
   }
   ```

3. **Create Service** (`app/Services/Module/NewFeatureService.php`)
   ```php
   class NewFeatureService
   {
       public function calculate(int $userId): array
       {
           $data = NewFeature::where('user_id', $userId)->get();
           // Business logic here
           return ['result' => $calculation];
       }
   }
   ```

4. **Update Agent** (`app/Agents/ModuleAgent.php`)
   ```php
   public function analyze(int $userId): array
   {
       $newFeatureService = app(NewFeatureService::class);
       $result = $newFeatureService->calculate($userId);

       return [
           'existing_data' => ...,
           'new_feature' => $result
       ];
   }
   ```

5. **Create Form Request** (`app/Http/Requests/Module/StoreNewFeatureRequest.php`)

6. **Add Controller Method** (`app/Http/Controllers/Api/ModuleController.php`)

7. **Add Route** (`routes/api.php`)

8. **Create JS Service** (`resources/js/services/moduleService.js`)

9. **Update Vuex Store** (`resources/js/store/modules/module.js`)

10. **Create Vue Component** and update dashboard

### Cross-Module Data Integration

**Pattern**: Estate module aggregating assets from all modules

```php
// In EstateController or EstateAssetAggregatorService
public function aggregateAssets(int $userId): array
{
    return [
        'estate_assets' => Asset::where('user_id', $userId)->get(),
        'investment_accounts' => InvestmentAccount::where('user_id', $userId)->get(),
        'properties' => Property::where('user_id', $userId)->get(),
        'savings_accounts' => SavingsAccount::where('user_id', $userId)->get(),
        'pensions' => DCPension::where('user_id', $userId)->get(),
    ];
}
```

---

## Testing Patterns

### Unit Test Example (Pest)

```php
<?php

use App\Services\Estate\IHTCalculator;
use App\Models\User;
use App\Models\Estate\IHTProfile;

describe('IHTCalculator', function () {
    it('calculates IHT liability correctly for single person', function () {
        $user = User::factory()->create();
        $profile = IHTProfile::factory()->create([
            'user_id' => $user->id,
            'available_nrb' => 325000,
        ]);

        $assets = collect([
            (object)['current_value' => 500000]
        ]);

        $calculator = new IHTCalculator();
        $result = $calculator->calculateIHTLiability($assets, $profile);

        // Estate: £500k - NRB: £325k = £175k taxable
        // IHT: £175k × 40% = £70k
        expect($result['iht_liability'])->toBe(70000.0);
    });
});
```

### Feature Test Example

```php
<?php

use App\Models\User;

test('user can create asset via API', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/api/estate/assets', [
            'asset_name' => 'Test Property',
            'asset_type' => 'property',
            'current_value' => 250000,
            'ownership_type' => 'individual',
        ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'data' => [
                'asset_name' => 'Test Property',
                'current_value' => 250000,
            ]
        ]);

    $this->assertDatabaseHas('assets', [
        'user_id' => $user->id,
        'asset_name' => 'Test Property',
    ]);
});
```

---

## Dashboard & UI Patterns

### Module Dashboard Structure

Each module follows this pattern:

**Tab Structure**:
1. **Current Situation** - Data entry forms, current holdings
2. **Analysis** - Charts, calculations, metrics
3. **Recommendations** - Generated advice from agent
4. **What-If Scenarios** - Scenario modeling
5. **Details** - Additional information, settings

### ApexCharts Usage

**Common Chart Types**:
- **Radial Bar** (Gauges): Adequacy scores, readiness percentages
- **Donut**: Asset allocation, expense breakdown
- **Line**: Performance over time, projections
- **Area**: Stacked projections, income streams
- **Bar**: Comparisons, waterfalls
- **Heatmap**: Coverage gaps, risk matrices

**Color Scheme**:
- Green (`#10B981`): Good, on track, adequate
- Amber (`#F59E0B`): Caution, action needed
- Red (`#EF4444`): Critical, urgent action required

---

## Production Deployment

### Build Process

```bash
# Install production dependencies
composer install --optimize-autoloader --no-dev

# Build frontend assets
npm run build

# Run migrations
php artisan migrate --force

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

### Environment Variables (Production)

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_DATABASE=fps_production
DB_USERNAME=fps_user
DB_PASSWORD=<strong-password>

CACHE_DRIVER=memcached
QUEUE_CONNECTION=database
SESSION_DRIVER=database

MEMCACHED_HOST=127.0.0.1
MEMCACHED_PORT=11211
```

### Server Requirements

- PHP 8.2+ with extensions: BCMath, Ctype, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML
- MySQL 8.0+
- Memcached 1.6+ (or Redis alternative)
- Nginx or Apache with mod_rewrite
- SSL Certificate (Let's Encrypt recommended)
- Supervisor for queue workers

---

## Key Development Principles

1. **UK-Specific**: Follow UK tax rules (2025/26 baseline), tax year April 6 - April 5
2. **Agent Pattern**: Encapsulate module logic in Agent classes with standardized interface
3. **Centralized Config**: Never hardcode tax rates; use `TaxConfigService` to retrieve database-driven tax configuration
4. **Data Isolation**: Users only access their own data; always filter by `user_id`
5. **Progressive Disclosure**: Summaries first, details on demand
6. **Mobile-First**: Responsive design 320px to 2560px
7. **Caching**: Cache expensive calculations with appropriate TTLs
8. **Testing**: Write Pest tests for all financial calculations
9. **No Auto-Populate**: Never auto-generate fictional demo data

---

## Documentation References

For deeper understanding, refer to:

- **CODEBASE_STRUCTURE.md**: Complete architecture, data flows, module breakdown
- **DATABASE_SCHEMA_GUIDE.md**: Database schema, table relationships
- **README.md**: Installation, setup, deployment instructions
- **OCTOBER_2025_FEATURES_UPDATE.md**: Recent feature updates (v0.1.2)
- **DEV_ENVIRONMENT_TROUBLESHOOTING.md**: Environment debugging guide

---

## Support & Resources

**Demo Credentials**:
- User: `demo@fps.com` / `password`
- Admin: `admin@fps.com` / `admin123456`

**Documentation Locations**:
- Main docs: Project root directory
- Skills: `.claude/skills/` (fps-module-builder, fps-feature-builder, fps-component-builder)
- Tests: `tests/` (Unit, Feature, Architecture)

---

**Current Version**: v0.2.1 (Beta)
**Last Updated**: November 4, 2025
**Status**: 🚀 Active Development - Core Modules Complete with Advanced Portfolio Optimization

---

🤖 **Built with [Claude Code](https://claude.com/claude-code)**
