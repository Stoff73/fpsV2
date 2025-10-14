# FPS Financial Planning System - Tech Stack Architecture

## Document Overview
This document provides a comprehensive architecture plan for the FPS (Financial Planning System) based on the requirements outlined in `moduleSimple.md`. It evaluates the proposed tech stack, identifies potential issues, and provides detailed recommendations for successful implementation.

---

## Executive Summary

### Proposed Tech Stack
- **Backend**: PHP
- **Database**: MySQL
- **Frontend**: HTML, CSS, JavaScript

### Overall Assessment
The proposed stack is **workable but requires enhancements** for optimal implementation. MySQL is a good choice for this project. PHP is acceptable but needs proper framework support and architectural patterns. The frontend needs a JavaScript framework to handle the complex interactive dashboards described in the requirements.

### Key Recommendations
1. ✅ **MySQL** - Good choice, use InnoDB engine for ACID compliance
2. ⚠️ **PHP** - Acceptable, but recommend using **Laravel framework**
3. ⚠️ **Frontend** - Add **Vue.js** or **React** framework + **ApexCharts** for visualizations
4. ⚠️ **Performance** - Implement Memcached caching and optimization for Monte Carlo simulations
5. ⚠️ **Queues** - Use database-backed queues (Memcached doesn't support queues)

---

## Detailed Tech Stack Analysis

### 1. Database: MySQL ✅ GOOD CHOICE

#### Why MySQL is Suitable for This Project

**Strengths:**
- ✅ **ACID Compliance**: InnoDB engine provides full ACID compliance for financial data integrity
- ✅ **Widely Used**: Most popular open-source database, mature and battle-tested
- ✅ **Excellent PHP Integration**: Native support via PDO and mysqli, strong Laravel support
- ✅ **JSON Support**: MySQL 5.7+ has JSON data type for flexible storage
- ✅ **Good Performance**: Excellent for read-heavy workloads (typical for financial dashboards)
- ✅ **Easy to Learn**: Large community, abundant resources and documentation
- ✅ **Cost-Effective Hosting**: Available on virtually every hosting platform
- ✅ **Proven in Production**: Used by major financial applications globally

**Key Considerations:**
- ⚠️ **JSON vs JSONB**: MySQL's JSON type is less performant than PostgreSQL's JSONB for complex queries
  - **Impact**: Tax configuration queries will be slightly slower
  - **Mitigation**: Cache tax config in Memcached (already planned), minimize JSON queries
- ⚠️ **Must Use InnoDB Engine**: Essential for ACID compliance and foreign keys
  - **Action**: Explicitly specify ENGINE=InnoDB in all table definitions
  - **Laravel Default**: Laravel uses InnoDB by default

**Ideal Use Cases in FPS:**
- User accounts and profiles
- Financial data (policies, accounts, holdings, pensions)
- Historical data and audit trails
- Tax configuration (stored as JSON)
- Calculation results caching
- What-if scenario storage

**Verdict**: MySQL is a **solid choice** for FPS, especially with InnoDB engine and Memcached caching.

---

### 2. Backend: PHP ⚠️ ACCEPTABLE WITH RECOMMENDATIONS

#### Strengths of PHP for FPS

**Pros:**
- ✅ Mature language with large ecosystem
- ✅ Excellent web application support
- ✅ Native MySQL integration (PDO, mysqli)
- ✅ Can handle financial calculations (with proper libraries)
- ✅ Good for server-side form processing
- ✅ Large developer community
- ✅ Cost-effective hosting options

**Cons & Concerns:**
- ⚠️ **Performance for CPU-Intensive Operations**: Monte Carlo simulations will be slower than Python/NumPy
- ⚠️ **Limited Scientific Libraries**: No equivalent to NumPy, SciPy, Pandas
- ⚠️ **Verbose for Complex Logic**: Financial calculations can become verbose
- ⚠️ **Type Safety**: Requires discipline (can use strict types in PHP 7+)

#### Critical Recommendation: Use Laravel Framework

**Why Laravel?**
- ✅ **MVC Architecture**: Clean separation of concerns
- ✅ **Eloquent ORM**: Elegant database interaction
- ✅ **Service Layer Pattern**: Perfect for agent classes
- ✅ **Caching**: Built-in Memcached support - critical for performance
- ✅ **Queue System**: Database-backed queues for long-running calculations (Monte Carlo)
- ✅ **Validation**: Robust form validation for dynamic inputs
- ✅ **API Support**: RESTful API for frontend communication
- ✅ **Testing**: Pest testing framework for financial calculation testing
- ✅ **Security**: Authentication, authorization, CSRF protection

**Alternative Frameworks:**
- **Symfony**: More enterprise-focused, steeper learning curve
- **CodeIgniter**: Lightweight but less feature-rich
- **Slim**: Micro-framework, would require more manual setup

**Verdict**: **Use Laravel for best results**

#### Handling Financial Calculations in PHP

**Standard Financial Calculations** (✅ PHP handles well):
- Tax calculations (Income Tax, NI, CGT, IHT)
- Coverage gap analysis
- Net worth calculations
- Compound interest
- Present value / Future value
- Annuity calculations
- Mortgage calculations
- ISA allowance tracking

**CPU-Intensive Calculations** (⚠️ Requires optimization):
- **Monte Carlo Simulations**: Investment & Retirement modules require thousands of iterations
  - PHP will be 5-10x slower than Python/NumPy
  - **Solution**:
    - Limit iterations (1,000 vs 10,000)
    - Use caching aggressively
    - Run in background queues
    - Pre-compute common scenarios
- **Portfolio Optimization**: Modern Portfolio Theory calculations
  - PHP can handle but may be slow
  - **Solution**: Use efficient algorithms, cache results

**Recommended PHP Libraries:**
- `brick/money`: Precise monetary calculations (avoids float precision issues)
- `brick/math`: Arbitrary precision arithmetic
- `phpoffice/phpspreadsheet`: Excel import/export (for tax config updates)
- `guzzlehttp/guzzle`: HTTP client (if ever need external data)

#### PHP Architectural Recommendations

**Use Object-Oriented Programming:**
```
app/
├── Agents/
│   ├── ProtectionAgent.php
│   ├── SavingsAgent.php
│   ├── InvestmentAgent.php
│   ├── RetirementAgent.php
│   ├── EstateAgent.php
│   └── CoordinatingAgent.php
├── Services/
│   ├── FinancialCalculations/
│   │   ├── TaxCalculator.php
│   │   ├── CompoundInterestCalculator.php
│   │   ├── MonteCarloSimulator.php
│   │   ├── IHTCalculator.php
│   │   └── PensionProjector.php
│   ├── RecommendationEngine.php
│   └── ScenarioBuilder.php
├── Models/
│   ├── User.php
│   ├── ProtectionPolicy.php
│   ├── SavingsAccount.php
│   ├── Investment.php
│   ├── Pension.php
│   └── Estate.php
├── Http/
│   ├── Controllers/
│   └── Requests/
└── Config/
    └── UKTaxRules.php
```

**Performance Optimization Strategies:**
1. **Caching**: Use Memcached for calculation results
   - Cache tax calculations for common scenarios
   - Cache Monte Carlo results for standard portfolios
   - Cache pension projections
2. **Queue System**: Database-backed Laravel queues for background processing
   - Monte Carlo simulations
   - What-if scenario generation
   - Report generation
3. **Database Optimization**:
   - Index frequently queried fields
   - Use database views for complex aggregations
   - Store pre-computed values when possible
4. **Code Optimization**:
   - Use strict types (`declare(strict_types=1)`)
   - Profile with Xdebug to find bottlenecks
   - Optimize loops in Monte Carlo simulations

**Verdict**: **PHP is acceptable with Laravel framework and proper optimization**

---

### 3. Frontend: HTML/CSS/JavaScript ⚠️ NEEDS ENHANCEMENT

#### Why Plain JavaScript is Insufficient

The `moduleSimple.md` spec describes **very complex, interactive dashboards**:
- Drag-and-drop interfaces (priority ranking, reordering)
- Real-time scenario builders with multiple interactive sliders
- Progressive disclosure (clickable cards that expand)
- Multiple chart types (pie, line, bar, Gantt, heatmaps, waterfall)
- Cross-module navigation
- Live form validation
- Dynamic form generation
- Real-time calculations as user types

**Plain JavaScript challenges:**
- ❌ State management nightmare
- ❌ No component reusability
- ❌ Difficult to maintain
- ❌ Poor code organization
- ❌ Manual DOM manipulation is error-prone
- ❌ No reactive data binding

#### Critical Recommendation: Add JavaScript Framework

**Option 1: Vue.js** ⭐ **RECOMMENDED for PHP Projects**

**Why Vue.js?**
- ✅ **Easiest Learning Curve**: Simple, intuitive syntax
- ✅ **Progressive Framework**: Can add incrementally to existing projects
- ✅ **Excellent Laravel Integration**: Popular in PHP community
- ✅ **Reactive Data Binding**: Perfect for live calculations
- ✅ **Component-Based**: Reusable dashboard components
- ✅ **Great Documentation**: Easy to learn
- ✅ **Vue Router**: Built-in routing for multi-module navigation
- ✅ **Vuex**: State management for complex data flows

**Vue.js Component Structure:**
```
resources/js/
├── components/
│   ├── Dashboard/
│   │   ├── MainDashboard.vue
│   │   ├── ModuleCard.vue
│   │   ├── GoalProgress.vue
│   │   └── ISAAllowanceTracker.vue
│   ├── Protection/
│   │   ├── ProtectionDashboard.vue
│   │   ├── CoverageGapChart.vue
│   │   ├── PolicyCard.vue
│   │   └── ScenarioBuilder.vue
│   ├── Savings/
│   │   ├── SavingsDashboard.vue
│   │   ├── EmergencyFundGauge.vue
│   │   ├── GoalCard.vue
│   │   └── LiquidityLadder.vue
│   ├── Investment/
│   │   ├── InvestmentDashboard.vue
│   │   ├── AssetAllocationChart.vue
│   │   ├── HoldingsTable.vue
│   │   └── MonteCarloResults.vue
│   ├── Retirement/
│   │   ├── RetirementDashboard.vue
│   │   ├── ReadinessGauge.vue
│   │   ├── PensionInventory.vue
│   │   └── DrawdownSimulator.vue
│   └── Estate/
│       ├── EstateDashboard.vue
│       ├── IHTCalculation.vue
│       ├── GiftingTimeline.vue
│       └── NetWorthStatement.vue
├── services/
│   ├── api.js
│   └── calculations.js
├── store/
│   ├── index.js
│   └── modules/
│       ├── protection.js
│       ├── savings.js
│       ├── investment.js
│       ├── retirement.js
│       └── estate.js
└── router/
    └── index.js
```

**Option 2: React**

**Why React?**
- ✅ Most popular framework (largest ecosystem)
- ✅ Component-based architecture
- ✅ Excellent for complex UIs
- ✅ Large library ecosystem
- ✅ React Hooks for state management

**Cons compared to Vue:**
- ❌ Steeper learning curve
- ❌ More boilerplate code
- ❌ Less common in PHP community
- ❌ Requires additional libraries (React Router, Redux/Context)

**Option 3: Alpine.js**

**Why Alpine.js?**
- ✅ **Lightweight** (~15KB)
- ✅ **Perfect for Laravel**: Created by Laravel community
- ✅ **Easy to Learn**: Uses HTML attributes
- ✅ **No Build Step**: Can use via CDN

**Cons:**
- ❌ **Limited for Complex UIs**: Best for simpler interactions
- ❌ Not suitable for SPA (Single Page Application)
- ❌ Less powerful state management
- ❌ **Not recommended for FPS complexity**

#### Recommendation: **Use Vue.js**

**Reasoning:**
1. Best balance of power and simplicity
2. Excellent Laravel integration (Laravel Mix/Vite)
3. Perfect for the complex interactive dashboards required
4. Reactive data binding for live calculations
5. Component reusability across modules
6. Easy to learn for PHP developers

---

### 4. Data Visualization: ApexCharts 📊

#### Critical Need: Charting Library

The spec requires extensive visualizations:
- Pie charts (asset allocation, expenditure breakdown)
- Line charts (portfolio performance, cashflow)
- Bar charts (income sources, IHT liability)
- Gantt charts (coverage timeline, gifting timeline)
- Heatmaps (coverage gaps, allocation deviation)
- Waterfall charts (IHT calculation breakdown)
- Gauges (readiness scores, adequacy scores)
- Donut charts (mini asset allocation)
- Geographic maps (geographic allocation)

#### ApexCharts ⭐ **SELECTED for FPS**

**Why ApexCharts is Perfect for FPS:**

**Strengths:**
- ✅ **Comprehensive Chart Types**: Covers ALL FPS visualization needs
  - Line, area, bar, column charts (performance, cashflow)
  - Pie, donut charts (asset allocation, breakdowns)
  - Heatmaps (coverage gaps, risk assessment)
  - Timeline/Gantt charts (gifting timeline, coverage timeline)
  - Radial bar gauges (readiness scores, adequacy metrics)
  - Waterfall charts (IHT calculation breakdown)
  - Candlestick, treemap, and more
- ✅ **Excellent Interactivity**: Built-in zoom, pan, hover, tooltips
- ✅ **Modern & Professional**: Polished aesthetics perfect for financial dashboards
- ✅ **Vue.js Integration**: `vue3-apexcharts` wrapper is excellent
- ✅ **Responsive**: Mobile-friendly out of the box
- ✅ **Animations**: Smooth transitions and loading animations
- ✅ **Real-time Updates**: Perfect for live calculations
- ✅ **Extensive Documentation**: Well-documented with examples
- ✅ **Free & Open Source**: MIT license

**Cons (Minimal):**
- ⚠️ Slightly larger bundle size than simpler libraries (~144KB minified)
  - Mitigation: Modern bundlers tree-shake unused features
- ⚠️ Learning curve slightly steeper than basic charting libraries
  - Mitigation: Excellent documentation and Vue wrapper simplifies usage

**Vue.js Integration:**
```bash
npm install --save apexcharts vue3-apexcharts
```

**Example Usage in Vue Component:**
```vue
<template>
  <apexchart
    type="radialBar"
    :options="chartOptions"
    :series="series"
  />
</template>

<script>
import VueApexCharts from 'vue3-apexcharts'

export default {
  components: {
    apexchart: VueApexCharts
  },
  data() {
    return {
      series: [67], // Coverage adequacy score
      chartOptions: {
        chart: { type: 'radialBar' },
        plotOptions: {
          radialBar: {
            hollow: { size: '70%' },
            dataLabels: {
              name: { fontSize: '22px' },
              value: { fontSize: '16px' },
              total: {
                show: true,
                label: 'Adequacy',
                formatter: () => '67%'
              }
            }
          }
        },
        labels: ['Coverage']
      }
    }
  }
}
</script>
```

#### Chart Types Mapping for FPS Modules

**Protection Module:**
- Radial bar gauge → Coverage adequacy score
- Heatmap → Coverage gap analysis across life stages
- Bar chart → Premium breakdown by policy type
- Timeline → Coverage timeline visualization

**Savings Module:**
- Radial bar gauge → Emergency fund runway
- Bar chart → Savings goal progress
- Line chart → Savings growth over time
- Area chart → Liquidity ladder

**Investment Module:**
- Donut chart → Asset allocation
- Line chart → Portfolio performance over time
- Heatmap → Asset allocation deviation from target
- Area chart → Monte Carlo simulation results (percentile ranges)
- Treemap → Holdings breakdown

**Retirement Module:**
- Radial bar gauge → Retirement readiness score
- Column chart → Income sources in retirement
- Line chart → Pension projection over time
- Area chart → Drawdown simulation

**Estate Module:**
- Waterfall chart → IHT calculation breakdown (estate value → NRB → RNRB → IHT due)
- Bar chart → Net worth statement (assets vs liabilities)
- Timeline → Gifting strategy timeline (7-year rule visualization)
- Area chart → Cashflow projection

**Dashboard:**
- Mixed chart → Net worth over time with multiple data series
- Donut chart → Total asset allocation
- Column chart → ISA allowance usage

#### Why Not Other Libraries?

**Chart.js:**
- ❌ Missing key chart types (waterfall, heatmap, timeline without plugins)
- ❌ Less interactive out of the box
- ❌ Less modern aesthetics
- ✅ Simpler, but insufficient for FPS comprehensive needs

**D3.js:**
- ❌ Very steep learning curve
- ❌ Requires significant custom code for each chart
- ❌ Overkill for FPS needs
- ✅ Most powerful, but ApexCharts provides sufficient power with better DX

#### Verdict: ApexCharts is Ideal

ApexCharts provides the perfect balance of:
- Comprehensive chart coverage (all FPS needs met)
- Professional aesthetics for financial applications
- Excellent interactivity for what-if scenarios
- Good Vue.js integration
- Reasonable bundle size
- Strong documentation and community support

---

### 5. Caching Layer: Memcached vs Redis 💾

#### Critical Requirement: High-Performance Caching

The FPS system requires aggressive caching for performance:
- Tax calculations (used frequently across all modules)
- Monte Carlo simulation results (CPU-intensive, must cache)
- Dashboard calculations (net worth, cashflow)
- What-if scenario results
- UK tax configuration data
- Session storage

#### Memcached vs Redis: Detailed Comparison

**Memcached** ⭐ **SELECTED for FPS**

**Strengths:**
- ✅ **Pure Caching Focus**: Optimized specifically for key-value caching
- ✅ **Simpler Architecture**: Less complex, easier to maintain
- ✅ **Faster for Simple Gets/Sets**: Slightly faster for basic caching operations
- ✅ **Multi-threaded**: Better CPU utilization on multi-core systems
- ✅ **Lower Memory Overhead**: More efficient memory usage per cached item
- ✅ **Easier to Scale Horizontally**: Built-in distributed caching

**Limitations:**
- ⚠️ **No Data Persistence**: All data lost on restart (acceptable for caching)
- ⚠️ **No Complex Data Structures**: Only supports strings (sufficient for FPS)
- ⚠️ **No Pub/Sub**: Cannot be used for real-time messaging
- ⚠️ **No Queue Support**: Cannot be used for Laravel Queues
- ⚠️ **No Transactions**: Cannot group operations atomically

**Redis**

**Strengths:**
- ✅ Rich data structures (lists, sets, sorted sets, hashes)
- ✅ Data persistence options (RDB, AOF)
- ✅ Pub/Sub messaging
- ✅ Queue support (Laravel Queues can use Redis)
- ✅ Transactions (MULTI/EXEC)
- ✅ Lua scripting
- ✅ More features overall

**Limitations:**
- ⚠️ **Single-threaded**: Less efficient on multi-core systems
- ⚠️ **More Complex**: Additional features add complexity
- ⚠️ **Higher Memory Overhead**: More memory per cached item
- ⚠️ **Overkill for Pure Caching**: Many features unused in FPS

#### Decision Rationale for FPS

**Why Memcached is Better for FPS:**

1. **Use Case Alignment**: FPS only needs simple key-value caching
   - Tax calculation results → String (JSON)
   - Monte Carlo results → String (JSON)
   - Dashboard data → String (JSON)
   - No need for Redis data structures

2. **Performance**: Memcached is faster for simple gets/sets
   - Monte Carlo cache lookups will be more frequent than writes
   - Multi-threaded architecture better for concurrent users

3. **Simplicity**: Fewer moving parts, easier to maintain
   - No persistence configuration needed
   - No snapshotting concerns
   - Simpler deployment

4. **Memory Efficiency**: More cached items for same memory budget
   - Important as cache grows with more users and scenarios

**Addressing the Queue Issue:**

Since Memcached doesn't support queues, FPS will use **database-backed queues**:

```php
// config/queue.php
'default' => env('QUEUE_CONNECTION', 'database'),

'connections' => [
    'database' => [
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
    ],
],
```

**Why Database Queues are Acceptable:**
- ✅ Monte Carlo simulations run infrequently (user-initiated)
- ✅ MySQL with proper indexes handles queue operations well
- ✅ Simpler architecture (one less service to manage)
- ✅ Persistent by nature (jobs survive restarts)
- ✅ Good for low-to-medium queue volume

**Performance Comparison for FPS Use Cases:**

| Operation | Memcached | Redis | Winner |
|-----------|-----------|-------|--------|
| Simple GET/SET | ~50,000 ops/sec | ~45,000 ops/sec | Memcached |
| Concurrent reads | Excellent (multi-threaded) | Good (single-threaded) | Memcached |
| Cache eviction | LRU (simple) | LRU + more options | Tie |
| Memory efficiency | 90% efficiency | 85% efficiency | Memcached |
| Queue operations | Not supported | Excellent | Redis |
| Data persistence | Not supported | Optional | Redis |

#### Alternative Hybrid Approach (Not Recommended)

**If you absolutely need Redis queues:**
- Use Memcached for caching (primary workload)
- Use Redis only for queues (secondary workload)
- **Cons**: Adds complexity, two caching services to maintain

**Verdict**: Database queues are sufficient for FPS, so pure Memcached is recommended.

#### Memcached Configuration for FPS

**Laravel Cache Configuration:**
```php
// config/cache.php
'default' => env('CACHE_DRIVER', 'memcached'),

'stores' => [
    'memcached' => [
        'driver' => 'memcached',
        'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
        'sasl' => [
            env('MEMCACHED_USERNAME'),
            env('MEMCACHED_PASSWORD'),
        ],
        'options' => [
            // Increase timeout for Monte Carlo results
            Memcached::OPT_CONNECT_TIMEOUT => 2000,
        ],
        'servers' => [
            [
                'host' => env('MEMCACHED_HOST', '127.0.0.1'),
                'port' => env('MEMCACHED_PORT', 11211),
                'weight' => 100,
            ],
        ],
    ],
],
```

**Cache TTL Strategy:**
```php
// Different TTLs for different data types
$taxConfig = Cache::remember('tax_config_2024_25', 3600, ...);      // 1 hour
$monteCarlo = Cache::remember('monte_carlo_' . $hash, 86400, ...);  // 24 hours
$dashboard = Cache::remember('dashboard_' . $userId, 1800, ...);    // 30 minutes
$scenarios = Cache::remember('scenario_' . $hash, 3600, ...);       // 1 hour
```

#### Migration from Redis to Memcached

**No Code Changes Needed:**
Laravel's cache facade abstracts the underlying driver. Simply change:
```bash
# .env
CACHE_DRIVER=memcached
QUEUE_CONNECTION=database
```

**Run Queue Migration:**
```bash
php artisan queue:table
php artisan migrate
```

---

## Recommended Final Tech Stack

### ✅ Recommended Architecture

| Layer | Technology | Justification |
|-------|-----------|---------------|
| **Backend Framework** | **Laravel 10.x** | Modern PHP framework, perfect for FPS complexity |
| **Backend Language** | **PHP 8.2+** | Mature, with typed properties and performance improvements |
| **Database** | **MySQL 8.0+** | ACID compliance (InnoDB), JSON support, excellent for financial data |
| **Frontend Framework** | **Vue.js 3** | Reactive, component-based, excellent Laravel integration |
| **Charting** | **ApexCharts** | Comprehensive chart types, excellent interactivity, perfect for financial dashboards |
| **State Management** | **Vuex** | Centralized state for complex cross-module data |
| **API Layer** | **Laravel API Resources** | RESTful API for frontend-backend communication |
| **Caching** | **Memcached** | Fast, simple, optimized for key-value caching |
| **Queue System** | **Laravel Queues (Database)** | Background processing for Monte Carlo simulations |
| **CSS Framework** | **Tailwind CSS** | Utility-first, rapid UI development, customizable |
| **Build Tool** | **Vite** | Fast, modern, excellent Vue integration |
| **Testing** | **Pest** | Modern PHP testing framework, built on PHPUnit with better DX |
| **API Testing** | **Postman** | Industry standard for API development and testing |

---

## Architecture Overview

### Application Layers

```
┌─────────────────────────────────────────────────────────────┐
│                     PRESENTATION LAYER                       │
│  Vue.js Components, Charts, Forms, Dashboards, Router       │
└─────────────────────────────────────────────────────────────┘
                            ↕ API (JSON)
┌─────────────────────────────────────────────────────────────┐
│                      API LAYER                               │
│  Laravel Controllers, Requests, Resources, Middleware        │
└─────────────────────────────────────────────────────────────┘
                            ↕
┌─────────────────────────────────────────────────────────────┐
│                   BUSINESS LOGIC LAYER                       │
│  Agent Classes, Services, Calculation Utilities              │
│  • ProtectionAgent    • TaxCalculator                       │
│  • SavingsAgent       • MonteCarloSimulator                 │
│  • InvestmentAgent    • IHTCalculator                       │
│  • RetirementAgent    • RecommendationEngine                │
│  • EstateAgent        • ScenarioBuilder                     │
│  • CoordinatingAgent                                         │
└─────────────────────────────────────────────────────────────┘
                            ↕
┌─────────────────────────────────────────────────────────────┐
│                   DATA ACCESS LAYER                          │
│  Eloquent Models, Repositories, Query Builders              │
└─────────────────────────────────────────────────────────────┘
                            ↕
┌─────────────────────────────────────────────────────────────┐
│                       DATABASE                               │
│  MySQL: Users, Policies, Accounts, Holdings, Config, Queues │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                     CACHE LAYER                              │
│  Memcached: Calculation Results, Tax Config, Sessions       │
└─────────────────────────────────────────────────────────────┘
```

---

## Detailed Architecture Design

### 1. Backend Structure (Laravel)

#### Directory Structure
```
fps-laravel/
├── app/
│   ├── Agents/                         # Core agent classes
│   │   ├── BaseAgent.php
│   │   ├── ProtectionAgent.php
│   │   ├── SavingsAgent.php
│   │   ├── InvestmentAgent.php
│   │   ├── RetirementAgent.php
│   │   ├── EstateAgent.php
│   │   └── CoordinatingAgent.php
│   │
│   ├── Services/                       # Service layer
│   │   ├── FinancialCalculations/
│   │   │   ├── TaxCalculator.php      # UK tax calculations
│   │   │   ├── CompoundInterestCalculator.php
│   │   │   ├── MonteCarloSimulator.php # Investment projections
│   │   │   ├── IHTCalculator.php      # Inheritance tax
│   │   │   ├── PensionProjector.php   # Retirement projections
│   │   │   ├── CoverageGapAnalyzer.php
│   │   │   ├── NetWorthCalculator.php
│   │   │   └── CashflowAnalyzer.php
│   │   ├── RecommendationEngine.php   # Generate recommendations
│   │   ├── ScenarioBuilder.php        # What-if scenarios
│   │   └── UKTaxConfigService.php     # Load and manage tax config
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── ProtectionController.php
│   │   │   │   ├── SavingsController.php
│   │   │   │   ├── InvestmentController.php
│   │   │   │   ├── RetirementController.php
│   │   │   │   └── EstateController.php
│   │   │   └── Auth/
│   │   │       └── AuthController.php
│   │   ├── Requests/                  # Form validation
│   │   │   ├── ProtectionFormRequest.php
│   │   │   ├── SavingsFormRequest.php
│   │   │   └── ...
│   │   ├── Resources/                 # API responses
│   │   │   ├── ProtectionResource.php
│   │   │   ├── DashboardResource.php
│   │   │   └── ...
│   │   └── Middleware/
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── Protection/
│   │   │   ├── LifeInsurancePolicy.php
│   │   │   ├── CriticalIllnessPolicy.php
│   │   │   └── IncomeProtectionPolicy.php
│   │   ├── Savings/
│   │   │   ├── SavingsAccount.php
│   │   │   ├── FixedBond.php
│   │   │   └── SavingsGoal.php
│   │   ├── Investment/
│   │   │   ├── InvestmentAccount.php
│   │   │   ├── Holding.php
│   │   │   └── InvestmentGoal.php
│   │   ├── Retirement/
│   │   │   ├── DCPension.php
│   │   │   ├── DBPension.php
│   │   │   └── StatePension.php
│   │   └── Estate/
│   │       ├── Asset.php
│   │       ├── Liability.php
│   │       ├── Gift.php
│   │       └── Will.php
│   │
│   ├── Jobs/                          # Queue jobs
│   │   ├── RunMonteCarloSimulation.php
│   │   ├── GenerateWhatIfScenarios.php
│   │   └── GenerateRecommendations.php
│   │
│   └── Config/
│       └── uk_tax_rules.php          # Centralized UK tax config
│
├── database/
│   ├── migrations/
│   └── seeders/
│
├── resources/
│   ├── js/
│   │   ├── app.js
│   │   ├── components/              # Vue components (see frontend section)
│   │   ├── store/                   # Vuex store
│   │   └── router/                  # Vue Router
│   └── views/
│       └── app.blade.php            # Main SPA entry point
│
├── routes/
│   ├── api.php                      # API routes
│   └── web.php                      # Web routes
│
├── tests/
│   ├── Unit/                        # Unit tests for calculations
│   └── Feature/                     # Feature tests for API
│
└── storage/
    └── app/
        └── tax-configs/             # Historical tax year configs
```

#### Agent Classes Design

**Base Agent (Abstract Class)**
```php
<?php

namespace App\Agents;

use App\Services\UKTaxConfigService;
use App\Services\RecommendationEngine;

abstract class BaseAgent
{
    protected UKTaxConfigService $taxConfig;
    protected RecommendationEngine $recommendationEngine;

    public function __construct(
        UKTaxConfigService $taxConfig,
        RecommendationEngine $recommendationEngine
    ) {
        $this->taxConfig = $taxConfig;
        $this->recommendationEngine = $recommendationEngine;
    }

    abstract public function analyze(array $inputs): array;
    abstract public function generateRecommendations(array $analysis): array;
    abstract public function buildScenarios(array $inputs, array $analysis): array;
}
```

**Example: Protection Agent**
```php
<?php

namespace App\Agents;

use App\Services\FinancialCalculations\CoverageGapAnalyzer;
use App\Services\ScenarioBuilder;

class ProtectionAgent extends BaseAgent
{
    private CoverageGapAnalyzer $coverageAnalyzer;
    private ScenarioBuilder $scenarioBuilder;

    public function analyze(array $inputs): array
    {
        return [
            'current_coverage' => $this->calculateCurrentCoverage($inputs),
            'coverage_gaps' => $this->coverageAnalyzer->analyzeGaps($inputs),
            'risk_exposure' => $this->assessRiskExposure($inputs),
            'policy_quality' => $this->assessPolicyQuality($inputs),
        ];
    }

    public function generateRecommendations(array $analysis): array
    {
        return $this->recommendationEngine->generateProtectionRecommendations($analysis);
    }

    public function buildScenarios(array $inputs, array $analysis): array
    {
        return $this->scenarioBuilder->buildProtectionScenarios($inputs, $analysis);
    }

    // Private methods for specific calculations...
}
```

#### Service Layer Design

**Tax Calculator Service**
```php
<?php

namespace App\Services\FinancialCalculations;

use App\Services\UKTaxConfigService;

class TaxCalculator
{
    private UKTaxConfigService $taxConfig;

    public function calculateIncomeTax(float $grossIncome, string $taxYear = null): array
    {
        $config = $this->taxConfig->getTaxConfig($taxYear);

        // Implement income tax calculation logic
        // Returns: ['tax_due' => 12000, 'effective_rate' => 0.20, 'breakdown' => [...]]
    }

    public function calculateNationalInsurance(float $income, string $employmentType): float
    {
        // NI calculation
    }

    public function calculateCapitalGainsTax(float $gain, string $assetType, string $taxBand): float
    {
        // CGT calculation
    }

    public function calculateInheritanceTax(array $estate, array $exemptions): array
    {
        // IHT calculation with NRB, RNRB, exemptions, reliefs
    }
}
```

**Monte Carlo Simulator (for Investment & Retirement)**
```php
<?php

namespace App\Services\FinancialCalculations;

class MonteCarloSimulator
{
    private int $iterations = 1000; // Configurable

    public function simulatePortfolioGrowth(
        float $startingValue,
        float $monthlyContribution,
        float $expectedReturn,
        float $volatility,
        int $years
    ): array {
        $results = [];

        for ($i = 0; $i < $this->iterations; $i++) {
            $results[] = $this->runSingleSimulation(
                $startingValue,
                $monthlyContribution,
                $expectedReturn,
                $volatility,
                $years
            );
        }

        return [
            'percentile_10' => $this->percentile($results, 10),
            'percentile_25' => $this->percentile($results, 25),
            'percentile_50' => $this->percentile($results, 50), // Median
            'percentile_75' => $this->percentile($results, 75),
            'percentile_90' => $this->percentile($results, 90),
            'probability_of_success' => $this->calculateSuccessRate($results, $targetValue),
        ];
    }

    private function runSingleSimulation(...): float
    {
        // Single simulation run using normal distribution
        // Returns final portfolio value
    }
}
```

#### API Design

**RESTful API Endpoints**
```
Authentication:
POST   /api/auth/register
POST   /api/auth/login
POST   /api/auth/logout
GET    /api/auth/user

Dashboard:
GET    /api/dashboard              # Main dashboard data
GET    /api/dashboard/net-worth    # Net worth calculation
GET    /api/dashboard/cashflow     # Cashflow summary

Protection Module:
GET    /api/protection                      # Get all protection data
POST   /api/protection/analyze              # Analyze protection needs
GET    /api/protection/recommendations      # Get recommendations
POST   /api/protection/scenarios            # Run what-if scenarios
POST   /api/protection/policies             # Add policy
PUT    /api/protection/policies/{id}        # Update policy
DELETE /api/protection/policies/{id}        # Delete policy

Savings Module:
GET    /api/savings
POST   /api/savings/analyze
GET    /api/savings/recommendations
POST   /api/savings/scenarios
POST   /api/savings/accounts
GET    /api/savings/isa-allowance           # ISA allowance tracker

Investment Module:
GET    /api/investment
POST   /api/investment/analyze
GET    /api/investment/recommendations
POST   /api/investment/scenarios
POST   /api/investment/monte-carlo          # Trigger Monte Carlo (async)
GET    /api/investment/monte-carlo/{jobId}  # Get results
POST   /api/investment/holdings

Retirement Module:
GET    /api/retirement
POST   /api/retirement/analyze
GET    /api/retirement/recommendations
POST   /api/retirement/scenarios
POST   /api/retirement/pensions

Estate Module:
GET    /api/estate
POST   /api/estate/analyze
GET    /api/estate/recommendations
POST   /api/estate/scenarios
GET    /api/estate/iht-calculation          # IHT calculation
POST   /api/estate/gifts

Coordinating Agent:
POST   /api/coordinate/analyze-all          # Run all agents
GET    /api/coordinate/recommendations      # Coordinated recommendations

Tax Configuration:
GET    /api/config/tax-rules                # Get current tax config
GET    /api/config/tax-rules/{taxYear}      # Get specific tax year
```

---

### 2. Frontend Structure (Vue.js)

#### Component Architecture

**Main App Structure**
```
resources/js/
├── app.js                        # Main entry point
├── router/
│   └── index.js                  # Vue Router configuration
├── store/
│   ├── index.js                  # Vuex store root
│   └── modules/
│       ├── auth.js
│       ├── dashboard.js
│       ├── protection.js
│       ├── savings.js
│       ├── investment.js
│       ├── retirement.js
│       └── estate.js
├── services/
│   ├── api.js                    # Axios API service
│   └── calculations.js           # Client-side calculations
├── components/
│   ├── Layout/
│   │   ├── AppLayout.vue
│   │   ├── Navbar.vue
│   │   ├── Sidebar.vue
│   │   └── Footer.vue
│   │
│   ├── Dashboard/
│   │   ├── MainDashboard.vue
│   │   ├── ModuleCard.vue
│   │   ├── NetWorthSnapshot.vue
│   │   ├── CashflowSummary.vue
│   │   ├── ISAAllowanceTracker.vue
│   │   ├── GoalProgressOverview.vue
│   │   └── PriorityActionsFeed.vue
│   │
│   ├── Protection/
│   │   ├── ProtectionDashboard.vue
│   │   ├── Tabs/
│   │   │   ├── CurrentSituation.vue
│   │   │   ├── GapAnalysis.vue
│   │   │   ├── Recommendations.vue
│   │   │   ├── WhatIfScenarios.vue
│   │   │   └── PolicyDetails.vue
│   │   ├── Charts/
│   │   │   ├── CoverageAdequacyGauge.vue
│   │   │   ├── CoverageGapChart.vue
│   │   │   ├── RiskHeatmap.vue
│   │   │   └── PremiumBreakdownChart.vue
│   │   ├── Forms/
│   │   │   ├── PersonalDemographicsForm.vue
│   │   │   ├── CurrentCoverageForm.vue
│   │   │   └── ProtectionGoalsForm.vue
│   │   └── PolicyCard.vue
│   │
│   ├── Savings/
│   │   ├── SavingsDashboard.vue
│   │   ├── Tabs/
│   │   │   ├── CurrentSituation.vue
│   │   │   ├── EmergencyFund.vue
│   │   │   ├── SavingsGoals.vue
│   │   │   ├── Recommendations.vue
│   │   │   └── WhatIfScenarios.vue
│   │   ├── Charts/
│   │   │   ├── EmergencyFundGauge.vue
│   │   │   ├── LiquidityLadder.vue
│   │   │   ├── InterestRateComparison.vue
│   │   │   └── GoalProgressChart.vue
│   │   └── SavingsGoalCard.vue
│   │
│   ├── Investment/
│   │   ├── InvestmentDashboard.vue
│   │   ├── Tabs/
│   │   │   ├── PortfolioOverview.vue
│   │   │   ├── Holdings.vue
│   │   │   ├── Performance.vue
│   │   │   ├── Goals.vue
│   │   │   ├── Recommendations.vue
│   │   │   └── WhatIfScenarios.vue
│   │   ├── Charts/
│   │   │   ├── AssetAllocationChart.vue
│   │   │   ├── PerformanceLineChart.vue
│   │   │   ├── GeographicMap.vue
│   │   │   └── MonteCarloResults.vue
│   │   └── HoldingsTable.vue
│   │
│   ├── Retirement/
│   │   ├── RetirementDashboard.vue
│   │   ├── Tabs/
│   │   │   ├── RetirementReadiness.vue
│   │   │   ├── PensionInventory.vue
│   │   │   ├── ContributionsAllowances.vue
│   │   │   ├── Projections.vue
│   │   │   └── DecumulationPlanning.vue
│   │   ├── Charts/
│   │   │   ├── ReadinessGauge.vue
│   │   │   ├── IncomeProjectionChart.vue
│   │   │   ├── DrawdownSimulator.vue
│   │   │   └── AnnuityVsDrawdownComparison.vue
│   │   └── PensionCard.vue
│   │
│   ├── Estate/
│   │   ├── EstateDashboard.vue
│   │   ├── Tabs/
│   │   │   ├── OverviewNetWorth.vue
│   │   │   ├── IHTLiability.vue
│   │   │   ├── GiftingStrategy.vue
│   │   │   ├── PersonalAccounts.vue
│   │   │   └── DocumentationProbate.vue
│   │   ├── Charts/
│   │   │   ├── NetWorthStatement.vue
│   │   │   ├── IHTWaterfallChart.vue
│   │   │   ├── GiftingTimeline.vue
│   │   │   └── CashflowChart.vue
│   │   └── AssetLiabilityCard.vue
│   │
│   ├── Shared/                    # Reusable components
│   │   ├── Forms/
│   │   │   ├── DynamicForm.vue
│   │   │   ├── FormField.vue
│   │   │   └── FormSection.vue
│   │   ├── ScenarioBuilder.vue
│   │   ├── RecommendationCard.vue
│   │   ├── LoadingSpinner.vue
│   │   ├── ProgressBar.vue
│   │   ├── GaugeChart.vue
│   │   └── Modal.vue
│   │
│   └── Auth/
│       ├── Login.vue
│       ├── Register.vue
│       └── ForgotPassword.vue
│
└── utils/
    ├── formatters.js             # Currency, date formatting
    ├── validators.js             # Form validation helpers
    └── helpers.js                # General utilities
```

#### Vue Router Configuration

```javascript
// router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import MainDashboard from '../components/Dashboard/MainDashboard.vue'
import ProtectionDashboard from '../components/Protection/ProtectionDashboard.vue'
// ... other imports

const routes = [
  {
    path: '/',
    name: 'Dashboard',
    component: MainDashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/protection',
    name: 'Protection',
    component: ProtectionDashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/savings',
    name: 'Savings',
    component: SavingsDashboard,
    meta: { requiresAuth: true }
  },
  // ... other module routes
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { requiresGuest: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation guards
router.beforeEach((to, from, next) => {
  const isAuthenticated = store.getters['auth/isAuthenticated']

  if (to.meta.requiresAuth && !isAuthenticated) {
    next('/login')
  } else if (to.meta.requiresGuest && isAuthenticated) {
    next('/')
  } else {
    next()
  }
})

export default router
```

#### Vuex Store Structure

```javascript
// store/modules/protection.js
export default {
  namespaced: true,

  state: {
    policies: [],
    analysis: null,
    recommendations: [],
    scenarios: [],
    loading: false,
    error: null
  },

  getters: {
    totalCoverage: (state) => {
      return state.policies.reduce((sum, policy) => sum + policy.sumAssured, 0)
    },
    coverageAdequacyScore: (state) => {
      return state.analysis?.coverageAdequacyScore || 0
    }
  },

  mutations: {
    SET_POLICIES(state, policies) {
      state.policies = policies
    },
    SET_ANALYSIS(state, analysis) {
      state.analysis = analysis
    },
    SET_RECOMMENDATIONS(state, recommendations) {
      state.recommendations = recommendations
    },
    SET_LOADING(state, loading) {
      state.loading = loading
    }
  },

  actions: {
    async fetchPolicies({ commit }) {
      commit('SET_LOADING', true)
      try {
        const response = await api.get('/api/protection')
        commit('SET_POLICIES', response.data.policies)
      } catch (error) {
        commit('SET_ERROR', error.message)
      } finally {
        commit('SET_LOADING', false)
      }
    },

    async analyzeProtection({ commit }, inputs) {
      const response = await api.post('/api/protection/analyze', inputs)
      commit('SET_ANALYSIS', response.data.analysis)
      commit('SET_RECOMMENDATIONS', response.data.recommendations)
      return response.data
    }
  }
}
```

---

### 3. Database Schema Design

#### Core Tables

**Users**
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    date_of_birth DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Protection Module Tables**
```sql
CREATE TABLE life_insurance_policies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    policy_type VARCHAR(100), -- 'level_term', 'decreasing_term', etc.
    provider VARCHAR(255),
    policy_number VARCHAR(100),
    sum_assured DECIMAL(15, 2),
    premium_amount DECIMAL(10, 2),
    premium_frequency VARCHAR(50),
    policy_start_date DATE,
    policy_term_years INT,
    beneficiaries TEXT,
    in_trust BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE critical_illness_policies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    provider VARCHAR(255),
    coverage_amount DECIMAL(15, 2),
    premium_amount DECIMAL(10, 2),
    conditions_covered JSON, -- MySQL JSON type
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Similar tables for income_protection_policies, health_insurance_policies
```

**Savings Module Tables**
```sql
CREATE TABLE savings_accounts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    account_type VARCHAR(100), -- 'easy_access', 'fixed_bond', 'cash_isa'
    institution VARCHAR(255),
    account_number VARCHAR(100),
    current_balance DECIMAL(15, 2),
    interest_rate DECIMAL(5, 4),
    is_isa BOOLEAN DEFAULT FALSE,
    isa_subscription_year VARCHAR(10), -- '2024/25'
    maturity_date DATE,
    access_restrictions VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE savings_goals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    goal_name VARCHAR(255),
    target_amount DECIMAL(15, 2),
    target_date DATE,
    priority VARCHAR(50), -- 'high', 'medium', 'low'
    current_saved DECIMAL(15, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Investment Module Tables**
```sql
CREATE TABLE investment_accounts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    account_type VARCHAR(100), -- 'isa', 'gia', 'onshore_bond', etc.
    provider VARCHAR(255),
    account_number VARCHAR(100),
    current_value DECIMAL(15, 2),
    contributions_ytd DECIMAL(15, 2),
    platform_fee_percent DECIMAL(5, 4),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE holdings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    investment_account_id BIGINT UNSIGNED NOT NULL,
    asset_type VARCHAR(100), -- 'equity', 'bond', 'fund', 'etf'
    security_name VARCHAR(255),
    isin VARCHAR(50),
    ticker VARCHAR(20),
    quantity DECIMAL(15, 6),
    purchase_price DECIMAL(15, 4),
    purchase_date DATE,
    current_price DECIMAL(15, 4),
    current_value DECIMAL(15, 2),
    dividend_yield DECIMAL(5, 4),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_account_id (investment_account_id),
    FOREIGN KEY (investment_account_id) REFERENCES investment_accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Retirement Module Tables**
```sql
CREATE TABLE dc_pensions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    scheme_name VARCHAR(255),
    scheme_type VARCHAR(100), -- 'workplace', 'sipp', 'personal'
    provider VARCHAR(255),
    member_number VARCHAR(100),
    current_fund_value DECIMAL(15, 2),
    employee_contribution_percent DECIMAL(5, 2),
    employer_contribution_percent DECIMAL(5, 2),
    retirement_age INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE db_pensions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    scheme_name VARCHAR(255),
    scheme_type VARCHAR(100), -- 'final_salary', 'career_average'
    accrued_annual_pension DECIMAL(15, 2),
    normal_retirement_age INT,
    pensionable_service_years DECIMAL(5, 2),
    spouse_pension_percent DECIMAL(5, 2),
    inflation_protection VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE state_pension (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    ni_years_completed INT,
    state_pension_forecast DECIMAL(10, 2),
    state_pension_age INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Estate Module Tables**
```sql
CREATE TABLE assets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    asset_type VARCHAR(100), -- 'property', 'bank_account', 'investment', 'business'
    asset_name VARCHAR(255),
    current_value DECIMAL(15, 2),
    ownership_percent DECIMAL(5, 2) DEFAULT 100.00,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE liabilities (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    liability_type VARCHAR(100), -- 'mortgage', 'loan', 'credit_card'
    lender VARCHAR(255),
    outstanding_balance DECIMAL(15, 2),
    monthly_payment DECIMAL(10, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE gifts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    gift_type VARCHAR(50), -- 'PET', 'CLT'
    recipient_name VARCHAR(255),
    gift_date DATE,
    gift_value DECIMAL(15, 2),
    exemption_claimed VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Tax Configuration (JSON storage)**
```sql
CREATE TABLE tax_configurations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tax_year VARCHAR(10) UNIQUE NOT NULL, -- '2024/25'
    config JSON NOT NULL, -- MySQL JSON type
    is_active BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tax_year (tax_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Example JSON structure:
{
  "tax_year": "2024/25",
  "income_tax": {
    "personal_allowance": 12570,
    "basic_rate_band": {"threshold": 50270, "rate": 0.20},
    "higher_rate_band": {"threshold": 125140, "rate": 0.40},
    "additional_rate_band": {"rate": 0.45}
  },
  "national_insurance": { ... },
  "capital_gains_tax": { ... },
  "isa_allowances": {
    "total_allowance": 20000,
    "lifetime_isa_allowance": 4000,
    "junior_isa_allowance": 9000
  },
  "inheritance_tax": {
    "iht_rate": 0.40,
    "nil_rate_band": 325000,
    "residence_nil_rate_band": 175000,
    "rnrb_taper_threshold": 2000000
  }
}
```

**Calculation Results Cache**
```sql
CREATE TABLE calculation_cache (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    module VARCHAR(50), -- 'protection', 'investment', etc.
    calculation_type VARCHAR(100),
    input_hash VARCHAR(64), -- MD5 hash of inputs for cache lookup
    result JSON, -- MySQL JSON type
    expires_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cache_lookup (user_id, module, calculation_type, input_hash),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 4. Centralized UK Tax Configuration

#### Storage Format (JSON in MySQL)

**Benefits of JSON:**
- ✅ Flexible schema
- ✅ Easy to update annually
- ✅ Can query specific values (MySQL 5.7+ supports JSON path queries)
- ✅ Version control in database
- ✅ Historical configs preserved
- ⚠️ Note: MySQL JSON is less performant than PostgreSQL JSONB, but caching mitigates this

**Tax Configuration Service**
```php
<?php

namespace App\Services;

use App\Models\TaxConfiguration;
use Illuminate\Support\Facades\Cache;

class UKTaxConfigService
{
    public function getTaxConfig(?string $taxYear = null): array
    {
        $taxYear = $taxYear ?? $this->getCurrentTaxYear();

        return Cache::remember("tax_config_{$taxYear}", 3600, function () use ($taxYear) {
            $config = TaxConfiguration::where('tax_year', $taxYear)->firstOrFail();
            return $config->config;
        });
    }

    public function getCurrentTaxYear(): string
    {
        $month = date('n');
        $year = date('Y');

        // UK tax year: April 6 to April 5
        if ($month >= 4) {
            return $year . '/' . ($year + 1);
        } else {
            return ($year - 1) . '/' . $year;
        }
    }

    public function getIncomeTaxBands(?string $taxYear = null): array
    {
        $config = $this->getTaxConfig($taxYear);
        return $config['income_tax'];
    }

    public function getISAAllowance(?string $taxYear = null): int
    {
        $config = $this->getTaxConfig($taxYear);
        return $config['isa_allowances']['total_allowance'];
    }

    public function getIHTConfig(?string $taxYear = null): array
    {
        $config = $this->getTaxConfig($taxYear);
        return $config['inheritance_tax'];
    }
}
```

**Loading Tax Config**
```php
// In database seeder or config file
$taxConfig2024_25 = [
    'tax_year' => '2024/25',
    'income_tax' => [
        'personal_allowance' => 12570,
        'personal_allowance_taper_threshold' => 100000,
        'basic_rate' => ['threshold' => 50270, 'rate' => 0.20],
        'higher_rate' => ['threshold' => 125140, 'rate' => 0.40],
        'additional_rate' => ['rate' => 0.45],
    ],
    'national_insurance' => [
        'class1_employee' => [
            'lower_earnings_limit' => 6396,
            'primary_threshold' => 12570,
            'upper_earnings_limit' => 50270,
            'rate_below_uel' => 0.12,
            'rate_above_uel' => 0.02,
        ],
        'class1_employer' => [
            'secondary_threshold' => 9100,
            'rate' => 0.138,
        ],
    ],
    'capital_gains_tax' => [
        'allowance' => 3000,
        'basic_rate' => 0.10,
        'higher_rate' => 0.20,
        'residential_basic_rate' => 0.18,
        'residential_higher_rate' => 0.28,
    ],
    'dividend_tax' => [
        'allowance' => 500,
        'basic_rate' => 0.0875,
        'higher_rate' => 0.3375,
        'additional_rate' => 0.3935,
    ],
    'savings_interest' => [
        'personal_savings_allowance_basic' => 1000,
        'personal_savings_allowance_higher' => 500,
        'personal_savings_allowance_additional' => 0,
    ],
    'isa_allowances' => [
        'total_allowance' => 20000,
        'lifetime_isa_allowance' => 4000,
        'junior_isa_allowance' => 9000,
    ],
    'pension_allowances' => [
        'annual_allowance' => 60000,
        'money_purchase_annual_allowance' => 10000,
        'tapered_threshold_income' => 200000,
        'tapered_adjusted_income' => 260000,
        'minimum_tapered_allowance' => 10000,
    ],
    'inheritance_tax' => [
        'iht_rate' => 0.40,
        'charity_iht_rate' => 0.36,
        'nil_rate_band' => 325000,
        'residence_nil_rate_band' => 175000,
        'rnrb_taper_threshold' => 2000000,
        'rnrb_taper_rate' => 1, // £1 reduction for every £2 over threshold
        'pets_taper_years' => 7,
        'clts_lookback_years' => 14,
    ],
    'gifting_exemptions' => [
        'annual_exemption' => 3000,
        'small_gift_exemption' => 250,
        'wedding_gift_child' => 5000,
        'wedding_gift_grandchild' => 2500,
        'wedding_gift_other' => 1000,
    ],
    'state_pension' => [
        'full_new_state_pension_weekly' => 221.20,
        'full_new_state_pension_annual' => 11502.40,
    ],
];

// Store in database
TaxConfiguration::create([
    'tax_year' => '2024/25',
    'config' => $taxConfig2024_25,
    'is_active' => true,
]);
```

---

## Performance Optimization Strategies

### 1. Caching Strategy

**What to Cache:**
- ✅ Tax configuration (Memcached, 1 hour TTL)
- ✅ Monte Carlo simulation results (Memcached, cache key based on input hash)
- ✅ Pension projections (Memcached, 24 hours)
- ✅ Dashboard calculations (Memcached, 30 minutes)
- ✅ What-if scenario results (Memcached, 1 hour)

**Laravel Caching Implementation with Memcached:**
```php
use Illuminate\Support\Facades\Cache;

// Cache tax calculation
$taxDue = Cache::remember("income_tax_{$userId}_{$income}_{$taxYear}", 3600, function () use ($income, $taxYear) {
    return $this->taxCalculator->calculateIncomeTax($income, $taxYear);
});

// Cache Monte Carlo results
$inputHash = md5(json_encode($inputs));
$results = Cache::remember("monte_carlo_{$userId}_{$inputHash}", 86400, function () use ($inputs) {
    return $this->monteCarloSimulator->simulate($inputs);
});

// Note: Laravel's Cache facade works identically with Memcached driver
// Configure CACHE_DRIVER=memcached in .env
```

### 2. Queue System for Long-Running Calculations

**Use Database-Backed Laravel Queues for:**
- Monte Carlo simulations
- What-if scenario generation (multiple scenarios at once)
- Report generation

**Queue Configuration:**
```php
// config/queue.php
'default' => env('QUEUE_CONNECTION', 'database'),

'connections' => [
    'database' => [
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
    ],
],
```

**Queue Table Migration:**
```bash
php artisan queue:table
php artisan migrate
```

**Implementation:**
```php
// Dispatch job
$job = new RunMonteCarloSimulation($userId, $inputs);
dispatch($job);

// Return job ID to frontend
return response()->json(['job_id' => $job->getId()]);

// Frontend polls for results
GET /api/investment/monte-carlo/{jobId}
```

**Queue Worker:**
```bash
# Run queue worker with Supervisor (production)
php artisan queue:work database --queue=default --tries=3
```

**Why Database Queues Work for FPS:**
- ✅ Monte Carlo simulations are infrequent (user-initiated)
- ✅ MySQL handles queue operations efficiently with proper indexes
- ✅ Simpler than Redis queues (one less service)
- ✅ Jobs persist across application restarts
- ✅ Sufficient performance for low-to-medium volume

### 3. Database Optimization

**Indexes:**
```sql
-- Index frequently queried fields
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_policies_user_id ON life_insurance_policies(user_id);
CREATE INDEX idx_accounts_user_id ON savings_accounts(user_id);
CREATE INDEX idx_holdings_account_id ON holdings(investment_account_id);

-- Composite indexes for common queries
CREATE INDEX idx_cache_lookup ON calculation_cache(user_id, module, calculation_type, input_hash);
```

**Query Optimization:**
- Use Eloquent with eager loading to avoid N+1 queries
- Use database views for complex aggregations
- Consider materialized views for expensive calculations

### 4. Frontend Performance

**Code Splitting:**
- Lazy load module components (only load Protection when user visits)
- Use Vue Router's dynamic imports

**Chart Performance:**
- Limit data points (e.g., monthly data for long-term charts, not daily)
- Use Chart.js decimation plugin for large datasets
- Lazy load charts (only render when visible)

**State Management:**
- Cache API responses in Vuex
- Implement request debouncing for live calculations

---

## Monte Carlo Simulation Performance

### Challenge
Monte Carlo simulations require 1,000-10,000 iterations. PHP will be slower than Python/NumPy.

### Solutions

**1. Limit Iterations (Most Practical)**
- Use 1,000 iterations instead of 10,000
- Still provides good statistical accuracy for FPS demo purposes
- Run time: ~1-2 seconds vs 10-20 seconds

**2. Optimize PHP Code**
```php
// Use efficient random number generation
$randomReturns = [];
for ($i = 0; $i < 12 * $years; $i++) {
    // Box-Muller transform for normal distribution
    $u1 = mt_rand() / mt_getrandmax();
    $u2 = mt_rand() / mt_getrandmax();
    $randomReturns[] = sqrt(-2 * log($u1)) * cos(2 * pi() * $u2);
}
```

**3. Use Background Queues**
- Run Monte Carlo in background job
- Return job ID immediately to user
- Frontend polls for results
- User sees "Calculating..." message

**4. Pre-compute Common Scenarios**
- Pre-compute scenarios for common inputs (e.g., £50k salary, £100k portfolio)
- Store in cache
- Use these as starting point for user-specific calculations

**5. Consider PHP Extension (Advanced)**
- If performance is critical, could write Monte Carlo in C as PHP extension
- Overkill for MVP, but possible for production

**6. Alternative: Python Microservice (If Needed)**
- If PHP performance is truly insufficient:
  - Create small Python Flask API for Monte Carlo only
  - PHP calls Python service via HTTP
  - Adds complexity, only if absolutely necessary

**Recommendation:** Start with optimized PHP (1,000 iterations + caching + queues). This should be sufficient for demo.

---

## Development Roadmap

### Phase 1: Foundation (Weeks 1-3)

**Week 1: Project Setup**
- ✅ Initialize Laravel project
- ✅ Set up MySQL database
- ✅ Configure Laravel authentication
- ✅ Set up Vue.js with Vite
- ✅ Install dependencies (Chart.js, Tailwind CSS, etc.)
- ✅ Create base database migrations
- ✅ Set up Git repository

**Week 2: Core Infrastructure**
- ✅ Implement UKTaxConfigService
- ✅ Seed tax configuration for 2024/25
- ✅ Create base Agent abstract class
- ✅ Set up API routing structure
- ✅ Create Vuex store structure
- ✅ Build layout components (navbar, sidebar)

**Week 3: Authentication & Dashboard**
- ✅ Implement user authentication
- ✅ Build main dashboard layout
- ✅ Create module cards (clickable)
- ✅ Implement basic navigation

### Phase 2: Protection Module (Weeks 4-6)

**Week 4: Backend**
- ✅ Create Protection models (life insurance, critical illness, income protection)
- ✅ Build ProtectionAgent class
- ✅ Implement coverage gap calculations
- ✅ Implement API endpoints

**Week 5: Frontend**
- ✅ Build Protection dashboard
- ✅ Create dynamic forms for inputs
- ✅ Build charts (coverage gauge, gap chart)
- ✅ Integrate with backend API

**Week 6: Scenarios & Recommendations**
- ✅ Implement what-if scenarios
- ✅ Build recommendation engine
- ✅ Add scenario builder UI
- ✅ Testing and refinement

### Phase 3: Savings Module (Weeks 7-9)

**Week 7: Backend**
- ✅ Create Savings models
- ✅ Build SavingsAgent class
- ✅ Implement emergency fund calculations
- ✅ ISA allowance tracking

**Week 8: Frontend**
- ✅ Build Savings dashboard
- ✅ Emergency fund gauge
- ✅ Savings goals UI
- ✅ Liquidity ladder visualization

**Week 9: Integration & Polish**
- ✅ Connect ISA allowance across Savings + Investment modules
- ✅ Scenarios and recommendations
- ✅ Testing

### Phase 4: Investment Module (Weeks 10-13)

**Week 10: Backend**
- ✅ Create Investment models
- ✅ Build InvestmentAgent class
- ✅ Implement portfolio calculations

**Week 11: Monte Carlo Simulation**
- ✅ Build MonteCarloSimulator
- ✅ Implement queue system
- ✅ Optimize performance
- ✅ Test extensively

**Week 12: Frontend**
- ✅ Build Investment dashboard
- ✅ Asset allocation charts
- ✅ Holdings table
- ✅ Performance charts

**Week 13: Scenarios & Goals**
- ✅ Monte Carlo results visualization
- ✅ What-if scenarios
- ✅ Goal probability calculations
- ✅ Testing

### Phase 5: Retirement Module (Weeks 14-17)

**Week 14: Backend**
- ✅ Create Retirement models (DC, DB, State Pension)
- ✅ Build RetirementAgent class
- ✅ Pension projections

**Week 15: Calculations**
- ✅ Annual allowance tracking
- ✅ Contribution optimization
- ✅ Retirement income projections
- ✅ Decumulation calculations

**Week 16: Frontend**
- ✅ Build Retirement dashboard
- ✅ Readiness gauge
- ✅ Pension inventory
- ✅ Contribution calculator

**Week 17: Decumulation Planning**
- ✅ Drawdown simulator
- ✅ Annuity vs drawdown comparison
- ✅ Scenarios and testing

### Phase 6: Estate Planning Module (Weeks 18-21)

**Week 18: Backend**
- ✅ Create Estate models (assets, liabilities, gifts)
- ✅ Build EstateAgent class
- ✅ Net worth calculations

**Week 19: IHT Calculations**
- ✅ Build IHTCalculator
- ✅ NRB, RNRB calculations
- ✅ Gifting strategy modeling
- ✅ PETs and CLTs tracking

**Week 20: Personal Accounts**
- ✅ P&L statement generation
- ✅ Cashflow analysis
- ✅ Balance sheet

**Week 21: Frontend**
- ✅ Build Estate dashboard
- ✅ Net worth visualization
- ✅ IHT waterfall chart
- ✅ Gifting timeline
- ✅ Testing

### Phase 7: Coordinating Agent (Week 22)

- ✅ Build CoordinatingAgent class
- ✅ Cross-module recommendation synthesis
- ✅ Priority actions feed
- ✅ Conflict resolution
- ✅ Integration testing

### Phase 8: Testing & Refinement (Weeks 23-24)

**Week 23: Testing**
- ✅ Unit tests for financial calculations (Pest)
- ✅ Feature tests for API endpoints (Pest)
- ✅ Architecture tests (Pest)
- ✅ Frontend component tests (Vue Test Utils)
- ✅ API testing with Postman collections

**Week 24: Polish & Documentation**
- ✅ UI/UX refinement
- ✅ Performance optimization
- ✅ Documentation
- ✅ Deployment preparation

### Total Timeline: **24 weeks (6 months)**

---

## Testing Strategy

### 1. Unit Tests (Pest)

**Why Pest?**
- ✅ Built on PHPUnit (all PHPUnit features available)
- ✅ Cleaner, more readable syntax
- ✅ Better developer experience
- ✅ Included by default in Laravel
- ✅ Architecture testing capabilities
- ✅ Excellent for financial calculation testing

**Installation:**
```bash
composer require pestphp/pest --dev --with-all-dependencies
composer require pestphp/pest-plugin-laravel --dev
php artisan pest:install
```

**Test Financial Calculations:**
```php
<?php

use App\Services\FinancialCalculations\TaxCalculator;
use App\Services\FinancialCalculations\IHTCalculator;
use App\Services\UKTaxConfigService;

test('income tax basic rate calculation', function () {
    $calculator = new TaxCalculator(new UKTaxConfigService());
    $result = $calculator->calculateIncomeTax(30000, '2024/25');

    expect($result['tax_due'])->toBe(3486) // (30000 - 12570) * 0.20
        ->and($result['effective_rate'])->toBeCloseTo(0.116, 3);
});

test('income tax higher rate calculation', function () {
    $calculator = new TaxCalculator(new UKTaxConfigService());
    $result = $calculator->calculateIncomeTax(60000, '2024/25');

    // PA: £12,570, Basic: £37,700 @ 20%, Higher: £9,730 @ 40%
    $expected = (37700 * 0.20) + (9730 * 0.40); // £11,432
    expect($result['tax_due'])->toBe($expected);
});

test('IHT calculation with NRB and RNRB', function () {
    $calculator = new IHTCalculator(new UKTaxConfigService());
    $estate = ['total_value' => 800000, 'main_residence' => 400000];
    $result = $calculator->calculate($estate);

    // Estate: £800k, NRB: £325k, RNRB: £175k, Total threshold: £500k
    // Taxable: £300k, IHT @ 40%: £120k
    expect($result['iht_due'])->toBe(120000)
        ->and($result['nil_rate_band'])->toBe(325000)
        ->and($result['residence_nil_rate_band'])->toBe(175000);
});

test('Monte Carlo simulation returns expected structure', function () {
    $simulator = new MonteCarloSimulator();
    $result = $simulator->simulatePortfolioGrowth(
        startingValue: 100000,
        monthlyContribution: 500,
        expectedReturn: 0.07,
        volatility: 0.15,
        years: 10
    );

    expect($result)
        ->toHaveKeys(['percentile_10', 'percentile_25', 'percentile_50', 'percentile_75', 'percentile_90'])
        ->and($result['percentile_50'])->toBeGreaterThan($result['percentile_10'])
        ->and($result['percentile_90'])->toBeGreaterThan($result['percentile_50']);
});

// Dataset usage for testing multiple scenarios
test('capital gains tax calculation', function ($gain, $assetType, $taxBand, $expected) {
    $calculator = new TaxCalculator(new UKTaxConfigService());
    $result = $calculator->calculateCapitalGainsTax($gain, $assetType, $taxBand);

    expect($result)->toBe($expected);
})->with([
    [10000, 'shares', 'basic', 700],      // (10000 - 3000) * 0.10
    [10000, 'shares', 'higher', 1400],    // (10000 - 3000) * 0.20
    [10000, 'property', 'basic', 1260],   // (10000 - 3000) * 0.18
    [10000, 'property', 'higher', 1960],  // (10000 - 3000) * 0.28
]);
```

### 2. Feature Tests (API with Pest)

```php
<?php

use App\Models\User;

test('analyze protection needs endpoint returns correct structure', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/protection/analyze', [
        'annual_income' => 50000,
        'mortgage_balance' => 200000,
        'dependents' => 2,
        'existing_coverage' => 100000,
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'analysis' => [
                'coverage_gaps',
                'adequacy_score',
                'recommendations'
            ]
        ]);

    expect($response->json('analysis.adequacy_score'))->toBeGreaterThan(0)
        ->and($response->json('analysis.adequacy_score'))->toBeLessThanOrEqual(100);
});

