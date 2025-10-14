# FPS (Financial Planning System) - Features & Technical Implementation Guide

**Document Version**: 1.0
**Date**: 2025-10-13
**Project**: FPS Financial Planning System - Technical Architecture
**Status**: Ready for Development

---

## Document Purpose

This document provides detailed technical specifications for implementing each feature of the FPS system, including:
- Feature breakdown by module with implementation approach
- Complete technology stack with rationale
- Database schema design
- API endpoint specifications
- Component architecture
- Testing strategy
- Deployment considerations

**Target Audience**: Development team, technical leads, architects

---

## Table of Contents

1. [Technology Stack](#technology-stack)
2. [Architecture Overview](#architecture-overview)
3. [Feature Implementation by Module](#feature-implementation-by-module)
   - [User Management](#1-user-management)
   - [Protection Module](#2-protection-module)
   - [Savings Module](#3-savings-module)
   - [Investment Module](#4-investment-module)
   - [Retirement Module](#5-retirement-module)
   - [Estate Planning Module](#6-estate-planning-module)
   - [Main Dashboard](#7-main-dashboard)
   - [Coordinating Agent](#8-coordinating-agent)
4. [Database Schema](#database-schema)
5. [API Endpoints](#api-endpoints)
6. [Frontend Components](#frontend-components)
7. [Testing Strategy](#testing-strategy)
8. [Deployment](#deployment)

---

## Technology Stack

### Complete Tech Stack

| Component | Technology | Version | Rationale |
|-----------|-----------|---------|-----------|
| **Backend Framework** | Laravel | 10.x | Modern PHP framework with MVC, ORM, caching, queues |
| **Backend Language** | PHP | 8.2+ | Typed properties, performance improvements, Laravel compatibility |
| **Database** | MySQL | 8.0+ | ACID compliance (InnoDB), JSON support, reliable for financial data |
| **Cache** | Memcached | 1.6+ | Fast key-value caching, multi-threaded, optimized for simple operations |
| **Queue System** | Laravel Queues (Database) | - | Background processing for Monte Carlo, sufficient for FPS volume |
| **Frontend Framework** | Vue.js | 3.x | Reactive, component-based, excellent Laravel integration |
| **State Management** | Vuex | 4.x | Centralized state for complex cross-module data |
| **Charting Library** | ApexCharts | Latest | Comprehensive chart types (waterfall, heatmap, timeline, gauges), excellent interactivity |
| **CSS Framework** | Tailwind CSS | 3.x | Utility-first, rapid UI development, customizable |
| **Build Tool** | Vite | Latest | Fast, modern bundler, excellent Vue integration |
| **Testing Framework** | Pest | Latest | Modern PHP testing, built on PHPUnit, better developer experience |
| **API Testing** | Postman | Latest | Industry standard for API development and testing |
| **Version Control** | Git | Latest | Source code management |

### Why This Stack?

**Laravel + PHP 8.2+:**
- ✅ Mature ecosystem with extensive packages
- ✅ Eloquent ORM for database operations
- ✅ Built-in authentication, caching, queues
- ✅ Service layer pattern perfect for agent architecture
- ✅ Excellent documentation and community

**MySQL 8.0+:**
- ✅ InnoDB engine provides ACID compliance (critical for financial data)
- ✅ JSON data type for flexible tax configuration storage
- ✅ Proven reliability and performance
- ✅ Wide hosting support and cost-effective

**Vue.js 3:**
- ✅ Reactive data binding perfect for live calculations
- ✅ Component-based architecture for reusable dashboard elements
- ✅ Easier learning curve than React
- ✅ Popular in Laravel community (excellent integration)

**ApexCharts:**
- ✅ Covers ALL FPS chart types (line, bar, pie, donut, heatmap, waterfall, timeline, radial gauges)
- ✅ Built-in interactivity (zoom, pan, hover, tooltips)
- ✅ Professional aesthetics for financial dashboards
- ✅ `vue3-apexcharts` wrapper for seamless Vue integration

**Memcached:**
- ✅ Faster than Redis for simple key-value caching (FPS primary use case)
- ✅ Multi-threaded (better for concurrent users)
- ✅ Simpler than Redis (no persistence, no complex data structures)
- ⚠️ No queue support → using database-backed Laravel queues instead

**Pest:**
- ✅ Built on PHPUnit (all PHPUnit features + more)
- ✅ Cleaner syntax: `test()`, `it()`, `expect()`
- ✅ Architecture testing capabilities
- ✅ Included by default in Laravel
- ✅ Better developer experience

**Postman:**
- ✅ Industry standard for API development
- ✅ Comprehensive feature set (collections, environments, testing, mocking)
- ✅ Team collaboration and documentation generation
- ✅ CI/CD integration

---

## Architecture Overview

### Three-Tier Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                   PRESENTATION LAYER                         │
│  Vue.js 3 Components, ApexCharts, Vuex, Vue Router          │
└─────────────────────────────────────────────────────────────┘
                            ↕ REST API (JSON)
┌─────────────────────────────────────────────────────────────┐
│                    APPLICATION LAYER                         │
│  Laravel Controllers, Agents, Services, Jobs, Middleware    │
│  • ProtectionAgent  • SavingsAgent  • InvestmentAgent      │
│  • RetirementAgent  • EstateAgent   • CoordinatingAgent     │
│  • TaxCalculator    • MonteCarloSimulator  • IHTCalculator  │
└─────────────────────────────────────────────────────────────┘
                            ↕ Eloquent ORM
┌─────────────────────────────────────────────────────────────┐
│                       DATA LAYER                             │
│  MySQL Database (Users, Policies, Accounts, Pensions, etc.) │
│  + Memcached (Tax Config, Calculation Results, Sessions)    │
└─────────────────────────────────────────────────────────────┘
```

### Agent-Based Architecture

Each module has an **Agent** class that:
1. Takes user inputs from dynamic forms
2. Performs domain-specific calculations using utility services
3. Generates recommendations based on analysis
4. Returns structured output to the API layer

**Agent Class Structure:**
```php
abstract class BaseAgent {
    abstract public function analyze(array $inputs): array;
    abstract public function generateRecommendations(array $analysis): array;
    abstract public function buildScenarios(array $inputs, array $analysis): array;
}
```

---

## Feature Implementation by Module

## 1. User Management

### Feature 1.1: User Registration & Authentication

**Implementation:**
- **Backend**: Laravel Breeze or Sanctum for authentication
- **Frontend**: Vue components for login, register, password reset
- **Database**: `users` table with encrypted passwords (bcrypt)

**API Endpoints:**
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login user
- `POST /api/auth/logout` - Logout user
- `GET /api/auth/user` - Get authenticated user details
- `POST /api/auth/password/email` - Send password reset email
- `POST /api/auth/password/reset` - Reset password

**Key Components:**
- `Login.vue` - Login form
- `Register.vue` - Registration form
- `ForgotPassword.vue` - Password reset request
- `ResetPassword.vue` - Password reset form

**Testing:**
- Unit tests for authentication logic
- Feature tests for API endpoints
- Form validation tests

---

## 2. Protection Module

### Feature 2.1: Protection Data Collection

**Implementation:**
- **Backend**:
  - Models: `LifeInsurancePolicy`, `CriticalIllnessPolicy`, `IncomeProtectionPolicy`, `HealthInsurancePolicy`
  - Form validation: Laravel Form Requests
- **Frontend**:
  - Dynamic forms with conditional fields
  - Components: `PersonalDemographicsForm.vue`, `CurrentCoverageForm.vue`, `ProtectionGoalsForm.vue`
- **Database**: Protection tables (see schema section)

**API Endpoints:**
- `GET /api/protection` - Get all user's protection data
- `POST /api/protection/policies` - Add new policy
- `PUT /api/protection/policies/{id}` - Update policy
- `DELETE /api/protection/policies/{id}` - Delete policy

**Testing:**
- Validation tests for form inputs
- Model relationship tests
- CRUD operation tests

---

### Feature 2.2: Coverage Gap Analysis

**Implementation:**
- **Backend**:
  - `ProtectionAgent.php` - Main agent class
  - `CoverageGapAnalyzer.php` - Service for gap calculations
  - Calculations:
    - Human capital: Income × multiplier × years to retirement
    - Debt protection: Mortgage + loans + credit cards
    - Education funding: £9,000/year × number of children × years
    - Final expenses: £5,000-£10,000
- **Frontend**:
  - Charts: Heatmap (ApexCharts heatmap)
  - Components: `CoverageGapChart.vue`, `CoverageAdequacyGauge.vue`

**API Endpoints:**
- `POST /api/protection/analyze` - Run coverage gap analysis

**Calculation Example:**
```php
public function calculateHumanCapital(float $income, int $age, int $retirementAge): float
{
    $yearsToRetirement = $retirementAge - $age;
    $multiplier = 10; // Standard rule of thumb
    return $income * $multiplier * min($yearsToRetirement, 10);
}
```

**Caching:**
- Cache key: `protection_gap_{user_id}_{input_hash}`
- TTL: 1 hour

**Testing:**
- Unit tests for human capital calculation
- Unit tests for gap analysis logic
- Integration tests with different input scenarios

---

### Feature 2.3: Protection Recommendations

**Implementation:**
- **Backend**:
  - `RecommendationEngine.php` - Service for generating recommendations
  - Priority scoring algorithm
  - Rule-based logic (e.g., "If mortgage gap > £100k → High priority")
- **Frontend**:
  - Components: `RecommendationCard.vue` (draggable for reordering)

**API Endpoints:**
- `GET /api/protection/recommendations` - Get prioritized recommendations

**Recommendation Structure:**
```php
[
    'priority' => 'high', // high, medium, low
    'category' => 'life_insurance',
    'action' => 'Increase life cover to £500,000',
    'rationale' => 'Coverage gap of £300,000 for mortgage and income replacement',
    'impact' => '£300,000 additional protection',
    'estimated_cost' => '£30/month',
]
```

**Testing:**
- Unit tests for priority scoring
- Tests for recommendation generation logic

---

### Feature 2.4: What-If Scenarios

**Implementation:**
- **Backend**:
  - `ScenarioBuilder.php` - Service for scenario modeling
  - Scenarios: Death, critical illness, income loss, premium changes
- **Frontend**:
  - Interactive sliders for adjusting parameters
  - Components: `ScenarioBuilder.vue`, `ScenarioComparisonChart.vue`

**API Endpoints:**
- `POST /api/protection/scenarios` - Run what-if scenario

**Testing:**
- Unit tests for scenario calculations
- UI tests for slider interactions

---

### Feature 2.5: Protection Dashboard

**Implementation:**
- **Frontend**:
  - Main card: `ProtectionOverviewCard.vue` (clickable)
  - Detailed dashboard: `ProtectionDashboard.vue` with tabs
  - Charts:
    - Coverage Adequacy Gauge (ApexCharts radial bar)
    - Coverage Gap Heatmap (ApexCharts heatmap)
    - Premium Breakdown (ApexCharts pie chart)
    - Coverage Timeline (ApexCharts timeline/gantt)

**ApexCharts Configuration Examples:**

**Coverage Adequacy Gauge:**
```vue
<apexchart
  type="radialBar"
  :options="{
    chart: { type: 'radialBar' },
    plotOptions: {
      radialBar: {
        hollow: { size: '70%' },
        dataLabels: {
          name: { fontSize: '22px' },
          value: { fontSize: '16px' },
          total: {
            show: true,
            label: 'Adequacy Score',
            formatter: () => '67%'
          }
        }
      }
    },
    labels: ['Coverage'],
    colors: ['#FF4560'] // Red for low, amber/green for higher scores
  }"
  :series="[67]"
/>
```

**Coverage Gap Heatmap:**
```vue
<apexchart
  type="heatmap"
  :options="{
    chart: { type: 'heatmap' },
    xaxis: { categories: ['Death', 'Critical Illness', 'Disability', 'Unemployment'] },
    yaxis: { categories: ['Now', 'Age 40', 'Age 50', 'Age 60', 'Retirement'] },
    colors: ['#008FFB'],
    dataLabels: { enabled: true }
  }"
  :series="[
    { name: 'Now', data: [100, 50, 30, 0] },
    { name: 'Age 40', data: [80, 40, 20, 0] },
    // ... more data
  ]"
/>
```

**Testing:**
- Component rendering tests
- Chart data binding tests

---

## 3. Savings Module

### Feature 3.1: Emergency Fund Analysis

**Implementation:**
- **Backend**:
  - `SavingsAgent.php` - Main agent class
  - Calculation: `runway = totalSavings / monthlyExpenditure`
  - Target: 3-6 months (configurable)
- **Frontend**:
  - Gauge chart (ApexCharts radial bar)
  - Component: `EmergencyFundGauge.vue`

**API Endpoints:**
- `POST /api/savings/analyze` - Analyze emergency fund and savings

**Calculation:**
```php
public function calculateEmergencyFundRunway(float $totalSavings, float $monthlyExpenditure): array
{
    $runway = $totalSavings / $monthlyExpenditure;
    $target = 6; // 6 months recommended
    $shortfall = max(0, ($target * $monthlyExpenditure) - $totalSavings);

    return [
        'runway_months' => round($runway, 1),
        'target_months' => $target,
        'adequacy_score' => min(100, ($runway / $target) * 100),
        'shortfall' => $shortfall,
    ];
}
```

**Testing:**
- Unit tests for runway calculation
- Edge cases (zero expenditure, negative savings)

---

### Feature 3.2: ISA Allowance Tracking

**Implementation:**
- **Backend**:
  - Track Cash ISA subscriptions (Savings Module)
  - Track Stocks & Shares ISA subscriptions (Investment Module)
  - Total ISA allowance: £20,000 (configurable in tax config)
- **Database**:
  - `isa_allowance_tracking` table per tax year
  - Fields: `user_id`, `tax_year`, `cash_isa_used`, `stocks_shares_isa_used`, `total_used`
- **Frontend**:
  - Progress bar showing ISA usage
  - Component: `ISAAllowanceTracker.vue` (shared between Savings and Investment dashboards)

**API Endpoints:**
- `GET /api/isa-allowance/{taxYear}` - Get ISA allowance usage for tax year
- `POST /api/isa-allowance/update` - Update ISA subscription

**Calculation:**
```php
public function getISAAllowanceStatus(int $userId, string $taxYear): array
{
    $totalAllowance = config('uk_tax.isa_allowances.total_allowance'); // £20,000

    // Get Cash ISA from Savings
    $cashISAUsed = SavingsAccount::where('user_id', $userId)
        ->where('is_isa', true)
        ->where('isa_subscription_year', $taxYear)
        ->sum('isa_subscription_amount');

    // Get Stocks & Shares ISA from Investment
    $stocksISAUsed = InvestmentAccount::where('user_id', $userId)
        ->where('account_type', 'isa')
        ->where('tax_year', $taxYear)
        ->sum('contributions_ytd');

    $totalUsed = $cashISAUsed + $stocksISAUsed;
    $remaining = $totalAllowance - $totalUsed;

    return [
        'total_allowance' => $totalAllowance,
        'cash_isa_used' => $cashISAUsed,
        'stocks_shares_isa_used' => $stocksISAUsed,
        'total_used' => $totalUsed,
        'remaining' => max(0, $remaining),
        'percentage_used' => ($totalUsed / $totalAllowance) * 100,
    ];
}
```

**Testing:**
- Unit tests for ISA tracking across modules
- Tests for cross-module coordination

---

### Feature 3.3: Savings Goals Tracking

**Implementation:**
- **Backend**:
  - Model: `SavingsGoal` (goal_name, target_amount, target_date, priority, current_saved)
  - Calculate: `months_remaining`, `required_monthly_savings`, `on_track_status`
- **Frontend**:
  - Goal cards with progress bars
  - Drag-to-reorder by priority
  - Component: `SavingsGoalCard.vue`

**API Endpoints:**
- `GET /api/savings/goals` - Get all savings goals
- `POST /api/savings/goals` - Add new goal
- `PUT /api/savings/goals/{id}` - Update goal
- `DELETE /api/savings/goals/{id}` - Delete goal

**Calculation:**
```php
public function calculateGoalProgress(SavingsGoal $goal): array
{
    $targetDate = Carbon::parse($goal->target_date);
    $monthsRemaining = max(1, Carbon::now()->diffInMonths($targetDate));
    $shortfall = max(0, $goal->target_amount - $goal->current_saved);
    $requiredMonthlySavings = $shortfall / $monthsRemaining;

    $progressPercent = ($goal->current_saved / $goal->target_amount) * 100;
    $onTrack = $goal->current_saved >= ($goal->target_amount * (1 - ($monthsRemaining / 12)));

    return [
        'progress_percent' => min(100, $progressPercent),
        'months_remaining' => $monthsRemaining,
        'shortfall' => $shortfall,
        'required_monthly_savings' => $requiredMonthlySavings,
        'on_track' => $onTrack,
    ];
}
```

**Testing:**
- Unit tests for goal progress calculations
- Tests for on-track status logic

---

### Feature 3.4: Savings Dashboard

**Implementation:**
- **Frontend**:
  - Charts:
    - Emergency Fund Gauge (ApexCharts radial bar)
    - Liquidity Ladder (ApexCharts bar chart)
    - Goal Progress Bars (ApexCharts bar chart)
    - Interest Rate Comparison (ApexCharts column chart)

**Liquidity Ladder Chart:**
```vue
<apexchart
  type="bar"
  :options="{
    chart: { type: 'bar', stacked: true },
    plotOptions: { bar: { horizontal: true } },
    xaxis: { categories: ['Immediate Access', '30-Day Notice', '90-Day Notice', '1-Year Fixed', '2-Year Fixed'] },
    yaxis: { title: { text: 'Amount (£)' } },
    legend: { position: 'top' },
    dataLabels: { enabled: true, formatter: (val) => `£${val.toLocaleString()}` }
  }"
  :series="[
    { name: 'Easy Access Savings', data: [5000, 0, 0, 0, 0] },
    { name: 'Notice Accounts', data: [0, 3000, 2000, 0, 0] },
    { name: 'Fixed Bonds', data: [0, 0, 0, 10000, 15000] }
  ]"
/>
```

**Testing:**
- Component rendering tests
- Chart data binding tests

---

## 4. Investment Module

### Feature 4.1: Portfolio Analysis

**Implementation:**
- **Backend**:
  - `InvestmentAgent.php` - Main agent class
  - Models: `InvestmentAccount`, `Holding`
  - Calculations:
    - Total portfolio value: Sum of all holdings
    - Returns: Time-weighted return, money-weighted return
    - Asset allocation: Group by asset_type, sum values
    - Fees: Sum of platform fees, fund OCF, transaction costs
- **Frontend**:
  - Charts:
    - Asset Allocation (ApexCharts donut chart)
    - Performance Line Chart (ApexCharts line chart with benchmarks)
    - Holdings Table (sortable, filterable)

**API Endpoints:**
- `GET /api/investment` - Get portfolio summary and holdings
- `POST /api/investment/holdings` - Add new holding
- `PUT /api/investment/holdings/{id}` - Update holding
- `DELETE /api/investment/holdings/{id}` - Delete holding

**Calculation: Time-Weighted Return:**
```php
public function calculateTimeWeightedReturn(array $holdings): float
{
    // Simplified TWR calculation
    $totalCurrentValue = array_sum(array_column($holdings, 'current_value'));
    $totalCostBasis = array_sum(array_map(function($h) {
        return $h['quantity'] * $h['purchase_price'];
    }, $holdings));

    return (($totalCurrentValue - $totalCostBasis) / $totalCostBasis) * 100;
}
```

**Asset Allocation Chart:**
```vue
<apexchart
  type="donut"
  :options="{
    chart: { type: 'donut' },
    labels: ['UK Equities', 'US Equities', 'Bonds', 'Cash', 'Alternatives'],
    legend: { position: 'bottom' },
    dataLabels: { enabled: true, formatter: (val) => `${val.toFixed(1)}%` }
  }"
  :series="[40, 30, 20, 5, 5]"
/>
```

**Testing:**
- Unit tests for return calculations
- Unit tests for asset allocation grouping
- Edge cases (empty portfolio, single holding)

---

### Feature 4.2: Monte Carlo Simulations

**Implementation:**
- **Backend**:
  - `MonteCarloSimulator.php` - Service for running simulations
  - Laravel Job: `RunMonteCarloSimulation.php` (background processing)
  - Queue: Database-backed Laravel queue
  - Iterations: 1,000 (configurable)
  - Algorithm: Geometric Brownian Motion with normal distribution
- **Frontend**:
  - Display job status (polling)
  - Charts: Area chart showing percentile ranges (10th, 25th, 50th, 75th, 90th)
  - Component: `MonteCarloResults.vue`

**API Endpoints:**
- `POST /api/investment/monte-carlo` - Start Monte Carlo simulation (returns job_id)
- `GET /api/investment/monte-carlo/{jobId}` - Get simulation results (poll this endpoint)

**Algorithm:**
```php
public function simulatePortfolioGrowth(
    float $startingValue,
    float $monthlyContribution,
    float $expectedReturn,
    float $volatility,
    int $years
): array {
    $iterations = 1000;
    $results = [];

    for ($i = 0; $i < $iterations; $i++) {
        $value = $startingValue;
        for ($month = 1; $month <= $years * 12; $month++) {
            // Generate random return using normal distribution
            $randomReturn = $this->generateNormalDistribution($expectedReturn / 12, $volatility / sqrt(12));
            $value = ($value + $monthlyContribution) * (1 + $randomReturn);
        }
        $results[] = $value;
    }

    sort($results);

    return [
        'percentile_10' => $results[intval($iterations * 0.10)],
        'percentile_25' => $results[intval($iterations * 0.25)],
        'percentile_50' => $results[intval($iterations * 0.50)],
        'percentile_75' => $results[intval($iterations * 0.75)],
        'percentile_90' => $results[intval($iterations * 0.90)],
    ];
}

private function generateNormalDistribution(float $mean, float $stdDev): float
{
    // Box-Muller transform
    $u1 = mt_rand() / mt_getrandmax();
    $u2 = mt_rand() / mt_getrandmax();
    $z = sqrt(-2 * log($u1)) * cos(2 * pi() * $u2);
    return $mean + ($z * $stdDev);
}
```

**Caching:**
- Cache key: `monte_carlo_{user_id}_{input_hash}`
- TTL: 24 hours (long cache since expensive calculation)

**Monte Carlo Results Chart:**
```vue
<apexchart
  type="area"
  :options="{
    chart: { type: 'area' },
    stroke: { curve: 'smooth' },
    fill: { type: 'gradient', opacity: [0.6, 0.4, 0.2] },
    xaxis: { categories: years, title: { text: 'Year' } },
    yaxis: { title: { text: 'Portfolio Value (£)' }, labels: { formatter: (val) => `£${(val/1000).toFixed(0)}k` } },
    legend: { position: 'top' }
  }"
  :series="[
    { name: '90th Percentile', data: percentile90Data },
    { name: '50th Percentile (Median)', data: percentile50Data },
    { name: '10th Percentile', data: percentile10Data }
  ]"
/>
```

**Testing:**
- Unit tests for Monte Carlo algorithm
- Tests for normal distribution generation
- Queue job tests
- Performance tests (should complete within 5 seconds)

---

### Feature 4.3: Investment Dashboard

**Implementation:**
- **Frontend**:
  - Charts:
    - Asset Allocation (ApexCharts donut chart)
    - Performance Over Time (ApexCharts line chart)
    - Holdings Breakdown (ApexCharts treemap)
    - Geographic Allocation (ApexCharts map - optional)
    - Allocation Deviation Heatmap (ApexCharts heatmap)

**Testing:**
- Component rendering tests
- Chart data binding tests

---

## 5. Retirement Module

### Feature 5.1: Retirement Readiness Score

**Implementation:**
- **Backend**:
  - `RetirementAgent.php` - Main agent class
  - Calculation: Compare projected income vs target income
  - Score formula: `(projectedIncome / targetIncome) * 100`, capped at 100

**API Endpoints:**
- `POST /api/retirement/analyze` - Analyze retirement readiness

**Calculation:**
```php
public function calculateRetirementReadinessScore(float $projectedIncome, float $targetIncome): array
{
    $readinessScore = min(100, ($projectedIncome / $targetIncome) * 100);
    $incomeGap = max(0, $targetIncome - $projectedIncome);
    $incomeReplacement = ($projectedIncome / $targetIncome) * 100;

    return [
        'readiness_score' => round($readinessScore),
        'projected_income' => $projectedIncome,
        'target_income' => $targetIncome,
        'income_gap' => $incomeGap,
        'income_replacement_ratio' => round($incomeReplacement, 1),
    ];
}
```

**Testing:**
- Unit tests for readiness score calculation
- Edge cases (zero income, target below projection)

---

### Feature 5.2: Pension Projections

**Implementation:**
- **Backend**:
  - DC Pension projection: Future value with contributions
  - DB Pension projection: Accrued benefit with revaluation
  - State Pension: Forecast based on NI years
  - Formula: `FV = PV * (1 + r)^n + PMT * [((1 + r)^n - 1) / r]`

**Calculation: DC Pension Projection:**
```php
public function projectDCPension(DCPension $pension, int $yearsToRetirement, float $growthRate): float
{
    $currentValue = $pension->current_fund_value;
    $monthlySalary = $pension->user->annual_salary / 12;
    $monthlyContribution = $monthlySalary * (
        $pension->employee_contribution_percent +
        $pension->employer_contribution_percent
    ) / 100;

    $monthlyGrowthRate = $growthRate / 12;
    $months = $yearsToRetirement * 12;

    // Future value with contributions
    $fv = $currentValue * pow(1 + $monthlyGrowthRate, $months) +
          $monthlyContribution * ((pow(1 + $monthlyGrowthRate, $months) - 1) / $monthlyGrowthRate);

    return $fv;
}
```

**Testing:**
- Unit tests for DC projection formula
- Unit tests for DB projection
- Validation tests for different growth rates

---

### Feature 5.3: Annual Allowance Tracking

**Implementation:**
- **Backend**:
  - Track annual pension contributions per tax year
  - Check against annual allowance (£60,000 for 2024/25)
  - Implement carry forward (3 previous years)
  - Implement tapering for high earners (threshold income > £200k, adjusted income > £260k)

**Calculation:**
```php
public function checkAnnualAllowance(int $userId, string $taxYear): array
{
    $standardAllowance = config('uk_tax.pension_allowances.annual_allowance'); // £60,000
    $contributions = $this->getTotalContributions($userId, $taxYear);

    // Check tapering (simplified)
    $thresholdIncome = $this->getThresholdIncome($userId, $taxYear);
    $adjustedIncome = $this->getAdjustedIncome($userId, $taxYear);

    $allowance = $standardAllowance;
    if ($thresholdIncome > 200000 && $adjustedIncome > 260000) {
        $reduction = min(50000, ($adjustedIncome - 260000) / 2);
        $allowance = max(10000, $standardAllowance - $reduction);
    }

    $carryForward = $this->getCarryForward($userId, $taxYear);
    $totalAvailable = $allowance + $carryForward;
    $remaining = max(0, $totalAvailable - $contributions);

    return [
        'annual_allowance' => $allowance,
        'contributions' => $contributions,
        'carry_forward_available' => $carryForward,
        'total_available' => $totalAvailable,
        'remaining' => $remaining,
        'tapered' => ($allowance < $standardAllowance),
    ];
}
```

**Testing:**
- Unit tests for carry forward logic
- Unit tests for tapering calculations
- Edge cases (MPAA, zero contributions)

---

### Feature 5.4: Decumulation Planning

**Implementation:**
- **Backend**:
  - Annuity vs Drawdown comparison
  - Sustainable withdrawal rate calculation (4% rule, dynamic strategies)
  - PCLS strategy (take full 25% or phase)
- **Frontend**:
  - Interactive drawdown simulator with sliders
  - Component: `DrawdownSimulator.vue`

**Calculation: Sustainable Withdrawal Rate:**
```php
public function calculateSustainableWithdrawal(
    float $portfolioValue,
    float $withdrawalRate,
    int $yearsInRetirement,
    float $growthRate,
    float $inflationRate
): array {
    $realGrowthRate = $growthRate - $inflationRate;
    $balance = $portfolioValue;
    $withdrawals = [];

    for ($year = 1; $year <= $yearsInRetirement; $year++) {
        $withdrawal = $portfolioValue * ($withdrawalRate / 100);
        $balance = ($balance - $withdrawal) * (1 + $realGrowthRate);
        $withdrawals[] = [
            'year' => $year,
            'withdrawal' => $withdrawal,
            'balance' => max(0, $balance),
        ];

        if ($balance <= 0) break;
    }

    return [
        'withdrawals' => $withdrawals,
        'portfolio_depleted' => ($balance <= 0),
        'years_sustainable' => count($withdrawals),
    ];
}
```

**Testing:**
- Unit tests for withdrawal sustainability
- Tests for different withdrawal rates (3%, 4%, 5%)

---

### Feature 5.5: Retirement Dashboard

**Implementation:**
- **Frontend**:
  - Charts:
    - Retirement Readiness Gauge (ApexCharts radial bar)
    - Income Projection (ApexCharts area chart with stacked series)
    - Contribution Breakdown (ApexCharts column chart)
    - Drawdown Simulator (ApexCharts line chart)

**Income Projection Chart:**
```vue
<apexchart
  type="area"
  :options="{
    chart: { type: 'area', stacked: true },
    xaxis: { categories: ages, title: { text: 'Age' } },
    yaxis: { title: { text: 'Annual Income (£)' } },
    legend: { position: 'top' },
    dataLabels: { enabled: false }
  }"
  :series="[
    { name: 'DC Pension', data: dcIncomeData },
    { name: 'DB Pension', data: dbIncomeData },
    { name: 'State Pension', data: statePensionData },
    { name: 'Other Income', data: otherIncomeData }
  ]"
/>
```

**Testing:**
- Component rendering tests
- Chart data binding tests

---

## 6. Estate Planning Module

### Feature 6.1: IHT Liability Calculation

**Implementation:**
- **Backend**:
  - `EstateAgent.php` - Main agent class
  - `IHTCalculator.php` - Service for IHT calculations
  - Steps:
    1. Calculate gross estate (assets - liabilities)
    2. Deduct exemptions (spouse, charity)
    3. Deduct reliefs (BPR, APR)
    4. Calculate NRB and RNRB (with transferable amounts)
    5. Calculate IHT at 40% (or 36% if 10%+ to charity)

**API Endpoints:**
- `POST /api/estate/analyze` - Analyze estate and calculate IHT
- `GET /api/estate/iht-calculation` - Get IHT calculation breakdown

**Calculation:**
```php
public function calculateIHT(array $estate): array
{
    $config = $this->taxConfig->getIHTConfig();

    // Step 1: Calculate gross estate
    $grossEstate = $estate['assets'] - $estate['liabilities'];

    // Step 2: Deduct exemptions
    $spouseExemption = $estate['spouse_bequest'] ?? 0;
    $charityExemption = $estate['charity_bequest'] ?? 0;

    // Step 3: Calculate taxable estate
    $taxableEstate = $grossEstate - $spouseExemption - $charityExemption;

    // Step 4: Calculate NRB and RNRB
    $nrb = $config['nil_rate_band']; // £325,000
    $transferableNRB = $estate['transferable_nrb'] ?? 0;
    $totalNRB = $nrb + $transferableNRB;

    $rnrb = $this->calculateRNRB($estate, $config);
    $transferableRNRB = $estate['transferable_rnrb'] ?? 0;
    $totalRNRB = $rnrb + $transferableRNRB;

    $totalThreshold = $totalNRB + $totalRNRB;

    // Step 5: Calculate IHT
    $ihtableAmount = max(0, $taxableEstate - $totalThreshold);

    // Check for charity discount (36% if 10%+ to charity)
    $ihtRate = 0.40;
    if ($charityExemption >= ($grossEstate * 0.10)) {
        $ihtRate = 0.36;
    }

    $ihtDue = $ihtableAmount * $ihtRate;

    return [
        'gross_estate' => $grossEstate,
        'spouse_exemption' => $spouseExemption,
        'charity_exemption' => $charityExemption,
        'taxable_estate' => $taxableEstate,
        'nil_rate_band' => $totalNRB,
        'residence_nil_rate_band' => $totalRNRB,
        'total_threshold' => $totalThreshold,
        'ihtable_amount' => $ihtableAmount,
        'iht_rate' => $ihtRate,
        'iht_due' => $ihtDue,
        'net_to_beneficiaries' => $grossEstate - $ihtDue,
    ];
}

private function calculateRNRB(array $estate, array $config): float
{
    $maxRNRB = $config['residence_nil_rate_band']; // £175,000
    $taperThreshold = $config['rnrb_taper_threshold']; // £2,000,000

    // Check if main residence in estate
    $mainResidenceValue = $estate['main_residence_value'] ?? 0;
    if ($mainResidenceValue == 0) return 0;

    // Check taper
    $grossEstate = $estate['assets'] - $estate['liabilities'];
    if ($grossEstate > $taperThreshold) {
        $excess = $grossEstate - $taperThreshold;
        $reduction = $excess / 2; // £1 reduction for every £2 over threshold
        return max(0, $maxRNRB - $reduction);
    }

    return $maxRNRB;
}
```

**Testing:**
- Unit tests for IHT calculation
- Tests for RNRB tapering
- Tests for charity discount (36% rate)
- Edge cases (estate below threshold, no main residence)

---

### Feature 6.2: PET and CLT Tracking

**Implementation:**
- **Backend**:
  - Model: `Gift` (gift_type: PET or CLT, gift_date, value, recipient)
  - PETs: Track 7-year countdown, calculate taper relief
  - CLTs: Track 14-year lookback, calculate cumulative total
- **Frontend**:
  - Timeline chart (ApexCharts timeline/gantt)
  - Component: `GiftingTimeline.vue`

**Calculation: PET Taper Relief:**
```php
public function calculatePETTaperRelief(Carbon $giftDate, float $giftValue): array
{
    $yearsAgo = Carbon::now()->diffInYears($giftDate);

    if ($yearsAgo < 3) {
        $taperRelief = 0; // Full IHT
    } elseif ($yearsAgo < 4) {
        $taperRelief = 0.20; // 20% relief
    } elseif ($yearsAgo < 5) {
        $taperRelief = 0.40; // 40% relief
    } elseif ($yearsAgo < 6) {
        $taperRelief = 0.60; // 60% relief
    } elseif ($yearsAgo < 7) {
        $taperRelief = 0.80; // 80% relief
    } else {
        $taperRelief = 1.00; // 100% relief (exempt)
    }

    $ihtableAmount = max(0, $giftValue - $config['nil_rate_band']);
    $ihtDue = $ihtableAmount * 0.40 * (1 - $taperRelief);

    return [
        'years_since_gift' => $yearsAgo,
        'taper_relief_percent' => $taperRelief * 100,
        'iht_due' => $ihtDue,
        'exempt' => ($yearsAgo >= 7),
    ];
}
```

**Gifting Timeline Chart:**
```vue
<apexchart
  type="rangeBar"
  :options="{
    chart: { type: 'rangeBar' },
    plotOptions: { bar: { horizontal: true } },
    xaxis: { type: 'datetime' },
    yaxis: { categories: giftNames },
    tooltip: {
      custom: function({series, seriesIndex, dataPointIndex, w}) {
        const gift = gifts[dataPointIndex];
        return `<div class='p-2'>
          <strong>${gift.recipient}</strong><br/>
          £${gift.value.toLocaleString()}<br/>
          ${gift.yearsRemaining} years until exempt
        </div>`;
      }
    }
  }"
  :series="[{
    data: gifts.map(g => ({
      x: g.recipient,
      y: [new Date(g.gift_date).getTime(), new Date(g.exempt_date).getTime()],
      fillColor: g.yearsRemaining < 2 ? '#00E396' : '#FF4560'
    }))
  }]"
/>
```

**Testing:**
- Unit tests for taper relief calculation
- Tests for 7-year rule
- Tests for CLT cumulative total

---

### Feature 6.3: Personal Financial Statements

**Implementation:**
- **Backend**:
  - Personal P&L: Income - Expenditure = Surplus/Deficit
  - Cashflow Statement: Cash inflows - outflows
  - Balance Sheet: Assets - Liabilities = Net Worth
- **Frontend**:
  - Charts:
    - Net Worth Statement (ApexCharts bar chart)
    - P&L Breakdown (ApexCharts pie chart)
    - Cashflow Over Time (ApexCharts line chart)

**Calculation: Personal P&L:**
```php
public function generatePersonalPL(int $userId): array
{
    // Income
    $employmentIncome = $this->getEmploymentIncome($userId);
    $pensionIncome = $this->getPensionIncome($userId);
    $rentalIncome = $this->getRentalIncome($userId);
    $investmentIncome = $this->getInvestmentIncome($userId);
    $totalIncome = $employmentIncome + $pensionIncome + $rentalIncome + $investmentIncome;

    // Expenditure
    $housingCosts = $this->getHousingCosts($userId);
    $livingCosts = $this->getLivingCosts($userId);
    $insurancePremiums = $this->getInsurancePremiums($userId);
    $loanRepayments = $this->getLoanRepayments($userId);
    $discretionarySpending = $this->getDiscretionarySpending($userId);
    $totalExpenditure = $housingCosts + $livingCosts + $insurancePremiums + $loanRepayments + $discretionarySpending;

    // Surplus/Deficit
    $netSurplus = $totalIncome - $totalExpenditure;

    return [
        'income' => [
            'employment' => $employmentIncome,
            'pension' => $pensionIncome,
            'rental' => $rentalIncome,
            'investment' => $investmentIncome,
            'total' => $totalIncome,
        ],
        'expenditure' => [
            'housing' => $housingCosts,
            'living' => $livingCosts,
            'insurance' => $insurancePremiums,
            'loans' => $loanRepayments,
            'discretionary' => $discretionarySpending,
            'total' => $totalExpenditure,
        ],
        'net_surplus' => $netSurplus,
    ];
}
```

**Testing:**
- Unit tests for P&L generation
- Tests for cashflow calculations
- Tests for net worth calculation

---

### Feature 6.4: Estate Planning Dashboard

**Implementation:**
- **Frontend**:
  - Charts:
    - Net Worth Statement (ApexCharts bar chart showing assets vs liabilities)
    - IHT Waterfall Chart (ApexCharts waterfall showing estate → IHT due)
    - Gifting Timeline (ApexCharts rangeBar for PETs and CLTs)
    - Cashflow Projection (ApexCharts line chart)

**IHT Waterfall Chart:**
```vue
<apexchart
  type="bar"
  :options="{
    chart: { type: 'bar' },
    plotOptions: { bar: { horizontal: false } },
    xaxis: { categories: ['Gross Estate', 'Exemptions', 'NRB', 'RNRB', 'Taxable Estate', 'IHT Due'] },
    colors: ['#008FFB', '#00E396', '#00E396', '#00E396', '#FEB019', '#FF4560'],
    dataLabels: { enabled: true, formatter: (val) => `£${(val/1000).toFixed(0)}k` }
  }"
  :series="[{
    name: 'Amount',
    data: [
      800000,  // Gross Estate
      -100000, // Exemptions (negative to show deduction)
      -325000, // NRB
      -175000, // RNRB
      200000,  // Taxable Estate
      80000    // IHT Due (40% of taxable)
    ]
  }]"
