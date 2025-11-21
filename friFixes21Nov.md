# Security Fixes - 21 November 2025

## Summary
Three security issues identified and resolved.

---

## 1. Token Logging Removed (High Severity)

**File:** `app/Http/Controllers/Api/AuthController.php`

**Issue:** Partial authentication token was being logged on user registration, creating a security risk.

**Fix:** Removed `token_preview` and `user_email` from log output. Now only logs `user_id`.

```php
// Before
\Log::info('SECURITY AUDIT: User registered', [
    'user_id' => $user->id,
    'user_email' => $user->email,
    'token_preview' => substr($token, 0, 20).'...',
]);

// After
\Log::info('User registered', [
    'user_id' => $user->id,
]);
```

---

## 2. Password Regex Updated (Medium Severity)

**File:** `app/Http/Controllers/Api/AuthController.php`

**Issue:** Password validation only accepted a whitelist of special characters (`@$!%*?&`), rejecting valid secure characters like `#`, `^`, `(`, `)`.

**Fix:** Changed regex to accept any non-alphanumeric character.

```php
// Before
'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/'

// After
'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).+$/'
```

---

## 3. CORS Hardcoded Origins Removed (Configuration Risk)

**File:** `config/cors.php`

**Issue:** Hardcoded localhost URLs mixed with production URLs could allow local dev environments to make requests to production API.

**Fix:** CORS origins now read from environment variables only.

```php
// Before
'allowed_origins' => [
    'http://localhost:8000',
    'http://127.0.0.1:8000',
    'http://localhost:5173',
    'https://csjones.co',
    env('FRONTEND_URL', 'http://localhost:5173'),
    env('APP_URL'),
],

// After
'allowed_origins' => array_filter(array_unique(array_merge(
    explode(',', env('ALLOWED_ORIGINS', '')),
    [
        env('FRONTEND_URL'),
        env('APP_URL'),
    ]
))),
```

**Environment Variables Added to `.env`:**
```
ALLOWED_ORIGINS=http://localhost:8000,http://127.0.0.1:8000,http://localhost:5173
FRONTEND_URL=http://localhost:8000
```

**Production `.env` should have:**
```
ALLOWED_ORIGINS=https://csjones.co
FRONTEND_URL=https://csjones.co
```

---

## 4. Trust Route Controller Mismatch (Domain Leakage)

**Files:** `routes/api.php`, `app/Http/Controllers/Api/Estate/TrustController.php`

**Issue:** Route `/api/trusts/upcoming-tax-returns` was pointing to `WillController` instead of `TrustController`.

**Fix:** Moved method to `TrustController` and updated route.

```php
// Before (routes/api.php)
Route::get('/trusts/upcoming-tax-returns', [WillController::class, 'getUpcomingTaxReturns']);

// After
Route::get('/trusts/upcoming-tax-returns', [TrustController::class, 'getUpcomingTaxReturns']);
```

---

## 5. N+1 Query Risk in User Model

**File:** `app/Models/User.php`

**Issue:** `hasAcceptedSpousePermission()` used `User::find($this->spouse_id)` which triggers separate DB queries when iterating over users.

**Fix:** Use existing relationship with eager-loading check.

```php
// Before
$spouse = User::find($this->spouse_id);

// After
$spouse = $this->relationLoaded('spouse') ? $this->spouse : $this->spouse()->first();
```

---

## 6. Float Casts for Financial Data (Known Limitation)

**File:** `app/Models/User.php`

**Issue:** Financial fields cast to `float` can cause precision errors in calculations.

**Status:** Documented as known limitation. Changing to integer (pence) storage would require:
- Database migrations for all financial columns
- Service layer updates
- Frontend conversion logic

**Recommendation:** For future major version, consider migrating to integer storage (pence) or using a Decimal library.

---

## Files Changed

| File | Change |
|------|--------|
| `app/Http/Controllers/Api/AuthController.php` | Removed token logging, updated password regex |
| `config/cors.php` | Removed hardcoded origins, use env vars |
| `.env` | Added ALLOWED_ORIGINS and FRONTEND_URL |
| `routes/api.php` | Fixed trust route to use TrustController |
| `app/Http/Controllers/Api/Estate/TrustController.php` | Added getUpcomingTaxReturns method |
| `app/Models/User.php` | Fixed N+1 query in hasAcceptedSpousePermission |

---

## 7. User Model Switched to Guarded

**File:** `app/Models/User.php`

**Issue:** Massive `$fillable` array (70+ fields) was difficult to maintain and prone to silent failures when new columns added.

**Fix:** Switched to `$guarded` approach - protects sensitive fields, allows all others.

```php
// Before
protected $fillable = [
    'name',
    'email',
    // ... 70+ more fields
];

// After
protected $guarded = [
    'id',
    'email_verified_at',
    'remember_token',
    'created_at',
    'updated_at',
];
```

---

## 8. Route Style Consistency (Already Compliant)

**File:** `routes/api.php`

**Status:** No fully qualified controller references found - already using imported classes.

---

## 9. Middleware Consolidation (Deferred)

**File:** `routes/api.php`

**Observation:** 20 separate `auth:sanctum` middleware applications.

**Status:** Deferred - consolidating into single group is a style improvement with risk of breaking routes. Current structure is functional and explicit.

---

## Files Changed

| File | Change |
|------|--------|
| `app/Http/Controllers/Api/AuthController.php` | Removed token logging, updated password regex |
| `config/cors.php` | Removed hardcoded origins, use env vars |
| `.env` | Added ALLOWED_ORIGINS and FRONTEND_URL |
| `routes/api.php` | Fixed trust route to use TrustController |
| `app/Http/Controllers/Api/Estate/TrustController.php` | Added getUpcomingTaxReturns method |
| `app/Models/User.php` | Fixed N+1 query, switched to guarded |
| `resources/js/components/UserProfile/ExpenditureOverview.vue` | Fixed isMarried prop type coercion |

---

## 10. Vue Prop Type Warning Fix

**File:** `resources/js/components/UserProfile/ExpenditureOverview.vue`

**Issue:** `isMarried` prop was passing `spouse_id` (Number) instead of Boolean, causing Vue warnings.

**Fix:** Added `!!` to coerce to boolean.

```javascript
// Before
return user.value?.marital_status === 'married' && user.value?.spouse_id;

// After
return user.value?.marital_status === 'married' && !!user.value?.spouse_id;
```

---

Generated: 21 November 2025
