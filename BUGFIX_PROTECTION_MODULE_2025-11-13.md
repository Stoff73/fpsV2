# Bug Fix Report: Protection Module - Add/Edit Policy Issues

**Date**: November 13, 2025
**Status**: ✅ Fixed - Ready for Deployment
**Severity**: Critical (blocked policy management in dashboard)
**Affected Version**: v0.2.7 (production)

---

## Issue Summary

Three related issues in the Protection module prevented users from adding or editing policies from the dashboard:

1. **Add Policy button does nothing** - No modal appears, no console errors
2. **Edit Policy modal shows blank** - Modal appears but form fields are empty
3. **Save throws error** - "Unknown policy type: undefined" when saving

---

## User Report

**Location**: Protection Dashboard → Policy Overview tab
**Actions Attempted**:
1. Clicked "Add Policy" button in overview → Nothing happened, no console errors
2. Clicked "Edit" on existing policy → Modal appeared but form was blank
3. Manually filled in blank form → Save button threw error

**Console Error**:
```javascript
PolicyDetail-B6-JSWWg.js:1 Failed to update policy: Error: Unknown policy type: undefined
    at eval (protection.js?t=1731513429768:262:15)
```

---

## Root Cause Analysis (Systematic Debugging)

### Phase 1: Investigation

Used systematic debugging process across three related issues:

#### Issue #1: Add Policy Button Does Nothing

**Component Hierarchy**:
```
ProtectionDashboard.vue (parent)
  ↓ @add-policy event
CurrentSituation.vue (child component)
  ↓ emits event
ProtectionDashboard.handleAddPolicy() (EMPTY STUB)
```

**Root Cause**:
- `ProtectionDashboard.vue` lines 189-191: `handleAddPolicy()` is empty stub with comment "CurrentSituation component handles this internally"
- But `CurrentSituation.vue` has NO modal - it only emits events and expects parent to handle them
- No modal exists in ProtectionDashboard to show
- Result: Button emits event, parent catches it but does nothing

#### Issue #2: Edit Policy Modal Shows Blank

**File**: `PolicyDetail.vue` lines 291-296

**Problem**: Missing `:is-editing` prop
```vue
<!-- BEFORE (broken) -->
<PolicyFormModal
  v-if="showEditModal"
  :policy="policy"              ✅ Provided
  @save="handlePolicyUpdate"
  @close="showEditModal = false"
/>
<!-- ❌ MISSING: :is-editing="true" -->
```

**Why This Matters**:
- `PolicyFormModal.vue` mounted() hook (lines 611-616):
```javascript
async mounted() {
  await this.loadFamilyMembers();
  if (this.isEditing && this.policy) {  // ❌ isEditing defaults to false
    this.loadPolicyData();
  }
}
```
- Without `isEditing="true"`, the modal never calls `loadPolicyData()`
- Result: Policy data is passed but never loaded into form fields

#### Issue #3: Save Throws "Unknown policy type: undefined"

**File**: `PolicyDetail.vue` lines 534-542

**Problem**: Calling store with wrong parameter names
```javascript
// BEFORE (broken)
await this.$store.dispatch('protection/updatePolicy', {
  endpoint,        // ❌ Store doesn't expect this
  policyId: this.policyId,  // ❌ Should be 'id'
  data: updatedPolicy,      // ❌ Should be 'policyData'
});
```

**Store Expects** (`protection.js` lines 255-265):
```javascript
async updatePolicy({ dispatch }, { policyType, id, policyData }) {
  const actionMap = {
    life: 'updateLifePolicy',
    criticalIllness: 'updateCriticalIllnessPolicy',
    // ...
  };

  const action = actionMap[policyType];
  if (!action) {
    throw new Error(`Unknown policy type: ${policyType}`);  // ⚠️ This error
  }
  // ...
}
```

**Result**: `policyType` is undefined, causing error

### Phase 2: Pattern Analysis

**Working Pattern Found**: `ProtectionPoliciesStep.vue` (onboarding - lines 123-129, 260-263)

This component works correctly for add/edit during onboarding:

```vue
<!-- Template -->
<PolicyFormModal
  v-if="showForm"
  :policy="editingPolicy"
  :is-editing="!!editingPolicy"  ← KEY: true for edit, false for add
  @close="closeForm"
  @save="handlePolicySaved"
/>

<!-- Script -->
<script>
const showForm = ref(false);
const editingPolicy = ref(null);

function addPolicy() {
  showForm.value = true;  // editingPolicy stays null
}

function editPolicy(policy) {
  editingPolicy.value = policy;
  showForm.value = true;
}

function closeForm() {
  showForm.value = false;
  editingPolicy.value = null;
}
</script>
```

