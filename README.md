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

**Version**: v0.2.9 (Beta - Production Ready)

**Completion Status**:
- ✅ **Foundation**: 100% (Authentication, routing, testing framework)
- ✅ **Core Modules**: 100% (All 5 modules fully functional)
- ✅ **Advanced Features**: 100% (Portfolio optimization, Monte Carlo simulations, IHT planning, mixed mortgages)
- ✅ **User Management**: 100% (Spouse accounts, joint ownership, data sharing)
- ✅ **Admin Panel**: 100% (User management, backups, tax configuration)
- ✅ **UI/UX**: Enhanced (Policy detail views, Dashboard Plans card, expenditure modes, managing agents)

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

**Deployment Agent**:
- **laravel-stack-deployer**: Handles Laravel + MySQL + Vue.js + Vite deployments to production/staging/development environments

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
- **Admin Login**: `admin@fps.com` / `admin123`

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

### November 15, 2025 - v0.2.9 Major Feature Release

**20 Database Migrations** - 60+ new fields across 8 tables

**Major Features**:

1. **Mixed Mortgages**:
   - ✅ Support for split repayment types (e.g., 70% repayment / 30% interest-only)
   - ✅ Support for split rate types (e.g., 60% fixed @ 2.5% / 40% variable @ 4.2%)
   - ✅ Full validation to ensure percentages add to 100%
   - ✅ Enhanced property detail display showing both splits

2. **Managing Agents for BTL Properties**:
   - ✅ Track property management company details (name, company, email, phone)
   - ✅ Track management fees
   - ✅ Conditional display (only for Buy-to-Let properties)
   - ✅ Integration with property detail views

3. **Expenditure Modes for Married Couples**:
   - ✅ Simple vs. Category entry modes
   - ✅ Joint (50/50 split) vs. Separate expenditure tracking
   - ✅ Spouse data integration with full backend API support
   - ✅ Enhanced education expense fields (school lunches, school extras, university fees)
   - ✅ Unified expenditure form component (2,200+ lines → 1,278 lines, 42% code reduction)

4. **Expanded Liability Types**:
   - ✅ 9 liability types instead of 4 (secured loan, unsecured loan, personal loan, car loan, hire purchase, overdraft, etc.)
   - ✅ More accurate debt categorization
   - ✅ Better reporting in Net Worth and IHT Planning

5. **Family Member Name Granularity**:
   - ✅ Split single 'name' field into first/middle/last names
   - ✅ Automatic data migration for existing records
   - ✅ Better support for formal documents and legal compliance

6. **Life Insurance Enhancements**:
   - ✅ Added `policy_end_date` field (required for term policies)
   - ✅ Made `policy_start_date` and `policy_term_years` optional
   - ✅ Added `is_mortgage_protection` flag to identify mortgage protection policies
   - ✅ Updated help text for better user guidance

7. **Employment & Pension Improvements**:
   - ✅ Added 'part_time' employment status option
   - ✅ Added `pension_type` field to DC pensions (Occupational, SIPP, Personal, Stakeholder)
   - ✅ Conditional field display based on pension type

**Critical Bug Fixes**:

1. **Estate Plan Spouse Data Integration**:
   - ✅ Fixed comprehensive estate plan only showing user data, not spouse data
   - ✅ Enhanced `ComprehensiveEstatePlanService` to include spouse assets/liabilities
   - ✅ Returns structured data: user/spouse/combined sections

2. **IHT Planning Liability Display**:
   - ✅ Fixed non-mortgage liabilities not displaying in IHT Planning breakdown
   - ✅ Corrected field names (`current_balance` instead of `amount`, `liability_name` instead of `description`)
   - ✅ All liability types now visible (credit cards, loans, hire purchase, etc.)

3. **Expenditure Data Display**:
   - ✅ Fixed expenditure tab showing zeros despite data in database
   - ✅ Enhanced `OnboardingService` to handle both flat and nested data structures
   - ✅ Supports both joint and separate expenditure modes

4. **Net Worth Card Liability Display**:
   - ✅ Fixed only mortgages showing, missing other liability types
   - ✅ Replaced deprecated `PersonalAccount` model with `Liability` model
   - ✅ Complete liability breakdown with all types visible

5. **Property/Mortgage Ownership Sync**:
   - ✅ Fixed joint properties creating individual mortgages instead of joint
   - ✅ Added Vue watchers to sync mortgage ownership with property ownership automatically
   - ✅ Joint properties with mortgages now create reciprocal records for both owners

