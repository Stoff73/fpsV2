# Joint Ownership & Linked Accounts Documentation

**Version**: 1.0
**Last Updated**: November 13, 2025
**Status**: Production Ready

---

## Overview

TenGo implements a **reciprocal records pattern** for joint ownership across multiple asset and liability types. When an asset is marked as jointly owned, the system creates TWO database records - one for each spouse - with cross-references via `joint_owner_id`.

### Supported Joint Ownership Types

The following models support joint ownership:

1. **Properties** (`properties` table)
2. **Mortgages** (`mortgages` table)
3. **Savings Accounts** (`savings_accounts` table)
4. **Investment Accounts** (`investment_accounts` table)
5. **Life Insurance Policies** (`life_insurance_policies` table)
6. **Critical Illness Policies** (`critical_illness_policies` table)
7. **Income Protection Policies** (`income_protection_policies` table)

---

## Database Schema Pattern

All joint-ownership enabled models include these fields:

```php
$table->enum('ownership_type', ['individual', 'joint', 'trust'])->default('individual');
$table->unsignedBigInteger('joint_owner_id')->nullable();
$table->foreignId('trust_id')->nullable()->constrained();
```

### Key Fields

- **`ownership_type`**: Enum - `individual`, `joint`, or `trust`
- **`joint_owner_id`**: Foreign key to `users.id` - references the spouse's user ID
- **`user_id`**: Foreign key to `users.id` - references the owner's user ID

---

## Reciprocal Records Pattern

### How It Works

When a joint asset is created:

1. **Primary Record**: Created for the user who initiated the action
2. **Reciprocal Record**: Automatically created for the spouse (`joint_owner_id`)
3. **Cross-References**: Each record points to the other via `joint_owner_id`

**Example**:

```
User Chris (ID: 1) creates a joint property with spouse Angela (ID: 2)

Primary Record:
- id: 100
- user_id: 1 (Chris)
- joint_owner_id: 2 (Angela)
- address_line_1: "123 Main Street"
- current_value: 250000 (50% of £500k)

Reciprocal Record:
- id: 101
- user_id: 2 (Angela)
- joint_owner_id: 1 (Chris)
- address_line_1: "123 Main Street"
- current_value: 250000 (50% of £500k)
```

### Value Splitting

For joint ownership, values are split based on `ownership_percentage`:

- **Properties**: `current_value` is split (default 50/50)
- **Mortgages**: `outstanding_balance` is split (default 50/50)
- **Savings**: `current_balance` is split 50/50
- **Investments**: `current_value` is split 50/50

---

## CRITICAL: Reciprocal Query Pattern

### The Problem (CRITICAL BUG - FIXED November 13, 2025)

**WRONG - This causes the wrong asset to be deleted when multiple joint assets exist:**

```php
// ❌ BAD - Matches ONLY on user IDs
$reciprocalProperty = Property::where('user_id', $property->joint_owner_id)
    ->where('joint_owner_id', $user->id)
    ->first();  // Gets FIRST match, not CORRECT match
```

**Why This Fails**:
- If Chris and Angela have 3 joint properties, this query returns the FIRST one
- Deleting "123 Main Street" might delete "456 Oak Avenue" instead
- APP BREAKING BUG

### The Solution (CORRECT PATTERN)

**ALWAYS match on user IDs + unique identifying fields:**

```php
// ✅ CORRECT - Matches on user IDs + unique identifiers
$reciprocalProperty = Property::where('user_id', $property->joint_owner_id)
    ->where('joint_owner_id', $user->id)
    ->where('address_line_1', $property->address_line_1)
    ->where('postcode', $property->postcode)
    ->first();  // Gets the CORRECT matching property
```

---

## Implementation by Controller

### 1. PropertyController

**Unique Identifiers**: `address_line_1` + `postcode`

**Update Method**:
```php
public function update(UpdatePropertyRequest $request, int $id): JsonResponse
{
    $user = $request->user();
    $property = Property::where('id', $id)
        ->where('user_id', $user->id)
        ->firstOrFail();

    $property->update($request->validated());

    // If joint property, update reciprocal record
    if ($property->joint_owner_id) {
        $reciprocalProperty = Property::where('user_id', $property->joint_owner_id)
            ->where('joint_owner_id', $user->id)
            ->where('address_line_1', $property->address_line_1)
            ->where('postcode', $property->postcode)
            ->first();

        if ($reciprocalProperty) {
            $reciprocalProperty->update($request->validated());
        }
    }

    // ... rest of method
}
```

