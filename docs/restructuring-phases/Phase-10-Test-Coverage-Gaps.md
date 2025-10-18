# Phase 10: Test Coverage Gaps Analysis

**Date:** 2025-10-18
**Analysis Type:** Comprehensive gap identification
**Purpose:** Identify untested code and create action plan to reach 80%+ coverage

---

## Gap Analysis Summary

| Category | Current Coverage | Target Coverage | Gap | Priority |
|----------|------------------|-----------------|-----|----------|
| **Backend Services** | ~80-85% | 90% | 5-10% | Medium |
| **API Controllers** | ~70-75% | 85% | 10-15% | High |
| **Models & Eloquent** | ~60-70% | 80% | 10-20% | Medium |
| **Frontend (Vue.js)** | 0% | 75% | 75% | **CRITICAL** |
| **Integration/E2E** | ~15% | 60% | 45% | High |
| **Overall Estimate** | ~60-65% | 80% | 15-20% | - |

---

## Critical Gaps (Priority 1)

### 1. Frontend Testing - COMPLETELY MISSING

**Impact:** Massive gap - frontend has ZERO test coverage

**Missing Test Types:**

#### Vue Component Tests (0 tests exist)
**Tools Needed:** Vitest + Vue Test Utils

**Critical Components to Test:**

1. **Dashboard Cards** (7 components)
   - `NetWorthOverviewCard.vue`
   - `RetirementOverviewCard.vue`
   - `EstateOverviewCard.vue`
   - `ProtectionOverviewCard.vue`
   - `TrustsOverviewCard.vue`
   - `UKTaxesOverviewCard.vue`
   - `QuickActions.vue`

**Test Coverage Needed:**
```javascript
// Example: NetWorthOverviewCard.test.js
describe('NetWorthOverviewCard', () => {
  it('renders net worth value correctly', () => { /* ... */ });
  it('formats currency as GBP', () => { /* ... */ });
  it('navigates to net worth page on click', () => { /* ... */ });
  it('displays loading state', () => { /* ... */ });
  it('handles error state', () => { /* ... */ });
});
```

2. **Form Components** (15+ components)
   - Property form modal
   - Mortgage form modal
   - Business interest form
   - Cash account form
   - Family member form
   - Income/occupation form
   - Trust form modal
   - DC/DB pension forms

**Test Coverage Needed:**
```javascript
// Example: PropertyFormModal.test.js
describe('PropertyFormModal', () => {
  it('validates required fields', () => { /* ... */ });
  it('emits save event with correct data', () => { /* ... */ }); // NOT @submit!
  it('handles API errors gracefully', () => { /* ... */ });
  it('resets form on cancel', () => { /* ... */ });
  it('populates form in edit mode', () => { /* ... */ });
  it('calculates ownership percentages correctly', () => { /* ... */ });
});
```

3. **Module Dashboards** (6 dashboards)
   - Net Worth Dashboard
   - Retirement Dashboard
   - Estate Dashboard
   - Protection Dashboard
   - Trusts Dashboard
   - User Profile

**Test Coverage Needed:**
```javascript
// Example: RetirementDashboard.test.js
describe('RetirementDashboard', () => {
  it('loads retirement data on mount', () => { /* ... */ });
  it('switches between tabs correctly', () => { /* ... */ });
  it('displays pension data in table', () => { /* ... */ });
  it('opens edit modal on row click', () => { /* ... */ });
  it('refreshes data after successful save', () => { /* ... */ });
});
```

#### Vuex Store Tests (0 tests exist)

**Stores to Test:**

1. **auth.js** (authentication store)
   - Login/logout mutations
   - User state management
   - Token handling

2. **netWorth.js**
   - Asset/liability state
   - CRUD mutations
   - API action calls

3. **trusts.js**
   - Trust state management
   - Beneficiary tracking
   - CRUD operations

4. **userProfile.js**
   - Personal info state
   - Family member management
   - Income/occupation state

**Test Coverage Needed:**
```javascript
// Example: netWorth.test.js
describe('netWorth store', () => {
  describe('mutations', () => {
    it('SET_PROPERTIES updates state correctly', () => { /* ... */ });
    it('ADD_PROPERTY adds new property to array', () => { /* ... */ });
    it('UPDATE_PROPERTY modifies existing property', () => { /* ... */ });
    it('DELETE_PROPERTY removes property from state', () => { /* ... */ });
  });

  describe('actions', () => {
    it('fetchProperties calls API and commits mutation', async () => { /* ... */ });
    it('createProperty sends POST request', async () => { /* ... */ });
    it('handles API errors in actions', async () => { /* ... */ });
  });

  describe('getters', () => {
    it('totalPropertyValue calculates sum correctly', () => { /* ... */ });
    it('netWorth subtracts liabilities from assets', () => { /* ... */ });
  });
});
```

