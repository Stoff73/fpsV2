# Task 04: Savings Module - Backend

**Objective**: Implement Savings module backend including emergency fund analysis, ISA tracking, and savings goals.

**Estimated Time**: 4-6 days

---

## Database Schema

### Savings Accounts Table

- [x] Create `savings_accounts` migration with fields:
  - `id`, `user_id`, `account_type` (VARCHAR), `institution` (VARCHAR)
  - `account_number` (VARCHAR, encrypted), `current_balance` (DECIMAL 15,2)
  - `interest_rate` (DECIMAL 5,4), `access_type` (enum: immediate, notice, fixed)
  - `notice_period_days` (INT), `maturity_date` (DATE)
  - `is_isa` (boolean), `isa_type` (VARCHAR: cash, stocks_shares, LISA)
  - `isa_subscription_year` (VARCHAR), `isa_subscription_amount` (DECIMAL 15,2)
  - `created_at`, `updated_at`
- [x] Add foreign key and index on `user_id`
- [x] Create `SavingsAccount` model with fillable fields
- [x] Add encrypted accessor/mutator for `account_number`

### Savings Goals Table

- [x] Create `savings_goals` migration with fields:
  - `id`, `user_id`, `goal_name` (VARCHAR), `target_amount` (DECIMAL 15,2)
  - `current_saved` (DECIMAL 15,2), `target_date` (DATE)
  - `priority` (enum: high, medium, low)
  - `linked_account_id` (nullable), `auto_transfer_amount` (DECIMAL 10,2)
  - `created_at`, `updated_at`
- [x] Add foreign key and index on `user_id`
- [x] Add foreign key on `linked_account_id` (references savings_accounts)
- [x] Create `SavingsGoal` model with relationships

### Expenditure Profile Table

- [x] Create `expenditure_profiles` migration with fields:
  - `id`, `user_id`, `monthly_housing` (DECIMAL 10,2), `monthly_utilities` (DECIMAL 10,2)
  - `monthly_food` (DECIMAL 10,2), `monthly_transport` (DECIMAL 10,2)
  - `monthly_insurance` (DECIMAL 10,2), `monthly_loans` (DECIMAL 10,2)
  - `monthly_discretionary` (DECIMAL 10,2), `total_monthly_expenditure` (DECIMAL 10,2)
  - `created_at`, `updated_at`
- [x] Add foreign key and index on `user_id`
- [x] Create `ExpenditureProfile` model

### ISA Allowance Tracking Table

- [x] Create `isa_allowance_tracking` migration with fields:
  - `id`, `user_id`, `tax_year` (VARCHAR: '2024/25')
  - `cash_isa_used` (DECIMAL 10,2), `stocks_shares_isa_used` (DECIMAL 10,2)
  - `lisa_used` (DECIMAL 10,2), `total_used` (DECIMAL 10,2)
  - `total_allowance` (DECIMAL 10,2)
  - `created_at`, `updated_at`
- [x] Add unique index on `user_id` and `tax_year`
- [x] Create `ISAAllowanceTracking` model

---

## Savings Agent

### SavingsAgent Class

- [x] Create `app/Agents/SavingsAgent.php` extending `BaseAgent`
- [x] Inject dependencies: `EmergencyFundCalculator`, `ISATracker`, `GoalProgressCalculator`
- [x] Implement `analyze(int $userId): array` method
- [x] Implement `generateRecommendations(array $analysis): array` method
- [x] Implement `buildScenarios(array $inputs, array $analysis): array` method

---

## Services

### Emergency Fund Calculator

- [x] Create `app/Services/Savings/EmergencyFundCalculator.php`
- [x] Implement `calculateRunway(float $totalSavings, float $monthlyExpenditure): float`
  - Formula: `totalSavings / monthlyExpenditure`
- [x] Implement `calculateAdequacy(float $runway, int $targetMonths = 6): array`
  - Return runway, target, adequacy_score, shortfall
- [x] Implement `calculateMonthlyTopUp(float $shortfall, int $months): float`
- [x] Implement `categorizeAdequacy(float $runway): string`
  - 6+ months: Excellent, 3-6: Good, 1-3: Fair, <1: Critical

### ISA Tracker

- [x] Create `app/Services/Savings/ISATracker.php`
- [x] Implement `getCurrentTaxYear(): string`
  - Return current UK tax year (e.g., '2024/25')
- [x] Implement `getISAAllowanceStatus(int $userId, string $taxYear): array`
  - Query cash_isa_used from savings_accounts
  - Query stocks_shares_isa_used from investment_accounts (cross-module)
  - Calculate total_used, remaining, percentage_used
- [x] Implement `updateISAUsage(int $userId, string $isaType, float $amount): void`
- [x] Implement `getTotalAllowance(string $taxYear): float`
  - Get from tax config (£20,000 for 2024/25)
- [x] Implement `getLISAAllowance(): float`
  - Return £4,000 (counts toward total)

### Goal Progress Calculator

- [x] Create `app/Services/Savings/GoalProgressCalculator.php`
- [x] Implement `calculateProgress(SavingsGoal $goal): array`
  - Calculate months_remaining, shortfall, required_monthly_savings
  - Calculate progress_percent, on_track status
- [x] Implement `projectGoalAchievement(SavingsGoal $goal, float $monthlyContribution, float $interestRate): array`
  - Use compound interest formula
- [x] Implement `prioritizeGoals(Collection $goals): Collection`
  - Sort by priority, then by target date

### Rate Comparator