test('investment Monte Carlo endpoint queues job', function () {
    Queue::fake();
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/investment/monte-carlo', [
        'starting_value' => 100000,
        'monthly_contribution' => 1000,
        'expected_return' => 0.07,
        'volatility' => 0.15,
        'years' => 20
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['job_id']);

    Queue::assertPushed(RunMonteCarloSimulation::class);
});

test('retirement projection calculates correctly', function () {
    $user = User::factory()->create();
    $pension = DCPension::factory()->for($user)->create([
        'current_fund_value' => 100000,
        'employee_contribution_percent' => 5,
        'employer_contribution_percent' => 3,
    ]);

    $response = $this->actingAs($user)->postJson('/api/retirement/analyze');

    expect($response->status())->toBe(200)
        ->and($response->json('analysis.total_pension_pot'))->toBeGreaterThan(100000);
});
```

### 3. Architecture Tests (Pest)

Pest's architecture testing ensures code quality:

```php
<?php

test('agents extend BaseAgent')
    ->expect('App\Agents')
    ->toExtend('App\Agents\BaseAgent')
    ->not->toBeUsed();

test('controllers are in correct namespace')
    ->expect('App\Http\Controllers')
    ->toUseNothing(['App\Models']); // Controllers should not directly use models

test('services are only used by agents and controllers')
    ->expect('App\Services')
    ->toOnlyBeUsedIn(['App\Agents', 'App\Http\Controllers']);