#### Service Layer Tests (0 tests exist)

**Services to Test:**

- `resources/js/services/mortgageService.js`
- `resources/js/services/netWorthService.js`
- `resources/js/services/propertyService.js`
- `resources/js/services/userProfileService.js`

**Test Coverage Needed:**
```javascript
// Example: propertyService.test.js
describe('propertyService', () => {
  it('fetchProperties makes GET request to /api/properties', async () => { /* ... */ });
  it('createProperty sends POST with correct data', async () => { /* ... */ });
  it('handles 422 validation errors', async () => { /* ... */ });
  it('handles network errors', async () => { /* ... */ });
});
```

**Action Required:**
1. Install Vitest: `npm install -D vitest @vue/test-utils jsdom`
2. Create `vitest.config.js`
3. Write 50+ component tests
4. Write 20+ store tests
5. Write 15+ service tests
6. **Target:** 75% frontend coverage

**Estimated Effort:** 40-60 hours

---

### 2. Retirement API Authentication Bypass

**Impact:** Security vulnerability - endpoints accessible without authentication

**Files Affected:**
- `routes/api.php` - Retirement routes missing `auth:sanctum` middleware

**Failing Tests:**
```
tests/Feature/RetirementModuleTest.php:
- GET /api/retirement returns 200 instead of 401 for unauthenticated
- POST /api/retirement/analyze returns 200 instead of 422/401
```

**Root Cause:**
Retirement routes not wrapped in `auth:sanctum` middleware group

**Fix Required:**
```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    // Ensure ALL retirement routes are inside this group
    Route::get('/retirement', [RetirementController::class, 'index']);
    Route::post('/retirement/analyze', [RetirementController::class, 'analyze']);
    Route::post('/retirement/pensions/dc', [RetirementController::class, 'storeDCPension']);
    // ... etc
});
```

**Action Required:**
1. Review `routes/api.php`
2. Move retirement routes inside `auth:sanctum` group
3. Re-run tests to verify 401 responses
4. Verify ALL API routes require authentication

**Estimated Effort:** 1-2 hours

---

### 3. Coordination ConflictResolver - ALL TESTS FAILING

**Impact:** Core cross-module coordination not working

**Files Affected:**
- `app/Services/Coordination/ConflictResolverService.php`
- `tests/Unit/Services/Coordination/ConflictResolverTest.php`

**Failing Tests (11 tests):**
- Cash flow conflict detection
- ISA allowance conflict resolution
- Protection vs Savings prioritization
- Contribution allocation logic
- Surplus allocation
- ISA split recommendations

**Potential Root Causes:**
1. Service implementation incomplete
2. Test expectations don't match implementation
3. Missing dependencies or calculation logic
4. Data structure mismatch

**Action Required:**
1. Read ConflictResolverService.php implementation
2. Compare with test expectations
3. Debug each failing test individually
4. Fix implementation or update tests
5. Ensure coordination logic works correctly

**Estimated Effort:** 8-12 hours

---

## High Priority Gaps (Priority 2)

### 4. Module-Specific Test Gaps

#### User Profile Module (Phase 02)
**Missing Tests:**
- `UserProfileServiceTest.php` - Does not exist
- Personal info CRUD logic
- Family member relationship validation
- Income/occupation calculations
- Household creation/linking

**Tests Needed:**
```php
// tests/Unit/Services/UserProfile/UserProfileServiceTest.php
describe('UserProfileService', function () {
    test('validatePersonalInfo checks all required fields');
    test('calculateNetIncomeAfterTax applies correct tax rates');
    test('linkSpouse creates bidirectional relationship');
    test('createHousehold generates household for couple');
    test('addFamilyMember validates relationships');
});
```

**Estimated Effort:** 6-8 hours

#### Trust Module (Phase 06)
**Missing Tests:**
- `TrustServiceTest.php` - Does not exist
- Trust valuation calculations
- Beneficiary share calculations
- IHT exemption logic for trusts
- Trust type validation