- [x] Create `app/Services/Savings/RateComparator.php`
- [x] Implement `compareToMarketRates(SavingsAccount $account): array`
  - Use fixed benchmark rates for comparison
  - Return: account_rate, market_rate, difference, is_competitive
- [x] Implement `getMarketBenchmarks(): array`
  - Return benchmark rates by account type
- [x] Implement `calculateInterestDifference(SavingsAccount $account, float $marketRate): float`

### Liquidity Analyzer

- [x] Create `app/Services/Savings/LiquidityAnalyzer.php`
- [x] Implement `categorizeLiquidity(Collection $accounts): array`
  - Group by: immediate, short_notice, fixed_term
- [x] Implement `buildLiquidityLadder(Collection $accounts): array`
  - Return timeline data structure
- [x] Implement `assessLiquidityRisk(array $liquidityProfile): string`

---

## API Endpoints

### Savings Controller

- [x] Create `app/Http/Controllers/Api/SavingsController.php`
- [x] Inject `SavingsAgent`, `ISATracker`
- [x] Implement `index(Request $request): JsonResponse`
  - Get all savings data (accounts, goals, expenditure profile)
- [x] Implement `analyze(SavingsAnalysisRequest $request): JsonResponse`
  - Call SavingsAgent->analyze()
  - Return emergency fund analysis, liquidity profile, rate comparison
- [x] Implement `recommendations(Request $request): JsonResponse`
- [x] Implement `scenarios(ScenarioRequest $request): JsonResponse`
- [x] Implement `isaAllowance(string $taxYear): JsonResponse`
  - Get ISA allowance status for specified tax year

### Savings Account CRUD

- [x] Implement `storeAccount(StoreSavingsAccountRequest $request): JsonResponse`
- [x] Implement `updateAccount(UpdateSavingsAccountRequest $request, int $id): JsonResponse`
- [x] Implement `destroyAccount(int $id): JsonResponse`
- [x] Add authorization checks

### Savings Goal CRUD

- [x] Implement `storeGoal(StoreSavingsGoalRequest $request): JsonResponse`
- [x] Implement `updateGoal(UpdateSavingsGoalRequest $request, int $id): JsonResponse`
- [x] Implement `destroyGoal(int $id): JsonResponse`
- [x] Implement `updateGoalProgress(int $id, float $amount): JsonResponse`

### Form Requests

- [x] Create `app/Http/Requests/Savings/SavingsAnalysisRequest.php`
- [x] Create `app/Http/Requests/Savings/StoreSavingsAccountRequest.php`
- [x] Create `app/Http/Requests/Savings/UpdateSavingsAccountRequest.php`
- [x] Create `app/Http/Requests/Savings/StoreSavingsGoalRequest.php`
- [x] Create `app/Http/Requests/Savings/UpdateSavingsGoalRequest.php`
- [x] Create `app/Http/Requests/Savings/ScenarioRequest.php`

### Routes

- [x] Add routes to `routes/api.php`:
  - `GET /api/savings` → index
  - `POST /api/savings/analyze` → analyze
  - `GET /api/savings/recommendations` → recommendations
  - `POST /api/savings/scenarios` → scenarios
  - `GET /api/savings/isa-allowance/{taxYear}` → isaAllowance
  - `POST /api/savings/accounts` → storeAccount
  - `PUT /api/savings/accounts/{id}` → updateAccount
  - `DELETE /api/savings/accounts/{id}` → destroyAccount
  - `GET /api/savings/goals` → goals index
  - `POST /api/savings/goals` → storeGoal
  - `PUT /api/savings/goals/{id}` → updateGoal
  - `DELETE /api/savings/goals/{id}` → destroyGoal
  - `PATCH /api/savings/goals/{id}/progress` → updateGoalProgress
- [x] Protect all routes with `auth:sanctum` middleware

---

## Caching Strategy

- [x] Implement caching in SavingsAgent->analyze()
- [x] Cache key: `savings_analysis_{user_id}_{input_hash}`
- [x] TTL: 30 minutes
- [x] Cache ISA allowance status: `isa_allowance_{user_id}_{tax_year}`
- [x] Implement cache invalidation on account/goal updates

---

## Testing Tasks

### Unit Tests

- [x] Test `calculateRunway()` with various inputs
- [x] Test `calculateAdequacy()` calculation
- [x] Test `getISAAllowanceStatus()` cross-module calculation
- [x] Test `calculateProgress()` for savings goals
- [x] Test `projectGoalAchievement()` with compound interest
- [x] Test `compareToMarketRates()` logic
- [x] Test `categorizeLiquidity()` grouping

### Feature Tests

- [x] Test GET /api/savings endpoint
- [x] Test POST /api/savings/analyze with valid data
- [x] Test GET /api/savings/isa-allowance/{taxYear} returns correct structure
- [x] Test POST /api/savings/accounts creates account
- [x] Test PUT /api/savings/accounts/{id} updates account
- [x] Test DELETE /api/savings/accounts/{id} deletes account
- [x] Test POST /api/savings/goals creates goal
- [x] Test PATCH /api/savings/goals/{id}/progress updates progress
- [x] Test authorization (users cannot access others' data)
- [x] Test ISA allowance updates when account is created/modified

### Integration Tests

- [x] Test full analysis flow: create profile → add accounts → run analysis
- [x] Test ISA allowance cross-module calculation (Savings + Investment)
- [x] Test cache hit/miss behavior
- [x] Test goal progress tracking over time

### Postman Collection

- [x] Create Savings Module collection in Postman
- [x] Add request for each endpoint
- [x] Add examples for ISA allowance tracking
- [x] Export collection