test('models do not have public properties')
    ->expect('App\Models')
    ->not->toHavePublicProperties();

test('financial calculations are pure functions')
    ->expect('App\Services\FinancialCalculations')
    ->classes->not->toHaveMethod('__construct'); // Stateless services
```

### 3. Frontend Tests (Vue Test Utils)

```javascript
import { mount } from '@vue/test-utils'
import EmergencyFundGauge from '@/components/Savings/EmergencyFundGauge.vue'

describe('EmergencyFundGauge', () => {
  it('displays correct runway in months', () => {
    const wrapper = mount(EmergencyFundGauge, {
      props: {
        savingsBalance: 15000,
        monthlyExpenses: 2500
      }
    })

    expect(wrapper.text()).toContain('6 months') // 15000 / 2500
  })
})
```

---

## Deployment Considerations

### Production Environment

**Recommended Stack:**
- **Server**: Ubuntu 22.04 LTS
- **Web Server**: Nginx (reverse proxy)
- **PHP**: PHP-FPM 8.2
- **Database**: MySQL 8.0
- **Cache**: Memcached 1.6+
- **Queue Worker**: Laravel Queue Worker (Supervisor)
- **SSL**: Let's Encrypt (Certbot)

**Hosting Options:**
- **AWS** (EC2, RDS MySQL, ElastiCache Memcached)
- **DigitalOcean** (Droplets, Managed MySQL, Managed Memcached)
- **Laravel Forge** (simplifies deployment, supports Memcached)
- **Ploi** (alternative to Forge)

**Memcached Installation:**
```bash
# Ubuntu/Debian
sudo apt-get install memcached libmemcached-tools
sudo apt-get install php-memcached