**Tests Needed:**
```php
// tests/Unit/Services/Trust/TrustServiceTest.php
describe('TrustService', function () {
    test('calculateTrustValue aggregates all trust assets');
    test('calculateBeneficiaryShares distributes correctly');
    test('checkIHTExemption validates trust exemption status');
    test('validateTrustType ensures valid trust category');
});
```

**Estimated Effort:** 4-6 hours

#### UK Taxes Module (Admin - Phase 08)
**Missing Tests:**
- `UKTaxesControllerTest.php` - Does not exist
- Tax rate CRUD operations
- Allowance updates
- Historical tax rate retrieval

**Tests Needed:**
```php
// tests/Feature/Api/UKTaxesControllerTest.php
describe('UKTaxes API', function () {
    test('GET /api/uk-taxes returns current tax rates');
    test('PUT /api/uk-taxes/:id updates tax rate (admin only)');
    test('POST /api/uk-taxes creates new tax year config (admin only)');
    test('non-admin users receive 403 forbidden');
});
```

**Estimated Effort:** 3-4 hours

---

### 5. Incomplete Module Test Coverage

#### Investment Module - Monte Carlo Simulator
**Current Status:** Some tests passing, edge cases failing

**Missing Coverage:**
- Extreme volatility scenarios (>50% annual volatility)
- Negative return sequences (bear markets)
- Very long time horizons (40+ years)
- Zero/negative starting balance edge cases

**Tests to Add:**
```php
test('handles extreme volatility without crashes', function () {
    $result = $simulator->runSimulation([
        'starting_balance' => 100000,
        'volatility' => 0.75, // 75% volatility
        'years' => 30,
    ]);
    expect($result['simulations'])->toHaveCount(1000);
});

test('handles prolonged bear market', function () {
    $result = $simulator->runSimulation([
        'expected_return' => -0.05, // -5% annual return
        'years' => 10,
    ]);
    expect($result['probability_of_success'])->toBeLessThan(50);
});
```

**Estimated Effort:** 3-4 hours

#### Estate Module - Cash Flow Projections
**Current Status:** 4 tests failing

**Failing Tests:**
- Personal P&L creation with debt servicing
- Multi-year cash flow projection
- Inflation application to future years
- Discretionary income calculation

**Root Cause Analysis Needed:**
1. Check inflation formula: `value * (1 + inflation_rate)^years`
2. Verify debt servicing deduction logic
3. Test cumulative cash flow calculation
4. Ensure discretionary income = total income - essential expenses

**Tests to Fix:**
```php
test('applies inflation correctly to future years', function () {
    $projection = $service->projectCashFlow([
        'income' => 50000,
        'expenses' => 40000,
        'years' => 5,
        'inflation_rate' => 0.03, // 3%
    ]);

    // Year 5 income should be 50000 * 1.03^5 = 57,964
    expect($projection['year_5']['income'])->toBeCloseTo(57964, 0);
});
```

**Estimated Effort:** 4-6 hours

---

## Medium Priority Gaps (Priority 3)

### 6. Integration Test Coverage

**Current Status:** Limited integration tests (only 3-4 test files)

**Missing Integration Tests:**

#### Complete User Journeys
1. **New User Onboarding Flow**
   - Register → Login → Complete Profile → Add First Asset → View Dashboard

2. **Property Purchase Journey**
   - Add Property → Add Mortgage → Link to Property → View Net Worth Impact

3. **Retirement Planning Journey**
   - Add DC Pension → Add DB Pension → Run Analysis → Review Recommendations → Adjust Contributions

4. **Estate Planning Journey**
   - Enter Assets → Add Liabilities → Calculate IHT → Review Gifting Strategy → Create Trust

5. **Cross-Module Coordination Flow**
   - Add Savings Goal → Add Investment Account → Get Recommendations → Resolve ISA Allowance Conflict

**Tests Needed:**
```php
// tests/Feature/Integration/UserOnboardingJourneyTest.php
test('new user can complete full onboarding', function () {
    // Step 1: Register
    $registerResponse = $this->postJson('/api/register', [/* ... */]);

    // Step 2: Login
    $loginResponse = $this->postJson('/api/login', [/* ... */]);
    $token = $loginResponse->json('token');

    // Step 3: Complete Profile
    $profileResponse = $this->withToken($token)
        ->putJson('/api/user-profile/personal-info', [/* ... */]);

    // Step 4: Add Property
    $propertyResponse = $this->withToken($token)
        ->postJson('/api/properties', [/* ... */]);

    // Step 5: View Dashboard
    $dashboardResponse = $this->withToken($token)
        ->getJson('/api/dashboard');

    expect($dashboardResponse->json('data.netWorth'))->toBeGreaterThan(0);
});
```