**Pattern Explanation**:
- Single `showForm` flag controls modal visibility
- Single `editingPolicy` variable holds policy for edit (null for add)
- `:is-editing="!!editingPolicy"` converts to boolean (false for add, true for edit)
- Modal component handles both modes with same form

---

## Fixes Applied

### Fix #1: ProtectionDashboard.vue - Add Modal and Implement Handlers

**Changes**:

1. **Added modal to template** (after line 119):
```vue
<!-- Policy Form Modal -->
<PolicyFormModal
  v-if="showForm"
  :policy="editingPolicy"
  :is-editing="!!editingPolicy"
  @close="closeForm"
  @save="handlePolicySaved"
/>
```

2. **Added PolicyFormModal import** (line 140):
```javascript
import PolicyFormModal from '@/components/Protection/PolicyFormModal.vue';
```

3. **Added to components** (line 152):
```javascript
components: {
  AppLayout,
  CurrentSituation,
  GapAnalysis,
  Recommendations,
  ProfileCompletenessAlert,
  PolicyFormModal,  // ← Added
},
```

4. **Added data properties** (lines 165-166):
```javascript
data() {
  return {
    activeTab: 'current',
    tabs: [ /* ... */ ],
    profileCompleteness: null,
    loadingCompleteness: false,
    showForm: false,       // ← Added
    editingPolicy: null,   // ← Added
  };
},
```

5. **Implemented handler methods** (lines 202-229):
```javascript
handleAddPolicy() {
  this.editingPolicy = null;
  this.showForm = true;
},

handleEditPolicy(policy) {
  this.editingPolicy = policy;
  this.showForm = true;
},

handleDeletePolicy(policy) {
  // CurrentSituation component handles this internally (navigates to PolicyDetail)
},

closeForm() {
  this.showForm = false;
  this.editingPolicy = null;
},

async handlePolicySaved(policyData) {
  try {
    // Reload protection data to show the new/updated policy
    await this.fetchProtectionData();
    this.closeForm();
  } catch (error) {
    console.error('Failed to reload protection data:', error);
  }
},
```

### Fix #2: PolicyDetail.vue - Add Missing :is-editing Prop

**File**: `PolicyDetail.vue` line 294

**Change**:
```vue
<!-- BEFORE -->
<PolicyFormModal
  v-if="showEditModal"
  :policy="policy"
  @save="handlePolicyUpdate"
  @close="showEditModal = false"
/>

<!-- AFTER -->
<PolicyFormModal
  v-if="showEditModal"
  :policy="policy"
  :is-editing="true"  ← Added
  @save="handlePolicyUpdate"
  @close="showEditModal = false"
/>
```

### Fix #3: PolicyDetail.vue - Fix Store Call Parameters

**File**: `PolicyDetail.vue` lines 534-549

**Change**:
```javascript
// BEFORE
async handlePolicyUpdate(updatedPolicy) {
  try {
    const endpoint = this.getApiEndpoint(this.policyType);
    await this.$store.dispatch('protection/updatePolicy', {
      endpoint,
      policyId: this.policyId,
      data: updatedPolicy,
    });

    this.showEditModal = false;
    await this.loadPolicy();
  } catch (error) {
    console.error('Failed to update policy:', error);
  }
},

// AFTER
async handlePolicyUpdate(updatedPolicy) {
  try {
    // Update policy via store (uses correct parameter names)
    await this.$store.dispatch('protection/updatePolicy', {
      policyType: this.policyType,  ← Changed
      id: this.policyId,            ← Changed
      policyData: updatedPolicy,    ← Changed
    });

    this.showEditModal = false;
    await this.loadPolicy();
  } catch (error) {
    console.error('Failed to update policy:', error);
  }
},
```

---

## Files Changed

1. **resources/js/views/Protection/ProtectionDashboard.vue**
   - Added PolicyFormModal import and component registration
   - Added showForm and editingPolicy data properties
   - Implemented handleAddPolicy(), handleEditPolicy(), closeForm(), handlePolicySaved()
   - Added modal to template

2. **resources/js/components/Protection/PolicyDetail.vue**
   - Added `:is-editing="true"` prop to PolicyFormModal (line 294)
   - Fixed handlePolicyUpdate() to pass correct parameters to store (lines 537-540)

3. **Frontend Build**
   - `public/build/assets/ProtectionDashboard-*.js` - New build hash
   - `public/build/assets/PolicyDetail-*.js` - New build hash
   - `public/build/manifest.json` - Updated manifest

---

## Deployment Process

### Method: SSH Terminal or File Manager Upload

Since this is a **frontend-only change** (no backend/database changes), only the built assets need to be uploaded.

#### Option 1: Upload via SiteGround File Manager (Recommended)

