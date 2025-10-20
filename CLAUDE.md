# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

---

## ⚠️ CRITICAL INSTRUCTION - MAINTAIN APPLICATION FUNCTIONALITY

**THE APPLICATION MUST REMAIN FULLY FUNCTIONAL AT ALL TIMES.**

When working on any task, feature, or improvement:

1. **DO NOT REMOVE** existing features, functionality, pages, views, components, or any other working code
2. **ONLY WORK** on the specific section, feature, or component that is explicitly being asked to be modified
3. **DO NOT MODIFY** other areas of the application unless absolutely necessary
4. **IF OTHER AREAS ARE AFFECTED** by your changes:
   - **STOP** and explain what other areas will be impacted
   - **ASK PERMISSION** before making changes to those areas
   - **PROVIDE DETAILS** about why those changes are necessary
   - **WAIT FOR APPROVAL** before proceeding

5. **WHEN ITERATING** on UI/UX or functionality:
   - Preserve all existing functionality
   - Only enhance or modify what is specifically requested
   - Do not remove working features in favor of new implementations without explicit approval

6. **BEFORE MAKING CHANGES**:
   - Understand the full scope of impact
   - Identify all files and components that will be modified
   - Consider backward compatibility
   - Ensure tests will still pass

**VIOLATION OF THIS RULE IS UNACCEPTABLE.** The user expects a stable, working application at all times.

---

## Project Overview

This is the **FPS (Financial Planning System)** - a comprehensive financial planning web application designed for UK individuals and families. The system covers five integrated modules: Protection, Savings, Investment, Retirement, and Estate Planning.

**Current Status**: Phase 02 complete (v0.1.1). Laravel 10.x backend with Sanctum authentication, Vue.js 3 frontend with full auth flow, Pest testing suite, User Profile module, Settings page, and error handling implemented. Net Worth (Property Management), Savings (Emergency Fund + ISA Tracking), Investment, Retirement, and Estate modules operational.

## Technology Stack

### Backend
- **Framework**: Laravel 10.x (PHP 8.2+)
- **Database**: MySQL 8.0+ with InnoDB engine
- **Cache**: Memcached 1.6+
- **Queue**: Laravel Queues (database-backed)
- **Testing**: Pest (built on PHPUnit)

### Frontend
- **Framework**: Vue.js 3
- **State Management**: Vuex 4.x
- **Build Tool**: Vite
- **Charts**: ApexCharts
- **CSS**: Tailwind CSS 3.x

### Development Tools
- **API Testing**: Postman
- **Version Control**: Git

## Architecture Overview

### Agent-Based System
The application uses an **agent-based architecture** where each module has an intelligent agent that:
1. Takes user inputs from dynamic forms
2. Performs domain-specific calculations
3. Generates recommendations
4. Returns structured outputs

**Key Agents**:
- `ProtectionAgent` - Life insurance, critical illness, income protection analysis
- `SavingsAgent` - Emergency fund, cash management, ISA tracking
- `InvestmentAgent` - Portfolio analysis, Monte Carlo simulations, asset allocation
- `RetirementAgent` - Pension projections, contribution optimization, decumulation planning
- `EstateAgent` - IHT calculations, gifting strategy, net worth analysis
- `CoordinatingAgent` (optional) - Aggregates recommendations across all modules

### Three-Tier Architecture
```
Presentation Layer (Vue.js 3 + ApexCharts)
         ↕
Application Layer (Laravel Controllers + Agents + Services)
         ↕
Data Layer (MySQL + Memcached)
```

### Project Structure

**Backend (Laravel):**
```
app/
├── Agents/              # Business logic agents (one per module)
├── Http/
│   ├── Controllers/Api/ # RESTful API controllers
│   ├── Requests/        # Form request validation classes
│   └── Middleware/      # Custom middleware
├── Models/              # Eloquent models (organized by module)
│   ├── Estate/
│   └── Investment/
├── Services/            # Reusable service classes for calculations
│   ├── Dashboard/
│   ├── Estate/
│   ├── Investment/
│   ├── Protection/
│   ├── Retirement/
│   └── Savings/
└── Jobs/                # Queue jobs (e.g., Monte Carlo simulations)
```

