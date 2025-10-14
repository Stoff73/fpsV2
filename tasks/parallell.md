# FPS Task Parallelization Guide

This document outlines which tasks can be done in parallel and which tasks will conflict if worked on simultaneously.

---

## ✅ Completed Tasks

- **Task 01**: Foundation Setup (100% Complete)
- **Task 02**: Protection Backend (100% Complete)

---

## 📋 Tasks That Can Be Done Together (In Parallel)

### Group 1: Multiple Module Backends

These backends are independent and don't share code/resources:

- **04-savings-backend** + **08-retirement-backend** + **10-estate-backend**
- **04-savings-backend** + **08-retirement-backend**
- **04-savings-backend** + **10-estate-backend**
- **08-retirement-backend** + **10-estate-backend**

### Group 2: Frontend + Different Module Backend

- **03-protection-frontend** + **04-savings-backend**
- **03-protection-frontend** + **08-retirement-backend**
- **03-protection-frontend** + **10-estate-backend**

### Group 3: Multiple Module Frontends (Once Backends Complete)

- **03-protection-frontend** + **05-savings-frontend** (if 04-savings-backend done)
- **03-protection-frontend** + **09-retirement-frontend** (if 08-retirement-backend done)
- **03-protection-frontend** + **11-estate-frontend** (if 10-estate-backend done)

---

## ⚠️ Tasks That CONFLICT (Cannot Be Done Together)

### ISA Tracking Cross-Module Conflict

**Cannot do together:**
- **04-savings-backend** + **06-investment-backend**

**Why?** Both modules work with ISA allowance tracking (cross-module feature). The ISA tracker needs to query data from both Savings (Cash ISAs) and Investment (S&S ISAs) modules. Doing these simultaneously will cause integration issues.

**Recommendation:** Complete 04-savings-backend FIRST, then do 06-investment-backend (which will update the ISA tracker to support Investment module).

### Dashboard Integration Conflicts

**Cannot do together:**
- **12-dashboard-integration** + any module frontend (03, 05, 07, 09, 11)

**Why?** Dashboard integration aggregates data from ALL module frontends. The overview cards need to be imported and integrated. Working on dashboard and module frontends simultaneously will cause component import/integration issues.

**Recommendation:** Complete ALL module frontends (03, 05, 07, 09, 11) BEFORE starting 12-dashboard-integration.

### Coordinating Agent Conflicts

**Cannot do together:**
- **13-coordinating-agent** + any module backend (04, 06, 08, 10)

**Why?** Coordinating Agent calls all module agents for holistic analysis. If module backends are incomplete, the Coordinating Agent can't properly integrate them.

**Recommendation:** Complete ALL module backends (02 ✅, 04, 06, 08, 10) BEFORE starting 13-coordinating-agent.

---

## 🚫 Tasks That MUST Be Done Alone

### Task 14: Final Testing & Deployment

**14-final-testing.md MUST be done after ALL other tasks are complete.**

**Why?**
- Requires comprehensive end-to-end testing across all modules
- Tests cross-module integrations (ISA tracking, net worth aggregation, holistic planning)
- Includes security hardening and performance optimization for the entire application
- Validates the complete user journey from registration through all 5 modules

---

## 📊 Recommended Implementation Strategy

### Phase 1: Protection Frontend (Solo)
- **03-protection-frontend** (5-7 days)

### Phase 2: Parallel Backend Development

Do these **together**:
- **04-savings-backend** (4-6 days)
- **08-retirement-backend** (6-8 days)
- **10-estate-backend** (6-8 days)

### Phase 3: Investment Backend (Solo)
- **06-investment-backend** (6-8 days) - Must be after 04-savings-backend for ISA integration

### Phase 4: Parallel Frontend Development

Do these **together** (once respective backends complete):
- **05-savings-frontend** (4-6 days)
- **07-investment-frontend** (5-7 days)
- **09-retirement-frontend** (5-7 days)
- **11-estate-frontend** (5-7 days)

### Phase 5: Integration (Sequential)

Do these **one at a time**:
1. **12-dashboard-integration** (4-6 days) - After ALL frontends complete
2. **13-coordinating-agent** (5-7 days) - After ALL backends complete

### Phase 6: Final Testing (Solo)
- **14-final-testing** (8-10 days) - After EVERYTHING else

