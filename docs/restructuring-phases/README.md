# FPS Restructuring - Phase Documentation

This directory contains detailed task lists for each phase of the FPS restructuring project.

---

## Phase Overview

| Phase | Title | Status | Estimated Hours | Dependencies |
|-------|-------|--------|-----------------|--------------|
| **1** | [Database Schema & Authentication](Phase-01-Database-Schema.md) | 🟢 Complete | 80 (actual: 6) | None |
| **2** | [User Profile Restructuring](Phase-02-User-Profile.md) | ⚪ Not Started | 80 | Phase 1 |
| **3** | [Net Worth Dashboard](Phase-03-Net-Worth-Dashboard.md) | ⚪ Not Started | 80 | Phase 1, 2 (partial) |
| **4** | [Property Module](Phase-04-Property-Module.md) | ⚪ Not Started | 80 | Phase 1, 3 |
| **5** | [Actions/Recommendations](Phase-05-Actions-Recommendations.md) | ⚪ Not Started | 40 | Phase 1-4 |
| **6** | [Trusts Dashboard](Phase-06-Trusts-Dashboard.md) | ⚪ Not Started | 40 | Phase 1, 3, 4 |
| **7** | [Dashboard Reordering](Phase-07-Dashboard-Reordering.md) | ⚪ Not Started | 20 | Phase 1-6 |
| **8** | [Admin Roles & RBAC](Phase-08-Admin-RBAC.md) | ⚪ Not Started | 20 | Phase 1 |
| **9** | [Data Migration](Phase-09-Data-Migration.md) | ⚪ Not Started | 40 | Phase 1-8 |
| **10** | [Testing & Documentation](Phase-10-Testing-Documentation.md) | ⚪ Not Started | 40 | Phase 1-9 |

**Total Estimated Hours:** 520 hours (13 weeks full-time)

---

## Status Legend

- 🟢 **Complete** - All tasks completed, tests passing
- 🟡 **In Progress** - Currently being worked on
- ⚪ **Not Started** - Awaiting dependencies or not yet begun
- 🔴 **Blocked** - Cannot proceed due to blockers

---

## Quick Links

### Phase 1: Database Schema & Authentication
**✅ Complete** - Foundation established

- [x] 12 migrations created and run successfully
- [x] 11 models created/updated (all complete with relationships)
- [x] Test seeders created (2 households, 4 users including admin)
- [x] 8 model factories created
- [x] Architecture tests passing (7/7)

[View Full Phase 1 Tasks →](Phase-01-Database-Schema.md)

---

### Phase 2: User Profile Restructuring
Transform Settings into comprehensive Profile page

- [ ] 3 new controllers (UserProfile, FamilyMembers, PersonalAccounts)
- [ ] 2 services (UserProfileService, PersonalAccountsService)
- [ ] 11 Vue components (tabs, forms, charts)
- [ ] Personal Accounts auto-calculation (P&L, Cashflow, Balance Sheet)

[View Full Phase 2 Tasks →](Phase-02-User-Profile.md)

---

### Phase 3: Net Worth Dashboard
Create primary financial overview dashboard

- [ ] NetWorthService (aggregation with ownership %)
- [ ] NetWorthController with caching (30 min TTL)
- [ ] Main dashboard Net Worth card (position 1)
- [ ] 6-tab Net Worth detail view
- [ ] 3 charts (allocation donut, trend line, breakdown bar)

[View Full Phase 3 Tasks →](Phase-03-Net-Worth-Dashboard.md)

---

### Phase 4: Property Module
Complete property management with UK tax calculators

- [ ] Property CRUD (main/secondary/BTL)
- [ ] Mortgage CRUD and amortization
- [ ] SDLT calculator (Stamp Duty Land Tax)
- [ ] CGT calculator (Capital Gains Tax)
- [ ] Rental income tax calculator

[View Full Phase 4 Tasks →](Phase-04-Property-Module.md)

---

### Phase 5: Actions/Recommendations
Aggregate and prioritize recommendations

- [ ] RecommendationsAggregatorService
- [ ] Actions card on main dashboard
- [ ] Filter by priority/category/module
- [ ] "Get Advice" and "Do It Myself" workflows

[View Full Phase 5 Tasks →](Phase-05-Actions-Recommendations.md)

---

### Phase 6: Trusts Dashboard
Comprehensive trust tracking

