# TenGo Application - Quick Reference Guide

**Version**: v0.2.6  
**Last Updated**: November 12, 2025

---

## Quick Stats

| Aspect | Count |
|--------|-------|
| Database Models | 45+ |
| API Endpoints | 80+ |
| Vue Components | 150+ |
| Services | 40+ |
| Agents | 6 |
| Vuex Store Modules | 15 |
| Database Tables | 45+ |
| Test Cases | 60+ |
| Routes (Vue Router) | 30+ |

---

## Five Core Modules

### 1. **Protection** - Insurance Analysis
- Life insurance (5 types)
- Critical illness (3 types)
- Income/disability protection
- Coverage gap analysis (0-100 score)
- Scenario modeling
- Professional plan generation

### 2. **Savings** - Cash Management
- 4 account types (ISA, General, Notice, Fixed)
- Emergency fund calculation (3-6 months)
- £20,000 ISA limit tracking (cross-module)
- Goal progress monitoring
- Rate comparison

### 3. **Investment** - Portfolio Optimization
- 7 account types (ISA, GIA, NS&I, Bonds, VCT, EIS)
- Advanced risk metrics (Alpha, Beta, Sharpe, VaR)
- 1,000-iteration Monte Carlo simulations
- Efficient Frontier analysis
- Fee analysis and tax optimization
- Asset location optimization

### 4. **Retirement** - Pension Planning
- DC/DB/State pension tracking
- **NEW**: DC pension portfolio optimization
- **NEW**: Polymorphic holdings system
- Annual allowance tracking (£60,000)
- Readiness scoring
- Decumulation planning

### 5. **Estate** - IHT & Net Worth
- Single & married IHT calculations
- Combined NRB (£650k) + RNRB (£350k)
- Actuarial life tables (ONS data)
- 7-year gifting rules
- Trust management
- Will planning
- Net worth tracking

---

## Special Features