1. **Log into SiteGround** → Site Tools → File Manager
2. **Navigate to**: `www/csjones.co/tengo-app/public/build/assets/`
3. **Upload these files** (from local `public/build/assets/`):
   - `ProtectionDashboard-nGLQHe7i.js`
   - `ProtectionDashboard-BBEL5px9.css`
   - `PolicyDetail-akYIJiaI.js`
4. **Navigate to**: `www/csjones.co/tengo-app/public/build/`
5. **Upload**: `manifest.json`

#### Option 2: Via SSH Terminal

If you have an SSH terminal connected:

```bash
# Navigate to local project directory
cd ~/Desktop/fpsApp/tengo

# Create tarball of build files
tar -czf protection-fix.tar.gz \
  public/build/assets/ProtectionDashboard-nGLQHe7i.js \
  public/build/assets/ProtectionDashboard-BBEL5px9.css \
  public/build/assets/PolicyDetail-akYIJiaI.js \
  public/build/manifest.json

# From your SSH terminal (already connected to server):
# Upload the tarball (use your preferred method - SFTP, File Manager, etc.)
# Then on server:
cd ~/www/csjones.co/tengo-app
tar -xzf protection-fix.tar.gz
rm protection-fix.tar.gz
```

**No Laravel cache clear needed** - This is purely a frontend change.

---

## Testing & Verification

**Test Steps**:

1. **Test Add Policy from Dashboard**:
   - Navigate to https://csjones.co/tengo/protection
   - Click "Add Policy" button in overview
   - **Expected**: Modal appears with blank form
   - Fill in policy details
   - Click "Save"
   - **Expected**: Modal closes, policy appears in list

2. **Test Edit Policy from Dashboard**:
   - Click "Edit" icon on any policy card in overview
   - **Expected**: Modal appears with form populated with policy data
   - Modify a field
   - Click "Save"
   - **Expected**: Modal closes, changes reflected in policy card

3. **Test Edit from Policy Detail Page**:
   - Click on a policy card to view details
   - Click "Edit" button
   - **Expected**: Modal appears with form populated
   - Modify a field
   - Click "Save"
   - **Expected**: No console errors, changes saved and displayed

**Success Criteria**:
- ✅ Add Policy button opens modal with blank form
- ✅ Edit buttons open modal with populated form
- ✅ Save button works without "Unknown policy type" error
- ✅ No console errors
- ✅ Changes persist after save

---

## Prevention for Future

### Code Review Checklist

When implementing reusable form modals:

- [ ] Modal accepts both `null` (add mode) and `object` (edit mode) for data prop
- [ ] Modal has `:is-editing` boolean prop to distinguish modes
- [ ] Parent component tracks `showForm` and `editingItem` state
- [ ] Parent implements `handleAdd()` (set item to null, show form)
- [ ] Parent implements `handleEdit(item)` (set item, show form)
- [ ] Parent implements `closeForm()` (hide form, reset item to null)
- [ ] Modal emits `@save` (not `@submit`) and `@close` events
- [ ] Store actions expect consistent parameter names across all calls

### Pattern to Follow

**Use ProtectionPoliciesStep.vue (onboarding) as reference** - it correctly implements the add/edit modal pattern.

---

## Related Patterns

This fix establishes the correct pattern for reusable form modals in TenGo:

**✅ Working Modules** (use this pattern):
- Protection (Onboarding) - ProtectionPoliciesStep.vue
- Property - PropertyForm modal usage
- Savings - SavingsAccountForm modal usage

**⚠️ Verify Other Modules**:
- Investment accounts
- Retirement pensions
- Estate assets

All modules with add/edit functionality should follow this pattern.

---

## Lessons Learned

1. **Unified Form Pattern**: Single modal component for both add and edit modes requires `:is-editing` prop
2. **Event Emission Architecture**: Child components emit events, parent manages state and modal visibility
3. **Store Parameter Consistency**: Always check store action signatures before calling them
4. **Pattern Analysis**: When debugging, find a working example first (onboarding worked, dashboard didn't)
5. **Systematic Debugging**: Following the 4-phase process (Investigation → Pattern Analysis → Hypothesis → Implementation) quickly identified all three related root causes

---

## References

- Systematic Debugging Skill: `.claude/skills/systematic-debugging`
- Working Pattern: `resources/js/components/Onboarding/steps/ProtectionPoliciesStep.vue`
- Store Implementation: `resources/js/store/modules/protection.js`
- CLAUDE.md Guidelines: "Section 4. Unified Form Components"

---

**Status**: ✅ **READY FOR DEPLOYMENT** - Built and tested locally

**Next Step**: Upload built assets to production server

---

**Documented By**: Claude Code (Anthropic)
**Date**: November 13, 2025

🤖 Generated with [Claude Code](https://claude.com/claude-code)