**Files Changed**: 50 files (4,480 insertions, 1,542 deletions)

**Documentation**: See `DEPLOYMENT_PATCH_v0.2.9.md` for complete details

---

### November 14-15, 2025 - v0.2.8 Post-Production Fixes

**10 Database Migrations**

**Critical Bug Fixes**:

1. **Joint Mortgage Reciprocal Creation** (CRITICAL):
   - ✅ Fixed joint properties with mortgages only creating ONE mortgage record instead of TWO
   - ✅ Root cause: Missing database columns (`ownership_type`, `joint_owner_name`)
   - ✅ Solution: Run pending migration, add watchers to sync ownership data
   - ✅ Impact: Joint mortgages now correctly create reciprocal records for both owners

2. **Retirement Module Consolidation**:
   - ✅ Created unified pension form with visual type selection (DC/DB/State)
   - ✅ Added DC pension types (Occupational, SIPP, Personal, Stakeholder)
   - ✅ Improved state pension form scrolling and dynamic titles
   - ✅ Consolidated retirement access to `/net-worth/retirement` only
   - ✅ Removed standalone `/retirement` route

3. **Net Worth UI Enhancements**:
   - ✅ Card grid layouts for investments and pensions
   - ✅ Color coding for Net Worth dashboard (blue assets, red liabilities)
   - ✅ Joint property/mortgage display improvements (full amounts with user share)
   - ✅ Mixed mortgage type display (percentages for split types)
   - ✅ Joint savings account full balance display
   - ✅ "Coming in Beta" messaging for business interests and chattels

4. **Critical Onboarding Fixes**:
   - ✅ Fixed expenditure form defaulting to simple total instead of detailed breakdown
   - ✅ Fixed state pension field name mismatch (422 validation errors)
   - ✅ Fixed expenditure data not persisting in separate mode
   - ✅ Fixed property management details not retained when editing
   - ✅ Fixed mortgage route parameter binding (404 errors)
   - ✅ Fixed all 24 mortgage fields persisting correctly
   - ✅ Comprehensive mortgage validation improvements
   - ✅ Removed invalid 'part_and_part' mortgage type

**Files Changed**: 67 files total

**Documentation**: See `DEPLOYMENT_PATCH_v0.2.8.md` for complete details

---

### November 12, 2025 - Critical Estate & Savings Fixes (Part 2)

**Estate IHT Calculation Fixes**:

1. **IHT-Exempt Assets (DC Pensions) Fix**:
   - ✅ Fixed Total Gross Assets including DC pensions (£500k) that should be IHT-exempt
   - ✅ Root cause: IHTCalculationService summed ALL assets without checking `is_iht_exempt` flag
   - ✅ Solution: Filter out `is_iht_exempt=true` assets before calculating gross estate
   - ✅ Result: Chris Jones now shows £1,839,000 (correct) instead of £2,339,000
   - ✅ Pattern: DC/DB pensions with nominated beneficiaries are outside estate for IHT