**Delete Method**:
```php
public function destroy(Request $request, int $id): JsonResponse
{
    $user = $request->user();
    $property = Property::where('id', $id)
        ->where('user_id', $user->id)
        ->firstOrFail();

    // If joint property, delete reciprocal record
    if ($property->joint_owner_id) {
        $reciprocalProperty = Property::where('user_id', $property->joint_owner_id)
            ->where('joint_owner_id', $user->id)
            ->where('address_line_1', $property->address_line_1)
            ->where('postcode', $property->postcode)
            ->first();

        if ($reciprocalProperty) {
            $reciprocalProperty->delete();
        }
    }

    $property->delete();

    return response()->json([
        'success' => true,
        'message' => 'Property deleted successfully',
    ]);
}
```

---

### 2. MortgageController

**Unique Identifiers**: `lender_name` + `outstanding_balance`

**Update Method**:
```php
public function update(UpdateMortgageRequest $request, int $id): JsonResponse
{
    $user = $request->user();
    $mortgage = Mortgage::where('id', $id)
        ->where('user_id', $user->id)
        ->firstOrFail();

    $validated = $request->validated();
    $mortgage->update($validated);

    // If joint mortgage, update reciprocal record
    if ($mortgage->joint_owner_id) {
        $reciprocalMortgage = Mortgage::where('user_id', $mortgage->joint_owner_id)
            ->where('joint_owner_id', $user->id)
            ->where('lender_name', $mortgage->lender_name)
            ->where('outstanding_balance', $mortgage->outstanding_balance)
            ->first();

        if ($reciprocalMortgage) {
            $reciprocalMortgage->update($validated);
        }
    }

    // ... rest of method
}
```

**Delete Method**:
```php
public function destroy(Request $request, int $id): JsonResponse
{
    $user = $request->user();
    $mortgage = Mortgage::where('id', $id)
        ->where('user_id', $user->id)
        ->firstOrFail();

    // If joint mortgage, delete reciprocal record
    if ($mortgage->joint_owner_id) {
        $reciprocalMortgage = Mortgage::where('user_id', $mortgage->joint_owner_id)
            ->where('joint_owner_id', $user->id)
            ->where('lender_name', $mortgage->lender_name)
            ->where('outstanding_balance', $mortgage->outstanding_balance)
            ->first();

        if ($reciprocalMortgage) {
            $reciprocalMortgage->delete();
        }
    }

    $mortgage->delete();

    return response()->json([
        'success' => true,
        'message' => 'Mortgage deleted successfully',
    ]);
}
```

---

### 3. SavingsController

**Unique Identifiers**: `institution` + `current_balance`

**Update Method** (`updateAccount`):
```php
public function updateAccount(UpdateSavingsAccountRequest $request, int $id): JsonResponse
{
    $user = $request->user();
    $account = SavingsAccount::where('id', $id)
        ->where('user_id', $user->id)
        ->firstOrFail();

    $account->update($request->validated());

    // If joint account, update reciprocal record
    if ($account->joint_owner_id) {
        $reciprocalAccount = SavingsAccount::where('user_id', $account->joint_owner_id)
            ->where('joint_owner_id', $user->id)
            ->where('institution', $account->institution)
            ->where('current_balance', $account->current_balance)
            ->first();

        if ($reciprocalAccount) {
            $reciprocalAccount->update($request->validated());
        }
    }

    // Invalidate cache for both users
    Cache::forget("savings_analysis_{$user->id}");
    if ($account->joint_owner_id) {
        Cache::forget("savings_analysis_{$account->joint_owner_id}");
    }

    // ... rest of method
}
```

**Delete Method** (`destroyAccount`):
```php
public function destroyAccount(Request $request, int $id): JsonResponse
{
    $user = $request->user();
    $account = SavingsAccount::where('id', $id)
        ->where('user_id', $user->id)
        ->firstOrFail();

    // If joint account, delete reciprocal record
    if ($account->joint_owner_id) {
        $reciprocalAccount = SavingsAccount::where('user_id', $account->joint_owner_id)
            ->where('joint_owner_id', $user->id)
            ->where('institution', $account->institution)
            ->where('current_balance', $account->current_balance)
            ->first();

        if ($reciprocalAccount) {
            $reciprocalAccount->delete();
            Cache::forget("savings_analysis_{$account->joint_owner_id}");
            $this->netWorthService->invalidateCache($account->joint_owner_id);
        }
    }

    $account->delete();

    // Invalidate cache
    Cache::forget("savings_analysis_{$user->id}");
    $this->netWorthService->invalidateCache($user->id);

    return response()->json([
        'success' => true,
        'message' => 'Savings account deleted successfully',
    ]);
}
```

