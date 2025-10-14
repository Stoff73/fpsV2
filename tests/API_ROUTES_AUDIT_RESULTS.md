# API Routes Audit Results

**Date**: October 14, 2025
**Audited By**: Claude Code
**Status**: ✅ All Routes Fixed

---

## Executive Summary

Performed comprehensive audit of all frontend API service files against backend routes. Found and fixed multiple endpoint mismatches that were causing 405 Method Not Allowed errors.

**Result**: All 5 modules now have correct API endpoints.

---

## Issues Found and Fixed

### Protection Module (/resources/js/services/protectionService.js)

**Status**: ✅ FIXED

#### Issues Found:
1. **Main endpoint**: `/protection/dashboard` → Should be `/protection`
2. **All policy endpoints missing `/policies/` prefix**:
   - `/protection/life-insurance` → Should be `/protection/policies/life`
   - `/protection/critical-illness` → Should be `/protection/policies/critical-illness`
   - `/protection/income-protection` → Should be `/protection/policies/income-protection`
   - `/protection/disability` → Should be `/protection/policies/disability`
   - `/protection/sickness-illness` → Should be `/protection/policies/sickness-illness`

#### Total Fixes: 16 endpoint URLs corrected

---

### Savings Module (/resources/js/services/savingsService.js)

**Status**: ✅ FIXED

#### Issues Found:
1. **Main endpoint**: `/savings/dashboard` → Should be `/savings`
2. **Goal progress method**: Using `.post()` → Should use `.patch()`

#### Total Fixes: 2 corrections

---

### Investment Module (/resources/js/services/investmentService.js)

**Status**: ✅ VERIFIED CORRECT
- All 16 endpoints match backend routes perfectly
- No changes needed

---

### Retirement Module (/resources/js/services/retirementService.js)

**Status**: ✅ VERIFIED CORRECT
- All 12 endpoints match backend routes perfectly
- No changes needed
- Note: Uses direct axios with API_BASE constant instead of api wrapper

---

### Estate Module (/resources/js/services/estateService.js)

**Status**: ✅ VERIFIED CORRECT
- All 17 endpoints match backend routes perfectly
- No changes needed

---

## Summary by Module

| Module | Total Endpoints | Issues Found | Status |
|--------|----------------|--------------|--------|
| Protection | 18 | 16 | ✅ Fixed |
| Savings | 15 | 2 | ✅ Fixed |
| Investment | 16 | 0 | ✅ Correct |
| Retirement | 12 | 0 | ✅ Correct |
| Estate | 17 | 0 | ✅ Correct |
| **TOTAL** | **78** | **18** | **✅ All Fixed** |

---

## Backend Route Reference

### Protection Routes (api.php lines 44-88)
```
GET    /api/protection
POST   /api/protection/analyze
GET    /api/protection/recommendations
POST   /api/protection/scenarios
POST   /api/protection/profile
POST   /api/protection/policies/life
PUT    /api/protection/policies/life/{id}
DELETE /api/protection/policies/life/{id}
POST   /api/protection/policies/critical-illness
PUT    /api/protection/policies/critical-illness/{id}
DELETE /api/protection/policies/critical-illness/{id}
POST   /api/protection/policies/income-protection
PUT    /api/protection/policies/income-protection/{id}
DELETE /api/protection/policies/income-protection/{id}
POST   /api/protection/policies/disability
PUT    /api/protection/policies/disability/{id}
DELETE /api/protection/policies/disability/{id}
POST   /api/protection/policies/sickness-illness
PUT    /api/protection/policies/sickness-illness/{id}
DELETE /api/protection/policies/sickness-illness/{id}
```

### Savings Routes (api.php lines 91-116)
```
GET    /api/savings
POST   /api/savings/analyze
GET    /api/savings/recommendations
POST   /api/savings/scenarios
GET    /api/savings/isa-allowance/{taxYear}
POST   /api/savings/accounts
PUT    /api/savings/accounts/{id}
DELETE /api/savings/accounts/{id}
GET    /api/savings/goals
POST   /api/savings/goals
PUT    /api/savings/goals/{id}
DELETE /api/savings/goals/{id}
PATCH  /api/savings/goals/{id}/progress
```