**Estimated Effort:** 20-30 hours for comprehensive integration tests

---

### 7. Edge Case & Boundary Testing

**Missing Edge Case Tests:**

#### Negative Values
- Negative net worth scenarios
- Negative pension balances (debt)
- Negative interest rates

#### Zero Values
- Zero assets/liabilities
- Zero pension contributions
- Zero IHT liability

#### Maximum Values
- Very large estates (£50m+)
- Very high incomes (£500k+)
- Maximum ISA contributions over decades

#### Date Boundaries
- Pension access before minimum pension age
- Gifting exactly 7 years ago (taper boundary)
- Tax year boundaries (April 5/6)

**Tests to Add:**
```php
test('handles negative net worth correctly', function () {
    $netWorth = $service->calculateNetWorth([
        'assets' => 100000,
        'liabilities' => 150000,
    ]);
    expect($netWorth)->toBe(-50000);
});

test('handles zero IHT liability when estate below NRB', function () {
    $iht = $service->calculateIHT(['estate_value' => 200000]);
    expect($iht['liability'])->toBe(0);
});
```

**Estimated Effort:** 10-15 hours

---

### 8. Validation & Error Handling Tests

**Missing Validation Tests:**

#### Form Request Validation
Many endpoints lack dedicated FormRequest validation tests

**Missing FormRequest Test Coverage:**
- `StoreFamilyMemberRequest`
- `UpdateFamilyMemberRequest`
- `StorePropertyRequest`
- `UpdatePropertyRequest`
- `StoreMortgageRequest`
- `UpdateMortgageRequest`
- `StorePersonalAccountLineItemRequest`
- `UpdatePersonalAccountLineItemRequest`

**Tests Needed:**
```php
// tests/Unit/Requests/StorePropertyRequestTest.php
describe('StorePropertyRequest', function () {
    test('requires property_type field');
    test('requires address_line_1 field');
    test('requires current_value field');
    test('validates property_type is valid enum value');
    test('validates current_value is numeric and positive');
    test('validates ownership_percentage is between 0 and 100');
    test('validates postcode format if provided');
});
```

**Estimated Effort:** 12-16 hours for all FormRequests

---

## Low Priority Gaps (Priority 4)

### 9. Performance Testing

**Missing Performance Tests:**

#### API Response Time Tests
```php
test('dashboard loads in under 500ms', function () {
    $start = microtime(true);
    $response = $this->getJson('/api/dashboard');
    $duration = microtime(true) - $start;

    expect($duration)->toBeLessThan(0.5); // 500ms
    $response->assertStatus(200);
});
```

#### Database Query Performance
```php
test('net worth calculation uses under 10 queries', function () {
    DB::enableQueryLog();
    $service->calculateNetWorth($userId);
    $queries = DB::getQueryLog();

    expect(count($queries))->toBeLessThan(10);
});
```

**Estimated Effort:** 6-8 hours

---

### 10. Security Testing

**Missing Security Tests:**

#### Authorization Tests
```php
test('user cannot access another users properties', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $property = Property::factory()->create(['user_id' => $user1->id]);

    $response = $this->actingAs($user2)
        ->getJson("/api/properties/{$property->id}");

    $response->assertStatus(403);
});
```

#### Input Sanitization Tests
```php
test('XSS attempts are sanitized in text fields', function () {
    $response = $this->postJson('/api/properties', [
        'address_line_1' => '<script>alert("XSS")</script>',
    ]);

    $property = Property::find($response->json('data.id'));
    expect($property->address_line_1)->not->toContain('<script>');
});
```

**Estimated Effort:** 8-10 hours

---

## Testing Infrastructure Gaps

### 11. Missing Testing Tools

**Current Tools:**
✅ Pest (backend unit/feature tests)
✅ Architecture tests
✅ Database factories

**Missing Tools:**
❌ **Vitest** - Frontend testing framework
❌ **Vue Test Utils** - Vue component testing
❌ **Playwright/Cypress** - E2E browser automation
❌ **Code Coverage Tool** - Xdebug or PCOV not enabled
❌ **PHPStan/Larastan** - Static analysis
❌ **ESLint** - Frontend code quality

**Installation Needed:**

