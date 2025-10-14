# FPS Postman Collections

This directory contains Postman collections for testing the FPS (Financial Planning System) API endpoints.

## Collections Available

### 1. Retirement Module Collection
**File**: `Retirement_Module.postman_collection.json`

Comprehensive collection covering all Retirement Planning module endpoints including:
- Retirement overview and analysis
- DC pension CRUD operations
- DB pension CRUD operations
- State Pension management
- Annual allowance checking

**Total Endpoints**: 12 organized in 5 folders

---

## Getting Started

### Step 1: Import Collection

1. Open Postman
2. Click **Import** button (top left)
3. Select **File** tab
4. Choose `Retirement_Module.postman_collection.json`
5. Click **Import**

### Step 2: Set Up Environment Variables

The collection uses two variables that need to be configured:

#### Option A: Collection Variables (Quick Start)

1. In Postman, select the **Retirement Module** collection
2. Click the **Variables** tab
3. Set the following variables:

| Variable | Initial Value | Current Value | Description |
|----------|--------------|---------------|-------------|
| `base_url` | `http://127.0.0.1:8000` | `http://127.0.0.1:8000` | API base URL |
| `auth_token` | *(leave empty)* | *(paste your token here)* | Sanctum bearer token |

4. Click **Save**

#### Option B: Postman Environment (Recommended for Multiple Collections)

1. Create a new environment: **Environments** → **Create Environment**
2. Name it `FPS Local Development`
3. Add variables:
   - `base_url`: `http://127.0.0.1:8000`
   - `auth_token`: *(leave empty for now)*
4. Click **Save**
5. Select the environment from the dropdown (top right)

### Step 3: Authenticate and Get Token

Before using the Retirement endpoints, you need a valid authentication token:

#### Method 1: Register a New User

```bash
curl -X POST http://127.0.0.1:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "Password123!",
    "password_confirmation": "Password123!",
    "date_of_birth": "1985-05-15",
    "gender": "male",
    "marital_status": "single"
  }'
```

#### Method 2: Login with Existing User

```bash
curl -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "Password123!"
  }'
```

**Response** (either method):
```json
{
  "success": true,
  "data": {
    "token": "1|abc123xyz...",
    "user": { ... }
  }
}
```

**Copy the token value** and paste it into the `auth_token` variable in Postman.

---

## Using the Collection

### Authorization

All endpoints require authentication. The collection is configured to automatically use the `{{auth_token}}` variable in the `Authorization` header as a Bearer token.

**Ensure you have set the `auth_token` variable** before making requests.

### Request Organization

The collection is organized into logical folders:

#### 📁 Retirement Overview
- **GET** Get All Retirement Data - Retrieve complete retirement portfolio
- **POST** Analyze Retirement Position - Run comprehensive analysis
- **GET** Get Recommendations - Personalized advice
- **POST** Run What-If Scenarios - Model contribution changes

#### 📁 DC Pensions (Defined Contribution)
- **POST** Create DC Pension - Add workplace pension, SIPP, or personal pension
- **PUT** Update DC Pension - Modify existing DC pension
- **DELETE** Delete DC Pension - Remove DC pension

#### 📁 DB Pensions (Defined Benefit)
- **POST** Create DB Pension - Add final salary/public sector pension
- **PUT** Update DB Pension - Modify existing DB pension
- **DELETE** Delete DB Pension - Remove DB pension

#### 📁 State Pension
- **POST** Update State Pension - Add/update NI years and forecast

#### 📁 Annual Allowance
- **GET** Check Annual Allowance - View allowance with tapering

---

## Example Workflow

### Scenario: User Setting Up Retirement Portfolio

**1. Get Current Retirement Data**
```
GET {{base_url}}/api/retirement
Authorization: Bearer {{auth_token}}
```

**2. Add a Workplace DC Pension**
```
POST {{base_url}}/api/retirement/pensions/dc
Content-Type: application/json
Authorization: Bearer {{auth_token}}

{
  "scheme_name": "Company Pension",
  "scheme_type": "workplace",
  "provider": "Scottish Widows",
  "current_fund_value": 85000,
  "employee_contribution_percent": 5,
  "employer_contribution_percent": 4,
  "monthly_contribution_amount": 450,
  "retirement_age": 67
}
```

**3. Add NHS DB Pension**
```
POST {{base_url}}/api/retirement/pensions/db
Content-Type: application/json
Authorization: Bearer {{auth_token}}

{
  "scheme_name": "NHS Pension Scheme",
  "scheme_type": "public_sector",
  "accrued_annual_pension": 12000,
  "pensionable_service_years": 15,
  "normal_retirement_age": 67,
  "inflation_protection": "cpi"
}
```

