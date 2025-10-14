# FPS Implementation Task List

This directory contains a comprehensive, step-by-step implementation plan for the Financial Planning System (FPS). The tasks are organized into 14 separate files, following a feature-driven, backend-first approach.

## Task Organization

### Foundation (1 file)
- **01-foundation-setup.md** - Core infrastructure, authentication, base architecture

### Module Implementation (10 files)
Each of the 5 modules follows a backend-first approach:

#### Protection Module
- **02-protection-backend.md** - API, services, database
- **03-protection-frontend.md** - Vue components, charts, forms

#### Savings Module
- **04-savings-backend.md** - API, services, database
- **05-savings-frontend.md** - Vue components, charts, forms

#### Investment Module
- **06-investment-backend.md** - API, services, database, Monte Carlo
- **07-investment-frontend.md** - Vue components, charts, forms

#### Retirement Module
- **08-retirement-backend.md** - API, services, database
- **09-retirement-frontend.md** - Vue components, charts, forms

#### Estate Planning Module
- **10-estate-backend.md** - API, services, database
- **11-estate-frontend.md** - Vue components, charts, forms

### Integration & Testing (3 files)
- **12-dashboard-integration.md** - Main dashboard, cross-module features
- **13-coordinating-agent.md** - Holistic planning, conflict resolution
- **14-final-testing.md** - Comprehensive testing, security, deployment

## Implementation Approach

### 1. Backend-First Strategy
For each module, complete the backend implementation before moving to the frontend. This ensures:
- Functional APIs ready for frontend integration
- Backend business logic tested independently
- Clear API contracts established
- Ability to test with tools like Postman before UI is built

### 2. Task File Guidelines
- **Implementation tasks**: Maximum 100 lines per file (to maintain focus and digestibility)
- **Testing tasks**: No line limit (comprehensive testing is critical)
- **Self-contained**: Each file can be completed independently
- **Checkboxes**: Use `- [ ]` format for easy progress tracking

### 3. Recommended Workflow

#### Phase 1: Foundation (Days 1-5)
Complete `01-foundation-setup.md` entirely before moving forward. This establishes:
- Laravel and Vue.js setup
- Authentication system
- Base agent architecture
- Centralized configuration
- Development environment

#### Phase 2: Module Implementation (Days 6-60)
For each module, follow this pattern:
1. Complete backend implementation (6-8 days per module)
2. Test backend thoroughly using Postman/Pest
3. Complete frontend implementation (5-7 days per module)
4. Test frontend integration
5. Move to next module

**Suggested Module Order:**
1. Protection (simplest, foundational)
2. Savings (builds on Protection)
3. Investment (introduces Monte Carlo complexity)
4. Retirement (most complex calculations)
5. Estate Planning (ties everything together)

#### Phase 3: Integration (Days 61-75)
1. Dashboard Integration (4-6 days)
2. Coordinating Agent (5-7 days)
3. Cross-module testing

#### Phase 4: Final Testing & Launch (Days 76-85)
Complete `14-final-testing.md`:
- Comprehensive testing
- Performance optimization
- Security hardening
- Deployment preparation

## Testing Strategy

Each task file includes extensive testing tasks at the end:
- **Unit Tests**: Test individual functions and methods
- **Feature Tests**: Test API endpoints
- **Integration Tests**: Test cross-module interactions
- **E2E Tests**: Test complete user journeys
- **Architecture Tests**: Ensure code follows conventions

Run tests frequently throughout implementation:
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --filter=Protection

# Run tests with coverage
php artisan test --coverage
```

## Progress Tracking

### Using Checkboxes
Each task file uses markdown checkboxes. Track progress by:
1. Mark `[x]` when task is completed
2. Add notes/comments as needed
3. Use git to track changes to task files

### Git Workflow
```bash
# Create feature branch for each task file
git checkout -b feature/01-foundation-setup

# Commit frequently
git commit -m "Complete database setup"

# Push and create PR when task file is complete
git push origin feature/01-foundation-setup
```

## Key Development Principles

### 1. UK-Specific Implementation
All tax rules, allowances, and regulations must be UK-specific:
- IHT: £325,000 NRB, £175,000 RNRB
- ISA Allowance: £20,000 for 2024/25
- Pension Annual Allowance: £60,000 with tapering
- CGT Allowance: £3,000 for 2024/25

### 2. Agent-Based Architecture
Every module has an Agent class that orchestrates:
- Analysis logic
- Recommendation generation
- Scenario building

Agents should NOT directly access the database. Use services instead.

### 3. Coding Standards
Follow the standards documented in `CLAUDE.md`:
- **PHP**: PSR-12
- **MySQL**: InnoDB, proper indexing, normalized structure
- **Vue.js**: Vue 3 Composition API, component naming conventions
- **JavaScript**: ES6+, async/await

### 4. Caching Strategy
Implement aggressive caching for expensive operations:
- Monte Carlo simulations: 24 hours
- Analysis results: 1 hour
- Dashboard data: 5 minutes

Invalidate cache on data updates.

### 5. Security First
- All routes protected with `auth:sanctum`
- All user data queries filtered by `user_id`
- Encrypt sensitive fields (account numbers, etc.)
- Validate all inputs
- Sanitize all outputs

## Dependencies Between Tasks

### Critical Path
These tasks must be completed in order:
1. **01-foundation-setup** → All other tasks depend on this
2. **Backend tasks** → Respective frontend tasks
3. **All module tasks** → **12-dashboard-integration**
4. **All module tasks** → **13-coordinating-agent**
5. **All tasks** → **14-final-testing**

### Parallel Work Opportunities
These can be worked on in parallel (if multiple developers):
- Different module backends (after foundation)
- Different module frontends (after their backends)
- Frontend work on Module A while backend work continues on Module B

## Estimated Timeline

- **Foundation**: 5 days
- **Protection**: 11-15 days (6-8 backend + 5-7 frontend)
- **Savings**: 11-15 days
- **Investment**: 11-15 days
- **Retirement**: 11-15 days
- **Estate Planning**: 11-15 days
- **Dashboard Integration**: 4-6 days
- **Coordinating Agent**: 5-7 days
- **Final Testing**: 8-10 days

**Total**: ~85-110 days (4-5 months for single developer)

With multiple developers working in parallel on different modules, timeline can be reduced to ~8-10 weeks.

## Questions or Issues?

If you encounter issues or have questions:
1. Review `CLAUDE.md` for architectural guidance
2. Review `docs/` directory for additional documentation
3. Check existing code patterns in similar modules
4. Consult UK regulations for tax/financial rules

## Notes

- Each task file is designed to be self-contained and completable independently
- Testing tasks are comprehensive and should not be skipped
- Backend completion is a prerequisite for frontend work
- Cross-module features (ISA tracking, net worth aggregation) require coordination
- The Coordinating Agent is the final piece that ties everything together

---

**Last Updated**: 2025-01-13
**Version**: 1.0
