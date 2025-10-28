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

---

## Project Overview

**FPS (Financial Planning System)** - UK-focused comprehensive financial planning application covering five integrated modules: Protection, Savings, Investment, Retirement, and Estate Planning.

**Current Version**: v0.1.2.12
**Tech Stack**: Laravel 10.x (PHP 8.2+) + Vue.js 3 + MySQL 8.0+ + Memcached

**Status**: Active Development - Core features complete, spouse management & joint ownership implemented

**Recent Updates (v0.1.2.12)**:
- Comprehensive Estate Plan enhancements with separate User/Spouse/Combined estate breakdown
- IHT Position displaying NOW vs PROJECTED scenarios side-by-side
- Separate NRB (£325k + £325k) and RNRB display for married couples
- Removed duplicate sections (Balance Sheet, Summary by Asset Type, Recommended Strategy box)
- Fixed age calculation in profile section
- Removed "Effective IHT Rate" (not a standard UK IHT concept)

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

**⚠️ CRITICAL**: You must run **BOTH** servers simultaneously:

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

**Location**: `config/uk_tax_config.php` (or `tax_configurations` table)

Contains all UK tax rules for 2025/26:
- Income tax bands & personal allowance (£12,570)
- National Insurance thresholds
- ISA allowance (£20,000 per tax year)
- Pension annual allowance (£60,000)
- IHT: NRB £325k, RNRB £175k, 40% rate
- PET/CLT gifting rules with 7-year taper relief

**CRITICAL**: Never hardcode tax rates - always use config values

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

---

## File Structure & Key Locations

### Backend Structure

```
app/
├── Agents/                    # Module analysis orchestrators (7 files)
│   ├── BaseAgent.php          # Abstract base class
│   ├── ProtectionAgent.php
│   ├── SavingsAgent.php
│   ├── InvestmentAgent.php
│   ├── RetirementAgent.php
│   ├── EstateAgent.php
│   └── CoordinatingAgent.php
│
├── Http/Controllers/Api/      # RESTful API controllers (25+ files)
│   ├── AuthController.php
│   ├── ProtectionController.php
│   ├── SavingsController.php
│   ├── InvestmentController.php
│   ├── RetirementController.php
│   ├── EstateController.php
│   ├── Estate/                # Estate sub-controllers
│   │   ├── IHTController.php
│   │   ├── GiftingController.php
│   │   ├── LifePolicyController.php
│   │   ├── TrustController.php
│   │   └── WillController.php
│   └── ... (other controllers)
│
├── Services/                  # Business logic (50+ files)
│   ├── Estate/                # 20+ estate services
│   │   ├── IHTCalculator.php
│   │   ├── NetWorthAnalyzer.php
│   │   ├── CashFlowProjector.php
│   │   ├── GiftingStrategy.php
│   │   ├── SecondDeathIHTCalculator.php
│   │   └── ...
│   ├── Protection/            # 5 protection services
│   ├── Savings/               # 5 savings services (includes ISATracker)
│   ├── Investment/            # 5 investment services
│   ├── Retirement/            # 5 retirement services
│   ├── Coordination/          # Cross-module services
│   └── UKTaxCalculator.php    # Shared tax calculator
│
├── Models/                    # Eloquent models (39 files)
│   ├── User.php
│   ├── FamilyMember.php
│   ├── Estate/                # Estate models subdirectory
│   │   ├── Asset.php
│   │   ├── Liability.php
│   │   ├── Gift.php
│   │   ├── Trust.php
│   │   ├── IHTProfile.php
│   │   └── Will.php
│   └── ... (other models)
│
└── Http/Requests/             # Form validation (30+ files)
    ├── Protection/
    ├── Savings/
    ├── Investment/
    ├── Retirement/
    └── Estate/

config/
└── uk_tax_config.php          # CRITICAL: All UK tax rules

routes/
└── api.php                    # All API routes (420+ lines)
```

### Frontend Structure