/>
```

**Testing:**
- Component rendering tests
- Chart data binding tests

---

## 7. Main Dashboard

### Feature 7.1: Unified Dashboard

**Implementation:**
- **Backend**:
  - API endpoint returns aggregated data from all 5 modules
- **Frontend**:
  - 5 module cards (Protection, Savings, Investment, Retirement, Estate)
  - ISA Allowance Tracker (progress bar)
  - Priority Actions Feed (top 5 actions from all modules)
  - Net Worth Snapshot
  - Cashflow Summary

**API Endpoints:**
- `GET /api/dashboard` - Get unified dashboard data

**Component Structure:**
```
MainDashboard.vue
├── ProtectionOverviewCard.vue (clickable)
├── SavingsOverviewCard.vue (clickable)
├── InvestmentOverviewCard.vue (clickable)
├── RetirementOverviewCard.vue (clickable)
├── EstateOverviewCard.vue (clickable)
├── ISAAllowanceTracker.vue
├── PriorityActionsFeed.vue
├── NetWorthSnapshot.vue
└── CashflowSummary.vue
```

**Testing:**
- Integration tests for dashboard data aggregation
- Component rendering tests

---

## 8. Coordinating Agent

### Feature 8.1: Cross-Module Recommendations

**Implementation:**
- **Backend**:
  - `CoordinatingAgent.php` - Aggregates recommendations from all 5 agents
  - Priority rules:
    1. Protection (ensure adequate coverage first)
    2. Emergency fund (3-6 months minimum)
    3. ISA allowance optimization
    4. Pension contributions (maximize employer match)
    5. Investment optimization
    6. IHT planning
- **Frontend**:
  - Component: `CoordinatedActionPlan.vue`

**API Endpoints:**
- `POST /api/coordinate/analyze-all` - Run all 5 agents and coordinate recommendations
- `GET /api/coordinate/recommendations` - Get coordinated action plan

**Coordination Logic:**
```php
public function coordinateRecommendations(int $userId): array
{
    // Run all 5 agents
    $protectionRecs = $this->protectionAgent->generateRecommendations($userId);
    $savingsRecs = $this->savingsAgent->generateRecommendations($userId);
    $investmentRecs = $this->investmentAgent->generateRecommendations($userId);
    $retirementRecs = $this->retirementAgent->generateRecommendations($userId);
    $estateRecs = $this->estateAgent->generateRecommendations($userId);

    // Apply priority rules
    $allRecs = array_merge($protectionRecs, $savingsRecs, $investmentRecs, $retirementRecs, $estateRecs);
    $prioritizedRecs = $this->applyPriorityRules($allRecs);

    // Identify conflicts (e.g., limited disposable income, ISA allowance)
    $conflicts = $this->identifyConflicts($prioritizedRecs);

    // Resolve conflicts and optimize
    $optimizedRecs = $this->resolveConflicts($prioritizedRecs, $conflicts);

    return [
        'recommendations' => $optimizedRecs,
        'conflicts' => $conflicts,
    ];
}

