# CLAUDE.md

This file provides guidance to Claude Code when working with this repository.

---

## ⚠️ CRITICAL INSTRUCTION - MAINTAIN APPLICATION FUNCTIONALITY

**THE APPLICATION MUST REMAIN FULLY FUNCTIONAL AT ALL TIMES.**

1. **DO NOT REMOVE** existing features, functionality, pages, views, components, or working code
2. **ONLY WORK** on the specific section explicitly being modified
3. **DO NOT MODIFY** other areas unless absolutely necessary
4. **IF OTHER AREAS ARE AFFECTED**:
   - STOP and explain what will be impacted
   - ASK PERMISSION before making changes
   - PROVIDE DETAILS about why changes are necessary
   - WAIT FOR APPROVAL before proceeding
5. **BEFORE MAKING CHANGES**:
   - Understand full scope of impact
   - Identify all files/components affected
   - Consider backward compatibility
   - Ensure tests will still pass

---

## ⚠️ CRITICAL INSTRUCTION - DATABASE BACKUP PROTOCOL

**ALWAYS CHECK FOR AND MAINTAIN DATABASE BACKUPS BEFORE ANY DESTRUCTIVE OPERATIONS.**

### Key Requirements:
1. **BEFORE Database Wipe**: Create backup via admin panel, verify it exists in `storage/app/backups/`
2. **NEVER** run `migrate:fresh` or `migrate:refresh` without explicit user approval
3. **Admin Account**: `admin@fps.com` / `admin123456` (ID: 1016)
4. **If Admin Lost**: Recreate via `php artisan tinker` with script

### Commands to AVOID Without Backup:
- ❌ `php artisan migrate:fresh`, `migrate:refresh`, `db:wipe`
- ❌ `php artisan migrate:rollback` (except single recent migration)
- ❌ Manual SQL `DROP DATABASE` or `TRUNCATE`

### Safe Commands:
- ✅ `php artisan migrate` (forward only)
- ✅ Admin panel backup/restore system

---

## ⚠️ CRITICAL INSTRUCTION - ALWAYS CHECK FOR RELEVANT SKILLS

**BEFORE STARTING ANY TASK, CHECK IF THERE IS A RELEVANT SKILL AVAILABLE.**

### Available Skills:
1. **systematic-debugging** - For ALL bug reports, unexpected behavior, troubleshooting
2. **fps-module-builder** - For creating new FPS modules (full-stack)
3. **fps-feature-builder** - For adding/extending features in existing modules
4. **fps-component-builder** - For creating Vue 3 components

**FAILURE TO USE AVAILABLE SKILLS IS UNACCEPTABLE.**

---

## Project Overview

**FPS (Financial Planning System)** - UK-focused comprehensive financial planning app with five integrated modules: Protection, Savings, Investment, Retirement, and Estate Planning.

**Current Version**: v0.1.2.2
- Laravel 10.x + Sanctum auth
- Vue.js 3 + Vuex + Vite + ApexCharts + Tailwind CSS
- Pest testing suite
- Full auth flow, User Profile, Settings, Admin Panel
- All five modules operational with spouse management, joint/trust ownership
- Second death IHT planning with cross-module data integration

## Architecture

### Agent-Based System
Each module has an agent that takes inputs, performs calculations, generates recommendations, and returns structured outputs.

**Agents**: ProtectionAgent, SavingsAgent, InvestmentAgent, RetirementAgent, EstateAgent

### Three-Tier Architecture
```
Vue.js 3 (Presentation) ↔ Laravel Controllers + Agents + Services (Application) ↔ MySQL + Memcached (Data)
```

### Project Structure
- **Backend**: `app/Agents/`, `app/Http/Controllers/Api/`, `app/Services/`, `app/Models/`
- **Frontend**: `resources/js/components/`, `resources/js/views/`, `resources/js/store/`, `resources/js/services/`
- **API**: RESTful, `/api` prefix, Sanctum token auth

## Key Implementation Details

### Centralized Tax Configuration
All UK tax rules in `config/uk_tax_config.php` or `tax_configurations` table:
- Income tax bands, NI thresholds
- ISA allowances (£20,000 for 2025/26)
- Pension annual allowance (£60,000)
- IHT: NRB £325k, RNRB £175k, 40% rate
- PET/CLT gifting rules

### ISA Allowance Tracking
Cross-module tracking (Savings + Investment):
- Total £20k/year, tracked via `ISATracker.php` service
- Cash ISA in Savings, S&S ISA in Investment
- Tax year: April 6 - April 5

### UK Tax Calculator
`app/Services/UKTaxCalculator.php` calculates net income after tax/NI for all income types (employment, self-employment, rental, dividend). Used by ProtectionAgent for human capital valuation.

### Background Jobs & Caching
- Monte Carlo simulations: Laravel Queue jobs (database-backed)
- Memcached TTLs: Tax config (1h), Monte Carlo (24h), Dashboard (30m)

## Module Summary

### Protection
Life/critical illness/income protection analysis. Uses NET income for human capital calculation. Distinguishes earned income (stops on death) vs. continuing income (rental/dividend).

### Savings
Emergency fund tracking (designated accounts), savings goals, ISA allowance monitoring. Runway = Emergency fund ÷ monthly expenditure.

### Investment
Portfolio analysis, asset allocation, Monte Carlo projections, fee analysis.

### Retirement
DC/DB/State pension inventory, contribution tracking, annual allowance monitoring, retirement readiness score.