---

### 4. InvestmentController

**Unique Identifiers**: `provider` + `current_value`

**Update Method** (`updateAccount`):
```php
public function updateAccount(Request $request, int $id): JsonResponse
{
    $user = $request->user();
    $account = InvestmentAccount::where('user_id', $user->id)->findOrFail($id);

    $validated = $request->validate([
        'account_name' => 'sometimes|string|max:255',
        'provider' => 'sometimes|string|max:255',
        'account_type' => 'sometimes|string',
        'current_value' => 'sometimes|numeric|min:0',
        'ownership_type' => 'sometimes|in:individual,joint,trust',
        'joint_owner_id' => 'nullable|exists:users,id',
    ]);

    $account->update($validated);

    // If joint account, update reciprocal record
    if ($account->joint_owner_id) {
        $reciprocalAccount = InvestmentAccount::where('user_id', $account->joint_owner_id)
            ->where('joint_owner_id', $user->id)
            ->where('provider', $account->provider)
            ->where('current_value', $account->current_value)
            ->first();

        if ($reciprocalAccount) {
            $reciprocalAccount->update($validated);
            $this->investmentAgent->clearCache($account->joint_owner_id);
        }
    }

    // Clear cache
    $this->investmentAgent->clearCache($user->id);

    return response()->json([
        'success' => true,
        'data' => $account->fresh(),
    ]);
}
```

**Delete Method** (`destroyAccount`):
```php
public function destroyAccount(Request $request, int $id): JsonResponse
{
    $user = $request->user();
    $account = InvestmentAccount::where('user_id', $user->id)->findOrFail($id);

    // If joint account, delete reciprocal record
    if ($account->joint_owner_id) {
        $reciprocalAccount = InvestmentAccount::where('user_id', $account->joint_owner_id)
            ->where('joint_owner_id', $user->id)
            ->where('provider', $account->provider)
            ->where('current_value', $account->current_value)
            ->first();

        if ($reciprocalAccount) {
            $reciprocalAccount->delete();
            $this->investmentAgent->clearCache($account->joint_owner_id);
        }
    }

    $account->delete();

    // Clear cache
    $this->investmentAgent->clearCache($user->id);

    return response()->json([
        'success' => true,
        'message' => 'Account deleted successfully',
    ]);
}
```

---

## Cache Invalidation Pattern

**CRITICAL**: Always invalidate cache for BOTH users when updating/deleting joint assets.

```php
// Invalidate cache for primary user
Cache::forget("savings_analysis_{$user->id}");

// Invalidate cache for joint owner
if ($account->joint_owner_id) {
    Cache::forget("savings_analysis_{$account->joint_owner_id}");
}
```

**Module-Specific Cache Invalidation**:

- **Savings**: `Cache::forget("savings_analysis_{$userId}")` + `NetWorthService::invalidateCache()`
- **Investment**: `InvestmentAgent::clearCache($userId)`
- **Retirement**: `RetirementAgent::clearCache($userId)`
- **Estate**: `Cache::forget("estate_analysis_{$userId}")`

---

## Model $fillable Requirements

All joint-ownership enabled models MUST include these fields in `$fillable`:

```php
protected $fillable = [
    // ... other fields
    'ownership_type',
    'joint_owner_id',
    'joint_owner_name',  // Optional, for display purposes
    // ... other fields
];
```

**CRITICAL**: Missing these fields from `$fillable` will cause reciprocal record creation to fail silently.

---

## Testing Procedures

### Test Scenario: Multiple Joint Assets

1. **Setup**: Create 2-3 joint assets of the same type between two spouses
   - Example: 3 joint properties between Chris and Angela

2. **Update Test**:
   - Log in as User A
   - Update Asset #2 (change value, address, etc.)
   - Log in as User B
   - Verify Asset #2 shows updated values
   - Verify Assets #1 and #3 are unchanged

3. **Delete Test**:
   - Log in as User A
   - Delete Asset #2
   - Log in as User B
   - Verify Asset #2 is deleted
   - Verify Assets #1 and #3 still exist