```
resources/js/
├── components/                # Vue components (150+ files)
│   ├── Estate/                # 45+ estate components
│   │   ├── IHTPlanning.vue
│   │   ├── GiftingStrategy.vue
│   │   ├── WillPlanning.vue
│   │   ├── LifePolicyStrategy.vue
│   │   └── ...
│   ├── Protection/            # 20+ protection components
│   ├── Savings/               # 15+ savings components
│   ├── Investment/            # 20+ investment components
│   ├── Retirement/            # 15+ retirement components
│   ├── NetWorth/              # 12+ net worth components
│   └── ... (other component directories)
│
├── views/                     # Page-level components (25 files)
│   ├── Dashboard.vue
│   ├── Estate/EstateDashboard.vue
│   ├── Protection/ProtectionDashboard.vue
│   └── ... (other dashboards)
│
├── store/modules/             # Vuex stores (16 files)
│   ├── auth.js
│   ├── estate.js
│   ├── protection.js
│   ├── savings.js
│   ├── investment.js
│   ├── retirement.js
│   └── ...
│
├── services/                  # API wrappers (17 files)
│   ├── api.js                 # Axios instance
│   ├── estateService.js
│   ├── protectionService.js
│   └── ...
│
└── router/
    └── index.js               # Vue Router config
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

**Schema**:
```sql
CREATE TABLE dc_pensions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    scheme_name VARCHAR(255) NOT NULL,
    current_value DECIMAL(15,2) NOT NULL,
    contribution_rate DECIMAL(5,4) DEFAULT 0.0000,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Vue.js 3 Standards

**Component Naming**:
- Multi-word component names (e.g., `AssetForm.vue`, `IHTPlanning.vue`)
- PascalCase in SFC, kebab-case in templates

**Component Structure**:
```vue
<template>
  <div class="component-wrapper">
    <h2>{{ title }}</h2>
    <form @submit.prevent="submitForm">
      <input v-model="formData.name" />
      <button type="submit">Save</button>
    </form>
  </div>
</template>

<script>
import estateService from '@/services/estateService';

export default {
  name: 'AssetForm',

  components: {
    // Component dependencies
  },

  props: {
    asset: {
      type: Object,
      default: null
    }
  },

  data() {
    return {
      formData: this.asset || { name: '' }
    };
  },

  computed: {
    title() {
      return this.asset ? 'Edit Asset' : 'Add Asset';
    }
  },

  methods: {
    async submitForm() {
      try {
        const response = this.asset
          ? await estateService.updateAsset(this.asset.id, this.formData)
          : await estateService.storeAsset(this.formData);

        this.$emit('save', response.data);  // NEVER use 'submit' for custom events
        this.$emit('close');
      } catch (error) {
        console.error('Error saving:', error);
      }
    }
  },

  mounted() {
    // Component lifecycle
  }
};
</script>

<style scoped>
/* Component styles */
</style>
```

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

### Inheritance Tax (IHT)
- **Rate**: 40% on estate above nil rate band
- **NRB**: £325,000 (transferable to spouse)
- **RNRB**: £175,000 (residence nil rate band, transferable)
- **Spouse Exemption**: Unlimited transfers to spouse
- **PETs**: Potentially Exempt Transfers - 7-year rule with taper relief
- **CLTs**: Chargeable Lifetime Transfers - 14-year lookback

### Pensions
- **Annual Allowance**: £60,000 (tapered for high earners)
- **Carry Forward**: 3-year lookback for unused allowance
- **MPAA**: £10,000 (Money Purchase Annual Allowance after accessing pension)
- **Lifetime Allowance**: Abolished from April 2024

### ISAs (Individual Savings Accounts)
- **Annual Allowance**: £20,000 (tax year April 6 - April 5)
- **LISA**: £4,000 (counts towards total allowance)
- **Tax Treatment**: Tax-free growth and withdrawals
- **IHT Treatment**: ISAs are NOT IHT-exempt; included in estate

### Tax Year
- **Period**: April 6 to April 5
- **Current**: 2025/26

---