### Estate Planning
IHT calculation, net worth tracking, property management, gifting strategy (PETs/CLTs), will planning, second death IHT planning for married couples with actuarial projections and automated gifting strategies.

## Asset Ownership & Spouse Management

### Ownership Types
All assets support: **Individual**, **Joint**, **Trust**

**Database Pattern**:
```php
$table->enum('ownership_type', ['individual', 'joint', 'trust'])->default('individual');
$table->bigInteger('joint_owner_id')->nullable();
$table->foreignId('trust_id')->nullable();
```

**CRITICAL**: Use 'individual' NOT 'sole' in forms. ISAs MUST be 'individual' only (UK rule).

### Spouse Account Management
- **Auto-Creation**: New email → creates account with random password, sends welcome email
- **Account Linking**: Existing email → links accounts bidirectionally, sets `marital_status = 'married'`
- **Permissions**: Granular view/edit permissions via `spouse_permissions` table

### Joint Ownership Pattern
User creates asset with `joint_owner_id` → backend auto-creates reciprocal record → both users see asset.

## Important UK Rules

**IHT**: 40% rate, NRB £325k, RNRB £175k (both transferable), PETs 7-year rule with taper relief, CLTs 14-year lookback

**Pensions**: Annual allowance £60k (tapered for high earners), 3-year carry forward, MPAA £10k

**ISAs**: £20k/year total allowance, LISA £4k (counts towards total)

## Dashboard & Charts

**Dashboard Pattern**: Main dashboard card (summary) → Detailed module dashboard with tabs (Current Situation, Analysis, Recommendations, What-If, Details)

**ApexCharts**: Radial bars (gauges), donuts (allocation), lines (performance), areas (projections), heatmaps (gaps), rangeBar (timelines), bars (waterfalls)

**Color Scheme**: Traffic light (green=good, amber=caution, red=critical)

## Development Workflow

### Setup
```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate && php artisan db:seed --class=TaxConfigurationSeeder
```

### Dev Servers
```bash
php artisan serve           # Laravel (port 8000)
npm run dev                 # Vite HMR
php artisan queue:work      # Queue worker (if needed)
```

### Testing
```bash
./vendor/bin/pest                    # All backend tests
./vendor/bin/pest --testsuite=Unit   # Unit tests only
npm run test                         # Frontend tests
```

### Database
```bash
php artisan migrate              # Forward migrations
php artisan migrate:rollback     # Rollback last (safe)
# AVOID: migrate:fresh, migrate:refresh, db:wipe without backup
```

### Production
```bash
composer install --optimize-autoloader --no-dev
npm run build
php artisan config:cache && php artisan route:cache
```

## Coding Standards

### PHP (PSR-12)
- **Naming**: PascalCase (classes), camelCase (methods/properties), snake_case (DB)
- **Formatting**: 4 spaces indent, 120 char line limit, braces same line for methods
- **Structure**: Unix LF, omit closing `?>`, declare types
- **Laravel**: Thin controllers, Form Requests for validation, Eloquent relationships, agents extend BaseAgent

### MySQL 8.0+
- **Naming**: snake_case for tables/columns, `id` for PK, `{table}_id` for FK
- **Schema**: InnoDB engine, index all FKs, `DECIMAL(15,2)` for currency, `DECIMAL(5,4)` for rates
- **Standards**: UPPERCASE keywords, prepared statements (Eloquent handles this)

### Vue.js 3
- **Naming**: Multi-word components, PascalCase in SFC, kebab-case in templates
- **Structure**: Data as function, detailed props with types/validators
- **Templates**: Always use `:key` with `v-for`, never `v-if` with `v-for`, use shorthands
- **Component Order**: name → components → props → data → computed → methods → lifecycle hooks

**CRITICAL - Form Modal Event Naming**:
**NEVER use `@submit` for custom events** - causes double submissions with native form submit.

**Correct Pattern**:
```vue
<!-- Modal Component -->
<form @submit.prevent="submitForm">
  <!-- fields -->
</form>
<script>
methods: {
  submitForm() {
    this.$emit('save', formData);  // Use 'save' NOT 'submit'
  }
}
</script>

<!-- Parent Component -->
<FormModal @save="handleSubmit" @close="closeModal" />
```

### JavaScript
- Use `const` by default, `let` when needed, never `var`
- Arrow functions for callbacks, template literals, destructuring, async/await
- Meaningful variable names

## Key Development Principles

1. **UK-Specific**: Follow UK tax rules (2025/26 baseline)
2. **Agent Pattern**: Encapsulate module logic in Agent classes
3. **Centralized Config**: Never hardcode tax rates
4. **Data Isolation**: Users only access their own data
5. **Progressive Disclosure**: Summaries first, details on demand
6. **Mobile-First**: Responsive 320px to 2560px
7. **Caching**: Cache expensive calculations
8. **Testing**: Pest tests for all financial calculations
9. **No Fictional Data**: Never auto-populate demo data

## Constraints

- UK only (no international tax jurisdictions)
- No external integrations (manual data entry)
- DB pensions for projection only (no transfer advice)
- Demo system (not production financial advisory tool)

## Important Documentation

- **FPS_PRD.md**: Product requirements, user stories
- **FPS_Features_TechStack.md**: Technical implementation, API specs, schemas
- **designStyleGuide.md**: UI/UX standards, components
- **OCTOBER_2025_FEATURES_UPDATE.md**: v0.1.2 features (spouse, joint ownership, trust)
- **Mermaid Diagrams**: `fpflow.md`, `protectionflow.md`, `investmentflow.md`, `retirementflow.md`, `estateflow.md`
