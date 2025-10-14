# Task 13: Coordinating Agent

**Objective**: Implement the Coordinating Agent to orchestrate cross-module analysis, resolve conflicts, and generate holistic financial plan.

**Estimated Time**: 5-7 days

---

## Coordinating Agent Class

### CoordinatingAgent Structure

- [ ] Create `app/Agents/CoordinatingAgent.php` extending `BaseAgent`
- [ ] Inject all module agents: `ProtectionAgent`, `SavingsAgent`, `InvestmentAgent`, `RetirementAgent`, `EstateAgent`
- [ ] Inject `ConflictResolver`, `PriorityRanker`, `HolisticPlanner`
- [ ] Implement `orchestrateAnalysis(int $userId): array` method
- [ ] Implement `generateHolisticPlan(int $userId): array` method
- [ ] Implement `resolveConflicts(array $allRecommendations): array` method
- [ ] Implement `rankRecommendations(array $recommendations): array` method

---

## Services

### Conflict Resolver

- [ ] Create `app/Services/Coordination/ConflictResolver.php`
- [ ] Implement `identifyConflicts(array $recommendations): array`
  - Detect competing recommendations (e.g., increase pension vs. increase emergency fund)
  - Identify cashflow conflicts
  - Find ISA allowance conflicts
- [ ] Implement `resolveProtectionVsSavings(array $recommendations): array`
  - If both recommend increased contributions, balance based on adequacy scores
- [ ] Implement `resolveContributionConflicts(float $availableSurplus, array $demands): array`
  - Allocate surplus across competing needs
  - Priority: Emergency fund → Protection → Pension → Investment → Estate
- [ ] Implement `resolveISAAllocation(float $isaAllowance, array $demands): array`
  - Split between Cash ISA (Savings) and S&S ISA (Investment)
  - Recommend optimal split based on goals and risk

### Priority Ranker

- [ ] Create `app/Services/Coordination/PriorityRanker.php`
- [ ] Implement `rankRecommendations(array $allRecommendations, array $userContext): array`
  - Score each recommendation based on:
    - Urgency (e.g., critical protection gap = high urgency)
    - Impact (financial benefit)
    - Effort (ease of implementation)
    - User priorities (from profile)
- [ ] Implement `calculateRecommendationScore(array $recommendation): float`
  - Formula: `score = (urgency × 0.4) + (impact × 0.3) + (ease × 0.2) + (userPriority × 0.1)`
- [ ] Implement `groupByCategory(array $recommendations): array`
  - Group by module: Protection, Savings, Investment, Retirement, Estate
- [ ] Implement `createActionPlan(array $rankedRecommendations): array`
  - Create timeline: Immediate, Short-term (0-3 months), Medium-term (3-12 months), Long-term (12+ months)

### Holistic Planner

- [ ] Create `app/Services/Coordination/HolisticPlanner.php`
- [ ] Implement `createHolisticPlan(int $userId, array $allAnalysis): array`
  - Combine insights from all 5 modules
  - Create comprehensive financial snapshot
- [ ] Implement `generateExecutiveSummary(array $plan): array`
  - Key strengths
  - Key vulnerabilities
  - Top 5 priorities
- [ ] Implement `projectNetWorthTrajectory(array $allData, int $years): array`
  - Project net worth over time considering all modules
- [ ] Implement `assessOverallRisk(array $allAnalysis): array`
  - Calculate overall risk score
  - Identify key risk areas

### Cash Flow Coordinator

- [ ] Create `app/Services/Coordination/CashFlowCoordinator.php`
- [ ] Implement `calculateAvailableSurplus(int $userId): float`
  - Get income from Estate module (Personal P&L)
  - Sum all contribution demands from modules
  - Calculate remaining surplus/deficit
- [ ] Implement `optimizeContributionAllocation(float $surplus, array $demands): array`
  - Allocate surplus optimally across all needs
- [ ] Implement `identifyCashFlowShortfalls(array $allocation): array`
  - Flag if demands exceed available surplus
  - Suggest income increase or expense reduction strategies

---

## API Endpoints

### Holistic Planning Controller

- [ ] Create `app/Http/Controllers/Api/HolisticPlanningController.php`
- [ ] Inject `CoordinatingAgent`
- [ ] Implement `analyze(Request $request): JsonResponse`
  - Call CoordinatingAgent->orchestrateAnalysis()
  - Return holistic analysis
- [ ] Implement `plan(Request $request): JsonResponse`
  - Call CoordinatingAgent->generateHolisticPlan()
  - Return prioritized action plan
- [ ] Implement `recommendations(Request $request): JsonResponse`
  - Get all recommendations ranked and conflict-resolved
- [ ] Implement `cashFlowAnalysis(Request $request): JsonResponse`
  - Get cash flow coordination results

### Routes

- [ ] Add routes to `routes/api.php`:
  - `POST /api/holistic/analyze`
  - `POST /api/holistic/plan`
  - `GET /api/holistic/recommendations`
  - `GET /api/holistic/cash-flow-analysis`
- [ ] Protect with `auth:sanctum` middleware

---

## Caching Strategy

- [ ] Cache holistic analysis: `holistic_analysis_{user_id}_{input_hash}`, TTL: 1 hour
- [ ] Cache holistic plan: `holistic_plan_{user_id}`, TTL: 24 hours
- [ ] Invalidate cache on any module data update

---

## Frontend Integration