2. **Second Death Projection Fix**:
   - ✅ Fixed married couples projecting to different ages depending on who views the page
   - ✅ Root cause: IHTCalculationService only used primary user's life expectancy
   - ✅ Solution: Calculate BOTH life expectancies, use max() for second death scenario
   - ✅ Result: Both Chris (36 years) and Ang (44 years) now project to 44 years (Ang's death)
   - ✅ UK IHT context: First death = spouse exemption (no IHT), second death = full combined estate taxed

3. **Breakdown Projection Alignment Fix**:
   - ✅ Fixed inconsistent projected values between service (£20.4M) and breakdown subtotals (£16.7M)
   - ✅ Root cause: IHTController used 4.5% growth to age 85 per spouse, service used 4.7% to second death
   - ✅ Solution: Aligned both to use 4.7% growth to second death (44 years for both spouses)
   - ✅ Result: Service and breakdown now show identical £20,476,882 projected value

4. **Cache Invalidation**:
   - ✅ Cleared stale IHT calculation cache that showed pre-fix values
   - ✅ All calculations now use updated filtering and projection logic

**Family Members & User Profile Fixes**:

5. **Spouse Family Members Sharing**:
   - ✅ Fixed spouse seeing empty family section when account created during onboarding
   - ✅ Root cause: UserProfileService only returned user's own family members
   - ✅ Solution: Added `getFamilyMembersWithSharing()` to include spouse's records
   - ✅ Pattern: Same sharing logic as FamilyMembersController (spouse + children)

6. **Password Requirements Guidance**:
   - ✅ Added password requirements hint to ChangePasswordModal and Register forms
   - ✅ Requirements: 8+ chars, 1 uppercase, 1 lowercase, 1 number, 1 special character
   - ✅ Prevents user confusion when password validation fails

**Savings Module Fixes**:

7. **Joint Savings Accounts 50/50 Split**:
   - ✅ Fixed joint savings showing full balance in one account, nothing in spouse's
   - ✅ Root cause: SavingsController didn't split balance before creating reciprocal records
   - ✅ Solution: Split balance 50/50, set ownership_percentage, create two records
   - ✅ Pattern: Now matches Property module (two records, each with their share)
   - ✅ Result: £45k joint account now shows £22.5k for Chris, £22.5k for Ang

8. **Duplicate Accounts in Savings View**:
   - ✅ Fixed Ang seeing 3 accounts instead of 1 (her joint + Chris's joint + Chris's individual)
   - ✅ Root cause: index() method included ALL spouse accounts, but joint accounts already use reciprocal records
   - ✅ Solution: Reverted to only fetch user's own accounts (joint records already exist per user)

9. **Ownership Tags on Savings Cards**:
   - ✅ Added ownership badge (Individual/Joint/Trust) to savings account cards
   - ✅ Badge colors: Gray (individual), Purple (joint), Amber (trust)
   - ✅ Consistent with Property and Investment module styling

**Files Modified**: 7 files
- `app/Services/Estate/IHTCalculationService.php`
- `app/Http/Controllers/Api/Estate/IHTController.php`
- `app/Services/UserProfile/UserProfileService.php`
- `app/Http/Controllers/Api/FamilyMembersController.php`
- `resources/js/components/Auth/ChangePasswordModal.vue`
- `resources/js/views/Register.vue`
- `app/Http/Controllers/Api/SavingsController.php`
- `resources/js/components/Savings/CurrentSituation.vue`

**Database Updates**: Fixed existing joint savings account (Chris £22.5k, Ang £22.5k)

**Impact**: All married couples now see consistent IHT calculations, joint accounts properly split, and family members correctly shared.

---

### November 12, 2025 - Estate IHT Planning Projected Values Fix

**Issue**: IHT breakdown table showed incorrect projected values in subtotal rows
- Asset subtotals displayed current total in both Current and Projected columns
- Liability subtotals used computed properties instead of backend-calculated values
- Projected net estate didn't account for mortgages being paid off by age 70

**Root Cause**:
1. Frontend had duplicate subtotal rows - one correct set (lines 266-271, 332-339) and one buggy set (lines 579-583, 644-648) that was actually being rendered
2. Buggy rows displayed `.total` in both columns instead of `.total` and `.projected_total`
3. Backend service assumed constant liabilities, but controller correctly calculated projected liabilities (mortgages = £0 if age >= 70)
4. Projected net estate wasn't recalculated after getting correct projected liabilities

**Solution**:
- **Frontend** (IHTPlanning.vue): Fixed 4 subtotal rows to use correct data properties:
  - User assets: `.projected_total` instead of `.total` (line 583)
  - Spouse assets: `.projected_total` instead of `.total` (line 648)
  - User liabilities: `.projected_total` instead of `userLiabilitiesProjectedTotal` (line 689)
  - Spouse liabilities: `.projected_total` instead of `spouseLiabilitiesProjectedTotal` (line 723)

- **Backend** (IHTController.php): Recalculate projected values after getting correct liabilities:
  - Recalculate `projected_net_estate` = projected assets - projected liabilities (mortgages + persistent liabilities)
  - Recalculate `projected_taxable_estate` and `projected_iht_liability` using corrected net estate
  - Ensures mortgages paid off by age 70 are reflected (£0), while other liabilities persist

**Result**:
- ✅ Current column shows current values
- ✅ Projected column shows projected values at estimated death age
- ✅ Projected net estate correctly accounts for mortgages being paid off
- ✅ Persistent liabilities (loans, credit cards) correctly remain at current value in projections

**Files Modified**: 2 files
- `resources/js/components/Estate/IHTPlanning.vue`
- `app/Http/Controllers/Api/Estate/IHTController.php`

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

### November 13, 2025 - Critical Bug Fixes (Production Deployment)

**Protection Module - Add/Edit Policy Fixes**:

1. **Add Policy Button Not Working**:
   - ✅ Fixed "Add Policy" button doing nothing in Protection Dashboard
   - ✅ Root cause: `handleAddPolicy()` was empty stub, no modal existed
   - ✅ Solution: Added PolicyFormModal to dashboard with proper state management
   - ✅ Pattern: Same unified form approach as onboarding (`:is-editing` prop)

2. **Edit Policy Modal Showing Blank**:
   - ✅ Fixed edit modal appearing but form fields empty
   - ✅ Root cause: Missing `:is-editing="true"` prop on PolicyFormModal
   - ✅ Solution: Added prop so modal knows to load policy data into form
   - ✅ Result: Edit modal now properly populates with policy data

3. **Save Throwing "Unknown policy type" Error**:
   - ✅ Fixed console error when saving edited policy
   - ✅ Root cause: Wrong parameter names passed to Vuex store action
   - ✅ Solution: Changed to correct parameters (`policyType`, `id`, `policyData`)
   - ✅ Result: Save operation now works without errors

**Files Modified**: 2 frontend components
- `resources/js/views/Protection/ProtectionDashboard.vue`
- `resources/js/components/Protection/PolicyDetail.vue`

**Status**: ✅ Deployed to production

---

**Savings Module - Ownership Fields Fix**:

4. **Savings Account Form Freeze**:
   - ✅ Fixed form freeze and 500/422 errors during onboarding
   - ✅ Root cause: SavingsAccount model missing ownership fields from `$fillable` array
   - ✅ Solution: Added `ownership_type`, `ownership_percentage`, `joint_owner_id`, `trust_id` to model
   - ✅ Result: Form now saves successfully

5. **Validation Mismatch**:
   - ✅ Fixed validation requiring `ownership_percentage` that frontend doesn't send
   - ✅ Solution: Changed validation to `nullable` with controller defaults
   - ✅ Pattern: Frontend doesn't send field, backend sets sensible default (100% or 50% for joint)

**Files Modified**: 3 backend files
- `app/Models/SavingsAccount.php`
- `app/Http/Requests/Savings/StoreSavingsAccountRequest.php`
- `app/Http/Controllers/Api/SavingsController.php`

**Status**: ✅ Deployed to production

---

**User Profile Module - Display Fixes**:

6. **Mortgage Allocation Fix**:
   - ✅ Fixed joint mortgages showing full amount under each spouse instead of 50/50 split
   - ✅ Root cause: `createJointMortgage()` copied full balance to both records
   - ✅ Solution: Split balance 50/50 when creating reciprocal mortgage records
   - ✅ Impact: Each spouse now shows their correct share (£100k each for £200k total)

7. **Interest Rate Display Fix**:
   - ✅ Fixed interest rates showing as 2700.00% instead of 27.00%
   - ✅ Root cause: Multiplying by 100 when rate already stored as percentage
   - ✅ Solution: Removed multiplication, rates stored as 27.00 not 0.27
   - ✅ Result: Interest rates display correctly

8. **Balance Sheet Individual Line Items**:
   - ✅ Fixed balance sheet showing categories instead of individual assets/liabilities
   - ✅ Root cause: PersonalAccountsService returned summary categories
   - ✅ Solution: Complete rewrite to return individual line items
   - ✅ Result: Users see each specific account with ownership percentages

**Files Modified**: 2 files
- `app/Http/Controllers/Api/MortgageController.php`
- `app/Services/UserProfile/PersonalAccountsService.php`
- `resources/js/components/UserProfile/LiabilitiesOverview.vue`

**Status**: ✅ Deployed to production (requires database fix for existing joint mortgages)

**Documentation**: See `BUGFIX_PROTECTION_MODULE_2025-11-13.md`, `BUGFIX_SAVINGS_OWNERSHIP_2025-11-13.md`, `BUGFIX_USER_PROFILE_2025-11-13.md`

---

### November 14, 2025 - Documentation Updates

**CLAUDE.md Updates**:
- ✅ Updated version to v0.2.7
- ✅ Added laravel-stack-deployer agent documentation
- ✅ Updated Known Issues section with critical mortgage issue
- ✅ Updated last modified date

**README.md Updates**:
- ✅ Updated version to v0.2.7
- ✅ Added laravel-stack-deployer agent to Agent-Based System section
- ✅ Added November 13-14 bug fix changelog
- ✅ Updated last modified date

---

**Current Version**: v0.2.9 (Beta - Production Ready)

**Last Updated**: November 17, 2025

**Status**: 🚀 Active Development - All Core Features Complete

---

🤖 **Built with [Claude Code](https://claude.com/claude-code)**