**Frontend (Vue.js 3):**
```
resources/js/
├── components/          # Vue components (organized by module)
│   ├── Common/          # Shared UI components
│   ├── Dashboard/
│   ├── Estate/
│   ├── Investment/
│   ├── Protection/
│   ├── Retirement/
│   ├── Savings/
│   └── Shared/          # Cross-module components
├── layouts/             # App layouts (AppLayout.vue)
├── router/              # Vue Router configuration
├── services/            # API service layer (axios wrappers)
├── store/               # Vuex state management
│   └── modules/         # Module-specific stores
├── utils/               # Utility functions
├── views/               # Page-level components (routed)
│   ├── Dashboard/
│   ├── Estate/
│   ├── Investment/
│   ├── Protection/
│   ├── Retirement/
│   └── Savings/
├── app.js               # Main Vue app entry point
└── bootstrap.js         # Axios configuration
```

**API Structure:**
- All API routes are prefixed with `/api` (defined in `routes/api.php`)
- Authentication uses Laravel Sanctum (token-based)
- API endpoints follow RESTful conventions
- Frontend services (e.g., `resources/js/services/investmentService.js`) wrap API calls

## Key Implementation Details

### Centralized Tax Configuration
All UK tax rules, allowances, and IHT rules are maintained in a centralized configuration file (`config/uk_tax_config.php` or `tax_configurations` table). This includes:
- Income tax bands and rates
- National Insurance thresholds
- ISA allowances (£20,000 total for 2025/26)
- Pension annual allowance (£60,000)
- IHT rates, NRB (£325,000), RNRB (£175,000)
- PET/CLT gifting rules

**Update process**: When UK tax rules change (typically after Budget), update the centralized config and deploy.

### ISA Allowance Tracking
ISA tracking is **cross-module** (implemented as of v0.1.1):
- Cash ISA subscriptions tracked in Savings Module (`savings_accounts.isa_subscription_amount`)
- Stocks & Shares ISA subscriptions tracked in Investment Module (`investment_accounts.isa_subscription_current_year`)
- Total combined allowance: £20,000 per tax year
- Current tax year: 2025/26 (dynamically calculated based on April 6 - April 5 UK tax year)
- The ISA allowance tracker appears on Savings > Cash tab
- Service: `app/Services/Savings/ISATracker.php` provides cross-module aggregation
- API endpoint: `/api/savings` returns full ISA allowance breakdown

### Calculation Patterns
All financial calculations are implemented as functions/methods within the application:
- **Protection**: Human capital valuation, coverage gap analysis
- **Savings**: Emergency fund runway, goal projections
- **Investment**: Monte Carlo simulations (1,000 iterations), asset allocation optimization
- **Retirement**: DC/DB pension projections, sustainable withdrawal rate
- **Estate**: IHT calculation with NRB/RNRB, PET taper relief (7-year rule), CLT tracking (14-year lookback)

**Note on DB Pensions**: DB pension information is captured for **income projection only** - no DB to DC transfer advice is provided.

### Background Jobs
Monte Carlo simulations run as **Laravel Queue jobs** (database-backed) to avoid blocking the UI. Results are polled via job ID.

### Caching Strategy
Memcached is used with TTLs based on data volatility:
- Tax config: 1 hour
- Monte Carlo results: 24 hours
- Dashboard data: 30 minutes

## Module Structure

### Protection Module
- Covers all UK protection products (life insurance, critical illness, income protection, health insurance)
- Key outputs: Coverage adequacy score, gap analysis heatmap, prioritized recommendations
- Dashboard charts: Coverage gauge, gap heatmap, premium breakdown, policy timeline

### Savings Module
- Emergency fund analysis (with designated account tracking), savings goals tracking, ISA allowance monitoring
- Key features:
  - Emergency Fund Tracking: Users can mark specific accounts as "emergency fund" (`is_emergency_fund` field)
  - Runway calculation: Emergency fund total ÷ monthly expenditure (from User Profile)
  - Empty state handling: Shows "Add Expenditure" button when monthly expenditure = 0
  - ISA Allowance Tracker: Cross-module tracking of Cash ISA + Stocks ISA + LISA
  - Account types: Savings Account, Current Account, Easy Access, Notice Account, Fixed Term
- Key outputs: Emergency fund runway (months), emergency fund total, ISA usage progress
- Dashboard tabs: Current Situation, Emergency Fund, Savings Goals, Recommendations, What-If Scenarios, Account Details
- Visual indicators: Green "Emergency Fund" badge, Blue "ISA" badge on accounts