## Common Development Patterns

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
   ```php
   class StoreNewFeatureRequest extends FormRequest
   {
       public function authorize(): bool
       {
           return true;
       }

       public function rules(): array
       {
           return [
               'name' => 'required|string|max:255',
               'value' => 'required|numeric|min:0',
           ];
       }
   }
   ```

6. **Add Controller Method** (`app/Http/Controllers/Api/ModuleController.php`)
   ```php
   public function storeNewFeature(StoreNewFeatureRequest $request): JsonResponse
   {
       $user = $request->user();
       $validated = $request->validated();

       $validated['user_id'] = $user->id;
       $feature = NewFeature::create($validated);

       Cache::forget("module_analysis_{$user->id}");

       return response()->json([
           'success' => true,
           'data' => $feature,
       ], 201);
   }
   ```

7. **Add Route** (`routes/api.php`)
   ```php
   Route::post('/module/new-feature', [ModuleController::class, 'storeNewFeature']);
   ```

8. **Create JS Service** (`resources/js/services/moduleService.js`)
   ```javascript
   async storeNewFeature(data) {
       const response = await api.post('/module/new-feature', data);
       return response.data;
   }
   ```

9. **Update Vuex Store** (`resources/js/store/modules/module.js`)
   ```javascript
   actions: {
       async saveNewFeature({ commit }, data) {
           const response = await moduleService.storeNewFeature(data);
           commit('addNewFeature', response.data);
           return response;
       }
   },
   mutations: {
       addNewFeature(state, feature) {
           state.newFeatures.push(feature);
       }
   }
   ```

10. **Create Vue Component** (`resources/js/components/Module/NewFeatureForm.vue`)

11. **Update Dashboard** (`resources/js/views/Module/ModuleDashboard.vue`)

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

    it('applies spouse exemption correctly', function () {
        $user = User::factory()->create(['marital_status' => 'married']);
        $profile = IHTProfile::factory()->create(['user_id' => $user->id]);

        // Test implementation...
        expect($result['spouse_exemption'])->toBeGreaterThan(0);
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

## Documentation References

For deeper understanding, refer to:

- **CODEBASE_STRUCTURE.md**: Complete architecture, data flows, module breakdown (1,425 lines)
- **CODEBASE_FILE_MAP.md**: File locations, dependency relationships (1,063 lines)
- **DATABASE_SCHEMA_GUIDE.md**: Database schema, table relationships
- **README.md**: Installation, setup, deployment instructions
- **OCTOBER_2025_FEATURES_UPDATE.md**: Recent feature updates (v0.1.2)

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
3. **Centralized Config**: Never hardcode tax rates; use `config/uk_tax_config.php`
4. **Data Isolation**: Users only access their own data; always filter by `user_id`
5. **Progressive Disclosure**: Summaries first, details on demand
6. **Mobile-First**: Responsive design 320px to 2560px
7. **Caching**: Cache expensive calculations with appropriate TTLs
8. **Testing**: Write Pest tests for all financial calculations
9. **No Auto-Populate**: Never auto-generate fictional demo data

---

## Constraints & Limitations

- **UK Only**: No international tax jurisdictions
- **Manual Entry**: No external integrations or Open Banking
- **DB Pensions**: Projection only, no transfer advice
- **Demo System**: Not a production financial advisory tool
- **Non-Regulated**: Educational/demonstration purposes only

---

## Support & Resources

**Issues/Questions**: Create an issue in the repository

**Documentation Locations**:
- Main docs: `/Users/Chris/Desktop/fpsV2/`
- Skills: `.claude/skills/` (fps-module-builder, fps-feature-builder, fps-component-builder)
- Tests: `tests/` (Unit, Feature, Architecture)

**Demo Credentials**:
- User: `demo@fps.com` / `password`
- Admin: `admin@fps.com` / `admin123456`

---

**Current Version**: v0.1.2.12 (Beta)
**Last Updated**: October 28, 2025
**Status**: 🚀 Active Development - Core Features Complete

---

🤖 **Built with [Claude Code](https://claude.com/claude-code)**