# Start Memcached service
sudo systemctl start memcached
sudo systemctl enable memcached

# Configure memory limit (default 64MB, increase for FPS)
sudo nano /etc/memcached.conf
# Set: -m 256  (256MB for cache)

# Restart service
sudo systemctl restart memcached
```

---

## Security Considerations

### 1. Financial Data Protection

- ✅ Encrypt sensitive fields (policy numbers, account numbers)
- ✅ Use HTTPS only (enforce SSL)
- ✅ Implement CSRF protection (Laravel default)
- ✅ Use Laravel Sanctum for API authentication
- ✅ Implement rate limiting on API endpoints

### 2. Input Validation

- ✅ Validate all form inputs (Laravel Form Requests)
- ✅ Sanitize user inputs
- ✅ Use parameterized queries (Eloquent ORM)

### 3. Audit Trail

```php
// Log important actions
Log::info('User calculated IHT', [
    'user_id' => $user->id,
    'estate_value' => $estateValue,
    'iht_liability' => $ihtLiability,
]);
```

---

## Summary & Recommendations

### ✅ Final Tech Stack Recommendation

| Component | Technology | Status |
|-----------|-----------|--------|
| **Backend Framework** | Laravel 10.x | ✅ Recommended |
| **Backend Language** | PHP 8.2+ | ✅ Acceptable with optimizations |
| **Database** | MySQL 8.0+ | ✅ Good choice with InnoDB |
| **Frontend Framework** | Vue.js 3 | ✅ Strongly recommended (add to stack) |
| **Charting** | ApexCharts | ✅ Recommended |
| **CSS Framework** | Tailwind CSS | ✅ Recommended |
| **Caching** | Memcached | ✅ Recommended |
| **Queue System** | Laravel Queues (Database) | ✅ Recommended |
| **Testing** | Pest | ✅ Recommended |
| **API Testing** | Postman | ✅ Recommended |

### Key Success Factors

1. **Use Laravel Framework**: Don't build in vanilla PHP
2. **Add Vue.js**: Essential for complex interactive dashboards
3. **Implement Memcached Caching**: Critical for performance
4. **Use Database Queues**: For Monte Carlo and long calculations
5. **Optimize Monte Carlo**: 1,000 iterations, caching, background processing
6. **Structure Code Well**: Agent classes, service layer, clean architecture
7. **Test Financial Calculations**: Unit tests are critical for accuracy

### Potential Issues & Mitigations

| Issue | Mitigation |
|-------|-----------|
| **Monte Carlo Performance** | Limit iterations, use queues, cache results |
| **Complex UI without Framework** | Add Vue.js (strongly recommended) |
| **Type Safety in PHP** | Use strict types, type hints, static analysis (PHPStan) |
| **Financial Calculation Accuracy** | Extensive unit testing, use `brick/money` for precision |
| **No Queue Support in Memcached** | Use database-backed queues (sufficient for FPS needs) |

### Is This Tech Stack Viable?

**Answer: YES, with the enhancements recommended above.**

The combination of **Laravel + MySQL + Vue.js + Memcached** is a solid, production-ready stack that can handle the FPS requirements. The key is proper architecture, optimization, and using the right tools for the job.

**Key Points About MySQL:**
- ✅ InnoDB engine provides ACID compliance essential for financial data
- ✅ JSON support (MySQL 5.7+) handles tax configuration well
- ✅ Memcached caching mitigates any JSON query performance differences vs PostgreSQL JSONB
- ✅ Widely supported and cost-effective hosting options

**Key Points About Memcached:**
- ✅ Faster than Redis for simple key-value caching (FPS primary use case)
- ✅ Multi-threaded architecture better for concurrent users
- ✅ Simpler than Redis (no persistence, no complex data structures)
- ⚠️ No queue support - using database-backed queues instead
- ⚠️ No data persistence - acceptable since caching is transient by nature

### Next Steps

1. ✅ Approve this tech stack architecture
2. ✅ Set up development environment
3. ✅ Begin Phase 1: Foundation (Laravel + Vue.js setup)
4. ✅ Implement centralized UK tax configuration
5. ✅ Build first module (Protection) as proof of concept
6. ✅ Iterate and refine

---

## Questions & Clarifications

If you have any questions about this architecture, need clarification on specific aspects, or want to discuss alternative approaches, please ask. This document provides a comprehensive blueprint for building the FPS system, but flexibility and iteration are important as development progresses.

**Document Version**: 1.3 (Updated Testing & Charting Stack)
**Date**: 2025-10-13
**Author**: FPS Architecture Planning
**Changelog**:
- v1.3: Standardized testing framework and charting library
  - Changed charting from "Chart.js + ApexCharts" to **ApexCharts only**
  - Comprehensive ApexCharts section with FPS module chart mapping
  - Changed testing framework from "PHPUnit + Pest" to **Pest only**
  - Updated testing section with Pest examples and architecture tests
  - Changed API testing from "Postman / Insomnia" to **Postman only**
  - Updated tech stack tables throughout document
  - Added detailed rationale for each decision
- v1.2: Changed caching from Redis to Memcached
  - Added comprehensive Redis vs Memcached comparison section
  - Updated all caching references to Memcached throughout document
  - Changed queue system to database-backed queues
  - Updated deployment section with Memcached installation instructions
  - Updated architecture diagrams to show Memcached
  - Added detailed tradeoff analysis and rationale
- v1.1: Changed database from PostgreSQL to MySQL 8.0+
  - Updated all SQL schemas to MySQL syntax
  - Changed JSONB to JSON
  - Added InnoDB engine specifications
  - Updated all references throughout document