```bash
# Frontend testing
npm install -D vitest @vue/test-utils jsdom

# E2E testing
npm install -D @playwright/test

# Backend coverage
pecl install pcov

# Static analysis
composer require --dev phpstan/phpstan larastan/larastan

# Frontend linting
npm install -D eslint @vue/eslint-config-prettier
```

**Estimated Effort:** 4-6 hours setup + configuration

---

### 12. CI/CD Pipeline Missing

**Current State:** Tests run manually only

**Missing:**
- Automated test runs on commit/push
- PR test validation
- Coverage reporting
- Failed test notifications

**Recommended Setup:**
```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  backend-tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2
          extensions: pcov
      - name: Install Dependencies
        run: composer install
      - name: Run Tests
        run: ./vendor/bin/pest --coverage --min=80

  frontend-tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup Node
        uses: actions/setup-node@v2
        with:
          node-version: 18
      - name: Install Dependencies
        run: npm ci
      - name: Run Tests
        run: npm run test:run
```

**Estimated Effort:** 6-8 hours

---

## Gap Closure Action Plan

### Sprint 1 (Week 1): Critical Gaps
**Total Effort:** 50-72 hours

1. ✅ Fix Retirement API Authentication (1-2h)
2. ✅ Debug ConflictResolver (8-12h)
3. ✅ Install Vitest & Vue Test Utils (4-6h)
4. ✅ Write Dashboard Card Tests (12-16h)
5. ✅ Write Core Component Tests (16-24h)
6. ✅ Write Vuex Store Tests (8-12h)

**Deliverable:** Frontend testing foundation + security fixes

---

### Sprint 2 (Week 2): High Priority Gaps
**Total Effort:** 40-56 hours

1. ✅ Create UserProfileServiceTest (6-8h)
2. ✅ Create TrustServiceTest (4-6h)
3. ✅ Create UKTaxesControllerTest (3-4h)
4. ✅ Fix Estate Cash Flow Tests (4-6h)
5. ✅ Fix Investment Monte Carlo Edge Cases (3-4h)
6. ✅ Write FormRequest Validation Tests (12-16h)
7. ✅ Write Integration Journey Tests (8-12h)

**Deliverable:** Complete module test coverage + validation tests

---

### Sprint 3 (Week 3): Medium Priority Gaps
**Total Effort:** 30-45 hours

1. ✅ Edge Case & Boundary Tests (10-15h)
2. ✅ Service Layer Tests (Frontend) (6-8h)
3. ✅ Performance Tests (6-8h)
4. ✅ Security Tests (8-10h)

**Deliverable:** Comprehensive edge case coverage + non-functional tests

---

### Sprint 4 (Week 4): Infrastructure & Low Priority
**Total Effort:** 20-30 hours

1. ✅ Install & Configure Code Coverage Tools (4-6h)
2. ✅ Set up CI/CD Pipeline (6-8h)
3. ✅ E2E Test Framework Setup (4-6h)
4. ✅ Static Analysis Setup (2-3h)
5. ✅ Documentation Updates (4-7h)

**Deliverable:** Full testing infrastructure + automation

---

## Success Criteria

✅ **80%+ Code Coverage** (overall)
✅ **90%+ Backend Service Coverage**
✅ **75%+ Frontend Component Coverage**
✅ **60%+ Integration Test Coverage**
✅ **All Critical Bugs Fixed** (Authentication, ConflictResolver)
✅ **All Priority 1 & 2 Tests Written**
✅ **CI/CD Pipeline Running**
✅ **No Failing Tests in Main Branch**

---

## Estimated Total Effort

| Sprint | Effort (hours) | Priority |
|--------|----------------|----------|
| Sprint 1 | 50-72 | Critical |
| Sprint 2 | 40-56 | High |
| Sprint 3 | 30-45 | Medium |
| Sprint 4 | 20-30 | Low |
| **TOTAL** | **140-203 hours** | **~4-5 weeks** |

---

**Recommendation:** Focus on Sprint 1 & 2 first to achieve minimum viable test coverage (>80%). Sprints 3 & 4 can be completed iteratively as time allows.

**Next Steps:**
1. Fix Retirement authentication (immediate)
2. Debug ConflictResolver (immediate)
3. Set up Vitest (this week)
4. Begin writing frontend component tests (this week)

---

**Document Created:** 2025-10-18
**Status:** Comprehensive gap analysis complete
**Ready For:** Sprint planning and test development