---

## 🎯 Quick Reference Matrix

| Task | Can Pair With | Conflicts With | Must Do Alone? | Dependencies |
|------|---------------|----------------|----------------|--------------|
| 03-protection-frontend | 04, 08, 10 backends | 12 | No | 02 ✅ |
| 04-savings-backend | 08, 10 backends, 03 frontend | **06** | No | 01 ✅ |
| 05-savings-frontend | 03, 09, 11 frontends | 12 | No | 04 |
| 06-investment-backend | 03 frontend (if 04 done) | **04** | After 04 | 01 ✅, 04 |
| 07-investment-frontend | 03, 05, 09, 11 frontends | 12 | No | 06 |
| 08-retirement-backend | 04, 10 backends, 03 frontend | 13 | No | 01 ✅ |
| 09-retirement-frontend | 03, 05, 07, 11 frontends | 12 | No | 08 |
| 10-estate-backend | 04, 08 backends, 03 frontend | 13 | No | 01 ✅ |
| 11-estate-frontend | 03, 05, 07, 09 frontends | 12 | No | 10 |
| 12-dashboard-integration | None | 03, 05, 07, 09, 11 | No | All frontends |
| 13-coordinating-agent | None | 04, 06, 08, 10 | No | All backends |
| 14-final-testing | None | Everything | **YES** | ALL tasks |

---

## 💡 Key Principles

### 1. Backend Before Frontend
Always complete a module's backend before starting its frontend. This ensures:
- Functional APIs ready for integration
- Clear API contracts established
- Ability to test with Postman/Pest before UI development

### 2. ISA Cross-Module Dependency
The ISA tracking system spans Savings and Investment modules:
- Savings tracks Cash ISA contributions
- Investment tracks Stocks & Shares ISA contributions
- Combined total must not exceed £20,000 (2024/25)
- **Complete Savings backend before Investment backend**

### 3. Dashboard Integration Last
Dashboard integration imports and displays all module overview cards:
- Requires all module frontends to be complete
- Aggregates cross-module data (net worth, ISA usage, financial health score)
- **Wait until all frontends are done**

### 4. Coordinating Agent Last
Coordinating Agent orchestrates all module agents:
- Calls ProtectionAgent, SavingsAgent, InvestmentAgent, RetirementAgent, EstateAgent
- Resolves cross-module conflicts
- Generates holistic recommendations
- **Wait until all backends are done**

### 5. Final Testing Must Be Last
Final testing validates the entire system:
- Cross-module integration tests
- End-to-end user journeys
- Performance optimization
- Security hardening
- **Cannot start until 100% of other tasks complete**

---

## 📈 Timeline Optimization

### Single Developer Timeline
- **Sequential approach**: ~85-110 days (17-22 weeks)
- **Optimized with smart sequencing**: ~75-95 days (15-19 weeks)

### Multi-Developer Timeline (3 developers)
By leveraging parallelization:
- **Phase 2**: 3 backends in parallel (6-8 days instead of 18-24)
- **Phase 4**: 4 frontends in parallel (5-7 days instead of 20-28)
- **Total**: ~50-65 days (10-13 weeks)

### Critical Path (Cannot Be Shortened)
1. Foundation Setup (5 days)
2. Protection Backend (6-8 days)
3. Protection Frontend (5-7 days)
4. Investment Backend (6-8 days) - must wait for Savings backend
5. Dashboard Integration (4-6 days) - must wait for all frontends
6. Coordinating Agent (5-7 days) - must wait for all backends
7. Final Testing (8-10 days) - must wait for everything

**Minimum possible timeline**: ~39-51 days with perfect parallelization

---

## ⚡ Quick Start Recommendations

### If You Have 1 Developer
Follow the phases sequentially as outlined above. Focus on completing entire phases before moving to the next.

### If You Have 2 Developers
- **Developer 1**: Work on Protection frontend (03)
- **Developer 2**: Work on Savings backend (04)
- Then both move to Phase 2 parallel work

### If You Have 3+ Developers
- **Developer 1**: Protection frontend (03)
- **Developer 2**: Savings backend (04)
- **Developer 3**: Retirement backend (08)
- **Developer 4** (if available): Estate backend (10)

---

**Last Updated**: 2025-10-14
**Version**: 1.0