### Holistic Plan View

- [ ] Create `resources/js/views/HolisticPlan.vue`
- [ ] Display executive summary card
- [ ] Display prioritized recommendations list (all modules)
- [ ] Display cash flow allocation chart
- [ ] Display net worth projection chart (10-20 years)
- [ ] Add timeline view: Immediate, Short, Medium, Long-term actions

### Executive Summary Component

- [ ] Create `resources/js/components/Holistic/ExecutiveSummary.vue`
- [ ] Display key strengths (green checkmarks)
- [ ] Display key vulnerabilities (red warnings)
- [ ] Display top 5 priorities (numbered list)
- [ ] Display overall risk score gauge

### Prioritized Recommendations List

- [ ] Create `resources/js/components/Holistic/PrioritizedRecommendations.vue`
- [ ] Display all recommendations sorted by priority score
- [ ] Group by timeline (Immediate, Short, Medium, Long-term)
- [ ] Add module badges (Protection, Savings, etc.)
- [ ] Add "Mark as Done" checkbox
- [ ] Add "View Details" to expand recommendation

### Cash Flow Allocation Chart

- [ ] Create `resources/js/components/Holistic/CashFlowAllocationChart.vue`
- [ ] Use ApexCharts stacked bar chart
- [ ] Show income vs. allocation breakdown
- [ ] Categories: Protection, Savings, Pension, Investment, Debt, Living Expenses
- [ ] Display surplus/deficit clearly
- [ ] Color code: green (surplus), red (deficit)

### Net Worth Projection Chart

- [ ] Create `resources/js/components/Holistic/NetWorthProjectionChart.vue`
- [ ] Use ApexCharts area chart
- [ ] X-axis: Years (now + 10-20 years)
- [ ] Y-axis: Net Worth (£)
- [ ] Show baseline projection vs. optimized projection (with recommendations)
- [ ] Add tooltips

---

## Holistic Plan Service (Frontend)

### Holistic API Service

- [ ] Create `resources/js/services/holisticService.js`
- [ ] Implement `analyzeHolistic(): Promise`
- [ ] Implement `getPlan(): Promise`
- [ ] Implement `getRecommendations(): Promise`
- [ ] Implement `getCashFlowAnalysis(): Promise`
- [ ] Implement `markRecommendationDone(id): Promise`

---

## Vuex Store Module

### Holistic Store

- [ ] Create `resources/js/store/modules/holistic.js`
- [ ] Define state: `analysis`, `plan`, `recommendations`, `cashFlowAnalysis`, `loading`, `error`
- [ ] Define mutations for all state properties
- [ ] Define actions for fetching data
- [ ] Define getters for computed values
- [ ] Register module in main store

---

## Navigation Integration

### Add to Main Menu

- [ ] Add "Holistic Plan" link to main navigation
- [ ] Add route `/holistic-plan` to Vue Router
- [ ] Protect route with authentication guard
- [ ] Add icon for holistic plan (e.g., puzzle pieces or interconnected nodes)

---

## Recommendation Tracking

### Recommendation Tracking Table

- [ ] Create `recommendation_tracking` migration with fields:
  - `id`, `user_id`, `recommendation_id` (generated), `module` (VARCHAR)
  - `recommendation_text` (TEXT), `priority_score` (DECIMAL 5,2)
  - `timeline` (enum: immediate, short_term, medium_term, long_term)
  - `status` (enum: pending, in_progress, completed, dismissed)
  - `completed_at` (TIMESTAMP nullable)
  - `created_at`, `updated_at`
- [ ] Add foreign key and index on `user_id`
- [ ] Create `RecommendationTracking` model

### Tracking Endpoints

- [ ] Add `POST /api/recommendations/{id}/mark-done` endpoint
- [ ] Add `POST /api/recommendations/{id}/dismiss` endpoint
- [ ] Add `GET /api/recommendations/completed` endpoint
- [ ] Update CoordinatingAgent to store recommendations in tracking table

---

## Testing Tasks

### Unit Tests

- [ ] Test `identifyConflicts()` detects competing recommendations
- [ ] Test `resolveContributionConflicts()` allocates surplus correctly
- [ ] Test `resolveISAAllocation()` splits allowance optimally
- [ ] Test `rankRecommendations()` scoring formula
- [ ] Test `calculateRecommendationScore()` with various inputs
- [ ] Test `createActionPlan()` timeline grouping
- [ ] Test `calculateAvailableSurplus()` calculation
- [ ] Test `optimizeContributionAllocation()` optimization logic

### Feature Tests

- [ ] Test POST /api/holistic/analyze endpoint
- [ ] Test POST /api/holistic/plan endpoint
- [ ] Test GET /api/holistic/recommendations endpoint
- [ ] Test recommendation tracking endpoints
- [ ] Test authorization checks

### Integration Tests

- [ ] Test full holistic analysis calling all module agents
- [ ] Test conflict resolution across modules
- [ ] Test priority ranking with real data
- [ ] Test cash flow coordination
- [ ] Test recommendation tracking workflow

### E2E Tests (Manual)

- [ ] Navigate to Holistic Plan view
- [ ] View executive summary
- [ ] View prioritized recommendations list
- [ ] Mark recommendation as done
- [ ] Dismiss recommendation
- [ ] View cash flow allocation chart
- [ ] View net worth projection
- [ ] Verify all module data is integrated
- [ ] Test responsive design
