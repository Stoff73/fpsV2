# TenGo - UK Financial Planning System

A comprehensive financial planning web application designed for UK individuals and families, covering five integrated modules: Protection, Savings, Investment, Retirement, and Estate Planning.

![Laravel](https://img.shields.io/badge/Laravel-10.x-red?logo=laravel)
![Vue.js](https://img.shields.io/badge/Vue.js-3.x-green?logo=vue.js)
![PHP](https://img.shields.io/badge/PHP-8.2+-blue?logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-blue?logo=mysql)
![Tests](https://img.shields.io/badge/tests-passing-brightgreen)

---

## 📋 Table of Contents

- [Overview](#overview)
- [Core Features](#core-features)
- [Module Features](#module-features)
- [Technology Stack](#technology-stack)
- [Installation](#installation)
- [Development](#development)
- [Testing](#testing)
- [Deployment](#deployment)
- [Documentation](#documentation)

---

## 🎯 Overview

**TenGo** is a UK-focused comprehensive financial planning application that helps individuals and families:

- **Analyze** their current financial situation across all major areas
- **Identify** gaps, risks, and opportunities
- **Plan** for their financial future with confidence
- **Track** progress towards financial goals
- **Generate** professional reports and recommendations

### Current Status

**Version**: v0.2.6 (Beta - Production Ready)

**Completion Status**:
- ✅ **Foundation**: 100% (Authentication, routing, testing framework)
- ✅ **Core Modules**: 100% (All 5 modules fully functional)
- ✅ **Advanced Features**: 95% (Portfolio optimization, Monte Carlo simulations, IHT planning)
- ✅ **User Management**: 100% (Spouse accounts, joint ownership, data sharing)
- ✅ **Admin Panel**: 100% (User management, backups, tax configuration)
- ✅ **UI/UX**: Enhanced (Policy detail views, Dashboard Plans card, life policy type tags)

---

## ✨ Core Features

### 🔐 Authentication & User Management

- **Secure Authentication**: Laravel Sanctum token-based authentication
- **User Profiles**: Comprehensive personal and financial information
- **Spouse Accounts**: Auto-creation and linking with bidirectional access
- **Joint Ownership**: Support for jointly owned assets (properties, investments, savings)
- **Trust Ownership**: Track assets held in trust
- **Data Sharing**: Granular permissions for spouse data access
- **Email Notifications**: Welcome emails and account linking notifications
- **Password Security**: First-time login password change requirement

### 🎯 Dashboard

The main dashboard provides a unified view of your financial planning:

- **Net Worth Overview**: Real-time tracking of assets and liabilities
- **Estate Planning Summary**: IHT liability and probate readiness
- **Protection Overview**: Coverage adequacy score and gaps
- **Trusts Overview**: Trust portfolio summary
- **Plans Card**: Quick access to all planning modules
  - ✅ Protection Plan (active)
  - ✅ Estate Plan (active)
  - ✅ Investment & Savings Plan (active)
  - 🔒 Retirement Plan (coming soon)
  - 🔒 Tax Plan (coming soon)
  - 🔒 Financial Plan (coming soon)
- **UK Taxes & Allowances** (Admin only): Current tax year configuration

### 📊 Tax Configuration System

- **Database-Driven**: All UK tax values stored in database, not hardcoded
- **Multi-Year Support**: 6 tax years available (2021/22 through 2025/26)
- **Admin Panel**: Easy tax year switching and value updates
- **Automatic Updates**: All calculations use current active tax year
- **Covers All Taxes**: Income tax, NI, CGT, dividend tax, IHT, stamp duty, ISA allowances, pension allowances

### 💾 Admin Panel

Four comprehensive tabs:

1. **Dashboard**: User statistics, system health, recent activity
2. **User Management**: View all users, manage accounts, impersonate users
3. **Database Backups**: Create, restore, and download database backups
4. **Tax Settings**: Switch tax years, update tax values, view historical data

### 📝 Letter to Spouse

- **Emergency Instructions**: Comprehensive 4-part guide for surviving spouse
- **Auto-Population**: Automatically aggregates data from all modules
- **Part 1**: What to do immediately (contacts, executor, attorney)
- **Part 2**: Accessing accounts (bank, investments, insurance, properties)
- **Part 3**: Long-term plans (estate documents, beneficiaries, education)
- **Part 4**: Funeral and final wishes
- **Dual View**: Each spouse can edit their own letter and view partner's (read-only)

---

## 🏗️ Module Features

### 🛡️ Protection Module

**Purpose**: Analyze life insurance, critical illness, and income protection coverage

**Features**:
- **Policy Portfolio View**: Enhanced card-based display with filtering and sorting
  - Filter by policy type (Life, Critical Illness, Income Protection, etc.)
  - Sort by coverage amount, policy type, or provider
  - Coverage summary tags showing total coverage per type
  - Add new policies directly from portfolio view
- **Policy Detail Pages**: Comprehensive individual policy views
  - Overview tab with key metrics and policy details
  - Coverage details with start date, term, and amounts
  - Premium information with annual cost calculation
  - Life policy type tags (Decreasing Term, Level Term, Whole of Life, etc.)
  - Edit and delete functionality
- **Coverage Gap Analysis**: Compare recommended coverage vs. current coverage
- **Adequacy Scoring**: Overall protection score (0-100) based on 8 metrics
- **Human Capital Calculation**: Lifetime earning potential based on age, income, education
- **Premium Affordability**: Check if premiums exceed 10% of income
- **Professional Reports**: Generate comprehensive Protection Plan with executive summary
- **Policy Timeline**: Visual representation of policy coverage periods
- **Strategy Tab**: Prioritized recommendations with cost estimates

**Life Insurance Policy Types**:
- **Decreasing Term**: Coverage reduces over time (typically for repayment mortgages)
- **Level Term**: Fixed coverage amount for specified term
- **Whole of Life**: Coverage for entire lifetime
- **Term**: Standard term assurance
- **Family Income Benefit**: Regular income payments instead of lump sum

**Calculations**:
- Life insurance coverage: 10-12x annual income + debts
- Critical illness: 3-5x annual income
- Income protection: 50-70% of gross income
- Educational fund needs: £50k per child
- Funeral costs: £5,000
- Emergency fund: 3-6 months expenses

### 💰 Savings Module

**Purpose**: Emergency fund analysis and savings goal tracking

**Features**:
- **Emergency Fund Calculator**: 3-6 month expense runway based on employment status
- **Savings Account Tracking**: Monitor all savings accounts with current balances
- **ISA Allowance Monitoring**: Track usage against £20,000 annual limit (cross-module)
- **Liquidity Ladder**: Categorize savings by access type (immediate, notice, fixed)
- **Savings Goals**: Set and track progress towards specific savings goals
- **Interest Rate Analysis**: Compare rates and identify better opportunities
- **Auto-Saving Recommendations**: Suggest automated savings strategies

**ISA Tracking**:
- Aggregates Cash ISAs from Savings module
- Aggregates Stocks & Shares ISAs from Investment module
- Warns when approaching or exceeding £20,000 limit
- Respects UK tax year (April 6 - April 5)

### 📈 Investment Module

**Purpose**: Portfolio analysis, optimization, and goal-based planning

**Features**:
- **Portfolio Management**: Track investment accounts and holdings
- **Holdings Management**: Add, edit, remove holdings with quantity and value tracking
- **Risk Metrics**: Alpha, Beta, Sharpe Ratio, Volatility, Max Drawdown, VaR (95%)
- **Asset Allocation**: Breakdown by asset class with diversification scoring
- **Monte Carlo Simulations**: 1,000 iterations with 5th, 50th, 95th percentile projections
- **Efficient Frontier**: Optimal risk/return positioning
- **Fee Analysis**: Platform fees, fund OCFs, total cost impact
- **Tax Efficiency**: Analyze tax drag and optimize account types
- **Rebalancing Support**: Compare current vs. target allocation
- **Goal Probability**: Likelihood of reaching investment goals
- **Account Types**: ISA, GIA, NS&I, Onshore/Offshore Bonds, VCT, EIS
- **Investment & Savings Plans**: Consolidated view with risk dashboard

**Advanced Analytics**:
- Portfolio optimization (maximize Sharpe ratio)
- Tax-loss harvesting opportunities
- Low-cost alternative recommendations
- Asset location optimization (tax-efficient wrapper selection)

### 🏖️ Retirement Module

**Purpose**: Pension tracking, projection, and decumulation planning

**Features**:
- **Pension Inventory**: Track DC, DB, and State pensions
- **DC Pension Portfolio Optimization**: Full holdings management and portfolio analysis
- **Portfolio Analysis Tab**: Risk metrics, asset allocation, diversification scoring
- **Holdings Management**: Add, edit, remove pension holdings
- **Advanced Risk Analytics**: Alpha, Beta, Sharpe Ratio for DC pension portfolios
- **Fee Analysis**: Platform fees and fund OCFs breakdown
- **Monte Carlo Integration**: Pension projections with scenario modeling
- **Income Projection**: Stacked area charts showing DC, DB, State pension income
- **Contribution Optimization**: Tax relief calculations and carry forward
- **Annual Allowance Tracking**: £60,000 limit + 3-year carry forward
- **Annuity vs. Drawdown**: Comparison with sustainability modeling
- **Decumulation Planning**: Longevity risk assessment
- **Target Retirement Income**: Set goals and track progress

**Calculations**:
- State pension forecasting (NI record based)
- DB pension income calculation
- DC pension growth projections
- Tax relief on contributions
- Lifetime allowance monitoring
- 4% safe withdrawal rate analysis

### 🏛️ Estate Planning Module

**Purpose**: IHT calculation, net worth tracking, and estate strategy

**Features**:
- **IHT Calculations**: Single and married couple scenarios
- **Net Worth Tracking**: Comprehensive asset and liability tracking
- **Gifting Strategy**: PET and CLT tracking with 7-year taper relief
- **Trust Management**: Track trusts with beneficiary and asset details
- **Will Planning**: Executor details, last review date, will storage location
- **Actuarial Projections**: Life expectancy-based IHT liability forecasting
- **Second Death Analysis**: Surviving spouse IHT planning with combined allowances
- **Life Policy Strategy**: Whole of Life vs. Self-Insurance comparison
- **Property Tracking**: Main residence, secondary residences, buy-to-let
- **Liability Management**: Mortgages, loans, credit cards
- **Asset Valuation**: Properties, pensions, investments, businesses
- **Probate Readiness**: Score based on documentation and planning

**IHT Calculations**:
- **Single Person**: £325,000 NRB + £175,000 RNRB
- **Married Couple (First Death)**: Spouse exemption, preserve allowances
- **Married Couple (Second Death)**: Combined £650,000 NRB + £350,000 RNRB
- **Gifting Rules**: 7-year rule with taper relief (years 3-7)
- **Growth Projections**: Estate growth modeling for future IHT liability

**Net Worth Components**:
- Assets: Properties, pensions, investments, savings, businesses
- Liabilities: Mortgages, loans, credit cards, other debts
- Personal P&L: Income vs. expenditure analysis
- Cash flow projections: 20-year forecasts

---

## 🛠️ Technology Stack

### Backend

- **Framework**: Laravel 10.x (PHP 8.2+)
- **Database**: MySQL 8.0+ with InnoDB engine
- **Cache**: Memcached 1.6+ (configurable to array for development)
- **Queue**: Laravel Queues (database-backed) for Monte Carlo simulations
- **Authentication**: Laravel Sanctum (token-based API authentication)
- **Testing**: Pest PHP (60+ passing tests)
- **Code Quality**: Laravel Pint (PSR-12 compliant)

### Frontend

- **Framework**: Vue.js 3 with Composition API and Options API
- **State Management**: Vuex 4.x (15+ store modules)
- **Build Tool**: Vite (HMR for development)
- **Charts**: ApexCharts (line, area, bar, donut, heatmap, gauge)
- **CSS**: Tailwind CSS 3.x (utility-first)
- **HTTP Client**: Axios
- **Components**: 150+ Vue components
- **Routing**: Vue Router with nested routes

### Architecture

**Three-Tier Architecture**:

```
┌─────────────────────────────────────┐
│ Presentation Layer                  │
│ Vue.js 3 + ApexCharts + Tailwind   │
└─────────────────┬───────────────────┘
                  │ REST API (80+ endpoints)
                  ↓
┌─────────────────────────────────────┐
│ Application Layer                   │
│ Laravel Controllers + 6 Agents      │
│ 40+ Services + Business Logic       │
└─────────────────┬───────────────────┘
                  │ Eloquent ORM
                  ↓
┌─────────────────────────────────────┐
│ Data Layer                          │
│ MySQL 8.0+ (45+ tables)            │
│ Memcached (calculation caching)    │
└─────────────────────────────────────┘
```

**Agent-Based System**:

Each module has an intelligent agent that orchestrates analysis:

- **ProtectionAgent**: Life/CI/IP coverage analysis
- **SavingsAgent**: Emergency fund & ISA tracking
- **InvestmentAgent**: Portfolio analysis & Monte Carlo simulations
- **RetirementAgent**: Pension projections & readiness scoring
- **CoordinatingAgent**: Cross-module holistic planning

**Note**: Estate module uses direct service architecture (EstateAgent deprecated in favor of IHTCalculationService).

### Request Flow

```
Vue Component → JS Service → API Call → Controller → Agent → Services → Models → Database
                                                        ↓
Response ← Store Mutation ← Component ← JSON ← Controller ← Calculation Results
```

---

## 📦 Installation

### System Requirements

- **PHP**: 8.2 or higher
- **MySQL**: 8.0 or higher
- **Node.js**: 18.x or higher
- **Composer**: 2.5 or higher
- **Memcached**: 1.6+ (optional, can use array driver for development)
- **RAM**: 4GB minimum, 8GB recommended

### Installation Steps

1. **Clone Repository**

```bash
git clone <repository-url> tengo
cd tengo
```

2. **Install Dependencies**

```bash
# PHP dependencies
composer install

# JavaScript dependencies
npm install
```

3. **Environment Configuration**

```bash
# Copy example environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

4. **Configure Database**

Edit `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```

5. **Run Migrations & Seeders**

```bash
# Run all migrations
php artisan migrate

# Seed tax configuration (required)
php artisan db:seed --class=TaxConfigurationSeeder

# Seed actuarial life tables (required for Estate module)
php artisan db:seed --class=ActuarialLifeTablesSeeder

# Optional: Seed demo user
php artisan db:seed --class=DemoUserSeeder
```

6. **Build Frontend Assets**

```bash
# For development (with HMR)
npm run dev

# For production
npm run build
```

7. **Access Application**

- **Application URL**: http://localhost:8000
- **Demo Login**: `demo@fps.com` / `password`
- **Admin Login**: `admin@fps.com` / `admin123456`

---

## 🧑‍💻 Development

### Running Development Servers

**⚠️ CRITICAL**: You must run **BOTH** servers simultaneously.

**Option 1: Startup Script (Recommended)**

```bash
./dev.sh
```

This script automatically:
- Kills existing server processes
- Exports correct local environment variables
- Clears Laravel and Vite caches
- Verifies MySQL connection and database existence
- Starts both Laravel and Vite servers
- Displays process IDs and helpful information

**Option 2: Manual (3 separate terminals)**

```bash
# Terminal 1 - Laravel Backend (REQUIRED)
php artisan serve

# Terminal 2 - Vite Frontend (REQUIRED)
npm run dev

# Terminal 3 - Queue Worker (Optional, for Monte Carlo)
php artisan queue:work database
```

**Why both servers?**
- **Laravel (port 8000)**: Serves backend API and pages
- **Vite (port 5173)**: Serves frontend assets with HMR
- Without Laravel: "unable to reach" errors
- Without Vite: Frontend assets won't load correctly

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

# Seed database
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

### Test Coverage

- **Architecture Tests**: 24 passing (enforce coding standards)
- **Unit Tests**: 36+ passing (service classes and calculations)
- **Feature Tests**: Multiple integration tests (API endpoints)
- **Total**: 60+ tests, 100% passing

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
DB_DATABASE=tengo_production
DB_USERNAME=tengo_user
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

Create `/etc/supervisor/conf.d/tengo-worker.conf`:

```ini
[program:tengo-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/tengo/artisan queue:work database --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/tengo/storage/logs/worker.log
```

---

## 📚 Documentation

### Available Documentation

- **CLAUDE.md**: Development guidelines and critical rules for Claude Code
- **COMPREHENSIVE_FEATURES_AND_ARCHITECTURE.md**: Complete technical reference
- **QUICK_REFERENCE.md**: Daily development reference with code patterns
- **DOCUMENTATION_INDEX.md**: Navigation guide for all documentation

### API Documentation

- **80+ API Endpoints** across all modules
- RESTful design with consistent response format
- Sanctum token-based authentication
- JSON request/response format

### Coding Standards

- **PHP**: PSR-12 compliant (enforced by Laravel Pint)
- **JavaScript/Vue**: Vue.js Style Guide (Priority A & B)
- **Naming**: PascalCase for classes, camelCase for methods, snake_case for database
- **Strict Types**: All PHP files use `declare(strict_types=1);`
- **Testing**: Pest tests for all financial calculations

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

- **Documentation**: See `CLAUDE.md` and `/docs` folder
- **Issues**: Create an issue in the repository

---

## 📋 Recent Updates (November 2025)

### November 12, 2025 - UI Enhancements

**Protection Module Improvements**:
- ✅ Enhanced policy portfolio view with card-based layout
- ✅ Added filtering by policy type (All, Life, Critical Illness, etc.)
- ✅ Added sorting options (coverage amount, type, provider)
- ✅ Coverage summary tags for quick overview
- ✅ New dedicated policy detail pages with comprehensive information
- ✅ Life policy type tags (Decreasing Term, Level Term, Whole of Life, etc.)
- ✅ Click-to-view navigation from policy cards to detail pages
- ✅ Renamed "Current Situation" tab to "Policy Overview"

**Dashboard Improvements**:
- ✅ Removed deprecated QuickActions component
- ✅ Inlined Plans card directly in Dashboard.vue
- ✅ Added Retirement Plan as greyed out option ("Coming soon")
- ✅ Greyed out Tax Plan and Financial Plan options
- ✅ Maintained all existing active plans (Protection, Estate, Investment & Savings)

**Files Modified**: 9 files
- Deleted: `QuickActions.vue`
- Added: `PolicyDetail.vue`
- Updated: Dashboard, Protection components, router, Vuex store

### November 12, 2025 - Critical Bug Fixes (Ownership & Spouse Data)

**PERMANENT FIX: Spouse Data Sharing in Estate Module**:
- ✅ Fixed persistent bug where spouse assets/liabilities never displayed
- ✅ Root cause: `hasAcceptedSpousePermission()` required separate permission record that was never created during onboarding
- ✅ Solution: Method now returns `true` automatically when both accounts are linked and married
- ✅ Impact: Estate module now correctly displays spouse data for second-death IHT calculations

**Property Equity Double-Division Bug Fix**:
- ✅ Fixed property equity showing half of correct value (£125k instead of £250k)
- ✅ Root cause: Database stores user's share, but services were multiplying by ownership_percentage again
- ✅ Fixed in: PropertyService, CrossModuleAssetAggregator, Property model, PropertyCard component
- ✅ Pattern established: Database value = user's share. NO multiplication needed.

**Joint Ownership Value Storage Fix**:
- ✅ Fixed joint investment/savings accounts storing full value in both user records
- ✅ Example: Joint GIA now stores £425k per user instead of £850k each
- ✅ Applied value division at creation time for consistency with properties
- ✅ Fixed in: InvestmentController, OnboardingService (investments & savings)
- ✅ Updated existing database records for joint accounts

**Cross-Module Asset Aggregation Fixes**:
- ✅ Fixed business and chattel value calculations (removed duplicate ownership_percentage multiplication)
- ✅ Fixed investment aggregation methods
- ✅ Ensures consistent calculation logic across all asset types

**Files Modified**: 8 files
- User.php, Property.php, PropertyService.php, PropertyCard.vue
- CrossModuleAssetAggregator.php, NetWorthService.php
- InvestmentController.php, OnboardingService.php

**Database Updates**: Fixed existing joint GIA accounts and holdings

---

**Current Version**: v0.2.6 (Beta - Production Ready)

**Last Updated**: November 12, 2025

**Status**: 🚀 Active Development - All Core Features Complete

---

🤖 **Built with [Claude Code](https://claude.com/claude-code)**