private function applyPriorityRules(array $recommendations): array
{
    usort($recommendations, function($a, $b) {
        $priorityOrder = ['protection' => 1, 'emergency_fund' => 2, 'isa' => 3, 'pension' => 4, 'investment' => 5, 'estate' => 6];
        return $priorityOrder[$a['category']] <=> $priorityOrder[$b['category']];
    });

    return $recommendations;
}
```

**Testing:**
- Integration tests for multi-agent coordination
- Tests for priority rule application
- Tests for conflict resolution

---

## Database Schema

### Core Tables

#### users
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    date_of_birth DATE,
    gender VARCHAR(50),
    marital_status VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### life_insurance_policies
```sql
CREATE TABLE life_insurance_policies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    policy_type VARCHAR(100),
    provider VARCHAR(255),
    policy_number VARCHAR(100),
    sum_assured DECIMAL(15, 2),
    premium_amount DECIMAL(10, 2),
    premium_frequency VARCHAR(50),
    policy_start_date DATE,
    policy_term_years INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### savings_accounts
```sql
CREATE TABLE savings_accounts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    account_type VARCHAR(100),
    institution VARCHAR(255),
    current_balance DECIMAL(15, 2),
    interest_rate DECIMAL(5, 4),
    is_isa BOOLEAN DEFAULT FALSE,
    isa_subscription_year VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### investment_accounts
```sql
CREATE TABLE investment_accounts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    account_type VARCHAR(100), -- 'isa', 'gia', 'onshore_bond', 'offshore_bond'
    provider VARCHAR(255),
    current_value DECIMAL(15, 2),
    contributions_ytd DECIMAL(15, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### holdings
```sql
CREATE TABLE holdings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    investment_account_id BIGINT UNSIGNED NOT NULL,
    asset_type VARCHAR(100),
    security_name VARCHAR(255),
    ticker VARCHAR(20),
    quantity DECIMAL(15, 6),
    purchase_price DECIMAL(15, 4),
    current_price DECIMAL(15, 4),
    current_value DECIMAL(15, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_account_id (investment_account_id),
    FOREIGN KEY (investment_account_id) REFERENCES investment_accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### dc_pensions
```sql
CREATE TABLE dc_pensions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    scheme_name VARCHAR(255),
    current_fund_value DECIMAL(15, 2),
    employee_contribution_percent DECIMAL(5, 2),
    employer_contribution_percent DECIMAL(5, 2),
    retirement_age INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### assets
```sql
CREATE TABLE assets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    asset_type VARCHAR(100), -- 'property', 'bank_account', 'investment', 'business'
    asset_name VARCHAR(255),
    current_value DECIMAL(15, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### gifts
```sql
CREATE TABLE gifts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    gift_type VARCHAR(50), -- 'PET', 'CLT'
    recipient_name VARCHAR(255),
    gift_date DATE,
    gift_value DECIMAL(15, 2),
    exemption_claimed VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### tax_configurations
```sql
CREATE TABLE tax_configurations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tax_year VARCHAR(10) UNIQUE NOT NULL, -- '2024/25'
    config JSON NOT NULL,
    is_active BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tax_year (tax_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### jobs (for Laravel Queue)
```sql
CREATE TABLE jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL,
    reserved_at INT UNSIGNED,
    available_at INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL,
    INDEX idx_queue (queue)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## API Endpoints

### Authentication
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login user
- `POST /api/auth/logout` - Logout user
- `GET /api/auth/user` - Get authenticated user

### Dashboard
- `GET /api/dashboard` - Get unified dashboard data

### Protection Module
- `GET /api/protection` - Get all protection data
- `POST /api/protection/analyze` - Analyze protection needs
- `GET /api/protection/recommendations` - Get recommendations
- `POST /api/protection/scenarios` - Run what-if scenarios
- `POST /api/protection/policies` - Add policy
- `PUT /api/protection/policies/{id}` - Update policy
- `DELETE /api/protection/policies/{id}` - Delete policy

### Savings Module
- `GET /api/savings` - Get all savings data
- `POST /api/savings/analyze` - Analyze savings and emergency fund
- `GET /api/savings/recommendations` - Get recommendations
- `POST /api/savings/scenarios` - Run what-if scenarios
- `GET /api/savings/isa-allowance` - Get ISA allowance usage
- `GET /api/savings/goals` - Get savings goals
- `POST /api/savings/goals` - Add goal
- `PUT /api/savings/goals/{id}` - Update goal
- `DELETE /api/savings/goals/{id}` - Delete goal

### Investment Module
- `GET /api/investment` - Get portfolio data
- `POST /api/investment/analyze` - Analyze portfolio
- `GET /api/investment/recommendations` - Get recommendations
- `POST /api/investment/scenarios` - Run what-if scenarios
- `POST /api/investment/monte-carlo` - Start Monte Carlo simulation (background job)
- `GET /api/investment/monte-carlo/{jobId}` - Get Monte Carlo results
- `POST /api/investment/holdings` - Add holding
- `PUT /api/investment/holdings/{id}` - Update holding
- `DELETE /api/investment/holdings/{id}` - Delete holding

### Retirement Module
- `GET /api/retirement` - Get retirement data
- `POST /api/retirement/analyze` - Analyze retirement readiness
- `GET /api/retirement/recommendations` - Get recommendations
- `POST /api/retirement/scenarios` - Run what-if scenarios
- `POST /api/retirement/pensions` - Add pension
- `PUT /api/retirement/pensions/{id}` - Update pension
- `DELETE /api/retirement/pensions/{id}` - Delete pension

### Estate Module
- `GET /api/estate` - Get estate data
- `POST /api/estate/analyze` - Analyze estate and calculate IHT
- `GET /api/estate/recommendations` - Get recommendations
- `POST /api/estate/scenarios` - Run what-if scenarios
- `GET /api/estate/iht-calculation` - Get IHT calculation breakdown
- `POST /api/estate/gifts` - Add gift
- `PUT /api/estate/gifts/{id}` - Update gift
- `DELETE /api/estate/gifts/{id}` - Delete gift

### Coordinating Agent
- `POST /api/coordinate/analyze-all` - Run all agents
- `GET /api/coordinate/recommendations` - Get coordinated recommendations

### Tax Configuration
- `GET /api/config/tax-rules` - Get current tax config
- `GET /api/config/tax-rules/{taxYear}` - Get specific tax year config

---

## Frontend Components

### Component Hierarchy

```
App.vue
├── AppLayout.vue
│   ├── Navbar.vue
│   ├── Sidebar.vue
│   └── Footer.vue
│
├── MainDashboard.vue
│   ├── ProtectionOverviewCard.vue
│   ├── SavingsOverviewCard.vue
│   ├── InvestmentOverviewCard.vue
│   ├── RetirementOverviewCard.vue
│   ├── EstateOverviewCard.vue
│   ├── ISAAllowanceTracker.vue
│   ├── PriorityActionsFeed.vue
│   ├── NetWorthSnapshot.vue
│   └── CashflowSummary.vue
│
├── ProtectionDashboard.vue
│   ├── CurrentSituation.vue
│   ├── GapAnalysis.vue
│   │   ├── CoverageGapChart.vue (ApexCharts heatmap)
│   │   └── CoverageAdequacyGauge.vue (ApexCharts radial bar)
│   ├── Recommendations.vue
│   │   └── RecommendationCard.vue
│   ├── WhatIfScenarios.vue
│   │   └── ScenarioBuilder.vue
│   └── PolicyDetails.vue
│       └── PolicyCard.vue
│
├── SavingsDashboard.vue
│   ├── CurrentSituation.vue
│   ├── EmergencyFund.vue
│   │   └── EmergencyFundGauge.vue (ApexCharts radial bar)
│   ├── SavingsGoals.vue
│   │   └── SavingsGoalCard.vue
│   ├── Recommendations.vue
│   ├── WhatIfScenarios.vue
│   └── AccountDetails.vue
│
├── InvestmentDashboard.vue
│   ├── PortfolioOverview.vue
│   │   ├── AssetAllocationChart.vue (ApexCharts donut)
│   │   └── GeographicMap.vue (ApexCharts map)
│   ├── Holdings.vue
│   │   └── HoldingsTable.vue
│   ├── Performance.vue
│   │   └── PerformanceLineChart.vue (ApexCharts line)
│   ├── Goals.vue
│   │   └── MonteCarloResults.vue (ApexCharts area)
│   ├── Recommendations.vue
│   ├── WhatIfScenarios.vue
│   └── TaxFees.vue
│
├── RetirementDashboard.vue
│   ├── RetirementReadiness.vue
│   │   └── ReadinessGauge.vue (ApexCharts radial bar)
│   ├── PensionInventory.vue
│   │   └── PensionCard.vue
│   ├── ContributionsAllowances.vue
│   ├── Projections.vue
│   │   └── IncomeProjectionChart.vue (ApexCharts area, stacked)
│   ├── Recommendations.vue
│   ├── WhatIfScenarios.vue
│   └── DecumulationPlanning.vue
│       └── DrawdownSimulator.vue (ApexCharts line)
│
└── EstateDashboard.vue
    ├── OverviewNetWorth.vue
    │   └── NetWorthStatement.vue (ApexCharts bar)
    ├── IHTLiability.vue
    │   └── IHTWaterfallChart.vue (ApexCharts waterfall)
    ├── GiftingStrategy.vue
    │   └── GiftingTimeline.vue (ApexCharts timeline/rangeBar)
    ├── PersonalAccounts.vue
    │   ├── PLStatement.vue
    │   ├── CashflowStatement.vue
    │   └── BalanceSheet.vue
    ├── Recommendations.vue
    ├── WhatIfScenarios.vue
    └── DocumentationProbate.vue
```

---

## Testing Strategy

### Unit Tests (Pest)

**Financial Calculations:**
- Tax calculations (income tax, NI, CGT, IHT)
- Coverage gap analysis
- Emergency fund runway
- Monte Carlo simulations
- Pension projections
- IHT calculations
- PET taper relief

**Example Test:**
```php
<?php

use App\Services\FinancialCalculations\TaxCalculator;

test('income tax basic rate calculation', function () {
    $calculator = new TaxCalculator();
    $result = $calculator->calculateIncomeTax(30000, '2024/25');

    expect($result['tax_due'])->toBe(3486) // (30000 - 12570) * 0.20
        ->and($result['effective_rate'])->toBeCloseTo(0.116, 3);
});

test('IHT calculation with NRB and RNRB', function () {
    $calculator = new IHTCalculator();
    $estate = ['total_value' => 800000, 'main_residence' => 400000];
    $result = $calculator->calculate($estate);

    expect($result['iht_due'])->toBe(120000)
        ->and($result['nil_rate_band'])->toBe(325000)
        ->and($result['residence_nil_rate_band'])->toBe(175000);
});
```

### Feature Tests (Pest)

**API Endpoints:**
- Authentication
- CRUD operations for all modules
- Analysis endpoints
- Recommendations endpoints

**Example Test:**
```php
<?php

use App\Models\User;

test('analyze protection needs endpoint', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/protection/analyze', [
        'annual_income' => 50000,
        'mortgage_balance' => 200000,
        'dependents' => 2,
        'existing_coverage' => 100000,
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'analysis' => ['coverage_gaps', 'adequacy_score', 'recommendations']
        ]);
});
```

### Architecture Tests (Pest)

**Code Quality:**
```php
<?php

test('agents extend BaseAgent')
    ->expect('App\Agents')
    ->toExtend('App\Agents\BaseAgent');

test('models do not have public properties')
    ->expect('App\Models')
    ->not->toHavePublicProperties();
```

### Frontend Tests (Vue Test Utils)

**Component Rendering:**
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

    expect(wrapper.text()).toContain('6 months')
  })
})
```

### API Testing (Postman)

**Create Postman Collections:**
- Authentication Collection
- Protection Module Collection
- Savings Module Collection
- Investment Module Collection
- Retirement Module Collection
- Estate Module Collection

**Collection Structure:**
```
FPS API Collection
├── Authentication
│   ├── Register
│   ├── Login
│   └── Get User
├── Protection
│   ├── Get Protection Data
│   ├── Add Policy
│   └── Analyze Protection
├── Savings
│   ├── Get Savings Data
│   ├── Add Goal
│   └── Analyze Savings
...
```

---

## Deployment

### Production Environment

**Recommended Stack:**
- **Server**: Ubuntu 22.04 LTS
- **Web Server**: Nginx (reverse proxy)
- **PHP**: PHP-FPM 8.2
- **Database**: MySQL 8.0
- **Cache**: Memcached 1.6+
- **Queue Worker**: Laravel Queue Worker (Supervisor)
- **SSL**: Let's Encrypt (Certbot)

### Installation Steps

**1. Install Dependencies:**
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip

# Install MySQL
sudo apt install mysql-server

# Install Memcached
sudo apt install memcached libmemcached-tools php8.2-memcached

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js & npm
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs
```