**4. Update State Pension Information**
```
POST {{base_url}}/api/retirement/state-pension
Content-Type: application/json
Authorization: Bearer {{auth_token}}

{
  "ni_years_completed": 30,
  "ni_years_required": 35,
  "state_pension_forecast_annual": 9858,
  "state_pension_age": 67
}
```

**5. Analyze Retirement Position**
```
POST {{base_url}}/api/retirement/analyze
Content-Type: application/json
Authorization: Bearer {{auth_token}}

{
  "growth_rate": 0.05,
  "inflation_rate": 0.025
}
```

**6. Check Annual Allowance**
```
GET {{base_url}}/api/retirement/annual-allowance/2024-25
Authorization: Bearer {{auth_token}}
```

**7. Run What-If Scenario**
```
POST {{base_url}}/api/retirement/scenarios
Content-Type: application/json
Authorization: Bearer {{auth_token}}

{
  "scenario_type": "contribution_increase",
  "additional_contribution": 100,
  "years_to_retirement": 22,
  "growth_rate": 0.05
}
```

---

## UK Pension System Reference

### DC Pension Types
- **Workplace**: Auto-enrolment pension with employer contributions
- **SIPP**: Self-Invested Personal Pension (DIY investing)
- **Personal**: Traditional personal pension plan

### DB Pension Schemes
- **Final Salary**: Based on final salary and years of service
- **Career Average**: Based on average salary throughout career
- **Public Sector**: NHS, Teachers', Civil Service, Local Government, Police

### Key UK Rules (2024/25 Tax Year)

| Rule | Amount | Notes |
|------|--------|-------|
| **Annual Allowance** | £60,000 | Total pension contributions per tax year |
| **Tapering Threshold** | £260,000 | Adjusted income above which AA is reduced |
| **Tapered Allowance Min** | £10,000 | Minimum allowance after tapering |
| **MPAA** | £10,000 | Money Purchase Annual Allowance (after flexible access) |
| **Carry Forward** | 3 years | Can use unused allowance from previous 3 years |
| **PCLS (Tax-Free Lump Sum)** | 25% | Of pension value, up to Lifetime Allowance |
| **State Pension (Full)** | £11,502.40 | Annual amount (requires 35 NI years) |
| **State Pension Age** | 66-67 | Depends on date of birth |

---

## Troubleshooting

### 401 Unauthorized Error

**Cause**: Missing or expired authentication token

**Solution**:
1. Verify `auth_token` variable is set
2. Token should start with number and pipe: `1|abc123...`
3. Get a fresh token using login endpoint if expired

### 403 Forbidden Error

**Cause**: Trying to access another user's pension data

**Solution**:
- You can only modify your own pensions
- Verify the pension ID belongs to your authenticated user

### 422 Validation Error

**Cause**: Invalid request data

**Solution**:
- Check required fields are present
- Verify enum values (e.g., `scheme_type` must be `workplace`, `sipp`, or `personal`)
- Ensure numeric fields are valid (e.g., percentages 0-100)

### 404 Not Found

**Cause**: Pension ID doesn't exist or wrong endpoint

**Solution**:
- Verify the ID exists by calling GET /api/retirement first
- Check the endpoint URL is correct

---

## Response Structures

### Success Response (200/201)
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

### Error Response (4xx/5xx)
```json
{
  "success": false,
  "message": "Error description",
  "errors": { ... }
}
```

---

## Testing Tips

### Use Collection Runner

1. Select the **Retirement Module** collection
2. Click **Run** button
3. Configure run:
   - Select all requests or specific folder
   - Set iterations: 1
   - Delay: 500ms between requests
4. Click **Run Retirement Module**

This will execute all requests in sequence and show pass/fail results.

### Scripts for Automation

You can add **Tests** scripts to requests for automated validation:

```javascript
// Example: Add to "Get All Retirement Data" Tests tab
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response has success field", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.eql(true);
});

pm.test("Response contains pension data", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('dc_pensions');
    pm.expect(jsonData.data).to.have.property('db_pensions');
});
```

---

## Additional Resources

- **FPS Documentation**: `/Users/CSJ/Desktop/fpsV2/FPS_PRD.md`
- **API Routes**: `/Users/CSJ/Desktop/fpsV2/routes/api.php`
- **Controller**: `/Users/CSJ/Desktop/fpsV2/app/Http/Controllers/Api/RetirementController.php`
- **Test Suite**: `/Users/CSJ/Desktop/fpsV2/tests/Feature/RetirementModuleTest.php`

---

## Support

For issues or questions:
1. Check test suite for example usage patterns
2. Review controller implementation for exact request/response formats
3. Consult FPS_PRD.md for business logic and UK tax rules