### Investment Routes (api.php lines 119-153)
```
GET    /api/investment
POST   /api/investment/analyze
GET    /api/investment/recommendations
POST   /api/investment/scenarios
POST   /api/investment/monte-carlo
GET    /api/investment/monte-carlo/{jobId}
POST   /api/investment/accounts
PUT    /api/investment/accounts/{id}
DELETE /api/investment/accounts/{id}
POST   /api/investment/holdings
PUT    /api/investment/holdings/{id}
DELETE /api/investment/holdings/{id}
POST   /api/investment/goals
PUT    /api/investment/goals/{id}
DELETE /api/investment/goals/{id}
POST   /api/investment/risk-profile
```

### Retirement Routes (api.php lines 194-220)
```
GET    /api/retirement
POST   /api/retirement/analyze
GET    /api/retirement/recommendations
POST   /api/retirement/scenarios
GET    /api/retirement/annual-allowance/{taxYear}
POST   /api/retirement/pensions/dc
PUT    /api/retirement/pensions/dc/{id}
DELETE /api/retirement/pensions/dc/{id}
POST   /api/retirement/pensions/db
PUT    /api/retirement/pensions/db/{id}
DELETE /api/retirement/pensions/db/{id}
POST   /api/retirement/state-pension
```

### Estate Routes (api.php lines 156-191)
```
GET    /api/estate
POST   /api/estate/analyze
GET    /api/estate/recommendations
POST   /api/estate/scenarios
POST   /api/estate/calculate-iht
GET    /api/estate/net-worth
GET    /api/estate/cash-flow/{taxYear}
POST   /api/estate/profile
POST   /api/estate/assets
PUT    /api/estate/assets/{id}
DELETE /api/estate/assets/{id}
POST   /api/estate/liabilities
PUT    /api/estate/liabilities/{id}
DELETE /api/estate/liabilities/{id}
POST   /api/estate/gifts
PUT    /api/estate/gifts/{id}
DELETE /api/estate/gifts/{id}
```

---

## Testing Recommendations

### 1. Manual Testing Priority

**High Priority** (Recently Fixed):
1. Protection Module - Add/Edit/Delete all policy types
2. Savings Module - Add savings goal and update progress

**Medium Priority** (Verification):
3. Investment Module - Create account, add holding
4. Retirement Module - Add DC pension, add DB pension
5. Estate Module - Add asset, add liability, add gift

### 2. Automated Testing

Create API integration tests for all CRUD operations:

```bash
# Backend API tests
./vendor/bin/pest tests/Feature/Api/ProtectionApiTest.php
./vendor/bin/pest tests/Feature/Api/SavingsApiTest.php
./vendor/bin/pest tests/Feature/Api/InvestmentApiTest.php
./vendor/bin/pest tests/Feature/Api/RetirementApiTest.php
./vendor/bin/pest tests/Feature/Api/EstateApiTest.php
```

### 3. Frontend Testing

Test form submissions for:
- ✅ Protection: Life insurance policy creation (user reported this working now)
- ⏳ Savings: Goal creation and progress updates
- ⏳ Investment: Account and holding creation
- ⏳ Retirement: Pension creation (DC/DB)
- ⏳ Estate: Asset/Liability/Gift creation

---

## Notes

### Authentication
All routes require `auth:sanctum` middleware. Ensure:
- User is logged in
- `axios.defaults.withCredentials = true` (already configured in bootstrap.js)
- CSRF token is sent with requests (already configured)

### Response Structure
All endpoints follow consistent format:
```json
{
  "success": true,
  "data": { ... },
  "message": "Success message"
}
```

Error responses:
```json
{
  "success": false,
  "message": "Error message",
  "errors": { ... }
}
```

### Service Architecture Notes

**Uses api wrapper** (import api from './api'):
- protectionService.js ✓
- savingsService.js ✓
- investmentService.js ✓
- estateService.js ✓

**Uses direct axios** (import axios from 'axios'):
- retirementService.js (has API_BASE constant)

Consider standardizing to use api wrapper everywhere for consistency.

---

## Verification Steps

1. ✅ Refresh browser (Cmd+Shift+R / Ctrl+Shift+R)
2. ✅ Check all modules load on dashboard
3. ⏳ Test adding a policy in Protection module
4. ⏳ Test adding a goal in Savings module
5. ⏳ Test adding an account in Investment module
6. ⏳ Test adding a pension in Retirement module
7. ⏳ Test adding an asset in Estate module

---

**Created By**: Claude Code
**Date**: October 14, 2025
**Status**: Ready for User Testing