4. **Repeat for all asset types**:
   - Properties
   - Mortgages
   - Savings Accounts
   - Investment Accounts

---

## Common Issues & Troubleshooting

### Issue: Reciprocal Record Not Created

**Symptoms**: Joint asset only appears in one account

**Causes**:
1. `joint_owner_id` not in model's `$fillable` array
2. `createJoint{Asset}()` method not called in controller
3. Database transaction rolled back due to validation error

**Fix**: Check model's `$fillable` array and controller's store method

---

### Issue: Wrong Asset Deleted

**Symptoms**: Deleting Asset A causes Asset B to be deleted instead

**Cause**: Reciprocal query matches ONLY on user IDs without unique identifiers

**Fix**: Add unique field matching to query (see Correct Pattern above)

---

### Issue: Updates Not Syncing

**Symptoms**: Updating asset in one account doesn't update in spouse's account

**Cause**: Missing reciprocal update logic in controller's `update()` method

**Fix**: Add reciprocal update logic with unique field matching

---

### Issue: NaN Errors After Deletion

**Symptoms**: `£NaN` appears in Net Worth or module calculations

**Cause**: Reciprocal record still exists after deletion, causing aggregation errors

**Fix**: Ensure reciprocal deletion logic exists and matches correct record

---

## Migration Checklist

When adding joint ownership to a new model:

- [ ] Add `ownership_type`, `joint_owner_id` columns to migration
- [ ] Add fields to model's `$fillable` array
- [ ] Add `ownership_type` and `joint_owner_id` to Form Request validation
- [ ] Implement `createJoint{Asset}()` private method in controller
- [ ] Add reciprocal update logic to `update()` method with unique field matching
- [ ] Add reciprocal deletion logic to `destroy()` method with unique field matching
- [ ] Add cache invalidation for both users
- [ ] Update frontend form to include ownership type selector
- [ ] Test with multiple joint assets between spouses

---

## Quick Reference: Unique Identifiers by Model

| Model | Unique Identifiers |
|-------|-------------------|
| Property | `address_line_1` + `postcode` |
| Mortgage | `lender_name` + `outstanding_balance` |
| SavingsAccount | `institution` + `current_balance` |
| InvestmentAccount | `provider` + `current_value` |
| LifeInsurancePolicy | `provider` + `policy_number` |
| CriticalIllnessPolicy | `provider` + `policy_number` |
| IncomeProtectionPolicy | `provider` + `policy_number` |

---

## Frontend Integration

### Spouse Relationship Loading

**CRITICAL**: The spouse relationship must be loaded from the backend for joint owner dropdowns to work correctly.

**Issue**: When Angela logs in and creates a joint asset, the joint owner dropdown was showing "Angela Slater-Jones (Spouse - Linked Account)" instead of "Chris Slater-Jones".

**Root Cause**: The `login()` method in `AuthController.php` wasn't loading the spouse relationship.

**Fix Applied** (November 13, 2025):

```php
// app/Http/Controllers/Api/AuthController.php - login() method

$user = User::where('email', $request->email)->firstOrFail();

// Load spouse relationship if spouse_id exists
if ($user->spouse_id) {
    $user->load('spouse');
}

$token = $user->createToken('auth_token')->plainTextToken;
```

**Frontend Usage**:

```javascript
// resources/js/store/modules/userProfile.js

spouse: (state, getters, rootState, rootGetters) => {
  const currentUser = rootGetters['auth/user'];

  if (!currentUser || !currentUser.spouse_id) {
    return null;
  }

  // Spouse relationship is now loaded from backend
  if (currentUser.spouse) {
    return {
      id: currentUser.spouse_id,
      name: currentUser.spouse.name || 'Spouse',
      email: currentUser.spouse.email,
      relationship: 'spouse',
    };
  }

  return null;
},
```

**Result**: Joint owner dropdowns now correctly show the spouse's name, not the current user's name.

---

## Version History

### v1.0 - November 13, 2025
- Initial documentation
- Fixed critical "wrong asset deleted" bug across all 4 controllers
- Established correct reciprocal query pattern with unique field matching
- Fixed spouse relationship loading in AuthController login() method
- Fixed joint owner dropdown showing wrong name
- Added comprehensive testing procedures

---

**Built with [Claude Code](https://claude.com/claude-code)**