### Investment Module
- Portfolio analysis, asset allocation, Monte Carlo projections, fee analysis
- Key outputs: Portfolio value, returns, allocation deviation, goal probability
- Dashboard charts: Asset allocation donut, performance line chart, Monte Carlo area chart

### Retirement Module
- Pension inventory (DC/DB/State), contribution tracking, annual allowance monitoring, decumulation planning
- Key outputs: Retirement readiness score, income projection, contribution recommendations
- Dashboard charts: Readiness gauge, income stacked area chart, drawdown simulator

### Estate Planning Module (Net Worth)
- IHT calculation, net worth tracking, personal P&L/cashflow, gifting strategy (PETs/CLTs)
- Property Management (v0.1.1):
  - Add/edit properties with simplified form (PropertyForm modal)
  - Required fields: Property Type, Address, Current Value
  - Optional fields: Outstanding Mortgage, Rental Income, Purchase Price/Date
  - Virtual field mapping: `address` → `address_line_1`, `rental_income` → `monthly_rental_income`
  - Property types: Main Residence, Secondary Residence, Buy to Let, Commercial, Land
- Dashboard tabs: Overview, Property, Investments, Cash, Business Interests, Chattels
- Key outputs: IHT liability, net worth, gifting timeline, probate readiness score
- Dashboard charts: IHT waterfall, net worth bar chart, gifting timeline (rangeBar)

## Important UK-Specific Rules

### Inheritance Tax (IHT)
- **Rate**: 40% (or 36% if 10%+ to charity)
- **NRB**: £325,000 (transferable between spouses)
- **RNRB**: £175,000 (main residence, tapered above £2m estate, transferable)
- **PETs**: 7-year rule with taper relief (20% per year in years 3-7)
- **CLTs**: 14-year lookback for cumulative total calculation

### Pensions
- **Annual Allowance**: £60,000 (tapered for high earners: threshold income >£200k, adjusted income >£260k)
- **Carry Forward**: Unused allowance from previous 3 tax years
- **MPAA**: £10,000 (after flexible pension access)

### ISAs
- **Total Allowance**: £20,000 per tax year
- **LISA**: £4,000 (counts towards total ISA allowance)

## Dashboard Design Pattern

All modules follow a consistent pattern:
1. **Main Dashboard Card** (summary view, clickable)
2. **Detailed Module Dashboard** with tabs:
   - Current Situation
   - Analysis/Gap Analysis
   - Recommendations
   - What-If Scenarios
   - Details

**Navigation**: Bidirectional between main dashboard and module dashboards with breadcrumbs.

## Chart Standards (ApexCharts)

All visualizations use ApexCharts:
- **Gauges**: Radial bar for scores (coverage adequacy, emergency fund, retirement readiness)
- **Asset Allocation**: Donut charts
- **Performance**: Line charts with benchmarks
- **Projections**: Area charts (often stacked for income sources)
- **Gaps/Heatmaps**: Heatmap charts
- **Timelines**: RangeBar or Timeline charts (gifting strategy)
- **Waterfalls**: Bar charts for IHT calculation breakdown

**Color scheme**: Traffic light system (green = good, amber = caution, red = critical)

## Testing Approach

### Pest Testing
- **Unit Tests**: All financial calculations (tax, IHT, projections, Monte Carlo)
- **Feature Tests**: API endpoints, CRUD operations
- **Architecture Tests**: Code quality checks (agents extend BaseAgent, models have no public properties)

### Frontend Tests
- **Vue Test Utils**: Component rendering, chart data binding

### API Testing
- **Postman Collections**: One collection per module

## Design System

Refer to `designStyleGuide.md` for comprehensive UI/UX standards including:
- Color palette
- Typography (font families, sizes, weights)
- Spacing and layout grids
- Component specifications
- Chart styling
- Responsive breakpoints
- Accessibility guidelines

## Mermaid Diagrams

Process flow diagrams are located in the root directory with `.md` extension:
- `fpflow.md` - Holistic financial planning end-to-end (6 stages)
- `protectionflow.md` - Protection insurance workflow
- `investmentflow.md` - Investment planning workflow
- `retirementflow.md` - Retirement planning workflow
- `estateflow.md` - Estate planning workflow
- `smediagrame.md` - Small business/owner-managed business process

