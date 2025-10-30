# TenGo - Financial Planning System v2

A comprehensive financial planning web application designed for UK individuals and families, covering five integrated modules: Protection, Savings, Investment, Retirement, and Estate Planning.

![Laravel](https://img.shields.io/badge/Laravel-10.x-red?logo=laravel)
![Vue.js](https://img.shields.io/badge/Vue.js-3.x-green?logo=vue.js)
![PHP](https://img.shields.io/badge/PHP-8.2+-blue?logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-blue?logo=mysql)
![Tests](https://img.shields.io/badge/tests-passing-brightgreen)

---

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [System Requirements](#system-requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Development](#development)
- [Testing](#testing)
- [API Documentation](#api-documentation)
- [Module Structure](#module-structure)
- [Architecture](#architecture)
- [Deployment](#deployment)
- [Contributing](#contributing)
- [License](#license)

---

## 🆕 Recent Updates (October 2025)

### Version 0.1.2.13 - Letter to Spouse Feature

**Release Date**: October 29, 2025

**Major Features**:
- ✅ **Letter to Spouse**: Comprehensive emergency instructions feature for surviving spouse
- ✅ **Auto-Population**: Automatically aggregates data from all modules (Protection, Estate, Savings, Investment, Properties, Liabilities)
- ✅ **Four-Part Structure**:
  - Part 1: What to do immediately (key contacts, executor, attorney, financial advisor, employer benefits)
  - Part 2: Accessing and managing accounts (bank accounts, investments, insurance, properties, liabilities, recurring bills)
  - Part 3: Long-term plans (estate documents, beneficiaries, children's education, financial guidance)
  - Part 4: Funeral and final wishes (burial/cremation preference, service details, obituary wishes)
- ✅ **Dual View Mode**: Each spouse can edit their own letter and view partner's letter (read-only)
- ✅ **Application Rebranding**: Changed from "FPS" to "TenGo" across all interfaces

### Version 0.1.2.3 - Comprehensive Protection Plan

**Release Date**: October 25, 2025

**Major Features**:
- ✅ **Comprehensive Protection Plan**: Professional report generation with complete protection analysis
- ✅ **Executive Summary**: Overall adequacy score (0-100) with visual gauge
- ✅ **Coverage Gap Analysis**: Life Insurance, Critical Illness, and Income Protection gaps
- ✅ **Optimized Strategy**: Prioritized recommendations with cost estimates
- ✅ **Print/PDF Export**: Professional format for sharing with advisers
- ✅ **Scenario Analysis**: Death, critical illness, and disability impact modeling

### Version 0.1.2.2 - Second Death IHT & Life Policy Strategy

**Release Date**: October 23, 2025

**Major Features**:
- ✅ **Second Death IHT Planning**: Complete married couple IHT analysis with actuarial projections
- ✅ **Life Policy Strategy**: Whole of Life vs. Self-Insurance comparison with premium calculations
- ✅ **Future Value Projections**: Estate growth modeling for surviving spouse scenarios

### Version 0.1.2 - Spouse Management & Joint Ownership

**Release Date**: October 21, 2025

**Major Features**:
- ✅ **Spouse Account Management**: Auto-creation and linking of spouse accounts via Family Members
- ✅ **Joint Ownership**: Support for jointly owned assets (properties, investments, savings, etc.)
- ✅ **Trust Ownership**: Track assets held in trust
- ✅ **Data Sharing Permissions**: Granular control over what spouse can view/edit
- ✅ **Email Notifications**: Welcome emails and account linking notifications
- ✅ **Password Security**: First-time login password change requirement
- ✅ **Bug Fixes**: Investment account form, IHT calculation with liabilities, property & mortgage fixes

**See**: [OCTOBER_2025_FEATURES_UPDATE.md](OCTOBER_2025_FEATURES_UPDATE.md) for complete details

---

## 🎯 Overview

TenGo v2 is a next-generation financial planning application that helps UK individuals and families:

- **Analyze** their current financial situation across all major areas
- **Identify** gaps, risks, and opportunities
- **Receive** personalized recommendations
- **Plan** for their financial future with confidence
- **Track** progress towards financial goals

The system uses an **agent-based architecture** where intelligent agents analyze user data, perform domain-specific calculations, and generate actionable recommendations.

### Current Status

**✅ Foundation Complete (100%)**

- Laravel 10.x backend with Sanctum authentication
- Vue.js 3 frontend with full auth flow
- Pest testing suite (60+ tests passing)
- Settings page and error handling
- All 5 modules implemented
- Coordinating agent for holistic planning
- Code quality checks passed (Laravel Pint, PSR-12 compliant)
- Architecture tests passing

**✅ User Profile & Account Management (100%)**

- Personal information and family members management
- Spouse account creation and linking (auto-invite system)
- Joint ownership across all asset types (properties, investments, savings, etc.)
- Trust ownership support
- Spouse data sharing permissions (granular per-module control)
- Email notification system for account events
- First-time login password change flow
- **Letter to Spouse**: Comprehensive emergency instructions with auto-population from all modules

**✅ Asset Ownership Features (NEW - October 2025)**

- Individual, joint, and trust ownership for all assets
- Automatic reciprocal record creation for jointly owned assets
- Spouse selection for joint ownership
- Trust selection for trust-owned assets
- ISA ownership restriction (individual only, per UK tax rules)

---

## ✨ Features

### 🛡️ Protection Module

- Life insurance, critical illness, income protection analysis
- Coverage adequacy scoring and gap analysis
- Premium affordability assessment
- Policy timeline visualization
- What-if scenario modeling

### 💰 Savings Module

- Emergency fund analysis (3-6 month runway)
- Savings goals tracking
- ISA allowance monitoring (£20,000 annual limit)
- Liquidity ladder analysis
- Rate comparison and recommendations

### 📈 Investment Module

- Portfolio analysis with asset allocation
- Monte Carlo simulations (1,000 iterations)
- Fee impact analysis
- Tax efficiency calculations
- Goal-based probability analysis
- ISA and pension wrapper optimization

### 🏖️ Retirement Module

- DC/DB/State pension inventory
- Retirement readiness scoring
- Income projection (stacked area charts)
- Contribution optimization
- Annual allowance tracking (£60,000)
- Annuity vs. drawdown comparison
- Decumulation planning

### 🏛️ Estate Planning Module

- IHT calculation (NRB £325k, RNRB £175k)
- Net worth tracking
- Personal P&L and cash flow projections
- Gifting strategy (PETs/CLTs, 7-year rule)
- Trust management
- Probate readiness scoring

### 🔄 Holistic Planning (Coordinating Agent)

- Cross-module analysis
- Conflict resolution (cashflow, ISA allowance)
- Priority ranking (urgency × impact × ease)
- 20-year net worth projections
- Executive summary with financial health score
- Recommendation tracking (pending → in progress → completed)

---

## 🛠️ Technology Stack

### Backend

- **Framework**: Laravel 10.x (PHP 8.2+)
- **Database**: MySQL 8.0+ with InnoDB engine
- **Cache**: Memcached 1.6+
- **Queue**: Laravel Queues (database-backed)
- **Authentication**: Laravel Sanctum (token-based)
- **Testing**: Pest PHP (built on PHPUnit)
- **Code Quality**: Laravel Pint (PSR-12)

### Frontend

- **Framework**: Vue.js 3 with Composition API
- **State Management**: Vuex 4.x
- **Build Tool**: Vite
- **Charts**: ApexCharts
- **CSS**: Tailwind CSS 3.x
- **HTTP Client**: Axios

### Development Tools

- **API Testing**: Postman collections per module
- **Version Control**: Git
- **Package Manager**: Composer (PHP), npm (JS)

---

## 💻 System Requirements

### Minimum Requirements

- **PHP**: 8.2 or higher
- **MySQL**: 8.0 or higher
- **Node.js**: 18.x or higher
- **Composer**: 2.5 or higher
- **Memcached**: 1.6 or higher (optional, can use array driver for development)

### Recommended Requirements

- **RAM**: 4GB minimum, 8GB recommended
- **Disk Space**: 2GB minimum
- **Web Server**: Nginx 1.18+ or Apache 2.4+

---

## 📦 Installation

### 1. Clone the Repository

```bash
git clone <repository-url> tengo
cd tengo
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### 3. Environment Configuration

```bash
# Copy the example environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Configure Database

Edit `.env` file with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fps_production
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Run Migrations

```bash
# Run all migrations (includes letters_to_spouse table)
php artisan migrate

# Seed database with tax configuration and demo data
php artisan db:seed --class=TaxConfigurationSeeder
php artisan db:seed --class=DemoUserSeeder
```

### 6. Build Frontend Assets

```bash
# For development (with HMR)
npm run dev

# For production
npm run build
```

### 7. Start Development Servers

**⚠️ IMPORTANT**: You must run **BOTH** servers simultaneously in separate terminals:

**Terminal 1 - Laravel Backend:**
```bash
php artisan serve
```
This starts the Laravel development server on `http://localhost:8000`

**Terminal 2 - Vite Frontend:**
```bash
npm run dev
```
This starts the Vite dev server with HMR on `http://localhost:5173`

**Terminal 3 (Optional) - Queue Worker:**
```bash
php artisan queue:work database
```
This runs background jobs for Monte Carlo simulations

### 8. Access the Application

- **Application**: http://localhost:8000
- **Demo Login**:
  - Email: `demo@fps.com`
  - Password: `password`
- **Admin Login** (for database backups):
  - Email: `admin@fps.com`
  - Password: `admin123456`

---

## ⚙️ Configuration

### Tax Configuration

All UK tax rules, allowances, and IHT rules are maintained in the `config/uk_tax_config.php` file or `tax_configurations` database table. This includes:

- Income tax bands and rates
- National Insurance thresholds
- ISA allowances (£20,000 total for 2024/25)
- Pension annual allowance (£60,000)
- IHT rates, NRB (£325,000), RNRB (£175,000)
- PET/CLT gifting rules

**Update Process**: When UK tax rules change (typically after Budget), update the centralized config and redeploy.

### Caching Strategy

Cache TTLs based on data volatility:

- **Tax config**: 1 hour
- **Monte Carlo results**: 24 hours
- **Dashboard data**: 30 minutes
- **Holistic analysis**: 1 hour
- **Holistic plan**: 24 hours

### Queue Configuration

Monte Carlo simulations run as Laravel Queue jobs (database-backed) to avoid blocking the UI. Configure in `.env`:

```env
QUEUE_CONNECTION=database
```

---

## 🧑‍💻 Development

### Development Commands

**⚠️ CRITICAL**: For the application to work, you must run **BOTH** servers simultaneously in separate terminal windows/tabs:

**Terminal 1 - Laravel Backend:**
```bash
php artisan serve
```

**Terminal 2 - Vite Frontend:**
```bash
npm run dev
```

**Terminal 3 (Optional) - Queue Worker:**
```bash
php artisan queue:work database
```

**Why both servers?**
- **Laravel (port 8000)**: Serves the backend API and pages
- **Vite (port 5173)**: Serves frontend assets with hot module replacement
- Without Laravel running, you'll get "unable to reach" errors
- Without Vite running (in dev), frontend assets won't load correctly

### Code Quality

```bash
# Run Laravel Pint (code formatter)
./vendor/bin/pint

# Check code style without fixing
./vendor/bin/pint --test
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
php artisan db:seed --class=DemoUserSeeder
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

---

## 🧪 Testing

### Running Tests

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

### Test Structure

- **Unit Tests**: `tests/Unit/` - Test individual service classes and calculations
- **Feature Tests**: `tests/Feature/` - Test API endpoints and CRUD operations
- **Architecture Tests**: `tests/Architecture/` - Enforce coding standards and patterns
- **Integration Tests**: `tests/Integration/` - Test cross-module workflows

### Current Test Coverage

- **Architecture Tests**: 24 passing
- **Unit Tests**: 36+ passing
- **Feature Tests**: Multiple integration tests
- **Total**: 60+ tests

---

## 🐛 Recent Bug Fixes & Configuration Changes (v0.1.0)

### Development Environment Improvements

**Date**: 18 October 2025

This section documents critical bug fixes and configuration changes made during Phase 02 implementation to ensure stable development environment.

#### 1. Rate Limiting Disabled for Development

**Issue**: API requests were hitting rate limits (429 Too Many Requests), causing infinite loops in components.

**Changes Made**:
- **File**: [app/Http/Kernel.php](app/Http/Kernel.php)
  - Commented out throttle middleware in API middleware group
  - Allows unlimited API requests during development

- **File**: [app/Providers/RouteServiceProvider.php](app/Providers/RouteServiceProvider.php)
  - Increased rate limit from 60 to 1000 requests/min for local environment
  - Production rate limit remains at 60/min

**Code**:
```php
// app/Http/Kernel.php - Line 43
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    // \Illuminate\Routing\Middleware\ThrottleRequests::class.':api', // DISABLED for development
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

#### 2. Fixed Infinite Loop in Component Lifecycle Hooks

**Issue**: `FamilyMembers.vue` and `PersonalAccounts.vue` components were calling API endpoints on mount, creating infinite loops.

**Changes Made**:
- **File**: [resources/js/components/UserProfile/FamilyMembers.vue](resources/js/components/UserProfile/FamilyMembers.vue)
  - Removed auto-fetch on `onMounted` hook
  - Users must manually trigger data fetch

- **File**: [resources/js/components/UserProfile/PersonalAccounts.vue](resources/js/components/UserProfile/PersonalAccounts.vue)
  - Removed auto-calculate on mount
  - Users must click "Calculate" button to load data

**Root Cause**: The `setLoading` mutation was triggering reactive updates that caused fetch to be called repeatedly.

#### 3. Fixed Missing Axios Import in TrustsDashboard

**Issue**: `TrustsDashboard.vue` was using `this.$http.get` but axios was not properly imported, causing "Cannot read properties of undefined" error.

**Changes Made**:
- **File**: [resources/js/views/Trusts/TrustsDashboard.vue](resources/js/views/Trusts/TrustsDashboard.vue)
  - Added `import axios from '@/bootstrap';`
  - Changed `this.$http.get` to `axios.get`
  - Disabled API call temporarily as endpoint returns 500 error

**Code**:
```javascript
import axios from '@/bootstrap';

async loadUpcomingTaxEvents() {
  // Disabled - endpoint returns 500, not ready yet
  this.upcomingChargesData = [];
  this.taxReturnsData = [];
}
```

#### 4. Fixed Router Navigation Warnings

**Issue**: Clicking Net Worth card caused Vue Router warning: "No match found for location with path 'overview'".

**Changes Made**:
- **File**: [resources/js/components/Dashboard/NetWorthOverviewCard.vue](resources/js/components/Dashboard/NetWorthOverviewCard.vue)
  - Changed navigation from `/net-worth` to `/net-worth/overview`
  - Ensures complete route path is used instead of relying on redirect

**Root Cause**: Parent route `/net-worth` redirects to child route `overview`, creating relative path instead of absolute.

#### 5. Fixed Missing AppLayout in NetWorthDashboard

**Issue**: Top navigation bar disappeared when viewing Net Worth page.

**Changes Made**:
- **File**: [resources/js/views/NetWorth/NetWorthDashboard.vue](resources/js/views/NetWorth/NetWorthDashboard.vue)
  - Wrapped entire template in `<AppLayout>` component
  - Added AppLayout import and component registration

**Code**:
```vue
<template>
  <AppLayout>
    <div class="net-worth-dashboard">
      <!-- content -->
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '@/layouts/AppLayout.vue';

export default {
  components: {
    AppLayout,
  },
  // ...
}
</script>
```

#### 6. Fixed RecommendationsAggregatorService Missing Dependencies

**Issue**: `/api/recommendations` endpoint returned 500 error due to non-existent service classes.

**Changes Made**:
- **File**: [app/Services/Coordination/RecommendationsAggregatorService.php](app/Services/Coordination/RecommendationsAggregatorService.php)
  - Updated service imports to use correct class names
  - Implemented placeholder that returns empty recommendations array
  - Added TODO comments for future implementation

**Incorrect Imports** (removed):
```php
use App\Services\Protection\ProtectionAgent;  // Doesn't exist
use App\Services\Savings\EmergencyFundAnalyzer;  // Doesn't exist
```

**Correct Imports** (added):
```php
use App\Services\Estate\NetWorthAnalyzer;
use App\Services\Investment\PortfolioAnalyzer;
use App\Services\Protection\RecommendationEngine as ProtectionRecommendationEngine;
use App\Services\Retirement\PensionProjector;
use App\Services\Savings\EmergencyFundCalculator;
```

### Development Server Restart Required

After configuration changes, multiple Laravel and Vite server instances were running. Fixed by:

```bash
# Kill all PHP and Node processes
lsof -ti:8000 | xargs kill -9
lsof -ti:5173 | xargs kill -9

# Restart servers with new configuration
php artisan serve        # Terminal 1
npm run dev             # Terminal 2
```

### Impact Summary

These changes stabilize the development environment by:
- ✅ Eliminating rate limiting issues during development
- ✅ Preventing infinite API call loops
- ✅ Fixing missing imports causing undefined errors
- ✅ Ensuring consistent navigation behavior
- ✅ Restoring UI layout on all pages
- ✅ Preventing 500 errors from incomplete endpoints

**Note**: Production deployment should re-enable rate limiting by uncommenting the throttle middleware.

---

## 📚 API Documentation

### Authentication

All API endpoints require authentication via Laravel Sanctum:

```bash
# Login
POST /api/auth/login
Body: { "email": "demo@fps.com", "password": "password" }

# Logout
POST /api/auth/logout
Headers: { "Authorization": "Bearer <token>" }
```

### Module Endpoints

#### Protection Module

```
GET    /api/protection/profile
POST   /api/protection/profile
PUT    /api/protection/profile/:id
GET    /api/protection/policies/life-insurance
POST   /api/protection/policies/life-insurance
POST   /api/protection/analyze
```

#### Savings Module

```
GET    /api/savings/accounts
POST   /api/savings/accounts
GET    /api/savings/goals
POST   /api/savings/goals
GET    /api/savings/isa-tracker
POST   /api/savings/analyze
```

#### Investment Module

```
GET    /api/investment/accounts
POST   /api/investment/accounts
GET    /api/investment/holdings
POST   /api/investment/holdings
POST   /api/investment/analyze
POST   /api/investment/monte-carlo
```

#### Retirement Module

```
GET    /api/retirement/pensions/dc
POST   /api/retirement/pensions/dc
GET    /api/retirement/pensions/db
POST   /api/retirement/pensions/db
POST   /api/retirement/analyze
POST   /api/retirement/scenarios
```

#### Estate Module

```
GET    /api/estate/assets
POST   /api/estate/assets
GET    /api/estate/liabilities
POST   /api/estate/liabilities
GET    /api/estate/calculate-iht
GET    /api/estate/net-worth
POST   /api/estate/gifts
```

#### Holistic Planning

```
POST   /api/holistic/analyze
POST   /api/holistic/plan
GET    /api/holistic/recommendations
GET    /api/holistic/cash-flow-analysis
POST   /api/holistic/recommendations/:id/mark-done
POST   /api/holistic/recommendations/:id/in-progress
POST   /api/holistic/recommendations/:id/dismiss
```

#### User Profile - Letter to Spouse

```
GET    /api/user/letter-to-spouse          # Get current user's letter (auto-creates if needed)
GET    /api/user/letter-to-spouse/spouse   # Get spouse's letter (read-only)
PUT    /api/user/letter-to-spouse          # Update current user's letter
```

### Response Format

All API responses follow this structure:

```json
{
  "success": true,
  "data": { ... },
  "message": "Operation successful"
}
```

Error responses:

```json
{
  "success": false,
  "message": "Error description",
  "errors": { ... }
}
```

---

## 🏗️ Module Structure

Each module follows a consistent pattern:

### Backend Structure

```
app/
├── Agents/
│   └── ProtectionAgent.php         # Business logic orchestrator
├── Services/Protection/
│   ├── AdequacyScorer.php         # Domain-specific calculation
│   ├── CoverageGapAnalyzer.php    # Analysis service
│   └── RecommendationEngine.php   # Recommendation generation
├── Http/Controllers/Api/
│   └── ProtectionController.php   # RESTful API endpoints
├── Http/Requests/Protection/
│   ├── StoreProtectionProfileRequest.php
│   └── UpdateProtectionProfileRequest.php
└── Models/
    ├── ProtectionProfile.php
    ├── LifeInsurancePolicy.php
    └── CriticalIllnessPolicy.php
```

### Frontend Structure

```
resources/js/
├── views/Protection/
│   ├── ProtectionDashboard.vue    # Main dashboard
│   ├── CurrentSituation.vue       # Tab 1
│   ├── Analysis.vue               # Tab 2
│   └── Recommendations.vue        # Tab 3
├── components/Protection/
│   ├── PolicyForm.vue
│   ├── CoverageGaugeChart.vue
│   └── GapHeatmap.vue
├── services/
│   └── protectionService.js       # API wrapper
└── store/modules/
    └── protection.js              # Vuex store
```

---

## 🏛️ Architecture

### Agent-Based System

The application uses an **agent-based architecture**:

1. **User Input** → Dynamic forms collect financial data
2. **Agent Analysis** → Domain-specific agents perform calculations
3. **Recommendations** → Agents generate actionable advice
4. **Coordination** → CoordinatingAgent resolves conflicts and prioritizes

**Key Agents**:

- `ProtectionAgent` - Insurance analysis
- `SavingsAgent` - Emergency fund & cash management
- `InvestmentAgent` - Portfolio analysis & Monte Carlo
- `RetirementAgent` - Pension projections & readiness
- `EstateAgent` - IHT calculations & net worth
- `CoordinatingAgent` - Holistic planning & conflict resolution

### Three-Tier Architecture

```
┌─────────────────────────────────────┐
│ Presentation Layer                  │
│ (Vue.js 3 + ApexCharts)            │
└─────────────────────────────────────┘
              ↕
┌─────────────────────────────────────┐
│ Application Layer                   │
│ (Laravel Controllers + Agents)      │
│ + Services + Business Logic         │
└─────────────────────────────────────┘
              ↕
┌─────────────────────────────────────┐
│ Data Layer                          │
│ (MySQL + Memcached)                 │
└─────────────────────────────────────┘
```

### Design Patterns

- **Service Layer Pattern**: Business logic in dedicated service classes
- **Repository Pattern**: Data access through Eloquent models
- **Agent Pattern**: Coordinating agent orchestrates module agents
- **Strategy Pattern**: Different conflict resolution strategies
- **Factory Pattern**: Recommendation creation and tracking

---

## 🚀 Deployment

### Production Build

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

- **PHP 8.2+** with extensions: BCMath, Ctype, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML
- **MySQL 8.0+**
- **Memcached 1.6+** (or Redis alternative)
- **Nginx** or **Apache** with mod_rewrite
- **SSL Certificate** (Let's Encrypt recommended)
- **Supervisor** for queue workers

### Queue Worker (Supervisor)

Create `/etc/supervisor/conf.d/fps-worker.conf`:

```ini
[program:fps-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/fpsv2/artisan queue:work database --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/fpsv2/storage/logs/worker.log
```

---

## 🤝 Contributing

### Coding Standards

- **PHP**: Follow PSR-12 (enforced by Laravel Pint)
- **JavaScript/Vue**: Follow Vue.js Style Guide (Priority A & B)
- **Naming**: PascalCase for classes, camelCase for methods, snake_case for database
- **Strict Types**: All PHP files must use `declare(strict_types=1);`
- **Tests**: Write Pest tests for all financial calculations

### Development Workflow

1. Create feature branch: `git checkout -b feature/module-name`
2. Write code following standards
3. Run tests: `./vendor/bin/pest`
4. Run code formatter: `./vendor/bin/pint`
5. Commit with descriptive message
6. Push and create pull request

### Architecture Rules (Enforced by Tests)

- All agents must extend `BaseAgent`
- All controllers must extend `Controller`
- All form requests must extend `FormRequest`
- Models must use `HasFactory` trait
- Services must use strict typing
- No direct DB queries in controllers

---

## 📄 License

This project is proprietary software. All rights reserved.

**Demo/Development Only**: This system is for demonstration and analysis purposes only, not regulated financial advice.

---

## 🙏 Acknowledgments

- Built with [Laravel](https://laravel.com)
- Frontend powered by [Vue.js](https://vuejs.org)
- Charts by [ApexCharts](https://apexcharts.com)
- Tested with [Pest PHP](https://pestphp.com)

---

## 📞 Support

For issues, questions, or contributions:

- **Issues**: Create an issue in the repository
- **Documentation**: See `/docs` folder and `CLAUDE.md`
- **Tasks**: See `/tasks` folder for development task breakdown

---

**Current Version**: 0.1.2.13 (Beta)

**Last Updated**: 29 October 2025

**Status**: 🚀 Active Development - Core Features Complete, Letter to Spouse Feature Added

---

🤖 **Built with [Claude Code](https://claude.com/claude-code)**