### Letter to Spouse
- 4-part emergency instructions
- Auto-populate from all modules
- Dual view (edit own, read spouse's)
- 30+ data fields

### Holistic Planning
- Cross-module analysis
- Conflict resolution
- Priority ranking
- 20-year projections
- Financial health score (0-100)

### Asset Ownership
- **3 types**: Individual, Joint (reciprocal), Trust
- Joint owner auto-linking
- Spouse data sharing controls
- ISA/Pension rules (individual only)

### User Management
- Admin panel with 4 tabs
- Database backup/restore
- Tax settings (6 tax years available)
- User management
- Auto-spouse account creation

---

## Key Technologies

| Layer | Tech |
|-------|------|
| Backend | Laravel 10.x + Sanctum auth |
| Frontend | Vue.js 3 + Vuex + ApexCharts |
| Database | MySQL 8.0+ + Memcached |
| Build | Vite + npm |
| Testing | Pest PHP + Vitest |
| Styling | Tailwind CSS 3.4 |
| Charts | ApexCharts 5.3 |

---

## Critical Database Tables

**User Management** (7):
- `users`, `personal_accounts`, `family_members`, `households`, `spouse_permissions`, `onboarding_progress`, `recommendation_tracking`

**Protection** (5):
- `protection_profiles`, `life_insurance_policies`, `critical_illness_policies`, `income_protection_policies`, `sickness_illness_policies`

**Savings** (3):
- `savings_accounts`, `savings_goals`, `isa_allowance_tracking`

**Investment** (9):
- `investment_accounts`, `holdings`, `investment_goals`, `investment_plans`, `investment_recommendations`, `investment_scenarios`, `risk_profiles`, `rebalancing_actions`, + analysis tables

**Retirement** (4):
- `dc_pensions`, `db_pensions`, `state_pensions`, `retirement_profiles` (+ polymorphic holdings)

**Estate** (13):
- `assets`, `liabilities`, `properties`, `mortgages`, `gifts`, `trusts`, `wills`, `bequests`, `iht_profiles`, `iht_calculations`, `net_worth_statements`, `actuarial_life_tables`, + business/chattels

---

## API Route Structure

### Authentication (5 routes)
```
POST /api/auth/register
POST /api/auth/login
POST /api/auth/logout
GET  /api/auth/user
POST /api/auth/change-password
```

### Onboarding (8+ routes)
```
GET  /api/onboarding/status
POST /api/onboarding/focus-area
GET  /api/onboarding/steps
POST /api/onboarding/step
POST /api/onboarding/complete
```

### User Profile (18+ routes)
```
GET  /api/user/profile
PUT  /api/user/profile/personal
GET  /api/user/letter-to-spouse
PUT  /api/user/letter-to-spouse
GET  /api/user/family-members
POST /api/user/family-members
```

### Per-Module Routes
- **Protection**: 8+ routes
- **Savings**: 10+ routes
- **Investment**: 25+ routes (largest)
- **Retirement**: 20+ routes
- **Estate**: 20+ routes
- **Holistic**: 8+ routes

---

## Frontend Structure

### View Files (25+)
- Dashboard.vue
- Protection/, Savings/, Investment/, Retirement/, Estate/ folders
- NetWorth/, Plans/, Trusts/, UKTaxes/ folders
- UserProfile.vue, Help.vue, Version.vue
- Admin/AdminPanel.vue
- Public pages (Landing, Calculators, Learning)

### Component Files (150+)
- Protection: 15+ components
- Savings: 10+ components
- Investment: 20+ components (largest)
- Retirement: 15+ components
- Estate: 20+ components
- Others: Dashboard, NetWorth, Holistic, Admin

### Vuex Modules (15)
```javascript
auth.js              // Login/logout, token, user role
protection.js        // Policies, gap analysis
savings.js           // Accounts, emergency fund, ISA tracking
investment.js        // Accounts, holdings, analysis (LARGEST - 46KB)
retirement.js        // Pensions, readiness
estate.js            // Assets, IHT, gifting
netWorth.js          // Overview, breakdown, trend
holistic.js          // Recommendations, executive summary
dashboard.js         // Module summaries
userProfile.js       // Profile data, family members
onboarding.js        // Progress tracking
trusts.js            // Trust data
spousePermission.js  // Data sharing permissions
recommendations.js   // Recommendation status
user.js              // User state
```

---

## Form Pattern (Critical Rule)

**Unified Form Components** - Single form used everywhere

```vue
<!-- Parent Component -->
<ItemForm
  v-if="showForm"
  :item="editingItem"
  :is-editing="!!editingItem"
  @save="handleSave"      <!-- Use @save, NOT @submit -->
  @close="closeForm"
/>

<!-- Form Component -->
export default {
  props: ['item', 'isEditing'],
  methods: {
    handleSubmit() {
      this.$emit('save', this.formData);  // Emit save event
    }
  }
}
```

**DO NOT USE**: `@submit` event (causes double submission)

---

## Service Layer Organization

### Specialized Calculators
- `MonteCarloSimulator` - 1,000 iteration portfolios
- `EfficientFrontierCalculator` - Markowitz optimization
- `IHTCalculationService` - Complete IHT modeling
- `PensionProjector` - Retirement income

### Analysis Services
- `PortfolioAnalyzer` - Risk metrics (Alpha, Beta, Sharpe, VaR)
- `CoverageGapAnalyzer` - Insurance needs
- `NetWorthAnalyzer` - Asset/liability aggregation
- `EmergencyFundCalculator` - Cash adequacy

### Optimization Services
- `AssetAllocationOptimizer` - Portfolio allocation
- `AssetLocationOptimizer` - Account placement
- `TaxEfficiencyCalculator` - Tax optimization
- `RebalancingCalculator` - Drift-based rebalancing

### Strategy Services
- `PersonalizedGiftingStrategyService` - Gifting optimization
- `PersonalizedTrustStrategyService` - Trust planning
- `GiftingTimelineService` - 7-year rules
- `LifePolicyStrategyService` - Insurance strategy

---

## Ownership Types (Three Options)

### 1. Individual
- Default for most assets
- Only option for ISAs and pensions

### 2. Joint (Creates Reciprocal Records)
- Links two people
- Can be spouse in system or external
- Applies to: Properties, investments, savings, business, chattels

### 3. Trust
- Links to Trust model
- IHT treatment differs
- Applies to: Properties, investments, chattels

---

## Tax Configuration

**Database-Driven** (NOT hardcoded)

### Access Pattern
```php
$taxConfig = new TaxConfigService();
$personalAllowance = $taxConfig->getIncomeTax()['personal_allowance'];
$isaLimit = $taxConfig->getISAAllowances()['annual_allowance'];
```

### Available Tax Years
- 2021/22, 2022/23, 2023/24, 2024/25, 2025/26 (active)

### Key Values (2025/26)
- Income tax personal allowance: £12,570
- ISA limit: £20,000
- Pension annual allowance: £60,000
- IHT nil rate band: £325,000
- IHT residence nil rate band: £175,000

---

## Admin Panel (4 Tabs)

1. **Dashboard**
   - User statistics
   - System health metrics
   - Database size

2. **User Management**
   - View all users
   - Assign admin roles
   - Manage accounts

3. **Database Backups**
   - Create on-demand backups
   - Restore from backup
   - View backup history
   - Auto-backup scheduling

4. **Tax Settings**
   - Select active tax year
   - Edit all tax values
   - Activate new year
   - Document changes

---

## Caching Strategy

| What | TTL | When Cleared |
|------|-----|--------------|
| Tax Config | 1 hour | On save |
| Monte Carlo Results | 24 hours | On new calculation |
| Dashboard Data | 30 minutes | On data change |
| Agent Analysis | 1 hour | On data change |
| Holistic Plan | 24 hours | On data change |

---

## Testing

### Test Frameworks
- **Pest PHP** - Backend unit/feature tests
- **Vitest** - Frontend component tests
- **Playwright** - E2E testing (configured)

### Test Coverage
- 60+ tests (100% passing)
- Unit tests for services/calculations
- Feature tests for APIs
- Architecture tests for code standards

### Run Tests
```bash
./vendor/bin/pest                   # All tests
./vendor/bin/pest --testsuite=Unit # Unit only
./vendor/bin/pest tests/Unit/Services/Protection/AdequacyScorerTest.php
```

---

## Development Setup

### First Time
```bash
git clone <repo> tengo
cd tengo
composer install && npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=TaxConfigurationSeeder
```

### Daily Development (3 Terminals)
```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev

# Terminal 3 (Optional)
php artisan queue:work database
```

### Or Use Script
```bash
./dev.sh
```

---

## Code Quality Tools

### Format Code (PSR-12)
```bash
./vendor/bin/pint              # Fix issues
./vendor/bin/pint --test       # Check only
```

### Clear Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## Key Files to Know

| File | Purpose |
|------|---------|
| `.env` | Local environment config |
| `app/Agents/*.php` | Module analysis orchestrators |
| `app/Services/` | Domain business logic |
| `app/Models/` | Eloquent models (45+ files) |
| `routes/api.php` | All 80+ API endpoints |
| `resources/js/router/index.js` | Vue Router (30+ routes) |
| `resources/js/store/modules/` | Vuex stores (15 files) |
| `CLAUDE.md` | Development guidelines |
| `database/migrations/` | Schema changes |
| `tests/` | Test suite (60+ tests) |

---

## Critical Rules

1. **NEVER** use `@submit` on form modals (use `@save`)
2. **NEVER** hardcode tax values (use `TaxConfigService`)
3. **NEVER** export production `.env` vars in development
4. **ALWAYS** use `declare(strict_types=1);` in PHP files
5. **ALWAYS** use centralized `ISATracker` for ISA limits
6. **ALWAYS** check both `user_id` AND `spouse_id` in queries
7. **ALWAYS** use canonical data types (no `second_home`, use `secondary_residence`)
8. **ALWAYS** follow unified form component pattern

---

## Common Development Tasks

### Add New Policy Type (Protection)
1. Add migration (update enum)
2. Update `PolicyForm.vue` options
3. Update `ProtectionController` validation
4. Update services if needed
5. Run tests

### Add New Investment Account Type
1. Add to `investment_accounts` enum
2. Update `InvestmentForm.vue` options
3. Update `AssetLocationOptimizer` if needed
4. Update `FeeAnalyzer` if needed
5. Run tests

### Update Tax Values
1. Admin Panel → Tax Settings
2. Select tax year, edit values
3. Activate (auto-deactivates previous)
4. Cache auto-clears after 1 hour

### Add Spouse Permission
1. Add fields to `SpousePermission` model
2. Update `spouse_permissions` table migration
3. Update `SpousePermissionController`
4. Update Vue components to check permission
5. Update tests

---

## Performance Tips

- Use `:key` binding on all `v-for` loops
- Lazy load Vue components with `() => import(...)`
- Cache expensive calculations in Memcached
- Use eager loading: `.with(['relation'])`
- Paginate large result sets
- Run Monte Carlo as queue jobs (async)

---

## Demo Credentials

| User | Email | Password | Notes |
|------|-------|----------|-------|
| Demo | demo@fps.com | password | Full access |
| Admin | admin@fps.com | admin123456 | Admin panel access |

---

## File Locations Quick Map

```
tengo/
├── app/
│   ├── Agents/                    # 6 agent files
│   ├── Services/                  # 40+ service files
│   ├── Models/                    # 45+ model files
│   ├── Http/Controllers/Api/      # API endpoints
│   ├── Http/Requests/             # Form validation
│   └── Jobs/                      # Queue jobs
├── resources/js/
│   ├── views/                     # 25+ page components
│   ├── components/                # 150+ components
│   ├── services/                  # API wrapper services
│   ├── store/modules/             # 15 Vuex modules
│   └── router/                    # 30+ routes
├── database/
│   ├── migrations/                # 50+ migrations
│   ├── seeders/                   # Data seeders
│   └── factories/                 # Test factories
├── tests/
│   ├── Unit/                      # 36+ unit tests
│   ├── Feature/                   # Integration tests
│   └── Architecture/              # 24 architecture tests
├── config/                        # Laravel config files
├── storage/app/backups/           # Database backups
├── CLAUDE.md                      # Development guide
├── README.md                      # Project README
└── COMPREHENSIVE_FEATURES_AND_ARCHITECTURE.md  # Full guide
```

---

## Next Steps for Developers

1. Read `CLAUDE.md` for development guidelines
2. Read `COMPREHENSIVE_FEATURES_AND_ARCHITECTURE.md` for full details
3. Run `./dev.sh` to start development
4. Explore `/dashboard` to see all modules
5. Check tests with `./vendor/bin/pest`
6. Follow form pattern for any new forms
7. Use `TaxConfigService` for tax values
8. Always validate via form requests
9. Run `./vendor/bin/pint` before committing
10. Check Vuex modules for state management

---

**Last Updated**: November 12, 2025  
**Version**: v0.2.6 (Beta)  
**Status**: Production-Ready with Security Review Recommended