- [ ] Trusts CRUD with asset aggregation
- [ ] IHT impact calculator (periodic charges)
- [ ] Trust assets from all modules
- [ ] Tax return due date tracking

[View Full Phase 6 Tasks →](Phase-06-Trusts-Dashboard.md)

---

### Phase 7: Dashboard Reordering
Reorder cards and update displays

- [ ] New card order: Net Worth, Retirement, Estate, Protection, Actions, Trusts, UK Taxes (admin)
- [ ] Update Retirement card (total pension value, years to retirement, projected income)
- [ ] Update Estate card (net worth from NetWorth service, IHT liability, probate readiness)

[View Full Phase 7 Tasks →](Phase-07-Dashboard-Reordering.md)

---

### Phase 8: Admin Roles & RBAC
Implement basic access control

- [ ] CheckAdminRole middleware
- [ ] Protect UK Taxes routes
- [ ] Conditional rendering in frontend
- [ ] Admin user seeder

[View Full Phase 8 Tasks →](Phase-08-Admin-RBAC.md)

---

### Phase 9: Data Migration
Migrate existing data to new structure

- [ ] Migrate estate assets to new asset tables
- [ ] Migrate savings_accounts to cash_accounts
- [ ] Data verification script
- [ ] Rollback plan

[View Full Phase 9 Tasks →](Phase-09-Data-Migration.md)

---

### Phase 10: Testing & Documentation
Comprehensive testing and documentation

- [ ] 60+ backend tests (Unit, Feature, Architecture)
- [ ] 20+ frontend tests
- [ ] Code coverage > 80%
- [ ] API documentation (Postman)
- [ ] User guide (8 chapters)

[View Full Phase 10 Tasks →](Phase-10-Testing-Documentation.md)

---

## How to Use These Documents

1. **Start with Phase 1** - Complete all tasks in order
2. **Check off tasks** as you complete them (edit the markdown file)
3. **Update status** in phase headers (Not Started → In Progress → Complete)
4. **Document blockers** in Notes section if you encounter issues
5. **Review Success Criteria** before marking phase complete
6. **Proceed to next phase** only when dependencies are met

---

## Progress Tracking

Update this section as phases are completed:

- **Week 1 (Day 1):** Phase 1 (Database Schema) - 🟢 Complete (6 hours, vs estimated 80 hours)
- **Week 3-4:** Phase 2 (User Profile) - ⚪ Not Started
- **Week 5-6:** Phase 3 (Net Worth Dashboard) - ⚪ Not Started
- **Week 7-8:** Phase 4 (Property Module) - ⚪ Not Started
- **Week 9:** Phase 5 (Actions/Recommendations) - ⚪ Not Started
- **Week 10:** Phase 6 (Trusts Dashboard) - ⚪ Not Started
- **Week 11:** Phase 7-8 (Dashboard & Admin) - ⚪ Not Started
- **Week 12:** Phase 9 (Data Migration) - ⚪ Not Started
- **Week 13:** Phase 10 (Testing & Documentation) - ⚪ Not Started

---

## Key Decisions & Changes

Document any significant decisions or deviations from the plan here:

- *2025-10-17:* Phase 1 started and completed in 6 hours
  - All 12 migrations created and run successfully (batch [2])
  - All 11 models (8 new + 3 updated) completed with relationships
  - Test seeders created (HouseholdSeeder, TestUsersSeeder, AdminUserSeeder)
  - 8 model factories created (PropertyFactory, CashAccountFactory, etc.)
  - Architecture tests created and passing (7/7 tests)
  - UserFactory update deferred to Phase 2 (will be done with User Profile work)
- *(Add new entries as work progresses)*

---

## Notes

- Each phase document contains detailed checklists - check off items as you complete them
- Some tasks can be parallelized (e.g., frontend and backend work)
- Testing tasks are integrated into each phase (don't defer testing to Phase 10)
- Cache invalidation is critical for Phase 3 - test thoroughly
- Keep Phase 9 (Data Migration) simple - focus on data integrity over feature additions

---

## Support

For questions or issues:
- Review the [Comprehensive FPS Restructuring Plan](../Comprehensive%20FPS%20Restructuring%20Plan.md)
- Check existing code for patterns and conventions
- Refer to [designStyleGuide.md](../../designStyleGuide.md) for UI/UX standards

---

**Last Updated:** 2025-10-17