**Format**: All diagrams use mermaid flowchart syntax with quoted node text to handle special characters.

## Important Documentation Files

- **FPS_PRD.md**: Product Requirements Document (functional requirements, user stories, success criteria)
- **FPS_Features_TechStack.md**: Technical implementation guide (feature breakdown, API specs, database schema, testing strategy, deployment)
- **moduleSimple.md**: Simplified architecture overview (agent-based approach, UK tax config structure)
- **designStyleGuide.md**: Complete design system and component specifications
- **design/*.html**: UI mockups and prototypes
- **NET_WORTH_SAVINGS_IMPLEMENTATION.md**: Detailed implementation documentation for Phase 02 v0.1.1 features (property management, ISA tracking, emergency fund)

## Development Workflow

### Initial Setup
```bash
# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed --class=TaxConfigurationSeeder
```

### Development Commands
```bash
# Start Laravel development server (port 8000)
php artisan serve

# Start Vite dev server with HMR (separate terminal)
npm run dev

# Queue worker for background jobs (separate terminal, if needed for Monte Carlo)
php artisan queue:work database
```

### Testing Commands

**Backend (Pest/PHPUnit):**
```bash
# Run all tests
./vendor/bin/pest

# Run specific test suite
./vendor/bin/pest --testsuite=Unit
./vendor/bin/pest --testsuite=Feature
./vendor/bin/pest --testsuite=Architecture

# Run specific test file
./vendor/bin/pest tests/Unit/Services/Protection/AdequacyScorerTest.php

# Run tests with coverage
./vendor/bin/pest --coverage

# Run tests in parallel (faster)
./vendor/bin/pest --parallel
```

**Frontend (Vitest):**
```bash
# Run all frontend tests
npm run test

# Run tests in watch mode (interactive)
npm run test

# Run tests once and exit
npm run test:run

# Run tests with UI
npm run test:ui
```

### Database Commands
```bash
# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Fresh migration (drops all tables and re-migrates)
php artisan migrate:fresh

# Seed database
php artisan db:seed
php artisan db:seed --class=TaxConfigurationSeeder
```

### Cache Commands
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

### Production Build
```bash
# Install production dependencies
composer install --optimize-autoloader --no-dev

# Build frontend assets
npm run build
```

## Coding Standards

### PHP (PSR-12)

Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards:

**File Structure:**

- Use Unix LF (linefeed) line endings
- End files with a non-blank line
- Omit closing `?>` tag in PHP-only files
- File header order: `<?php` tag → file docblock → declare statements → namespace → use statements → code

**Naming Conventions:**

- Use PascalCase for class names (e.g., `ProtectionAgent`, `IHTCalculator`)
- Use camelCase for methods and properties (e.g., `calculateHumanCapital()`, `$annualIncome`)
- Use lowercase for PHP reserved keywords and types
- Use short type keywords (`bool`, `int`, `float` instead of `boolean`, `integer`, `double`)
- Do not prefix properties or methods with single underscore

**Formatting:**

- Use 4 spaces for indentation (no tabs)
- Soft line length limit: 120 characters (recommended: 80 characters)
- No trailing whitespace
- One statement per line
- Visibility must be declared for all properties and methods
- Use braces for all control structures
- Opening braces on same line for methods/functions
- Use `elseif` instead of `else if`

**Example:**

```php
<?php

declare(strict_types=1);

namespace App\Agents;

use App\Services\TaxCalculator;

class ProtectionAgent extends BaseAgent
{
    public function calculateCoverageGap(float $income, int $age): array
    {
        $humanCapital = $this->calculateHumanCapital($income, $age);

        return [
            'gap' => $humanCapital - $existingCoverage,
            'adequacy_score' => min(100, ($existingCoverage / $humanCapital) * 100),
        ];
    }
}
```

### MySQL 8.0+

Follow [MySQL coding guidelines](https://dev.mysql.com/doc/dev/mysql-server/8.4.6/PAGE_CODING_GUIDELINES.html):

**Naming Conventions:**

- Use `snake_case` for table names (e.g., `life_insurance_policies`, `dc_pensions`)
- Use `snake_case` for column names (e.g., `user_id`, `sum_assured`, `premium_amount`)
- Use descriptive names that reflect the data content
- Primary keys: `id` (BIGINT UNSIGNED AUTO_INCREMENT)
- Foreign keys: `{table}_id` (e.g., `user_id`, `investment_account_id`)

**Schema Design:**

- Use InnoDB engine for all tables (ACID compliance, foreign key support)
- Always define indexes on foreign keys
- Use appropriate data types:
  - `BIGINT UNSIGNED` for IDs
  - `DECIMAL(15,2)` for currency values
  - `DECIMAL(5,4)` for percentages/interest rates
  - `VARCHAR` with appropriate length for strings
  - `DATE` for dates, `TIMESTAMP` for created_at/updated_at
- Include `created_at` and `updated_at` timestamps on all tables
- Use `ON DELETE CASCADE` for dependent data, `ON DELETE SET NULL` for optional references

**Query Formatting:**

- Write SQL keywords in UPPERCASE
- Use meaningful aliases for tables
- Break complex queries across multiple lines for readability
- Always use prepared statements (Eloquent ORM handles this)

**Example:**

```sql
CREATE TABLE dc_pensions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    scheme_name VARCHAR(255) NOT NULL,
    current_fund_value DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    employee_contribution_percent DECIMAL(5,2),
    employer_contribution_percent DECIMAL(5,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Vue.js 3

Follow [Vue.js Style Guide](https://v2.vuejs.org/v2/style-guide/) (Priority A & B rules):

**Component Naming (Priority A - Essential):**

- Component names must be multi-word (except root `App`)
- Use PascalCase in JS/SFC: `ProtectionDashboard.vue`, `EmergencyFundGauge.vue`
- Use kebab-case in DOM templates: `<protection-dashboard>`, `<emergency-fund-gauge>`

**Component Structure:**

- Component data must be a function returning an object
- Each component should be in its own file
- Use single-file components (.vue) for all components

**Props (Priority A):**

- Define props with detailed specifications (type, required, default, validator)
- Use camelCase for prop names in component definitions
- Use kebab-case for prop names in templates

**Template Syntax (Priority B):**

- Always use `key` with `v-for`
- Never use `v-if` on the same element as `v-for` (use computed properties)
- Use directive shorthands consistently (`:` for `v-bind`, `@` for `v-on`)
- Keep template expressions simple (move complex logic to computed properties)
- Quote attribute values

**Component Organization:**

- Use consistent order for component options: name → components → props → data → computed → methods → lifecycle hooks
- Prefix base/single-instance components: `BaseButton`, `TheNavbar`

**IMPORTANT: Form Modal Event Naming:**

When creating form modal components, **NEVER** use `@submit` as the event name for custom events. This causes conflicts with native HTML form submit events, resulting in double submissions and validation errors.

**Correct Pattern:**

```vue
<!-- In the Form Modal Component -->
<template>
  <form @submit.prevent="submitForm">
    <!-- form fields -->
  </form>
</template>

<script>
methods: {
  submitForm() {
    // Validate form
    if (!this.validateForm()) {
      this.submitting = false;
      return;
    }

    // Emit 'save' event (NOT 'submit')
    this.$emit('save', formData);
  }
}
</script>
```

```vue
<!-- In the Parent Component -->
<template>
  <FormModal
    @save="handleSubmit"  <!-- Use @save NOT @submit -->
    @close="closeModal"
  />
</template>

<script>
methods: {
  async handleSubmit(formData) {
    try {
      await this.saveData(formData);
      this.closeModal(); // Close modal AFTER successful save
      this.successMessage = 'Saved successfully';
    } catch (error) {
      this.error = error.message;
      // Modal stays open on error
    }
  }
}
</script>
```

**Why this matters:**
- `@submit` catches BOTH custom Vue events AND native form submit events
- This causes double API calls: first with correct data, second with SubmitEvent object
- Second call fails with 422 validation errors
- Using `@save` avoids this conflict entirely

**Example:**

```vue
<template>
  <div class="protection-overview-card" @click="navigateToDetail">
    <h3>{{ title }}</h3>
    <div class="metric">
      <span class="value">{{ formattedScore }}</span>
      <span class="label">Coverage Score</span>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ProtectionOverviewCard',

  props: {
    title: {
      type: String,
      required: true,
    },
    adequacyScore: {
      type: Number,
      required: true,
      validator: (value) => value >= 0 && value <= 100,
    },
  },

  computed: {
    formattedScore() {
      return `${this.adequacyScore}%`;
    },
  },

  methods: {
    navigateToDetail() {
      this.$router.push('/protection');
    },
  },
};
</script>
```

### JavaScript/TypeScript

**General Best Practices:**

- Use `const` by default, `let` when reassignment is needed, never use `var`
- Use arrow functions for callbacks and anonymous functions
- Use template literals for string interpolation
- Use destructuring for objects and arrays
- Use async/await instead of promise chains
- Use meaningful variable names (avoid single letters except in loops)

**TypeScript (if used):**

- Always define explicit types for function parameters and return values
- Use interfaces for object shapes, types for unions/intersections
- Avoid `any` type - use `unknown` if type is truly unknown
- Use optional chaining (`?.`) and nullish coalescing (`??`)
- Enable strict mode in tsconfig.json

**Example:**

```typescript
interface RetirementProjection {
  retirementAge: number;
  projectedIncome: number;
  targetIncome: number;
  readinessScore: number;
}

async function calculateRetirementReadiness(
  userId: number
): Promise<RetirementProjection> {
  const pensions = await fetchUserPensions(userId);
  const projectedIncome = calculateTotalProjectedIncome(pensions);
  const targetIncome = await getTargetRetirementIncome(userId);

  return {
    retirementAge: 67,
    projectedIncome,
    targetIncome,
    readinessScore: Math.min(100, (projectedIncome / targetIncome) * 100),
  };
}
```

### Laravel Best Practices

**Controllers:**

- Keep controllers thin - delegate business logic to services/agents
- Use Form Requests for validation
- Return consistent JSON responses for API endpoints

**Models:**

- Use Eloquent relationships (hasMany, belongsTo, etc.)
- Define fillable or guarded properties
- Use mutators and accessors for data transformation
- Keep models focused on data representation, not business logic

**Services/Agents:**

- Create dedicated service classes for business logic
- Agent classes should inherit from `BaseAgent`
- Keep methods focused and single-purpose
- Use dependency injection

**Routes:**

- Use route groups for related endpoints
- Use route model binding where appropriate
- Protect routes with middleware (auth, CSRF)

**Example:**

```php
// Agent
class RetirementAgent extends BaseAgent
{
    public function __construct(
        private TaxCalculator $taxCalculator,
        private PensionProjector $projector
    ) {}

    public function analyze(int $userId): array
    {
        $pensions = DCPension::where('user_id', $userId)->get();
        $projections = $this->projector->projectPensions($pensions);

        return $this->generateRecommendations($projections);
    }
}

// Controller
class RetirementController extends Controller
{
    public function analyze(RetirementAnalysisRequest $request, RetirementAgent $agent)
    {
        $analysis = $agent->analyze($request->user()->id);

        return response()->json([
            'success' => true,
            'data' => $analysis,
        ]);
    }
}
```

## Key Principles for Development

1. **UK-Specific**: All calculations must follow UK tax rules and regulations (2025/26 tax year as current baseline)
2. **Agent Pattern**: Each module's logic should be encapsulated in an Agent class
3. **Centralized Config**: Never hardcode tax rates - always reference centralized config
4. **No Financial Advice Disclaimer**: FPS is for demonstration/analysis purposes only, not regulated financial advice
5. **Data Isolation**: Users can only access their own data (authorization checks)
6. **Progressive Disclosure**: Show summaries first, details on demand
7. **Mobile-First**: Responsive design from 320px to 2560px
8. **Caching**: Cache expensive calculations (especially Monte Carlo)
9. **Background Jobs**: Long-running calculations (>2 seconds) should be queued
10. **Testing**: Write Pest tests for all financial calculations before implementation
11. **Code Quality**: Follow PSR-12 for PHP, Vue.js style guide, and maintain consistent code style throughout
12. **No Fictional Data**: Never auto-populate demo or test data in the database - users must enter their own data

## Constraints & Limitations

- **UK Only**: System designed exclusively for UK tax rules and products
- **No External Integrations**: No bank/platform APIs (manual data entry)
- **DB Pensions**: Captured for projection only - no transfer advice
- **No Real-Time Market Data**: Investment prices are manually entered/updated
- **Demo System**: Not a production financial advisory tool

## Future Roadmap (Out of Scope for Initial Build)

- Bank/platform integrations for automated data import
- Real-time market data feeds
- Multi-user/advisor accounts
- Native mobile apps
- AI chatbot interface
- International tax jurisdictions