**2. Configure Memcached:**
```bash
# Edit Memcached config
sudo nano /etc/memcached.conf

# Set memory limit (256MB for FPS)
-m 256

# Restart Memcached
sudo systemctl restart memcached
sudo systemctl enable memcached
```

**3. Deploy Laravel Application:**
```bash
# Clone repository
git clone <repo-url> /var/www/fps
cd /var/www/fps

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install
npm run build

# Set permissions
sudo chown -R www-data:www-data /var/www/fps
sudo chmod -R 755 /var/www/fps/storage

# Configure environment
cp .env.example .env
nano .env

# Generate key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Seed tax configuration
php artisan db:seed --class=TaxConfigurationSeeder

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**4. Configure Nginx:**
```nginx
server {
    listen 80;
    server_name fps.example.com;
    root /var/www/fps/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**5. Configure Supervisor (Queue Worker):**
```bash
sudo nano /etc/supervisor/conf.d/fps-worker.conf
```

```ini
[program:fps-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/fps/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasneeded=false
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/fps/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start fps-worker:*
```

**6. Setup SSL (Let's Encrypt):**
```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d fps.example.com
```

---

## Performance Optimization

### Caching Strategy

**Memcached Configuration:**
```php
// config/cache.php
'default' => env('CACHE_DRIVER', 'memcached'),

'stores' => [
    'memcached' => [
        'driver' => 'memcached',
        'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
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
```

### Database Optimization

**Indexes:**
```sql
-- User lookups
CREATE INDEX idx_users_email ON users(email);

-- Protection module
CREATE INDEX idx_policies_user_id ON life_insurance_policies(user_id);

-- Savings module
CREATE INDEX idx_savings_user_id ON savings_accounts(user_id);

-- Investment module
CREATE INDEX idx_accounts_user_id ON investment_accounts(user_id);
CREATE INDEX idx_holdings_account_id ON holdings(investment_account_id);

-- Retirement module
CREATE INDEX idx_pensions_user_id ON dc_pensions(user_id);

-- Estate module
CREATE INDEX idx_assets_user_id ON assets(user_id);
CREATE INDEX idx_gifts_user_id ON gifts(user_id);
```

---

## Security Considerations

### Data Protection

1. **Encryption**:
   - HTTPS only (TLS 1.3)
   - Sensitive fields encrypted at rest
   - Bcrypt password hashing

2. **Authentication**:
   - Laravel Sanctum for API authentication
   - CSRF protection enabled
   - Rate limiting on API endpoints

3. **Input Validation**:
   - Laravel Form Requests for all inputs
   - Sanitize user inputs
   - Parameterized queries (Eloquent ORM)

4. **Financial Data Protection**:
   - Encrypt policy numbers, account numbers
   - Audit trail for all financial data changes
   - User data isolation (can only access own data)

---

## Maintenance & Updates

### Annual Tax Configuration Updates

**When UK tax rules change (typically after Budget):**

1. Update `config/uk_tax_config.php` or tax_configurations table
2. Add new tax year configuration
3. Run tests to verify calculations
4. Deploy update

**Example Update:**
```php
// database/seeders/TaxConfigurationSeeder.php
TaxConfiguration::create([
    'tax_year' => '2025/26',
    'config' => [
        'income_tax' => [
            'personal_allowance' => 12570, // Updated if changed
            'basic_rate' => ['threshold' => 50270, 'rate' => 0.20],
            // ... more config
        ],
        // ... more tax rules
    ],
    'is_active' => true,
]);
```

---

## Conclusion

This document provides a comprehensive technical blueprint for implementing the FPS system. Each feature is broken down with:
- ✅ Clear implementation approach
- ✅ Database schema design
- ✅ API endpoint specifications
- ✅ Calculation algorithms
- ✅ Frontend component structure
- ✅ ApexCharts examples
- ✅ Testing strategy
- ✅ Deployment instructions

**Next Steps:**
1. Review this document with the development team
2. Set up development environment
3. Begin Phase 1: Foundation (Weeks 1-3)
4. Implement modules incrementally as outlined in the PRD timeline

---

**End of Features & Technical Implementation Guide**
